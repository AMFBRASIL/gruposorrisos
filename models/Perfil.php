<?php
require_once 'BaseModel.php';

class Perfil extends BaseModel {
    protected $table = 'tbl_perfis';
    protected $primaryKey = 'id_perfil';
    
    /**
     * Busca todos os perfis ativos
     */
    public function findAtivos() {
        return $this->findAll();
    }
    
    /**
     * Busca perfis com paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal
        $sql = "SELECT p.*, 
                       COUNT(u.id_usuario) as total_usuarios
                FROM {$this->table} p
                LEFT JOIN tbl_usuarios u ON p.id_perfil = u.id_perfil AND u.ativo = 1
                WHERE p.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " GROUP BY p.id_perfil ORDER BY p.nome_perfil LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p WHERE p.ativo = 1";
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
     * Busca permissões de um perfil
     */
    public function getPermissoes($idPerfil) {
        $sql = "SELECT pp.*, p.nome_pagina, p.descricao as descricao_pagina,
                       pp.permissao_visualizar, pp.permissao_inserir, pp.permissao_editar, pp.permissao_excluir
                FROM tbl_perfil_paginas pp
                LEFT JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
                WHERE pp.id_perfil = ? AND pp.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPerfil]);
        return $stmt->fetchAll();
    }
    
    /**
     * Salva permissões de um perfil
     */
    public function salvarPermissoes($idPerfil, $permissoes) {
        // Primeiro, remove todas as permissões existentes
        $sql = "DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPerfil]);
        
        // Depois, insere as novas permissões
        if (!empty($permissoes)) {
            $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($permissoes as $pagina => $perms) {
                $idPagina = $this->getIdPagina($pagina);
                if ($idPagina) {
                    $stmt->execute([
                        $idPerfil,
                        $idPagina,
                        in_array('read', $perms) ? 1 : 0,
                        in_array('create', $perms) ? 1 : 0,
                        in_array('update', $perms) ? 1 : 0,
                        in_array('delete', $perms) ? 1 : 0
                    ]);
                }
            }
        }
    }
    
    /**
     * Busca ID da página pelo nome
     */
    private function getIdPagina($nomePagina) {
        $sql = "SELECT id_pagina FROM tbl_paginas WHERE nome_pagina = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nomePagina]);
        $result = $stmt->fetch();
        return $result ? $result['id_pagina'] : null;
    }
    
    /**
     * Busca todas as páginas disponíveis
     */
    public function getPaginas() {
        $sql = "SELECT * FROM tbl_paginas WHERE ativo = 1 ORDER BY nome_pagina";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca estatísticas dos perfis
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_perfis,
                    COUNT(CASE WHEN ativo = 1 THEN 1 END) as perfis_ativos,
                    COUNT(CASE WHEN ativo = 0 THEN 1 END) as perfis_inativos,
                    (SELECT COUNT(*) FROM tbl_usuarios WHERE ativo = 1) as total_usuarios,
                    (SELECT COUNT(DISTINCT id_perfil) FROM tbl_usuarios WHERE ativo = 1) as perfis_em_uso
                FROM {$this->table}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca perfis com contagem de usuários
     */
    public function findAllWithUserCount() {
        $sql = "SELECT p.*, 
                       COUNT(u.id_usuario) as total_usuarios
                FROM {$this->table} p
                LEFT JOIN tbl_usuarios u ON p.id_perfil = u.id_perfil AND u.ativo = 1
                WHERE p.ativo = 1
                GROUP BY p.id_perfil
                ORDER BY p.nome_perfil";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca perfil por nome
     */
    public function findByNome($nome) {
        $sql = "SELECT * FROM {$this->table} WHERE nome_perfil = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se o nome do perfil já existe
     */
    public function nomeExiste($nome, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nome_perfil = ? AND ativo = 1";
        $params = [$nome];
        
        if ($excludeId) {
            $sql .= " AND id_perfil != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o perfil pode ser excluído
     */
    public function podeExcluir($id) {
        // Verifica se há usuários usando este perfil
        $sql = "SELECT COUNT(*) as total FROM tbl_usuarios WHERE id_perfil = ? AND ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $usuarios = $stmt->fetch()['total'];
        
        return $usuarios == 0;
    }
    
    /**
     * Busca usuários de um perfil
     */
    public function getUsuarios($idPerfil) {
        $sql = "SELECT u.*, 
                       f.nome_filial
                FROM tbl_usuarios u
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial
                WHERE u.id_perfil = ? AND u.ativo = 1
                ORDER BY u.nome_completo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPerfil]);
        return $stmt->fetchAll();
    }
    
    /**
     * Insere perfis padrão do sistema
     */
    public function inserirPerfisPadrao() {
        $perfis = [
            [
                'nome_perfil' => 'Administrador',
                'descricao' => 'Acesso total ao sistema, pode gerenciar usuários, configurações e todos os módulos'
            ],
            [
                'nome_perfil' => 'Gerente',
                'descricao' => 'Acesso gerencial, pode visualizar relatórios e gerenciar estoque'
            ],
            [
                'nome_perfil' => 'Operador',
                'descricao' => 'Acesso operacional, pode registrar movimentações e consultar estoque'
            ],
            [
                'nome_perfil' => 'Visualizador',
                'descricao' => 'Acesso apenas para visualização, não pode fazer alterações'
            ]
        ];
        
        $inseridos = 0;
        foreach ($perfis as $perfil) {
            if (!$this->nomeExiste($perfil['nome_perfil'])) {
                $this->insert($perfil);
                $inseridos++;
            }
        }
        
        return $inseridos;
    }

    /**
     * Buscar permissões de páginas para um perfil
     */
    public function getPermissoesPaginas($idPerfil) {
        try {
            $sql = "SELECT 
                        pp.id_pagina,
                        pp.permissao_visualizar,
                        pp.permissao_inserir,
                        pp.permissao_editar,
                        pp.permissao_excluir
                    FROM tbl_perfil_paginas pp
                    WHERE pp.id_perfil = ? AND pp.ativo = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idPerfil]);
            $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converter para formato de array associativo
            $resultado = [];
            foreach ($permissoes as $permissao) {
                $resultado[$permissao['id_pagina']] = [
                    'permissao_visualizar' => (bool)$permissao['permissao_visualizar'],
                    'permissao_inserir' => (bool)$permissao['permissao_inserir'],
                    'permissao_editar' => (bool)$permissao['permissao_editar'],
                    'permissao_excluir' => (bool)$permissao['permissao_excluir']
                ];
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Atualizar permissões de páginas para um perfil
     */
    public function atualizarPermissoesPaginas($idPerfil, $permissoes) {
        try {
            $this->pdo->beginTransaction();
            
            // Primeiro, desativar todas as permissões existentes
            $sqlDesativar = "UPDATE tbl_perfil_paginas SET ativo = 0 WHERE id_perfil = ?";
            $stmt = $this->pdo->prepare($sqlDesativar);
            $stmt->execute([$idPerfil]);
            
            // Inserir/atualizar novas permissões
            $sqlInserir = "INSERT INTO tbl_perfil_paginas (
                                id_perfil, 
                                id_pagina, 
                                permissao_visualizar, 
                                permissao_inserir, 
                                permissao_editar, 
                                permissao_excluir, 
                                ativo, 
                                data_criacao
                            ) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                            ON DUPLICATE KEY UPDATE 
                                permissao_visualizar = VALUES(permissao_visualizar),
                                permissao_inserir = VALUES(permissao_inserir),
                                permissao_editar = VALUES(permissao_editar),
                                permissao_excluir = VALUES(permissao_excluir),
                                ativo = 1,
                                data_atualizacao = NOW()";
            
            $stmt = $this->pdo->prepare($sqlInserir);
            
            foreach ($permissoes as $permissao) {
                $stmt->execute([
                    $idPerfil,
                    $permissao['id_pagina'],
                    $permissao['permissao_visualizar'] ? 1 : 0,
                    $permissao['permissao_inserir'] ? 1 : 0,
                    $permissao['permissao_editar'] ? 1 : 0,
                    $permissao['permissao_excluir'] ? 1 : 0
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Verificar se usuário tem permissão para uma página específica
     */
    public function verificarPermissao($idPerfil, $idPagina, $tipoPermissao) {
        try {
            $sql = "SELECT $tipoPermissao 
                    FROM tbl_perfil_paginas 
                    WHERE id_perfil = ? AND id_pagina = ? AND ativo = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idPerfil, $idPagina]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado && $resultado[$tipoPermissao] == 1;
            
        } catch (Exception $e) {
            // Se der erro, retornar false (sem permissão)
            return false;
        }
    }
    
    /**
     * Buscar todas as permissões de um usuário (via perfil)
     */
    public function getPermissoesUsuario($idUsuario) {
        try {
            // Primeiro, buscar o perfil do usuário
            $sqlUsuario = "SELECT id_perfil FROM tbl_usuarios WHERE id_usuario = ? AND ativo = 1";
            $stmt = $this->pdo->prepare($sqlUsuario);
            $stmt->execute([$idUsuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario || !$usuario['id_perfil']) {
                return [];
            }
            
            // Buscar permissões do perfil
            return $this->getPermissoesPaginas($usuario['id_perfil']);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?> 