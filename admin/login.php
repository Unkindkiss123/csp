<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit;
}

$erro = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_validate();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

  // Anti-bruteforce (janela móvel por IP)
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $conn->query("DELETE FROM tentativas_login WHERE TIMESTAMPDIFF(SECOND, ultima_tentativa, NOW()) > " . (int)LOGIN_JANELA_SEGUNDOS);
  $tentativas = 0;
  $res = $conn->prepare('SELECT tentativas FROM tentativas_login WHERE ip = ?');
  $res->bind_param('s', $ip);
  $res->execute();
  $res->bind_result($tentativas);
  $res->fetch();
  $res->close();
  if ($tentativas >= (int)LOGIN_MAX_TENTATIVAS) {
    $erro = 'Demasiadas tentativas. Tenta mais tarde.';
  } else {
    $stmt = $conn->prepare("SELECT id, email, password_hash, role FROM utilizadores WHERE email=? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $u = $stmt->get_result()->fetch_assoc();
      if ($u && password_verify($pass, (string)$u['password_hash'])) {
        // rehash se necessário
        if (password_needs_rehash((string)$u['password_hash'], PASSWORD_DEFAULT)) {
          $newHash = password_hash($pass, PASSWORD_DEFAULT);
          $up = $conn->prepare('UPDATE utilizadores SET password_hash = ? WHERE id = ?');
          if ($up) { $id = (int)$u['id']; $up->bind_param('si', $newHash, $id); $up->execute(); }
        }
        // reset tentativas
        $del = $conn->prepare('DELETE FROM tentativas_login WHERE ip = ?');
        if ($del) { $del->bind_param('s', $ip); $del->execute(); }

        $role = $u['role'] ?: 'viewer';
        auth_login((int)$u['id'], (string)$u['email'], (string)$role);
        session_regenerate_id(true);
        $dest = $_SESSION['redirect_after_login'] ?? (BASE_URL . 'admin/index.php');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $dest);
        exit;
      } else {
        // falha → incrementa tentativas
        if ($tentativas > 0) {
          $upd = $conn->prepare('UPDATE tentativas_login SET tentativas = tentativas + 1, ultima_tentativa = NOW() WHERE ip = ?');
          if ($upd) { $upd->bind_param('s', $ip); $upd->execute(); }
        } else {
          $ins = $conn->prepare('INSERT INTO tentativas_login (ip, tentativas, ultima_tentativa) VALUES (?, 1, NOW())');
          if ($ins) { $ins->bind_param('s', $ip); $ins->execute(); }
        }
      }
    }
  }
    $erro = 'Credenciais inválidas.';
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login · Backoffice</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/estilo.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h1 class="mb-4">Backoffice · Login</h1>
  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <?php csrf_field(); ?>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Palavra-passe</label>
      <input class="form-control" type="password" name="password" required>
    </div>
    <button class="btn btn-primary">Entrar</button>
  </form>
</body>
</html>
