<?php

require_once 'BaseModel.php';

class Pagina extends BaseModel {
    protected $table = 'tbl_paginas';
    
    public function __construct($pdo) {
        parent::__construct($pdo);
    }
    
    /**
     * Buscar todas as páginas ativas
     */
    public function findAtivas() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY ordem, nome_pagina";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar páginas ativas com fallback para tabela não existir
     */
    public function findAtivasSafe() {
        try {
            return $this->findAtivas();
        } catch (Exception $e) {
            // Se a tabela não existir, retornar array vazio
            error_log("Tabela tbl_paginas não encontrada: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar página por ID
     */
    public function findById($id, $ativoCol = 'ativo') {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_pagina = ? AND $ativoCol = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar página por nome
     */
    public function findByNome($nome) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE nome_pagina = ? AND ativo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nome]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar páginas por categoria
     */
    public function findByCategoria($categoria) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE categoria = ? AND ativo = 1 ORDER BY ordem, nome_pagina";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar todas as categorias disponíveis
     */
    public function getCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE ativo = 1 AND categoria IS NOT NULL ORDER BY categoria";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar páginas com estatísticas
     */
    public function findWithStats() {
        try {
            $sql = "SELECT 
                        p.*,
                        COALESCE(pc.total_acessos, 0) as total_acessos,
                        COALESCE(pc.ultimo_acesso, 'Nunca') as ultimo_acesso
                    FROM {$this->table} p
                    LEFT JOIN (
                        SELECT 
                            id_pagina,
                            COUNT(*) as total_acessos,
                            MAX(data_acesso) as ultimo_acesso
                        FROM tbl_paginas_acesso
                        GROUP BY id_pagina
                    ) pc ON p.id_pagina = pc.id_pagina
                    WHERE p.ativo = 1
                    ORDER BY p.ordem, p.nome_pagina";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Registrar acesso a uma página
     */
    public function registrarAcesso($idPagina, $idUsuario = null) {
        try {
            $sql = "INSERT INTO tbl_paginas_acesso (id_pagina, id_usuario, data_acesso, ip_acesso) 
                    VALUES (?, ?, NOW(), ?)";
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idPagina, $idUsuario, $ip]);
            
            return true;
        } catch (Exception $e) {
            // Log do erro mas não falha a operação
            error_log("Erro ao registrar acesso à página: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar estatísticas de acesso
     */
    public function getEstatisticasAcesso() {
        try {
            $sql = "SELECT 
                        p.nome_pagina,
                        p.categoria,
                        COALESCE(COUNT(pa.id_acesso), 0) as total_acessos,
                        COALESCE(MAX(pa.data_acesso), 'Nunca') as ultimo_acesso
                    FROM {$this->table} p
                    LEFT JOIN tbl_paginas_acesso pa ON p.id_pagina = pa.id_pagina
                    WHERE p.ativo = 1
                    GROUP BY p.id_pagina, p.nome_pagina, p.categoria
                    ORDER BY total_acessos DESC, p.nome_pagina";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar páginas mais acessadas
     */
    public function getMaisAcessadas($limit = 10) {
        try {
            $sql = "SELECT 
                        p.nome_pagina,
                        p.categoria,
                        COUNT(pa.id_acesso) as total_acessos
                    FROM {$this->table} p
                    LEFT JOIN tbl_paginas_acesso pa ON p.id_pagina = pa.id_pagina
                    WHERE p.ativo = 1
                    GROUP BY p.id_pagina, p.nome_pagina, p.categoria
                    HAVING total_acessos > 0
                    ORDER BY total_acessos DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Verificar se nome da página já existe
     */
    public function nomeExiste($nome, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nome_pagina = ?";
            $params = [$nome];
            
            if ($excludeId) {
                $sql .= " AND id_pagina != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Criar nova página
     */
    public function criar($dados) {
        try {
            $sql = "INSERT INTO {$this->table} (
                        nome_pagina, 
                        url_pagina, 
                        descricao, 
                        categoria, 
                        icone, 
                        cor, 
                        ordem, 
                        ativo, 
                        data_criacao
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['nome_pagina'],
                $dados['url_pagina'],
                $dados['descricao'],
                $dados['categoria'] ?? null,
                $dados['icone'] ?? null,
                $dados['cor'] ?? 'primary',
                $dados['ordem'] ?? 0,
                $dados['ativo'] ?? 1
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Atualizar página
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE {$this->table} SET 
                        nome_pagina = ?, 
                        url_pagina = ?, 
                        descricao = ?, 
                        categoria = ?, 
                        icone = ?, 
                        cor = ?, 
                        ordem = ?, 
                        ativo = ?, 
                        data_atualizacao = NOW()
                    WHERE id_pagina = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['nome_pagina'],
                $dados['url_pagina'],
                $dados['descricao'],
                $dados['categoria'] ?? null,
                $dados['icone'] ?? null,
                $dados['cor'] ?? 'primary',
                $dados['ordem'] ?? 0,
                $dados['ativo'] ?? 1,
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Excluir página (soft delete)
     */
    public function excluir($id) {
        try {
            $sql = "UPDATE {$this->table} SET ativo = 0, data_atualizacao = NOW() WHERE id_pagina = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Reordenar páginas
     */
    public function reordenar($ordens) {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($ordens as $id => $ordem) {
                $sql = "UPDATE {$this->table} SET ordem = ?, data_atualizacao = NOW() WHERE id_pagina = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$ordem, $id]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
?> 