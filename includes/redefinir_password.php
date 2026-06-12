<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/config.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  site_redirect('/views/redefinir_view.php');
}

validarCSRF();

$token = (string)($_POST['token'] ?? '');
$senha = (string)($_POST['senha'] ?? '');
$senha2 = (string)($_POST['senha2'] ?? '');

if ($token === '') {
  alert('danger', 'Token inválido.');
  site_redirect('/views/recuperar_view.php');
}
if ($senha !== $senha2) {
  alert('danger', 'As senhas não coincidem.');
  site_redirect('/views/redefinir_view.php?token=' . urlencode($token));
}
if (strlen($senha) < 8 || strlen($senha) > 64) {
  alert('danger', 'A senha deve ter entre 8 e 64 caracteres.');
  site_redirect('/views/redefinir_view.php?token=' . urlencode($token));
}
// Requisitos básicos
if (!preg_match('/[A-Z]/', $senha) || !preg_match('/\d/', $senha) || !preg_match('/[@$!%*?&\-_^#()+=]/', $senha)) {
  alert('danger', 'A senha deve ter pelo menos uma maiúscula, um número e um símbolo.');
  site_redirect('/views/redefinir_view.php?token=' . urlencode($token));
}

$tokenHash = hash('sha256', $token);

// Valida token
$stmt = $conn->prepare('SELECT tr.id, tr.utilizador_id, u.usuario, u.email FROM tokens_recuperacao tr INNER JOIN utilizadores u ON u.id = tr.utilizador_id WHERE tr.token_hash = ? AND tr.usado = 0 AND tr.expira_em > NOW() LIMIT 1');
$stmt->bind_param('s', $tokenHash);
$stmt->execute();
$stmt->bind_result($tid, $uid, $usuario, $email);
if (!$stmt->fetch()) {
  $stmt->close();
  alert('danger', 'Token inválido ou expirado.');
  site_redirect('/views/recuperar_view.php');
}
$stmt->close();

// Atualiza senha
$hash = password_hash($senha, PASSWORD_DEFAULT);
$upd = $conn->prepare('UPDATE utilizadores SET password_hash = ? WHERE id = ?');
$upd->bind_param('si', $hash, $uid);
$upd->execute();
$upd->close();

// Marca token como usado
$mark = $conn->prepare('UPDATE tokens_recuperacao SET usado = 1 WHERE id = ?');
$mark->bind_param('i', $tid);
$mark->execute();
$mark->close();

alert('success', 'Senha alterada com sucesso. Já podes iniciar sessão.');
site_redirect('/views/login_view.php');
