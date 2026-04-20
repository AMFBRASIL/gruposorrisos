-- Script para adicionar categoria EPI ao sistema
-- Esta categoria será usada para testar o campo CA

-- Inserir categoria EPI
INSERT INTO `tbl_categorias` (`nome_categoria`, `descricao`, `ativo`) VALUES
('EPI - Equipamento de Proteção Individual', 'Equipamentos de proteção individual para segurança do trabalho', 1);

-- Verificar se foi inserida
SELECT * FROM `tbl_categorias` WHERE `nome_categoria` LIKE '%EPI%';

-- Comentário: Esta categoria será usada para testar o campo CA
-- O campo CA aparecerá automaticamente quando esta categoria for selecionada 