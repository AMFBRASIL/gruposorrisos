<?php
/**
 * API para gerenciar perfil do usuário
 * Permite atualizar dados pessoais e trocar senha
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/config.php';
require_once '../../config/session.php';
require_once '../../config/conexao.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado'
    ]);
    exit();
}

try {
    $conexao = Conexao::getInstance();
    $pdo = $conexao->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Buscar dados do usuário
            $stmt = $pdo->prepare("SELECT u.id_usuario, u.nome_completo, u.email, u.cpf, u.telefone, 
                                          u.ativo, u.ultimo_acesso, u.data_criacao, p.nome_perfil
                                   FROM tbl_usuarios u 
                                   LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
                                   WHERE u.id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Remover dados sensíveis
            unset($usuario['senha']);
            
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
            break;
            
        case 'POST':
            // Atualizar dados do usuário
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Validar dados obrigatórios
            if (empty($input['nome_completo']) || empty($input['email'])) {
                throw new Exception('Nome e e-mail são obrigatórios');
            }
            
            // Validar formato do e-mail
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('E-mail inválido');
            }
            
            $pdo->beginTransaction();
            
            // Verificar se e-mail já existe para outro usuário
            $stmt = $pdo->prepare("SELECT id_usuario FROM tbl_usuarios WHERE email = ? AND id_usuario != ?");
            $stmt->execute([$input['email'], $_SESSION['usuario_id']]);
            if ($stmt->fetch()) {
                throw new Exception('Este e-mail já está sendo usado por outro usuário');
            }
            
            // Preparar dados para atualização
            $dados = [
                'nome_completo' => trim($input['nome_completo']),
                'email' => trim($input['email']),
                'telefone' => !empty($input['telefone']) ? trim($input['telefone']) : null,
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];
            
            $sql = "UPDATE tbl_usuarios SET 
                        nome_completo = ?, 
                        email = ?, 
                        telefone = ?, 
                        data_atualizacao = ?";
            $params = [
                $dados['nome_completo'],
                $dados['email'],
                $dados['telefone'],
                $dados['data_atualizacao']
            ];
            
            // Se solicitou alteração de senha
            if (!empty($input['alterar_senha']) && $input['alterar_senha']) {
                if (empty($input['senha_atual']) || empty($input['nova_senha'])) {
                    throw new Exception('Senha atual e nova senha são obrigatórias');
                }
                
                // Verificar senha atual
                $stmt = $pdo->prepare("SELECT senha FROM tbl_usuarios WHERE id_usuario = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $senhaAtual = $stmt->fetchColumn();
                
                if (!password_verify($input['senha_atual'], $senhaAtual)) {
                    throw new Exception('Senha atual incorreta');
                }
                
                // Validar nova senha
                if (strlen($input['nova_senha']) < 6) {
                    throw new Exception('A nova senha deve ter pelo menos 6 caracteres');
                }
                
                // Adicionar nova senha na atualização
                $sql .= ", senha = ?";
                $params[] = password_hash($input['nova_senha'], PASSWORD_DEFAULT);
                
                // Log da alteração de senha
                error_log("Usuário {$_SESSION['usuario_id']} alterou a senha em " . date('Y-m-d H:i:s'));
            }
            
            $sql .= " WHERE id_usuario = ?";
            $params[] = $_SESSION['usuario_id'];
            
            // Executar atualização
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Registrar log da ação
            $stmt = $pdo->prepare("INSERT INTO tbl_logs_sistema (id_usuario, acao, tabela, id_registro, dados_novos, ip_usuario) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['usuario_id'],
                'ATUALIZACAO_PERFIL',
                'tbl_usuarios',
                $_SESSION['usuario_id'],
                json_encode([
                    'nome_completo' => $dados['nome_completo'],
                    'email' => $dados['email'],
                    'telefone' => $dados['telefone'],
                    'alterou_senha' => !empty($input['alterar_senha']) && $input['alterar_senha']
                ]),
                $_SERVER['REMOTE_ADDR'] ?? 'N/A'
            ]);
            
            // Atualizar dados da sessão se necessário
            if (isset($_SESSION['usuario_nome'])) {
                $_SESSION['usuario_nome'] = $dados['nome_completo'];
            }
            if (isset($_SESSION['usuario_email'])) {
                $_SESSION['usuario_email'] = $dados['email'];
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Dados atualizados com sucesso'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método não permitido'
            ]);
            break;
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log do erro
    error_log("Erro na API meu-perfil.php: " . $e->getMessage() . " - Usuário: " . ($_SESSION['usuario_id'] ?? 'N/A'));
} 