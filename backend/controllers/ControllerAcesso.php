<?php
/**
 * Controller de Controle de Acesso às Páginas
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Esta controller gerencia o controle de acesso às páginas
 * baseado nas permissões dos usuários
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/ControllerPermissoes.php';

class ControllerAcesso {
    private $pdo;
    private $controllerPermissoes;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->controllerPermissoes = new ControllerPermissoes();
    }
    
    /**
     * Verifica se o usuário pode acessar a página atual
     * @return bool True se pode acessar, False caso contrário
     */
    public function verificarAcessoPagina() {
        if (!isLoggedIn()) {
            return false;
        }
        
        $urlPagina = basename($_SERVER['PHP_SELF']);
        $temPermissao = $this->controllerPermissoes->verificarPermissaoUsuarioLogado($urlPagina, 'visualizar');
        
        if (!$temPermissao) {
            $mensagem = "Você não tem permissão para acessar a página {$urlPagina}";
            $codigo = '403';
            $tipo = 'warning';
            
            $this->redirecionarSemPermissao($mensagem, $codigo, $tipo);
        }
        
        return $temPermissao;
    }
    
    /**
     * Redireciona para página de erro se não tiver permissão
     * @param string $mensagem Mensagem de erro
     * @param string $codigo Código de erro HTTP
     * @param string $tipo Tipo de erro
     */
    public function redirecionarSemPermissao($mensagem = 'Acesso negado', $codigo = '403', $tipo = 'error') {
        // Registrar erro de acesso
        $this->registrarErroAcesso($mensagem, $codigo, $tipo);
        
        $url = "error.php?message=" . urlencode($mensagem) . 
               "&codigo=" . urlencode($codigo) . 
               "&tipo=" . urlencode($tipo);
        
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Registra erro de acesso no sistema
     * @param string $mensagem Mensagem de erro
     * @param string $codigo Código de erro
     * @param string $tipo Tipo de erro
     */
    private function registrarErroAcesso($mensagem, $codigo, $tipo) {
        try {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário não identificado';
            $urlPagina = $_SERVER['REQUEST_URI'] ?? 'N/A';
            $ipUsuario = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
            
            $dados = [
                'mensagem' => $mensagem,
                'codigo' => $codigo,
                'tipo' => $tipo,
                'url_pagina' => $urlPagina,
                'ip_usuario' => $ipUsuario,
                'user_agent' => $userAgent,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $sql = "INSERT INTO tbl_logs_sistema (id_usuario, acao, tabela, dados_novos, ip_usuario) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $usuarioId,
                'ERRO_ACESSO_' . $codigo,
                'sistema',
                json_encode($dados),
                $ipUsuario
            ]);
            
        } catch (Exception $e) {
            // Silenciosamente ignora erros de log
            error_log("Erro ao registrar log de acesso: " . $e->getMessage());
        }
    }
    
    /**
     * Verifica permissão e redireciona se necessário
     * @param string $acao Ação a ser verificada
     * @param string $urlPagina URL da página (opcional)
     * @param bool $redirecionar Se deve redirecionar em caso de erro
     * @return bool True se tem permissão, False caso contrário
     */
    public function verificarEAutorizar($acao = 'visualizar', $urlPagina = null, $redirecionar = true) {
        if (!$urlPagina) {
            $urlPagina = basename($_SERVER['PHP_SELF']);
        }
        
        $temPermissao = $this->controllerPermissoes->verificarPermissaoUsuarioLogado($urlPagina, $acao);
        
        if (!$temPermissao && $redirecionar) {
            $mensagem = "Sem permissão para {$acao} na página {$urlPagina}";
            $codigo = '403';
            $tipo = 'warning';
            
            $this->redirecionarSemPermissao($mensagem, $codigo, $tipo);
        }
        
        return $temPermissao;
    }
    
    /**
     * Obtém menu baseado nas permissões do usuário
     * @return array Array com itens do menu
     */
    public function obterMenuUsuario() {
        $paginas = $this->controllerPermissoes->obterPaginasPermitidas();
        $menu = [];
        
        foreach ($paginas as $pagina) {
            $categoria = $pagina['categoria'] ?? 'outros';
            if (!isset($menu[$categoria])) {
                $menu[$categoria] = [
                    'nome' => $this->obterNomeCategoria($categoria),
                    'icone' => $this->obterIconeCategoria($categoria),
                    'paginas' => []
                ];
            }
            
            $menu[$categoria]['paginas'][] = $pagina;
        }
        
        // Ordenar categorias
        uasort($menu, function($a, $b) {
            $ordemCategorias = [
                'gestao' => 1,
                'estoque' => 2,
                'compras' => 3,
                'relatorios' => 4,
                'configuracoes' => 5,
                'outros' => 99
            ];
            
            $ordemA = $ordemCategorias[$a['nome']] ?? 99;
            $ordemB = $ordemCategorias[$b['nome']] ?? 99;
            
            return $ordemA <=> $ordemB;
        });
        
        return $menu;
    }
    
    /**
     * Verifica se uma página está registrada na tabela tbl_paginas
     * @param string $urlPagina URL da página
     * @return bool True se está registrada, False caso contrário
     */
    public function verificarSePaginaRegistrada($urlPagina) {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_paginas WHERE url_pagina = ? AND ativo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$urlPagina]);
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar se página está registrada: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém nome amigável da categoria
     * @param string $categoria Categoria
     * @return string Nome amigável
     */
    private function obterNomeCategoria($categoria) {
        $nomes = [
            'gestao' => 'Gestão',
            'estoque' => 'Gestão de Estoque',
            'compras' => 'Fornecedores & Compras',
            'relatorios' => 'Relatórios & Monitoramento',
            'configuracoes' => 'Administração',
            'outros' => 'Outros'
        ];
        
        return $nomes[$categoria] ?? ucfirst($categoria);
    }
    
    /**
     * Obtém ícone da categoria
     * @param string $categoria Categoria
     * @return string Ícone
     */
    private function obterIconeCategoria($categoria) {
        $icones = [
            'gestao' => 'bi-gear',
            'estoque' => 'bi-box-seam',
            'compras' => 'bi-cart',
            'relatorios' => 'bi-graph-up',
            'configuracoes' => 'bi-gear',
            'outros' => 'bi-three-dots'
        ];
        
        return $icones[$categoria] ?? 'bi-three-dots';
    }
    
    /**
     * Verifica se o usuário pode executar uma ação específica
     * @param string $acao Ação
     * @param string $urlPagina URL da página
     * @return bool True se pode executar
     */
    public function podeExecutarAcao($acao, $urlPagina = null) {
        return $this->controllerPermissoes->podeExecutarAcao($acao, $urlPagina);
    }
    
    /**
     * Obtém botões de ação baseados nas permissões
     * @param string $urlPagina URL da página
     * @return array Array com botões permitidos
     */
    public function obterBotoesPermitidos($urlPagina = null) {
        if (!$urlPagina) {
            $urlPagina = basename($_SERVER['PHP_SELF']);
        }
        
        $botoes = [];
        
        if ($this->podeExecutarAcao('inserir', $urlPagina)) {
            $botoes[] = 'novo';
        }
        
        if ($this->podeExecutarAcao('editar', $urlPagina)) {
            $botoes[] = 'editar';
        }
        
        if ($this->podeExecutarAcao('excluir', $urlPagina)) {
            $botoes[] = 'excluir';
        }
        
        // Botões sempre permitidos para visualização
        $botoes[] = 'visualizar';
        $botoes[] = 'exportar';
        $botoes[] = 'imprimir';
        
        return $botoes;
    }
    
    /**
     * Renderiza o menu baseado nas permissões
     * @return string HTML do menu
     */
    public function renderizarMenu() {
        $menu = $this->obterMenuUsuario();
        $html = '';
        
        foreach ($menu as $categoria => $dados) {
            $html .= $this->renderizarCategoriaMenu($categoria, $dados);
        }
        
        return $html;
    }
    
    /**
     * Renderiza uma categoria do menu
     * @param string $categoria Categoria
     * @param array $dados Dados da categoria
     * @return string HTML da categoria
     */
    private function renderizarCategoriaMenu($categoria, $dados) {
        $html = '<li class="nav-item mt-3">';
        $html .= '<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">';
        $html .= '<span>' . htmlspecialchars($dados['nome']) . '</span>';
        $html .= '</h6>';
        $html .= '</li>';
        
        foreach ($dados['paginas'] as $pagina) {
            $html .= $this->renderizarItemMenu($pagina);
        }
        
        return $html;
    }
    
    /**
     * Renderiza um item do menu
     * @param array $pagina Dados da página
     * @return string HTML do item
     */
    private function renderizarItemMenu($pagina) {
        $current = basename($_SERVER['PHP_SELF'], '.php');
        $isActive = ($current == basename($pagina['url_pagina'], '.php')) ? ' active' : '';
        
        $html = '<li class="nav-item">';
        $html .= '<a class="nav-link' . $isActive . '" href="' . htmlspecialchars($pagina['url_pagina']) . '">';
        $html .= '<i class="' . htmlspecialchars($pagina['icone']) . ' me-2"></i>';
        $html .= htmlspecialchars($pagina['nome_pagina']);
        $html .= '</a>';
        $html .= '</li>';
        
        return $html;
    }
    
    /**
     * Verifica se deve mostrar um elemento baseado nas permissões
     * @param string $elemento Elemento a ser verificado
     * @param string $urlPagina URL da página
     * @return bool True se deve mostrar
     */
    public function deveMostrar($elemento, $urlPagina = null) {
        if (!$urlPagina) {
            $urlPagina = basename($_SERVER['PHP_SELF']);
        }
        
        switch ($elemento) {
            case 'botao_novo':
                return $this->podeExecutarAcao('inserir', $urlPagina);
            case 'botao_editar':
                return $this->podeExecutarAcao('editar', $urlPagina);
            case 'botao_excluir':
                return $this->podeExecutarAcao('excluir', $urlPagina);
            case 'formulario':
                return $this->podeExecutarAcao('inserir', $urlPagina) || 
                       $this->podeExecutarAcao('editar', $urlPagina);
            default:
                return true;
        }
    }
    
    /**
     * Obtém permissões resumidas para exibição
     * @return array Array com permissões resumidas
     */
    public function obterPermissoesResumidas() {
        return $this->controllerPermissoes->obterPermissoesResumidas();
    }
    
    /**
     * Registra acesso à página atual
     */
    public function registrarAcessoPagina() {
        if (!isLoggedIn()) {
            return;
        }
        
        $urlPagina = basename($_SERVER['PHP_SELF']);
        $paginas = $this->controllerPermissoes->obterTodasPaginas();
        
        foreach ($paginas as $pagina) {
            if ($pagina['url_pagina'] === $urlPagina) {
                $this->controllerPermissoes->registrarAcesso(
                    $_SESSION['usuario_id'],
                    $pagina['id_pagina']
                );
                break;
            }
        }
    }

    /**
     * Obtém a primeira página permitida para o usuário logado
     * @return string|null URL da primeira página permitida ou null se não houver
     */
    public function obterPrimeiraPaginaPermitida() {
        if (!isLoggedIn()) {
            return null;
        }
        
        try {
            $paginas = $this->obterMenuUsuario();
            
            // Procurar pela primeira página disponível
            foreach ($paginas as $categoria => $dadosCategoria) {
                if (isset($dadosCategoria['paginas']) && !empty($dadosCategoria['paginas'])) {
                    // Retornar a primeira página da primeira categoria
                    $primeiraPagina = $dadosCategoria['paginas'][0];
                    return $primeiraPagina['url_pagina'];
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro ao obter primeira página permitida: " . $e->getMessage());
            return null;
        }
    }
}
?> 