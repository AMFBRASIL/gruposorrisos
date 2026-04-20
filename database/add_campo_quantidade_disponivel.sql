-- Script para adicionar o campo quantidade_disponivel na tabela tbl_itens_pedido_compra
-- Data: 2025-01-XX
-- Descrição: Campo para armazenar a quantidade disponível informada pelo fornecedor

-- Verificar se a coluna já existe antes de adicionar
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_itens_pedido_compra' 
AND COLUMN_NAME = 'quantidade_disponivel';

-- Adicionar coluna se não existir
SET @sql = '';
IF @column_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_itens_pedido_compra 
                ADD COLUMN quantidade_disponivel DECIMAL(10,3) NULL 
                COMMENT ''Quantidade disponível informada pelo fornecedor''
                AFTER quantidade';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT ''Coluna quantidade_disponivel adicionada com sucesso!'' as resultado;
ELSE
    SELECT ''Coluna quantidade_disponivel já existe na tabela!'' as resultado;
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
AND TABLE_NAME = 'tbl_itens_pedido_compra' 
AND COLUMN_NAME = 'quantidade_disponivel';

