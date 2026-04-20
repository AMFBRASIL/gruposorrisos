-- Script para inserir pedidos de exemplo para o fornecedor ID 14
-- Data: 2025-01-22
-- Objetivo: Criar dados de teste para a página pedidos-fornecedores.php

-- Verificar se o fornecedor ID 14 existe
SELECT 'Verificando fornecedor ID 14...' as status;
SELECT id_fornecedor, razao_social, nome_fantasia FROM tbl_fornecedores WHERE id_fornecedor = 14;

-- Verificar se existem filiais
SELECT 'Verificando filiais disponíveis...' as status;
SELECT id_filial, nome_filial FROM tbl_filiais WHERE ativo = 1 LIMIT 5;

-- Verificar se existem usuários
SELECT 'Verificando usuários disponíveis...' as status;
SELECT id_usuario, nome_completo, perfil FROM tbl_usuarios WHERE ativo = 1 LIMIT 5;

-- Verificar se existem materiais
SELECT 'Verificando materiais disponíveis...' as status;
SELECT id_material, nome, codigo FROM tbl_materiais WHERE ativo = 1 LIMIT 10;

-- Inserir pedidos de exemplo para o fornecedor ID 14
-- (Execute apenas se não houver conflitos)

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

-- Inserir itens para o Pedido 1 (Materiais de Escritório)
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_material, 
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
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_material, 
    quantidade, 
    preco_unitario, 
    valor_total, 
    observacoes, 
    data_criacao
) VALUES 
(@pedido2_id, 6, 25.000, 45.00, 1125.00, 'Mouse óptico USB, 3 botões', NOW()),
(@pedido2_id, 7, 25.000, 295.00, 7375.00, 'Teclado USB ABNT2, com teclas numéricas', NOW());

-- Inserir itens para o Pedido 3 (Materiais de Limpeza)
INSERT INTO tbl_itens_pedido_compra (
    id_pedido, 
    id_material, 
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
SELECT 'Verificando pedidos inseridos...' as status;
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
SELECT 'Verificando itens dos pedidos...' as status;
SELECT 
    pi.id_pedido,
    p.numero_pedido,
    m.nome as material,
    pi.quantidade,
    pi.preco_unitario,
    pi.valor_total,
    pi.observacoes
FROM tbl_itens_pedido_compra pi
JOIN tbl_pedidos_compra p ON pi.id_pedido = p.id_pedido
JOIN tbl_materiais m ON pi.id_material = m.id_material
WHERE p.id_fornecedor = 14
ORDER BY pi.id_pedido, pi.id_item;

-- Mensagem de conclusão
SELECT 'Script executado com sucesso!' as status;
SELECT 'Pedidos de exemplo criados para o fornecedor ID 14' as mensagem;
SELECT 'Agora a página pedidos-fornecedores.php deve exibir os pedidos' as instrucao; 