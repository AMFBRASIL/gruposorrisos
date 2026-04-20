-- Script SQL SIMPLES para adicionar o campo quantidade_disponivel
-- Execute este script no seu banco de dados MySQL/MariaDB

-- Adicionar coluna quantidade_disponivel na tabela tbl_itens_pedido_compra
ALTER TABLE `tbl_itens_pedido_compra` 
ADD COLUMN `quantidade_disponivel` DECIMAL(10,3) NULL 
COMMENT 'Quantidade disponível informada pelo fornecedor'
AFTER `quantidade`;

-- Verificar se a coluna foi criada
DESCRIBE `tbl_itens_pedido_compra`;

