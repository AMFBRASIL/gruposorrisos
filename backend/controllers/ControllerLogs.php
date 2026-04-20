<?php
/**
 * Controller para gerenciamento de logs do sistema
 */

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/LogSistema.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Filial.php';

class ControllerLogs {
    private $pdo;
    private $logModel;
    private $usuarioModel;
    private $filialModel;
    
    public function __construct() {
        $this->pdo = Conexao::getInstance()->getPdo();
        $this->logModel = new LogSistema($this->pdo);
        $this->usuarioModel = new Usuario($this->pdo);
        $this->filialModel = new Filial($this->pdo);
    }
    
    /**
     * Lista logs com filtros e paginação
     */
    public function listar($filtros = []) {
        $page = $filtros['page'] ?? 1;
        $limit = $filtros['limit'] ?? 50;
        $search = $filtros['search'] ?? '';
        $usuario = $filtros['usuario'] ?? '';
        $filial = $filtros['filial'] ?? '';
        $acao = $filtros['acao'] ?? '';
        $tabela = $filtros['tabela'] ?? '';
        $dataInicio = $filtros['data_inicio'] ?? '';
        $dataFim = $filtros['data_fim'] ?? '';
        $ip = $filtros['ip'] ?? '';
        
        $where = [];
        $params = [];
        
        // Filtro de busca geral
        if (!empty($search)) {
            $where[] = "(l.acao LIKE ? OR l.tabela LIKE ? OR l.ip_usuario LIKE ? OR u.nome_completo LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Filtro por usuário
        if (!empty($usuario)) {
            $where[] = "l.id_usuario = ?";
            $params[] = $usuario;
        }
        
        // Filtro por filial
        if (!empty($filial)) {
            $where[] = "l.id_filial = ?";
            $params[] = $filial;
        }
        
        // Filtro por ação
        if (!empty($acao)) {
            $where[] = "l.acao = ?";
            $params[] = $acao;
        }
        
        // Filtro por tabela
        if (!empty($tabela)) {
            $where[] = "l.tabela = ?";
            $params[] = $tabela;
        }
        
        // Filtro por período
        if (!empty($dataInicio) && !empty($dataFim)) {
            $where[] = "DATE(l.data_log) BETWEEN ? AND ?";
            $params[] = $dataInicio;
            $params[] = $dataFim;
        } elseif (!empty($dataInicio)) {
            $where[] = "DATE(l.data_log) >= ?";
            $params[] = $dataInicio;
        } elseif (!empty($dataFim)) {
            $where[] = "DATE(l.data_log) <= ?";
            $params[] = $dataFim;
        }
        
        // Filtro por IP
        if (!empty($ip)) {
            $where[] = "l.ip_usuario = ?";
            $params[] = $ip;
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->logModel->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca log por ID
     */
    public function buscarPorId($id) {
        if (empty($id)) {
            throw new Exception('ID do log é obrigatório');
        }
        
        $log = $this->logModel->findByIdWithRelations($id);
        
        if (!$log) {
            throw new Exception('Log não encontrado');
        }
        
        return $log;
    }
    
    /**
     * Busca estatísticas dos logs
     */
    public function obterEstatisticas() {
        return $this->logModel->getEstatisticas();
    }
    
    /**
     * Busca ações distintas
     */
    public function obterAcoes() {
        return $this->logModel->getAcoesDistintas();
    }
    
    /**
     * Busca tabelas distintas
     */
    public function obterTabelas() {
        return $this->logModel->getTabelasDistintas();
    }
    
    /**
     * Busca usuários para filtro
     */
    public function obterUsuarios() {
        return $this->usuarioModel->findAll();
    }
    
    /**
     * Busca filiais para filtro
     */
    public function obterFiliais() {
        try {
            return $this->filialModel->findAtivas();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Limpa logs antigos
     */
    public function limparLogsAntigos($dias = 90) {
        if ($dias < 30) {
            throw new Exception('Não é permitido excluir logs com menos de 30 dias');
        }
        
        $qtdExcluida = $this->logModel->limparLogsAntigos($dias);
        
        return [
            'success' => true,
            'quantidade_excluida' => $qtdExcluida,
            'message' => "Foram excluídos $qtdExcluida logs com mais de $dias dias"
        ];
    }
    
    /**
     * Exporta logs para CSV
     */
    public function exportarCSV($filtros = []) {
        // Remove paginação para exportar tudo
        $filtros['limit'] = 999999;
        $filtros['page'] = 1;
        
        $resultado = $this->listar($filtros);
        $logs = $resultado['data'];
        
        if (empty($logs)) {
            throw new Exception('Nenhum log encontrado para exportar');
        }
        
        // Cabeçalhos do CSV
        $csv = [];
        $csv[] = [
            'ID',
            'Data/Hora',
            'Usuário',
            'Email',
            'Filial',
            'Ação',
            'Tabela',
            'ID Registro',
            'IP',
            'User Agent'
        ];
        
        // Dados
        foreach ($logs as $log) {
            $csv[] = [
                $log['id_log'],
                $log['data_log'],
                $log['usuario_nome'] ?? '-',
                $log['usuario_email'] ?? '-',
                $log['nome_filial'] ?? '-',
                $log['acao'] ?? '-',
                $log['tabela'] ?? '-',
                $log['id_registro'] ?? '-',
                $log['ip_usuario'] ?? '-',
                $log['user_agent'] ?? '-'
            ];
        }
        
        return $csv;
    }
    
    /**
     * Conta logs por ação
     */
    public function contarPorAcao() {
        return $this->logModel->countByAcao();
    }
    
    /**
     * Conta logs por usuário
     */
    public function contarPorUsuario($limite = 10) {
        return $this->logModel->countByUsuario($limite);
    }
}
?>





