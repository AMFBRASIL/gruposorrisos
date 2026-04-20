<?php
/**
 * Utilitários para envio de e-mails usando PHPMailer
 * GrupoSorrisos - Sistema de Gestão
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe utilitária para envio de e-mails
 */
class EmailUtils {
    
    /**
     * Configurações SMTP padrão
     */
    private static $smtpConfig = [
        'host' => 'smtp.hostinger.com',
        'username' => 'contato@gruposorrisos.com.br',
        'password' => 'Sorrisos2024###',
        'secure' => 'ssl',
        'port' => 465,
        'from_email' => 'contato@gruposorrisos.com.br',
        'from_name' => 'Grupo Sorrisos',
        'reply_to' => 'contato@gruposorrisos.com.br',
        'reply_to_name' => 'Gestão Estoque Grupo Sorrisos'
    ];
    
    /**
     * Envia e-mail usando PHPMailer com SMTP
     * 
     * @param string $toEmail E-mail do destinatário
     * @param string $toName Nome do destinatário
     * @param string $subject Assunto do e-mail
     * @param string $htmlBody Corpo HTML do e-mail
     * @param string $textBody Corpo texto do e-mail (opcional)
     * @param array $attachments Array de anexos (opcional)
     * @return bool True se enviado com sucesso, false caso contrário
     */
    public static function enviarEmail($toEmail, $toName, $subject, $htmlBody, $textBody = null, $attachments = []) {
        error_log("=== INÍCIO ENVIO EMAIL UTILS ===");
        error_log("Para: {$toEmail} ({$toName})");
        error_log("Assunto: {$subject}");
        
        try {
            $mail = new PHPMailer(true);
            
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = self::$smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$smtpConfig['username'];
            $mail->Password = self::$smtpConfig['password'];
            $mail->SMTPSecure = self::$smtpConfig['secure'];
            $mail->Port = self::$smtpConfig['port'];
            
            // Configurações de charset UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            // Configurações de debug (opcional)
            $mail->SMTPDebug = 0; // 0 = sem debug, 2 = debug completo
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: " . $str);
            };
            
            // Remetente e destinatário
            $mail->setFrom(self::$smtpConfig['from_email'], self::$smtpConfig['from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo(self::$smtpConfig['reply_to'], self::$smtpConfig['reply_to_name']);
            
            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            if ($textBody) {
                $mail->AltBody = $textBody;
            }
            
            // Adicionar anexos se fornecidos
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $name = $attachment['name'] ?? basename($attachment['path']);
                        $mail->addAttachment($attachment['path'], $name);
                    }
                }
            }
            
            error_log("Preparando para enviar e-mail via SMTP...");
            $mail->send();
            error_log("E-mail enviado com sucesso para {$toEmail}");
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail para {$toEmail}: " . $e->getMessage());
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            error_log("Arquivo: " . $e->getFile() . " Linha: " . $e->getLine());
            return false;
        } finally {
            error_log("=== FIM ENVIO EMAIL UTILS ===");
        }
    }
    
    /**
     * Envia e-mail de recuperação de senha
     * 
     * @param string $email E-mail do destinatário
     * @param string $nome Nome do destinatário
     * @param string $resetUrl URL para redefinir senha
     * @return bool True se enviado com sucesso
     */
    public static function enviarEmailRecuperacao($email, $nome, $resetUrl) {
        $subject = 'GrupoSorrisos - Recuperação de Senha';
        
        $htmlBody = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperação de Senha - GrupoSorrisos</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f8f9fa; }
                .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>GrupoSorrisos</h1>
                    <p>Recuperação de Senha</p>
                </div>
                <div class='content'>
                    <h2>Olá, {$nome}!</h2>
                    <p>Recebemos uma solicitação para redefinir sua senha no GrupoSorrisos.</p>
                    <p>Para continuar com a recuperação, clique no botão abaixo:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Redefinir Minha Senha</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este link é válido por 1 hora</li>
                            <li>Se você não solicitou esta recuperação, ignore este e-mail</li>
                            <li>Nunca compartilhe este link com outras pessoas</li>
                        </ul>
                    </div>
                    
                    <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
                    <p style='word-break: break-all; color: #666;'>{$resetUrl}</p>
                    
                    <p>Atenciosamente,<br>Equipe GrupoSorrisos</p>
                </div>
                <div class='footer'>
                    <p>Este e-mail foi enviado automaticamente. Não responda a esta mensagem.</p>
                    <p>© " . date('Y') . " GrupoSorrisos. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $textBody = "Olá {$nome}!\n\nRecebemos uma solicitação para redefinir sua senha no GrupoSorrisos.\n\nPara continuar com a recuperação, acesse este link: {$resetUrl}\n\nEste link é válido por 1 hora.\n\nSe você não solicitou esta recuperação, ignore este e-mail.\n\nAtenciosamente,\nEquipe GrupoSorrisos";
        
        return self::enviarEmail($email, $nome, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Envia e-mail de confirmação de redefinição de senha
     * 
     * @param string $email E-mail do destinatário
     * @param string $nome Nome do destinatário
     * @return bool True se enviado com sucesso
     */
    public static function enviarEmailConfirmacaoRedefinicao($email, $nome) {
        $subject = 'GrupoSorrisos - Senha Redefinida com Sucesso';
        
        $htmlBody = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Senha Redefinida - GrupoSorrisos</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f8f9fa; }
                .success-icon { font-size: 3rem; color: #198754; text-align: center; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>GrupoSorrisos</h1>
                    <p>Senha Redefinida</p>
                </div>
                <div class='content'>
                    <div class='success-icon'>✓</div>
                    <h2>Olá, {$nome}!</h2>
                    <p>Sua senha foi redefinida com sucesso no GrupoSorrisos.</p>
                    <p>Você já pode fazer login com sua nova senha.</p>
                    
                    <div class='warning'>
                        <strong>🔒 Segurança:</strong>
                        <ul>
                            <li>Mantenha sua senha em local seguro</li>
                            <li>Não compartilhe sua senha com outras pessoas</li>
                            <li>Use uma senha única para cada serviço</li>
                            <li>Se você não solicitou esta redefinição, entre em contato conosco imediatamente</li>
                        </ul>
                    </div>
                    
                    <p>Para acessar sua conta, clique no link abaixo:</p>
                    <p style='text-align: center;'>
                        <a href='https://app.gruposorrisos.com.br' style='display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>
                            Acessar GrupoSorrisos
                        </a>
                    </p>
                    
                    <p>Atenciosamente,<br>Equipe GrupoSorrisos</p>
                </div>
                <div class='footer'>
                    <p>Este e-mail foi enviado automaticamente. Não responda a esta mensagem.</p>
                    <p>© " . date('Y') . " GrupoSorrisos. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $textBody = "Olá {$nome}!\n\nSua senha foi redefinida com sucesso no GrupoSorrisos.\n\nVocê já pode fazer login com sua nova senha em: https://fornecedor.cadbr.com.br/login\n\nSe você não solicitou esta redefinição, entre em contato conosco imediatamente.\n\nAtenciosamente,\nEquipe GrupoSorrisos";
        
        return self::enviarEmail($email, $nome, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Envia e-mail de pedido de compra para fornecedor
     * 
     * @param array $pedido Dados do pedido de compra
     * @param array $fornecedor Dados do fornecedor
     * @return bool True se enviado com sucesso
     */
    public static function enviarEmailPedidoCompra($pedido, $fornecedor) {
        $subject = "Pedido de Compra {$pedido['numero_pedido']} - {$fornecedor['razao_social']}";
        
        // Formatar status para exibição
        $statusDisplay = self::formatarStatusPedido($pedido['status']);
        
        // Formatar datas
        $dataSolicitacao = date('d/m/Y H:i', strtotime($pedido['data_solicitacao']));
        $dataEntrega = $pedido['data_entrega_prevista'] ? 
            date('d/m/Y', strtotime($pedido['data_entrega_prevista'])) : 'Não informado';
        
        // Formatar valores monetários
        $valorTotal = number_format($pedido['valor_total'], 2, ',', '.');
        
        // Preparar itens
        $itensHtml = '';
        if (!empty($pedido['itens'])) {
            foreach ($pedido['itens'] as $item) {
                $itensHtml .= '<tr>';
                $itensHtml .= '<td><strong>' . htmlspecialchars($item['codigo_material']) . '</strong></td>';
                $itensHtml .= '<td>' . htmlspecialchars($item['nome_material']) . '</td>';
                $itensHtml .= '<td>' . $item['quantidade'] . '</td>';
                $itensHtml .= '<td>R$ ' . number_format($item['preco_unitario'], 2, ',', '.') . '</td>';
                $itensHtml .= '<td><strong>R$ ' . number_format($item['valor_total'], 2, ',', '.') . '</strong></td>';
                $itensHtml .= '</tr>';
            }
        } else {
            $itensHtml = '<tr><td colspan="5" style="text-align: center; color: #6c757d;">Nenhum item encontrado</td></tr>';
        }
        
        // Gerar links de ação
        $baseUrl = self::getBaseUrl();
        $linkAprovacao = $baseUrl . "/fornecedor/aprovar-pedido.php?id=" . $pedido['id_pedido'];
        $linkVisualizacao = $baseUrl . "/fornecedor/visualizar-pedido.php?id=" . $pedido['id_pedido'];
        
        $htmlBody = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Pedido de Compra - {$pedido['numero_pedido']}</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; }
                .email-container { background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
                .header .subtitle { margin: 10px 0 0 0; opacity: 0.9; font-size: 16px; }
                .content { padding: 30px; }
                .info-section { margin-bottom: 25px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #2563eb; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
                .info-item { margin-bottom: 15px; }
                .info-label { font-weight: 600; color: #2563eb; margin-bottom: 5px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
                .info-value { font-size: 16px; color: #333; }
                .table-container { margin: 25px 0; overflow-x: auto; }
                .items-table { width: 100%; border-collapse: collapse; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
                .items-table th { background-color: #2563eb; color: white; padding: 15px; text-align: left; font-weight: 600; font-size: 14px; }
                .items-table td { padding: 15px; border-bottom: 1px solid #e9ecef; font-size: 14px; }
                .items-table tr:nth-child(even) { background-color: #f8f9fa; }
                .total-section { background-color: #e8f5e8; padding: 20px; border-radius: 8px; text-align: right; margin-top: 20px; }
                .total-value { font-size: 24px; font-weight: 700; color: #28a745; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e9ecef; }
                .footer p { margin: 5px 0; color: #6c757d; font-size: 14px; }
                .action-buttons { text-align: center; margin: 30px 0; }
                .btn { display: inline-block; padding: 12px 24px; margin: 0 10px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; }
                .btn-primary { background-color: #2563eb; color: white; }
                .btn-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); }
                .btn-success { background-color: #28a745; color: white; }
                .btn-success:hover { background-color: #218838; transform: translateY(-2px); }
                .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
                .status-pendente { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
                .status-aprovado { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
                .status-em_producao { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
                .status-enviado { background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
                .status-recebido { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
                .status-cancelado { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
                @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } .content { padding: 20px; } .header { padding: 20px; } }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1>📋 Pedido de Compra</h1>
                    <p class='subtitle'>{$pedido['numero_pedido']}</p>
                </div>
                
                <div class='content'>
                    <div class='info-section'>
                        <h3 style='margin-top: 0; color: #2563eb;'>📋 Informações do Pedido</h3>
                        <div class='info-grid'>
                            <div class='info-item'>
                                <div class='info-label'>Número do Pedido</div>
                                <div class='info-value'>{$pedido['numero_pedido']}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Status</div>
                                <div class='info-value'>
                                    <span class='status-badge status-{$pedido['status']}'>{$statusDisplay}</span>
                                </div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Data de Solicitação</div>
                                <div class='info-value'>{$dataSolicitacao}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Entrega Prevista</div>
                                <div class='info-value'>{$dataEntrega}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Clínica</div>
                                <div class='info-value'>{$pedido['nome_filial']}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Solicitante</div>
                                <div class='info-value'>{$pedido['nome_usuario']}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class='info-section'>
                        <h3 style='margin-top: 0; color: #2563eb;'>🛍️ Itens Solicitados</h3>
                        <div class='table-container'>
                            <table class='items-table'>
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Material</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unitário</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$itensHtml}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class='total-section'>
                            <div class='info-label'>Valor Total do Pedido</div>
                            <div class='total-value'>R$ {$valorTotal}</div>
                        </div>
                    </div>";
        
        // Adicionar observações se existirem
        if (!empty($pedido['observacoes'])) {
            $htmlBody .= "
                    <div class='info-section'>
                        <h3 style='margin-top: 0; color: #2563eb;'>💬 Observações</h3>
                        <p style='margin: 0; font-style: italic;'>{$pedido['observacoes']}</p>
                    </div>";
        }
        
        $htmlBody .= "
                    <div class='action-buttons'>
                        <a href='{$linkAprovacao}' class='btn btn-success'>✅ Aprovar Pedido</a>
                        <a href='{$linkVisualizacao}' class='btn btn-primary'>👁️ Visualizar Detalhes</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Este é um email automático do sistema de gestão de estoque.</strong></p>
                    <p>Para dúvidas, entre em contato com a equipe de compras.</p>
                    <p>Data de envio: " . date('d/m/Y H:i') . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        $textBody = "Pedido de Compra {$pedido['numero_pedido']}\n\n";
        $textBody .= "Fornecedor: {$fornecedor['razao_social']}\n";
        $textBody .= "Clínica: {$pedido['nome_filial']}\n";
        $textBody .= "Data de Solicitação: {$dataSolicitacao}\n";
        $textBody .= "Entrega Prevista: {$dataEntrega}\n";
        $textBody .= "Valor Total: R$ {$valorTotal}\n\n";
        $textBody .= "Para aprovar o pedido, acesse: {$linkAprovacao}\n";
        $textBody .= "Para visualizar detalhes: {$linkVisualizacao}\n\n";
        $textBody .= "Este é um email automático do sistema de gestão de estoque.";
        
        return self::enviarEmail($fornecedor['email'], $fornecedor['razao_social'], $subject, $htmlBody, $textBody);
    }
    
    /**
     * Envia email de boas-vindas para novo usuário
     * 
     * @param string $email Email do usuário
     * @param string $nome Nome completo do usuário
     * @param string $senhaTemporaria Senha temporária gerada
     * @param string $perfil Nome do perfil do usuário
     * @return bool True se enviado com sucesso
     */
    public static function enviarEmailBoasVindas($email, $nome, $senhaTemporaria, $perfil) {
        $subject = "🎉 Bem-vindo ao Sistema Grupo Sorrisos!";
        
        $htmlBody = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Bem-vindo ao Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .welcome-title { font-size: 28px; margin: 0 0 10px 0; }
                .welcome-subtitle { font-size: 16px; margin: 0; opacity: 0.9; }
                .info-section { background: white; padding: 25px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .info-title { color: #2563eb; font-size: 20px; margin: 0 0 15px 0; }
                .info-item { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
                .info-label { font-weight: bold; color: #6b7280; }
                .info-value { color: #111827; }
                .credentials { background: #dbeafe; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .credentials-title { color: #1e40af; font-size: 18px; margin: 0 0 15px 0; text-align: center; }
                .credential-item { display: flex; justify-content: space-between; margin: 8px 0; padding: 8px 0; }
                .credential-label { font-weight: bold; color: #1e40af; }
                .credential-value { font-family: monospace; background: #eff6ff; padding: 4px 8px; border-radius: 4px; color: #1e40af; }
                .action-buttons { text-align: center; margin: 25px 0; }
                .btn { display: inline-block; padding: 12px 24px; margin: 0 10px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
                .btn:hover { background: #2563eb; }
                .footer { text-align: center; margin-top: 30px; padding: 20px; color: #6b7280; font-size: 14px; }
                .important { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0; }
                .important-title { color: #d97706; font-weight: bold; margin: 0 0 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 class='welcome-title'>🎉 Bem-vindo ao Sistema!</h1>
                    <p class='welcome-subtitle'>Grupo Sorrisos - Sistema de Gestão de Estoque</p>
                </div>
                
                <div class='content'>
                    <div class='info-section'>
                        <h2 class='info-title'>👋 Olá, {$nome}!</h2>
                        <p>Seja bem-vindo ao sistema de gestão de estoque do Grupo Sorrisos!</p>
                        <p>Seu perfil foi configurado como: <strong>{$perfil}</strong></p>
                    </div>
                    
                    <div class='credentials'>
                        <h3 class='credentials-title'>🔐 Suas Credenciais de Acesso</h3>
                        <div class='credential-item'>
                            <span class='credential-label'>E-mail:</span>
                            <span class='credential-value'>{$email}</span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label'>Senha:</span>
                            <span class='credential-value'>{$senhaTemporaria}</span>
                        </div>
                    </div>
                    
                    <div class='important'>
                        <div class='important-title'>⚠️ Importante:</div>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>Esta é sua senha temporária de acesso</li>
                            <li>Recomendamos alterar a senha no primeiro acesso</li>
                            <li>Mantenha suas credenciais seguras</li>
                        </ul>
                    </div>
                    
                    <div class='action-buttons'>
                        <a href='https://app.gruposorrisos.com.br/login' class='btn'>🚀 Acessar o Sistema</a>
                    </div>
                    
                    <div class='info-section'>
                        <h3 class='info-title'>📱 Como Acessar</h3>
                        <ol style='margin: 10px 0; padding-left: 20px;'>
                            <li>Acesse o sistema através do link acima</li>
                            <li>Use seu e-mail e a senha temporária</li>
                            <li>Após o primeiro acesso, altere sua senha</li>
                            <li>Explore as funcionalidades disponíveis para seu perfil</li>
                        </ol>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Este é um email automático do sistema de gestão de estoque.</strong></p>
                    <p>Para dúvidas, entre em contato com o administrador do sistema.</p>
                    <p>Data de envio: " . date('d/m/Y H:i') . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        $textBody = "Bem-vindo ao Sistema Grupo Sorrisos!\n\n";
        $textBody .= "Olá, {$nome}!\n";
        $textBody .= "Seja bem-vindo ao sistema de gestão de estoque do Grupo Sorrisos!\n\n";
        $textBody .= "Suas credenciais de acesso:\n";
        $textBody .= "E-mail: {$email}\n";
        $textBody .= "Senha: {$senhaTemporaria}\n\n";
        $textBody .= "Perfil: {$perfil}\n\n";
        $textBody .= "Importante:\n";
        $textBody .= "- Esta é sua senha temporária de acesso\n";
        $textBody .= "- Recomendamos alterar a senha no primeiro acesso\n";
        $textBody .= "- Mantenha suas credenciais seguras\n\n";
        $textBody .= "Para acessar o sistema, use o link: " . self::getBaseUrl() . "/login.php\n\n";
        $textBody .= "Este é um email automático do sistema de gestão de estoque.";
        
        return self::enviarEmail($email, $nome, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Formatar status do pedido para exibição
     */
    private static function formatarStatusPedido($status) {
        $statusMap = [
            'pendente' => 'Pendente',
            'aprovado' => 'Aprovado',
            'em_producao' => 'Em Produção',
            'enviado' => 'Enviado',
            'recebido' => 'Recebido',
            'cancelado' => 'Cancelado'
        ];
        
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * Obter URL base do sistema
     */
    private static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        
        return $protocol . '://' . $host . $path;
    }
}
?> 