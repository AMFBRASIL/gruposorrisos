<?php
/**
 * Importador de Materiais de Limpeza
 * Lê: database/import/MATERIAIS DE LIMPEZA.csv
 * Insere em: tbl_catalogo_materiais + tbl_estoque_filiais
 */

require_once 'config/conexao.php';

header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║  IMPORTADOR DE MATERIAIS DE LIMPEZA - CSV → BANCO  ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Ler arquivo CSV
    $arquivoCSV = 'database/import/MATERIAIS DE LIMPEZA.csv';
    
    if (!file_exists($arquivoCSV)) {
        die("❌ Arquivo não encontrado: $arquivoCSV\n");
    }
    
    $handle = fopen($arquivoCSV, 'r');
    if (!$handle) {
        die("❌ Erro ao abrir arquivo\n");
    }
    
    // Pular as primeiras 3 linhas (cabeçalho)
    for ($i = 0; $i < 3; $i++) {
        fgetcsv($handle, 0, ';');
    }
    
    $materiais = [];
    $linha = 4;
    $codigoSequencial = 1;
    
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        // O nome/descrição está na coluna 3 (índice 2) - formato: ;;PRODUTO
        if (count($data) < 3 || empty($data[2])) {
            continue;
        }
        
        $descricao = trim($data[2]);
        
        // Pular linhas vazias ou linhas de cabeçalho
        if (empty($descricao) || strpos($descricao, 'PEDIDO') !== false || strpos($descricao, 'EM USO') !== false) {
            continue;
        }
        
        // Gerar código automático
        $codigo = 'LIMP' . str_pad($codigoSequencial, 4, '0', STR_PAD_LEFT);
        
        $materiais[] = [
            'codigo' => $codigo,
            'descricao' => $descricao,
            'linha' => $linha
        ];
        
        $codigoSequencial++;
        $linha++;
    }
    
    fclose($handle);
    
    echo "📊 Total de materiais de limpeza encontrados: " . count($materiais) . "\n\n";
    
    // Buscar categorias
    echo "🔍 Buscando categoria MATERIAS DE LIMPEZA...\n";
    $stmt = $pdo->query("SELECT id_categoria FROM tbl_categorias WHERE nome_categoria LIKE '%LIMPEZA%' LIMIT 1");
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    $idCategoria = $categoria ? $categoria['id_categoria'] : null;
    
    if ($idCategoria) {
        echo "✅ Categoria encontrada: ID {$idCategoria}\n\n";
    } else {
        echo "⚠️  Categoria MATERIAS DE LIMPEZA não encontrada!\n";
        echo "   Execute: database/inserir_categorias_materiais.sql\n\n";
    }
    
    // Buscar filiais ativas
    $stmtFiliais = $pdo->query("SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1");
    $filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC);
    echo "🏢 Filiais encontradas: " . count($filiais) . "\n";
    foreach ($filiais as $fil) {
        echo "   - Filial {$fil['id_filial']}: {$fil['nome_filial']}\n";
    }
    echo "\n";
    
    // Buscar fornecedor padrão para materiais de limpeza
    echo "🔍 Buscando fornecedor para materiais de limpeza...\n";
    $stmt = $pdo->query("SELECT id_fornecedor FROM tbl_fornecedores WHERE razao_social LIKE '%EAC%' AND ativo = 1 LIMIT 1");
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
    $idFornecedor = $fornecedor ? $fornecedor['id_fornecedor'] : null;
    
    if ($idFornecedor) {
        echo "✅ Fornecedor encontrado: ID {$idFornecedor}\n\n";
    } else {
        echo "⚠️  Fornecedor não encontrado - materiais ficarão sem fornecedor\n\n";
    }
    
    $stats = [
        'materiaisInseridos' => 0,
        'estoquesInseridos' => 0,
        'erros' => 0
    ];
    
    // Iniciar transação
    echo "📝 Iniciando importação...\n\n";
    $pdo->beginTransaction();
    
    foreach ($materiais as $idx => $mat) {
        $codigo = $mat['codigo'];
        $nome = $mat['descricao'];
        $descricao = $mat['descricao'];
        
        // Determinar unidade baseada na descrição
        $idUnidade = 1; // Default: UN
        $descLower = strtolower($nome);
        
        if (strpos($descLower, 'litro') !== false || strpos($descLower, '5l') !== false || strpos($descLower, '2l') !== false || strpos($descLower, '1l') !== false) {
            $idUnidade = 3; // L (Litro)
        } elseif (strpos($descLower, 'cx') !== false || strpos($descLower, 'caixa') !== false) {
            $idUnidade = 6; // CX
        } elseif (strpos($descLower, 'pct') !== false || strpos($descLower, 'pacote') !== false || strpos($descLower, 'fardo') !== false) {
            $idUnidade = 7; // PCT
        } elseif (strpos($descLower, 'ml') !== false && strpos($descLower, 'litro') === false) {
            $idUnidade = 1; // UN (frasco)
        }
        
        try {
            // 1. INSERIR NO CATÁLOGO
            $sqlInsertCatalogo = "INSERT INTO tbl_catalogo_materiais (
                codigo, nome, descricao, id_categoria, id_fornecedor, id_fabricante,
                id_unidade, preco_unitario_padrao, estoque_minimo_padrao, estoque_maximo_padrao,
                observacoes, ativo, data_criacao
            ) VALUES (?, ?, ?, ?, ?, NULL, ?, 0.00, 10, 100, 'Material de limpeza importado automaticamente', 1, NOW())";
            
            $stmtCatalogo = $pdo->prepare($sqlInsertCatalogo);
            $stmtCatalogo->execute([
                $codigo,
                $nome,
                $descricao,
                $idCategoria,
                $idFornecedor,
                $idUnidade
            ]);
            
            $idCatalogo = $pdo->lastInsertId();
            $stats['materiaisInseridos']++;
            
            // 2. CRIAR ESTOQUE EM TODAS AS FILIAIS
            $sqlInsertEstoque = "INSERT INTO tbl_estoque_filiais (
                id_catalogo, id_filial, estoque_atual, estoque_minimo, estoque_maximo,
                preco_unitario, localizacao_estoque, observacoes_estoque, ativo, data_criacao
            ) VALUES (?, ?, 0.00, 10.00, 100.00, 0.00, 'A definir', 'Estoque criado automaticamente na importação', 1, NOW())";
            
            $stmtEstoque = $pdo->prepare($sqlInsertEstoque);
            
            foreach ($filiais as $filial) {
                $stmtEstoque->execute([$idCatalogo, $filial['id_filial']]);
                $stats['estoquesInseridos']++;
            }
            
            // Progresso
            if (($idx + 1) % 20 == 0) {
                echo "   ✅ Processados " . ($idx + 1) . "/" . count($materiais) . " materiais...\n";
            }
            
        } catch (Exception $e) {
            $stats['erros']++;
            echo "   ❌ Erro ao inserir material {$codigo}: " . $e->getMessage() . "\n";
        }
    }
    
    // Commit da transação
    $pdo->commit();
    
    echo "\n" . str_repeat("=", 76) . "\n";
    echo "✅ IMPORTAÇÃO DE MATERIAIS DE LIMPEZA CONCLUÍDA!\n";
    echo str_repeat("=", 76) . "\n\n";
    
    echo "📈 ESTATÍSTICAS FINAIS:\n";
    echo "   ├─ Materiais importados: " . $stats['materiaisInseridos'] . "\n";
    echo "   ├─ Estoques criados: " . $stats['estoquesInseridos'] . "\n";
    echo "   ├─ Erros: " . $stats['erros'] . "\n";
    echo "   └─ Filiais cobertas: " . count($filiais) . "\n\n";
    
    echo "🔍 VERIFICAÇÃO FINAL:\n";
    $stmtVerif = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE codigo LIKE 'LIMP%' AND ativo = 1");
    $totalCatalogo = $stmtVerif->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   ├─ Materiais de limpeza no catálogo: {$totalCatalogo}\n";
    
    $stmtVerifEstoque = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $totalEstoque = $stmtVerifEstoque->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   └─ Total de estoques no sistema: {$totalEstoque}\n\n";
    
    echo "✅ Materiais de limpeza importados e estoque criado em todas as filiais!\n";
    echo "🎯 Acesse material.php para visualizar os materiais.\n\n";
    
    echo "📋 CÓDIGOS GERADOS:\n";
    echo "   De: LIMP0001\n";
    echo "   Até: LIMP" . str_pad(count($materiais), 4, '0', STR_PAD_LEFT) . "\n";
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n⚠️  Rollback executado - nenhuma alteração foi feita no banco!\n";
    }
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "📍 Linha: " . $e->getLine() . " | Arquivo: " . $e->getFile() . "\n";
}
?>

