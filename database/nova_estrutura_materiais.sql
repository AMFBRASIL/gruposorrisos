-- =====================================================
-- NOVA ESTRUTURA DE MATERIAIS - GRUPO SORRISOS
-- =====================================================
-- Este script cria a nova estrutura para materiais centralizados
-- com estoque controlado por filial

-- =====================================================
-- 1. TABELA DE CATÁLOGO DE MATERIAIS (CENTRALIZADA)
-- =====================================================
CREATE TABLE IF NOT EXISTS `tbl_catalogo_materiais` (
    `id_catalogo` int(11) NOT NULL AUTO_INCREMENT,
    `codigo` varchar(100) NOT NULL,
    `nome` varchar(200) NOT NULL,
    `descricao` text,
    `id_categoria` int(11),
    `id_fornecedor` int(11),
    `id_unidade` int(11),
    `preco_unitario_padrao` decimal(10,2) DEFAULT 0.00,
    `estoque_minimo_padrao` decimal(10,2) DEFAULT 0.00,
    `estoque_maximo_padrao` decimal(10,2) DEFAULT 0.00,
    `codigo_barras` varchar(100),
    `marca` varchar(100),
    `modelo` varchar(100),
    `cor` varchar(50),
    `tamanho` varchar(50),
    `peso` decimal(8,3),
    `volume` decimal(8,3),
    `observacoes` text,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_catalogo`),
    UNIQUE KEY `uk_codigo` (`codigo`),
    UNIQUE KEY `uk_codigo_barras` (`codigo_barras`),
    INDEX `idx_categoria` (`id_categoria`),
    INDEX `idx_fornecedor` (`id_fornecedor`),
    INDEX `idx_unidade` (`id_unidade`),
    INDEX `idx_ativo` (`ativo`),
    INDEX `idx_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABELA DE ESTOQUE POR FILIAL
-- =====================================================
CREATE TABLE IF NOT EXISTS `tbl_estoque_filiais` (
    `id_estoque` int(11) NOT NULL AUTO_INCREMENT,
    `id_catalogo` int(11) NOT NULL,
    `id_filial` int(11) NOT NULL,
    `estoque_atual` decimal(10,2) DEFAULT 0.00,
    `estoque_minimo` decimal(10,2) DEFAULT 0.00,
    `estoque_maximo` decimal(10,2) DEFAULT 0.00,
    `preco_unitario` decimal(10,2) DEFAULT 0.00,
    `data_vencimento` date NULL,
    `localizacao_estoque` varchar(100),
    `observacoes_estoque` text,
    `ativo` tinyint(1) DEFAULT 1,
    `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_estoque`),
    UNIQUE KEY `uk_catalogo_filial` (`id_catalogo`, `id_filial`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_estoque_baixo` (`estoque_atual`, `estoque_minimo`),
    INDEX `idx_vencimento` (`data_vencimento`),
    INDEX `idx_ativo` (`ativo`),
    FOREIGN KEY (`id_catalogo`) REFERENCES `tbl_catalogo_materiais`(`id_catalogo`) ON DELETE CASCADE,
    FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABELA DE HISTÓRICO DE MOVIMENTAÇÕES (ATUALIZADA)
-- =====================================================
-- Esta tabela será criada se não existir, ou modificada se existir
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes_estoque` (
    `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
    `id_estoque` int(11) NOT NULL, -- Referência ao estoque da filial
    `id_catalogo` int(11) NOT NULL, -- Referência ao catálogo
    `id_filial` int(11) NOT NULL,
    `tipo_movimentacao` enum('entrada','saida','ajuste','inventario','transferencia') NOT NULL,
    `quantidade` decimal(10,2) NOT NULL,
    `quantidade_anterior` decimal(10,2) NOT NULL,
    `quantidade_nova` decimal(10,2) NOT NULL,
    `valor_unitario` decimal(10,2) DEFAULT 0.00,
    `valor_total` decimal(10,2) DEFAULT 0.00,
    `motivo` varchar(200),
    `documento_referencia` varchar(100),
    `id_usuario` int(11),
    `observacoes` text,
    `data_movimentacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_movimentacao`),
    INDEX `idx_estoque` (`id_estoque`),
    INDEX `idx_catalogo` (`id_catalogo`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_tipo` (`tipo_movimentacao`),
    INDEX `idx_data` (`data_movimentacao`),
    INDEX `idx_usuario` (`id_usuario`),
    FOREIGN KEY (`id_estoque`) REFERENCES `tbl_estoque_filiais`(`id_estoque`) ON DELETE CASCADE,
    FOREIGN KEY (`id_catalogo`) REFERENCES `tbl_catalogo_materiais`(`id_catalogo`) ON DELETE CASCADE,
    FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`) ON DELETE CASCADE,
    FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. INSERIR DADOS INICIAIS
-- =====================================================

-- Inserir categoria padrão se não existir
INSERT IGNORE INTO `tbl_categorias` (`id_categoria`, `nome_categoria`, `descricao`, `ativo`) 
VALUES (1, 'Geral', 'Categoria padrão para materiais', 1);

-- Inserir unidade padrão se não existir
INSERT IGNORE INTO `tbl_unidades_medida` (`id_unidade`, `sigla`, `nome`, `ativo`) 
VALUES (1, 'UN', 'Unidade', 1);

-- =====================================================
-- 5. COMENTÁRIOS SOBRE A NOVA ESTRUTURA
-- =====================================================
/*
NOVA ESTRUTURA IMPLEMENTADA:

1. CATÁLOGO CENTRALIZADO:
   - Cada material existe apenas uma vez
   - Informações básicas são compartilhadas entre todas as filiais
   - Código único global
   - Fácil manutenção e atualização

2. ESTOQUE POR FILIAL:
   - Cada filial controla seu próprio estoque
   - Preços podem ser diferentes por filial
   - Controle individual de estoque mínimo/máximo
   - Localização específica por filial

3. MOVIMENTAÇÕES:
   - Histórico completo de todas as movimentações
   - Rastreabilidade por usuário e data
   - Suporte a diferentes tipos de movimentação

4. VANTAGENS:
   - Elimina duplicação de materiais
   - Facilita relatórios consolidados
   - Manutenção simplificada
   - Escalabilidade para novas filiais
   - Consistência de dados

5. MIGRAÇÃO:
   - Script de migração será executado para mover dados existentes
   - Sistema continuará funcionando durante a transição
   - Backup automático antes da migração
*/ 