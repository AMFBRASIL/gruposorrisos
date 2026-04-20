<?php
/**
 * SCRIPT DE VERIFICAÇÃO DE ESTOQUE POR FILIAL
 * Grupo Sorrisos
 * 
 * Este script verifica o status dos estoques em todas as filiais
 * e identifica materiais que não possuem estoque.
 */

require_once __DIR__ . '/../config/conexao.php';

echo "🔍 VERIFICAÇÃO DE ESTOQUE POR FILIAL\n";
echo "====================================\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // 1. Verificar filiais ativas
    echo "1. FILIAIS ATIVAS:\n";
    $stmt = $pdo->query("SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1 ORDER BY nome_filial");
    $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($filiais)) {
        echo "❌ Nenhuma filial ativa encontrada!\n";
        exit;
    }
    
    foreach ($filiais as $filial) {
        echo "   ✅ {$filial['nome_filial']} (ID: {$filial['id_filial']})\n";
    }
    echo "\n";
    
    // 2. Verificar materiais do catálogo
    echo "2. MATERIAIS DO CATÁLOGO:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE ativo = 1");
    $totalMateriais = $stmt->fetch()['total'];
    echo "   📦 Total de materiais: {$totalMateriais}\n\n";
    
    // 3. Verificar estoques por filial
    echo "3. ESTOQUES POR FILIAL:\n";
    echo "   " . str_repeat("-", 80) . "\n";
    printf("   %-30s %-15s %-15s %-15s\n", "FILIAL", "TOTAL", "COM ESTOQUE", "SEM ESTOQUE");
    echo "   " . str_repeat("-", 80) . "\n";
    
    $totalGeral = 0;
    $comEstoqueGeral = 0;
    $semEstoqueGeral = 0;
    
    foreach ($filiais as $filial) {
        // Total de estoques para esta filial
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE id_filial = ? AND ativo = 1");
        $stmt->execute([$filial['id_filial']]);
        $totalEstoques = $stmt->fetch()['total'];
        
        // Materiais com estoque > 0
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE id_filial = ? AND estoque_atual > 0 AND ativo = 1");
        $stmt->execute([$filial['id_filial']]);
        $comEstoque = $stmt->fetch()['total'];
        
        // Materiais sem estoque
        $semEstoque = $totalEstoques - $comEstoque;
        
        printf("   %-30s %-15s %-15s %-15s\n", 
               $filial['nome_filial'], 
               $totalEstoques, 
               $comEstoque, 
               $semEstoque);
        
        $totalGeral += $totalEstoques;
        $comEstoqueGeral += $comEstoque;
        $semEstoqueGeral += $semEstoque;
    }
    
    echo "   " . str_repeat("-", 80) . "\n";
    printf("   %-30s %-15s %-15s %-15s\n", "TOTAL GERAL", $totalGeral, $comEstoqueGeral, $semEstoqueGeral);
    echo "\n";
    
    // 4. Análise de cobertura
    echo "4. ANÁLISE DE COBERTURA:\n";
    $cobertura = ($totalGeral / ($totalMateriais * count($filiais))) * 100;
    echo "   📊 Cobertura de estoque: " . number_format($cobertura, 2) . "%\n";
    
    if ($cobertura >= 100) {
        echo "   ✅ EXCELENTE: Todos os materiais possuem estoque em todas as filiais!\n";
    } elseif ($cobertura >= 80) {
        echo "   🟡 BOM: Maioria dos materiais possui estoque\n";
    } elseif ($cobertura >= 50) {
        echo "   🟠 REGULAR: Metade dos materiais possui estoque\n";
    } else {
        echo "   🔴 RUIM: Menos da metade dos materiais possui estoque\n";
    }
    echo "\n";
    
    // 5. Materiais sem estoque em cada filial
    echo "5. MATERIAIS SEM ESTOQUE (TOP 5 por filial):\n";
    foreach ($filiais as $filial) {
        echo "\n   🏥 {$filial['nome_filial']}:\n";
        
        $stmt = $pdo->prepare("
            SELECT cm.codigo, cm.nome, ef.estoque_atual
            FROM tbl_catalogo_materiais cm
            LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ?
            WHERE cm.ativo = 1 
            AND (ef.id_estoque IS NULL OR ef.estoque_atual = 0 OR ef.ativo = 0)
            ORDER BY cm.nome
            LIMIT 5
        ");
        $stmt->execute([$filial['id_filial']]);
        $materiaisSemEstoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($materiaisSemEstoque)) {
            echo "      ✅ Todos os materiais possuem estoque\n";
        } else {
            foreach ($materiaisSemEstoque as $material) {
                $status = $material['estoque_atual'] === null ? '❌ SEM ESTOQUE' : '⚠️  ESTOQUE ZERO';
                echo "      {$status}: {$material['codigo']} - {$material['nome']}\n";
            }
        }
    }
    
    // 6. Recomendações
    echo "\n6. RECOMENDAÇÕES:\n";
    if ($cobertura < 100) {
        echo "   🔧 Execute o script 'inicializar_estoque_filiais.php' para criar estoques faltantes\n";
        echo "   📝 Configure estoques mínimos e máximos para cada material/filial\n";
        echo "   🚚 Configure preços unitários específicos por filial se necessário\n";
    } else {
        echo "   ✅ Sistema está funcionando perfeitamente!\n";
        echo "   📊 Monitore estoques regularmente\n";
        echo "   🔄 Configure alertas para estoque baixo\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 