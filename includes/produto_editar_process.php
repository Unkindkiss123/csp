<?php
require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/helpers.php';

validarCSRF();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  set_alert('danger', 'ID de produto em falta.');
  site_redirect('/admin/produtos.php');
}

$nome = trim((string)($_POST['nome'] ?? ''));
$descricao = trim((string)($_POST['descricao'] ?? ''));
$preco = (float)($_POST['preco'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$referencia = trim((string)($_POST['referencia'] ?? ''));
$categoria = trim((string)($_POST['categoria'] ?? ''));
$marca = trim((string)($_POST['marca'] ?? ''));
$caracteristicas = trim((string)($_POST['caracteristicas'] ?? ''));
$especificacoes = trim((string)($_POST['especificacoes_tecnicas'] ?? ''));
$assistencia = isset($_POST['assistencia']) ? 1 : 0;
$requer_orcamento = isset($_POST['requer_orcamento']) ? 1 : 0;
$ativo = isset($_POST['ativo']) ? 1 : 0;

if ($nome === '' || $preco < 0 || $stock < 0) {
  set_alert('danger', 'Verifique os campos obrigatórios e valores.');
  site_redirect('/admin/produto_editar.php?id=' . $id);
}

$stmt = $conn->prepare('UPDATE produtos SET nome=?, descricao=?, preco=?, stock=?, referencia=?, categoria=?, marca=?, caracteristicas=?, especificacoes_tecnicas=?, assistencia=?, requer_orcamento=?, ativo=? WHERE id=?');
$stmt->bind_param('ssdisssssiiii', $nome, $descricao, $preco, $stock, $referencia, $categoria, $marca, $caracteristicas, $especificacoes, $assistencia, $requer_orcamento, $ativo, $id);
if ($stmt->execute()) {
  $stmt->close();
  set_alert('success', 'Produto atualizado com sucesso.');
  // Redirect para gestor com flag de sucesso
  $base = defined('BASE_URL') ? BASE_URL : '';
  header('Location: ' . rtrim($base, '/') . '/admin/produtos.php?updated=1');
  exit;
}
$err = $conn->error;
$stmt->close();
set_alert('danger', 'Falha ao atualizar: ' . $err);
site_redirect('/admin/produto_editar.php?id=' . $id);
