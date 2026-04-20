<?php
/**
 * SCRIPT DE INICIALIZAÇÃO DE ESTOQUE PARA TODAS AS FILIAIS
 * Grupo Sorrisos
 * 
 * Este script cria estoque zerado para todos os materiais do catálogo
 * em todas as filiais ativas do sistema.
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/EstoqueFilial.php';

echo "🚀 INICIALIZANDO ESTOQUE PARA TODAS AS FILIAIS\n";
echo "==============================================\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    $estoque = new EstoqueFilial($pdo);
    
    // 1. Verificar filiais ativas
    echo "1. Verificando filiais ativas...\n";
    $stmt = $pdo->query("SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1 ORDER BY nome_filial");
    $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($filiais)) {
        echo "❌ Nenhuma filial ativa encontrada!\n";
        exit;
    }
    
    echo "✅ " . count($filiais) . " filiais encontradas:\n";
    foreach ($filiais as $filial) {
        echo "   - {$filial['nome_filial']} (ID: {$filial['id_filial']})\n";
    }
    echo "\n";
    
    // 2. Verificar materiais do catálogo
    echo "2. Verificando materiais do catálogo...\n";
    $stmt = $pdo->query("SELECT id_catalogo, codigo, nome FROM tbl_catalogo_materiais WHERE ativo = 1 ORDER BY nome");
    $materiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($materiais)) {
        echo "❌ Nenhum material encontrado no catálogo!\n";
        exit;
    }
    
    echo "✅ " . count($materiais) . " materiais encontrados no catálogo\n\n";
    
    // 3. Verificar estoques existentes
    echo "3. Verificando estoques existentes...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $estoquesExistentes = $stmt->fetch()['total'];
    echo "✅ " . $estoquesExistentes . " estoques já existem\n\n";
    
    // 4. Calcular total de estoques necessários
    $totalNecessario = count($filiais) * count($materiais);
    $totalFaltando = $totalNecessario - $estoquesExistentes;
    
    echo "4. ANÁLISE:\n";
    echo "   - Total de estoques necessários: {$totalNecessario}\n";
    echo "   - Estoques existentes: {$estoquesExistentes}\n";
    echo "   - Estoques faltando: {$totalFaltando}\n\n";
    
    if ($totalFaltando <= 0) {
        echo "✅ Todos os estoques já estão criados!\n";
        exit;
    }
    
    // 5. Perguntar se deve continuar
    echo "⚠️  ATENÇÃO: Este processo criará {$totalFaltando} registros de estoque.\n";
    echo "Deseja continuar? (s/N): ";
    
    $handle = fopen("php://stdin", "r");
    $resposta = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($resposta) !== 's') {
        echo "❌ Operação cancelada pelo usuário.\n";
        exit;
    }
    
    echo "\n5. Iniciando criação de estoques...\n";
    
    // 6. Criar estoques faltantes
    $criados = 0;
    $erros = 0;
    
    foreach ($materiais as $material) {
        foreach ($filiais as $filial) {
            try {
                // Verificar se já existe
                $stmt = $pdo->prepare("SELECT id_estoque FROM tbl_estoque_filiais WHERE id_catalogo = ? AND id_filial = ? AND ativo = 1");
                $stmt->execute([$material['id_catalogo'], $filial['id_filial']]);
                
                if (!$stmt->fetch()) {
                    // Criar estoque zerado
                    $dadosEstoque = [
                        'id_catalogo' => $material['id_catalogo'],
                        'id_filial' => $filial['id_filial'],
                        'estoque_atual' => 0.00,
                        'estoque_minimo' => 0.00,
                        'estoque_maximo' => 0.00,
                        'preco_unitario' => 0.00,
                        'localizacao_estoque' => 'A definir',
                        'observacoes_estoque' => 'Estoque inicializado automaticamente',
                        'ativo' => 1
                    ];
                    
                    $estoque->insert($dadosEstoque);
                    $criados++;
                    
                    if ($criados % 10 == 0) {
                        echo "   ✅ {$criados} estoques criados...\n";
                    }
                }
            } catch (Exception $e) {
                $erros++;
                echo "   ❌ Erro ao criar estoque para {$material['nome']} em {$filial['nome_filial']}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n6. RESULTADO FINAL:\n";
    echo "   ✅ Estoques criados: {$criados}\n";
    echo "   ❌ Erros: {$erros}\n";
    echo "   📊 Total processado: " . ($criados + $erros) . "\n\n";
    
    // 7. Verificação final
    echo "7. Verificação final...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $totalFinal = $stmt->fetch()['total'];
    echo "   📊 Total de estoques no sistema: {$totalFinal}\n";
    echo "   🎯 Total esperado: {$totalNecessario}\n";
    
    if ($totalFinal >= $totalNecessario) {
        echo "   ✅ SUCESSO: Todos os estoques foram criados!\n";
    } else {
        echo "   ⚠️  ATENÇÃO: Alguns estoques podem não ter sido criados.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 