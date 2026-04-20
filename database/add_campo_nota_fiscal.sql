-- Script para adicionar o campo url_nota_fiscal na tabela tbl_pedidos_compra
-- Data: 2025-01-XX
-- Descrição: Campo para armazenar a URL da Nota Fiscal no S3 ou localmente

-- Verificar se a coluna já existe antes de adicionar
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'url_nota_fiscal';

-- Adicionar coluna se não existir
SET @sql = '';
IF @column_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_pedidos_compra 
                ADD COLUMN url_nota_fiscal VARCHAR(500) NULL 
                COMMENT ''URL da Nota Fiscal (S3 ou local)''
                AFTER observacoes';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Coluna url_nota_fiscal adicionada com sucesso!' as resultado;
ELSE
    SELECT 'Coluna url_nota_fiscal já existe na tabela!' as resultado;
END IF;

-- Verificar estrutura final da tabela
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'url_nota_fiscal';
