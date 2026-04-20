<?php
session_start();
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
            header('Location: index.php');
        }
        exit;
    } catch (Exception $e) {
        // Fallback para index.php em caso de erro
        header('Location: index.php');
        exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Partículas flutuantes -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Lado esquerdo - Branding -->
            <div class="login-brand">
                <img src="assets/img/logo-grupo-sorrisos.jpg" alt="Grupo Sorrisos" class="brand-logo">
                <h1 class="brand-title">Grupo Sorrisos</h1>
                <p class="brand-subtitle">
                    Sistema de Gestão de Estoque Odontológico<br>
                    Tecnologia avançada para controle total
                </p>
            </div>

            <!-- Lado direito - Formulário -->
            <div class="login-form">
                <div class="form-header fade-in">
                    <h2 class="form-title">Bem-vindo de volta</h2>
                    <p class="form-subtitle">Entre com suas credenciais para acessar o sistema</p>
                </div>

                <form id="loginForm" autocomplete="off">
                    <div id="alertContainer"></div>
                    
                    <div class="form-group fade-in">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="exemplo@gruposorrisos.com" required>
                    </div>

                    <div class="form-group fade-in">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Digite sua senha" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
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

                <div class="text-center mt-4 fade-in">
                    <span style="color: #666; font-size: 0.9rem;">
                        Sistema protegido por criptografia avançada
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="login-footer">
        &copy; <?php echo date('Y'); ?> Grupo Sorrisos Odontologia. Todos os direitos reservados.
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Criar partículas flutuantes
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 4 + 2;
                const x = Math.random() * window.innerWidth;
                const y = Math.random() * window.innerHeight;
                const delay = Math.random() * 6;
                
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                particle.style.animationDelay = delay + 's';
                
                particlesContainer.appendChild(particle);
            }
        }

        // Toggle password visibility
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

        // Validação de email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Mostrar alerta
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

        // Loading state
        function setLoading(loading) {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            
            if (loading) {
                btn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'flex';
                btn.style.alignItems = 'center';
                btn.style.justifyContent = 'center';
            } else {
                btn.disabled = false;
                btnText.style.display = 'flex';
                btnLoading.style.display = 'none';
            }
        }

        // Função para obter página de redirecionamento
        function obterPaginaRedirecionamento() {
            fetch('backend/api/redirecionamento.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirecionar para a página permitida
                        window.location.href = data.redirect_url;
                    } else {
                        // Fallback para index.php em caso de erro
                        console.error('Erro ao obter redirecionamento:', data.error);
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.error('Erro de conexão:', error);
                    // Fallback para index.php em caso de erro
                    window.location.href = 'index.php';
                });
        }

        // Login AJAX
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                
                // Limpar alertas anteriores
                document.getElementById('alertContainer').innerHTML = '';
                
                // Validação
                if (!email || !password) {
                    showAlert('Por favor, preencha todos os campos.', 'warning');
                    return;
                }
                
                if (!isValidEmail(email)) {
                    showAlert('Por favor, insira um e-mail válido.', 'warning');
                    return;
                }
                
                // Iniciar loading
                setLoading(true);
                
                // Requisição AJAX
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
                        
                        // Efeito de sucesso
                        document.querySelector('.login-card').style.animation = 'slideInUp 0.5s ease-out';
                        
                        // Obter página de redirecionamento inteligente
                        setTimeout(() => {
                            obterPaginaRedirecionamento();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'E-mail ou senha incorretos.', 'danger');
                        
                        // Efeito de erro
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

            // Efeitos de foco nos campos
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        });

        // Animação de shake para erro
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