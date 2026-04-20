-- Script para verificar o campo status da tabela tbl_pedidos_compra
-- Data: 2025-01-22
-- Objetivo: Descobrir por que o status não está sendo atualizado

-- 1. Verificar estrutura do campo status
DESCRIBE tbl_pedidos_compra;

-- 2. Verificar se o campo status existe e suas propriedades
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_TYPE,
    EXTRA
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_pedidos_compra' 
AND COLUMN_NAME = 'status';

-- 3. Verificar valores atuais do campo status
SELECT 
    status,
    COUNT(*) as total,
    GROUP_CONCAT(DISTINCT status) as valores_unicos
FROM tbl_pedidos_compra 
GROUP BY status;

-- 4. Verificar se existem constraints no campo status
SELECT 
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE,
    CHECK_CLAUSE
FROM information_schema.CHECK_CONSTRAINTS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tbl_pedidos_compra';

-- 5. Verificar se o campo status é ENUM e quais valores aceita
SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status';

-- 6. Verificar pedidos com status 'respondido'
SELECT 
    id_pedido,
    numero_pedido,
    status,
    data_criacao,
    data_atualizacao
FROM tbl_pedidos_compra 
WHERE status = 'respondido'
ORDER BY data_atualizacao DESC
LIMIT 5;

-- 7. Verificar pedidos com status vazio ou NULL
SELECT 
    id_pedido,
    numero_pedido,
    status,
    data_criacao,
    data_atualizacao
FROM tbl_pedidos_compra 
WHERE status IS NULL OR status = '' OR status = ' '
ORDER BY data_atualizacao DESC
LIMIT 5;

-- 8. Tentar atualizar um pedido para status 'respondido' (teste)
-- SELECT id_pedido, numero_pedido, status FROM tbl_pedidos_compra WHERE status = 'pendente' LIMIT 1;
-- UPDATE tbl_pedidos_compra SET status = 'respondido', data_atualizacao = NOW() WHERE id_pedido = [ID_DO_PEDIDO]; 