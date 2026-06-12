<?php
require_once __DIR__ . '/_admin_guard.php';
require_once __DIR__ . '/../includes/helpers.php';
// Rota canónica para edição de produto no backoffice
// A view já inclui header/footer; para evitar duplicação, apenas incluímos a view.
require_once __DIR__ . '/../views/admin/produto_editar.php';
