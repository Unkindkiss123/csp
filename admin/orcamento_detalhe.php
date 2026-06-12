<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../includes/helpers.php';

$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Detalhe do Orçamento';

// Preserve filtros para voltar
$preserveKeys = ['estado','servico','page'];
$q = [];
foreach ($preserveKeys as $k) { if (isset($_GET[$k]) && $_GET[$k] !== '') $q[$k] = $_GET[$k]; }
$backQS = $q ? ('?' . http_build_query($q)) : '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
  include __DIR__ . '/../componentes/header.php';
  echo '<div class="container my-5"><div class="alert alert-danger">ID inválido.</div><a class="btn btn-outline-secondary" href="' . h(url('/admin/orcamentos.php' . $backQS)) . '">Voltar</a></div>';
  include __DIR__ . '/../componentes/footer.php';
  exit;
}

// Handle estado update (POST)
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['estado'])) {
  csrf_validate();
  $novo = (string)($_POST['estado'] ?? '');
  $valid = ['novo','triagem','aguarda_cliente','orcamento_enviado','aceite','rejeitado','arquivado'];
  if (in_array($novo, $valid, true)) {
    $stmt = $conn->prepare('UPDATE leads SET estado=? WHERE id=? LIMIT 1');
    $stmt->bind_param('si', $novo, $id);
    $stmt->execute();
    $stmt->close();
  }
  $qs = $q ? ('&' . http_build_query($q)) : '';
  header('Location: ' . BASE_URL . 'admin/orcamento_detalhe.php?id=' . $id . $qs . '&updated=1');
  exit;
}

// Fetch lead
$stmt = $conn->prepare("SELECT * FROM leads WHERE id=? AND source='orcamento' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$lead = $res->fetch_assoc();
$stmt->close();

include __DIR__ . '/../componentes/header.php';

if (!$lead) {
  echo '<div class="container my-5"><div class="alert alert-warning">Registo não encontrado.</div><a class="btn btn-outline-secondary" href="' . h(url('/admin/orcamentos.php' . $backQS)) . '">Voltar</a></div>';
  include __DIR__ . '/../componentes/footer.php';
  exit;
}

// Build mailto
$mailto = '';
if (!empty($lead['email'])) {
  $subject = rawurlencode('[CSP] Resposta ao teu pedido (#' . $lead['id'] . ')');
  $body = "Olá {$lead['nome']},\n\n" .
          "Obrigado pelo teu pedido de orçamento. Segue um breve resumo:\n\n" .
          "Serviço: {$lead['servico']}\n" .
          "Localidade: {$lead['localidade']}\n" .
          "Prazo: {$lead['prazo']}\n" .
          "Orçamento estimado: {$lead['orcamento_estimado']}\n" .
          "Mensagem original:\n{$lead['mensagem']}\n\n\n" .
          "Cumprimentos,\nConstrói Spa & Pool";
  $mailto = 'mailto:' . rawurlencode($lead['email']) . '?subject=' . $subject . '&body=' . rawurlencode($body);
}

// Nota: helper h() já existe em includes/helpers.php; não redefinir aqui
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= h(url('/')) ?>">Início</a></li>
  <li class="breadcrumb-item"><a href="<?= h(url('/admin/index.php')) ?>">Backoffice</a></li>
        <li class="breadcrumb-item"><a href="<?= h(url('/admin/orcamentos.php' . $backQS)) ?>">Orçamentos</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= (int)$lead['id'] ?></li>
      </ol>
    </nav>

    <?php if(isset($_GET['updated'])): ?>
      <div class="alert alert-success">Estado atualizado com sucesso.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h4 mb-0">#<?= (int)$lead['id'] ?> — <?= h($lead['nome']) ?></h2>
      <div class="d-flex gap-2">
        <?php if ($mailto): ?>
          <a class="btn btn-primary" href="<?= $mailto ?>">Responder por email</a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= h(url('/admin/orcamentos.php' . $backQS)) ?>">Voltar</a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card p-3">
          <h5>Dados do cliente</h5>
          <div><strong>Nome:</strong> <?= h($lead['nome']) ?></div>
          <div><strong>Email:</strong> <a href="mailto:<?= h($lead['email']) ?>"><?= h($lead['email']) ?></a></div>
          <div><strong>Telefone:</strong> <?= h($lead['telefone']) ?: '—' ?></div>
          <div><strong>Data:</strong> <?= h($lead['created_at']) ?></div>
          <div class="mt-2"><strong>Estado:</strong>
            <div class="d-flex align-items-center gap-2 mt-1">
              <span class="estado-chip <?= h(estado_class((string)$lead['estado'])) ?>"><?= h(estado_label((string)$lead['estado'])) ?></span>
              <form method="post" class="d-inline">
                <?php gerarCSRF(); ?>
                <select name="estado" class="form-select d-inline w-auto">
                  <?php $opts = ['novo','triagem','aguarda_cliente','orcamento_enviado','aceite','rejeitado','arquivado'];
                  foreach ($opts as $op): ?>
                    <option value="<?= h($op) ?>" <?= $lead['estado']===$op?'selected':''; ?>><?= h(estado_label($op)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-primary ms-2">Atualizar</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card p-3">
          <h5>Pedido</h5>
          <div><strong>Assunto:</strong> <?= h($lead['assunto']) ?: '—' ?></div>
          <div><strong>Serviço:</strong> <?= h($lead['servico']) ?: '—' ?></div>
          <div><strong>Localidade:</strong> <?= h($lead['localidade']) ?: '—' ?></div>
          <div class="d-flex gap-3">
            <div><strong>Prazo:</strong> <?= h($lead['prazo']) ?: '—' ?></div>
            <div><strong>Orçamento estimado:</strong> <?= h($lead['orcamento_estimado']) ?: '—' ?></div>
          </div>
          <div class="mt-2"><strong>Mensagem:</strong><br><?= nl2br(h($lead['mensagem'])) ?></div>
          <?php if(!empty($lead['anexo_path']) && function_exists('site_to_fs') && file_exists(site_to_fs($lead['anexo_path']))): ?>
            <div class="mt-3">
              <a class="btn btn-outline-primary btn-sm" target="_blank" href="<?= h(url($lead['anexo_path'])) ?>">Ver anexo</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($lead['utm_source'] || $lead['utm_medium'] || $lead['utm_campaign']): ?>
      <div class="card p-3 mt-3">
        <h6 class="mb-2">UTM</h6>
        <div class="small text-muted">
          source: <?= h($lead['utm_source']) ?: '—' ?> ·
          medium: <?= h($lead['utm_medium']) ?: '—' ?> ·
          campaign: <?= h($lead['utm_campaign']) ?: '—' ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
