<?php
/**
 * Envio de e-mails via API Mailgun (v3).
 */

class MailgunSender {

    /** @var string|null */
    private static $ultimoErro = null;

    public static function getUltimoErro(): ?string {
        return self::$ultimoErro;
    }

    public static function normalizarDominio(string $domain): string {
        $d = trim($domain);
        $d = preg_replace('#^https?://#i', '', $d);
        $d = preg_replace('#/.*$#', '', $d);
        return strtolower(trim($d));
    }

    /**
     * @param array{api_key:string,domain:string,api_base:string,from_email:string,from_name:string} $config
     * @return array{success:bool,error?:string}
     */
    public static function enviarComResultado(
        array $config,
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null
    ): array {
        self::$ultimoErro = null;

        if (!function_exists('curl_init')) {
            self::$ultimoErro = 'Extensão cURL do PHP não está habilitada no servidor (ative em php.ini).';
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        $apiKey = trim($config['api_key'] ?? '');
        $domain = self::normalizarDominio((string)($config['domain'] ?? ''));
        if ($apiKey === '') {
            self::$ultimoErro = 'Chave API Mailgun não configurada. Salve a chave em Configurações.';
            return ['success' => false, 'error' => self::$ultimoErro];
        }
        if ($domain === '') {
            self::$ultimoErro = 'Domínio Mailgun não informado.';
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        $apiBase = rtrim(trim($config['api_base'] ?? 'https://api.mailgun.net'), '/');
        $fromEmail = trim($config['from_email'] ?? '');
        $fromName = trim($config['from_name'] ?? 'Grupo Sorrisos');
        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            self::$ultimoErro = 'E-mail remetente (From) inválido ou vazio.';
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        if (strpos($domain, 'sandbox') !== false && stripos($fromEmail, $domain) === false) {
            self::$ultimoErro = 'Domínio sandbox: use como remetente postmaster@' . $domain
                . ' (e autorize o destinatário do teste no painel Mailgun).';
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        $from = $fromName !== '' ? "{$fromName} <{$fromEmail}>" : $fromEmail;
        $to = $toName !== '' ? "{$toName} <{$toEmail}>" : $toEmail;

        $url = "{$apiBase}/v3/{$domain}/messages";
        $post = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'html' => $htmlBody,
        ];
        if ($textBody !== null && $textBody !== '') {
            $post['text'] = $textBody;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            self::$ultimoErro = 'Não foi possível iniciar a conexão HTTP (curl_init).';
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => 'api:' . $apiKey,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            self::$ultimoErro = 'Erro de rede: ' . ($curlErr ?: 'falha cURL');
            if (stripos($curlErr, 'SSL') !== false) {
                self::$ultimoErro .= ' — verifique certificados CA do PHP/XAMPP.';
            }
            return ['success' => false, 'error' => self::$ultimoErro];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            error_log("MailgunSender: enviado para {$toEmail} (HTTP {$httpCode})");
            return ['success' => true];
        }

        $msgApi = self::extrairMensagemApi($response);
        self::$ultimoErro = self::mensagemHttp($httpCode, $msgApi, $domain, $apiBase);
        error_log("MailgunSender: HTTP {$httpCode} — {$response}");
        return ['success' => false, 'error' => self::$ultimoErro];
    }

    public static function enviar(
        array $config,
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null
    ): bool {
        return self::enviarComResultado($config, $toEmail, $toName, $subject, $htmlBody, $textBody)['success'];
    }

    private static function extrairMensagemApi(string $response): string {
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return trim($response) !== '' ? substr(trim($response), 0, 300) : '';
        }
        if (!empty($data['message'])) {
            return (string) $data['message'];
        }
        if (!empty($data['error'])) {
            return is_string($data['error']) ? $data['error'] : json_encode($data['error']);
        }
        return '';
    }

    private static function mensagemHttp(int $code, string $apiMsg, string $domain, string $apiBase): string {
        $base = $apiMsg !== '' ? $apiMsg : 'Erro desconhecido da Mailgun';
        $regiao = (strpos($apiBase, 'api.eu.') !== false) ? 'Europa' : 'EUA';

        switch ($code) {
            case 401:
                return "Não autorizado (401): {$base}. Confira a chave API e se a região ({$regiao}) está correta.";
            case 403:
                return "Proibido (403): {$base}. Domínio «{$domain}» ou remetente pode não estar autorizado.";
            case 404:
                return "Domínio não encontrado (404): «{$domain}». Use só o nome (ex.: mg.seudominio.com), sem https://.";
            default:
                return "Mailgun HTTP {$code}: {$base}";
        }
    }
}
