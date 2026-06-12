<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/funcoes_produtos.php';
$menuItems = get_menu_items($conn, 'principal');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto = $id > 0 ? getProdutoById($conn, $id) : null;

include __DIR__ . '/../../componentes/header.php';
?>

<div class="container py-4">
  <h1 class="h5 mb-3">Editar Produto</h1>
  <?php if (!$produto): ?>
    <div class="alert alert-warning">Produto não encontrado.</div>
    <a href="./produtos.php" class="btn btn-secondary btn-sm">Voltar</a>
  <?php else: ?>
  <form method="post" action="<?= h(url_for('/views/admin/produto_editar_process.php')) ?>" class="card border-0 shadow-sm">
      <div class="card-body">
        <?php gerarCSRF(); ?>
        <input type="hidden" name="id" value="<?= (int)$produto['id'] ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nome *</label>
            <input type="text" name="nome" class="form-control" value="<?= h($produto['nome']) ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Preço *</label>
            <div class="input-group">
              <input type="number" name="preco" step="0.01" min="0" class="form-control" value="<?= h((string)$produto['preco']) ?>" required>
              <span class="input-group-text">€</span>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Stock *</label>
            <input type="number" name="stock" step="1" min="0" class="form-control" value="<?= h((string)$produto['stock']) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Referência</label>
            <input type="text" name="referencia" class="form-control" value="<?= h((string)($produto['referencia'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoria</label>
            <input type="text" name="categoria" class="form-control" value="<?= h((string)($produto['categoria'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Marca</label>
            <input type="text" name="marca" class="form-control" value="<?= h((string)($produto['marca'] ?? '')) ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"><?= h((string)($produto['descricao'] ?? '')) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Características</label>
            <textarea name="caracteristicas" class="form-control" rows="3"><?= h((string)($produto['caracteristicas'] ?? '')) ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Especificações Técnicas</label>
            <textarea name="especificacoes_tecnicas" class="form-control" rows="4" placeholder="Detalhe técnico, séries, materiais, potência, medidas..."><?= h((string)($produto['especificacoes_tecnicas'] ?? '')) ?></textarea>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="assistencia" name="assistencia" <?= !empty($produto['assistencia']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="assistencia">Assistência e Instalação disponíveis</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="requer_orcamento" name="requer_orcamento" <?= !empty($produto['requer_orcamento']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="requer_orcamento">Este produto requer pedido de orçamento</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="ativo" name="ativo" <?= !empty($produto['ativo']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="ativo">Ativar produto</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/admin/produtos.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar alterações</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
