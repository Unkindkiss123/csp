-- Constrói Spa & Pool — DB Diff (MySQL 8+/MariaDB 10.4+)
-- Objetivo: alinhar a BD real com o que o código usa, de forma idempotente.
-- Segurança: Faça backup antes de executar.

SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- (Opcional) Criar e usar a BD
-- CREATE DATABASE IF NOT EXISTS `constroi_spa_pool`
--   DEFAULT CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- USE `constroi_spa_pool`;

/* ---------------------------------------
   UTILIZADORES
---------------------------------------- */
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NULL,
  `email` VARCHAR(190) NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `nome_completo` VARCHAR(120) NOT NULL,
  `role` ENUM('admin','user','cliente') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Normalizações/ajustes sem perda
ALTER TABLE `utilizadores`
  MODIFY `email` VARCHAR(190) NOT NULL,
  MODIFY `senha_hash` VARCHAR(255) NOT NULL;

ALTER TABLE `utilizadores`
  ADD COLUMN IF NOT EXISTS `nome_completo` VARCHAR(120) NOT NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `usuario` VARCHAR(50) NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Garantir que existe exatamente 1 índice UNIQUE em `email` (idempotente)
-- 1) Se não existir nenhum UNIQUE(email), cria o preferido `ux_utilizadores_email`
SET @email_unique_count := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'utilizadores'
    AND column_name = 'email'
    AND non_unique = 0
);
SET @sql := IF(@email_unique_count = 0,
  'ALTER TABLE `utilizadores` ADD UNIQUE KEY `ux_utilizadores_email` (`email`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Se existirem múltiplos UNIQUE(email), elimina os redundantes preservando `ux_utilizadores_email` se existir
-- Recalcular após potencial criação
SET @email_unique_count := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'utilizadores'
    AND column_name = 'email'
    AND non_unique = 0
);
SET @ux_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'utilizadores'
    AND column_name = 'email'
    AND non_unique = 0
    AND index_name = 'ux_utilizadores_email'
);
SET @keep_idx := IF(@ux_exists > 0,
  'ux_utilizadores_email',
  (
    SELECT index_name
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'utilizadores'
      AND column_name = 'email'
      AND non_unique = 0
    LIMIT 1
  )
);
SET SESSION group_concat_max_len = 10000;
SET @drop_cmds := (
  SELECT GROUP_CONCAT(CONCAT('DROP INDEX `', index_name, '`') SEPARATOR ', ')
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'utilizadores'
    AND column_name = 'email'
    AND non_unique = 0
    AND index_name <> @keep_idx
);
SET @sql := IF(@email_unique_count > 1 AND @drop_cmds IS NOT NULL,
  CONCAT('ALTER TABLE `utilizadores` ', @drop_cmds),
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* ---------------------------------------
   TOKENS_RECUPERACAO
---------------------------------------- */
CREATE TABLE IF NOT EXISTS `tokens_recuperacao` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `utilizador_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expira_em` DATETIME NOT NULL,
  `usado` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tr_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tokens_recuperacao`
  MODIFY `token_hash` CHAR(64) NOT NULL,
  ADD COLUMN IF NOT EXISTS `expira_em` DATETIME NOT NULL,
  ADD COLUMN IF NOT EXISTS `usado` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Garantir índice em token_hash via INFORMATION_SCHEMA (idempotente)
SET @idx2_exists := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'tokens_recuperacao' AND index_name = 'idx_tr_token'
);
SET @sql := IF(@idx2_exists = 0,
  'ALTER TABLE `tokens_recuperacao` ADD INDEX `idx_tr_token` (`token_hash`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK utilizador_id -> utilizadores(id) ON DELETE CASCADE (idempotente via INFORMATION_SCHEMA)
SET @fk_exists := (
  SELECT COUNT(1) FROM information_schema.referential_constraints
  WHERE constraint_schema = DATABASE() AND constraint_name = 'fk_tr_user'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `tokens_recuperacao`\n   ADD CONSTRAINT `fk_tr_user`\n   FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores`(`id`)\n   ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* ---------------------------------------
   CONTACTOS
---------------------------------------- */
CREATE TABLE IF NOT EXISTS `contactos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `assunto` VARCHAR(190) NULL,
  `mensagem` TEXT NOT NULL,
  `ip` VARCHAR(64) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `contactos`
  MODIFY `email` VARCHAR(190) NOT NULL,
  ADD COLUMN IF NOT EXISTS `assunto` VARCHAR(190) NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `ip` VARCHAR(64) NULL;

/* ---------------------------------------
   NORMALIZAÇÃO ENGINE/CHARSET
---------------------------------------- */
ALTER TABLE `utilizadores`
  ENGINE=InnoDB,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `tokens_recuperacao`
  ENGINE=InnoDB,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contactos`
  ENGINE=InnoDB,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/* ---------------------------------------
   RELATÓRIO (manual)
---------------------------------------- */
-- Após executar, verifique:
-- SHOW CREATE TABLE `utilizadores`\G
-- SHOW CREATE TABLE `tokens_recuperacao`\G
-- SHOW CREATE TABLE `contactos`\G
-- SELECT COUNT(*) FROM `utilizadores`;
-- SELECT COUNT(*) FROM `tokens_recuperacao`;
-- SELECT COUNT(*) FROM `contactos`;
-- Confirme que existe UNIQUE KEY em utilizadores(email), INDEX em tokens_recuperacao(token_hash)
-- e FK fk_tr_user (utilizador_id -> utilizadores.id ON DELETE CASCADE).
