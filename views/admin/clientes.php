<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Clientes';
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_dashboard.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Clientes</li>
      </ol>
    </nav>
    <div class="bg-white shadow-sm rounded-3 p-4 p-md-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 text-primary mb-0">Clientes</h1>
        <a href="#" class="btn btn-primary btn-sm" disabled>+ Adicionar cliente</a>
      </div>
      <p class="text-muted mb-0">Placeholder visual. Aqui será possível gerir a lista de clientes.</p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
