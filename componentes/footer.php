<?php
declare(strict_types=1);
/**
 * Footer component to close body/html and include shared scripts
 */
?>

<!-- Bootstrap JS -->
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
// Base URL helper (igual ao utilizado no header)
if (!function_exists('str_starts_with')) {
	function str_starts_with($haystack, $needle) {
		return $needle === '' ? true : strpos($haystack, $needle) === 0;
	}
}
$docRootFs = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
$projectRootFs = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
$projectRootFs = rtrim($projectRootFs, '/');
$projectBaseUrl = '';
if ($docRootFs && str_starts_with($projectRootFs, $docRootFs)) {
	$projectBaseUrl = substr($projectRootFs, strlen($docRootFs));
	$projectBaseUrl = '/' . ltrim($projectBaseUrl, '/');
}
$asset = function (string $path) use ($projectBaseUrl): string {
	$base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : rtrim($projectBaseUrl, '/');
	return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
};
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
?>
<footer class="csp-footer site-footer mt-auto">
	<div class="container">
		<div class="footer-row">
			<div class="footer-social">
				<span class="label">Siga-nos nas redes sociais</span>
				<a class="social-icon" href="https://web.facebook.com/Constroi-SP" target="_blank" rel="noopener" aria-label="Facebook">
					<i class="bi bi-facebook fs-5"></i>
				</a>
				<a class="social-icon" href="https://www.instagram.com/constroi_sp" target="_blank" rel="noopener" aria-label="Instagram">
					<i class="bi bi-instagram fs-5"></i>
				</a>
			</div>
			<div class="footer-logos">
				<img src="<?= htmlspecialchars(asset_url('/public/imagens/ralc.png')) ?>" alt="RALC">
				<img src="<?= htmlspecialchars(asset_url('/public/imagens/livro_reclamacoes.png')) ?>" alt="Livro de Reclamações">
			</div>
		</div>
		<p class="footer-legal small mb-0 mt-3">&copy; <?= date('Y') ?> Constrói Spa &amp; Pool</p>
	</div>
</footer>

<!-- Cookie banner (ativo) -->
<div id="cookie-banner" class="position-fixed bottom-0 start-0 end-0 p-3 cookie-banner" style="z-index: 1080;">
	<div class="alert alert-light border shadow-sm d-flex align-items-center justify-content-between gap-3 mb-0" role="region" aria-label="Aviso de cookies">
		<div class="me-2">
			<strong>Cookies:</strong> Utilizamos cookies para melhorar a sua experiência. <!-- função pendente para gestão de consentimento -->
		</div>
		<!-- dentro do banner de cookies -->
		<div class="d-flex gap-2">
		  <button id="csp-accept" class="btn btn-primary btn-sm">Aceitar</button>
		  <a id="csp-more" href="/politica-cookies.php" class="btn btn-outline-secondary btn-sm">Saber mais</a>
		</div>
	</div>
	<style>
		@media (max-width: 576px) { .alert[role="region"] { font-size: .9rem; } }
	</style>
</div>

<!-- App JS -->
<script defer src="<?= htmlspecialchars(asset_with_version($asset('/public/js/app.min.js'))) ?>"></script>
<!-- Forms behaviors -->
<script defer src="<?= htmlspecialchars(asset_with_version($asset('/public/js/forms.min.js'))) ?>"></script>

<!-- Optional Schema: Organization -->
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "Organization",
	"name": "Constrói Spa & Pool",
	"url": "<?= htmlspecialchars(asset_url('/')) ?>",
	"logo": "<?= htmlspecialchars(asset_url('/public/imagens/logo_no_text.png')) ?>",
	"sameAs": [
		"https://web.facebook.com/Constroi-SP",
		"https://www.instagram.com/constroi_sp"
	]
}
</script>

<!-- Cookie consent logic (guardar/ocultar) -->
<script>
(function(){
	var KEY='csp_cookie_consent';
	var bar=document.getElementById('cookie-banner');
	if(!bar) return;

	try{ if(localStorage.getItem(KEY)==='1'){ bar.style.display='none'; return; } }catch(e){}

	var accept=document.getElementById('csp-accept');
	if(accept){ accept.addEventListener('click',function(){
		try{ localStorage.setItem(KEY,'1'); }catch(e){}
		bar.style.display='none';
	});}
})();
</script>

<!-- Tema (toggle consistente no fim do body) -->
<script>
(function(){
	var KEY='csp_theme';
	var root=document.documentElement;
	var headerEl=document.querySelector('.header-gradient');
	function applyTheme(mode){
		var t=(mode==='dark')?'dark':'light';
		root.setAttribute('data-theme',t);
		root.classList.toggle('theme-dark',t==='dark');
		if(headerEl){
			headerEl.classList.toggle('dark', t==='dark');
			headerEl.classList.toggle('light', t!=='dark');
		}
		try{ localStorage.setItem(KEY,t); }catch(e){}
	}
	var saved; try{ saved=localStorage.getItem(KEY); }catch(e){}
	applyTheme(saved|| (root.getAttribute('data-theme')==='dark'?'dark':'light'));
	var btn=document.getElementById('csp-theme-toggle');
	if(btn){ btn.addEventListener('click',function(){
		applyTheme(root.getAttribute('data-theme')==='dark'?'light':'dark');
	});}
})();
</script>
</body>
</html>
