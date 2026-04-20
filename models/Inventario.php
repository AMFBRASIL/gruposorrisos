<?php
require_once 'BaseModel.php';

class Inventario extends BaseModel {
    protected $table = 'tbl_inventario';
    protected $primaryKey = 'id_inventario';
    
    /**
     * Busca inventários com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT i.*, 
                       f.nome_filial,
                       u.nome_completo as nome_responsavel,
                       COUNT(ii.id_item_inventario) as total_itens,
                       COUNT(CASE WHEN ii.status_item = 'contado' THEN 1 END) as itens_contados,
                       COUNT(CASE WHEN ii.status_item = 'divergente' THEN 1 END) as itens_divergentes,
                       SUM(ii.valor_total_sistema) as valor_total_sistema,
                       SUM(ii.valor_total_contado) as valor_total_contado
                FROM {$this->table} i
                LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial
                LEFT JOIN tbl_usuarios u ON i.id_usuario_responsavel = u.id_usuario
                LEFT JOIN tbl_itens_inventario ii ON i.id_inventario = ii.id_inventario";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " GROUP BY i.id_inventario ORDER BY i.data_inicio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca inventário por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT i.*, 
                       f.nome_filial,
                       u.nome_completo as nome_responsavel
                FROM {$this->table} i
                LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial
                LEFT JOIN tbl_usuarios u ON i.id_usuario_responsavel = u.id_usuario
                WHERE i.id_inventario = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca inventários por filial
     */
    public function findByFilial($idFilial) {
        return $this->findAllWithRelations('i.id_filial = ?', [$idFilial]);
    }
    
    /**
     * Busca inventários por status
     */
    public function findByStatus($status) {
        return $this->findAllWithRelations('i.status = ?', [$status]);
    }
    
    /**
     * Busca inventários em andamento
     */
    public function findEmAndamento($idFilial = null) {
        $where = 'i.status = "em_andamento"';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND i.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Busca inventários finalizados
     */
    public function findFinalizados($idFilial = null) {
        $where = 'i.status = "finalizado"';
        $params = [];
        
        if ($idFilial) {
            $where .= ' AND i.id_filial = ?';
            $params[] = $idFilial;
        }
        
        return $this->findAllWithRelations($where, $params);
    }
    
    /**
     * Gera número único para inventário
     */
    public function gerarNumeroInventario() {
        $ano = date('Y');
        $numero = 1;
        
        // Buscar o maior número de inventário do ano
        $sql = "SELECT numero_inventario FROM {$this->table} 
                WHERE numero_inventario LIKE ? 
                ORDER BY numero_inventario DESC 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["INV-{$ano}-%"]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Extrair o número do último inventário
            $ultimoNumero = $result['numero_inventario'];
            if (preg_match('/INV-\d{4}-(\d{3})/', $ultimoNumero, $matches)) {
                $numero = intval($matches[1]) + 1;
            }
        }
        
        // Verificar se o número já existe e incrementar até encontrar um único
        do {
            $numeroTeste = sprintf('INV-%s-%03d', $ano, $numero);
            
            $sqlCheck = "SELECT COUNT(*) as total FROM {$this->table} WHERE numero_inventario = ?";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([$numeroTeste]);
            $existe = $stmtCheck->fetch()['total'];
            
            if ($existe == 0) {
                return $numeroTeste;
            }
            
            $numero++;
        } while (true);
    }
    
    /**
     * Busca com filtros e paginação
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['id_filial'])) {
            $where[] = 'i.id_filial = ?';
            $params[] = $filters['id_filial'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'i.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['numero_inventario'])) {
            $where[] = 'i.numero_inventario LIKE ?';
            $params[] = '%' . $filters['numero_inventario'] . '%';
        }
        
        if (!empty($filters['data_inicio'])) {
            $where[] = 'DATE(i.data_inicio) >= ?';
            $params[] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $where[] = 'DATE(i.data_inicio) <= ?';
            $params[] = $filters['data_fim'];
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca com paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query para contar total
        $countSql = "SELECT COUNT(DISTINCT i.id_inventario) as total FROM {$this->table} i";
        if (!empty($where)) {
            $countSql .= " WHERE " . $where;
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Query principal
        $sql = "SELECT i.*, 
                       f.nome_filial,
                       u.nome_completo as nome_responsavel,
                       COUNT(ii.id_item_inventario) as total_itens,
                       COUNT(CASE WHEN ii.status_item = 'contado' THEN 1 END) as itens_contados,
                       COUNT(CASE WHEN ii.status_item = 'divergente' THEN 1 END) as itens_divergentes,
                       SUM(ii.valor_total_sistema) as valor_total_sistema,
                       SUM(ii.valor_total_contado) as valor_total_contado
                FROM {$this->table} i
                LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial
                LEFT JOIN tbl_usuarios u ON i.id_usuario_responsavel = u.id_usuario
                LEFT JOIN tbl_itens_inventario ii ON i.id_inventario = ii.id_inventario";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        
        $sql .= " GROUP BY i.id_inventario ORDER BY i.data_inicio DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $inventarios = $stmt->fetchAll();
        
        return [
            'inventarios' => $inventarios,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Cria novo inventário
     */
    public function criar($dados) {
        $numeroInventario = $this->gerarNumeroInventario();
        
        $sql = "INSERT INTO {$this->table} (
                    numero_inventario, id_filial, id_usuario_responsavel, 
                    observacoes, total_itens
                ) VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $numeroInventario,
            $dados['id_filial'],
            $dados['id_usuario_responsavel'],
            $dados['observacoes'] ?? null,
            $dados['total_itens'] ?? 0
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Atualiza inventário
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE {$this->table} SET 
                    observacoes = ?,
                    status = ?,
                    data_fim = ?,
                    data_atualizacao = CURRENT_TIMESTAMP
                WHERE id_inventario = ?";
        
        $params = [
            $dados['observacoes'] ?? null,
            $dados['status'] ?? 'em_andamento',
            $dados['status'] === 'finalizado' ? date('Y-m-d H:i:s') : null,
            $id
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Finaliza inventário e atualiza estoques automaticamente
     */
    public function finalizar($id, $idUsuario = null) {
        error_log("🔍 Inventario::finalizar - Finalizando inventário ID: $id");
        
        // Buscar dados do inventário
        $inventario = $this->findByIdWithRelations($id);
        if (!$inventario) {
            throw new Exception('Inventário não encontrado');
        }
        
        $idFilial = $inventario['id_filial'];
        error_log("📊 Filial do inventário: $idFilial");
        
        // Buscar todos os itens divergentes que ainda não foram ajustados
        require_once __DIR__ . '/ItemInventario.php';
        $itemInventario = new ItemInventario($this->pdo);
        
        $itensDivergentes = $itemInventario->findDivergentes($id);
        error_log("📦 Itens divergentes encontrados: " . count($itensDivergentes));
        
        // Iniciar transação
        $this->pdo->beginTransaction();
        
        try {
            // Processar cada item divergente automaticamente
            foreach ($itensDivergentes as $item) {
                if ($item['quantidade_contada'] !== null) {
                    error_log("🔄 Processando item divergente ID: {$item['id_item_inventario']}");
                    
                    // Ajustar item automaticamente (atualiza estoque e cria movimentação)
                    $itemInventario->ajustarItem($item['id_item_inventario'], [
                        'observacoes' => 'Ajuste automático ao finalizar inventário',
                        'id_usuario' => $idUsuario
                    ]);
                    
                    error_log("✅ Item {$item['id_item_inventario']} ajustado automaticamente");
                }
            }
            
            // Atualizar status do inventário
            $sql = "UPDATE {$this->table} SET 
                        status = 'finalizado',
                        data_fim = CURRENT_TIMESTAMP,
                        data_atualizacao = CURRENT_TIMESTAMP
                    WHERE id_inventario = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$id]);
            
            if (!$resultado) {
                throw new Exception('Erro ao atualizar status do inventário');
            }
            
            // Commit da transação
            $this->pdo->commit();
            error_log("✅ Inventário finalizado com sucesso - Todos os itens divergentes foram ajustados");
            
            return true;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $this->pdo->rollBack();
            error_log("❌ Erro ao finalizar inventário: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancela inventário
     */
    public function cancelar($id) {
        $sql = "UPDATE {$this->table} SET 
                    status = 'cancelado',
                    data_atualizacao = CURRENT_TIMESTAMP
                WHERE id_inventario = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Conta inventários por status
     */
    public function countByStatus($status, $idFilial = null) {
        $where = 'status = ?';
        $params = [$status];
        
        if ($idFilial) {
            $where .= ' AND id_filial = ?';
            $params[] = $idFilial;
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    /**
     * Conta inventários em andamento
     */
    public function countEmAndamento($idFilial = null) {
        return $this->countByStatus('em_andamento', $idFilial);
    }
    
    /**
     * Conta inventários finalizados
     */
    public function countFinalizados($idFilial = null) {
        return $this->countByStatus('finalizado', $idFilial);
    }
    
    /**
     * Conta inventários cancelados
     */
    public function countCancelados($idFilial = null) {
        return $this->countByStatus('cancelado', $idFilial);
    }
    
    /**
     * Conta total de inventários
     */
    public function countTotal($idFilial = null) {
        $where = '';
        $params = [];
        
        if ($idFilial) {
            $where = 'WHERE id_filial = ?';
            $params = [$idFilial];
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    /**
     * Exclui inventário e seus itens
     */
    public function delete($id) {
        try {
            // Primeiro excluir todos os itens do inventário
            $sqlDeleteItens = "DELETE FROM tbl_itens_inventario WHERE id_inventario = ?";
            $stmtDeleteItens = $this->pdo->prepare($sqlDeleteItens);
            $stmtDeleteItens->execute([$id]);
            
            // Depois excluir o inventário
            $sqlDeleteInventario = "DELETE FROM {$this->table} WHERE id_inventario = ?";
            $stmtDeleteInventario = $this->pdo->prepare($sqlDeleteInventario);
            $stmtDeleteInventario->execute([$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao excluir inventário: " . $e->getMessage());
            return false;
        }
    }
} 