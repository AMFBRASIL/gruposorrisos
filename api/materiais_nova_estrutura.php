<?php
/**
 * NOVA API DE MATERIAIS - ESTRUTURA CENTRALIZADA
 * Grupo Sorrisos
 * 
 * Esta API usa a nova estrutura:
 * - tbl_catalogo_materiais (materiais centralizados)
 * - tbl_estoque_filiais (estoque por filial)
 */

// Desabilitar exibição de erros para evitar corromper o JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Handler para erros fatais (garante resposta JSON)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erro interno do servidor',
            'details' => $error['message'] . ' em ' . $error['file'] . ' linha ' . $error['line']
        ]);
        error_log("Erro fatal na API de materiais: " . print_r($error, true));
    }
});

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/CatalogoMaterial.php';
require_once __DIR__ . '/../models/EstoqueFilial.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Fornecedor.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $catalogo = new CatalogoMaterial($pdo);
    $estoque = new EstoqueFilial($pdo);
    $categoria = new Categoria($pdo);
    $fornecedor = new Fornecedor($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($catalogo, $estoque, $categoria, $fornecedor, $action);
            break;
        case 'POST':
            handlePost($catalogo, $estoque, $action);
            break;
        case 'PUT':
            handlePut($catalogo, $estoque, $action);
            break;
        case 'DELETE':
            handleDelete($catalogo, $estoque, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

/**
 * Manipula requisições GET
 */
function handleGet($catalogo, $estoque, $categoria, $fornecedor, $action) {
    switch ($action) {
        case 'list':
            listarMateriais($estoque);
            break;
            
        case 'catalogo':
            listarCatalogo($catalogo);
            break;
            
        case 'get':
            buscarMaterial($estoque);
            break;
            
        case 'categorias':
            listarCategorias($categoria);
            break;
            
        case 'fornecedores':
            listarFornecedores($fornecedor);
            break;
            
        case 'estatisticas':
            buscarEstatisticas($estoque);
            break;
            
        case 'estoque-baixo':
            buscarEstoqueBaixo($estoque);
            break;
            
        case 'estoque-zerado':
            buscarEstoqueZerado($estoque);
            break;
            
        case 'materiais-sem-estoque':
            buscarMateriaisSemEstoque($estoque);
            break;
            
        case 'marcas':
            buscarMarcas($catalogo);
            break;
            
        case 'modelos':
            buscarModelos($catalogo);
            break;
            
        case 'cores':
            buscarCores($catalogo);
            break;
            
        case 'tamanhos':
            buscarTamanhos($catalogo);
            break;
            
        case 'filiais':
            listarFiliais();
            break;
            
        case 'buscar-estoque':
            buscarEstoqueEspecifico($estoque);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

/**
 * Lista materiais com estoque (por filial)
 */
function listarMateriais($estoque) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    
    // Obter filial do parâmetro ou usar a do usuário logado
    $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
    
    // Busca genérica - procura em código, nome e descrição
    $busca = $_GET['busca'] ?? $_GET['codigo'] ?? null;
    
    $filters = [
        'id_filial' => $filialId,
        'id_categoria' => $_GET['categoria'] ?? null,
        'id_fornecedor' => $_GET['fornecedor'] ?? null,
        'busca' => $busca, // Busca genérica em múltiplos campos
        'em_estoque' => isset($_GET['em_estoque']) ? true : false,
        'estoque_baixo' => isset($_GET['estoque_baixo']) ? true : false,
        'estoque_zerado' => isset($_GET['estoque_zerado']) ? true : false,
        'precisa_ressuprimento' => isset($_GET['precisa_ressuprimento']) ? true : false
    ];
    
    $result = $estoque->findWithFilters($filters, $page, $limit);
    
    // Formatar dados para compatibilidade com frontend
    $result['success'] = true;
    $result['data'] = array_map('formatarMaterialEstoque', $result['data']);
    
    echo json_encode($result);
}

/**
 * Lista catálogo de materiais (sem estoque)
 */
function listarCatalogo($catalogo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    
    $filters = [
        'codigo' => $_GET['codigo'] ?? null,
        'nome' => $_GET['nome'] ?? null,
        'id_categoria' => $_GET['categoria'] ?? null,
        'id_fornecedor' => $_GET['fornecedor'] ?? null,
        'marca' => $_GET['marca'] ?? null
    ];
    
    $result = $catalogo->findWithFilters($filters, $page, $limit);
    $result['success'] = true;
    
    echo json_encode($result);
}

/**
 * Busca material específico
 */
function buscarMaterial($estoque) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID não fornecido']);
        return;
    }
    
    $material = $estoque->findByIdWithRelations($id);
    if ($material) {
        echo json_encode([
            'success' => true, 
            'data' => formatarMaterialEstoque($material)
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Material não encontrado']);
    }
}

/**
 * Lista categorias
 */
function listarCategorias($categoria) {
    try {
        $categorias = $categoria->findAll();
        echo json_encode([
            'success' => true,
            'data' => $categorias
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar categorias: ' . $e->getMessage()]);
    }
}

/**
 * Lista fornecedores
 */
function listarFornecedores($fornecedor) {
    try {
        $fornecedores = $fornecedor->findAll();
        echo json_encode([
            'success' => true,
            'data' => $fornecedores
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar fornecedores: ' . $e->getMessage()]);
    }
}

/**
 * Busca estatísticas
 */
function buscarEstatisticas($estoque) {
    try {
        $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
        $stats = $estoque->getEstatisticasPorFilial($filialId);
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar estatísticas: ' . $e->getMessage()]);
    }
}

/**
 * Busca estoque baixo
 */
function buscarEstoqueBaixo($estoque) {
    try {
        $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
        $materiais = $estoque->findEstoqueBaixo($filialId);
        
        echo json_encode([
            'success' => true,
            'data' => array_map('formatarMaterialEstoque', $materiais)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar estoque baixo: ' . $e->getMessage()]);
    }
}

/**
 * Busca estoque zerado
 */
function buscarEstoqueZerado($estoque) {
    try {
        $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
        $materiais = $estoque->findEstoqueZerado($filialId);
        
        echo json_encode([
            'success' => true,
            'data' => array_map('formatarMaterialEstoque', $materiais)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar estoque zerado: ' . $e->getMessage()]);
    }
}

/**
 * Busca materiais sem estoque
 */
function buscarMateriaisSemEstoque($estoque) {
    try {
        $filialId = $_GET['filial_id'] ?? getCurrentUserFilialId();
        $materiais = $estoque->findMateriaisSemEstoque($filialId);
        
        echo json_encode([
            'success' => true,
            'data' => $materiais
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar materiais sem estoque: ' . $e->getMessage()]);
    }
}

/**
 * Busca marcas disponíveis
 */
function buscarMarcas($catalogo) {
    try {
        $marcas = $catalogo->getMarcas();
        echo json_encode([
            'success' => true,
            'data' => $marcas
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar marcas: ' . $e->getMessage()]);
    }
}

/**
 * Busca modelos disponíveis
 */
function buscarModelos($catalogo) {
    try {
        $modelos = $catalogo->getModelos();
        echo json_encode([
            'success' => true,
            'data' => $modelos
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar modelos: ' . $e->getMessage()]);
    }
}

/**
 * Busca cores disponíveis
 */
function buscarCores($catalogo) {
    try {
        $cores = $catalogo->getCores();
        echo json_encode([
            'success' => true,
            'data' => $cores
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar cores: ' . $e->getMessage()]);
    }
}

/**
 * Busca tamanhos disponíveis
 */
function buscarTamanhos($catalogo) {
    try {
        $tamanhos = $catalogo->getTamanhos();
        echo json_encode([
            'success' => true,
            'data' => $tamanhos
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar tamanhos: ' . $e->getMessage()]);
    }
}

/**
 * Manipula requisições POST
 */
function handlePost($catalogo, $estoque, $action) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            criarMaterial($catalogo, $estoque, $data);
            break;
            
        case 'duplicar':
            duplicarMaterial($catalogo, $estoque, $data);
            break;
            
        case 'estoque':
            gerenciarEstoque($estoque, $data);
            break;
            
        case 'atualizar-configuracoes-estoque':
            atualizarConfiguracoesEstoque($estoque, $data);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

/**
 * Cria novo material
 */
function criarMaterial($catalogo, $estoque, $data) {
    try {
        // Nova estrutura: dados separados entre catálogo e estoque
        $catalogoData = $data['catalogo'] ?? $data; // Fallback para compatibilidade
        $estoqueData = $data['estoque'] ?? [];
        
        // Validar dados obrigatórios do catálogo
        if (empty($catalogoData['codigo']) || empty($catalogoData['nome'])) {
            throw new Exception('Código e nome são obrigatórios');
        }
        
        // Verificar se código já existe
        if ($catalogo->codigoExiste($catalogoData['codigo'])) {
            throw new Exception('Código já existe no catálogo');
        }
        
        // Verificar código de barras se fornecido
        if (!empty($catalogoData['codigo_barras']) && $catalogo->codigoBarrasExiste($catalogoData['codigo_barras'])) {
            throw new Exception('Código de barras já existe no catálogo');
        }
        
        // Função auxiliar para converter strings vazias em NULL
        $emptyToNull = function($value) {
            return (is_string($value) && trim($value) === '') ? null : $value;
        };
        
        // Criar material no catálogo centralizado
        $dadosCatalogo = [
            'codigo' => $catalogoData['codigo'],
            'nome' => $catalogoData['nome'],
            'descricao' => $emptyToNull($catalogoData['descricao'] ?? null),
            'id_categoria' => $emptyToNull($catalogoData['id_categoria'] ?? null),
            'id_fornecedor' => $emptyToNull($catalogoData['id_fornecedor'] ?? null),
            'id_fabricante' => $emptyToNull($catalogoData['id_fabricante'] ?? null),
            'id_unidade' => $catalogoData['id_unidade'] ?? 1,
            'preco_unitario_padrao' => $catalogoData['preco_unitario_padrao'] ?? 0.00,
            'estoque_minimo_padrao' => $catalogoData['estoque_minimo_padrao'] ?? 0.00,
            'estoque_maximo_padrao' => $catalogoData['estoque_maximo_padrao'] ?? 0.00,
            'codigo_barras' => $emptyToNull($catalogoData['codigo_barras'] ?? null),
            'ca' => $emptyToNull($catalogoData['ca'] ?? null),
            'marca' => $emptyToNull($catalogoData['marca'] ?? null),
            'modelo' => $emptyToNull($catalogoData['modelo'] ?? null),
            'cor' => $emptyToNull($catalogoData['cor'] ?? null),
            'tamanho' => $emptyToNull($catalogoData['tamanho'] ?? null),
            'peso' => $emptyToNull($catalogoData['peso'] ?? null),
            'volume' => $emptyToNull($catalogoData['volume'] ?? null),
            'observacoes' => $emptyToNull($catalogoData['observacoes'] ?? null)
        ];
        
        $idCatalogo = $catalogo->insert($dadosCatalogo);
        
        // Criar estoque para a filial se especificada
        if (!empty($estoqueData['id_filial'])) {
            $dadosEstoque = [
                'estoque_atual' => $estoqueData['estoque_atual'] ?? 0.00,
                // Se estoque_minimo/maximo não foram fornecidos, deixar NULL (será usado valor padrão do catálogo como fallback)
                // Se foram fornecidos, usar os valores específicos da filial
                'estoque_minimo' => isset($estoqueData['estoque_minimo']) ? $estoqueData['estoque_minimo'] : null,
                'estoque_maximo' => isset($estoqueData['estoque_maximo']) ? $estoqueData['estoque_maximo'] : null,
                'preco_unitario' => $estoqueData['preco_unitario'] ?? $catalogoData['preco_unitario_padrao'] ?? 0.00,
                'data_vencimento' => $estoqueData['data_vencimento'] ?? null,
                'localizacao_estoque' => $estoqueData['localizacao_estoque'] ?? null,
                'observacoes_estoque' => $estoqueData['observacoes_estoque'] ?? null
            ];
            
            $estoque->criarOuAtualizarEstoque($idCatalogo, $estoqueData['id_filial'], $dadosEstoque);
        }
        
        // NOVA FUNCIONALIDADE: Criar estoque em TODAS as filiais
        if (!empty($estoqueData['criar_em_todas_filiais']) && $estoqueData['criar_em_todas_filiais'] === true) {
            // Log interno (não envia para o cliente)
            error_log("Criando estoque em todas as filiais para material ID: {$idCatalogo}");
            
            // Buscar todas as filiais ativas
            $pdoInstance = Conexao::getInstance()->getPdo();
            $stmt = $pdoInstance->prepare("SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1 ORDER BY nome_filial");
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estoquesCriados = 0;
            $erros = 0;
            
            foreach ($filiais as $filial) {
                try {
                    // IMPORTANTE: Não definir estoque_minimo e estoque_maximo ao criar - devem ser configurados por filial depois
                    // Usar valores padrão do catálogo apenas como fallback inicial (NULL permite que seja configurado depois)
                    $dadosEstoque = [
                        'estoque_atual' => $estoqueData['estoque_atual'] ?? 0.00,
                        // Não definir estoque_minimo e estoque_maximo - deixar NULL para ser configurado por filial
                        // O sistema usará os valores padrão do catálogo como fallback até serem configurados
                        'preco_unitario' => $estoqueData['preco_unitario'] ?? $catalogoData['preco_unitario_padrao'] ?? 0.00,
                        'localizacao_estoque' => $estoqueData['localizacao_estoque'] ?? 'A definir',
                        'observacoes_estoque' => $estoqueData['observacoes_estoque'] ?? 'Estoque inicial criado automaticamente. Configure estoque mínimo/máximo por filial na tela de materiais.',
                        'ativo' => 1
                    ];
                    
                    $estoque->criarOuAtualizarEstoque($idCatalogo, $filial['id_filial'], $dadosEstoque);
                    $estoquesCriados++;
                    
                } catch (Exception $e) {
                    $erros++;
                    error_log("Erro ao criar estoque para material {$idCatalogo} na filial {$filial['nome_filial']}: " . $e->getMessage());
                }
            }
            
            // Log dos resultados (não envia para o cliente)
            error_log("Estoques criados: {$estoquesCriados} filiais" . ($erros > 0 ? " | Erros: {$erros}" : ""));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Material criado com sucesso',
            'id_catalogo' => $idCatalogo
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        error_log("Erro ao criar material: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Duplica material
 */
function duplicarMaterial($catalogo, $estoque, $data) {
    try {
        $id = $data['id'] ?? null;
        if (!$id) {
            throw new Exception('ID do material não fornecido');
        }
        
        // Buscar material original
        $material = $estoque->findByIdWithRelations($id);
        if (!$material) {
            throw new Exception('Material não encontrado');
        }
        
        // Criar novo código
        $novoCodigo = $material['codigo'] . '_COPY_' . time();
        
        // Criar material duplicado
        $dadosCatalogo = [
            'codigo' => $novoCodigo,
            'nome' => $material['nome'] . ' (Cópia)',
            'descricao' => $material['descricao'],
            'id_categoria' => $material['id_categoria'],
            'id_fornecedor' => $material['id_fornecedor'],
            'id_unidade' => $material['id_unidade'],
            'preco_unitario_padrao' => $material['preco_unitario_padrao'] ?? 0.00,
            'estoque_minimo_padrao' => $material['estoque_minimo_padrao'] ?? 0.00,
            'estoque_maximo_padrao' => $material['estoque_maximo_padrao'] ?? 0.00,
            'codigo_barras' => null, // Não duplicar código de barras
            'marca' => $material['marca'],
            'modelo' => $material['modelo'],
            'cor' => $material['cor'],
            'tamanho' => $material['tamanho'],
            'peso' => $material['peso'],
            'volume' => $material['volume'],
            'observacoes' => $material['observacoes']
        ];
        
        $idCatalogo = $catalogo->insert($dadosCatalogo);
        
        // Criar estoque para a mesma filial
        $dadosEstoque = [
            'estoque_atual' => 0.00, // Estoque zerado para cópia
            'estoque_minimo' => $material['estoque_minimo'] ?? 0.00,
            'estoque_maximo' => $material['estoque_maximo'] ?? 0.00,
            'preco_unitario' => $material['preco_unitario'] ?? 0.00,
            'data_vencimento' => null,
            'localizacao_estoque' => $material['localizacao_estoque'],
            'observacoes_estoque' => 'Material duplicado do ID: ' . $id
        ];
        
        $estoque->criarOuAtualizarEstoque($idCatalogo, $material['id_filial'], $dadosEstoque);
        
        echo json_encode([
            'success' => true,
            'message' => 'Material duplicado com sucesso',
            'id_catalogo' => $idCatalogo
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Manipula requisições PUT
 */
function handlePut($catalogo, $estoque, $action) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            atualizarMaterial($catalogo, $estoque, $data);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

/**
 * Atualiza material
 */
function atualizarMaterial($catalogo, $estoque, $data) {
    try {
        // Buscar ID da query string OU do corpo JSON
        $id = $_GET['id'] ?? $data['id'] ?? null;
        if (!$id) {
            throw new Exception('ID do material não fornecido');
        }
        
        // Buscar material
        $material = $estoque->findByIdWithRelations($id);
        if (!$material) {
            throw new Exception('Material não encontrado');
        }
        
        // Função auxiliar para converter strings vazias em NULL
        $emptyToNull = function($value) {
            return (is_string($value) && trim($value) === '') ? null : $value;
        };
        
        // Separar dados do catálogo e estoque
        $catalogoData = $data['catalogo'] ?? $data;
        $estoqueData = $data['estoque'] ?? [];
        
        // Atualizar catálogo
        $dadosCatalogo = [
            'codigo' => $catalogoData['codigo'] ?? $material['codigo'],
            'nome' => $catalogoData['nome'] ?? $material['nome'],
            'descricao' => $emptyToNull($catalogoData['descricao'] ?? $material['descricao']),
            'id_categoria' => $emptyToNull($catalogoData['id_categoria'] ?? $material['id_categoria']),
            'id_fornecedor' => $emptyToNull($catalogoData['id_fornecedor'] ?? $material['id_fornecedor']),
            'id_fabricante' => $emptyToNull($catalogoData['id_fabricante'] ?? $material['id_fabricante']),
            'id_unidade' => $catalogoData['id_unidade'] ?? $material['id_unidade'],
            'preco_unitario_padrao' => $catalogoData['preco_unitario_padrao'] ?? $material['preco_unitario_padrao'],
            'estoque_minimo_padrao' => $catalogoData['estoque_minimo_padrao'] ?? $material['estoque_minimo_padrao'],
            'estoque_maximo_padrao' => $catalogoData['estoque_maximo_padrao'] ?? $material['estoque_maximo_padrao'],
            'codigo_barras' => $emptyToNull($catalogoData['codigo_barras'] ?? $material['codigo_barras']),
            'ca' => $emptyToNull($catalogoData['ca'] ?? $material['ca']),
            'marca' => $emptyToNull($catalogoData['marca'] ?? $material['marca']),
            'modelo' => $emptyToNull($catalogoData['modelo'] ?? $material['modelo']),
            'cor' => $emptyToNull($catalogoData['cor'] ?? $material['cor']),
            'tamanho' => $emptyToNull($catalogoData['tamanho'] ?? $material['tamanho']),
            'peso' => $emptyToNull($catalogoData['peso'] ?? $material['peso']),
            'volume' => $emptyToNull($catalogoData['volume'] ?? $material['volume']),
            'observacoes' => $emptyToNull($catalogoData['observacoes'] ?? $material['observacoes'])
        ];
        
        $catalogo->update($material['id_catalogo'], $dadosCatalogo);
        














        
        // Atualizar estoque da filial se fornecido
        if (!empty($estoqueData['id_filial'])) {
            $dadosEstoqueFilial = [
                'estoque_atual' => $estoqueData['estoque_atual'] ?? $material['estoque_atual'] ?? 0.00,
                // IMPORTANTE: Salvar estoque_minimo e estoque_maximo na filial quando fornecidos
                'estoque_minimo' => isset($estoqueData['estoque_minimo']) && $estoqueData['estoque_minimo'] !== null ? $estoqueData['estoque_minimo'] : null,
                'estoque_maximo' => isset($estoqueData['estoque_maximo']) && $estoqueData['estoque_maximo'] !== null ? $estoqueData['estoque_maximo'] : null,
                'preco_unitario' => $estoqueData['preco_unitario'] ?? $material['preco_unitario'] ?? 0.00,
                'data_vencimento' => $estoqueData['data_vencimento'] ?? $material['data_vencimento'] ?? null,
                'localizacao_estoque' => $estoqueData['localizacao_estoque'] ?? $material['localizacao_estoque'] ?? null,
                'observacoes_estoque' => $estoqueData['observacoes_estoque'] ?? $material['observacoes_estoque'] ?? null
            ];
            
            // Usar criarOuAtualizarEstoque para garantir que salve na filial correta
            $estoque->criarOuAtualizarEstoque($material['id_catalogo'], $estoqueData['id_filial'], $dadosEstoqueFilial);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Material atualizado com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Manipula requisições DELETE
 */
function handleDelete($catalogo, $estoque, $action) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'delete':
            excluirMaterial($catalogo, $estoque, $data);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não especificada']);
            break;
    }
}

/**
 * Exclui material
 */
function excluirMaterial($catalogo, $estoque, $data) {
    try {
        $id = $data['id'] ?? null;
        if (!$id) {
            throw new Exception('ID do material não fornecido');
        }
        
        // Buscar material
        $material = $estoque->findByIdWithRelations($id);
        if (!$material) {
            throw new Exception('Material não encontrado');
        }
        
        // Marcar estoque como inativo
        $estoque->update($id, ['ativo' => 0]);
        
        // Verificar se há outros estoques ativos para este material
        $outrosEstoques = $estoque->findByMaterial($material['id_catalogo']);
        $estoquesAtivos = array_filter($outrosEstoques, function($e) { return $e['ativo'] == 1; });
        
        // Se não há outros estoques ativos, marcar catálogo como inativo
        if (empty($estoquesAtivos)) {
            $catalogo->update($material['id_catalogo'], ['ativo' => 0]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Material excluído com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Formata material para compatibilidade com frontend
 */
function formatarMaterialEstoque($material) {
    // Usar valores calculados (com COALESCE) se disponíveis, senão usar valores diretos
    $estoqueMinimo = $material['estoque_minimo_calculado'] ?? $material['estoque_minimo'] ?? 0;
    $estoqueMaximo = $material['estoque_maximo_calculado'] ?? $material['estoque_maximo'] ?? 0;
    
    return [
        'id_material' => $material['id_estoque'],
        'id_catalogo' => $material['id_catalogo'],
        'codigo' => $material['codigo'],
        'nome' => $material['nome'],
        'descricao' => $material['descricao'],
        'id_categoria' => $material['id_categoria'],
        'id_fornecedor' => $material['id_fornecedor'],
        'id_fabricante' => $material['id_fabricante'] ?? null,
        'id_unidade' => $material['id_unidade'],
        'id_filial' => $material['id_filial'],
        'estoque_atual' => $material['estoque_atual'],
        'estoque_minimo' => $estoqueMinimo,
        'estoque_maximo' => $estoqueMaximo,
        'preco_unitario' => $material['preco_unitario'],
        'data_vencimento' => $material['data_vencimento'],
        'localizacao_estoque' => $material['localizacao_estoque'],
        'observacoes_estoque' => $material['observacoes_estoque'],
        'observacoes' => $material['observacoes'] ?? null,
        'nome_categoria' => $material['nome_categoria'],
        'fornecedor_nome' => $material['fornecedor_nome'],
        'fabricante_nome' => $material['fabricante_nome'] ?? null,
        'unidade_sigla' => $material['unidade_sigla'],
        'unidade_nome' => $material['unidade_nome'],
        'nome_filial' => $material['nome_filial'],
        'codigo_barras' => $material['codigo_barras'] ?? null,
        'ca' => $material['ca'] ?? null,
        'marca' => $material['marca'] ?? null,
        'modelo' => $material['modelo'] ?? null,
        'cor' => $material['cor'] ?? null,
        'tamanho' => $material['tamanho'] ?? null,
        'peso' => $material['peso'] ?? null,
        'volume' => $material['volume'] ?? null,
        'ativo' => $material['ativo'] ?? 1,
        'data_criacao' => $material['data_criacao'] ?? null,
        'data_atualizacao' => $material['data_atualizacao'] ?? null
    ];
}

/**
 * Lista todas as filiais ativas
 */
function listarFiliais() {
    try {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT id_filial, nome_filial, cnpj, telefone, email, endereco, cidade, estado
            FROM tbl_filiais 
            WHERE filial_ativa = 1 
            ORDER BY nome_filial
        ");
        $stmt->execute();
        $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'filiais' => $filiais
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao listar filiais: ' . $e->getMessage()
        ]);
    }
}

/**
 * Gerencia estoque de um material (criar ou atualizar)
 */
function gerenciarEstoque($estoque, $data) {
    try {
        $idCatalogo = $data['id_catalogo'] ?? null;
        $idFilial = $data['id_filial'] ?? null;
        
        if (!$idCatalogo || !$idFilial) {
            throw new Exception('ID do material e filial são obrigatórios');
        }
        
        // Log dos dados recebidos
        error_log("🔍 Dados recebidos para estoque:");
        error_log("   - id_catalogo: {$idCatalogo}");
        error_log("   - id_filial: {$idFilial}");
        error_log("   - dados: " . json_encode($data));
        
        // Dados do estoque
        $dadosEstoque = [
            'estoque_atual' => $data['estoque_atual'] ?? 0.00,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0.00,
            'estoque_maximo' => $data['estoque_maximo'] ?? 0.00,
            'preco_unitario' => $data['preco_unitario'] ?? 0.00,
            'localizacao_estoque' => $data['localizacao_estoque'] ?? null,
            'observacoes_estoque' => $data['observacoes'] ?? null
        ];
        
        error_log("📦 Dados do estoque preparados: " . json_encode($dadosEstoque));
        
        // Verificar se já existe estoque
        $estoqueExistente = $estoque->findByMaterialEFilial($idCatalogo, $idFilial);
        error_log("🔍 Estoque existente: " . ($estoqueExistente ? "SIM" : "NÃO"));
        
        if ($estoqueExistente) {
            error_log("📝 Atualizando estoque existente ID: {$estoqueExistente['id_estoque']}");
        } else {
            error_log("🆕 Criando novo estoque");
        }
        
        // Criar ou atualizar estoque
        $resultado = $estoque->criarOuAtualizarEstoque($idCatalogo, $idFilial, $dadosEstoque);
        
        error_log("✅ Resultado da operação: " . ($resultado ? "SUCESSO" : "FALHA"));
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Estoque salvo com sucesso',
                'data' => [
                    'id_catalogo' => $idCatalogo,
                    'id_filial' => $idFilial,
                    'estoque' => $dadosEstoque,
                    'operacao' => $estoqueExistente ? 'atualizado' : 'criado'
                ]
            ]);
        } else {
            throw new Exception('Erro ao salvar estoque');
        }
        
    } catch (Exception $e) {
        error_log("❌ ERRO na função gerenciarEstoque: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao gerenciar estoque: ' . $e->getMessage()
        ]);
    }
}

/**
 * Busca estoque específico de um material em uma filial
 */
function buscarEstoqueEspecifico($estoque) {
    try {
        $idCatalogo = $_GET['id_catalogo'] ?? null;
        $idFilial = $_GET['id_filial'] ?? null;
        
        if (!$idCatalogo || !$idFilial) {
            throw new Exception('ID do material e filial são obrigatórios');
        }
        
        // Buscar estoque existente
        $estoqueExistente = $estoque->findByMaterialEFilial($idCatalogo, $idFilial);
        
        // Buscar valores padrão do catálogo
        $pdo = Conexao::getInstance()->getPdo();
        $stmtCatalogo = $pdo->prepare("SELECT estoque_minimo_padrao, estoque_maximo_padrao, preco_unitario_padrao FROM tbl_catalogo_materiais WHERE id_catalogo = ?");
        $stmtCatalogo->execute([$idCatalogo]);
        $catalogo = $stmtCatalogo->fetch(PDO::FETCH_ASSOC);
        
        if ($estoqueExistente) {
            // Se existir estoque, usar valores da filial se definidos, senão usar padrão do catálogo
            // Manter valores NULL da filial para permitir edição (frontend mostrará padrão mas permitirá configurar)
            $estoqueExistente['estoque_minimo_padrao'] = $catalogo['estoque_minimo_padrao'] ?? 0.00;
            $estoqueExistente['estoque_maximo_padrao'] = $catalogo['estoque_maximo_padrao'] ?? 0.00;
            // Para exibição, usar valor calculado (prioriza filial, senão padrão)
            $estoqueExistente['estoque_minimo_exibicao'] = $estoqueExistente['estoque_minimo'] !== null 
                ? $estoqueExistente['estoque_minimo'] 
                : ($catalogo['estoque_minimo_padrao'] ?? 0.00);
            $estoqueExistente['estoque_maximo_exibicao'] = $estoqueExistente['estoque_maximo'] !== null 
                ? $estoqueExistente['estoque_maximo'] 
                : ($catalogo['estoque_maximo_padrao'] ?? 0.00);
            echo json_encode([
                'success' => true,
                'estoque' => $estoqueExistente
            ]);
        } else {
            // Se não existir estoque, retornar valores padrão do catálogo
            echo json_encode([
                'success' => true,
                'estoque' => [
                    'estoque_atual' => 0.00,
                    'estoque_minimo' => null,  // NULL permite configurar depois
                    'estoque_maximo' => null,  // NULL permite configurar depois
                    'estoque_minimo_padrao' => $catalogo['estoque_minimo_padrao'] ?? 0.00,
                    'estoque_maximo_padrao' => $catalogo['estoque_maximo_padrao'] ?? 0.00,
                    'preco_unitario' => $catalogo['preco_unitario_padrao'] ?? 0.00,
                    'localizacao_estoque' => '',
                    'observacoes_estoque' => ''
                ]
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao buscar estoque: ' . $e->getMessage()
        ]);
    }
}

/**
 * Atualiza configurações de estoque (mínimo e máximo)
 */
function atualizarConfiguracoesEstoque($estoque, $data) {
    try {
        $idCatalogo = $data['id_catalogo'] ?? null;
        $idFilial = $data['id_filial'] ?? null;
        $estoqueMinimo = $data['estoque_minimo'] ?? 0.00;
        $estoqueMaximo = $data['estoque_maximo'] ?? 0.00;
        
        if (!$idCatalogo || !$idFilial) {
            throw new Exception('ID do material e filial são obrigatórios');
        }
        
        // Validar valores
        if ($estoqueMinimo < 0) {
            throw new Exception('Estoque mínimo não pode ser negativo');
        }
        if ($estoqueMaximo < 0) {
            throw new Exception('Estoque máximo não pode ser negativo');
        }
        if ($estoqueMaximo > 0 && $estoqueMinimo > $estoqueMaximo) {
            throw new Exception('Estoque mínimo não pode ser maior que o máximo');
        }
        
        // Buscar estoque existente
        $estoqueExistente = $estoque->findByMaterialEFilial($idCatalogo, $idFilial);
        
        if ($estoqueExistente) {
            // Atualizar estoque existente
            $dadosEstoque = [
                'estoque_minimo' => $estoqueMinimo,
                'estoque_maximo' => $estoqueMaximo
            ];
            
            $resultado = $estoque->update($estoqueExistente['id_estoque'], $dadosEstoque);
            
            if ($resultado) {
                // Buscar dados atualizados
                $estoqueAtualizado = $estoque->findByMaterialEFilial($idCatalogo, $idFilial);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações de estoque atualizadas com sucesso',
                    'data' => [
                        'id_catalogo' => $idCatalogo,
                        'id_filial' => $idFilial,
                        'estoque_atual' => $estoqueAtualizado['estoque_atual'] ?? 0,
                        'estoque_minimo' => $estoqueMinimo,
                        'estoque_maximo' => $estoqueMaximo
                    ]
                ]);
            } else {
                throw new Exception('Erro ao atualizar configurações de estoque');
            }
        } else {
            // Criar novo estoque com as configurações
            $dadosEstoque = [
                'estoque_atual' => 0.00,
                'estoque_minimo' => $estoqueMinimo,
                'estoque_maximo' => $estoqueMaximo,
                'preco_unitario' => 0.00
            ];
            
            $resultado = $estoque->criarOuAtualizarEstoque($idCatalogo, $idFilial, $dadosEstoque);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estoque criado com configurações definidas',
                    'data' => [
                        'id_catalogo' => $idCatalogo,
                        'id_filial' => $idFilial,
                        'estoque_atual' => 0,
                        'estoque_minimo' => $estoqueMinimo,
                        'estoque_maximo' => $estoqueMaximo
                    ]
                ]);
            } else {
                throw new Exception('Erro ao criar estoque com configurações');
            }
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao atualizar configurações de estoque: ' . $e->getMessage()
        ]);
    }
}

?> 