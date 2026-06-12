<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/recaptcha.php';
$page_title = 'Contactos | Constrói Spa & Pool';
$page_description = 'Fala connosco para pedidos de orçamento, assistência e informações sobre serviços.';
$breadcrumb = [
  ['label'=>'Início','href'=>'/'],
  ['label'=>'Contactos','href'=>null]
];
$menuItems = get_menu_items($conn, 'principal');
include __DIR__ . '/../componentes/header.php';
?>
<?php $tituloPagina='Contactos'; $subtituloPagina='Fale connosco'; $pageClass='contactos'; include __DIR__.'/../componentes/page_hero.php'; ?>

<main class="container my-4 pt-0 pb-5" id="conteudo">
  <div class="text-dark">
    <div class="p-4 p-md-5 bg-white shadow-sm rounded-3">
      <h1 class="h3 mb-3 text-primary">Contactos</h1>
      <p class="mb-3">Fala connosco: estamos aqui para ajudar.</p>
      <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success">Enviado com sucesso. Obrigado!</div>
      <?php elseif(isset($_GET['err'])): ?>
        <?php
          $map = [
            'val' => 'Por favor verifica os campos obrigatórios.',
            'csrf' => 'Sessão expirada. Atualiza a página e tenta de novo.',
            'captcha' => 'Validação reCAPTCHA falhou.',
            'db' => 'Ocorreu um erro ao gravar. Tenta mais tarde.'
          ];
        ?>
        <div class="alert alert-danger"><?= h($map[$_GET['err']] ?? 'Erro ao enviar.') ?></div>
      <?php endif; ?>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <h2 class="h6 text-primary">Informações de contacto</h2>
            <ul class="mb-0 text-muted">
              <li>Email: info@constroi-spa-pool.test</li>
              <li>Telefone: (+351) 900 000 000</li>
              <li>Horário: 09:00–18:00 (dias úteis)</li>
            </ul>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <h2 class="h6 text-primary">Envia-nos uma mensagem</h2>
            <form method="post" action="<?= url('post_lead.php') ?>" class="row g-3" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="source" value="contacto">
              <input type="text" name="website" value="" autocomplete="off" style="position:absolute;left:-9999px;top:-9999px">
              <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input class="form-control" name="nome" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Telefone (opcional)</label>
                <input type="tel" class="form-control" name="telefone">
              </div>
              <div class="col-md-6">
                <label class="form-label">Assunto</label>
                <input type="text" class="form-control" name="assunto">
              </div>
              <div class="col-12">
                <label class="form-label">Mensagem</label>
                <textarea class="form-control" name="mensagem" rows="4" required></textarea>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="consent" id="consentCtt" value="1" required>
                  <label class="form-check-label" for="consentCtt">Concordo com o tratamento dos meus dados segundo a Política de Privacidade.</label>
                </div>
              </div>
              <?php if(defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY): ?>
              <div class="col-12">
                <div class="g-recaptcha" data-sitekey="<?= h(RECAPTCHA_SITE_KEY) ?>"></div>
              </div>
              <script src="https://www.google.com/recaptcha/api.js" async defer></script>
              <?php endif; ?>
              <div class="col-12 d-grid d-md-inline">
                <button class="btn btn-primary">Enviar</button>
              </div>
            </form>
            <?php if(isset($_GET['ok'])): ?>
              <div class="alert alert-success mt-3">Mensagem enviada com sucesso!</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="row g-3 mt-2">
        <div class="col-12">
          <div class="ratio ratio-16x9 bg-light border rounded-3 d-flex align-items-center justify-content-center">
            <span class="text-muted">Mapa / Google Maps (placeholder visual)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php // script já injetado condicionalmente acima quando RECAPTCHA_SITE_KEY estiver configurada ?>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
