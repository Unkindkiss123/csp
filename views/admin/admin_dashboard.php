<?php
require_once __DIR__ . '/../../admin/_admin_guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
$servicosCounts = ['total'=>0,'ativosPub'=>0,'rascunhos'=>0,'internos'=>0];
// Carregar contadores de Serviços
require_once __DIR__ . '/../../includes/ServicosModel.php';
$svcModel = new ServicosModel($conn);
$servicosCounts['total'] = $svcModel->contarTotal();
$servicosCounts['ativosPub'] = $svcModel->contarAtivosPublicados();
$servicosCounts['rascunhos'] = $svcModel->contarRascunhos();
$servicosCounts['internos'] = $svcModel->contarInternos();
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Backoffice · Dashboard';
// Contagem de orçamentos novos
$novos = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM leads WHERE source='orcamento' AND estado='novo'");
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$novos = (int)($row['c'] ?? 0);
$stmt->close();
include __DIR__ . '/../../componentes/header.php';
?>

<main id="conteudo" class="text-dark py-4">
  <div class="container-xl">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Início</a></li>
        <li class="breadcrumb-item active" aria-current="page">Backoffice</li>
      </ol>
    </nav>
    <div class="row g-3">
      <aside class="col-lg-3">
        <div class="list-group sticky-top" style="top: calc(var(--site-header-h) + 1rem);">
          <a class="list-group-item list-group-item-action active" href="<?= BASE_URL ?>admin/index.php">Painel</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/orcamentos.php">Orçamentos</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/produtos.php">Produtos</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/encomendas.php">Encomendas</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/utilizadores.php">Utilizadores</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/categorias.php">Categorias</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/marcas.php">Marcas</a>
          <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/contactos.php">Contactos</a>
        </div>
      </aside>
      <section class="col-lg-9">
        <div class="row g-3">
          <?php
            $cards = [
              [
                'title' => 'Serviços',
                'desc' => 'Gerir serviços e publicação.',
                'href' => BASE_URL . 'admin/servicos_listar.php',
                'icon' => 'bi-tools',
                'meta' => 'Total: ' . $servicosCounts['total'] . ' · Ativos+Publicados: ' . $servicosCounts['ativosPub'] . ' · Rascunhos: ' . $servicosCounts['rascunhos'] . ' · Internos: ' . $servicosCounts['internos'],
                'hint' => ($servicosCounts['ativosPub'] === 0 ? 'Crie e publique o primeiro serviço.' : '')
              ],
              ['title' => 'Orçamentos', 'desc' => 'Gerir pedidos de orçamento. Novos: ' . $novos, 'href' => BASE_URL . 'admin/orcamentos.php', 'icon' => 'bi-cash-coin'],
              ['title' => 'Produtos', 'desc' => 'Gerir catálogo, preços e imagens.', 'href' => BASE_URL . 'admin/produtos.php', 'icon' => 'bi-box-seam'],
              ['title' => 'Encomendas', 'desc' => 'Acompanhar pedidos e estados.', 'href' => BASE_URL . 'admin/encomendas.php', 'icon' => 'bi-receipt-cutoff'],
              ['title' => 'Utilizadores', 'desc' => 'Gerir contas e permissões.', 'href' => BASE_URL . 'admin/utilizadores.php', 'icon' => 'bi-people'],
              ['title' => 'Categorias', 'desc' => 'Organizar o catálogo.', 'href' => BASE_URL . 'admin/categorias.php', 'icon' => 'bi-tags'],
              ['title' => 'Marcas', 'desc' => 'Gerir marcas apresentadas.', 'href' => BASE_URL . 'admin/marcas.php', 'icon' => 'bi-award'],
              ['title' => 'Contactos', 'desc' => 'Ler mensagens recebidas.', 'href' => BASE_URL . 'admin/contactos.php', 'icon' => 'bi-envelope'],
            ];
            foreach ($cards as $c):
          ?>
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 icon-circle-48 icon-accent-bg">
                    <i class="bi <?= h($c['icon']) ?> fs-4"></i>
                  </div>
                  <h2 class="h5 text-primary mb-1"><?= h($c['title']) ?></h2>
                  <p class="text-muted small mb-1"><?= h($c['desc']) ?></p>
                  <?php if (!empty($c['meta'])): ?>
                    <div class="small text-muted mb-2"><?= h($c['meta']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($c['hint'])): ?>
                    <div class="small text-warning mb-2"><?= h($c['hint']) ?></div>
                  <?php endif; ?>
                  <div class="mt-auto d-grid">
                    <a href="<?= h($c['href']) ?>" class="btn btn-outline-primary">Abrir</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../componentes/footer.php'; ?>
