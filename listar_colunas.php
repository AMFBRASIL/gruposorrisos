<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Colunas da Tabela tbl_filiais</h1>";

try {
    // Configurações do banco
    $host = 'localhost';
    $dbname = 'gruposorrisos';
    $user = 'root';
    $pass = '';
    
    // Criar conexão PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<h2>Colunas da tabela:</h2>";
    
    $stmt = $pdo->query("DESCRIBE tbl_filiais");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($colunas as $coluna) {
        echo "<li><strong>" . $coluna['Field'] . "</strong> - " . $coluna['Type'] . "</li>";
    }
    echo "</ul>";
    
    echo "<h2>Primeiro registro (para ver os dados):</h2>";
    
    $stmt = $pdo->query("SELECT * FROM tbl_filiais LIMIT 1");
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registro) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Coluna</th><th>Valor</th></tr>";
        foreach ($registro as $coluna => $valor) {
            echo "<tr>";
            echo "<td>" . $coluna . "</td>";
            echo "<td>" . ($valor ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum registro encontrado.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
}
?> 