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
        // Listar filiais
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
        
        $offset = ($pagina - 1) * $porPagina;
        
        // Construir WHERE
        $where = [];
        $params = [];
        
        if (!empty($busca)) {
            $where[] = "(nome_filial LIKE ? OR codigo_filial LIKE ? OR cidade LIKE ?)";
            $buscaParam = "%$busca%";
            $params[] = $buscaParam;
            $params[] = $buscaParam;
            $params[] = $buscaParam;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Query para contar total
        $countQuery = "SELECT COUNT(*) as total FROM tbl_filiais $whereClause";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query para buscar filiais
        $query = "
            SELECT 
                *,
                0 as total_funcionarios
            FROM tbl_filiais 
            $whereClause 
            ORDER BY nome_filial ASC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $porPagina;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar indicadores
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN filial_ativa = '1' THEN 1 ELSE 0 END) as ativas,
                SUM(CASE WHEN filial_ativa = '0' THEN 1 ELSE 0 END) as inativas,
                0 as funcionarios
            FROM tbl_filiais
        ");
        $stmt->execute();
        $indicadores = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'filiais' => $filiais,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'indicadores' => $indicadores
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 