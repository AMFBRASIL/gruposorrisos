-- Tabela para armazenar mensagens do chat dos pedidos
CREATE TABLE IF NOT EXISTS tbl_chat_pedidos (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_usuario_remetente INT NOT NULL,
    tipo_usuario ENUM('empresa', 'fornecedor') NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Chaves estrangeiras
    FOREIGN KEY (id_pedido) REFERENCES tbl_pedidos_compra(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_remetente) REFERENCES tbl_usuarios(id_usuario) ON DELETE CASCADE,
    
    -- Índices para performance
    INDEX idx_pedido_data (id_pedido, data_envio),
    INDEX idx_usuario_tipo (id_usuario_remetente, tipo_usuario),
    INDEX idx_lida (lida)
);

-- Inserir dados de exemplo para demonstração
INSERT INTO tbl_chat_pedidos (id_pedido, id_usuario_remetente, tipo_usuario, mensagem) VALUES
(1, 1, 'empresa', 'Olá, gostaria de confirmar o prazo de entrega para este pedido.'),
(1, 2, 'fornecedor', 'Bom dia! O prazo estimado é de 15 dias úteis a partir da confirmação.'),
(1, 1, 'empresa', 'Perfeito! Podemos confirmar então. Aguardamos a entrega.');