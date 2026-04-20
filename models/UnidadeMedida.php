<?php
require_once 'BaseModel.php';

class UnidadeMedida extends BaseModel {
    protected $table = 'tbl_unidades_medida';
    protected $primaryKey = 'id_unidade';
    
    /**
     * Busca unidades com contagem de materiais
     */
    public function findAllWithMaterialCount() {
        $sql = "SELECT u.*, 
                       COUNT(DISTINCT m.id_catalogo) as total_materiais
                FROM {$this->table} u
                LEFT JOIN tbl_catalogo_materiais m ON u.id_unidade = m.id_unidade AND m.ativo = 1
                WHERE u.ativo = 1
                GROUP BY u.id_unidade
                ORDER BY u.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca unidade por sigla
     */
    public function findBySigla($sigla) {
        $sql = "SELECT * FROM {$this->table} WHERE sigla = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sigla]);
        return $stmt->fetch();
    }
    
    /**
     * Busca unidade por nome
     */
    public function findByNome($nome) {
        $sql = "SELECT * FROM {$this->table} WHERE nome = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se a sigla já existe
     */
    public function siglaExiste($sigla, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE sigla = ? AND ativo = 1";
        $params = [$sigla];
        
        if ($excludeId) {
            $sql .= " AND id_unidade != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o nome já existe
     */
    public function nomeExiste($nome, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nome = ? AND ativo = 1";
        $params = [$nome];
        
        if ($excludeId) {
            $sql .= " AND id_unidade != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Busca unidades com filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['sigla'])) {
            $where[] = 'sigla LIKE ?';
            $params[] = '%' . $filters['sigla'] . '%';
        }
        
        if (!empty($filters['nome'])) {
            $where[] = 'nome LIKE ?';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Verifica se a unidade pode ser excluída
     */
    public function podeExcluir($id) {
        // Verifica se há materiais usando esta unidade
        $sql = "SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE id_unidade = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $materiais = $stmt->fetch()['total'];
        
        return $materiais == 0;
    }
    
    /**
     * Busca estatísticas da unidade
     */
    public function getEstatisticas($idUnidade, $idFilial = null) {
        $sql = "SELECT 
                    COUNT(DISTINCT cm.id_catalogo) as total_materiais,
                    SUM(ef.estoque_atual) as total_estoque,
                    SUM(ef.estoque_atual * COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0)) as valor_total_estoque,
                    COUNT(CASE WHEN ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AND ef.estoque_atual > 0 THEN 1 END) as materiais_estoque_baixo,
                    COUNT(CASE WHEN ef.estoque_atual = 0 THEN 1 END) as materiais_estoque_zerado
                FROM tbl_catalogo_materiais cm
                INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                WHERE cm.id_unidade = ? AND cm.ativo = 1 AND ef.ativo = 1";
        
        $params = [$idUnidade];
        if ($idFilial) {
            $sql .= " AND ef.id_filial = ?";
            $params[] = $idFilial;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Busca materiais de uma unidade
     */
    public function getMateriais($idUnidade, $idFilial = null) {
        $sql = "SELECT cm.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       fil.nome_filial,
                       ef.estoque_atual,
                       ef.estoque_minimo,
                       ef.estoque_maximo
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo";
        
        if ($idFilial) {
            $sql .= " AND ef.id_filial = ?";
        }
        
        $sql .= " LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
                WHERE cm.id_unidade = ? AND cm.ativo = 1";
        
        $params = [];
        if ($idFilial) {
            $params[] = $idFilial;
        }
        $params[] = $idUnidade;
        
        if ($idFilial) {
            $sql .= " AND ef.ativo = 1";
        }
        
        $sql .= " ORDER BY cm.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca unidades mais utilizadas
     */
    public function findTopUnidades($limit = 10) {
        $sql = "SELECT u.*, 
                       COUNT(DISTINCT m.id_catalogo) as total_materiais,
                       SUM(ef.estoque_atual) as total_estoque
                FROM {$this->table} u
                LEFT JOIN tbl_catalogo_materiais m ON u.id_unidade = m.id_unidade AND m.ativo = 1
                LEFT JOIN tbl_estoque_filiais ef ON m.id_catalogo = ef.id_catalogo AND ef.ativo = 1
                WHERE u.ativo = 1
                GROUP BY u.id_unidade
                HAVING total_materiais > 0
                ORDER BY total_materiais DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Insere unidades padrão do sistema
     */
    public function inserirUnidadesPadrao() {
        $unidades = [
            ['sigla' => 'UN', 'nome' => 'Unidade', 'descricao' => 'Unidade padrão'],
            ['sigla' => 'KG', 'nome' => 'Quilograma', 'descricao' => 'Unidade de peso'],
            ['sigla' => 'G', 'nome' => 'Grama', 'descricao' => 'Unidade de peso'],
            ['sigla' => 'L', 'nome' => 'Litro', 'descricao' => 'Unidade de volume'],
            ['sigla' => 'ML', 'nome' => 'Mililitro', 'descricao' => 'Unidade de volume'],
            ['sigla' => 'M', 'nome' => 'Metro', 'descricao' => 'Unidade de comprimento'],
            ['sigla' => 'CM', 'nome' => 'Centímetro', 'descricao' => 'Unidade de comprimento'],
            ['sigla' => 'MM', 'nome' => 'Milímetro', 'descricao' => 'Unidade de comprimento'],
            ['sigla' => 'M2', 'nome' => 'Metro Quadrado', 'descricao' => 'Unidade de área'],
            ['sigla' => 'M3', 'nome' => 'Metro Cúbico', 'descricao' => 'Unidade de volume'],
            ['sigla' => 'CX', 'nome' => 'Caixa', 'descricao' => 'Unidade de embalagem'],
            ['sigla' => 'PCT', 'nome' => 'Pacote', 'descricao' => 'Unidade de embalagem'],
            ['sigla' => 'FARDO', 'nome' => 'Fardo', 'descricao' => 'Unidade de embalagem'],
            ['sigla' => 'ROL', 'nome' => 'Rolo', 'descricao' => 'Unidade de embalagem'],
            ['sigla' => 'PAR', 'nome' => 'Par', 'descricao' => 'Unidade de medida'],
            ['sigla' => 'DZ', 'nome' => 'Dúzia', 'descricao' => 'Unidade de medida'],
            ['sigla' => 'CENTO', 'nome' => 'Cento', 'descricao' => 'Unidade de medida'],
            ['sigla' => 'MIL', 'nome' => 'Milheiro', 'descricao' => 'Unidade de medida']
        ];
        
        $inseridos = 0;
        foreach ($unidades as $unidade) {
            if (!$this->siglaExiste($unidade['sigla'])) {
                $this->insert($unidade);
                $inseridos++;
            }
        }
        
        return $inseridos;
    }
}
?> 