<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../../componentes/header.php';
?>

<div class="container py-4">
  <h1 class="h5 mb-3">Gestão de Serviços</h1>
  <div class="alert alert-info">Administração de serviços e itens do menu (dropdown) será implementada aqui.</div>
  <a href="./index.php" class="btn btn-secondary btn-sm">Voltar ao Backoffice</a>
</div>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
