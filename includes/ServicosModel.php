<?php
declare(strict_types=1);

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/slugify.php';

class ServicosModel {
    private mysqli $conn;

    public function __construct(mysqli $conn) { $this->conn = $conn; }

    public function listar(array $f = []): array {
        $where = [];
        $params = [];
        $types = '';
        if (!empty($f['tipo'])) { $where[] = 'tipo=?'; $types.='s'; $params[] = (string)$f['tipo']; }
        if (!empty($f['estado'])) { $where[] = 'estado_visibilidade=?'; $types.='s'; $params[] = (string)$f['estado']; }
        $sql = 'SELECT * FROM servicos';
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY ordem ASC, updated_at DESC';
        $stmt = $this->conn->prepare($sql);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function obter(int $id): ?array {
        $stmt = $this->conn->prepare('SELECT * FROM servicos WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function obterPorSlug(string $slug): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM servicos WHERE slug=? AND status_publicacao='publicado' AND estado_visibilidade='ativo' LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function criar(array $d): int {
        $titulo = trim((string)($d['titulo'] ?? ''));
        $slug = slugify($this->conn, $titulo, null);
        $stmt = $this->conn->prepare('INSERT INTO servicos (titulo,resumo_curto,descricao_longa,imagem_principal,tipo,preco_base,estado_visibilidade,status_publicacao,slug,categoria_id,ordem,seo_title,seo_description,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
        $preco = isset($d['preco_base']) && $d['preco_base'] !== '' ? (string)$d['preco_base'] : null;
        $estado = $d['estado_visibilidade'] ?? 'inativo';
        $status = $d['status_publicacao'] ?? 'rascunho';
        $resumo = $d['resumo_curto'] ?? '';
        $desc = $d['descricao_longa'] ?? '';
        $img = $d['imagem_principal'] ?? '';
        $tipo = $d['tipo'] ?? '';
        $cat = isset($d['categoria_id']) && $d['categoria_id'] !== '' ? (int)$d['categoria_id'] : null;
        $ordem = isset($d['ordem']) && $d['ordem'] !== '' ? (int)$d['ordem'] : 0;
        $seo_title = $d['seo_title'] ?? null;
        $seo_desc  = $d['seo_description'] ?? null;
        $stmt->bind_param('sssssssssisss', $titulo,$resumo,$desc,$img,$tipo,$preco,$estado,$status,$slug,$cat,$ordem,$seo_title,$seo_desc);
        $ok = $stmt->execute();
        $id = $ok ? (int)$stmt->insert_id : 0;
        $stmt->close();
        return $id;
    }

    public function atualizar(int $id, array $d): bool {
        $curr = $this->obter($id);
        if (!$curr) return false;
        $titulo = trim((string)($d['titulo'] ?? $curr['titulo']));
        $novoSlug = $curr['slug'];
        if ($titulo !== $curr['titulo']) {
            $novoSlug = slugify($this->conn, $titulo, $id);
        }
        $resumo = $d['resumo_curto'] ?? $curr['resumo_curto'];
        $desc   = $d['descricao_longa'] ?? $curr['descricao_longa'];
        $img    = array_key_exists('imagem_principal',$d) ? ($d['imagem_principal'] ?? '') : $curr['imagem_principal'];
        $tipo   = $d['tipo'] ?? $curr['tipo'];
        $preco  = array_key_exists('preco_base',$d) ? ($d['preco_base'] !== '' ? (string)$d['preco_base'] : null) : ($curr['preco_base'] ?? null);
        $estado = $d['estado_visibilidade'] ?? $curr['estado_visibilidade'];
        $status = $d['status_publicacao'] ?? $curr['status_publicacao'];
        $cat    = array_key_exists('categoria_id',$d) ? ($d['categoria_id'] !== '' ? (int)$d['categoria_id'] : null) : ($curr['categoria_id'] ?? null);
        $ordem  = array_key_exists('ordem',$d) ? (int)$d['ordem'] : (int)$curr['ordem'];
        $seo_title = array_key_exists('seo_title',$d) ? ($d['seo_title'] ?: null) : ($curr['seo_title'] ?? null);
        $seo_desc  = array_key_exists('seo_description',$d) ? ($d['seo_description'] ?: null) : ($curr['seo_description'] ?? null);

        $stmt = $this->conn->prepare('UPDATE servicos SET titulo=?,resumo_curto=?,descricao_longa=?,imagem_principal=?,tipo=?,preco_base=?,estado_visibilidade=?,status_publicacao=?,slug=?,categoria_id=?,ordem=?,seo_title=?,seo_description=?,updated_at=NOW() WHERE id=?');
        $stmt->bind_param('sssssssssissssi', $titulo,$resumo,$desc,$img,$tipo,$preco,$estado,$status,$novoSlug,$cat,$ordem,$seo_title,$seo_desc,$id);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok && $novoSlug !== $curr['slug']) {
            $this->registarRedirect($curr['slug'], $novoSlug);
        }
        return $ok;
    }

    public function apagar(int $id): bool {
        // Bloquear se usado em leads (servico == titulo atual)
        $curr = $this->obter($id);
        if (!$curr) return false;
        $titulo = (string)$curr['titulo'];
        $stmt = $this->conn->prepare("SELECT COUNT(*) c FROM leads WHERE source='orcamento' AND servico=?");
        $stmt->bind_param('s', $titulo);
        $stmt->execute();
        $res = $stmt->get_result();
        $n = (int)($res->fetch_assoc()['c'] ?? 0);
        $stmt->close();
        if ($n > 0) {
            return false;
        }
        $stmt = $this->conn->prepare('DELETE FROM servicos WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function publicar(int $id): bool {
        $curr = $this->obter($id);
        if (!$curr) return false;
        // Campos obrigatórios
        foreach (['titulo','resumo_curto','descricao_longa','imagem_principal','tipo'] as $req) {
            if (empty($curr[$req])) return false;
        }
        $stmt = $this->conn->prepare("UPDATE servicos SET status_publicacao='publicado', updated_at=NOW() WHERE id=?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function despublicar(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE servicos SET status_publicacao='rascunho', updated_at=NOW() WHERE id=?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function mudarEstadoVisibilidade(int $id, string $estado): bool {
        $allowed = ['ativo','inativo','interno'];
        if (!in_array($estado, $allowed, true)) return false;
        $stmt = $this->conn->prepare('UPDATE servicos SET estado_visibilidade=?, updated_at=NOW() WHERE id=?');
        $stmt->bind_param('si', $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function registarRedirect(string $from, string $to): bool {
        if ($from === $to) return true;
        $stmt = $this->conn->prepare('INSERT INTO redirects (from_slug, to_slug) VALUES (?, ?)');
        $stmt->bind_param('ss', $from, $to);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function listarPublicosParaSelect(): array {
        $sql = "SELECT id, titulo FROM servicos WHERE estado_visibilidade='ativo' AND status_publicacao='publicado' ORDER BY ordem ASC, titulo ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ---- Contadores ----
    public function contarTotal(): int {
        $res = $this->conn->query('SELECT COUNT(*) c FROM servicos');
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }

    public function contarAtivosPublicados(): int {
        $res = $this->conn->query("SELECT COUNT(*) c FROM servicos WHERE estado_visibilidade='ativo' AND status_publicacao='publicado'");
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }

    public function contarRascunhos(): int {
        $res = $this->conn->query("SELECT COUNT(*) c FROM servicos WHERE status_publicacao='rascunho'");
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }

    public function contarInternos(): int {
        $res = $this->conn->query("SELECT COUNT(*) c FROM servicos WHERE estado_visibilidade='interno'");
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }
}
