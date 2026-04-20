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
require_once '../../models/Ticket.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $ticket = new Ticket($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $pagina = (int)($_GET['pagina'] ?? 1);
                    $porPagina = (int)($_GET['por_pagina'] ?? 10);
                    $filtros = [
                        'busca' => $_GET['busca'] ?? '',
                        'status' => $_GET['status'] ?? '',
                        'prioridade' => $_GET['prioridade'] ?? '',
                        'categoria' => $_GET['categoria'] ?? ''
                    ];
                    
                    $resultado = $ticket->listar($pagina, $porPagina, $filtros);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'get':
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id > 0) {
                        $dados = $ticket->buscarPorId($id);
                        if ($dados) {
                            echo json_encode(['success' => true, 'ticket' => $dados]);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Ticket não encontrado']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                    }
                    break;
                    
                case 'categorias':
                    $categorias = $ticket->buscarCategorias();
                    echo json_encode(['success' => true, 'categorias' => $categorias]);
                    break;
                    
                case 'prioridades':
                    $prioridades = $ticket->buscarPrioridades();
                    echo json_encode(['success' => true, 'prioridades' => $prioridades]);
                    break;
                    
                case 'status':
                    $status = $ticket->buscarStatus();
                    echo json_encode(['success' => true, 'status' => $status]);
                    break;
                    
                case 'comentarios':
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id > 0) {
                        $comentarios = $ticket->buscarComentarios($id);
                        echo json_encode(['success' => true, 'comentarios' => $comentarios]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                    }
                    break;
                    
                case 'anexos':
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id > 0) {
                        $anexos = $ticket->buscarAnexos($id);
                        echo json_encode(['success' => true, 'anexos' => $anexos]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                    }
                    break;
                    
                case 'download_anexo':
                    $idAnexo = (int)($_GET['id_anexo'] ?? 0);
                    if ($idAnexo > 0) {
                        $stmt = $pdo->prepare("SELECT * FROM tbl_anexos_ticket WHERE id_anexo = ?");
                        $stmt->execute([$idAnexo]);
                        $anexo = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($anexo) {
                            // Converter caminho relativo para absoluto
                            $caminhoArquivo = '../../' . $anexo['caminho'];
                            
                            if (file_exists($caminhoArquivo)) {
                                header('Content-Type: application/octet-stream');
                                header('Content-Disposition: attachment; filename="' . $anexo['nome_original'] . '"');
                                header('Content-Length: ' . filesize($caminhoArquivo));
                                readfile($caminhoArquivo);
                                exit;
                            } else {
                                http_response_code(404);
                                echo json_encode(['success' => false, 'error' => 'Arquivo não encontrado']);
                            }
                        } else {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'error' => 'Anexo não encontrado']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                    }
                    break;
                    
                case 'estatisticas':
                    $estatisticas = $ticket->getEstatisticas();
                    echo json_encode(['success' => true, 'estatisticas' => $estatisticas]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    // Verificar se é upload de arquivo (multipart/form-data)
                    if (!empty($_FILES)) {
                        $dados = [
                            'titulo' => $_POST['titulo'] ?? '',
                            'descricao' => $_POST['descricao'] ?? '',
                            'id_categoria' => $_POST['id_categoria'] ?? '',
                            'id_prioridade' => $_POST['id_prioridade'] ?? '',
                            'id_usuario_solicitante' => $_POST['id_usuario_solicitante'] ?? 1,
                            'id_usuario_atribuido' => $_POST['id_usuario_atribuido'] ?? null,
                            'id_filial' => $_POST['id_filial'] ?? null
                        ];
                    } else {
                        $dados = json_decode(file_get_contents('php://input'), true);
                    }
                    
                    if (empty($dados['titulo'])) {
                        echo json_encode(['success' => false, 'error' => 'Título é obrigatório']);
                        break;
                    }
                    
                    $idTicket = $ticket->criar($dados);
                    
                    // Processar anexos se houver
                    if (!empty($_FILES['anexos']) && $idTicket) {
                        $anexosSalvos = [];
                        $diretorioBase = '../../uploads/tickets/';
                        
                        // Criar diretório base se não existir
                        if (!file_exists($diretorioBase)) {
                            mkdir($diretorioBase, 0755, true);
                        }
                        
                        $diretorioAnexos = $diretorioBase . $idTicket . '/';
                        
                        // Criar diretório do ticket se não existir
                        if (!file_exists($diretorioAnexos)) {
                            mkdir($diretorioAnexos, 0755, true);
                        }
                        
                        $arquivos = $_FILES['anexos'];
                        $totalArquivos = is_array($arquivos['name']) ? count($arquivos['name']) : 1;
                        
                        for ($i = 0; $i < $totalArquivos; $i++) {
                            $nomeOriginal = is_array($arquivos['name']) ? $arquivos['name'][$i] : $arquivos['name'];
                            $tipoArquivo = is_array($arquivos['type']) ? $arquivos['type'][$i] : $arquivos['type'];
                            $tamanho = is_array($arquivos['size']) ? $arquivos['size'][$i] : $arquivos['size'];
                            $tmpName = is_array($arquivos['tmp_name']) ? $arquivos['tmp_name'][$i] : $arquivos['tmp_name'];
                            $erro = is_array($arquivos['error']) ? $arquivos['error'][$i] : $arquivos['error'];
                            
                            // Validar tamanho (10MB máximo)
                            if ($tamanho > 10 * 1024 * 1024) {
                                continue; // Pular arquivo muito grande
                            }
                            
                            if ($erro === UPLOAD_ERR_OK) {
                                $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                                $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;
                                $caminhoCompleto = $diretorioAnexos . $nomeArquivo;
                                
                                if (move_uploaded_file($tmpName, $caminhoCompleto)) {
                                    // Salvar caminho relativo no banco
                                    $caminhoRelativo = 'uploads/tickets/' . $idTicket . '/' . $nomeArquivo;
                                    $ticket->salvarAnexo(
                                        $idTicket,
                                        $dados['id_usuario_solicitante'],
                                        $nomeArquivo,
                                        $nomeOriginal,
                                        $tipoArquivo,
                                        $tamanho,
                                        $caminhoRelativo
                                    );
                                    $anexosSalvos[] = $nomeOriginal;
                                }
                            }
                        }
                    }
                    
                    echo json_encode(['success' => true, 'id_ticket' => $idTicket, 'anexos' => $anexosSalvos ?? []]);
                    break;
                    
                case 'comentario':
                    // Verificar se é upload de arquivo (multipart/form-data)
                    if (!empty($_FILES)) {
                        $dados = [
                            'id_ticket' => $_POST['id_ticket'] ?? '',
                            'id_usuario' => $_POST['id_usuario'] ?? '',
                            'comentario' => $_POST['comentario'] ?? '',
                            'tipo' => $_POST['tipo'] ?? 'comentario',
                            'dados_anteriores' => $_POST['dados_anteriores'] ?? null,
                            'dados_novos' => $_POST['dados_novos'] ?? null
                        ];
                    } else {
                        $dados = json_decode(file_get_contents('php://input'), true);
                    }
                    
                    if (empty($dados['id_ticket']) || empty($dados['id_usuario'])) {
                        echo json_encode(['success' => false, 'error' => 'Dados obrigatórios não fornecidos']);
                        break;
                    }
                    
                    // Permitir comentário vazio se houver anexos
                    if (empty($dados['comentario']) && empty($_FILES['anexos'])) {
                        echo json_encode(['success' => false, 'error' => 'Digite um comentário ou anexe um arquivo']);
                        break;
                    }
                    
                    $idComentario = $ticket->adicionarComentario(
                        $dados['id_ticket'],
                        $dados['id_usuario'],
                        $dados['comentario'] ?? '',
                        $dados['tipo'] ?? 'comentario',
                        $dados['dados_anteriores'] ?? null,
                        $dados['dados_novos'] ?? null
                    );
                    
                    // Processar anexos se houver
                    $anexosSalvos = [];
                    if (!empty($_FILES['anexos']) && $idComentario) {
                        $idTicket = $dados['id_ticket'];
                        $diretorioBase = '../../uploads/tickets/';
                        
                        // Criar diretório base se não existir
                        if (!file_exists($diretorioBase)) {
                            mkdir($diretorioBase, 0755, true);
                        }
                        
                        $diretorioAnexos = $diretorioBase . $idTicket . '/';
                        
                        // Criar diretório do ticket se não existir
                        if (!file_exists($diretorioAnexos)) {
                            mkdir($diretorioAnexos, 0755, true);
                        }
                        
                        $arquivos = $_FILES['anexos'];
                        $totalArquivos = is_array($arquivos['name']) ? count($arquivos['name']) : 1;
                        
                        for ($i = 0; $i < $totalArquivos; $i++) {
                            $nomeOriginal = is_array($arquivos['name']) ? $arquivos['name'][$i] : $arquivos['name'];
                            $tipoArquivo = is_array($arquivos['type']) ? $arquivos['type'][$i] : $arquivos['type'];
                            $tamanho = is_array($arquivos['size']) ? $arquivos['size'][$i] : $arquivos['size'];
                            $tmpName = is_array($arquivos['tmp_name']) ? $arquivos['tmp_name'][$i] : $arquivos['tmp_name'];
                            $erro = is_array($arquivos['error']) ? $arquivos['error'][$i] : $arquivos['error'];
                            
                            // Validar tamanho (10MB máximo)
                            if ($tamanho > 10 * 1024 * 1024) {
                                continue; // Pular arquivo muito grande
                            }
                            
                            if ($erro === UPLOAD_ERR_OK) {
                                $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                                $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;
                                $caminhoCompleto = $diretorioAnexos . $nomeArquivo;
                                
                                if (move_uploaded_file($tmpName, $caminhoCompleto)) {
                                    // Salvar caminho relativo no banco
                                    $caminhoRelativo = 'uploads/tickets/' . $idTicket . '/' . $nomeArquivo;
                                    $ticket->salvarAnexo(
                                        $idTicket,
                                        $dados['id_usuario'],
                                        $nomeArquivo,
                                        $nomeOriginal,
                                        $tipoArquivo,
                                        $tamanho,
                                        $caminhoRelativo,
                                        $idComentario // Associar anexo ao comentário
                                    );
                                    $anexosSalvos[] = $nomeOriginal;
                                }
                            }
                        }
                    }
                    
                    echo json_encode(['success' => (bool)$idComentario, 'id_comentario' => $idComentario, 'anexos' => $anexosSalvos]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'PUT':
            switch ($action) {
                case 'update':
                    $dados = json_decode(file_get_contents('php://input'), true);
                    $id = (int)($_GET['id'] ?? 0);
                    
                    if ($id <= 0) {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                        break;
                    }
                    
                    $resultado = $ticket->atualizar($id, $dados);
                    echo json_encode(['success' => $resultado]);
                    break;
                    
                case 'fechar':
                    $id = (int)($_GET['id'] ?? 0);
                    $dados = json_decode(file_get_contents('php://input'), true);
                    
                    if ($id <= 0) {
                        echo json_encode(['success' => false, 'error' => 'ID inválido']);
                        break;
                    }
                    
                    $resultado = $ticket->fechar(
                        $id,
                        $dados['avaliacao'] ?? null,
                        $dados['comentario_avaliacao'] ?? null
                    );
                    echo json_encode(['success' => $resultado]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
                    break;
            }
            break;
            
        case 'DELETE':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'anexo') {
                $idAnexo = (int)($_GET['id_anexo'] ?? 0);
                if ($idAnexo > 0) {
                    $resultado = $ticket->deletarAnexo($idAnexo);
                    echo json_encode(['success' => $resultado]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID do anexo inválido']);
                }
            } else {
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    // Soft delete - apenas marcar como inativo
                    $stmt = $pdo->prepare("UPDATE tbl_tickets SET ativo = 0 WHERE id_ticket = ?");
                    $resultado = $stmt->execute([$id]);
                    echo json_encode(['success' => $resultado]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID inválido']);
                }
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 