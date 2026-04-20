-- ============================================================================
-- Script para adicionar campo de Fabricante
-- Data: 2025-11-03
-- ============================================================================

-- 1. Adicionar campo is_fabricante na tabela tbl_fornecedores
-- Este campo indica se o fornecedor também é um fabricante
ALTER TABLE `tbl_fornecedores` 
ADD COLUMN `is_fabricante` TINYINT(1) DEFAULT 0 COMMENT 'Indica se o fornecedor também é fabricante' AFTER `ativo`;

-- 2. Adicionar campo id_fabricante na tabela tbl_catalogo_materiais
-- Este campo armazena o fabricante do material (pode ser diferente do fornecedor)
ALTER TABLE `tbl_catalogo_materiais` 
ADD COLUMN `id_fabricante` INT(11) NULL COMMENT 'ID do fabricante do material' AFTER `id_fornecedor`,
ADD KEY `idx_catalogo_materiais_fabricante` (`id_fabricante`),
ADD CONSTRAINT `fk_catalogo_materiais_fabricante` FOREIGN KEY (`id_fabricante`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================================
-- Verificar as alterações
-- ============================================================================
-- Para verificar se os campos foram adicionados corretamente:
-- DESCRIBE tbl_fornecedores;
-- DESCRIBE tbl_catalogo_materiais;

-- ============================================================================
-- NOTAS:
-- ============================================================================
-- 1. O campo is_fabricante em tbl_fornecedores indica se aquele fornecedor 
--    também atua como fabricante
-- 2. O campo id_fabricante em tbl_catalogo_materiais permite associar um material a 
--    um fabricante específico
-- 3. Um material pode ter um fornecedor (quem vende) e um fabricante (quem produz)
-- 4. O fabricante deve ser um fornecedor marcado com is_fabricante = 1
-- ============================================================================

