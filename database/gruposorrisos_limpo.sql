-- =====================================================
-- SISTEMA DE ESTOQUE GRUPO SORRISOS - ESTRUTURA LIMPA
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. TABELAS DE CONFIGURAÇÃO E SISTEMA
-- --------------------------------------------------------

-- Tabela de filiais
CREATE TABLE IF NOT EXISTS `tbl_filiais` (
  `id_filial` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_filial` varchar(10) NOT NULL COMMENT 'Código único da filial',
  `nome_filial` varchar(200) NOT NULL COMMENT 'Nome da filial',
  `razao_social` varchar(200) NOT NULL COMMENT 'Razão social da filial',
  `cnpj` varchar(18) DEFAULT NULL COMMENT 'CNPJ da filial',
  `inscricao_estadual` varchar(20) DEFAULT NULL COMMENT 'Inscrição estadual',
  `endereco` text DEFAULT NULL COMMENT 'Endereço completo',
  `cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade',
  `estado` char(2) DEFAULT NULL COMMENT 'Estado',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP',
  `telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone',
  `email` varchar(150) DEFAULT NULL COMMENT 'E-mail',
  `responsavel` varchar(200) DEFAULT NULL COMMENT 'Responsável',
  `tipo_filial` enum('matriz','filial','polo') DEFAULT 'filial',
  `filial_ativa` tinyint(1) DEFAULT 1 COMMENT 'Status ativo/inativo',
  `data_inauguracao` date DEFAULT NULL COMMENT 'Data de inauguração',
  `observacoes` text DEFAULT NULL COMMENT 'Observações',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_filial`),
  UNIQUE KEY `codigo_filial` (`codigo_filial`),
  UNIQUE KEY `cnpj` (`cnpj`),
  KEY `idx_filial_ativa` (`filial_ativa`),
  KEY `idx_cidade_estado` (`cidade`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de perfis de usuário
CREATE TABLE IF NOT EXISTS `tbl_perfis` (
  `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
  `nome_perfil` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `tbl_usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `id_perfil` int(11) NOT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_acesso` timestamp NULL DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`),
  KEY `idx_usuarios_perfil` (`id_perfil`),
  KEY `idx_usuarios_filial` (`id_filial`),
  KEY `idx_usuarios_ativo` (`ativo`),
  CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`),
  CONSTRAINT `fk_usuarios_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de páginas do sistema
CREATE TABLE IF NOT EXISTS `tbl_paginas` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `nome_pagina` varchar(100) NOT NULL,
  `url_pagina` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pagina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de permissões
CREATE TABLE IF NOT EXISTS `tbl_permissoes` (
  `id_permissao` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `pode_visualizar` tinyint(1) DEFAULT 0,
  `pode_inserir` tinyint(1) DEFAULT 0,
  `pode_editar` tinyint(1) DEFAULT 0,
  `pode_excluir` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_permissao`),
  UNIQUE KEY `uk_perfil_pagina` (`id_perfil`,`id_pagina`),
  CONSTRAINT `fk_permissoes_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE,
  CONSTRAINT `fk_permissoes_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. TABELAS DE CATÁLOGO
-- --------------------------------------------------------

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS `tbl_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_pai` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`),
  KEY `categoria_pai` (`categoria_pai`),
  CONSTRAINT `fk_categorias_pai` FOREIGN KEY (`categoria_pai`) REFERENCES `tbl_categorias` (`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de unidades de medida
CREATE TABLE IF NOT EXISTS `tbl_unidades_medida` (
  `id_unidade` int(11) NOT NULL AUTO_INCREMENT,
  `sigla` varchar(10) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_unidade`),
  UNIQUE KEY `sigla` (`sigla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS `tbl_fornecedores` (
  `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(200) NOT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `inscricao_estadual` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contato_principal` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_fornecedor`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS `tbl_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. TABELAS DE ESTOQUE
-- --------------------------------------------------------

-- Tabela de materiais
CREATE TABLE IF NOT EXISTS `tbl_materiais` (
  `id_material` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `estoque_minimo` decimal(10,3) DEFAULT 0.000,
  `estoque_maximo` decimal(10,3) DEFAULT 0.000,
  `estoque_atual` decimal(10,3) DEFAULT 0.000,
  `localizacao_estoque` varchar(100) DEFAULT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_material`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_materiais_categoria` (`id_categoria`),
  KEY `idx_materiais_fornecedor` (`id_fornecedor`),
  KEY `idx_materiais_filial` (`id_filial`),
  KEY `idx_materiais_ativo` (`ativo`),
  KEY `idx_materiais_estoque` (`estoque_atual`),
  CONSTRAINT `fk_materiais_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias` (`id_categoria`),
  CONSTRAINT `fk_materiais_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE SET NULL,
  CONSTRAINT `fk_materiais_unidade` FOREIGN KEY (`id_unidade`) REFERENCES `tbl_unidades_medida` (`id_unidade`),
  CONSTRAINT `fk_materiais_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de estoque por filial (controle multi-filial)
CREATE TABLE IF NOT EXISTS `tbl_estoque_filial` (
  `id_estoque_filial` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `estoque_atual` decimal(15,3) NOT NULL DEFAULT 0,
  `estoque_minimo` decimal(15,3) DEFAULT 0,
  `estoque_maximo` decimal(15,3) DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL COMMENT 'Localização física',
  `custo_medio` decimal(15,4) DEFAULT 0,
  `ultima_movimentacao` datetime DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estoque_filial`),
  UNIQUE KEY `uk_material_filial` (`id_material`, `id_filial`),
  KEY `fk_estoque_filial_material` (`id_material`),
  KEY `fk_estoque_filial_filial` (`id_filial`),
  CONSTRAINT `fk_estoque_filial_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
  CONSTRAINT `fk_estoque_filial_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de lotes (controle de validade)
CREATE TABLE IF NOT EXISTS `tbl_lotes` (
  `id_lote` int(11) NOT NULL AUTO_INCREMENT,
  `numero_lote` varchar(50) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `quantidade_inicial` decimal(15,3) NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `custo_unitario` decimal(15,4) DEFAULT 0,
  `fornecedor_lote` varchar(200) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('ativo','vencido','consumido','cancelado') NOT NULL DEFAULT 'ativo',
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `uk_numero_lote` (`numero_lote`),
  KEY `fk_lote_material` (`id_material`),
  KEY `fk_lote_filial` (`id_filial`),
  KEY `idx_data_validade` (`data_validade`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_lote_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
  CONSTRAINT `fk_lote_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. TABELAS DE MOVIMENTAÇÕES
-- --------------------------------------------------------

-- Tabela de tipos de movimentação
CREATE TABLE IF NOT EXISTS `tbl_tipos_movimentacao` (
  `id_tipo_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tipo_movimentacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de movimentações
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste','devolucao','inventario') NOT NULL,
  `subtipo_movimentacao` varchar(50) DEFAULT NULL COMMENT 'Compra, Venda, Transferência, etc.',
  `id_material` int(11) NOT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `id_filial_origem` int(11) DEFAULT NULL,
  `id_filial_destino` int(11) DEFAULT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `estoque_anterior_origem` decimal(15,3) DEFAULT 0,
  `estoque_atual_origem` decimal(15,3) DEFAULT 0,
  `estoque_anterior_destino` decimal(15,3) DEFAULT 0,
  `estoque_atual_destino` decimal(15,3) DEFAULT 0,
  `valor_unitario` decimal(15,4) DEFAULT NULL,
  `valor_total` decimal(15,4) DEFAULT NULL,
  `custo_medio_anterior` decimal(15,4) DEFAULT 0,
  `custo_medio_atual` decimal(15,4) DEFAULT 0,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_pedido_compra` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `motivo_ajuste` varchar(200) DEFAULT NULL,
  `status_movimentacao` enum('pendente','aprovada','executada','cancelada','estornada') NOT NULL DEFAULT 'executada',
  `id_usuario_solicitante` int(11) DEFAULT NULL,
  `id_usuario_executor` int(11) NOT NULL,
  `data_solicitacao` datetime DEFAULT NULL,
  `data_aprovacao` datetime DEFAULT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimentacao`),
  UNIQUE KEY `numero_movimentacao` (`numero_movimentacao`),
  KEY `fk_movimentacao_material` (`id_material`),
  KEY `fk_movimentacao_lote` (`id_lote`),
  KEY `fk_movimentacao_filial_origem` (`id_filial_origem`),
  KEY `fk_movimentacao_filial_destino` (`id_filial_destino`),
  KEY `fk_movimentacao_fornecedor` (`id_fornecedor`),
  KEY `fk_movimentacao_cliente` (`id_cliente`),
  KEY `fk_movimentacao_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `fk_movimentacao_usuario_executor` (`id_usuario_executor`),
  KEY `idx_data_movimentacao` (`data_movimentacao`),
  KEY `idx_tipo_movimentacao` (`tipo_movimentacao`),
  KEY `idx_status_movimentacao` (`status_movimentacao`),
  CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_lote` FOREIGN KEY (`id_lote`) REFERENCES `tbl_lotes` (`id_lote`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_usuario_executor` FOREIGN KEY (`id_usuario_executor`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. TABELAS DE PEDIDOS
-- --------------------------------------------------------

-- Tabela de pedidos de compra
CREATE TABLE IF NOT EXISTS `tbl_pedidos_compra` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `id_usuario_solicitante` int(11) NOT NULL,
  `id_usuario_aprovador` int(11) DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  `data_entrega_prevista` date DEFAULT NULL,
  `data_entrega_realizada` date DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado','em_entrega','entregue','cancelado') DEFAULT 'pendente',
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `id_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `id_usuario_aprovador` (`id_usuario_aprovador`),
  KEY `idx_pedidos_filial` (`id_filial`),
  KEY `idx_pedidos_fornecedor` (`id_fornecedor`),
  KEY `idx_pedidos_status` (`status`),
  KEY `idx_pedidos_data` (`data_solicitacao`),
  CONSTRAINT `fk_pedidos_compra_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_pedidos_compra_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`),
  CONSTRAINT `fk_pedidos_compra_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`),
  CONSTRAINT `fk_pedidos_compra_usuario_aprovador` FOREIGN KEY (`id_usuario_aprovador`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens do pedido de compra
CREATE TABLE IF NOT EXISTS `tbl_itens_pedido_compra` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_material` (`id_material`),
  CONSTRAINT `fk_itens_pedido_compra_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbl_pedidos_compra` (`id_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_pedido_compra_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. TABELAS DE SUPORTE E TICKETS
-- --------------------------------------------------------

-- Tabela de categorias de tickets
CREATE TABLE IF NOT EXISTS `tbl_categorias_ticket` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_categoria` VARCHAR(100) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#007bff',
    `icone` VARCHAR(50) DEFAULT 'bi-tag',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de prioridades de tickets
CREATE TABLE IF NOT EXISTS `tbl_prioridades_ticket` (
    `id_prioridade` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_prioridade` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-flag',
    `tempo_esperado` INT DEFAULT 1440 COMMENT 'Tempo em minutos',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de status de tickets
CREATE TABLE IF NOT EXISTS `tbl_status_ticket` (
    `id_status` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_status` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-circle',
    `is_final` TINYINT(1) DEFAULT 0 COMMENT 'Se é um status final',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela principal de tickets
CREATE TABLE IF NOT EXISTS `tbl_tickets` (
    `id_ticket` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_ticket` VARCHAR(20) UNIQUE NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `id_categoria` INT,
    `id_prioridade` INT,
    `id_status` INT,
    `id_usuario_solicitante` INT,
    `id_usuario_atribuido` INT,
    `id_filial` INT,
    `data_abertura` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `data_fechamento` DATETIME NULL,
    `tempo_resolucao` INT NULL COMMENT 'Tempo em minutos',
    `avaliacao` TINYINT NULL COMMENT '1-5 estrelas',
    `comentario_avaliacao` TEXT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`id_status`),
    INDEX `idx_prioridade` (`id_prioridade`),
    INDEX `idx_usuario_solicitante` (`id_usuario_solicitante`),
    INDEX `idx_usuario_atribuido` (`id_usuario_atribuido`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_data_abertura` (`data_abertura`),
    CONSTRAINT `fk_tickets_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias_ticket`(`id_categoria`),
    CONSTRAINT `fk_tickets_prioridade` FOREIGN KEY (`id_prioridade`) REFERENCES `tbl_prioridades_ticket`(`id_prioridade`),
    CONSTRAINT `fk_tickets_status` FOREIGN KEY (`id_status`) REFERENCES `tbl_status_ticket`(`id_status`),
    CONSTRAINT `fk_tickets_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios`(`id_usuario`),
    CONSTRAINT `fk_tickets_usuario_atribuido` FOREIGN KEY (`id_usuario_atribuido`) REFERENCES `tbl_usuarios`(`id_usuario`),
    CONSTRAINT `fk_tickets_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`)
);

-- Tabela de comentários de tickets
CREATE TABLE IF NOT EXISTS `tbl_comentarios_ticket` (
    `id_comentario` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ticket` INT NOT NULL,
    `id_usuario` INT NOT NULL,
    `comentario` TEXT NOT NULL,
    `tipo` ENUM('comentario', 'status', 'atribuicao', 'prioridade') DEFAULT 'comentario',
    `dados_anteriores` JSON NULL,
    `dados_novos` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_tipo` (`tipo`),
    CONSTRAINT `fk_comentarios_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets`(`id_ticket`),
    CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`)
);

-- --------------------------------------------------------
-- 7. TABELAS DE AUDITORIA E LOGS
-- --------------------------------------------------------

-- Tabela de auditoria de movimentações
CREATE TABLE IF NOT EXISTS `tbl_auditoria_movimentacoes` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimentacao` int(11) NOT NULL,
  `acao` enum('criacao','alteracao','cancelamento','estorno') NOT NULL,
  `dados_anteriores` json DEFAULT NULL,
  `dados_novos` json DEFAULT NULL,
  `motivo_alteracao` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_auditoria` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `fk_auditoria_movimentacao` (`id_movimentacao`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  KEY `idx_data_auditoria` (`data_auditoria`),
  CONSTRAINT `fk_auditoria_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE CASCADE,
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs do sistema
CREATE TABLE IF NOT EXISTS `tbl_logs_sistema` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_logs_usuario` (`id_usuario`),
  KEY `idx_logs_filial` (`id_filial`),
  KEY `idx_logs_data` (`data_log`),
  KEY `idx_logs_acao` (`acao`),
  CONSTRAINT `fk_logs_sistema_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_logs_sistema_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. TABELAS DE CONFIGURAÇÕES
-- --------------------------------------------------------

-- Tabela de alertas de estoque
CREATE TABLE IF NOT EXISTS `tbl_alertas_estoque` (
  `id_alerta` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `tipo_alerta` enum('estoque_baixo','estoque_zerado','vencimento_proximo','vencido','estoque_alto') NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `quantidade_referencia` decimal(15,3) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `dias_vencimento` int(11) DEFAULT NULL,
  `status` enum('ativo','resolvido','ignorado') NOT NULL DEFAULT 'ativo',
  `prioridade` enum('baixa','media','alta','critica') NOT NULL DEFAULT 'media',
  `mensagem` text DEFAULT NULL,
  `id_usuario_responsavel` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_resolucao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alerta`),
  KEY `fk_alerta_material` (`id_material`),
  KEY `fk_alerta_filial` (`id_filial`),
  KEY `fk_alerta_usuario` (`id_usuario_responsavel`),
  KEY `idx_tipo_alerta` (`tipo_alerta`),
  KEY `idx_status_alerta` (`status`),
  KEY `idx_prioridade_alerta` (`prioridade`),
  CONSTRAINT `fk_alerta_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
  CONSTRAINT `fk_alerta_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
  CONSTRAINT `fk_alerta_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS `tbl_configuracoes_estoque` (
  `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `tipo` enum('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
  `categoria` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_configuracao`),
  UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- --------------------------------------------------------

CREATE INDEX `idx_movimentacao_data_tipo` ON `tbl_movimentacoes` (`data_movimentacao`, `tipo_movimentacao`);
CREATE INDEX `idx_movimentacao_material_data` ON `tbl_movimentacoes` (`id_material`, `data_movimentacao`);
CREATE INDEX `idx_lote_material_validade` ON `tbl_lotes` (`id_material`, `data_validade`, `status`);
CREATE INDEX `idx_estoque_filial_atual` ON `tbl_estoque_filial` (`estoque_atual`, `id_filial`);
CREATE INDEX `idx_alerta_status_prioridade` ON `tbl_alertas_estoque` (`status`, `prioridade`, `tipo_alerta`);

-- --------------------------------------------------------
-- 10. DADOS INICIAIS
-- --------------------------------------------------------

-- Inserir configurações padrão
INSERT INTO `tbl_configuracoes_estoque` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('estoque_baixo_percentual', '20', 'Percentual para considerar estoque baixo', 'integer', 'alertas'),
('dias_vencimento_alerta', '30', 'Dias antes do vencimento para alertar', 'integer', 'alertas'),
('custo_medio_metodo', 'ponderado', 'Método de cálculo do custo médio', 'string', 'custos'),
('movimentacao_aprovacao_obrigatoria', 'false', 'Se movimentações precisam de aprovação', 'boolean', 'workflow'),
('estoque_negativo_permitido', 'false', 'Se permite estoque negativo', 'boolean', 'estoque'),
('decimal_estoque', '3', 'Número de casas decimais para estoque', 'integer', 'formato'),
('decimal_valor', '4', 'Número de casas decimais para valores', 'integer', 'formato');

-- Inserir dados padrão para categorias de tickets
INSERT INTO `tbl_categorias_ticket` (`nome_categoria`, `descricao`, `cor`, `icone`) VALUES
('Suporte Técnico', 'Problemas técnicos e suporte', '#dc3545', 'bi-tools'),
('Sistema', 'Problemas com o sistema', '#007bff', 'bi-gear'),
('Estoque', 'Problemas relacionados ao estoque', '#28a745', 'bi-box-seam'),
('Financeiro', 'Problemas financeiros', '#ffc107', 'bi-currency-dollar'),
('RH', 'Recursos humanos', '#17a2b8', 'bi-people'),
('Outros', 'Outras categorias', '#6c757d', 'bi-three-dots');

-- Inserir dados padrão para prioridades de tickets
INSERT INTO `tbl_prioridades_ticket` (`nome_prioridade`, `descricao`, `cor`, `icone`, `tempo_esperado`) VALUES
('Baixa', 'Pode ser resolvido em até 72h', '#6c757d', 'bi-flag', 4320),
('Média', 'Deve ser resolvido em até 24h', '#ffc107', 'bi-flag-fill', 1440),
('Alta', 'Deve ser resolvido em até 4h', '#fd7e14', 'bi-exclamation-triangle', 240),
('Crítica', 'Deve ser resolvido imediatamente', '#dc3545', 'bi-exclamation-triangle-fill', 60);

-- Inserir dados padrão para status de tickets
INSERT INTO `tbl_status_ticket` (`nome_status`, `descricao`, `cor`, `icone`, `is_final`) VALUES
('Aberto', 'Ticket recém aberto', '#007bff', 'bi-circle-fill', 0),
('Em Análise', 'Ticket sendo analisado', '#ffc107', 'bi-clock', 0),
('Em Andamento', 'Ticket sendo trabalhado', '#17a2b8', 'bi-play-circle', 0),
('Aguardando Cliente', 'Aguardando resposta do cliente', '#6c757d', 'bi-pause-circle', 0),
('Aguardando Terceiros', 'Aguardando terceiros', '#fd7e14', 'bi-people', 0),
('Resolvido', 'Ticket resolvido', '#28a745', 'bi-check-circle', 1),
('Fechado', 'Ticket fechado', '#6c757d', 'bi-x-circle', 1),
('Cancelado', 'Ticket cancelado', '#dc3545', 'bi-x-circle-fill', 1);

COMMIT; 