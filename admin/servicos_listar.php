<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/ServicosModel.php';

$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Serviços';
$model = new ServicosModel($conn);

$tipo = (string)($_GET['tipo'] ?? '');
$estado = (string)($_GET['estado'] ?? '');
$lista = $model->listar(['tipo'=>$tipo ?: null,'estado'=>$estado ?: null]);

include __DIR__ . '/../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= h(url('/admin/index.php')) ?>">Backoffice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Serviços</li>
      </ol>
    </nav>

    <?php if (!empty($_SESSION['alert'])): $a=$_SESSION['alert']; unset($_SESSION['alert']); ?>
      <div class="alert alert-<?= h($a['type']) ?>"><?= h($a['msg']) ?></div>
    <?php endif; ?>

    <div class="bg-white shadow-sm rounded-3 p-3 p-md-4 mb-3">
      <form class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label">Tipo</label>
          <select name="tipo" class="form-select" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach (['manutencao','preco_fixo','orcamento_personalizado'] as $t): ?>
              <option value="<?= h($t) ?>" <?= $tipo===$t?'selected':''; ?>><?= h(ucwords(str_replace('_',' ',$t))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <label class="form-label">Estado visibilidade</label>
          <select name="estado" class="form-select" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach (['ativo','inativo','interno'] as $e): ?>
              <option value="<?= h($e) ?>" <?= $estado===$e?'selected':''; ?>><?= h(ucfirst($e)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4 text-sm-end">
          <a class="btn btn-primary mt-3 mt-sm-0" href="<?= h(url('/admin/servico_form.php')) ?>">Novo serviço</a>
        </div>
      </form>
    </div>

    <div class="table-responsive bg-white shadow-sm rounded-3">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Visibilidade</th>
            <th>Publicação</th>
            <th>Ordem</th>
            <th>Atualizado</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$lista): ?><tr><td colspan="8" class="text-center text-muted">Sem serviços.</td></tr><?php endif; ?>
          <?php foreach ($lista as $s): ?>
            <tr>
              <td><?= (int)$s['id'] ?></td>
              <td><?= h($s['titulo']) ?></td>
              <td><?= h(ucwords(str_replace('_',' ', $s['tipo']))) ?></td>
              <td><?= h(ucfirst($s['estado_visibilidade'])) ?></td>
              <td><?= h(ucfirst($s['status_publicacao'])) ?></td>
              <td><?= (int)$s['ordem'] ?></td>
              <td><?= h($s['updated_at']) ?></td>
              <td class="text-end">
                <div class="btn-group">
                  <a class="btn btn-outline-primary btn-sm" href="<?= h(url('/admin/servico_form.php?id='.(int)$s['id'])) ?>">Editar</a>
                  <form method="post" action="<?= h(url('/admin/servicos_handler.php')) ?>" onsubmit="return confirm('Confirmar ação?');">
                    <?php gerarCSRF(); ?>
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <?php if ($s['status_publicacao']==='publicado'): ?>
                      <input type="hidden" name="acao" value="despublicar">
                      <button class="btn btn-outline-warning btn-sm" type="submit">Despublicar</button>
                    <?php else: ?>
                      <input type="hidden" name="acao" value="publicar">
                      <button class="btn btn-outline-success btn-sm" type="submit">Publicar</button>
                    <?php endif; ?>
                  </form>
                  <form method="post" action="<?= h(url('/admin/servicos_handler.php')) ?>" onsubmit="return confirm('Apagar este serviço?');">
                    <?php gerarCSRF(); ?>
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm" type="submit">Apagar</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
