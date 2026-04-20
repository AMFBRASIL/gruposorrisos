<?php
/**
 * Script para verificar a estrutura atual da tabela tbl_itens_pedido_compra
 */

require_once 'config/config.php';
require_once 'config/conexao.php';

echo "🔍 Verificando estrutura da tabela tbl_itens_pedido_compra...\n\n";

try {
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    echo "✅ Conexão com banco estabelecida\n\n";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_itens_pedido_compra'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela tbl_itens_pedido_compra não existe!\n";
        exit(1);
    }
    
    echo "✅ Tabela tbl_itens_pedido_compra existe\n\n";
    
    // Verificar estrutura atual
    echo "📋 Estrutura atual da tabela:\n";
    $stmt = $pdo->query("DESCRIBE tbl_itens_pedido_compra");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $colunasExistentes = [];
    foreach ($colunas as $coluna) {
        $colunasExistentes[] = $coluna['Field'];
        echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
    }
    
    echo "\n";
    
    // Verificar colunas específicas
    $colunasImportantes = ['id_material', 'id_catalogo'];
    
    echo "🔍 Verificando colunas importantes:\n";
    foreach ($colunasImportantes as $coluna) {
        if (in_array($coluna, $colunasExistentes)) {
            echo "✅ Coluna '$coluna' existe\n";
        } else {
            echo "❌ Coluna '$coluna' NÃO existe\n";
        }
    }
    
    echo "\n✅ Verificação concluída!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>