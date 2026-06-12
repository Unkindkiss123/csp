<?php
// Variáveis esperadas ANTES do include:
// $tituloPagina (string), $subtituloPagina (string opcional), $pageClass (ex.: 'produtos','servicos','contactos','perfil','home')
?>
<section class="page-hero csp-reveal <?= isset($pageClass)? htmlspecialchars($pageClass) : '' ?>">
  <div class="container">
    <?php if (!empty($tituloPagina)): ?>
      <h1 class="display-5 fw-bold mb-1"><?= htmlspecialchars($tituloPagina) ?></h1>
    <?php endif; ?>
    <?php if (isset($breadcrumb) && is_array($breadcrumb)) { include __DIR__.'/breadcrumbs.php'; } ?>
    <?php if (!empty($subtituloPagina)): ?>
      <p class="lead mb-0"><?= htmlspecialchars($subtituloPagina) ?></p>
    <?php endif; ?>
  </div>
  </section>
