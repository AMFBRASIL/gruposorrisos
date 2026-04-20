<?php
/**
 * Script para adicionar coluna 'ativo' na tabela tbl_itens_pedido_compra
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

echo "🔧 Adicionando coluna 'ativo' na tabela tbl_itens_pedido_compra...\n\n";

try {
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    echo "✅ Conexão com banco estabelecida\n\n";
    
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_itens_pedido_compra LIKE 'ativo'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️ Coluna 'ativo' já existe na tabela tbl_itens_pedido_compra\n";
        echo "✅ Nenhuma alteração necessária\n";
    } else {
        echo "🔍 Coluna 'ativo' não existe. Adicionando...\n";
        
        // Adicionar coluna ativo
        $sql = "ALTER TABLE tbl_itens_pedido_compra ADD COLUMN ativo TINYINT(1) DEFAULT 1 COMMENT 'Indica se o item está ativo'";
        $pdo->exec($sql);
        
        echo "✅ Coluna 'ativo' adicionada com sucesso!\n";
        
        // Verificar estrutura atualizada
        echo "\n🔍 Estrutura atualizada da tabela:\n";
        $stmt = $pdo->query("DESCRIBE tbl_itens_pedido_compra");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($colunas as $coluna) {
            echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
        }
        
        // Adicionar índice para otimização
        echo "\n🔍 Adicionando índice para coluna 'ativo'...\n";
        try {
            $sql = "ALTER TABLE tbl_itens_pedido_compra ADD INDEX idx_ativo (ativo)";
            $pdo->exec($sql);
            echo "✅ Índice adicionado com sucesso!\n";
        } catch (Exception $e) {
            echo "⚠️ Índice já existe ou erro ao criar: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Processo concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 