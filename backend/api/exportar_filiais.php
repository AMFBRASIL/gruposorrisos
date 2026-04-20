<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $formato = $_GET['formato'] ?? 'xls';
        $busca = $_GET['busca'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $status = $_GET['status'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        
        // Construir WHERE
        $where = [];
        $params = [];
        
        if (!empty($busca)) {
            $where[] = "(f.nome_filial LIKE ? OR f.codigo_filial LIKE ? OR f.cidade LIKE ? OR f.responsavel LIKE ?)";
            $buscaParam = "%$busca%";
            $params[] = $buscaParam;
            $params[] = $buscaParam;
            $params[] = $buscaParam;
            $params[] = $buscaParam;
        }
        
        if (!empty($estado)) {
            $where[] = "f.estado = ?";
            $params[] = $estado;
        }
        
        if (!empty($status)) {
            $where[] = "f.filial_ativa = ?";
            $params[] = $status;
        }
        
        if (!empty($tipo)) {
            $where[] = "f.tipo_filial = ?";
            $params[] = $tipo;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Query para buscar filiais
        $query = "
            SELECT 
                f.*,
                (SELECT COUNT(*) FROM tbl_usuarios u WHERE u.id_filial = f.id_filial) as total_funcionarios
            FROM tbl_filiais f 
            $whereClause 
            ORDER BY f.nome_filial ASC
        ";
        
        // Verificar se a tabela tbl_usuarios existe
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_usuarios'");
            $usuariosExiste = $stmt->rowCount() > 0;
            
            if (!$usuariosExiste) {
                // Se não existe, usar 0 como total de funcionários
                $query = "
                    SELECT 
                        f.*,
                        0 as total_funcionarios
                    FROM tbl_filiais f 
                    $whereClause 
                    ORDER BY f.nome_filial ASC
                ";
            }
        } catch (Exception $e) {
            // Em caso de erro, usar 0 como total de funcionários
            $query = "
                SELECT 
                    f.*,
                    0 as total_funcionarios
                FROM tbl_filiais f 
                $whereClause 
                ORDER BY f.nome_filial ASC
            ";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($formato === 'xls') {
            exportarXLS($filiais);
        } else {
            exportarPDF($filiais);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}

function exportarXLS($filiais) {
    // Definir headers para download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="filiais_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Criar conteúdo do Excel
    $output = "Código\tNome\tRazão Social\tTipo\tCidade\tEstado\tTelefone\tEmail\tResponsável\tStatus\tFuncionários\tData Inauguração\n";
    
    foreach ($filiais as $filial) {
        $status = $filial['filial_ativa'] ? 'Ativa' : 'Inativa';
        $dataInauguracao = $filial['data_inauguracao'] ? date('d/m/Y', strtotime($filial['data_inauguracao'])) : '';
        
        $output .= implode("\t", [
            $filial['codigo_filial'],
            $filial['nome_filial'],
            $filial['razao_social'],
            $filial['tipo_filial'],
            $filial['cidade'],
            $filial['estado'],
            $filial['telefone'],
            $filial['email'],
            $filial['responsavel'],
            $status,
            $filial['total_funcionarios'],
            $dataInauguracao
        ]) . "\n";
    }
    
    echo $output;
}

function exportarPDF($filiais) {
    // Implementar exportação PDF se necessário
    echo json_encode(['success' => false, 'error' => 'Exportação PDF não implementada']);
} 