<?php
require_once 'BaseModel.php';

class ItemInventario extends BaseModel {
    protected $table = 'tbl_itens_inventario';
    protected $primaryKey = 'id_item_inventario';
    
    /**
     * Busca itens de inventário com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT ii.*, 
                       cm.codigo as codigo_material,
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       u.nome_completo as nome_contador,
                       ef.estoque_atual,
                       ef.preco_unitario
                FROM {$this->table} ii
                LEFT JOIN tbl_catalogo_materiais cm ON ii.id_material = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_usuarios u ON ii.id_usuario_contador = u.id_usuario
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = (
                    SELECT i.id_filial FROM tbl_inventario i WHERE i.id_inventario = ii.id_inventario
                )";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY ii.id_item_inventario ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca item por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT ii.*, 
                       cm.codigo as codigo_material,
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       u.nome_completo as nome_contador,
                       ef.estoque_atual,
                       ef.preco_unitario
                FROM {$this->table} ii
                LEFT JOIN tbl_catalogo_materiais cm ON ii.id_material = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_usuarios u ON ii.id_usuario_contador = u.id_usuario
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = (
                    SELECT i.id_filial FROM tbl_inventario i WHERE i.id_inventario = ii.id_inventario
                )
                WHERE ii.id_item_inventario = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca itens por inventário
     */
    public function findByInventario($idInventario) {
        return $this->findAllWithRelations('ii.id_inventario = ?', [$idInventario]);
    }
    
    /**
     * Busca itens por status
     */
    public function findByStatus($idInventario, $status) {
        return $this->findAllWithRelations('ii.id_inventario = ? AND ii.status_item = ?', [$idInventario, $status]);
    }
    
    /**
     * Busca itens pendentes
     */
    public function findPendentes($idInventario) {
        return $this->findByStatus($idInventario, 'pendente');
    }
    
    /**
     * Busca itens contados
     */
    public function findContados($idInventario) {
        return $this->findByStatus($idInventario, 'contado');
    }
    
    /**
     * Busca itens divergentes
     */
    public function findDivergentes($idInventario) {
        return $this->findByStatus($idInventario, 'divergente');
    }
    
    /**
     * Cria novo item de inventário
     */
    public function criar($dados) {
        $sql = "INSERT INTO {$this->table} (
                    id_inventario, id_material, quantidade_sistema, 
                    valor_unitario, valor_total_sistema, observacoes
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $dados['id_inventario'],
            $dados['id_material'],
            $dados['quantidade_sistema'],
            $dados['valor_unitario'],
            $dados['valor_total_sistema'],
            $dados['observacoes'] ?? null
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Atualiza contagem do item
     */
    public function atualizarContagem($id, $dados) {
        error_log("🔍 ItemInventario::atualizarContagem - ID: $id, Dados: " . json_encode($dados));
        
        $quantidadeContada = $dados['quantidade_contada'];
        $quantidadeSistema = $dados['quantidade_sistema'];
        $valorUnitario = $dados['valor_unitario'];
        
        $quantidadeDivergencia = $quantidadeContada - $quantidadeSistema;
        $valorTotalContado = $quantidadeContada * $valorUnitario;
        
        $statusItem = $quantidadeDivergencia == 0 ? 'contado' : 'divergente';
        
        error_log("📊 ItemInventario::atualizarContagem - Status calculado: $statusItem");
        
        $sql = "UPDATE {$this->table} SET 
                    quantidade_contada = ?,
                    quantidade_divergencia = ?,
                    valor_total_contado = ?,
                    status_item = ?,
                    observacoes = ?,
                    data_contagem = CURRENT_TIMESTAMP,
                    id_usuario_contador = ?,
                    data_atualizacao = CURRENT_TIMESTAMP
                WHERE id_item_inventario = ?";
        
        $params = [
            $quantidadeContada,
            $quantidadeDivergencia,
            $valorTotalContado,
            $statusItem,
            $dados['observacoes'] ?? null,
            $dados['id_usuario_contador'],
            $id
        ];
        
        error_log("🔍 ItemInventario::atualizarContagem - SQL: $sql");
        error_log("🔍 ItemInventario::atualizarContagem - Parâmetros: " . json_encode($params));
        
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute($params);
        
        error_log("✅ ItemInventario::atualizarContagem - Resultado: " . ($resultado ? 'SUCESSO' : 'FALHA'));
        
        return $resultado;
    }
    
    /**
     * Ajusta item divergente e atualiza estoque automaticamente
     */
    public function ajustarItem($id, $dados) {
        error_log("🔍 ItemInventario::ajustarItem - Ajustando item ID: $id");
        
        // Buscar dados completos do item
        $item = $this->findByIdWithRelations($id);
        if (!$item) {
            throw new Exception('Item de inventário não encontrado');
        }
        
        // Verificar se já foi ajustado
        if ($item['status_item'] === 'ajustado') {
            error_log("⚠️ Item já foi ajustado anteriormente");
            return true; // Já ajustado, não precisa fazer nada
        }
        
        // Verificar se tem quantidade contada
        if ($item['quantidade_contada'] === null) {
            throw new Exception('Item não possui quantidade contada. Conte o item antes de ajustar.');
        }
        
        // Buscar dados do inventário para obter a filial
        $sqlInventario = "SELECT id_filial FROM tbl_inventario WHERE id_inventario = ?";
        $stmtInventario = $this->pdo->prepare($sqlInventario);
        $stmtInventario->execute([$item['id_inventario']]);
        $inventario = $stmtInventario->fetch(PDO::FETCH_ASSOC);
        
        if (!$inventario) {
            throw new Exception('Inventário não encontrado');
        }
        
        $idFilial = $inventario['id_filial'];
        $idCatalogo = $item['id_material'];
        $quantidadeSistema = (float)$item['quantidade_sistema'];
        $quantidadeContada = (float)$item['quantidade_contada'];
        $quantidadeDivergencia = $quantidadeContada - $quantidadeSistema;
        
        error_log("📊 Dados do ajuste:");
        error_log("   - Filial: $idFilial");
        error_log("   - Material: $idCatalogo");
        error_log("   - Sistema: $quantidadeSistema");
        error_log("   - Contado: $quantidadeContada");
        error_log("   - Divergência: $quantidadeDivergencia");
        
        // Iniciar transação
        $this->pdo->beginTransaction();
        
        try {
            // 1. Atualizar status do item
            $sql = "UPDATE {$this->table} SET 
                        status_item = 'ajustado',
                        observacoes = ?,
                        data_atualizacao = CURRENT_TIMESTAMP
                    WHERE id_item_inventario = ?";
            
            $params = [
                $dados['observacoes'] ?? null,
                $id
            ];
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // 2. Garantir que existe registro de estoque para este material nesta filial
            require_once __DIR__ . '/EstoqueFilial.php';
            $estoqueFilial = new EstoqueFilial($this->pdo);
            $estoqueExistente = $estoqueFilial->findByMaterialEFilial($idCatalogo, $idFilial);
            
            if (!$estoqueExistente) {
                // Criar registro de estoque se não existir
                $dadosEstoque = [
                    'id_catalogo' => $idCatalogo,
                    'id_filial' => $idFilial,
                    'estoque_atual' => $quantidadeSistema, // Começar com o estoque do sistema
                    'estoque_minimo' => null,
                    'estoque_maximo' => null,
                    'preco_unitario' => $item['valor_unitario'] ?? 0,
                    'ativo' => 1
                ];
                $estoqueFilial->criarOuAtualizarEstoque($idCatalogo, $idFilial, $dadosEstoque);
                error_log("✅ Registro de estoque criado inicialmente com quantidade do sistema: {$quantidadeSistema}");
            }
            
            // 3. Criar movimentação de estoque (ela vai atualizar o estoque automaticamente)
            require_once __DIR__ . '/Movimentacao.php';
            $movimentacao = new Movimentacao();
            
            $valorUnitario = (float)($item['valor_unitario'] ?? 0);
            
            // IMPORTANTE: A movimentação do tipo 'ajuste' vai calcular e atualizar o estoque automaticamente
            // quantidade_divergencia pode ser positiva (entrada) ou negativa (saída)
            // Exemplo: Sistema=0, Contado=50, Divergência=+50 → Estoque final = 0 + 50 = 50 ✓
            // Exemplo: Sistema=50, Contado=0, Divergência=-50 → Estoque final = 50 + (-50) = 0 ✓
            $dadosMovimentacao = [
                'id_catalogo' => $idCatalogo,
                'id_filial_destino' => $idFilial,
                'tipo_movimentacao' => 'ajuste', // Tipo seguro que está em todos os enums
                'subtipo_movimentacao' => 'inventario', // Subtipo para identificar origem
                'quantidade' => $quantidadeDivergencia, // Pode ser positivo ou negativo
                'valor_unitario' => $valorUnitario,
                'valor_total' => abs($quantidadeDivergencia) * $valorUnitario,
                'documento' => 'INV-' . $item['id_inventario'],
                'numero_documento' => 'ITEM-' . $id,
                'observacoes' => 'Ajuste de inventário - ' . ($dados['observacoes'] ?? 'Divergência identificada e ajustada') . 
                                ' (Sistema: ' . $quantidadeSistema . ', Contado: ' . $quantidadeContada . ')',
                'id_usuario_executor' => $dados['id_usuario'] ?? null
            ];
            
            // Criar movimentação (ela atualiza o estoque automaticamente)
            $movimentacao->criar($dadosMovimentacao);
            error_log("✅ Movimentação criada e estoque atualizado automaticamente");
            
            // 4. Verificar se o estoque foi atualizado corretamente
            $estoqueVerificado = $estoqueFilial->findByMaterialEFilial($idCatalogo, $idFilial);
            $estoqueFinal = (float)($estoqueVerificado['estoque_atual'] ?? 0);
            
            if ($estoqueFinal != $quantidadeContada) {
                // Se por algum motivo o estoque não foi atualizado corretamente, forçar atualização
                error_log("⚠️ Estoque não corresponde ao esperado. Esperado: {$quantidadeContada}, Atual: {$estoqueFinal}. Forçando atualização...");
                $dadosEstoque = [
                    'estoque_atual' => $quantidadeContada,
                    'data_atualizacao' => date('Y-m-d H:i:s')
                ];
                $estoqueFilial->update($estoqueVerificado['id_estoque'], $dadosEstoque);
                error_log("✅ Estoque corrigido manualmente: {$quantidadeContada}");
            } else {
                error_log("✅ Estoque verificado e correto: {$quantidadeContada}");
            }
            
            // Commit da transação
            $this->pdo->commit();
            error_log("✅ Item ajustado com sucesso - Estoque atualizado");
            
            return true;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $this->pdo->rollBack();
            error_log("❌ Erro ao ajustar item: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca com filtros e paginação
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10) {
        $where = [];
        $params = [];
        
        if (!empty($filters['id_inventario'])) {
            $where[] = 'ii.id_inventario = ?';
            $params[] = $filters['id_inventario'];
        }
        
        if (!empty($filters['status_item'])) {
            $where[] = 'ii.status_item = ?';
            $params[] = $filters['status_item'];
        }
        
        if (!empty($filters['id_material'])) {
            $where[] = 'ii.id_material = ?';
            $params[] = $filters['id_material'];
        }
        
        if (!empty($filters['termo_busca'])) {
            $where[] = '(cm.codigo LIKE ? OR cm.nome LIKE ?)';
            $params[] = '%' . $filters['termo_busca'] . '%';
            $params[] = '%' . $filters['termo_busca'] . '%';
        }
        
        if (!empty($filters['codigo_material'])) {
            $where[] = 'cm.codigo LIKE ?';
            $params[] = '%' . $filters['codigo_material'] . '%';
        }
        
        if (!empty($filters['nome_material'])) {
            $where[] = 'cm.nome LIKE ?';
            $params[] = '%' . $filters['nome_material'] . '%';
        }
        
        if (!empty($filters['id_categoria'])) {
            $where[] = 'cm.id_categoria = ?';
            $params[] = $filters['id_categoria'];
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca com paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query para contar total - sempre precisa do JOIN com materiais para filtros funcionarem
        $countSql = "SELECT COUNT(DISTINCT ii.id_item_inventario) as total 
                     FROM {$this->table} ii
                     LEFT JOIN tbl_catalogo_materiais cm ON ii.id_material = cm.id_catalogo";
        if (!empty($where)) {
            $countSql .= " WHERE " . $where;
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Query principal
        $sql = "SELECT ii.*, 
                       cm.codigo as codigo_material,
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       u.nome_completo as nome_contador,
                       ef.estoque_atual,
                       ef.preco_unitario
                FROM {$this->table} ii
                LEFT JOIN tbl_catalogo_materiais cm ON ii.id_material = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_usuarios u ON ii.id_usuario_contador = u.id_usuario
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = (
                    SELECT i.id_filial FROM tbl_inventario i WHERE i.id_inventario = ii.id_inventario
                )";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        
        $sql .= " ORDER BY ii.id_item_inventario ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $itens = $stmt->fetchAll();
        
        return [
            'data' => $itens,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Conta itens por status
     */
    public function countByStatus($idInventario, $status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE id_inventario = ? AND status_item = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idInventario, $status]);
        return $stmt->fetch()['total'];
    }
    
    /**
     * Conta itens pendentes
     */
    public function countPendentes($idInventario) {
        return $this->countByStatus($idInventario, 'pendente');
    }
    
    /**
     * Conta itens contados
     */
    public function countContados($idInventario) {
        return $this->countByStatus($idInventario, 'contado');
    }
    
    /**
     * Conta itens divergentes
     */
    public function countDivergentes($idInventario) {
        return $this->countByStatus($idInventario, 'divergente');
    }
    
    /**
     * Conta itens ajustados
     */
    public function countAjustados($idInventario) {
        return $this->countByStatus($idInventario, 'ajustado');
    }
    
    /**
     * Calcula totais do inventário
     */
    public function calcularTotais($idInventario) {
        $sql = "SELECT 
                    COUNT(*) as total_itens,
                    COUNT(CASE WHEN status_item = 'contado' THEN 1 END) as itens_contados,
                    COUNT(CASE WHEN status_item = 'divergente' THEN 1 END) as itens_divergentes,
                    COUNT(CASE WHEN status_item = 'ajustado' THEN 1 END) as itens_ajustados,
                    SUM(valor_total_sistema) as valor_total_sistema,
                    SUM(valor_total_contado) as valor_total_contado
                FROM {$this->table} 
                WHERE id_inventario = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idInventario]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se todos os itens do inventário foram contados
     */
    public function verificarSeTodosContados($idInventario) {
        try {
            error_log("🔍 ItemInventario::verificarSeTodosContados - Verificando inventário ID: $idInventario");
            
            // Contar total de itens
            $sqlTotal = "SELECT COUNT(*) as total FROM {$this->table} WHERE id_inventario = ?";
            $stmtTotal = $this->pdo->prepare($sqlTotal);
            $stmtTotal->execute([$idInventario]);
            $total = $stmtTotal->fetch()['total'];
            
            // Contar itens contados
            $sqlContados = "SELECT COUNT(*) as contados FROM {$this->table} 
                           WHERE id_inventario = ? AND status_item IN ('contado', 'ajustado')";
            $stmtContados = $this->pdo->prepare($sqlContados);
            $stmtContados->execute([$idInventario]);
            $contados = $stmtContados->fetch()['contados'];
            
            error_log("📊 ItemInventario::verificarSeTodosContados - Total: $total, Contados: $contados");
            
            // Retorna true se todos foram contados
            $resultado = $total > 0 && $total === $contados;
            error_log("✅ ItemInventario::verificarSeTodosContados - Resultado: " . ($resultado ? 'TODOS CONTADOS' : 'AINDA PENDENTES'));
            
            return $resultado;
        } catch (Exception $e) {
            error_log("❌ ItemInventario::verificarSeTodosContados - Erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Adiciona materiais ao inventário
     */
    public function adicionarMateriais($idInventario, $idFilial) {
        // Buscar todos os materiais com estoque na filial
        $sql = "SELECT cm.id_catalogo as id_material, ef.estoque_atual, ef.preco_unitario 
                FROM tbl_catalogo_materiais cm
                INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                WHERE ef.id_filial = ? AND cm.ativo = 1 AND ef.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial]);
        $materiais = $stmt->fetchAll();
        
        $adicionados = 0;
        foreach ($materiais as $material) {
            $valorTotal = $material['estoque_atual'] * $material['preco_unitario'];
            
            $sqlInsert = "INSERT INTO {$this->table} (
                            id_inventario, id_material, quantidade_sistema, 
                            valor_unitario, valor_total_sistema, status_item
                          ) VALUES (?, ?, ?, ?, ?, 'pendente')";
            
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $idInventario,
                $material['id_material'],
                $material['estoque_atual'],
                $material['preco_unitario'],
                $valorTotal
            ]);
            
            $adicionados++;
        }
        
        return $adicionados;
    }
    
    /**
     * Adiciona apenas materiais novos que não estão no inventário
     * Útil para atualizar inventário com produtos cadastrados após a criação
     */
    public function adicionarMateriaisNovos($idInventario, $idFilial) {
        // Buscar materiais que estão no catálogo mas NÃO estão no inventário
        $sql = "SELECT cm.id_catalogo as id_material, 
                       COALESCE(ef.estoque_atual, 0) as estoque_atual, 
                       COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) as preco_unitario 
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ?
                WHERE cm.ativo = 1 
                  AND (ef.ativo = 1 OR ef.ativo IS NULL)
                  AND cm.id_catalogo NOT IN (
                      SELECT id_material 
                      FROM {$this->table} 
                      WHERE id_inventario = ?
                  )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial, $idInventario]);
        $materiaisNovos = $stmt->fetchAll();
        
        $adicionados = 0;
        foreach ($materiaisNovos as $material) {
            $valorTotal = $material['estoque_atual'] * $material['preco_unitario'];
            
            $sqlInsert = "INSERT INTO {$this->table} (
                            id_inventario, id_material, quantidade_sistema, 
                            valor_unitario, valor_total_sistema, status_item
                          ) VALUES (?, ?, ?, ?, ?, 'pendente')";
            
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $idInventario,
                $material['id_material'],
                $material['estoque_atual'],
                $material['preco_unitario'],
                $valorTotal
            ]);
            
            $adicionados++;
        }
        
        return [
            'total_encontrados' => count($materiaisNovos),
            'adicionados' => $adicionados
        ];
    }
    
    /**
     * Conta quantos materiais novos existem que não estão no inventário
     */
    public function contarMateriaisNovos($idInventario, $idFilial) {
        $sql = "SELECT COUNT(*) as total
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ?
                WHERE cm.ativo = 1 
                  AND (ef.ativo = 1 OR ef.ativo IS NULL)
                  AND cm.id_catalogo NOT IN (
                      SELECT id_material 
                      FROM {$this->table} 
                      WHERE id_inventario = ?
                  )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial, $idInventario]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
} 