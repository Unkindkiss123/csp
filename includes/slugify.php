<?php
declare(strict_types=1);

function slugify_basic(string $string): string {
    $s = trim(mb_strtolower($string, 'UTF-8'));
    // Remove acentos
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    $s = preg_replace('~[^a-z0-9\s-]~', '', (string)$s);
    $s = preg_replace('~[\s-]+~', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'item';
}

/**
 * Gera um slug único na tabela `servicos` (coluna `slug`).
 * Aceita um callable $exists que verifica existência do slug.
 */
function ensure_unique_slug(string $baseSlug, callable $exists): string {
    $slug = $baseSlug;
    $i = 2;
    while ($exists($slug)) {
        $slug = $baseSlug . '-' . $i;
        $i++;
        if ($i > 1000) break; // safety
    }
    return $slug;
}

/**
 * Helper principal: gera slug e garante unicidade usando a BD `$conn` (mysqli)
 */
function slugify(mysqli $conn, string $title, ?int $ignoreId = null): string {
    $base = slugify_basic($title);
    $exists = function(string $slug) use ($conn, $ignoreId): bool {
        if ($ignoreId) {
            $stmt = $conn->prepare('SELECT 1 FROM servicos WHERE slug=? AND id<>? LIMIT 1');
            $stmt->bind_param('si', $slug, $ignoreId);
        } else {
            $stmt = $conn->prepare('SELECT 1 FROM servicos WHERE slug=? LIMIT 1');
            $stmt->bind_param('s', $slug);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $has = (bool)$res->fetch_row();
        $stmt->close();
        return $has;
    };
    return ensure_unique_slug($base, $exists);
}
