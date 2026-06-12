<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Orçamentos';

// Estado apenas editável no detalhe; nenhum POST handler aqui

// Filters
$estado = trim((string)($_GET['estado'] ?? ''));
$servico = trim((string)($_GET['servico'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20; $offset = ($page-1)*$limit;

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  $where = 'WHERE source="orcamento"';
  $params = []; $types = '';
  if ($estado !== '') { $where .= ' AND estado = ?'; $types.='s'; $params[]=$estado; }
  if ($servico !== '') { $where .= ' AND servico = ?'; $types.='s'; $params[]=$servico; }
  $sql = 'SELECT id, nome, email, telefone, servico, localidade, estado, created_at FROM leads ' . $where . ' ORDER BY created_at DESC';
  $stmt = $conn->prepare($sql);
  if ($types !== '') { $stmt->bind_param($types, ...$params); }
  $stmt->execute(); $res = $stmt->get_result();
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="orcamentos.csv"');
  $out = fopen('php://output','w');
  // UTF-8 BOM for Excel compatibility
  fwrite($out, "\xEF\xBB\xBF");
  fputcsv($out, ['ID','Nome','Email','Telefone','Serviço','Localidade','Estado','Data'], ';');
  while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
      $row['id'], $row['nome'], $row['email'], $row['telefone'], $row['servico'], $row['localidade'], estado_label((string)$row['estado']), $row['created_at']
    ], ';');
  }
  fclose($out);
  exit;
}

// Count total
$where = 'WHERE source="orcamento"';
$params = []; $types = '';
if ($estado !== '') { $where .= ' AND estado = ?'; $types.='s'; $params[]=$estado; }
if ($servico !== '') { $where .= ' AND servico = ?'; $types.='s'; $params[]=$servico; }

$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM leads ' . $where);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute(); $res = $stmt->get_result(); $row = $res->fetch_assoc(); $total = (int)($row['total'] ?? 0); $stmt->close();
$totalPages = max(1, (int)ceil($total/$limit));

// Fetch page
$sql = 'SELECT id, nome, email, telefone, servico, localidade, estado, mensagem, created_at FROM leads ' . $where . ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
$types2 = $types . 'ii'; $params2 = $params; $params2[] = $limit; $params2[] = $offset;
$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types2, ...$params2); } else { $stmt->bind_param('ii', $limit, $offset); }
$stmt->execute(); $lista = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

// Distinct serviços for filter
$srv = $conn->query("SELECT DISTINCT servico FROM leads WHERE source='orcamento' AND servico IS NOT NULL AND servico <> '' ORDER BY servico ASC");
$servicosOpts = [];
while ($r = $srv->fetch_assoc()) { $servicosOpts[] = $r['servico']; }

include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_dashboard.php">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Orçamentos</li>
      </ol>
    </nav>

    <?php if (!empty($_SESSION['alert'])): $a=$_SESSION['alert']; unset($_SESSION['alert']); ?>
      <div class="alert alert-<?= h($a['type'] ?? 'info') ?>"><?= h($a['msg'] ?? '') ?></div>
    <?php endif; ?>

    <div class="bg-white shadow-sm rounded-3 p-3 p-md-4 mb-3">
      <form class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach (['novo','triagem','aguarda_cliente','orcamento_enviado','aceite','rejeitado','arquivado'] as $opt): ?>
              <option value="<?= h($opt) ?>" <?= $estado===$opt?'selected':''; ?>><?= h(ucwords(str_replace('_',' ', $opt))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <label class="form-label">Serviço</label>
          <select name="servico" class="form-select" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($servicosOpts as $opt): ?>
              <option value="<?= h($opt) ?>" <?= $servico===$opt?'selected':''; ?>><?= h($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4 text-sm-end">
          <a class="btn btn-outline-secondary mt-3 mt-sm-0" href="?<?= http_build_query(array_filter(['estado'=>$estado,'servico'=>$servico])) ?>&export=csv">Exportar CSV</a>
        </div>
      </form>
    </div>

    <div class="table-responsive bg-white shadow-sm rounded-3">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Serviço</th>
            <th>Localidade</th>
            <th>Estado</th>
            <th>Data</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$lista): ?>
            <tr><td colspan="9" class="text-center text-muted">Sem registos.</td></tr>
          <?php endif; ?>
          <?php foreach ($lista as $l): ?>
            <tr>
              <td><?= (int)$l['id'] ?></td>
              <td><?= h($l['nome']) ?></td>
              <td><a href="mailto:<?= h($l['email']) ?>"><?= h($l['email']) ?></a></td>
              <td><?= h($l['telefone']) ?></td>
              <td><?= h($l['servico']) ?></td>
              <td><?= h($l['localidade']) ?></td>
              <td class="estado-cell <?= h(estado_class((string)$l['estado'])) ?>">
                <?= h(estado_label((string)$l['estado'])) ?>
              </td>
              <td><?= h($l['created_at']) ?></td>
              <td class="text-end">
                <?php $qsLink = http_build_query(array_filter(['estado'=>$estado,'servico'=>$servico,'page'=>$page])); $qsLink = $qsLink? '&'.$qsLink : ''; ?>
                <a class="btn btn-outline-primary btn-sm" href="<?= h(url('/admin/orcamento_detalhe.php?id='.(int)$l['id'].$qsLink)) ?>">Ver</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="mt-3" aria-label="Paginação">
        <ul class="pagination justify-content-center">
          <?php $qsBase = array_filter(['estado'=>$estado,'servico'=>$servico]); ?>
          <li class="page-item <?= $page<=1?'disabled':''; ?>">
            <a class="page-link" href="?<?= http_build_query($qsBase + ['page'=>$page-1]) ?>">Anterior</a>
          </li>
          <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
            <li class="page-item <?= $i===$page?'active':''; ?>"><a class="page-link" href="?<?= http_build_query($qsBase + ['page'=>$i]) ?>"><?= $i ?></a></li>
          <?php endfor; ?>
          <li class="page-item <?= $page>=$totalPages?'disabled':''; ?>">
            <a class="page-link" href="?<?= http_build_query($qsBase + ['page'=>$page+1]) ?>">Seguinte</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <!-- Modal Detalhes -->
  <div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalhes do pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <dl class="row mb-0" id="leadDetails"></dl>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  if (!btn) return;
  const data = btn.getAttribute('data-lead');
  if (!data) return;
  const lead = JSON.parse(data);
  const map = {
    'ID': lead.id,
    'Nome': lead.nome,
    'Email': lead.email,
    'Telefone': lead.telefone,
    'Serviço': lead.servico,
    'Localidade': lead.localidade,
    'Estado': lead.estado,
    'Data': lead.created_at,
    'Mensagem': lead.mensagem
  };
  const dl = document.getElementById('leadDetails');
  dl.innerHTML = '';
  Object.keys(map).forEach(k => {
    const dt = document.createElement('dt'); dt.className = 'col-sm-3'; dt.textContent = k;
    const dd = document.createElement('dd'); dd.className = 'col-sm-9'; dd.textContent = map[k] ?? '';
    dl.appendChild(dt); dl.appendChild(dd);
  });
});
</script>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
