<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/mailer.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  site_redirect('/views/perfil_view.php');
}

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

// CSRF obrigatório
try {
  validarCSRF();
} catch (Throwable $e) {
  if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => 'CSRF inválido.']);
    exit;
  }
  alert('danger', 'CSRF inválido.');
  site_redirect('/views/perfil_view.php');
}

$uid = (int)($_SESSION['user_id'] ?? 0);

// Buscar email e nome do utilizador
$stmt = $conn->prepare('SELECT email, nome_completo FROM utilizadores WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($email, $nome);
if (!$stmt->fetch()) {
  $stmt->close();
  if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => 'Utilizador não encontrado.']);
    exit;
  }
  alert('danger', 'Utilizador não encontrado.');
  site_redirect('/views/perfil_view.php');
}
$stmt->close();

// Rate-limit: 60s desde o último envio
$rlStmt = $conn->prepare('SELECT enviado_em FROM perfil_codigos WHERE utilizador_id = ? ORDER BY enviado_em DESC LIMIT 1');
$rlStmt->bind_param('i', $uid);
$rlStmt->execute();
$rlStmt->bind_result($lastSentAt);
if ($rlStmt->fetch()) {
  $lastTs = strtotime((string)$lastSentAt);
  if ($lastTs && (time() - $lastTs) < 60) {
    $rlStmt->close();
    if ($isAjax) {
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode(['success' => false, 'message' => 'Aguarda alguns segundos antes de pedir novo código.']);
      exit;
    }
    alert('warning', 'Aguarda alguns segundos antes de pedir novo código.');
    site_redirect('/views/perfil_view.php');
  }
}
$rlStmt->close();

// Geração do código (6 dígitos zero-padded)
$codeInt = random_int(0, 999999);
$code = str_pad((string)$codeInt, 6, '0', STR_PAD_LEFT);
$codeHash = hash('sha256', $code);
$expiraEm = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

// Inserir registo
$ins = $conn->prepare('INSERT INTO perfil_codigos (utilizador_id, code_hash, expira_em, tentativas, enviado_em, usado) VALUES (?, ?, ?, 0, NOW(), 0)');
$ins->bind_param('iss', $uid, $codeHash, $expiraEm);
$okIns = $ins->execute();
$ins->close();

if (!$okIns) {
  if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => 'Não foi possível gerar o código. Tenta novamente.']);
    exit;
  }
  alert('danger', 'Não foi possível gerar o código. Tenta novamente.');
  site_redirect('/views/perfil_view.php');
}

// Envio de email
$subject = 'Código de confirmação para alterar a palavra-passe';
$html = '<p>Olá ' . h($nome) . ',</p>'
      . '<p>O teu código de confirmação é: <strong style="font-size:18px; letter-spacing:2px;">' . h($code) . '</strong></p>'
      . '<p>Este código é válido por 10 minutos.</p>'
      . '<p>Se não foste tu a pedir, ignora este email.</p>';
$sendRes = send_mail((string)$email, $subject, $html);

// Resposta diferenciada para AJAX vs navegação normal
if ($isAjax) {
  header('Content-Type: application/json; charset=UTF-8');
  $resp = ['success' => true, 'message' => 'Código enviado para o teu email.'];
  if (defined('APP_ENV') && APP_ENV === 'local') {
    // Expor o código no DEV para facilitar testes (não logar em produção)
    $resp['dev_code'] = $code;
  }
  echo json_encode($resp);
  exit;
}

// Modo navegação normal: alerts em sessão e redirect
if (defined('APP_ENV') && APP_ENV === 'local') {
  alert('success', 'Código DEV: ' . $code . ' (válido 10 min)');
} else {
  alert('success', 'Código enviado para o teu email.');
}
site_redirect('/views/perfil_view.php');
