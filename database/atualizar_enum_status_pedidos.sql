-- Script para atualizar o ENUM do campo status na tabela tbl_pedidos_compra
-- Adiciona os status faltantes incluindo 'enviar_para_faturamento' e 'aprovado_para_faturar'
-- Execute este script no seu banco de dados

-- Verificar estrutura atual
SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status';

-- Atualizar o ENUM para incluir todos os status necessários
ALTER TABLE `tbl_pedidos_compra` 
MODIFY COLUMN `status` ENUM(
    'em_analise',
    'pendente', 
    'aprovado_cotacao',
    'enviar_para_faturamento',
    'enviar_faturamento',  -- Mantido para compatibilidade
    'aprovado_para_faturar',
    'faturado',
    'em_transito',
    'entregue',
    'recebido',
    'cancelado'
) DEFAULT 'em_analise';

-- Verificar estrutura atualizada
SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status';

-- Verificar se há pedidos com status NULL ou vazio
SELECT 
    id_pedido, 
    numero_pedido, 
    status, 
    data_atualizacao 
FROM tbl_pedidos_compra 
WHERE status IS NULL OR status = '' 
ORDER BY data_atualizacao DESC;

