<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/conexao.php';
require_once '../../config/session.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Se não conseguir decodificar JSON, tentar $_POST
if ($input === null) {
    $input = $_POST;
}

$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar_mensagens':
            echo json_encode(listarMensagens($pdo, $input));
            break;
            
        case 'enviar_mensagem':
            echo json_encode(enviarMensagem($pdo, $input));
            break;
            
        case 'marcar_como_lida':
            echo json_encode(marcarComoLida($pdo, $input));
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ação não especificada ou inválida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

/**
 * Lista as mensagens de um pedido específico
 */
function listarMensagens($pdo, $input) {
    $pedido_id = $input['pedido_id'] ?? 0;
    
    // Log para debug
    error_log("listarMensagens chamado - pedido_id: {$pedido_id}, usuario_id: " . ($_SESSION['usuario_id'] ?? 'não definido'));
    
    if (!$pedido_id) {
        error_log("Erro: ID do pedido não fornecido");
        return ['success' => false, 'error' => 'ID do pedido é obrigatório'];
    }
    
    // Verificar se o usuário tem acesso ao pedido
    if (!verificarAcessoPedido($pdo, $pedido_id, $_SESSION['usuario_id'])) {
        error_log("Acesso negado ao pedido {$pedido_id} para usuário {$_SESSION['usuario_id']}");
        return ['success' => false, 'error' => 'Acesso negado ao pedido'];
    }
    
    try {
        $sql = "SELECT 
                    c.id_mensagem,
                    c.mensagem,
                    c.data_envio,
                    c.tipo_usuario,
                    c.lida,
                    c.id_usuario_remetente,
                    u.nome_completo as nome_remetente
                FROM tbl_chat_pedidos c
                LEFT JOIN tbl_usuarios u ON c.id_usuario_remetente = u.id_usuario
                WHERE c.id_pedido = ? AND (c.ativo = 1 OR c.ativo IS NULL OR c.ativo = '1')
                ORDER BY c.data_envio ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pedido_id]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Mensagens encontradas para pedido {$pedido_id}: " . count($mensagens));
        
        // Formatar as mensagens
        $perfil_id = $_SESSION['usuario_perfil_id'] ?? null;
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $tipo_usuario_atual = getTipoUsuario($perfil_id);
        
        error_log("Tipo de usuário atual: {$tipo_usuario_atual}, Perfil ID: {$perfil_id}, Usuário ID: {$usuario_id}");
        
        foreach ($mensagens as &$mensagem) {
            $mensagem['data_envio_formatada'] = date('d/m/Y H:i', strtotime($mensagem['data_envio']));
            
            // Verificar se a mensagem é do usuário atual
            // Comparar pelo id_usuario_remetente (mais confiável) e também pelo tipo_usuario
            $mensagem_tipo = $mensagem['tipo_usuario'] ?? '';
            $mensagem_remetente_id = $mensagem['id_usuario_remetente'] ?? null;
            
            // A mensagem é "minha" se o remetente for o usuário atual
            $mensagem['eh_minha'] = ($mensagem_remetente_id == $usuario_id);
            
            error_log("Mensagem ID {$mensagem['id_mensagem']}: remetente_id={$mensagem_remetente_id}, usuario_atual={$usuario_id}, tipo={$mensagem_tipo}, eh_minha=" . ($mensagem['eh_minha'] ? 'true' : 'false'));
        }
        
        return [
            'success' => true,
            'mensagens' => $mensagens
        ];
    } catch (Exception $e) {
        error_log("Erro ao listar mensagens: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro ao buscar mensagens: ' . $e->getMessage()];
    }
}

/**
 * Envia uma nova mensagem
 */
function enviarMensagem($pdo, $input) {
    $pedido_id = $input['pedido_id'] ?? 0;
    $mensagem = trim($input['mensagem'] ?? '');
    
    if (!$pedido_id || !$mensagem) {
        return ['success' => false, 'error' => 'ID do pedido e mensagem são obrigatórios'];
    }
    
    // Verificar se o usuário tem acesso ao pedido
    if (!verificarAcessoPedido($pdo, $pedido_id, $_SESSION['usuario_id'])) {
        return ['success' => false, 'error' => 'Acesso negado ao pedido'];
    }
    
    $tipo_usuario = getTipoUsuario($_SESSION['usuario_perfil_id']);
    
    $sql = "INSERT INTO tbl_chat_pedidos 
            (id_pedido, id_usuario_remetente, tipo_usuario, mensagem) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $pedido_id,
        $_SESSION['usuario_id'],
        $tipo_usuario,
        $mensagem
    ]);
    
    if ($success) {
        $id_mensagem = $pdo->lastInsertId();
        
        // Buscar a mensagem recém-criada para retornar
        $sql_select = "SELECT 
                        c.id_mensagem,
                        c.mensagem,
                        c.data_envio,
                        c.tipo_usuario,
                        c.lida,
                        u.nome_completo as nome_remetente
                    FROM tbl_chat_pedidos c
                    LEFT JOIN tbl_usuarios u ON c.id_usuario_remetente = u.id_usuario
                    WHERE c.id_mensagem = ?";
        
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute([$id_mensagem]);
        $nova_mensagem = $stmt_select->fetch(PDO::FETCH_ASSOC);
        
        if ($nova_mensagem) {
            $nova_mensagem['data_envio_formatada'] = date('d/m/Y H:i', strtotime($nova_mensagem['data_envio']));
            $nova_mensagem['eh_minha'] = true;
        }
        
        return [
            'success' => true,
            'message' => 'Mensagem enviada com sucesso',
            'mensagem' => $nova_mensagem
        ];
    } else {
        return ['success' => false, 'error' => 'Erro ao enviar mensagem'];
    }
}

/**
 * Marca mensagens como lidas
 */
function marcarComoLida($pdo, $input) {
    $pedido_id = $input['pedido_id'] ?? 0;
    
    if (!$pedido_id) {
        return ['success' => false, 'error' => 'ID do pedido é obrigatório'];
    }
    
    // Verificar se o usuário tem acesso ao pedido
    if (!verificarAcessoPedido($pdo, $pedido_id, $_SESSION['usuario_id'])) {
        return ['success' => false, 'error' => 'Acesso negado ao pedido'];
    }
    
    $tipo_usuario = getTipoUsuario($_SESSION['usuario_perfil_id']);
    
    // Marcar como lidas apenas as mensagens que NÃO são do usuário atual
    $sql = "UPDATE tbl_chat_pedidos 
            SET lida = 1 
            WHERE id_pedido = ? 
            AND tipo_usuario != ? 
            AND lida = 0";
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$pedido_id, $tipo_usuario]);
    
    return [
        'success' => $success,
        'message' => $success ? 'Mensagens marcadas como lidas' : 'Erro ao marcar mensagens como lidas'
    ];
}

/**
 * Verifica se o usuário tem acesso ao pedido
 */
function verificarAcessoPedido($pdo, $pedido_id, $usuario_id) {
    try {
        // Verificar se o pedido existe
        $sql = "SELECT id_pedido, id_fornecedor, ativo FROM tbl_pedidos_compra WHERE id_pedido = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            error_log("Pedido não encontrado: {$pedido_id}");
            return false;
        }
        
        // Verificar se o pedido está ativo
        if ($pedido['ativo'] != 1 && $pedido['ativo'] !== null) {
            error_log("Pedido inativo: {$pedido_id}");
            return false;
        }
        
        // Verificar se é fornecedor
        $perfil_id = $_SESSION['usuario_perfil_id'] ?? null;
        $perfil_nome = $_SESSION['usuario_perfil'] ?? '';
        
        // Verificar se o usuário é fornecedor (pode ser pelo perfil_id ou pelo nome do perfil)
        $ehFornecedor = false;
        if (strtolower($perfil_nome) === 'fornecedor' || $perfil_id == 5) { // Perfil 5 é Fornecedor conforme banco
            $ehFornecedor = true;
        }
        
        if ($ehFornecedor) {
            // Para fornecedores, verificar se o pedido pertence ao fornecedor vinculado ao usuário
            $sql_fornecedor = "SELECT id_fornecedor FROM tbl_usuarios WHERE id_usuario = ?";
            $stmt_fornecedor = $pdo->prepare($sql_fornecedor);
            $stmt_fornecedor->execute([$usuario_id]);
            $usuario = $stmt_fornecedor->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario || !$usuario['id_fornecedor']) {
                error_log("Usuário fornecedor sem id_fornecedor vinculado: {$usuario_id}");
                return false;
            }
            
            // Verificar se o pedido pertence ao fornecedor do usuário
            if ($pedido['id_fornecedor'] == $usuario['id_fornecedor']) {
                error_log("Acesso permitido: Fornecedor {$usuario['id_fornecedor']} tem acesso ao pedido {$pedido_id}");
                return true;
            } else {
                error_log("Acesso negado: Fornecedor {$usuario['id_fornecedor']} não tem acesso ao pedido {$pedido_id} (pedido pertence ao fornecedor {$pedido['id_fornecedor']})");
                return false;
            }
        }
        
        // Para usuários da empresa, permitir acesso
        error_log("Acesso permitido: Usuário da empresa {$usuario_id} tem acesso ao pedido {$pedido_id}");
        return true;
        
    } catch (Exception $e) {
        error_log("Erro ao verificar acesso ao pedido: " . $e->getMessage());
        return false;
    }
}

/**
 * Determina o tipo de usuário baseado no perfil
 */
function getTipoUsuario($perfil_id) {
    // Perfil 5 é Fornecedor conforme banco de dados (tbl_perfis)
    // Verificar também pelo nome do perfil na sessão
    $perfil_nome = $_SESSION['usuario_perfil'] ?? '';
    
    if (strtolower($perfil_nome) === 'fornecedor' || $perfil_id == 5) {
        return 'fornecedor';
    }
    
    return 'empresa';
}
?>