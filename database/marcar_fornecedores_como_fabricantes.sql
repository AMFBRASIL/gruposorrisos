-- ============================================================================
-- Script para Marcar Fornecedores como Fabricantes
-- Data: 2025-11-03
-- ============================================================================

-- ATENÇÃO: Este script deve ser executado DEPOIS de adicionar_campo_fabricante.sql

-- ============================================================================
-- OPÇÃO 1: Marcar TODOS os fornecedores ativos como fabricantes
-- ============================================================================
-- Descomente a linha abaixo para marcar todos
-- UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE ativo = 1;

-- ============================================================================
-- OPÇÃO 2: Marcar fornecedores específicos como fabricantes
-- ============================================================================
-- Substitua os IDs pelos IDs dos fornecedores que são fabricantes

-- Exemplo: Marcar fornecedores com ID 1, 2 e 3
-- UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE id_fornecedor IN (1, 2, 3);

-- Ou um por vez:
-- UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE id_fornecedor = 1;
-- UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE id_fornecedor = 2;

-- ============================================================================
-- OPÇÃO 3: Marcar com base no nome
-- ============================================================================
-- UPDATE tbl_fornecedores SET is_fabricante = 1 WHERE razao_social LIKE '%Nome do Fabricante%';

-- ============================================================================
-- Verificar quais fornecedores são fabricantes
-- ============================================================================
SELECT 
    id_fornecedor,
    razao_social,
    nome_fantasia,
    is_fabricante,
    CASE 
        WHEN is_fabricante = 1 THEN '✅ É Fabricante'
        ELSE '❌ Não é Fabricante'
    END as status_fabricante
FROM tbl_fornecedores
WHERE ativo = 1
ORDER BY razao_social;

-- ============================================================================
-- NOTAS:
-- ============================================================================
-- 1. Descomente a query UPDATE que você deseja executar
-- 2. Substitua os IDs ou nomes conforme necessário
-- 3. Execute a query SELECT no final para verificar
-- ============================================================================

