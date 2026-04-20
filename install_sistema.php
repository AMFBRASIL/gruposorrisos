<?php
/**
 * Script de Instalação Automática - Grupo Sorrisos
 * Execute este arquivo para instalar o sistema completo
 */

// Configurações de conexão (ajuste conforme seu ambiente)
$config = [
    'host' => 'localhost',
    'dbname' => 'u460638534_sorrisos',
    'username' => 'u460638534_sorrisos',
    'password' => 'SuaSenhaAqui123!',
    'charset' => 'utf8mb4'
];

echo "<h1>🚀 Instalação Automática - Sistema Grupo Sorrisos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .step { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 4px solid #28a745; }
    .error { border-left: 4px solid #dc3545; }
    .info { border-left: 4px solid #17a2b8; }
    .warning { border-left: 4px solid #ffc107; }
    pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style>";

try {
    // =====================================================
    // PASSO 1: CONECTAR AO MYSQL
    // =====================================================
    echo "<div class='step info'>";
    echo "<h3>📡 Passo 1: Conectando ao MySQL...</h3>";
    
    $pdo = new PDO(
        "mysql:host={$config['host']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao MySQL com sucesso!<br>";
    echo "👤 Usuário: {$config['username']}<br>";
    echo "🏠 Host: {$config['host']}<br>";
    echo "</div>";
    
    // =====================================================
    // PASSO 2: CRIAR BANCO DE DADOS
    // =====================================================
    echo "<div class='step info'>";
    echo "<h3>🗄️ Passo 2: Criando banco de dados...</h3>";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados '{$config['dbname']}' criado/selecionado com sucesso!<br>";
    
    $pdo->exec("USE `{$config['dbname']}`");
    echo "✅ Banco de dados selecionado!<br>";
    echo "</div>";
    
    // =====================================================
    // PASSO 3: CRIAR TABELAS
    // =====================================================
    echo "<div class='step info'>";
    echo "<h3>🏗️ Passo 3: Criando tabelas...</h3>";
    
    // Array com todas as tabelas e suas estruturas
    $tabelas = [
        'tbl_perfis' => "
            CREATE TABLE IF NOT EXISTS `tbl_perfis` (
                `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
                `nome_perfil` varchar(100) NOT NULL,
                `descricao` text,
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_perfil`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_usuarios' => "
            CREATE TABLE IF NOT EXISTS `tbl_usuarios` (
                `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
                `nome_completo` varchar(200) NOT NULL,
                `email` varchar(150) NOT NULL UNIQUE,
                `senha` varchar(255) NOT NULL,
                `cpf` varchar(14) UNIQUE,
                `telefone` varchar(20),
                `id_perfil` int(11) NOT NULL,
                `id_filial` int(11),
                `ativo` tinyint(1) DEFAULT 1,
                `ultimo_acesso` timestamp NULL,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_usuario`),
                INDEX `idx_email` (`email`),
                INDEX `idx_perfil` (`id_perfil`),
                INDEX `idx_filial` (`id_filial`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_filiais' => "
            CREATE TABLE IF NOT EXISTS `tbl_filiais` (
                `id_filial` int(11) NOT NULL AUTO_INCREMENT,
                `nome_filial` varchar(200) NOT NULL,
                `codigo_filial` varchar(50) UNIQUE,
                `endereco` text,
                `cidade` varchar(100),
                `estado` varchar(2),
                `cep` varchar(10),
                `telefone` varchar(20),
                `email` varchar(150),
                `responsavel` varchar(200),
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_filial`),
                INDEX `idx_codigo` (`codigo_filial`),
                INDEX `idx_ativo` (`ativo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_paginas' => "
            CREATE TABLE IF NOT EXISTS `tbl_paginas` (
                `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
                `nome_pagina` varchar(200) NOT NULL,
                `url_pagina` varchar(200) NOT NULL UNIQUE,
                `categoria` varchar(100) NOT NULL,
                `descricao` text,
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_pagina`),
                INDEX `idx_url` (`url_pagina`),
                INDEX `idx_categoria` (`categoria`),
                INDEX `idx_ativo` (`ativo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_perfil_paginas' => "
            CREATE TABLE IF NOT EXISTS `tbl_perfil_paginas` (
                `id_perfil_pagina` int(11) NOT NULL AUTO_INCREMENT,
                `id_perfil` int(11) NOT NULL,
                `id_pagina` int(11) NOT NULL,
                `permissao_visualizar` tinyint(1) DEFAULT 0,
                `permissao_inserir` tinyint(1) DEFAULT 0,
                `permissao_editar` tinyint(1) DEFAULT 0,
                `permissao_excluir` tinyint(1) DEFAULT 0,
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_perfil_pagina`),
                UNIQUE KEY `uk_perfil_pagina` (`id_perfil`, `id_pagina`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_materiais' => "
            CREATE TABLE IF NOT EXISTS `tbl_materiais` (
                `id_material` int(11) NOT NULL AUTO_INCREMENT,
                `codigo` varchar(100) NOT NULL UNIQUE,
                `nome` varchar(200) NOT NULL,
                `descricao` text,
                `id_filial` int(11) NOT NULL,
                `id_unidade` int(11),
                `estoque_atual` decimal(10,2) DEFAULT 0,
                `estoque_minimo` decimal(10,2) DEFAULT 0,
                `estoque_maximo` decimal(10,2) DEFAULT 0,
                `preco_unitario` decimal(10,2) DEFAULT 0,
                `data_vencimento` date,
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_material`),
                INDEX `idx_codigo` (`codigo`),
                INDEX `idx_filial` (`id_filial`),
                INDEX `idx_vencimento` (`data_vencimento`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_unidades_medida' => "
            CREATE TABLE IF NOT EXISTS `tbl_unidades_medida` (
                `id_unidade` int(11) NOT NULL AUTO_INCREMENT,
                `sigla` varchar(10) NOT NULL UNIQUE,
                `nome` varchar(100) NOT NULL,
                `ativo` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id_unidade`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_inventario' => "
            CREATE TABLE IF NOT EXISTS `tbl_inventario` (
                `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
                `numero_inventario` varchar(50) NOT NULL UNIQUE,
                `id_filial` int(11) NOT NULL,
                `id_usuario_responsavel` int(11) NOT NULL,
                `status` enum('em_andamento','finalizado','cancelado') DEFAULT 'em_andamento',
                `observacoes` text,
                `data_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_fim` timestamp NULL,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_inventario`),
                INDEX `idx_numero` (`numero_inventario`),
                INDEX `idx_filial` (`id_filial`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_itens_inventario' => "
            CREATE TABLE IF NOT EXISTS `tbl_itens_inventario` (
                `id_item_inventario` int(11) NOT NULL AUTO_INCREMENT,
                `id_inventario` int(11) NOT NULL,
                `id_material` int(11) NOT NULL,
                `quantidade_sistema` decimal(10,2) NOT NULL,
                `quantidade_contada` decimal(10,2),
                `valor_unitario` decimal(10,2) NOT NULL,
                `valor_total_sistema` decimal(10,2) NOT NULL,
                `valor_total_contado` decimal(10,2),
                `status_item` enum('pendente','contado','divergente','ajustado') DEFAULT 'pendente',
                `observacoes` text,
                `id_usuario_contador` int(11),
                `data_contagem` timestamp NULL,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_item_inventario`),
                INDEX `idx_inventario` (`id_inventario`),
                INDEX `idx_material` (`id_material`),
                INDEX `idx_status` (`status_item`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_fornecedores' => "
            CREATE TABLE IF NOT EXISTS `tbl_fornecedores` (
                `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT,
                `razao_social` varchar(200) NOT NULL,
                `nome_fantasia` varchar(200),
                `cnpj` varchar(18) UNIQUE,
                `email` varchar(150) NOT NULL,
                `telefone` varchar(20),
                `endereco` text,
                `cidade` varchar(100),
                `estado` varchar(2),
                `cep` varchar(10),
                `responsavel` varchar(200),
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_fornecedor`),
                INDEX `idx_cnpj` (`cnpj`),
                INDEX `idx_email` (`email`),
                INDEX `idx_ativo` (`ativo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_pedidos_compra' => "
            CREATE TABLE IF NOT EXISTS `tbl_pedidos_compra` (
                `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
                `numero_pedido` varchar(50) NOT NULL UNIQUE,
                `id_fornecedor` int(11) NOT NULL,
                `id_usuario_solicitante` int(11) NOT NULL,
                `id_filial` int(11) NOT NULL,
                `status` enum('pendente','aprovado','em_producao','enviado','recebido','cancelado') DEFAULT 'pendente',
                `data_solicitacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_entrega_prevista` date,
                `data_entrega_real` date,
                `valor_total` decimal(10,2) DEFAULT 0,
                `observacoes` text,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_pedido`),
                INDEX `idx_numero` (`numero_pedido`),
                INDEX `idx_fornecedor` (`id_fornecedor`),
                INDEX `idx_status` (`status`),
                INDEX `idx_filial` (`id_filial`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_itens_pedido' => "
            CREATE TABLE IF NOT EXISTS `tbl_itens_pedido` (
                `id_item_pedido` int(11) NOT NULL AUTO_INCREMENT,
                `id_pedido` int(11) NOT NULL,
                `id_material` int(11) NOT NULL,
                `quantidade` decimal(10,2) NOT NULL,
                `preco_unitario` decimal(10,2) NOT NULL,
                `valor_total` decimal(10,2) NOT NULL,
                `observacoes` text,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_item_pedido`),
                INDEX `idx_pedido` (`id_pedido`),
                INDEX `idx_material` (`id_material`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_configuracoes' => "
            CREATE TABLE IF NOT EXISTS `tbl_configuracoes` (
                `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
                `chave` varchar(100) NOT NULL UNIQUE,
                `valor` text,
                `descricao` text,
                `tipo` enum('texto','numero','booleano','json','email','telefone','moeda','fuso_horario') DEFAULT 'texto',
                `categoria` varchar(50) DEFAULT 'geral',
                `ativo` tinyint(1) DEFAULT 1,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_configuracao`),
                INDEX `idx_chave` (`chave`),
                INDEX `idx_categoria` (`categoria`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tbl_logs_sistema' => "
            CREATE TABLE IF NOT EXISTS `tbl_logs_sistema` (
                `id_log` int(11) NOT NULL AUTO_INCREMENT,
                `id_usuario` int(11),
                `id_filial` int(11),
                `acao` varchar(100) NOT NULL,
                `tabela` varchar(100),
                `id_registro` int(11),
                `dados_novos` text,
                `dados_anteriores` text,
                `ip_usuario` varchar(45),
                `user_agent` text,
                `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_log`),
                INDEX `idx_usuario` (`id_usuario`),
                INDEX `idx_acao` (`acao`),
                INDEX `idx_data` (`data_criacao`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    $tabelasCriadas = 0;
    foreach ($tabelas as $nomeTabela => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Tabela <strong>{$nomeTabela}</strong> criada com sucesso!<br>";
            $tabelasCriadas++;
        } catch (Exception $e) {
            echo "⚠️ Tabela <strong>{$nomeTabela}</strong> já existe ou erro: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br>📊 <strong>Total de tabelas criadas:</strong> {$tabelasCriadas}<br>";
    echo "</div>";
    
    // =====================================================
    // PASSO 4: INSERIR DADOS INICIAIS
    // =====================================================
    echo "<div class='step info'>";
    echo "<h3>📝 Passo 4: Inserindo dados iniciais...</h3>";
    
    // Inserir perfis
    $perfis = [
        [1, 'Administrador', 'Acesso total ao sistema'],
        [2, 'Gerente', 'Gerencia filiais e usuários'],
        [3, 'Operador', 'Operações de estoque e compras'],
        [4, 'Visualizador', 'Apenas visualização de dados'),
        [5, 'Fornecedor', 'Acesso para fornecedores']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfis (id_perfil, nome_perfil, descricao) VALUES (?, ?, ?)");
    foreach ($perfis as $perfil) {
        $stmt->execute($perfil);
    }
    echo "✅ Perfis inseridos com sucesso!<br>";
    
    // Inserir unidades de medida
    $unidades = [
        [1, 'UN', 'Unidade'],
        [2, 'KG', 'Quilograma'],
        [3, 'L', 'Litro'],
        [4, 'M', 'Metro'],
        [5, 'M²', 'Metro Quadrado'],
        [6, 'CX', 'Caixa'],
        [7, 'PCT', 'Pacote']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_unidades_medida (id_unidade, sigla, nome) VALUES (?, ?, ?)");
    foreach ($unidades as $unidade) {
        $stmt->execute($unidade);
    }
    echo "✅ Unidades de medida inseridas com sucesso!<br>";
    
    // Inserir filiais
    $filiais = [
        [1, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS PETROLINA LTDA', 'PETROLINA', 'Petrolina', 'PE'],
        [2, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS LTDA', 'SORRISOS', 'Recife', 'PE'],
        [3, 'CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS GARANHUNS LTDA', 'GARANHUNS', 'Garanhuns', 'PE'],
        [4, 'CENTRO ODONTOLOGICO PERNAMBUCO ARCOVERDE LTDA', 'ARCOVERDE', 'Arcoverde', 'PE']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_filiais (id_filial, nome_filial, codigo_filial, cidade, estado) VALUES (?, ?, ?, ?, ?)");
    foreach ($filiais as $filial) {
        $stmt->execute($filial);
    }
    echo "✅ Filiais inseridas com sucesso!<br>";
    
    // Inserir usuário administrador
    $senhaHash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_usuarios (id_usuario, nome_completo, email, senha, id_perfil, id_filial, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 'Administrador', 'admin@gruposorrisos.com', $senhaHash, 1, 1, 1]);
    echo "✅ Usuário administrador criado com sucesso!<br>";
    
    // Inserir páginas
    $paginas = [
        [1, 'Dashboard', 'index.php', 'gestao', 'Página principal do sistema'],
        [2, 'Usuários', 'usuarios.php', 'gestao', 'Gestão de usuários do sistema'],
        [3, 'Perfil de Acesso', 'perfil-acesso.php', 'gestao', 'Configuração de perfis e permissões'],
        [4, 'Filiais/Clínicas', 'filiais.php', 'gestao', 'Gestão de filiais e clínicas'],
        [5, 'Materiais', 'material.php', 'estoque', 'Gestão de materiais e estoque'],
        [6, 'Movimentações', 'movimentacoes.php', 'estoque', 'Controle de movimentações de estoque'],
        [7, 'Alertas', 'alertas.php', 'estoque', 'Sistema de alertas de estoque'],
        [8, 'Inventário', 'inventario.php', 'estoque', 'Controle de inventários'],
        [9, 'Pedidos de Compra Interno', 'pedidos-compra.php', 'compras', 'Pedidos de compra internos'],
        [10, 'Fornecedores', 'fornecedores.php', 'compras', 'Gestão de fornecedores'],
        [11, 'Relatórios', 'relatorios.php', 'relatorios', 'Geração de relatórios'),
        [12, 'Tickets', 'tickets.php', 'relatorios', 'Sistema de tickets'),
        [13, 'Configurações', 'configuracoes.php', 'configuracoes', 'Configurações do sistema'),
        [14, 'Módulos do Sistema', 'paginas.php', 'gestao', 'Gestão de módulos do sistema'),
        [15, 'Pedidos Compra Fornecedor', 'pedidos-fornecedores.php', 'compras', 'Pedidos para fornecedores')
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_paginas (id_pagina, nome_pagina, url_pagina, categoria, descricao) VALUES (?, ?, ?, ?, ?)");
    foreach ($paginas as $pagina) {
        $stmt->execute($pagina);
    }
    echo "✅ Páginas do sistema inseridas com sucesso!<br>";
    
    // Inserir configurações
    $configuracoes = [
        ['empresa_nome', 'Grupo Sorrisos Ltda', 'Nome da empresa', 'texto', 'empresa'],
        ['empresa_email', 'contato@gruposorrisos.com', 'E-mail principal da empresa', 'email', 'empresa'],
        ['empresa_telefone', '(11) 99999-9999', 'Telefone principal da empresa', 'telefone', 'empresa'],
        ['empresa_moeda', 'BRL', 'Moeda padrão do sistema', 'moeda', 'empresa'],
        ['empresa_fuso', 'America/Sao_Paulo', 'Fuso horário padrão', 'fuso_horario', 'empresa'],
        ['notifica_email', '1', 'Ativar notificações por e-mail', 'booleano', 'notificacoes'],
        ['notifica_pagamentos', '1', 'Notificar sobre pagamentos realizados', 'booleano', 'notificacoes'],
        ['notifica_vencimentos', '1', 'Alertas de contas próximas ao vencimento', 'booleano', 'notificacoes'),
        ['notifica_relatorios', '0', 'Envio automático de relatórios mensais', 'booleano', 'notificacoes'),
        ['backup_automatico', '1', 'Ativar backup automático', 'booleano', 'sistema'),
        ['backup_intervalo', 'diario', 'Intervalo do backup (diario, semanal, mensal)', 'texto', 'sistema'),
        ['backup_historico', '12', 'Manter histórico de backup em meses', 'numero', 'sistema'),
        ['seguranca_2fa', '0', 'Ativar autenticação em duas etapas', 'booleano', 'seguranca'),
        ['sessao_expira', '30', 'Tempo de expiração da sessão em minutos', 'numero', 'seguranca'),
        ['log_auditoria', '1', 'Ativar log de auditoria', 'booleano', 'seguranca'),
        ['estoque_alerta_baixo', '1', 'Ativar alertas de estoque baixo', 'booleano', 'estoque'),
        ['estoque_alerta_zerado', '1', 'Ativar alertas de estoque zerado', 'booleano', 'estoque'),
        ['estoque_alerta_excedido', '1', 'Ativar alertas de estoque excedido', 'booleano', 'estoque'),
        ['estoque_dias_antecedencia', '7', 'Dias de antecedência para alertas de vencimento', 'numero', 'estoque'),
        ['relatorio_paginacao', '20', 'Itens por página nos relatórios', 'numero', 'relatorios'),
        ['relatorio_formato_padrao', 'pdf', 'Formato padrão dos relatórios', 'texto', 'relatorios'),
        ['relatorio_auto_gerar', '0', 'Gerar relatórios automaticamente', 'booleano', 'relatorios')
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria) VALUES (?, ?, ?, ?, ?)");
    foreach ($configuracoes as $config) {
        $stmt->execute($config);
    }
    echo "✅ Configurações do sistema inseridas com sucesso!<br>";
    
    echo "</div>";
    
    // =====================================================
    // PASSO 5: CONFIGURAR PERMISSÕES
    // =====================================================
    echo "<div class='step info'>";
    echo "<h3>🔐 Passo 5: Configurando permissões...</h3>";
    
    // Buscar todas as páginas
    $stmt = $pdo->query("SELECT id_pagina FROM tbl_paginas");
    $paginasIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Administrador: acesso total
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir) VALUES (?, ?, 1, 1, 1, 1)");
    foreach ($paginasIds as $idPagina) {
        $stmt->execute([1, $idPagina]);
    }
    echo "✅ Permissões do Administrador configuradas!<br>";
    
    // Gerente: gestão, estoque e compras
    $stmt = $pdo->query("SELECT id_pagina FROM tbl_paginas WHERE categoria IN ('gestao', 'estoque', 'compras')");
    $paginasGerente = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir) VALUES (?, ?, 1, 1, 1, 1)");
    foreach ($paginasGerente as $idPagina) {
        $stmt->execute([2, $idPagina]);
    }
    echo "✅ Permissões do Gerente configuradas!<br>";
    
    // Operador: estoque e compras
    $stmt = $pdo->query("SELECT id_pagina FROM tbl_paginas WHERE categoria IN ('estoque', 'compras')");
    $paginasOperador = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir) VALUES (?, ?, 1, 1, 1, 0)");
    foreach ($paginasOperador as $idPagina) {
        $stmt->execute([3, $idPagina]);
    }
    echo "✅ Permissões do Operador configuradas!<br>";
    
    // Visualizador: apenas visualização
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir) VALUES (?, ?, 1, 0, 0, 0)");
    foreach ($paginasIds as $idPagina) {
        $stmt->execute([4, $idPagina]);
    }
    echo "✅ Permissões do Visualizador configuradas!<br>";
    
    // Fornecedor: apenas página específica
    $stmt = $pdo->query("SELECT id_pagina FROM tbl_paginas WHERE url_pagina = 'pedidos-fornecedores.php'");
    $paginaFornecedor = $stmt->fetchColumn();
    
    if ($paginaFornecedor) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir) VALUES (?, ?, 1, 1, 1, 1)");
        $stmt->execute([5, $paginaFornecedor]);
        echo "✅ Permissões do Fornecedor configuradas!<br>";
    }
    
    echo "</div>";
    
    // =====================================================
    // PASSO 6: VERIFICAÇÃO FINAL
    // =====================================================
    echo "<div class='step success'>";
    echo "<h3>🎉 Passo 6: Verificação final da instalação...</h3>";
    
    // Contar registros
    $tabelas = ['tbl_perfis', 'tbl_usuarios', 'tbl_filiais', 'tbl_paginas', 'tbl_configuracoes'];
    foreach ($tabelas as $tabela) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tabela}");
        $total = $stmt->fetchColumn();
        echo "📊 <strong>{$tabela}:</strong> {$total} registros<br>";
    }
    
    // Verificar usuário administrador
    $stmt = $pdo->query("SELECT u.nome_completo, u.email, p.nome_perfil FROM tbl_usuarios u JOIN tbl_perfis p ON u.id_perfil = p.id_perfil WHERE u.id_usuario = 1");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<br>👤 <strong>Usuário Administrador:</strong><br>";
        echo "Nome: {$admin['nome_completo']}<br>";
        echo "Email: {$admin['email']}<br>";
        echo "Perfil: {$admin['nome_perfil']}<br>";
        echo "Senha: <strong>password</strong> (altere após o primeiro login!)<br>";
    }
    
    echo "<br>✅ <strong>INSTALAÇÃO CONCLUÍDA COM SUCESSO!</strong><br>";
    echo "</div>";
    
    // =====================================================
    // INFORMAÇÕES IMPORTANTES
    // =====================================================
    echo "<div class='step warning'>";
    echo "<h3>⚠️ INFORMAÇÕES IMPORTANTES:</h3>";
    echo "<ul>";
    echo "<li><strong>Altere a senha do administrador</strong> após o primeiro login!</li>";
    echo "<li>Configure as <strong>configurações de email</strong> em configuracoes.php</li>";
    echo "<li>Verifique as <strong>permissões</strong> dos usuários</li>";
    echo "<li>Configure as <strong>filiais</strong> conforme necessário</li>";
    echo "<li>Faça <strong>backup regular</strong> do banco de dados</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h3>❌ ERRO NA INSTALAÇÃO:</h3>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>

<div class="step info">
    <h3>📋 PRÓXIMOS PASSOS:</h3>
    <ol>
        <li><strong>Teste o login</strong> com admin@gruposorrisos.com / password</li>
        <li><strong>Altere a senha</strong> do administrador</li>
        <li><strong>Configure as filiais</strong> conforme sua necessidade</li>
        <li><strong>Adicione usuários</strong> para sua equipe</li>
        <li><strong>Configure permissões</strong> específicas se necessário</li>
    </ol>
</div> 