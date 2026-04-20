<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';
require_once '../controllers/ControllerLogs.php';

try {
    $controller = new ControllerLogs();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $filtros = [
                'page' => $_GET['page'] ?? 1,
                'limit' => $_GET['limit'] ?? 50,
                'search' => $_GET['search'] ?? '',
                'usuario' => $_GET['usuario'] ?? '',
                'filial' => $_GET['filial'] ?? '',
                'acao' => $_GET['acao'] ?? '',
                'tabela' => $_GET['tabela'] ?? '',
                'data_inicio' => $_GET['data_inicio'] ?? '',
                'data_fim' => $_GET['data_fim'] ?? '',
                'ip' => $_GET['ip'] ?? ''
            ];
            
            $result = $controller->listar($filtros);
            
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
            $log = $controller->buscarPorId($id);
            
            echo json_encode([
                'success' => true,
                'data' => $log
            ]);
            break;
            
        case 'estatisticas':
            $estatisticas = $controller->obterEstatisticas();
            
            echo json_encode([
                'success' => true,
                'data' => $estatisticas
            ]);
            break;
            
        case 'acoes':
            $acoes = $controller->obterAcoes();
            
            echo json_encode([
                'success' => true,
                'data' => $acoes
            ]);
            break;
            
        case 'tabelas':
            $tabelas = $controller->obterTabelas();
            
            echo json_encode([
                'success' => true,
                'data' => $tabelas
            ]);
            break;
            
        case 'usuarios':
            $usuarios = $controller->obterUsuarios();
            
            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);
            break;
            
        case 'filiais':
            $filiais = $controller->obterFiliais();
            
            echo json_encode([
                'success' => true,
                'data' => $filiais
            ]);
            break;
            
        case 'export':
            $filtros = [
                'search' => $_GET['search'] ?? '',
                'usuario' => $_GET['usuario'] ?? '',
                'filial' => $_GET['filial'] ?? '',
                'acao' => $_GET['acao'] ?? '',
                'tabela' => $_GET['tabela'] ?? '',
                'data_inicio' => $_GET['data_inicio'] ?? '',
                'data_fim' => $_GET['data_fim'] ?? '',
                'ip' => $_GET['ip'] ?? ''
            ];
            
            $csv = $controller->exportarCSV($filtros);
            
            // Mudar header para download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="logs_sistema_' . date('Y-m-d_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // BOM UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csv as $row) {
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
            exit;
            break;
            
        case 'limpar':
            $dias = $_GET['dias'] ?? 90;
            $result = $controller->limparLogsAntigos($dias);
            
            echo json_encode($result);
            break;
            
        case 'count_por_acao':
            $dados = $controller->contarPorAcao();
            
            echo json_encode([
                'success' => true,
                'data' => $dados
            ]);
            break;
            
        case 'count_por_usuario':
            $limite = $_GET['limite'] ?? 10;
            $dados = $controller->contarPorUsuario($limite);
            
            echo json_encode([
                'success' => true,
                'data' => $dados
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





