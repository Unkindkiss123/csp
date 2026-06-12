<?php
require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/helpers.php';

validarCSRF();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  set_alert('danger', 'ID inválido.');
  site_redirect('/views/admin/produtos.php');
}

// Buscar produto para apagar imagens
$stmt = $conn->prepare('SELECT imagem_principal, imagens_adicionais FROM produtos WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$produto = $res->fetch_assoc();
$stmt->close();

if ($produto) {
  $paths = [];
  if (!empty($produto['imagem_principal'])) $paths[] = (string)$produto['imagem_principal'];
  if (!empty($produto['imagens_adicionais'])) {
    $arr = json_decode((string)$produto['imagens_adicionais'], true);
    if (is_array($arr)) {
      foreach ($arr as $p) { if (is_string($p) && $p !== '') $paths[] = $p; }
    }
  }
  foreach ($paths as $p) {
    // Converter site path para FS
    $fs = site_to_fs($p);
    if (is_file($fs)) { @unlink($fs); }
    // Apagar versão .webp, se existir
    $info = pathinfo($fs);
    $webp = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.webp';
    if (is_file($webp)) { @unlink($webp); }
  }
}

// Remover produto
$del = $conn->prepare('DELETE FROM produtos WHERE id=?');
$del->bind_param('i', $id);
if ($del->execute()) {
  $del->close();
  set_alert('success', 'Produto removido.');
  site_redirect('/views/admin/produtos.php');
}
$err = $conn->error;
$del->close();
set_alert('danger', 'Falha ao remover: ' . $err);
site_redirect('/views/admin/produtos.php');
