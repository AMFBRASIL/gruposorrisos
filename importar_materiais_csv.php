<?php
/**
 * Importador de Materiais do CSV para SQL
 * Lê: database/import/MATERIAIS ODONTOLOGICOS.csv
 * Gera: database/materiais_odontologicos_import.sql
 */

require_once 'config/conexao.php';

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║  IMPORTADOR DE MATERIAIS ODONTOLÓGICOS - CSV → SQL  ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Ler arquivo CSV
    $arquivoCSV = 'database/import/MATERIAIS ODONTOLOGICOS.csv';
    
    if (!file_exists($arquivoCSV)) {
        die("❌ Arquivo não encontrado: $arquivoCSV\n");
    }
    
    $handle = fopen($arquivoCSV, 'r');
    if (!$handle) {
        die("❌ Erro ao abrir arquivo\n");
    }
    
    // Pular as primeiras 5 linhas (cabeçalho)
    for ($i = 0; $i < 5; $i++) {
        fgetcsv($handle, 0, ';');
    }
    
    $materiais = [];
    $linha = 6;
    
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        // Verificar se tem dados válidos
        if (count($data) < 5 || empty($data[1])) {
            continue;
        }
        
        $codigoProduto = trim($data[1]);
        $descricaoProduto = trim($data[2]);
        $unidade = trim($data[3]);
        $marca = trim($data[4]);
        
        // Pular linhas vazias
        if (empty($codigoProduto) || empty($descricaoProduto)) {
            continue;
        }
        
        $materiais[] = [
            'codigo' => $codigoProduto,
            'descricao' => $descricaoProduto,
            'unidade' => $unidade,
            'marca' => $marca,
            'linha' => $linha
        ];
        
        $linha++;
    }
    
    fclose($handle);
    
    echo "📊 Total de materiais encontrados: " . count($materiais) . "\n\n";
    
    // Buscar unidades disponíveis
    echo "🔍 Buscando unidades de medida...\n";
    $stmt = $pdo->query("SELECT id_unidade, sigla, nome FROM tbl_unidades_medida");
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Encontradas " . count($unidades) . " unidades\n";
    foreach ($unidades as $u) {
        echo "   - ID {$u['id_unidade']}: {$u['sigla']} ({$u['nome']})\n";
    }
    echo "\n";
    
    // Buscar categorias
    echo "🔍 Buscando categorias...\n";
    $stmt = $pdo->query("SELECT id_categoria, nome_categoria FROM tbl_categorias WHERE ativo = 1");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Encontradas " . count($categorias) . " categorias\n\n";
    
    // Buscar fornecedores/fabricantes
    echo "🔍 Buscando fabricantes...\n";
    $stmt = $pdo->query("SELECT id_fornecedor, razao_social, is_fabricante FROM tbl_fornecedores WHERE ativo = 1");
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Encontrados " . count($fornecedores) . " fornecedores/fabricantes\n\n";
    
    // Gerar SQL
    echo "📝 Importando materiais DIRETO no banco...\n";
    echo "   (Inserindo no catálogo + criando estoque em todas as filiais)\n\n";
    
    // Buscar todas as filiais ativas
    $stmtFiliais = $pdo->query("SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1");
    $filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC);
    echo "🏢 Filiais encontradas: " . count($filiais) . "\n";
    foreach ($filiais as $fil) {
        echo "   - Filial {$fil['id_filial']}: {$fil['nome_filial']}\n";
    }
    echo "\n";
    
    $stats = [
        'comUnidade' => 0, 
        'semUnidade' => 0, 
        'comFabricante' => 0, 
        'semFabricante' => 0,
        'materiaisInseridos' => 0,
        'estoquesInseridos' => 0,
        'erros' => 0
    ];
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    foreach ($materiais as $idx => $mat) {
        $codigo = addslashes($mat['codigo']);
        $nome = addslashes($mat['descricao']);
        $descricao = addslashes($mat['descricao']); // Usar a mesma descrição
        $marcaOriginal = $mat['marca'];
        $unidadeOriginal = $mat['unidade'];
        
        // Buscar unidade com LIKE
        $idUnidade = 1; // Default: UNIDADE
        $unidadeBusca = strtoupper($unidadeOriginal);
        
        if (strpos($unidadeBusca, 'UNIDADE') !== false || strpos($unidadeBusca, 'PECA') !== false || strpos($unidadeBusca, 'UN') !== false) {
            $idUnidade = 1; // UN
            $stats['comUnidade']++;
        } elseif (strpos($unidadeBusca, 'CAIXA') !== false || strpos($unidadeBusca, 'CX') !== false) {
            $idUnidade = 7; // CX
            $stats['comUnidade']++;
        } elseif (strpos($unidadeBusca, 'PACOTE') !== false || strpos($unidadeBusca, 'PCT') !== false) {
            $idUnidade = 8; // PCT
            $stats['comUnidade']++;
        } else {
            $idUnidade = 1; // Default
            $stats['semUnidade']++;
        }
        
        // Buscar categoria baseada na descrição ou marca com LIKE inteligente
        $categoriaSQL = "(SELECT id_categoria FROM tbl_categorias WHERE nome_categoria LIKE '%CLINICO%' LIMIT 1)"; // Default
        $descLower = strtolower($nome);
        $marcaLower = strtolower($marcaOriginal);
        
        // Mapping inteligente por palavras-chave
        $mappingCategorias = [
            'ANESTESICOS E AGULHA GENGIVAL' => ['anest', 'agulha', 'lidoca', 'mepivaca', 'articaine', 'prilocaina'],
            'ORTODONTIA' => ['ortodon', 'arco', 'bracket', 'braquete', 'fio ortodon', 'elastico', 'tubo', 'barra lingual'],
            'ENDODONTIA' => ['endo', 'lima', 'obtura', 'guta', 'condensador', 'localizador', 'pulp'],
            'BROCAS' => ['broca', 'ponta diamantada', 'fresa'],
            'DENTÍSTICA E ESTÉTICA' => ['resina', 'adesivo', 'cimento', 'clareador', 'composite', 'bond'],
            'BIOSSEGURANÇA' => ['luva', 'máscara', 'óculos', 'avental', 'gorro', 'propé', 'epi'],
            'DESCARTÁVEIS' => ['babador', 'copo', 'sugador', 'campo', 'descartável'],
            'CIRURGIA E PERIODONTIA' => ['bisturi', 'lâmina', 'fio cirúrgico', 'sutura', 'pinça', 'periost'],
            'HIGIENE ORAL' => ['escova', 'pasta', 'fio dental', 'enxaguante'],
            'RADIOLOGIA' => ['filme', 'radiograf', 'rx', 'sensor', 'revelador', 'fixador'],
            'MATERIAS DE LIMPEZA' => ['álcool', 'detergente', 'sabão', 'desinfet', 'limpeza', 'papel'],
            'IMPLANTODONTIA' => ['implante', 'pilar', 'parafuso protético', 'cicatrizador'],
            'PROTESE' => ['protese', 'prótese', 'coroa', 'ponte'],
            'MOLDAGEM E MODELO' => ['silicone', 'alginato', 'moldeira', 'gesso'],
        ];
        
        foreach ($mappingCategorias as $categoria => $palavrasChave) {
            foreach ($palavrasChave as $palavra) {
                if (strpos($descLower, $palavra) !== false || strpos($marcaLower, $palavra) !== false) {
                    $categoriaSQL = "(SELECT id_categoria FROM tbl_categorias WHERE nome_categoria LIKE '%{$categoria}%' LIMIT 1)";
                    break 2;
                }
            }
        }
        
        // Buscar fabricante com LIKE na marca
        $fabricanteSQL = "NULL";
        if (!empty($marcaOriginal)) {
            $marcaLike = addslashes($marcaOriginal);
            $fabricanteSQL = "(SELECT id_fornecedor FROM tbl_fornecedores WHERE razao_social LIKE '%{$marcaLike}%' AND ativo = 1 LIMIT 1)";
            $stats['comFabricante']++;
        } else {
            $stats['semFabricante']++;
        }
        
        // Buscar IDs de categoria e fabricante
        $idCategoria = null;
        if ($categoriaSQL != "NULL") {
            $stmtCat = $pdo->query(str_replace(['(', ')'], '', $categoriaSQL));
            $resCat = $stmtCat->fetch(PDO::FETCH_ASSOC);
            $idCategoria = $resCat ? $resCat['id_categoria'] : null;
        }
        
        $idFabricante = null;
        $idFornecedor = null;
        if (!empty($marcaOriginal)) {
            $marcaLike = addslashes($marcaOriginal);
            $stmtFab = $pdo->prepare("SELECT id_fornecedor FROM tbl_fornecedores WHERE razao_social LIKE ? AND ativo = 1 LIMIT 1");
            $stmtFab->execute(["%{$marcaOriginal}%"]);
            $resFab = $stmtFab->fetch(PDO::FETCH_ASSOC);
            $idFabricante = $resFab ? $resFab['id_fornecedor'] : null;
            $idFornecedor = $idFabricante;
        }
        
        $observacoes = "Marca: {$marcaOriginal} | Unidade Original: {$unidadeOriginal}";
        
        try {
            // 1. INSERIR NO CATÁLOGO
            $sqlInsertCatalogo = "INSERT INTO tbl_catalogo_materiais (
                codigo, nome, descricao, id_categoria, id_fornecedor, id_fabricante,
                id_unidade, preco_unitario_padrao, estoque_minimo_padrao, estoque_maximo_padrao,
                observacoes, ativo, data_criacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, 10, 100, ?, 1, NOW())";
            
            $stmtCatalogo = $pdo->prepare($sqlInsertCatalogo);
            $stmtCatalogo->execute([
                $mat['codigo'],
                $mat['descricao'],
                $mat['descricao'],
                $idCategoria,
                $idFornecedor,
                $idFabricante,
                $idUnidade,
                $observacoes
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
    echo "✅ IMPORTAÇÃO CONCLUÍDA!\n";
    echo str_repeat("=", 76) . "\n\n";
    
    echo "📈 ESTATÍSTICAS FINAIS:\n";
    echo "   ├─ Materiais importados: " . $stats['materiaisInseridos'] . "\n";
    echo "   ├─ Estoques criados: " . $stats['estoquesInseridos'] . "\n";
    echo "   ├─ Com unidade mapeada: " . $stats['comUnidade'] . "\n";
    echo "   ├─ Sem unidade (default UN): " . $stats['semUnidade'] . "\n";
    echo "   ├─ Com fabricante: " . $stats['comFabricante'] . "\n";
    echo "   ├─ Sem fabricante: " . $stats['semFabricante'] . "\n";
    echo "   └─ Erros: " . $stats['erros'] . "\n\n";
    
    echo "🔍 VERIFICAÇÃO:\n";
    $stmtVerif = $pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE ativo = 1");
    $totalCatalogo = $stmtVerif->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   ├─ Total no catálogo: {$totalCatalogo}\n";
    
    $stmtVerifEstoque = $pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
    $totalEstoque = $stmtVerifEstoque->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   └─ Total de estoques: {$totalEstoque}\n\n";
    
    echo "✅ Materiais importados e estoque criado em todas as filiais!\n";
    echo "🎯 Acesse material.php para visualizar os materiais.\n";
    
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

