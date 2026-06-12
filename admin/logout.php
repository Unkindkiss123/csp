<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

auth_logout();
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
