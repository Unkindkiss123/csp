<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
$page_title = 'Constrói Spa & Pool — Piscinas, Spas e Obra';
$breadcrumb = [
  ['label' => 'Início', 'href' => null]
];
$page_description = 'Construção de piscinas, assistência técnica, manutenção e obras. Constrói Spa & Pool em Vidigueira.';
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../componentes/header.php';
?>

<?php $tituloPagina='Início'; $subtituloPagina=''; $pageClass='home'; include __DIR__.'/../componentes/page_hero.php'; ?>

<main id="conteudo" class="container my-4 text-dark">
  <!-- Hero Carousel -->
  <section class="pt-2 csp-reveal">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" aria-label="Destaques">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active" style="background: linear-gradient(135deg, rgba(172,220,255,.6), rgba(76,176,232,.6));">
          <div class="container-xl">
            <div class="row align-items-center py-5" style="min-height: 360px;">
              <div class="col-lg-7 text-white">
                <h1 class="display-6 fw-bold">Piscinas de sonho, com qualidade e garantia</h1>
                <p class="lead">Projetamos e construímos piscinas à medida, com acompanhamento técnico dedicado.</p>
                <a href="<?= htmlspecialchars($url_for('/servicos.php')) ?>" class="btn btn-light text-primary fw-semibold">Conheça os serviços</a>
              </div>
              <div class="col-lg-5 d-none d-lg-block text-end">
                <?= render_picture('/public/imagens/wave.png', 'Elemento decorativo', 'img-fluid', 'lazy', '320px') ?>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item" style="background: linear-gradient(135deg, rgba(76,176,232,.6), rgba(45,138,200,.6));">
          <div class="container-xl">
            <div class="row align-items-center py-5" style="min-height: 360px;">
              <div class="col-lg-7 text-white">
                <h2 class="h1 fw-bold">Assistência técnica e manutenção</h2>
                <p class="lead">Mantemos o seu equipamento eficiente e a água perfeita durante todo o ano.</p>
                <a href="<?= htmlspecialchars($url_for('/views/servico_assistencia_view.php')) ?>" class="btn btn-light text-primary fw-semibold">Assistência Técnica</a>
              </div>
              <div class="col-lg-5 d-none d-lg-block text-end">
                <?= render_picture('/public/imagens/logo_no_text.png', 'Logótipo Constrói Spa & Pool', 'img-fluid', 'lazy', '220px') ?>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item" style="background: linear-gradient(135deg, rgba(45,138,200,.6), rgba(172,220,255,.6));">
          <div class="container-xl">
            <div class="row align-items-center py-5" style="min-height: 360px;">
              <div class="col-lg-7 text-white">
                <h2 class="h1 fw-bold">Spas e acessórios</h2>
                <p class="lead">Seleção de produtos para conforto e bem-estar no seu espaço exterior.</p>
                <a href="<?= htmlspecialchars($url_for('/produtos.php')) ?>" class="btn btn-light text-primary fw-semibold">Ver produtos</a>
              </div>
              <div class="col-lg-5 d-none d-lg-block text-end">
                <?= render_picture('/public/imagens/og-cover.jpg', 'Imagem de destaque', 'img-fluid rounded', 'lazy', '(max-width:768px) 100vw, 40vw') ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Anterior">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Seguinte">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Seguinte</span>
      </button>
    </div>
  </section>

  <!-- Serviços -->
  <section class="py-4 py-md-5 bg-light csp-reveal">
    <div class="container-xl">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h3 text-primary mb-0">Serviços</h2>
  <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($url_for('/servicos.php')) ?>">Ver todos</a>
      </div>
    <div class="row g-3 g-md-4 csp-reveal">
        <?php
          $servicos = [
            ['icon' => 'bi-building', 'title' => 'Construção de Piscinas', 'href' => './servico_construcao_view.php'],
            ['icon' => 'bi-wrench-adjustable', 'title' => 'Assistência Técnica', 'href' => './servico_assistencia_view.php'],
            ['icon' => 'bi-recycle', 'title' => 'Manutenção', 'href' => './servico_manutencao_view.php'],
            ['icon' => 'bi-badge-hd', 'title' => 'Tela Armada', 'href' => './servico_tela_view.php'],
            ['icon' => 'bi-clipboard-check', 'title' => 'Acompanhamento em Obra', 'href' => './servico_acompanhamento_view.php'],
            ['icon' => 'bi-bricks', 'title' => 'Construção Civil', 'href' => './servico_civil_view.php'],
          ];
          foreach ($servicos as $s):
        ?>
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 icon-circle-56 icon-accent-bg">
                  <i class="bi <?= h($s['icon']) ?> fs-4"></i>
                </div>
                <h3 class="h5 text-primary"><?= h($s['title']) ?></h3>
                <p class="text-muted small mb-3">Soluções profissionais e personalizadas. <!-- função pendente --></p>
                <div class="mt-auto d-grid">
                  <a class="btn btn-outline-primary" href="<?= h($s['href']) ?>">Saiba mais</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Produtos em destaque (placeholders) -->
  <section class="py-4 py-md-5">
    <div class="container-xl">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h3 text-primary mb-0">Produtos</h2>
  <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($url_for('/produtos.php')) ?>">Ver catálogo</a>
      </div>
  <div class="row g-3 g-md-4 csp-reveal">
        <?php for ($i=1; $i<=6; $i++): ?>
          <div class="col-12 col-sm-6 col-lg-4">
            <article class="card h-100 product-card border-0 shadow-sm">
              <div class="ratio ratio-4x3 product-img-wrap">
                <?= render_picture('/public/imagens/produtos/placeholder.jpg', 'Produto', 'product-img', 'lazy', '(max-width:576px) 100vw, 33vw') ?>
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="h6 card-title mb-1">Produto #<?= $i ?></h3>
                <p class="text-muted small mb-2">Descrição breve do produto. <!-- função pendente --></p>
                <div class="text-primary fw-semibold mb-3">€0,00</div>
                <div class="mt-auto d-grid">
                  <a class="btn btn-outline-primary" href="<?= htmlspecialchars($url_for('/produtos.php')) ?>">Ver mais</a>
                </div>
              </div>
            </article>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- Sobre nós -->
  <section class="py-4 py-md-5 bg-light">
    <div class="container-xl">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <h2 class="h3 text-primary">Sobre nós</h2>
          <p class="text-muted">Profissionais com experiência em construção de piscinas, assistência técnica e manutenção. Trabalhamos com materiais de qualidade e foco no detalhe.</p>
          <ul class="text-muted">
            <li>Equipa técnica especializada</li>
            <li>Projetos personalizados</li>
            <li>Suporte e acompanhamento</li>
          </ul>
        </div>
        <div class="col-lg-6">
          <div class="ratio ratio-16x9 bg-white rounded shadow-sm d-flex align-items-center justify-content-center">
            <span class="text-muted">Vídeo / imagem da empresa (placeholder)</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contactos rápidos -->
  <section class="py-4 py-md-5">
    <div class="container-xl">
      <div class="p-4 p-md-5 bg-white shadow-sm rounded-3">
        <div class="row g-3 align-items-center">
          <div class="col-lg-8">
            <h2 class="h4 text-primary">Precisa de ajuda ou orçamento?</h2>
            <p class="text-muted mb-0">Fale connosco — respondemos rapidamente.</p>
          </div>
          <div class="col-lg-4 text-lg-end d-grid d-sm-flex gap-2 justify-content-lg-end">
            <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($url_for('/contactos.php')) ?>">Contactar</a>
            <a class="btn btn-outline-primary btn-lg" href="<?= htmlspecialchars($url_for('/servicos.php')) ?>">Ver serviços</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
