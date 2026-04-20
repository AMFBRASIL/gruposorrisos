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
require_once '../../models/Usuario.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $usuarioModel = new Usuario($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Recebe os dados do POST
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Se não conseguiu decodificar JSON, tenta dados do formulário
        if (!$input) {
            $input = $_POST;
        }
        
        // Valida os dados de entrada
        $errors = validateInput($input);
        
        if (!empty($errors)) {
            logLoginAttempt($input['email'] ?? 'unknown', false, $_SERVER['REMOTE_ADDR']);
            echo json_encode(['success' => false, 'error' => 'Dados inválidos', 'errors' => $errors]);
            exit;
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        
        try {
            // Usa o model Usuario que já atualiza o ultimo_acesso automaticamente
            $user = $usuarioModel->autenticar($email, $password);
            
            if (!$user) {
                logLoginAttempt($email, false, $_SERVER['REMOTE_ADDR']);
                echo json_encode(['success' => false, 'error' => 'Email ou senha inválidos']);
                exit;
            }
            
            // Login bem-sucedido
            logLoginAttempt($email, true, $_SERVER['REMOTE_ADDR'], $user['id_usuario']);
            
            // Inicia a sessão
            session_start();
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['nome_completo'];
            $_SESSION['usuario_email'] = $user['email'];
            $_SESSION['usuario_perfil'] = $user['nome_perfil'];
            $_SESSION['usuario_perfil_id'] = $user['id_perfil'];
            $_SESSION['usuario_filial_id'] = $user['id_filial'];
            $_SESSION['usuario_filial'] = $user['nome_filial'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Remove a senha dos dados retornados
            unset($user['senha']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            logLoginAttempt($email, false, $_SERVER['REMOTE_ADDR']);
            echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}

// Função para validar dados de entrada
function validateInput($data) {
    $errors = [];
    
    if (empty($data['email'])) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($data['password'])) {
        $errors[] = 'Senha é obrigatória';
    }
    
    return $errors;
}

// Função para registrar log de tentativa de login
function logLoginAttempt($email, $success, $ip, $userId = null) {
    try {
        $pdo = Conexao::getInstance()->getPdo();
        $sql = "INSERT INTO tbl_logs_sistema (id_usuario, acao, dados_novos, ip_usuario) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $dados = [
            'email' => $email,
            'success' => $success,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $stmt->execute([
            $userId,
            $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED',
            json_encode($dados),
            $ip
        ]);
    } catch (Exception $e) {
        // Silenciosamente ignora erros de log
    }
} 