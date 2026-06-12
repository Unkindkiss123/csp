<?php
declare(strict_types=1);

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function csrf_field(): void {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_validate(): void {
    $ok = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token']);
    if (!$ok) {
        http_response_code(400);
        exit('CSRF inválido');
    }
}

// --- Compatibilidade com aliases anteriores usados no projeto ---
// Se alguma parte do código chamar estes wrappers, redirecionar para novas funções
if (!function_exists('gerar_token_csrf')) {
    function gerar_token_csrf(): void { csrf_field(); }
}
if (!function_exists('validar_token_csrf')) {
    function validar_token_csrf(): void { csrf_validate(); }
}
// Aliases legados diretos
if (!function_exists('gerarCSRF')) {
    function gerarCSRF(): void { csrf_field(); }
}
if (!function_exists('validarCSRF')) {
    function validarCSRF(): void { csrf_validate(); }
}
if (!function_exists('validate_csrf')) {
    function validate_csrf(string $token): bool {
        $sessionToken = isset($_SESSION['csrf_token']) ? (string)$_SESSION['csrf_token'] : '';
        return ($token !== '' && $sessionToken !== '' && hash_equals($sessionToken, $token));
    }
}
