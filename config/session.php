<?php
/**
 * Gerenciamento de Sessões
 * Funções para verificar autenticação e gerenciar sessões
 */

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Verifica se o usuário tem perfil específico
 */
function hasProfile($perfil) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $perfilUsuario = $_SESSION['usuario_perfil'] ?? '';
    return strtolower($perfilUsuario) === strtolower($perfil);
}

/**
 * Verifica se o usuário é administrador
 */
function isAdmin() {
    return hasProfile('Administrador');
}

/**
 * Verifica se o usuário é gerente
 */
function isGerente() {
    return hasProfile('Gerente') || isAdmin();
}

/**
 * Verifica se o usuário é operador
 */
function isOperador() {
    return hasProfile('Operador') || isGerente();
}

/**
 * Verifica se o usuário é visualizador
 */
function isVisualizador() {
    return hasProfile('Visualizador') || isOperador();
}

/**
 * Redireciona para login se não estiver autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redireciona para página de erro se não tiver permissão
 */
function requireProfile($perfil) {
    requireLogin();
    
    if (!hasProfile($perfil)) {
        header('Location: error.php?message=Acesso negado');
        exit();
    }
}

/**
 * Redireciona para página de erro se não for administrador
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: error.php?message=Acesso negado - Administrador necessário');
        exit();
    }
}

/**
 * Redireciona para página de erro se não for gerente
 */
function requireGerente() {
    requireLogin();
    
    if (!isGerente()) {
        header('Location: error.php?message=Acesso negado - Gerente necessário');
        exit();
    }
}

/**
 * Redireciona para página de erro se não for operador
 */
function requireOperador() {
    requireLogin();
    
    if (!isOperador()) {
        header('Location: error.php?message=Acesso negado - Operador necessário');
        exit();
    }
}

/**
 * Obtém dados do usuário logado
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? null,
        'email' => $_SESSION['usuario_email'] ?? null,
        'perfil' => $_SESSION['usuario_perfil'] ?? null,
        'filial' => $_SESSION['usuario_filial'] ?? null,
        'filial_id' => $_SESSION['usuario_filial_id'] ?? null,
        'filial_codigo' => $_SESSION['usuario_filial_codigo'] ?? null
    ];
}

/**
 * Obtém ID da filial do usuário logado
 */
function getCurrentUserFilialId() {
    return $_SESSION['usuario_filial_id'] ?? null;
}

/**
 * Obtém nome da filial do usuário logado
 */
function getCurrentUserFilial() {
    return $_SESSION['usuario_filial'] ?? null;
}

/**
 * Obtém perfil do usuário logado
 */
function getCurrentUserProfile() {
    return $_SESSION['usuario_perfil'] ?? null;
}

/**
 * Obtém nome do usuário logado
 */
function getCurrentUserName() {
    return $_SESSION['usuario_nome'] ?? null;
}

/**
 * Obtém email do usuário logado
 */
function getCurrentUserEmail() {
    return $_SESSION['usuario_email'] ?? null;
}

/**
 * Registra log de atividade
 */
function logActivity($acao, $dados = null, $tabela = null, $registroId = null) {
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        require_once 'conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
        
        $sql = "INSERT INTO tbl_logs_sistema (id_usuario, id_filial, acao, tabela, id_registro, dados_novos, ip_usuario) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['usuario_id'],
            $_SESSION['usuario_filial_id'],
            $acao,
            $tabela,
            $registroId,
            $dados ? json_encode($dados) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        return true;
    } catch (Exception $e) {
        // Silenciosamente ignora erros de log
        return false;
    }
}

/**
 * Verifica se a sessão expirou
 */
function checkSessionExpiration() {
    $maxSessionTime = 8 * 60 * 60; // 8 horas em segundos
    
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $maxSessionTime) {
        // Sessão expirou
        session_destroy();
        return false;
    }
    
    // Atualiza o tempo de login
    $_SESSION['login_time'] = time();
    return true;
}

/**
 * Força logout do usuário
 */
function forceLogout() {
    session_destroy();
    header('Location: login.php?message=Sessão expirada');
    exit();
}

/**
 * Verifica e renova a sessão se necessário
 */
function checkAndRenewSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!checkSessionExpiration()) {
        forceLogout();
        return false;
    }
    
    return true;
}
?> 