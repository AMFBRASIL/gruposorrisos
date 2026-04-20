<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

echo "<h2>Criação da Tabela de Configurações</h2>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Criar tabela de configurações
    $sql = "CREATE TABLE IF NOT EXISTS tbl_configuracoes (
        id_configuracao INT AUTO_INCREMENT PRIMARY KEY,
        chave VARCHAR(100) NOT NULL UNIQUE,
        valor TEXT,
        descricao TEXT,
        tipo ENUM('texto', 'numero', 'booleano', 'json', 'email', 'telefone', 'moeda', 'fuso_horario') DEFAULT 'texto',
        categoria VARCHAR(50) DEFAULT 'geral',
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Tabela tbl_configuracoes criada com sucesso<br>";
    
    // Inserir configurações padrão
    $configuracoes = [
        // Configurações Gerais da Empresa
        ['empresa_nome', 'Grupo Sorrisos Ltda', 'Nome da empresa', 'texto', 'empresa'],
        ['empresa_email', 'contato@gruposorrisos.com', 'E-mail principal da empresa', 'email', 'empresa'],
        ['empresa_telefone', '(11) 99999-9999', 'Telefone principal da empresa', 'telefone', 'empresa'],
        ['empresa_moeda', 'BRL', 'Moeda padrão do sistema', 'moeda', 'empresa'],
        ['empresa_fuso', 'America/Sao_Paulo', 'Fuso horário padrão', 'fuso_horario', 'empresa'],
        
        // Configurações de Notificações
        ['notifica_email', '1', 'Ativar notificações por e-mail', 'booleano', 'notificacoes'],
        ['notifica_pagamentos', '1', 'Notificar sobre pagamentos realizados', 'booleano', 'notificacoes'],
        ['notifica_vencimentos', '1', 'Alertas de contas próximas ao vencimento', 'booleano', 'notificacoes'],
        ['notifica_relatorios', '0', 'Envio automático de relatórios mensais', 'booleano', 'notificacoes'],
        
        // Configurações de Sistema e Backup
        ['backup_automatico', '1', 'Ativar backup automático', 'booleano', 'sistema'],
        ['backup_intervalo', 'diario', 'Intervalo do backup (diario, semanal, mensal)', 'texto', 'sistema'],
        ['backup_historico', '12', 'Manter histórico de backup em meses', 'numero', 'sistema'],
        
        // Configurações de Segurança
        ['seguranca_2fa', '0', 'Ativar autenticação em duas etapas', 'booleano', 'seguranca'],
        ['sessao_expira', '30', 'Tempo de expiração da sessão em minutos', 'numero', 'seguranca'],
        ['log_auditoria', '1', 'Ativar log de auditoria', 'booleano', 'seguranca'],
        
        // Configurações de Estoque
        ['estoque_alerta_baixo', '1', 'Ativar alertas de estoque baixo', 'booleano', 'estoque'],
        ['estoque_alerta_zerado', '1', 'Ativar alertas de estoque zerado', 'booleano', 'estoque'],
        ['estoque_alerta_excedido', '1', 'Ativar alertas de estoque excedido', 'booleano', 'estoque'],
        ['estoque_dias_antecedencia', '7', 'Dias de antecedência para alertas de vencimento', 'numero', 'estoque'],
        
        // Configurações de Relatórios
        ['relatorio_paginacao', '20', 'Itens por página nos relatórios', 'numero', 'relatorios'],
        ['relatorio_formato_padrao', 'pdf', 'Formato padrão dos relatórios', 'texto', 'relatorios'],
        ['relatorio_auto_gerar', '0', 'Gerar relatórios automaticamente', 'booleano', 'relatorios']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($configuracoes as $config) {
        $stmt->execute($config);
    }
    
    echo "✅ Configurações padrão inseridas com sucesso<br>";
    echo "<br><strong>Total de configurações:</strong> " . count($configuracoes) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}
?> 