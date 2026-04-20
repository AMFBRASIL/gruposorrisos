<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Aumentar timeout para operações que podem demorar (criação de inventário com muitos materiais)
set_time_limit(300); // 5 minutos
ini_set('max_execution_time', 300);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/conexao.php';
require_once '../models/Inventario.php';
require_once '../models/ItemInventario.php';
// require_once '../models/Material.php'; // Não mais necessário com nova estrutura
require_once '../models/Filial.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $inventario = new Inventario($pdo);
    $itemInventario = new ItemInventario($pdo);
    $filial = new Filial($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($inventario, $itemInventario, $filial, $action);
            break;
        case 'POST':
            handlePost($inventario, $itemInventario, $action);
            break;
        case 'PUT':
            handlePut($inventario, $itemInventario, $action);
            break;
        case 'DELETE':
            handleDelete($inventario, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Erro fatal em inventario.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    error_log('Erro fatal (Error) em inventario.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

function handleGet($inventario, $itemInventario, $filial, $action) {
    global $pdo;
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            
            // Obter filial da requisição ou usar a do usuário logado
            $idFilial = $_GET['id_filial'] ?? getCurrentUserFilialId();
            
            $filters = [
                'id_filial' => $idFilial,
                'status' => $_GET['status'] ?? null,
                'numero_inventario' => $_GET['numero_inventario'] ?? null,
                'data_inicio' => $_GET['data_inicio'] ?? null,
                'data_fim' => $_GET['data_fim'] ?? null
            ];
            
            $result = $inventario->findWithFilters($filters, $page, $limit);
            echo json_encode($result);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            $result = $inventario->findByIdWithRelations($id);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Inventário não encontrado']);
            }
            break;
            
        case 'itens':
            $idInventario = $_GET['id_inventario'] ?? null;
            if (!$idInventario) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            error_log("🔍 API inventario.php: Buscando itens para inventário ID: $idInventario");
            
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $filters = [
                'id_inventario' => $idInventario,
                'status_item' => $_GET['status_item'] ?? null,
                'termo_busca' => $_GET['termo_busca'] ?? null,
                'codigo_material' => $_GET['codigo_material'] ?? null,
                'nome_material' => $_GET['nome_material'] ?? null,
                'id_categoria' => $_GET['id_categoria'] ?? null
            ];
            
            error_log("🔍 API inventario.php: Filtros aplicados: " . json_encode($filters));
            
            $result = $itemInventario->findWithFilters($filters, $page, $limit);
            error_log("📦 API inventario.php: Resultado encontrado: " . json_encode($result));
            
            echo json_encode($result);
            break;
            
        case 'materiais_novos':
            $idInventario = $_GET['id_inventario'] ?? null;
            if (!$idInventario) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                // Buscar ID da filial do inventário
                $inventarioData = $inventario->findByIdWithRelations($idInventario);
                if (!$inventarioData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Inventário não encontrado']);
                    return;
                }
                
                $idFilial = $inventarioData['id_filial'];
                $total = $itemInventario->contarMateriaisNovos($idInventario, $idFilial);
                
                echo json_encode([
                    'success' => true,
                    'total' => $total
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao contar materiais novos: ' . $e->getMessage()]);
            }
            break;
            
        case 'item':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do item não fornecido']);
                return;
            }
            
            $result = $itemInventario->findByIdWithRelations($id);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Item não encontrado']);
            }
            break;
            
        case 'materiais':
            $idFilial = getCurrentUserFilialId();
            // Buscar materiais com estoque na filial usando nova estrutura
            $sql = "SELECT cm.id_catalogo as id_material, cm.codigo, cm.nome, 
                           um.sigla as unidade, ef.estoque_atual, ef.preco_unitario
                    FROM tbl_catalogo_materiais cm
                    INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                    LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                    WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1
                    ORDER BY cm.nome";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idFilial]);
            $materiais = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $materiais]);
            break;
            
        case 'filiais':
            $filiais = $filial->findAll();
            echo json_encode(['success' => true, 'data' => $filiais]);
            break;
            
        case 'stats':
            // Obter filial da requisição ou usar a do usuário logado
            $idFilial = $_GET['id_filial'] ?? getCurrentUserFilialId();
            
            // Se não tiver filial, usar a primeira filial disponível
            if (!$idFilial) {
                $stmt = $pdo->prepare("SELECT id_filial FROM tbl_filiais ORDER BY id_filial LIMIT 1");
                $stmt->execute();
                $primeiraFilial = $stmt->fetch();
                $idFilial = $primeiraFilial ? $primeiraFilial['id_filial'] : null;
            }
            
            if ($idFilial) {
                $stats = [
                    'total' => $inventario->countTotal($idFilial),
                    'em_andamento' => $inventario->countEmAndamento($idFilial),
                    'finalizados' => $inventario->countFinalizados($idFilial),
                    'cancelados' => $inventario->countCancelados($idFilial)
                ];
            } else {
                $stats = [
                    'total' => 0,
                    'em_andamento' => 0,
                    'finalizados' => 0,
                    'cancelados' => 0
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

function handlePost($inventario, $itemInventario, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            // Obter filial do usuário logado se não fornecida
            if (empty($input['id_filial'])) {
                $input['id_filial'] = getCurrentUserFilialId();
            }
            
            // Se ainda não tiver filial, usar a primeira filial disponível
            if (empty($input['id_filial'])) {
                $stmt = $pdo->prepare("SELECT id_filial FROM tbl_filiais ORDER BY id_filial LIMIT 1");
                $stmt->execute();
                $primeiraFilial = $stmt->fetch();
                $input['id_filial'] = $primeiraFilial ? $primeiraFilial['id_filial'] : null;
            }
            
            // Validar campos obrigatórios
            $required = ['id_usuario_responsavel'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Campo obrigatório não fornecido: {$field}"]);
                    return;
                }
            }
            
            // Validar se tem filial
            if (empty($input['id_filial'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nenhuma filial disponível para criar inventário']);
                return;
            }
            
            try {
                $idInventario = $inventario->criar($input);
                
                // Adicionar materiais automaticamente
                $adicionados = 0;
                try {
                    $adicionados = $itemInventario->adicionarMateriais($idInventario, $input['id_filial']);
                } catch (Exception $e) {
                    error_log('Erro ao adicionar materiais: ' . $e->getMessage());
                    // Continuar mesmo se houver erro ao adicionar alguns materiais
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Inventário criado com sucesso',
                    'data' => [
                        'id_inventario' => $idInventario,
                        'materiais_adicionados' => $adicionados
                    ]
                ]);
            } catch (Exception $e) {
                error_log('Erro ao criar inventário: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao criar inventário: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'contar':
            $idItem = $input['id_item_inventario'] ?? null;
            if (!$idItem) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do item não fornecido']);
                return;
            }
            
            try {
                // Buscar dados do item para obter quantidade_sistema e valor_unitario
                $item = $itemInventario->findByIdWithRelations($idItem);
                if (!$item) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Item não encontrado']);
                    return;
                }
                
                // Adicionar os dados que faltam
                $input['quantidade_sistema'] = $item['quantidade_sistema'];
                $input['valor_unitario'] = $item['valor_unitario'];
                
                $result = $itemInventario->atualizarContagem($idItem, $input);
                if ($result) {
                    // Verificar se todos os itens do inventário foram contados
                    $idInventario = $item['id_inventario'];
                    $todosContados = $itemInventario->verificarSeTodosContados($idInventario);
                    
                    if ($todosContados) {
                        // Finalizar automaticamente o inventário
                        $inventario->finalizar($idInventario);
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Contagem salva e inventário finalizado automaticamente!',
                            'inventario_finalizado' => true
                        ]);
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Contagem atualizada com sucesso']);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao atualizar contagem']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar contagem: ' . $e->getMessage()]);
            }
            break;
            
        case 'ajustar':
            $idItem = $input['id_item_inventario'] ?? null;
            if (!$idItem) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do item não fornecido']);
                return;
            }
            
            try {
                // Adicionar ID do usuário logado aos dados
                $input['id_usuario'] = $_SESSION['usuario_id'] ?? null;
                
                $result = $itemInventario->ajustarItem($idItem, $input);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Item ajustado com sucesso e estoque atualizado']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao ajustar item']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao ajustar item: ' . $e->getMessage()]);
            }
            break;
            
        case 'ajustar_lote':
            $idInventario = $input['id_inventario'] ?? null;
            if (!$idInventario) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                // Buscar todos os itens divergentes do inventário
                $itensDivergentes = $itemInventario->findDivergentes($idInventario);
                
                if (empty($itensDivergentes)) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Nenhum item divergente encontrado para ajustar',
                        'data' => [
                            'total' => 0,
                            'ajustados' => 0,
                            'erros' => []
                        ]
                    ]);
                    return;
                }
                
                $total = count($itensDivergentes);
                $ajustados = 0;
                $erros = [];
                $idUsuario = $_SESSION['usuario_id'] ?? null;
                
                // Ajustar cada item divergente
                foreach ($itensDivergentes as $item) {
                    try {
                        $dadosAjuste = [
                            'observacoes' => 'Ajuste em lote de itens divergentes',
                            'id_usuario' => $idUsuario
                        ];
                        
                        $result = $itemInventario->ajustarItem($item['id_item_inventario'], $dadosAjuste);
                        if ($result) {
                            $ajustados++;
                        } else {
                            $erros[] = [
                                'id_item' => $item['id_item_inventario'],
                                'material' => $item['nome_material'] ?? 'N/A',
                                'erro' => 'Falha ao ajustar item'
                            ];
                        }
                    } catch (Exception $e) {
                        $erros[] = [
                            'id_item' => $item['id_item_inventario'],
                            'material' => $item['nome_material'] ?? 'N/A',
                            'erro' => $e->getMessage()
                        ];
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => "Ajuste em lote concluído: {$ajustados} de {$total} itens ajustados com sucesso",
                    'data' => [
                        'total' => $total,
                        'ajustados' => $ajustados,
                        'erros' => $erros
                    ]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao ajustar itens em lote: ' . $e->getMessage()]);
            }
            break;
            
        case 'atualizar_materiais':
            $idInventario = $input['id_inventario'] ?? null;
            if (!$idInventario) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                // Buscar ID da filial do inventário
                $inventarioData = $inventario->findByIdWithRelations($idInventario);
                if (!$inventarioData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Inventário não encontrado']);
                    return;
                }
                
                $idFilial = $inventarioData['id_filial'];
                
                // Adicionar apenas materiais novos
                $resultado = $itemInventario->adicionarMateriaisNovos($idInventario, $idFilial);
                
                if ($resultado['adicionados'] > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => "Inventário atualizado! {$resultado['adicionados']} novo(s) material(is) adicionado(s).",
                        'data' => $resultado
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Nenhum material novo encontrado para adicionar ao inventário.',
                        'data' => $resultado
                    ]);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar inventário: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

function handlePut($inventario, $itemInventario, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            $id = $input['id_inventario'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                $result = $inventario->atualizar($id, $input);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Inventário atualizado com sucesso']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao atualizar inventário']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar inventário: ' . $e->getMessage()]);
            }
            break;
            
        case 'finalizar':
            $id = $input['id_inventario'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                $idUsuario = $_SESSION['usuario_id'] ?? null;
                $result = $inventario->finalizar($id, $idUsuario);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Inventário finalizado com sucesso. Todos os itens divergentes foram ajustados automaticamente.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao finalizar inventário']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao finalizar inventário: ' . $e->getMessage()]);
            }
            break;
            
        case 'cancelar':
            $id = $input['id_inventario'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do inventário não fornecido']);
                return;
            }
            
            try {
                $result = $inventario->cancelar($id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Inventário cancelado com sucesso']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao cancelar inventário']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao cancelar inventário: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

function handleDelete($inventario, $action) {
    switch ($action) {
        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            try {
                $result = $inventario->delete($id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Inventário excluído com sucesso']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erro ao excluir inventário']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir inventário: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
} 