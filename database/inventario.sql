-- Tabela de inventário
CREATE TABLE IF NOT EXISTS `tbl_inventario` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `numero_inventario` varchar(20) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_usuario_responsavel` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fim` datetime DEFAULT NULL,
  `status` enum('em_andamento','finalizado','cancelado') NOT NULL DEFAULT 'em_andamento',
  `observacoes` text DEFAULT NULL,
  `total_itens` int(11) DEFAULT 0,
  `itens_contados` int(11) DEFAULT 0,
  `itens_divergentes` int(11) DEFAULT 0,
  `valor_total_sistema` decimal(15,4) DEFAULT 0.0000,
  `valor_total_contado` decimal(15,4) DEFAULT 0.0000,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_inventario`),
  UNIQUE KEY `numero_inventario` (`numero_inventario`),
  KEY `fk_inventario_filial` (`id_filial`),
  KEY `fk_inventario_usuario` (`id_usuario_responsavel`),
  KEY `idx_status` (`status`),
  KEY `idx_data_inicio` (`data_inicio`),
  CONSTRAINT `fk_inventario_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_inventario_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens do inventário
CREATE TABLE IF NOT EXISTS `tbl_itens_inventario` (
  `id_item_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `id_inventario` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade_sistema` decimal(15,3) NOT NULL DEFAULT 0.000,
  `quantidade_contada` decimal(15,3) DEFAULT NULL,
  `quantidade_divergencia` decimal(15,3) DEFAULT 0.000,
  `valor_unitario` decimal(15,4) DEFAULT 0.0000,
  `valor_total_sistema` decimal(15,4) DEFAULT 0.0000,
  `valor_total_contado` decimal(15,4) DEFAULT 0.0000,
  `observacoes` text DEFAULT NULL,
  `status_item` enum('pendente','contado','divergente','ajustado') NOT NULL DEFAULT 'pendente',
  `data_contagem` datetime DEFAULT NULL,
  `id_usuario_contador` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item_inventario`),
  UNIQUE KEY `uk_inventario_material` (`id_inventario`, `id_material`),
  KEY `fk_item_inventario_inventario` (`id_inventario`),
  KEY `fk_item_inventario_material` (`id_material`),
  KEY `fk_item_inventario_usuario` (`id_usuario_contador`),
  KEY `idx_status_item` (`status_item`),
  CONSTRAINT `fk_item_inventario_inventario` FOREIGN KEY (`id_inventario`) REFERENCES `tbl_inventario` (`id_inventario`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_inventario_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`),
  CONSTRAINT `fk_item_inventario_usuario` FOREIGN KEY (`id_usuario_contador`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_inventario` (`numero_inventario`, `id_filial`, `id_usuario_responsavel`, `status`, `observacoes`, `total_itens`, `itens_contados`, `itens_divergentes`) VALUES
('INV-2024-001', 1, 1, 'finalizado', 'Inventário mensal - Janeiro 2024', 6, 6, 2),
('INV-2024-002', 1, 2, 'em_andamento', 'Inventário de verificação', 6, 3, 0),
('INV-2024-003', 2, 3, 'em_andamento', 'Inventário filial RJ', 4, 2, 1);

-- Inserir itens de exemplo
INSERT INTO `tbl_itens_inventario` (`id_inventario`, `id_material`, `quantidade_sistema`, `quantidade_contada`, `quantidade_divergencia`, `valor_unitario`, `valor_total_sistema`, `valor_total_contado`, `status_item`, `observacoes`) VALUES
(1, 1, 50.000, 48.000, -2.000, 1299.99, 64999.50, 62399.52, 'divergente', 'Diferença de 2 unidades'),
(1, 2, 15.000, 15.000, 0.000, 2899.99, 43499.85, 43499.85, 'contado', 'Quantidade correta'),
(1, 3, 100.000, 102.000, 2.000, 15.99, 1599.00, 1630.98, 'divergente', 'Excesso de 2 unidades'),
(1, 4, 150.000, 150.000, 0.000, 8.99, 1348.50, 1348.50, 'contado', 'Quantidade correta'),
(1, 5, 20.000, 20.000, 0.000, 199.99, 3999.80, 3999.80, 'contado', 'Quantidade correta'),
(1, 6, 80.000, 80.000, 0.000, 12.99, 1039.20, 1039.20, 'contado', 'Quantidade correta'),
(2, 1, 48.000, 48.000, 0.000, 1299.99, 62399.52, 62399.52, 'contado', 'Quantidade correta'),
(2, 2, 15.000, 15.000, 0.000, 2899.99, 43499.85, 43499.85, 'contado', 'Quantidade correta'),
(2, 3, 102.000, NULL, 0.000, 15.99, 1630.98, 0.00, 'pendente', NULL),
(2, 4, 150.000, NULL, 0.000, 8.99, 1348.50, 0.00, 'pendente', NULL),
(2, 5, 20.000, NULL, 0.000, 199.99, 3999.80, 0.00, 'pendente', NULL),
(2, 6, 80.000, NULL, 0.000, 12.99, 1039.20, 0.00, 'pendente', NULL),
(3, 1, 30.000, 30.000, 0.000, 1299.99, 38999.70, 38999.70, 'contado', 'Quantidade correta'),
(3, 2, 10.000, 9.000, -1.000, 2899.99, 28999.90, 26099.91, 'divergente', 'Falta 1 unidade'),
(3, 3, 50.000, NULL, 0.000, 15.99, 799.50, 0.00, 'pendente', NULL),
(3, 4, 100.000, NULL, 0.000, 8.99, 899.00, 0.00, 'pendente', NULL); 