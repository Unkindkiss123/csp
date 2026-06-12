<?php
// Script de seed para criar um utilizador admin
// Uso: acede via browser: http://localhost/constroi_spa_pool/includes/seed_admin.php
// Depois remove ou protege este ficheiro.

require_once __DIR__ . '/db_connect.php';
session_start();

$usuario = 'admin';
$email = 'admin@exemplo.com';
$nome = 'Administrador';
$senha = 'Admin123!'; // altera antes de usar em produção
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Verifica se já existe
$stmt = $conn->prepare('SELECT id FROM utilizadores WHERE usuario = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $usuario, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo '<p>Admin já existe. Nada a fazer.</p>';
  exit;
}
$stmt->close();

$stmt = $conn->prepare('INSERT INTO utilizadores (usuario, email, password_hash, nome_completo, localidade, andar, porta, numero, cod_postal, data_nascimento, telefone, contribuinte, role) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,\'admin\')');
$stmt->bind_param('ssssssssssss', $usuario, $email, $hash, $nome, $loc, $andar, $porta, $numero, $cp, $nasc, $tel, $nif);

// Valores vazios para campos não essenciais
$loc = '';
$andar = '';
$porta = '';
$numero = '';
$cp = '';
$nasc = null;
$tel = '';
$nif = '';

if ($stmt->execute()) {
  echo '<p>Utilizador admin criado com sucesso.</p>';
  echo '<p><strong>Credenciais</strong></p>';
  echo '<p>Utilizador: ' . htmlspecialchars($usuario) . '</p>';
  echo '<p>Email: ' . htmlspecialchars($email) . '</p>';
  echo '<p>Senha (altere depois de entrar): ' . htmlspecialchars($senha) . '</p>';
  echo '<p><a href="../views/login_view.php">Ir para login</a></p>';
} else {
  echo '<p>Falha ao criar admin: ' . htmlspecialchars($conn->error) . '</p>';
}
