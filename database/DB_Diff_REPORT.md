# DB Diff Report — Constrói Spa & Pool

> Nota: Este ficheiro é um RELATÓRIO em Markdown (não é SQL).  
> Para executar as alterações na BD, usa o ficheiro `database/DB_Diff.sql` no phpMyAdmin.

Data: 2025-10-22
Alvo: MySQL 8+/MariaDB 10.4+

Este relatório resume as diferenças entre a BD real (backup anexo) e o que o projeto usa no código, e descreve como o script `database/DB_Diff.sql` as corrige de forma idempotente.

## Resumo de diferenças detetadas

Com base no código e no backup fornecido:

- `utilizadores`
  - O código usa: `usuario`, `email`, `senha_hash`, `nome_completo`, (`role` enum), e timestamps.
  - Backup: `email` é `VARCHAR(60)`, `nome_completo` `VARCHAR(60)`, existe `role` e `criado_em`.
  - Ação: alongar `email` para `VARCHAR(190)`, garantir `senha_hash` `VARCHAR(255)`, adicionar colunas em falta (`usuario`, `nome_completo`, `created_at`) se não existirem, e garantir `UNIQUE(email)`.

- `tokens_recuperacao`
  - O código usa: `utilizador_id`, `token_hash`, `expira_em`, `usado`, `criado_em`.
  - Backup: já possui `utilizador_id`, `token_hash`, `expira_em`, `usado`, `criado_em`.
  - Ação: garantir índice em `token_hash`, e FK `fk_tr_user` (`utilizador_id` → `utilizadores`.`id` ON DELETE CASCADE) se em falta.

- `contactos`
  - O código usa a tabela para registo de contactos; nem sempre há `assunto`.
  - Backup: existe `contactos` sem `assunto` e com `criado_em` (timestamp).
  - Ação: criar tabela se faltar; adicionar colunas opcionais `assunto`, `created_at`, e normalizar `email` para `VARCHAR(190)`.

- Engine/Charset
  - Ação: normalizar todas as tabelas críticas para `ENGINE=InnoDB` e `utf8mb4_unicode_ci`.

## O que o DB_Diff.sql faz

1. Cria as tabelas críticas se ainda não existirem: `utilizadores`, `tokens_recuperacao`, `contactos`.
2. Em `utilizadores`:
   - `MODIFY email VARCHAR(190) NOT NULL`, `MODIFY senha_hash VARCHAR(255) NOT NULL`.
   - `ADD COLUMN IF NOT EXISTS nome_completo VARCHAR(120) NOT NULL`, `usuario VARCHAR(50)`, `created_at DATETIME ...`.
   - Garante `UNIQUE(email)` (tenta com `IF NOT EXISTS` e tem fallback via `INFORMATION_SCHEMA`).
3. Em `tokens_recuperacao`:
   - Garante colunas logically necessárias (no-op se já existirem).
   - `ADD INDEX IF NOT EXISTS idx_tr_token (token_hash)`.
   - Adiciona FK `fk_tr_user` de forma idempotente usando `INFORMATION_SCHEMA`.
4. Em `contactos`:
   - Adiciona `assunto` e `created_at` (não remove `criado_em`).
   - Alarga `email` para `VARCHAR(190)`.
5. Normaliza ENGINE/CHARSET para `InnoDB`/`utf8mb4_unicode_ci`.

## Como executar

1) Backup obrigatório
- Faça backup no phpMyAdmin ou via CLI antes de aplicar.

2) Executar o script
- No phpMyAdmin, SELECIONE primeiro a BD do projeto (ex.: `constroi_spa_pool`) no painel da esquerda. Em alternativa, podes executar `USE <nome_da_BD>` antes de qualquer consulta.
- Depois, corre o conteúdo do ficheiro `database/DB_Diff.sql`.

3) Validação rápida (colar no SQL do phpMyAdmin)

```sql
-- 0) Sanidade: confirmar a BD ativa
SELECT DATABASE() AS current_db;               -- deve ser 'constroi_spa_pool' (ou o nome da tua BD)
-- Se não for, muda explicitamente (ajusta o nome, se diferente):
USE `constroi_spa_pool`;

-- 1) Tabelas presentes?
SHOW TABLES LIKE 'utilizadores';
SHOW TABLES LIKE 'tokens_recuperacao';
SHOW TABLES LIKE 'contactos';

-- 2) Estruturas (totalmente qualificadas para evitar contexto errado)
SHOW CREATE TABLE `constroi_spa_pool`.`utilizadores`;
SHOW CREATE TABLE `constroi_spa_pool`.`tokens_recuperacao`;
SHOW CREATE TABLE `constroi_spa_pool`.`contactos`;

-- 3) Índices (rápido)
SHOW INDEX FROM `constroi_spa_pool`.`utilizadores`;
SHOW INDEX FROM `constroi_spa_pool`.`tokens_recuperacao`;

-- 4) FK de tokens_recuperacao -> utilizadores (espera-se DELETE_RULE = CASCADE)
SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
WHERE rc.CONSTRAINT_SCHEMA = 'constroi_spa_pool'
  AND rc.TABLE_NAME = 'tokens_recuperacao'
  AND rc.REFERENCED_TABLE_NAME = 'utilizadores';
```

4) O que pode mudar após aplicar
- `utilizadores.email` poderá ser alargado para 190 chars (Unicode-safe para índices).
- `utilizadores` passará a garantir exatamente um `UNIQUE(email)` (se já existirem índices únicos duplicados, o script remove os redundantes e mantém um).
- `tokens_recuperacao` terá índice em `token_hash` e FK para `utilizadores` se não existiam.
- `contactos` poderá ganhar `assunto` e `created_at`.

## Bónus (opcional) — Seed de Admin

Para criar um admin mínimo (ajuste o email e o hash):

```sql
-- Gerar hash em PHP (num script ou no terminal PHP):
-- echo password_hash('Admin123!', PASSWORD_DEFAULT);

INSERT INTO `utilizadores` (`usuario`, `nome_completo`, `email`, `senha_hash`, `role`)
VALUES ('admin', 'Administrador', 'admin@exemplo.pt', '$2y$10$COLOCA_AQUI_O_HASH', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);
```

Dica: também existe `includes/tools/hash_password.php` no projeto (apenas em DEV), que pode gerar hashes de forma segura.

## Resolução de erros comuns

- Erro `#1109 - Tabela 'utilizadores' desconhecida em 'information_schema'`
  - Causa: estás a executar as queries com o contexto da BD errado (por ex., `information_schema`).
  - Solução: seleciona no phpMyAdmin a BD do projeto (por ex., `constroi_spa_pool`) no painel da esquerda, ou executa antes `USE \`constroi_spa_pool\`;`.
  - Dica: usa nomes totalmente qualificados nas queries (ex.: `SHOW INDEX FROM \`constroi_spa_pool\`.\`utilizadores\`;`) para evitar depender do contexto.

- Índices duplicados em `utilizadores(email)`
  - Sintoma: `SHOW INDEX` mostra mais de um índice UNIQUE para a coluna `email` (ex.: `email` e `ux_utilizadores_email`).
  - Estado após script: o `DB_Diff.sql` já normaliza e mantém apenas um índice único (preferindo `ux_utilizadores_email` se existir).
  - Manual (se precisares limpar à parte): substitui o nome abaixo pelo índice que queres REMOVER (não removas todos!).

  ```sql
  USE `constroi_spa_pool`;
  -- Exemplo: remover um índice redundante em email
  ALTER TABLE `utilizadores` DROP INDEX `email`;
  ```

- Erro ao usar `SOURCE` no phpMyAdmin
  - Sintoma: `#1064 - ... perto de 'SOURCE ...'` quando tentas correr `SOURCE caminho/DB_Diff.sql` na aba SQL do phpMyAdmin.
  - Causa: `SOURCE` é um comando do cliente `mysql` (CLI) e não é suportado na janela SQL do phpMyAdmin.
  - Soluções:
    1) phpMyAdmin → Importar: abre a aba Importar na tua BD (`constroi_spa_pool`), seleciona o ficheiro `database/DB_Diff.sql` e executa.
   2) Linha de comandos (Windows PowerShell):

       ```powershell
       # Caminho padrão do XAMPP; ajusta se necessário
       & "C:\xampp\mysql\bin\mysql.exe" -u root -p --default-character-set=utf8mb4 constroi_spa_pool -e "source C:/xampp/htdocs/constroi_spa_and_pool/database/DB_Diff.sql"
       ```

       Observações:
       - Em PowerShell, o redirecionamento de input com `<` não funciona como no cmd/bash; usa `-e "source ..."` como acima.
       - Podes ser pedido a password do `root` (ou usa outro utilizador com permissões adequadas).
     - Importante: este comando corre no TERMINAL (PowerShell). Não o coloques na aba SQL do phpMyAdmin.

   3) Linha de comandos (Windows cmd.exe):

     ```bat
     "C:\xampp\mysql\bin\mysql.exe" -u root -p --default-character-set=utf8mb4 constroi_spa_pool -e "source C:/xampp/htdocs/constroi_spa_and_pool/database/DB_Diff.sql"
     ```
