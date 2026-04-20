<?php
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

$current = basename($_SERVER['PHP_SELF'], '.php');

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem acesso à página atual
// IMPORTANTE: Só verificar se a página estiver registrada na tabela
$paginaAtual = basename($_SERVER['PHP_SELF']);
$paginaRegistrada = $controllerAcesso->verificarSePaginaRegistrada($paginaAtual);

if ($paginaRegistrada) {
    // Só verificar acesso se a página estiver registrada
    if (!$controllerAcesso->verificarAcessoPagina()) {
        $controllerAcesso->redirecionarSemPermissao('Acesso negado a esta página');
    }
    
    // Registrar acesso à página
    $controllerAcesso->registrarAcessoPagina();
}

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
                <?php if (!empty($menuUsuario)): ?>
                    <?php foreach ($menuUsuario as $categoria => $dados): ?>
                        <!-- Categoria: <?php echo htmlspecialchars($dados['nome']); ?> -->
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                                <span><?php echo htmlspecialchars($dados['nome']); ?></span>
                            </h6>
                        </li>
                        
                        <?php foreach ($dados['paginas'] as $pagina): ?>
                            <li class="nav-item">
                                <a class="nav-link<?php if($current == basename($pagina['url_pagina'], '.php')) echo ' active'; ?>" 
                                   href="<?php echo htmlspecialchars($pagina['url_pagina']); ?>">
                                    <i class="<?php echo htmlspecialchars($pagina['icone'] ?? 'bi-circle'); ?> me-2"></i>
                                    <?php echo htmlspecialchars($pagina['nome_pagina']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Menu vazio - mostrar mensagem -->
                    <li class="nav-item">
                        <div class="text-muted text-center p-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Nenhuma página disponível
                        </div>
                    </li>
                <?php endif; ?>
                
                <!-- SAIR -->
                <li class="nav-item">
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