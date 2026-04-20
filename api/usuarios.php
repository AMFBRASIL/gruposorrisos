<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/conexao.php';
require_once '../models/Usuario.php';
require_once '../models/Perfil.php';
require_once '../models/Filial.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $usuario = new Usuario($pdo);
    $perfil = new Perfil($pdo);
    $filial = new Filial($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($usuario, $perfil, $filial, $action);
            break;
        case 'POST':
            handlePost($usuario, $action);
            break;
        case 'PUT':
            handlePut($usuario, $action);
            break;
        case 'DELETE':
            handleDelete($usuario, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

function handleGet($usuario, $perfil, $filial, $action) {
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            $filters = [
                'nome' => $_GET['nome'] ?? null,
                'email' => $_GET['email'] ?? null,
                'perfil' => $_GET['perfil'] ?? null,
                'filial' => $_GET['filial'] ?? null,
                'ativo' => isset($_GET['ativo']) ? $_GET['ativo'] : null
            ];
            
            $result = $usuario->findWithFilters($filters, $page, $limit, $search);
            echo json_encode($result);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            $result = $usuario->findByIdWithRelations($id);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuário não encontrado']);
            }
            break;
            
        case 'estatisticas':
            // Total de usuários
            $total = $usuario->countAll();
            
            // Usuários ativos
            $ativos = $usuario->countAtivos();
            
            // Usuários inativos
            $inativos = $total - $ativos;
            
            // Usuários por perfil
            $usuariosPorPerfil = $usuario->countByPerfil();
            
            // Usuários por filial
            $usuariosPorFilial = $usuario->countByFilial();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'ativos' => $ativos,
                    'inativos' => $inativos,
                    'por_perfil' => $usuariosPorPerfil,
                    'por_filial' => $usuariosPorFilial
                ]
            ]);
            break;
            
        case 'perfis':
            $perfis = $perfil->findAtivos();
            echo json_encode(['success' => true, 'data' => $perfis]);
            break;
            
        case 'filiais':
            $filiais = $filial->findAtivas();
            echo json_encode(['success' => true, 'data' => $filiais]);
            break;
            
        default:
            // Lista padrão
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            
            $result = $usuario->findWithFilters([], $page, $limit, $search);
            echo json_encode($result);
            break;
    }
}

function handlePost($usuario, $action) {
    switch ($action) {
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validações básicas
            if (empty($data['nome_completo'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nome completo é obrigatório']);
                return;
            }
            
            if (empty($data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'E-mail é obrigatório']);
                return;
            }
            
            if (empty($data['senha'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Senha é obrigatória']);
                return;
            }
            
            // Verificar se email já existe
            if ($usuario->emailExiste($data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'E-mail já cadastrado']);
                return;
            }
            
            // Verificar se CPF já existe (se fornecido)
            if (!empty($data['cpf']) && $usuario->cpfExiste($data['cpf'])) {
                http_response_code(400);
                echo json_encode(['error' => 'CPF já cadastrado']);
                return;
            }
            
            // Adicionar dados padrão
            $data['ativo'] = $data['ativo'] ?? 1;
            $data['data_criacao'] = date('Y-m-d H:i:s');
            
            $result = $usuario->criarUsuario($data);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso',
                    'id' => $result
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao criar usuário']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handlePut($usuario, $action) {
    switch ($action) {
        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            // Verificar se email já existe (exceto o próprio)
            if (!empty($data['email']) && $usuario->emailExiste($data['email'], $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'E-mail já cadastrado']);
                return;
            }
            
            // Verificar se CPF já existe (exceto o próprio)
            if (!empty($data['cpf']) && $usuario->cpfExiste($data['cpf'], $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'CPF já cadastrado']);
                return;
            }
            
            $data['data_atualizacao'] = date('Y-m-d H:i:s');
            
            $result = $usuario->atualizarUsuario($id, $data);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar usuário']);
            }
            break;
            
        case 'alterar_senha':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            if (empty($data['nova_senha'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nova senha é obrigatória']);
                return;
            }
            
            $result = $usuario->alterarSenha($id, $data['nova_senha']);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Senha alterada com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao alterar senha']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handleDelete($usuario, $action) {
    switch ($action) {
        case 'delete':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            $result = $usuario->delete($id);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário excluído com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir usuário']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}
?> 