<?php
declare(strict_types=1);
// Teste simples de integração do PHPMailer com Composer
// URL: http://localhost/constroi_spa_and_pool/teste_mailer.php

// Carrega config para ler constantes de SMTP/MAIL_DRIVER
require_once __DIR__ . '/includes/config.php';

// Autoload do Composer (PHPMailer)
require __DIR__ . '/vendor/autoload.php';

echo '<h3>Teste de Integração PHPMailer</h3>';

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo '❌ PHPMailer não encontrado. Instala com: <code>composer require phpmailer/phpmailer</code> e volta a tentar.';
    return;
}

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo '✅ PHPMailer carregado com sucesso! Versão: ' . htmlspecialchars(PHPMailer\PHPMailer\PHPMailer::VERSION) . '<br>';

    // Teste de envio (opcional) quando MAIL_DRIVER='smtp'
    if (defined('MAIL_DRIVER') && MAIL_DRIVER === 'smtp') {
        $mail->isSMTP();
        $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $mail->SMTPAuth = defined('SMTP_AUTH') ? (bool)SMTP_AUTH : true;
        $mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $mail->CharSet = 'UTF-8';
        $fromEmail = defined('MAIL_FROM_EMAIL') && MAIL_FROM_EMAIL !== '' ? MAIL_FROM_EMAIL : 'no-reply@localhost';
        $fromName  = defined('MAIL_FROM_NAME') && MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : 'Constrói Spa & Pool';
        $mail->setFrom($fromEmail, $fromName);
        // Envia para o próprio remetente como teste
        $mail->addAddress($fromEmail, 'Teste Local');
        $mail->isHTML(true);
        $mail->Subject = 'Teste PHPMailer (Constrói Spa & Pool)';
        $mail->Body    = '🎉 PHPMailer está funcional e configurado corretamente!';
        $mail->AltBody = 'PHPMailer está funcional e configurado corretamente!';
        $mail->send();
        echo '✉️ Email de teste enviado com sucesso!';
    } else {
        echo 'ℹ️ Teste de envio não executado (MAIL_DRIVER != \"smtp\").';
    }
} catch (Throwable $e) {
    echo '❌ Erro ao usar PHPMailer: ' . htmlspecialchars($e->getMessage());
}
