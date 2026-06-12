<?php
require_once __DIR__ . '/config.php';

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if (defined('IS_LOCAL') && IS_LOCAL) {
        die('Erro na ligação à base de dados: ' . $conn->connect_error);
    }
    error_log('DB connect error: ' . $conn->connect_error);
    http_response_code(500);
    exit;
}

$conn->set_charset('utf8mb4');
