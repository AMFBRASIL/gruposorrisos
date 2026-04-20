<?php
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

$current = basename($_SERVER['PHP_SELF'], '.php');

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem acesso à página atual
if (!$controllerAcesso->verificarAcessoPagina()) {
    $controllerAcesso->redirecionarSemPermissao('Acesso negado a esta página');
}

// Registrar acesso à página
$controllerAcesso->registrarAcessoPagina();

// Obter menu baseado nas permissões
$menuUsuario = $controllerAcesso->obterMenuUsuario();

// Debug: mostrar menu obtido
echo "<!-- DEBUG: Menu obtido: " . print_r($menuUsuario, true) . " -->";
?>
<!-- Botão de Toggle para Mobile -->
<button class="btn btn-primary d-md-none position-fixed" 
        style="top: 10px; left: 10px; z-index: 1050;" 
        type="button" 
        data-bs-toggle="offcanvas" 
        data-bs-target="#sidebarMenu" 
        aria-controls="sidebarMenu">
    <i class="bi bi-list"></i>
</button>

<!-- Overlay para fechar o menu no mobile -->
<div class="offcanvas-backdrop fade d-md-none" id="sidebarBackdrop" style="display: none;"></div>

<!-- Sidebar Responsivo -->
<div class="offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header d-md-none">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 1.2rem;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <span class="fw-bold">GRUPO SORRISOS</span>
            </div>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-0" style="overflow-y: auto;">
        <nav class="sidebar p-3">
            <div class="d-flex align-items-center mb-4 d-none d-md-flex">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.5rem;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <span class="ms-2 fw-bold fs-5"> GRUPO | SORRISOS </span>
            </div>
            
            <ul class="nav flex-column">
                <!-- GRUPO: DASHBOARD -->
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='index') echo ' active'; ?>" href="index">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i>Dashboard
                    </a>
                </li>
                
                <!-- GRUPO: GESTÃO DE ESTOQUE -->
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Gestão de Estoque</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='material'||$current=='material' || $current=='addMaterial') echo ' active'; ?>" href="material">
                        <i class="bi bi-box me-2"></i>Material
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='categorias') echo ' active'; ?>" href="categorias">
                        <i class="bi bi-tags me-2"></i>Categorias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='filiais'||$current=='filiais' || $current=='filiais' || $current=='addFilial') echo ' active'; ?>" href="filiais">
                        <i class="bi bi-building me-2"></i>Filiais
                    </a>
                </li>
                
                <!-- GRUPO: MOVIMENTAÇÕES -->
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Movimentações</span>
                    </h6>
                </li>
                <!---<li class="nav-item">
                    <a class="nav-link<?php //if($current=='entrada-estoque') echo ' active'; ?>" href="entrada-estoque">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Entrada Estoque
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php //if($current=='saida-estoque') echo ' active'; ?>" href="saida-estoque">
                        <i class="bi bi-box-arrow-up me-2"></i>Saída Estoque
                    </a>
                </li>--->
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='movimentacao'||$current=='movimentacoes'||$current=='addMovimentacao') echo ' active'; ?>" href="movimentacoes">
                        <i class="bi bi-arrow-left-right me-2"></i>Movimentação
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='inventario') echo ' active'; ?>" href="inventario">
                        <i class="bi bi-clipboard-data me-2"></i>Inventário
                    </a>
                </li>
                
                <!-- GRUPO: FORNECEDORES E COMPRAS -->
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Fornecedores & Compras</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='fornecedores'||$current=='addFornecedor') echo ' active'; ?>" href="fornecedores">
                        <i class="bi bi-truck me-2"></i>Fornecedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='pedidos-compra') echo ' active'; ?>" href="pedidos-compra">
                        <i class="bi bi-cart-check me-2"></i>Pedidos de Compra
                    </a>
                </li>
                
                <!-- GRUPO: FINANCEIRO -->
                <!---<li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Financeiro</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='contas-pagar') echo ' active'; ?>" href="contas-pagar">
                        <i class="bi bi-file-earmark-text me-2"></i>Contas a Pagar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='contas-receber') echo ' active'; ?>" href="contas-receber">
                        <i class="bi bi-file-earmark-text me-2"></i>Contas a Receber
                    </a>
                </li>--->
                
                <!-- GRUPO: RELATÓRIOS E MONITORAMENTO -->
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Relatórios & Monitoramento</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='relatorios') echo ' active'; ?>" href="relatorios">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>Relatórios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='alertas') echo ' active'; ?>" href="alertas">
                        <i class="bi bi-exclamation-triangle me-2"></i>Alertas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='logs') echo ' active'; ?>" href="logs">
                        <i class="bi bi-clock-history me-2"></i>Logs
                    </a>
                </li>
                
                <!-- GRUPO: ADMINISTRAÇÃO -->
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Administração</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='usuarios') echo ' active'; ?>" href="usuarios">
                        <i class="bi bi-people me-2"></i>Usuários
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='perfil-acesso') echo ' active'; ?>" href="perfil-acesso">
                        <i class="bi bi-shield-lock me-2"></i>Perfil Acesso
                    </a>
                </li>
               
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='tickets') echo ' active'; ?>" href="tickets">
                        <i class="bi bi-chat-dots me-2"></i>Tickets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if($current=='configuracoes') echo ' active'; ?>" href="configuracoes">
                        <i class="bi bi-gear me-2"></i>Configurações
                    </a>
                </li>
                
                <!-- SAIR -->
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="#" onclick="logout()">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<style>
/* Estilos responsivos para o sidebar */
.sidebar {
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

/* Desktop: altura natural com scroll quando necessário */
@media (min-width: 768px) {
    .sidebar {
        min-height: 100vh;
        max-height: 100vh;
        overflow-y: auto;
    }
}

/* Mobile: altura fixa com scroll se necessário */
@media (max-width: 767.98px) {
    .sidebar {
        min-height: 100vh;
        max-height: 100vh;
        overflow-y: auto;
    }
}

/* Desktop: sidebar fixo */
@media (min-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        overflow-y: auto;
        z-index: 1000;
    }
    
    /* Ajustar o conteúdo principal */
    main {
        margin-left: 280px !important;
    }
    
    /* Esconder botão de toggle no desktop */
    .btn-primary[data-bs-toggle="offcanvas"] {
        display: none !important;
    }
}

/* Mobile: sidebar como offcanvas */
@media (max-width: 767.98px) {
    .sidebar {
        background: #fff;
        border-right: none;
        height: 100vh;
        overflow-y: auto;
    }
    
    /* Ajustar o conteúdo principal no mobile */
    main {
        margin-left: 0 !important;
        padding-top: 60px !important;
    }
    
    /* Botão de toggle visível no mobile */
    .btn-primary[data-bs-toggle="offcanvas"] {
        display: block !important;
    }
}

.sidebar-heading {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.nav-link {
    color: #6c757d;
    transition: all 0.3s ease;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
    padding: 0.75rem 1rem;
}

.nav-link:hover {
    color: #495057;
    background-color: #f8f9fa;
}

.nav-link.active {
    color: #fff;
    background-color: #0d6efd;
}

.nav-link.active:hover {
    color: #fff;
    background-color: #0b5ed7;
}

/* Melhorar espaçamento no mobile */
@media (max-width: 767.98px) {
    .nav-link {
        padding: 1rem;
        font-size: 1.1rem;
    }
    
    .sidebar-heading {
        font-size: 0.8rem;
        padding: 1rem 1rem 0.5rem 1rem;
    }
}

/* Animações suaves */
.offcanvas {
    transition: transform 0.3s ease-in-out;
}

.offcanvas-backdrop {
    transition: opacity 0.3s ease-in-out;
}

/* Scroll invisível mas funcional */
.sidebar::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: transparent;
}

/* Firefox - scroll invisível */
.sidebar {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

/* Desktop: scroll apenas quando necessário */
@media (min-width: 768px) {
    .offcanvas-body {
        overflow-y: auto !important;
    }
    
    .sidebar {
        overflow-y: auto !important;
    }
}
</style>

<script>
// Função de logout
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        // Fazer requisição para logout
        fetch('backend/api/logout.php', {
            method: 'POST'
        }).finally(() => {
            // Redirecionar para login
            window.location.href = 'login.php';
        });
    }
}

// Fechar menu automaticamente no mobile após clicar em um link
document.addEventListener('DOMContentLoaded', function() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    const navLinks = sidebarMenu.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Fechar o offcanvas no mobile
            if (window.innerWidth < 768) {
                const offcanvas = bootstrap.Offcanvas.getInstance(sidebarMenu);
                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        });
    });
    
    // Fechar menu ao clicar fora (apenas no mobile)
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 768) {
            const sidebarMenu = document.getElementById('sidebarMenu');
            const toggleButton = document.querySelector('[data-bs-toggle="offcanvas"]');
            
            if (!sidebarMenu.contains(event.target) && !toggleButton.contains(event.target)) {
                const offcanvas = bootstrap.Offcanvas.getInstance(sidebarMenu);
                if (offcanvas && offcanvas._isShown) {
                    offcanvas.hide();
                }
            }
        }
    });
});
</script>