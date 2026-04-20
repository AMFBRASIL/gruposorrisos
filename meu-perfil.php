<?php
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Registrar acesso à página
$controllerAcesso->registrarAcessoPagina();

$menuActive = 'meu_perfil';

// Buscar dados do usuário logado
try {
    require_once 'config/conexao.php';
    $pdo = Conexao::getInstance()->getPdo();
    
    $stmt = $pdo->prepare("SELECT u.*, p.nome_perfil 
                          FROM tbl_usuarios u 
                          LEFT JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
                          WHERE u.id_usuario = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header('Location: error.php?message=Usuário não encontrado&codigo=404&tipo=error');
        exit;
    }
} catch (Exception $e) {
    header('Location: error.php?message=Erro ao carregar dados&codigo=500&tipo=error');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Meu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/meu-perfil.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 0;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-body {
            padding: 2rem;
        }
        
        .form-floating > label {
            color: #6b7280;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: transform 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        
        .btn-outline-secondary {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: transform 0.2s ease;
        }
        
        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .info-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #374151;
        }
        
        .loading {
            display: none;
        }
        
        .loading.show {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi bi-person-circle me-3"></i>
                Meu Perfil
            </h1>
            <p class="page-subtitle">Gerencie suas informações pessoais e configurações de conta</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h3 class="mb-1"><?php echo htmlspecialchars($usuario['nome_completo']); ?></h3>
                        <div class="info-badge"><?php echo htmlspecialchars($usuario['nome_perfil']); ?></div>
                    </div>
                    
                    <div class="profile-body">
                        <form id="form-perfil">
                            <!-- Dados Pessoais -->
                            <h4 class="section-title">
                                <i class="bi bi-person me-2"></i>
                                Dados Pessoais
                            </h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nome_completo" 
                                               value="<?php echo htmlspecialchars($usuario['nome_completo']); ?>" required>
                                        <label for="nome_completo">Nome Completo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" 
                                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                        <label for="email">E-mail</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="cpf" 
                                               value="<?php echo htmlspecialchars($usuario['cpf']); ?>" readonly>
                                        <label for="cpf">CPF</label>
                                    </div>
                                    <small class="text-muted">CPF não pode ser alterado</small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="telefone" 
                                               value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                                        <label for="telefone">Telefone</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Alteração de Senha -->
                            <h4 class="section-title">
                                <i class="bi bi-shield-lock me-2"></i>
                                Segurança
                            </h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="alterar_senha">
                                        <label class="form-check-label" for="alterar_senha">
                                            Desejo alterar minha senha
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="campos-senha" style="display: none;">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="form-floating position-relative">
                                            <input type="password" class="form-control" id="senha_atual" 
                                                   autocomplete="current-password">
                                            <label for="senha_atual">Senha Atual</label>
                                            <button type="button" class="password-toggle" onclick="togglePassword('senha_atual')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating position-relative">
                                            <input type="password" class="form-control" id="nova_senha" 
                                                   autocomplete="new-password">
                                            <label for="nova_senha">Nova Senha</label>
                                            <button type="button" class="password-toggle" onclick="togglePassword('nova_senha')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating position-relative">
                                            <input type="password" class="form-control" id="confirmar_senha" 
                                                   autocomplete="new-password">
                                            <label for="confirmar_senha">Confirmar Senha</label>
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirmar_senha')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Dicas para uma senha segura:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Use pelo menos 8 caracteres</li>
                                        <li>Combine letras maiúsculas e minúsculas</li>
                                        <li>Inclua números e símbolos</li>
                                        <li>Evite informações pessoais óbvias</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Botões de Ação -->
                            <div class="d-flex gap-3 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Voltar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Loading Overlay -->
    <div class="loading position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center" 
         style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <div>Salvando alterações...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Garantir que o loading esteja oculto ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const loading = document.querySelector('.loading');
            if (loading) {
                loading.classList.remove('show');
                loading.style.display = 'none';
            }
        });
        
        // Controlar exibição dos campos de senha
        document.getElementById('alterar_senha').addEventListener('change', function() {
            const camposSenha = document.getElementById('campos-senha');
            if (this.checked) {
                camposSenha.style.display = 'block';
                // Tornar campos obrigatórios
                document.getElementById('senha_atual').required = true;
                document.getElementById('nova_senha').required = true;
                document.getElementById('confirmar_senha').required = true;
            } else {
                camposSenha.style.display = 'none';
                // Remover obrigatoriedade e limpar campos
                document.getElementById('senha_atual').required = false;
                document.getElementById('nova_senha').required = false;
                document.getElementById('confirmar_senha').required = false;
                document.getElementById('senha_atual').value = '';
                document.getElementById('nova_senha').value = '';
                document.getElementById('confirmar_senha').value = '';
            }
        });
        
        // Toggle de visibilidade da senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
        
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = value;
        });
        
        // Validação e envio do formulário
        document.getElementById('form-perfil').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const loading = document.querySelector('.loading');
            
            try {
                // Validações
                const alterarSenha = document.getElementById('alterar_senha').checked;
                
                if (alterarSenha) {
                    const senhaAtual = document.getElementById('senha_atual').value;
                    const novaSenha = document.getElementById('nova_senha').value;
                    const confirmarSenha = document.getElementById('confirmar_senha').value;
                    
                    if (!senhaAtual || !novaSenha || !confirmarSenha) {
                        Swal.fire('Erro', 'Preencha todos os campos de senha', 'error');
                        return;
                    }
                    
                    if (novaSenha !== confirmarSenha) {
                        Swal.fire('Erro', 'A nova senha e a confirmação não coincidem', 'error');
                        return;
                    }
                    
                    if (novaSenha.length < 6) {
                        Swal.fire('Erro', 'A nova senha deve ter pelo menos 6 caracteres', 'error');
                        return;
                    }
                }
                
                // Validar email
                const email = document.getElementById('email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire('Erro', 'Digite um e-mail válido', 'error');
                    return;
                }
                
                loading.classList.add('show');
                
                // Preparar dados
                const dados = {
                    nome_completo: document.getElementById('nome_completo').value,
                    email: email,
                    telefone: document.getElementById('telefone').value,
                    alterar_senha: alterarSenha
                };
                
                if (alterarSenha) {
                    dados.senha_atual = document.getElementById('senha_atual').value;
                    dados.nova_senha = document.getElementById('nova_senha').value;
                }
                
                // Enviar dados
                const response = await fetch('backend/api/meu-perfil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dados)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire('Sucesso', 'Dados atualizados com sucesso!', 'success').then(() => {
                        // Limpar campos de senha
                        document.getElementById('alterar_senha').checked = false;
                        document.getElementById('alterar_senha').dispatchEvent(new Event('change'));
                        
                        // Atualizar nome na interface se necessário
                        const nomeAtualizado = document.getElementById('nome_completo').value;
                        document.querySelector('.profile-header h3').textContent = nomeAtualizado;
                    });
                } else {
                    Swal.fire('Erro', result.message || 'Erro ao atualizar dados', 'error');
                }
                
            } catch (error) {
                console.error('Erro:', error);
                Swal.fire('Erro', 'Erro de conexão. Tente novamente.', 'error');
            } finally {
                loading.classList.remove('show');
            }
        });
    </script>
</body>
</html> 