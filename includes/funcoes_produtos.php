<?php
// Funções de produtos (CRUD de leitura principal)

/**
 * Obtém produtos com filtros opcionais.
 * Opções aceites em $opts:
 *  - q: termo de pesquisa (nome ou referencia)
 *  - categoria: string
 *  - marca: string
 *  - pmin: preço mínimo (float)
 *  - pmax: preço máximo (float)
 *  - ativo: 1 (default) ou 0/1 para filtrar
 */
function getProdutos(mysqli $conn, array $opts = []): array {
  $where = [];
  $types = '';
  $params = [];

  // Ativo por omissão
  if (array_key_exists('ativo', $opts)) {
    $where[] = 'ativo = ?';
    $types .= 'i';
    $params[] = (int)$opts['ativo'];
  } else {
    $where[] = 'ativo = 1';
  }

  if (!empty($opts['q'])) {
    $where[] = '(nome LIKE CONCAT("%", ?, "%") OR referencia LIKE CONCAT("%", ?, "%"))';
    $types .= 'ss';
    $params[] = $opts['q'];
    $params[] = $opts['q'];
  }
  if (!empty($opts['categoria'])) {
    $where[] = 'categoria = ?';
    $types .= 's';
    $params[] = $opts['categoria'];
  }
  if (!empty($opts['marca'])) {
    $where[] = 'marca = ?';
    $types .= 's';
    $params[] = $opts['marca'];
  }
  if (array_key_exists('pmin', $opts) && $opts['pmin'] !== '' && $opts['pmin'] !== null) {
    $where[] = 'preco >= ?';
    $types .= 'd';
    $params[] = (float)$opts['pmin'];
  }
  if (array_key_exists('pmax', $opts) && $opts['pmax'] !== '' && $opts['pmax'] !== null) {
    $where[] = 'preco <= ?';
    $types .= 'd';
    $params[] = (float)$opts['pmax'];
  }

  $sql = 'SELECT id, nome, descricao, preco, stock, referencia, categoria, marca, caracteristicas, especificacoes_tecnicas, assistencia, requer_orcamento, imagem_principal, imagens_adicionais, ativo, criado_em FROM produtos';
  if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  // Ordenação segura (whitelist)
  $order = (string)($opts['order'] ?? 'recente');
  switch ($order) {
    case 'preco_asc':
      $sql .= ' ORDER BY preco ASC, criado_em DESC';
      break;
    case 'preco_desc':
      $sql .= ' ORDER BY preco DESC, criado_em DESC';
      break;
    case 'nome_asc':
      $sql .= ' ORDER BY nome ASC';
      break;
    case 'nome_desc':
      $sql .= ' ORDER BY nome DESC';
      break;
    default:
      $sql .= ' ORDER BY criado_em DESC';
  }
  // Paginação
  $limit = isset($opts['limit']) ? (int)$opts['limit'] : 0;
  $offset = isset($opts['offset']) ? (int)$opts['offset'] : 0;
  if ($limit > 0) {
    $sql .= ' LIMIT ' . $limit;
    if ($offset > 0) {
      $sql .= ' OFFSET ' . $offset;
    }
  }

  $stmt = $conn->prepare($sql);
  if ($types) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = [];
  while ($row = $res->fetch_assoc()) {
    $row['imagens_adicionais'] = decodeImagens($row['imagens_adicionais'] ?? '');
    $rows[] = $row;
  }
  $stmt->close();
  return $rows;
}

/** Conta total de produtos que correspondem aos filtros (para paginação). */
function countProdutos(mysqli $conn, array $opts = []): int {
  $where = [];
  $types = '';
  $params = [];

  if (array_key_exists('ativo', $opts)) {
    $where[] = 'ativo = ?';
    $types .= 'i';
    $params[] = (int)$opts['ativo'];
  } else {
    $where[] = 'ativo = 1';
  }
  if (!empty($opts['q'])) {
    $where[] = '(nome LIKE CONCAT("%", ?, "%") OR referencia LIKE CONCAT("%", ?, "%"))';
    $types .= 'ss';
    $params[] = $opts['q'];
    $params[] = $opts['q'];
  }
  if (!empty($opts['categoria'])) {
    $where[] = 'categoria = ?';
    $types .= 's';
    $params[] = $opts['categoria'];
  }
  if (!empty($opts['marca'])) {
    $where[] = 'marca = ?';
    $types .= 's';
    $params[] = $opts['marca'];
  }
  if (array_key_exists('pmin', $opts) && $opts['pmin'] !== '' && $opts['pmin'] !== null) {
    $where[] = 'preco >= ?';
    $types .= 'd';
    $params[] = (float)$opts['pmin'];
  }
  if (array_key_exists('pmax', $opts) && $opts['pmax'] !== '' && $opts['pmax'] !== null) {
    $where[] = 'preco <= ?';
    $types .= 'd';
    $params[] = (float)$opts['pmax'];
  }

  $sql = 'SELECT COUNT(*) AS total FROM produtos';
  if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  $stmt = $conn->prepare($sql);
  if ($types) { $stmt->bind_param($types, ...$params); }
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();
  return (int)($row['total'] ?? 0);
}

/** Obtém um único produto por ID. */
function getProdutoById(mysqli $conn, int $id): ?array {
  $stmt = $conn->prepare('SELECT id, nome, descricao, preco, stock, referencia, categoria, marca, caracteristicas, especificacoes_tecnicas, assistencia, requer_orcamento, imagem_principal, imagens_adicionais, ativo, criado_em FROM produtos WHERE id = ? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();
  if (!$row) return null;
  $row['imagens_adicionais'] = decodeImagens($row['imagens_adicionais'] ?? '');
  return $row;
}

/** Pesquisa produtos por termo (nome/referencia). */
function pesquisarProdutos(mysqli $conn, string $termo): array {
  return getProdutos($conn, ['q' => $termo, 'ativo' => 1]);
}

/** Decodifica JSON de imagens adicionais de forma resiliente. */
function decodeImagens($json): array {
  if (!$json) return [];
  $arr = json_decode((string)$json, true);
  return is_array($arr) ? $arr : [];
}

/** Cria um produto e devolve o ID inserido ou false em erro. */
function criarProduto(mysqli $conn, array $data) {
  $sql = 'INSERT INTO produtos (nome, descricao, preco, stock, referencia, categoria, marca, caracteristicas, especificacoes_tecnicas, assistencia, requer_orcamento, imagem_principal, imagens_adicionais, ativo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
  $stmt = $conn->prepare($sql);
  $nome = (string)($data['nome'] ?? '');
  $descricao = (string)($data['descricao'] ?? '');
  $preco = (float)($data['preco'] ?? 0);
  $stock = (int)($data['stock'] ?? 0);
  $referencia = (string)($data['referencia'] ?? '');
  $categoria = (string)($data['categoria'] ?? '');
  $marca = (string)($data['marca'] ?? '');
  $caracteristicas = (string)($data['caracteristicas'] ?? '');
  $especificacoes = (string)($data['especificacoes_tecnicas'] ?? '');
  $assistencia = isset($data['assistencia']) ? (int)$data['assistencia'] : 0;
  $requer_orcamento = isset($data['requer_orcamento']) ? (int)$data['requer_orcamento'] : 1;
  $imagem_principal = (string)($data['imagem_principal'] ?? '');
  $imagens_adicionais = $data['imagens_adicionais'] ?? [];
  $imagensJson = json_encode(array_values(array_filter($imagens_adicionais, fn($x) => is_string($x) && $x !== '')));
  $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
  $stmt->bind_param('ssdisssssissisi', $nome, $descricao, $preco, $stock, $referencia, $categoria, $marca, $caracteristicas, $especificacoes, $assistencia, $requer_orcamento, $imagem_principal, $imagensJson, $ativo);
  if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
  }
  $err = $conn->error;
  $stmt->close();
  return false;
}
