-- Inserir configurações de horário de funcionamento
INSERT INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria, ativo) VALUES 
('horario_funcionamento_ativo', '0', 'Ativar controle de horário de funcionamento', 'boolean', 'sistema', 1),
('horario_inicio_semana', '08:00', 'Horário de início - Segunda a Sexta', 'time', 'sistema', 1),
('horario_fim_semana', '18:00', 'Horário de fim - Segunda a Sexta', 'time', 'sistema', 1),
('horario_inicio_sabado', '08:00', 'Horário de início - Sábado', 'time', 'sistema', 1),
('horario_fim_sabado', '12:00', 'Horário de fim - Sábado', 'time', 'sistema', 1),
('horario_domingo_ativo', '0', 'Permitir acesso aos domingos', 'boolean', 'sistema', 1),
('horario_inicio_domingo', '08:00', 'Horário de início - Domingo', 'time', 'sistema', 1),
('horario_fim_domingo', '12:00', 'Horário de fim - Domingo', 'time', 'sistema', 1);

-- Verificar se as configurações foram inseridas
SELECT * FROM tbl_configuracoes WHERE categoria = 'sistema' AND chave LIKE 'horario_%';