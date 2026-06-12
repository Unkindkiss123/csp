<?php
declare(strict_types=1);
// Carregar config primeiro para aplicar ini de sessão antes de iniciar
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php'; // usar implementações oficiais de CSRF (não redefinir aqui)
if (session_status() === PHP_SESSION_NONE) session_start();

// Guardar alertas em sessão para renderizar na view seguinte
function alert(string $type, string $msg): void {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $_SESSION['alert'] = ['type' => $type, 'msg' => (string)$msg];
}

// Alias compatível com chamadas existentes noutras partes do projeto
function set_alert(string $type, string $msg): void { alert($type, $msg); }

// Nota: funções de CSRF foram movidas para includes/csrf.php para evitar duplicações.

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ----- Estados de leads: helpers de classe e label -----
if (!function_exists('estado_class')) {
  function estado_class(string $estado): string {
    $map = [
      'novo' => 'est-novo',
      'triagem' => 'est-triagem',
      'aguarda_cliente' => 'est-aguarda',
      'orcamento_enviado' => 'est-enviado',
      'aceite' => 'est-aceite',
      'rejeitado' => 'est-rejeitado',
      'arquivado' => 'est-arquivado',
    ];
    return $map[$estado] ?? 'est-novo';
  }
}
if (!function_exists('estado_label')) {
  function estado_label(string $estado): string {
    return ucwords(str_replace('_', ' ', $estado));
  }
}

// IP real simples (para o bloqueio)
function cliente_ip(): string {
  $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'];
  foreach ($keys as $k) {
    if (!empty($_SERVER[$k])) {
      $ip = explode(',', $_SERVER[$k])[0];
      return trim($ip);
    }
  }
  return '0.0.0.0';
}

/** Redirect helper that respects the project base URL (works under subfolder like /constroi_spa_and_pool) */
function site_redirect(string $path): void {
  // Accept full URLs
  if (preg_match('#^https?://#i', $path)) {
    header('Location: ' . $path);
    exit;
  }
  // Build absolute site URL using existing helper
  if (!function_exists('asset_url')) {
    // Fallback minimal: try to compute base 
    $docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
    $projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
    $projectRootFs = rtrim($projectRootFs, '/');
    $base = '';
    if ($docRootFs && strpos($projectRootFs, $docRootFs) === 0) {
      $base = substr($projectRootFs, strlen($docRootFs));
      $base = '/' . ltrim($base, '/');
    }
    $url = rtrim($base, '/') . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
  }
  $url = asset_url($path);
  header('Location: ' . $url);
  exit;
}

/**
 * Carrega itens de menu a partir da tabela nav_menu.
 * Normaliza os hrefs removendo prefixos de pasta do projeto (ex.: /constroi_spa_pool)
 * para que o header os resolva com o base path correto.
 */
function get_menu_items(mysqli $conn, string $siteKey = 'principal'): array {
  $stmt = $conn->prepare("SELECT label, href FROM nav_menu WHERE site_key=? AND is_visible=1 AND is_active=1 AND parent_id IS NULL ORDER BY ordem ASC");
  $stmt->bind_param('s', $siteKey);
  $stmt->execute();
  $res = $stmt->get_result();
  $items = [];

  // Compute project base url (same logic as header component)
  $docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
  $projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
  $projectRootFs = rtrim($projectRootFs, '/');
  $projectBaseUrl = '';
  if ($docRootFs && strpos($projectRootFs, $docRootFs) === 0) {
    $projectBaseUrl = substr($projectRootFs, strlen($docRootFs));
    $projectBaseUrl = '/' . ltrim($projectBaseUrl, '/');
  }

  while ($row = $res->fetch_assoc()) {
    $label = (string)($row['label'] ?? '');
    $href = (string)($row['href'] ?? '#');
    // Keep absolute external URLs
    if (!preg_match('#^https?://#i', $href)) {
      // If href starts with the project base url (e.g., /constroi_spa_and_pool), strip that prefix only
      if ($projectBaseUrl !== '' && strpos($href, $projectBaseUrl . '/') === 0) {
        $href = substr($href, strlen($projectBaseUrl));
      }
      // Ensure leading slash for site-relative paths, but DON'T strip valid first segments like '/views'
      if ($href === '' || $href[0] !== '/') {
        $href = '/' . ltrim($href, '/');
      }
    }
    $items[] = ['label' => $label, 'href' => $href];
  }
  $stmt->close();
  return $items;
}

/**
 * Carrega subitens (children) do menu a partir de um rótulo de pai.
 * Útil para dropdowns como "Serviços".
 */
function get_menu_children_by_label(mysqli $conn, string $parentLabel, string $siteKey = 'principal'): array {
  // Encontra o ID do item pai
  $stmt = $conn->prepare("SELECT id FROM nav_menu WHERE site_key=? AND label=? AND is_visible=1 AND is_active=1 LIMIT 1");
  $stmt->bind_param('ss', $siteKey, $parentLabel);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();
  if (!$row || empty($row['id'])) return [];
  $parentId = (int)$row['id'];

  $stmt = $conn->prepare("SELECT label, href FROM nav_menu WHERE site_key=? AND is_visible=1 AND is_active=1 AND parent_id = ? ORDER BY ordem ASC, id ASC");
  $stmt->bind_param('si', $siteKey, $parentId);
  $stmt->execute();
  $res = $stmt->get_result();

  // Compute project base url (mesma lógica do get_menu_items)
  $docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
  $projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
  $projectRootFs = rtrim($projectRootFs, '/');
  $projectBaseUrl = '';
  if ($docRootFs && strpos($projectRootFs, $docRootFs) === 0) {
    $projectBaseUrl = substr($projectRootFs, strlen($docRootFs));
    $projectBaseUrl = '/' . ltrim($projectBaseUrl, '/');
  }

  $items = [];
  while ($row = $res->fetch_assoc()) {
    $label = (string)($row['label'] ?? '');
    $href = (string)($row['href'] ?? '#');
    if (!preg_match('#^https?://#i', $href)) {
      if ($projectBaseUrl !== '' && strpos($href, $projectBaseUrl . '/') === 0) {
        $href = substr($href, strlen($projectBaseUrl));
      }
      if ($href === '' || $href[0] !== '/') {
        $href = '/' . ltrim($href, '/');
      }
    }
    $items[] = ['label' => $label, 'href' => $href];
  }
  $stmt->close();
  return $items;
}

// ---------- Assets e imagens responsivas ----------
/** Retorna prefixo base URL do projeto (ex.: /constroi_spa_and_pool) e caminho FS da raiz do projeto */
function _project_paths(): array {
  $docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
  $projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
  $projectRootFs = rtrim($projectRootFs, '/');
  $projectBaseUrl = '';
  if ($docRootFs && strpos($projectRootFs, $docRootFs) === 0) {
    $projectBaseUrl = substr($projectRootFs, strlen($docRootFs));
    $projectBaseUrl = '/' . ltrim($projectBaseUrl, '/');
  }
  return [$projectBaseUrl, $projectRootFs];
}

/** Constrói URL absoluto do site para um caminho (que pode vir relativo ou absoluto) */
function asset_url(string $path): string {
  if (preg_match('#^https?://#i', $path)) return $path;
  $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
  // BASE_URL pode ser absoluto (https://...) ou relativo (/subpasta)
  if ($path !== '' && $path[0] === '/') {
    return ($base === '' ? '' : $base) . $path;
  }
  return ($base === '' ? '' : $base . '/') . ltrim($path, '/');
}

// Alias conveniente para construir URLs absolutas no site (equivalente a $url_for do header)
function url_for(string $path): string { return asset_url($path); }

// Helper único e curto para construir URLs do site
if (!function_exists('url')) {
  function url(string $path = ''): string {
    return asset_url('/' . ltrim($path, '/'));
  }
}

/** Converte path do site (começado em /) para filesystem */
function site_to_fs(string $sitePath): string {
  [, $rootFs] = _project_paths();
  $sitePath = '/' . ltrim($sitePath, '/');
  return $rootFs . $sitePath;
}

/**
 * Renderiza <picture> com fallback <img> e <source type="image/webp"> se existir.
 * Aceita também candidatos de srcset se ficheiros com sufixos -300w, -600w, -1200w existirem.
 */
function render_picture(string $sitePath, string $alt = '', string $class = 'img-fluid', string $loading = 'lazy', string $sizes = ''): string {
  // Normalizar para site-root
  if (!preg_match('#^/|^https?://#i', $sitePath)) {
    $sitePath = '/' . ltrim($sitePath, '/');
  }
  // Extensão
  $dotPos = strrpos($sitePath, '.');
  $baseNoExt = $dotPos !== false ? substr($sitePath, 0, $dotPos) : $sitePath;
  $ext = $dotPos !== false ? strtolower(substr($sitePath, $dotPos+1)) : '';
  $isRaster = in_array($ext, ['jpg','jpeg','png','webp'], true);
  if (!$isRaster) {
    // fallback simples
    $imgUrl = asset_url($sitePath);
    return '<img src="' . h($imgUrl) . '" alt="' . h($alt) . '" class="' . h($class) . '"' . ($loading? ' loading="' . h($loading) . '"':'') . ' />';
  }

  // Candidatos de tamanho
  $cands = [300, 600, 1200];
  $srcsetJpeg = [];
  foreach ($cands as $w) {
    $cand = $baseNoExt . '-' . $w . 'w.' . $ext;
    if (file_exists(site_to_fs($cand))) {
      $srcsetJpeg[] = asset_url($cand) . ' ' . $w . 'w';
    }
  }
  $srcJpeg = asset_url($sitePath);
  $srcsetJpeg = $srcsetJpeg ? implode(', ', $srcsetJpeg) : '';

  // WebP
  $webpBase = $baseNoExt . '.webp';
  $srcsetWebp = [];
  foreach ($cands as $w) {
    $cand = $baseNoExt . '-' . $w . 'w.webp';
    if (file_exists(site_to_fs($cand))) {
      $srcsetWebp[] = asset_url($cand) . ' ' . $w . 'w';
    }
  }
  $hasWebp = file_exists(site_to_fs($webpBase)) || !empty($srcsetWebp);
  $srcWebp = $hasWebp && file_exists(site_to_fs($webpBase)) ? asset_url($webpBase) : '';
  $srcsetWebp = $srcsetWebp ? implode(', ', $srcsetWebp) : '';

  if ($sizes === '') {
    $sizes = '(max-width: 576px) 100vw, (max-width: 992px) 50vw, 33vw';
  }

  $html = '<picture>';
  if ($hasWebp) {
    $html .= '<source type="image/webp" ' . ($srcsetWebp ? ('srcset="' . h($srcsetWebp) . '" sizes="' . h($sizes) . '"') : ('srcset="' . h($srcWebp) . '"')) . ' />';
  }
  $imgAttrs = 'src="' . h($srcJpeg) . '" alt="' . h($alt) . '" class="' . h($class) . '"';
  if ($loading) $imgAttrs .= ' loading="' . h($loading) . '"';
  if ($srcsetJpeg) $imgAttrs .= ' srcset="' . h($srcsetJpeg) . '" sizes="' . h($sizes) . '"';
  $html .= '<img ' . $imgAttrs . ' />';
  $html .= '</picture>';
  return $html;
}

/**
 * Processa upload de imagem com segurança e retorna resultado padronizado.
 * - Valida MIME real e tamanho (máx. 3MB)
 * - Gera nome aleatório seguro
 * - Cria diretório de destino se não existir
 * - (Opcional) Gera versão .webp quando suportado
 * Retorno: ['sucesso' => bool, 'ficheiro' => ?string, 'erro' => ?string]
 */
function processarImagem(array $ficheiro, string $subpasta = 'produtos'): array {
  $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  $max_tamanho = 3 * 1024 * 1024; // 3MB
  $resultado = ['sucesso' => false, 'ficheiro' => null, 'erro' => null];

  // Verifica se foi enviado ficheiro válido via HTTP POST
  $errCode = $ficheiro['error'] ?? UPLOAD_ERR_NO_FILE;
  if (empty($ficheiro['tmp_name']) || $errCode !== UPLOAD_ERR_OK || !is_uploaded_file($ficheiro['tmp_name'])) {
    $resultado['erro'] = 'Nenhum ficheiro enviado.';
    return $resultado;
  }

  // Valida tamanho
  $size = (int)($ficheiro['size'] ?? 0);
  if ($size <= 0 || $size > $max_tamanho) {
    $resultado['erro'] = 'Ficheiro demasiado grande (máx. 3MB).';
    return $resultado;
  }

  // Descobre MIME real
  $mime_real = null;
  // getimagesize primeiro para confirmar que é imagem
  $imgInfo = @getimagesize($ficheiro['tmp_name']);
  if (is_array($imgInfo) && !empty($imgInfo['mime'])) {
    $mime_real = $imgInfo['mime'];
  }
  if (function_exists('finfo_open')) {
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
      $tmpMime = @finfo_file($finfo, $ficheiro['tmp_name']);
      if ($tmpMime) $mime_real = $tmpMime;
      @finfo_close($finfo);
    }
  }
  if (!$mime_real && isset($ficheiro['type'])) {
    $mime_real = (string)$ficheiro['type'];
  }
  if (!isset($permitidos[$mime_real])) {
    $resultado['erro'] = 'Formato inválido. Apenas JPG, PNG e WEBP.';
    return $resultado;
  }

  // Confirma que é realmente uma imagem
  if (!$imgInfo) {
    $resultado['erro'] = 'O ficheiro enviado não é uma imagem válida.';
    return $resultado;
  }

  // Gera nome seguro com a extensão mapeada (ignora a extensão original)
  $ext = $permitidos[$mime_real];
  $nome_novo = bin2hex(random_bytes(8)) . '.' . $ext;

  // Diretório de destino sob /public/imagens/<subpasta>/
  $baseImagens = realpath(__DIR__ . '/../public/imagens') ?: (__DIR__ . '/../public/imagens');
  $destino_dir = rtrim($baseImagens, '/\\') . DIRECTORY_SEPARATOR . trim($subpasta, '/\\') . DIRECTORY_SEPARATOR;
  if (!is_dir($destino_dir)) {
    @mkdir($destino_dir, 0775, true);
  }
  @chmod($destino_dir, 0775);
  $destino = $destino_dir . $nome_novo;

  // Move o ficheiro
  if (!@move_uploaded_file($ficheiro['tmp_name'], $destino)) {
    $resultado['erro'] = 'Erro ao guardar a imagem.';
    return $resultado;
  }

  // (Opcional) cria .webp ao lado do original se suportado e não for webp
  if ($ext !== 'webp' && function_exists('imagewebp')) {
    $data = @file_get_contents($destino);
    if ($data !== false) {
      $img = @imagecreatefromstring($data);
      if ($img) {
        $webpPath = $destino_dir . pathinfo($nome_novo, PATHINFO_FILENAME) . '.webp';
        @imagewebp($img, $webpPath, 80);
        imagedestroy($img);
      }
    }
  }

  // Caminho site-relative para guardar na BD
  $resultado['sucesso'] = true;
  $resultado['ficheiro'] = '/public/imagens/' . trim($subpasta, '/') . '/' . $nome_novo;
  return $resultado;
}
