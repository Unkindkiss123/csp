<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
  header('Location: ../views/login_view.php?erro=acesso_negado');
  exit;
}
