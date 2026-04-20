-- Tabela principal de tickets
CREATE TABLE IF NOT EXISTS `tbl_tickets` (
    `id_ticket` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_ticket` VARCHAR(20) UNIQUE NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `id_categoria` INT,
    `id_prioridade` INT,
    `id_status` INT,
    `id_usuario_solicitante` INT,
    `id_usuario_atribuido` INT,
    `id_filial` INT,
    `data_abertura` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `data_fechamento` DATETIME NULL,
    `tempo_resolucao` INT NULL COMMENT 'Tempo em minutos',
    `avaliacao` TINYINT NULL COMMENT '1-5 estrelas',
    `comentario_avaliacao` TEXT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`id_status`),
    INDEX `idx_prioridade` (`id_prioridade`),
    INDEX `idx_usuario_solicitante` (`id_usuario_solicitante`),
    INDEX `idx_usuario_atribuido` (`id_usuario_atribuido`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_data_abertura` (`data_abertura`)
);

-- Tabela de categorias de tickets
CREATE TABLE IF NOT EXISTS `tbl_categorias_ticket` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_categoria` VARCHAR(100) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#007bff',
    `icone` VARCHAR(50) DEFAULT 'bi-tag',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de prioridades
CREATE TABLE IF NOT EXISTS `tbl_prioridades_ticket` (
    `id_prioridade` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_prioridade` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-flag',
    `tempo_esperado` INT DEFAULT 1440 COMMENT 'Tempo em minutos (24h padrão)',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de status
CREATE TABLE IF NOT EXISTS `tbl_status_ticket` (
    `id_status` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_status` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-circle',
    `is_final` TINYINT(1) DEFAULT 0 COMMENT 'Se é um status final (fechado)',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de comentários/atualizações do ticket
CREATE TABLE IF NOT EXISTS `tbl_comentarios_ticket` (
    `id_comentario` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ticket` INT NOT NULL,
    `id_usuario` INT NOT NULL,
    `comentario` TEXT NOT NULL,
    `tipo` ENUM('comentario', 'status', 'atribuicao', 'prioridade') DEFAULT 'comentario',
    `dados_anteriores` JSON NULL COMMENT 'Dados anteriores para histórico',
    `dados_novos` JSON NULL COMMENT 'Dados novos para histórico',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_tipo` (`tipo`)
);

-- Tabela de anexos do ticket
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
);

-- Inserir dados padrão para categorias
INSERT INTO `tbl_categorias_ticket` (`nome_categoria`, `descricao`, `cor`, `icone`) VALUES
('Suporte Técnico', 'Problemas técnicos e suporte', '#dc3545', 'bi-tools'),
('Sistema', 'Problemas com o sistema', '#007bff', 'bi-gear'),
('Estoque', 'Problemas relacionados ao estoque', '#28a745', 'bi-box-seam'),
('Financeiro', 'Problemas financeiros', '#ffc107', 'bi-currency-dollar'),
('RH', 'Recursos humanos', '#17a2b8', 'bi-people'),
('Outros', 'Outras categorias', '#6c757d', 'bi-three-dots');

-- Inserir dados padrão para prioridades
INSERT INTO `tbl_prioridades_ticket` (`nome_prioridade`, `descricao`, `cor`, `icone`, `tempo_esperado`) VALUES
('Baixa', 'Pode ser resolvido em até 72h', '#6c757d', 'bi-flag', 4320),
('Média', 'Deve ser resolvido em até 24h', '#ffc107', 'bi-flag-fill', 1440),
('Alta', 'Deve ser resolvido em até 4h', '#fd7e14', 'bi-exclamation-triangle', 240),
('Crítica', 'Deve ser resolvido imediatamente', '#dc3545', 'bi-exclamation-triangle-fill', 60);

-- Inserir dados padrão para status
INSERT INTO `tbl_status_ticket` (`nome_status`, `descricao`, `cor`, `icone`, `is_final`) VALUES
('Aberto', 'Ticket recém aberto', '#007bff', 'bi-circle-fill', 0),
('Em Análise', 'Ticket sendo analisado', '#ffc107', 'bi-clock', 0),
('Em Andamento', 'Ticket sendo trabalhado', '#17a2b8', 'bi-play-circle', 0),
('Aguardando Cliente', 'Aguardando resposta do cliente', '#6c757d', 'bi-pause-circle', 0),
('Aguardando Terceiros', 'Aguardando terceiros', '#fd7e14', 'bi-people', 0),
('Resolvido', 'Ticket resolvido', '#28a745', 'bi-check-circle', 1),
('Fechado', 'Ticket fechado', '#6c757d', 'bi-x-circle', 1),
('Cancelado', 'Ticket cancelado', '#dc3545', 'bi-x-circle-fill', 1);

-- Adicionar chaves estrangeiras
ALTER TABLE `tbl_tickets` 
ADD CONSTRAINT `fk_tickets_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias_ticket`(`id_categoria`),
ADD CONSTRAINT `fk_tickets_prioridade` FOREIGN KEY (`id_prioridade`) REFERENCES `tbl_prioridades_ticket`(`id_prioridade`),
ADD CONSTRAINT `fk_tickets_status` FOREIGN KEY (`id_status`) REFERENCES `tbl_status_ticket`(`id_status`),
ADD CONSTRAINT `fk_tickets_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios`(`id_usuario`),
ADD CONSTRAINT `fk_tickets_usuario_atribuido` FOREIGN KEY (`id_usuario_atribuido`) REFERENCES `tbl_usuarios`(`id_usuario`),
ADD CONSTRAINT `fk_tickets_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`);

ALTER TABLE `tbl_comentarios_ticket` 
ADD CONSTRAINT `fk_comentarios_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets`(`id_ticket`),
ADD CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`);

ALTER TABLE `tbl_anexos_ticket` 
ADD CONSTRAINT `fk_anexos_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets`(`id_ticket`),
ADD CONSTRAINT `fk_anexos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`); 