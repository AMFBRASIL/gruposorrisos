-- Criar tabela para histórico de mudanças de status dos pedidos
CREATE TABLE IF NOT EXISTS tbl_historico_status_pedidos (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    status ENUM('em_analise','pendente','aprovado_cotacao','enviar_faturamento','faturado','em_transito','recebido','cancelado') NOT NULL,
    observacao TEXT,
    data_alteracao DATETIME NOT NULL,
    id_usuario INT,
    INDEX idx_pedido (id_pedido),
    INDEX idx_data (data_alteracao),
    FOREIGN KEY (id_pedido) REFERENCES tbl_pedidos_compra(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES tbl_usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campo id_pedido_compra na tabela de movimentações se não existir
ALTER TABLE tbl_movimentacoes_estoque 
ADD COLUMN IF NOT EXISTS id_pedido_compra INT,
ADD INDEX IF NOT EXISTS idx_pedido_compra (id_pedido_compra),
ADD FOREIGN KEY IF NOT EXISTS fk_mov_pedido (id_pedido_compra) REFERENCES tbl_pedidos_compra(id_pedido) ON DELETE SET NULL;