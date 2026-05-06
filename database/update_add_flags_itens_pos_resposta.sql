-- Novos campos para controlar itens inseridos após resposta do fornecedor
-- Tabela: tbl_itens_pedido_compra
-- Objetivo: destacar itens "novos" para o fornecedor responder

USE gruposorrisos;

-- 1) Flag de item novo após resposta
SET @sql_novo_pos_resposta = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_itens_pedido_compra'
              AND column_name = 'novo_pos_resposta'
        ),
        'SELECT "Coluna novo_pos_resposta já existe" AS status',
        'ALTER TABLE tbl_itens_pedido_compra ADD COLUMN novo_pos_resposta TINYINT(1) NOT NULL DEFAULT 0 AFTER observacoes'
    )
);
PREPARE stmt_novo_pos_resposta FROM @sql_novo_pos_resposta;
EXECUTE stmt_novo_pos_resposta;
DEALLOCATE PREPARE stmt_novo_pos_resposta;

-- 2) Data de inclusão do item novo (após resposta)
SET @sql_data_inclusao = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_itens_pedido_compra'
              AND column_name = 'data_inclusao_pos_resposta'
        ),
        'SELECT "Coluna data_inclusao_pos_resposta já existe" AS status',
        'ALTER TABLE tbl_itens_pedido_compra ADD COLUMN data_inclusao_pos_resposta DATETIME NULL AFTER novo_pos_resposta'
    )
);
PREPARE stmt_data_inclusao FROM @sql_data_inclusao;
EXECUTE stmt_data_inclusao;
DEALLOCATE PREPARE stmt_data_inclusao;

-- 3) Usuário que incluiu o item pós-resposta (opcional, auditoria)
SET @sql_usuario_inclusao = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_itens_pedido_compra'
              AND column_name = 'id_usuario_inclusao_pos_resposta'
        ),
        'SELECT "Coluna id_usuario_inclusao_pos_resposta já existe" AS status',
        'ALTER TABLE tbl_itens_pedido_compra ADD COLUMN id_usuario_inclusao_pos_resposta INT(11) NULL AFTER data_inclusao_pos_resposta'
    )
);
PREPARE stmt_usuario_inclusao FROM @sql_usuario_inclusao;
EXECUTE stmt_usuario_inclusao;
DEALLOCATE PREPARE stmt_usuario_inclusao;

-- 4) Data da resposta do fornecedor para o item novo (quando a pendência for sanada)
SET @sql_data_resposta = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_itens_pedido_compra'
              AND column_name = 'data_resposta_novo_item'
        ),
        'SELECT "Coluna data_resposta_novo_item já existe" AS status',
        'ALTER TABLE tbl_itens_pedido_compra ADD COLUMN data_resposta_novo_item DATETIME NULL AFTER id_usuario_inclusao_pos_resposta'
    )
);
PREPARE stmt_data_resposta FROM @sql_data_resposta;
EXECUTE stmt_data_resposta;
DEALLOCATE PREPARE stmt_data_resposta;

-- 5) Índice para performance da busca de pendências
SET @sql_idx = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'tbl_itens_pedido_compra'
              AND index_name = 'idx_itens_novo_pos_resposta'
        ),
        'SELECT "Índice idx_itens_novo_pos_resposta já existe" AS status',
        'ALTER TABLE tbl_itens_pedido_compra ADD INDEX idx_itens_novo_pos_resposta (id_pedido, novo_pos_resposta)'
    )
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- Verificação final
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'tbl_itens_pedido_compra'
  AND column_name IN (
      'novo_pos_resposta',
      'data_inclusao_pos_resposta',
      'id_usuario_inclusao_pos_resposta',
      'data_resposta_novo_item'
  )
ORDER BY ordinal_position;
