<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';
require_once '../../models/Usuario.php';
require_once '../../models/Perfil.php';
require_once '../../models/Filial.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $usuarioModel = new Usuario($pdo);
    $perfilModel = new Perfil($pdo);
    $filialModel = new Filial($pdo);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            $perfil = $_GET['perfil'] ?? '';
            $filial = $_GET['filial'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(u.nome_completo LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($perfil)) {
                $where[] = "u.id_perfil = ?";
                $params[] = $perfil;
            }
            
            if (!empty($filial)) {
                $where[] = "u.id_filial = ?";
                $params[] = $filial;
            }
            
            if ($status !== '') {
                $where[] = "u.ativo = ?";
                $params[] = $status;
            }
            
            $whereClause = !empty($where) ? implode(' AND ', $where) : '';
            
            $result = $usuarioModel->findWithPagination($page, $limit, $whereClause, $params);
            
            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'pagination' => [
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'total' => $result['total'],
                    'total_pages' => $result['total_pages']
                ]
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do usuário é obrigatório');
            }
            
            $usuario = $usuarioModel->findById($id);
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
            break;
            
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Validações básicas
            if (empty($input['nome_completo'])) {
                throw new Exception('Nome completo é obrigatório');
            }
            
            if (empty($input['email'])) {
                throw new Exception('E-mail é obrigatório');
            }
            
            // Verificar se e-mail já existe
            if ($usuarioModel->emailExiste($input['email'])) {
                throw new Exception('E-mail já está em uso');
            }
            
            // Validar que senha foi fornecida
            if (empty($input['senha'])) {
                throw new Exception('Senha é obrigatória');
            }
            
            // Usar a senha fornecida pelo administrador
            $senhaOriginal = $input['senha'];
            $input['senha'] = password_hash($input['senha'], PASSWORD_DEFAULT);
            
            // Inserir usuário
            $id = $usuarioModel->insert($input);
            
            // Buscar informações do perfil para o email
            $perfilNome = 'Usuário';
            if (isset($input['id_perfil'])) {
                $perfil = $perfilModel->findById($input['id_perfil']);
                if ($perfil) {
                    $perfilNome = $perfil['nome_perfil'];
                }
            }
            
            // Enviar email de boas-vindas
            $emailEnviado = false;
            try {
                require_once '../utils/EmailUtils.php';
                $emailEnviado = EmailUtils::enviarEmailBoasVindas(
                    $input['email'],
                    $input['nome_completo'],
                    $senhaOriginal,
                    $perfilNome
                );
            } catch (Exception $e) {
                error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuário criado com sucesso' . ($emailEnviado ? ' e email de boas-vindas enviado' : ''),
                'id' => $id,
                'email_enviado' => $emailEnviado
            ]);
            break;
            
        case 'update':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do usuário é obrigatório');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Verificar se usuário existe
            $usuario = $usuarioModel->findById($id);
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Se e-mail foi alterado, verificar se já existe
            if (isset($input['email']) && $input['email'] !== $usuario['email']) {
                if ($usuarioModel->emailExiste($input['email'], $id)) {
                    throw new Exception('E-mail já está em uso');
                }
            }
            
            // Se senha foi fornecida, fazer hash
            if (!empty($input['senha'])) {
                $input['senha'] = password_hash($input['senha'], PASSWORD_DEFAULT);
            } else {
                unset($input['senha']);
            }
            
            $usuarioModel->update($id, $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso'
            ]);
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do usuário é obrigatório');
            }
            
            $usuario = $usuarioModel->findById($id);
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            $usuarioModel->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
            break;
            
        case 'perfis':
            $perfis = $perfilModel->findAtivos();
            echo json_encode([
                'success' => true,
                'data' => $perfis
            ]);
            break;
            
        case 'filiais':
            $filiais = $filialModel->findAtivas();
            echo json_encode([
                'success' => true,
                'data' => $filiais
            ]);
            break;
            
        case 'estatisticas':
            $estatisticas = $usuarioModel->getEstatisticas();
            echo json_encode([
                'success' => true,
                'data' => $estatisticas
            ]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Gera uma senha temporária segura
 * @return string Senha temporária
 */
function gerarSenhaTemporaria() {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    $senha = '';
    
    // Garantir pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial
    $senha .= $caracteres[rand(0, 25)]; // Letra maiúscula
    $senha .= $caracteres[rand(26, 51)]; // Letra minúscula
    $senha .= $caracteres[rand(52, 61)]; // Número
    $senha .= $caracteres[rand(62, 69)]; // Caractere especial
    
    // Completar com caracteres aleatórios
    for ($i = 4; $i < 12; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    // Embaralhar a senha
    return str_shuffle($senha);
}
?> 