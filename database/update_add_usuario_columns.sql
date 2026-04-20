-- Script para adicionar colunas de usuário na tabela tbl_pedidos_compra
USE gruposorrisos;

-- Verificar se a coluna id_usuario_solicitante existe
SET @sql = '';
SELECT COUNT(*) INTO @column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'id_usuario_solicitante';

-- Adicionar coluna id_usuario_solicitante se não existir
IF @column_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_pedidos_compra ADD COLUMN id_usuario_solicitante INT(11) DEFAULT NULL AFTER observacoes';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Coluna id_usuario_solicitante adicionada com sucesso!' as resultado;
ELSE
    SELECT 'Coluna id_usuario_solicitante já existe!' as resultado;
END IF;

-- Verificar se a coluna id_usuario_aprovador existe
SET @sql = '';
SELECT COUNT(*) INTO @column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'id_usuario_aprovador';

-- Adicionar coluna id_usuario_aprovador se não existir
IF @column_exists = 0 THEN
    SET @sql = 'ALTER TABLE tbl_pedidos_compra ADD COLUMN id_usuario_aprovador INT(11) DEFAULT NULL AFTER id_usuario_solicitante';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Coluna id_usuario_aprovador adicionada com sucesso!' as resultado;
ELSE
    SELECT 'Coluna id_usuario_aprovador já existe!' as resultado;
END IF;

-- Remover coluna id_usuario_criacao se existir (para evitar confusão)
SELECT COUNT(*) INTO @column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'id_usuario_criacao';

IF @column_exists > 0 THEN
    SET @sql = 'ALTER TABLE tbl_pedidos_compra DROP COLUMN id_usuario_criacao';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Coluna id_usuario_criacao removida!' as resultado;
END IF;

-- Mostrar estrutura atualizada da tabela
DESCRIBE tbl_pedidos_compra;