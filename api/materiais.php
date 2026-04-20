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
require_once '../models/Material.php';
require_once '../models/Categoria.php';
require_once '../models/Fornecedor.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $material = new Material($pdo);
    $categoria = new Categoria($pdo);
    $fornecedor = new Fornecedor($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($material, $categoria, $fornecedor, $action);
            break;
        case 'POST':
            handlePost($material, $action);
            break;
        case 'PUT':
            handlePut($material, $action);
            break;
        case 'DELETE':
            handleDelete($material, $action);
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

function handleGet($material, $categoria, $fornecedor, $action) {
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            
            // Obter filial do parâmetro ou usar a do usuário logado
            $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
            error_log('Filial ID para listagem: ' . ($filialId ?: 'TODAS'));
            
            $filters = [
                'id_filial' => $filialId,
                'id_categoria' => $_GET['categoria'] ?? null,
                'id_fornecedor' => $_GET['fornecedor'] ?? null,
                'codigo' => $_GET['codigo'] ?? null,
                'nome' => $_GET['nome'] ?? null,
                'estoque_baixo' => isset($_GET['estoque_baixo']) ? true : false,
                'estoque_zerado' => isset($_GET['estoque_zerado']) ? true : false,
                'precisa_ressuprimento' => isset($_GET['precisa_ressuprimento']) ? true : false
            ];
            
            $result = $material->findWithFilters($filters, $page, $limit);
            echo json_encode($result);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            $result = $material->findByIdWithRelations($id);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Material não encontrado']);
            }
            break;
            
        case 'categorias':
            $categorias = $categoria->findAll();
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;
            
        case 'fornecedores':
            $fornecedores = $fornecedor->findAll();
            echo json_encode(['success' => true, 'data' => $fornecedores]);
            break;
            
        case 'estatisticas':
            $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
            
            // Debug: Log da filial
            error_log('Filial ID para estatísticas: ' . ($filialId ?: 'TODAS'));
            
            // Se não há filial específica, buscar de todas as filiais
            if (!$filialId) {
                // Total de materiais
                $total = $material->countAll();
                
                // Em estoque
                $emEstoque = $material->countEmEstoque(null);
                
                // Estoque baixo
                $estoqueBaixo = $material->countEstoqueBaixo(null);
                
                // Sem estoque
                $semEstoque = $material->countEstoqueZerado(null);
            } else {
                // Total de materiais
                $total = $material->countByFilial($filialId);
                
                // Em estoque
                $emEstoque = $material->countEmEstoque($filialId);
                
                // Estoque baixo
                $estoqueBaixo = $material->countEstoqueBaixo($filialId);
                
                // Sem estoque
                $semEstoque = $material->countEstoqueZerado($filialId);
            }
            
            $resultado = [
                'success' => true,
                'data' => [
                    'total' => $total,
                    'em_estoque' => $emEstoque,
                    'estoque_baixo' => $estoqueBaixo,
                    'sem_estoque' => $semEstoque,
                    'precisa_ressuprimento' => $material->countPrecisaRessuprimento($filialId),
                    'ressuprimento_preventivo' => $material->countRessuprimentoPreventivo($filialId)
                ]
            ];
            
            error_log('Resultado das estatísticas: ' . json_encode($resultado));
            echo json_encode($resultado);
            break;
            
        case 'duplicar':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            $original = $material->findByIdWithRelations($id);
            if (!$original) {
                http_response_code(404);
                echo json_encode(['error' => 'Material original não encontrado']);
                return;
            }
            // Preparar dados para duplicação
            $novo = $original;
            unset($novo['id_material']);
            // Ajustar código e nome para evitar duplicidade
            $novo['codigo'] = $original['codigo'] . '_DUP';
            $novo['nome'] = $original['nome'] . ' (Cópia)';
            $novo['ativo'] = 1;
            $novo['data_criacao'] = date('Y-m-d H:i:s');
            $novo['data_atualizacao'] = null;
            // Remover campos de relacionamento
            unset($novo['nome_categoria'], $novo['fornecedor_nome'], $novo['unidade_sigla'], $novo['unidade_nome'], $novo['nome_filial']);
            try {
                $idNovo = $material->insert($novo);
                echo json_encode(['success' => true, 'message' => 'Material duplicado com sucesso', 'id' => $idNovo]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao duplicar material: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handlePost($material, $action) {
    switch ($action) {
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validações básicas
            if (empty($data['codigo']) || empty($data['nome']) || empty($data['id_categoria'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Campos obrigatórios não preenchidos']);
                return;
            }
            
            // Verificar se código já existe
            if ($material->codigoExiste($data['codigo'], getCurrentUserFilialId())) {
                http_response_code(400);
                echo json_encode(['error' => 'Código já existe']);
                return;
            }
            
            // Remover campos que não existem na tabela
            unset($data['marca']);
            
            // Tratar campos que podem ser vazios para evitar erro de chave estrangeira
            if (empty($data['id_fornecedor'])) {
                $data['id_fornecedor'] = null;
            }
            
            if (empty($data['id_categoria'])) {
                $data['id_categoria'] = null;
            }
            
            if (empty($data['id_unidade'])) {
                $data['id_unidade'] = null;
            }
            
            // Adicionar dados do usuário
            $filialId = getCurrentUserFilialId();
            
            // Se o frontend enviou um id_filial específico, usar ele
            if (!empty($data['id_filial'])) {
                $filialId = $data['id_filial'];
                error_log('✅ Usando filial enviada pelo frontend: ' . $filialId);
            } else if (!$filialId) {
                // Se não tem filial, usar a primeira filial disponível
                try {
                    require_once '../models/Filial.php';
                    $filial = new Filial();
                    $filiais = $filial->findAtivas();
                    if (!empty($filiais)) {
                        $filialId = $filiais[0]['id_filial'];
                        error_log('⚠️ Usando primeira filial disponível: ' . $filialId);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Nenhuma filial disponível']);
                        return;
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao buscar filial: ' . $e->getMessage()]);
                    return;
                }
            }
            
            $data['id_filial'] = $filialId;
            $data['ativo'] = 1;
            $data['data_criacao'] = date('Y-m-d H:i:s');
            
            $result = $material->insert($data);
            if ($result) {
                // Log da ação
                logActivity('CRIAR', $data, 'tbl_materiais', $result);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Material criado com sucesso',
                    'id' => $result
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao criar material']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handlePut($material, $action) {
    switch ($action) {
        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            // Verificar se código já existe (exceto o próprio)
            $filialId = getCurrentUserFilialId();
            
            // Se o frontend enviou um id_filial específico, usar ele
            if (!empty($data['id_filial'])) {
                $filialId = $data['id_filial'];
                error_log('✅ Update - Usando filial enviada pelo frontend: ' . $filialId);
            } else if (!$filialId) {
                // Se não tem filial, usar a primeira filial disponível
                try {
                    require_once '../models/Filial.php';
                    $filial = new Filial();
                    $filiais = $filial->findAtivas();
                    if (!empty($filiais)) {
                        $filialId = $filiais[0]['id_filial'];
                        error_log('⚠️ Update - Usando primeira filial disponível: ' . $filialId);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Nenhuma filial disponível']);
                        return;
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao buscar filial: ' . $e->getMessage()]);
                    return;
                }
            }
            
            if (!empty($data['codigo']) && $material->codigoExiste($data['codigo'], $filialId, $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Código já existe']);
                return;
            }
            
            // Remover campos que não existem na tabela
            unset($data['marca']);
            
            // Tratar campos que podem ser vazios para evitar erro de chave estrangeira
            if (empty($data['id_fornecedor'])) {
                $data['id_fornecedor'] = null;
            }
            
            if (empty($data['id_categoria'])) {
                $data['id_categoria'] = null;
            }
            
            if (empty($data['id_unidade'])) {
                $data['id_unidade'] = null;
            }
            
            $data['data_atualizacao'] = date('Y-m-d H:i:s');
            
            $result = $material->update($id, $data);
            if ($result) {
                // Log da ação
                logActivity('ATUALIZAR', $data, 'tbl_materiais', $id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Material atualizado com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar material']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handleDelete($material, $action) {
    switch ($action) {
        case 'delete':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            // Soft delete - apenas desativar
            $result = $material->update($id, ['ativo' => 0]);
            if ($result) {
                // Log da ação
                logActivity('EXCLUIR', null, 'tbl_materiais', $id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Material excluído com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir material']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}
?> 