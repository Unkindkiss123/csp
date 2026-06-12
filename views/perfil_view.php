<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';

// Carrega dados atuais do utilizador autenticado
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT usuario, email, nome_completo FROM utilizadores WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($usuario, $email, $nome);
$stmt->fetch();
$stmt->close();

$menuItems = get_menu_items($conn, 'principal');
$page_title = 'O meu perfil · Constrói Spa & Pool';
$page_description = 'Atualiza os teus dados de conta com segurança.';
include __DIR__ . '/../componentes/header.php';
?>
<?php 
$breadcrumb = [
  ['label'=>'Início','href'=>'/'],
  ['label'=>'Perfil','href'=>null]
];
$tituloPagina='A minha conta'; $subtituloPagina='Dados, encomendas e segurança'; $pageClass='perfil'; include __DIR__.'/../componentes/page_hero.php'; ?>

<main id="conteudo" class="container my-4">
<div class="py-4">
  <div class="row g-3">
    <div class="col-lg-3">
      <div class="list-group sticky-top" style="top: calc(var(--site-header-h) + 1rem);">
        <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#tab-dados" role="tab">Dados pessoais</a>
        <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-encomendas" role="tab">Encomendas</a>
        <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-historico" role="tab">Histórico</a>
        <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-seguranca" role="tab">Segurança</a>
        <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-pagamento" role="tab">Métodos de pagamento</a>
      </div>
    </div>
    <div class="col-lg-9">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
          <h1 class="h4 mb-2 text-primary">O meu perfil</h1>
          <div class="theme-toggle mt-2">
            <button id="toggleThemeBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
              <span id="themeIcon">🌙</span>
              <span id="themeText">Ativar modo escuro</span>
            </button>
          </div>
          <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?= h($_SESSION['flash_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= h($_SESSION['flash_error']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['alert'])): $a = $_SESSION['alert']; unset($_SESSION['alert']); ?>
            <div class="alert alert-<?= h($a['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
              <?= h($a['msg'] ?? '') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
          <?php endif; ?>
          <div class="tab-content">
            <!-- Dados pessoais -->
            <div class="tab-pane fade show active" id="tab-dados" role="tabpanel">
              <h2 class="h5 mb-3">Dados pessoais</h2>
              <form id="form-perfil" method="post" action="<?= h(url_for('/perfil_update.php')) ?>" class="row g-3">
                <?php gerarCSRF(); ?>
                <div class="col-md-6">
                  <label class="form-label">Utilizador</label>
                  <input class="form-control" value="<?= h($usuario) ?>" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nome completo</label>
                  <input type="text" name="nome_completo" class="form-control" value="<?= h($nome) ?>" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" value="<?= h($email) ?>" required>
                </div>
                <div class="col-12 d-grid d-md-inline">
                  <button class="btn btn-primary">Guardar alterações</button>
                  <a class="btn btn-outline-secondary" href="<?= h(url_for('/views/dashboard_view.php')) ?>">Cancelar</a>
                </div>
              </form>
            </div>

            <!-- Encomendas -->
            <div class="tab-pane fade" id="tab-encomendas" role="tabpanel">
              <h2 class="h5 mb-3">As minhas encomendas</h2>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Data</th>
                      <th>Estado</th>
                      <th>Total</th>
                      <th class="text-end">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>—</td><td>—</td><td><span class="badge bg-secondary">—</span></td><td>€ —</td>
                      <td class="text-end"><button class="btn btn-outline-primary btn-sm" disabled>Ver</button></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- função pendente: listar encomendas do utilizador -->
            </div>

            <!-- Histórico -->
            <div class="tab-pane fade" id="tab-historico" role="tabpanel">
              <h2 class="h5 mb-3">Histórico</h2>
              <p class="text-muted">Não há registos para apresentar. <!-- função pendente -->
              </p>
            </div>

            <!-- Segurança -->
            <div class="tab-pane fade" id="tab-seguranca" role="tabpanel">
              <h2 class="h5 mb-3">Segurança</h2>
              <form id="form-password" method="post" action="<?= h(url_for('/perfil_update.php')) ?>" class="mt-2">
                <?php gerarCSRF(); ?>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Senha atual</label>
                    <div class="input-group">
                      <input type="password" name="password_atual" id="pwAtual" class="form-control" autocomplete="off" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="window.Constroi && Constroi.togglePass('pwAtual', this)" aria-label="Mostrar/ocultar palavra‑passe">Ver</button>
                    </div>
                  </div>
                  <div class="col-md-6"></div>
                  <div class="col-md-6">
                    <label class="form-label">Nova senha</label>
                    <div class="input-group">
                      <input type="password" name="password_nova" id="pwNova" class="form-control" minlength="8" maxlength="64" autocomplete="off" pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_^#()+=]).{8,64}$" title="Mín. 8 caracteres e pelo menos: uma maiúscula, um número e um símbolo" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="window.Constroi && Constroi.togglePass('pwNova', this)" aria-label="Mostrar/ocultar palavra‑passe">Ver</button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Confirmar nova senha</label>
                    <div class="input-group">
                      <input type="password" name="password_nova_confirm" id="pwNova2" class="form-control" minlength="8" maxlength="64" autocomplete="off" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="window.Constroi && Constroi.togglePass('pwNova2', this)" aria-label="Mostrar/ocultar palavra‑passe">Ver</button>
                    </div>
                  </div>
                </div>

                <!-- BLOCO DO CÓDIGO: inicialmente oculto -->
                <div id="bloco-codigo" class="mt-3 d-none">
                  <label class="form-label">Código de confirmação</label>
                  <div class="d-flex gap-2">
                    <input
                      id="codigo-confirmacao"
                      name="codigo_confirmacao"
                      type="text"
                      class="form-control"
                      inputmode="numeric"
                      pattern="[0-9]{6}"
                      minlength="6"
                      maxlength="6"
                      autocomplete="one-time-code"
                      placeholder="6 dígitos"
                      aria-describedby="ajuda-codigo"
                      oninput="this.value = this.value.replace(/\s+/g,'')"
                    />
                    <button type="button" id="btn-reenviar" class="btn btn-outline-secondary">Reenviar (60s)</button>
                  </div>
                  <small id="ajuda-codigo" class="text-muted">Introduz o código recebido por email. Válido por 10 minutos.</small>
                </div>

                <div class="mt-3">
                  <button type="submit" id="btn-password" name="acao" value="alterar_password" class="btn btn-primary">
                    Alterar palavra-passe
                  </button>
                </div>
              </form>
            </div>

            <!-- Pagamento -->
            <div class="tab-pane fade" id="tab-pagamento" role="tabpanel">
              <h2 class="h5 mb-3">Métodos de pagamento</h2>
              <div class="border rounded-3 p-3">
                <p class="text-muted mb-0">Sem métodos adicionados. <!-- função pendente --></p>
              </div>
            </div>
          </div>

          <script>
(function() {
  const BASE_URL = '<?= defined("BASE_URL") ? BASE_URL : "" ?>';
  const form = document.getElementById('form-password');
  const btnSubmit = document.getElementById('btn-password');
  const blocoCodigo = document.getElementById('bloco-codigo');
  const inputCodigo = document.getElementById('codigo-confirmacao');
  const btnReenviar = document.getElementById('btn-reenviar');
  const csrf = document.querySelector('input[name="csrf_token"]');

  let passo2 = false;      // controla se já estamos no passo de confirmação
  let cooldown = false;    // controla cooldown de reenvio

  async function enviarCodigo() {
    if (cooldown) return;
    cooldown = true;
    try {
      const body = new URLSearchParams();
      if (csrf) body.append('csrf_token', csrf.value);
      const res = await fetch(`${BASE_URL}/perfil_enviar_codigo.php`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body
      });
      let data = null;
      const ct = res.headers.get('content-type') || '';
      if (ct.includes('application/json')) {
        try { data = await res.json(); } catch(_) {}
      }
      // revelar bloco do código e preparar passo 2
      blocoCodigo.classList.remove('d-none');
      inputCodigo.setAttribute('required', 'required');
      inputCodigo.focus();
      btnSubmit.textContent = 'Confirmar alteração';
      // Mensagem DEV inline quando disponível
      if (data && data.dev_code) {
        const ajuda = document.getElementById('ajuda-codigo');
        if (ajuda) {
          ajuda.textContent = `Código DEV: ${data.dev_code} (válido 10 min)`;
          ajuda.classList.remove('text-muted');
        }
      }
      // cooldown 60s no botão de reenviar
      let tempo = 60;
      btnReenviar.disabled = true;
      btnReenviar.textContent = `Reenviar (${tempo}s)`;
      const timer = setInterval(() => {
        tempo--;
        btnReenviar.textContent = `Reenviar (${tempo}s)`;
        if (tempo <= 0) {
          clearInterval(timer);
          btnReenviar.disabled = false;
          btnReenviar.textContent = 'Reenviar';
          cooldown = false;
        }
      }, 1000);
      passo2 = true;
    } catch (e) {
      alert('Não foi possível enviar o código agora. Tenta de novo.');
      cooldown = false;
    }
  }

  // 1. Intercetar submit: se ainda não estamos no passo 2, envia código e bloqueia a submissão
  form.addEventListener('submit', function(ev) {
    if (!passo2) {
      ev.preventDefault();
      enviarCodigo();
    } else {
      // passo2: deixa submeter — o backend valida o código
    }
  });

  // 2. Reenviar código manualmente (com cooldown)
  btnReenviar.addEventListener('click', function() {
    if (!cooldown) enviarCodigo();
  });
})();
</script>
<script>
// Alternância de tema (claro/escuro) — escopo da página de perfil
document.addEventListener("DOMContentLoaded", function() {
  const btn = document.getElementById("toggleThemeBtn");
  const html = document.documentElement;
  const icon = document.getElementById("themeIcon");
  const text = document.getElementById("themeText");

  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    html.classList.add("theme-dark");
    icon.textContent = "☀️";
    text.textContent = "Desativar modo escuro";
  }

  if (btn) {
    btn.addEventListener("click", () => {
      html.classList.toggle("theme-dark");
      const isDark = html.classList.contains("theme-dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");
      icon.textContent = isDark ? "☀️" : "🌙";
      text.textContent = isDark ? "Desativar modo escuro" : "Ativar modo escuro";
    });
  }
});
</script>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
