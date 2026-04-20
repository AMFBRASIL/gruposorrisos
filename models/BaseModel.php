<?php
require_once __DIR__ . '/../config/conexao.php';

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->pdo = Conexao::getInstance()->getPdo();
    }
    
    /**
     * Busca todos os registros ativos
     */
    public function findAll($where = '', $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY {$this->primaryKey} DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca um registro por ID
     */
    public function findById($id, $ativoCol = 'ativo') {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND $ativoCol = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Insere um novo registro
     */
    public function insert($data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $fieldList = implode(', ', $fields);
        
        $sql = "INSERT INTO {$this->table} ({$fieldList}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Atualiza um registro
     */
    public function update($id, $data) {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * Exclui logicamente um registro (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca com paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY {$this->primaryKey} DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
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
     * Executa uma query customizada
     */
    public function executeQuery($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa uma query customizada retornando apenas um registro
     */
    public function executeQuerySingle($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
?> 