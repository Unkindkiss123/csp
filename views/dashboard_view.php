<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../componentes/header.php';
?>

<section class="py-4">
  <div class="container">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h1 class="h4 mb-3">Olá, <?= h($_SESSION['nome'] ?? $_SESSION['usuario'] ?? 'Utilizador'); ?> 👋</h1>
        <p class="text-muted">Bem-vindo ao painel da Constrói Spa &amp; Pool.</p>
        <div class="mt-3">
          <a href="../logout.php" class="btn btn-outline-danger btn-sm">Terminar sessão</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
