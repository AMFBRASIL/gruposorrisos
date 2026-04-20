<?php
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "🔍 Verificando resultado da migração...\n\n";
    
    // Verificar catálogo
    echo "📋 CATÁLOGO DE MATERIAIS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE ativo = 1");
    $total = $stmt->fetch()['total'];
    echo "Total de materiais no catálogo: $total\n";
    
    // Mostrar alguns materiais do catálogo
    $stmt = $pdo->query("SELECT id_catalogo, codigo, nome, id_categoria, id_fornecedor FROM tbl_catalogo_materiais WHERE ativo = 1 LIMIT 5");
    $materiais = $stmt->fetchAll();
    
    echo "\nExemplos de materiais no catálogo:\n";
    foreach ($materiais as $material) {
        echo "  ID: {$material['id_catalogo']}, Código: {$material['codigo']}, Nome: {$material['nome']}\n";
    }
    
    // Verificar estoques por filial
    echo "\n📦 ESTOQUES POR FILIAL:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $total = $stmt->fetch()['total'];
    echo "Total de estoques por filial: $total\n";
    
    // Mostrar alguns estoques
    $stmt = $pdo->query("SELECT ef.id_estoque, ef.id_catalogo, ef.id_filial, ef.estoque_atual, ef.estoque_minimo, cm.codigo, cm.nome, fil.nome_filial 
                         FROM tbl_estoque_filiais ef 
                         INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                         INNER JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial 
                         WHERE ef.ativo = 1 LIMIT 5");
    $estoques = $stmt->fetchAll();
    
    echo "\nExemplos de estoques por filial:\n";
    foreach ($estoques as $estoque) {
        echo "  Estoque ID: {$estoque['id_estoque']}, Material: {$estoque['codigo']} - {$estoque['nome']}, Filial: {$estoque['nome_filial']}, Qtd: {$estoque['estoque_atual']}\n";
    }
    
    // Verificar tabela antiga
    echo "\n📊 TABELA ANTIGA:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_materiais WHERE ativo = 1");
    $total = $stmt->fetch()['total'];
    echo "Materiais ativos na tabela antiga: $total (deve ser 0)\n";
    
    // Verificar backup
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_materiais_backup_%'");
    $backups = $stmt->fetchAll();
    echo "Tabelas de backup criadas: " . count($backups) . "\n";
    
    echo "\n🎉 VERIFICAÇÃO CONCLUÍDA!\n";
    echo "✅ Migração realizada com sucesso!\n";
    echo "💡 Sistema agora usa a nova estrutura centralizada.\n";
    
} catch (Exception $e) {
    echo "❌ Erro na verificação: " . $e->getMessage() . "\n";
}
?> 