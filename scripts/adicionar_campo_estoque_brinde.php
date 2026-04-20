<?php
/**
 * Script para adicionar campo estoque_brinde na tabela tbl_estoque_filiais
 * Execute este script para implementar controle de estoque separado para brindes
 */

require_once __DIR__ . '/../config/conexao.php';

echo "🚀 Iniciando implementação do campo estoque_brinde...\n\n";

try {
    // Obter instância da conexão
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    // 1. Verificar se o campo já existe
    echo "📋 Verificando estrutura atual da tabela...\n";
    $sql = "DESCRIBE tbl_estoque_filiais";
    $stmt = $pdo->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('estoque_brinde', $colunas)) {
        echo "⚠️  Campo estoque_brinde já existe na tabela\n";
        echo "🔄 Removendo campo existente para recriar...\n";
        
        $sql = "ALTER TABLE tbl_estoque_filiais DROP COLUMN estoque_brinde";
        $pdo->exec($sql);
        echo "   ✅ Campo estoque_brinde removido\n";
    }
    
    // 2. Adicionar campo estoque_brinde
    echo "\n📝 Adicionando campo estoque_brinde...\n";
    
    $sql = "ALTER TABLE tbl_estoque_filiais ADD COLUMN estoque_brinde DECIMAL(15,3) DEFAULT 0.000 COMMENT 'Estoque de brindes separado do estoque comercial'";
    $pdo->exec($sql);
    echo "   ✅ Campo estoque_brinde adicionado\n";
    
    // 3. Adicionar índice para melhor performance
    echo "\n🔍 Adicionando índice para estoque_brinde...\n";
    
    $sql = "ALTER TABLE tbl_estoque_filiais ADD INDEX idx_estoque_brinde (estoque_brinde)";
    $pdo->exec($sql);
    echo "   ✅ Índice idx_estoque_brinde adicionado\n";
    
    // 4. Verificar estrutura final
    echo "\n📊 Verificando estrutura final da tabela...\n";
    $sql = "DESCRIBE tbl_estoque_filiais";
    $stmt = $pdo->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Estrutura atual da tabela tbl_estoque_filiais:\n";
    foreach ($colunas as $coluna) {
        $tipo = $coluna['Type'];
        $null = $coluna['Null'];
        $default = $coluna['Default'];
        $comment = $coluna['Comment'] ?? '';
        
        echo "   - {$coluna['Field']}: {$tipo} {$null} DEFAULT {$default}";
        if ($comment) {
            echo " COMMENT '{$comment}'";
        }
        echo "\n";
    }
    
    echo "\n🎉 Campo estoque_brinde implementado com sucesso!\n";
    echo "📋 Funcionalidades implementadas:\n";
    echo "   1. ✅ Estoque de brindes separado do estoque comercial\n";
    echo "   2. ✅ Controle independente para materiais de brinde\n";
    echo "   3. ✅ Estatísticas separadas para brindes\n";
    echo "   4. ✅ Índices para melhor performance\n";
    
    echo "\n🔧 Como funciona agora:\n";
    echo "   - Materiais NORMALS: afetam estoque_atual\n";
    echo "   - Materiais BRINDE: afetam estoque_brinde\n";
    echo "   - Controle separado para relatórios e gestão\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 