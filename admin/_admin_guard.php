<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

// Todos os ficheiros do backoffice devem incluir este guard no topo.
// Ex.: require_once __DIR__ . '/_admin_guard.php';
require_login('editor'); // mínimo editor por omissão; altera para 'admin' onde for crítico
