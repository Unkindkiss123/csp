-- Constrói Spa & Pool - Esquema de Base de Dados (MySQL 8+)
-- Engine e collation
SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- Utilizadores
CREATE TABLE IF NOT EXISTS utilizadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  nome_completo VARCHAR(120) NOT NULL,
  localidade VARCHAR(120) DEFAULT NULL,
  andar VARCHAR(20) DEFAULT NULL,
  porta VARCHAR(20) DEFAULT NULL,
  numero VARCHAR(20) DEFAULT NULL,
  cod_postal VARCHAR(20) DEFAULT NULL,
  data_nascimento DATE DEFAULT NULL,
  telefone VARCHAR(30) DEFAULT NULL,
  contribuinte VARCHAR(20) DEFAULT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_nome (nome_completo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tentativas de login para rate limiting
CREATE TABLE IF NOT EXISTS tentativas_login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  tentativas INT NOT NULL DEFAULT 0,
  ultima_tentativa TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tl_ip (ip),
  KEY idx_tl_ultima (ultima_tentativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu de navegação
CREATE TABLE IF NOT EXISTS nav_menu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_key VARCHAR(50) NOT NULL DEFAULT 'principal',
  label VARCHAR(100) NOT NULL,
  href VARCHAR(255) NOT NULL,
  parent_id INT DEFAULT NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  ordem INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_nm_label (label),
  KEY idx_nm_ordem (ordem),
  CONSTRAINT fk_nm_parent FOREIGN KEY (parent_id) REFERENCES nav_menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Produtos
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(160) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  referencia VARCHAR(80) DEFAULT NULL,
  categoria VARCHAR(120) DEFAULT NULL,
  marca VARCHAR(120) DEFAULT NULL,
  caracteristicas TEXT,
  especificacoes_tecnicas TEXT NULL,
  assistencia TINYINT(1) NOT NULL DEFAULT 0,
  requer_orcamento TINYINT(1) NOT NULL DEFAULT 1,
  imagem_principal VARCHAR(255) DEFAULT NULL,
  imagens_adicionais JSON DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_prod_nome (nome),
  KEY idx_prod_ref (referencia),
  KEY idx_prod_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Atualizações de esquema (executar manualmente se a tabela já existir)
-- ALTER TABLE produtos
--   ADD COLUMN especificacoes_tecnicas TEXT NULL AFTER caracteristicas,
--   ADD COLUMN assistencia TINYINT(1) NOT NULL DEFAULT 0 AFTER especificacoes_tecnicas;
--   ADD COLUMN requer_orcamento TINYINT(1) NOT NULL DEFAULT 1 AFTER assistencia;

-- Imagens de produtos (opcional, para multiplas imagens normalizadas)
CREATE TABLE IF NOT EXISTS produtos_imagens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produto_id INT NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  ordem INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pi_prod (produto_id),
  KEY idx_pi_ordem (ordem),
  CONSTRAINT fk_pi_prod FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tokens de recuperação de palavra-passe
CREATE TABLE IF NOT EXISTS tokens_recuperacao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tr_user (utilizador_id),
  KEY idx_tr_expira (expira_em),
  CONSTRAINT fk_tr_user FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
