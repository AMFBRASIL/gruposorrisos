-- Separa observações do comprador e do fornecedor no pedido
-- Campo comprador: tbl_pedidos_compra.observacoes
-- Campo fornecedor: tbl_pedidos_compra.observacoes_fornecedor

USE gruposorrisos;

-- 1) Adicionar campo de observações do fornecedor (se ainda não existir)
SET @sql_obs_fornecedor = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_pedidos_compra'
              AND column_name = 'observacoes_fornecedor'
        ),
        'SELECT "Coluna observacoes_fornecedor já existe" AS status',
        'ALTER TABLE tbl_pedidos_compra ADD COLUMN observacoes_fornecedor TEXT NULL AFTER observacoes'
    )
);
PREPARE stmt_obs_fornecedor FROM @sql_obs_fornecedor;
EXECUTE stmt_obs_fornecedor;
DEALLOCATE PREPARE stmt_obs_fornecedor;

-- 2) Adicionar condições de pagamento (se ainda não existir)
SET @sql_cond_pag = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_pedidos_compra'
              AND column_name = 'condicoes_pagamento'
        ),
        'SELECT "Coluna condicoes_pagamento já existe" AS status',
        'ALTER TABLE tbl_pedidos_compra ADD COLUMN condicoes_pagamento VARCHAR(255) NULL AFTER data_entrega_prevista'
    )
);
PREPARE stmt_cond_pag FROM @sql_cond_pag;
EXECUTE stmt_cond_pag;
DEALLOCATE PREPARE stmt_cond_pag;

-- 3) Verificação final
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'tbl_pedidos_compra'
  AND column_name IN ('observacoes', 'observacoes_fornecedor', 'condicoes_pagamento')
ORDER BY column_name;
