-- =====================================================
-- TRIGGERS E PROCEDURES CORRIGIDOS - SISTEMA DE ESTOQUE GRUPO SORRISOS
-- =====================================================

-- --------------------------------------------------------
-- 1. TRIGGERS PARA AUTOMATIZAÇÃO
-- --------------------------------------------------------

-- Trigger para atualizar estoque por filial automaticamente
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `tr_movimentacao_estoque_filial` 
AFTER INSERT ON `tbl_movimentacoes`
FOR EACH ROW
BEGIN
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    
    -- Atualizar estoque da filial origem (se houver)
    IF NEW.id_filial_origem IS NOT NULL THEN
        -- Buscar estoque atual
        SELECT COALESCE(estoque_atual, 0) INTO v_estoque_atual
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
        SELECT COALESCE(estoque_atual, 0) INTO v_estoque_atual
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_destino;
        
        -- Inserir ou atualizar registro
        INSERT INTO tbl_estoque_filial (id_material, id_filial, estoque_atual, ultima_movimentacao)
        VALUES (NEW.id_material, NEW.id_filial_destino, NEW.estoque_atual_destino, NEW.data_movimentacao)
        ON DUPLICATE KEY UPDATE 
            estoque_atual = NEW.estoque_atual_destino,
            ultima_movimentacao = NEW.data_movimentacao;
    END IF;
END//
DELIMITER ;

-- Trigger para auditoria automática de movimentações
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `tr_auditoria_movimentacao` 
AFTER INSERT ON `tbl_movimentacoes`
FOR EACH ROW
BEGIN
    INSERT INTO tbl_auditoria_movimentacoes (
        id_movimentacao, 
        acao, 
        dados_novos, 
        id_usuario, 
        data_auditoria
    ) VALUES (
        NEW.id_movimentacao,
        'criacao',
        JSON_OBJECT(
            'numero_movimentacao', NEW.numero_movimentacao,
            'tipo_movimentacao', NEW.tipo_movimentacao,
            'quantidade', NEW.quantidade,
            'valor_total', NEW.valor_total,
            'status_movimentacao', NEW.status_movimentacao
        ),
        NEW.id_usuario_executor,
        NOW()
    );
END//
DELIMITER ;

-- Trigger para atualizar estoque na tabela de materiais
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `tr_atualizar_estoque_material` 
AFTER INSERT ON `tbl_movimentacoes`
FOR EACH ROW
BEGIN
    -- Atualizar estoque na tabela de materiais (filial principal)
    IF NEW.id_filial_destino IS NOT NULL THEN
        UPDATE tbl_materiais 
        SET estoque_atual = NEW.estoque_atual_destino,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_destino;
    ELSEIF NEW.id_filial_origem IS NOT NULL THEN
        UPDATE tbl_materiais 
        SET estoque_atual = NEW.estoque_atual_origem,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_origem;
    END IF;
END//
DELIMITER ;

-- Trigger para gerar alertas de estoque baixo
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `tr_alerta_estoque_baixo` 
AFTER INSERT ON `tbl_movimentacoes`
FOR EACH ROW
BEGIN
    DECLARE v_estoque_minimo DECIMAL(15,3) DEFAULT 0;
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    DECLARE v_filial_id INT DEFAULT NULL;
    
    -- Determinar filial afetada
    IF NEW.id_filial_destino IS NOT NULL THEN
        SET v_filial_id = NEW.id_filial_destino;
        SET v_estoque_atual = NEW.estoque_atual_destino;
    ELSEIF NEW.id_filial_origem IS NOT NULL THEN
        SET v_filial_id = NEW.id_filial_origem;
        SET v_estoque_atual = NEW.estoque_atual_origem;
    END IF;
    
    -- Buscar estoque mínimo
    IF v_filial_id IS NOT NULL THEN
        SELECT COALESCE(estoque_minimo, 0) INTO v_estoque_minimo
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = v_filial_id;
        
        -- Gerar alerta de estoque baixo
        IF v_estoque_atual <= v_estoque_minimo AND v_estoque_atual > 0 THEN
            INSERT INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual, 
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                NEW.id_material, v_filial_id, 'estoque_baixo', v_estoque_atual,
                v_estoque_minimo, 'media', 
                CONCAT('Estoque baixo detectado após movimentação. Quantidade atual: ', v_estoque_atual)
            );
        END IF;
        
        -- Gerar alerta de estoque zerado
        IF v_estoque_atual = 0 THEN
            INSERT INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                prioridade, mensagem
            ) VALUES (
                NEW.id_material, v_filial_id, 'estoque_zerado', v_estoque_atual,
                'alta', 'Estoque zerado - necessário reposição urgente'
            );
        END IF;
    END IF;
END//
DELIMITER ;

-- --------------------------------------------------------
-- 2. PROCEDURES ÚTEIS (CORRIGIDAS)
-- --------------------------------------------------------

-- Procedure para calcular custo médio
DELIMITER //
CREATE PROCEDURE `sp_calcular_custo_medio`(
    IN p_id_material INT,
    IN p_id_filial INT
)
BEGIN
    DECLARE v_custo_medio DECIMAL(15,4);
    
    SELECT 
        CASE 
            WHEN SUM(quantidade) > 0 THEN SUM(valor_total) / SUM(quantidade)
            ELSE 0 
        END INTO v_custo_medio
    FROM tbl_movimentacoes 
    WHERE id_material = p_id_material 
    AND id_filial_destino = p_id_filial 
    AND tipo_movimentacao = 'entrada'
    AND status_movimentacao = 'executada';
    
    UPDATE tbl_estoque_filial 
    SET custo_medio = v_custo_medio 
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    SELECT v_custo_medio as custo_medio_calculado;
END//
DELIMITER ;

-- Procedure para gerar alertas de estoque
DELIMITER //
CREATE PROCEDURE `sp_gerar_alertas_estoque`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_material, v_id_filial INT;
    DECLARE v_estoque_atual, v_estoque_minimo, v_estoque_maximo DECIMAL(15,3);
    DECLARE v_cursor CURSOR FOR 
        SELECT id_material, id_filial, estoque_atual, estoque_minimo, estoque_maximo 
        FROM tbl_estoque_filial;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN v_cursor;
    
    read_loop: LOOP
        FETCH v_cursor INTO v_id_material, v_id_filial, v_estoque_atual, v_estoque_minimo, v_estoque_maximo;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Alerta de estoque baixo
        IF v_estoque_atual <= v_estoque_minimo AND v_estoque_atual > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual, 
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_baixo', v_estoque_atual,
                v_estoque_minimo, 'media', CONCAT('Estoque baixo: ', v_estoque_atual, ' unidades')
            );
        END IF;
        
        -- Alerta de estoque zerado
        IF v_estoque_atual = 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_zerado', v_estoque_atual,
                'alta', 'Estoque zerado - necessário reposição urgente'
            );
        END IF;
        
        -- Alerta de estoque alto
        IF v_estoque_atual > v_estoque_maximo AND v_estoque_maximo > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_alto', v_estoque_atual,
                v_estoque_maximo, 'baixa', CONCAT('Estoque alto: ', v_estoque_atual, ' unidades')
            );
        END IF;
        
    END LOOP;
    
    CLOSE v_cursor;
    
    SELECT 'Alertas de estoque gerados com sucesso!' as resultado;
END//
DELIMITER ;

-- Procedure para verificar vencimento de lotes
DELIMITER //
CREATE PROCEDURE `sp_verificar_vencimento_lotes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_lote, v_id_material, v_id_filial INT;
    DECLARE v_data_validade DATE;
    DECLARE v_dias_vencimento INT;
    DECLARE v_cursor CURSOR FOR 
        SELECT id_lote, id_material, id_filial, data_validade
        FROM tbl_lotes 
        WHERE status = 'ativo' AND data_validade IS NOT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN v_cursor;
    
    read_loop: LOOP
        FETCH v_cursor INTO v_id_lote, v_id_material, v_id_filial, v_data_validade;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET v_dias_vencimento = DATEDIFF(v_data_validade, CURDATE());
        
        -- Alerta de vencimento próximo (30 dias)
        IF v_dias_vencimento <= 30 AND v_dias_vencimento > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                data_vencimento, dias_vencimento, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'vencimento_proximo', 0,
                v_data_validade, v_dias_vencimento, 'media',
                CONCAT('Lote vence em ', v_dias_vencimento, ' dias')
            );
        END IF;
        
        -- Alerta de vencido
        IF v_dias_vencimento < 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                data_vencimento, dias_vencimento, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'vencido', 0,
                v_data_validade, v_dias_vencimento, 'alta',
                CONCAT('Lote vencido há ', ABS(v_dias_vencimento), ' dias')
            );
            
            -- Marcar lote como vencido
            UPDATE tbl_lotes SET status = 'vencido' WHERE id_lote = v_id_lote;
        END IF;
        
    END LOOP;
    
    CLOSE v_cursor;
    
    SELECT 'Verificação de vencimento concluída!' as resultado;
END//
DELIMITER ;

-- Procedure para gerar número de movimentação
DELIMITER //
CREATE PROCEDURE `sp_gerar_numero_movimentacao`(
    OUT p_numero_movimentacao VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_movimentacao, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_movimentacoes 
    WHERE numero_movimentacao LIKE CONCAT('MOV-', v_ano, '-%');
    
    SET v_numero = CONCAT('MOV-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_movimentacoes WHERE numero_movimentacao = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('MOV-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_movimentacao = v_numero;
END//
DELIMITER ;

-- Procedure para gerar número de pedido
DELIMITER //
CREATE PROCEDURE `sp_gerar_numero_pedido`(
    OUT p_numero_pedido VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_pedido, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_pedidos_compra 
    WHERE numero_pedido LIKE CONCAT('PED-', v_ano, '-%');
    
    SET v_numero = CONCAT('PED-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_pedidos_compra WHERE numero_pedido = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('PED-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_pedido = v_numero;
END//
DELIMITER ;

-- Procedure para gerar número de ticket
DELIMITER //
CREATE PROCEDURE `sp_gerar_numero_ticket`(
    OUT p_numero_ticket VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_ticket, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_tickets 
    WHERE numero_ticket LIKE CONCAT('TKT-', v_ano, '-%');
    
    SET v_numero = CONCAT('TKT-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_tickets WHERE numero_ticket = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('TKT-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_ticket = v_numero;
END//
DELIMITER ;

-- Procedure para limpar alertas antigos (CORRIGIDA)
DELIMITER //
CREATE PROCEDURE `sp_limpar_alertas_antigos`(
    IN p_dias INT
)
BEGIN
    DECLARE v_dias INT DEFAULT 30;
    
    -- Se p_dias for NULL, usar valor padrão
    IF p_dias IS NULL THEN
        SET v_dias = 30;
    ELSE
        SET v_dias = p_dias;
    END IF;
    
    DELETE FROM tbl_alertas_estoque 
    WHERE data_criacao < DATE_SUB(NOW(), INTERVAL v_dias DAY)
    AND status IN ('resolvido', 'ignorado');
    
    SELECT CONCAT('Alertas antigos removidos (mais de ', v_dias, ' dias)') as resultado;
END//
DELIMITER ;

-- Procedure para backup de dados críticos
DELIMITER //
CREATE PROCEDURE `sp_backup_dados_criticos`()
BEGIN
    -- Criar tabela de backup com timestamp
    SET @backup_table = CONCAT('backup_movimentacoes_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'));
    SET @sql = CONCAT('CREATE TABLE ', @backup_table, ' AS SELECT * FROM tbl_movimentacoes');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    SELECT CONCAT('Backup criado: ', @backup_table) as resultado;
END//
DELIMITER ;

-- --------------------------------------------------------
-- 3. FUNCTIONS ÚTEIS
-- --------------------------------------------------------

-- Function para calcular valor total do estoque
DELIMITER //
CREATE FUNCTION `fn_calcular_valor_estoque`(
    p_id_material INT,
    p_id_filial INT
) RETURNS DECIMAL(15,4)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_valor_total DECIMAL(15,4) DEFAULT 0;
    
    SELECT COALESCE(estoque_atual * custo_medio, 0)
    INTO v_valor_total
    FROM tbl_estoque_filial
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    RETURN v_valor_total;
END//
DELIMITER ;

-- Function para verificar se material tem estoque suficiente
DELIMITER //
CREATE FUNCTION `fn_verificar_estoque_suficiente`(
    p_id_material INT,
    p_id_filial INT,
    p_quantidade DECIMAL(15,3)
) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    
    SELECT COALESCE(estoque_atual, 0)
    INTO v_estoque_atual
    FROM tbl_estoque_filial
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    RETURN v_estoque_atual >= p_quantidade;
END//
DELIMITER ;

-- Function para calcular dias até vencimento
DELIMITER //
CREATE FUNCTION `fn_dias_ate_vencimento`(
    p_data_validade DATE
) RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    RETURN DATEDIFF(p_data_validade, CURDATE());
END//
DELIMITER ;

-- --------------------------------------------------------
-- 4. EVENTOS AGENDADOS
-- --------------------------------------------------------

-- Evento para gerar alertas diariamente
DELIMITER //
CREATE EVENT IF NOT EXISTS `ev_gerar_alertas_diario`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL sp_gerar_alertas_estoque();
    CALL sp_verificar_vencimento_lotes();
END//
DELIMITER ;

-- Evento para limpar alertas antigos semanalmente
DELIMITER //
CREATE EVENT IF NOT EXISTS `ev_limpar_alertas_semanal`
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL sp_limpar_alertas_antigos(30);
END//
DELIMITER ;

-- Evento para backup mensal
DELIMITER //
CREATE EVENT IF NOT EXISTS `ev_backup_mensal`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL sp_backup_dados_criticos();
END//
DELIMITER ;

-- --------------------------------------------------------
-- 5. ATIVAR EVENTOS
-- --------------------------------------------------------

SET GLOBAL event_scheduler = ON;

-- --------------------------------------------------------
-- 6. MENSAGEM DE CONCLUSÃO
-- --------------------------------------------------------

SELECT 'TRIGGERS E PROCEDURES CORRIGIDOS CRIADOS COM SUCESSO!' as Status; 