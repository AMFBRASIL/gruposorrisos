<?php
require_once '../config/config.php';
require_once '../config/conexao.php';

echo "<h2>Criação das Tabelas do Sistema</h2>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Lista de tabelas necessárias
    $tabelas = [
        'tbl_categorias' => [
            'id_categoria' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nome_categoria' => 'VARCHAR(100) NOT NULL',
            'descricao' => 'TEXT',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'tbl_fornecedores' => [
            'id_fornecedor' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'razao_social' => 'VARCHAR(255) NOT NULL',
            'nome_fantasia' => 'VARCHAR(255)',
            'cnpj' => 'VARCHAR(18)',
            'inscricao_estadual' => 'VARCHAR(20)',
            'endereco' => 'TEXT',
            'cidade' => 'VARCHAR(100)',
            'estado' => 'VARCHAR(2)',
            'cep' => 'VARCHAR(10)',
            'telefone' => 'VARCHAR(20)',
            'email' => 'VARCHAR(255)',
            'contato' => 'VARCHAR(100)',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'tbl_unidades_medida' => [
            'id_unidade' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'sigla' => 'VARCHAR(10) NOT NULL UNIQUE',
            'nome' => 'VARCHAR(50) NOT NULL',
            'descricao' => 'TEXT',
            'ativo' => 'BOOLEAN DEFAULT TRUE'
        ],
        'tbl_filiais' => [
            'id_filial' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'codigo' => 'VARCHAR(10) NOT NULL UNIQUE',
            'nome_filial' => 'VARCHAR(255) NOT NULL',
            'endereco' => 'TEXT',
            'cidade' => 'VARCHAR(100)',
            'estado' => 'VARCHAR(2)',
            'telefone' => 'VARCHAR(20)',
            'email' => 'VARCHAR(255)',
            'responsavel' => 'VARCHAR(100)',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'tbl_materiais' => [
            'id_material' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'codigo' => 'VARCHAR(50) NOT NULL',
            'codigo_barras' => 'VARCHAR(50)',
            'nome' => 'VARCHAR(255) NOT NULL',
            'descricao' => 'TEXT',
            'marca' => 'VARCHAR(100)',
            'id_categoria' => 'INT',
            'id_fornecedor' => 'INT',
            'id_unidade' => 'INT',
            'id_filial' => 'INT',
            'preco_custo' => 'DECIMAL(10,2) DEFAULT 0',
            'preco_venda' => 'DECIMAL(10,2) DEFAULT 0',
            'localizacao' => 'VARCHAR(100)',
            'estoque_atual' => 'DECIMAL(10,3) DEFAULT 0',
            'estoque_minimo' => 'DECIMAL(10,3) DEFAULT 0',
            'estoque_maximo' => 'DECIMAL(10,3) DEFAULT 0',
            'observacoes' => 'TEXT',
            'ativo' => 'BOOLEAN DEFAULT TRUE',
            'data_criacao' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'data_atualizacao' => 'TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP',
            'INDEX idx_codigo_filial (codigo, id_filial)',
            'INDEX idx_categoria (id_categoria)',
            'INDEX idx_fornecedor (id_fornecedor)',
            'INDEX idx_filial (id_filial)'
        ]
    ];
    
    foreach ($tabelas as $tabela => $colunas) {
        echo "<h3>Criando tabela: $tabela</h3>";
        
        // Verificar se a tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        $existe = $stmt->rowCount() > 0;
        
        if ($existe) {
            echo "✅ Tabela <strong>$tabela</strong> já existe<br>";
        } else {
            echo "❌ Tabela <strong>$tabela</strong> não existe - Criando...<br>";
            
            // Criar tabela
            $sql = "CREATE TABLE $tabela (";
            $colunas_sql = [];
            foreach ($colunas as $coluna => $tipo) {
                if (strpos($tipo, 'INDEX') === 0) {
                    // É um índice
                    $colunas_sql[] = $tipo;
                } else {
                    $colunas_sql[] = "$coluna $tipo";
                }
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
    
    // Inserir dados padrão
    echo "<h3>Inserindo dados padrão</h3>";
    
    // Unidades de medida
    $unidades = [
        ['UN', 'Unidade'],
        ['KG', 'Quilograma'],
        ['M', 'Metro'],
        ['M²', 'Metro Quadrado'],
        ['M³', 'Metro Cúbico'],
        ['L', 'Litros'],
        ['CX', 'Caixa'],
        ['PCT', 'Pacote'],
        ['ROL', 'Rolo'],
        ['PAR', 'Par']
    ];
    
    foreach ($unidades as $unidade) {
        $sql = "INSERT IGNORE INTO tbl_unidades_medida (sigla, nome) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($unidade);
    }
    echo "✅ Unidades de medida inseridas<br>";
    
    // Categorias padrão
    $categorias = [
        ['Cimento', 'Materiais de construção - Cimento'],
        ['Areia', 'Materiais de construção - Areia'],
        ['Brita', 'Materiais de construção - Brita'],
        ['Tijolo', 'Materiais de construção - Tijolos'],
        ['Ferro', 'Materiais de construção - Ferro'],
        ['Madeira', 'Materiais de construção - Madeira'],
        ['Tinta', 'Materiais de acabamento - Tintas'],
        ['Argamassa', 'Materiais de acabamento - Argamassa'],
        ['Rejunte', 'Materiais de acabamento - Rejunte'],
        ['Ferramentas', 'Ferramentas e equipamentos']
    ];
    
    foreach ($categorias as $categoria) {
        $sql = "INSERT IGNORE INTO tbl_categorias (nome_categoria, descricao) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($categoria);
    }
    echo "✅ Categorias padrão inseridas<br>";
    
    // Filial padrão
    $sql = "INSERT IGNORE INTO tbl_filiais (codigo, nome_filial, cidade, estado) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['001', 'Filial Principal', 'São Paulo', 'SP']);
    echo "✅ Filial padrão inserida<br>";
    
    // Fornecedores de exemplo
    $fornecedores = [
        ['Construção ABC Ltda', 'Construção ABC', '12.345.678/0001-90', 'São Paulo', 'SP'],
        ['Materiais Silva Ltda', 'Materiais Silva', '98.765.432/0001-10', 'Rio de Janeiro', 'RJ'],
        ['Ferro e Aço Ltda', 'Ferro e Aço', '11.222.333/0001-44', 'Belo Horizonte', 'MG']
    ];
    
    foreach ($fornecedores as $fornecedor) {
        $sql = "INSERT IGNORE INTO tbl_fornecedores (razao_social, nome_fantasia, cnpj, cidade, estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($fornecedor);
    }
    echo "✅ Fornecedores de exemplo inseridos<br>";
    
    // Materiais de exemplo
    $materiais = [
        ['CIM-001', 'Cimento Portland CPII-Z-32', 'Cimento Portland CPII-Z-32 para construção', 1, 1, 1, 1, 23.50, 25.90, 'Prateleira A-01', 150, 20, 200, 'Votorantim'],
        ['TIJ-001', 'Tijolo Comum 6 furos', 'Tijolo comum com 6 furos para alvenaria', 4, 2, 1, 1, 380.00, 420.00, 'Prateleira B-02', 8, 100, 1000, 'Cerâmica Real'],
        ['FER-10', 'Ferro CA-50 10mm', 'Ferro vergalhão CA-50 10mm', 5, 3, 1, 1, 26.80, 28.50, 'Prateleira C-03', 0, 15, 100, 'Gerdau'],
        ['ARE-FIN', 'Areia Fina Lavada', 'Areia fina lavada para construção', 2, 1, 1, 1, 32.00, 35.00, 'Prateleira D-04', 45, 10, 100, 'Pedreira Central']
    ];
    
    foreach ($materiais as $material) {
        $sql = "INSERT IGNORE INTO tbl_materiais (codigo, nome, descricao, id_categoria, id_fornecedor, id_unidade, id_filial, preco_custo, preco_venda, localizacao, estoque_atual, estoque_minimo, estoque_maximo, marca) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($material);
    }
    echo "✅ Materiais de exemplo inseridos<br>";
    
    echo "<h3>Status do Sistema</h3>";
    echo "✅ Todas as tabelas foram criadas com sucesso!<br>";
    echo "✅ Dados padrão inseridos<br>";
    echo "🔑 Você pode agora usar o sistema de materiais<br>";
    echo "<a href='../material.php'>Clique aqui para acessar a página de materiais</a><br>";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão com o banco: " . $e->getMessage();
}
?> 