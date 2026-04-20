<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/session.php';
require_once '../controllers/ControllerAcesso.php';

try {
    // Verificar se usuário está logado
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'error' => 'Usuário não está logado'
        ]);
        exit;
    }
    
    // Inicializar controller de acesso
    $controllerAcesso = new ControllerAcesso();
    
    // Obter primeira página permitida
    $primeiraPagina = $controllerAcesso->obterPrimeiraPaginaPermitida();
    
    if ($primeiraPagina) {
        echo json_encode([
            'success' => true,
            'redirect_url' => $primeiraPagina,
            'message' => 'Redirecionamento configurado com sucesso'
        ]);
    } else {
        // Fallback para index.php se não houver páginas permitidas
        echo json_encode([
            'success' => true,
            'redirect_url' => 'index.php',
            'message' => 'Usando página padrão (index.php)',
            'fallback' => true
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 