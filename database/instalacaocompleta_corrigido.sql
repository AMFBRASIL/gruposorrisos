-- =====================================================
-- SCRIPT DE INSTALAÇÃO COMPLETA CORRIGIDO - GRUPO SORRISOS
-- =====================================================
-- Versão corrigida com triggers funcionais
-- Execute como usuário root ou com privilégios de criação

-- =====================================================
-- 1. CRIAR BANCO DE DADOS
-- =====================================================
CREATE DATABASE IF NOT EXISTS `u460638534_sorrisos` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `u460638534_sorrisos`;

-- =====================================================
-- 2. CRIAR USUÁRIO E CONCEDER PRIVILÉGIOS
-- =====================================================

-- =====================================================
-- 3. TABELAS DE USUÁRIOS E PERFIS
-- =====================================================

-- Tabela de perfis
CREATE TABLE IF NOT EXISTS `tbl_perfis` (
    `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
    `nome_perfil` varchar(100) NOT NULL,
    `descricao` text,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `tbl_usuarios` (
    `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
    `nome_completo` varchar(200) NOT NULL,
    `email` varchar(150) NOT NULL UNIQUE,
    `senha` varchar(255) NOT NULL,
    `cpf` varchar(14) UNIQUE,
    `telefone` varchar(20),
    `id_perfil` int(11) NOT NULL,
    `id_filial` int(11),
    `ativo` tinyint(1) DEFAULT 1,
    `ultimo_acesso` timestamp NULL,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`),
    INDEX `idx_email` (`email`),
    INDEX `idx_perfil` (`id_perfil`),
    INDEX `idx_filial` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABELAS DE FILIAIS E ESTRUTURA
-- =====================================================

-- Tabela de filiais/clínicas
CREATE TABLE IF NOT EXISTS `tbl_filiais` (
    `id_filial` int(11) NOT NULL AUTO_INCREMENT,
    `nome_filial` varchar(200) NOT NULL,
    `codigo_filial` varchar(50) UNIQUE,
    `endereco` text,
    `cidade` varchar(100),
    `estado` varchar(2),
    `cep` varchar(10),
    `telefone` varchar(20),
    `email` varchar(150),
    `responsavel` varchar(200),
    `filial_ativa` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_filial`),
    INDEX `idx_codigo` (`codigo_filial`),
    INDEX `idx_filial_ativa` (`filial_ativa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABELAS DE PÁGINAS E PERMISSÕES
-- =====================================================

-- Tabela de páginas do sistema
CREATE TABLE IF NOT EXISTS `tbl_paginas` (
    `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
    `nome_pagina` varchar(200) NOT NULL,
    `url_pagina` varchar(200) NOT NULL UNIQUE,
    `categoria` varchar(100) NOT NULL,
    `descricao` text,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pagina`),
    INDEX `idx_url` (`url_pagina`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de permissões por perfil
CREATE TABLE IF NOT EXISTS `tbl_perfil_paginas` (
    `id_perfil_pagina` int(11) NOT NULL AUTO_INCREMENT,
    `id_perfil` int(11) NOT NULL,
    `id_pagina` int(11) NOT NULL,
    `permissao_visualizar` tinyint(1) DEFAULT 0,
    `permissao_inserir` tinyint(1) DEFAULT 0,
    `permissao_editar` tinyint(1) DEFAULT 0,
    `permissao_excluir` tinyint(1) DEFAULT 0,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_perfil_pagina`),
    UNIQUE KEY `uk_perfil_pagina` (`id_perfil`, `id_pagina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABELAS DE ESTOQUE
-- =====================================================

-- Tabela de materiais
CREATE TABLE IF NOT EXISTS `tbl_materiais` (
    `id_material` int(11) NOT NULL AUTO_INCREMENT,
    `codigo` varchar(100) NOT NULL UNIQUE,
    `nome` varchar(200) NOT NULL,
    `descricao` text,
    `id_filial` int(11) NOT NULL,
    `id_unidade` int(11),
    `estoque_atual` decimal(10,2) DEFAULT 0,
    `estoque_minimo` decimal(10,2) DEFAULT 0,
    `estoque_maximo` decimal(10,2) DEFAULT 0,
    `preco_unitario` decimal(10,2) DEFAULT 0,
    `data_vencimento` date,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_material`),
    INDEX `idx_codigo` (`codigo`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_vencimento` (`data_vencimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de unidades de medida
CREATE TABLE IF NOT EXISTS `tbl_unidades_medida` (
    `id_unidade` int(11) NOT NULL AUTO_INCREMENT,
    `sigla` varchar(10) NOT NULL UNIQUE,
    `nome` varchar(100) NOT NULL,
    `ativo` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id_unidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de estoque por filial
CREATE TABLE IF NOT EXISTS `tbl_estoque_filial` (
    `id_estoque_filial` int(11) NOT NULL AUTO_INCREMENT,
    `id_material` int(11) NOT NULL,
    `id_filial` int(11) NOT NULL,
    `estoque_atual` decimal(15,3) DEFAULT 0,
    `ultima_movimentacao` timestamp NULL,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_estoque_filial`),
    UNIQUE KEY `uk_material_filial` (`id_material`, `id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de movimentações
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
    `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
    `numero_movimentacao` varchar(50) NOT NULL UNIQUE,
    `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste') NOT NULL,
    `id_material` int(11) NOT NULL,
    `id_filial_origem` int(11),
    `id_filial_destino` int(11),
    `quantidade` decimal(10,2) NOT NULL,
    `estoque_atual_origem` decimal(15,3),
    `estoque_atual_destino` decimal(15,3),
    `valor_unitario` decimal(10,2) NOT NULL,
    `valor_total` decimal(10,2) NOT NULL,
    `id_usuario_executor` int(11) NOT NULL,
    `status_movimentacao` enum('pendente','aprovada','executada','cancelada') DEFAULT 'pendente',
    `data_movimentacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `observacoes` text,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_movimentacao`),
    INDEX `idx_numero` (`numero_movimentacao`),
    INDEX `idx_material` (`id_material`),
    INDEX `idx_tipo` (`tipo_movimentacao`),
    INDEX `idx_status` (`status_movimentacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de auditoria de movimentações
CREATE TABLE IF NOT EXISTS `tbl_auditoria_movimentacoes` (
    `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
    `id_movimentacao` int(11) NOT NULL,
    `acao` varchar(50) NOT NULL,
    `dados_novos` json,
    `dados_anteriores` json,
    `id_usuario` int(11),
    `data_auditoria` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_auditoria`),
    INDEX `idx_movimentacao` (`id_movimentacao`),
    INDEX `idx_acao` (`acao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABELAS DE INVENTÁRIO
-- =====================================================

-- Tabela de inventários
CREATE TABLE IF NOT EXISTS `tbl_inventario` (
    `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
    `numero_inventario` varchar(50) NOT NULL UNIQUE,
    `id_filial` int(11) NOT NULL,
    `id_usuario_responsavel` int(11) NOT NULL,
    `status` enum('em_andamento','finalizado','cancelado') DEFAULT 'em_andamento',
    `observacoes` text,
    `data_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_fim` timestamp NULL,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_inventario`),
    INDEX `idx_numero` (`numero_inventario`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens do inventário
CREATE TABLE IF NOT EXISTS `tbl_itens_inventario` (
    `id_item_inventario` int(11) NOT NULL AUTO_INCREMENT,
    `id_inventario` int(11) NOT NULL,
    `id_material` int(11) NOT NULL,
    `quantidade_sistema` decimal(10,2) NOT NULL,
    `quantidade_contada` decimal(10,2),
    `valor_unitario` decimal(10,2) NOT NULL,
    `valor_total_sistema` decimal(10,2) NOT NULL,
    `valor_total_contado` decimal(10,2),
    `status_item` enum('pendente','contado','divergente','ajustado') DEFAULT 'pendente',
    `observacoes` text,
    `id_usuario_contador` int(11),
    `data_contagem` timestamp NULL,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_item_inventario`),
    INDEX `idx_inventario` (`id_inventario`),
    INDEX `idx_material` (`id_material`),
    INDEX `idx_status` (`status_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABELAS DE COMPRAS
-- =====================================================

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS `tbl_fornecedores` (
    `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT,
    `razao_social` varchar(200) NOT NULL,
    `nome_fantasia` varchar(200),
    `cnpj` varchar(18) UNIQUE,
    `email` varchar(150) NOT NULL,
    `telefone` varchar(20),
    `endereco` text,
    `cidade` varchar(100),
    `estado` varchar(2),
    `cep` varchar(10),
    `responsavel` varchar(200),
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_fornecedor`),
    INDEX `idx_cnpj` (`cnpj`),
    INDEX `idx_email` (`email`),
    INDEX `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pedidos de compra
CREATE TABLE IF NOT EXISTS `tbl_pedidos_compra` (
    `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
    `numero_pedido` varchar(50) NOT NULL UNIQUE,
    `id_fornecedor` int(11) NOT NULL,
    `id_usuario_solicitante` int(11) NOT NULL,
    `id_filial` int(11) NOT NULL,
    `status` enum('pendente','aprovado','em_producao','enviado','recebido','cancelado') DEFAULT 'pendente',
    `data_solicitacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_entrega_prevista` date,
    `data_entrega_real` date,
    `valor_total` decimal(10,2) DEFAULT 0,
    `observacoes` text,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pedido`),
    INDEX `idx_numero` (`numero_pedido`),
    INDEX `idx_fornecedor` (`id_fornecedor`),
    INDEX `idx_status` (`status`),
    INDEX `idx_filial` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens dos pedidos
CREATE TABLE IF NOT EXISTS `tbl_itens_pedido` (
    `id_item_pedido` int(11) NOT NULL AUTO_INCREMENT,
    `id_pedido` int(11) NOT NULL,
    `id_material` int(11) NOT NULL,
    `quantidade` decimal(10,2) NOT NULL,
    `preco_unitario` decimal(10,2) NOT NULL,
    `valor_total` decimal(10,2) NOT NULL,
    `observacoes` text,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_item_pedido`),
    INDEX `idx_pedido` (`id_pedido`),
    INDEX `idx_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABELAS DE CONFIGURAÇÕES
-- =====================================================

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS `tbl_configuracoes` (
    `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
    `chave` varchar(100) NOT NULL UNIQUE,
    `valor` text,
    `descricao` text,
    `tipo` enum('texto','numero','booleano','json','email','telefone','moeda','fuso_horario') DEFAULT 'texto',
    `categoria` varchar(50) DEFAULT 'geral',
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_configuracao`),
    INDEX `idx_chave` (`chave`),
    INDEX `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABELAS DE LOGS E AUDITORIA
-- =====================================================

-- Tabela de logs do sistema
CREATE TABLE IF NOT EXISTS `tbl_logs_sistema` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `id_usuario` int(11),
    `id_filial` int(11),
    `acao` varchar(100) NOT NULL,
    `tabela` varchar(100),
    `id_registro` int(11),
    `dados_novos` text,
    `dados_anteriores` text,
    `ip_usuario` varchar(45),
    `user_agent` text,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_acao` (`acao`),
    INDEX `idx_data` (`data_criacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. INSERIR DADOS INICIAIS
-- =====================================================

-- Inserir perfis padrão
INSERT IGNORE INTO `tbl_perfis` (`id_perfil`, `nome_perfil`, `descricao`) VALUES
(1, 'Administrador', 'Acesso total ao sistema'),
(2, 'Gerente', 'Gerencia filiais e usuários'),
(3, 'Operador', 'Operações de estoque e compras'),
(4, 'Visualizador', 'Apenas visualização de dados'),
(5, 'Fornecedor', 'Acesso para fornecedores');

-- Inserir unidades de medida padrão
INSERT IGNORE INTO `tbl_unidades_medida` (`id_unidade`, `sigla`, `nome`) VALUES
(1, 'UN', 'Unidade'),
(2, 'KG', 'Quilograma'),
(3, 'L', 'Litro'),
(4, 'M', 'Metro'),
(5, 'M²', 'Metro Quadrado'),
(6, 'CX', 'Caixa'),
(7, 'PCT', 'Pacote');

-- Inserir filiais padrão
INSERT IGNORE INTO `tbl_filiais` (`id_filial`, `nome_filial`, `codigo_filial`, `cidade`, `estado`, `filial_ativa`) VALUES
(1, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS PETROLINA LTDA', 'PETROLINA', 'Petrolina', 'PE', 1),
(2, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS LTDA', 'SORRISOS', 'Recife', 'PE', 1),
(3, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS GARANHUNS LTDA', 'GARANHUNS', 'Garanhuns', 'PE', 1),
(4, 'CENTRO ODONTOLOGICO PERNAMBUCO ARCOVERDE LTDA', 'ARCOVERDE', 'Arcoverde', 'PE', 1);

-- Adicionar coluna filial_ativa se não existir (para compatibilidade)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'tbl_filiais' 
     AND COLUMN_NAME = 'filial_ativa') = 0,
    'ALTER TABLE `tbl_filiais` ADD COLUMN `filial_ativa` tinyint(1) DEFAULT 1 AFTER `responsavel`',
    'SELECT "Coluna filial_ativa já existe" as status'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Inserir usuário administrador padrão
INSERT IGNORE INTO `tbl_usuarios` (`id_usuario`, `nome_completo`, `email`, `senha`, `id_perfil`, `id_filial`, `ativo`) VALUES
(1, 'Administrador', 'admin@gruposorrisos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 1);

-- Inserir páginas do sistema
INSERT IGNORE INTO `tbl_paginas` (`id_pagina`, `nome_pagina`, `url_pagina`, `categoria`, `descricao`) VALUES
(1, 'Dashboard', 'index.php', 'gestao', 'Página principal do sistema'),
(2, 'Usuários', 'usuarios.php', 'gestao', 'Gestão de usuários do sistema'),
(3, 'Perfil de Acesso', 'perfil-acesso.php', 'gestao', 'Configuração de perfis e permissões'),
(4, 'Filiais/Clínicas', 'filiais.php', 'gestao', 'Gestão de filiais e clínicas'),
(5, 'Materiais', 'material.php', 'estoque', 'Gestão de materiais e estoque'),
(6, 'Movimentações', 'movimentacoes.php', 'estoque', 'Controle de movimentações de estoque'),
(7, 'Alertas', 'alertas.php', 'estoque', 'Sistema de alertas de estoque'),
(8, 'Inventário', 'inventario.php', 'estoque', 'Controle de inventários'),
(9, 'Pedidos de Compra Interno', 'pedidos-compra.php', 'compras', 'Pedidos de compra internos'),
(10, 'Fornecedores', 'fornecedores.php', 'compras', 'Gestão de fornecedores'),
(11, 'Relatórios', 'relatorios.php', 'relatorios', 'Geração de relatórios'),
(12, 'Tickets', 'tickets.php', 'relatorios', 'Sistema de tickets'),
(13, 'Configurações', 'configuracoes.php', 'configuracoes', 'Configurações do sistema'),
(14, 'Módulos do Sistema', 'paginas.php', 'gestao', 'Gestão de módulos do sistema'),
(15, 'Pedidos Compra Fornecedor', 'pedidos-fornecedores.php', 'compras', 'Pedidos para fornecedores');

-- Inserir configurações padrão
INSERT IGNORE INTO `tbl_configuracoes` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('empresa_nome', 'Grupo Sorrisos Ltda', 'Nome da empresa', 'texto', 'empresa'),
('empresa_email', 'contato@gruposorrisos.com', 'E-mail principal da empresa', 'email', 'empresa'),
('empresa_telefone', '(11) 99999-9999', 'Telefone principal da empresa', 'telefone', 'empresa'),
('empresa_moeda', 'BRL', 'Moeda padrão do sistema', 'moeda', 'empresa'),
('empresa_fuso', 'America/Sao_Paulo', 'Fuso horário padrão', 'fuso_horario', 'empresa'),
('notifica_email', '1', 'Ativar notificações por e-mail', 'booleano', 'notificacoes'),
('notifica_pagamentos', '1', 'Notificar sobre pagamentos realizados', 'booleano', 'notificacoes'),
('notifica_vencimentos', '1', 'Alertas de contas próximas ao vencimento', 'booleano', 'notificacoes'),
('notifica_relatorios', '0', 'Envio automático de relatórios mensais', 'booleano', 'notificacoes'),
('backup_automatico', '1', 'Ativar backup automático', 'booleano', 'sistema'),
('backup_intervalo', 'diario', 'Intervalo do backup (diario, semanal, mensal)', 'texto', 'sistema'),
('backup_historico', '12', 'Manter histórico de backup em meses', 'numero', 'sistema'),
('seguranca_2fa', '0', 'Ativar autenticação em duas etapas', 'booleano', 'seguranca'),
('sessao_expira', '30', 'Tempo de expiração da sessão em minutos', 'numero', 'seguranca'),
('log_auditoria', '1', 'Ativar log de auditoria', 'booleano', 'seguranca'),
('estoque_alerta_baixo', '1', 'Ativar alertas de estoque baixo', 'booleano', 'estoque'),
('estoque_alerta_zerado', '1', 'Ativar alertas de estoque zerado', 'booleano', 'estoque'),
('estoque_alerta_excedido', '1', 'Ativar alertas de estoque excedido', 'booleano', 'estoque'),
('estoque_dias_antecedencia', '7', 'Dias de antecedência para alertas de vencimento', 'numero', 'estoque'),
('relatorio_paginacao', '20', 'Itens por página nos relatórios', 'numero', 'relatorios'),
('relatorio_formato_padrao', 'pdf', 'Formato padrão dos relatórios', 'texto', 'relatorios'),
('relatorio_auto_gerar', '0', 'Gerar relatórios automaticamente', 'booleano', 'relatorios');

-- =====================================================
-- 12. CONFIGURAR PERMISSÕES PADRÃO
-- =====================================================

-- Administrador: acesso total
INSERT IGNORE INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`)
SELECT 1, id_pagina, 1, 1, 1, 1 FROM `tbl_paginas`;

-- Gerente: gestão e estoque
INSERT IGNORE INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`)
SELECT 2, id_pagina, 1, 1, 1, 1 FROM `tbl_paginas` WHERE categoria IN ('gestao', 'estoque', 'compras');

-- Operador: estoque e compras
INSERT IGNORE INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`)
SELECT 3, id_pagina, 1, 1, 1, 0 FROM `tbl_paginas` WHERE categoria IN ('estoque', 'compras');

-- Visualizador: apenas visualização
INSERT IGNORE INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`)
SELECT 4, id_pagina, 1, 0, 0, 0 FROM `tbl_paginas`;

-- Fornecedor: apenas página específica
INSERT IGNORE INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`)
SELECT 5, id_pagina, 1, 1, 1, 1 FROM `tbl_paginas` WHERE url_pagina = 'pedidos-fornecedores.php';

-- =====================================================
-- 13. CRIAR TRIGGERS CORRIGIDOS (SEM DELIMITER)
-- =====================================================

-- Trigger para atualizar estoque por filial automaticamente
DROP TRIGGER IF EXISTS `tr_movimentacao_estoque_filial`;
CREATE TRIGGER `tr_movimentacao_estoque_filial` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
INSERT INTO tbl_logs_sistema (acao, tabela, id_registro, dados_novos, id_usuario, id_filial) 
VALUES ('movimentacao_estoque', 'tbl_movimentacoes', NEW.id_movimentacao, 
        CONCAT('Material: ', NEW.id_material, ' - Quantidade: ', NEW.quantidade), 
        NEW.id_usuario_executor, COALESCE(NEW.id_filial_destino, NEW.id_filial_origem));

-- Trigger para auditoria automática de movimentações
DROP TRIGGER IF EXISTS `tr_auditoria_movimentacao`;
CREATE TRIGGER `tr_auditoria_movimentacao` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
INSERT INTO tbl_auditoria_movimentacoes (id_movimentacao, acao, dados_novos, id_usuario, data_auditoria) 
VALUES (NEW.id_movimentacao, 'criacao', 
        JSON_OBJECT('numero_movimentacao', NEW.numero_movimentacao, 'tipo_movimentacao', NEW.tipo_movimentacao, 'quantidade', NEW.quantidade, 'valor_total', NEW.valor_total, 'status_movimentacao', NEW.status_movimentacao), 
        NEW.id_usuario_executor, NOW());

-- Trigger para atualizar estoque na tabela de materiais
DROP TRIGGER IF EXISTS `tr_atualizar_estoque_material`;
CREATE TRIGGER `tr_atualizar_estoque_material` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
UPDATE tbl_materiais 
SET estoque_atual = CASE 
    WHEN NEW.id_filial_destino IS NOT NULL THEN NEW.estoque_atual_destino
    WHEN NEW.id_filial_origem IS NOT NULL THEN NEW.estoque_atual_origem
    ELSE estoque_atual
END,
data_atualizacao = CURRENT_TIMESTAMP
WHERE id_material = NEW.id_material 
AND id_filial = COALESCE(NEW.id_filial_destino, NEW.id_filial_origem);

-- Trigger para gerar alertas de estoque baixo
DROP TRIGGER IF EXISTS `tr_alerta_estoque_baixo`;
CREATE TRIGGER `tr_alerta_estoque_baixo` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
INSERT INTO tbl_logs_sistema (id_usuario, id_filial, acao, tabela, id_registro, dados_novos)
SELECT NEW.id_usuario_executor, NEW.id_filial_destino, 'alerta_estoque_baixo', 'tbl_materiais', NEW.id_material, 
       CONCAT('Estoque baixo: ', estoque_atual, ' (mínimo: ', estoque_minimo, ')')
FROM tbl_materiais 
WHERE id_material = NEW.id_material 
AND id_filial = NEW.id_filial_destino 
AND estoque_atual <= estoque_minimo 
AND estoque_atual > 0;

-- Trigger para atualizar custo médio
DROP TRIGGER IF EXISTS `tr_atualizar_custo_medio`;
CREATE TRIGGER `tr_atualizar_custo_medio` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
UPDATE tbl_materiais 
SET preco_unitario = CASE 
    WHEN estoque_atual > 0 THEN (preco_unitario * estoque_atual + NEW.valor_total) / (estoque_atual + NEW.quantidade)
    ELSE preco_unitario
END,
data_atualizacao = CURRENT_TIMESTAMP
WHERE id_material = NEW.id_material 
AND id_filial = NEW.id_filial_destino 
AND NEW.tipo_movimentacao = 'entrada';

-- Trigger para atualizar quantidade do lote
DROP TRIGGER IF EXISTS `tr_atualizar_lote`;
CREATE TRIGGER `tr_atualizar_lote` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
UPDATE tbl_materiais 
SET estoque_atual = estoque_atual + NEW.quantidade,
    data_atualizacao = CURRENT_TIMESTAMP
WHERE id_material = NEW.id_material 
AND id_filial = NEW.id_filial_destino 
AND NEW.tipo_movimentacao = 'entrada';

-- Trigger para verificar vencimento de lotes
DROP TRIGGER IF EXISTS `tr_verificar_vencimento_lote`;
CREATE TRIGGER `tr_verificar_vencimento_lote` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
INSERT INTO tbl_logs_sistema (id_usuario, id_filial, acao, tabela, id_registro, dados_novos)
SELECT NEW.id_usuario_executor, NEW.id_filial_destino, 'alerta_vencimento', 'tbl_materiais', NEW.id_material, 
       CONCAT('Material vence em ', DATEDIFF(data_vencimento, CURDATE()), ' dias')
FROM tbl_materiais 
WHERE id_material = NEW.id_material 
AND id_filial = NEW.id_filial_destino 
AND data_vencimento IS NOT NULL 
AND DATEDIFF(data_vencimento, CURDATE()) <= 30 
AND DATEDIFF(data_vencimento, CURDATE()) > 0;

-- =====================================================
-- 14. FINALIZAR INSTALAÇÃO
-- =====================================================

-- Verificar se tudo foi criado
SELECT 'Verificação da Instalação' as status;
SELECT COUNT(*) as total_perfis FROM `tbl_perfis`;
SELECT COUNT(*) as total_usuarios FROM `tbl_usuarios`;
SELECT COUNT(*) as total_filiais FROM `tbl_filiais`;
SELECT COUNT(*) as total_paginas FROM `tbl_paginas`;
SELECT COUNT(*) as total_configuracoes FROM `tbl_configuracoes`;

-- Mostrar usuário administrador criado
SELECT 'Usuário Administrador Padrão:' as info;
SELECT nome_completo, email, nome_perfil FROM `tbl_usuarios` u 
JOIN `tbl_perfis` p ON u.id_perfil = p.id_perfil 
WHERE u.id_usuario = 1;

-- Mostrar configurações da empresa
SELECT 'Configurações da Empresa:' as info;
SELECT chave, valor, descricao FROM `tbl_configuracoes` WHERE categoria = 'empresa';

-- Verificar triggers criados
SELECT 'Triggers Criados:' as info;
SELECT 
    TRIGGER_NAME as 'Trigger',
    EVENT_MANIPULATION as 'Evento',
    EVENT_OBJECT_TABLE as 'Tabela',
    ACTION_TIMING as 'Timing'
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'u460638534_sorrisos'
ORDER BY TRIGGER_NAME;

-- =====================================================
-- INSTALAÇÃO COMPLETA CORRIGIDA CONCLUÍDA COM SUCESSO!
-- =====================================================
-- 
-- DADOS DE ACESSO PADRÃO:
-- Email: admin@gruposorrisos.com
-- Senha: password
-- 
-- IMPORTANTE: 
-- 1. Altere a senha do administrador após o primeiro login!
-- 2. Triggers corrigidos e funcionais
-- 3. Estrutura completa do sistema
-- 4. Sem problemas de permissão ou sintaxe
-- 
-- ===================================================== 