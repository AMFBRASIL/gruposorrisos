-- Script para criar a tabela de permissões de páginas por perfil
-- Grupo Sorrisos - Sistema de Gestão de Estoque

-- 1. Criar tabela de permissões de páginas por perfil
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
  KEY `idx_ativo` (`ativo`),
  CONSTRAINT `fk_perfil_paginas_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE,
  CONSTRAINT `fk_perfil_paginas_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Inserir permissões padrão para o perfil Administrador
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

-- 3. Inserir permissões padrão para o perfil Gerente
INSERT INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`, `ativo`)
SELECT 
    p.id_perfil,
    pg.id_pagina,
    1, 1, 1, 0, 1
FROM `tbl_perfis` p
CROSS JOIN `tbl_paginas` pg
WHERE p.nome_perfil = 'Gerente' AND pg.ativo = 1
ON DUPLICATE KEY UPDATE
    `permissao_visualizar` = 1,
    `permissao_inserir` = 1,
    `permissao_editar` = 1,
    `permissao_excluir` = 0,
    `ativo` = 1,
    `data_atualizacao` = CURRENT_TIMESTAMP;

-- 4. Inserir permissões padrão para o perfil Operador
INSERT INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`, `ativo`)
SELECT 
    p.id_perfil,
    pg.id_pagina,
    1, 1, 0, 0, 1
FROM `tbl_perfis` p
CROSS JOIN `tbl_paginas` pg
WHERE p.nome_perfil = 'Operador' AND pg.ativo = 1
ON DUPLICATE KEY UPDATE
    `permissao_visualizar` = 1,
    `permissao_inserir` = 1,
    `permissao_editar` = 0,
    `permissao_excluir` = 0,
    `ativo` = 1,
    `data_atualizacao` = CURRENT_TIMESTAMP;

-- 5. Inserir permissões padrão para o perfil Visualizador
INSERT INTO `tbl_perfil_paginas` (`id_perfil`, `id_pagina`, `permissao_visualizar`, `permissao_inserir`, `permissao_editar`, `permissao_excluir`, `ativo`)
SELECT 
    p.id_perfil,
    pg.id_pagina,
    1, 0, 0, 0, 1
FROM `tbl_perfis` p
CROSS JOIN `tbl_paginas` pg
WHERE p.nome_perfil = 'Visualizador' AND pg.ativo = 1
ON DUPLICATE KEY UPDATE
    `permissao_visualizar` = 1,
    `permissao_inserir` = 0,
    `permissao_editar` = 0,
    `permissao_excluir` = 0,
    `ativo` = 1,
    `data_atualizacao` = CURRENT_TIMESTAMP;

-- 6. Verificar estrutura da tabela
DESCRIBE `tbl_perfil_paginas`;

-- 7. Mostrar permissões criadas
SELECT 
    p.nome_perfil,
    pg.nome_pagina,
    pp.permissao_visualizar,
    pp.permissao_inserir,
    pp.permissao_editar,
    pp.permissao_excluir,
    pp.ativo
FROM `tbl_perfil_paginas` pp
JOIN `tbl_perfis` p ON pp.id_perfil = p.id_perfil
JOIN `tbl_paginas` pg ON pp.id_pagina = pg.id_pagina
WHERE pp.ativo = 1
ORDER BY p.nome_perfil, pg.ordem;

-- 8. Estatísticas de permissões
SELECT 
    p.nome_perfil,
    COUNT(*) as total_paginas,
    SUM(pp.permissao_visualizar) as pode_visualizar,
    SUM(pp.permissao_inserir) as pode_inserir,
    SUM(pp.permissao_editar) as pode_editar,
    SUM(pp.permissao_excluir) as pode_excluir
FROM `tbl_perfil_paginas` pp
JOIN `tbl_perfis` p ON pp.id_perfil = p.id_perfil
WHERE pp.ativo = 1
GROUP BY p.id_perfil, p.nome_perfil
ORDER BY p.nome_perfil; 