<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/recaptcha.php';
$menuItems = get_menu_items($conn, 'principal');
$page_title = 'Iniciar sessão · Constrói Spa & Pool';
$page_description = 'Acede à tua conta para gerir pedidos e preferências.';
$body_classes = 'auth-page';
include __DIR__ . '/../componentes/header.php';
?>

<section class="bg-gradiente py-4 auth-page">
  <div class="container" style="max-width: 720px;">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4 p-md-5">
        <h1 class="h3 mb-3 text-center text-primary">Iniciar sessão</h1>
        <?php if (!empty($_SESSION['alert'])): ?>
          <div class="alert alert-<?= $_SESSION['alert']['type'] ?> text-center">
            <?= htmlspecialchars($_SESSION['alert']['msg']) ?>
          </div>
        <?php unset($_SESSION['alert']); endif; ?>
        <?php if (!empty($_GET['out'])): ?>
          <div class="alert alert-info text-center">Terminaste a sessão. Por favor, volta a iniciar.</div>
        <?php endif; ?>
        <p class="text-center text-muted mb-4">Acede à tua conta da Constrói Spa &amp; Pool</p>

  <form method="POST" action="../login.php" novalidate>
          <?php gerarCSRF(); ?>

          <div class="mb-3">
            <input type="text" class="form-control required-hover" name="usuario" placeholder="Utilizador ou Email" aria-label="Utilizador ou Email" title="Utilizador ou Email (aceita ambos)" required autofocus>
          </div>

          <div class="mb-3">
            <div class="input-group">
              <input type="password" class="form-control required-hover" name="senha" id="loginSenha" placeholder="Senha" aria-label="Senha" minlength="8" maxlength="64" pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_^#()+=]).{8,64}$" title="Mín. 8 caracteres e pelo menos: uma maiúscula, um número e um símbolo" required autocomplete="off">
              <button class="btn btn-outline-secondary" type="button" onclick="Constroi.togglePass('loginSenha', this)">Mostrar</button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="keepLogged">
              <label class="form-check-label" for="keepLogged">Manter sessão iniciada</label>
            </div>
            <a class="small" href="./recuperar_view.php">Esqueci a senha</a>
          </div>

          <?php if (recaptcha_is_configured()): ?>
            <div class="mb-3">
              <div class="g-recaptcha" data-sitekey="<?= h(RECAPTCHA_SITE_KEY) ?>"></div>
            </div>
          <?php endif; ?>

          <div class="d-grid gap-2">
            <button class="btn btn-primary" type="submit">Entrar</button>
            <a href="./register_view.php" class="btn btn-success">Criar conta</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php if (recaptcha_is_configured()): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
