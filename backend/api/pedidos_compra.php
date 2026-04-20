<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Valida e limpa uma data antes de salvar
 */
function validarData($data) {
    if (empty($data) || $data === '0000-00-00' || $data === '0000-00-00 00:00:00') {
        return null;
    }
    
    // Verificar se a data é válida
    $timestamp = strtotime($data);
    if ($timestamp === false || $timestamp < 0) {
        return null;
    }
    
    // Verificar se o ano é válido (maior que 1900)
    $ano = date('Y', $timestamp);
    if ($ano < 1900) {
        return null;
    }
    
    return $data;
}

try {
    // Verificar se estamos executando via CLI ou HTTP
    if (php_sapi_name() === 'cli') {
        require_once __DIR__ . '/../../config/conexao.php';
        require_once __DIR__ . '/../../config/session.php';
        require_once __DIR__ . '/../../models/PedidoCompra.php';
    } else {
        require_once '../../config/conexao.php';
        require_once '../../config/session.php';
        require_once '../../models/PedidoCompra.php';
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar dependências: ' . $e->getMessage()]);
    exit;
}

// Verificação de autenticação
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Para requisições POST, ler action do corpo JSON se não estiver na URL
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    // Só sobrescreve se não tiver sido passado via query string
    if (!$action && isset($input['action'])) {
        $action = $input['action'];
    }
}

try {
    $pedidoCompra = new PedidoCompra();
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $pagina = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $filtros = [
                        'busca' => $_GET['busca'] ?? '',
                        'status' => $_GET['status'] ?? '',
                        'fornecedor' => $_GET['fornecedor'] ?? '',
                        'data_inicio' => $_GET['data_inicio'] ?? '',
                        'data_fim' => $_GET['data_fim'] ?? ''
                    ];
                    
                    $resultado = $pedidoCompra->findWithFilters($filtros, $pagina, $limit);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'get':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
                        break;
                    }
                    
                    $pedido = $pedidoCompra->findByIdWithRelations($id);
                    if ($pedido) {
                        $itens = $pedidoCompra->buscarItens($id);
                        $pedido['itens'] = $itens;
                        
                        // Debug: verificar se url_nota_fiscal existe
                        error_log("Pedido ID {$id} - url_nota_fiscal: " . ($pedido['url_nota_fiscal'] ?? 'NÃO EXISTE'));
                        
                        echo json_encode(['success' => true, 'pedido' => $pedido]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
                    }
                    break;
                    
                case 'stats':
                    $stats = $pedidoCompra->getEstatisticas();
                    echo json_encode(['success' => true, 'stats' => $stats]);
                    break;
                    
                case 'fornecedores':
                    $fornecedores = $pedidoCompra->buscarFornecedores();
                    echo json_encode(['success' => true, 'fornecedores' => $fornecedores]);
                    break;
                    
                case 'materiais':
                    $materiais = $pedidoCompra->buscarMateriais();
                    echo json_encode(['success' => true, 'materiais' => $materiais]);
                    break;
                    
                case 'materiais-estoque-baixo':
                    $idFilial = $_GET['id_filial'] ?? null;
                    $idFornecedor = $_GET['id_fornecedor'] ?? null;
                    $filtroEstoque = $_GET['filtro_estoque'] ?? 'critico';
                    
                    if (!$idFilial || !$idFornecedor) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID da filial e fornecedor são obrigatórios']);
                        break;
                    }
                    
                    $materiais = $pedidoCompra->buscarMateriaisEstoqueBaixo($idFilial, $idFornecedor, $filtroEstoque);
                    echo json_encode(['success' => true, 'materiais' => $materiais]);
                    break;
                    
                case 'ultimo-preco-material':
                    $idMaterial = $_GET['id_material'] ?? null;
                    $idFilial = $_GET['id_filial'] ?? null;
                    
                    if (!$idMaterial) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID do material é obrigatório']);
                        break;
                    }
                    
                    $ultimoPreco = $pedidoCompra->buscarUltimoPrecoMaterial($idMaterial, $idFilial);
                    echo json_encode(['success' => true, 'ultimo_preco' => $ultimoPreco]);
                    break;
                    
                case 'filiais':
                    $filiais = $pedidoCompra->buscarFiliais();
                    echo json_encode(['success' => true, 'filiais' => $filiais]);
                    break;
                    
                case 'pesquisar_material':
                    $busca = $_GET['busca'] ?? '';
                    $idFilial = $_GET['id_filial'] ?? null;
                    $idFornecedor = $_GET['id_fornecedor'] ?? null;
                    
                    if (!$busca || !$idFilial || !$idFornecedor) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Busca, filial e fornecedor são obrigatórios']);
                        break;
                    }
                    
                    $materiais = $pedidoCompra->pesquisarMaterial($busca, $idFilial, $idFornecedor);
                    echo json_encode(['success' => true, 'data' => $materiais]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
                        break;
                    }

                    
                    
                    $dados = [
                        'id_fornecedor' => $input['id_fornecedor'] ?? null,
                        'id_filial' => $input['id_filial'] ?? getCurrentUserFilialId(),
                        'data_entrega_prevista' => validarData($input['data_entrega_prevista'] ?? null),
                        'prioridade' => $input['prioridade'] ?? 'padrao',
                        'prazo_entrega' => $input['prazo_entrega'] ?? 8,
                        // Removido o status padrão para usar o padrão do banco (em_analise)
                        'valor_total' => $input['valor_total'] ?? 0,
                        'observacoes' => $input['observacoes'] ?? '',
                        'id_usuario_solicitante' => getCurrentUser()['id'] ?? 1,
                        'itens' => $input['itens'] ?? []
                    ];
                    
                    // Só define status se foi explicitamente fornecido
                    if (isset($input['status'])) {
                        $dados['status'] = $input['status'];
                    }
                    
                    $resultado = $pedidoCompra->criar($dados);
                    echo json_encode(['success' => true, 'message' => 'Pedido criado com sucesso', 'data' => $resultado]);
                    break;
                    
                case 'atualizar_status':
                    try {
                        $input = json_decode(file_get_contents('php://input'), true);
                        
                        if (!$input) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
                            break;
                        }
                        
                        $id_pedido = $input['id_pedido'] ?? null;
                        $novo_status = $input['novo_status'] ?? null;
                        $observacao = $input['observacao'] ?? null;
                        
                        if (!$id_pedido || !$novo_status) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'error' => 'ID do pedido e novo status são obrigatórios']);
                            break;
                        }
                        
                        $resultado = $pedidoCompra->atualizarStatus($id_pedido, $novo_status, $observacao);
                        
                        if ($resultado && isset($resultado['success']) && $resultado['success']) {
                            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
                        } else {
                            $errorMsg = isset($resultado['message']) ? $resultado['message'] : 'Erro ao atualizar status';
                            http_response_code(500);
                            echo json_encode(['success' => false, 'error' => $errorMsg]);
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'PUT':
            switch ($action) {
                case 'update':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
                        break;
                    }
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
                        break;
                    }
                    
                    $pedidoAtual = $pedidoCompra->findById($id);
                    if (!$pedidoAtual) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
                        break;
                    }
                    
                    $statusBloqueadosEdicao = ['enviado', 'em_transito', 'entregue', 'recebido', 'cancelado'];
                    if (in_array($pedidoAtual['status'], $statusBloqueadosEdicao)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Este pedido já foi enviado e não pode mais ser editado']);
                        break;
                    }
                    
                    $dados = [
                        'id_fornecedor' => $input['id_fornecedor'] ?? null,
                        'id_filial' => $input['id_filial'] ?? getCurrentUserFilialId(),
                        'data_entrega_prevista' => validarData($input['data_entrega_prevista'] ?? null),
                        'prioridade' => $input['prioridade'] ?? 'padrao',
                        'prazo_entrega' => $input['prazo_entrega'] ?? 8,
                        'status' => $input['status'] ?? $pedidoAtual['status'],
                        'valor_total' => $input['valor_total'] ?? 0,
                        'observacoes' => $input['observacoes'] ?? '',
                        'itens' => $input['itens'] ?? []
                    ];
                    
                    $pedidoCompra->atualizar($id, $dados);
                    echo json_encode(['success' => true, 'message' => 'Pedido atualizado com sucesso']);
                    break;
                    
                case 'update-status':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
                        break;
                    }
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
                        break;
                    }
                    
                    $novoStatus = $input['status'] ?? null;
                    $observacao = $input['observacao'] ?? '';
                    
                    if (!$novoStatus) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Novo status não fornecido']);
                        break;
                    }
                    
                    // Validar se o status é válido conforme novo fluxo
                    $statusValidos = ['rascunho', 'aguardando_aprovacao', 'aprovado_cotacao', 'aprovado_para_faturar', 'cancelado'];
                    if (!in_array($novoStatus, $statusValidos)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Status inválido']);
                        break;
                    }
                    
                    // Buscar status atual do pedido para o histórico
                    $pedidoAtual = $pedidoCompra->findById($id);
                    if (!$pedidoAtual) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
                        break;
                    }
                    
                    $statusAnterior = $pedidoAtual['status'];
                    
                    // Atualizar apenas o status do pedido
                    $pedidoCompra->atualizarStatus($id, $novoStatus, $observacao);
                    
                    // Registrar no histórico de status
                    try {
                        $queryHistorico = "INSERT INTO tbl_historico_status_pedidos 
                                          (id_pedido, status, observacao, data_alteracao, id_usuario) 
                                          VALUES (:pedido_id, :status_novo, :observacao, NOW(), :usuario_id)";

                        $stmtHistorico = $db->prepare($queryHistorico);
                        $stmtHistorico->bindParam(':pedido_id', $id, PDO::PARAM_INT);
                        $stmtHistorico->bindParam(':status_novo', $novoStatus);
                        $stmtHistorico->bindParam(':observacao', $observacao);
                        $stmtHistorico->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
                        
                        if (!$stmtHistorico->execute()) {
                            error_log("Erro ao registrar histórico de status");
                        }
                    } catch (Exception $e) {
                        error_log("Erro ao registrar histórico de status: " . $e->getMessage());
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'Status do pedido atualizado com sucesso']);
                    break;
                    
                case 'enviar-email':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'ID do pedido não fornecido']);
                        break;
                    }
                    
                    try {
                        // Buscar dados completos do pedido
                        $pedido = $pedidoCompra->buscarPorId($id);
                        if (!$pedido) {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
                            break;
                        }
                        
                        // Buscar dados do fornecedor
                        require_once __DIR__ . '/../models/Fornecedor.php';
                        $fornecedorModel = new Fornecedor();
                        $fornecedor = $fornecedorModel->buscarPorId($pedido['id_fornecedor']);
                        
                        if (!$fornecedor || empty($fornecedor['email'])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'error' => 'Fornecedor não encontrado ou sem email cadastrado']);
                            break;
                        }
                        
                        // Enviar email usando EmailUtils
                        require_once __DIR__ . '/../utils/EmailUtils.php';
                        $resultado = EmailUtils::enviarEmailPedidoCompra($pedido, $fornecedor);
                        
                        if ($resultado) {
                            echo json_encode(['success' => true, 'message' => 'Email enviado com sucesso para o fornecedor']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'error' => 'Erro ao enviar email']);
                        }
                        
                    } catch (Exception $e) {
                        error_log("Erro ao enviar email do pedido: " . $e->getMessage());
                        http_response_code(500);
                        echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
                    }
                    break;
                    
                case 'atualizar-precos':
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input || !isset($input['id_pedido']) || !isset($input['precos'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
                        break;
                    }

                    try {
                        $idPedido = $input['id_pedido'];
                        $precos = $input['precos'];
                        
                        // Validar se o pedido existe e está pendente
                        $pedido = $pedidoCompra->buscarPorId($idPedido);
                        if (!$pedido) {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
                            break;
                        }
                        
                        if ($pedido['status'] !== 'pendente') {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'error' => 'Apenas pedidos pendentes podem ter preços atualizados']);
                            break;
                        }
                        
                        // Atualizar preços dos itens
                        $resultado = $pedidoCompra->atualizarPrecosItens($idPedido, $precos);
                        
                        if ($resultado) {
                            echo json_encode(['success' => true, 'message' => 'Preços atualizados com sucesso']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar preços']);
                        }
                        
                    } catch (Exception $e) {
                        error_log("Erro ao atualizar preços: " . $e->getMessage());
                        http_response_code(500);
                        echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
                break;
            }
            
            $pedidoCompra->excluir($id);
            echo json_encode(['success' => true, 'message' => 'Pedido excluído com sucesso']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
?>