<?php
require_once 'BaseModel.php';

class CatalogoMaterial extends BaseModel {
    protected $table = 'tbl_catalogo_materiais';
    protected $primaryKey = 'id_catalogo';
    
    /**
     * Busca materiais do catálogo com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT cm.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome
                FROM {$this->table} cm
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                WHERE cm.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY cm.nome ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca material do catálogo por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT cm.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome
                FROM {$this->table} cm
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                WHERE cm.id_catalogo = ? AND cm.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca material por código
     */
    public function findByCodigo($codigo) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }
    
    /**
     * Busca material por código de barras
     */
    public function findByCodigoBarras($codigoBarras) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_barras = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigoBarras]);
        return $stmt->fetch();
    }
    
    /**
     * Busca materiais com filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['codigo'])) {
            $where[] = 'cm.codigo LIKE ?';
            $params[] = '%' . $filters['codigo'] . '%';
        }
        
        if (!empty($filters['nome'])) {
            $where[] = 'cm.nome LIKE ?';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        if (!empty($filters['id_categoria'])) {
            $where[] = 'cm.id_categoria = ?';
            $params[] = $filters['id_categoria'];
        }
        
        if (!empty($filters['id_fornecedor'])) {
            $where[] = 'cm.id_fornecedor = ?';
            $params[] = $filters['id_fornecedor'];
        }
        
        if (!empty($filters['marca'])) {
            $where[] = 'cm.marca LIKE ?';
            $params[] = '%' . $filters['marca'] . '%';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca materiais com paginação e filtros
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOINs
        $sql = "SELECT cm.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome
                FROM {$this->table} cm
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                WHERE cm.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY cm.nome ASC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} cm WHERE cm.ativo = 1";
        if (!empty($where)) {
            $countSql .= " AND " . $where;
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
     * Verifica se o código já existe
     */
    public function codigoExiste($codigo, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE codigo = ? AND ativo = 1";
        $params = [$codigo];
        
        if ($excludeId) {
            $sql .= " AND id_catalogo != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o código de barras já existe
     */
    public function codigoBarrasExiste($codigoBarras, $excludeId = null) {
        if (empty($codigoBarras)) return false;
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE codigo_barras = ? AND ativo = 1";
        $params = [$codigoBarras];
        
        if ($excludeId) {
            $sql .= " AND id_catalogo != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca estatísticas do catálogo
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_materiais,
                    COUNT(CASE WHEN ativo = 1 THEN 1 END) as materiais_ativos,
                    COUNT(CASE WHEN id_categoria IS NOT NULL THEN 1 END) as com_categoria,
                    COUNT(CASE WHEN id_fornecedor IS NOT NULL THEN 1 END) as com_fornecedor,
                    COUNT(CASE WHEN codigo_barras IS NOT NULL THEN 1 END) as com_codigo_barras
                FROM {$this->table}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca materiais por categoria
     */
    public function findByCategoria($idCategoria) {
        return $this->findAllWithRelations('cm.id_categoria = ?', [$idCategoria]);
    }
    
    /**
     * Busca materiais por fornecedor
     */
    public function findByFornecedor($idFornecedor) {
        return $this->findAllWithRelations('cm.id_fornecedor = ?', [$idFornecedor]);
    }
    
    /**
     * Busca materiais por marca
     */
    public function findByMarca($marca) {
        return $this->findAllWithRelations('cm.marca LIKE ?', ['%' . $marca . '%']);
    }
    
    /**
     * Busca marcas disponíveis
     */
    public function getMarcas() {
        $sql = "SELECT DISTINCT marca FROM {$this->table} WHERE marca IS NOT NULL AND marca != '' AND ativo = 1 ORDER BY marca";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Busca modelos disponíveis
     */
    public function getModelos() {
        $sql = "SELECT DISTINCT modelo FROM {$this->table} WHERE modelo IS NOT NULL AND modelo != '' AND ativo = 1 ORDER BY modelo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Busca cores disponíveis
     */
    public function getCores() {
        $sql = "SELECT DISTINCT cor FROM {$this->table} WHERE cor IS NOT NULL AND cor != '' AND ativo = 1 ORDER BY cor";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Busca tamanhos disponíveis
     */
    public function getTamanhos() {
        $sql = "SELECT DISTINCT tamanho FROM {$this->table} WHERE tamanho IS NOT NULL AND tamanho != '' AND ativo = 1 ORDER BY tamanho";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?> 