<?php
// Script de instalação do Sistema de Controle de Estoque

// Configurações do banco
$host = 'localhost';
$username = 'root';
$password = '';
$database_name = 'estoque_sistema';

echo "<h2>Instalação do Sistema de Controle de Estoque</h2>";

try {
    // Conectar ao MySQL sem selecionar banco
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Conexão com MySQL estabelecida</p>";
    
    // Criar banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Banco de dados '$database_name' criado</p>";
    
    // Selecionar o banco
    $pdo->exec("USE `$database_name`");
    
    // Ler e executar o schema SQL
    $schema = file_get_contents('database/schema.sql');
    
    // Remover a linha de criação do banco e USE
    $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
    $schema = preg_replace('/USE.*?;/s', '', $schema);
    
    // Executar as queries
    $queries = explode(';', $schema);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    
    echo "<p>✓ Tabelas criadas com sucesso</p>";
    
    // Criar diretórios necessários
    $directories = ['uploads', 'backups', 'logs'];
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "<p>✓ Diretório '$dir' criado</p>";
        }
    }
    
    // Verificar se o usuário admin já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@sistema.com']);
    
    if ($stmt->fetchColumn() == 0) {
        // Criar usuário admin se não existir
        $senha_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (empresa_id, perfil_id, nome, email, senha, cpf, ativo) VALUES (1, 1, 'Administrador', 'admin@sistema.com', ?, '123.456.789-00', 1)");
        $stmt->execute([$senha_hash]);
        echo "<p>✓ Usuário administrador criado (admin@sistema.com / password)</p>";
    }
    
    // Inserir dados de exemplo
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fornecedores WHERE empresa_id = 1");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        // Inserir fornecedores de exemplo
        $fornecedores = [
            ['Fornecedor ABC Ltda', 'ABC Ltda', '12.345.678/0001-01', 'São Paulo', 'SP'],
            ['Fornecedor XYZ S.A.', 'XYZ S.A.', '98.765.432/0001-02', 'Rio de Janeiro', 'RJ'],
            ['Distribuidora Central', 'Dist Central', '11.222.333/0001-03', 'Belo Horizonte', 'MG']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO fornecedores (empresa_id, razao_social, nome_fantasia, cnpj, cidade, estado) VALUES (1, ?, ?, ?, ?, ?)");
        foreach ($fornecedores as $fornecedor) {
            $stmt->execute($fornecedor);
        }
        echo "<p>✓ Fornecedores de exemplo criados</p>";
    }
    
    // Inserir produtos de exemplo
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE empresa_id = 1");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        // Inserir produtos de exemplo
        $produtos = [
            ['Notebook Dell Inspiron', 'Notebook Dell Inspiron 15 polegadas, 8GB RAM, 256GB SSD', 'SKU001', '7891234567890', 2500.00, 3200.00, 5, 10, 1, 1],
            ['Mouse Wireless Logitech', 'Mouse sem fio Logitech M185, preto', 'SKU002', '7891234567891', 25.00, 45.00, 20, 50, 1, 1],
            ['Teclado Mecânico RGB', 'Teclado mecânico com RGB, switches blue', 'SKU003', '7891234567892', 150.00, 280.00, 8, 15, 1, 1],
            ['Monitor LG 24"', 'Monitor LG 24 polegadas, Full HD, HDMI', 'SKU004', '7891234567893', 400.00, 650.00, 3, 8, 1, 1],
            ['Webcam HD 1080p', 'Webcam HD 1080p com microfone integrado', 'SKU005', '7891234567894', 80.00, 120.00, 10, 25, 1, 1]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO produtos (empresa_id, nome, descricao, sku, codigo_barras, preco_custo, preco_venda, estoque_minimo, estoque_atual, categoria_id, fornecedor_id) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($produtos as $produto) {
            $stmt->execute($produto);
        }
        echo "<p>✓ Produtos de exemplo criados</p>";
    }
    
    echo "<h3 style='color: green;'>✓ Instalação concluída com sucesso!</h3>";
    echo "<p><strong>Credenciais de acesso:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@sistema.com</li>";
    echo "<li><strong>Senha:</strong> password</li>";
    echo "</ul>";
    echo "<p><a href='login.php' class='btn btn-primary'>Ir para o Login</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>✗ Erro na instalação:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Verifique se:</p>";
    echo "<ul>";
    echo "<li>O MySQL está rodando</li>";
    echo "<li>As credenciais estão corretas</li>";
    echo "<li>O usuário tem permissões para criar bancos de dados</li>";
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- O conteúdo PHP será exibido aqui -->
    </div>
</body>
</html> 