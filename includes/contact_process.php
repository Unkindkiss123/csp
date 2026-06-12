<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/recaptcha.php';

// Apenas POST
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo '<div class="container p-4"><div class="alert alert-danger">Método não permitido.</div></div>';
  exit;
}

// CSRF
validarCSRF();

// Honeypot (bots)
$website = trim($_POST['website'] ?? '');
if ($website !== '') {
  // finge sucesso, mas ignora
  header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../views/contactos_view.php?enviado=1');
  exit;
}

$nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$mensagem = htmlspecialchars(trim($_POST['mensagem'] ?? ''));

// reCAPTCHA (se configurado)
if (recaptcha_is_configured()) {
  $recaptchaResp = (string)($_POST['g-recaptcha-response'] ?? '');
  $ver = verify_recaptcha($recaptchaResp, cliente_ip());
  if (!$ver['success']) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['contact_errors'] = ['Validação de segurança falhou.'];
    $_SESSION['contact_old'] = ['nome'=>$nome,'email'=>$email,'mensagem'=>$mensagem];
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../views/contactos_view.php?erro=1');
    exit;
  }
}

$erros = [];
if ($nome === '') $erros[] = 'Nome é obrigatório.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Email inválido.';
if ($mensagem === '') $erros[] = 'Mensagem é obrigatória.';

if ($erros) {
  // Guardar na sessão e voltar
  $_SESSION['contact_errors'] = $erros;
  $_SESSION['contact_old'] = ['nome'=>$nome,'email'=>$email,'mensagem'=>$mensagem];
  header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../views/contactos_view.php?erro=1');
  exit;
}

// Estratégia simples: gravar numa tabela de contactos se existir; caso contrário, enviar e-mail (placeholder)
try {
  if (isset($conn) && $conn instanceof mysqli) {
    $conn->query("CREATE TABLE IF NOT EXISTS contactos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nome VARCHAR(120) NOT NULL,
      email VARCHAR(180) NOT NULL,
      mensagem TEXT NOT NULL,
      ip VARCHAR(64) DEFAULT NULL,
      criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $conn->prepare('INSERT INTO contactos (nome, email, mensagem, ip) VALUES (?,?,?,?)');
    $ip = cliente_ip();
    $stmt->bind_param('ssss', $nome, $email, $mensagem, $ip);
    $stmt->execute();
    $stmt->close();
  } else {
    // Placeholder de envio e-mail: ajusta para mail() ou PHPMailer se necessário
    // mail('info@constroi-spa-pool.test', 'Novo contacto', $mensagem, 'From: '.$email);
  }
} catch (Throwable $e) {
  // Fallback silencioso: não quebrar UX
  if (session_status() === PHP_SESSION_NONE) session_start();
  $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Ocorreu um erro ao enviar a mensagem. Tenta novamente mais tarde.'];
}
// Limpar dados antigos
unset($_SESSION['contact_errors'], $_SESSION['contact_old']);
$_SESSION['contact_success'] = 'Mensagem enviada com sucesso. Obrigado pelo contacto!';
header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../views/contactos_view.php?enviado=1');
exit;
