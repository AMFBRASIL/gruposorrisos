-- Configurações SMTP (tela configuracoes.php). Execute uma vez no banco existente.
-- EmailUtils lê estas chaves quando smtp_ativo = 1.

INSERT IGNORE INTO `tbl_configuracoes` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('smtp_ativo', '0', 'Ativar envio de e-mails via SMTP', 'booleano', 'email'),
('smtp_host', '', 'Servidor SMTP (hostname)', 'texto', 'email'),
('smtp_port', '587', 'Porta SMTP (587 TLS, 465 SSL)', 'numero', 'email'),
('smtp_secure', 'tls', 'Criptografia: tls, ssl ou none', 'texto', 'email'),
('smtp_timeout', '15', 'Timeout da conexão SMTP em segundos', 'numero', 'email'),
('smtp_username', '', 'Usuário SMTP', 'texto', 'email'),
('smtp_password', '', 'Senha SMTP', 'texto', 'email'),
('smtp_from_email', '', 'E-mail remetente (From)', 'email', 'email'),
('smtp_from_name', 'Grupo Sorrisos', 'Nome exibido do remetente', 'texto', 'email'),
('smtp_reply_to', '', 'E-mail Reply-To (opcional)', 'email', 'email'),
('smtp_reply_to_name', '', 'Nome Reply-To (opcional)', 'texto', 'email');
