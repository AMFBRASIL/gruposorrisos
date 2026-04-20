<?php
require_once 'BaseModel.php';

class Usuario extends BaseModel {
    protected $table = 'tbl_usuarios';
    protected $primaryKey = 'id_usuario';
    
    /**
     * Autentica um usuário
     */
    public function autenticar($email, $senha) {
        $sql = "SELECT u.id_usuario,
                       u.nome_completo,
                       u.email,
                       u.cpf,
                       u.telefone,
                       u.senha,
                       u.id_perfil,
                       u.id_filial,
                       u.ativo,
                       u.ultimo_acesso,
                       u.data_criacao,
                       u.data_atualizacao,
                       p.nome_perfil,
                       f.nome_filial,
                       f.codigo_filial,
                       f.id_filial as filial_id
                FROM {$this->table} u
                LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial
                WHERE u.email = ? AND u.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Atualiza último acesso
            $this->atualizarUltimoAcesso($usuario['id_usuario']);
            
            // Remove a senha do array antes de retornar
            unset($usuario['senha']);
            
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Atualiza o último acesso do usuário
     */
    private function atualizarUltimoAcesso($idUsuario) {
        $sql = "UPDATE {$this->table} SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
    }
    
    /**
     * Busca usuário por email
     */
    public function findByEmail($email) {
        $sql = "SELECT u.id_usuario,
                       u.nome_completo,
                       u.email,
                       u.cpf,
                       u.telefone,
                       u.senha,
                       u.id_perfil,
                       u.id_filial,
                       u.ativo,
                       u.ultimo_acesso,
                       u.data_criacao,
                       u.data_atualizacao,
                       p.nome_perfil,
                       f.nome_filial,
                       f.id_filial as filial_id
                FROM {$this->table} u
                LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial
                WHERE u.email = ? AND u.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Busca usuários com informações relacionadas
     */
    public function findAllWithRelations($where = '', $params = []) {
        $sql = "SELECT u.id_usuario,
                       u.nome_completo,
                       u.email,
                       u.cpf,
                       u.telefone,
                       u.senha,
                       u.id_perfil,
                       u.id_filial,
                       u.ativo,
                       u.ultimo_acesso,
                       u.data_criacao,
                       u.data_atualizacao,
                       p.nome_perfil,
                       f.nome_filial,
                       f.id_filial as filial_id
                FROM {$this->table} u
                LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial
                WHERE u.ativo = 1";
        
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY u.nome_completo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca usuário por ID com informações relacionadas
     */
    public function findByIdWithRelations($id) {
        $sql = "SELECT u.id_usuario,
                       u.nome_completo,
                       u.email,
                       u.cpf,
                       u.telefone,
                       u.senha,
                       u.id_perfil,
                       u.id_filial,
                       u.ativo,
                       u.ultimo_acesso,
                       u.data_criacao,
                       u.data_atualizacao,
                       p.nome_perfil,
                       f.nome_filial,
                       f.id_filial as filial_id
                FROM {$this->table} u
                LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial
                WHERE u.id_usuario = ? AND u.ativo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica se o email já existe
     */
    public function emailExiste($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ? AND ativo = 1";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verifica se o CPF já existe
     */
    public function cpfExiste($cpf, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE cpf = ? AND ativo = 1";
        $params = [$cpf];
        
        if ($excludeId) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Cria um novo usuário
     */
    public function criarUsuario($dados) {
        // Criptografa a senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        return $this->insert($dados);
    }
    
    /**
     * Atualiza um usuário
     */
    public function atualizarUsuario($id, $dados) {
        // Se uma nova senha foi fornecida, criptografa
        if (!empty($dados['senha'])) {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        } else {
            unset($dados['senha']);
        }
        
        return $this->update($id, $dados);
    }
    
    /**
     * Altera a senha de um usuário
     */
    public function alterarSenha($idUsuario, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->table} SET senha = ? WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$senhaHash, $idUsuario]);
    }
    
    /**
     * Busca usuários por filial
     */
    public function findByFilial($idFilial) {
        return $this->findAllWithRelations('u.id_filial = ?', [$idFilial]);
    }
    
    /**
     * Busca usuários por perfil
     */
    public function findByPerfil($idPerfil) {
        return $this->findAllWithRelations('u.id_perfil = ?', [$idPerfil]);
    }
    
    /**
     * Busca usuários com filtros e paginação
     */
    public function findWithPagination($page = 1, $limit = 10, $where = '', $params = []) {
        $offset = ($page - 1) * $limit;
        
        // Query principal com JOINs
        $sql = "SELECT u.id_usuario,
                       u.nome_completo,
                       u.email,
                       u.cpf,
                       u.telefone,
                       u.senha,
                       u.id_perfil,
                       u.id_filial,
                       u.ativo,
                       u.ultimo_acesso,
                       u.data_criacao,
                       u.data_atualizacao,
                       p.nome_perfil,
                       f.nome_filial,
                       f.id_filial as filial_id
                FROM {$this->table} u
                LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil
                LEFT JOIN tbl_filiais f ON u.id_filial = f.id_filial";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY u.nome_completo LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($allParams);
        $data = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} u";
        if (!empty($where)) {
            $countSql .= " WHERE " . $where;
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
     * Busca usuários com filtros
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 10, $search = '') {
        $where = [];
        $params = [];
        
        if (!empty($filters['nome_completo'])) {
            $where[] = 'u.nome_completo LIKE ?';
            $params[] = '%' . $filters['nome_completo'] . '%';
        }
        
        if (!empty($filters['email'])) {
            $where[] = 'u.email LIKE ?';
            $params[] = '%' . $filters['email'] . '%';
        }
        
        if (!empty($filters['id_filial'])) {
            $where[] = 'u.id_filial = ?';
            $params[] = $filters['id_filial'];
        }
        
        if (!empty($filters['id_perfil'])) {
            $where[] = 'u.id_perfil = ?';
            $params[] = $filters['id_perfil'];
        }
        
        if (isset($filters['ativo'])) {
            $where[] = 'u.ativo = ?';
            $params[] = $filters['ativo'];
        }
        
        // Busca geral
        if (!empty($search)) {
            $where[] = "(u.nome_completo LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($page, $limit, $whereClause, $params);
    }
    
    /**
     * Busca estatísticas de usuários
     */
    public function getEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_usuarios,
                    COUNT(CASE WHEN ativo = 1 THEN 1 END) as usuarios_ativos,
                    COUNT(CASE WHEN ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as usuarios_ativos_7dias,
                    COUNT(CASE WHEN ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as usuarios_ativos_30dias
                FROM {$this->table}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Cria usuário administrador padrão
     */
    public function criarUsuarioAdmin() {
        // Verifica se já existe um usuário admin
        $admin = $this->findByEmail('admin@sistema.com');
        if ($admin) {
            return false; // Já existe
        }
        
        // Busca perfil administrador
        $sql = "SELECT id_perfil FROM tbl_perfis WHERE nome_perfil LIKE '%admin%' OR nome_perfil LIKE '%administrador%' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $perfil = $stmt->fetch();
        
        // Busca filial matriz
        $sql = "SELECT id_filial FROM tbl_filiais WHERE codigo_filial = 'MATRIZ' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $filial = $stmt->fetch();
        
        $dadosAdmin = [
            'nome_completo' => 'Administrador do Sistema',
            'email' => 'admin@sistema.com',
            'senha' => 'password', // Será criptografada no método criarUsuario
            'cpf' => '000.000.000-00',
            'telefone' => '(11) 99999-9999',
            'id_perfil' => $perfil ? $perfil['id_perfil'] : 1,
            'id_filial' => $filial ? $filial['id_filial'] : 1,
            'ativo' => 1
        ];
        
        return $this->criarUsuario($dadosAdmin);
    }



    /**
     * Conta total de usuÃ¡rios
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Conta usuÃ¡rios ativos
     */
    public function countAtivos() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Conta usuÃ¡rios por perfil
     */
    public function countByPerfil() {
        $sql = "SELECT p.nome_perfil, COUNT(u.id_usuario) as total
                FROM tbl_perfis p
                LEFT JOIN {$this->table} u ON p.id_perfil = u.id_perfil AND u.ativo = 1
                WHERE p.ativo = 1
                GROUP BY p.id_perfil, p.nome_perfil
                ORDER BY p.nome_perfil";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Conta usuÃ¡rios por filial
     */
    public function countByFilial() {
        $sql = "SELECT f.nome_filial, COUNT(u.id_usuario) as total
                FROM tbl_filiais f
                LEFT JOIN {$this->table} u ON f.id_filial = u.id_filial AND u.ativo = 1
                WHERE f.filial_ativa = 1
                GROUP BY f.id_filial, f.nome_filial
                ORDER BY f.nome_filial";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?> 