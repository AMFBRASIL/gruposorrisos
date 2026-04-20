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
        // Array com todos os estados brasileiros
        $estados = [
            ['uf' => 'AC', 'nome' => 'Acre'],
            ['uf' => 'AL', 'nome' => 'Alagoas'],
            ['uf' => 'AP', 'nome' => 'Amapá'],
            ['uf' => 'AM', 'nome' => 'Amazonas'],
            ['uf' => 'BA', 'nome' => 'Bahia'],
            ['uf' => 'CE', 'nome' => 'Ceará'],
            ['uf' => 'DF', 'nome' => 'Distrito Federal'],
            ['uf' => 'ES', 'nome' => 'Espírito Santo'],
            ['uf' => 'GO', 'nome' => 'Goiás'],
            ['uf' => 'MA', 'nome' => 'Maranhão'],
            ['uf' => 'MT', 'nome' => 'Mato Grosso'],
            ['uf' => 'MS', 'nome' => 'Mato Grosso do Sul'],
            ['uf' => 'MG', 'nome' => 'Minas Gerais'],
            ['uf' => 'PA', 'nome' => 'Pará'],
            ['uf' => 'PB', 'nome' => 'Paraíba'],
            ['uf' => 'PR', 'nome' => 'Paraná'],
            ['uf' => 'PE', 'nome' => 'Pernambuco'],
            ['uf' => 'PI', 'nome' => 'Piauí'],
            ['uf' => 'RJ', 'nome' => 'Rio de Janeiro'],
            ['uf' => 'RN', 'nome' => 'Rio Grande do Norte'],
            ['uf' => 'RS', 'nome' => 'Rio Grande do Sul'],
            ['uf' => 'RO', 'nome' => 'Rondônia'],
            ['uf' => 'RR', 'nome' => 'Roraima'],
            ['uf' => 'SC', 'nome' => 'Santa Catarina'],
            ['uf' => 'SP', 'nome' => 'São Paulo'],
            ['uf' => 'SE', 'nome' => 'Sergipe'],
            ['uf' => 'TO', 'nome' => 'Tocantins']
        ];

        // Ordenar por nome
        usort($estados, function($a, $b) {
            return strcmp($a['nome'], $b['nome']);
        });

        echo json_encode([
            'success' => true,
            'estados' => $estados
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 