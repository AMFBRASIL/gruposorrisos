<?php
require_once 'BaseModel.php';

class LogSistema extends BaseModel {
    protected $table = 'tbl_logs_sistema';
    protected $primaryKey = 'id_log';
    
    /**
     * Busca logs com informações de usuário e filial
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT l.id_log,
                       l.id_usuario,
                       l.id_filial,
                       l.acao,
                       l.tabela,
                       l.id_registro,
                       l.dados_anteriores,
                       l.dados_novos,
                       l.ip_usuario,
                       l.user_agent,
                       l.data_log,
                       u.nome_completo as usuario_nome,
                       u.email as usuario_email,
                       f.nome_filial,
                       f.codigo_filial
                FROM {$this->table} l
                LEFT JOIN tbl_usuarios u ON l.id_usuario = u.id_usuario
                LEFT JOIN tbl_filiais f ON l.id_filial = f.id_filial";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY l.data_log DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca log por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT l.id_log,
                       l.id_usuario,
                       l.id_filial,
                       l.acao,
                       l.tabela,
                       l.id_registro,
                       l.dados_anteriores,
                       l.dados_novos,
                       l.ip_usuario,
                       l.user_agent,
                       l.data_log,
                       u.nome_completo as usuario_nome,
                       u.email as usuario_email,
                       f.nome_filial,
                       f.codigo_filial
                FROM {$this->table} l
                LEFT JOIN tbl_usuarios u ON l.id_usuario = u.id_usuario
                LEFT JOIN tbl_filiais f ON l.id_filial = f.id_filial
                WHERE l.id_log = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca logs com filtros e paginação
     */
    public function findWithPagination($page = 1, $limit = 50, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOINs
        $sql = "SELECT l.id_log,
                       l.id_usuario,
                       l.id_filial,
                       l.acao,
                       l.tabela,
                       l.id_registro,
                       l.dados_anteriores,
                       l.dados_novos,
                       l.ip_usuario,
                       l.user_agent,
                       l.data_log,
                       u.nome_completo as usuario_nome,
                       u.email as usuario_email,
                       f.nome_filial,
                       f.codigo_filial
                FROM {$this->table} l
                LEFT JOIN tbl_usuarios u ON l.id_usuario = u.id_usuario
                LEFT JOIN tbl_filiais f ON l.id_filial = f.id_filial";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY l.data_log DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} l";
        if (!empty($where)) {
            $countSql .= " WHERE " . $where;
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca logs por usuário
     */
    public function findByUsuario($idUsuario, $page = 1, $limit = 50) {
        return $this->findWithPagination($page, $limit, 'l.id_usuario = ?', [$idUsuario]);
    }
    
    /**
     * Busca logs por filial
     */
    public function findByFilial($idFilial, $page = 1, $limit = 50) {
        return $this->findWithPagination($page, $limit, 'l.id_filial = ?', [$idFilial]);
    }
    
    /**
     * Busca logs por ação
     */
    public function findByAcao($acao, $page = 1, $limit = 50) {
        return $this->findWithPagination($page, $limit, 'l.acao = ?', [$acao]);
    }
    
    /**
     * Busca logs por tabela
     */
    public function findByTabela($tabela, $page = 1, $limit = 50) {
        return $this->findWithPagination($page, $limit, 'l.tabela = ?', [$tabela]);
    }
    
    /**
     * Busca logs por período
     */
    public function findByPeriodo($dataInicio, $dataFim, $page = 1, $limit = 50) {
        return $this->findWithPagination(
            $page, 
            $limit, 
            'l.data_log BETWEEN ? AND ?', 
            [$dataInicio, $dataFim]
        );
    }
    
    /**
     * Busca estatísticas de logs
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_logs,
                    COUNT(DISTINCT id_usuario) as total_usuarios,
                    COUNT(CASE WHEN data_log >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as logs_24h,
                    COUNT(CASE WHEN data_log >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as logs_7dias,
                    COUNT(CASE WHEN data_log >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as logs_30dias
                FROM {$this->table}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca ações distintas
     */
    public function getAcoesDistintas() {
        $sql = "SELECT DISTINCT acao FROM {$this->table} WHERE acao IS NOT NULL ORDER BY acao";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Busca tabelas distintas
     */
    public function getTabelasDistintas() {
        $sql = "SELECT DISTINCT tabela FROM {$this->table} WHERE tabela IS NOT NULL ORDER BY tabela";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Registra novo log
     */
    public function registrarLog($dados) {
        $campos = [
            'id_usuario' => $dados['id_usuario'] ?? null,
            'id_filial' => $dados['id_filial'] ?? null,
            'acao' => $dados['acao'] ?? null,
            'tabela' => $dados['tabela'] ?? null,
            'id_registro' => $dados['id_registro'] ?? null,
            'dados_anteriores' => isset($dados['dados_anteriores']) ? json_encode($dados['dados_anteriores']) : null,
            'dados_novos' => isset($dados['dados_novos']) ? json_encode($dados['dados_novos']) : null,
            'ip_usuario' => $dados['ip_usuario'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $dados['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->insert($campos);
    }
    
    /**
     * Limpa logs antigos (manutenção)
     */
    public function limparLogsAntigos($dias = 90) {
        $sql = "DELETE FROM {$this->table} WHERE data_log < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->rowCount();
    }
    
    /**
     * Busca logs por IP
     */
    public function findByIp($ip, $page = 1, $limit = 50) {
        return $this->findWithPagination($page, $limit, 'l.ip_usuario = ?', [$ip]);
    }
    
    /**
     * Conta logs por ação
     */
    public function countByAcao() {
        $sql = "SELECT acao, COUNT(*) as total
                FROM {$this->table}
                WHERE acao IS NOT NULL
                GROUP BY acao
                ORDER BY total DESC
                LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Conta logs por usuário
     */
    public function countByUsuario($limite = 10) {
        $sql = "SELECT u.nome_completo, COUNT(l.id_log) as total
                FROM {$this->table} l
                LEFT JOIN tbl_usuarios u ON l.id_usuario = u.id_usuario
                WHERE l.id_usuario IS NOT NULL
                GROUP BY l.id_usuario, u.nome_completo
                ORDER BY total DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}
?>





