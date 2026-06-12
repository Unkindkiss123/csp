<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/** Returns true if keys are present to enable reCAPTCHA rendering/verification */
function recaptcha_is_configured(): bool {
    return (defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY !== '') && (defined('RECAPTCHA_SECRET_KEY') && RECAPTCHA_SECRET_KEY !== '');
}

/**
 * Verify Google reCAPTCHA v2 response server-side.
 * @param string $response token from g-recaptcha-response
 * @param string $remoteIp optional client IP
 * @return array [success=>bool, score?=>float, errorCodes=>array]
 */
function verify_recaptcha(string $response, string $remoteIp = ''): array {
    $result = ['success' => false, 'errorCodes' => []];
    if (!recaptcha_is_configured()) {
        // When not configured, treat as success to avoid blocking local dev
        $result['success'] = IS_LOCAL; // Allow on localhost, require in production
        if (!$result['success']) $result['errorCodes'] = ['recaptcha-not-configured'];
        return $result;
    }
    $secret = RECAPTCHA_SECRET_KEY;
    if ($response === '') {
        $result['errorCodes'] = ['missing-input-response'];
        return $result;
    }
    // Use file_get_contents to avoid adding dependencies; allow_url_fopen must be enabled
    $data = http_build_query([
        'secret' => $secret,
        'response' => $response,
        'remoteip' => $remoteIp ?: cliente_ip(),
    ]);
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'timeout' => 5,
        ],
    ];
    $context = stream_context_create($opts);
    $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if ($resp === false) {
        $result['errorCodes'] = ['recaptcha-unreachable'];
        return $result;
    }
    $json = json_decode($resp, true);
    if (is_array($json) && !empty($json['success'])) {
        $result['success'] = true;
    } else {
        $result['errorCodes'] = isset($json['error-codes']) && is_array($json['error-codes']) ? $json['error-codes'] : ['verification-failed'];
    }
    return $result;
}
