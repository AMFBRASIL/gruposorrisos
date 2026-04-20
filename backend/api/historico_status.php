<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get':
            $pedido_id = $_GET['pedido_id'] ?? null;
            
            if (!$pedido_id) {
                throw new Exception('ID do pedido é obrigatório');
            }
            
            // Buscar histórico de status do pedido
            $query = "SELECT 
                        hs.id_historico as id,
                        LAG(hs.status) OVER (PARTITION BY hs.id_pedido ORDER BY hs.data_alteracao) as status_anterior,
                        hs.status as status_novo,
                        hs.status as status,  -- Adicionar campo status diretamente
                        hs.observacao,
                        hs.data_alteracao,
                        hs.id_usuario as usuario_id,
                        u.nome_completo as usuario_nome
                      FROM tbl_historico_status_pedidos hs
                      LEFT JOIN tbl_usuarios u ON hs.id_usuario = u.id_usuario
                      WHERE hs.id_pedido = :pedido_id
                      ORDER BY hs.data_alteracao ASC";
            
            // Log para debug
            error_log("Buscando histórico para pedido ID: {$pedido_id}");
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para debug
            error_log("Histórico bruto retornado para pedido {$pedido_id}: " . json_encode($historico));
            
            if (empty($historico)) {
                error_log("Nenhum histórico encontrado para pedido ID: {$pedido_id}");
            }
            
            // Mapear nomes dos status (todos os possíveis)
            $status_map = [
                'em_analise' => 'Em Análise',
                'pendente' => 'Pendente',
                'aprovado_cotacao' => 'Aprovado para Cotação',
                'aprovado' => 'Aprovado',
                'enviar_para_faturamento' => 'Enviar para Faturamento',
                'enviar_faturamento' => 'Enviar para Faturamento', // Compatibilidade
                'aprovado_para_faturar' => 'Aprovado para Faturar',
                'faturado' => 'Faturado',
                'em_producao' => 'Em Produção',
                'em_transito' => 'Em Trânsito',
                'enviado' => 'Enviado',
                'entregue' => 'Entregue',
                'recebido' => 'Recebido',
                'rejeitado' => 'Rejeitado',
                'em_entrega' => 'Em Entrega',
                'atrasado' => 'Atrasado',
                'urgente' => 'Urgente',
                'aguardando_aprovacao' => 'Aguardando Aprovação',
                'parcialmente_recebido' => 'Parcialmente Recebido',
                'cancelado' => 'Cancelado'
            ];
            
            // Formatar histórico
            $historico_formatado = [];
            foreach ($historico as $item) {
                $statusNovo = $item['status_novo'] ?? null;
                $statusAnterior = $item['status_anterior'] ?? null;
                
                // Log para debug (remover em produção se necessário)
                if (!$statusNovo) {
                    error_log("Histórico sem status_novo: " . json_encode($item));
                }
                
                // Se status_novo não existir, tentar usar o campo 'status' diretamente
                if (!$statusNovo && isset($item['status'])) {
                    $statusNovo = $item['status'];
                }
                
                $historico_formatado[] = [
                    'id' => $item['id'] ?? null,
                    'status' => $statusNovo, // Adicionar campo 'status' para compatibilidade
                    'status_anterior' => $statusAnterior,
                    'status_anterior_nome' => $statusAnterior ? ($status_map[$statusAnterior] ?? ucfirst(str_replace('_', ' ', $statusAnterior))) : null,
                    'status_novo' => $statusNovo,
                    'status_novo_nome' => $statusNovo ? ($status_map[$statusNovo] ?? ucfirst(str_replace('_', ' ', $statusNovo))) : null,
                    'observacao' => $item['observacao'] ?? null,
                    'data_alteracao' => $item['data_alteracao'] ?? null,
                    'usuario_id' => $item['usuario_id'] ?? null,
                    'usuario_nome' => $item['usuario_nome'] ?? 'Sistema',
                    'usuario' => $item['usuario_nome'] ?? 'Sistema' // Adicionar campo 'usuario' para compatibilidade
                ];
            }
            
            // Log para debug
            error_log("Histórico formatado para pedido {$pedido_id}: " . json_encode($historico_formatado));
            
            echo json_encode([
                'success' => true,
                'historico' => $historico_formatado
            ]);
            break;
            
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $pedido_id = $input['pedido_id'] ?? null;
            $status_anterior = $input['status_anterior'] ?? null;
            $status_novo = $input['status_novo'] ?? null;
            $observacao = $input['observacao'] ?? null;
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            
            if (!$pedido_id || !$status_novo) {
                throw new Exception('Dados obrigatórios não fornecidos');
            }
            
            // Inserir no histórico
            $query = "INSERT INTO tbl_historico_status_pedidos 
                      (id_pedido, status, observacao, data_alteracao, id_usuario) 
                      VALUES (:pedido_id, :status_novo, :observacao, NOW(), :usuario_id)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->bindParam(':status_novo', $status_novo);
            $stmt->bindParam(':observacao', $observacao);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Histórico registrado com sucesso'
                ]);
            } else {
                throw new Exception('Erro ao registrar histórico');
            }
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>