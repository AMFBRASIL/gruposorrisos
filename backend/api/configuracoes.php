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
require_once '../../config/session.php';
require_once '../../models/Configuracao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS' && !isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $configuracao = new Configuracao();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'buscar_por_chave':
                    $chave = $_GET['chave'] ?? '';
                    if (empty($chave)) {
                        echo json_encode(['success' => false, 'error' => 'Chave é obrigatória']);
                        break;
                    }
                    
                    $resultado = $configuracao->buscarPorChave($chave);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'buscar_por_categoria':
                    $categoria = $_GET['categoria'] ?? '';
                    if (empty($categoria)) {
                        echo json_encode(['success' => false, 'error' => 'Categoria é obrigatória']);
                        break;
                    }
                    
                    $resultado = $configuracao->buscarPorCategoria($categoria);
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'buscar_agrupadas':
                    $resultado = $configuracao->buscarAgrupadasPorCategoria();
                    foreach ($resultado as $cat => $lista) {
                        $chavesSecretas = ['smtp_password', 'mailgun_api_key'];
                    foreach ($lista as $i => $row) {
                            $ch = $row['chave'] ?? '';
                            if (in_array($ch, $chavesSecretas, true) && !empty($row['valor'])) {
                                $resultado[$cat][$i]['valor'] = '';
                                $resultado[$cat][$i]['_senha_definida'] = true;
                            }
                        }
                    }
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                case 'estatisticas':
                    $resultado = $configuracao->getEstatisticas();
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
                    
                default:
                    // Buscar todas as configurações
                    $resultado = $configuracao->buscarTodas();
                    echo json_encode(['success' => true, 'data' => $resultado]);
                    break;
            }
            break;
            
        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (isset($dados['action'])) {
                switch ($dados['action']) {
                    case 'atualizar_multiplas':
                        if (!isset($dados['configuracoes']) || !is_array($dados['configuracoes'])) {
                            echo json_encode(['success' => false, 'error' => 'Configurações são obrigatórias']);
                            break;
                        }
                        
                        $lista = $dados['configuracoes'];
                        // Não sobrescrever segredos se o campo veio em branco
                        foreach (['smtp_password', 'mailgun_api_key'] as $chaveSecreta) {
                            if (isset($lista[$chaveSecreta]) && trim((string)$lista[$chaveSecreta]) === '') {
                                unset($lista[$chaveSecreta]);
                            }
                        }
                        if (isset($lista['email_provedor'])) {
                            $p = strtolower(trim((string)$lista['email_provedor']));
                            $lista['email_provedor'] = ($p === 'mailgun') ? 'mailgun' : 'smtp';
                        }
                        if (isset($lista['mailgun_domain'])) {
                            require_once __DIR__ . '/../utils/MailgunSender.php';
                            $lista['mailgun_domain'] = MailgunSender::normalizarDominio((string)$lista['mailgun_domain']);
                        }
                        
                        $configuracao->atualizarConfiguracoes($lista);
                        echo json_encode(['success' => true, 'message' => 'Configurações atualizadas com sucesso']);
                        break;

                    case 'testar_email':
                    case 'testar_smtp':
                        $emailTeste = isset($dados['email_teste']) ? trim((string)$dados['email_teste']) : '';
                        if ($emailTeste === '' || filter_var($emailTeste, FILTER_VALIDATE_EMAIL) === false) {
                            echo json_encode(['success' => false, 'error' => 'Informe um e-mail válido para o teste']);
                            break;
                        }
                        require_once __DIR__ . '/../utils/EmailUtils.php';
                        $override = null;
                        if (!empty($dados['config_teste']) && is_array($dados['config_teste'])) {
                            $override = $dados['config_teste'];
                        }
                        $provedor = EmailUtils::obterProvedorEmail();
                        if ($override !== null && !empty($override['email_provedor'])) {
                            $p = strtolower(trim((string)$override['email_provedor']));
                            $provedor = ($p === 'mailgun') ? 'mailgun' : 'smtp';
                        }
                        if ($provedor === 'mailgun') {
                            if (!function_exists('curl_init')) {
                                echo json_encode(['success' => false, 'error' => 'Extensão cURL do PHP não está ativa. Habilite extension=curl no php.ini do XAMPP.']);
                                break;
                            }
                        } else {
                            $vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
                            if (!$vendorAutoload || !is_readable($vendorAutoload)) {
                                echo json_encode(['success' => false, 'error' => 'Composer (vendor) não encontrado no servidor']);
                                break;
                            }
                        }
                        $rotulo = $provedor === 'mailgun' ? 'Mailgun' : 'SMTP';
                        $html = '<p>Este é um e-mail de teste do sistema <strong>Grupo Sorrisos</strong>.</p><p>Se você recebeu esta mensagem, a configuração de e-mail (' . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . ') está correta.</p>';
                        $texto = "E-mail de teste Grupo Sorrisos.\nProvedor: {$rotulo}.";
                        $ok = EmailUtils::enviarEmail(
                            $emailTeste,
                            'Teste de e-mail',
                            'Grupo Sorrisos — Teste de envio (' . $rotulo . ')',
                            $html,
                            $texto,
                            [],
                            $override
                        );
                        $detalhe = EmailUtils::getUltimoErro();
                        $msgErro = $detalhe ?: ($provedor === 'mailgun'
                            ? 'Falha ao enviar. Verifique domínio, região, chave API e remetente.'
                            : 'Falha ao enviar. Verifique host, porta, usuário e senha.');
                        echo json_encode([
                            'success' => $ok,
                            'error' => $ok ? null : $msgErro,
                            'message' => $ok ? 'E-mail de teste enviado. Verifique a caixa de entrada (e spam).' : $msgErro
                        ]);
                        break;
                        
                    case 'definir_valor':
                        if (!isset($dados['chave']) || !isset($dados['valor'])) {
                            echo json_encode(['success' => false, 'error' => 'Chave e valor são obrigatórios']);
                            break;
                        }
                        
                        $configuracao->setValor($dados['chave'], $dados['valor']);
                        echo json_encode(['success' => true, 'message' => 'Configuração atualizada com sucesso']);
                        break;
                        
                    default:
                        echo json_encode(['success' => false, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ação não especificada']);
            }
            break;
            
        case 'PUT':
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($dados['chave']) || !isset($dados['valor'])) {
                echo json_encode(['success' => false, 'error' => 'Chave e valor são obrigatórios']);
                break;
            }
            
            $configuracao->setValor($dados['chave'], $dados['valor']);
            echo json_encode(['success' => true, 'message' => 'Configuração atualizada com sucesso']);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
} 