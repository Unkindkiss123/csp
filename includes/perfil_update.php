<?php
declare(strict_types=1);
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/mailer.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  site_redirect('/views/perfil_view.php');
}

// Unificar validação CSRF
if (!function_exists('validate_csrf') || !validate_csrf($_POST['csrf_token'] ?? '')) {
  if (defined('APP_ENV') && APP_ENV === 'production') {
    error_log('[perfil_update] user_id=' . (int)($_SESSION['user_id'] ?? 0) . ' motivo=csrf');
  }
  http_response_code(400);
  exit('CSRF inválido');
}

// Rate limit suave: mínimo PROFILE_RATE_LIMIT_SECONDS entre updates
if (session_status() === PHP_SESSION_NONE) session_start();
$now = time();
$last = (int)($_SESSION['last_profile_post'] ?? 0);
$_window = defined('PROFILE_RATE_LIMIT_SECONDS') ? (int)PROFILE_RATE_LIMIT_SECONDS : 5;
if ($last && ($now - $last) < $_window) {
  $_SESSION['flash_error'] = 'Demasiados pedidos. Tenta outra vez em alguns segundos.';
  site_redirect('/views/perfil_view.php');
}
$_SESSION['last_profile_post'] = $now;

$uid = (int)($_SESSION['user_id'] ?? 0);
// Campos de perfil (podem não existir se o POST for apenas para alterar password)
$nome = isset($_POST['nome_completo']) ? trim((string)$_POST['nome_completo']) : '';
$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
// Aceitar nomes alternativos para compatibilidade
$senhaAtual = (string)($_POST['senha_atual'] ?? $_POST['password_atual'] ?? '');
$senhaNova = (string)($_POST['senha_nova'] ?? $_POST['password_nova'] ?? '');
$senhaNova2 = (string)($_POST['senha_nova2'] ?? $_POST['password_nova_confirm'] ?? '');
// Código de confirmação (6 dígitos)
$codigoRaw = isset($_POST['codigo_confirmacao']) ? trim((string)$_POST['codigo_confirmacao']) : '';

// Validação defensiva de nome/email (apenas se estes campos vierem no POST)
if ($nome !== '' || $email !== '') {
  if ($nome === '') { $_SESSION['flash_error'] = 'Nome não pode estar vazio.'; site_redirect('/views/perfil_view.php'); }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $_SESSION['flash_error'] = 'Email inválido.'; site_redirect('/views/perfil_view.php'); }
  $nomeLen = function_exists('mb_strlen') ? mb_strlen($nome, 'UTF-8') : strlen($nome);
  if ($nomeLen > 120) { $_SESSION['flash_error'] = 'Nome demasiado longo (máx. 120 caracteres).'; site_redirect('/views/perfil_view.php'); }

  // Verifica duplicação de email noutros utilizadores
  $stmt = $conn->prepare('SELECT id FROM utilizadores WHERE email = ? AND id <> ? LIMIT 1');
  $stmt->bind_param('si', $email, $uid);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) { $stmt->close(); $_SESSION['flash_error'] = 'Este email já está em uso.'; site_redirect('/views/perfil_view.php'); }
  $stmt->close();

  // Atualiza nome/email
  $upd = $conn->prepare('UPDATE utilizadores SET nome_completo = ?, email = ? WHERE id = ?');
  $upd->bind_param('ssi', $nome, $email, $uid);
  $upd->execute();
  $upd->close();
}

// Se pretende alterar a senha (requer confirmação por código)
if ($senhaNova !== '' || $senhaNova2 !== '') {
  if ($senhaNova !== $senhaNova2) { $_SESSION['flash_error'] = 'Nova senha e confirmação não coincidem.'; site_redirect('/views/perfil_view.php'); }
  if (strlen($senhaNova) < 8 || strlen($senhaNova) > 64) { $_SESSION['flash_error'] = 'A nova senha deve ter entre 8 e 64 caracteres.'; site_redirect('/views/perfil_view.php'); }
  if (!preg_match('/[A-Z]/', $senhaNova) || !preg_match('/\d/', $senhaNova) || !preg_match('/[@$!%*?&\-_^#()+=]/', $senhaNova)) { $_SESSION['flash_error'] = 'A nova senha deve ter pelo menos uma maiúscula, um número e um símbolo.'; site_redirect('/views/perfil_view.php'); }
  // Código de confirmação obrigatório e válido (6 dígitos)
  if ($codigoRaw === '' || !preg_match('/^[0-9]{6}$/', $codigoRaw)) {
    $_SESSION['flash_error'] = 'Código de confirmação inválido.';
    site_redirect('/views/perfil_view.php');
  }
  // Transação: SELECT hash atual -> UPDATE senha -> DELETE tokens -> COMMIT
  $q = $u = $stmtDel = null;
  try {
    $conn->begin_transaction();

    // SELECT hash atual
  $q = $conn->prepare('SELECT password_hash, email, nome_completo FROM utilizadores WHERE id = ?');
    if (!$q) { throw new Exception('Falha prepare SELECT'); }
    $q->bind_param('i', $uid);
    if (!$q->execute()) { throw new Exception('Falha execute SELECT'); }
    $q->bind_result($hash, $emailAtualDB, $nomeAtualDB);
    if (!$q->fetch()) { throw new Exception('Utilizador não encontrado'); }
    $q->close(); $q = null;

    // Validar senha atual
  if ($senhaAtual === '' || !password_verify($senhaAtual, $hash)) {
      $conn->rollback();
      if (defined('APP_ENV') && APP_ENV === 'production') {
        error_log('[perfil_update] user_id=' . (int)$uid . ' motivo=password_invalida');
      }
      $_SESSION['flash_error'] = 'Senha atual incorreta.';
      site_redirect('/views/perfil_view.php');
    }

    // Buscar último código não usado
    $stmtCodigo = $conn->prepare('SELECT id, code_hash, expira_em, tentativas FROM perfil_codigos WHERE utilizador_id = ? AND usado = 0 ORDER BY enviado_em DESC LIMIT 1');
    if (!$stmtCodigo) { throw new Exception('Falha prepare SELECT codigo'); }
    $stmtCodigo->bind_param('i', $uid);
    if (!$stmtCodigo->execute()) { throw new Exception('Falha execute SELECT codigo'); }
    $stmtCodigo->bind_result($codigoId, $codeHashDB, $expiraEm, $tentativas);
    if (!$stmtCodigo->fetch()) {
      $stmtCodigo->close();
      $conn->rollback();
      $_SESSION['flash_error'] = 'Não existe nenhum código válido. Pede um novo código.';
      site_redirect('/views/perfil_view.php');
    }
    $stmtCodigo->close();

    // Valida expiração
    if (strtotime((string)$expiraEm) <= time()) {
      $_SESSION['flash_error'] = 'O código expirou. Pede um novo código.';
      // Marca como usado para não confundir
      $conn->query('UPDATE perfil_codigos SET usado = 1 WHERE id = ' . (int)$codigoId);
      $conn->rollback();
      site_redirect('/views/perfil_view.php');
    }

    // Verifica tentativas
    if ((int)$tentativas >= 3) {
      $_SESSION['flash_error'] = 'Excedeste o número de tentativas. Pede um novo código.';
      $conn->rollback();
      site_redirect('/views/perfil_view.php');
    }

    // Compara hash do código
    $codigoHash = hash('sha256', $codigoRaw);
    if (!hash_equals((string)$codeHashDB, $codigoHash)) {
      // Incrementa tentativas
      $inc = $conn->prepare('UPDATE perfil_codigos SET tentativas = tentativas + 1 WHERE id = ?');
      if ($inc) { $inc->bind_param('i', $codigoId); $inc->execute(); $inc->close(); }
      $conn->rollback();
      $_SESSION['flash_error'] = 'Código incorreto.';
      site_redirect('/views/perfil_view.php');
    }

    // UPDATE nova senha
  $novoHash = password_hash($senhaNova, PASSWORD_DEFAULT);
  $u = $conn->prepare('UPDATE utilizadores SET password_hash = ? WHERE id = ?');
    if (!$u) { throw new Exception('Falha prepare UPDATE'); }
    $u->bind_param('si', $novoHash, $uid);
    if (!$u->execute()) { throw new Exception('Falha execute UPDATE'); }
    $u->close(); $u = null;

    // Marca código como usado
    $mark = $conn->prepare('UPDATE perfil_codigos SET usado = 1 WHERE id = ?');
    if ($mark) { $mark->bind_param('i', $codigoId); $mark->execute(); $mark->close(); }

    // DELETE tokens de recuperação
    $stmtDel = $conn->prepare('DELETE FROM tokens_recuperacao WHERE utilizador_id = ?');
    if (!$stmtDel) { throw new Exception('Falha prepare DELETE'); }
    $stmtDel->bind_param('i', $uid);
    if (!$stmtDel->execute()) { throw new Exception('Falha execute DELETE'); }
    $stmtDel->close(); $stmtDel = null;

    // Commit da transação
    $conn->commit();

    // Regenerar sessão após troca de password
    if (session_status() === PHP_SESSION_ACTIVE) { session_regenerate_id(true); }

    // Notificação por email (informativa) após alteração de password
    try {
      $subject = 'A tua palavra‑passe foi alterada';
      $ip = cliente_ip();
      $html = '<p>Olá ' . h($nome) . ',</p>'
            . '<p>A tua palavra‑passe foi alterada com sucesso.</p>'
            . '<p>Se não foste tu a fazer esta alteração, redefine de imediato a tua senha através do link de recuperação e contacta o suporte.</p>'
            . '<p><small>IP: ' . h($ip) . ' · Data: ' . h(date('Y-m-d H:i:s')) . '</small></p>';
      // Usa o email atual (preferir valor da BD se nome/email não vieram no POST)
      $emailPara = ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : (string)$emailAtualDB;
      $nomePara  = ($nome !== '') ? $nome : (string)$nomeAtualDB;
      $html = '<p>Olá ' . h($nomePara) . ',</p>'
            . '<p>A tua palavra‑passe foi alterada com sucesso.</p>'
            . '<p>Se não foste tu a fazer esta alteração, redefine de imediato a tua senha através do link de recuperação e contacta o suporte.</p>'
            . '<p><small>IP: ' . h($ip) . ' · Data: ' . h(date('Y-m-d H:i:s')) . '</small></p>';
      if (filter_var($emailPara, FILTER_VALIDATE_EMAIL)) {
        @send_mail($emailPara, $subject, $html);
      }
    } catch (Throwable $__) { /* silencioso em DEV */ }
  } catch (Throwable $e) {
    // Rollback em caso de erro
    try { $conn->rollback(); } catch (Throwable $__) {}
    if ($q) { try { $q->close(); } catch (Throwable $__) {} }
    if ($u) { try { $u->close(); } catch (Throwable $__) {} }
    if ($stmtDel) { try { $stmtDel->close(); } catch (Throwable $__) {} }
    $_SESSION['flash_error'] = 'Não foi possível atualizar a password. Tenta novamente.';
    site_redirect('/views/perfil_view.php');
  }
  $_SESSION['flash_success'] = 'Palavra‑passe atualizada com sucesso.';
  site_redirect('/views/perfil_view.php');
}

// Se não houve alteração de password, mas atualizámos o perfil (nome/email)
if ($nome !== '' || $email !== '') {
  // Atualiza sessão básica (se fornecido)
  if ($nome !== '') { $_SESSION['nome'] = $nome; }
  $_SESSION['flash_success'] = 'Perfil atualizado com sucesso.';
} else {
  // Nada a fazer
  $_SESSION['flash_success'] = 'Nada a atualizar.';
}
site_redirect('/views/perfil_view.php');
