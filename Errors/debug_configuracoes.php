<?php
require_once 'config/config.php';
require_once 'config/conexao.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    
    echo "<h1>🔍 Debug - Tabela de Configurações</h1>";
    
    // 1. Verificar se a tabela existe
    echo "<h2>1. Estrutura da Tabela:</h2>";
    $stmt = $pdo->query("DESCRIBE tbl_configuracoes");
    $colunas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>{$coluna['Field']}</td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar total de configurações
    echo "<h2>2. Total de Configurações:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_configuracoes");
    $total = $stmt->fetch()['total'];
    echo "<p>Total de configurações: <strong>{$total}</strong></p>";
    
    // 3. Verificar configurações por categoria
    echo "<h2>3. Configurações por Categoria:</h2>";
    $stmt = $pdo->query("SELECT categoria, COUNT(*) as qtd FROM tbl_configuracoes GROUP BY categoria ORDER BY categoria");
    $categorias = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Categoria</th><th>Quantidade</th></tr>";
    foreach ($categorias as $cat) {
        echo "<tr>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>{$cat['qtd']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Verificar algumas configurações de exemplo
    echo "<h2>4. Exemplos de Configurações:</h2>";
    $stmt = $pdo->query("SELECT chave, valor, tipo, categoria FROM tbl_configuracoes LIMIT 10");
    $configs = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Chave</th><th>Valor</th><th>Tipo</th><th>Categoria</th></tr>";
    foreach ($configs as $config) {
        echo "<tr>";
        echo "<td>{$config['chave']}</td>";
        echo "<td>{$config['valor']}</td>";
        echo "<td>{$config['tipo']}</td>";
        echo "<td>{$config['categoria']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Testar API
    echo "<h2>5. Teste da API:</h2>";
    try {
        require_once 'models/Configuracao.php';
        $configuracao = new Configuracao($pdo);
        
        // Testar buscar todas
        $todas = $configuracao->buscarTodas();
        echo "<p>✅ Modelo funcionando: <strong>" . count($todas) . "</strong> configurações encontradas</p>";
        
        // Testar buscar por categoria
        $empresa = $configuracao->buscarPorCategoria('empresa');
        echo "<p>✅ Categoria 'empresa': <strong>" . count($empresa) . "</strong> configurações</p>";
        
        // Testar buscar por chave
        $nome = $configuracao->buscarPorChave('empresa_nome');
        echo "<p>✅ Chave 'empresa_nome': <strong>{$nome['valor']}</strong></p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Erro no modelo: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
table { margin: 10px 0; border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background: #f8f9fa; }
</style> 