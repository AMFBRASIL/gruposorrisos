<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST' || $method === 'GET') {
        // Inicia a sessão
        session_start();
        
        // Verifica se o usuário está logado
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // Registra o logout
            $usuarioId = $_SESSION['user_id'] ?? null;
            $usuarioNome = $_SESSION['user_name'] ?? 'Desconhecido';
            
            logLogout($usuarioId, $usuarioNome, $_SERVER['REMOTE_ADDR']);
            
            // Limpa todas as variáveis da sessão
            $_SESSION = array();
            
            // Destrói a sessão
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            
            echo json_encode([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
                'redirect_url' => 'login.php'
            ]);
            
        } else {
            echo json_encode(['success' => false, 'error' => 'Usuário não estava logado']);
        }
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}

// Função para registrar log de logout
function logLogout($usuarioId, $usuarioNome, $ip) {
    try {
        $pdo = Conexao::getInstance()->getPdo();
        $sql = "INSERT INTO tbl_logs_sistema (id_usuario, acao, dados_novos, ip_usuario) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $dados = [
            'usuario_nome' => $usuarioNome,
            'timestamp' => date('Y-m-d H:i:s'),
            'session_duration' => isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) : 0
        ];
        
        $stmt->execute([
            $usuarioId,
            'LOGOUT',
            json_encode($dados),
            $ip
        ]);
    } catch (Exception $e) {
        // Silenciosamente ignora erros de log
    }
} 