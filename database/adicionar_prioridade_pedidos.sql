-- Script para adicionar campo de prioridade nos pedidos de compra
-- Data: 2025-01-22

-- Adicionar campo de prioridade na tabela tbl_pedidos_compra
ALTER TABLE `tbl_pedidos_compra` 
ADD COLUMN `prioridade` ENUM('padrao', 'critico', 'urgente') DEFAULT 'padrao' AFTER `status`,
ADD COLUMN `prazo_entrega` INT(3) DEFAULT 8 COMMENT 'Prazo em dias para entrega' AFTER `prioridade`;

-- Adicionar índice para otimizar consultas por prioridade
ALTER TABLE `tbl_pedidos_compra` 
ADD INDEX `idx_prioridade` (`prioridade`),
ADD INDEX `idx_prazo_entrega` (`prazo_entrega`);

-- Atualizar registros existentes para ter prioridade padrão
UPDATE `tbl_pedidos_compra` SET `prioridade` = 'padrao', `prazo_entrega` = 8 WHERE `prioridade` IS NULL;

-- Comentários sobre as prioridades
-- padrao: até 8 dias (padrão do sistema)
-- critico: até 3 dias (usuário define)
-- urgente: hoje/imediato (usuário define)

-- Verificar se a alteração foi aplicada
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME IN ('prioridade', 'prazo_entrega'); 