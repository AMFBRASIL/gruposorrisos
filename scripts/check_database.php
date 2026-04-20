<?php
require_once '../config/config.php';
require_once '../config/conexao.php';

echo "<h2>Verificação do Banco de Dados</h2>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Lista de tabelas necessárias
    $tabelas = [
        'tbl_usuarios' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nome' => 'VARCHAR(255) NOT NULL',
            'email' => 'VARCHAR(255) UNIQUE NOT NULL',
            'senha' => 'VARCHAR(255) NOT NULL',
            'id_perfil' => 'INT',
            'id_filial' => 'INT',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'tbl_perfis' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nome' => 'VARCHAR(100) NOT NULL',
            'descricao' => 'TEXT',
            'ativo' => 'BOOLEAN DEFAULT TRUE'
        ],
        'tbl_produtos' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nome' => 'VARCHAR(255) NOT NULL',
            'descricao' => 'TEXT',
            'codigo' => 'VARCHAR(50) UNIQUE',
            'id_categoria' => 'INT',
            'id_filial' => 'INT',
            'estoque_atual' => 'DECIMAL(10,2) DEFAULT 0',
            'estoque_minimo' => 'DECIMAL(10,2) DEFAULT 0',
            'preco_custo' => 'DECIMAL(10,2) DEFAULT 0',
            'preco_venda' => 'DECIMAL(10,2) DEFAULT 0',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'tbl_categorias' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nome' => 'VARCHAR(100) NOT NULL',
            'descricao' => 'TEXT',
            'ativo' => 'BOOLEAN DEFAULT TRUE'
        ],
        'tbl_logs_sistema' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'id_usuario' => 'INT',
            'id_filial' => 'INT',
            'acao' => 'VARCHAR(100) NOT NULL',
            'tabela' => 'VARCHAR(100)',
            'id_registro' => 'INT',
            'dados_novos' => 'TEXT',
            'ip_usuario' => 'VARCHAR(45)',
            'data_hora' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ];
    
    foreach ($tabelas as $tabela => $colunas) {
        echo "<h3>Verificando tabela: $tabela</h3>";
        
        // Verificar se a tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        $existe = $stmt->rowCount() > 0;
        
        if ($existe) {
            echo "✅ Tabela <strong>$tabela</strong> existe<br>";
        } else {
            echo "❌ Tabela <strong>$tabela</strong> não existe - Criando...<br>";
            
            // Criar tabela
            $sql = "CREATE TABLE $tabela (";
            $colunas_sql = [];
            foreach ($colunas as $coluna => $tipo) {
                $colunas_sql[] = "$coluna $tipo";
            }
            $sql .= implode(', ', $colunas_sql);
            $sql .= ")";
            
            try {
                $pdo->exec($sql);
                echo "✅ Tabela <strong>$tabela</strong> criada com sucesso<br>";
            } catch (Exception $e) {
                echo "❌ Erro ao criar tabela <strong>$tabela</strong>: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // Verificar se há dados nas tabelas principais
    echo "<h3>Verificando dados nas tabelas</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_perfis");
    $total_perfis = $stmt->fetch()['total'];
    echo "Perfis cadastrados: $total_perfis<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_usuarios");
    $total_usuarios = $stmt->fetch()['total'];
    echo "Usuários cadastrados: $total_usuarios<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_produtos");
    $total_produtos = $stmt->fetch()['total'];
    echo "Produtos cadastrados: $total_produtos<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_categorias");
    $total_categorias = $stmt->fetch()['total'];
    echo "Categorias cadastradas: $total_categorias<br>";
    
    echo "<h3>Status do Sistema</h3>";
    if ($total_perfis > 0 && $total_usuarios > 0) {
        echo "✅ Sistema pronto para uso!<br>";
        echo "🔑 Credenciais padrão: admin@sistema.com / password<br>";
    } else {
        echo "⚠️ Sistema precisa ser inicializado<br>";
        echo "<a href='init_system.php'>Clique aqui para inicializar o sistema</a><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão com o banco: " . $e->getMessage();
}
?> 