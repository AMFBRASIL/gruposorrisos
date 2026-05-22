-- Migração: novo fluxo de pedidos de compra (Grupo Sorrisos)
-- Execute no banco de produção/homologação após backup

-- 1) Ampliar ENUM com novos status + manter legado temporariamente
ALTER TABLE `tbl_pedidos_compra`
MODIFY COLUMN `status` ENUM(
    'aguardando_cotacao',
    'em_cotacao',
    'aguardando_aprovacao_socio',
    'aprovado_socio',
    'aguardando_faturamento',
    'em_faturamento',
    'em_conferencia',
    'finalizado',
    'cancelado',
    'em_analise',
    'pendente',
    'aprovado_cotacao',
    'enviar_para_faturamento',
    'enviar_faturamento',
    'aprovado_para_faturar',
    'faturado',
    'em_transito',
    'entregue',
    'recebido',
    'aguardando_aprovacao',
    'aprovado',
    'em_producao',
    'enviado',
    'parcialmente_recebido'
) NOT NULL DEFAULT 'aguardando_cotacao';

-- 2) Migrar pedidos existentes para o novo fluxo
UPDATE tbl_pedidos_compra SET status = 'aguardando_cotacao'
WHERE status IN ('em_analise', 'pendente', 'aguardando_aprovacao', 'rascunho');

UPDATE tbl_pedidos_compra SET status = 'em_cotacao'
WHERE status IN ('aprovado_cotacao');

UPDATE tbl_pedidos_compra SET status = 'aguardando_faturamento'
WHERE status IN ('enviar_para_faturamento', 'enviar_faturamento');

UPDATE tbl_pedidos_compra SET status = 'em_faturamento'
WHERE status IN ('aprovado_para_faturar', 'faturado', 'em_transito', 'enviado', 'em_producao', 'aprovado');

UPDATE tbl_pedidos_compra SET status = 'em_conferencia'
WHERE status IN ('entregue', 'parcialmente_recebido');

UPDATE tbl_pedidos_compra SET status = 'finalizado'
WHERE status = 'recebido';

-- 3) Histórico (se a coluna for ENUM — ajuste conforme seu schema)
-- SHOW COLUMNS FROM tbl_historico_status_pedidos LIKE 'status';
