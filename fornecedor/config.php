<?php
/**
 * Configurações do Sistema de Fornecedores
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'grupo_sorrisos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da aplicação
define('APP_NAME', 'Sistema de Fornecedores - Grupo Sorrisos');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/sistemas/_estoquegrupoSorrisos');

// Configurações de email
define('EMAIL_FROM', 'compras@gruposorrisos.com.br');
define('EMAIL_FROM_NAME', 'Sistema de Compras - Grupo Sorrisos');

// Configurações de sessão
define('SESSION_NAME', 'fornecedor_session');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configurações de upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Configurações de paginação
define('ITEMS_PER_PAGE', 20);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de debug
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);

// Função para debug
function debug($data, $exit = false) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($exit) exit;
    }
}

// Função para log de erros
function logError($message, $context = []) {
    if (LOG_ERRORS) {
        $logFile = __DIR__ . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

// Função para sanitizar input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para gerar token seguro
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['fornecedor_id']) && !empty($_SESSION['fornecedor_id']);
}

// Função para redirecionar
function redirect($url) {
    header("Location: {$url}");
    exit;
}

// Função para obter URL base
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['REQUEST_URI']);
    
    return $protocol . '://' . $host . $path;
}

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Configurações de erro
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações de timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('America/Sao_Paulo');
}

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Verificar se é uma requisição HTTPS em produção
if (!DEBUG_MODE && !isset($_SERVER['HTTPS'])) {
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    redirect($redirectUrl);
}
?> 