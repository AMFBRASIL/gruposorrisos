-- Script para recuperar pedidos que perderam o status
-- Execute este script após atualizar o ENUM

-- Verificar pedidos sem status
SELECT 
    id_pedido, 
    numero_pedido, 
    status, 
    data_criacao,
    data_atualizacao 
FROM tbl_pedidos_compra 
WHERE status IS NULL OR status = '' 
ORDER BY data_atualizacao DESC;

-- Verificar histórico de status para recuperar o último status conhecido
SELECT 
    p.id_pedido,
    p.numero_pedido,
    p.status as status_atual,
    h.status as ultimo_status_historico,
    h.data_alteracao
FROM tbl_pedidos_compra p
LEFT JOIN (
    SELECT 
        id_pedido,
        status,
        data_alteracao,
        ROW_NUMBER() OVER (PARTITION BY id_pedido ORDER BY data_alteracao DESC) as rn
    FROM tbl_historico_status_pedidos
) h ON p.id_pedido = h.id_pedido AND h.rn = 1
WHERE p.status IS NULL OR p.status = ''
ORDER BY h.data_alteracao DESC;

-- Atualizar pedidos sem status baseado no histórico
-- (Execute apenas se o histórico tiver informações confiáveis)
UPDATE tbl_pedidos_compra p
INNER JOIN (
    SELECT 
        id_pedido,
        status,
        data_alteracao,
        ROW_NUMBER() OVER (PARTITION BY id_pedido ORDER BY data_alteracao DESC) as rn
    FROM tbl_historico_status_pedidos
) h ON p.id_pedido = h.id_pedido AND h.rn = 1
SET p.status = h.status,
    p.data_atualizacao = NOW()
WHERE (p.status IS NULL OR p.status = '')
  AND h.status IS NOT NULL
  AND h.status != '';

-- Se não houver histórico, definir status padrão baseado na data
UPDATE tbl_pedidos_compra
SET status = 'em_analise',
    data_atualizacao = NOW()
WHERE (status IS NULL OR status = '')
  AND id_pedido NOT IN (
    SELECT DISTINCT id_pedido 
    FROM tbl_historico_status_pedidos 
    WHERE status IS NOT NULL AND status != ''
  );

-- Verificar resultado final
SELECT 
    status,
    COUNT(*) as total
FROM tbl_pedidos_compra
GROUP BY status
ORDER BY total DESC;

