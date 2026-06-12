<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Categorias';
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_dashboard.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Categorias</li>
      </ol>
    </nav>
    <div class="row g-3">
      <aside class="col-lg-3">
        <div class="list-group sticky-top" style="top: calc(var(--site-header-h) + 1rem);">
          <a class="list-group-item list-group-item-action" href="./admin_dashboard.php">Painel</a>
          <a class="list-group-item list-group-item-action" href="./gestor_produtos.php">Produtos</a>
          <a class="list-group-item list-group-item-action" href="./gestor_encomendas.php">Encomendas</a>
          <a class="list-group-item list-group-item-action" href="./gestor_utilizadores.php">Utilizadores</a>
          <a class="list-group-item list-group-item-action active" href="./gestor_categorias.php">Categorias</a>
          <a class="list-group-item list-group-item-action" href="./gestor_marcas.php">Marcas</a>
          <a class="list-group-item list-group-item-action" href="./gestor_contactos.php">Contactos</a>
        </div>
      </aside>
      <section class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h1 class="h4 text-primary mb-0">Categorias</h1>
          <button class="btn btn-primary" disabled>Nova categoria</button>
        </div>
        <div class="table-responsive bg-white rounded-3 shadow-sm">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i=1; $i<=5; $i++): ?>
              <tr>
                <td><?= $i ?></td>
                <td>Categoria <?= $i ?></td>
                <td class="text-muted">—</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary" disabled>Editar</button>
                  <button class="btn btn-sm btn-outline-danger" disabled>Apagar</button>
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
