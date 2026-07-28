<?php
session_start();
require_once __DIR__ . '/config/url.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirecionamento inteligente baseado nas permissões
    require_once 'config/session.php';
    require_once 'backend/controllers/ControllerAcesso.php';
    
    try {
        $controllerAcesso = new ControllerAcesso();
        $primeiraPagina = $controllerAcesso->obterPrimeiraPaginaPermitida();
        
        if ($primeiraPagina) {
            header('Location: ' . $primeiraPagina);
        } else {
            redirect_to('index');
        }
        exit;
    } catch (Exception $e) {
        redirect_to('index');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Grupo Sorrisos Odontologia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/login.css') ?: time(); ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Lado esquerdo - Branding -->
            <div class="login-brand">
                <div class="brand-wave" aria-hidden="true"></div>
                <div class="brand-logo-card">
                    <img src="assets/img/logo-grupo-sorrisos.svg" alt="Grupo Sorrisos Odontologia">
                </div>
                <h1 class="brand-title">
                    <span class="brand-grupo">Grupo</span>
                    <span class="brand-sorrisos">Sorrisos</span>
                </h1>
                <p class="brand-subtitle">
                    Sistema de Gestão de Estoque Odontológico<br>
                    Tecnologia avançada para <strong>controle total</strong>
                </p>
            </div>

            <!-- Lado direito - Formulário -->
            <div class="login-form">
                <div class="form-header fade-in">
                    <h2 class="form-title">Bem-vindo <span class="accent">de volta</span></h2>
                    <p class="form-subtitle">Entre com suas credenciais para acessar o sistema</p>
                </div>

                <form id="loginForm" autocomplete="off">
                    <div id="alertContainer"></div>
                    
                    <div class="form-group fade-in">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-wrap">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="exemplo@gruposorrisos.com" required>
                        </div>
                    </div>

                    <div class="form-group fade-in">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Digite sua senha" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Mostrar senha">
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-links fade-in">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Lembrar de mim</label>
                        </div>
                        <a href="#" class="forgot-link">Esqueci minha senha</a>
                    </div>

                    <button type="submit" class="btn-login fade-in" id="loginBtn">
                        <span class="btn-text">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Entrar no Sistema
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <div class="spinner me-2"></div>
                            Processando...
                        </span>
                    </button>
                </form>

                <div class="security-note fade-in">
                    Sistema protegido por <strong>criptografia avançada</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="login-footer">
        &copy; <?php echo date('Y'); ?> <strong class="orange">Grupo Sorrisos</strong> <strong class="red">Odontologia</strong>. Todos os direitos reservados.
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('bi-eye');
                passwordIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('bi-eye-slash');
                passwordIcon.classList.add('bi-eye');
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 
                             type === 'warning' ? 'alert-warning' : 
                             type === 'info' ? 'alert-info' : 'alert-danger';
            
            const icon = type === 'success' ? 'check-circle' : 
                        type === 'warning' ? 'exclamation-triangle' : 
                        type === 'info' ? 'info-circle' : 'exclamation-circle';

            alertContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function setLoading(loading) {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            
            if (loading) {
                btn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
            } else {
                btn.disabled = false;
                btnText.style.display = 'inline-flex';
                btnLoading.style.display = 'none';
            }
        }

        function obterPaginaRedirecionamento() {
            fetch('backend/api/redirecionamento.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        console.error('Erro ao obter redirecionamento:', data.error);
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.error('Erro de conexão:', error);
                    window.location.href = 'index.php';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                
                document.getElementById('alertContainer').innerHTML = '';
                
                if (!email || !password) {
                    showAlert('Por favor, preencha todos os campos.', 'warning');
                    return;
                }
                
                if (!isValidEmail(email)) {
                    showAlert('Por favor, insira um e-mail válido.', 'warning');
                    return;
                }
                
                setLoading(true);
                
                fetch('backend/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message || 'Login realizado com sucesso!', 'success');
                        setTimeout(() => {
                            obterPaginaRedirecionamento();
                        }, 1200);
                    } else {
                        showAlert(data.message || 'E-mail ou senha incorretos.', 'danger');
                        loginForm.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            loginForm.style.animation = '';
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showAlert('Erro de conexão. Verifique sua internet e tente novamente.', 'danger');
                })
                .finally(() => {
                    setLoading(false);
                });
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
