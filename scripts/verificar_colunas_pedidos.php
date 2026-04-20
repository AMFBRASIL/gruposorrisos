<?php
/**
 * Script para verificar e adicionar colunas necessárias na tabela tbl_itens_pedido_compra
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

echo "🔍 Verificando colunas necessárias na tabela tbl_itens_pedido_compra...\n\n";

try {
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    echo "✅ Conexão com banco estabelecida\n\n";
    
    // Verificar estrutura atual
    echo "🔍 Estrutura atual da tabela:\n";
    $stmt = $pdo->query("DESCRIBE tbl_itens_pedido_compra");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $colunasExistentes = [];
    foreach ($colunas as $coluna) {
        $colunasExistentes[] = $coluna['Field'];
        echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
    }
    
    echo "\n";
    
    // Colunas necessárias para a API
    $colunasNecessarias = [
        'preco_fornecedor' => "DECIMAL(10,2) NULL COMMENT 'Preço oferecido pelo fornecedor'",
        'disponivel' => "TINYINT(1) DEFAULT 1 COMMENT 'Indica se o item está disponível'",
        'data_atualizacao' => "TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização'"
    ];
    
    echo "🔍 Verificando colunas necessárias:\n";
    
    foreach ($colunasNecessarias as $coluna => $definicao) {
        if (in_array($coluna, $colunasExistentes)) {
            echo "✅ Coluna '$coluna' já existe\n";
        } else {
            echo "⚠️ Coluna '$coluna' não existe. Adicionando...\n";
            
            try {
                $sql = "ALTER TABLE tbl_itens_pedido_compra ADD COLUMN $coluna $definicao";
                $pdo->exec($sql);
                echo "✅ Coluna '$coluna' adicionada com sucesso!\n";
            } catch (Exception $e) {
                echo "❌ Erro ao adicionar coluna '$coluna': " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    
    // Verificar estrutura final
    echo "🔍 Estrutura final da tabela:\n";
    $stmt = $pdo->query("DESCRIBE tbl_itens_pedido_compra");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $coluna) {
        echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
    }
    
    echo "\n✅ Processo concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 