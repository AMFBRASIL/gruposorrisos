-- =====================================================
-- MIGRAÇÃO COMPLETA - SISTEMA DE ESTOQUE GRUPO SORRISOS
-- =====================================================

-- Este script executa a migração completa do banco de dados
-- Execute este arquivo para recriar toda a estrutura do sistema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. LIMPEZA DO BANCO (OPCIONAL - DESCOMENTE SE NECESSÁRIO)
-- --------------------------------------------------------

-- DROP DATABASE IF EXISTS gruposorrisos;
-- CREATE DATABASE gruposorrisos DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE gruposorrisos;

-- --------------------------------------------------------
-- 2. EXECUTAR ESTRUTURA PRINCIPAL
-- --------------------------------------------------------

-- Executar o script de estrutura limpa
SOURCE database/gruposorrisos_limpo.sql;

-- --------------------------------------------------------
-- 3. EXECUTAR TRIGGERS E PROCEDURES
-- --------------------------------------------------------

-- Executar o script de triggers e procedures
SOURCE database/triggers_procedures.sql;

-- --------------------------------------------------------
-- 4. EXECUTAR DADOS DE EXEMPLO
-- --------------------------------------------------------

-- Executar o script de dados de exemplo
SOURCE database/dados_exemplo.sql;

-- --------------------------------------------------------
-- 5. VERIFICAÇÃO FINAL
-- --------------------------------------------------------

-- Verificar tabelas criadas
SELECT 
    TABLE_NAME as 'Tabela',
    TABLE_ROWS as 'Registros',
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as 'Tamanho (MB)'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
ORDER BY TABLE_NAME;

-- Verificar triggers criados
SELECT 
    TRIGGER_NAME as 'Trigger',
    EVENT_MANIPULATION as 'Evento',
    EVENT_OBJECT_TABLE as 'Tabela',
    ACTION_TIMING as 'Timing'
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'gruposorrisos' 
ORDER BY TRIGGER_NAME;

-- Verificar procedures criados
SELECT 
    ROUTINE_NAME as 'Procedure',
    ROUTINE_TYPE as 'Tipo',
    CREATED as 'Criado'
FROM information_schema.ROUTINES 
WHERE ROUTINE_SCHEMA = 'gruposorrisos' 
ORDER BY ROUTINE_NAME;

-- Verificar functions criadas
SELECT 
    ROUTINE_NAME as 'Function',
    ROUTINE_TYPE as 'Tipo',
    CREATED as 'Criado'
FROM information_schema.ROUTINES 
WHERE ROUTINE_SCHEMA = 'gruposorrisos' 
AND ROUTINE_TYPE = 'FUNCTION'
ORDER BY ROUTINE_NAME;

-- Verificar eventos criados
SELECT 
    EVENT_NAME as 'Evento',
    INTERVAL_VALUE as 'Intervalo',
    INTERVAL_FIELD as 'Campo',
    STATUS as 'Status'
FROM information_schema.EVENTS 
WHERE EVENT_SCHEMA = 'gruposorrisos' 
ORDER BY EVENT_NAME;

-- Verificar foreign keys
SELECT 
    CONSTRAINT_NAME as 'Constraint',
    TABLE_NAME as 'Tabela',
    COLUMN_NAME as 'Coluna',
    REFERENCED_TABLE_NAME as 'Tabela Referenciada',
    REFERENCED_COLUMN_NAME as 'Coluna Referenciada'
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- Verificar índices
SELECT 
    TABLE_NAME as 'Tabela',
    INDEX_NAME as 'Índice',
    COLUMN_NAME as 'Coluna'
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'gruposorrisos' 
ORDER BY TABLE_NAME, INDEX_NAME;

-- --------------------------------------------------------
-- 6. TESTE DAS FUNCIONALIDADES
-- --------------------------------------------------------

-- Testar procedure de geração de números
CALL sp_gerar_numero_movimentacao(@numero_mov);
SELECT @numero_mov as 'Número de Movimentação Gerado';

CALL sp_gerar_numero_pedido(@numero_ped);
SELECT @numero_ped as 'Número de Pedido Gerado';

CALL sp_gerar_numero_ticket(@numero_tkt);
SELECT @numero_tkt as 'Número de Ticket Gerado';

-- Testar function de cálculo de valor
SELECT 
    id_material,
    id_filial,
    estoque_atual,
    custo_medio,
    fn_calcular_valor_estoque(id_material, id_filial) as valor_total
FROM tbl_estoque_filial 
LIMIT 5;

-- Testar function de verificação de estoque
SELECT 
    id_material,
    id_filial,
    estoque_atual,
    fn_verificar_estoque_suficiente(id_material, id_filial, 10) as tem_estoque_suficiente
FROM tbl_estoque_filial 
LIMIT 5;

-- --------------------------------------------------------
-- 7. MENSAGEM DE CONCLUSÃO
-- --------------------------------------------------------

SELECT 'MIGRAÇÃO COMPLETA CONCLUÍDA COM SUCESSO!' as Status;
SELECT 'Sistema pronto para uso com todas as funcionalidades!' as Observacao;

COMMIT; 