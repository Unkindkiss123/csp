<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/funcoes_produtos.php';

$menuItems = get_menu_items($conn, 'principal');

// Processa POST
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  validarCSRF();
  $nome = trim($_POST['nome'] ?? '');
  $descricao = trim($_POST['descricao'] ?? '');
  $preco = (float)($_POST['preco'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $referencia = trim($_POST['referencia'] ?? '');
  $categoria = trim($_POST['categoria'] ?? '');
  $marca = trim($_POST['marca'] ?? '');
  $caracteristicas = trim($_POST['caracteristicas'] ?? '');
  $especificacoes = trim($_POST['especificacoes_tecnicas'] ?? '');
  $assistencia = isset($_POST['assistencia']) ? 1 : 0;
  $requer_orcamento = isset($_POST['requer_orcamento']) ? 1 : 0; // 0 = pode ir ao carrinho, 1 = requer orçamento
  $ativo = isset($_POST['ativo']) ? 1 : 0;

  if ($nome === '') $erros[] = 'Nome é obrigatório.';
  if ($preco < 0) $erros[] = 'Preço inválido.';
  if ($stock < 0) $erros[] = 'Stock inválido.';

  // Upload seguro de imagens utilizando helper centralizado
  $imagem_principal = '';
  $imagens_adicionais = [];

  // Principal
  if (isset($_FILES['imagem_principal'])) {
    $res = processarImagem($_FILES['imagem_principal'], 'produtos');
    if ($res['sucesso']) {
      $imagem_principal = $res['ficheiro'];
    } else if (!empty($_FILES['imagem_principal']['name'])) {
      $erros[] = $res['erro'] ?: 'Imagem principal inválida (formato ou tamanho).';
    }
  }

  // Adicionais (array)
  if (!empty($_FILES['imagens_adicionais']['name']) && is_array($_FILES['imagens_adicionais']['name'])) {
    $count = count($_FILES['imagens_adicionais']['name']);
    for ($i = 0; $i < $count; $i++) {
      if (!is_uploaded_file($_FILES['imagens_adicionais']['tmp_name'][$i])) continue;
      $file = [
        'name' => $_FILES['imagens_adicionais']['name'][$i],
        'type' => $_FILES['imagens_adicionais']['type'][$i] ?? '',
        'tmp_name' => $_FILES['imagens_adicionais']['tmp_name'][$i],
        'error' => $_FILES['imagens_adicionais']['error'][$i],
        'size' => $_FILES['imagens_adicionais']['size'][$i],
      ];
      $res = processarImagem($file, 'produtos');
      if ($res['sucesso']) $imagens_adicionais[] = $res['ficheiro'];
    }
  }

  if (!$erros) {
    $novoId = criarProduto($conn, [
      'nome' => $nome,
      'descricao' => $descricao,
      'preco' => $preco,
      'stock' => $stock,
      'referencia' => $referencia,
      'categoria' => $categoria,
      'marca' => $marca,
      'caracteristicas' => $caracteristicas,
      'especificacoes_tecnicas' => $especificacoes,
      'assistencia' => $assistencia,
      'requer_orcamento' => $requer_orcamento,
      'imagem_principal' => $imagem_principal,
      'imagens_adicionais' => $imagens_adicionais,
      'ativo' => $ativo,
    ]);
    if ($novoId) {
      header('Location: ./produto_editar.php?id=' . (int)$novoId);
      exit;
    } else {
      $erros[] = 'Falha ao criar produto.';
    }
  }
}

include __DIR__ . '/../../componentes/header.php';
?>

<div class="container py-4">
  <h1 class="h5 mb-3">Novo Produto</h1>

  <?php if (!empty($erros)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($erros as $e): ?>
          <li><?= h($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card border-0 shadow-sm">
    <div class="card-body">
      <?php gerarCSRF(); ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nome *</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Preço *</label>
          <div class="input-group">
            <input type="number" name="preco" step="0.01" min="0" class="form-control" required>
            <span class="input-group-text">€</span>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Stock *</label>
          <input type="number" name="stock" step="1" min="0" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Referência</label>
          <input type="text" name="referencia" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Categoria</label>
          <input type="text" name="categoria" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca</label>
          <input type="text" name="marca" class="form-control">
        </div>

        <div class="col-12">
          <label class="form-label">Descrição</label>
          <textarea name="descricao" class="form-control" rows="4"></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Características (texto livre)</label>
          <textarea name="caracteristicas" class="form-control" rows="3"></textarea>
        </div>

        <div class="col-12">
          <label class="form-label">Especificações Técnicas</label>
          <textarea name="especificacoes_tecnicas" class="form-control" rows="4" placeholder="Detalhe técnico, séries, materiais, potência, medidas..."></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">Imagem principal</label>
          <input type="file" name="imagem_principal" accept="image/*" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Imagens adicionais</label>
          <input type="file" name="imagens_adicionais[]" accept="image/*" multiple class="form-control">
        </div>

        <div class="col-12">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="1" id="assistencia" name="assistencia">
            <label class="form-check-label" for="assistencia">Assistência e Instalação disponíveis</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="1" id="requer_orcamento" name="requer_orcamento" checked>
            <label class="form-check-label" for="requer_orcamento">Este produto requer pedido de orçamento</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="ativo" name="ativo" checked>
            <label class="form-check-label" for="ativo">Ativar produto</label>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <a href="./produtos.php" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
