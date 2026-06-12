<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
session_unset();
session_destroy();
require_once __DIR__ . '/helpers.php';
header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/views/login_view.php');
exit;
