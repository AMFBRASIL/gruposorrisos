-- Desconto padrão na cotação do fornecedor (configurável em Configurações > Pedidos)
INSERT IGNORE INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria)
VALUES (
    'pedidos_desconto_fornecedor_percentual',
    '5',
    'Desconto padrão (%) aplicado na cotação do fornecedor sobre o preço bruto de cada item',
    'numero',
    'pedidos'
);

-- Percentual efetivo gravado no pedido quando o fornecedor responde (histórico)
ALTER TABLE tbl_pedidos_compra
    ADD COLUMN desconto_cotacao_percentual DECIMAL(5,2) NULL DEFAULT NULL
    COMMENT 'Percentual de desconto aplicado na cotação do fornecedor';
