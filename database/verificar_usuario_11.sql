-- Verificar se o usuário ID 11 é um fornecedor
-- Consulta corrigida (sem coluna 'perfil')

-- 1. Verificar dados do usuário ID 11
SELECT 
    u.id_usuario,
    u.nome_completo,
    u.email,
    u.ativo as usuario_ativo,
    f.id_fornecedor,
    f.razao_social,
    f.nome_fantasia,
    f.email as email_fornecedor,
    f.ativo as fornecedor_ativo
FROM tbl_usuarios u
LEFT JOIN tbl_fornecedores f ON u.id_usuario = f.id_fornecedor
WHERE u.id_usuario = 11;

-- 2. Verificar se existem fornecedores na tabela
SELECT 
    'Total de fornecedores' as descricao,
    COUNT(*) as total
FROM tbl_fornecedores
UNION ALL
SELECT 
    'Fornecedores ativos' as descricao,
    COUNT(*) as total
FROM tbl_fornecedores
WHERE ativo = 1;

-- 3. Verificar se existem usuários na tabela
SELECT 
    'Total de usuários' as descricao,
    COUNT(*) as total
FROM tbl_usuarios
UNION ALL
SELECT 
    'Usuários ativos' as descricao,
    COUNT(*) as total
FROM tbl_usuarios
WHERE ativo = 1;

-- 4. Verificar estrutura da tabela tbl_usuarios
DESCRIBE tbl_usuarios;

-- 5. Verificar estrutura da tabela tbl_fornecedores
DESCRIBE tbl_fornecedores; 