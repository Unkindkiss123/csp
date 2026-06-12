<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../../componentes/header.php';
?>

<section class="py-4">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 mb-0">Backoffice</h1>
      <span class="badge bg-warning text-dark">Admin</span>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h2 class="h5">Produtos</h2>
            <p class="text-muted small">Gerir catálogo, preços e imagens.</p>
            <div class="mt-auto d-grid"><a href="<?= BASE_URL ?>admin/produtos.php" class="btn btn-primary btn-sm">Abrir</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h2 class="h5">Orçamentos</h2>
            <p class="text-muted small">Pedidos e propostas (placeholder).</p>
            <div class="mt-auto d-grid"><a href="<?= BASE_URL ?>admin/orcamentos.php" class="btn btn-primary btn-sm">Abrir</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h2 class="h5">Clientes</h2>
            <p class="text-muted small">Gestão de clientes (placeholder).</p>
            <div class="mt-auto d-grid"><a href="<?= BASE_URL ?>admin/clientes.php" class="btn btn-primary btn-sm">Abrir</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h2 class="h5">Configurações</h2>
            <p class="text-muted small">Tema e branding (placeholder).</p>
            <div class="mt-auto d-grid"><a href="<?= BASE_URL ?>admin/config.php" class="btn btn-primary btn-sm">Abrir</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
