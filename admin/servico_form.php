<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/ServicosModel.php';

$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Serviço';
$model = new ServicosModel($conn);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$svc = $id ? $model->obter($id) : null;

include __DIR__ . '/../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= h(url('/admin/index.php')) ?>">Backoffice</a></li>
        <li class="breadcrumb-item"><a href="<?= h(url('/admin/servicos_listar.php')) ?>">Serviços</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $id? 'Editar' : 'Novo' ?></li>
      </ol>
    </nav>

    <?php if (!empty($_SESSION['alert'])): $a=$_SESSION['alert']; unset($_SESSION['alert']); ?>
      <div class="alert alert-<?= h($a['type']) ?>"><?= h($a['msg']) ?></div>
    <?php endif; ?>

    <div class="bg-white shadow-sm rounded-3 p-3 p-md-4">
      <form method="post" action="<?= h(url('/admin/servicos_handler.php')) ?>" enctype="multipart/form-data">
        <?php gerarCSRF(); ?>
        <input type="hidden" name="acao" value="<?= $id? 'atualizar':'criar' ?>">
        <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-conteudo" type="button" role="tab">Conteúdo</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-imagem" type="button" role="tab">Imagem</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab">SEO</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-opcoes" type="button" role="tab">Opções por tipo</button></li>
        </ul>

        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="tab-conteudo" role="tabpanel">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Título *</label>
                <input class="form-control" name="titulo" value="<?= h($svc['titulo'] ?? '') ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Tipo *</label>
                <?php $tipo = $svc['tipo'] ?? ''; ?>
                <div class="d-flex gap-3">
                  <?php foreach(['manutencao'=>'Manutenção','preco_fixo'=>'Preço fixo','orcamento_personalizado'=>'Orçamento personalizado'] as $k=>$lbl): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="tipo" id="tipo_<?= h($k) ?>" value="<?= h($k) ?>" <?= $tipo===$k?'checked':''; ?> required>
                      <label class="form-check-label" for="tipo_<?= h($k) ?>"><?= h($lbl) ?></label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="col-md-12">
                <label class="form-label">Resumo curto *</label>
                <input class="form-control" name="resumo_curto" maxlength="220" value="<?= h($svc['resumo_curto'] ?? '') ?>" required>
              </div>
              <div class="col-md-8">
                <label class="form-label">Descrição longa *</label>
                <textarea class="form-control" name="descricao_longa" rows="8" required><?= h($svc['descricao_longa'] ?? '') ?></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label">Preço base (opcional)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="preco_base" value="<?= h((string)($svc['preco_base'] ?? '')) ?>">
                <label class="form-label mt-3">Estado de visibilidade</label>
                <?php $est = $svc['estado_visibilidade'] ?? 'inativo'; ?>
                <select name="estado_visibilidade" class="form-select">
                  <?php foreach(['ativo','inativo','interno'] as $op): ?>
                    <option value="<?= h($op) ?>" <?= $est===$op?'selected':''; ?>><?= h(ucfirst($op)) ?></option>
                  <?php endforeach; ?>
                </select>
                <label class="form-label mt-3">Ordem</label>
                <input type="number" class="form-control" name="ordem" value="<?= h((string)($svc['ordem'] ?? 0)) ?>">
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-imagem" role="tabpanel">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Imagem principal <?= empty($svc)? '*':''; ?></label>
                <input type="file" name="imagem_principal" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <input type="hidden" name="imagem_principal_existente" value="<?= h($svc['imagem_principal'] ?? '') ?>">
                <div class="form-text">Aceite: JPG, PNG, WEBP. Até 5MB.</div>
              </div>
              <div class="col-md-6">
                <?php if (!empty($svc['imagem_principal'])): ?>
                  <img src="<?= h(url($svc['imagem_principal'])) ?>" class="img-fluid rounded border" alt="Imagem atual">
                <?php else: ?>
                  <div class="text-muted small">Sem imagem carregada.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-seo" role="tabpanel">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">SEO Title</label>
                <input class="form-control" name="seo_title" maxlength="70" value="<?= h($svc['seo_title'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">SEO Description</label>
                <input class="form-control" name="seo_description" maxlength="160" value="<?= h($svc['seo_description'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-opcoes" role="tabpanel">
            <div class="alert alert-info mb-0">
              <?php $t = $svc['tipo'] ?? 'manutencao'; ?>
              <?php if ($t==='manutencao'): ?>
                Para serviços de manutenção, poderá futuramente configurar planos e periodicidades.
              <?php elseif ($t==='preco_fixo'): ?>
                Para serviços de preço fixo, poderá configurar pacotes e extras.
              <?php else: ?>
                Para serviços de orçamento personalizado, poderá definir perguntas adicionais no formulário.
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
          <a class="btn btn-outline-secondary" href="<?= h(url('/admin/servicos_listar.php')) ?>">Voltar</a>
          <button class="btn btn-primary" type="submit">Guardar rascunho</button>
        </div>
      </form>

      <?php if ($id): ?>
      <hr>
      <div class="d-flex flex-wrap gap-2">
        <form method="post" action="<?= h(url('/admin/servicos_handler.php')) ?>">
          <?php gerarCSRF(); ?>
          <input type="hidden" name="acao" value="<?= ($svc['status_publicacao'] ?? 'rascunho')==='publicado' ? 'despublicar' : 'publicar' ?>">
          <input type="hidden" name="id" value="<?= (int)$id ?>">
          <?php if (($svc['status_publicacao'] ?? 'rascunho')==='publicado'): ?>
            <button class="btn btn-warning" type="submit">Despublicar</button>
          <?php else: ?>
            <button class="btn btn-success" type="submit">Publicar</button>
          <?php endif; ?>
        </form>

        <form method="post" action="<?= h(url('/admin/servicos_handler.php')) ?>">
          <?php gerarCSRF(); ?>
          <input type="hidden" name="acao" value="estado">
          <input type="hidden" name="id" value="<?= (int)$id ?>">
          <div class="input-group" style="max-width:380px;">
            <label class="input-group-text">Visibilidade</label>
            <select name="estado_visibilidade" class="form-select">
              <?php $est = $svc['estado_visibilidade'] ?? 'inativo'; foreach(['ativo','inativo','interno'] as $op): ?>
                <option value="<?= h($op) ?>" <?= $est===$op?'selected':''; ?>><?= h(ucfirst($op)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-primary" type="submit">Aplicar</button>
          </div>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
