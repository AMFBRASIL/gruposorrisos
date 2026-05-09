<?php
require_once 'config/session.php';
require_once 'config/config.php';
requireLogin();
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
        .nav-tabs-config {
            border-bottom: 1px solid #e9ecef;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        .nav-tabs-config .nav-link {
            border: none;
            border-radius: 10px 10px 0 0;
            color: #64748b;
            font-weight: 600;
            padding: 0.65rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }
        .nav-tabs-config .nav-link:hover {
            color: #2563eb;
            background: #f1f5f9;
        }
        .nav-tabs-config .nav-link.active {
            color: #2563eb;
            background: #eff6ff;
            border-bottom: 3px solid #2563eb;
        }
        .tab-pane-config {
            min-height: 280px;
        }
        .tab-subcard {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem 1.35rem;
            border: 1px solid #eef2f7;
            height: 100%;
        }
        @media (max-width: 991px) {
            .card-config .card-body { padding: 1.2rem; }
            .nav-tabs-config .nav-link { font-size: 0.9rem; padding: 0.5rem 0.75rem; }
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<!-- Main Content -->
<main class="main-content">
    <form id="form-configuracoes">
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-3 mb-4">
            <div class="flex-grow-1">
                <h2 class="fw-bold mb-1">Configurações</h2>
                <p class="text-muted mb-0">Organize por abas e salve quando terminar — um único formulário para todo o sistema.</p>
            </div>
            <button type="submit" class="btn btn-salvar" id="btn-salvar">
                <i class="bi bi-save me-1"></i> Salvar alterações
            </button>
        </div>

        <div class="card card-config mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-tabs-config px-3 pt-3 mb-0" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-geral-btn" data-bs-toggle="tab" data-bs-target="#tab-geral" type="button" role="tab" aria-controls="tab-geral" aria-selected="true"><i class="bi bi-building"></i> Geral</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-email-btn" data-bs-toggle="tab" data-bs-target="#tab-email" type="button" role="tab" aria-controls="tab-email" aria-selected="false"><i class="bi bi-envelope-at"></i> E-mail (SMTP)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-notif-btn" data-bs-toggle="tab" data-bs-target="#tab-notif" type="button" role="tab" aria-controls="tab-notif" aria-selected="false"><i class="bi bi-bell"></i> Notificações</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-sistema-btn" data-bs-toggle="tab" data-bs-target="#tab-sistema" type="button" role="tab" aria-controls="tab-sistema" aria-selected="false"><i class="bi bi-hdd-network"></i> Sistema</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-seg-btn" data-bs-toggle="tab" data-bs-target="#tab-seg" type="button" role="tab" aria-controls="tab-seg" aria-selected="false"><i class="bi bi-shield-lock"></i> Segurança</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-hor-btn" data-bs-toggle="tab" data-bs-target="#tab-hor" type="button" role="tab" aria-controls="tab-hor" aria-selected="false"><i class="bi bi-clock"></i> Horários</button>
                    </li>
                </ul>

                <div class="tab-content border-0 tab-pane-config p-4 pt-3">
                    <!-- Aba Geral -->
                    <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-btn" tabindex="0">
                        <div class="tab-subcard">
                            <div class="section-title mb-3"><i class="bi bi-gear"></i> Dados da empresa</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="empresa_nome">Nome da empresa</label>
                                    <input type="text" class="form-control" id="empresa_nome" name="empresa_nome" data-chave="empresa_nome">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="empresa_email">E-mail principal</label>
                                    <input type="email" class="form-control" id="empresa_email" name="empresa_email" data-chave="empresa_email">
                                </div>
                                <div class="col-md-6">
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
                                    <label class="form-label" for="empresa_fuso">Fuso horário</label>
                                    <select class="form-select" id="empresa_fuso" name="empresa_fuso" data-chave="empresa_fuso">
                                        <option value="America/Sao_Paulo">Brasília (UTC-3)</option>
                                        <option value="Europe/Lisbon">Lisboa (UTC+0)</option>
                                        <option value="America/New_York">Nova York (UTC-5)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba E-mail SMTP -->
                    <div class="tab-pane fade" id="tab-email" role="tabpanel" aria-labelledby="tab-email-btn" tabindex="0">
                        <div class="tab-subcard mb-3">
                            <div class="section-title mb-2"><i class="bi bi-envelope-at"></i> Servidor de envio</div>
                            <p class="text-muted small mb-3">Boas-vindas, pedidos, recuperação de senha e demais e-mails do sistema usam estas definições. Ative o SMTP só depois de preencher host e credenciais.</p>
                            <div class="row align-items-center mb-3 g-0">
                                <div class="col">
                                    <div class="fw-semibold">Ativar envio por SMTP</div>
                                    <div class="text-muted small">Desligado = nenhum e-mail é enviado.</div>
                                </div>
                                <div class="col-auto">
                                    <input class="form-check-input" type="checkbox" id="smtp_ativo" name="smtp_ativo" data-chave="smtp_ativo" role="switch" style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="row g-3">
                                <div class="col-lg-8">
                                    <label class="form-label" for="smtp_host">Servidor SMTP (host)</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" data-chave="smtp_host" placeholder="ex.: smtp.seudominio.com" autocomplete="off">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label" for="smtp_port">Porta</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" data-chave="smtp_port" min="1" max="65535" placeholder="587 ou 465">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="smtp_secure">Criptografia</label>
                                    <select class="form-select" id="smtp_secure" name="smtp_secure" data-chave="smtp_secure">
                                        <option value="tls">TLS (STARTTLS) — porta 587</option>
                                        <option value="ssl">SSL — porta 465</option>
                                        <option value="none">Nenhuma</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="smtp_timeout">Timeout (segundos)</label>
                                    <input type="number" class="form-control" id="smtp_timeout" name="smtp_timeout" data-chave="smtp_timeout" min="5" max="120" placeholder="15">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="smtp_username">Usuário SMTP</label>
                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" data-chave="smtp_username" placeholder="Geralmente o e-mail completo" autocomplete="off">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="smtp_password">Senha SMTP</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" data-chave="smtp_password" placeholder="Em branco mantém a senha já salva" autocomplete="new-password">
                                    <div class="form-text" id="smtp_password_hint"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-subcard mb-3">
                            <div class="section-title mb-3"><i class="bi bi-person-vcard"></i> Remetente e resposta</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="smtp_from_email">E-mail remetente (From)</label>
                                    <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" data-chave="smtp_from_email" placeholder="noreply@seudominio.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="smtp_from_name">Nome do remetente</label>
                                    <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" data-chave="smtp_from_name" placeholder="Grupo Sorrisos">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="smtp_reply_to">Reply-To (opcional)</label>
                                    <input type="email" class="form-control" id="smtp_reply_to" name="smtp_reply_to" data-chave="smtp_reply_to" placeholder="Mesmo do remetente se vazio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="smtp_reply_to_name">Nome Reply-To (opcional)</label>
                                    <input type="text" class="form-control" id="smtp_reply_to_name" name="smtp_reply_to_name" data-chave="smtp_reply_to_name">
                                </div>
                            </div>
                        </div>
                        <div class="tab-subcard">
                            <div class="section-title mb-3"><i class="bi bi-send-check"></i> Teste de envio</div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label" for="smtp_email_teste">Enviar teste para</label>
                                    <input type="email" class="form-control" id="smtp_email_teste" placeholder="E-mail que receberá o teste">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-primary w-100" id="btn-testar-smtp"><i class="bi bi-send me-1"></i> Testar SMTP</button>
                                </div>
                                <div class="col-12">
                                    <p class="text-muted small mb-0 mt-2">O teste usa apenas valores <strong>já salvos</strong>. Salve as alterações antes, se mudou host ou senha.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Notificações -->
                    <div class="tab-pane fade" id="tab-notif" role="tabpanel" aria-labelledby="tab-notif-btn" tabindex="0">
                        <div class="tab-subcard">
                            <div class="section-title mb-3"><i class="bi bi-bell"></i> Preferências de alertas</div>
                            <div class="list-group list-group-flush rounded-3 border">
                                <label class="list-group-item d-flex align-items-center justify-content-between py-3 mb-0">
                                    <span><span class="fw-semibold d-block">E-mail de notificações</span><span class="text-muted small">Receber alertas por e-mail</span></span>
                                    <input class="form-check-input m-0" type="checkbox" id="notifica_email" name="notifica_email" data-chave="notifica_email">
                                </label>
                                <label class="list-group-item d-flex align-items-center justify-content-between py-3 mb-0">
                                    <span><span class="fw-semibold d-block">Pagamentos</span><span class="text-muted small">Notificar sobre pagamentos</span></span>
                                    <input class="form-check-input m-0" type="checkbox" id="notifica_pagamentos" name="notifica_pagamentos" data-chave="notifica_pagamentos">
                                </label>
                                <label class="list-group-item d-flex align-items-center justify-content-between py-3 mb-0">
                                    <span><span class="fw-semibold d-block">Vencimentos</span><span class="text-muted small">Contas próximas ao vencimento</span></span>
                                    <input class="form-check-input m-0" type="checkbox" id="notifica_vencimentos" name="notifica_vencimentos" data-chave="notifica_vencimentos">
                                </label>
                                <label class="list-group-item d-flex align-items-center justify-content-between py-3 mb-0 border-0">
                                    <span><span class="fw-semibold d-block">Relatórios automáticos</span><span class="text-muted small">Envio mensal de relatórios</span></span>
                                    <input class="form-check-input m-0" type="checkbox" id="notifica_relatorios" name="notifica_relatorios" data-chave="notifica_relatorios">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Sistema -->
                    <div class="tab-pane fade" id="tab-sistema" role="tabpanel" aria-labelledby="tab-sistema-btn" tabindex="0">
                        <div class="tab-subcard">
                            <div class="section-title mb-3"><i class="bi bi-cloud-arrow-down"></i> Backup</div>
                            <div class="row align-items-center mb-3 g-0">
                                <div class="col">
                                    <div class="fw-semibold">Backup automático</div>
                                    <div class="text-muted small">Rotina de cópia dos dados</div>
                                </div>
                                <div class="col-auto">
                                    <input class="form-check-input" type="checkbox" id="backup_automatico" name="backup_automatico" data-chave="backup_automatico" role="switch" style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="backup_intervalo">Intervalo</label>
                                    <select class="form-select" id="backup_intervalo" name="backup_intervalo" data-chave="backup_intervalo">
                                        <option value="diario">Diário</option>
                                        <option value="semanal">Semanal</option>
                                        <option value="mensal">Mensal</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="backup_historico">Manter histórico (meses)</label>
                                    <input type="number" class="form-control" id="backup_historico" name="backup_historico" data-chave="backup_historico" min="1" max="60">
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="button" class="btn btn-outline flex-fill"><i class="bi bi-download me-1"></i> Exportar dados</button>
                                <button type="button" class="btn btn-outline flex-fill"><i class="bi bi-upload me-1"></i> Importar dados</button>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Segurança -->
                    <div class="tab-pane fade" id="tab-seg" role="tabpanel" aria-labelledby="tab-seg-btn" tabindex="0">
                        <div class="tab-subcard mb-3">
                            <div class="section-title mb-3"><i class="bi bi-shield-lock"></i> Políticas</div>
                            <div class="row align-items-center mb-3">
                                <div class="col">
                                    <div class="fw-semibold">Autenticação em duas etapas</div>
                                    <div class="text-muted small">Camada extra no login</div>
                                </div>
                                <div class="col-auto">
                                    <input class="form-check-input" type="checkbox" id="seguranca_2fa" name="seguranca_2fa" data-chave="seguranca_2fa" role="switch" style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="sessao_expira">Sessão expira (minutos)</label>
                                <input type="range" class="form-range slider" min="5" max="120" step="1" id="sessao_expira" name="sessao_expira" data-chave="sessao_expira">
                                <div class="d-flex align-items-center gap-2"><span id="sessao_expira_val" class="fw-semibold text-primary">30</span><span class="text-muted small">minutos</span></div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-semibold">Log de auditoria</div>
                                    <div class="text-muted small">Registrar ações dos usuários</div>
                                </div>
                                <div class="col-auto">
                                    <input class="form-check-input" type="checkbox" id="log_auditoria" name="log_auditoria" data-chave="log_auditoria" role="switch" style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                        </div>
                        <div class="tab-subcard">
                            <div class="fw-semibold mb-2"><i class="bi bi-key me-1"></i> Sua conta</div>
                            <p class="text-muted small mb-3">Altere a senha do usuário logado. Demais usuários são gerenciados em Usuários.</p>
                            <button type="button" class="btn btn-outline w-100" id="btn-alterar-senha-config"><i class="bi bi-shield-lock me-1"></i> Alterar senha</button>
                        </div>
                    </div>

                    <!-- Aba Horários -->
                    <div class="tab-pane fade" id="tab-hor" role="tabpanel" aria-labelledby="tab-hor-btn" tabindex="0">
                        <div class="tab-subcard">
                            <div class="section-title mb-3"><i class="bi bi-clock"></i> Horário de funcionamento</div>
                            <div class="row align-items-center mb-3">
                                <div class="col">
                                    <div class="fw-semibold">Controle de horário</div>
                                    <div class="text-muted small">Restringir acesso ao sistema por faixa de horário</div>
                                </div>
                                <div class="col-auto">
                                    <input class="form-check-input" type="checkbox" id="horario_funcionamento_ativo" name="horario_funcionamento_ativo" data-chave="horario_funcionamento_ativo" role="switch" style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-week me-1"></i> Segunda a sexta</h6>
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
                                <div class="col-12">
                                    <div class="row align-items-center mb-2">
                                        <div class="col">
                                            <h6 class="fw-semibold mb-0"><i class="bi bi-calendar-x me-1"></i> Domingo</h6>
                                            <div class="text-muted small">Permitir acesso aos domingos</div>
                                        </div>
                                        <div class="col-auto">
                                            <input class="form-check-input" type="checkbox" id="horario_domingo_ativo" name="horario_domingo_ativo" data-chave="horario_domingo_ativo">
                                        </div>
                                    </div>
                                    <div class="row g-2" id="horarios-domingo" style="display: none;">
                                        <div class="col-sm-6">
                                            <label class="form-label" for="horario_inicio_domingo">Início</label>
                                            <input type="time" class="form-control" id="horario_inicio_domingo" name="horario_inicio_domingo" data-chave="horario_inicio_domingo">
                                        </div>
                                        <div class="col-sm-6">
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
        </div>
    </form>
</main>

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
function smtpSenhaJaSalva() {
    for (const categoria in configuracoes) {
        const row = configuracoes[categoria].find(c => c.chave === 'smtp_password');
        if (row && row._senha_definida) {
            return true;
        }
    }
    return false;
}

function preencherFormulario() {
    // Preencher campos de texto e select
    document.querySelectorAll('[data-chave]').forEach(element => {
        const chave = element.getAttribute('data-chave');
        const valor = getValorConfiguracao(chave);
        
        if (element.type === 'checkbox') {
            element.checked = valor === '1' || valor === 'true';
        } else if (element.type === 'range') {
            element.value = valor || 30;
            const ev = document.getElementById('sessao_expira_val');
            if (ev) ev.textContent = element.value;
        } else {
            element.value = valor || '';
        }
    });

    const hint = document.getElementById('smtp_password_hint');
    if (hint) {
        hint.textContent = smtpSenhaJaSalva()
            ? 'Uma senha já está salva no servidor. Preencha apenas se quiser alterá-la.'
            : 'Informe a senha do SMTP para o primeiro envio.';
    }
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

            if (chave === 'smtp_password' && !String(valor).trim()) {
                return;
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
            throw new Error(data.erro || data.error || 'Erro ao salvar configurações');
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
            const ev = document.getElementById('sessao_expira_val');
            if (ev) ev.textContent = this.value;
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

    const btnTestarSmtp = document.getElementById('btn-testar-smtp');
    if (btnTestarSmtp) {
        btnTestarSmtp.addEventListener('click', async function() {
            const email = document.getElementById('smtp_email_teste').value.trim();
            if (!email) {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Informe o e-mail que receberá o teste.' });
                return;
            }
            btnTestarSmtp.disabled = true;
            const original = btnTestarSmtp.innerHTML;
            btnTestarSmtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';
            try {
                const response = await fetch('backend/api/configuracoes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'testar_smtp', email_teste: email })
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Enviado', text: data.message || 'Verifique a caixa de entrada e o spam.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Falha', text: data.error || data.message || 'Não foi possível enviar.' });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao comunicar com o servidor.' });
            } finally {
                btnTestarSmtp.disabled = false;
                btnTestarSmtp.innerHTML = original;
            }
        });
    }

    // Modal Alterar Senha

    const btnAlterarSenha = document.getElementById('btn-alterar-senha-config');
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