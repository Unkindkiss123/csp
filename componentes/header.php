<?php
/**
 * Header component for Constrói Spa & Pool
 * - Bootstrap 5 responsive navbar
 * - Modular PHP include (place in views/* as include 'componentes/header.php')
 * - Dynamic menu placeholder (from DB)
 * - User section (authenticated vs guest)
 */

// Load config so session ini can be applied before starting session
require_once __DIR__ . '/../includes/config.php';
// Ensure session is started for user auth state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Polyfills for PHP < 8
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return $needle === '' ? true : strpos($haystack, $needle) === 0;
    }
}

// Helper: current path to highlight active menu
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';

// Compute project base URL from filesystem path (works under XAMPP)
$docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
$projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
$projectRootFs = rtrim($projectRootFs, '/');
$projectBaseUrl = '';
if ($docRootFs && str_starts_with($projectRootFs, $docRootFs)) {
    $projectBaseUrl = substr($projectRootFs, strlen($docRootFs));
    $projectBaseUrl = '/' . ltrim($projectBaseUrl, '/');
}

// URL helper to prepend base URL when needed
$asset = function (string $path) use ($projectBaseUrl): string {
    $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : rtrim($projectBaseUrl, '/');
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
};

// Build URL for internal links (handles absolute http(s), absolute site paths, and relative)
$url_for = function (string $href) use ($projectBaseUrl): string {
    if (preg_match('#^https?://#i', $href)) return $href;
    $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : rtrim($projectBaseUrl, '/');
    if (str_starts_with($href, '/')) return $base . $href;
    return $base . '/' . ltrim($href, '/');
};

// (imagem resolver removido; usamos caminhos diretos com asset()+versão)

// Example: dynamic menu items placeholder (to be replaced by DB query)
// Expected shape:
// $menuItems = [
//   ['label' => 'Início', 'href' => '/index.php'],
//   ['label' => 'Produtos', 'href' => '/views/produtos_view.php'],
//   ['label' => 'Contactos', 'href' => '/views/contactos_view.php'],
// ];
// If a controller populates $menuItems, we'll use it; else fall back to a minimal default
if (!isset($menuItems) || !is_array($menuItems) || count($menuItems) === 0) {
            // Fallback do menu principal (site-relative) — aponta para wrappers na raiz
            $menuItems = [
                ['label' => 'Início', 'href' => '/index.php'],
                ['label' => 'Serviços', 'href' => '/servicos.php'],
                ['label' => 'Produtos', 'href' => '/produtos.php'],
                ['label' => 'Sobre', 'href' => '/sobre.php'],
                ['label' => 'Contactos', 'href' => '/contactos.php'],
            ];
}

// User data
// If a full user array exists, use it; otherwise derive from session keys set on login
$user = $_SESSION['user'] ?? null;
if (!$user && isset($_SESSION['user_id'])) {
    $user = [
        'name' => $_SESSION['nome'] ?? $_SESSION['usuario'] ?? 'Utilizador',
        'avatar' => $_SESSION['avatar'] ?? '',
    ];
}
?>

<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
            $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
            $defaultTitle = 'Constrói Spa & Pool';
            $defaultDesc  = 'Constrói Spa & Pool — construção de piscinas, assistência técnica, manutenção e obras em Vidigueira.';
            $defaultCanonical = $base !== '' ? $base.'/' : '/';
            $defaultOg = $asset('/public/imagens/og-cover.jpg');

            $page_title       = isset($page_title)       && $page_title       !== '' ? $page_title       : $defaultTitle;
            $page_description = isset($page_description) && $page_description !== '' ? $page_description : $defaultDesc;
            $page_canonical   = isset($page_canonical)   && $page_canonical   !== '' ? $page_canonical   : $defaultCanonical;
            $page_og_image    = isset($page_og_image)    && $page_og_image    !== '' ? $page_og_image    : $defaultOg;
        ?>
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- SEO básico -->
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
    <!-- Favicon -->
    <link rel="icon" href="<?= htmlspecialchars($asset('/public/imagens/favicon.png')) ?>">

    <!-- Open Graph / Social -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page_og_image) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">

                <script>
                (function(){
                    var KEY='csp_theme';
                    try{
                        var t=localStorage.getItem(KEY);
                        if(!t){
                            t=(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light';
                        }
                        document.documentElement.setAttribute('data-theme', t);
                        document.documentElement.classList.toggle('theme-dark', t==='dark');
                        localStorage.setItem(KEY,t);
                    }catch(e){}
                })();
                </script>

    <!-- Bootstrap CSS (5.x) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Google Fonts: Poppins and Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons (para ícones nos serviços, se necessário) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Project CSS (minificado) -->
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_with_version($asset('/public/css/estilo.min.css'))) ?>">
    <!-- Dark theme (only active when html has .theme-dark) -->
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_with_version($asset('/public/css/dark-mode.css'))) ?>">

        <!-- Performance: Preload da logo -->
        <link rel="preload" as="image" href="<?= htmlspecialchars($asset('/public/imagens/logo_no_text.png')) ?>">

        <!-- PWA manifest + theme color -->
        <link rel="manifest" href="<?= htmlspecialchars($asset('/manifest.json')) ?>">
        <meta name="theme-color" content="#4CB0E8">

        <!-- SEO Local (Schema.org) -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "LocalBusiness",
            "name": "Constrói Spa & Pool",
            "image": "https://www.constroi-spa-pool.pt/public/imagens/og-cover.jpg",
            "telephone": "+351 900 000 000",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Estrada de Cuba, 6A",
                "postalCode": "7960-213",
                "addressLocality": "Vidigueira",
                "addressCountry": "PT"
            },
            "url": "https://www.constroi-spa-pool.pt/",
            "geo": {
              "@type": "GeoCoordinates",
              "latitude": 38.2086,
              "longitude": -7.8006
            }
        }
        </script>
</head>
<?php
    // Permite marcar páginas de autenticação/checkout etc. com classes adicionais para JS/CSS scoped
    $body_classes = trim(('text-white' . ' ' . (isset($body_classes) ? (string)$body_classes : '')));
?>
<body class="<?= htmlspecialchars($body_classes) ?>">
<a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-light btn-sm" href="#conteudo">Saltar para conteúdo</a>
<header class="site-header header-gradient light fixed-top bg-gradiente py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3">
            <!-- Logo + Brand -->
            <a href="<?= htmlspecialchars($url_for('/index.php')) ?>" class="d-flex align-items-center text-white text-decoration-none brand-wrap ps-2 ps-md-3">
                <img src="<?= htmlspecialchars(asset_with_version($asset('/public/imagens/logo_no_text.png'))) ?>" alt="Constrói Spa & Pool" class="brand-logo img-fluid me-2" width="80" height="80">
                <div class="d-flex flex-column">
                    <span class="brand-title">CONSTRÓI</span>
                    <div class="brand-subcol d-flex flex-column align-items-start">
                        <span class="brand-subtitle-pill ms-2">Spa &amp; Pool</span>
                        <img src="<?= htmlspecialchars(asset_with_version($asset('/public/imagens/wave.png'))) ?>" alt="Onda decorativa" class="brand-wave img-fluid mt-1" width="120" height="12" loading="lazy">
                    </div>
                </div>
            </a>

            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg p-0 navbar-dark flex-shrink-0">
                <div class="container-fluid px-2 px-md-3">
                    <!-- Hamburger -->
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegação">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="menuPrincipal">
                        <!-- Menu Links -->
                        <ul class="navbar-nav mb-2 mb-lg-0 gap-lg-2 nav-desktop-separators text-uppercase mx-lg-auto">
                            <?php foreach ($menuItems as $item):
                                $href = (string)($item['href'] ?? '#');
                                $label = (string)($item['label'] ?? '');
                                $resolvedHref = $url_for($href);
                                $isServicos = (mb_strtolower($label, 'UTF-8') === 'serviços' || mb_strtolower($label, 'UTF-8') === 'servicos');
                                // Active rules
                                $active = '';
                                if ($isServicos) {
                                    $servicesPaths = ['/servicos.php','/views/servicos_view.php','/views/servico_construcao_view.php','/views/servico_assistencia_view.php','/views/servico_manutencao_view.php','/views/servico_tela_view.php','/views/servico_acompanhamento_view.php','/views/servico_civil_view.php'];
                                    foreach ($servicesPaths as $sp) {
                                        if (str_starts_with($currentPath, rtrim($projectBaseUrl,'/') . $sp) || str_starts_with($currentPath, $sp)) { $active = 'active'; break; }
                                    }
                                } else {
                                    $active = ($href !== '#' && (str_starts_with($currentPath, $href) || str_starts_with($currentPath, $resolvedHref))) ? 'active' : '';
                                }

                                if ($isServicos):
                                    // tenta carregar subitens da BD somente se existir $conn válido
                                    $servicosChildren = [];
                                    if (function_exists('get_menu_children_by_label') && isset($conn) && $conn instanceof mysqli) {
                                        $servicosChildren = get_menu_children_by_label($conn, 'Serviços', 'principal');
                                    }
                                                                        if (!$servicosChildren) {
                                                                                $servicosChildren = [
                                                                                        ['label' => 'Construção de Piscinas', 'href' => '/views/servico_construcao_view.php'],
                                                                                        ['label' => 'Assistência Técnica', 'href' => '/views/servico_assistencia_view.php'],
                                                                                        ['label' => 'Manutenção', 'href' => '/views/servico_manutencao_view.php'],
                                                                                        ['label' => 'Colocação em Tela Armada', 'href' => '/views/servico_tela_view.php'],
                                                                                        ['label' => 'Acompanhamento em Obra', 'href' => '/views/servico_acompanhamento_view.php'],
                                                                                        ['label' => 'Construção Civil', 'href' => '/views/servico_civil_view.php'],
                                                                                ];
                                                                        }
                                                                ?>
                                    <li class="nav-item dropdown hover-dropdown">
                                                                            <a class="nav-link dropdown-toggle <?=$active?>" href="<?= htmlspecialchars($url_for('/servicos.php')) ?>" id="navServicos" role="button" data-bs-toggle="dropdown" aria-expanded="false" <?= $active? 'aria-current="page"':''; ?>>
                                        Serviços
                                      </a>
                                      <ul class="dropdown-menu dropdown-menu-services fade" aria-labelledby="navServicos">
                                                                                <?php foreach ($servicosChildren as $sc): ?>
                                                                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($url_for($sc['href'])) ?>"><?= htmlspecialchars($sc['label']) ?></a></li>
                                                                                <?php endforeach; ?>
                                      </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?=$active?>" href="<?= htmlspecialchars($resolvedHref) ?>" <?= $active? 'aria-current="page"':''; ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>

                        <!-- User Section (mobile/tablet inside collapse) -->
                        <div class="user-controls d-flex d-lg-none justify-content-end align-items-center gap-2 mt-2 w-100">
                            <?php if ($user): ?>
                                <?php
                                $avatar = $user['avatar'] ?? '';
                                $avatarSrc = $avatar !== '' ? $avatar : 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" rx="40" fill="%232D8AC8"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Poppins, Arial" font-size="28" fill="white">U</text></svg>');
                                ?>
                                <div class="user-block d-flex flex-column align-items-center">
                                    <img src="<?=htmlspecialchars($avatarSrc)?>" class="rounded-circle user-avatar" alt="Avatar do utilizador" width="40" height="40">
                                    <div class="user-name small mt-1 text-center"><?=htmlspecialchars($user['name'] ?? 'Utilizador')?> </div>
                                </div>
                                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a class="btn btn-warning btn-sm" href="<?= ADMIN_DASHBOARD ?>">Backoffice</a>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($url_for('/views/perfil_view.php')) ?>" class="btn btn-outline-light btn-sm">Perfil</a>
                                <a href="<?= htmlspecialchars($url_for('/logout.php')) ?>" class="btn btn-outline-light btn-sm">Sair</a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($url_for('/views/login_view.php')) ?>" class="btn btn-outline-light btn-sm">Entrar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <button id="csp-theme-toggle" class="btn btn-outline-light btn-sm csp-btn ms-3" type="button" aria-label="Alternar tema">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <!-- User Section (desktop outside collapse, keeps nav centered) -->
                <div class="d-none d-lg-flex align-items-center gap-3 user-section user-section-desktop pe-3">
                    <?php if ($user): ?>
                        <?php
                        $avatar = $user['avatar'] ?? '';
                        $avatarSrc = $avatar !== '' ? $avatar : 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" rx="40" fill="%232D8AC8"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Poppins, Arial" font-size="28" fill="white">U</text></svg>');
                        ?>
                        <div class="user-block d-flex flex-column align-items-center">
                            <img src="<?=htmlspecialchars($avatarSrc)?>" class="rounded-circle user-avatar" alt="Avatar do utilizador" width="40" height="40">
                            <div class="user-name small mt-1 text-center"><?=htmlspecialchars($user['name'] ?? 'Utilizador')?> </div>
                        </div>
                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a class="btn btn-warning btn-sm text-dark" href="<?= ADMIN_DASHBOARD ?>">Backoffice</a>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($url_for('/views/perfil_view.php')) ?>" class="btn btn-outline-light btn-sm">Perfil</a>
                        <a href="<?= htmlspecialchars($url_for('/logout.php')) ?>" class="btn btn-outline-light btn-sm">Terminar sessão</a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url_for('/views/login_view.php')) ?>" class="btn btn-light btn-sm text-primary fw-semibold">Login/Registo</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
</header>
<script>
(function(){
  function init(){
    // Ripple effect for .csp-btn elements
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.csp-btn'); if(!btn) return;
      var rect = btn.getBoundingClientRect();
      btn.style.setProperty('--x', (e.clientX - rect.left) + 'px');
      btn.style.setProperty('--y', (e.clientY - rect.top) + 'px');
    });

    // Reveal (IntersectionObserver)
    var revealEls = document.querySelectorAll('.csp-reveal');
    if('IntersectionObserver' in window && revealEls.length){
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){ if(entry.isIntersecting){ entry.target.classList.add('is-visible'); } });
      }, { threshold: .15 });
      revealEls.forEach(function(el){ io.observe(el); });
    } else {
      revealEls.forEach(function(el){ el.classList.add('is-visible'); });
    }
  }
  if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', init); }
  else { init(); }
})();
</script>
