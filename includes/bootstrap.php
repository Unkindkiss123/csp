<?php
// Caminhos e sessão segura
require_once __DIR__ . '/../config.php';

// BASE_PATH (filesystem) para includes absolutos
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
}

// Sessão com cookies seguros
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Cabeçalhos mínimos de segurança
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// DB connect (ajustar se já existir)
require_once __DIR__ . '/db_connect.php';

// Flag para evitar acesso direto a certos ficheiros
if (!defined('APP_BOOTSTRAPPED')) {
    define('APP_BOOTSTRAPPED', true);
}
