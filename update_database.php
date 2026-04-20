<?php
// Script para atualizar o banco de dados adicionando o campo data_pedido

require_once 'config/conexao.php';

try {
    // Ler e executar o script SQL
    $sql = file_get_contents('database/update_add_data_pedido.sql');
    
    // Remover comentários e dividir em comandos
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $command) {
        if (!empty($command) && !preg_match('/^--/', $command)) {
            $pdo->exec($command);
            echo "Comando executado: " . substr($command, 0, 50) . "...\n";
        }
    }
    
    // Verificar se a coluna foi adicionada
    $stmt = $pdo->query("DESCRIBE tbl_pedidos_compra");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== Estrutura atual da tabela tbl_pedidos_compra ===\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " - " . $column['Null'] . " - " . $column['Default'] . "\n";
    }
    
    // Verificar especificamente se data_pedido existe
    $hasDataPedido = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'data_pedido') {
            $hasDataPedido = true;
            break;
        }
    }
    
    if ($hasDataPedido) {
        echo "\n✅ Campo 'data_pedido' foi adicionado com sucesso!\n";
    } else {
        echo "\n❌ Campo 'data_pedido' ainda não existe na tabela.\n";
    }
    
} catch (Exception $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
}
?>