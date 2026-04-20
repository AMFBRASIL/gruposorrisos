<?php
require_once 'BaseModel.php';

class Filial extends BaseModel {
    protected $table = 'tbl_filiais';
    protected $primaryKey = 'id_filial';
    
    /**
     * Busca todos os registros (sem filtro de ativo)
     */
    public function findAll($where = '', $params = []) {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY {$this->primaryKey} DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca filiais ativas
     */
    public function findAtivas() {
        return $this->findAll('filial_ativa = 1');
    }
    
    /**
     * Busca filial por código
     */
    public function findByCodigo($codigo) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_filial = ? AND filial_ativa = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }
    
    /**
     * Busca filial por CNPJ
     */
    public function findByCnpj($cnpj) {
        $sql = "SELECT * FROM {$this->table} WHERE cnpj = ? AND filial_ativa = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cnpj]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se o código da filial já existe
     */
    public function codigoExiste($codigo, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE codigo_filial = ? AND filial_ativa = 1";
        $params = [$codigo];
        
        if ($excludeId) {
            $sql .= " AND id_filial != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o CNPJ já existe
     */
    public function cnpjExiste($cnpj, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE cnpj = ? AND filial_ativa = 1";
        $params = [$cnpj];
        
        if ($excludeId) {
            $sql .= " AND id_filial != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca filiais por tipo
     */
    public function findByTipo($tipo) {
        return $this->findAll('tipo_filial = ?', [$tipo]);
    }
    
    /**
     * Busca filiais por estado
     */
    public function findByEstado($estado) {
        return $this->findAll('estado = ?', [$estado]);
    }
    
    /**
     * Busca filiais com filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['tipo_filial'])) {
            $where[] = 'tipo_filial = ?';
            $params[] = $filters['tipo_filial'];
        }
        
        if (!empty($filters['estado'])) {
            $where[] = 'estado = ?';
            $params[] = $filters['estado'];
        }
        
        if (!empty($filters['cidade'])) {
            $where[] = 'cidade LIKE ?';
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (!empty($filters['nome_filial'])) {
            $where[] = 'nome_filial LIKE ?';
            $params[] = '%' . $filters['nome_filial'] . '%';
        }
        
        if (!empty($filters['codigo_filial'])) {
            $where[] = 'codigo_filial LIKE ?';
            $params[] = '%' . $filters['codigo_filial'] . '%';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Ativa/Desativa uma filial
     */
    public function toggleStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET filial_ativa = ? WHERE id_filial = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Busca estatísticas da filial
     */
    public function getEstatisticas($idFilial) {
        $sql = "SELECT 
                    (SELECT COUNT(DISTINCT cm.id_catalogo) 
                     FROM tbl_catalogo_materiais cm 
                     INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
                     WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1) as total_materiais,
                    (SELECT COUNT(DISTINCT cm.id_catalogo) 
                     FROM tbl_catalogo_materiais cm 
                     INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
                     WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1 
                     AND ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) 
                     AND ef.estoque_atual > 0) as materiais_estoque_baixo,
                    (SELECT COUNT(DISTINCT cm.id_catalogo) 
                     FROM tbl_catalogo_materiais cm 
                     INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo 
                     WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1 
                     AND ef.estoque_atual = 0) as materiais_estoque_zerado,
                    (SELECT COUNT(*) FROM tbl_movimentacoes WHERE id_filial = ? AND DATE(data_movimentacao) = CURDATE()) as movimentacoes_hoje,
                    (SELECT COUNT(*) FROM tbl_usuarios WHERE id_filial = ? AND ativo = 1) as total_usuarios";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial, $idFilial, $idFilial, $idFilial, $idFilial]);
        return $stmt->fetch();
    }
    
    /**
     * Busca resumo de estoque por filial
     */
    public function getResumoEstoque($idFilial) {
        $sql = "SELECT 
                    SUM(ef.estoque_atual) as total_estoque,
                    SUM(ef.estoque_atual * COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0)) as valor_total_estoque,
                    COUNT(DISTINCT cm.id_catalogo) as total_materiais,
                    COUNT(CASE WHEN ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AND ef.estoque_atual > 0 THEN 1 END) as materiais_estoque_baixo,
                    COUNT(CASE WHEN ef.estoque_atual = 0 THEN 1 END) as materiais_estoque_zerado
                FROM tbl_catalogo_materiais cm
                INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial]);
        return $stmt->fetch();
    }

    /**
     * Busca filial por ID (usando filial_ativa)
     */
    public function findById($id, $ativoCol = 'filial_ativa') {
        return parent::findById($id, 'filial_ativa');
    }
}
?> 