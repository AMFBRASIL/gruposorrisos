-- Script para adicionar o campo data_pedido à tabela tbl_pedidos_compra
-- Execute este script no banco de dados para corrigir o erro

USE gruposorrisos;

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'data_pedido';

-- Adicionar a coluna apenas se ela não existir
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE tbl_pedidos_compra ADD COLUMN data_pedido DATE NOT NULL AFTER numero_pedido',
    'SELECT "Coluna data_pedido já existe" as status');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar a estrutura atualizada
DESCRIBE tbl_pedidos_compra;