<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir arquivos necessários
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../config/conexao.php';
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../models/Movimentacao.php';
} else {
    require_once '../../config/conexao.php';
    require_once '../../config/session.php';
    require_once '../../models/Movimentacao.php';
}

try {
    $movimentacao = new Movimentacao();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $busca = $_GET['busca'] ?? '';
                    $tipo = $_GET['tipo'] ?? '';
                    $data_inicio = $_GET['data_inicio'] ?? '';
                    $data_fim = $_GET['data_fim'] ?? '';
                    
                    $result = $movimentacao->findWithFilters($page, $limit, $busca, $tipo, $data_inicio, $data_fim);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                    break;
                    
                case 'get':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        throw new Exception('ID não fornecido');
                    }
                    
                    $data = $movimentacao->findByIdWithRelations($id);
                    if (!$data) {
                        throw new Exception('Movimentação não encontrada');
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                case 'stats':
                    $data = $movimentacao->getEstatisticas();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                case 'materiais':
                    $filialId = $_GET['filial_id'] ?? null;
                    $filtroBrinde = $_GET['filtro_brinde'] ?? null;
                    error_log('Filial ID para materiais: ' . ($filialId ?: 'TODAS'));
                    error_log('Filtro brinde: ' . ($filtroBrinde ?: 'NENHUM'));
                    $data = $movimentacao->buscarMateriais($filialId, $filtroBrinde);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                case 'filiais':
                    $data = $movimentacao->buscarFiliais();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                case 'fornecedores':
                    $data = $movimentacao->buscarFornecedores();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                case 'clientes':
                    $data = $movimentacao->buscarClientes();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                default:
                    // Listar movimentações (padrão)
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $busca = $_GET['busca'] ?? '';
                    $tipo = $_GET['tipo'] ?? '';
                    $data_inicio = $_GET['data_inicio'] ?? '';
                    $data_fim = $_GET['data_fim'] ?? '';
                    
                    $result = $movimentacao->findWithFilters($page, $limit, $busca, $tipo, $data_inicio, $data_fim);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    error_log('📥 Dados recebidos na API: ' . json_encode($input));
                    
                    if (!$input) {
                        throw new Exception('Dados inválidos');
                    }
                    
                    // Validar campos obrigatórios
                    $required = ['tipo_movimentacao', 'materiais', 'id_usuario_executor'];
                    foreach ($required as $field) {
                        if (empty($input[$field])) {
                            throw new Exception("Campo obrigatório não fornecido: {$field}");
                        }
                    }
                    
                    // Validar se materiais é um array
                    if (!is_array($input['materiais']) || empty($input['materiais'])) {
                        throw new Exception('Campo materiais deve ser um array não vazio');
                    }
                    
                    error_log('📦 Materiais recebidos: ' . json_encode($input['materiais']));
                    
                    // Validar cada material
                    foreach ($input['materiais'] as $index => $material) {
                        $materialRequired = ['id_catalogo', 'quantidade'];
                        foreach ($materialRequired as $field) {
                            if (empty($material[$field])) {
                                throw new Exception("Campo obrigatório não fornecido no material {$index}: {$field}");
                            }
                        }
                        
                        error_log("🔍 Material {$index}: " . json_encode($material));
                    }
                    
                    // Criar movimentação para cada material
                    $idsCriados = [];
                    foreach ($input['materiais'] as $material) {
                        $dadosMovimentacao = [
                            'tipo_movimentacao' => $input['tipo_movimentacao'],
                            'id_catalogo' => $material['id_catalogo'],
                            'quantidade' => $material['quantidade'],
                            'valor_unitario' => $material['valor_unitario'] ?? null,
                            'valor_total' => $material['valor_total'] ?? null,
                            'id_filial_origem' => $input['id_filial_origem'] ?? null,
                            'id_filial_destino' => $input['id_filial_destino'] ?? null,
                            'id_cliente' => $input['id_cliente'] ?? null,
                            'documento' => $input['documento'] ?? null,
                            'observacoes' => $input['observacoes'] ?? null,
                            'id_usuario_executor' => $input['id_usuario_executor'],
                            // Campos de brinde
                            'is_brinde' => $input['is_brinde'] ?? 0,
                            'fornecedor_brinde' => $input['fornecedor_brinde'] ?? null,
                            'valor_estimado_brinde' => $input['valor_estimado_brinde'] ?? null
                        ];
                        
                        error_log('💾 Dados da movimentação: ' . json_encode($dadosMovimentacao));
                        
                        $id = $movimentacao->criar($dadosMovimentacao);
                        $idsCriados[] = $id;
                        
                        error_log("✅ Movimentação criada com ID: {$id}");
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Movimentações criadas com sucesso',
                        'data' => ['ids' => $idsCriados]
                    ]);
                    break;
                    
                default:
                    http_response_code(405);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'PUT':
            switch ($action) {
                case 'update':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        throw new Exception('ID não fornecido');
                    }
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        throw new Exception('Dados inválidos');
                    }
                    
                    // Atualizar movimentação
                    $result = $movimentacao->update($id, $input);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Movimentação atualizada com sucesso'
                    ]);
                    break;
                    
                default:
                    http_response_code(405);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'DELETE':
            switch ($action) {
                case 'delete':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        throw new Exception('ID não fornecido');
                    }
                    
                    $result = $movimentacao->delete($id);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Movimentação excluída com sucesso'
                    ]);
                    break;
                    
                default:
                    http_response_code(405);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
} 