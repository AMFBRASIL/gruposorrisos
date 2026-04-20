-- ============================================================================
-- Script de Inserção de Materiais com Lookup Automático
-- Tabela: tbl_catalogo_materiais
-- Data: 2025-11-03
-- 
-- Este script faz matching automático de:
-- - Categorias (usando LIKE)
-- - Unidades (usando LIKE)  
-- - Fornecedores/Fabricantes (usando LIKE)
-- ============================================================================

-- ============================================================================
-- FUNÇÃO AUXILIAR: Buscar ID de Categoria
-- ============================================================================
-- Exemplo de uso:
-- (SELECT id_categoria FROM tbl_categorias WHERE nome_categoria LIKE '%ANEST%' LIMIT 1)

-- ============================================================================
-- FUNÇÃO AUXILIAR: Buscar ID de Unidade
-- ============================================================================
-- 1 = UN (Unidade)
-- 2 = KG (Quilograma)
-- 3 = M (Metro)
-- 4 = M² (Metro Quadrado)
-- 5 = M³ (Metro Cúbico)
-- 6 = L (Litro)
-- 7 = CX (Caixa)
-- 8 = PCT (Pacote)
-- 9 = ROL (Rolo)
-- 10 = PAR (Par)

-- ============================================================================
-- TEMPLATE DE INSERÇÃO
-- ============================================================================
-- Cole aqui os dados dos seus materiais seguindo este formato:

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

-- ============================================================================
-- EXEMPLO 1: Material com busca automática de categoria
-- ============================================================================
(
    'MAT001',
    'Nome do Material Aqui',
    'Descrição detalhada do material',
    -- Busca categoria que contém a palavra-chave
    (SELECT id_categoria FROM tbl_categorias 
     WHERE nome_categoria LIKE '%PALAVRA_CHAVE%' 
     OR nome_categoria LIKE '%OUTRA_PALAVRA%'
     LIMIT 1),
    -- Busca fornecedor/fabricante
    (SELECT id_fornecedor FROM tbl_fornecedores 
     WHERE razao_social LIKE '%NOME_FORNECEDOR%' 
     AND ativo = 1 
     LIMIT 1),
    -- Busca fabricante (se tiver)
    (SELECT id_fornecedor FROM tbl_fornecedores 
     WHERE razao_social LIKE '%NOME_FABRICANTE%' 
     AND is_fabricante = 1 
     AND ativo = 1 
     LIMIT 1),
    1, -- Unidade: 1=UN, 7=CX, 8=PCT
    0.00, -- Preço
    10, -- Estoque mínimo
    100, -- Estoque máximo
    NULL, -- Código de barras
    NULL, -- CA
    NULL, -- Observações
    1, -- Ativo
    NOW()
);

-- ============================================================================
-- HELPER: Ver todas as categorias disponíveis
-- ============================================================================
-- SELECT id_categoria, nome_categoria FROM tbl_categorias ORDER BY nome_categoria;

-- ============================================================================
-- HELPER: Ver todos os fornecedores
-- ============================================================================
-- SELECT id_fornecedor, razao_social, is_fabricante FROM tbl_fornecedores WHERE ativo = 1;

-- ============================================================================
-- CATEGORIAS DISPONÍVEIS (para referência):
-- ============================================================================
-- ANESTESICOS E AGULHA GENGIVAL
-- ARTICULADOR
-- BIOSSEGURANÇA
-- BROCAS
-- CIMENTOS
-- CIRURGIA E PERIODONTIA
-- CLINICO GERAL
-- DENTÍSTICA E ESTÉTICA
-- DESCARTÁVEIS
-- ENDODONTIA
-- EQUIPAMENTOS LABORATORIAIS
-- HIGIENE ORAL
-- IMPLANTODONTIA
-- INSTRUMENTAIS
-- MANDRIL
-- MATERIAS DE LIMPEZA
-- MOLDAGEM E MODELO
-- ORTODONTIA
-- PLASTIFICADORA
-- PREVENÇAO E PROFILAXIA
-- PROTESE
-- PROTESE CLINICA
-- PROTESE LABORATORIAL
-- RADIOLOGIA

-- ============================================================================
-- FORNECEDORES CADASTRADOS (verificar antes de usar):
-- ============================================================================
-- ID 12 - 3M DO BRASIL LTDA - BRASIL (is_fabricante = 1)
-- ID 13 - DENTAL CREMER PRODUTOS ODONTOLOGICOS S.A. (is_fabricante = 0)
-- ID 14 - GARDEIS EQUIP. DE PROTECAO INDIVIDUAL (is_fabricante = 0)
-- ID 15 - EAC LIVRARIA E PAPELARIA LTDA - ME (is_fabricante = 0)

-- ============================================================================

