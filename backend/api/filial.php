<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';
require_once '../../models/Filial.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $filialModel = new Filial($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $filiais = $filialModel->findAtivas();
                    echo json_encode(['success' => true, 'filiais' => $filiais]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $idFilial = $data['id_filial'] ?? null;
            
            if (!$idFilial) {
                echo json_encode(['success' => false, 'error' => 'ID da filial não informado']);
                break;
            }
            
            try {
                $filial = $filialModel->findById($idFilial);
                if (!$filial) {
                    echo json_encode(['success' => false, 'error' => 'Filial não encontrada']);
                    break;
                }
                
                // Atualiza sessão
                session_start();
                $_SESSION['usuario_filial_id'] = $filial['id_filial'];
                $_SESSION['usuario_filial'] = $filial['nome_filial'];
                $_SESSION['usuario_filial_codigo'] = $filial['codigo_filial'];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Filial alterada com sucesso', 
                    'filial' => $filial
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Erro ao atualizar filial: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 