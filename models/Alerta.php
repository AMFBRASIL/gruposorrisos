<?php
require_once 'BaseModel.php';

class Alerta extends BaseModel {
    protected $table = 'tbl_alertas_estoque';
    protected $primaryKey = 'id_alerta';
    
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            parent::__construct();
        }
    }
    
    /**
     * Sobrescreve findById para não usar coluna 'ativo' (alertas usam 'status')
     */
    public function findById($id, $ativoCol = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Sobrescreve delete para marcar como 'ignorado' ao invés de usar coluna 'ativo'
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET status = 'ignorado' WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca alertas com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material,
                    um.sigla as unidade_medida
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE ae.status = 'ativo'";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        
        $sql .= " ORDER BY ae.data_criacao DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca um alerta por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material,
                    um.sigla as unidade_medida
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE ae.id_alerta = ? AND ae.status = 'ativo'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca com paginação e filtros
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOIN correto para materiais
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material,
                    um.sigla as unidade_medida
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
                WHERE ae.status = 'ativo'";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        
        $sql .= " ORDER BY ae.data_criacao DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} ae WHERE ae.status = 'ativo'";
        if (!empty($where)) {
            $countSql .= " AND " . $where;
        }
        
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        return [
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Cria um novo alerta
     */
    public function criar($dados) {
        $sql = "INSERT INTO {$this->table} (
                    id_filial, id_catalogo, tipo_alerta, quantidade_atual, 
                    quantidade_referencia, dias_vencimento, mensagem, prioridade,
                    id_usuario_responsavel, data_criacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $dados['id_filial'],
            $dados['id_material'], // Mantendo id_material para compatibilidade nos dados passados
            $dados['tipo_alerta'],
            $dados['quantidade_atual'] ?? 0,
            $dados['quantidade_referencia'] ?? null,
            $dados['dias_vencimento'] ?? null,
            $dados['mensagem'] ?? null,
            $dados['prioridade'] ?? 'media',
            $dados['id_usuario_responsavel'] ?? null
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Marca alerta como resolvido
     */
    public function marcarComoResolvido($id) {
        $sql = "UPDATE {$this->table} SET status = 'resolvido', data_resolucao = NOW() WHERE id_alerta = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Marca todos os alertas como resolvidos
     */
    public function marcarTodosComoResolvidos() {
        $sql = "UPDATE {$this->table} SET status = 'resolvido', data_resolucao = NOW() WHERE status = 'ativo'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
    
    /**
     * Busca alertas críticos
     */
    public function getAlertasCriticos($limit = 5) {
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                WHERE ae.status = 'ativo' AND ae.prioridade = 'critica'
                ORDER BY ae.data_criacao DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Gera estatísticas dos alertas
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_alertas,
                    SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as alertas_ativos,
                    SUM(CASE WHEN status = 'resolvido' THEN 1 ELSE 0 END) as alertas_resolvidos,
                    SUM(CASE WHEN prioridade = 'critica' THEN 1 ELSE 0 END) as alertas_criticos,
                    SUM(CASE WHEN prioridade = 'alta' THEN 1 ELSE 0 END) as alertas_altos,
                    SUM(CASE WHEN prioridade = 'media' THEN 1 ELSE 0 END) as alertas_medios,
                    SUM(CASE WHEN prioridade = 'baixa' THEN 1 ELSE 0 END) as alertas_baixos,
                    SUM(CASE WHEN DATE(data_criacao) = CURDATE() THEN 1 ELSE 0 END) as alertas_hoje,
                    SUM(CASE WHEN status = 'resolvido' AND DATE(data_resolucao) = CURDATE() THEN 1 ELSE 0 END) as resolvidos_hoje,
                    COUNT(DISTINCT id_catalogo) as produtos_afetados,
                    COUNT(DISTINCT id_filial) as filiais_afetadas
                FROM {$this->table}";
        
        return $this->executeQuerySingle($sql);
    }
    
    /**
     * Gera alertas automáticos baseados no estoque
     */
    public function gerarAlertasAutomaticos() {
        // Buscar materiais com estoque baixo
        $sql = "SELECT 
                    cm.id_catalogo as id_material,
                    cm.nome as nome_material,
                    ef.estoque_atual,
                    COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) as estoque_minimo,
                    COALESCE(ef.estoque_maximo, cm.estoque_maximo_padrao, 0) as estoque_maximo,
                    f.id_filial,
                    f.nome_filial
                FROM tbl_catalogo_materiais cm
                INNER JOIN tbl_estoque_filiais ef ON cm.id_catalogo = ef.id_catalogo
                INNER JOIN tbl_filiais f ON ef.id_filial = f.id_filial
                WHERE cm.ativo = 1 
                AND ef.ativo = 1
                AND f.filial_ativa = 1
                AND (ef.estoque_atual <= COALESCE(ef.estoque_minimo, cm.estoque_minimo_padrao, 0) OR ef.estoque_atual = 0)";
        
        $materiais = $this->executeQuery($sql);
        $alertasCriados = 0;
        
        foreach ($materiais as $material) {
            // Verificar se já existe alerta ativo para este material/filial
            $sqlCheck = "SELECT COUNT(*) as total FROM {$this->table} 
                        WHERE id_catalogo = ? AND id_filial = ? AND status = 'ativo'";
            $stmt = $this->pdo->prepare($sqlCheck);
            $stmt->execute([$material['id_material'], $material['id_filial']]);
            $existe = $stmt->fetch()['total'] > 0;
            
            if (!$existe) {
                $tipoAlerta = $material['estoque_atual'] == 0 ? 'estoque_zerado' : 'estoque_baixo';
                $prioridade = $material['estoque_atual'] == 0 ? 'critica' : 'alta';
                
                $mensagem = $material['estoque_atual'] == 0 
                    ? "Produto {$material['nome_material']} está com estoque zerado na filial {$material['nome_filial']}"
                    : "Produto {$material['nome_material']} está com estoque baixo ({$material['estoque_atual']}) na filial {$material['nome_filial']}";
                
                $this->criar([
                    'id_filial' => $material['id_filial'],
                    'id_material' => $material['id_material'],
                    'tipo_alerta' => $tipoAlerta,
                    'quantidade_atual' => $material['estoque_atual'],
                    'quantidade_referencia' => $material['estoque_minimo'] ?? 0,
                    'mensagem' => $mensagem,
                    'prioridade' => $prioridade
                ]);
                
                $alertasCriados++;
            }
        }
        
        return $alertasCriados;
    }
    
    /**
     * Busca alertas ativos
     */
    public function getAlertasAtivos($limit = 10) {
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                WHERE ae.status = 'ativo'
                ORDER BY ae.data_criacao DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca alertas por tipo
     */
    public function getAlertasPorTipo($tipo, $limit = 10) {
        $sql = "SELECT 
                    ae.*,
                    f.nome_filial,
                    cm.codigo as codigo_material,
                    cm.nome as nome_material
                FROM {$this->table} ae
                LEFT JOIN tbl_filiais f ON ae.id_filial = f.id_filial
                LEFT JOIN tbl_catalogo_materiais cm ON ae.id_catalogo = cm.id_catalogo
                WHERE ae.status = 'ativo' AND ae.tipo_alerta = ?
                ORDER BY ae.data_criacao DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tipo, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Método de compatibilidade - marca alerta como lido (usando status resolvido)
     */
    public function marcarComoLido($id) {
        return $this->marcarComoResolvido($id);
    }
    
    /**
     * Método de compatibilidade - marca todos os alertas como lidos
     */
    public function marcarTodosComoLidos() {
        return $this->marcarTodosComoResolvidos();
    }
    
    /**
     * Método de compatibilidade - busca alertas não lidos (usando status ativo)
     */
    public function getAlertasNaoLidos($limit = 10) {
        return $this->getAlertasAtivos($limit);
    }
} 