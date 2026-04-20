-- Tabela de movimentações de estoque
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste') NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `estoque_anterior` decimal(10,2) NOT NULL DEFAULT 0,
  `estoque_atual` decimal(10,2) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `id_filial_origem` int(11) DEFAULT NULL,
  `id_filial_destino` int(11) DEFAULT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimentacao`),
  UNIQUE KEY `numero_movimentacao` (`numero_movimentacao`),
  KEY `fk_movimentacao_material` (`id_material`),
  KEY `fk_movimentacao_filial_origem` (`id_filial_origem`),
  KEY `fk_movimentacao_filial_destino` (`id_filial_destino`),
  KEY `fk_movimentacao_fornecedor` (`id_fornecedor`),
  KEY `fk_movimentacao_usuario` (`id_usuario`),
  CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de clientes (se não existir)
CREATE TABLE IF NOT EXISTS `tbl_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_movimentacoes` (`numero_movimentacao`, `tipo_movimentacao`, `id_material`, `quantidade`, `estoque_anterior`, `estoque_atual`, `valor_unitario`, `valor_total`, `id_filial_origem`, `id_filial_destino`, `id_fornecedor`, `documento`, `observacoes`, `id_usuario`) VALUES
('MOV-001', 'entrada', 1, 50.00, 0.00, 50.00, 1299.99, 64999.50, NULL, 1, 1, 'NF-123456', 'Entrada de smartphones Galaxy A54', 1),
('MOV-002', 'saida', 2, 5.00, 25.00, 20.00, 299.99, 1499.95, 1, NULL, NULL, 'PED-789012', 'Saída para cliente XYZ', 1),
('MOV-003', 'entrada', 3, 15.00, 0.00, 15.00, 2899.99, 43499.85, NULL, 1, 2, 'NF-654321', 'Entrada de notebooks Lenovo', 1),
('MOV-004', 'saida', 4, 12.00, 30.00, 18.00, 149.99, 1799.88, 1, NULL, NULL, 'VEN-345678', 'Venda para loja física', 1),
('MOV-005', 'transferencia', 5, 8.00, 15.00, 7.00, 199.99, 1599.92, 1, 2, NULL, 'TRANS-001', 'Transferência entre filiais', 1),
('MOV-006', 'ajuste', 6, -3.00, 20.00, 17.00, 89.99, 269.97, 1, NULL, NULL, 'AJU-001', 'Ajuste de inventário', 1);

INSERT INTO `tbl_clientes` (`nome_cliente`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`) VALUES
('Cliente XYZ', 'contato@xyz.com', '(11) 99999-9999', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567'),
('Loja Física', 'loja@empresa.com', '(11) 88888-8888', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100'); 