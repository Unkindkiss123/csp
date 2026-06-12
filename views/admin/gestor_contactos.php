<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Contactos';
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_dashboard.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contactos</li>
      </ol>
    </nav>
    <div class="row g-3">
      <aside class="col-lg-3">
        <div class="list-group sticky-top" style="top: calc(var(--site-header-h) + 1rem);">
          <a class="list-group-item list-group-item-action" href="./admin_dashboard.php">Painel</a>
          <a class="list-group-item list-group-item-action" href="./gestor_produtos.php">Produtos</a>
          <a class="list-group-item list-group-item-action" href="./gestor_encomendas.php">Encomendas</a>
          <a class="list-group-item list-group-item-action" href="./gestor_utilizadores.php">Utilizadores</a>
          <a class="list-group-item list-group-item-action" href="./gestor_categorias.php">Categorias</a>
          <a class="list-group-item list-group-item-action" href="./gestor_marcas.php">Marcas</a>
          <a class="list-group-item list-group-item-action active" href="./gestor_contactos.php">Contactos</a>
        </div>
      </aside>
      <section class="col-lg-9">
        <h1 class="h4 text-primary mb-3">Contactos recebidos</h1>
        <div class="table-responsive bg-white rounded-3 shadow-sm">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Mensagem</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i=1; $i<=5; $i++): ?>
              <tr>
                <td><?= $i ?></td>
                <td>Nome <?= $i ?></td>
                <td>email<?= $i ?>@exemplo.pt</td>
                <td class="text-truncate" style="max-width: 260px">Mensagem de exemplo…</td>
                <td>2025-10-01</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary" disabled>Ver</button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>Marcar lido</button>
                </td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
