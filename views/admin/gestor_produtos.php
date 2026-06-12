<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Produtos';
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/index.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Produtos</li>
      </ol>
    </nav>
    <div class="row g-3">
      <aside class="col-lg-3">
        <div class="list-group sticky-top" style="top: calc(var(--site-header-h) + 1rem);">
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/index.php">Painel</a>
          <a class="list-group-item list-group-item-action active" href="<?= BASE_URL ?>admin/produtos.php">Produtos</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/encomendas.php">Encomendas</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/utilizadores.php">Utilizadores</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/categorias.php">Categorias</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/marcas.php">Marcas</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/contactos.php">Contactos</a>
        </div>
      </aside>
      <section class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h1 class="h4 text-primary mb-0">Gestor de produtos</h1>
          <a href="<?= BASE_URL ?>admin/produto_novo.php" class="btn btn-primary">Novo produto</a>
        </div>
        <div class="table-responsive bg-white rounded-3 shadow-sm">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Estado</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Placeholder de linhas; substituir por loop da BD -->
              <?php for ($i=1; $i<=5; $i++): ?>
              <tr>
                <td><?= $i ?></td>
                <td>Produto <?= $i ?></td>
                <td>€ 0,00</td>
                <td><span class="badge bg-success">Ativo</span></td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>admin/produto_editar.php?id=<?= $i ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                  <a href="<?= BASE_URL ?>admin/produto_remover.php?id=<?= $i ?>" class="btn btn-sm btn-outline-danger">Apagar</a>
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
