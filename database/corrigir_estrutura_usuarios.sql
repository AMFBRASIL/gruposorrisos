-- Script para corrigir a estrutura da tabela tbl_usuarios
-- Data: 2025-01-22
-- Objetivo: Adicionar campo id_fornecedor para vincular usuários a fornecedores

-- 1. Verificar estrutura atual da tabela tbl_usuarios
DESCRIBE tbl_usuarios;

-- 2. Adicionar campo id_fornecedor
ALTER TABLE tbl_usuarios 
ADD COLUMN id_fornecedor INT NULL COMMENT 'FK para tbl_fornecedores - NULL se não for fornecedor';

-- 3. Adicionar constraint de chave estrangeira
ALTER TABLE tbl_usuarios 
ADD CONSTRAINT fk_usuarios_fornecedor 
FOREIGN KEY (id_fornecedor) REFERENCES tbl_fornecedores(id_fornecedor);

-- 4. Verificar se a alteração foi aplicada
DESCRIBE tbl_usuarios;

-- 5. Verificar constraint criada
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_usuarios' 
AND REFERENCED_TABLE_NAME = 'tbl_fornecedores';

-- 6. Verificar estrutura final
SELECT 'Estrutura corrigida com sucesso!' as status; 