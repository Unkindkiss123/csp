<?php
if (!defined('APP_BOOTSTRAPPED')) { http_response_code(403); exit; }

function auth_user() {
    return $_SESSION['auth'] ?? null;
}

function auth_login($userId, $email, $role) {
    $_SESSION['auth'] = [
        'id'    => (int)$userId,
        'email' => $email,
        'role'  => $role
    ];
}

function auth_logout() {
    $_SESSION['auth'] = null;
    unset($_SESSION['auth']);
    session_regenerate_id(true);
}

function is_logged_in() {
    return !empty($_SESSION['auth']['id']);
}

function has_role($role) {
    if (!is_logged_in()) return false;
    $map = ['viewer'=>1,'editor'=>2,'admin'=>3];
    $u   = $_SESSION['auth']['role'] ?? 'viewer';
    return ($map[$u] ?? 0) >= ($map[$role] ?? 0);
}

function require_login($minRole = 'viewer') {
    if (!is_logged_in() || !has_role($minRole)) {
        // guardar destino e redirecionar para login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? (BASE_URL . 'admin/admin_dashboard.php');
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}
