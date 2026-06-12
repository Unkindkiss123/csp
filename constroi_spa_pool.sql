-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24-Out-2025 às 19:45
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `constroi_spa_pool`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `assunto` varchar(190) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `contactos`
--

INSERT INTO `contactos` (`id`, `nome`, `email`, `assunto`, `mensagem`, `ip`, `criado_em`, `created_at`) VALUES
(1, 'Nuno Matos', 'mrspooks@outlook.pt', NULL, 'sdfsdfs', '::1', '2025-10-10 11:31:30', '2025-10-22 12:12:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `source` enum('orcamento','contacto') NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `telefone` varchar(40) DEFAULT NULL,
  `assunto` varchar(160) DEFAULT NULL,
  `servico` varchar(80) DEFAULT NULL,
  `localidade` varchar(120) DEFAULT NULL,
  `prazo` varchar(80) DEFAULT NULL,
  `orcamento_estimado` varchar(80) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `anexo_path` varchar(255) DEFAULT NULL,
  `estado` enum('novo','triagem','aguarda_cliente','orcamento_enviado','alterado','aceite','rejeitado','expirado','arquivado','concluido') DEFAULT 'novo',
  `validade_dias` tinyint(3) UNSIGNED DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `expirou_em` datetime DEFAULT NULL,
  `versao` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `motivo_rejeicao` enum('preco','timing','outro_fornecedor','sem_resposta') DEFAULT NULL,
  `utm_source` varchar(80) DEFAULT NULL,
  `utm_medium` varchar(80) DEFAULT NULL,
  `utm_campaign` varchar(80) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `leads`
--

INSERT INTO `leads` (`id`, `source`, `nome`, `email`, `telefone`, `assunto`, `servico`, `localidade`, `prazo`, `orcamento_estimado`, `mensagem`, `anexo_path`, `estado`, `validade_dias`, `enviado_em`, `expirou_em`, `versao`, `motivo_rejeicao`, `utm_source`, `utm_medium`, `utm_campaign`, `created_at`) VALUES
(1, 'orcamento', 'Nuno Matos', 'nunovmatos1337@gmail.com', '965183998', 'Pedido de orçamento', 'Construção de Piscinas', 'rio de mouro', '4 meses', '123453', 'asgbdsgdbasgg', NULL, 'novo', NULL, NULL, NULL, 1, NULL, '', '', '', '2025-10-24 15:39:25'),
(2, 'orcamento', 'Nuno Matos', 'nunovmatos1337@gmail.com', '965183998', 'Pedido de orçamento', 'Manutenção', 'rio de mouro', '1 semana', '150', 'asfafaf', '/uploads/leads/lead_1761317578_03b6956c.jpg', 'novo', NULL, NULL, NULL, 1, NULL, '', '', '', '2025-10-24 15:52:58');

-- --------------------------------------------------------

--
-- Estrutura da tabela `nav_menu`
--

CREATE TABLE `nav_menu` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_key` varchar(50) NOT NULL DEFAULT 'principal',
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `href` varchar(255) NOT NULL,
  `target` varchar(20) NOT NULL DEFAULT '_self',
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `nav_menu`
--

INSERT INTO `nav_menu` (`id`, `site_key`, `parent_id`, `label`, `href`, `target`, `icon`, `is_active`, `is_visible`, `ordem`, `created_at`, `updated_at`) VALUES
(14, 'principal', NULL, 'Produtos', '/views/produtos_view.php', '_self', NULL, 1, 1, 1, '2025-10-09 11:42:53', '2025-10-09 11:42:53'),
(15, 'principal', NULL, 'Serviços', '/views/servicos_view.php', '_self', NULL, 1, 1, 2, '2025-10-09 11:42:53', '2025-10-09 11:42:53'),
(16, 'principal', NULL, 'Contactos', '/views/contactos_view.php', '_self', NULL, 1, 1, 3, '2025-10-09 11:42:53', '2025-10-09 11:42:53'),
(17, 'principal', 15, 'Construção de Piscinas', '/views/servico_construcao_view.php', '_self', NULL, 1, 1, 1, '2025-10-09 11:42:54', '2025-10-09 11:42:54'),
(18, 'principal', 15, 'Assistência Técnica', '/views/servico_assistencia_view.php', '_self', NULL, 1, 1, 2, '2025-10-09 11:42:54', '2025-10-09 11:42:54'),
(19, 'principal', 15, 'Manutenção', '/views/servico_manutencao_view.php', '_self', NULL, 1, 1, 3, '2025-10-09 11:42:54', '2025-10-09 11:42:54'),
(20, 'principal', 15, 'Colocação em Tela Armada', '/views/servico_tela_view.php', '_self', NULL, 1, 1, 4, '2025-10-09 11:42:54', '2025-10-09 11:42:54'),
(21, 'principal', 15, 'Acompanhamento em Obra', '/views/servico_acompanhamento_view.php', '_self', NULL, 1, 1, 5, '2025-10-09 11:42:54', '2025-10-09 11:42:54'),
(22, 'principal', 15, 'Construção Civil', '/views/servico_civil_view.php', '_self', NULL, 1, 1, 6, '2025-10-09 11:42:54', '2025-10-09 11:42:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil_codigos`
--

CREATE TABLE `perfil_codigos` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `code_hash` char(64) NOT NULL,
  `expira_em` datetime NOT NULL,
  `tentativas` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `enviado_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `perfil_codigos`
--

INSERT INTO `perfil_codigos` (`id`, `utilizador_id`, `code_hash`, `expira_em`, `tentativas`, `enviado_em`, `usado`, `created_at`) VALUES
(7, 6, '58ba30bb31b56436d92b202bd4f278b1588fa2aef256053e4887fe5c0a432bf7', '2025-10-24 19:54:25', 0, '2025-10-24 18:44:25', 1, '2025-10-24 17:44:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `referencia` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `caracteristicas` text DEFAULT NULL,
  `especificacoes_tecnicas` text DEFAULT NULL,
  `assistencia` tinyint(1) NOT NULL DEFAULT 0,
  `imagem_principal` varchar(255) DEFAULT NULL,
  `imagens_adicionais` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `requer_orcamento` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `stock`, `referencia`, `categoria`, `marca`, `caracteristicas`, `especificacoes_tecnicas`, `assistencia`, `imagem_principal`, `imagens_adicionais`, `ativo`, `criado_em`, `requer_orcamento`) VALUES
(1, 'Spa Relax 3002', 'Spa compacto ideal para espaços reduzidos, com jatos de massagem ajustáveis e cromoterapia.', 1299.90, 8, 'SP-3000', 'Spas', 'HydroZen', '3 lugares; 18 jatos; cromoterapia', '', 1, '/public/imagens/logo_no_text.png', '[\"/public/imagens/logo_no_text.png\"]', 1, '2025-10-09 11:15:35', 0),
(2, 'Bomba Piscina ProFlow 1.5HP', 'Bomba de alto desempenho para filtração contínua com baixo consumo.', 349.00, 0, 'PF-1500', 'Bombas', 'AquaPro', '1.5HP; 230V; IP55', NULL, 0, '/public/imagens/logo_no_text.png', '[\"/public/imagens/logo_no_text.png\",\"/public/imagens/logo_no_text.png\"]', 0, '2025-10-09 11:15:35', 1),
(3, 'Cobertura Térmica 6mm (6x3m)', 'Reduz a evaporação e mantém a temperatura da água, aumentando a eficiência energética.', 189.99, 14, 'CT-63', 'Coberturas', 'ThermaPool', '6mm; anti-UV', '', 1, '/public/imagens/logo_no_text.png', '[]', 1, '2025-10-09 11:15:35', 1),
(4, 'Sistema de Cloração Salina XS', 'Tratamento por sal com controlo automático, baixo custo de manutenção.', 799.50, 3, 'CS-XS', 'Tratamento de Água', 'BlueSalt', 'Para piscinas até 40m³', NULL, 0, '/public/imagens/logo_no_text.png', '[\"/public/imagens/logo_no_text.png\"]', 1, '2025-10-09 11:15:35', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `redirects`
--

CREATE TABLE `redirects` (
  `id` int(11) NOT NULL,
  `from_slug` varchar(180) NOT NULL,
  `to_slug` varchar(180) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(160) NOT NULL,
  `resumo_curto` varchar(220) NOT NULL,
  `descricao_longa` text NOT NULL,
  `imagem_principal` varchar(255) NOT NULL,
  `tipo` enum('manutencao','preco_fixo','orcamento_personalizado') NOT NULL,
  `preco_base` decimal(10,2) DEFAULT NULL,
  `estado_visibilidade` enum('ativo','inativo','interno') NOT NULL DEFAULT 'inativo',
  `status_publicacao` enum('rascunho','publicado') NOT NULL DEFAULT 'rascunho',
  `slug` varchar(180) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(70) DEFAULT NULL,
  `seo_description` varchar(160) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tentativas_login`
--

CREATE TABLE `tentativas_login` (
  `ip` varchar(45) NOT NULL,
  `tentativas` int(11) DEFAULT 0,
  `ultima_tentativa` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tokens_recuperacao`
--

CREATE TABLE `tokens_recuperacao` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `usuario` varchar(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nome_completo` varchar(60) NOT NULL,
  `localidade` varchar(50) DEFAULT NULL,
  `andar` varchar(10) DEFAULT NULL,
  `porta` varchar(10) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `cod_postal` varchar(10) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `contribuinte` varchar(15) DEFAULT NULL,
  `role` enum('admin','editor','viewer','cliente') NOT NULL DEFAULT 'cliente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `usuario`, `email`, `password_hash`, `nome_completo`, `localidade`, `andar`, `porta`, `numero`, `cod_postal`, `data_nascimento`, `telefone`, `contribuinte`, `role`, `criado_em`, `created_at`) VALUES
(6, 'admin', 'admin@exemplo.com', '$2y$10$CGzUVJdxf/NGnaKU4bBtS.IvqB2UREpvxidpctghL.krWqE4uc1YW', 'Administrador 1', '', '', '', '', '', NULL, '', '', 'admin', '2025-10-13 09:28:06', '2025-10-22 12:12:49'),
(12, 'teste1', 'nunovmatos1337@gmail.com', '$2y$12$tH9N554foZrsu0sdm.4FG.d7B5SIF/lLyP4OB5sGn.2DXsEAWW1am', 'Nuno Matos', 'rio de mouro', '', '', '', '2635-473', '0000-00-00', '965183998', '', 'cliente', '2025-10-24 16:54:25', '2025-10-24 17:54:25');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `nav_menu`
--
ALTER TABLE `nav_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_navmenu_parent` (`parent_id`),
  ADD KEY `idx_site_parent_ordem` (`site_key`,`parent_id`,`ordem`),
  ADD KEY `idx_visible_active` (`is_visible`,`is_active`);

--
-- Índices para tabela `perfil_codigos`
--
ALTER TABLE `perfil_codigos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilizador_id` (`utilizador_id`),
  ADD KEY `idx_expira_em` (`expira_em`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produtos_nome` (`nome`),
  ADD KEY `idx_produtos_referencia` (`referencia`),
  ADD KEY `idx_produtos_categoria` (`categoria`),
  ADD KEY `idx_produtos_marca` (`marca`),
  ADD KEY `idx_produtos_ativo` (`ativo`);

--
-- Índices para tabela `redirects`
--
ALTER TABLE `redirects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_slug` (`from_slug`),
  ADD KEY `to_slug` (`to_slug`);

--
-- Índices para tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_servicos_pub` (`estado_visibilidade`,`status_publicacao`,`ordem`,`titulo`);

--
-- Índices para tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  ADD PRIMARY KEY (`ip`);

--
-- Índices para tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tr_user` (`utilizador_id`),
  ADD KEY `idx_tr_expira` (`expira_em`),
  ADD KEY `idx_tr_token` (`token_hash`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `ux_utilizadores_email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `nav_menu`
--
ALTER TABLE `nav_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `perfil_codigos`
--
ALTER TABLE `perfil_codigos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `redirects`
--
ALTER TABLE `redirects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `nav_menu`
--
ALTER TABLE `nav_menu`
  ADD CONSTRAINT `fk_navmenu_parent` FOREIGN KEY (`parent_id`) REFERENCES `nav_menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `perfil_codigos`
--
ALTER TABLE `perfil_codigos`
  ADD CONSTRAINT `fk_perfil_codigos_user` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD CONSTRAINT `fk_tr_user` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
