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
require_once '../../models/Perfil.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $perfilModel = new Perfil($pdo);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(p.nome_perfil LIKE ? OR p.descricao LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $whereClause = !empty($where) ? implode(' AND ', $where) : '';
            
            $result = $perfilModel->findWithPagination($page, $limit, $whereClause, $params);
            
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
                throw new Exception('ID do perfil é obrigatório');
            }
            
            $perfil = $perfilModel->findById($id);
            if (!$perfil) {
                throw new Exception('Perfil não encontrado');
            }
            
            // Buscar permissões do perfil
            $permissoes = $perfilModel->getPermissoes($id);
            $perfil['permissoes'] = $permissoes;
            
            echo json_encode([
                'success' => true,
                'data' => $perfil
            ]);
            break;
            
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Validações básicas
            if (empty($input['nome_perfil'])) {
                throw new Exception('Nome do perfil é obrigatório');
            }
            
            if (empty($input['descricao'])) {
                throw new Exception('Descrição é obrigatória');
            }
            
            // Verificar se nome já existe
            if ($perfilModel->nomeExiste($input['nome_perfil'])) {
                throw new Exception('Nome do perfil já está em uso');
            }
            
            // Filtrar apenas campos válidos da tabela
            $camposValidos = ['nome_perfil', 'descricao', 'ativo'];
            $dadosFiltrados = array_intersect_key($input, array_flip($camposValidos));
            
            $id = $perfilModel->insert($dadosFiltrados);
            
            // Salvar permissões se fornecidas
            if (!empty($input['permissoes'])) {
                $perfilModel->salvarPermissoes($id, $input['permissoes']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil criado com sucesso',
                'id' => $id
            ]);
            break;
            
        case 'update':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do perfil é obrigatório');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Verificar se perfil existe
            $perfil = $perfilModel->findById($id);
            if (!$perfil) {
                throw new Exception('Perfil não encontrado');
            }
            
            // Filtrar apenas campos válidos da tabela
            $camposValidos = ['nome_perfil', 'descricao', 'ativo'];
            $dadosFiltrados = array_intersect_key($input, array_flip($camposValidos));
            
            // Se nome foi alterado, verificar se já existe
            if (isset($dadosFiltrados['nome_perfil']) && $dadosFiltrados['nome_perfil'] !== $perfil['nome_perfil']) {
                if ($perfilModel->nomeExiste($dadosFiltrados['nome_perfil'], $id)) {
                    throw new Exception('Nome do perfil já está em uso');
                }
            }
            
            // Só fazer update se houver dados válidos
            if (!empty($dadosFiltrados)) {
                $perfilModel->update($id, $dadosFiltrados);
            }
            
            // Atualizar permissões se fornecidas
            if (isset($input['permissoes'])) {
                $perfilModel->salvarPermissoes($id, $input['permissoes']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso'
            ]);
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do perfil é obrigatório');
            }
            
            $perfil = $perfilModel->findById($id);
            if (!$perfil) {
                throw new Exception('Perfil não encontrado');
            }
            
            // Verificar se pode ser excluído
            if (!$perfilModel->podeExcluir($id)) {
                throw new Exception('Não é possível excluir este perfil pois há usuários vinculados');
            }
            
            $perfilModel->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
            ]);
            break;
            
        case 'estatisticas':
            $estatisticas = $perfilModel->getEstatisticas();
            echo json_encode([
                'success' => true,
                'data' => $estatisticas
            ]);
            break;
            
        case 'paginas':
            $paginas = $perfilModel->getPaginas();
            echo json_encode([
                'success' => true,
                'data' => $paginas
            ]);
            break;
            
        case 'permissoes':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do perfil é obrigatório');
            }
            
            $permissoes = $perfilModel->getPermissoesPaginas($id);
            echo json_encode([
                'success' => true,
                'data' => $permissoes
            ]);
            break;
            
        case 'atualizar-permissoes':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            if (empty($input['id_perfil'])) {
                throw new Exception('ID do perfil é obrigatório');
            }
            
            if (!isset($input['permissoes'])) {
                throw new Exception('Permissões são obrigatórias');
            }
            
            $resultado = $perfilModel->atualizarPermissoesPaginas($input['id_perfil'], $input['permissoes']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Permissões atualizadas com sucesso'
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
?> 