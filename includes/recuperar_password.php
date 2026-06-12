<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/recaptcha.php';
require_once __DIR__ . '/mailer.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  site_redirect('/views/recuperar_view.php');
}

validarCSRF();

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  alert('danger', 'Indica um email válido.');
  site_redirect('/views/recuperar_view.php');
}

// reCAPTCHA (se configurado)
if (recaptcha_is_configured()) {
  $recaptchaResp = (string)($_POST['g-recaptcha-response'] ?? '');
  $ver = verify_recaptcha($recaptchaResp, cliente_ip());
  if (!$ver['success']) {
    alert('danger', 'Validação de segurança falhou. Tenta novamente.');
    site_redirect('/views/recuperar_view.php');
  }
}

// Gera tabela se não existir
$conn->query("CREATE TABLE IF NOT EXISTS tokens_recuperacao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tr_user (utilizador_id),
  KEY idx_tr_expira (expira_em),
  CONSTRAINT fk_tr_user FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Procura utilizador
$stmt = $conn->prepare('SELECT id, nome_completo FROM utilizadores WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($uid, $nome);
if (!$stmt->fetch()) {
  $stmt->close();
  // resposta genérica para não revelar emails
  alert('info', 'Se o email existir, enviámos instruções de recuperação.');
  site_redirect('/views/recuperar_view.php');
}
$stmt->close();

// Gera token e guarda hash
$token = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);
$expira = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

$ins = $conn->prepare('INSERT INTO tokens_recuperacao (utilizador_id, token_hash, expira_em) VALUES (?,?,?)');
$ins->bind_param('iss', $uid, $tokenHash, $expira);
$ins->execute();
$ins->close();

// Construir link absoluto
$base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
$link = $base . '/views/redefinir_view.php?token=' . urlencode($token);

// Em DEV: mostra o link diretamente na interface para facilitar testes
if (defined('IS_LOCAL') && IS_LOCAL) {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $_SESSION['reset_preview_link'] = $link;
}

// Envio de email em produção (opcional: mail())
$subject = 'Recuperar palavra-passe';
$html = '<p>Olá ' . htmlspecialchars($nome) . ',</p>'
  . '<p>Para redefinir a tua palavra-passe clica no link abaixo (válido por 30 minutos):</p>'
  . '<p><a href="' . htmlspecialchars($link) . '">Redefinir palavra‑passe</a></p>'
  . '<p>Se não foste tu a pedir, ignora esta mensagem.</p>';
$send = send_mail($email, $subject, $html);

alert('success', 'Se o email existir, enviámos um link de recuperação (válido por 30 minutos).');
site_redirect('/views/recuperar_view.php');
