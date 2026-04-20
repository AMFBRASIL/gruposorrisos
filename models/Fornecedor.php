<?php
require_once 'BaseModel.php';

class Fornecedor extends BaseModel {
    protected $table = 'tbl_fornecedores';
    protected $primaryKey = 'id_fornecedor';
    
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            parent::__construct();
        }
    }
    
    /**
     * Busca fornecedores com filtros e paginação
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['razao_social'])) {
            $where[] = "razao_social LIKE ?";
            $params[] = '%' . $filters['razao_social'] . '%';
        }
        
        if (!empty($filters['nome_fantasia'])) {
            $where[] = "nome_fantasia LIKE ?";
            $params[] = '%' . $filters['nome_fantasia'] . '%';
        }
        
        if (!empty($filters['cnpj'])) {
            $where[] = "cnpj LIKE ?";
            $params[] = '%' . $filters['cnpj'] . '%';
        }
        
        if (!empty($filters['cidade'])) {
            $where[] = "cidade LIKE ?";
            $params[] = '%' . $filters['cidade'] . '%';
        }
        
        if (!empty($filters['estado'])) {
            $where[] = "estado = ?";
            $params[] = $filters['estado'];
        }
        
        if (isset($filters['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        if (isset($filters['is_fabricante'])) {
            $where[] = "is_fabricante = ?";
            $params[] = $filters['is_fabricante'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Buscar dados
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY razao_social LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca fornecedores ativos
     */
    public function findAtivos() {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY razao_social";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca apenas fabricantes (fornecedores que são fabricantes)
     */
    public function findFabricantes() {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 AND is_fabricante = 1 ORDER BY razao_social";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca apenas fornecedores (que NÃO são fabricantes)
     */
    public function findApenasFornecedores() {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 AND (is_fabricante = 0 OR is_fabricante IS NULL) ORDER BY razao_social";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca fornecedor por CNPJ
     */
    public function findByCnpj($cnpj) {
        $sql = "SELECT * FROM {$this->table} WHERE cnpj = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cnpj]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se CNPJ já existe
     */
    public function cnpjExiste($cnpj, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE cnpj = ? AND ativo = 1";
        $params = [$cnpj];
        
        if ($excludeId) {
            $sql .= " AND id_fornecedor != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca fornecedores com contagem de materiais
     */
    public function findAllWithMaterialCount() {
        $sql = "SELECT f.*, 
                       COUNT(cm.id_catalogo) as total_materiais
                FROM {$this->table} f
                LEFT JOIN tbl_catalogo_materiais cm ON f.id_fornecedor = cm.id_fornecedor AND cm.ativo = 1
                WHERE f.ativo = 1
                GROUP BY f.id_fornecedor
                ORDER BY f.razao_social";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Conta total de fornecedores
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    /**
     * Conta fornecedores ativos
     */
    public function countAtivos() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    /**
     * Conta produtos fornecidos
     */
    public function countProdutosFornecidos() {
        $sql = "SELECT COUNT(DISTINCT cm.id_catalogo) as total 
                FROM tbl_catalogo_materiais cm 
                INNER JOIN {$this->table} f ON cm.id_fornecedor = f.id_fornecedor 
                WHERE cm.ativo = 1 AND f.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    /**
     * Conta pedidos do mês (simulado)
     */
    public function countPedidosMes() {
        $sql = "SELECT COUNT(*) as total 
                FROM tbl_pedidos_compra pc 
                INNER JOIN {$this->table} f ON pc.id_fornecedor = f.id_fornecedor 
                WHERE MONTH(pc.data_solicitacao) = MONTH(CURRENT_DATE()) 
                AND YEAR(pc.data_solicitacao) = YEAR(CURRENT_DATE())
                AND f.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    /**
     * Verifica se fornecedor tem materiais vinculados
     */
    public function hasMateriais($idFornecedor) {
        $sql = "SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE id_fornecedor = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFornecedor]);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Conta materiais por fornecedor
     */
    public function countMateriaisByFornecedor($idFornecedor) {
        $sql = "SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE id_fornecedor = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFornecedor]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Conta pedidos de compra por fornecedor
     */
    public function countPedidosByFornecedor($idFornecedor) {
        $sql = "SELECT COUNT(*) as total FROM tbl_pedidos_compra WHERE id_fornecedor = ? AND (ativo = 1 OR ativo IS NULL)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFornecedor]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Cria um novo fornecedor
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    razao_social, nome_fantasia, cnpj, inscricao_estadual, 
                    endereco, cidade, estado, cep, telefone, email, 
                    contato_principal, ativo, is_fabricante, data_criacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['razao_social'],
            $data['nome_fantasia'] ?? null,
            $data['cnpj'] ?? null,
            $data['inscricao_estadual'] ?? null,
            $data['endereco'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['cep'] ?? null,
            $data['telefone'] ?? null,
            $data['email'] ?? null,
            $data['contato_principal'] ?? null,
            $data['ativo'] ?? 1,
            $data['is_fabricante'] ?? 0,
            $data['data_criacao'] ?? date('Y-m-d H:i:s')
        ]);
        
        return $result ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Atualiza um fornecedor
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                    razao_social = ?, nome_fantasia = ?, cnpj = ?, inscricao_estadual = ?,
                    endereco = ?, cidade = ?, estado = ?, cep = ?, telefone = ?, email = ?,
                    contato_principal = ?, ativo = ?, is_fabricante = ?, data_atualizacao = ?
                WHERE id_fornecedor = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['razao_social'],
            $data['nome_fantasia'] ?? null,
            $data['cnpj'] ?? null,
            $data['inscricao_estadual'] ?? null,
            $data['endereco'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['cep'] ?? null,
            $data['telefone'] ?? null,
            $data['email'] ?? null,
            $data['contato_principal'] ?? null,
            $data['ativo'] ?? 1,
            $data['is_fabricante'] ?? 0,
            $data['data_atualizacao'] ?? date('Y-m-d H:i:s'),
            $id
        ]);
    }
    
    /**
     * Exclui um fornecedor (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET ativo = 0, data_atualizacao = ? WHERE id_fornecedor = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }
    
    /**
     * Busca fornecedor por ID
     */
    public function findById($id, $ativoCol = 'ativo') {
        return parent::findById($id, 'ativo');
    }
    
    /**
     * Busca fornecedores para select
     */
    public function findForSelect() {
        $sql = "SELECT id_fornecedor, razao_social, nome_fantasia FROM {$this->table} WHERE ativo = 1 ORDER BY razao_social";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar fornecedor por email
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?>