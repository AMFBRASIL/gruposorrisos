-- =====================================================
-- DADOS DE EXEMPLO - SISTEMA DE ESTOQUE GRUPO SORRISOS
-- =====================================================

-- Inserir filiais
INSERT INTO `tbl_filiais` (`codigo_filial`, `nome_filial`, `razao_social`, `cnpj`, `endereco`, `cidade`, `estado`, `telefone`, `email`, `responsavel`, `tipo_filial`, `filial_ativa`) VALUES
('MAT001', 'Matriz - São Paulo', 'Grupo Sorrisos Ltda', '12.345.678/0001-90', 'Av. Paulista, 1000', 'São Paulo', 'SP', '(11) 3000-0000', 'contato@gruposorrisos.com.br', 'Maria Silva', 'matriz', 1),
('FIL001', 'Filial - Rio de Janeiro', 'Grupo Sorrisos Ltda', '12.345.678/0002-71', 'Rua do Ouvidor, 150', 'Rio de Janeiro', 'RJ', '(21) 2500-0000', 'rj@gruposorrisos.com.br', 'João Santos', 'filial', 1),
('FIL002', 'Filial - Belo Horizonte', 'Grupo Sorrisos Ltda', '12.345.678/0003-52', 'Av. Afonso Pena, 500', 'Belo Horizonte', 'MG', '(31) 3200-0000', 'bh@gruposorrisos.com.br', 'Ana Costa', 'filial', 1),
('FIL003', 'Filial - Brasília', 'Grupo Sorrisos Ltda', '12.345.678/0004-33', 'SQS 115, Bloco A', 'Brasília', 'DF', '(61) 3300-0000', 'bsb@gruposorrisos.com.br', 'Carlos Oliveira', 'filial', 0);

-- Inserir perfis
INSERT INTO `tbl_perfis` (`nome_perfil`, `descricao`) VALUES
('Administrador', 'Acesso total ao sistema'),
('Gerente', 'Gerencia filiais e equipes'),
('Operador', 'Operações básicas de estoque'),
('Visualizador', 'Apenas visualização de relatórios');

-- Inserir usuários
INSERT INTO `tbl_usuarios` (`nome_completo`, `email`, `senha`, `cpf`, `telefone`, `id_perfil`, `id_filial`, `ativo`) VALUES
('Administrador Sistema', 'admin@gruposorrisos.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123.456.789-00', '(11) 99999-9999', 1, 1, 1),
('Maria Silva', 'maria@gruposorrisos.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '111.222.333-44', '(11) 88888-8888', 2, 1, 1),
('João Santos', 'joao@gruposorrisos.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '222.333.444-55', '(21) 77777-7777', 2, 2, 1),
('Ana Costa', 'ana@gruposorrisos.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '333.444.555-66', '(31) 66666-6666', 3, 3, 1);

-- Inserir categorias
INSERT INTO `tbl_categorias` (`nome_categoria`, `descricao`) VALUES
('Eletrônicos', 'Produtos eletrônicos e tecnológicos'),
('Informática', 'Equipamentos e acessórios de informática'),
('Escritório', 'Materiais de escritório'),
('Limpeza', 'Produtos de limpeza'),
('Manutenção', 'Materiais de manutenção'),
('Alimentação', 'Produtos alimentícios');

-- Inserir unidades de medida
INSERT INTO `tbl_unidades_medida` (`sigla`, `nome`, `descricao`) VALUES
('UN', 'Unidade', 'Unidade individual'),
('KG', 'Quilograma', 'Peso em quilogramas'),
('L', 'Litro', 'Volume em litros'),
('M', 'Metro', 'Comprimento em metros'),
('M²', 'Metro Quadrado', 'Área em metros quadrados'),
('CX', 'Caixa', 'Caixa com múltiplas unidades'),
('PCT', 'Pacote', 'Pacote com múltiplas unidades');

-- Inserir fornecedores
INSERT INTO `tbl_fornecedores` (`razao_social`, `nome_fantasia`, `cnpj`, `endereco`, `cidade`, `estado`, `telefone`, `email`, `contato_principal`) VALUES
('Fornecedor ABC Ltda', 'ABC Fornecedor', '11.111.111/0001-11', 'Rua das Flores, 123', 'São Paulo', 'SP', '(11) 1111-1111', 'contato@abc.com.br', 'João Silva'),
('Distribuidora XYZ S/A', 'XYZ Distribuidora', '22.222.222/0001-22', 'Av. Principal, 456', 'Rio de Janeiro', 'RJ', '(21) 2222-2222', 'vendas@xyz.com.br', 'Maria Santos'),
('Comercial 123 Ltda', '123 Comercial', '33.333.333/0001-33', 'Rua Comercial, 789', 'Belo Horizonte', 'MG', '(31) 3333-3333', 'pedidos@123.com.br', 'Pedro Costa');

-- Inserir clientes
INSERT INTO `tbl_clientes` (`nome_cliente`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`) VALUES
('Cliente A', 'clientea@email.com', '(11) 4444-4444', 'Rua Cliente A, 100', 'São Paulo', 'SP', '01234-567'),
('Cliente B', 'clienteb@email.com', '(21) 5555-5555', 'Av. Cliente B, 200', 'Rio de Janeiro', 'RJ', '20000-000'),
('Cliente C', 'clientec@email.com', '(31) 6666-6666', 'Rua Cliente C, 300', 'Belo Horizonte', 'MG', '30000-000');

-- Inserir materiais
INSERT INTO `tbl_materiais` (`codigo`, `nome`, `descricao`, `id_categoria`, `id_fornecedor`, `id_unidade`, `id_filial`, `preco_unitario`, `estoque_minimo`, `estoque_maximo`, `estoque_atual`, `localizacao_estoque`) VALUES
('MAT001', 'Smartphone Galaxy A54', 'Smartphone Samsung Galaxy A54 128GB', 1, 1, 1, 1, 1299.99, 10.000, 100.000, 50.000, 'Prateleira A1'),
('MAT002', 'Notebook Dell Inspiron', 'Notebook Dell Inspiron 15 polegadas', 2, 2, 1, 1, 2899.99, 5.000, 50.000, 15.000, 'Prateleira B2'),
('MAT003', 'Papel A4 500 folhas', 'Papel A4 500 folhas 75g', 3, 3, 6, 1, 15.99, 20.000, 200.000, 100.000, 'Prateleira C3'),
('MAT004', 'Detergente Líquido', 'Detergente líquido 500ml', 4, 3, 3, 1, 8.99, 30.000, 300.000, 150.000, 'Prateleira D4'),
('MAT005', 'Furadeira Elétrica', 'Furadeira elétrica 500W', 5, 1, 1, 1, 199.99, 5.000, 50.000, 20.000, 'Prateleira E5'),
('MAT006', 'Café em Pó', 'Café em pó 500g', 6, 3, 2, 1, 12.99, 25.000, 250.000, 80.000, 'Prateleira F6');

-- Inserir tipos de movimentação
INSERT INTO `tbl_tipos_movimentacao` (`nome`, `descricao`, `tipo`) VALUES
('Compra', 'Entrada por compra de fornecedor', 'entrada'),
('Venda', 'Saída por venda', 'saida'),
('Transferência', 'Transferência entre filiais', 'saida'),
('Ajuste', 'Ajuste de inventário', 'saida'),
('Devolução', 'Devolução de cliente', 'entrada'),
('Perda', 'Perda ou dano', 'saida');

-- Inserir estoque por filial
INSERT INTO `tbl_estoque_filial` (`id_material`, `id_filial`, `estoque_atual`, `estoque_minimo`, `estoque_maximo`, `localizacao`, `custo_medio`) VALUES
(1, 1, 50.000, 10.000, 100.000, 'Prateleira A1', 1200.00),
(2, 1, 15.000, 5.000, 50.000, 'Prateleira B2', 2800.00),
(3, 1, 100.000, 20.000, 200.000, 'Prateleira C3', 15.00),
(4, 1, 150.000, 30.000, 300.000, 'Prateleira D4', 8.50),
(5, 1, 20.000, 5.000, 50.000, 'Prateleira E5', 190.00),
(6, 1, 80.000, 25.000, 250.000, 'Prateleira F6', 12.00);

-- Inserir lotes
INSERT INTO `tbl_lotes` (`numero_lote`, `id_material`, `id_filial`, `quantidade_inicial`, `quantidade_atual`, `data_fabricacao`, `data_validade`, `custo_unitario`, `fornecedor_lote`) VALUES
('LOT001-2024', 1, 1, 50.000, 50.000, '2024-01-01', '2026-01-01', 1200.00, 'Fornecedor ABC Ltda'),
('LOT002-2024', 2, 1, 15.000, 15.000, '2024-01-15', '2027-01-15', 2800.00, 'Distribuidora XYZ S/A'),
('LOT003-2024', 3, 1, 100.000, 100.000, '2024-02-01', '2025-02-01', 15.00, 'Comercial 123 Ltda'),
('LOT004-2024', 4, 1, 150.000, 150.000, '2024-02-15', '2025-02-15', 8.50, 'Comercial 123 Ltda'),
('LOT005-2024', 5, 1, 20.000, 20.000, '2024-03-01', '2026-03-01', 190.00, 'Fornecedor ABC Ltda'),
('LOT006-2024', 6, 1, 80.000, 80.000, '2024-03-15', '2025-03-15', 12.00, 'Comercial 123 Ltda');

-- Inserir movimentações de exemplo
INSERT INTO `tbl_movimentacoes` (`numero_movimentacao`, `tipo_movimentacao`, `subtipo_movimentacao`, `id_material`, `id_lote`, `id_filial_destino`, `quantidade`, `estoque_anterior_destino`, `estoque_atual_destino`, `valor_unitario`, `valor_total`, `id_fornecedor`, `documento`, `numero_documento`, `observacoes`, `id_usuario_executor`) VALUES
('MOV-2024-001', 'entrada', 'Compra', 1, 1, 1, 50.000, 0.000, 50.000, 1200.00, 60000.00, 1, 'Nota Fiscal', 'NF-001/2024', 'Entrada inicial de smartphones', 1),
('MOV-2024-002', 'entrada', 'Compra', 2, 2, 1, 15.000, 0.000, 15.000, 2800.00, 42000.00, 2, 'Nota Fiscal', 'NF-002/2024', 'Entrada inicial de notebooks', 1),
('MOV-2024-003', 'entrada', 'Compra', 3, 3, 1, 100.000, 0.000, 100.000, 15.00, 1500.00, 3, 'Nota Fiscal', 'NF-003/2024', 'Entrada inicial de papel A4', 1),
('MOV-2024-004', 'saida', 'Venda', 1, 1, 1, 5.000, 50.000, 45.000, 1299.99, 6499.95, NULL, 'Venda', 'VEN-001/2024', 'Venda para cliente', 2),
('MOV-2024-005', 'transferencia', 'Transferência', 2, 2, 2, 3.000, 15.000, 12.000, 2899.99, 8699.97, NULL, 'Transferência', 'TRANS-001/2024', 'Transferência para filial RJ', 1);

-- Inserir pedidos de compra
INSERT INTO `tbl_pedidos_compra` (`numero_pedido`, `id_filial`, `id_fornecedor`, `id_usuario_solicitante`, `data_entrega_prevista`, `status`, `valor_total`, `observacoes`) VALUES
('PED-2024-001', 1, 1, 2, '2024-01-25', 'aprovado', 12500.00, 'Pedido de materiais de escritório'),
('PED-2024-002', 1, 2, 2, '2024-01-30', 'em_entrega', 8900.00, 'Equipamentos de informática'),
('PED-2024-003', 2, 1, 3, '2024-02-05', 'pendente', 15600.00, 'Materiais de limpeza'),
('PED-2024-004', 1, 3, 2, '2024-01-28', 'entregue', 7200.00, 'Ferramentas e equipamentos');

-- Inserir itens dos pedidos
INSERT INTO `tbl_itens_pedido_compra` (`id_pedido`, `id_material`, `quantidade`, `preco_unitario`, `valor_total`, `observacoes`) VALUES
(1, 3, 50.000, 15.00, 750.00, 'Papel A4'),
(1, 4, 100.000, 8.50, 850.00, 'Detergente'),
(2, 2, 3.000, 2899.99, 8699.97, 'Notebooks'),
(3, 4, 200.000, 8.50, 1700.00, 'Produtos de limpeza'),
(4, 5, 20.000, 199.99, 3999.80, 'Furadeiras');

-- Inserir tickets de exemplo
INSERT INTO `tbl_tickets` (`numero_ticket`, `titulo`, `descricao`, `id_categoria`, `id_prioridade`, `id_status`, `id_usuario_solicitante`, `id_usuario_atribuido`, `id_filial`) VALUES
('TKT-2024-001', 'Problema no sistema de estoque', 'Não consigo registrar entrada de materiais', 2, 2, 1, 2, 1, 1),
('TKT-2024-002', 'Estoque baixo de papel A4', 'Preciso de reposição urgente', 3, 3, 2, 3, 2, 2),
('TKT-2024-003', 'Erro na impressão de relatórios', 'Relatórios não estão saindo corretamente', 2, 1, 3, 4, 1, 3);

-- Inserir comentários dos tickets
INSERT INTO `tbl_comentarios_ticket` (`id_ticket`, `id_usuario`, `comentario`, `tipo`) VALUES
(1, 2, 'Sistema travando ao tentar registrar entrada', 'comentario'),
(1, 1, 'Vou verificar o problema', 'comentario'),
(2, 3, 'Estoque chegou ao mínimo', 'comentario'),
(2, 2, 'Pedido de reposição já foi feito', 'comentario'),
(3, 4, 'Relatórios com formatação incorreta', 'comentario'),
(3, 1, 'Problema identificado, corrigindo', 'comentario');

-- Inserir alertas de estoque
INSERT INTO `tbl_alertas_estoque` (`id_material`, `id_filial`, `tipo_alerta`, `quantidade_atual`, `quantidade_referencia`, `prioridade`, `mensagem`) VALUES
(3, 2, 'estoque_baixo', 15.000, 20.000, 'media', 'Estoque de papel A4 abaixo do mínimo'),
(4, 3, 'estoque_zerado', 0.000, 30.000, 'alta', 'Estoque de detergente zerado'),
(5, 1, 'estoque_alto', 45.000, 50.000, 'baixa', 'Estoque de furadeiras acima do máximo');

COMMIT; 