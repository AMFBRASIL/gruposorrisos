-- Tabela de Pedidos de Compra
CREATE TABLE IF NOT EXISTS `tbl_pedidos_compra` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(20) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `data_pedido` date NOT NULL,
  `data_entrega_prevista` date DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovado','em_producao','enviado','recebido','cancelado') DEFAULT 'em_analise',
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text,
  `id_usuario_criacao` int(11) DEFAULT NULL,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `id_fornecedor` (`id_fornecedor`),
  KEY `id_filial` (`id_filial`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Pedido de Compra
CREATE TABLE IF NOT EXISTS `tbl_itens_pedido_compra` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_pedidos_compra` (`numero_pedido`, `id_fornecedor`, `id_filial`, `data_pedido`, `data_entrega_prevista`, `status`, `valor_total`, `observacoes`, `id_usuario_criacao`) VALUES
('PED-2024-001', 1, 1, '2024-01-15', '2024-01-25', 'aprovado', 12500.00, 'Pedido de materiais de escritório', 1),
('PED-2024-002', 2, 1, '2024-01-16', '2024-01-30', 'em_producao', 8900.00, 'Equipamentos de informática', 1),
('PED-2024-003', 1, 2, '2024-01-17', '2024-02-05', 'pendente', 15600.00, 'Materiais de limpeza', 2),
('PED-2024-004', 3, 1, '2024-01-18', '2024-01-28', 'enviado', 7200.00, 'Ferramentas e equipamentos', 1),
('PED-2024-005', 2, 2, '2024-01-19', '2024-02-10', 'pendente', 18900.00, 'Materiais de construção', 2);

INSERT INTO `tbl_itens_pedido_compra` (`id_pedido`, `id_material`, `quantidade`, `preco_unitario`, `valor_total`, `observacoes`) VALUES
(1, 1, 50.000, 150.00, 7500.00, 'Papel A4'),
(1, 2, 100.000, 50.00, 5000.00, 'Canetas'),
(2, 3, 10.000, 890.00, 8900.00, 'Notebooks'),
(3, 4, 200.000, 78.00, 15600.00, 'Produtos de limpeza'),
(4, 5, 20.000, 360.00, 7200.00, 'Ferramentas'),
(5, 6, 30.000, 630.00, 18900.00, 'Materiais de construção');