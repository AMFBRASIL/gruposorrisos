-- Script CORRIGIDO para inserir pedidos de exemplo para o fornecedor ID 14
-- Data: 2025-01-22
-- Objetivo: Criar dados de teste para a página pedidos-fornecedores.php
-- CORREÇÕES APLICADAS baseadas nos erros encontrados

-- 1. Primeiro, vamos verificar a estrutura real das tabelas
SELECT '=== VERIFICANDO ESTRUTURA DAS TABELAS ===' as status;

-- Verificar se o fornecedor ID 14 existe
SELECT '1. Verificando fornecedor ID 14...' as status;
SELECT id_fornecedor, razao_social, nome_fantasia FROM tbl_fornecedores WHERE id_fornecedor = 14;

-- Se não existir, vamos criar um fornecedor de exemplo
SELECT '2. Criando fornecedor ID 14 se não existir...' as status;
INSERT IGNORE INTO tbl_fornecedores (
    id_fornecedor, 
    razao_social, 
    nome_fantasia, 
    cnpj, 
    email, 
    telefone, 
    endereco, 
    cidade, 
    estado, 
    cep, 
    responsavel, 
    ativo, 
    data_criacao, 
    data_atualizacao
) VALUES (
    14, 
    'Fornecedor Exemplo Ltda', 
    'Fornecedor Exemplo', 
    '12.345.678/0001-90', 
    'contato@fornecedorexemplo.com.br', 
    '(11) 99999-9999', 
    'Rua das Flores, 123', 
    'São Paulo', 
    'SP', 
    '01234-567', 
    'João Fornecedor', 
    1, 
    NOW(), 
    NOW()
);

-- Verificar se existem filiais (sem usar coluna 'ativo')
SELECT '3. Verificando filiais disponíveis...' as status;
SELECT id_filial, nome_filial FROM tbl_filiais LIMIT 5;

-- Se não houver filiais, criar algumas
SELECT '4. Criando filiais se não existirem...' as status;
INSERT IGNORE INTO tbl_filiais (id_filial, nome_filial, endereco, cidade, estado, cep, telefone, email, ativo, data_criacao, data_atualizacao) VALUES
(1, 'Filial Centro', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', '(11) 88888-8888', 'centro@empresa.com', 1, NOW(), NOW()),
(2, 'Filial Norte', 'Rua Augusta, 500', 'São Paulo', 'SP', '01212-000', '(11) 77777-7777', 'norte@empresa.com', 1, NOW(), NOW()),
(3, 'Filial Sul', 'Av. Brigadeiro Faria Lima, 2000', 'São Paulo', 'SP', '01452-002', '(11) 66666-6666', 'sul@empresa.com', 1, NOW(), NOW());

-- Verificar se existem usuários (sem usar coluna 'perfil')
SELECT '5. Verificando usuários disponíveis...' as status;
SELECT id_usuario, nome_completo, email FROM tbl_usuarios LIMIT 5;

-- Se não houver usuários, criar alguns
SELECT '6. Criando usuários se não existirem...' as status;
INSERT IGNORE INTO tbl_usuarios (id_usuario, nome_completo, email, senha, perfil, ativo, data_criacao, data_atualizacao) VALUES
(1, 'João Silva', 'joao@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 1, NOW(), NOW()),
(2, 'Maria Santos', 'maria@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gerente', 1, NOW(), NOW()),
(3, 'Pedro Oliveira', 'pedro@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuário', 1, NOW(), NOW());

-- Verificar se existem materiais (tabela correta)
SELECT '7. Verificando materiais disponíveis...' as status;
SHOW TABLES LIKE '%material%';

-- Verificar se existe tbl_catalogo_materiais
SELECT '8. Verificando tbl_catalogo_materiais...' as status;
SELECT id_catalogo, nome, codigo FROM tbl_catalogo_materiais LIMIT 10;

-- Se não houver materiais, criar alguns
SELECT '9. Criando materiais se não existirem...' as status;
INSERT IGNORE INTO tbl_catalogo_materiais (id_catalogo, nome, codigo, descricao, id_categoria, id_unidade, preco_unitario, estoque_minimo, ativo, data_criacao, data_atualizacao) VALUES
(1, 'Papel A4 500 folhas', 'PAP001', 'Papel A4 branco, gramatura 75g, 500 folhas', 1, 1, 15.00, 10, 1, NOW(), NOW()),
(2, 'Caneta Esferográfica Azul', 'CAN001', 'Caneta esferográfica azul, ponta média', 1, 1, 2.50, 50, 1, NOW(), NOW()),
(3, 'Bloco de Notas A5', 'BLO001', 'Bloco de notas A5, 100 folhas, espiral', 1, 1, 8.00, 20, 1, NOW(), NOW()),
(4, 'Lápis HB', 'LAP001', 'Lápis HB, caixa com 12 unidades', 1, 1, 1.20, 30, 1, NOW(), NOW()),
(5, 'Borracha Branca', 'BOR001', 'Borracha branca, tamanho médio', 1, 1, 0.80, 40, 1, NOW(), NOW()),
(6, 'Mouse Óptico USB', 'MOU001', 'Mouse óptico USB, 3 botões', 2, 1, 45.00, 5, 1, NOW(), NOW()),
(7, 'Teclado USB', 'TEC001', 'Teclado USB ABNT2, com teclas numéricas', 2, 1, 295.00, 5, 1, NOW(), NOW()),
(8, 'Detergente Líquido', 'DET001', 'Detergente líquido neutro, 500ml', 3, 1, 8.50, 25, 1, NOW(), NOW()),
(9, 'Desinfetante', 'DES001', 'Desinfetante concentrado, 1L', 3, 1, 12.00, 15, 1, NOW(), NOW()),
(10, 'Papel Higiênico', 'PAP002', 'Papel higiênico, 30m, 4 rolos', 3, 1, 14.00, 20, 1, NOW(), NOW());

-- Verificar se existem categorias
SELECT '10. Verificando categorias...' as status;
SELECT id_categoria, nome_categoria FROM tbl_categorias LIMIT 5;

-- Se não houver categorias, criar algumas
SELECT '11. Criando categorias se não existirem...' as status;
INSERT IGNORE INTO tbl_categorias (id_categoria, nome_categoria, descricao, ativo, data_criacao, data_atualizacao) VALUES
(1, 'Papelaria', 'Materiais de escritório e papelaria', 1, NOW(), NOW()),
(2, 'Informática', 'Equipamentos e acessórios de informática', 1, NOW(), NOW()),
(3, 'Limpeza', 'Produtos de limpeza e higiene', 1, NOW(), NOW());

-- Verificar se existem unidades de medida
SELECT '12. Verificando unidades de medida...' as status;
SELECT id_unidade, sigla, nome FROM tbl_unidades_medida LIMIT 5;

-- Se não houver unidades, criar algumas
SELECT '13. Criando unidades de medida se não existirem...' as status;
INSERT IGNORE INTO tbl_unidades_medida (id_unidade, sigla, nome, descricao, ativo, data_criacao, data_atualizacao) VALUES
(1, 'un', 'Unidade', 'Unidade individual', 1, NOW(), NOW()),
(2, 'kg', 'Quilograma', 'Peso em quilogramas', 1, NOW(), NOW()),
(3, 'm', 'Metro', 'Comprimento em metros', 1, NOW(), NOW()),
(4, 'L', 'Litro', 'Volume em litros', 1, NOW(), NOW());

-- Agora vamos inserir os pedidos
SELECT '=== INSERINDO PEDIDOS ===' as status;

-- Verificar se já existem pedidos para o fornecedor 14
SELECT '14. Verificando pedidos existentes...' as status;
SELECT COUNT(*) as total FROM tbl_pedidos_compra WHERE id_fornecedor = 14;

-- Inserir pedidos de exemplo para o fornecedor ID 14
SELECT '15. Inserindo pedidos...' as status;

-- Pedido 1: Materiais de Escritório
INSERT INTO tbl_pedidos_compra (
    numero_pedido, 
    id_filial, 
    id_fornecedor, 
    id_usuario_solicitante, 
    data_solicitacao,
    data_entrega_prevista, 
    status, 
    valor_total, 
    observacoes, 
    ativo, 
    data_criacao, 
    data_atualizacao
) VALUES (
    'PED-2025-001', 
    1, 
    14, 
    1, 
    NOW(), 
    DATE_ADD(NOW(), INTERVAL 10 DAY), 
    'pendente', 
    15000.00, 
    'Pedido de materiais de escritório para reposição mensal', 
    1, 
    NOW(), 
    NOW()
);

-- Pedido 2: Equipamentos de Informática
INSERT INTO tbl_pedidos_compra (
    numero_pedido, 
    id_filial, 
    id_fornecedor, 
    id_usuario_solicitante, 
    data_solicitacao,
    data_entrega_prevista, 
    status, 
    valor_total, 
    observacoes, 
    ativo, 
    data_criacao, 
    data_atualizacao
) VALUES (
    'PED-2025-002', 
    2, 
    14, 
    2, 
    NOW(), 
    DATE_ADD(NOW(), INTERVAL 15 DAY), 
    'pendente', 
    8500.00, 
    'Equipamentos de informática para nova filial', 
    1, 
    NOW(), 
    NOW()
);

-- Pedido 3: Materiais de Limpeza
INSERT INTO tbl_pedidos_compra (
    numero_pedido, 
    id_filial, 
    id_fornecedor, 
    id_usuario_solicitante, 
    data_solicitacao,
    data_entrega_prevista, 
    status, 
    valor_total, 
    observacoes, 
    ativo, 
    data_criacao, 
    data_atualizacao
) VALUES (
    'PED-2025-003', 
    1, 
    14, 
    3, 
    NOW(), 
    DATE_ADD(NOW(), INTERVAL 7 DAY), 
    'pendente', 
    2500.00, 
    'Materiais de limpeza para manutenção', 
    1, 
    NOW(), 
    NOW()
);

-- Obter IDs dos pedidos inseridos
SET @pedido1_id = LAST_INSERT_ID() - 2;
SET @pedido2_id = LAST_INSERT_ID() - 1;
SET @pedido3_id = LAST_INSERT_ID();

SELECT '16. IDs dos pedidos criados:' as status;
SELECT @pedido1_id as pedido1_id, @pedido2_id as pedido2_id, @pedido3_id as pedido3_id;

-- Inserir itens para o Pedido 1 (Materiais de Escritório)
SELECT '17. Inserindo itens do Pedido 1...' as status;
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_catalogo, 
    quantidade, 
    preco_unitario, 
    valor_total, 
    observacoes, 
    data_criacao
) VALUES 
(@pedido1_id, 1, 100.000, 15.00, 1500.00, 'Papel A4 500 folhas, gramatura 75g', NOW()),
(@pedido1_id, 2, 200.000, 2.50, 500.00, 'Canetas esferográficas azuis, ponta média', NOW()),
(@pedido1_id, 3, 50.000, 8.00, 400.00, 'Blocos de notas A5, 100 folhas', NOW()),
(@pedido1_id, 4, 150.000, 1.20, 180.00, 'Lápis HB, caixa com 12 unidades', NOW()),
(@pedido1_id, 5, 80.000, 0.80, 64.00, 'Borracha branca, tamanho médio', NOW());

-- Inserir itens para o Pedido 2 (Equipamentos de Informática)
SELECT '18. Inserindo itens do Pedido 2...' as status;
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_catalogo, 
    quantidade, 
    preco_unitario, 
    valor_total, 
    observacoes, 
    data_criacao
) VALUES 
(@pedido2_id, 6, 25.000, 45.00, 1125.00, 'Mouse óptico USB, 3 botões', NOW()),
(@pedido2_id, 7, 25.000, 295.00, 7375.00, 'Teclado USB ABNT2, com teclas numéricas', NOW());

-- Inserir itens para o Pedido 3 (Materiais de Limpeza)
SELECT '19. Inserindo itens do Pedido 3...' as status;
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_catalogo, 
    quantidade, 
    preco_unitario, 
    valor_total, 
    observacoes, 
    data_criacao
) VALUES 
(@pedido3_id, 8, 100.000, 8.50, 850.00, 'Detergente líquido neutro, 500ml', NOW()),
(@pedido3_id, 9, 50.000, 12.00, 600.00, 'Desinfetante concentrado, 1L', NOW()),
(@pedido3_id, 10, 75.000, 14.00, 1050.00, 'Papel higiênico, 30m, 4 rolos', NOW());

-- Verificar os pedidos inseridos
SELECT '=== VERIFICAÇÃO FINAL ===' as status;
SELECT '20. Verificando pedidos inseridos...' as status;
SELECT 
    p.id_pedido,
    p.numero_pedido,
    p.status,
    p.valor_total,
    p.observacoes,
    f.nome_filial,
    u.nome_completo as solicitante,
    COUNT(pi.id_item) as total_itens
FROM tbl_pedidos_compra p
LEFT JOIN tbl_filiais f ON p.id_filial = f.id_filial
LEFT JOIN tbl_usuarios u ON p.id_usuario_solicitante = u.id_usuario
LEFT JOIN tbl_itens_pedido_compra pi ON p.id_pedido = pi.id_pedido
WHERE p.id_fornecedor = 14
GROUP BY p.id_pedido
ORDER BY p.data_criacao DESC;

-- Verificar itens dos pedidos
SELECT '21. Verificando itens dos pedidos...' as status;
SELECT 
    pi.id_pedido,
    p.numero_pedido,
    cm.nome as material,
    pi.quantidade,
    pi.preco_unitario,
    pi.valor_total,
    pi.observacoes
FROM tbl_itens_pedido_compra pi
JOIN tbl_pedidos_compra p ON pi.id_pedido = p.id_pedido
JOIN tbl_catalogo_materiais cm ON pi.id_catalogo = cm.id_catalogo
WHERE p.id_fornecedor = 14
ORDER BY pi.id_pedido, pi.id_item;

-- Mensagem de conclusão
SELECT '=== CONCLUSÃO ===' as status;
SELECT '🎉 Script executado com sucesso!' as status;
SELECT 'Pedidos de exemplo criados para o fornecedor ID 14' as mensagem;
SELECT 'Agora a página pedidos-fornecedores.php deve exibir os pedidos' as instrucao; 