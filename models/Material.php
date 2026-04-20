<?php
require_once 'BaseModel.php';

class Material extends BaseModel {
    protected $table = 'tbl_materiais';
    protected $primaryKey = 'id_material';
    
    /**
     * Busca materiais com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT m.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} m
                LEFT JOIN tbl_categorias c ON m.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON m.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON m.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON m.id_filial = fil.id_filial
                WHERE m.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY m.id_material DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca material por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT m.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} m
                LEFT JOIN tbl_categorias c ON m.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON m.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON m.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON m.id_filial = fil.id_filial
                WHERE m.id_material = ? AND m.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca materiais por filial
     */
    public function findByFilial($idFilial) {
        return $this->findAllWithRelations('m.id_filial = ?', [$idFilial]);
    }
    
    /**
     * Busca materiais com estoque baixo
     */
    public function findEstoqueBaixo($idFilial = null) {
        $where = 'm.estoque_atual <= m.estoque_minimo AND m.estoque_atual > 0';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND m.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Busca materiais com estoque zerado
     */
    public function findEstoqueZerado($idFilial = null) {
        $where = 'm.estoque_atual = 0';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND m.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Busca materiais por código
     */
    public function findByCodigo($codigo, $idFilial = null) {
        $where = 'm.codigo = ?';
        $params = [$codigo];
        
        if ($idFilial) {
            $where .= ' AND m.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Busca materiais por código de barras
     */
    public function findByCodigoBarras($codigoBarras, $idFilial = null) {
        $where = 'm.codigo_barras = ?';
        $params = [$codigoBarras];
        
        if ($idFilial) {
            $where .= ' AND m.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Atualiza o estoque de um material
     */
    public function atualizarEstoque($idMaterial, $quantidade, $tipo = 'entrada') {
        $sql = "UPDATE {$this->table} SET 
                estoque_atual = estoque_atual " . ($tipo == 'entrada' ? '+' : '-') . " ?,
                data_atualizacao = CURRENT_TIMESTAMP
                WHERE id_material = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantidade, $idMaterial]);
    }
    
    /**
     * Verifica se o código já existe
     */
    public function codigoExiste($codigo, $idFilial, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE codigo = ? AND id_filial = ? AND ativo = 1";
        $params = [$codigo, $idFilial];
        
        if ($excludeId) {
            $sql .= " AND id_material != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca materiais com paginação e filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['id_filial'])) {
            $where[] = 'm.id_filial = ?';
            $params[] = $filters['id_filial'];
        }
        
        if (!empty($filters['id_categoria'])) {
            $where[] = 'm.id_categoria = ?';
            $params[] = $filters['id_categoria'];
        }
        
        if (!empty($filters['id_fornecedor'])) {
            $where[] = 'm.id_fornecedor = ?';
            $params[] = $filters['id_fornecedor'];
        }
        
        if (!empty($filters['codigo'])) {
            $where[] = 'm.codigo LIKE ?';
            $params[] = '%' . $filters['codigo'] . '%';
        }
        
        if (!empty($filters['nome'])) {
            $where[] = 'm.nome LIKE ?';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        if (isset($filters['estoque_baixo']) && $filters['estoque_baixo']) {
            $where[] = 'm.estoque_atual <= m.estoque_minimo';
        }
        
        if (isset($filters['estoque_zerado']) && $filters['estoque_zerado']) {
            $where[] = 'm.estoque_atual = 0';
        }
        
        if (isset($filters['precisa_ressuprimento']) && $filters['precisa_ressuprimento']) {
            $where[] = 'm.estoque_atual <= m.estoque_minimo';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Conta materiais por filial
     */
    public function countByFilial($idFilial) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE id_filial = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta materiais em estoque
     */
    public function countEmEstoque($idFilial = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estoque_atual > estoque_minimo AND ativo = 1";
        $params = [];
        
        if ($idFilial) {
            $sql .= " AND id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta materiais com estoque baixo
     */
    public function countEstoqueBaixo($idFilial = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estoque_atual <= estoque_minimo AND estoque_atual > 0 AND ativo = 1";
        $params = [];
        
        if ($idFilial) {
            $sql .= " AND id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta materiais com estoque zerado
     */
    public function countEstoqueZerado($idFilial = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estoque_atual = 0 AND ativo = 1";
        $params = [];
        
        if ($idFilial) {
            $sql .= " AND id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta materiais que precisam de ressuprimento (estoque <= mínimo)
     */
    public function countPrecisaRessuprimento($idFilial = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estoque_atual <= estoque_minimo AND ativo = 1";
        $params = [];
        
        if ($idFilial) {
            $sql .= " AND id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta materiais para ressuprimento preventivo (estoque > mínimo mas < máximo)
     */
    public function countRessuprimentoPreventivo($idFilial = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE estoque_atual > estoque_minimo 
                AND estoque_atual < COALESCE(estoque_maximo, estoque_minimo * 3) 
                AND ativo = 1";
        $params = [];
        
        if ($idFilial) {
            $sql .= " AND id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Busca com paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal
        $sql = "SELECT m.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} m
                LEFT JOIN tbl_categorias c ON m.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON m.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON m.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON m.id_filial = fil.id_filial
                WHERE m.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        
        $sql .= " ORDER BY m.id_material DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} m WHERE m.ativo = 1";
        if (!empty($where)) {
            $countSql .= " AND " . $where;
        }
        
        $countParams = array_slice($params, 0, -2); // Remove limit e offset
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        // Calcular paginação
        $totalPages = ceil($total / $limit);
        $start = $offset + 1;
        $end = min($offset + $limit, $total);
        
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'start' => $start,
            'end' => $end,
            'limit' => $limit
        ];
        
        // Calcular páginas para exibição
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        $pagination['start_page'] = $startPage;
        $pagination['end_page'] = $endPage;
        
        return [
            'success' => true,
            'data' => $data,
            'pagination' => $pagination
        ];
    }

    /**
     * Conta todos os materiais (ativos)
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    /**
     * Conta materiais categorizados (id_categoria > 0)
     */
    public function countCategorizados() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1 AND id_categoria > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    /**
     * Conta materiais sem categoria (id_categoria IS NULL ou 0)
     */
    public function countSemCategoria() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1 AND (id_categoria IS NULL OR id_categoria = 0)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
?> 