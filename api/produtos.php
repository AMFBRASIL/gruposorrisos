<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/conexao.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Verificar se o usuário está logado
    if (!isLoggedIn()) {
        error_log('❌ API produtos.php: Usuário não está logado');
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit;
    }
    
    error_log('✅ API produtos.php: Usuário logado - ID: ' . ($_SESSION['usuario_id'] ?? 'NULL'));
    error_log('✅ API produtos.php: Usuário filial ID: ' . ($_SESSION['usuario_filial_id'] ?? 'NULL'));
    
    // Verificar o tipo de requisição
    $path = $_GET['path'] ?? '';
    error_log('🔍 API produtos.php: Path solicitado = ' . $path);
    
    switch ($path) {
        case 'estatisticas':
            // Estatísticas do dashboard
            $stats = getDashboardStats($pdo);
            echo json_encode($stats);
            break;
            
        case 'estoque-baixo':
            // Produtos com estoque baixo
            error_log('🔍 API produtos.php: Endpoint estoque-baixo chamado');
            $produtos = getProdutosEstoqueBaixo($pdo);
            error_log('📦 API produtos.php: Produtos encontrados: ' . count($produtos));
            echo json_encode(['success' => true, 'data' => $produtos]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint não encontrado']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

/**
 * Obtém estatísticas do dashboard
 */
function getDashboardStats($pdo) {
    // IMPORTANTE: Priorizar filial_id da URL (filial selecionada pelo usuário) sobre a da sessão
    $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
    if ($filialId) {
        $filialId = (int)$filialId;
    }
    
    error_log('📊 getDashboardStats: Filial ID = ' . ($filialId ?? 'NULL'));
    
    // Total de produtos (apenas da filial selecionada)
    $sql = "SELECT COUNT(DISTINCT cm.id_catalogo) as total 
            FROM tbl_catalogo_materiais cm 
            INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
            WHERE cm.ativo = 1 AND ef.ativo = 1";
    $params = [];
    
    if ($filialId) {
        $sql .= " AND ef.id_filial = ?";
        $params[] = $filialId;
    }
    
    error_log('📊 Total Produtos SQL: ' . $sql);
    error_log('📊 Total Produtos Params: ' . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $totalProdutos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    error_log('📊 Total Produtos Resultado: ' . $totalProdutos);
    
    // Produtos com estoque baixo (apenas da filial selecionada)
    // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo da filial sobre valores padrão
    $sql = "SELECT COUNT(DISTINCT cm.id_catalogo) as total 
            FROM tbl_catalogo_materiais cm 
            INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
            WHERE cm.ativo = 1 AND ef.ativo = 1 
            AND ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AND ef.estoque_atual > 0";
    $params = [];
    if ($filialId) {
        $sql .= " AND ef.id_filial = ?";
        $params[] = $filialId;
    }
    
    error_log('📊 Estoque Baixo SQL: ' . $sql);
    error_log('📊 Estoque Baixo Params: ' . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $estoqueBaixo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    error_log('📊 Estoque Baixo Resultado: ' . $estoqueBaixo);
    
    // Produtos com estoque zerado (apenas da filial selecionada)
    $sql = "SELECT COUNT(DISTINCT cm.id_catalogo) as total 
            FROM tbl_catalogo_materiais cm 
            INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
            WHERE cm.ativo = 1 AND ef.ativo = 1 
            AND ef.estoque_atual = 0";
    $params = [];
    if ($filialId) {
        $sql .= " AND ef.id_filial = ?";
        $params[] = $filialId;
    }
    
    error_log('📊 Estoque Zerado SQL: ' . $sql);
    error_log('📊 Estoque Zerado Params: ' . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $estoqueZerado = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    error_log('📊 Estoque Zerado Resultado: ' . $estoqueZerado);
    
    // Valor total em estoque (apenas da filial selecionada)
    // IMPORTANTE: Usar COALESCE para priorizar preço da filial sobre preço padrão
    $sql = "SELECT SUM(ef.estoque_atual * COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0)) as valor_total 
            FROM tbl_catalogo_materiais cm 
            INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
            WHERE cm.ativo = 1 AND ef.ativo = 1 
            AND ef.estoque_atual > 0";
    $params = [];
    if ($filialId) {
        $sql .= " AND ef.id_filial = ?";
        $params[] = $filialId;
    }
    
    error_log('📊 Valor Total SQL: ' . $sql);
    error_log('📊 Valor Total Params: ' . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $valorTotal = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;
    
    error_log('📊 Valor Total Resultado: ' . $valorTotal);
    
    $resultado = [
        'success' => true,
        'total_produtos' => (int)$totalProdutos,
        'produtos_estoque_baixo' => (int)$estoqueBaixo,
        'produtos_estoque_zerado' => (int)$estoqueZerado,
        'valor_total_custo' => (float)$valorTotal
    ];
    
    error_log('📊 Resultado Final: ' . json_encode($resultado));
    
    return $resultado;
}

/**
 * Obtém produtos com estoque baixo
 */
function getProdutosEstoqueBaixo($pdo) {
    // IMPORTANTE: Priorizar filial_id da URL (filial selecionada pelo usuário) sobre a da sessão
    $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
    if ($filialId) {
        $filialId = (int)$filialId;
    }
    error_log('🔍 getProdutosEstoqueBaixo: Filial ID = ' . ($filialId ?? 'NULL'));
    
    // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo da filial sobre valores padrão
    $sql = "SELECT cm.id_catalogo as id_material, cm.nome, ef.estoque_atual, 
            COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo,
                   c.nome_categoria as categoria_nome, um.sigla as unidade
            FROM tbl_catalogo_materiais cm 
            INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
            LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria 
            LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
            WHERE cm.ativo = 1 AND ef.ativo = 1 
            AND ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AND ef.estoque_atual > 0";
    
    $params = [];
    if ($filialId) {
        $sql .= " AND ef.id_filial = ?";
        $params[] = $filialId;
    }
    
    $sql .= " ORDER BY ef.estoque_atual ASC LIMIT 10";
    
    error_log('🔍 getProdutosEstoqueBaixo: SQL = ' . $sql);
    error_log('🔍 getProdutosEstoqueBaixo: Parâmetros = ' . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('📦 getProdutosEstoqueBaixo: Resultado encontrado = ' . count($resultado));
    
    return $resultado;
}
?> 