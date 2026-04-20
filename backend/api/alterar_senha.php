<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';
require_once '../../config/session.php';
require_once '../../models/BaseModel.php';
require_once '../utils/EmailUtils.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        session_start();
        
        // Verifica se o usuário está logado
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
            exit;
        }
        
        $usuarioAtual = getCurrentUser();
        $id_usuario = $usuarioAtual['id'];

$dados = json_decode(file_get_contents('php://input'), true);
$senha_atual = $dados['senha_atual'] ?? '';
$nova_senha = $dados['nova_senha'] ?? '';

if (!$senha_atual || !$nova_senha) {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

try {
    $pdo = Conexao::getInstance()->getPdo();
    $stmt = $pdo->prepare('SELECT nome_completo, email, senha FROM tbl_usuarios WHERE id_usuario = ? AND ativo = 1');
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['erro' => 'Usuário não encontrado.']);
        exit;
    }
    // Verifica senha atual
    if (!password_verify($senha_atual, $usuario['senha'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Senha atual incorreta.']);
        exit;
    }
    // Atualiza senha
    $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE tbl_usuarios SET senha = ?, data_atualizacao = NOW() WHERE id_usuario = ?');
    $stmt->execute([$nova_hash, $id_usuario]);

    // Enviar e-mail de notificação
    $toEmail = $usuario['email'];
    $toName = $usuario['nome_completo'];
    $subject = '🔒 Alteração de Senha - Grupo Sorrisos';
    
    $dataHora = date('d/m/Y \à\s H:i');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Não identificado';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Não identificado';
    
    $htmlBody = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Alteração de Senha - Grupo Sorrisos</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f4f4; 
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white; 
                border-radius: 10px; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
                overflow: hidden; 
            }
            .header { 
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 28px; 
                font-weight: 600; 
            }
            .header p { 
                margin: 10px 0 0 0; 
                opacity: 0.9; 
                font-size: 16px; 
            }
            .content { 
                padding: 40px 30px; 
            }
            .success-icon { 
                text-align: center; 
                margin: 20px 0; 
            }
            .success-icon .icon { 
                display: inline-block; 
                width: 60px; 
                height: 60px; 
                background: #10b981; 
                border-radius: 50%; 
                line-height: 60px; 
                font-size: 30px; 
                color: white; 
            }
            .greeting { 
                font-size: 20px; 
                font-weight: 600; 
                color: #1f2937; 
                margin-bottom: 20px; 
            }
            .message { 
                font-size: 16px; 
                color: #4b5563; 
                margin-bottom: 25px; 
            }
            .info-box { 
                background: #f8fafc; 
                border: 1px solid #e2e8f0; 
                border-radius: 8px; 
                padding: 20px; 
                margin: 25px 0; 
            }
            .info-box h3 { 
                margin: 0 0 15px 0; 
                color: #1f2937; 
                font-size: 18px; 
                display: flex; 
                align-items: center; 
            }
            .info-box h3::before { 
                content: '📅'; 
                margin-right: 8px; 
            }
            .info-item { 
                display: flex; 
                justify-content: space-between; 
                margin-bottom: 8px; 
                padding: 8px 0; 
                border-bottom: 1px solid #e2e8f0; 
            }
            .info-item:last-child { 
                border-bottom: none; 
                margin-bottom: 0; 
            }
            .info-label { 
                font-weight: 600; 
                color: #374151; 
            }
            .info-value { 
                color: #6b7280; 
            }
            .warning-box { 
                background: #fef3c7; 
                border: 1px solid #f59e0b; 
                border-radius: 8px; 
                padding: 20px; 
                margin: 25px 0; 
            }
            .warning-box h3 { 
                margin: 0 0 15px 0; 
                color: #92400e; 
                font-size: 18px; 
                display: flex; 
                align-items: center; 
            }
            .warning-box h3::before { 
                content: '⚠️'; 
                margin-right: 8px; 
            }
            .warning-list { 
                margin: 0; 
                padding-left: 20px; 
                color: #92400e; 
            }
            .warning-list li { 
                margin-bottom: 8px; 
            }
            .contact-info { 
                background: #eff6ff; 
                border: 1px solid #3b82f6; 
                border-radius: 8px; 
                padding: 20px; 
                margin: 25px 0; 
                text-align: center; 
            }
            .contact-info h3 { 
                margin: 0 0 15px 0; 
                color: #1e40af; 
                font-size: 18px; 
            }
            .contact-info p { 
                margin: 0; 
                color: #1e40af; 
                font-weight: 500; 
            }
            .footer { 
                background: #f8fafc; 
                padding: 30px; 
                text-align: center; 
                color: #6b7280; 
                font-size: 14px; 
            }
            .footer p { 
                margin: 5px 0; 
            }
            .logo { 
                font-size: 24px; 
                font-weight: bold; 
                margin-bottom: 10px; 
            }
            @media (max-width: 600px) {
                .container { 
                    margin: 10px; 
                    border-radius: 8px; 
                }
                .content { 
                    padding: 30px 20px; 
                }
                .header { 
                    padding: 25px 15px; 
                }
                .header h1 { 
                    font-size: 24px; 
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>😊 Grupo Sorrisos</div>
                <h1>Alteração de Senha</h1>
                <p>Sistema de Gestão de Estoque</p>
            </div>
            
            <div class='content'>
                <div class='success-icon'>
                    <div class='icon'>✓</div>
                </div>
                
                <div class='greeting'>Olá, {$toName}!</div>
                
                <div class='message'>
                    Informamos que a senha de acesso ao seu sistema foi alterada com sucesso.
                    Esta é uma notificação de segurança para manter você informado sobre as atividades em sua conta.
                </div>
                
                <div class='info-box'>
                    <h3>Detalhes da Alteração</h3>
                    <div class='info-item'>
                        <span class='info-label'>Data e Hora:</span>
                        <span class='info-value'>{$dataHora}</span>
                    </div>
                    <div class='info-item'>
                        <span class='info-label'>Endereço IP:</span>
                        <span class='info-value'>{$ipAddress}</span>
                    </div>
                    <div class='info-item'>
                        <span class='info-label'>Dispositivo:</span>
                        <span class='info-value'>" . substr($userAgent, 0, 50) . "...</span>
                    </div>
                </div>
                
                <div class='warning-box'>
                    <h3>Importante - Segurança</h3>
                    <ul class='warning-list'>
                        <li>Se você não realizou esta alteração, entre em contato conosco imediatamente</li>
                        <li>Nunca compartilhe sua senha com outras pessoas</li>
                        <li>Use uma senha única e segura para sua conta</li>
                        <li>Ative a autenticação em duas etapas se disponível</li>
                    </ul>
                </div>
                
                <div class='contact-info'>
                    <h3>Precisa de Ajuda?</h3>
                    <p>Entre em contato com nosso suporte técnico</p>
                    <p><strong>E-mail:</strong> suporte@gruposorrisos.com.br</p>
                    <p><strong>Telefone:</strong> (11) 99999-9999</p>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>Este e-mail foi enviado automaticamente pelo sistema.</strong></p>
                <p>Por questões de segurança, não responda a esta mensagem.</p>
                <p>© " . date('Y') . " Grupo Sorrisos. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $textBody = "🔒 ALTERAÇÃO DE SENHA - GRUPO SORRISOS

Olá, {$toName}!

Informamos que a senha de acesso ao seu sistema foi alterada com sucesso.

DETALHES DA ALTERAÇÃO:
• Data e Hora: {$dataHora}
• Endereço IP: {$ipAddress}
• Dispositivo: " . substr($userAgent, 0, 50) . "...

IMPORTANTE - SEGURANÇA:
• Se você não realizou esta alteração, entre em contato conosco imediatamente
• Nunca compartilhe sua senha com outras pessoas
• Use uma senha única e segura para sua conta
• Ative a autenticação em duas etapas se disponível

PRECISA DE AJUDA?
E-mail: suporte@gruposorrisos.com.br
Telefone: (11) 99999-9999

Este e-mail foi enviado automaticamente pelo sistema.
Por questões de segurança, não responda a esta mensagem.

© " . date('Y') . " Grupo Sorrisos. Todos os direitos reservados.";
    
    EmailUtils::enviarEmail($toEmail, $toName, $subject, $htmlBody, $textBody);

    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao alterar senha: ' . $e->getMessage()]);
} 