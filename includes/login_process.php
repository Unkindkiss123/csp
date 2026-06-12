<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/recaptcha.php';
// Melhor feedback de CSRF
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || (!hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token']))) {
  alert('danger', 'Sessão expirada. Faz login novamente.');
  site_redirect('/views/login_view.php');
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$usuario = trim($_POST['usuario'] ?? '');
$senha = (string)($_POST['senha'] ?? '');
$ip = cliente_ip();

// reCAPTCHA (se configurado)
if (recaptcha_is_configured()) {
  $recaptchaResp = (string)($_POST['g-recaptcha-response'] ?? '');
  $ver = verify_recaptcha($recaptchaResp, $ip);
  if (!$ver['success']) {
    alert('danger', 'Validação de segurança falhou. Tenta novamente.');
    site_redirect('/views/login_view.php');
  }
}

// Controlo de tentativas
// Limpa registos antigos (janela móvel)
$conn->query("DELETE FROM tentativas_login WHERE TIMESTAMPDIFF(SECOND, ultima_tentativa, NOW()) > " . (int)LOGIN_JANELA_SEGUNDOS);

$tentativas = 0;
$res = $conn->prepare('SELECT tentativas FROM tentativas_login WHERE ip = ?');
$res->bind_param('s', $ip);
$res->execute();
$res->bind_result($tentativas);
$res->fetch();
$res->close();

if ($tentativas >= (int)LOGIN_MAX_TENTATIVAS) {
  alert('danger', 'Demasiadas tentativas. Tente novamente mais tarde.');
  site_redirect('/views/login_view.php');
}

// Busca utilizador por username OU email
$stmt = $conn->prepare('SELECT id, usuario, password_hash, role, nome_completo FROM utilizadores WHERE usuario = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $usuario, $usuario);
$stmt->execute();
$stmt->bind_result($id, $userDb, $hash, $role, $nome);
if ($stmt->fetch() && password_verify($senha, (string)$hash)) {
  // sucesso
  $stmt->close();
  $del = $conn->prepare('DELETE FROM tentativas_login WHERE ip = ?');
  $del->bind_param('s', $ip);
  $del->execute();

  // Rehash se necessário
  if (password_needs_rehash((string)$hash, PASSWORD_DEFAULT)) {
    $newHash = password_hash($senha, PASSWORD_DEFAULT);
    $upd = $conn->prepare('UPDATE utilizadores SET password_hash = ? WHERE id = ?');
    if ($upd) { $upd->bind_param('si', $newHash, $id); $upd->execute(); }
  }

  $_SESSION['user_id'] = $id;
  $_SESSION['usuario'] = $userDb;
  $_SESSION['role'] = $role;
  $_SESSION['nome'] = $nome;

  // Regenerar sessão após login
  if (session_status() === PHP_SESSION_ACTIVE) { session_regenerate_id(true); }

  site_redirect('/views/dashboard_view.php');
} else {
  $stmt->close();
  // falha → incrementa tentativas
  if ($tentativas > 0) {
    $upd = $conn->prepare('UPDATE tentativas_login SET tentativas = tentativas + 1, ultima_tentativa = NOW() WHERE ip = ?');
    $upd->bind_param('s', $ip);
    $upd->execute();
  } else {
    $ins = $conn->prepare('INSERT INTO tentativas_login (ip, tentativas, ultima_tentativa) VALUES (?, 1, NOW())');
    $ins->bind_param('s', $ip);
    $ins->execute();
  }
  alert('danger', 'Credenciais inválidas.');
  site_redirect('/views/login_view.php');
}
