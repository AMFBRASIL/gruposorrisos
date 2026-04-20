-- ============================================================================
-- Script de Inserção de Materiais de Exemplo
-- Tabela: tbl_catalogo_materiais
-- Data: 2025-11-03
-- ============================================================================

-- IMPORTANTE: Ajuste os IDs conforme seu banco de dados
-- - id_categoria: Consulte suas categorias em tbl_categorias
-- - id_fornecedor: Use os IDs dos fornecedores cadastrados
-- - id_fabricante: Use os IDs dos fabricantes (is_fabricante = 1)
-- - id_unidade: 1=UN, 2=KG, 3=M, 4=M², 5=M³, 6=L, 7=CX, 8=PCT, 9=ROL, 10=PAR

-- ============================================================================
-- MATERIAIS DE EXEMPLO
-- ============================================================================

INSERT INTO tbl_catalogo_materiais (
    codigo,
    nome,
    descricao,
    id_categoria,
    id_fornecedor,
    id_fabricante,
    id_unidade,
    preco_unitario_padrao,
    estoque_minimo_padrao,
    estoque_maximo_padrao,
    codigo_barras,
    ca,
    observacoes,
    ativo,
    data_criacao
) VALUES

-- 1. ANESTÉSICOS
(
    'ANEST001',
    'Anestésico Mepivacaína 2% com Vasoconstrictor 1:100.000',
    'Anestésico local de uso odontológico - Tubete 1,8ml',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'ANESTESICOS E AGULHA GENGIVAL' LIMIT 1),
    13, -- DENTAL CREMER (fornecedor)
    12, -- 3M DO BRASIL (fabricante)
    7,  -- CX (Caixa)
    85.50,
    5,
    50,
    '7896181905486',
    NULL,
    'Caixa com 50 tubetes de 1,8ml',
    1,
    NOW()
),

(
    'ANEST002',
    'Agulha Gengival Descartável 27G Curta',
    'Agulha descartável para anestesia odontológica 27G x 0,40mm x 21mm',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'ANESTESICOS E AGULHA GENGIVAL' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    7,  -- CX
    35.90,
    10,
    100,
    '7898357420156',
    NULL,
    'Caixa com 100 unidades',
    1,
    NOW()
),

-- 2. BIOSSEGURANÇA
(
    'BIO001',
    'Luva de Procedimento Nitrílica Azul - Tamanho M',
    'Luva de procedimento não cirúrgica, ambidestra, sem pó',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'BIOSSEGURANÇA' LIMIT 1),
    14, -- GARDEIS
    12, -- 3M DO BRASIL
    7,  -- CX
    28.50,
    20,
    200,
    '7898357410238',
    'CA 12345',
    'Caixa com 100 unidades - Uso EPI',
    1,
    NOW()
),

(
    'BIO002',
    'Máscara Tripla Descartável com Elástico',
    'Máscara cirúrgica descartável tripla camada com clips nasal',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'BIOSSEGURANÇA' LIMIT 1),
    14, -- GARDEIS
    12, -- 3M DO BRASIL
    7,  -- CX
    15.90,
    30,
    300,
    '7896987450123',
    'CA 54321',
    'Caixa com 50 unidades - Uso EPI',
    1,
    NOW()
),

(
    'BIO003',
    'Óculos de Proteção Individual Transparente',
    'Óculos de segurança transparente com proteção lateral',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'BIOSSEGURANÇA' LIMIT 1),
    14, -- GARDEIS
    12, -- 3M DO BRASIL
    1,  -- UN
    12.50,
    10,
    50,
    '7891234567890',
    'CA 98765',
    'Proteção contra respingos e aerossóis - Uso EPI',
    1,
    NOW()
),

-- 3. DESCARTÁVEIS
(
    'DESC001',
    'Babador Descartável Papel Rosa',
    'Babador descartável de papel impermeável 30x40cm',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'DESCARTÁVEIS' LIMIT 1),
    13, -- DENTAL CREMER
    NULL, -- Sem fabricante específico
    8,  -- PCT
    18.90,
    10,
    100,
    '7898765432109',
    NULL,
    'Pacote com 100 unidades',
    1,
    NOW()
),

(
    'DESC002',
    'Copo Descartável 200ml Branco',
    'Copo descartável de plástico para bochecho',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'DESCARTÁVEIS' LIMIT 1),
    13, -- DENTAL CREMER
    NULL,
    8,  -- PCT
    8.50,
    20,
    200,
    '7891234098765',
    NULL,
    'Pacote com 100 unidades',
    1,
    NOW()
),

-- 4. ENDODONTIA
(
    'ENDO001',
    'Lima Endodôntica K-File 21mm #15',
    'Lima manual tipo K de aço inoxidável 21mm calibre 15',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'ENDODONTIA' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    7,  -- CX
    45.90,
    5,
    30,
    '7896543210987',
    NULL,
    'Caixa com 6 unidades',
    1,
    NOW()
),

-- 5. HIGIENE ORAL
(
    'HIG001',
    'Escova Dental Adulto Macia',
    'Escova dental para adultos com cerdas macias',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'HIGIENE ORAL' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    1,  -- UN
    4.50,
    50,
    500,
    '7890123456789',
    NULL,
    'Cores sortidas',
    1,
    NOW()
),

(
    'HIG002',
    'Fio Dental PTFE 50m',
    'Fio dental de PTFE resistente ao desfiamento',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'HIGIENE ORAL' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    1,  -- UN
    8.90,
    30,
    300,
    '7896321456987',
    NULL,
    'Embalagem com 50 metros',
    1,
    NOW()
),

-- 6. DENTÍSTICA E ESTÉTICA
(
    'DENT001',
    'Resina Composta Fotopolimerizável A2',
    'Resina composta universal nanoparticulada cor A2',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'DENTÍSTICA E ESTÉTICA' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    1,  -- UN
    95.00,
    3,
    20,
    '7891478523690',
    NULL,
    'Seringa 4g',
    1,
    NOW()
),

-- 7. BROCAS
(
    'BROCA001',
    'Broca Carbide FG 1014 Esférica',
    'Broca esférica diamantada alta rotação FG 1014',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'BROCAS' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    1,  -- UN
    5.50,
    20,
    100,
    '7890987654321',
    NULL,
    'Broca para alta rotação',
    1,
    NOW()
),

-- 8. ORTODONTIA
(
    'ORTO001',
    'Bracket Metálico Roth 022 - Kit Completo',
    'Kit completo de brackets metálicos prescrição Roth 022',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'ORTODONTIA' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    7,  -- CX
    350.00,
    2,
    10,
    '7896547890123',
    NULL,
    'Kit com 20 brackets + acessórios',
    1,
    NOW()
),

-- 9. RADIOLOGIA
(
    'RADIO001',
    'Filme Radiográfico Periapical Adulto',
    'Filme radiográfico intraoral periapical tamanho 2 (31x41mm)',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'RADIOLOGIA' LIMIT 1),
    13, -- DENTAL CREMER
    12, -- 3M DO BRASIL
    7,  -- CX
    75.00,
    5,
    30,
    '7893216549870',
    NULL,
    'Caixa com 150 filmes',
    1,
    NOW()
),

-- 10. MATERIAIS DE LIMPEZA
(
    'LIMP001',
    'Álcool 70% Líquido 1 Litro',
    'Álcool etílico hidratado 70% INPM para limpeza e desinfecção',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'MATERIAS DE LIMPEZA' LIMIT 1),
    15, -- EAC LIVRARIA
    NULL,
    1,  -- UN
    12.50,
    10,
    50,
    '7891000001110',
    NULL,
    'Frasco com 1 litro',
    1,
    NOW()
),

(
    'LIMP002',
    'Papel Toalha Interfolhado Branco',
    'Papel toalha interfolhado 100% celulose virgem',
    (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria = 'MATERIAS DE LIMPEZA' LIMIT 1),
    15, -- EAC LIVRARIA
    NULL,
    8,  -- PCT
    15.90,
    20,
    100,
    '7891234567123',
    NULL,
    'Pacote com 1000 folhas',
    1,
    NOW()
);

-- ============================================================================
-- Verificação
-- ============================================================================
-- Para verificar se os materiais foram inseridos:
SELECT 
    id_catalogo,
    codigo,
    nome,
    codigo_barras,
    preco_unitario_padrao,
    ativo
FROM tbl_catalogo_materiais
WHERE ativo = 1
ORDER BY id_catalogo DESC
LIMIT 15;

-- ============================================================================
-- NOTAS IMPORTANTES:
-- ============================================================================
-- 1. Ajuste os IDs de categoria, fornecedor e fabricante conforme seu banco
-- 2. Os códigos de barras são exemplos (alguns reais, outros fictícios)
-- 3. Os preços são aproximados e devem ser ajustados conforme sua realidade
-- 4. Este script cria o catálogo centralizado
-- 5. Para criar o estoque por filial, use a tela addMaterial.php ou script separado
-- ============================================================================

