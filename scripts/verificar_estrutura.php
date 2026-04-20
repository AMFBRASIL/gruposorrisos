<?php
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "🔍 Verificando estrutura da tabela tbl_materiais...\n\n";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_materiais'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela tbl_materiais não encontrada!\n";
        exit;
    }
    
    echo "✅ Tabela tbl_materiais encontrada\n\n";
    
    // Mostrar estrutura da tabela
    echo "📋 ESTRUTURA DA TABELA:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("DESCRIBE tbl_materiais");
    while ($row = $stmt->fetch()) {
        echo sprintf("%-20s %-20s %-10s %-10s %-10s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL'
        );
    }
    
    echo "\n📊 DADOS DA TABELA:\n";
    echo str_repeat("-", 50) . "\n";
    
    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_materiais");
    $total = $stmt->fetch()['total'];
    echo "Total de registros: $total\n";
    
    // Mostrar alguns registros de exemplo
    $stmt = $pdo->query("SELECT * FROM tbl_materiais LIMIT 3");
    $exemplos = $stmt->fetchAll();
    
    echo "\n📝 EXEMPLOS DE REGISTROS:\n";
    foreach ($exemplos as $i => $reg) {
        echo "\nRegistro " . ($i + 1) . ":\n";
        foreach ($reg as $campo => $valor) {
            echo "  $campo: " . (is_null($valor) ? 'NULL' : $valor) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 