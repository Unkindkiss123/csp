<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/ServicosModel.php';

$model = new ServicosModel($conn);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$acao = $_REQUEST['acao'] ?? '';

// CSRF mandatory on state-changing actions
if (in_array($acao, ['criar','atualizar','apagar','publicar','despublicar','estado'], true)) {
  if ($method !== 'POST') { http_response_code(405); exit('Método não permitido'); }
  csrf_validate();
}

// Role enforcement: destructive/publish actions require admin
if (in_array($acao, ['apagar','publicar','despublicar'], true)) {
  require_login('admin');
}

function back_to_list(string $msgType, string $msg) {
  set_alert($msgType, $msg);
  header('Location: ' . BASE_URL . 'admin/servicos_listar.php');
  exit;
}

if ($acao === 'criar') {
  // Upload imagem (opcional no rascunho, mas campo existe)
  $img = $_POST['imagem_principal_existente'] ?? '';
  if (!empty($_FILES['imagem_principal']['name'])) {
    $r = salvar_imagem_servico($_FILES['imagem_principal']);
    if ($r['ok']) $img = $r['path']; else back_to_list('danger', $r['erro']);
  }
  $data = [
    'titulo' => trim((string)($_POST['titulo'] ?? '')),
    'resumo_curto' => trim((string)($_POST['resumo_curto'] ?? '')),
    'descricao_longa' => trim((string)($_POST['descricao_longa'] ?? '')),
    'imagem_principal' => $img,
    'tipo' => trim((string)($_POST['tipo'] ?? '')),
    'preco_base' => ($_POST['preco_base'] ?? '') !== '' ? (string)$_POST['preco_base'] : null,
    'seo_title' => trim((string)($_POST['seo_title'] ?? '')) ?: null,
    'seo_description' => trim((string)($_POST['seo_description'] ?? '')) ?: null,
    'estado_visibilidade' => $_POST['estado_visibilidade'] ?? 'inativo',
    'status_publicacao' => 'rascunho',
    'ordem' => (int)($_POST['ordem'] ?? 0),
  ];
  $id = $model->criar($data);
  if ($id > 0) {
    set_alert('success', 'Serviço criado.');
    header('Location: ' . url('/admin/servico_form.php?id=' . $id));
    exit;
  }
  back_to_list('danger', 'Não foi possível criar o serviço.');
}

if ($acao === 'atualizar') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id < 1) back_to_list('danger', 'ID inválido.');
  $curr = $model->obter($id);
  if (!$curr) back_to_list('danger', 'Registo não encontrado.');
  $img = $_POST['imagem_principal_existente'] ?? ($curr['imagem_principal'] ?? '');
  if (!empty($_FILES['imagem_principal']['name'])) {
    $r = salvar_imagem_servico($_FILES['imagem_principal']);
    if ($r['ok']) $img = $r['path']; else back_to_list('danger', $r['erro']);
  }
  $data = [
    'titulo' => trim((string)($_POST['titulo'] ?? $curr['titulo'])),
    'resumo_curto' => trim((string)($_POST['resumo_curto'] ?? $curr['resumo_curto'])),
    'descricao_longa' => trim((string)($_POST['descricao_longa'] ?? $curr['descricao_longa'])),
    'imagem_principal' => $img,
    'tipo' => trim((string)($_POST['tipo'] ?? $curr['tipo'])),
    'preco_base' => ($_POST['preco_base'] ?? '') !== '' ? (string)$_POST['preco_base'] : null,
    'seo_title' => trim((string)($_POST['seo_title'] ?? '')) ?: null,
    'seo_description' => trim((string)($_POST['seo_description'] ?? '')) ?: null,
    'estado_visibilidade' => $_POST['estado_visibilidade'] ?? $curr['estado_visibilidade'],
    'status_publicacao' => $curr['status_publicacao'],
    'ordem' => (int)($_POST['ordem'] ?? $curr['ordem']),
  ];
  $ok = $model->atualizar($id, $data);
  back_to_list($ok ? 'success' : 'danger', $ok ? 'Atualizado com sucesso.' : 'Falha ao atualizar.');
}

if ($acao === 'apagar') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id < 1) back_to_list('danger', 'ID inválido.');
  $ok = $model->apagar($id);
  if (!$ok) back_to_list('danger', 'Não é possível apagar: serviço usado em orçamentos.');
  back_to_list('success', 'Serviço apagado.');
}

if ($acao === 'publicar') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id < 1) back_to_list('danger', 'ID inválido.');
  $ok = $model->publicar($id);
  back_to_list($ok ? 'success' : 'danger', $ok ? 'Publicado.' : 'Preencha os campos obrigatórios antes de publicar.');
}

if ($acao === 'despublicar') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id < 1) back_to_list('danger', 'ID inválido.');
  $ok = $model->despublicar($id);
  back_to_list($ok ? 'success' : 'danger', $ok ? 'Despublicado.' : 'Falha ao despublicar.');
}

if ($acao === 'estado') {
  $id = (int)($_POST['id'] ?? 0);
  $estado = (string)($_POST['estado_visibilidade'] ?? 'inativo');
  if ($id < 1) back_to_list('danger', 'ID inválido.');
  $ok = $model->mudarEstadoVisibilidade($id, $estado);
  back_to_list($ok ? 'success' : 'danger', $ok ? 'Estado de visibilidade atualizado.' : 'Estado inválido.');
}

// Fallback
header('Location: ' . BASE_URL . 'admin/servicos_listar.php');
exit;

// ---------- Utils ----------
function salvar_imagem_servico(array $file): array {
  $max = 5 * 1024 * 1024; // 5MB
  $allowedExt = ['jpg','jpeg','png','webp'];
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return ['ok'=>false,'erro'=>'Falha no upload da imagem.'];
  if (($file['size'] ?? 0) > $max) return ['ok'=>false,'erro'=>'Imagem demasiado grande (máx. 5MB).'];
  $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExt, true)) return ['ok'=>false,'erro'=>'Formato inválido. Use JPG, PNG ou WEBP.'];
  $root = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
  $dir = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'imagens' . DIRECTORY_SEPARATOR . 'servicos' . DIRECTORY_SEPARATOR;
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $name = 'svc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . $name;
  if (!@move_uploaded_file($file['tmp_name'], $dest)) return ['ok'=>false,'erro'=>'Não foi possível guardar a imagem.'];
  $sitePath = '/public/imagens/servicos/' . $name;
  return ['ok'=>true,'path'=>$sitePath];
}
