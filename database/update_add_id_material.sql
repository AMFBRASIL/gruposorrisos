-- Script para adicionar coluna id_material na tabela tbl_itens_pedido_compra
USE gruposorrisos;

-- Verificar se a coluna id_material existe
SET @sql = '';
SELECT COUNT(*) INTO @column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_itens_pedido_compra' 
AND COLUMN_NAME = 'id_material';

-- Adicionar coluna id_material se não existir
IF @column_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_itens_pedido_compra ADD COLUMN id_material INT(11) NOT NULL AFTER id_pedido';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Coluna id_material adicionada com sucesso!' as resultado;
ELSE
    SELECT 'Coluna id_material já existe!' as resultado;
END IF;

-- Verificar se existe índice para id_material
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_itens_pedido_compra' 
AND INDEX_NAME = 'id_material';

-- Adicionar índice se não existir
IF @index_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_itens_pedido_compra ADD INDEX id_material (id_material)';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Índice id_material adicionado com sucesso!' as resultado;
ELSE
    SELECT 'Índice id_material já existe!' as resultado;
END IF;

-- Verificar se existe foreign key para id_material
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_itens_pedido_compra' 
AND COLUMN_NAME = 'id_material'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Adicionar foreign key se não existir (opcional, pode dar erro se tabela tbl_materiais não existir)
-- IF @fk_exists = 0 THEN
--     SET @sql = 'ALTER TABLE tbl_itens_pedido_compra ADD CONSTRAINT fk_itens_pedido_material FOREIGN KEY (id_material) REFERENCES tbl_materiais(id_material)';
--     PREPARE stmt FROM @sql;
--     EXECUTE stmt;
--     DEALLOCATE PREPARE stmt;
--     SELECT 'Foreign key id_material adicionada com sucesso!' as resultado;
-- ELSE
--     SELECT 'Foreign key id_material já existe!' as resultado;
-- END IF;

-- Mostrar estrutura atualizada da tabela
DESCRIBE tbl_itens_pedido_compra;