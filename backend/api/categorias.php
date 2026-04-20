<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/session.php';
require_once '../../config/conexao.php';
require_once '../../models/Categoria.php';

// Verificar se o usuário está logado
// Temporariamente desabilitado para teste
/*
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}
*/

try {
    $pdo = Conexao::getInstance()->getPdo();
    $categoriaModel = new Categoria($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $busca = $_GET['busca'] ?? '';
                    $status = $_GET['status'] ?? '';
                    
                    $result = $categoriaModel->findWithPagination($page, $limit, $busca, $status);
                    echo json_encode($result);
                    break;
                    
                case 'get':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
                        break;
                    }
                    
                    $categoria = $categoriaModel->findByIdWithStats($id);
                    if ($categoria) {
                        echo json_encode(['success' => true, 'categoria' => $categoria]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Categoria não encontrada']);
                    }
                    break;
                    
                case 'stats':
                    require_once '../../models/Material.php';
                    $materialModel = new Material($pdo);
                    $total = $materialModel->countAll();
                    $categorizados = $materialModel->countCategorizados();
                    $semCategoria = $materialModel->countSemCategoria();
                    $totalCategorias = $categoriaModel->countAtivas();
                    
                    echo json_encode([
                        'success' => true,
                        'total' => $totalCategorias,
                        'categorizados' => $categorizados,
                        'sem_categoria' => $semCategoria
                    ]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nome_categoria'])) {
                echo json_encode(['success' => false, 'error' => 'Nome da categoria é obrigatório']);
                break;
            }
            
            // Verificar se nome já existe
            if ($categoriaModel->nomeExiste($data['nome_categoria'])) {
                echo json_encode(['success' => false, 'error' => 'Nome da categoria já existe']);
                break;
            }
            
            try {
                $id = $categoriaModel->insert([
                    'nome_categoria' => trim($data['nome_categoria']),
                    'descricao' => trim($data['descricao'] ?? ''),
                    'ativo' => $data['ativo'] ?? 1
                ]);
                echo json_encode(['success' => true, 'message' => 'Categoria criada com sucesso', 'id' => $id]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Erro ao criar categoria: ' . $e->getMessage()]);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id']) || empty($data['nome_categoria'])) {
                echo json_encode(['success' => false, 'error' => 'ID e nome são obrigatórios']);
                break;
            }
            
            // Verificar se nome já existe (excluindo o próprio registro)
            if ($categoriaModel->nomeExiste($data['nome_categoria'], $data['id'])) {
                echo json_encode(['success' => false, 'error' => 'Nome da categoria já existe']);
                break;
            }
            
            try {
                $categoriaModel->update($data['id'], [
                    'nome_categoria' => trim($data['nome_categoria']),
                    'descricao' => trim($data['descricao'] ?? ''),
                    'ativo' => $data['ativo'] ?? 1,
                    'data_atualizacao' => date('Y-m-d H:i:s')
                ]);
                echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Erro ao atualizar categoria: ' . $e->getMessage()]);
            }
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID é obrigatório']);
                break;
            }
            
            try {
                // Verificar se há materiais usando esta categoria (nova estrutura)
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE id_categoria = ? AND ativo = 1");
                $stmt->execute([$data['id']]);
                $result = $stmt->fetch();
                
                if ($result['total'] > 0) {
                    echo json_encode(['success' => false, 'error' => 'Não é possível excluir uma categoria que possui materiais associados']);
                    break;
                }
                
                $categoriaModel->update($data['id'], ['ativo' => 0]);
                echo json_encode(['success' => true, 'message' => 'Categoria removida com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Erro ao remover categoria: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?> 