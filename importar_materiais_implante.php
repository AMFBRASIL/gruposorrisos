<?php
/**
 * Importador de Materiais de Implante
 * Lê: database/import/MATERIAIS DE IMPLANTE.csv
 * Insere em: tbl_catalogo_materiais + tbl_estoque_filiais
 */

require_once 'config/conexao.php';

header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║  IMPORTADOR DE MATERIAIS DE IMPLANTE - CSV → BANCO ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Ler arquivo CSV
    $arquivoCSV = 'database/import/MATERIAIS DE IMPLANTE.csv';
    
    if (!file_exists($arquivoCSV)) {
        die("❌ Arquivo não encontrado: $arquivoCSV\n");
    }
    
    $handle = fopen($arquivoCSV, 'r');
    if (!$handle) {
        die("❌ Erro ao abrir arquivo\n");
    }
    
    // Pular as primeiras 4 linhas (cabeçalho)
    for ($i = 0; $i < 4; $i++) {
        fgetcsv($handle, 0, ';');
    }
    
    $materiais = [];
    $linha = 5;
    $codigoSequencial = 1;
    
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        // Formato: COD;PRODUTOS;ESTOQUE;PEDIDO
        if (count($data) < 2) {
            continue;
        }
        
        $codigoProduto = trim($data[0]);
        $nomeProduto = trim($data[1]);
        
        // Pular linhas vazias
        if (empty($nomeProduto)) {
            continue;
        }
        
        // Se não tem código, gerar automático
        if (empty($codigoProduto)) {
            $codigoProduto = 'IMPL' . str_pad($codigoSequencial, 4, '0', STR_PAD_LEFT);
            $codigoSequencial++;
        }
        
        $materiais[] = [
            'codigo' => $codigoProduto,
            'descricao' => $nomeProduto,
            'linha' => $linha
        ];
        
        $linha++;
    }
    
    fclose($handle);
    
    echo "📊 Total de materiais de implante encontrados: " . count($materiais) . "\n\n";
    
    // Buscar categorias
    echo "🔍 Buscando categoria IMPLANTODONTIA...\n";
    $stmt = $pdo->query("SELECT id_categoria FROM tbl_categorias WHERE nome_categoria LIKE '%IMPLANT%' LIMIT 1");
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    $idCategoria = $categoria ? $categoria['id_categoria'] : null;
    
    if ($idCategoria) {
        echo "✅ Categoria encontrada: ID {$idCategoria}\n\n";
    } else {
        echo "⚠️  Categoria IMPLANTODONTIA não encontrada!\n";
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
    
    // Buscar fabricante SIN IMPLANTES
    echo "🔍 Buscando fabricante SIN IMPLANTES...\n";
    $stmt = $pdo->query("SELECT id_fornecedor FROM tbl_fornecedores WHERE razao_social LIKE '%SIN%' AND ativo = 1 LIMIT 1");
    $fabricante = $stmt->fetch(PDO::FETCH_ASSOC);
    $idFabricante = $fabricante ? $fabricante['id_fornecedor'] : null;
    
    if ($idFabricante) {
        echo "✅ Fabricante encontrado: ID {$idFabricante}\n\n";
    } else {
        echo "⚠️  Fabricante SIN IMPLANTES não encontrado\n";
        echo "   Os materiais ficarão sem fabricante\n";
        echo "   Cadastre em addFornecedor.php marcando 'É Fabricante'\n\n";
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
        
        // Unidade padrão para implantes: UN
        $idUnidade = 1;
        
        try {
            // 1. INSERIR NO CATÁLOGO
            $sqlInsertCatalogo = "INSERT INTO tbl_catalogo_materiais (
                codigo, nome, descricao, id_categoria, id_fornecedor, id_fabricante,
                id_unidade, preco_unitario_padrao, estoque_minimo_padrao, estoque_maximo_padrao,
                observacoes, ativo, data_criacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, 5, 50, 'Material de implante - SIN Sistema de Implante', 1, NOW())";
            
            $stmtCatalogo = $pdo->prepare($sqlInsertCatalogo);
            $stmtCatalogo->execute([
                $codigo,
                $nome,
                $descricao,
                $idCategoria,
                $idFabricante, // Fornecedor = Fabricante para implantes
                $idFabricante,  // Fabricante
                $idUnidade
            ]);
            
            $idCatalogo = $pdo->lastInsertId();
            $stats['materiaisInseridos']++;
            
            // 2. CRIAR ESTOQUE EM TODAS AS FILIAIS
            $sqlInsertEstoque = "INSERT INTO tbl_estoque_filiais (
                id_catalogo, id_filial, estoque_atual, estoque_minimo, estoque_maximo,
                preco_unitario, localizacao_estoque, observacoes_estoque, ativo, data_criacao
            ) VALUES (?, ?, 0.00, 5.00, 50.00, 0.00, 'Estoque de Implantes', 'Estoque criado automaticamente na importação', 1, NOW())";
            
            $stmtEstoque = $pdo->prepare($sqlInsertEstoque);
            
            foreach ($filiais as $filial) {
                $stmtEstoque->execute([$idCatalogo, $filial['id_filial']]);
                $stats['estoquesInseridos']++;
            }
            
            // Progresso
            if (($idx + 1) % 50 == 0) {
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
    echo "✅ IMPORTAÇÃO DE MATERIAIS DE IMPLANTE CONCLUÍDA!\n";
    echo str_repeat("=", 76) . "\n\n";
    
    echo "📈 ESTATÍSTICAS FINAIS:\n";
    echo "   ├─ Materiais importados: " . $stats['materiaisInseridos'] . "\n";
    echo "   ├─ Estoques criados: " . $stats['estoquesInseridos'] . "\n";
    echo "   ├─ Erros: " . $stats['erros'] . "\n";
    echo "   └─ Filiais cobertas: " . count($filiais) . "\n\n";
    
    echo "🔍 VERIFICAÇÃO FINAL:\n";
    
    // Materiais de implante
    $stmtVerif = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE codigo LIKE 'IMPL%' OR codigo LIKE '%AN%' OR codigo LIKE '%CI%' AND ativo = 1");
    $totalImplantes = $stmtVerif->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   ├─ Materiais de implante no catálogo: {$totalImplantes}\n";
    
    // Total geral
    $stmtTotal = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE ativo = 1");
    $totalGeral = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   ├─ TOTAL GERAL de materiais: {$totalGeral}\n";
    
    $stmtVerifEstoque = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $totalEstoque = $stmtVerifEstoque->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   └─ Total de estoques no sistema: {$totalEstoque}\n\n";
    
    echo "✅ Materiais de implante importados e estoque criado em todas as filiais!\n";
    echo "🎯 Acesse material.php para visualizar os materiais.\n\n";
    
    echo "📋 TIPOS DE CÓDIGOS:\n";
    echo "   - Códigos originais mantidos (ex: AN4100, CI4104, SA4855T)\n";
    echo "   - Códigos gerados para itens sem código: IMPL0001, IMPL0002, etc.\n\n";
    
    echo "📦 CATEGORIAS DOS MATERIAIS:\n";
    echo "   - Implantes\n";
    echo "   - Cicatrizadores\n";
    echo "   - Análogos\n";
    echo "   - Abutments\n";
    echo "   - Enxertos ósseos\n";
    echo "   - Cilindros\n";
    echo "   - E mais...\n";
    
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

