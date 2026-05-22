-- Configurações de e-mail: provedor SMTP ou Mailgun API
-- Execute após backup. Novas chaves são criadas automaticamente ao salvar em Configurações.

INSERT IGNORE INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria) VALUES
('email_provedor', 'smtp', 'Provedor de envio: smtp ou mailgun', 'texto', 'email'),
('mailgun_domain', '', 'Domínio Mailgun (ex.: mg.seudominio.com)', 'texto', 'email'),
('mailgun_region', 'us', 'Região Mailgun: us ou eu', 'texto', 'email'),
('mailgun_api_key', '', 'Chave API privada Mailgun', 'texto', 'email');
