-- Script para adicionar campo CA (Certificado de Aprovação) à tabela tbl_materiais
-- Este campo será usado para materiais da categoria EPI

-- Adicionar campo CA à tabela tbl_materiais
ALTER TABLE `tbl_materiais` 
ADD COLUMN `ca` VARCHAR(50) NULL COMMENT 'Certificado de Aprovação (CA) para materiais EPI' 
AFTER `codigo_barras`;

-- Adicionar índice para melhorar performance em consultas por CA
ALTER TABLE `tbl_materiais` 
ADD INDEX `idx_materiais_ca` (`ca`);

-- Comentário explicativo sobre o uso do campo
-- O campo CA é obrigatório para materiais da categoria EPI (Equipamento de Proteção Individual)
-- Pode conter letras e números (ex: CA-12345, ABC-2024-001, etc.) 