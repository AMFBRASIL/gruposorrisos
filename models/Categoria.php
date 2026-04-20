<?php
require_once 'BaseModel.php';

class Categoria extends BaseModel {
    protected $table = 'tbl_categorias';
    protected $primaryKey = 'id_categoria';
    
    /**
     * Busca categorias ativas
     */
    public function findAtivas() {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY nome_categoria";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca categoria por nome
     */
    public function findByNome($nome) {
        $sql = "SELECT * FROM {$this->table} WHERE nome_categoria = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se nome já existe
     */
    public function nomeExiste($nome, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nome_categoria = ? AND ativo = 1";
        $params = [$nome];
        
        if ($excludeId) {
            $sql .= " AND id_categoria != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca com paginação e filtros
     */
    public function findWithPagination($page = 1, $limit = 10, $busca = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        // Construir WHERE
        $where = [];
        $params = [];
        
        if (!empty($busca)) {
            $where[] = "(c.nome_categoria LIKE ? OR c.descricao LIKE ?)";
            $params[] = "%{$busca}%";
            $params[] = "%{$busca}%";
        }
        
        if ($status !== '') {
            $where[] = "c.ativo = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Query principal
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT m.id_catalogo) as total_materiais
                FROM {$this->table} c
                LEFT JOIN tbl_catalogo_materiais m ON c.id_categoria = m.id_categoria AND m.ativo = 1
                {$whereClause}
                GROUP BY c.id_categoria
                ORDER BY c.nome_categoria
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Contar total
        $countSql = "SELECT COUNT(DISTINCT c.id_categoria) as total FROM {$this->table} c {$whereClause}";
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
            'start_page' => max(1, $page - 2),
            'end_page' => min($totalPages, $page + 2)
        ];
        
        return [
            'success' => true,
            'categorias' => $data,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Busca categoria por ID com estatísticas
     */
    public function findByIdWithStats($id) {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT m.id_catalogo) as total_materiais
                FROM {$this->table} c
                LEFT JOIN tbl_catalogo_materiais m ON c.id_categoria = m.id_categoria AND m.ativo = 1
                WHERE c.id_categoria = ?
                GROUP BY c.id_categoria";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Conta categorias ativas
     */
    public function countAtivas() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Conta total de categorias
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
?> 