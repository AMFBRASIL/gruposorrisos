-- Tabela de Filiais
CREATE TABLE IF NOT EXISTS `tbl_filiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `tipo` enum('matriz','filial') NOT NULL DEFAULT 'filial',
  `cnpj` varchar(18) DEFAULT NULL,
  `inscricao_estadual` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `responsavel` varchar(255) DEFAULT NULL,
  `email_responsavel` varchar(255) DEFAULT NULL,
  `telefone_responsavel` varchar(20) DEFAULT NULL,
  `data_abertura` date DEFAULT NULL,
  `status` enum('ativa','inativa') NOT NULL DEFAULT 'ativa',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_status` (`status`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_cidade` (`cidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_filiais` (`codigo`, `nome`, `tipo`, `cnpj`, `endereco`, `cidade`, `estado`, `telefone`, `email`, `responsavel`, `email_responsavel`, `status`, `observacoes`) VALUES
('MAT001', 'Matriz - São Paulo', 'matriz', '12.345.678/0001-90', 'Av. Paulista, 1000', 'São Paulo', 'SP', '(11) 3000-0000', 'contato@gruposorrisos.com.br', 'Maria Silva', 'maria@gruposorrisos.com.br', 'ativa', 'Sede principal da empresa'),
('FIL001', 'Filial - Rio de Janeiro', 'filial', '12.345.678/0002-71', 'Rua do Ouvidor, 150', 'Rio de Janeiro', 'RJ', '(21) 2500-0000', 'rj@gruposorrisos.com.br', 'João Santos', 'joao@gruposorrisos.com.br', 'ativa', 'Filial da capital carioca'),
('FIL002', 'Filial - Belo Horizonte', 'filial', '12.345.678/0003-52', 'Av. Afonso Pena, 500', 'Belo Horizonte', 'MG', '(31) 3200-0000', 'bh@gruposorrisos.com.br', 'Ana Costa', 'ana@gruposorrisos.com.br', 'ativa', 'Filial de Minas Gerais'),
('FIL003', 'Filial - Brasília', 'filial', '12.345.678/0004-33', 'SQS 115, Bloco A', 'Brasília', 'DF', '(61) 3300-0000', 'bsb@gruposorrisos.com.br', 'Carlos Oliveira', 'carlos@gruposorrisos.com.br', 'inativa', 'Filial temporariamente inativa'); 