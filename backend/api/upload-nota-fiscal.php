<?php
// Habilitar exibição de erros para debug (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexao.php';

// Verificar se o helper existe antes de incluir
$helperPath = __DIR__ . '/../../helpers/S3Uploader.php';
if (!file_exists($helperPath)) {
    error_log("S3Uploader não encontrado em: " . $helperPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro de configuração do sistema']);
    exit;
}
require_once $helperPath;

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

// Verificar se é fornecedor
if ($_SESSION['usuario_perfil'] !== 'Fornecedor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Apenas fornecedores podem fazer upload de NF.']);
    exit;
}

header('Content-Type: application/json');

try {
    // Verificar se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Verificar se há arquivo enviado
    if (!isset($_FILES['nota_fiscal']) || $_FILES['nota_fiscal']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nenhum arquivo enviado ou erro no upload');
    }
    
    $file = $_FILES['nota_fiscal'];
    $pedidoId = $_POST['pedido_id'] ?? null;
    
    if (!$pedidoId) {
        throw new Exception('ID do pedido não fornecido');
    }
    
    // Validar pedido
    $pdo = Conexao::getInstance()->getPdo();
    
    // Primeiro, verificar se o usuário está vinculado a um fornecedor
    $stmt = $pdo->prepare("SELECT id_usuario, id_fornecedor FROM tbl_usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario || !$usuario['id_fornecedor']) {
        throw new Exception('Usuário não está vinculado a um fornecedor');
    }
    
    $fornecedorId = $usuario['id_fornecedor'];
    
    // Buscar pedido (verificar se campo ativo existe)
    try {
        $stmt = $pdo->prepare("SELECT id_pedido, id_fornecedor, status, url_nota_fiscal FROM tbl_pedidos_compra WHERE id_pedido = ? AND (ativo = 1 OR ativo IS NULL)");
        $stmt->execute([$pedidoId]);
    } catch (PDOException $e) {
        // Se campo ativo não existir, fazer query sem ele
        $stmt = $pdo->prepare("SELECT id_pedido, id_fornecedor, status, url_nota_fiscal FROM tbl_pedidos_compra WHERE id_pedido = ?");
        $stmt->execute([$pedidoId]);
    }
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido não encontrado');
    }
    
    // Verificar se o pedido pertence ao fornecedor
    if ($pedido['id_fornecedor'] != $fornecedorId) {
        throw new Exception('Pedido não pertence ao fornecedor');
    }
    
    // Verificar se o status permite upload de NF
    // Permitir upload quando: aprovado_para_faturar, em_transito, entregue
    $statusPermitidos = ['aprovado_para_faturar', 'em_transito', 'entregue', 'aprovado_cotacao'];
    if (!in_array($pedido['status'], $statusPermitidos)) {
        throw new Exception('Status do pedido não permite upload de Nota Fiscal. Status atual: ' . $pedido['status']);
    }
    
    // Validar tipo de arquivo
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    if (!S3Uploader::validateFileType($file['name'], $allowedTypes)) {
        throw new Exception('Tipo de arquivo não permitido. Apenas: ' . implode(', ', $allowedTypes));
    }
    
    // Validar tamanho (máximo 10MB)
    if (!S3Uploader::validateFileSize($file['size'], 10)) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 10MB');
    }
    
    // Fazer upload
    $uploader = new S3Uploader();
    $result = $uploader->uploadFile(
        $file['tmp_name'],
        $file['name'],
        'notas-fiscais/pedido-' . $pedidoId
    );
    
    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Erro ao fazer upload');
    }
    
    // Salvar URL no banco de dados
    $urlNotaFiscal = $result['url'];
    $keyNotaFiscal = $result['key'] ?? null;
    
    // Se já existe uma NF, deletar a anterior (arquivo local)
    if (!empty($pedido['url_nota_fiscal'])) {
        // Remover barra inicial se existir para construir caminho absoluto
        $oldPath = ltrim($pedido['url_nota_fiscal'], '/');
        $oldFullPath = __DIR__ . '/../../' . $oldPath;
        
        // Verificar se é um arquivo local e deletar
        if (file_exists($oldFullPath) && is_file($oldFullPath)) {
            @unlink($oldFullPath);
        }
    }
    
    $stmt = $pdo->prepare("UPDATE tbl_pedidos_compra SET url_nota_fiscal = ? WHERE id_pedido = ?");
    $stmt->execute([$urlNotaFiscal, $pedidoId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Nota Fiscal enviada com sucesso!',
        'url' => $urlNotaFiscal,
        'key' => $keyNotaFiscal
    ]);
    
} catch (Exception $e) {
    error_log("Erro no upload de NF: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Erro fatal no upload de NF: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor. Verifique os logs para mais detalhes.'
    ]);
}
