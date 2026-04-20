<?php
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "🔧 Corrigindo constraints das tabelas...\n\n";
    
    // Corrigir constraint de código de barras para permitir NULL
    echo "📋 Corrigindo constraint de código de barras...\n";
    
    // Remover constraint antiga
    $pdo->exec("ALTER TABLE tbl_catalogo_materiais DROP INDEX uk_codigo_barras");
    echo "   ✅ Constraint antiga removida\n";
    
    // Adicionar nova constraint que permite NULL
    $pdo->exec("ALTER TABLE tbl_catalogo_materiais ADD UNIQUE KEY uk_codigo_barras (codigo_barras)");
    echo "   ✅ Nova constraint adicionada\n";
    
    // Verificar se há códigos de barras vazios e limpar
    echo "📋 Limpando códigos de barras vazios...\n";
    $pdo->exec("UPDATE tbl_catalogo_materiais SET codigo_barras = NULL WHERE codigo_barras = '' OR codigo_barras = 'NULL'");
    echo "   ✅ Códigos de barras vazios limpos\n";
    
    echo "\n🎉 Constraints corrigidas com sucesso!\n";
    echo "💡 Agora você pode executar a migração novamente.\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao corrigir constraints: " . $e->getMessage() . "\n";
}
?> 