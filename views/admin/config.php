<?php
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/helpers.php';
$page_title = 'Backoffice · Configurações';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_dashboard.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Configurações</li>
      </ol>
    </nav>
    <div class="bg-white shadow-sm rounded-3 p-4 p-md-5">
      <h1 class="h4 text-primary mb-3">Configurações</h1>
      <p class="text-muted">Placeholder visual para preferências gerais.</p>
      <hr>
      <form>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="toggleDark" disabled>
          <label class="form-check-label" for="toggleDark">Modo escuro (placeholder)</label>
        </div>
        <div class="mb-3">
          <label class="form-label">Logótipo (placeholder)</label>
          <input type="file" class="form-control" disabled>
        </div>
        <button type="button" class="btn btn-primary" disabled>Guardar</button>
      </form>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
