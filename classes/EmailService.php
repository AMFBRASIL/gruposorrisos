<?php

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        // Configurações SMTP (ajustar conforme seu servidor)
        $this->smtp_host = 'smtp.gmail.com'; // ou seu servidor SMTP
        $this->smtp_port = 587;
        $this->smtp_username = 'seu-email@gmail.com'; // configurar
        $this->smtp_password = 'sua-senha-app'; // configurar
        $this->smtp_encryption = 'tls';
        $this->from_email = 'compras@sistema.com';
        $this->from_name = 'Sistema de Compras';
    }
    
    /**
     * Enviar pedido de compra para fornecedor
     */
    public function enviarPedidoCompra($pedido, $fornecedor) {
        try {
            // Preparar dados do email
            $dados = $this->prepararDadosPedido($pedido, $fornecedor);
            
            // Carregar template
            $template = $this->carregarTemplate('templates/email_pedido_compra.html');
            
            // Substituir variáveis no template
            $conteudo = $this->substituirVariaveis($template, $dados);
            
            // Enviar email
            $assunto = "Pedido de Compra {$pedido['numero_pedido']} - {$fornecedor['razao_social']}";
            
            return $this->enviarEmail(
                $fornecedor['email'],
                $fornecedor['razao_social'],
                $assunto,
                $conteudo
            );
            
        } catch (Exception $e) {
            error_log("Erro ao enviar pedido de compra: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Preparar dados para o template
     */
    private function prepararDadosPedido($pedido, $fornecedor) {
        // Formatar status para exibição
        $statusDisplay = $this->formatarStatus($pedido['status']);
        
        // Formatar datas
        $dataSolicitacao = date('d/m/Y H:i', strtotime($pedido['data_solicitacao']));
        $dataEntrega = $pedido['data_entrega_prevista'] ? 
            date('d/m/Y', strtotime($pedido['data_entrega_prevista'])) : 'Não informado';
        
        // Formatar valores monetários
        $valorTotal = number_format($pedido['valor_total'], 2, ',', '.');
        
        // Preparar itens
        $itens = [];
        if (!empty($pedido['itens'])) {
            foreach ($pedido['itens'] as $item) {
                $itens[] = [
                    'codigo_material' => $item['codigo_material'],
                    'nome_material' => $item['nome_material'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => number_format($item['preco_unitario'], 2, ',', '.'),
                    'valor_total' => number_format($item['valor_total'], 2, ',', '.')
                ];
            }
        }
        
        // Gerar links de ação
        $baseUrl = $this->getBaseUrl();
        $linkAprovacao = $baseUrl . "/fornecedor/aprovar-pedido.php?id=" . $pedido['id_pedido'];
        $linkVisualizacao = $baseUrl . "/fornecedor/visualizar-pedido.php?id=" . $pedido['id_pedido'];
        
        return [
            'numero_pedido' => $pedido['numero_pedido'],
            'status' => $pedido['status'],
            'status_display' => $statusDisplay,
            'data_solicitacao' => $dataSolicitacao,
            'data_entrega_prevista' => $dataEntrega,
            'nome_filial' => $pedido['nome_filial'],
            'nome_usuario' => $pedido['nome_usuario'],
            'valor_total' => $valorTotal,
            'observacoes' => $pedido['observacoes'] ?? '',
            'itens' => $itens,
            'link_aprovacao' => $linkAprovacao,
            'link_visualizacao' => $linkVisualizacao,
            'data_envio' => date('d/m/Y H:i')
        ];
    }
    
    /**
     * Formatar status para exibição
     */
    private function formatarStatus($status) {
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
     * Carregar template HTML
     */
    private function carregarTemplate($caminho) {
        if (!file_exists($caminho)) {
            throw new Exception("Template não encontrado: {$caminho}");
        }
        
        return file_get_contents($caminho);
    }
    
    /**
     * Substituir variáveis no template
     */
    private function substituirVariaveis($template, $dados) {
        $conteudo = $template;
        
        // Substituir variáveis simples
        foreach ($dados as $chave => $valor) {
            if (is_string($valor) || is_numeric($valor)) {
                $conteudo = str_replace("{{" . $chave . "}}", $valor, $conteudo);
            }
        }
        
        // Substituir itens (array)
        if (!empty($dados['itens'])) {
            $itensHtml = '';
            foreach ($dados['itens'] as $item) {
                $itemHtml = '<tr>';
                $itemHtml .= '<td><strong>' . $item['codigo_material'] . '</strong></td>';
                $itemHtml .= '<td>' . $item['nome_material'] . '</td>';
                $itemHtml .= '<td>' . $item['quantidade'] . '</td>';
                $itemHtml .= '<td>R$ ' . $item['preco_unitario'] . '</td>';
                $itemHtml .= '<td><strong>R$ ' . $item['valor_total'] . '</strong></td>';
                $itemHtml .= '</tr>';
                $itensHtml .= $itemHtml;
            }
            $conteudo = str_replace('{{#itens}}', '', $conteudo);
            $conteudo = str_replace('{{/itens}}', '', $conteudo);
            $conteudo = str_replace('{{#itens}}', $itensHtml, $conteudo);
        } else {
            $conteudo = str_replace('{{#itens}}', '', $conteudo);
            $conteudo = str_replace('{{/itens}}', '', $conteudo);
            $conteudo = str_replace('{{#itens}}', '<tr><td colspan="5" style="text-align: center; color: #6c757d;">Nenhum item encontrado</td></tr>', $conteudo);
        }
        
        // Substituir observações (condicional)
        if (empty($dados['observacoes'])) {
            $conteudo = str_replace('{{#observacoes}}', '', $conteudo);
            $conteudo = str_replace('{{/observacoes}}', '', $conteudo);
        } else {
            $conteudo = str_replace('{{#observacoes}}', '', $conteudo);
            $conteudo = str_replace('{{/observacoes}}', '', $conteudo);
        }
        
        return $conteudo;
    }
    
    /**
     * Enviar email usando PHPMailer ou função nativa
     */
    private function enviarEmail($para, $nomePara, $assunto, $conteudo) {
        // Verificar se PHPMailer está disponível
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->enviarComPHPMailer($para, $nomePara, $assunto, $conteudo);
        } else {
            return $this->enviarComFuncaoNativa($para, $nomePara, $assunto, $conteudo);
        }
    }
    
    /**
     * Enviar usando PHPMailer (recomendado)
     */
    private function enviarComPHPMailer($para, $nomePara, $assunto, $conteudo) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            $mail->Port = $this->smtp_port;
            $mail->CharSet = 'UTF-8';
            
            // Remetente e destinatário
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($para, $nomePara);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $conteudo;
            $mail->AltBody = strip_tags($conteudo);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erro PHPMailer: " . $e->getMessage());
            throw new Exception("Erro ao enviar email: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar usando função nativa do PHP (fallback)
     */
    private function enviarComFuncaoNativa($para, $nomePara, $assunto, $conteudo) {
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->from_name . ' <' . $this->from_email . '>',
                'Reply-To: ' . $this->from_email,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $resultado = mail($para, $assunto, $conteudo, implode("\r\n", $headers));
            
            if (!$resultado) {
                throw new Exception("Falha ao enviar email usando função nativa");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro função nativa: " . $e->getMessage());
            throw new Exception("Erro ao enviar email: " . $e->getMessage());
        }
    }
    
    /**
     * Obter URL base do sistema
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Configurar credenciais SMTP
     */
    public function configurarSMTP($host, $port, $username, $password, $encryption = 'tls') {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_encryption = $encryption;
    }
    
    /**
     * Configurar remetente
     */
    public function configurarRemetente($email, $nome) {
        $this->from_email = $email;
        $this->from_name = $nome;
    }
} 