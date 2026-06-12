<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
// Tentar carregar Composer autoload (PHPMailer opcional)
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php'] as $autoload) {
  if (file_exists($autoload)) { require_once $autoload; break; }
}

/**
 * Envia um email simples em HTML usando mail() quando disponível.
 * Em DEV (ou quando não configurado), devolve sucesso e deixa o envio real a cargo do preview do fluxo.
 * @return array [sent=>bool, method=>string, error=>string|null]
 */
function send_mail(string $to, string $subject, string $html, string $text = ''): array {
  $fromEmail = defined('MAIL_FROM_EMAIL') && MAIL_FROM_EMAIL !== '' ? MAIL_FROM_EMAIL : 'no-reply@localhost';
  $fromName  = defined('MAIL_FROM_NAME') && MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : 'Constroi Spa & Pool';
  $driver    = defined('MAIL_DRIVER') ? MAIL_DRIVER : (IS_LOCAL ? 'none' : 'mail');

  if ($driver === 'none') {
    return ['sent' => true, 'method' => 'none', 'error' => null];
  }

  // mail(): requer configuração no php.ini (Windows usa SMTP configurado lá)
  if ($driver === 'mail') {
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . encode_header($fromName) . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $ok = @mail($to, encode_header($subject), $html, implode("\r\n", $headers));
    if (!$ok) mailer_log('mail() failed for ' . $to . ' subject=' . $subject);
    return ['sent' => (bool)$ok, 'method' => 'mail', 'error' => $ok ? null : 'mail() returned false'];
  }

  // SMTP placeholder (recomendado integrar PHPMailer para produção confiável)
  if ($driver === 'smtp') {
    // Preferir PHPMailer se disponível
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
      try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $mail->SMTPAuth = defined('SMTP_AUTH') ? (bool)SMTP_AUTH : true;
        $mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName ?: $fromEmail);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text !== '' ? $text : strip_tags($html);
        $mail->send();
        return ['sent' => true, 'method' => 'phpmailer', 'error' => null];
      } catch (Throwable $e) {
        mailer_log('PHPMailer error: ' . $e->getMessage());
        // Fallback para SMTP minimal
      }
    }
    $res = smtp_send($to, $subject, $html, $text, $fromEmail, $fromName);
    return $res;
  }

  return ['sent' => false, 'method' => $driver, 'error' => 'Unsupported MAIL_DRIVER=' . $driver];
}

function encode_header(string $str): string {
  // RFC 2047 encoding for non-ASCII
  if (preg_match('/[\x80-\xFF]/', $str)) {
    return '=?UTF-8?B?' . base64_encode($str) . '?=';
  }
  return $str;
}

/**
 * Minimal SMTP client (LOGIN auth) with optional TLS/SSL.
 * Designed for simple transactional emails without external libs. For advanced needs, use PHPMailer.
 */
function smtp_send(string $to, string $subject, string $html, string $text, string $fromEmail, string $fromName): array {
  $host = defined('SMTP_HOST') ? SMTP_HOST : '';
  $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
  $user = defined('SMTP_USER') ? SMTP_USER : '';
  $pass = defined('SMTP_PASS') ? SMTP_PASS : '';
  $secure = defined('SMTP_SECURE') ? strtolower((string)SMTP_SECURE) : 'tls'; // tls|ssl|''
  $auth = defined('SMTP_AUTH') ? (bool)SMTP_AUTH : true;
  if ($host === '' || $port <= 0) {
    return ['sent' => false, 'method' => 'smtp', 'error' => 'SMTP_HOST/SMTP_PORT em falta'];
  }

  $transport = ($secure === 'ssl') ? 'ssl' : 'tcp';
  $remote = $transport . '://' . $host . ':' . $port;
  $context = stream_context_create([
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true,
    ],
  ]);
  $fp = @stream_socket_client($remote, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
  if (!$fp) {
    return ['sent' => false, 'method' => 'smtp', 'error' => 'Conexão falhou: ' . $errno . ' ' . $errstr];
  }
  stream_set_timeout($fp, 10);

  $read = function() use ($fp) {
    $data = '';
    while ($line = fgets($fp, 515)) {
      $data .= $line;
      if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $data;
  };
  $expect = function(string $code) use ($read) {
    $resp = $read();
    if (strncmp($resp, $code, 3) !== 0) {
      throw new Exception('SMTP erro: esperado ' . $code . ', obtido: ' . trim($resp));
    }
    return $resp;
  };
  $send = function(string $cmd) use ($fp, $read) {
    fwrite($fp, $cmd . "\r\n");
    return $read();
  };

  try {
    $expect('220');
    $heloHost = 'localhost';
    $send('EHLO ' . $heloHost);

    if ($secure === 'tls') {
      $resp = $send('STARTTLS');
      if (strncmp($resp, '220', 3) !== 0) {
        throw new Exception('STARTTLS falhou: ' . trim($resp));
      }
      if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        throw new Exception('Falha ao negociar TLS');
      }
      // EHLO novamente após TLS
      $send('EHLO ' . $heloHost);
    }

    if ($auth) {
      $send('AUTH LOGIN');
      $expect('334');
      $send(base64_encode($user));
      $expect('334');
      $send(base64_encode($pass));
      $expect('235');
    }

    $send('MAIL FROM: <' . $fromEmail . '>');
    $expect('250');
    $send('RCPT TO: <' . $to . '>');
    $expect('250');
    $send('DATA');
    $expect('354');

    $boundary = 'bnd_' . bin2hex(random_bytes(6));
    $headers = [];
    $headers[] = 'From: ' . encode_header($fromName) . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $to . '>';
    $headers[] = 'Subject: ' . encode_header($subject);
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= ($text !== '' ? $text : strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $html))) . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $html . "\r\n";
    $body .= "--$boundary--\r\n";

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . ".\r\n";
    fwrite($fp, $data);
    $expect('250');
    $send('QUIT');
    fclose($fp);
    return ['sent' => true, 'method' => 'smtp', 'error' => null];
  } catch (Throwable $e) {
    @fclose($fp);
    mailer_log('SMTP error: ' . $e->getMessage());
    return ['sent' => false, 'method' => 'smtp', 'error' => $e->getMessage()];
  }
}

function mailer_log(string $message): void {
  try {
    $root = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
    $logDir = $root . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
    @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'mailer.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND);
  } catch (Throwable $e) { /* ignore logging errors */ }
}
