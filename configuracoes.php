<?php
$menuActive = 'configuracoes';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/configuracao.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f7f9fb; }
        .card-config { border: none; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 1.5rem; }
        .card-config .card-body { padding: 2rem 2rem 1.5rem 2rem; }
        .form-label { font-weight: 500; color: #222; }
        .form-control, .form-select { border-radius: 8px; }
        .form-switch .form-check-input { width: 2.5em; height: 1.3em; }
        .form-switch .form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
        .section-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem; }
        .section-title i { font-size: 1.3rem; color: #2563eb; }
        .btn-salvar { background: #2563eb; color: #fff; border-radius: 8px; font-weight: 500; }
        .btn-salvar:hover { background: #1d4ed8; }
        .btn-outline { border: 1px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-weight: 500; }
        .btn-outline:hover { background: #f1f3f5; }
        .slider { width: 100%; }
        .slider::-webkit-slider-thumb { background: #2563eb; }
        .slider::-moz-range-thumb { background: #2563eb; }
        .slider::-ms-thumb { background: #2563eb; }
        @media (max-width: 991px) {
            .card-config .card-body { padding: 1.2rem; }
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<!-- Main Content -->
<main class="main-content">
            <form id="form-configuracoes">
                <div class="d-flex align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-0">Configurações</h2>
                        <div class="text-muted" style="font-size: 1rem;">Gerencie as configurações do sistema</div>
                    </div>
                    <button type="submit" class="btn btn-salvar ms-auto" id="btn-salvar">
                        <i class="bi bi-save me-1"></i> Salvar Alterações
                    </button>
                </div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card card-config mb-4">
                            <div class="card-body">
                                <div class="section-title"><i class="bi bi-gear"></i> Configurações Gerais</div>
                                <div class="row g-3 mb-2">
                                    <div class="col-md-12">
                                        <label class="form-label" for="empresa_nome">Nome da Empresa</label>
                                        <input type="text" class="form-control" id="empresa_nome" name="empresa_nome" data-chave="empresa_nome">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="empresa_email">E-mail Principal</label>
                                        <input type="email" class="form-control" id="empresa_email" name="empresa_email" data-chave="empresa_email">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="empresa_telefone">Telefone</label>
                                        <input type="text" class="form-control" id="empresa_telefone" name="empresa_telefone" data-chave="empresa_telefone">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="empresa_moeda">Moeda</label>
                                        <select class="form-select" id="empresa_moeda" name="empresa_moeda" data-chave="empresa_moeda">
                                            <option value="BRL">Real (BRL)</option>
                                            <option value="USD">Dólar (USD)</option>
                                            <option value="EUR">Euro (EUR)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="empresa_fuso">Fuso Horário</label>
                                        <select class="form-select" id="empresa_fuso" name="empresa_fuso" data-chave="empresa_fuso">
                                            <option value="America/Sao_Paulo">Brasília (UTC-3)</option>
                                            <option value="Europe/Lisbon">Lisboa (UTC+0)</option>
                                            <option value="America/New_York">Nova York (UTC-5)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card card-config mb-4">
                            <div class="card-body">
                                <div class="section-title"><i class="bi bi-bell"></i> Notificações</div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">E-mail de Notificações</div>
                                        <div class="text-muted small">Receber notificações por e-mail</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="notifica_email" name="notifica_email" data-chave="notifica_email">
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Pagamentos</div>
                                        <div class="text-muted small">Notificar sobre pagamentos realizados</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="notifica_pagamentos" name="notifica_pagamentos" data-chave="notifica_pagamentos">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Vencimentos</div>
                                        <div class="text-muted small">Alertas de contas próximas ao vencimento</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="notifica_vencimentos" name="notifica_vencimentos" data-chave="notifica_vencimentos">
                                    </div>
                                </div>
                                <div class="row align-items-center g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Relatórios Automáticos</div>
                                        <div class="text-muted small">Envio automático de relatórios mensais</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="notifica_relatorios" name="notifica_relatorios" data-chave="notifica_relatorios">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card card-config mb-4">
                            <div class="card-body">
                                <div class="section-title"><i class="bi bi-hdd-network"></i> Sistema e Backup</div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Backup Automático</div>
                                        <div class="text-muted small">Backup automático dos dados</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="backup_automatico" name="backup_automatico" data-chave="backup_automatico">
                                    </div>
                                </div>
                                <div class="row g-3 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label" for="backup_intervalo">Intervalo do Backup</label>
                                        <select class="form-select" id="backup_intervalo" name="backup_intervalo" data-chave="backup_intervalo">
                                            <option value="diario">Diário</option>
                                            <option value="semanal">Semanal</option>
                                            <option value="mensal">Mensal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="backup_historico">Manter Histórico (meses)</label>
                                        <input type="number" class="form-control" id="backup_historico" name="backup_historico" data-chave="backup_historico" min="1" max="60">
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-outline w-50"><i class="bi bi-download me-1"></i> Exportar Dados</button>
                                    <button type="button" class="btn btn-outline w-50"><i class="bi bi-upload me-1"></i> Importar Dados</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card card-config mb-4">
                            <div class="card-body">
                                <div class="section-title"><i class="bi bi-shield-lock"></i> Segurança</div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Autenticação em Duas Etapas</div>
                                        <div class="text-muted small">Maior segurança no login</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="seguranca_2fa" name="seguranca_2fa" data-chave="seguranca_2fa">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="sessao_expira">Sessão Expira (minutos)</label>
                                    <input type="range" class="form-range slider" min="5" max="120" step="1" id="sessao_expira" name="sessao_expira" data-chave="sessao_expira" oninput="document.getElementById('sessao_expira_val').innerText = this.value">
                                    <span id="sessao_expira_val" class="ms-2">30</span>
                                </div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Log de Auditoria</div>
                                        <div class="text-muted small">Registrar ações dos usuários</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="log_auditoria" name="log_auditoria" data-chave="log_auditoria">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline w-100">Alterar Senha</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card card-config mb-4">
                            <div class="card-body">
                                <div class="section-title"><i class="bi bi-clock"></i> Horário de Funcionamento</div>
                                <div class="row align-items-center mb-3 g-0">
                                    <div class="col-10">
                                        <div class="fw-semibold">Controle de Horário</div>
                                        <div class="text-muted small">Restringir acesso ao sistema por horário</div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <input class="form-check-input float-end" type="checkbox" id="horario_funcionamento_ativo" name="horario_funcionamento_ativo" data-chave="horario_funcionamento_ativo">
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-week me-1"></i> Segunda a Sexta</h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label" for="horario_inicio_semana">Início</label>
                                                <input type="time" class="form-control" id="horario_inicio_semana" name="horario_inicio_semana" data-chave="horario_inicio_semana">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" for="horario_fim_semana">Fim</label>
                                                <input type="time" class="form-control" id="horario_fim_semana" name="horario_fim_semana" data-chave="horario_fim_semana">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-day me-1"></i> Sábado</h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label" for="horario_inicio_sabado">Início</label>
                                                <input type="time" class="form-control" id="horario_inicio_sabado" name="horario_inicio_sabado" data-chave="horario_inicio_sabado">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" for="horario_fim_sabado">Fim</label>
                                                <input type="time" class="form-control" id="horario_fim_sabado" name="horario_fim_sabado" data-chave="horario_fim_sabado">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row align-items-center mb-2 g-0">
                                            <div class="col-10">
                                                <h6 class="fw-semibold mb-0"><i class="bi bi-calendar-x me-1"></i> Domingo</h6>
                                                <div class="text-muted small">Permitir acesso aos domingos</div>
                                            </div>
                                            <div class="col-2 text-end">
                                                <input class="form-check-input float-end" type="checkbox" id="horario_domingo_ativo" name="horario_domingo_ativo" data-chave="horario_domingo_ativo">
                                            </div>
                                        </div>
                                        <div class="row g-2" id="horarios-domingo" style="display: none;">
                                            <div class="col-6">
                                                <label class="form-label" for="horario_inicio_domingo">Início</label>
                                                <input type="time" class="form-control" id="horario_inicio_domingo" name="horario_inicio_domingo" data-chave="horario_inicio_domingo">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" for="horario_fim_domingo">Fim</label>
                                                <input type="time" class="form-control" id="horario_fim_domingo" name="horario_fim_domingo" data-chave="horario_fim_domingo">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Modal Alterar Senha -->
<div class="modal fade" id="modalAlterarSenha" tabindex="-1" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalAlterarSenhaLabel">
          <i class="bi bi-shield-lock text-primary me-2"></i>Alterar Senha
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-alterar-senha">
          <div class="mb-3">
            <label for="senha_atual" class="form-label">Senha Atual</label>
            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required autocomplete="current-password">
          </div>
          <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required autocomplete="new-password">
          </div>
          <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variável global para armazenar configurações
let configuracoes = {};

// Carregar configurações do banco de dados
async function carregarConfiguracoes() {
    try {
        const response = await fetch('backend/api/configuracoes.php?action=buscar_agrupadas');
        const data = await response.json();
        
        if (data.success) {
            configuracoes = data.data;
            preencherFormulario();
        } else {
            console.error('Erro ao carregar configurações:', data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar configurações:', error);
    }
}

// Preencher formulário com as configurações carregadas
function preencherFormulario() {
    // Preencher campos de texto e select
    document.querySelectorAll('[data-chave]').forEach(element => {
        const chave = element.getAttribute('data-chave');
        const valor = getValorConfiguracao(chave);
        
        if (element.type === 'checkbox') {
            element.checked = valor === '1' || valor === 'true';
        } else if (element.type === 'range') {
            element.value = valor || 30;
            document.getElementById('sessao_expira_val').innerText = element.value;
        } else {
            element.value = valor || '';
        }
    });
}

// Obter valor de uma configuração
function getValorConfiguracao(chave) {
    for (const categoria in configuracoes) {
        const config = configuracoes[categoria].find(c => c.chave === chave);
        if (config) {
            return config.valor;
        }
    }
    return null;
}

// Salvar configurações
async function salvarConfiguracoes() {
    const btnSalvar = document.getElementById('btn-salvar');
    const btnOriginal = btnSalvar.innerHTML;
    
    // Mostrar loading
    btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
    btnSalvar.disabled = true;
    
    try {
        // Coletar todas as configurações do formulário
        const configuracoesParaSalvar = {};
        
        document.querySelectorAll('[data-chave]').forEach(element => {
            const chave = element.getAttribute('data-chave');
            let valor;
            
            if (element.type === 'checkbox') {
                valor = element.checked ? '1' : '0';
            } else {
                valor = element.value;
            }
            
            configuracoesParaSalvar[chave] = valor;
        });
        
        // Enviar para a API
        const response = await fetch('backend/api/configuracoes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'atualizar_multiplas',
                configuracoes: configuracoesParaSalvar
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Atualizar configurações locais
            await carregarConfiguracoes();
            
            // Mostrar sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Configurações salvas com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.erro || 'Erro ao salvar configurações');
        }
        
    } catch (error) {
        console.error('Erro ao salvar configurações:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao salvar configurações: ' + error.message
        });
    } finally {
        // Restaurar botão
        btnSalvar.innerHTML = btnOriginal;
        btnSalvar.disabled = false;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Carregar configurações ao iniciar
    carregarConfiguracoes();
    
    // Atualizar valor do slider
const slider = document.getElementById('sessao_expira');
    if (slider) {
        slider.oninput = function() {
            document.getElementById('sessao_expira_val').innerText = this.value;
        };
    }
    
    // Controlar exibição dos horários de domingo
    const checkboxDomingo = document.getElementById('horario_domingo_ativo');
    const horariosDomingo = document.getElementById('horarios-domingo');
    
    if (checkboxDomingo && horariosDomingo) {
        checkboxDomingo.addEventListener('change', function() {
            if (this.checked) {
                horariosDomingo.style.display = 'block';
            } else {
                horariosDomingo.style.display = 'none';
            }
        });
        
        // Verificar estado inicial
        if (checkboxDomingo.checked) {
            horariosDomingo.style.display = 'block';
        }
    }
    
    // Submissão do formulário
    document.getElementById('form-configuracoes').addEventListener('submit', function(e) {
        e.preventDefault();
        salvarConfiguracoes();
    });

    // Modal Alterar Senha

    const btnAlterarSenha = document.querySelector('button.btn-outline.w-100');
    if (btnAlterarSenha) {
        btnAlterarSenha.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalAlterarSenha'));
            document.getElementById('form-alterar-senha').reset();
            modal.show();
        });
    }

    // Submissão do formulário de alteração de senha
    const formAlterarSenha = document.getElementById('form-alterar-senha');
    if (formAlterarSenha) {
        formAlterarSenha.addEventListener('submit', async function(e) {
            e.preventDefault();
            const senha_atual = document.getElementById('senha_atual').value;
            const nova_senha = document.getElementById('nova_senha').value;
            const confirmar_senha = document.getElementById('confirmar_senha').value;

            if (nova_senha.length < 6) {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'A nova senha deve ter pelo menos 6 caracteres.' });
                return;
            }
            if (nova_senha !== confirmar_senha) {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'A confirmação da senha não confere.' });
                return;
            }

            // Mostrar SweetAlert de processamento
            Swal.fire({
                title: 'Processando...',
                text: 'Alterando sua senha e enviando notificação por e-mail',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar para o backend
            try {
                const response = await fetch('backend/api/alterar_senha.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ senha_atual, nova_senha })
                });
                const data = await response.json();
                
                // Fechar SweetAlert de processamento
                Swal.close();
                
                if (data.sucesso) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Sucesso!', 
                        text: 'Senha alterada com sucesso. Um e-mail de confirmação foi enviado para sua caixa de entrada.', 
                        timer: 3000, 
                        showConfirmButton: false 
                    });
                    bootstrap.Modal.getInstance(document.getElementById('modalAlterarSenha')).hide();
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: data.erro || 'Não foi possível alterar a senha.' });
                }
            } catch (error) {
                // Fechar SweetAlert de processamento
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao comunicar com o servidor.' });
            }
        });
    }
});
</script>
</body>
</html>