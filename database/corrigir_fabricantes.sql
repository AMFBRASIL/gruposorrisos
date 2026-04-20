-- ============================================================================
-- Correção: Ativar e Marcar Fabricantes
-- Data: 2025-11-03
-- ============================================================================

-- Situação atual:
-- ID 12 - 3M DO BRASIL: is_fabricante = 1, ativo = 0 ❌
-- ID 13 - DENTAL CREMER: is_fabricante = 0, ativo = 1 ❌

-- ============================================================================
-- SOLUÇÃO: Ativar 3M DO BRASIL (já é fabricante, só precisa ativar)
-- ============================================================================
UPDATE tbl_fornecedores 
SET ativo = 1 
WHERE id_fornecedor = 12;

-- ============================================================================
-- SOLUÇÃO ADICIONAL: Marcar DENTAL CREMER como fabricante também
-- ============================================================================
UPDATE tbl_fornecedores 
SET is_fabricante = 1 
WHERE id_fornecedor = 13;

-- ============================================================================
-- Verificar o resultado
-- ============================================================================
SELECT 
    id_fornecedor,
    razao_social,
    ativo,
    is_fabricante,
    CASE 
        WHEN ativo = 1 AND is_fabricante = 1 THEN '✅ ATIVO E FABRICANTE'
        WHEN ativo = 1 AND is_fabricante = 0 THEN '⚠️ Ativo mas não é fabricante'
        WHEN ativo = 0 AND is_fabricante = 1 THEN '⚠️ É fabricante mas inativo'
        ELSE '❌ Inativo e não é fabricante'
    END as status
FROM tbl_fornecedores
ORDER BY id_fornecedor;

-- ============================================================================
-- Testar a query que a API usa
-- ============================================================================
SELECT id_fornecedor, razao_social, ativo, is_fabricante 
FROM tbl_fornecedores 
WHERE ativo = 1 AND is_fabricante = 1 
ORDER BY razao_social;

-- Deve retornar pelo menos 1 resultado agora!
-- ============================================================================

