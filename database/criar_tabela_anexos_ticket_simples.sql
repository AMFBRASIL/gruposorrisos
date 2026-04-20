-- =====================================================
-- SQL SIMPLIFICADO - Criar tabela de anexos de tickets
-- =====================================================

-- Criar tabela de anexos de tickets
CREATE TABLE IF NOT EXISTS `tbl_anexos_ticket` (
    `id_anexo` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ticket` INT NOT NULL,
    `id_usuario` INT NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `nome_original` VARCHAR(255) NOT NULL,
    `tipo_arquivo` VARCHAR(100),
    `tamanho` INT COMMENT 'Tamanho em bytes',
    `caminho` VARCHAR(500) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar chaves estrangeiras
ALTER TABLE `tbl_anexos_ticket` 
ADD CONSTRAINT `fk_anexos_ticket` 
FOREIGN KEY (`id_ticket`) 
REFERENCES `tbl_tickets`(`id_ticket`) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE `tbl_anexos_ticket` 
ADD CONSTRAINT `fk_anexos_usuario` 
FOREIGN KEY (`id_usuario`) 
REFERENCES `tbl_usuarios`(`id_usuario`) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

