<?php
require_once 'BaseModel.php';

class TipoMovimentacao extends BaseModel {
    protected $table = 'tbl_tipos_movimentacao';
    protected $primaryKey = 'id_tipo_movimentacao';
    
    /**
     * Busca tipos de movimentação com contagem de uso
     */
    public function findAllWithUsageCount() {
        $sql = "SELECT tm.*, 
                       COUNT(m.id_movimentacao) as total_uso
                FROM {$this->table} tm
                LEFT JOIN tbl_movimentacoes m ON tm.id_tipo_movimentacao = m.id_tipo_movimentacao
                WHERE tm.ativo = 1
                GROUP BY tm.id_tipo_movimentacao
                ORDER BY tm.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca tipos por categoria (entrada/saída)
     */
    public function findByTipo($tipo) {
        return $this->findAll('tipo = ?', [$tipo]);
    }
    
    /**
     * Busca tipos de entrada
     */
    public function findEntradas() {
        return $this->findByTipo('entrada');
    }
    
    /**
     * Busca tipos de saída
     */
    public function findSaidas() {
        return $this->findByTipo('saida');
    }
    
    /**
     * Busca tipo por nome
     */
    public function findByNome($nome) {
        $sql = "SELECT * FROM {$this->table} WHERE nome = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se o nome já existe
     */
    public function nomeExiste($nome, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nome = ? AND ativo = 1";
        $params = [$nome];
        
        if ($excludeId) {
            $sql .= " AND id_tipo_movimentacao != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o tipo pode ser excluído
     */
    public function podeExcluir($id) {
        // Verifica se há movimentações usando este tipo
        $sql = "SELECT COUNT(*) as total FROM tbl_movimentacoes WHERE id_tipo_movimentacao = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $movimentacoes = $stmt->fetch()['total'];
        
        return $movimentacoes == 0;
    }
    
    /**
     * Busca estatísticas do tipo de movimentação
     */
    public function getEstatisticas($idTipoMovimentacao) {
        $sql = "SELECT 
                    COUNT(*) as total_movimentacoes,
                    SUM(quantidade) as quantidade_total,
                    SUM(valor_total) as valor_total,
                    COUNT(DISTINCT id_material) as materiais_diferentes,
                    COUNT(DISTINCT id_filial) as filiais_diferentes,
                    MIN(data_movimentacao) as primeira_movimentacao,
                    MAX(data_movimentacao) as ultima_movimentacao
                FROM tbl_movimentacoes 
                WHERE id_tipo_movimentacao = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idTipoMovimentacao]);
        return $stmt->fetch();
    }
    
    /**
     * Busca movimentações de um tipo
     */
    public function getMovimentacoes($idTipoMovimentacao, $idFilial = null, $limit = null) {
        $sql = "SELECT m.*, 
                       mat.codigo as material_codigo,
                       mat.nome as material_nome,
                       u.nome_completo as usuario_nome,
                       fil.nome_filial
                FROM tbl_movimentacoes m
                LEFT JOIN tbl_materiais mat ON m.id_material = mat.id_material
                LEFT JOIN tbl_usuarios u ON m.id_usuario = u.id_usuario
                LEFT JOIN tbl_filiais fil ON m.id_filial = fil.id_filial
                WHERE m.id_tipo_movimentacao = ?
                ORDER BY m.data_movimentacao DESC";
        
        $params = [$idTipoMovimentacao];
        
        if ($idFilial) {
            $sql .= " AND m.id_filial = ?";
            $params[] = $idFilial;
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca tipos mais utilizados
     */
    public function findTopTipos($limit = 10) {
        $sql = "SELECT tm.*, 
                       COUNT(m.id_movimentacao) as total_movimentacoes,
                       SUM(m.quantidade) as quantidade_total,
                       SUM(m.valor_total) as valor_total
                FROM {$this->table} tm
                LEFT JOIN tbl_movimentacoes m ON tm.id_tipo_movimentacao = m.id_tipo_movimentacao
                WHERE tm.ativo = 1
                GROUP BY tm.id_tipo_movimentacao
                HAVING total_movimentacoes > 0
                ORDER BY total_movimentacoes DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Insere tipos padrão do sistema
     */
    public function inserirTiposPadrao() {
        $tipos = [
            // Tipos de entrada
            ['nome' => 'Compra', 'descricao' => 'Entrada por compra de fornecedor', 'tipo' => 'entrada'],
            ['nome' => 'Transferência', 'descricao' => 'Entrada por transferência de outra filial', 'tipo' => 'entrada'],
            ['nome' => 'Devolução', 'descricao' => 'Entrada por devolução de cliente', 'tipo' => 'entrada'],
            ['nome' => 'Ajuste de Inventário', 'descricao' => 'Entrada por ajuste de inventário', 'tipo' => 'entrada'],
            ['nome' => 'Doação', 'descricao' => 'Entrada por doação', 'tipo' => 'entrada'],
            ['nome' => 'Produção', 'descricao' => 'Entrada por produção interna', 'tipo' => 'entrada'],
            
            // Tipos de saída
            ['nome' => 'Venda', 'descricao' => 'Saída por venda', 'tipo' => 'saida'],
            ['nome' => 'Transferência', 'descricao' => 'Saída por transferência para outra filial', 'tipo' => 'saida'],
            ['nome' => 'Consumo', 'descricao' => 'Saída por consumo interno', 'tipo' => 'saida'],
            ['nome' => 'Perda', 'descricao' => 'Saída por perda/deterioração', 'tipo' => 'saida'],
            ['nome' => 'Ajuste de Inventário', 'descricao' => 'Saída por ajuste de inventário', 'tipo' => 'saida'],
            ['nome' => 'Descarte', 'descricao' => 'Saída por descarte', 'tipo' => 'saida'],
            ['nome' => 'Empréstimo', 'descricao' => 'Saída por empréstimo', 'tipo' => 'saida']
        ];
        
        $inseridos = 0;
        foreach ($tipos as $tipo) {
            if (!$this->nomeExiste($tipo['nome'])) {
                $this->insert($tipo);
                $inseridos++;
            }
        }
        
        return $inseridos;
    }
    
    /**
     * Busca resumo de movimentações por tipo
     */
    public function getResumoMovimentacoes($dataInicio = null, $dataFim = null, $idFilial = null) {
        $sql = "SELECT 
                    tm.id_tipo_movimentacao,
                    tm.nome,
                    tm.tipo,
                    COUNT(m.id_movimentacao) as total_movimentacoes,
                    SUM(m.quantidade) as quantidade_total,
                    SUM(m.valor_total) as valor_total
                FROM {$this->table} tm
                LEFT JOIN tbl_movimentacoes m ON tm.id_tipo_movimentacao = m.id_tipo_movimentacao";
        
        $where = ['tm.ativo = 1'];
        $params = [];
        
        if ($dataInicio) {
            $where[] = 'DATE(m.data_movimentacao) >= ?';
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $where[] = 'DATE(m.data_movimentacao) <= ?';
            $params[] = $dataFim;
        }
        
        if ($idFilial) {
            $where[] = 'm.id_filial = ?';
            $params[] = $idFilial;
        }
        
        $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " GROUP BY tm.id_tipo_movimentacao, tm.nome, tm.tipo";
        $sql .= " ORDER BY tm.tipo, total_movimentacoes DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?> 