<?php
// Base de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
// Usa a tua BD existente (ajusta se necessário em phpMyAdmin)
define('DB_NAME', 'constroi_spa_pool');

// Deteção de ambiente e reporting de erros
if (!defined('IS_LOCAL')) {
	define('IS_LOCAL', strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);
}
error_reporting(IS_LOCAL ? E_ALL : 0);
ini_set('display_errors', IS_LOCAL ? '1' : '0');

// APP_ENV compatível com prompts (local|production)
if (!defined('APP_ENV')) {
	define('APP_ENV', IS_LOCAL ? 'local' : 'production');
}

// Base URL para gerar links absolutos no domínio final
if (!defined('BASE_URL')) {
    define('BASE_URL', IS_LOCAL ? '/constroi_spa_and_pool' : 'https://www.constroi-spa-pool.pt');
}

// Admin dashboard absolute path helper
if (!defined('ADMIN_DASHBOARD')) {
	define('ADMIN_DASHBOARD', BASE_URL . '/views/admin/admin_dashboard.php');
}

// Sessões e cookies (aplicar antes de iniciar a sessão)
if (session_status() === PHP_SESSION_NONE) {
	ini_set('session.cookie_httponly', '1');
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') { ini_set('session.cookie_secure', '1'); }
	ini_set('session.cookie_samesite', 'Lax');
	// Extra: reforço de segurança de sessão
	ini_set('session.use_strict_mode', '1');
}

// Tentativas de login (segurança)
define('LOGIN_MAX_TENTATIVAS', 5);
define('LOGIN_JANELA_SEGUNDOS', 300); // 5 minutos

// Rate limit suave para atualização de perfil (segundos)
if (!defined('PROFILE_RATE_LIMIT_SECONDS')) {
	define('PROFILE_RATE_LIMIT_SECONDS', 5);
}

// Versão de assets para cache-busting
if (!defined('ASSETS_VERSION')) {
	// Bump this when static assets change to force cache refresh
	define('ASSETS_VERSION', '11');
}

// Helper para anexar versão a assets
if (!function_exists('asset_with_version')) {
	function asset_with_version(string $urlPath): string {
		$sep = (strpos($urlPath, '?') !== false) ? '&' : '?';
		return $urlPath . $sep . 'v=' . rawurlencode(ASSETS_VERSION);
	}
}

// Google reCAPTCHA v2 (Checkbox)
if (!defined('RECAPTCHA_SITE_KEY')) {
	$site = getenv('RECAPTCHA_SITE_KEY') ?: '';
	if ($site === '' && IS_LOCAL) {
		// Google public test key (safe for dev only)
		$site = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
	}
	define('RECAPTCHA_SITE_KEY', $site);
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
	$secret = getenv('RECAPTCHA_SECRET_KEY') ?: '';
	if ($secret === '' && IS_LOCAL) {
		// Google public test secret (safe for dev only)
		$secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
	}
	define('RECAPTCHA_SECRET_KEY', $secret);
}

// Email (remetente) – opcional, usado quando envio real estiver ativo
if (!defined('MAIL_FROM_EMAIL')) {
	define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: '');
}
if (!defined('MAIL_FROM_NAME')) {
	define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: '');
}

// Mail driver e SMTP (se necessário)
if (!defined('MAIL_DRIVER')) {
	// none | mail | smtp
	$drv = getenv('MAIL_DRIVER') ?: (IS_LOCAL ? 'none' : 'mail');
	define('MAIL_DRIVER', $drv);
}
if (!defined('SMTP_HOST')) {
	define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
}
if (!defined('SMTP_PORT')) {
	define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
}
if (!defined('SMTP_USER')) {
	define('SMTP_USER', getenv('SMTP_USER') ?: '');
}
if (!defined('SMTP_PASS')) {
	define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
}
if (!defined('SMTP_SECURE')) {
	// tls | ssl | empty
	define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
}
if (!defined('SMTP_AUTH')) {
	$authEnv = getenv('SMTP_AUTH');
	$auth = ($authEnv === false || $authEnv === '') ? true : (bool)$authEnv;
	define('SMTP_AUTH', $auth);
}

