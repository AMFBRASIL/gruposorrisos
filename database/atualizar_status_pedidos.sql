-- Script para atualizar os status dos pedidos de compra
-- Data: 2025-01-22
-- Objetivo: Adequar os status ao novo modelo solicitado

-- 1. Verificar estrutura atual do campo status
SELECT 'Verificando estrutura atual do campo status...' as status;
SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status';

-- 2. Verificar dados atuais
SELECT 'Verificando status atuais na tabela...' as status;
SELECT status, COUNT(*) as total FROM tbl_pedidos_compra GROUP BY status;

-- 3. Fazer backup dos dados atuais
SELECT 'Criando backup dos dados atuais...' as status;
CREATE TABLE IF NOT EXISTS tbl_pedidos_compra_backup_status AS 
SELECT id_pedido, numero_pedido, status, data_atualizacao 
FROM tbl_pedidos_compra;

-- 4. Mapear status antigos para novos
SELECT 'Mapeamento de status antigos para novos:' as info;
SELECT 
    'em_analise -> em_analise (mantém)' as mapeamento
UNION ALL SELECT 'pendente -> pendente (mantém)'
UNION ALL SELECT 'aprovado -> aprovado_cotacao'
UNION ALL SELECT 'em_producao -> enviar_faturamento'
UNION ALL SELECT 'enviado -> em_transito'
UNION ALL SELECT 'recebido -> recebido (mantém)'
UNION ALL SELECT 'cancelado -> cancelado (mantém)';

-- 5. Alterar o campo status para incluir os novos valores
SELECT 'Alterando estrutura do campo status...' as status;
ALTER TABLE tbl_pedidos_compra 
MODIFY COLUMN status ENUM(
    'em_analise',
    'pendente', 
    'aprovado_cotacao',
    'enviar_faturamento',
    'faturado',
    'em_transito',
    'recebido',
    'cancelado'
) DEFAULT 'em_analise';

-- 6. Atualizar dados existentes para o novo modelo
SELECT 'Atualizando dados existentes...' as status;

-- Mapear 'aprovado' para 'aprovado_cotacao'
UPDATE tbl_pedidos_compra 
SET status = 'aprovado_cotacao', data_atualizacao = NOW()
WHERE status = 'aprovado';

-- Mapear 'em_producao' para 'enviar_faturamento'
UPDATE tbl_pedidos_compra 
SET status = 'enviar_faturamento', data_atualizacao = NOW()
WHERE status = 'em_producao';

-- Mapear 'enviado' para 'em_transito'
UPDATE tbl_pedidos_compra 
SET status = 'em_transito', data_atualizacao = NOW()
WHERE status = 'enviado';

-- 7. Verificar resultado final
SELECT 'Verificando resultado final...' as status;
SELECT status, COUNT(*) as total FROM tbl_pedidos_compra GROUP BY status;

-- 8. Mostrar estrutura atualizada
SELECT 'Estrutura atualizada do campo status:' as info;
SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status';

SELECT 'Atualização concluída com sucesso!' as resultado;

-- Comentários sobre os novos status:
/*
NOVOS STATUS IMPLEMENTADOS:
1. em_analise - Quando o gerente analisa
2. pendente - Quando o gerente já analisou mas o setor de compras também vai aprovar
3. aprovado_cotacao - Quando o setor de compras aprovou e foi para o fornecedor dar preços
4. enviar_faturamento - Quando o fornecedor já colocou os preços e está tudo em ordem para faturar
5. faturado - Quando o pedido está pronto para enviar
6. em_transito - Quando o fornecedor já enviou e está em trânsito
7. recebido - Quando o material foi recebido com sucesso e validado pela empresa
8. cancelado - Quando houve algum problema e foi cancelado

MAPEAMENTO DOS STATUS ANTIGOS:
- em_analise -> em_analise (mantém)
- pendente -> pendente (mantém)
- aprovado -> aprovado_cotacao
- em_producao -> enviar_faturamento
- enviado -> em_transito
- recebido -> recebido (mantém)
- cancelado -> cancelado (mantém)
*/