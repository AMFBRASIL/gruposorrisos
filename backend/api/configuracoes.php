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
require_once '../../models/Configuracao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $configuracao = new Configuracao($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'buscar_por_chave':
                    $chave = $_GET['chave'] ?? '';
                    if (empty($chave)) {
                        echo json_encode(['success' => false, 'error' => 'Chave é obrigatória']);
                        break;
                    }
                    
                    $resultado = $configuracao->buscarPorChave($chave);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'buscar_por_categoria':
                    $categoria = $_GET['categoria'] ?? '';
                    if (empty($categoria)) {
                        echo json_encode(['success' => false, 'error' => 'Categoria é obrigatória']);
                        break;
                    }
                    
                    $resultado = $configuracao->buscarPorCategoria($categoria);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'buscar_agrupadas':
                    $resultado = $configuracao->buscarAgrupadasPorCategoria();
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'estatisticas':
                    $resultado = $configuracao->getEstatisticas();
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                default:
                    // Buscar todas as configurações
                    $resultado = $configuracao->buscarTodas();
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
            }
            break;
            
        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (isset($dados['action'])) {
                switch ($dados['action']) {
                    case 'atualizar_multiplas':
                        if (!isset($dados['configuracoes']) || !is_array($dados['configuracoes'])) {
                            echo json_encode(['success' => false, 'error' => 'Configurações são obrigatórias']);
                            break;
                        }
                        
                        $configuracao->atualizarConfiguracoes($dados['configuracoes']);
                        echo json_encode(['success' => true, 'message' => 'Configurações atualizadas com sucesso']);
                        break;
                        
                    case 'definir_valor':
                        if (!isset($dados['chave']) || !isset($dados['valor'])) {
                            echo json_encode(['success' => false, 'error' => 'Chave e valor são obrigatórios']);
                            break;
                        }
                        
                        $configuracao->setValor($dados['chave'], $dados['valor']);
                        echo json_encode(['success' => true, 'message' => 'Configuração atualizada com sucesso']);
                        break;
                        
                    default:
                        echo json_encode(['success' => false, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
            }
            break;
            
        case 'PUT':
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($dados['chave']) || !isset($dados['valor'])) {
                echo json_encode(['success' => false, 'error' => 'Chave e valor são obrigatórios']);
                break;
            }
            
            $configuracao->setValor($dados['chave'], $dados['valor']);
            echo json_encode(['success' => true, 'message' => 'Configuração atualizada com sucesso']);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 