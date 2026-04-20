-- =====================================================
-- Adicionar campo id_comentario na tabela de anexos
-- para associar anexos aos comentários específicos
-- =====================================================

-- Adicionar coluna id_comentario (opcional, pode ser NULL para anexos sem comentário)
ALTER TABLE `tbl_anexos_ticket` 
ADD COLUMN `id_comentario` INT NULL COMMENT 'ID do comentário ao qual o anexo está associado' AFTER `id_ticket`;

-- Adicionar índice para melhorar performance
ALTER TABLE `tbl_anexos_ticket` 
ADD INDEX `idx_comentario` (`id_comentario`);

-- Adicionar chave estrangeira para comentários
ALTER TABLE `tbl_anexos_ticket` 
ADD CONSTRAINT `fk_anexos_comentario` 
FOREIGN KEY (`id_comentario`) 
REFERENCES `tbl_comentarios_ticket`(`id_comentario`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Verificar estrutura atualizada
DESCRIBE `tbl_anexos_ticket`;


