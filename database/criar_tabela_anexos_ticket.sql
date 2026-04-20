-- =====================================================
-- Script SQL para criar tabela de anexos de tickets
-- Sistema: Grupo Sorrisos - Gestão de Estoque
-- Data: 2024
-- =====================================================

-- Verificar se a tabela já existe e removê-la (opcional)
-- DROP TABLE IF EXISTS `tbl_anexos_ticket`;

-- Criar tabela de anexos de tickets
CREATE TABLE IF NOT EXISTS `tbl_anexos_ticket` (
    `id_anexo` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único do anexo',
    `id_ticket` INT NOT NULL COMMENT 'ID do ticket ao qual o anexo pertence',
    `id_usuario` INT NOT NULL COMMENT 'ID do usuário que fez o upload do anexo',
    `nome_arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo salvo no servidor (com hash único)',
    `nome_original` VARCHAR(255) NOT NULL COMMENT 'Nome original do arquivo enviado pelo usuário',
    `tipo_arquivo` VARCHAR(100) COMMENT 'Tipo MIME do arquivo (ex: application/pdf, image/jpeg)',
    `tamanho` INT COMMENT 'Tamanho do arquivo em bytes',
    `caminho` VARCHAR(500) NOT NULL COMMENT 'Caminho relativo do arquivo no servidor',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
    
    -- Índices para melhorar performance nas consultas
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela para armazenar anexos de arquivos dos tickets';

-- Adicionar chaves estrangeiras (após criar a tabela)
-- IMPORTANTE: Certifique-se de que as tabelas tbl_tickets e tbl_usuarios já existam

-- Verificar se as constraints já existem antes de adicionar
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_anexos_ticket'
    AND CONSTRAINT_NAME = 'fk_anexos_ticket'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `tbl_anexos_ticket` 
     ADD CONSTRAINT `fk_anexos_ticket` 
     FOREIGN KEY (`id_ticket`) 
     REFERENCES `tbl_tickets`(`id_ticket`) 
     ON DELETE CASCADE 
     ON UPDATE CASCADE',
    'SELECT "Constraint fk_anexos_ticket já existe" AS mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_anexos_ticket'
    AND CONSTRAINT_NAME = 'fk_anexos_usuario'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `tbl_anexos_ticket` 
     ADD CONSTRAINT `fk_anexos_usuario` 
     FOREIGN KEY (`id_usuario`) 
     REFERENCES `tbl_usuarios`(`id_usuario`) 
     ON DELETE RESTRICT 
     ON UPDATE CASCADE',
    'SELECT "Constraint fk_anexos_usuario já existe" AS mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- ESTRUTURA DA TABELA
-- =====================================================
-- id_anexo: Chave primária auto-incrementada
-- id_ticket: Referência ao ticket (FK para tbl_tickets)
-- id_usuario: Referência ao usuário que fez upload (FK para tbl_usuarios)
-- nome_arquivo: Nome único gerado pelo sistema (ex: 507f1f77bcf86cd799439011_1234567890.pdf)
-- nome_original: Nome original do arquivo enviado pelo usuário
-- tipo_arquivo: Tipo MIME do arquivo (ex: application/pdf, image/png)
-- tamanho: Tamanho em bytes do arquivo
-- caminho: Caminho relativo (ex: uploads/tickets/123/507f1f77bcf86cd799439011_1234567890.pdf)
-- created_at: Timestamp de quando o anexo foi criado
-- =====================================================

-- Verificar se a tabela foi criada corretamente
SELECT 
    TABLE_NAME,
    TABLE_COMMENT,
    ENGINE,
    TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_anexos_ticket';

-- Verificar estrutura da tabela
DESCRIBE `tbl_anexos_ticket`;

-- Verificar índices
SHOW INDEX FROM `tbl_anexos_ticket`;

-- Verificar chaves estrangeiras
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_anexos_ticket'
AND REFERENCED_TABLE_NAME IS NOT NULL;

