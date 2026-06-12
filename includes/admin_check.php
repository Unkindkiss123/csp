<?php
// Admin-only access gate
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Not logged in → redirect to login
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/login_view.php?erro=acesso_negado');
    exit;
}

// Logged in but not admin → redirect to login with error
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'views/login_view.php?erro=acesso_negado');
    exit;
}
