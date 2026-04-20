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

function ConsultaCNPJWSNew($CNPJ) {
    if (empty($CNPJ)) {
        return false;
    }

    // Remover caracteres especiais do CNPJ
    $CNPJ = preg_replace('/[^0-9]/', '', $CNPJ);

    if (strlen($CNPJ) !== 14) {
        return false;
    }

    $url = "https://comercial.cnpj.ws/cnpj/" . $CNPJ;

    // Setup the request
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Set your auth headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "x_api_token: Mk43QWISaip3RTodBchBknczqlPuRf3nVOJZ7eP5XS7j",
    ));

    // Get stringified data/output
    $data = curl_exec($ch);

    // Get info about the request
    $info = curl_getinfo($ch);
    
    // Close curl resource to free up system resources
    curl_close($ch);

    if (!$data) {
        error_log("Erro na consulta CNPJ: curl_exec retornou false");
        return false;
    }

    $decoded = json_decode($data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erro ao decodificar JSON da consulta CNPJ: " . json_last_error_msg());
        error_log("Dados recebidos: " . $data);
        return false;
    }

    return $decoded;
}

// Processar requisições
try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            $cnpj = $_GET['cnpj'] ?? '';
            
            if (empty($cnpj)) {
                echo json_encode(['success' => false, 'error' => 'CNPJ não fornecido']);
                exit;
            }
            
            $resultado = ConsultaCNPJWSNew($cnpj);
            
            if ($resultado === false) {
                echo json_encode(['success' => false, 'error' => 'Erro ao consultar CNPJ']);
            } else {
                // Verificar se a consulta foi bem-sucedida
                if (isset($resultado['estabelecimento'])) {
                    $estabelecimento = $resultado['estabelecimento'] ?? [];
                    
                    // Formatar telefone
                    $telefone = '';
                    if (!empty($estabelecimento['ddd1']) && !empty($estabelecimento['telefone1'])) {
                        $telefone = $estabelecimento['ddd1'] . $estabelecimento['telefone1'];
                    }
                    
                    // Formatar endereço completo
                    $endereco = '';
                    if (!empty($estabelecimento['tipo_logradouro'])) {
                        $endereco .= $estabelecimento['tipo_logradouro'] . ' ';
                    }
                    $endereco .= $estabelecimento['logradouro'] ?? '';
                    
                    // Buscar primeira inscrição estadual ativa
                    $inscricao_estadual = '';
                    if (isset($resultado['estabelecimento']['inscricoes_estaduais']) && 
                        is_array($resultado['estabelecimento']['inscricoes_estaduais'])) {
                        foreach ($resultado['estabelecimento']['inscricoes_estaduais'] as $ie) {
                            if (isset($ie['ativo']) && $ie['ativo']) {
                                $inscricao_estadual = $ie['inscricao_estadual'] ?? '';
                                break;
                            }
                        }
                    }
                    
                    $dados = [
                        'success' => true,
                        'razao_social' => $resultado['razao_social'] ?? '',
                        'nome_fantasia' => $estabelecimento['nome_fantasia'] ?? '',
                        'cnpj' => $estabelecimento['cnpj'] ?? $cnpj,
                        'inscricao_estadual' => $inscricao_estadual,
                        'endereco' => $endereco,
                        'numero' => $estabelecimento['numero'] ?? '',
                        'complemento' => $estabelecimento['complemento'] ?? '',
                        'bairro' => $estabelecimento['bairro'] ?? '',
                        'cidade' => $estabelecimento['cidade']['nome'] ?? '',
                        'estado' => $estabelecimento['estado']['sigla'] ?? '',
                        'cep' => $estabelecimento['cep'] ?? '',
                        'telefone' => $telefone,
                        'email' => $estabelecimento['email'] ?? '',
                        'situacao' => $estabelecimento['situacao_cadastral'] ?? '',
                        'data_abertura' => $estabelecimento['data_inicio_atividade'] ?? '',
                        'porte' => $resultado['porte']['descricao'] ?? '',
                        'natureza_juridica' => $resultado['natureza_juridica']['descricao'] ?? '',
                        'capital_social' => $resultado['capital_social'] ?? ''
                    ];
                    
                    echo json_encode($dados);
                } else {
                    echo json_encode(['success' => false, 'error' => 'CNPJ não encontrado ou inválido']);
                }
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}