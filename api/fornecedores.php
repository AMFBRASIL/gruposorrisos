<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/conexao.php';
require_once '../models/Fornecedor.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $fornecedor = new Fornecedor($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($fornecedor, $action);
            break;
        case 'POST':
            handlePost($fornecedor, $action);
            break;
        case 'PUT':
            handlePut($fornecedor, $action);
            break;
        case 'DELETE':
            handleDelete($fornecedor, $action);
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

function handleGet($fornecedor, $action) {
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $filters = [
                'razao_social' => $_GET['razao_social'] ?? null,
                'nome_fantasia' => $_GET['nome_fantasia'] ?? null,
                'cnpj' => $_GET['cnpj'] ?? null,
                'cidade' => $_GET['cidade'] ?? null,
                'estado' => $_GET['estado'] ?? null,
                'ativo' => isset($_GET['ativo']) ? $_GET['ativo'] : null,
                'is_fabricante' => isset($_GET['is_fabricante']) ? $_GET['is_fabricante'] : null
            ];
            
            $result = $fornecedor->findWithFilters($filters, $page, $limit);
            
            // Adicionar contagem de materiais e pedidos para cada fornecedor
            if ($result['data']) {
                foreach ($result['data'] as &$fornecedor_data) {
                    $fornecedor_data['total_materiais'] = $fornecedor->countMateriaisByFornecedor($fornecedor_data['id_fornecedor']);
                    $fornecedor_data['total_pedidos'] = $fornecedor->countPedidosByFornecedor($fornecedor_data['id_fornecedor']);
                }
            }
            
            echo json_encode($result);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            $result = $fornecedor->findById($id);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Fornecedor não encontrado']);
            }
            break;
            
        case 'estatisticas':
            // Total de fornecedores
            $total = $fornecedor->countAll();
            
            // Fornecedores ativos
            $ativos = $fornecedor->countAtivos();
            
            // Fornecedores inativos
            $inativos = $total - $ativos;
            
            // Produtos fornecidos (total de materiais)
            $produtosFornecidos = $fornecedor->countProdutosFornecidos();
            
            // Pedidos este mês (simulado)
            $pedidosMes = $fornecedor->countPedidosMes();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'ativos' => $ativos,
                    'inativos' => $inativos,
                    'produtos_fornecidos' => $produtosFornecidos,
                    'pedidos_mes' => $pedidosMes
                ]
            ]);
            break;
            
        case 'ativos':
            $fornecedores = $fornecedor->findAtivos();
            echo json_encode(['success' => true, 'data' => $fornecedores]);
            break;
            
        case 'fabricantes':
            // Listar apenas fornecedores que são fabricantes
            $fornecedores = $fornecedor->findFabricantes();
            echo json_encode(['success' => true, 'data' => $fornecedores]);
            break;
            
        case 'apenas-fornecedores':
            // Listar apenas fornecedores que NÃO são fabricantes
            $fornecedores = $fornecedor->findApenasFornecedores();
            echo json_encode(['success' => true, 'data' => $fornecedores]);
            break;
            
        case 'verificar-email':
            // Verificar se email já existe
            $email = $_GET['email'] ?? null;
            $id = $_GET['id'] ?? null; // ID do fornecedor atual (para edição)
            
            if (!$email) {
                http_response_code(400);
                echo json_encode(['error' => 'E-mail não fornecido']);
                return;
            }
            
            $fornecedorExistente = $fornecedor->buscarPorEmail($email);
            $emailExiste = false;
            
            if ($fornecedorExistente) {
                // Se estiver editando, verificar se o email pertence a outro fornecedor
                if ($id && $fornecedorExistente['id_fornecedor'] == $id) {
                    $emailExiste = false; // É o próprio fornecedor
                } else {
                    $emailExiste = true; // Email já existe em outro fornecedor
                }
            }
            
            echo json_encode([
                'success' => true,
                'email_existe' => $emailExiste,
                'mensagem' => $emailExiste ? 'Este e-mail já está cadastrado' : 'E-mail disponível'
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handlePost($fornecedor, $action) {
    switch ($action) {
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validações básicas
            if (empty($data['razao_social'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Razão social é obrigatória']);
                return;
            }
            
            // Verificar se CNPJ já existe
            if (!empty($data['cnpj']) && $fornecedor->cnpjExiste($data['cnpj'])) {
                http_response_code(400);
                echo json_encode(['error' => 'CNPJ já cadastrado']);
                return;
            }
            
            // Verificar se email já existe
            if (!empty($data['email'])) {
                $fornecedorExistente = $fornecedor->buscarPorEmail($data['email']);
                if ($fornecedorExistente) {
                    http_response_code(400);
                    echo json_encode(['error' => 'E-mail já cadastrado para outro fornecedor']);
                    return;
                }
            }
            
            // Adicionar dados padrão
            $data['ativo'] = $data['ativo'] ?? 1;
            $data['is_fabricante'] = $data['is_fabricante'] ?? 0;
            $data['data_criacao'] = date('Y-m-d H:i:s');
            
            $result = $fornecedor->create($data);
            if ($result) {
                // Log da ação
                logActivity('CRIAR', $data, 'tbl_fornecedores', $result);
                
                // Criar usuário para o fornecedor automaticamente
                $usuarioCriado = false;
                $mensagemUsuario = '';
                
                // Verificar se email foi fornecido para criar usuário
                if (!empty($data['email'])) {
                    try {
                        require_once '../models/Usuario.php';
                        require_once '../backend/utils/EmailUtils.php';
                        
                        // Obter conexão PDO
                        $pdo = Conexao::getInstance()->getPdo();
                        $usuarioModel = new Usuario($pdo);
                        
                        // Verificar se já existe usuário com este email
                        $usuarioExistente = $usuarioModel->findByEmail($data['email']);
                        
                        if (!$usuarioExistente) {
                            // Gerar senha automática se não foi fornecida
                            $senhaOriginal = !empty($data['senha']) ? $data['senha'] : 'fornecedor' . rand(1000, 9999);
                            
                            // Buscar ID do perfil Fornecedor
                            $stmt = $pdo->prepare("SELECT id_perfil FROM tbl_perfis WHERE nome_perfil = 'Fornecedor'");
                            $stmt->execute();
                            $perfilFornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
                            $idPerfilFornecedor = $perfilFornecedor ? $perfilFornecedor['id_perfil'] : 5;
                            
                            // Dados do usuário
                            $dadosUsuario = [
                                'nome_completo' => $data['razao_social'],
                                'email' => $data['email'],
                                'senha' => password_hash($senhaOriginal, PASSWORD_DEFAULT),
                                'id_perfil' => $idPerfilFornecedor,
                                'id_fornecedor' => $result, // Vincular ao fornecedor criado
                                'ativo' => 1,
                                'data_criacao' => date('Y-m-d H:i:s')
                            ];
                            
                            // Criar usuário
                            $idUsuario = $usuarioModel->insert($dadosUsuario);
                            
                            if ($idUsuario) {
                                $usuarioCriado = true;
                                
                                // Enviar email de boas-vindas
                                try {
                                    $emailEnviado = EmailUtils::enviarEmailBoasVindas(
                                        $data['email'],
                                        $data['razao_social'],
                                        $senhaOriginal,
                                        'Fornecedor'
                                    );
                                    
                                    if ($emailEnviado) {
                                        $mensagemUsuario = ' e usuário criado com email de boas-vindas enviado';
                                    } else {
                                        $mensagemUsuario = ' e usuário criado (email não enviado)';
                                    }
                                } catch (Exception $e) {
                                    error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
                                    $mensagemUsuario = ' e usuário criado (email não enviado)';
                                }
                            } else {
                                $mensagemUsuario = ' (erro ao criar usuário)';
                            }
                        } else {
                            // Vincular usuário existente ao fornecedor
                            $stmt = $pdo->prepare("UPDATE tbl_usuarios SET id_fornecedor = ? WHERE id_usuario = ?");
                            $vinculado = $stmt->execute([$result, $usuarioExistente['id_usuario']]);
                            
                            if ($vinculado) {
                                $usuarioCriado = true;
                                $mensagemUsuario = ' e usuário existente vinculado ao fornecedor';
                            } else {
                                $mensagemUsuario = ' (erro ao vincular usuário existente)';
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Erro ao criar/vincular usuário para fornecedor: " . $e->getMessage());
                        $mensagemUsuario = ' (erro ao criar usuário)';
                    }
                } else {
                    $mensagemUsuario = ' (usuário não criado - email não fornecido)';
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Fornecedor criado com sucesso' . $mensagemUsuario,
                    'id' => $result,
                    'usuario_criado' => $usuarioCriado
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao criar fornecedor']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handlePut($fornecedor, $action) {
    switch ($action) {
        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            // Verificar se CNPJ já existe (exceto o próprio)
            if (!empty($data['cnpj']) && $fornecedor->cnpjExiste($data['cnpj'], $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'CNPJ já cadastrado']);
                return;
            }
            
            // Verificar se email já existe (exceto o próprio)
            if (!empty($data['email'])) {
                $fornecedorExistente = $fornecedor->buscarPorEmail($data['email']);
                if ($fornecedorExistente && $fornecedorExistente['id_fornecedor'] != $id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'E-mail já cadastrado para outro fornecedor']);
                    return;
                }
            }
            
            $data['data_atualizacao'] = date('Y-m-d H:i:s');
            
            $result = $fornecedor->update($id, $data);
            if ($result) {
                // Log da ação
                logActivity('ATUALIZAR', $data, 'tbl_fornecedores', $id);
                
                // Verificar se fornecedor já tem usuário vinculado
                $usuarioCriado = false;
                $mensagemUsuario = '';
                
                if (!empty($data['email'])) {
                    try {
                        require_once '../models/Usuario.php';
                        require_once '../backend/utils/EmailUtils.php';
                        
                        // Obter conexão PDO
                        $pdo = Conexao::getInstance()->getPdo();
                        $usuarioModel = new Usuario($pdo);
                        
                        // Verificar se já existe usuário vinculado a este fornecedor
                        $stmt = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE id_fornecedor = ? AND ativo = 1");
                        $stmt->execute([$id]);
                        $usuarioVinculado = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$usuarioVinculado) {
                            // Verificar se já existe usuário com este email
                        $usuarioExistente = $usuarioModel->findByEmail($data['email']);
                            
                            if (!$usuarioExistente) {
                                // Criar novo usuário
                                $senhaOriginal = !empty($data['senha']) ? $data['senha'] : 'fornecedor' . rand(1000, 9999);
                                
                                // Buscar ID do perfil Fornecedor
                                $stmt = $pdo->prepare("SELECT id_perfil FROM tbl_perfis WHERE nome_perfil = 'Fornecedor'");
                                $stmt->execute();
                                $perfilFornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
                                $idPerfilFornecedor = $perfilFornecedor ? $perfilFornecedor['id_perfil'] : 5;
                                
                                $dadosUsuario = [
                                    'nome_completo' => $data['razao_social'],
                                    'email' => $data['email'],
                                    'senha' => password_hash($senhaOriginal, PASSWORD_DEFAULT),
                                    'id_perfil' => $idPerfilFornecedor,
                                    'id_fornecedor' => $id,
                                    'ativo' => 1,
                                    'data_criacao' => date('Y-m-d H:i:s')
                                ];
                                
                                $idUsuario = $usuarioModel->insert($dadosUsuario);
                                
                                if ($idUsuario) {
                                    $usuarioCriado = true;
                                    
                                    // Enviar email de boas-vindas
                                    try {
                                        $emailEnviado = EmailUtils::enviarEmailBoasVindas(
                                            $data['email'],
                                            $data['razao_social'],
                                            $senhaOriginal,
                                            'Fornecedor'
                                        );
                                        
                                        if ($emailEnviado) {
                                            $mensagemUsuario = ' e usuário criado com email de boas-vindas enviado';
                                        } else {
                                            $mensagemUsuario = ' e usuário criado (email não enviado)';
                                        }
                                    } catch (Exception $e) {
                                        error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
                                        $mensagemUsuario = ' e usuário criado (email não enviado)';
                                    }
                                } else {
                                    $mensagemUsuario = ' (erro ao criar usuário)';
                                }
                            } else {
                                // Vincular usuário existente ao fornecedor
                                $stmt = $pdo->prepare("UPDATE tbl_usuarios SET id_fornecedor = ? WHERE id_usuario = ?");
                                $vinculado = $stmt->execute([$id, $usuarioExistente['id_usuario']]);
                                
                                if ($vinculado) {
                                    $usuarioCriado = true;
                                    $mensagemUsuario = ' e usuário existente vinculado ao fornecedor';
                                } else {
                                    $mensagemUsuario = ' (erro ao vincular usuário existente)';
                                }
                            }
                        } else {
                            // Atualizar dados do usuário existente se necessário
                            if (!empty($data['senha'])) {
                                $stmt = $pdo->prepare("UPDATE tbl_usuarios SET senha = ? WHERE id_usuario = ?");
                                $stmt->execute([password_hash($data['senha'], PASSWORD_DEFAULT), $usuarioVinculado['id_usuario']]);
                                $mensagemUsuario = ' e senha do usuário atualizada';
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Erro ao criar/vincular usuário para fornecedor: " . $e->getMessage());
                        $mensagemUsuario = ' (erro ao processar usuário)';
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Fornecedor atualizado com sucesso' . $mensagemUsuario,
                    'usuario_criado' => $usuarioCriado
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao atualizar fornecedor']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}

function handleDelete($fornecedor, $action) {
    switch ($action) {
        case 'delete':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
                return;
            }
            
            // Verificar se fornecedor tem materiais vinculados
            if ($fornecedor->hasMateriais($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Não é possível excluir fornecedor com materiais vinculados']);
                return;
            }
            
            $result = $fornecedor->delete($id);
            if ($result) {
                // Log da ação
                logActivity('EXCLUIR', null, 'tbl_fornecedores', $id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Fornecedor excluído com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir fornecedor']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}
?>