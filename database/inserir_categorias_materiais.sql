-- ============================================================================
-- Script de Inserção de Categorias de Materiais
-- Tabela: tbl_categorias
-- Data: 2025-11-03
-- ============================================================================

-- Inserir as categorias principais do sistema
INSERT INTO tbl_categorias (nome_categoria, descricao, categoria_pai, ativo, data_criacao, data_atualizacao) VALUES
('ANESTESICOS E AGULHA GENGIVAL', 'Materiais anestésicos e agulhas para aplicação gengival', NULL, 1, NOW(), NOW()),
('ARTICULADOR', 'Articuladores e acessórios para laboratório', NULL, 1, NOW(), NOW()),
('BIOSSEGURANÇA', 'Equipamentos e materiais de biossegurança', NULL, 1, NOW(), NOW()),
('BROCAS', 'Brocas odontológicas de diversos tipos', NULL, 1, NOW(), NOW()),
('CIMENTOS', 'Cimentos odontológicos para diversos procedimentos', NULL, 1, NOW(), NOW()),
('CIRURGIA E PERIODONTIA', 'Materiais para procedimentos cirúrgicos e periodontais', NULL, 1, NOW(), NOW()),
('CLINICO GERAL', 'Materiais para clínica geral odontológica', NULL, 1, NOW(), NOW()),
('DENTÍSTICA E ESTÉTICA', 'Materiais para procedimentos de dentística e estética dental', NULL, 1, NOW(), NOW()),
('DESCARTÁVEIS', 'Materiais descartáveis de uso odontológico', NULL, 1, NOW(), NOW()),
('ENDODONTIA', 'Materiais e instrumentais para tratamento endodôntico', NULL, 1, NOW(), NOW()),
('EQUIPAMENTOS LABORATORIAIS', 'Equipamentos para laboratório de prótese', NULL, 1, NOW(), NOW()),
('HIGIENE ORAL', 'Produtos para higiene oral', NULL, 1, NOW(), NOW()),
('IMPLANTODONTIA', 'Materiais e componentes para implantodontia', NULL, 1, NOW(), NOW()),
('INSTRUMENTAIS', 'Instrumentais odontológicos diversos', NULL, 1, NOW(), NOW()),
('MANDRIL', 'Mandris e acessórios', NULL, 1, NOW(), NOW()),
('MATERIAS DE LIMPEZA', 'Materiais de limpeza e higienização', NULL, 1, NOW(), NOW()),
('MOLDAGEM E MODELO', 'Materiais para moldagem e confecção de modelos', NULL, 1, NOW(), NOW()),
('ORTODONTIA', 'Materiais e acessórios para ortodontia', NULL, 1, NOW(), NOW()),
('PLASTIFICADORA', 'Plastificadoras e acessórios', NULL, 1, NOW(), NOW()),
('PREVENÇAO E PROFILAXIA', 'Materiais para prevenção e profilaxia dental', NULL, 1, NOW(), NOW()),
('PROTESE', 'Materiais para prótese dentária', NULL, 1, NOW(), NOW()),
('PROTESE CLINICA', 'Materiais para prótese clínica', NULL, 1, NOW(), NOW()),
('PROTESE LABORATORIAL', 'Materiais para prótese laboratorial', NULL, 1, NOW(), NOW()),
('RADIOLOGIA', 'Materiais e equipamentos para radiologia odontológica', NULL, 1, NOW(), NOW());

-- ============================================================================
-- Verificar inserção
-- ============================================================================
-- Para verificar se as categorias foram inseridas corretamente, execute:
-- SELECT id_categoria, nome_categoria, ativo, data_criacao FROM tbl_categorias ORDER BY nome_categoria;

-- ============================================================================
-- NOTAS:
-- ============================================================================
-- 1. Todas as categorias são inseridas como categorias principais (categoria_pai = NULL)
-- 2. Todas as categorias estão ativas por padrão (ativo = 1)
-- 3. As datas de criação e atualização são definidas automaticamente
-- 4. Caso precise adicionar subcategorias no futuro, use o id_categoria 
--    da categoria pai no campo categoria_pai
-- ============================================================================

