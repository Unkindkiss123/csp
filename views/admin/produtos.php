<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/funcoes_produtos.php';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../../componentes/header.php';

// Load a few products for the list (extend with filters/search later)
$produtos = getProdutos($conn, ['limit' => 20, 'offset' => 0, 'order' => 'recente']);
?>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h5 mb-0">Gestão de Produtos</h1>
    <a href="./produto_novo.php" class="btn btn-success btn-sm">+ Novo Produto</a>
  </div>

  <?php if (!empty($_SESSION['alert'])): $a = $_SESSION['alert']; unset($_SESSION['alert']); ?>
    <div class="alert alert-<?= h($a['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
      <?= h((string)($a['msg'] ?? '')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Categoria</th>
          <th>Preço</th>
          <th>Estado</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($produtos as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= h($p['nome']) ?></td>
          <td><?= h($p['categoria']) ?></td>
          <td><?= number_format((float)$p['preco'], 2, ',', ' ') ?> €</td>
          <td>
            <?php if ((int)$p['ativo'] === 1): ?>
              <span class="badge bg-success">Ativo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inativo</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <a class="btn btn-outline-primary btn-sm" href="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/admin/produto_editar.php?id=<?= (int)$p['id'] ?>">Editar</a>
            <form action="<?= h(url_for('/views/admin/produto_remover_process.php')) ?>" method="post" class="d-inline" onsubmit="return confirm('Remover este produto?');">
              <?php gerarCSRF(); ?>
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="btn btn-outline-danger btn-sm">Remover</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
