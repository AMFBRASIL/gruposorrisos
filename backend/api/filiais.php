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

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            // Se não há action, assume list por padrão
            if (empty($action)) {
                listarFiliais();
                break;
            }
            
            switch ($action) {
                case 'list':
                    listarFiliais();
                    break;
                case 'get':
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id > 0) {
                        buscarFilialPorId($id);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                    }
                    break;
                case 'estatisticas':
                    buscarIndicadores();
                    break;
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['action']) && $data['action'] === 'duplicar') {
                duplicarFiliais($data['ids']);
            } else {
                criarFilial($data);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            atualizarFilial($data);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            excluirFilial($data['id']);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}

function listarFiliais() {
    global $pdo;
    
    // Verificar se é uma requisição do seletor (sem parâmetros de paginação)
    $isSelector = !isset($_GET['pagina']) && !isset($_GET['por_pagina']);
    
    // Parâmetros de paginação e filtros
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
    
    $offset = ($pagina - 1) * $porPagina;
    
    // Construir WHERE
    $where = [];
    $params = [];
    
    if (!empty($busca)) {
        $where[] = "(nome_filial LIKE ? OR codigo_filial LIKE ? OR cidade LIKE ? OR responsavel LIKE ?)";
        $buscaParam = "%$busca%";
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
    }
    
    if (!empty($estado)) {
        $where[] = "estado = ?";
        $params[] = $estado;
    }
    
    if ($status !== '' && $status !== null) {
        $where[] = "filial_ativa = ?";
        $params[] = ($status === 'ativa') ? 1 : 0;
    }
    
    if (!empty($tipo)) {
        $where[] = "tipo_filial = ?";
        $params[] = $tipo;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Contar total
    $countSql = "SELECT COUNT(*) as total FROM tbl_filiais $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    // Buscar dados - Se for seletor, buscar todas as filiais sem LIMIT
    if ($isSelector) {
        $sql = "
            SELECT 
                id_filial as id, 
                codigo_filial as codigo, 
                nome_filial as nome, 
                tipo_filial as tipo,
                cep, endereco, cidade, estado,
                telefone, email, responsavel, cnpj,
                CASE WHEN filial_ativa = 1 THEN 'ativa' ELSE 'inativa' END as status, 
                data_inauguracao,
                data_criacao, 
                data_atualizacao,
                0 as total_funcionarios
            FROM tbl_filiais 
            $whereClause 
            ORDER BY codigo_filial
        ";
    } else {
        $sql = "
            SELECT 
                id_filial as id, 
                codigo_filial as codigo, 
                nome_filial as nome, 
                tipo_filial as tipo,
                cep, endereco, cidade, estado,
                telefone, email, responsavel, cnpj,
                CASE WHEN filial_ativa = 1 THEN 'ativa' ELSE 'inativa' END as status, 
                data_inauguracao,
                data_criacao, 
                data_atualizacao,
                0 as total_funcionarios
            FROM tbl_filiais 
            $whereClause 
            ORDER BY codigo_filial 
            LIMIT $porPagina OFFSET $offset
        ";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filiais = $stmt->fetchAll();
    
    // Buscar indicadores
    $indicadoresStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN filial_ativa = 1 THEN 1 ELSE 0 END) as ativas,
            SUM(CASE WHEN filial_ativa = 0 THEN 1 ELSE 0 END) as inativas,
            0 as funcionarios
        FROM tbl_filiais
    ");
    $indicadoresStmt->execute();
    $indicadores = $indicadoresStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'filiais' => $filiais,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $porPagina,
        'total_paginas' => $isSelector ? 1 : ceil($total / $porPagina),
        'indicadores' => $indicadores
    ]);
}

function buscarFilialPorId($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            id_filial, codigo_filial, nome_filial, razao_social, tipo_filial,
            cnpj, inscricao_estadual, endereco, cidade, estado,
            cep, telefone, email, responsavel, filial_ativa, data_inauguracao,
            data_criacao, data_atualizacao, observacoes
        FROM tbl_filiais 
        WHERE id_filial = ?
    ");
    $stmt->execute([$id]);
    $filial = $stmt->fetch();
    
    if ($filial) {
        // Mapear campos para compatibilidade com o frontend
        $filial['id'] = $filial['id_filial'];
        $filial['codigo'] = $filial['codigo_filial'];
        $filial['nome'] = $filial['nome_filial'];
        $filial['tipo'] = $filial['tipo_filial'];
        $filial['status'] = $filial['filial_ativa'] == 1 ? 'ativa' : 'inativa';
        $filial['data_abertura'] = $filial['data_inauguracao'];
        $filial['created_at'] = $filial['data_criacao'];
        $filial['updated_at'] = $filial['data_atualizacao'];
        
        echo json_encode(['success' => true, 'filial' => $filial]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Filial não encontrada']);
    }
}

function buscarIndicadores() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_filiais,
            SUM(CASE WHEN filial_ativa = 1 THEN 1 ELSE 0 END) as filiais_ativas,
            SUM(CASE WHEN filial_ativa = 0 THEN 1 ELSE 0 END) as filiais_inativas
        FROM tbl_filiais
    ");
    $stmt->execute();
    $indicadores = $stmt->fetch();
    
    echo json_encode(['success' => true, 'indicadores' => $indicadores]);
}

function criarFilial($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tbl_filiais (
                codigo_filial, nome_filial, razao_social, tipo_filial,
                cnpj, inscricao_estadual, endereco, cidade, estado,
                cep, telefone, email, responsavel, filial_ativa, data_inauguracao,
                observacoes
            ) VALUES (
                :codigo_filial, :nome_filial, :razao_social, :tipo_filial,
                :cnpj, :inscricao_estadual, :endereco, :cidade, :estado,
                :cep, :telefone, :email, :responsavel, :filial_ativa, :data_inauguracao,
                :observacoes
            )
        ");
        
        $resultado = $stmt->execute([
            'codigo_filial' => $data['codigo'],
            'nome_filial' => $data['nome'],
            'razao_social' => $data['razao_social'] ?? '',
            'tipo_filial' => $data['tipo'],
            'cnpj' => $data['cnpj'] ?? null,
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'endereco' => $data['endereco'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'cep' => $data['cep'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'responsavel' => $data['responsavel'] ?? null,
            'filial_ativa' => $data['status'] === 'ativa' ? 1 : 0,
            'data_inauguracao' => $data['data_abertura'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        if ($resultado) {
            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'id_filial' => $id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao criar filial']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao criar filial: ' . $e->getMessage()]);
    }
}

function atualizarFilial($data) {
    global $pdo;
    
    // Debug: verificar o que está sendo enviado
    error_log('Dados recebidos na atualização: ' . json_encode($data));
    
    try {
        $stmt = $pdo->prepare("
            UPDATE tbl_filiais SET
                codigo_filial = :codigo_filial,
                nome_filial = :nome_filial,
                razao_social = :razao_social,
                tipo_filial = :tipo_filial,
                cnpj = :cnpj,
                inscricao_estadual = :inscricao_estadual,
                endereco = :endereco,
                cidade = :cidade,
                estado = :estado,
                cep = :cep,
                telefone = :telefone,
                email = :email,
                responsavel = :responsavel,
                filial_ativa = :filial_ativa,
                data_inauguracao = :data_inauguracao,
                observacoes = :observacoes,
                data_atualizacao = NOW()
            WHERE id_filial = :id_filial
        ");
        
        $resultado = $stmt->execute([
            'id_filial' => $data['id'],
            'codigo_filial' => $data['codigo'],
            'nome_filial' => $data['nome'],
            'razao_social' => $data['razao_social'] ?? '',
            'tipo_filial' => $data['tipo'],
            'cnpj' => $data['cnpj'] ?? null,
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'endereco' => $data['endereco'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'cep' => $data['cep'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'responsavel' => $data['responsavel'] ?? null,
            'filial_ativa' => $data['status'] === 'ativa' ? 1 : 0,
            'data_inauguracao' => $data['data_abertura'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        echo json_encode(['success' => $resultado]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao atualizar filial: ' . $e->getMessage()]);
    }
}

function excluirFilial($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE tbl_filiais SET filial_ativa = 0 WHERE id_filial = ?");
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Filial excluída com sucesso']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir filial']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao excluir filial: ' . $e->getMessage()]);
    }
}

function duplicarFiliais($ids) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        foreach ($ids as $id) {
            // Buscar filial original
            $stmt = $pdo->prepare("SELECT * FROM tbl_filiais WHERE id_filial = ?");
            $stmt->execute([$id]);
            $filial = $stmt->fetch();
            
            if ($filial) {
                // Gerar novo código - solução simples
                $novoCodigo = NULL;
                
                // CNPJ não será duplicado pois é um documento único
                $novoCnpj = null;
                
                // Inserir cópia
                $stmt = $pdo->prepare("
                    INSERT INTO tbl_filiais (
                        codigo_filial, nome_filial, razao_social, tipo_filial,
                        cnpj, inscricao_estadual, endereco, cidade, estado,
                        cep, telefone, email, responsavel, filial_ativa, data_inauguracao,
                        observacoes
                    ) VALUES (
                        :codigo_filial, :nome_filial, :razao_social, :tipo_filial,
                        :cnpj, :inscricao_estadual, :endereco, :cidade, :estado,
                        :cep, :telefone, :email, :responsavel, :filial_ativa, :data_inauguracao,
                        :observacoes
                    )
                ");
                
                $stmt->execute([
                    'codigo_filial' => date("dmY"),
                    'nome_filial' => $filial['nome_filial'] . ' COPIA',
                    'razao_social' => $filial['razao_social'],
                    'tipo_filial' => $filial['tipo_filial'],
                    'cnpj' => $novoCnpj,
                    'inscricao_estadual' => $filial['inscricao_estadual'],
                    'endereco' => $filial['endereco'],
                    'cidade' => $filial['cidade'],
                    'estado' => $filial['estado'],
                    'cep' => $filial['cep'],
                    'telefone' => $filial['telefone'],
                    'email' => $filial['email'],
                    'responsavel' => $filial['responsavel'],
                    'filial_ativa' => $filial['filial_ativa'],
                    'data_inauguracao' => $filial['data_inauguracao'],
                    'observacoes' => $filial['observacoes']
                ]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Erro ao duplicar filiais: ' . $e->getMessage()]);
    }
} 