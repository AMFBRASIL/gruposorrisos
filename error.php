<?php
/**
 * Página de Erro - Sistema de Controle de Acesso
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obter mensagem de erro da URL
$mensagem = $_GET['message'] ?? 'Erro desconhecido';
$tipo = $_GET['tipo'] ?? 'error';
$codigo = $_GET['codigo'] ?? '403';

// Tratamento específico para erro de horário
if ($tipo === 'horario') {
    $codigo = '403';
    $tipo = 'warning';
    $mensagem = $_SESSION['erro_horario'] ?? 'Sistema fora do horário de funcionamento.';
    // Limpar mensagem da sessão
    unset($_SESSION['erro_horario']);
}

// Definir ícones e cores baseados no tipo
$icones = [
    'error' => 'bi-exclamation-triangle-fill',
    'warning' => 'bi-exclamation-triangle',
    'info' => 'bi-info-circle',
    'success' => 'bi-check-circle'
];

$cores = [
    'error' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
    'success' => 'success'
];

$icone = $icones[$tipo] ?? $icones['error'];
$cor = $cores[$tipo] ?? $cores['error'];

// Verificar se o usuário está logado
$usuarioLogado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfilUsuario = $_SESSION['usuario_perfil'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .user-info h6 {
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .user-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }
        
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            text-align: left;
            font-size: 0.9rem;
        }
        
        .error-details h6 {
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .error-details ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .error-code {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Ícone de erro -->
        <i class="bi <?= $icone ?> text-<?= $cor ?> error-icon"></i>
        
        <!-- Código de erro -->
        <div class="error-code text-<?= $cor ?>"><?= $codigo ?></div>
        
        <!-- Título do erro -->
        <h1 class="error-title">
            <?php
            if ($tipo === 'warning' && isset($_GET['tipo']) && $_GET['tipo'] === 'horario') {
                echo 'Sistema Fora do Horário';
            } else {
                switch ($codigo) {
                    case '403':
                        echo 'Acesso Negado';
                        break;
                    case '404':
                        echo 'Página Não Encontrada';
                        break;
                    case '500':
                        echo 'Erro Interno do Servidor';
                        break;
                    default:
                        echo 'Erro do Sistema';
                }
            }
            ?>
        </h1>
        
        <!-- Mensagem de erro -->
        <p class="error-message"><?= htmlspecialchars($mensagem) ?></p>
        
        <!-- Informações do usuário (se logado) -->
        <?php if ($usuarioLogado): ?>
        <div class="user-info">
            <h6><i class="bi bi-person-circle me-2"></i>Informações do Usuário</h6>
            <p><strong>Nome:</strong> <?= htmlspecialchars($nomeUsuario) ?></p>
            <p><strong>Perfil:</strong> <?= htmlspecialchars($perfilUsuario) ?></p>
            <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s') ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Botões de ação -->
        <div class="btn-group">
            <?php if ($usuarioLogado): ?>
                <?php if (isset($_GET['tipo']) && $_GET['tipo'] === 'horario'): ?>
                    <a href="#" onclick="location.reload()" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Tentar Novamente
                    </a>
                    
                    <a href="configuracoes.php" class="btn btn-outline-secondary">
                        <i class="bi bi-clock me-2"></i>Ver Configurações
                    </a>
                    
                    <a href="#" onclick="logout()" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Voltar ao Início
                    </a>
                    
                    <?php if ($codigo === '403'): ?>
                    <a href="perfil-acesso.php" class="btn btn-outline-secondary">
                        <i class="bi bi-shield-lock me-2"></i>Verificar Permissões
                    </a>
                    <?php endif; ?>
                    
                    <a href="#" onclick="logout()" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Fazer Login
                </a>
                
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-2"></i>Página Inicial
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Detalhes técnicos -->
        <div class="error-details">
            <h6><i class="bi bi-info-circle me-2"></i>Detalhes Técnicos</h6>
            <ul>
                <li><strong>Código de Erro:</strong> <?= $codigo ?></li>
                <li><strong>Tipo:</strong> <?= ucfirst($tipo) ?></li>
                <li><strong>URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></li>
                <li><strong>IP:</strong> <?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?></li>
                <li><strong>User Agent:</strong> <?= htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 100)) ?></li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
        
        // Adicionar efeito de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.error-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.5s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
