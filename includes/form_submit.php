<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/recaptcha.php';
require_once __DIR__ . '/mailer.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit;
}

// Flags de dev para reCAPTCHA (opcional)
$RECAPTCHA_ENABLED = defined('RECAPTCHA_ENABLED') ? (bool)RECAPTCHA_ENABLED : false;

// CSRF check: usar csrf_check() se existir; senão validação manual
$csrfToken = (string)($_POST['csrf_token'] ?? '');
if (function_exists('csrf_check')) {
    if (!csrf_check($csrfToken)) {
        error_log('[leads] CSRF inválido');
        header('Location: ' . url((($_POST['source'] ?? '') === 'orcamento' ? 'orcamento.php' : 'contactos.php')) . '?err=csrf');
        exit;
    }
} else {
    if (!isset($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], $csrfToken)) {
        error_log('[leads] CSRF inválido');
        header('Location: ' . url((($_POST['source'] ?? '') === 'orcamento' ? 'orcamento.php' : 'contactos.php')) . '?err=csrf');
        exit;
    }
}

// Honeypot (bot trap)
if (!empty($_POST['website'])) {
    // Simular sucesso para bots, mas não inserir
    error_log('[leads] Honeypot acionado');
    $dest = (($_POST['source'] ?? '') === 'orcamento') ? url('orcamento.php') . '?ok=1' : url('contactos.php') . '?ok=1';
    header('Location: ' . $dest);
    exit;
}

$source = strtolower(trim((string)($_POST['source'] ?? '')));
if ($source !== 'orcamento' && $source !== 'contacto') { $source = 'contacto'; }

$nome      = trim((string)($_POST['nome'] ?? ''));
$email     = trim((string)($_POST['email'] ?? ''));
$telefone  = trim((string)($_POST['telefone'] ?? ''));
$assunto   = trim((string)($_POST['assunto'] ?? ''));
$servico   = trim((string)($_POST['servico'] ?? ''));
$localidade= trim((string)($_POST['localidade'] ?? ''));
$prazo     = trim((string)($_POST['prazo'] ?? ''));
$orcEst    = preg_replace('/[^\d\.,]/', '', trim((string)($_POST['orcamento_estimado'] ?? '')));
$mensagem  = trim((string)($_POST['mensagem'] ?? ''));
$utm_source  = trim((string)($_POST['utm_source'] ?? ($_GET['utm_source'] ?? '')));
$utm_medium  = trim((string)($_POST['utm_medium'] ?? ($_GET['utm_medium'] ?? '')));
$utm_campaign= trim((string)($_POST['utm_campaign'] ?? ($_GET['utm_campaign'] ?? '')));
$consent   = !empty($_POST['consent']);

$errors = [];
if ($nome === '') $errors[] = 'Nome é obrigatório.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
if ($source === 'orcamento' && $telefone === '') $errors[] = 'Telefone é obrigatório para orçamentos.';
if (!$consent) $errors[] = 'É necessário consentir o tratamento de dados.';

// reCAPTCHA (opcional em dev)
if ($RECAPTCHA_ENABLED) {
    $captchaToken = (string)($_POST['g-recaptcha-response'] ?? '');
    $vr = verify_recaptcha($captchaToken);
    $okCaptcha = is_array($vr) ? (bool)($vr['success'] ?? false) : (bool)$vr;
    if (!$okCaptcha) {
        header('Location: ' . url(($source === 'orcamento' ? 'orcamento.php' : 'contactos.php')) . '?err=captcha');
        exit;
    }
}

// Upload handling (optional)
$anexoPath = null;
if (!empty($_FILES['anexo']) && is_array($_FILES['anexo']) && (int)$_FILES['anexo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['anexo'];
    if ((int)$f['error'] !== UPLOAD_ERR_OK || empty($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) {
        $errors[] = 'Falha no upload do ficheiro.';
    } else {
        $max = 5 * 1024 * 1024; // 5MB
        if ((int)$f['size'] > $max) {
            $errors[] = 'Ficheiro demasiado grande (máx. 5MB).';
        } else {
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'application/pdf' => 'pdf',
            ];
            $mime = null;
            if (function_exists('finfo_open')) {
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                if ($fi) { $mime = finfo_file($fi, $f['tmp_name']); finfo_close($fi); }
            }
            if (!$mime && isset($f['type'])) { $mime = (string)$f['type']; }
            if (!isset($allowed[$mime])) {
                $errors[] = 'Tipo de ficheiro inválido. Aceitamos JPG, PNG ou PDF.';
            } else {
                $ext = $allowed[$mime];
                $root = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
                $dir  = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'leads' . DIRECTORY_SEPARATOR;
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $name = 'lead_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $dir . $name;
                if (!@move_uploaded_file($f['tmp_name'], $dest)) {
                    $errors[] = 'Não foi possível guardar o anexo.';
                } else {
                    $anexoPath = '/uploads/leads/' . $name;
                }
            }
        }
    }
}

if (!empty($errors)) {
    header('Location: ' . url(($source === 'orcamento' ? 'orcamento.php' : 'contactos.php')) . '?err=val');
    exit;
}

// Insert into leads
$estado = 'novo';
$assunto_final = $assunto;
if ($assunto_final === '') {
    $assunto_final = ($source === 'orcamento') ? 'Pedido de orçamento' : 'Contacto do site';
}

$stmt = $conn->prepare('INSERT INTO leads (source, nome, email, telefone, assunto, servico, localidade, prazo, orcamento_estimado, mensagem, anexo_path, estado, utm_source, utm_medium, utm_campaign, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
$stmt->bind_param(
    'sssssssssssssss',
    $source,
    $nome,
    $email,
    $telefone,
    $assunto_final,
    $servico,
    $localidade,
    $prazo,
    $orcEst,
    $mensagem,
    $anexoPath,
    $estado,
    $utm_source,
    $utm_medium,
    $utm_campaign
);
$ok = $stmt->execute();
if (!$ok) {
    error_log('[leads] INSERT_ERROR: ' . $stmt->errno . ' ' . $stmt->error);
}
$leadId = $ok ? (int)$stmt->insert_id : 0;
$stmt->close();

if (!$ok) {
    // LOG explícito para debug de BD
    $logDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
    $logPath = rtrim($logDir, '/\\') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'form_submit.log';
    @is_dir(dirname($logPath)) || @mkdir(dirname($logPath), 0775, true);
    @file_put_contents($logPath, date('c') . ' INSERT_ERROR: ' . $conn->errno . ' ' . $conn->error . PHP_EOL, FILE_APPEND);
    header('Location: ' . url(($source === 'orcamento' ? 'orcamento.php' : 'contactos.php')) . '?err=db');
    exit;
}

// Emails
$adminEmail = (defined('MAIL_FROM_EMAIL') && MAIL_FROM_EMAIL !== '') ? MAIL_FROM_EMAIL : 'admin@localhost';
$siteName = 'Constrói Spa & Pool';

$clienteSubject = $siteName . ' — Recebemos o seu ' . ($source === 'orcamento' ? 'pedido de orçamento' : 'contacto');
$clienteHtml = '<p>Olá ' . h($nome) . ',</p>' .
               '<p>Confirmamos a receção do seu ' . ($source === 'orcamento' ? 'pedido de orçamento' : 'contacto') . '. Responderemos em breve.</p>' .
               '<p>Obrigado,<br>' . $siteName . '</p>';
@send_mail($email, $clienteSubject, $clienteHtml);

$adminSubject = '[Lead] ' . strtoupper($source) . ' #' . $leadId . ' — ' . $nome;
$adminHtml = '<h3>Nova lead (' . h($source) . ')</h3>' .
             '<ul>' .
             '<li><strong>Nome:</strong> ' . h($nome) . '</li>' .
             '<li><strong>Email:</strong> ' . h($email) . '</li>' .
             ($telefone !== '' ? '<li><strong>Telefone:</strong> ' . h($telefone) . '</li>' : '') .
             ($servico !== '' ? '<li><strong>Serviço:</strong> ' . h($servico) . '</li>' : '') .
             ($localidade !== '' ? '<li><strong>Localidade:</strong> ' . h($localidade) . '</li>' : '') .
             ($prazo !== '' ? '<li><strong>Prazo:</strong> ' . h($prazo) . '</li>' : '') .
             ($orcEst !== '' ? '<li><strong>Orçamento estimado:</strong> ' . h($orcEst) . ' €</li>' : '') .
             '<li><strong>Assunto:</strong> ' . h($assunto_final) . '</li>' .
             '<li><strong>Mensagem:</strong><br>' . nl2br(h($mensagem)) . '</li>' .
             ($anexoPath ? '<li><strong>Anexo:</strong> ' . h($anexoPath) . '</li>' : '') .
             '</ul>' .
             '<p><a href="' . h(url('/admin/orcamentos.php')) . '">Abrir no backoffice</a></p>';
@send_mail($adminEmail, $adminSubject, $adminHtml);

// Redirect success
$dest = ($source === 'orcamento') ? url('orcamento.php') . '?ok=1' : url('contactos.php') . '?ok=1';
header('Location: ' . $dest);
exit;
