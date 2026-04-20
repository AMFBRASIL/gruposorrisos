<?php

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/BaseModel.php';

class Movimentacao extends BaseModel
{
    protected $table = 'tbl_movimentacoes';
    protected $primaryKey = 'id_movimentacao';

    public function __construct()
    {
        parent::__construct();
    }

    public function findAllWithRelations()
    {
        $sql = "SELECT m.*, 
                       cm.codigo as codigo_material, 
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       f_origem.nome_filial as filial_origem,
                       f_destino.nome_filial as filial_destino,
                       forn.razao_social as nome_fornecedor,
                       cli.nome_cliente,
                       u.nome_completo as nome_usuario
                FROM {$this->table} m
                LEFT JOIN tbl_catalogo_materiais cm ON m.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_filiais f_origem ON m.id_filial_origem = f_origem.id_filial
                LEFT JOIN tbl_filiais f_destino ON m.id_filial_destino = f_destino.id_filial
                LEFT JOIN tbl_fornecedores forn ON m.id_fornecedor = forn.id_fornecedor
                LEFT JOIN tbl_clientes cli ON m.id_cliente = cli.id_cliente
                LEFT JOIN tbl_usuarios u ON m.id_usuario_executor = u.id_usuario
                ORDER BY m.data_movimentacao DESC";
        
        return $this->executeQuery($sql);
    }

    public function findByIdWithRelations($id)
    {
        $sql = "SELECT m.*, 
                       cm.codigo as codigo_material, 
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       f_origem.nome_filial as filial_origem,
                       f_destino.nome_filial as filial_destino,
                       forn.razao_social as nome_fornecedor,
                       cli.nome_cliente,
                       u.nome_completo as nome_usuario
                FROM {$this->table} m
                LEFT JOIN tbl_catalogo_materiais cm ON m.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_filiais f_origem ON m.id_filial_origem = f_origem.id_filial
                LEFT JOIN tbl_filiais f_destino ON m.id_filial_destino = f_destino.id_filial
                LEFT JOIN tbl_fornecedores forn ON m.id_fornecedor = forn.id_fornecedor
                LEFT JOIN tbl_clientes cli ON m.id_cliente = cli.id_cliente
                LEFT JOIN tbl_usuarios u ON m.id_usuario_executor = u.id_usuario
                WHERE m.{$this->primaryKey} = ?";
        
        return $this->executeQuerySingle($sql, [$id]);
    }

    public function findWithFilters($page = 1, $limit = 10, $busca = '', $tipo = '', $data_inicio = '', $data_fim = '')
    {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        if (!empty($busca)) {
            $where[] = "(m.numero_movimentacao LIKE ? OR cm.codigo LIKE ? OR cm.nome LIKE ? OR m.documento LIKE ?)";
            $buscaParam = "%{$busca}%";
            $params[] = $buscaParam;
            $params[] = $buscaParam;
            $params[] = $buscaParam;
            $params[] = $buscaParam;
        }

        if (!empty($tipo)) {
            $where[] = "m.tipo_movimentacao = ?";
            $params[] = $tipo;
        }

        if (!empty($data_inicio)) {
            $where[] = "DATE(m.data_movimentacao) >= ?";
            $params[] = $data_inicio;
        }

        if (!empty($data_fim)) {
            $where[] = "DATE(m.data_movimentacao) <= ?";
            $params[] = $data_fim;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT m.*, 
                       cm.codigo as codigo_material, 
                       cm.nome as nome_material,
                       um.sigla as unidade_material,
                       f_origem.nome_filial as filial_origem,
                       f_destino.nome_filial as filial_destino,
                       forn.razao_social as nome_fornecedor,
                       cli.nome_cliente,
                       u.nome_completo as nome_usuario
                FROM {$this->table} m
                LEFT JOIN tbl_catalogo_materiais cm ON m.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                LEFT JOIN tbl_filiais f_origem ON m.id_filial_origem = f_origem.id_filial
                LEFT JOIN tbl_filiais f_destino ON m.id_filial_destino = f_destino.id_filial
                LEFT JOIN tbl_fornecedores forn ON m.id_fornecedor = forn.id_fornecedor
                LEFT JOIN tbl_clientes cli ON m.id_cliente = cli.id_cliente
                LEFT JOIN tbl_usuarios u ON m.id_usuario_executor = u.id_usuario
                {$whereClause}
                ORDER BY m.data_movimentacao DESC 
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $data = $this->executeQuery($sql, $params);

        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} m
                     LEFT JOIN tbl_catalogo_materiais cm ON m.id_catalogo = cm.id_catalogo
                     {$whereClause}";
        
        $total = $this->executeQuerySingle($countSql, array_slice($params, 0, -2))['total'];

        return [
            'movimentacoes' => $data,
            'total' => $total,
            'paginas' => ceil($total / $limit),
            'pagina_atual' => $page
        ];
    }

    public function gerarNumeroMovimentacao()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(numero_movimentacao, 5) AS UNSIGNED)) as ultimo_numero 
                FROM {$this->table} 
                WHERE numero_movimentacao LIKE 'MOV-%'";
        
        $result = $this->executeQuerySingle($sql);
        $proximoNumero = ($result['ultimo_numero'] ?? 0) + 1;
        
        return 'MOV-' . str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);
    }

    public function criar($dados)
    {
        error_log('🚀 Iniciando criação de movimentação: ' . json_encode($dados));
        
        $numeroMovimentacao = $this->gerarNumeroMovimentacao();
        error_log('📋 Número da movimentação gerado: ' . $numeroMovimentacao);
        
        // Buscar estoque atual do material
        $estoqueAtual = $this->buscarEstoqueAtual($dados['id_catalogo'], $dados['id_filial_destino'] ?? $dados['id_filial_origem']);
        $estoqueAnterior = $estoqueAtual;
        
        // Buscar custo médio atual do estoque
        $custoMedioAnterior = $this->buscarCustoMedioAtual($dados['id_catalogo'], $dados['id_filial_destino'] ?? $dados['id_filial_origem']);
        $custoMedioAtual = $custoMedioAnterior;
        
        error_log("📦 Estoque atual: {$estoqueAtual}, Anterior: {$estoqueAnterior}");
        error_log("💰 Custo médio atual: {$custoMedioAnterior}");
        
        // Calcular novo estoque baseado no tipo de movimentação
        switch ($dados['tipo_movimentacao']) {
            case 'entrada':
                $estoqueAtual += $dados['quantidade'];
                // Calcular novo custo médio ponderado para entradas
                if ($dados['valor_unitario'] && $dados['valor_unitario'] > 0) {
                    $valorTotalAnterior = $estoqueAnterior * $custoMedioAnterior;
                    $valorTotalEntrada = $dados['quantidade'] * $dados['valor_unitario'];
                    $estoqueTotal = $estoqueAnterior + $dados['quantidade'];
                    
                    if ($estoqueTotal > 0) {
                        $custoMedioAtual = ($valorTotalAnterior + $valorTotalEntrada) / $estoqueTotal;
                    } else {
                        $custoMedioAtual = $dados['valor_unitario'];
                    }
                    error_log("📊 Cálculo custo médio - Anterior: {$custoMedioAnterior}, Novo: {$custoMedioAtual}");
                }
                break;
            case 'saida':
                $estoqueAtual -= $dados['quantidade'];
                // Para saídas, o custo médio permanece o mesmo
                break;
            case 'transferencia':
                // Para transferência, o estoque diminui na origem e aumenta no destino
                $estoqueAtual -= $dados['quantidade'];
                // O custo médio permanece o mesmo na origem
                break;
            case 'ajuste':
                $estoqueAtual += $dados['quantidade']; // quantidade pode ser negativa para ajuste
                // Para ajustes, o custo médio pode ser recalculado se houver valor_unitario
                if (isset($dados['valor_unitario']) && $dados['valor_unitario'] > 0 && $estoqueAtual > 0) {
                    $custoMedioAtual = $dados['valor_unitario'];
                }
                break;
        }
        
        error_log("🔄 Novo estoque calculado: {$estoqueAtual}");
        error_log("💰 Valores - Unitário: {$dados['valor_unitario']}, Total: {$dados['valor_total']}");
        error_log("💰 Custo médio - Anterior: {$custoMedioAnterior}, Atual: {$custoMedioAtual}");
        
        // Log dos campos de brinde se existirem
        if (isset($dados['is_brinde'])) {
            $fornecedor = isset($dados['fornecedor_brinde']) ? $dados['fornecedor_brinde'] : 'N/A';
            $valor = isset($dados['valor_estimado_brinde']) ? $dados['valor_estimado_brinde'] : 'N/A';
            error_log("🎁 Campos de brinde - is_brinde: {$dados['is_brinde']}, fornecedor: {$fornecedor}, valor: {$valor}");
        }
        
        $sql = "INSERT INTO {$this->table} (
                    numero_movimentacao, tipo_movimentacao, subtipo_movimentacao, id_catalogo, 
                    quantidade, estoque_anterior_destino, estoque_atual_destino, 
                    valor_unitario, valor_total, custo_medio_anterior, custo_medio_atual,
                    id_filial_origem, id_filial_destino, 
                    id_fornecedor, id_cliente, documento, numero_documento, observacoes, 
                    id_usuario_executor, is_brinde, fornecedor_brinde, valor_estimado_brinde
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $numeroMovimentacao,
            $dados['tipo_movimentacao'],
            $dados['subtipo_movimentacao'] ?? null,
            $dados['id_catalogo'],
            $dados['quantidade'],
            $estoqueAnterior,
            $estoqueAtual,
            $dados['valor_unitario'] ?? null,
            $dados['valor_total'] ?? null,
            $custoMedioAnterior,
            $custoMedioAtual,
            $dados['id_filial_origem'] ?? null,
            $dados['id_filial_destino'] ?? null,
            $dados['id_fornecedor'] ?? null,
            $dados['id_cliente'] ?? null,
            $dados['documento'] ?? null,
            $dados['numero_documento'] ?? null,
            $dados['observacoes'] ?? null,
            $dados['id_usuario_executor'],
            $dados['is_brinde'] ?? 0,
            $dados['fornecedor_brinde'] ?? null,
            $dados['valor_estimado_brinde'] ?? null
        ];
        
        error_log('📝 SQL: ' . $sql);
        error_log('🔢 Parâmetros: ' . json_encode($params));

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $id = $this->pdo->lastInsertId();
        
        error_log("✅ Movimentação inserida com ID: {$id}");
        
        // Atualizar estoque do material
        if ($dados['is_brinde'] == 1) {
            // Para brindes, atualizar estoque de brinde separadamente
            $this->atualizarEstoqueBrinde($dados['id_catalogo'], $dados['quantidade'], $dados['id_filial_destino'] ?? $dados['id_filial_origem']);
            error_log("🎁 Estoque de brinde atualizado para material {$dados['id_catalogo']}");
        } else {
            // Para materiais normais, atualizar estoque normalmente
            $this->atualizarEstoqueMaterial($dados['id_catalogo'], $estoqueAtual, $dados['id_filial_destino'] ?? $dados['id_filial_origem']);
            
            // O custo médio já foi salvo na movimentação (custo_medio_atual)
            // Não precisa atualizar em outra tabela
            if ($dados['tipo_movimentacao'] === 'entrada' && $custoMedioAtual != $custoMedioAnterior) {
                error_log("💰 Custo médio calculado e salvo na movimentação: {$custoMedioAnterior} -> {$custoMedioAtual}");
            }
            
            error_log("📦 Estoque normal atualizado para material {$dados['id_catalogo']}");
        }
        
        return $id;
    }
    
    private function buscarCustoMedioAtual($idMaterial, $idFilial = null)
    {
        // Buscar o custo médio atual da última movimentação de entrada para este material e filial
        if ($idFilial) {
            $sql = "SELECT custo_medio_atual 
                    FROM {$this->table} 
                    WHERE id_catalogo = ? 
                    AND (id_filial_destino = ? OR id_filial_origem = ?)
                    AND tipo_movimentacao IN ('entrada', 'ajuste')
                    ORDER BY data_movimentacao DESC 
                    LIMIT 1";
            $result = $this->executeQuerySingle($sql, [$idMaterial, $idFilial, $idFilial]);
        } else {
            $sql = "SELECT custo_medio_atual 
                    FROM {$this->table} 
                    WHERE id_catalogo = ? 
                    AND tipo_movimentacao IN ('entrada', 'ajuste')
                    ORDER BY data_movimentacao DESC 
                    LIMIT 1";
            $result = $this->executeQuerySingle($sql, [$idMaterial]);
        }
        
        // Se não encontrou movimentação anterior, usar 0 como padrão
        $custoMedio = $result ? ($result['custo_medio_atual'] ?? 0) : 0;
        
        // Se o custo médio for 0, tentar usar o preço padrão do catálogo
        if ($custoMedio == 0) {
            $sql = "SELECT preco_unitario_padrao 
                    FROM tbl_catalogo_materiais 
                    WHERE id_catalogo = ?";
            $result = $this->executeQuerySingle($sql, [$idMaterial]);
            $custoMedio = $result ? ($result['preco_unitario_padrao'] ?? 0) : 0;
        }
        
        return $custoMedio;
    }
    
    private function atualizarCustoMedio($idMaterial, $novoCustoMedio, $idFilial = null)
    {
        // O custo médio é salvo na movimentação (custo_medio_atual), 
        // não precisa atualizar em outra tabela
        // Esta função é mantida para compatibilidade, mas não faz nada
        // pois o custo médio é calculado e salvo automaticamente na movimentação
        error_log("💰 Custo médio calculado: {$novoCustoMedio} (salvo na movimentação)");
    }

    private function buscarEstoqueAtual($idMaterial, $idFilial = null)
    {
        if ($idFilial) {
            $sql = "SELECT ef.estoque_atual FROM tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    WHERE cm.id_catalogo = ? AND ef.id_filial = ?";
            $result = $this->executeQuerySingle($sql, [$idMaterial, $idFilial]);
        } else {
            $sql = "SELECT ef.estoque_atual FROM tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    WHERE cm.id_catalogo = ?";
            $result = $this->executeQuerySingle($sql, [$idMaterial]);
        }
        return $result ? $result['estoque_atual'] : 0;
    }

    private function atualizarEstoqueMaterial($idMaterial, $novoEstoque, $idFilial = null)
    {
        if ($idFilial) {
            $sql = "UPDATE tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    SET ef.estoque_atual = ? 
                    WHERE cm.id_catalogo = ? AND ef.id_filial = ?";
            $this->executeQuery($sql, [$novoEstoque, $idMaterial, $idFilial]);
        } else {
            $sql = "UPDATE tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    SET ef.estoque_atual = ? 
                    WHERE cm.id_catalogo = ?";
            $this->executeQuery($sql, [$novoEstoque, $idMaterial]);
        }
    }

    private function atualizarEstoqueBrinde($idMaterial, $quantidade, $idFilial = null)
    {
        try {
            // Verificar se já existe registro de estoque de brinde
            if ($idFilial) {
                $sql = "SELECT estoque_brinde FROM tbl_estoque_filiais ef 
                        INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                        WHERE cm.id_catalogo = ? AND ef.id_filial = ?";
                $result = $this->executeQuerySingle($sql, [$idMaterial, $idFilial]);
            } else {
                $sql = "SELECT estoque_brinde FROM tbl_estoque_filiais ef 
                        INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                        WHERE cm.id_catalogo = ?";
                $result = $this->executeQuerySingle($sql, [$idMaterial]);
            }
            
            $estoqueBrindeAtual = $result ? ($result['estoque_brinde'] ?? 0) : 0;
            $novoEstoqueBrinde = $estoqueBrindeAtual + $quantidade;
            
            // Atualizar ou criar campo estoque_brinde
            if ($idFilial) {
                $sql = "UPDATE tbl_estoque_filiais ef 
                        INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                        SET ef.estoque_brinde = ? 
                        WHERE cm.id_catalogo = ? AND ef.id_filial = ?";
                $this->executeQuery($sql, [$novoEstoqueBrinde, $idMaterial, $idFilial]);
            } else {
                $sql = "UPDATE tbl_estoque_filiais ef 
                        INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                        SET ef.estoque_brinde = ? 
                        WHERE cm.id_catalogo = ?";
                $this->executeQuery($sql, [$novoEstoqueBrinde, $idMaterial]);
            }
            
            error_log("🎁 Estoque de brinde atualizado: {$estoqueBrindeAtual} + {$quantidade} = {$novoEstoqueBrinde}");
            
        } catch (Exception $e) {
            error_log("❌ Erro ao atualizar estoque de brinde: " . $e->getMessage());
            // Se der erro, não afeta o fluxo principal
        }
    }

    public function getEstatisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_movimentacoes,
                    SUM(CASE WHEN tipo_movimentacao = 'entrada' THEN 1 ELSE 0 END) as entradas,
                    SUM(CASE WHEN tipo_movimentacao = 'saida' THEN 1 ELSE 0 END) as saidas,
                    SUM(CASE WHEN tipo_movimentacao = 'transferencia' THEN 1 ELSE 0 END) as transferencias,
                    SUM(CASE WHEN tipo_movimentacao = 'ajuste' THEN 1 ELSE 0 END) as ajustes,
                    SUM(CASE WHEN tipo_movimentacao = 'entrada' THEN valor_total ELSE 0 END) as valor_entradas,
                    SUM(CASE WHEN tipo_movimentacao = 'saida' THEN valor_total ELSE 0 END) as valor_saidas,
                    COUNT(DISTINCT id_catalogo) as materiais_movimentados,
                    -- Estatísticas de brinde
                    SUM(CASE WHEN is_brinde = 1 THEN 1 ELSE 0 END) as materiais_brinde,
                    SUM(CASE WHEN is_brinde = 1 THEN valor_estimado_brinde ELSE 0 END) as valor_brindes,
                    COUNT(DISTINCT CASE WHEN is_brinde = 1 THEN fornecedor_brinde END) as fornecedores_brinde,
                    -- Estoque total de brindes (soma de todos os materiais)
                    (SELECT COALESCE(SUM(ef.estoque_brinde), 0) FROM tbl_estoque_filiais ef) as estoque_total_brindes
                FROM {$this->table}
                WHERE DATE(data_movimentacao) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        
        return $this->executeQuerySingle($sql);
    }

    public function buscarMateriais($idFilial = null, $filtroBrinde = null)
    {
        $params = [];
        
        // Construir JOIN com filtro de filial
        if ($idFilial) {
            $sql = "SELECT cm.id_catalogo, cm.codigo, cm.nome, um.sigla as unidade, 
                           CAST(COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) AS DECIMAL(10,2)) as preco_unitario, 
                           f.razao_social as nome_fornecedor, 
                           COALESCE(ef.id_filial, ?) as id_filial,
                           CAST(COALESCE(ef.estoque_atual, 0) AS DECIMAL(15,3)) as estoque_atual, 
                           CAST(COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AS DECIMAL(15,3)) as estoque_minimo, 
                           CAST(COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) AS DECIMAL(15,3)) as estoque_maximo
                    FROM tbl_catalogo_materiais cm
                    LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND ef.id_filial = ? AND (ef.ativo = 1 OR ef.ativo IS NULL)
                    LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                    LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                    WHERE cm.ativo = 1";
            $params[] = $idFilial;
            $params[] = $idFilial;
        } else {
            $sql = "SELECT cm.id_catalogo, cm.codigo, cm.nome, um.sigla as unidade, 
                           CAST(COALESCE(ef.preco_unitario, cm.preco_unitario_padrao, 0) AS DECIMAL(10,2)) as preco_unitario, 
                           f.razao_social as nome_fornecedor, 
                           ef.id_filial,
                           CAST(COALESCE(ef.estoque_atual, 0) AS DECIMAL(15,3)) as estoque_atual, 
                           CAST(COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) AS DECIMAL(15,3)) as estoque_minimo, 
                           CAST(COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) AS DECIMAL(15,3)) as estoque_maximo
                    FROM tbl_catalogo_materiais cm
                    LEFT JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo AND (ef.ativo = 1 OR ef.ativo IS NULL)
                    LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                    LEFT JOIN tbl_fornecedores f ON cm.id_fornecedor = f.id_fornecedor
                    WHERE cm.ativo = 1";
        }
        
        // Filtrar por brinde se especificado
        if ($filtroBrinde) {
            switch ($filtroBrinde) {
                case 'apenas_brindes':
                    $sql .= " AND cm.is_brinde = 1";
                    break;
                case 'excluir_brindes':
                    $sql .= " AND (cm.is_brinde = 0 OR cm.is_brinde IS NULL)";
                    break;
            }
        }
        
        $sql .= " ORDER BY cm.nome";
        
        return $this->executeQuery($sql, $params);
    }

    public function buscarFiliais()
    {
        $sql = "SELECT id_filial, nome_filial 
                FROM tbl_filiais 
                WHERE filial_ativa = 1 
                ORDER BY nome_filial";
        
        return $this->executeQuery($sql);
    }

    public function buscarFornecedores()
    {
        $sql = "SELECT id_fornecedor, razao_social as nome_fornecedor 
                FROM tbl_fornecedores 
                WHERE ativo = 1 
                ORDER BY razao_social";
        
        return $this->executeQuery($sql);
    }

    public function buscarClientes()
    {
        $sql = "SELECT id_cliente, nome_cliente 
                FROM tbl_clientes 
                WHERE ativo = 1 
                ORDER BY nome_cliente";
        
        return $this->executeQuery($sql);
    }
    
    /**
     * Sobrescreve o método delete do BaseModel para fazer exclusão física
     * e reverter o estoque quando necessário
     */
    public function delete($id)
    {
        try {
            // Buscar dados da movimentação antes de excluir
            $movimentacao = $this->findByIdWithRelations($id);
            
            if (!$movimentacao) {
                throw new Exception('Movimentação não encontrada');
            }
            
            error_log("🗑️ Iniciando exclusão da movimentação ID: {$id}");
            error_log("📦 Dados da movimentação: " . json_encode($movimentacao));
            
            // Reverter o estoque antes de excluir
            $this->reverterEstoque($movimentacao);
            
            // Fazer exclusão física
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                error_log("✅ Movimentação {$id} excluída com sucesso");
            } else {
                error_log("❌ Erro ao excluir movimentação {$id}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ Erro ao excluir movimentação: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Reverte o estoque quando uma movimentação é excluída
     */
    private function reverterEstoque($movimentacao)
    {
        try {
            $idCatalogo = $movimentacao['id_catalogo'];
            $tipoMovimentacao = $movimentacao['tipo_movimentacao'];
            $quantidade = $movimentacao['quantidade'];
            $idFilialDestino = $movimentacao['id_filial_destino'];
            $idFilialOrigem = $movimentacao['id_filial_origem'];
            $isBrinde = $movimentacao['is_brinde'] ?? 0;
            
            error_log("🔄 Revertendo estoque para movimentação tipo: {$tipoMovimentacao}");
            
            // Determinar qual filial usar para reverter
            $idFilial = $idFilialDestino ?? $idFilialOrigem;
            
            if (!$idFilial) {
                error_log("⚠️ Filial não encontrada, não é possível reverter estoque");
                return;
            }
            
            // Buscar estoque atual
            $estoqueAtual = $this->buscarEstoqueAtual($idCatalogo, $idFilial);
            error_log("📦 Estoque atual antes da reversão: {$estoqueAtual}");
            
            // Reverter baseado no tipo de movimentação
            $novoEstoque = $estoqueAtual;
            
            switch ($tipoMovimentacao) {
                case 'entrada':
                    // Se era entrada, subtrair a quantidade (reverter)
                    $novoEstoque -= $quantidade;
                    break;
                case 'saida':
                    // Se era saída, adicionar a quantidade (reverter)
                    $novoEstoque += $quantidade;
                    break;
                case 'transferencia':
                    // Para transferência, reverter na origem e destino
                    if ($idFilialOrigem) {
                        $estoqueOrigem = $this->buscarEstoqueAtual($idCatalogo, $idFilialOrigem);
                        $novoEstoqueOrigem = $estoqueOrigem + $quantidade;
                        $this->atualizarEstoqueMaterial($idCatalogo, $novoEstoqueOrigem, $idFilialOrigem);
                        error_log("🔄 Estoque origem revertido: {$estoqueOrigem} -> {$novoEstoqueOrigem}");
                    }
                    if ($idFilialDestino) {
                        $estoqueDestino = $this->buscarEstoqueAtual($idCatalogo, $idFilialDestino);
                        $novoEstoque = $estoqueDestino - $quantidade;
                    }
                    break;
                case 'ajuste':
                    // Para ajuste, usar o estoque anterior
                    $novoEstoque = $movimentacao['estoque_anterior_destino'] ?? $estoqueAtual;
                    break;
            }
            
            error_log("📦 Novo estoque após reversão: {$novoEstoque}");
            
            // Atualizar estoque
            if ($isBrinde == 1) {
                // Reverter estoque de brinde
                $estoqueBrindeAtual = $this->buscarEstoqueBrindeAtual($idCatalogo, $idFilial);
                $novoEstoqueBrinde = $estoqueBrindeAtual - $quantidade;
                $this->atualizarEstoqueBrinde($idCatalogo, -$quantidade, $idFilial);
                error_log("🎁 Estoque de brinde revertido: {$estoqueBrindeAtual} -> {$novoEstoqueBrinde}");
            } else {
                // Atualizar estoque normal
                $this->atualizarEstoqueMaterial($idCatalogo, $novoEstoque, $idFilial);
                error_log("📦 Estoque normal revertido para: {$novoEstoque}");
            }
            
        } catch (Exception $e) {
            error_log("❌ Erro ao reverter estoque: " . $e->getMessage());
            // Não lançar exceção para não impedir a exclusão
        }
    }
    
    /**
     * Busca estoque de brinde atual
     */
    private function buscarEstoqueBrindeAtual($idMaterial, $idFilial = null)
    {
        if ($idFilial) {
            $sql = "SELECT estoque_brinde FROM tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    WHERE cm.id_catalogo = ? AND ef.id_filial = ?";
            $result = $this->executeQuerySingle($sql, [$idMaterial, $idFilial]);
        } else {
            $sql = "SELECT estoque_brinde FROM tbl_estoque_filiais ef 
                    INNER JOIN tbl_catalogo_materiais cm ON ef.id_catalogo = cm.id_catalogo 
                    WHERE cm.id_catalogo = ?";
            $result = $this->executeQuerySingle($sql, [$idMaterial]);
        }
        return $result ? ($result['estoque_brinde'] ?? 0) : 0;
    }
} 