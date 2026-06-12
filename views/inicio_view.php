<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';
$page_title = 'Início | Constrói Spa & Pool';
$page_description = 'Construção de piscinas, assistência técnica e manutenção em Vidigueira e região.';
$breadcrumb = [
  ['label'=>'Início','href'=>'/'],
  ['label'=>'Início (simples)','href'=>null]
];
// Carrega menu da BD
$menuItems = get_menu_items($conn, 'principal');
// Página inicial simples para visualizar o header
include __DIR__ . '/../componentes/header.php';
?>

<main class="container my-4 pt-3 pb-5" id="conteudo">
  <div class="text-dark">
    <div class="p-4 p-md-5 bg-white shadow-sm rounded-3">
      <h1 class="h3 mb-3 text-primary">Bem-vindo à Constrói Spa & Pool</h1>
      <p class="mb-3">Esta é uma página de exemplo para validares o header responsivo e o tema visual.</p>
  <a href="<?= htmlspecialchars($url_for('/servicos.php')) ?>" class="btn btn-primary">Ver serviços</a>
    </div>
  </div>
  <div class="py-4"></div>
</main>

<?php include __DIR__ . '/../componentes/footer.php'; ?>
