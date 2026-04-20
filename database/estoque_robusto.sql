-- =====================================================
-- SISTEMA DE ESTOQUE ROBUSTO E AVANÇADO
-- =====================================================

-- 1. TABELA DE ESTOQUE POR FILIAL (Controle Multi-Filial)
CREATE TABLE IF NOT EXISTS `tbl_estoque_filial` (
  `id_estoque_filial` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `estoque_atual` decimal(15,3) NOT NULL DEFAULT 0,
  `estoque_minimo` decimal(15,3) DEFAULT 0,
  `estoque_maximo` decimal(15,3) DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL COMMENT 'Localização física (prateleira, corredor, etc.)',
  `custo_medio` decimal(15,4) DEFAULT 0,
  `ultima_movimentacao` datetime DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estoque_filial`),
  UNIQUE KEY `uk_material_filial` (`id_material`, `id_filial`),
  KEY `fk_estoque_filial_material` (`id_material`),
  KEY `fk_estoque_filial_filial` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA DE LOTES (Controle de Validade e Rastreabilidade)
CREATE TABLE IF NOT EXISTS `tbl_lotes` (
  `id_lote` int(11) NOT NULL AUTO_INCREMENT,
  `numero_lote` varchar(50) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `quantidade_inicial` decimal(15,3) NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
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
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE MOVIMENTAÇÕES AVANÇADA
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste','devolucao','inventario') NOT NULL,
  `subtipo_movimentacao` varchar(50) DEFAULT NULL COMMENT 'Compra, Venda, Transferência, Ajuste, etc.',
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
  `id_venda` int(11) DEFAULT NULL,
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
  KEY `idx_status_movimentacao` (`status_movimentacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABELA DE AUDITORIA DE MOVIMENTAÇÕES
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
  KEY `idx_data_auditoria` (`data_auditoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE HISTÓRICO DE CUSTOS
CREATE TABLE IF NOT EXISTS `tbl_historico_custos` (
  `id_historico_custo` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `custo_anterior` decimal(15,4) DEFAULT 0,
  `custo_atual` decimal(15,4) DEFAULT 0,
  `tipo_alteracao` enum('entrada','ajuste','reavaliacao') NOT NULL,
  `id_movimentacao` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historico_custo`),
  KEY `fk_historico_custo_material` (`id_material`),
  KEY `fk_historico_custo_filial` (`id_filial`),
  KEY `fk_historico_custo_movimentacao` (`id_movimentacao`),
  KEY `fk_historico_custo_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABELA DE ALERTAS DE ESTOQUE
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
  KEY `idx_prioridade_alerta` (`prioridade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABELA DE CONFIGURAÇÕES DE ESTOQUE
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

-- Inserir configurações padrão
INSERT INTO `tbl_configuracoes_estoque` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('estoque_baixo_percentual', '20', 'Percentual para considerar estoque baixo', 'integer', 'alertas'),
('dias_vencimento_alerta', '30', 'Dias antes do vencimento para alertar', 'integer', 'alertas'),
('custo_medio_metodo', 'ponderado', 'Método de cálculo do custo médio (ponderado, fifo, lifo)', 'string', 'custos'),
('movimentacao_aprovacao_obrigatoria', 'false', 'Se movimentações precisam de aprovação', 'boolean', 'workflow'),
('estoque_negativo_permitido', 'false', 'Se permite estoque negativo', 'boolean', 'estoque'),
('decimal_estoque', '3', 'Número de casas decimais para estoque', 'integer', 'formato'),
('decimal_valor', '4', 'Número de casas decimais para valores', 'integer', 'formato');

-- 8. ADICIONAR FOREIGN KEYS APÓS CRIAR TODAS AS TABELAS

-- Foreign keys para tbl_estoque_filial
ALTER TABLE `tbl_estoque_filial` 
ADD CONSTRAINT `fk_estoque_filial_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_estoque_filial_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE;

-- Foreign keys para tbl_lotes
ALTER TABLE `tbl_lotes` 
ADD CONSTRAINT `fk_lote_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_lote_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE;

-- Foreign keys para tbl_movimentacoes
ALTER TABLE `tbl_movimentacoes` 
ADD CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_lote` FOREIGN KEY (`id_lote`) REFERENCES `tbl_lotes` (`id_lote`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_usuario_executor` FOREIGN KEY (`id_usuario_executor`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_auditoria_movimentacoes
ALTER TABLE `tbl_auditoria_movimentacoes` 
ADD CONSTRAINT `fk_auditoria_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_historico_custos
ALTER TABLE `tbl_historico_custos` 
ADD CONSTRAINT `fk_historico_custo_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_historico_custo_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_historico_custo_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_historico_custo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_alertas_estoque
ALTER TABLE `tbl_alertas_estoque` 
ADD CONSTRAINT `fk_alerta_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_alerta_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_alerta_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL;

-- 9. ÍNDICES ADICIONAIS PARA PERFORMANCE
CREATE INDEX `idx_movimentacao_data_tipo` ON `tbl_movimentacoes` (`data_movimentacao`, `tipo_movimentacao`);
CREATE INDEX `idx_movimentacao_material_data` ON `tbl_movimentacoes` (`id_material`, `data_movimentacao`);
CREATE INDEX `idx_lote_material_validade` ON `tbl_lotes` (`id_material`, `data_validade`, `status`);
CREATE INDEX `idx_estoque_filial_atual` ON `tbl_estoque_filial` (`estoque_atual`, `id_filial`);
CREATE INDEX `idx_alerta_status_prioridade` ON `tbl_alertas_estoque` (`status`, `prioridade`, `tipo_alerta`);

-- 10. VIEWS PARA CONSULTAS

-- View para estoque atual por filial
CREATE OR REPLACE VIEW `vw_estoque_atual` AS
SELECT 
    m.id_material,
    m.codigo as codigo_material,
    m.nome as nome_material,
    m.unidade,
    f.id_filial,
    f.nome_filial,
    COALESCE(ef.estoque_atual, 0) as estoque_atual,
    COALESCE(ef.estoque_minimo, 0) as estoque_minimo,
    COALESCE(ef.estoque_maximo, 0) as estoque_maximo,
    COALESCE(ef.custo_medio, 0) as custo_medio,
    ef.localizacao,
    ef.ultima_movimentacao,
    CASE 
        WHEN COALESCE(ef.estoque_atual, 0) <= COALESCE(ef.estoque_minimo, 0) THEN 'baixo'
        WHEN COALESCE(ef.estoque_atual, 0) = 0 THEN 'zerado'
        WHEN COALESCE(ef.estoque_atual, 0) >= COALESCE(ef.estoque_maximo, 999999) THEN 'alto'
        ELSE 'normal'
    END as status_estoque
FROM tbl_materiais m
CROSS JOIN tbl_filiais f
LEFT JOIN tbl_estoque_filial ef ON m.id_material = ef.id_material AND f.id_filial = ef.id_filial
WHERE m.ativo = 1 AND f.filial_ativa = 1;

-- View para movimentações com detalhes
CREATE OR REPLACE VIEW `vw_movimentacoes_detalhadas` AS
SELECT 
    m.*,
    mat.codigo as codigo_material,
    mat.nome as nome_material,
    mat.unidade as unidade_material,
    l.numero_lote,
    l.data_validade,
    f_origem.nome_filial as filial_origem,
    f_destino.nome_filial as filial_destino,
    forn.razao_social as nome_fornecedor,
    cli.nome_cliente,
    u_sol.nome_completo as nome_usuario_solicitante,
    u_exec.nome_completo as nome_usuario_executor
FROM tbl_movimentacoes m
LEFT JOIN tbl_materiais mat ON m.id_material = mat.id_material
LEFT JOIN tbl_lotes l ON m.id_lote = l.id_lote
LEFT JOIN tbl_filiais f_origem ON m.id_filial_origem = f_origem.id_filial
LEFT JOIN tbl_filiais f_destino ON m.id_filial_destino = f_destino.id_filial
LEFT JOIN tbl_fornecedores forn ON m.id_fornecedor = forn.id_fornecedor
LEFT JOIN tbl_clientes cli ON m.id_cliente = cli.id_cliente
LEFT JOIN tbl_usuarios u_sol ON m.id_usuario_solicitante = u_sol.id_usuario
LEFT JOIN tbl_usuarios u_exec ON m.id_usuario_executor = u_exec.id_usuario; 