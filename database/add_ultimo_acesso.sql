-- =====================================================
-- Adicionar campo ultimo_acesso na tabela tbl_usuarios
-- =====================================================

-- Verificar e adicionar o campo ultimo_acesso se não existir
SET @dbname = DATABASE();
SET @tablename = 'tbl_usuarios';
SET @columnname = 'ultimo_acesso';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' timestamp NULL AFTER ativo')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar índice no campo ultimo_acesso para otimizar consultas de estatísticas
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'idx_ultimo_acesso')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX idx_ultimo_acesso (ultimo_acesso)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Informações sobre a execução
SELECT 'Campo ultimo_acesso verificado/adicionado com sucesso na tabela tbl_usuarios' as status;

