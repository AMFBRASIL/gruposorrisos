<?php
/**
 * Script para adicionar campos de brinde na tabela tbl_movimentacoes
 * Execute este script para implementar a funcionalidade de brindes
 */

require_once __DIR__ . '/../config/conexao.php';

echo "🚀 Iniciando implementação dos campos de brinde...\n\n";

try {
    // Obter instância da conexão
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    // 1. Verificar se os campos já existem
    echo "📋 Verificando estrutura atual da tabela...\n";
    $sql = "DESCRIBE tbl_movimentacoes";
    $stmt = $pdo->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $camposBrinde = ['is_brinde', 'fornecedor_brinde', 'valor_estimado_brinde'];
    $camposExistentes = [];
    
    foreach ($colunas as $coluna) {
        if (in_array($coluna, $camposBrinde)) {
            $camposExistentes[] = $coluna;
        }
    }
    
    if (!empty($camposExistentes)) {
        echo "⚠️  Campos de brinde já existem: " . implode(', ', $camposExistentes) . "\n";
        echo "🔄 Removendo campos existentes para recriar...\n";
        
        foreach ($camposExistentes as $campo) {
            $sql = "ALTER TABLE tbl_movimentacoes DROP COLUMN {$campo}";
            $pdo->exec($sql);
            echo "   ✅ Campo {$campo} removido\n";
        }
    }
    
    // 2. Adicionar campos de brinde
    echo "\n📝 Adicionando campos de brinde...\n";
    
    // Campo is_brinde (boolean)
    $sql = "ALTER TABLE tbl_movimentacoes ADD COLUMN is_brinde TINYINT(1) DEFAULT 0 COMMENT 'Indica se é movimentação de brinde'";
    $pdo->exec($sql);
    echo "   ✅ Campo is_brinde adicionado\n";
    
    // Campo fornecedor_brinde (varchar)
    $sql = "ALTER TABLE tbl_movimentacoes ADD COLUMN fornecedor_brinde VARCHAR(200) NULL COMMENT 'Nome do fornecedor do brinde'";
    $pdo->exec($sql);
    echo "   ✅ Campo fornecedor_brinde adicionado\n";
    
    // Campo valor_estimado_brinde (decimal)
    $sql = "ALTER TABLE tbl_movimentacoes ADD COLUMN valor_estimado_brinde DECIMAL(15,4) NULL COMMENT 'Valor estimado do brinde'";
    $pdo->exec($sql);
    echo "   ✅ Campo valor_estimado_brinde adicionado\n";
    
    // 3. Adicionar índices para melhor performance
    echo "\n🔍 Adicionando índices para campos de brinde...\n";
    
    $sql = "ALTER TABLE tbl_movimentacoes ADD INDEX idx_is_brinde (is_brinde)";
    $pdo->exec($sql);
    echo "   ✅ Índice idx_is_brinde adicionado\n";
    
    $sql = "ALTER TABLE tbl_movimentacoes ADD INDEX idx_fornecedor_brinde (fornecedor_brinde)";
    $pdo->exec($sql);
    echo "   ✅ Índice idx_fornecedor_brinde adicionado\n";
    
    // 4. Verificar estrutura final
    echo "\n📊 Verificando estrutura final da tabela...\n";
    $sql = "DESCRIBE tbl_movimentacoes";
    $stmt = $pdo->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Estrutura atual da tabela tbl_movimentacoes:\n";
    foreach ($colunas as $coluna) {
        $tipo = $coluna['Type'];
        $null = $coluna['Null'];
        $default = $coluna['Default'];
        $comment = $coluna['Comment'];
        
        echo "   - {$coluna['Field']}: {$tipo} {$null} DEFAULT {$default}";
        if ($comment) {
            echo " COMMENT '{$comment}'";
        }
        echo "\n";
    }
    
    echo "\n🎉 Campos de brinde implementados com sucesso!\n";
    echo "📋 Próximos passos:\n";
    echo "   1. Atualizar o modelo Movimentacao.php\n";
    echo "   2. Atualizar a API de movimentações\n";
    echo "   3. Testar a funcionalidade completa\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 