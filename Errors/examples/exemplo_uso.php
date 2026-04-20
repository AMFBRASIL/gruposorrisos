<?php
/**
 * Exemplo prático de uso do Sistema de Estoque
 * Demonstra como usar os modelos para operações comuns
 */

require_once __DIR__ . '/../config/autoload.php';
loadConfig();

echo "=== EXEMPLO DE USO DO SISTEMA DE ESTOQUE ===\n\n";

try {
    // 1. CARREGANDO OS MODELOS
    echo "1. Carregando modelos...\n";
    $material = new Material();
    $movimentacao = new Movimentacao();
    $filial = new Filial();
    $categoria = new Categoria();
    $fornecedor = new Fornecedor();
    $unidade = new UnidadeMedida();
    $tipoMovimentacao = new TipoMovimentacao();
    echo "✅ Modelos carregados com sucesso!\n\n";
    
    // 2. BUSCANDO DADOS BÁSICOS
    echo "2. Buscando dados básicos...\n";
    
    // Filiais ativas
    $filiais = $filial->findAtivas();
    echo "   - Filiais ativas: " . count($filiais) . "\n";
    
    // Categorias
    $categorias = $categoria->findAll();
    echo "   - Categorias: " . count($categorias) . "\n";
    
    // Fornecedores
    $fornecedores = $fornecedor->findAll();
    echo "   - Fornecedores: " . count($fornecedores) . "\n";
    
    // Unidades de medida
    $unidades = $unidade->findAll();
    echo "   - Unidades de medida: " . count($unidades) . "\n";
    
    // Tipos de movimentação
    $tipos = $tipoMovimentacao->findAll();
    echo "   - Tipos de movimentação: " . count($tipos) . "\n";
    echo "✅ Dados básicos carregados!\n\n";
    
    // 3. EXEMPLO: CADASTRANDO UM MATERIAL
    echo "3. Exemplo: Cadastrando um material...\n";
    
    if (!empty($filiais) && !empty($categorias) && !empty($unidades)) {
        $dadosMaterial = [
            'codigo' => 'PAPEL001',
            'nome' => 'Papel A4 75g',
            'descricao' => 'Papel A4 branco 75g, pacote com 500 folhas',
            'id_categoria' => $categorias[0]['id_categoria'],
            'id_filial' => $filiais[0]['id_filial'],
            'id_unidade' => $unidades[0]['id_unidade'],
            'preco_unitario' => 25.90,
            'estoque_minimo' => 10,
            'estoque_maximo' => 200,
            'estoque_atual' => 0,
            'localizacao_estoque' => 'Prateleira A1 - Gaveta 1',
            'observacoes' => 'Material de escritório essencial'
        ];
        
        // Verifica se o material já existe
        if (!$material->codigoExiste($dadosMaterial['codigo'], $dadosMaterial['id_filial'])) {
            $idMaterial = $material->insert($dadosMaterial);
            echo "   ✅ Material cadastrado com ID: $idMaterial\n";
            
            // 4. EXEMPLO: REGISTRANDO UMA ENTRADA
            echo "4. Exemplo: Registrando entrada de estoque...\n";
            
            // Busca tipo de entrada
            $tiposEntrada = $tipoMovimentacao->findEntradas();
            if (!empty($tiposEntrada)) {
                $dadosEntrada = [
                    'id_filial' => $dadosMaterial['id_filial'],
                    'id_material' => $idMaterial,
                    'id_tipo_movimentacao' => $tiposEntrada[0]['id_tipo_movimentacao'],
                    'id_usuario' => 1, // Usuário padrão
                    'quantidade' => 50,
                    'preco_unitario' => 25.90,
                    'numero_documento' => 'NF2024001',
                    'observacoes' => 'Entrada inicial de estoque'
                ];
                
                $idMovimentacao = $movimentacao->registrarMovimentacao($dadosEntrada);
                echo "   ✅ Entrada registrada com ID: $idMovimentacao\n";
                
                // 5. VERIFICANDO O ESTOQUE ATUALIZADO
                echo "5. Verificando estoque atualizado...\n";
                $materialAtualizado = $material->findById($idMaterial);
                echo "   - Estoque atual: {$materialAtualizado['estoque_atual']}\n";
                echo "   - Valor total em estoque: R$ " . number_format($materialAtualizado['estoque_atual'] * $materialAtualizado['preco_unitario'], 2, ',', '.') . "\n";
                
                // 6. EXEMPLO: REGISTRANDO UMA SAÍDA
                echo "6. Exemplo: Registrando saída de estoque...\n";
                
                // Busca tipo de saída
                $tiposSaida = $tipoMovimentacao->findSaidas();
                if (!empty($tiposSaida)) {
                    $dadosSaida = [
                        'id_filial' => $dadosMaterial['id_filial'],
                        'id_material' => $idMaterial,
                        'id_tipo_movimentacao' => $tiposSaida[0]['id_tipo_movimentacao'],
                        'id_usuario' => 1,
                        'quantidade' => 5,
                        'numero_documento' => 'REQ001',
                        'observacoes' => 'Saída para uso interno'
                    ];
                    
                    $idMovimentacaoSaida = $movimentacao->registrarMovimentacao($dadosSaida);
                    echo "   ✅ Saída registrada com ID: $idMovimentacaoSaida\n";
                    
                    // Verifica estoque após saída
                    $materialFinal = $material->findById($idMaterial);
                    echo "   - Estoque após saída: {$materialFinal['estoque_atual']}\n";
                }
            }
        } else {
            echo "   ℹ️  Material já existe no sistema\n";
        }
    }
    
    // 7. EXEMPLO: CONSULTAS E RELATÓRIOS
    echo "\n7. Exemplo: Consultas e relatórios...\n";
    
    // Materiais com estoque baixo
    $estoqueBaixo = $material->findEstoqueBaixo();
    echo "   - Materiais com estoque baixo: " . count($estoqueBaixo) . "\n";
    
    // Materiais com estoque zerado
    $estoqueZerado = $material->findEstoqueZerado();
    echo "   - Materiais com estoque zerado: " . count($estoqueZerado) . "\n";
    
    // Movimentações recentes
    $movimentacoesRecentes = $movimentacao->findByFilial($filiais[0]['id_filial'], 5);
    echo "   - Últimas 5 movimentações: " . count($movimentacoesRecentes) . "\n";
    
    // 8. EXEMPLO: FILTROS AVANÇADOS
    echo "\n8. Exemplo: Filtros avançados...\n";
    
    $filtros = [
        'id_filial' => $filiais[0]['id_filial'],
        'estoque_baixo' => true
    ];
    
    $resultadoFiltrado = $material->findWithFilters($filtros, 1, 10);
    echo "   - Materiais com filtros: {$resultadoFiltrado['total']} encontrados\n";
    echo "   - Página atual: {$resultadoFiltrado['page']} de {$resultadoFiltrado['total_pages']}\n";
    
    // 9. EXEMPLO: ESTATÍSTICAS
    echo "\n9. Exemplo: Estatísticas...\n";
    
    if (!empty($filiais)) {
        $estatisticasFilial = $filial->getEstatisticas($filiais[0]['id_filial']);
        echo "   - Total de materiais na filial: {$estatisticasFilial['total_materiais']}\n";
        echo "   - Materiais com estoque baixo: {$estatisticasFilial['materiais_estoque_baixo']}\n";
        echo "   - Materiais com estoque zerado: {$estatisticasFilial['materiais_estoque_zerado']}\n";
        echo "   - Movimentações hoje: {$estatisticasFilial['movimentacoes_hoje']}\n";
    }
    
    // 10. EXEMPLO: BUSCA POR CÓDIGO
    echo "\n10. Exemplo: Busca por código...\n";
    
    $materialPorCodigo = $material->findByCodigo('PAPEL001', $filiais[0]['id_filial']);
    if ($materialPorCodigo) {
        echo "   ✅ Material encontrado: {$materialPorCodigo[0]['nome']}\n";
        echo "   - Categoria: {$materialPorCodigo[0]['nome_categoria']}\n";
        echo "   - Unidade: {$materialPorCodigo[0]['unidade_sigla']}\n";
        echo "   - Estoque: {$materialPorCodigo[0]['estoque_atual']}\n";
    } else {
        echo "   ℹ️  Material não encontrado\n";
    }
    
    echo "\n=== EXEMPLO CONCLUÍDO COM SUCESSO! ===\n";
    echo "O sistema está funcionando corretamente.\n";
    
} catch (Exception $e) {
    echo "❌ Erro no exemplo: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DICAS DE USO ===\n";
echo "1. Sempre verifique se os dados básicos existem antes de inserir materiais\n";
echo "2. Use transações para operações críticas\n";
echo "3. Valide os dados antes de inserir\n";
echo "4. Use os filtros para consultas eficientes\n";
echo "5. Monitore o estoque regularmente\n";
echo "6. Mantenha os dados de fornecedores atualizados\n";
echo "7. Use códigos únicos para materiais\n";
echo "8. Documente as movimentações com observações\n";
?> 