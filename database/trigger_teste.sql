-- =====================================================
-- TESTE DE TRIGGER SIMPLES
-- =====================================================

-- Teste 1: Trigger básico sem variáveis
DROP TRIGGER IF EXISTS `tr_teste_basico`;
CREATE TRIGGER `tr_teste_basico` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
BEGIN
    INSERT INTO tbl_logs_sistema (acao, tabela, id_registro, dados_novos) 
    VALUES ('teste', 'tbl_movimentacoes', NEW.id_movimentacao, 'Teste de trigger');
END;

-- Teste 2: Trigger com delimitador explícito
DELIMITER $$
DROP TRIGGER IF EXISTS `tr_teste_delimitador`$$
CREATE TRIGGER `tr_teste_delimitador` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
BEGIN
    INSERT INTO tbl_logs_sistema (acao, tabela, id_registro, dados_novos) 
    VALUES ('teste_delimitador', 'tbl_movimentacoes', NEW.id_movimentacao, 'Teste com delimitador');
END$$
DELIMITER ;

-- Teste 3: Trigger com variável simples
DELIMITER $$
DROP TRIGGER IF EXISTS `tr_teste_variavel`$$
CREATE TRIGGER `tr_teste_variavel` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
BEGIN
    DECLARE valor_teste INT DEFAULT 1;
    SET valor_teste = NEW.id_movimentacao;
    
    INSERT INTO tbl_logs_sistema (acao, tabela, id_registro, dados_novos) 
    VALUES ('teste_variavel', 'tbl_movimentacoes', valor_teste, 'Teste com variável');
END$$
DELIMITER ;

-- Teste 4: Trigger original corrigido
DELIMITER $$
DROP TRIGGER IF EXISTS `tr_movimentacao_estoque_filial`$$
CREATE TRIGGER `tr_movimentacao_estoque_filial` 
AFTER INSERT ON `tbl_movimentacoes` 
FOR EACH ROW 
BEGIN
    DECLARE estoque_atual DECIMAL(15,3) DEFAULT 0;
    
    -- Atualizar estoque da filial origem (se houver)
    IF NEW.id_filial_origem IS NOT NULL THEN
        -- Buscar estoque atual
        SELECT COALESCE(estoque_atual, 0) INTO estoque_atual
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_origem;
        
        -- Inserir ou atualizar registro
        INSERT INTO tbl_estoque_filial (id_material, id_filial, estoque_atual, ultima_movimentacao)
        VALUES (NEW.id_material, NEW.id_filial_origem, NEW.estoque_atual_origem, NEW.data_movimentacao)
        ON DUPLICATE KEY UPDATE 
            estoque_atual = NEW.estoque_atual_origem,
            ultima_movimentacao = NEW.data_movimentacao;
    END IF;
    
    -- Atualizar estoque da filial destino (se houver)
    IF NEW.id_filial_destino IS NOT NULL THEN
        -- Buscar estoque atual
        SELECT COALESCE(estoque_atual, 0) INTO estoque_atual
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_destino;
        
        -- Inserir ou atualizar registro
        INSERT INTO tbl_estoque_filial (id_material, id_filial, estoque_atual, ultima_movimentacao)
        VALUES (NEW.id_material, NEW.id_filial_destino, NEW.estoque_atual_destino, NEW.data_movimentacao)
        ON DUPLICATE KEY UPDATE 
            estoque_atual = NEW.estoque_atual_destino,
            ultima_movimentacao = NEW.data_movimentacao;
    END IF;
END$$
DELIMITER ; 