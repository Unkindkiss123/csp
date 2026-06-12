<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/recaptcha.php';
validarCSRF();

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$usuario = trim($_POST['usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = (string)($_POST['senha'] ?? '');
$nome = trim($_POST['nome_completo'] ?? '');
$localidade = trim($_POST['localidade'] ?? '');
$andar = trim($_POST['andar'] ?? '');
$porta = trim($_POST['porta'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$cod_postal = trim($_POST['cod_postal'] ?? '');
$data_nascimento = trim($_POST['data_nascimento'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$contribuinte = trim($_POST['contribuinte'] ?? '');

// reCAPTCHA (se configurado)
if (recaptcha_is_configured()) {
  $recaptchaResp = (string)($_POST['g-recaptcha-response'] ?? '');
  $ver = verify_recaptcha($recaptchaResp);
  if (!$ver['success']) {
    alert('danger', 'Validação de segurança falhou. Resolve o reCAPTCHA.');
    site_redirect('/views/register_view.php');
  }
}

// Validações mínimas
$erros = [];
if ($usuario === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,20}$/', $usuario)) $erros[] = 'Utilizador inválido (3-20 chars).';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Email inválido.';

// Política de password
$senhaLen = strlen($senha);
if ($senhaLen < 8 || $senhaLen > 64) {
  $erros[] = 'A senha deve ter entre 8 e 64 caracteres.';
}
// Sem espaços no início/fim
if ($senha !== trim($senha)) {
  $erros[] = 'A senha não pode começar nem terminar com espaços.';
}
// Não igual a utilizador/email
if (strcasecmp($senha, $usuario) === 0 || strcasecmp($senha, $email) === 0) {
  $erros[] = 'A senha não pode ser igual ao utilizador ou email.';
}
// Requisitos: pelo menos uma maiúscula, um número e um símbolo
$hasUpper = preg_match('/[A-Z]/', $senha);
$hasDigit = preg_match('/\d/', $senha);
$hasSymbol = preg_match('/[@$!%*?&\-_^#()+=]/', $senha);
if (!$hasUpper || !$hasDigit || !$hasSymbol) {
  $erros[] = 'A senha deve ter pelo menos uma maiúscula, um número e um símbolo.';
}
// Bloquear sequências óbvias
$weakSeq = ['12345','123456','abcdef','qwerty','password','admin','letmein','senha','constrói','empresa'];
$senhaLower = mb_strtolower($senha, 'UTF-8');
foreach ($weakSeq as $seq) {
  if (strpos($senhaLower, $seq) !== false) { $erros[] = 'A senha é demasiado óbvia. Escolhe outra.'; break; }
}

if ($nome === '') $erros[] = 'Nome completo é obrigatório.';
if ($data_nascimento !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_nascimento)) $erros[] = 'Data de nascimento inválida (YYYY-MM-DD).';

if ($erros) {
  foreach ($erros as $e) alert('danger', (string)$e);
  site_redirect('/views/register_view.php');
}

// Duplicados
$stmt = $conn->prepare('SELECT id FROM utilizadores WHERE usuario = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $usuario, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  alert('danger', 'Utilizador ou email já existem.');
  site_redirect('/views/register_view.php');
}
$stmt->close();

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare('INSERT INTO utilizadores (usuario, email, password_hash, nome_completo, localidade, andar, porta, numero, cod_postal, data_nascimento, telefone, contribuinte) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
$stmt->bind_param('ssssssssssss', $usuario, $email, $hash, $nome, $localidade, $andar, $porta, $numero, $cod_postal, $data_nascimento, $telefone, $contribuinte);

if ($stmt->execute()) {
  alert('success', 'Conta criada com sucesso! Faz login para continuar.');
  site_redirect('/views/login_view.php');
} else {
  alert('danger', 'Erro ao criar conta.');
  site_redirect('/views/register_view.php');
}
