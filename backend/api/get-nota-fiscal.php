<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexao.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json');

try {
    $pedidoId = $_GET['pedido_id'] ?? null;
    
    if (!$pedidoId) {
        throw new Exception('ID do pedido não fornecido');
    }
    
    // Buscar pedido
    require_once __DIR__ . '/../../config/conexao.php';
    $pdo = Conexao::getInstance()->getPdo();
    $stmt = $pdo->prepare("SELECT id_pedido, url_nota_fiscal FROM tbl_pedidos_compra WHERE id_pedido = ? AND ativo = 1");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido não encontrado');
    }
    
    if (empty($pedido['url_nota_fiscal'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Nota Fiscal não encontrada para este pedido'
        ]);
        exit;
    }
    
    // URL já é relativa à raiz (ex: /uploads/notas-fiscais/...)
    // Construir URL completa incluindo o caminho base do projeto
    $url = $pedido['url_nota_fiscal'];
    
    // Garantir que a URL começa com / (relativa à raiz do site)
    if (empty($url)) {
        throw new Exception('URL da Nota Fiscal está vazia');
    }
    
    // Remover qualquer "../" ou caminhos relativos
    $url = str_replace('../', '', $url);
    $url = str_replace('./', '', $url);
    
    // Garantir que começa com /
    if ($url[0] !== '/') {
        $url = '/' . $url;
    }
    
    // Remover barras duplicadas
    $url = preg_replace('#/+#', '/', $url);
    
    // Obter o caminho base do projeto
    // __DIR__ = /caminho/completo/sistemas/_estoquegrupoSorrisos/backend/api
    // Voltar 2 níveis para chegar na raiz do projeto
    $projectRoot = dirname(dirname(__DIR__));
    
    // Obter o caminho relativo do projeto a partir da raiz do servidor web
    // Usar SCRIPT_NAME para obter o caminho relativo
    $scriptPath = $_SERVER['SCRIPT_NAME']; // Ex: /sistemas/_estoquegrupoSorrisos/backend/api/get-nota-fiscal.php
    $basePath = dirname(dirname(dirname($scriptPath))); // Volta 3 níveis: /sistemas/_estoquegrupoSorrisos
    
    // Se o basePath não for apenas "/", adicionar antes da URL
    if ($basePath !== '/' && $basePath !== '' && $basePath !== '.') {
        // Remover barra final se houver
        $basePath = rtrim($basePath, '/');
        // Adicionar o caminho base antes da URL
        $url = $basePath . $url;
    }
    
    // Construir URL completa
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fullUrl = $protocol . '://' . $host . $url;
    
    echo json_encode([
        'success' => true,
        'url' => $fullUrl
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
