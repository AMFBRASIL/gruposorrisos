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
require_once '../../models/Alerta.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $alertaModel = new Alerta($pdo);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            $tipo = $_GET['tipo'] ?? '';
            $nivel = $_GET['nivel'] ?? '';
            $status = $_GET['status'] ?? '';
            $filial = $_GET['filial'] ?? '';
            
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(cm.codigo LIKE ? OR cm.nome LIKE ? OR ae.mensagem LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($tipo)) {
                $where[] = "ae.tipo_alerta = ?";
                $params[] = $tipo;
            }
            
            if (!empty($nivel)) {
                $where[] = "ae.prioridade = ?";
                $params[] = $nivel;
            }
            
            if (!empty($status)) {
                if ($status === 'ativo') {
                    $where[] = "ae.status = 'ativo'";
                } elseif ($status === 'resolvido') {
                    $where[] = "ae.status = 'resolvido'";
                }
            }
            
            if (!empty($filial)) {
                $where[] = "ae.id_filial = ?";
                $params[] = $filial;
            }
            
            $whereClause = !empty($where) ? implode(' AND ', $where) : '';
            
            $result = $alertaModel->findWithPagination($page, $limit, $whereClause, $params);
            
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
                throw new Exception('ID do alerta é obrigatório');
            }
            
            $alerta = $alertaModel->findById($id);
            if (!$alerta) {
                throw new Exception('Alerta não encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $alerta
            ]);
            break;
            
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Validações básicas
            if (empty($input['id_filial'])) {
                throw new Exception('Filial é obrigatória');
            }
            
            if (empty($input['id_material'])) {
                throw new Exception('Material é obrigatório');
            }
            
            if (empty($input['tipo_alerta'])) {
                throw new Exception('Tipo de alerta é obrigatório');
            }
            
            $id = $alertaModel->insert($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Alerta criado com sucesso',
                'id' => $id
            ]);
            break;
            
        case 'update':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do alerta é obrigatório');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dados inválidos');
            }
            
            // Verificar se alerta existe
            $alerta = $alertaModel->findById($id);
            if (!$alerta) {
                throw new Exception('Alerta não encontrado');
            }
            
            $alertaModel->update($id, $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Alerta atualizado com sucesso'
            ]);
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do alerta é obrigatório');
            }
            
            $alerta = $alertaModel->findById($id);
            if (!$alerta) {
                throw new Exception('Alerta não encontrado');
            }
            
            $alertaModel->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Alerta excluído com sucesso'
            ]);
            break;
            
        case 'marcar_lido':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID do alerta é obrigatório');
            }
            
            $alerta = $alertaModel->findById($id);
            if (!$alerta) {
                throw new Exception('Alerta não encontrado');
            }
            
            $alertaModel->marcarComoLido($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Alerta marcado como lido'
            ]);
            break;
            
        case 'marcar_todos_lidos':
            $alertaModel->marcarTodosComoLidos();
            
            echo json_encode([
                'success' => true,
                'message' => 'Todos os alertas foram marcados como lidos'
            ]);
            break;
            
        case 'estatisticas':
            $estatisticas = $alertaModel->getEstatisticas();
            echo json_encode([
                'success' => true,
                'data' => $estatisticas
            ]);
            break;
            
        case 'alertas_criticos':
            $alertasCriticos = $alertaModel->getAlertasCriticos();
            echo json_encode([
                'success' => true,
                'data' => $alertasCriticos
            ]);
            break;
            
        case 'gerar_alertas':
            $alertaModel->gerarAlertasAutomaticos();
            
            echo json_encode([
                'success' => true,
                'message' => 'Alertas automáticos gerados com sucesso'
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