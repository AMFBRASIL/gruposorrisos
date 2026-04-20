<?php
/**
 * Controller de Controle de Acesso e Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Esta controller gerencia todas as permissões e controles de acesso
 * das páginas do sistema baseado nos perfis dos usuários
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

class ControllerPermissoes {
    private $pdo;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }
    
    /**
     * Verifica se o usuário tem permissão para acessar uma página específica
     * @param int $idUsuario ID do usuário
     * @param string $urlPagina URL da página a ser verificada
     * @param string $acao Ação a ser verificada (visualizar, inserir, editar, excluir)
     * @return bool True se tem permissão, False caso contrário
     */
    public function verificarPermissao($idUsuario, $urlPagina, $acao = 'visualizar') {
        try {
            // Buscar perfil do usuário
            $sql = "SELECT u.id_perfil, p.nome_perfil 
                    FROM tbl_usuarios u 
                    JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
                    WHERE u.id_usuario = ? AND u.ativo = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idUsuario]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                return false;
            }
            
            // Se for administrador, tem acesso total
            if (strtolower($usuario['nome_perfil']) === 'administrador') {
                return true;
            }
            
            // Buscar permissão específica
            $sql = "SELECT pp.permissao_visualizar, pp.permissao_inserir, 
                           pp.permissao_editar, pp.permissao_excluir
                    FROM tbl_perfil_paginas pp
                    JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
                    WHERE pp.id_perfil = ? AND p.url_pagina = ? AND pp.ativo = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario['id_perfil'], $urlPagina]);
            $permissao = $stmt->fetch();
            
            if (!$permissao) {
                return false;
            }
            
            // Verificar permissão específica
            switch ($acao) {
                case 'visualizar':
                    return (bool)$permissao['permissao_visualizar'];
                case 'inserir':
                    return (bool)$permissao['permissao_inserir'];
                case 'editar':
                    return (bool)$permissao['permissao_editar'];
                case 'excluir':
                    return (bool)$permissao['permissao_excluir'];
                default:
                    return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao verificar permissão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se o usuário logado tem permissão para acessar uma página
     * @param string $urlPagina URL da página a ser verificada
     * @param string $acao Ação a ser verificada
     * @return bool True se tem permissão, False caso contrário
     */
    public function verificarPermissaoUsuarioLogado($urlPagina, $acao = 'visualizar') {
        if (!isLoggedIn()) {
            return false;
        }
        
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        if (!$idUsuario) {
            return false;
        }
        
        return $this->verificarPermissao($idUsuario, $urlPagina, $acao);
    }
    
    /**
     * Obtém todas as permissões de um perfil específico
     * @param int $idPerfil ID do perfil
     * @return array Array com as permissões
     */
    public function obterPermissoesPerfil($idPerfil) {
        try {
            $sql = "SELECT pp.*, p.nome_pagina, p.url_pagina, p.descricao, p.categoria, p.icone, p.ordem
                    FROM tbl_perfil_paginas pp
                    JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
                    WHERE pp.id_perfil = ? AND pp.ativo = 1 AND p.ativo = 1
                    ORDER BY p.ordem, p.nome_pagina";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idPerfil]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter permissões do perfil: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém todas as permissões do usuário logado
     * @return array Array com as permissões
     */
    public function obterPermissoesUsuarioLogado() {
        if (!isLoggedIn()) {
            return [];
        }
        
        $idPerfil = $_SESSION['usuario_perfil_id'] ?? null;
        if (!$idPerfil) {
            return [];
        }
        
        return $this->obterPermissoesPerfil($idPerfil);
    }
    
    /**
     * Obtém todas as páginas que o usuário pode acessar
     * @return array Array com as páginas permitidas
     */
    public function obterPaginasPermitidas() {
        $permissoes = $this->obterPermissoesUsuarioLogado();
        $paginas = [];
        
        foreach ($permissoes as $permissao) {
            if (isset($permissao['permissao_visualizar']) && $permissao['permissao_visualizar']) {
                $paginas[] = [
                    'id_pagina' => $permissao['id_pagina'],
                    'nome_pagina' => $permissao['nome_pagina'],
                    'url_pagina' => $permissao['url_pagina'],
                    'descricao' => $permissao['descricao'],
                    'categoria' => $permissao['categoria'],
                    'icone' => $permissao['icone'],
                    'ordem' => $permissao['ordem'],
                    'pode_inserir' => (bool)($permissao['permissao_inserir'] ?? false),
                    'pode_editar' => (bool)($permissao['permissao_editar'] ?? false),
                    'pode_excluir' => (bool)($permissao['permissao_excluir'] ?? false)
                ];
            }
        }
        
        return $paginas;
    }
    
    /**
     * Obtém páginas agrupadas por categoria
     * @return array Array com páginas agrupadas por categoria
     */
    public function obterPaginasPorCategoria() {
        $paginas = $this->obterPaginasPermitidas();
        $categorias = [];
        
        foreach ($paginas as $pagina) {
            $categoria = $pagina['categoria'] ?? 'outros';
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [];
            }
            $categorias[$categoria][] = $pagina;
        }
        
        // Ordenar páginas dentro de cada categoria
        foreach ($categorias as &$paginasCategoria) {
            usort($paginasCategoria, function($a, $b) {
                return $a['ordem'] <=> $b['ordem'];
            });
        }
        
        return $categorias;
    }
    
    /**
     * Salva as permissões de um perfil
     * @param int $idPerfil ID do perfil
     * @param array $permissoes Array com as permissões
     * @return bool True se salvou com sucesso, False caso contrário
     */
    public function salvarPermissoesPerfil($idPerfil, $permissoes) {
        try {
            $this->pdo->beginTransaction();
            
            // Desativar todas as permissões existentes
            $sql = "UPDATE tbl_perfil_paginas SET ativo = 0 WHERE id_perfil = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idPerfil]);
            
            // Inserir novas permissões
            $sql = "INSERT INTO tbl_perfil_paginas (
                        id_perfil, id_pagina, permissao_visualizar, permissao_inserir, 
                        permissao_editar, permissao_excluir, ativo, data_criacao
                    ) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE 
                        permissao_visualizar = VALUES(permissao_visualizar),
                        permissao_inserir = VALUES(permissao_inserir),
                        permissao_editar = VALUES(permissao_editar),
                        permissao_excluir = VALUES(permissao_excluir),
                        ativo = 1,
                        data_atualizacao = NOW()";
            
            $stmt = $this->pdo->prepare($sql);
            
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
            error_log("Erro ao salvar permissões: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém estatísticas de permissões
     * @return array Array com estatísticas
     */
    public function obterEstatisticasPermissoes() {
        try {
            $sql = "SELECT 
                        p.nome_perfil,
                        COUNT(pp.id_perfil_pagina) as total_paginas,
                        SUM(pp.permissao_visualizar) as pode_visualizar,
                        SUM(pp.permissao_inserir) as pode_inserir,
                        SUM(pp.permissao_editar) as pode_editar,
                        SUM(pp.permissao_excluir) as pode_excluir
                    FROM tbl_perfis p
                    LEFT JOIN tbl_perfil_paginas pp ON p.id_perfil = pp.id_perfil AND pp.ativo = 1
                    WHERE p.ativo = 1
                    GROUP BY p.id_perfil, p.nome_perfil
                    ORDER BY p.nome_perfil";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém todas as páginas disponíveis no sistema
     * @return array Array com todas as páginas
     */
    public function obterTodasPaginas() {
        try {
            $sql = "SELECT id_pagina, nome_pagina, url_pagina, descricao, categoria, icone, ordem, ativo
                    FROM tbl_paginas 
                    WHERE ativo = 1 
                    ORDER BY ordem, nome_pagina";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter páginas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém todos os perfis disponíveis
     * @return array Array com todos os perfis
     */
    public function obterTodosPerfis() {
        try {
            $sql = "SELECT id_perfil, nome_perfil, descricao, ativo
                    FROM tbl_perfis 
                    WHERE ativo = 1 
                    ORDER BY nome_perfil";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter perfis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica se uma página específica existe e está ativa
     * @param string $urlPagina URL da página
     * @return bool True se existe e está ativa, False caso contrário
     */
    public function paginaExiste($urlPagina) {
        try {
            $sql = "SELECT COUNT(*) as total FROM tbl_paginas WHERE url_pagina = ? AND ativo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$urlPagina]);
            
            $resultado = $stmt->fetch();
            return $resultado['total'] > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar página: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra acesso a uma página
     * @param int $idUsuario ID do usuário
     * @param int $idPagina ID da página
     * @return bool True se registrou com sucesso, False caso contrário
     */
    public function registrarAcesso($idUsuario, $idPagina) {
        try {
            $sql = "INSERT INTO tbl_paginas_acesso (id_pagina, id_usuario, data_acesso, ip_acesso, user_agent) 
                    VALUES (?, ?, NOW(), ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $idPagina,
                $idUsuario,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao registrar acesso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém histórico de acessos de um usuário
     * @param int $idUsuario ID do usuário
     * @param int $limite Limite de registros
     * @return array Array com histórico de acessos
     */
    public function obterHistoricoAcessos($idUsuario, $limite = 50) {
        try {
            $sql = "SELECT pa.*, p.nome_pagina, p.url_pagina
                    FROM tbl_paginas_acesso pa
                    JOIN tbl_paginas p ON pa.id_pagina = p.id_pagina
                    WHERE pa.id_usuario = ?
                    ORDER BY pa.data_acesso DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idUsuario, $limite]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter histórico: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica se o usuário tem permissão para executar uma ação específica
     * @param string $acao Ação a ser verificada
     * @param string $urlPagina URL da página (opcional)
     * @return bool True se tem permissão, False caso contrário
     */
    public function podeExecutarAcao($acao, $urlPagina = null) {
        if (!isLoggedIn()) {
            return false;
        }
        
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        if (!$urlPagina) {
            $urlPagina = basename($_SERVER['PHP_SELF']);
        }
        
        return $this->verificarPermissao($idUsuario, $urlPagina, $acao);
    }
    
    /**
     * Obtém permissões resumidas do usuário logado
     * @return array Array com permissões resumidas
     */
    public function obterPermissoesResumidas() {
        $permissoes = $this->obterPermissoesUsuarioLogado();
        $resumo = [
            'total_paginas' => count($permissoes),
            'pode_inserir' => 0,
            'pode_editar' => 0,
            'pode_excluir' => 0,
            'categorias' => []
        ];
        
        foreach ($permissoes as $permissao) {
            // Usar as chaves corretas do array retornado por obterPermissoesUsuarioLogado
            if (isset($permissao['permissao_inserir']) && $permissao['permissao_inserir']) {
                $resumo['pode_inserir']++;
            }
            if (isset($permissao['permissao_editar']) && $permissao['permissao_editar']) {
                $resumo['pode_editar']++;
            }
            if (isset($permissao['permissao_excluir']) && $permissao['permissao_excluir']) {
                $resumo['pode_excluir']++;
            }
            
            $categoria = $permissao['categoria'] ?? 'outros';
            if (!isset($resumo['categorias'][$categoria])) {
                $resumo['categorias'][$categoria] = 0;
            }
            $resumo['categorias'][$categoria]++;
        }
        
        return $resumo;
    }
}
?> 