<?php
require_once 'BaseModel.php';

class PedidoCompra extends BaseModel {
    protected $table = 'tbl_pedidos_compra';
    protected $primaryKey = 'id_pedido';
    
    /**
     * Valida e limpa uma data antes de salvar
     */
    private function validarData($data) {
        if (empty($data) || $data === '0000-00-00' || $data === '0000-00-00 00:00:00') {
            return null;
        }
        
        // Verificar se a data é válida
        $timestamp = strtotime($data);
        if ($timestamp === false || $timestamp < 0) {
            return null;
        }
        
        // Verificar se o ano é válido (maior que 1900)
        $ano = date('Y', $timestamp);
        if ($ano < 1900) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Busca pedidos com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT pc.*, 
                       f.razao_social as nome_fornecedor,
                       fil.nome_filial,
                       u.nome_completo as nome_usuario
                FROM {$this->table} pc
                LEFT JOIN tbl_fornecedores f ON pc.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_filiais fil ON pc.id_filial = fil.id_filial
                LEFT JOIN tbl_usuarios u ON pc.id_usuario_solicitante = u.id_usuario
                WHERE 1=1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY pc.id_pedido DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca pedido por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT pc.*, 
                       f.razao_social as nome_fornecedor,
                       fil.nome_filial,
                       u.nome_completo as nome_usuario
                FROM {$this->table} pc
                LEFT JOIN tbl_fornecedores f ON pc.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_filiais fil ON pc.id_filial = fil.id_filial
                LEFT JOIN tbl_usuarios u ON pc.id_usuario_solicitante = u.id_usuario
                WHERE pc.id_pedido = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca pedidos com filtros e paginação
     */
    public function findWithFilters($filtros = [], $page = 1, $limit = 10) {
        $where = "1=1";
        $params = [];
        
        if (!empty($filtros['busca'])) {
            $where .= " AND (pc.numero_pedido LIKE ? OR f.razao_social LIKE ? OR pc.observacoes LIKE ?)";
            $busca = "%{$filtros['busca']}%";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
        }
        
        if (!empty($filtros['status'])) {
            $where .= " AND pc.status = ?";
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['fornecedor'])) {
            $where .= " AND pc.id_fornecedor = ?";
            $params[] = $filtros['fornecedor'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= " AND pc.data_solicitacao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= " AND pc.data_solicitacao <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        return $this->findWithPagination($page, $limit, $where, $params);
    }
    
    /**
     * Busca com paginação personalizada para pedidos (sem coluna ativo)
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOINs
        $sql = "SELECT pc.*, 
                       f.razao_social as nome_fornecedor,
                       fil.nome_filial,
                       u.nome_completo as nome_usuario
                FROM {$this->table} pc
                LEFT JOIN tbl_fornecedores f ON pc.id_fornecedor = f.id_fornecedor
                LEFT JOIN tbl_filiais fil ON pc.id_filial = fil.id_filial
                LEFT JOIN tbl_usuarios u ON pc.id_usuario_solicitante = u.id_usuario";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY pc.id_pedido DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} pc
                     LEFT JOIN tbl_fornecedores f ON pc.id_fornecedor = f.id_fornecedor";
        if (!empty($where)) {
            $countSql .= " WHERE " . $where;
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        return [
            'pedidos' => $data,
            'total' => $total,
            'paginas' => ceil($total / $limit),
            'pagina_atual' => $page
        ];
    }
    
    /**
     * Gerar número único do pedido
     */
    public function gerarNumeroPedido() {
        $ano = date('Y');
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE YEAR(data_criacao) = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ano]);
        $result = $stmt->fetch();
        
        $numero = $result['total'] + 1;
        return "PED-{$ano}-" . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Criar novo pedido com itens
     */
    public function criar($dados) {
        try {
            $this->pdo->beginTransaction();
            
            $numeroPedido = $this->gerarNumeroPedido();
            
            $dadosPedido = [
                'numero_pedido' => $numeroPedido,
                'id_fornecedor' => $dados['id_fornecedor'],
                'id_filial' => $dados['id_filial'],
                'data_pedido' => date('Y-m-d'),
                'data_entrega_prevista' => $this->validarData($dados['data_entrega_prevista']),
                'status' => $dados['status'] ?? 'em_analise',
                'valor_total' => $dados['valor_total'],
                'observacoes' => $dados['observacoes'],
                'id_usuario_solicitante' => $dados['id_usuario_solicitante']
            ];
            
            $idPedido = $this->insert($dadosPedido);
            
            // Inserir itens do pedido
            if (!empty($dados['itens'])) {
                foreach ($dados['itens'] as $item) {
                    $this->inserirItem($idPedido, $item);
                }
            }
            
            $this->pdo->commit();
            return ['success' => true, 'id_pedido' => $idPedido, 'numero_pedido' => $numeroPedido];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Inserir item do pedido
     */
    private function inserirItem($idPedido, $item) {
        $sql = "INSERT INTO tbl_itens_pedido_compra (id_pedido, id_catalogo, quantidade, preco_unitario, valor_total, observacoes) 
               VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $idPedido,
            $item['id_material'], // Mantém o nome do campo no array mas insere em id_catalogo
            $item['quantidade'],
            $item['preco_unitario'],
            $item['valor_total'],
            $item['observacoes'] ?? null
        ]);
    }
    
    /**
     * Buscar itens do pedido
     */
    public function buscarItens($idPedido) {
        $sql = "SELECT ipc.*,
                       ipc.id_catalogo as id_material,
                       cm.codigo as codigo_material, 
                       cm.nome as nome_material,
                       um.sigla as unidade_medida,
                       ipc.quantidade_disponivel,
                       ipc.disponivel,
                       ipc.preco_fornecedor
                FROM tbl_itens_pedido_compra ipc
                LEFT JOIN tbl_catalogo_materiais cm ON ipc.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE ipc.id_pedido = ? AND (ipc.ativo = 1 OR ipc.ativo IS NULL)
                ORDER BY ipc.id_item";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPedido]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log para debug (remover em produção se necessário)
        error_log("Itens retornados para pedido {$idPedido}: " . json_encode($itens));
        
        return $itens;
    }
    
    /**
     * Atualizar pedido
     */
    public function atualizar($id, $dados) {
        try {
            $this->pdo->beginTransaction();
            
            // Atualizar dados principais do pedido
            $sql = "UPDATE {$this->table} SET 
                    id_fornecedor = ?, 
                    id_filial = ?, 
                    data_entrega_prevista = ?, 
                    prioridade = ?, 
                    prazo_entrega = ?, 
                    status = ?, 
                    valor_total = ?, 
                    observacoes = ?,
                    data_atualizacao = NOW()
                    WHERE id_pedido = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['id_fornecedor'],
                $dados['id_filial'],
                $this->validarData($dados['data_entrega_prevista']),
                $dados['prioridade'] ?? 'padrao',
                $dados['prazo_entrega'] ?? 8,
                $dados['status'],
                $dados['valor_total'],
                $dados['observacoes'],
                $id
            ]);
            
            // Se houver itens, atualizar/inserir
            if (!empty($dados['itens'])) {
                // Remover itens existentes
                $sqlDelete = "DELETE FROM tbl_itens_pedido_compra WHERE id_pedido = ?";
                $stmtDelete = $this->pdo->prepare($sqlDelete);
                $stmtDelete->execute([$id]);
                
                // Inserir novos itens
                foreach ($dados['itens'] as $item) {
                    $sqlItem = "INSERT INTO tbl_itens_pedido_compra 
                               (id_pedido, id_catalogo, quantidade, preco_unitario, valor_total) 
                               VALUES (?, ?, ?, ?, ?)";
                    $stmtItem = $this->pdo->prepare($sqlItem);
                    $stmtItem->execute([
                        $id,
                        $item['id_material'],
                        $item['quantidade'],
                        $item['preco_unitario'],
                        $item['valor_total']
                    ]);
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    

    
    /**
     * Atualizar preços dos itens de um pedido
     */
    public function atualizarPrecosItens($idPedido, $precos) {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($precos as $preco) {
                $idItem = $preco['id_item'];
                $precoUnitario = $preco['preco_unitario'];
                $quantidade = 0;
                
                // Buscar quantidade do item
                $sqlQuantidade = "SELECT quantidade FROM {$this->tableItens} WHERE id_item = ?";
                $stmtQuantidade = $this->pdo->prepare($sqlQuantidade);
                $stmtQuantidade->execute([$idItem]);
                $item = $stmtQuantidade->fetch(PDO::FETCH_ASSOC);
                
                if ($item) {
                    $quantidade = $item['quantidade'];
                    $valorTotal = $quantidade * $precoUnitario;
                    
                    // Atualizar preço unitário e valor total
                    $sqlUpdate = "UPDATE {$this->tableItens} SET 
                                 preco_unitario = ?, 
                                 valor_total = ?,
                                 data_atualizacao = NOW()
                                 WHERE id_item = ?";
                    
                    $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([$precoUnitario, $valorTotal, $idItem]);
                }
            }
            
            // Recalcular valor total do pedido
            $sqlTotal = "SELECT SUM(valor_total) as total FROM {$this->tableItens} WHERE id_pedido = ?";
            $stmtTotal = $this->pdo->prepare($sqlTotal);
            $stmtTotal->execute([$idPedido]);
            $resultado = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            
            $novoTotal = $resultado['total'] ?? 0;
            
            // Atualizar valor total do pedido
            $sqlPedido = "UPDATE {$this->table} SET 
                         valor_total = ?,
                         data_atualizacao = NOW()
                         WHERE id_pedido = ?";
            
            $stmtPedido = $this->pdo->prepare($sqlPedido);
            $stmtPedido->execute([$novoTotal, $idPedido]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Excluir pedido e seus itens
     */
    public function excluir($id) {
        try {
            $this->pdo->beginTransaction();
            
            // Excluir itens primeiro
            $sqlItens = "DELETE FROM tbl_itens_pedido_compra WHERE id_pedido = ?";
            $stmtItens = $this->pdo->prepare($sqlItens);
            $stmtItens->execute([$id]);
            
            // Excluir pedido definitivamente (hard delete)
            // Não usamos soft delete aqui porque a listagem de pedidos considera registros da tabela principal.
            $sqlPedido = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmtPedido = $this->pdo->prepare($sqlPedido);
            $stmtPedido->execute([$id]);
            
            $this->pdo->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Buscar estatísticas
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                COUNT(*) as total_pedidos,
                COUNT(CASE WHEN status = 'em_analise' THEN 1 END) as em_analise,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as aprovados,
                COUNT(CASE WHEN status = 'em_producao' THEN 1 END) as em_producao,
                COUNT(CASE WHEN status = 'enviado' THEN 1 END) as enviados,
                COUNT(CASE WHEN status = 'entregue' THEN 1 END) as entregues,
                COUNT(CASE WHEN status = 'atrasado' THEN 1 END) as atrasados,
                COUNT(CASE WHEN status = 'urgente' THEN 1 END) as urgentes,
                COUNT(CASE WHEN status = 'em_transito' THEN 1 END) as em_transito,
                COUNT(CASE WHEN status = 'aguardando_aprovacao' THEN 1 END) as aguardando_aprovacao,
                COUNT(CASE WHEN status = 'parcialmente_recebido' THEN 1 END) as parcialmente_recebido,
                COUNT(CASE WHEN status = 'recebido' THEN 1 END) as recebidos,
                COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
                SUM(valor_total) as valor_total,
                COUNT(CASE WHEN DATE(data_criacao) = CURDATE() THEN 1 END) as hoje
                FROM {$this->table}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obter status disponíveis
     */
    public function getStatusDisponiveis() {
        return [
            'em_analise' => 'Em Análise',
            'pendente' => 'Pendente',
            'aprovado_cotacao' => 'Aprovado Cotação',
            'enviar_para_faturamento' => 'Enviar para Faturamento',
            'aprovado_para_faturar' => 'Aprovado para Faturar',
            'aprovado' => 'Aprovado',
            'em_producao' => 'Em Produção',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'atrasado' => 'Atrasado',
            'urgente' => 'Urgente',
            'em_transito' => 'Em Trânsito',
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'parcialmente_recebido' => 'Parcialmente Recebido',
            'recebido' => 'Recebido',
            'cancelado' => 'Cancelado'
        ];
    }
    
    /**
     * Verificar status da entrega prevista
     */
    public function getStatusEntrega($dataEntregaPrevista) {
        if (!$dataEntregaPrevista) {
            return ['status' => 'sem_data', 'classe' => 'text-muted', 'texto' => 'Sem data prevista'];
        }
        
        $hoje = new DateTime();
        $entrega = new DateTime($dataEntregaPrevista);
        $diferenca = $hoje->diff($entrega);
        
        if ($entrega < $hoje) {
            // Atrasado
            return [
                'status' => 'atrasado',
                'classe' => 'text-danger bg-danger-light',
                'texto' => 'Atrasado há ' . $diferenca->days . ' dia(s)'
            ];
        } elseif ($diferenca->days <= 3 && $entrega >= $hoje) {
            // A vencer (próximos 3 dias)
            return [
                'status' => 'a_vencer',
                'classe' => 'text-warning bg-warning-light',
                'texto' => 'Vence em ' . $diferenca->days . ' dia(s)'
            ];
        } else {
            // No prazo
            return [
                'status' => 'no_prazo',
                'classe' => 'text-success bg-success-light',
                'texto' => 'No prazo'
            ];
        }
    }
    
    /**
     * Atualizar status do pedido
     */
    public function atualizarStatus($idPedido, $novoStatus, $observacao = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Mapear status antigos para novos (compatibilidade)
            $mapeamentoStatus = [
                'enviar_faturamento' => 'enviar_para_faturamento'
            ];
            if (isset($mapeamentoStatus[$novoStatus])) {
                $novoStatus = $mapeamentoStatus[$novoStatus];
            }
            
            // Verificar se o status é válido
            $statusValidos = array_keys($this->getStatusDisponiveis());
            if (!in_array($novoStatus, $statusValidos)) {
                throw new Exception('Status inválido: ' . $novoStatus);
            }
            
            // Buscar status atual do pedido
            $pedidoAtual = $this->findById($idPedido);
            if (!$pedidoAtual) {
                throw new Exception('Pedido não encontrado');
            }
            
            // Validar transição de status
            $validacao = $this->validarTransicaoStatus($pedidoAtual['status'], $novoStatus);
            if (!$validacao['valido']) {
                throw new Exception($validacao['erro']);
            }
            
            // Atualizar status
            $sql = "UPDATE {$this->table} SET status = ?, data_atualizacao = NOW() WHERE id_pedido = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$novoStatus, $idPedido]);
            
            // Verificar se a atualização foi bem-sucedida
            if ($stmt->rowCount() === 0) {
                throw new Exception('Nenhuma linha foi atualizada. Verifique se o ID do pedido está correto.');
            }
            
            // Verificar se o status foi realmente salvo (pode falhar silenciosamente se o ENUM não aceitar o valor)
            $pedidoVerificado = $this->findById($idPedido);
            if (!$pedidoVerificado || $pedidoVerificado['status'] !== $novoStatus) {
                // Tentar com o status alternativo se houver
                if ($novoStatus === 'enviar_para_faturamento') {
                    $statusAlternativo = 'enviar_faturamento';
                    $stmt->execute([$statusAlternativo, $idPedido]);
                    $pedidoVerificado = $this->findById($idPedido);
                    if ($pedidoVerificado && $pedidoVerificado['status'] === $statusAlternativo) {
                        $novoStatus = $statusAlternativo; // Usar o status alternativo
                        error_log("Status atualizado para alternativa: {$statusAlternativo}");
                    } else {
                        throw new Exception('Erro ao salvar status. O valor pode não estar no ENUM do banco de dados. Status tentado: ' . $novoStatus);
                    }
                } else {
                    throw new Exception('Erro ao salvar status. O valor pode não estar no ENUM do banco de dados. Status tentado: ' . $novoStatus);
                }
            }
            
            // Registrar histórico
            $this->registrarHistoricoStatus($idPedido, $novoStatus, $observacao);
            
            // Se status for 'recebido', dar entrada no estoque
            if ($novoStatus === 'recebido') {
                $this->processarEntradaEstoque($idPedido);
            }
            
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Status atualizado com sucesso'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Registrar histórico de mudança de status
     */
    private function registrarHistoricoStatus($idPedido, $status, $observacao = null) {
        $sql = "INSERT INTO tbl_historico_status_pedidos 
                (id_pedido, status, observacao, data_alteracao, id_usuario) 
                VALUES (?, ?, ?, NOW(), ?)";
        
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPedido, $status, $observacao, $idUsuario]);
    }
    
    /**
     * Processar entrada automática no estoque quando recebido
     */
    private function processarEntradaEstoque($idPedido) {
        // Buscar itens do pedido
        $itens = $this->buscarItens($idPedido);
        $pedido = $this->findById($idPedido);
        
        foreach ($itens as $item) {
            // Log para debug
            error_log("Processando item para entrada: " . json_encode($item));
            
            // Verificar se o registro existe na tbl_estoque_filiais
            $sqlVerifica = "SELECT id_estoque FROM tbl_estoque_filiais 
                           WHERE id_catalogo = ? AND id_filial = ?";
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->execute([$item['id_catalogo'], $pedido['id_filial']]);
            
            $idEstoque = null;
            
            if ($stmtVerifica->rowCount() > 0) {
                // Buscar o id_estoque existente
                $estoqueExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
                $idEstoque = $estoqueExistente['id_estoque'];
                
                // Atualizar estoque existente
                $sqlEstoque = "UPDATE tbl_estoque_filiais 
                              SET estoque_atual = estoque_atual + ?
                              WHERE id_catalogo = ? AND id_filial = ?";
                
                $stmt = $this->pdo->prepare($sqlEstoque);
                $stmt->execute([
                    $item['quantidade'],
                    $item['id_catalogo'],
                    $pedido['id_filial']
                ]);
                
                error_log("Estoque atualizado: material {$item['id_catalogo']}, filial {$pedido['id_filial']}, +{$item['quantidade']}");
            } else {
                // Criar novo registro de estoque
                $sqlInsert = "INSERT INTO tbl_estoque_filiais 
                             (id_catalogo, id_filial, estoque_atual, estoque_minimo, estoque_maximo, 
                              preco_unitario, ativo, data_criacao)
                             VALUES (?, ?, ?, 0, 0, 0, 1, NOW())";
                
                $stmt = $this->pdo->prepare($sqlInsert);
                $stmt->execute([
                    $item['id_catalogo'],
                    $pedido['id_filial'],
                    $item['quantidade']
                ]);
                
                // Buscar o id_estoque recém-criado
                $idEstoque = $this->pdo->lastInsertId();
                
                error_log("Novo estoque criado: material {$item['id_catalogo']}, filial {$pedido['id_filial']}, quantidade {$item['quantidade']}, id_estoque: {$idEstoque}");
            }
            
            // Registrar movimentação de estoque (incluindo id_estoque)
            $sqlMovimentacao = "INSERT INTO tbl_movimentacoes_estoque 
                               (id_estoque, id_catalogo, id_filial, tipo_movimentacao, quantidade, 
                                observacoes, data_movimentacao, id_usuario, id_pedido_compra)
                               VALUES (?, ?, ?, 'entrada', ?, ?, NOW(), ?, ?)";
            
            $observacoes = "Entrada automática - Pedido #{$pedido['numero_pedido']} recebido";
            $idUsuario = $_SESSION['usuario_id'] ?? null;
            
            $stmt = $this->pdo->prepare($sqlMovimentacao);
            $stmt->execute([
                $idEstoque,
                $item['id_catalogo'],
                $pedido['id_filial'],
                $item['quantidade'],
                $observacoes,
                $idUsuario,
                $idPedido
            ]);
            
            error_log("Movimentação registrada para material {$item['id_catalogo']}, id_estoque: {$idEstoque}");
        }
    }
    
    /**
     * Buscar fornecedores
     */
    public function buscarFornecedores() {
        $sql = "SELECT id_fornecedor, razao_social as nome_fornecedor FROM tbl_fornecedores WHERE ativo = 1 ORDER BY razao_social";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar materiais
     */
    public function buscarMateriais() {
        $sql = "SELECT cm.id_catalogo as id_material, cm.codigo, cm.nome, um.sigla as unidade_medida, 
                       COALESCE(ef.estoque_atual, 0) as estoque_atual,
                       COALESCE(ef.preco_unitario, 0) as preco_unitario
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE cm.ativo = 1 
                ORDER BY cm.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Validar transição de status conforme fluxo de negócio
     */
    public function validarTransicaoStatus($statusAtual, $novoStatus, $perfilUsuario = null) {
        // Mapear status antigos para novos (compatibilidade)
        $mapeamentoStatus = [
            'enviar_faturamento' => 'enviar_para_faturamento'
        ];
        if (isset($mapeamentoStatus[$statusAtual])) {
            $statusAtual = $mapeamentoStatus[$statusAtual];
        }
        if (isset($mapeamentoStatus[$novoStatus])) {
            $novoStatus = $mapeamentoStatus[$novoStatus];
        }
        
        // Status finais que não permitem transições
        $statusFinais = ['recebido', 'cancelado'];
        
        // Se o status atual é final, não permite transições
        if (in_array($statusAtual, $statusFinais)) {
            return ['valido' => false, 'erro' => 'Pedido já está em status final e não pode ser alterado'];
        }
        
        // Se o novo status é cancelado, sempre permitir (exceto se já estiver cancelado)
        if ($novoStatus === 'cancelado') {
            return ['valido' => true];
        }
        
        $fluxoPermitido = [
            'em_analise' => ['pendente', 'cancelado'], // Gestor pode aprovar para Pendente
            'pendente' => ['aprovado_cotacao', 'cancelado'], // Setor de compras pode aprovar para Aprovado Cotação
            'aprovado_cotacao' => ['enviar_para_faturamento', 'pendente', 'cancelado'], // Fornecedor faz cotação
            'enviar_para_faturamento' => ['aprovado_para_faturar', 'aprovado_cotacao', 'cancelado'], // Setor de compras avalia
            'aprovado_para_faturar' => ['em_transito', 'enviar_para_faturamento', 'cancelado'], // Fornecedor pode enviar
            'em_transito' => ['entregue', 'aprovado_para_faturar', 'cancelado'], // Pode voltar, finalizar ou cancelar
            'entregue' => ['recebido', 'cancelado'], // Finalização ou cancelamento
            'rascunho' => ['em_analise', 'cancelado'], // Rascunho pode ir para análise ou cancelar
            'aguardando_aprovacao' => ['aprovado_cotacao', 'pendente', 'cancelado'] // Aguardando aprovação
        ];
        
        // Verificar se a transição é permitida
        if (!isset($fluxoPermitido[$statusAtual])) {
            // Se o status não está no fluxo, mas não é final, permitir apenas cancelamento
            if ($novoStatus === 'cancelado') {
                return ['valido' => true];
            }
            return ['valido' => false, 'erro' => 'Status atual inválido ou transição não permitida: ' . $statusAtual . ' -> ' . $novoStatus];
        }
        
        if (!in_array($novoStatus, $fluxoPermitido[$statusAtual])) {
            return ['valido' => false, 'erro' => 'Transição não permitida no fluxo de negócio: ' . $statusAtual . ' -> ' . $novoStatus];
        }
        
        return ['valido' => true];
    }
    
    /**
     * Obter próximos status possíveis para um pedido
     */
    public function getProximosStatusPossiveis($statusAtual, $perfilUsuario = null) {
        $fluxo = [
            'em_analise' => ['pendente' => 'Aprovar (Gestor)'],
            'pendente' => ['aprovado_cotacao' => 'Aprovar Cotação (Compras)'],
            'aprovado_cotacao' => ['enviar_para_faturamento' => 'Enviar Cotação (Fornecedor)'],
            'enviar_para_faturamento' => ['aprovado_para_faturar' => 'Aprovar Faturamento (Compras)'],
            'aprovado_para_faturar' => ['em_transito' => 'Enviar Pedido (Fornecedor)'],
            'em_transito' => ['entregue' => 'Marcar como Entregue'],
            'entregue' => ['recebido' => 'Confirmar Recebimento']
        ];
        
        $statusDisponiveis = $this->getStatusDisponiveis();
        $proximos = [];
        
        if (isset($fluxo[$statusAtual])) {
            foreach ($fluxo[$statusAtual] as $status => $acao) {
                $proximos[] = [
                    'status' => $status,
                    'nome' => $statusDisponiveis[$status] ?? $status,
                    'acao' => $acao
                ];
            }
        }
        
        // Sempre permitir cancelar (exceto se já cancelado ou recebido)
        if (!in_array($statusAtual, ['cancelado', 'recebido'])) {
            $proximos[] = [
                'status' => 'cancelado',
                'nome' => 'Cancelado',
                'acao' => 'Cancelar Pedido'
            ];
        }
        
        return $proximos;
    }
    
    /**
     * Buscar materiais com estoque baixo por filial e fornecedor
     */
    public function buscarMateriaisEstoqueBaixo($idFilial, $idFornecedor, $filtroEstoque = 'critico') {
        $sql = "SELECT cm.id_catalogo as id_material, cm.codigo, cm.nome, um.sigla as unidade_medida, 
                       COALESCE(ef.estoque_atual, 0) as estoque_atual,
                       COALESCE(ef.preco_unitario, 0) as preco_unitario,
                       COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ?
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE cm.ativo = 1
                  AND cm.id_fornecedor = ?";
        
        if ($filtroEstoque === 'normal') {
            $sql .= " AND ef.estoque_atual > COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0)";
        } elseif ($filtroEstoque === 'critico') {
            $sql .= " AND (ef.estoque_atual IS NULL OR ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0))";
        } // 'todos' não adiciona filtro adicional
        
        $sql .= " ORDER BY cm.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial, $idFornecedor]);
        return $stmt->fetchAll();
    }
    
    /**
     * Pesquisar material por código ou nome
     */
    public function pesquisarMaterial($busca, $idFilial, $idFornecedor) {
        $sql = "SELECT cm.id_catalogo as id_material, cm.codigo, cm.nome, um.sigla as unidade_medida, 
                       COALESCE(ef.estoque_atual, 0) as estoque_atual,
                       COALESCE(ef.preco_unitario, 0) as preco_unitario
                FROM tbl_catalogo_materiais cm
                LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ?
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE cm.ativo = 1 
                AND (cm.codigo LIKE ? OR cm.nome LIKE ?)
                AND cm.id_fornecedor = ?
                ORDER BY cm.nome
                LIMIT 20";
        
        $buscaTermo = "%{$busca}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idFilial, $buscaTermo, $buscaTermo, $idFornecedor]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar filiais
     */
    public function buscarFiliais() {
        $sql = "SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1 ORDER BY nome_filial";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar pedidos por fornecedor e status
     */
    public function buscarPorFornecedor($idFornecedor, $status = null) {
        try {
            $sql = "SELECT 
                        pc.*,
                        f.nome_filial,
                        u.nome as nome_usuario
                    FROM {$this->table} pc
                    LEFT JOIN tbl_filiais f ON pc.id_filial = f.id_filial
                    LEFT JOIN tbl_usuarios u ON pc.id_usuario_solicitante = u.id_usuario
                    WHERE pc.id_fornecedor = ?";
            
            $params = [$idFornecedor];
            
            if ($status) {
                $sql .= " AND pc.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY pc.data_solicitacao DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar itens para cada pedido
            foreach ($pedidos as &$pedido) {
                $pedido['itens'] = $this->buscarItens($pedido['id_pedido']);
            }
            
            return $pedidos;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Busca pedido por ID (sobrescreve o método da BaseModel)
     * A tabela de pedidos não tem coluna 'ativo'
     */
    public function findById($id, $ativoCol = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>