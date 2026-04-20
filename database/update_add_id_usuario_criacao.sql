-- Script para adicionar a coluna id_usuario_criacao na tabela tbl_pedidos_compra
-- Execute este script no phpMyAdmin ou MySQL

USE gruposorrisos;

-- Verificar se a coluna id_usuario_criacao existe
SET @sql = '';
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
  AND TABLE_NAME = 'tbl_pedidos_compra' 
  AND COLUMN_NAME = 'id_usuario_criacao';

-- Se a coluna não existir, adicionar ela
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE tbl_pedidos_compra ADD COLUMN id_usuario_criacao int(11) DEFAULT NULL AFTER observacoes;',
    'SELECT "Coluna id_usuario_criacao já existe" as status;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar a estrutura atualizada da tabela
DESCRIBE tbl_pedidos_compra;

-- Confirmar se a coluna foi adicionada
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
  AND TABLE_NAME = 'tbl_pedidos_compra'
  AND COLUMN_NAME = 'id_usuario_criacao';