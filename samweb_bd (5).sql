-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16-Set-2026 às 18:01
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
-- Banco de dados: `samweb_bd`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alojamento`
--

CREATE TABLE `alojamento` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `dominio_id` int(11) NOT NULL,
  `inicio` date NOT NULL,
  `expiracao` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `alojamento`
--

INSERT INTO `alojamento` (`id`, `cliente_id`, `produto_id`, `dominio_id`, `inicio`, `expiracao`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, '2026-09-15', '2027-09-15', 'Inactivo', '2026-09-10 11:43:16', '2026-09-10 11:45:44'),
(2, 1, 2, 2, '2026-09-15', '2027-09-15', 'Activo', '2026-09-15 11:45:08', '2026-09-15 11:45:08');

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categoria`
--

INSERT INTO `categoria` (`id`, `nome`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 1, '2026-08-31 16:42:14', '2026-08-31 16:42:14'),
(2, 'Business', 1, '2026-08-31 16:42:14', '2026-08-31 16:42:14'),
(3, 'Domínios', 1, '2026-09-02 15:06:38', '2026-09-02 15:06:38'),
(4, 'Hosting', 1, '2026-09-02 15:07:03', '2026-09-02 15:07:03'),
(6, 'Serviço de Email', 1, '2026-09-02 15:07:50', '2026-09-02 15:07:50'),
(7, 'Certificado SSL', 1, '2026-09-16 10:43:00', '2026-09-16 10:43:00'),
(8, 'Hospedagem VPS', 1, '2026-09-16 16:23:17', '2026-09-16 16:23:17');

-- --------------------------------------------------------

--
-- Estrutura da tabela `certificado_ssl`
--

CREATE TABLE `certificado_ssl` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `dominio_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `inicio` date NOT NULL,
  `expiracao` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `certificado_ssl`
--

INSERT INTO `certificado_ssl` (`id`, `cliente_id`, `dominio_id`, `produto_id`, `inicio`, `expiracao`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, '2026-09-10', '2027-09-10', 'Activo', '2026-09-10 12:40:59', '2026-09-10 12:48:09'),
(2, 1, 1, 5, '2026-09-16', '2027-09-16', 'Activo', '2026-09-16 10:49:37', '2026-09-16 10:49:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `tipo` enum('Particular','Empresa','','') NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nif` varchar(30) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cliente`
--

INSERT INTO `cliente` (`id`, `utilizador_id`, `tipo`, `nome`, `nif`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 'Particular', 'SAMWEB', '5002094711', 1, '2026-09-02 10:45:02', '2026-09-02 12:46:56'),
(2, 2, 'Empresa', 'Pavest', '5002094722', 1, '2026-09-10 12:29:49', '2026-09-10 12:29:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `dominio`
--

CREATE TABLE `dominio` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nome` varchar(253) NOT NULL,
  `inicio` date NOT NULL,
  `expiracao` date NOT NULL,
  `renovacao_auto` tinyint(4) NOT NULL,
  `estado` tinyint(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `dominio`
--

INSERT INTO `dominio` (`id`, `cliente_id`, `nome`, `inicio`, `expiracao`, `renovacao_auto`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 'samweb.ao', '2026-09-15', '2027-09-15', 1, 1, '2026-09-09 16:10:23', '2026-09-15 14:17:09'),
(2, 1, 'samvieira.co.ao', '2026-09-10', '2027-09-10', 1, 1, '2026-09-10 11:41:53', '2026-09-10 11:41:53'),
(3, 2, 'quitanda.ao', '2026-09-16', '2027-09-16', 1, 1, '2026-09-16 17:03:10', '2026-09-16 17:03:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int(15) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nif` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(25) NOT NULL,
  `estado` enum('Activo','Inativo','Em desenvolvimento','Dissolvida') NOT NULL DEFAULT 'Activo',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `empresa`
--

INSERT INTO `empresa` (`id`, `nome`, `nif`, `email`, `telefone`, `estado`, `created_at`, `updated_at`) VALUES
(2, 'SAMVIEIRA - COMÉRCIO E PRESTAÇÃO DE SERVIÇO', '5002094711', 'samvieiralda@gmail.com', '+244 947 345 928', 'Activo', '2026-09-01 16:32:23', '2026-09-01 16:32:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `factura`
--

CREATE TABLE `factura` (
  `id` int(11) NOT NULL,
  `tipo` enum('FT - Factura','FR - Factura Recibo','FP - Factura Proforma','') NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `numero` varchar(30) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `vencimento` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `factura`
--

INSERT INTO `factura` (`id`, `tipo`, `cliente_id`, `pedido_id`, `numero`, `total`, `vencimento`, `estado`, `created_at`, `updated_at`) VALUES
(2, '', 2, 4, 'FT-20260911131905-536', 675000.00, '2026-09-18', 'Emitida', '2026-09-11 13:19:05', '2026-09-11 13:19:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `hospedagem_vps`
--

CREATE TABLE `hospedagem_vps` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `inicio` date NOT NULL,
  `expiracao` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `hospedagem_vps`
--

INSERT INTO `hospedagem_vps` (`id`, `cliente_id`, `produto_id`, `inicio`, `expiracao`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-09-10', '2027-09-10', 'Activo', '2026-09-10 13:05:21', '2026-09-10 13:05:21'),
(2, 2, 2, '2026-09-10', '2028-09-10', 'Inactivo', '2026-09-10 13:06:01', '2026-09-10 13:12:10'),
(3, 2, 6, '2026-09-16', '2027-09-16', 'Activo', '2026-09-16 15:12:30', '2026-09-16 15:12:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_pedido`
--

CREATE TABLE `item_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `dominio_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(15,2) NOT NULL,
  `periodo` varchar(30) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `item_pedido`
--

INSERT INTO `item_pedido` (`id`, `pedido_id`, `produto_id`, `dominio_id`, `quantidade`, `preco`, `periodo`, `subtotal`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 2, 5, 45000.00, '1 ano', 225000.00, '2026-09-11 10:47:24', '2026-09-11 10:47:24'),
(4, 2, 2, 2, 10, 45000.00, '2 anos', 450000.00, '2026-09-15 09:17:50', '2026-09-15 09:17:50'),
(5, 1, 1, 1, 1, 24000.00, '1 ano', 24000.00, '2026-09-15 14:07:12', '2026-09-15 14:07:12'),
(6, 1, 4, 1, 1, 30000.00, '1 ano', 30000.00, '2026-09-16 10:14:10', '2026-09-16 10:14:10'),
(7, 1, 5, 1, 1, 8000.00, '1 ano', 8000.00, '2026-09-16 10:46:30', '2026-09-16 10:46:30'),
(8, 3, 6, 3, 1, 120000.00, '1 ano', 120000.00, '2026-09-16 15:05:43', '2026-09-16 15:05:43'),
(9, 3, 3, 3, 1, 150000.00, '1 ano', 150000.00, '2026-09-16 15:43:48', '2026-09-16 15:43:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagem_ticket`
--

CREATE TABLE `mensagem_ticket` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamento`
--

CREATE TABLE `pagamento` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data_pagamento` datetime NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `pedido`
--

INSERT INTO `pedido` (`id`, `cliente_id`, `numero`, `total`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 'PED-20260911101548-9', 62000.00, 'Pendente', '2026-09-11 10:15:48', '2026-09-16 10:46:30'),
(2, 1, 'PED-20260911102007-2', 675000.00, 'Pendente', '2026-09-11 10:20:07', '2026-09-15 09:17:50'),
(3, 2, 'PED-20260911102012-6', 270000.00, 'Pendente', '2026-09-11 10:20:12', '2026-09-16 15:43:48'),
(4, 2, 'PED-20260911102017-9', 675000.00, 'Pendente', '2026-09-11 10:20:17', '2026-09-11 12:27:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `plataforma`
--

CREATE TABLE `plataforma` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `dominio` varchar(150) NOT NULL,
  `moeda` varchar(3) NOT NULL,
  `estado` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `preco`
--

CREATE TABLE `preco` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `periodo` varchar(30) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `preco`
--

INSERT INTO `preco` (`id`, `produto_id`, `periodo`, `valor`, `created_at`, `updated_at`) VALUES
(1, 2, '1 ano', 25000.00, '2026-09-09 15:44:16', '2026-09-09 15:44:16'),
(2, 2, '2 anos', 45000.00, '2026-09-09 15:51:39', '2026-09-09 15:51:39'),
(3, 1, '1 ano', 24000.00, '2026-09-15 14:06:54', '2026-09-15 14:06:54'),
(4, 4, '1 ano', 30000.00, '2026-09-16 10:08:24', '2026-09-16 10:08:24'),
(5, 5, '1 ano', 8000.00, '2026-09-16 10:44:46', '2026-09-16 10:44:46'),
(6, 6, '1 ano', 120000.00, '2026-09-16 14:50:53', '2026-09-16 14:50:53'),
(7, 3, '1 ano', 150000.00, '2026-09-16 15:34:30', '2026-09-16 15:34:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto`
--

CREATE TABLE `produto` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `estado` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produto`
--

INSERT INTO `produto` (`id`, `categoria_id`, `nome`, `descricao`, `tipo`, `estado`, `created_at`, `updated_at`) VALUES
(1, 2, 'saftview.ao', 'dominio .ao', 'nacional', 1, '2026-08-31 16:45:10', '2026-09-09 15:28:59'),
(2, 1, 'Alojamentos', 'Hospedar o site ', 'Website', 1, '2026-09-09 15:25:00', '2026-09-09 15:25:00'),
(3, 1, 'Criação de Um Sistema Web', 'Aplicação Web', 'website', 1, '2026-09-09 15:28:29', '2026-09-09 15:28:29'),
(4, 6, 'Serviço de Email', 'Serviço de email profissional associado ao domínio', 'Email', 1, '2026-09-16 10:04:46', '2026-09-16 10:04:46'),
(5, 7, 'Certificado SSL', 'Certificado SSL para proteção e segurança do domín', 'SSL', 1, '2026-09-16 10:43:49', '2026-09-16 10:43:49'),
(6, 8, 'VPS NVMe 2', '1 vCPU 4 GB de RAM 50 GB de Armazenamento', '', 1, '2026-09-16 16:27:11', '2026-09-16 16:27:11');

-- --------------------------------------------------------

--
-- Estrutura da tabela `provisionamento`
--

CREATE TABLE `provisionamento` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `item_pedido_id` int(11) NOT NULL,
  `tipo_servico` enum('Hospedagem VPS','Website','Dominio','Alojamento','Servico de Email','Certificado SSL') NOT NULL,
  `estado` enum('Pendente','Processando','Concluido','Falhou','Cancelado','Suspenso') NOT NULL DEFAULT 'Pendente',
  `mensagem` varchar(50) NOT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_conclusao` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `provisionamento`
--

INSERT INTO `provisionamento` (`id`, `pedido_id`, `item_pedido_id`, `tipo_servico`, `estado`, `mensagem`, `data_inicio`, `data_conclusao`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 'Alojamento', 'Concluido', 'Alojamento provisionado com sucesso.', '2026-09-15 11:45:07', '2026-09-15 11:45:08', '2026-09-14 11:20:41', '2026-09-15 11:45:08'),
(2, 1, 5, 'Dominio', 'Concluido', 'Domínio provisionado com sucesso.', '2026-09-15 14:17:09', '2026-09-15 14:17:09', '2026-09-15 14:12:03', '2026-09-15 14:17:09'),
(3, 1, 6, 'Servico de Email', 'Concluido', 'Serviço de email provisionado com sucesso.', '2026-09-16 10:21:54', '2026-09-16 10:21:54', '2026-09-16 10:18:56', '2026-09-16 10:21:54'),
(4, 1, 7, 'Certificado SSL', 'Concluido', 'Certificado SSL provisionado com sucesso.', '2026-09-16 10:49:37', '2026-09-16 10:49:37', '2026-09-16 10:48:08', '2026-09-16 10:49:37'),
(5, 3, 8, 'Hospedagem VPS', 'Concluido', 'Hospedagem VPS provisionada com sucesso.', '2026-09-16 15:12:30', '2026-09-16 15:12:30', '2026-09-16 15:12:03', '2026-09-16 15:12:30'),
(6, 3, 9, 'Website', 'Concluido', 'Website provisionado com sucesso.', '2026-09-16 15:46:35', '2026-09-16 15:46:35', '2026-09-16 15:46:15', '2026-09-16 15:46:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `servico_email`
--

CREATE TABLE `servico_email` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `dominio_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `inicio` date NOT NULL,
  `expiracao` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `servico_email`
--

INSERT INTO `servico_email` (`id`, `cliente_id`, `dominio_id`, `produto_id`, `inicio`, `expiracao`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, '2026-09-10', '2027-09-10', '0', '2026-09-10 12:13:57', '2026-09-10 12:13:57'),
(2, 1, 1, 4, '2026-09-16', '2027-09-16', 'Activo', '2026-09-16 10:21:54', '2026-09-16 10:21:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ticket`
--

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `assunto` varchar(200) NOT NULL,
  `prioridade` varchar(20) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

CREATE TABLE `utilizador` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`id`, `nome`, `email`, `telefone`, `senha`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Cliente Teste SAMWEB', 'cliente@samweb.ao', '+244 900 000 000', 'COLE_AQUI_O_HASH', 1, '2026-09-04 14:26:50', '2026-09-04 14:26:50'),
(2, 'Pavest', 'geral@pavest.ao', '+244 953650709', '$2y$10$wsM.Zzj3sT/Ga2l/EYOqJuX2byA67wlDVqNxaTdl7ZJz//VoE65Mq', 1, '2026-09-10 12:29:49', '2026-09-10 12:29:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `website`
--

CREATE TABLE `website` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `site` varchar(150) NOT NULL,
  `inicio` date NOT NULL,
  `prazo` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `website`
--

INSERT INTO `website` (`id`, `cliente_id`, `produto_id`, `site`, `inicio`, `prazo`, `estado`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'Website-3', '2026-09-16', '2027-09-16', 'Activo', '2026-09-16 15:46:35', '2026-09-16 15:46:35');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alojamento`
--
ALTER TABLE `alojamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `dominio_id` (`dominio_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `certificado_ssl`
--
ALTER TABLE `certificado_ssl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `dominio_id` (`dominio_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizador_id` (`utilizador_id`);

--
-- Índices para tabela `dominio`
--
ALTER TABLE `dominio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices para tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nif` (`nif`);

--
-- Índices para tabela `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Índices para tabela `hospedagem_vps`
--
ALTER TABLE `hospedagem_vps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `item_pedido`
--
ALTER TABLE `item_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `dominio_id` (`dominio_id`);

--
-- Índices para tabela `mensagem_ticket`
--
ALTER TABLE `mensagem_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `utilizador_id` (`utilizador_id`);

--
-- Índices para tabela `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fatura_id` (`factura_id`);

--
-- Índices para tabela `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`cliente_id`);

--
-- Índices para tabela `plataforma`
--
ALTER TABLE `plataforma`
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices para tabela `preco`
--
ALTER TABLE `preco`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `provisionamento`
--
ALTER TABLE `provisionamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_pedido_id` (`item_pedido_id`);

--
-- Índices para tabela `servico_email`
--
ALTER TABLE `servico_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dominio_id` (`dominio_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `website`
--
ALTER TABLE `website`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alojamento`
--
ALTER TABLE `alojamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `certificado_ssl`
--
ALTER TABLE `certificado_ssl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `dominio`
--
ALTER TABLE `dominio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `hospedagem_vps`
--
ALTER TABLE `hospedagem_vps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `item_pedido`
--
ALTER TABLE `item_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `mensagem_ticket`
--
ALTER TABLE `mensagem_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `preco`
--
ALTER TABLE `preco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `provisionamento`
--
ALTER TABLE `provisionamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `servico_email`
--
ALTER TABLE `servico_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `website`
--
ALTER TABLE `website`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alojamento`
--
ALTER TABLE `alojamento`
  ADD CONSTRAINT `alojamento_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `alojamento_ibfk_2` FOREIGN KEY (`dominio_id`) REFERENCES `dominio` (`id`),
  ADD CONSTRAINT `alojamento_ibfk_3` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);

--
-- Limitadores para a tabela `certificado_ssl`
--
ALTER TABLE `certificado_ssl`
  ADD CONSTRAINT `certificado_ssl_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `certificado_ssl_ibfk_2` FOREIGN KEY (`dominio_id`) REFERENCES `dominio` (`id`),
  ADD CONSTRAINT `certificado_ssl_ibfk_3` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);

--
-- Limitadores para a tabela `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`);

--
-- Limitadores para a tabela `dominio`
--
ALTER TABLE `dominio`
  ADD CONSTRAINT `dominio_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`);

--
-- Limitadores para a tabela `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`);

--
-- Limitadores para a tabela `hospedagem_vps`
--
ALTER TABLE `hospedagem_vps`
  ADD CONSTRAINT `hospedagem_vps_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `hospedagem_vps_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);

--
-- Limitadores para a tabela `item_pedido`
--
ALTER TABLE `item_pedido`
  ADD CONSTRAINT `item_pedido_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`),
  ADD CONSTRAINT `item_pedido_ibfk_2` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`),
  ADD CONSTRAINT `item_pedido_ibfk_3` FOREIGN KEY (`dominio_id`) REFERENCES `dominio` (`id`);

--
-- Limitadores para a tabela `mensagem_ticket`
--
ALTER TABLE `mensagem_ticket`
  ADD CONSTRAINT `mensagem_ticket_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`),
  ADD CONSTRAINT `mensagem_ticket_ibfk_2` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`);

--
-- Limitadores para a tabela `pagamento`
--
ALTER TABLE `pagamento`
  ADD CONSTRAINT `pagamento_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `factura` (`id`);

--
-- Limitadores para a tabela `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`);

--
-- Limitadores para a tabela `plataforma`
--
ALTER TABLE `plataforma`
  ADD CONSTRAINT `plataforma_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`);

--
-- Limitadores para a tabela `preco`
--
ALTER TABLE `preco`
  ADD CONSTRAINT `preco_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);

--
-- Limitadores para a tabela `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `produto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`);

--
-- Limitadores para a tabela `provisionamento`
--
ALTER TABLE `provisionamento`
  ADD CONSTRAINT `provisionamento_ibfk_1` FOREIGN KEY (`item_pedido_id`) REFERENCES `item_pedido` (`id`);

--
-- Limitadores para a tabela `servico_email`
--
ALTER TABLE `servico_email`
  ADD CONSTRAINT `servico_email_ibfk_1` FOREIGN KEY (`dominio_id`) REFERENCES `dominio` (`id`),
  ADD CONSTRAINT `servico_email_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `servico_email_ibfk_3` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);

--
-- Limitadores para a tabela `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`);

--
-- Limitadores para a tabela `website`
--
ALTER TABLE `website`
  ADD CONSTRAINT `website_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `website_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
