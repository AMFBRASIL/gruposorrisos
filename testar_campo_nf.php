<?php
require_once 'config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Verificar se a coluna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'url_nota_fiscal'");
    $coluna = $stmt->fetch();
    
    if ($coluna) {
        echo "✅ Coluna url_nota_fiscal existe!\n";
        echo "Tipo: " . $coluna['Type'] . "\n";
        echo "Null: " . $coluna['Null'] . "\n";
    } else {
        echo "❌ Coluna url_nota_fiscal NÃO existe!\n";
        echo "Execute o script: database/add_campo_nota_fiscal.sql\n";
    }
    
    // Verificar se há pedidos com NF
    $stmt = $pdo->query("SELECT id_pedido, numero_pedido, url_nota_fiscal FROM tbl_pedidos_compra WHERE url_nota_fiscal IS NOT NULL AND url_nota_fiscal != '' LIMIT 5");
    $pedidos = $stmt->fetchAll();
    
    if (count($pedidos) > 0) {
        echo "\n✅ Pedidos com NF encontrados:\n";
        foreach ($pedidos as $pedido) {
            echo "  - Pedido ID {$pedido['id_pedido']} ({$pedido['numero_pedido']}): {$pedido['url_nota_fiscal']}\n";
        }
    } else {
        echo "\n⚠️ Nenhum pedido com NF encontrado no banco.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
