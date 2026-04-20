-- Script para verificar se o campo CA foi adicionado à tabela tbl_materiais

-- Verificar estrutura da tabela
DESCRIBE `tbl_materiais`;

-- Verificar se o campo CA existe
SHOW COLUMNS FROM `tbl_materiais` LIKE 'ca';

-- Verificar índices da tabela
SHOW INDEX FROM `tbl_materiais`;

-- Comentário: Execute este script para verificar se o campo CA foi criado corretamente
-- Se o campo não existir, execute primeiro o script adicionar_campo_ca.sql 