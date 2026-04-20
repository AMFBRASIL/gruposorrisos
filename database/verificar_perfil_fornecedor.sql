-- Script para verificar e criar o perfil 'Fornecedor'
-- Data: 2025-01-22
-- Objetivo: Garantir que o perfil 'Fornecedor' existe na tabela tbl_perfis

-- 1. Verificar se a tabela tbl_perfis existe
SHOW TABLES LIKE 'tbl_perfis';

-- 2. Se existir, verificar estrutura
DESCRIBE tbl_perfis;

-- 3. Verificar perfis existentes
SELECT * FROM tbl_perfis ORDER BY id_perfil;

-- 4. Verificar se existe o perfil 'Fornecedor'
SELECT * FROM tbl_perfis WHERE nome_perfil = 'Fornecedor';

-- 5. Se não existir, criar o perfil 'Fornecedor'
-- (Descomente as linhas abaixo se precisar criar o perfil)

-- INSERT INTO tbl_perfis (nome_perfil, descricao, ativo, data_criacao, data_atualizacao) 
-- VALUES ('Fornecedor', 'Usuário fornecedor do sistema', 1, NOW(), NOW());

-- 6. Verificar novamente após criação
-- SELECT * FROM tbl_perfis WHERE nome_perfil = 'Fornecedor';

-- 7. Verificar relacionamento com usuários
SELECT 
    u.id_usuario,
    u.nome_completo,
    p.nome_perfil,
    u.id_fornecedor
FROM tbl_usuarios u
LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
ORDER BY u.id_usuario; 