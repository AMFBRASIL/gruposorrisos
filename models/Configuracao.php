<?php
require_once 'BaseModel.php';

class Configuracao extends BaseModel {
    protected $table = 'tbl_configuracoes';
    protected $primaryKey = 'id_configuracao';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Buscar configuração por chave
     */
    public function buscarPorChave($chave) {
        $sql = "SELECT * FROM {$this->table} WHERE chave = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$chave]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar valor de uma configuração
     */
    public function getValor($chave, $padrao = null) {
        $config = $this->buscarPorChave($chave);
        if ($config) {
            return $config['valor'];
        }
        return $padrao;
    }
    
    /**
     * Definir valor de uma configuração
     */
    public function setValor($chave, $valor) {
        $sql = "INSERT INTO {$this->table} (chave, valor) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE valor = ?, data_atualizacao = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$chave, $valor, $valor]);
    }
    
    /**
     * Buscar configurações por categoria
     */
    public function buscarPorCategoria($categoria) {
        $sql = "SELECT * FROM {$this->table} WHERE categoria = ? AND ativo = 1 ORDER BY chave";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoria]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar todas as configurações ativas
     */
    public function buscarTodas() {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY categoria, chave";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Atualizar múltiplas configurações
     */
    public function atualizarConfiguracoes($configuracoes) {
        $this->pdo->beginTransaction();
        
        try {
            foreach ($configuracoes as $chave => $valor) {
                $sql = "UPDATE {$this->table} SET valor = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE chave = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$valor, $chave]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    /**
     * Buscar configurações agrupadas por categoria
     */
    public function buscarAgrupadasPorCategoria() {
        $sql = "SELECT categoria, chave, valor, descricao, tipo FROM {$this->table} 
                WHERE ativo = 1 ORDER BY categoria, chave";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $resultado = [];
        while ($row = $stmt->fetch()) {
            $categoria = $row['categoria'];
            if (!isset($resultado[$categoria])) {
                $resultado[$categoria] = [];
            }
            $resultado[$categoria][] = $row;
        }
        
        return $resultado;
    }
    
    /**
     * Validar configuração
     */
    public function validarConfiguracao($chave, $valor, $tipo) {
        switch ($tipo) {
            case 'email':
                return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
            case 'numero':
                return is_numeric($valor);
            case 'booleano':
                return in_array($valor, ['0', '1', 'true', 'false']);
            case 'telefone':
                return preg_match('/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/', $valor);
            default:
                return true;
        }
    }
    
    /**
     * Buscar estatísticas das configurações
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_configuracoes,
                    COUNT(CASE WHEN categoria = 'empresa' THEN 1 END) as config_empresa,
                    COUNT(CASE WHEN categoria = 'notificacoes' THEN 1 END) as config_notificacoes,
                    COUNT(CASE WHEN categoria = 'sistema' THEN 1 END) as config_sistema,
                    COUNT(CASE WHEN categoria = 'seguranca' THEN 1 END) as config_seguranca,
                    COUNT(CASE WHEN categoria = 'estoque' THEN 1 END) as config_estoque,
                    COUNT(CASE WHEN categoria = 'relatorios' THEN 1 END) as config_relatorios
                FROM {$this->table} 
                WHERE ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?> 