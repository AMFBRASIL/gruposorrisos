<?php
require_once 'BaseModel.php';

class EstoqueFilial extends BaseModel {
    protected $table = 'tbl_estoque_filiais';
    protected $primaryKey = 'id_estoque';
    
    /**
     * Busca estoque com informações do material e filial
     */
    public function findWithRelations($where = '', $params = []) {
        // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo/maximo da filial sobre valores padrão do catálogo
        $sql = "SELECT ef.id_estoque,
                       ef.id_catalogo,
                       ef.id_filial,
                       ef.estoque_atual,
                       COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo,
                       COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) as estoque_maximo,
                       COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) as preco_unitario,
                       ef.data_vencimento,
                       ef.localizacao_estoque,
                       ef.observacoes_estoque,
                       ef.ativo,
                       ef.data_criacao,
                       ef.data_atualizacao,
                       cm.codigo,
                       cm.nome,
                       cm.descricao,
                       cm.id_categoria,
                       cm.id_fornecedor,
                       cm.id_unidade,
                       cm.codigo_barras,
                       cm.ca,
                       cm.marca,
                       cm.modelo,
                       cm.cor,
                       cm.tamanho,
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} ef
                INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
                WHERE ef.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY cm.nome ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca estoque por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo/maximo da filial sobre valores padrão do catálogo
        $sql = "SELECT ef.id_estoque,
                       ef.id_catalogo,
                       ef.id_filial,
                       ef.estoque_atual,
                       COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo,
                       COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) as estoque_maximo,
                       COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) as preco_unitario,
                       ef.data_vencimento,
                       ef.localizacao_estoque,
                       ef.observacoes_estoque,
                       ef.ativo,
                       ef.data_criacao,
                       ef.data_atualizacao,
                       cm.codigo,
                       cm.nome,
                       cm.descricao,
                       cm.id_categoria,
                       cm.id_fornecedor,
                       cm.id_fabricante,
                       cm.id_unidade,
                       cm.codigo_barras,
                       cm.ca,
                       cm.marca,
                       cm.modelo,
                       cm.cor,
                       cm.tamanho,
                       cm.observacoes,
                       cm.ativo,
                       cm.data_criacao,
                       cm.data_atualizacao,
                       cm.estoque_minimo_padrao,
                       cm.estoque_maximo_padrao,
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       fab.razao_social as fabricante_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} ef
                INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_fornecedores fab ON cm.id_fabricante = fab.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
                WHERE ef.id_estoque = ? AND ef.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca estoque por material e filial
     */
    public function findByMaterialEFilial($idCatalogo, $idFilial) {
        // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo/maximo da filial sobre valores padrão do catálogo
        $sql = "SELECT ef.id_estoque,
                       ef.id_catalogo,
                       ef.id_filial,
                       ef.estoque_atual,
                       ef.estoque_minimo,  -- Valor da filial (pode ser NULL)
                       ef.estoque_maximo,  -- Valor da filial (pode ser NULL)
                       COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo_calculado,
                       COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) as estoque_maximo_calculado,
                       COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) as preco_unitario,
                       ef.data_vencimento,
                       ef.localizacao_estoque,
                       ef.observacoes_estoque,
                       ef.ativo,
                       ef.data_criacao,
                       ef.data_atualizacao,
                       cm.codigo,
                       cm.nome,
                       cm.descricao,
                       cm.id_categoria,
                       cm.id_fornecedor,
                       cm.id_unidade,
                       cm.estoque_minimo_padrao,
                       cm.estoque_maximo_padrao,
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} ef
                INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
                WHERE ef.id_catalogo = ? AND ef.id_filial = ? AND ef.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCatalogo, $idFilial]);
        return $stmt->fetch();
    }
    
    /**
     * Busca estoques por filial
     */
    public function findByFilial($idFilial) {
        return $this->findWithRelations('ef.id_filial = ?', [$idFilial]);
    }
    
    /**
     * Busca estoques por material (todas as filiais)
     */
    public function findByMaterial($idCatalogo) {
        return $this->findWithRelations('ef.id_catalogo = ?', [$idCatalogo]);
    }
    
    /**
     * Busca estoques com filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['id_filial'])) {
            $where[] = 'ef.id_filial = ?';
            $params[] = $filters['id_filial'];
        }
        
        if (!empty($filters['id_categoria'])) {
            $where[] = 'cm.id_categoria = ?';
            $params[] = $filters['id_categoria'];
        }
        
        if (!empty($filters['id_fornecedor'])) {
            $where[] = 'cm.id_fornecedor = ?';
            $params[] = $filters['id_fornecedor'];
        }
        
        // Busca genérica em múltiplos campos (codigo, nome, descricao)
        if (!empty($filters['busca'])) {
            $where[] = '(cm.codigo LIKE ? OR cm.nome LIKE ? OR cm.descricao LIKE ?)';
            $searchTerm = '%' . $filters['busca'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Busca específica por código (mantido para compatibilidade)
        if (!empty($filters['codigo']) && empty($filters['busca'])) {
            $where[] = 'cm.codigo LIKE ?';
            $params[] = '%' . $filters['codigo'] . '%';
        }
        
        // Busca específica por nome (mantido para compatibilidade)
        if (!empty($filters['nome']) && empty($filters['busca'])) {
            $where[] = 'cm.nome LIKE ?';
            $params[] = '%' . $filters['nome'] . '%';
        }
        
        if (isset($filters['em_estoque']) && $filters['em_estoque']) {
            // "Em estoque" na tela: estoque maior que o mínimo configurado (status verde)
            $where[] = 'ef.estoque_atual > COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0)';
        }
        
        if (isset($filters['estoque_baixo']) && $filters['estoque_baixo']) {
            $where[] = 'ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0)';
        }
        
        if (isset($filters['estoque_zerado']) && $filters['estoque_zerado']) {
            $where[] = 'ef.estoque_atual = 0';
        }
        
        if (isset($filters['precisa_ressuprimento']) && $filters['precisa_ressuprimento']) {
            $where[] = 'ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0)';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca estoques com paginação e filtros
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOINs
        // IMPORTANTE: Usar COALESCE para priorizar estoque_minimo/maximo da filial sobre valores padrão do catálogo
        $sql = "SELECT ef.id_estoque,
                       ef.id_catalogo,
                       ef.id_filial,
                       ef.estoque_atual,
                       COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo,
                       COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) as estoque_maximo,
                       COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) as preco_unitario,
                       ef.data_vencimento,
                       ef.localizacao_estoque,
                       ef.observacoes_estoque,
                       ef.ativo,
                       ef.data_criacao,
                       ef.data_atualizacao,
                       cm.codigo,
                       cm.nome,
                       cm.descricao,
                       cm.id_categoria,
                       cm.id_fornecedor,
                       cm.id_unidade,
                       cm.marca,
                       cm.modelo,
                       cm.cor,
                       cm.tamanho,
                       cm.estoque_minimo_padrao,
                       cm.estoque_maximo_padrao,
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome,
                       fil.nome_filial
                FROM {$this->table} ef
                INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                LEFT JOIN tbl_filiais fil ON ef.id_filial = fil.id_filial
                WHERE ef.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY cm.nome ASC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} ef
                     INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                     WHERE ef.ativo = 1";
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
     * Atualiza o estoque de um material
     */
    public function atualizarEstoque($idEstoque, $quantidade, $tipo = 'entrada') {
        $sql = "UPDATE {$this->table} SET 
                estoque_atual = estoque_atual " . ($tipo == 'entrada' ? '+' : '-') . " ?,
                data_atualizacao = CURRENT_TIMESTAMP
                WHERE id_estoque = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantidade, $idEstoque]);
    }
    
    /**
     * Busca estoques com estoque baixo
     */
    public function findEstoqueBaixo($idFilial = null) {
        // Usar COALESCE para priorizar estoque_minimo da filial, senão usar padrão do catálogo
        $where = 'ef.estoque_atual <= COALESCE(ef.estoque_minimo, (SELECT cm2.estoque_minimo_padrao FROM tbl_catalogo_materiais cm2 WHERE cm2.id_catalogo = ef.id_catalogo), 0) AND ef.estoque_atual > 0';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND ef.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findWithRelations($where, $params);
    }
    
    /**
     * Busca estoques zerados
     */
    public function findEstoqueZerado($idFilial = null) {
        $where = 'ef.estoque_atual = 0';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND ef.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findWithRelations($where, $params);
    }
    
    /**
     * Busca estatísticas de estoque por filial
     */
    public function getEstatisticasPorFilial($idFilial = null) {
        $where = 'ef.ativo = 1';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND ef.id_filial = ?';
            $params[] = $idFilial;
        }
        
        // IMPORTANTE: Fazer JOIN com tbl_catalogo_materiais para acessar estoque_minimo_padrao
        $sql = "SELECT 
                    COUNT(*) as total_materiais,
                    SUM(ef.estoque_atual) as estoque_total,
                    SUM(CASE WHEN ef.estoque_atual > 0 THEN 1 ELSE 0 END) as em_estoque,
                    SUM(CASE WHEN ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AND ef.estoque_atual > 0 THEN 1 ELSE 0 END) as estoque_baixo,
                    SUM(CASE WHEN ef.estoque_atual = 0 THEN 1 ELSE 0 END) as sem_estoque,
                    SUM(CASE WHEN ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) THEN 1 ELSE 0 END) as precisa_ressuprimento
                FROM {$this->table} ef
                INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo
                WHERE $where";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Cria ou atualiza estoque para uma filial
     */
    public function criarOuAtualizarEstoque($idCatalogo, $idFilial, $dados) {
        // Verificar se já existe
        $existente = $this->findByMaterialEFilial($idCatalogo, $idFilial);
        
        if ($existente) {
            // Atualizar existente
            return $this->update($existente['id_estoque'], $dados);
        } else {
            // Criar novo
            $dados['id_catalogo'] = $idCatalogo;
            $dados['id_filial'] = $idFilial;
            return $this->insert($dados);
        }
    }
    
    /**
     * Busca materiais sem estoque em uma filial específica
     */
    public function findMateriaisSemEstoque($idFilial) {
        $sql = "SELECT cm.*, 
                       c.nome_categoria,
                       f.razao_social as fornecedor_nome,
                       u.sigla as unidade_sigla,
                       u.nome as unidade_nome
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
                LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_unidades_medida u ON cm.id_unidade = u.id_unidade
                WHERE cm.ativo = 1 
                AND cm.id_catalogo NOT IN (
                    SELECT DISTINCT ef.id_catalogo 
                    FROM {$this->table} ef 
                    WHERE ef.id_filial = ? AND ef.ativo = 1
                )
                ORDER BY cm.nome ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial]);
        return $stmt->fetchAll();
    }
}
?> 