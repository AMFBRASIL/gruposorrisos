-- Script para atualizar a estrutura da tabela tbl_paginas e inserir módulos
-- Grupo Sorrisos - Sistema de Gestão de Estoque

-- 1. Verificar se a tabela existe e criar se necessário
CREATE TABLE IF NOT EXISTS `tbl_paginas` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `nome_pagina` varchar(255) NOT NULL,
  `url_pagina` varchar(255) NOT NULL,
  `descricao` text,
  `categoria` varchar(100) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `cor` varchar(50) DEFAULT 'primary',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pagina`),
  UNIQUE KEY `uk_nome_pagina` (`nome_pagina`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_ordem` (`ordem`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Adicionar novas colunas se não existirem
ALTER TABLE `tbl_paginas` 
ADD COLUMN IF NOT EXISTS `categoria` varchar(100) DEFAULT NULL AFTER `descricao`,
ADD COLUMN IF NOT EXISTS `icone` varchar(100) DEFAULT NULL AFTER `categoria`,
ADD COLUMN IF NOT EXISTS `cor` varchar(50) DEFAULT 'primary' AFTER `icone`,
ADD COLUMN IF NOT EXISTS `ordem` int(11) DEFAULT 0 AFTER `cor`,
ADD COLUMN IF NOT EXISTS `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `ativo`,
ADD COLUMN IF NOT EXISTS `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `data_criacao`;

-- 3. Criar índices se não existirem
CREATE INDEX IF NOT EXISTS `idx_categoria` ON `tbl_paginas` (`categoria`);
CREATE INDEX IF NOT EXISTS `idx_ordem` ON `tbl_paginas` (`ordem`);
CREATE INDEX IF NOT EXISTS `idx_ativo` ON `tbl_paginas` (`ativo`);

-- 4. Limpar dados existentes (opcional - descomente se quiser recriar tudo)
-- TRUNCATE TABLE `tbl_paginas`;

-- 5. Inserir módulos do sistema
INSERT INTO `tbl_paginas` (`nome_pagina`, `url_pagina`, `descricao`, `categoria`, `icone`, `cor`, `ordem`, `ativo`) VALUES
-- Gestão
('Dashboard', 'index.php', 'Painel principal com visão geral do sistema', 'gestao', 'bi-speedometer2', 'primary', 1, 1),
('Usuários', 'usuarios.php', 'Gerenciamento de usuários e perfis de acesso', 'gestao', 'bi-people', 'primary', 2, 1),
('Perfil de Acesso', 'perfil-acesso.php', 'Gerenciamento de perfis e permissões', 'gestao', 'bi-shield-lock', 'primary', 3, 1),
('Filiais/Clínicas', 'filiais.php', 'Gestão de filiais e unidades', 'gestao', 'bi-hospital', 'danger', 4, 1),

-- Estoque
('Materiais', 'material.php', 'Controle de estoque e cadastro de materiais', 'estoque', 'bi-box-seam', 'success', 5, 1),
('Movimentações', 'movimentacoes.php', 'Controle de entrada, saída e transferências', 'estoque', 'bi-arrow-left-right', 'info', 6, 1),
('Alertas', 'alertas.php', 'Sistema de alertas de estoque', 'estoque', 'bi-exclamation-triangle', 'warning', 7, 1),
('Inventário', 'inventario.php', 'Controle de inventário físico', 'estoque', 'bi-clipboard-data', 'info', 8, 1),

-- Compras
('Pedidos de Compra', 'pedidos-compra.php', 'Gestão de pedidos e cotações', 'compras', 'bi-cart-check', 'warning', 9, 1),
('Fornecedores', 'fornecedores.php', 'Cadastro e gestão de fornecedores', 'compras', 'bi-building', 'secondary', 10, 1),

-- Relatórios
('Relatórios', 'relatorios.php', 'Relatórios e análises do sistema', 'relatorios', 'bi-graph-up', 'dark', 11, 1),
('Tickets', 'tickets.php', 'Sistema de tickets e suporte', 'relatorios', 'bi-ticket-detailed', 'info', 12, 1),

-- Configurações
('Configurações', 'configuracoes.php', 'Configurações gerais do sistema', 'configuracoes', 'bi-gear', 'dark', 13, 1),

-- Módulos de Acesso
('Módulos do Sistema', 'paginas.php', 'Visão geral de todos os módulos disponíveis', 'gestao', 'bi-grid-3x3-gap', 'primary', 14, 1)

ON DUPLICATE KEY UPDATE
  `url_pagina` = VALUES(`url_pagina`),
  `descricao` = VALUES(`descricao`),
  `categoria` = VALUES(`categoria`),
  `icone` = VALUES(`icone`),
  `cor` = VALUES(`cor`),
  `ordem` = VALUES(`ordem`),
  `ativo` = VALUES(`ativo`),
  `data_atualizacao` = CURRENT_TIMESTAMP;

-- 6. Criar tabela de controle de acesso às páginas (se não existir)
CREATE TABLE IF NOT EXISTS `tbl_paginas_acesso` (
  `id_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `id_pagina` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `data_acesso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_acesso` varchar(45) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id_acesso`),
  KEY `idx_id_pagina` (`id_pagina`),
  KEY `idx_id_usuario` (`id_usuario`),
  KEY `idx_data_acesso` (`data_acesso`),
  CONSTRAINT `fk_paginas_acesso_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE,
  CONSTRAINT `fk_paginas_acesso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Verificar se a tabela de permissões de páginas existe
CREATE TABLE IF NOT EXISTS `tbl_perfil_paginas` (
  `id_perfil_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `permissao_visualizar` tinyint(1) DEFAULT 1,
  `permissao_inserir` tinyint(1) DEFAULT 0,
  `permissao_editar` tinyint(1) DEFAULT 0,
  `permissao_excluir` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_perfil_pagina`),
  UNIQUE KEY `uk_perfil_pagina` (`id_perfil`, `id_pagina`),
  KEY `idx_id_perfil` (`id_perfil`),
  KEY `idx_id_pagina` (`id_pagina`),
  CONSTRAINT `fk_perfil_paginas_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE,
  CONSTRAINT `fk_perfil_paginas_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Inserir permissões padrão para todos os perfis e páginas
-- Primeiro, vamos inserir as permissões para o perfil Administrador
INSERT INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`, `ativo`)
SELECT 
    p.id_perfil,
    pg.id_pagina,
    1, 1, 1, 1, 1
FROM `tbl_perfis` p
CROSS JOIN `tbl_paginas` pg
WHERE p.nome_perfil = 'Administrador' AND pg.ativo = 1
ON DUPLICATE KEY UPDATE
    `permissao_visualizar` = 1,
    `permissao_inserir` = 1,
    `permissao_editar` = 1,
    `permissao_excluir` = 1,
    `ativo` = 1,
    `data_atualizacao` = CURRENT_TIMESTAMP;

-- 9. Verificar estrutura final
DESCRIBE `tbl_paginas`;

-- 10. Mostrar páginas inseridas
SELECT 
    `id_pagina`,
    `nome_pagina`,
    `categoria`,
    `icone`,
    `cor`,
    `ordem`,
    `ativo`
FROM `tbl_paginas` 
WHERE `ativo` = 1 
ORDER BY `ordem`, `nome_pagina`;

-- 11. Mostrar estatísticas
SELECT 
    `categoria`,
    COUNT(*) as total_paginas,
    SUM(CASE WHEN `ativo` = 1 THEN 1 ELSE 0 END) as paginas_ativas
FROM `tbl_paginas` 
GROUP BY `categoria` 
ORDER BY `categoria`; 