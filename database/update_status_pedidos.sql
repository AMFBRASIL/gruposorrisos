-- Script para adicionar o status 'em_analise' na tabela de pedidos de compra
-- Execute este script no seu banco de dados

ALTER TABLE `tbl_pedidos_compra` 
MODIFY COLUMN `status` enum('pendente','em_analise','aprovado','em_producao','enviado','recebido','cancelado') DEFAULT 'em_analise';

-- Verificar se a alteração foi aplicada
SHOW COLUMNS FROM `tbl_pedidos_compra` LIKE 'status';