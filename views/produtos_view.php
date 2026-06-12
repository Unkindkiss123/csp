<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/funcoes_produtos.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Produtos | Constrói Spa & Pool';
$page_description = 'Equipamentos, spas e acessórios de qualidade para piscinas.';
$breadcrumb = [
  ['label'=>'Início','href'=>'/'],
  ['label'=>'Produtos','href'=>null]
];
include __DIR__ . '/../componentes/header.php';

// Helper simples para truncar descrição
function resumo(string $txt, int $lim = 120): string {
  $t = trim(strip_tags($txt));
  if (mb_strlen($t) <= $lim) return $t;
  return rtrim(mb_substr($t, 0, $lim - 1)) . '…';
}

// Resolve path de imagem para funcionar a partir de /views sob subpastas (XAMPP)
function resolve_img_path(?string $p = null): string {
  $fallback = '../public/imagens/logo_no_text.png';
  if (!$p) return $fallback;
  if (preg_match('#^https?://#i', $p)) return $p;
  // Base da pasta do projeto (ex.: /constroi_spa_and_pool)
  $base = '/' . basename(dirname(__DIR__));
  if ($p[0] === '/') {
    // /public/... -> ../public/...
    if (strpos($p, '/public/') === 0) return '..' . $p;
    // /constroi_spa_and_pool/public/... -> ../public/...
    if (strpos($p, $base . '/public/') === 0) return '..' . substr($p, strlen($base));
    // fallback: sobe um nível
    return '..' . $p;
  }
  return $p;
}

// Parâmetros de pesquisa/fitros (prontos para backend)
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['categoria'] ?? '');
$brand = trim($_GET['marca'] ?? '');
$pmin = trim($_GET['pmin'] ?? '');
$pmax = trim($_GET['pmax'] ?? '');
// Ordenação e paginação
$order = trim($_GET['order'] ?? 'recente');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Consultar BD (ativos por defeito) com filtros
$total = countProdutos($conn, [
  'q' => $q,
  'categoria' => $cat,
  'marca' => $brand,
  'pmin' => $pmin,
  'pmax' => $pmax,
  'ativo' => 1,
]);
$produtos = getProdutos($conn, [
  'q' => $q,
  'categoria' => $cat,
  'marca' => $brand,
  'pmin' => $pmin,
  'pmax' => $pmax,
  'ativo' => 1,
  'order' => $order,
  'limit' => $perPage,
  'offset' => $offset,
]);
$totalPages = max(1, (int)ceil($total / $perPage));
?>

<?php $tituloPagina='Produtos'; $subtituloPagina='Catálogo e filtros'; $pageClass='produtos'; include __DIR__.'/../componentes/page_hero.php'; ?>

<main class="container my-4 pt-0 pb-4" id="conteudo">
  <div class="container-xl text-dark">
    <!-- Barra de ferramentas: pesquisa e botão de filtros para mobile -->
    <div class="bg-white shadow-sm rounded-3 p-3 p-md-4 mb-3 mb-md-4">
      <form class="row g-2 align-items-stretch" method="GET" action="">
        <div class="col-12 col-md">
          <div class="input-group">
            <input type="text" class="form-control" name="q" value="<?= h($q) ?>" placeholder="Procurar por nome ou referência…" aria-label="Pesquisar produtos">
            <button class="btn btn-primary" type="submit">Pesquisar</button>
          </div>
        </div>
        <div class="col-12 col-md-auto">
          <select class="form-select" name="order" onchange="this.form.submit()" aria-label="Ordenar">
            <option value="recente" <?= $order==='recente'?'selected':''; ?>>Mais recentes</option>
            <option value="preco_asc" <?= $order==='preco_asc'?'selected':''; ?>>Preço: menor para maior</option>
            <option value="preco_desc" <?= $order==='preco_desc'?'selected':''; ?>>Preço: maior para menor</option>
            <option value="nome_asc" <?= $order==='nome_asc'?'selected':''; ?>>Nome: A → Z</option>
            <option value="nome_desc" <?= $order==='nome_desc'?'selected':''; ?>>Nome: Z → A</option>
          </select>
        </div>
        <div class="col-12 col-md-auto d-md-none">
          <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false" aria-controls="filtersCollapse">Filtrar</button>
        </div>
      </form>
    </div>

    <div class="row g-3 g-md-4">
      <!-- Filtros (coluna esquerda em desktop; collapse em mobile) -->
      <aside class="col-12 col-lg-3">
        <div class="collapse d-lg-block" id="filtersCollapse">
          <div class="bg-white shadow-sm rounded-3 p-3 p-md-4">
            <h2 class="h6 text-primary mb-3">Filtros</h2>
            <form method="GET" action="">
              <input type="hidden" name="q" value="<?= h($q) ?>">

              <div class="mb-3">
                <label class="form-label">Categoria</label>
                <select class="form-select" name="categoria">
                  <option value="">Todas</option>
                  <option value="Spas" <?= $cat==='Spas'?'selected':''; ?>>Spas</option>
                  <option value="Bombas" <?= $cat==='Bombas'?'selected':''; ?>>Bombas</option>
                  <option value="Coberturas" <?= $cat==='Coberturas'?'selected':''; ?>>Coberturas</option>
                  <option value="Tratamento de Água" <?= $cat==='Tratamento de Água'?'selected':''; ?>>Tratamento de Água</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Marca</label>
                <select class="form-select" name="marca">
                  <option value="">Todas</option>
                  <option value="HydroZen" <?= $brand==='HydroZen'?'selected':''; ?>>HydroZen</option>
                  <option value="AquaPro" <?= $brand==='AquaPro'?'selected':''; ?>>AquaPro</option>
                  <option value="ThermaPool" <?= $brand==='ThermaPool'?'selected':''; ?>>ThermaPool</option>
                  <option value="BlueSalt" <?= $brand==='BlueSalt'?'selected':''; ?>>BlueSalt</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Preço</label>
                <div class="row g-2">
                  <div class="col-6">
                    <input type="number" step="0.01" min="0" class="form-control" name="pmin" value="<?= h($pmin) ?>" placeholder="Min">
                  </div>
                  <div class="col-6">
                    <input type="number" step="0.01" min="0" class="form-control" name="pmax" value="<?= h($pmax) ?>" placeholder="Max">
                  </div>
                </div>
              </div>

              <div class="d-grid">
                <button class="btn btn-primary" type="submit">Aplicar</button>
              </div>
            </form>
          </div>
        </div>
      </aside>

      <!-- Grelha de produtos -->
      <section class="col-12 col-lg-9">
  <div class="row g-3 g-md-4 csp-reveal">
          <?php if (empty($produtos)): ?>
            <div class="col-12">
              <div class="bg-white shadow-sm rounded-3 p-4 text-center text-muted">Sem produtos a apresentar.</div>
            </div>
          <?php else: ?>
            <?php foreach ($produtos as $p):
              $ativo = !empty($p['ativo']);
              $img = (string)($p['imagem_principal'] ?? '');
              $img = resolve_img_path($img);
              $nome = (string)($p['nome'] ?? 'Produto');
              $desc = (string)($p['descricao'] ?? '');
              $preco = isset($p['preco']) ? (float)$p['preco'] : 0.0;
              $categoria = (string)($p['categoria'] ?? '');
              $marca = (string)($p['marca'] ?? '');
              $id = isset($p['id']) ? (int)$p['id'] : 0;
              // URL da página individual do produto
              $link = 'produto_detalhe_view.php?id=' . $id;
            ?>
              <div class="col-12 col-sm-6 col-lg-4">
                <article class="card product-card h-100 border-0 shadow-sm position-relative <?= $ativo?'':'product-inactive'; ?>">
                  <div class="ratio ratio-4x3 product-img-wrap">
                    <?= render_picture(strpos($img, '../')===0? substr($img, 2) : $img, $nome, 'product-img img-fluid', 'lazy') ?>
                    <?php if (!$ativo): ?>
                      <div class="product-overlay">Indisponível</div>
                    <?php endif; ?>
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h3 class="h6 card-title mb-1" title="<?= h($nome) ?>"><?= h($nome) ?></h3>
                    <p class="card-text text-muted small mb-2" title="<?= h($desc) ?>"><?= h(resumo($desc, 90)) ?></p>
                    <div class="mb-2 fw-semibold text-primary fs-5">€<?= number_format($preco, 2, ',', ' ') ?></div>
                    <div class="text-muted small mb-3">
                      <span><?= h($categoria) ?></span>
                      <?php if ($marca): ?> · <span><?= h($marca) ?></span><?php endif; ?>
                      <?php if (!empty($p['assistencia'])): ?>
                        <span class="badge bg-info text-dark ms-2">Assistência e Instalação</span>
                      <?php endif; ?>
                    </div>
                    <div class="mt-auto d-grid">
                      <?php if (empty($p['requer_orcamento'])): ?>
                        <button class="btn btn-primary w-100" disabled>Adicionar ao carrinho</button>
                      <?php else: ?>
                        <a class="btn btn-outline-primary w-100" href="<?= url_for('/views/' . $link) ?>">Pedir Orçamento</a>
                      <?php endif; ?>
                      <a href="<?= url_for('/views/' . $link) ?>" class="stretched-link" aria-label="Ver produto <?= h($nome) ?>"></a>
                    </div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
          <?php
            // Monta a query base preservando filtros e ordenação
            $baseParams = [
              'q' => $q, 'categoria' => $cat, 'marca' => $brand, 'pmin' => $pmin, 'pmax' => $pmax, 'order' => $order
            ];
            function buildUrl($pageNum, $params) {
              $params['page'] = $pageNum;
              $qs = http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
              return '?' . $qs;
            }
          ?>
          <nav class="mt-3" aria-label="Paginação de produtos">
            <ul class="pagination justify-content-center">
              <li class="page-item <?= $page<=1?'disabled':''; ?>">
                <a class="page-link" href="<?= $page<=1?'#':buildUrl($page-1,$baseParams); ?>" tabindex="-1">Anterior</a>
              </li>
              <?php
                $maxToShow = 5;
                $start = max(1, $page - 2);
                $end = min($totalPages, $start + $maxToShow - 1);
                $start = max(1, $end - $maxToShow + 1);
                for ($i = $start; $i <= $end; $i++):
              ?>
                <li class="page-item <?= $i==$page?'active':''; ?>">
                  <a class="page-link" href="<?= buildUrl($i,$baseParams); ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?= $page>=$totalPages?'disabled':''; ?>">
                <a class="page-link" href="<?= $page>=$totalPages?'#':buildUrl($page+1,$baseParams); ?>">Seguinte</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
