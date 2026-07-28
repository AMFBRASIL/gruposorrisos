<?php
// Incluir configurações
require_once 'config/config.php';
require_once 'config/session.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verificar horário de funcionamento
require_once 'middleware/horario_middleware.php';

$user = getCurrentUser();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/index.css') ?: time(); ?>">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        :root {
            --brand-orange: #f57c00;
            --brand-orange-dark: #ef6c00;
            --brand-red: #e53935;
            --brand-gray: #757575;
            --brand-gray-dark: #616161;
        }
        .dashboard-header {
            background: linear-gradient(90deg, #ff9800 0%, #f57c00 55%, #ef6c00 100%) !important;
            color: #fff;
            border-radius: 18px;
            padding: 28px 28px;
            box-shadow: 0 10px 28px rgba(245, 124, 0, 0.28);
        }
        .dashboard-header h2 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.35rem;
        }
        .dashboard-header p {
            opacity: 0.95;
            margin-bottom: 1rem !important;
        }
        .dashboard-header .btn-light {
            background: rgba(255,255,255,0.95);
            border: none;
            color: var(--brand-orange);
            font-weight: 600;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
        }
        .dashboard-header .btn-outline-light {
            border: 1.5px solid rgba(255,255,255,0.85);
            color: #fff;
            font-weight: 600;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            background: transparent;
        }
        .dashboard-header .btn-outline-light:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .dashboard-header-mark {
            opacity: 0.22;
            pointer-events: none;
            user-select: none;
        }
        .brand-orange { color: var(--brand-orange) !important; }
        .brand-red { color: var(--brand-red) !important; }
        .brand-gray { color: var(--brand-gray) !important; }
        .bg-brand-orange { background-color: var(--brand-orange) !important; border-color: var(--brand-orange) !important; color: #fff !important; }
        .btn-brand-orange { background-color: var(--brand-orange); border-color: var(--brand-orange); color: #fff; border-radius: 10px; font-weight: 600; }
        .btn-brand-orange:hover { background-color: var(--brand-orange-dark); border-color: var(--brand-orange-dark); color: #fff; }
        .btn-brand-red { background-color: var(--brand-red); border-color: var(--brand-red); color: #fff; border-radius: 10px; font-weight: 600; }
        .btn-brand-red:hover { background-color: #c62828; border-color: #c62828; color: #fff; }
        .btn-brand-gray { background-color: var(--brand-gray); border-color: var(--brand-gray); color: #fff; border-radius: 10px; font-weight: 600; }
        .btn-brand-gray:hover { background-color: var(--brand-gray-dark); border-color: var(--brand-gray-dark); color: #fff; }
        .bg-brand-orange-soft { background-color: rgba(245, 124, 0, 0.12) !important; }
        .bg-brand-red-soft { background-color: rgba(229, 57, 53, 0.12) !important; }
        .bg-brand-gray-soft { background-color: rgba(117, 117, 117, 0.12) !important; }

        /* Cards resumo — estilo pastel da referência */
        .card-summary {
            border: none !important;
            border-radius: 16px !important;
            min-height: 118px;
            position: relative;
            overflow: hidden;
            box-shadow: none;
        }
        .card-summary .card-body {
            padding: 1.15rem 1.25rem;
            position: relative;
            z-index: 1;
        }
        .card-summary .card-title {
            font-size: 0.92rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .card-summary .card-text {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            line-height: 1.1;
        }
        .card-summary small {
            font-size: 0.78rem;
            opacity: 0.85;
        }
        .card-summary .icon {
            position: absolute;
            top: 50%;
            right: 1.1rem;
            transform: translateY(-50%);
            font-size: 2.6rem;
            opacity: 0.35;
            z-index: 0;
        }
        .card-summary.card-produtos {
            background: #fff3e0 !important;
            color: #ef6c00 !important;
        }
        .card-summary.card-baixo {
            background: #fff8e1 !important;
            color: #f9a825 !important;
        }
        .card-summary.card-zerado {
            background: #ffebee !important;
            color: #e53935 !important;
        }
        .card-summary.card-valor {
            background: #f5f5f5 !important;
            color: #757575 !important;
        }
        .card-summary.card-produtos .card-title,
        .card-summary.card-produtos .card-text,
        .card-summary.card-produtos small { color: #ef6c00 !important; }
        .card-summary.card-baixo .card-title,
        .card-summary.card-baixo .card-text,
        .card-summary.card-baixo small { color: #f9a825 !important; }
        .card-summary.card-zerado .card-title,
        .card-summary.card-zerado .card-text,
        .card-summary.card-zerado small { color: #e53935 !important; }
        .card-summary.card-valor .card-title,
        .card-summary.card-valor .card-text,
        .card-summary.card-valor small { color: #757575 !important; }

        .dash-panel {
            border: 1px solid #eef0f3 !important;
            border-radius: 16px !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
        }
        .dash-pedidos-fases .fase-item {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            padding: 0.55rem 0.35rem;
            background: #fafafa;
            transition: background 0.15s ease;
        }
        .dash-pedidos-fases .fase-item:hover { background: #fff3e0; }
        .dash-pedidos-fases .fase-val {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .dash-pedidos-fases .fase-nome {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            font-weight: 600;
            margin-top: 0.15rem;
        }
        .dash-pedidos-fases .fase-hint {
            font-size: 0.6rem;
            color: #94a3b8;
            margin-top: 0.1rem;
            line-height: 1.15;
        }
        .badge-aberto {
            background: #fff3e0 !important;
            color: #f57c00 !important;
            border: 1px solid rgba(245,124,0,0.25);
            font-weight: 600;
        }
    </style>

</head>
<body>
<div class="container-fluid">

<?php include 'menu.php'; ?>

<!-- Main Content -->
<main class="main-content">
            <!-- Header -->
            <div class="dashboard-header mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h2>Bem-vindo, <?php echo htmlspecialchars($user['nome'] ?? 'Usuário'); ?>!</h2>
                        <p class="mb-3">Gerencie seu estoque de forma inteligente e eficiente</p>
                        <button class="btn btn-light btn-sm me-2" onclick="window.location.href='addMaterial'"><i class="bi bi-plus-circle me-1"></i>Novo Material</button>
                        <button class="btn btn-outline-light btn-sm" onclick="window.location.href='movimentacoes'"><i class="bi bi-arrow-left-right me-1"></i>Movimentação</button>
                    </div>
                    <div class="d-none d-md-block">
                        <img src="<?php echo htmlspecialchars(app_url('assets/img/logo-house-white.svg')); ?>" alt="" class="dashboard-header-mark" width="96" height="96">
                    </div>
                </div>
            </div>
            <!-- Selector de Filiais -->
            <div class="row mb-4">
                <div class="col-12 col-md-10">
                    <label for="selector-filial" class="form-label fw-semibold">Selecionar Clínica</label>
                    <div id="filial-selector-container"></div>
                </div>
            </div>
            <!-- Cards Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-summary card-produtos">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-box"></i></div>
                            <h6 class="card-title">Total de Produtos</h6>
                            <h3 class="card-text" id="total-produtos">0</h3>
                            <small>Produtos cadastrados no sistema</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary card-baixo">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-bell"></i></div>
                            <h6 class="card-title">Estoque Baixo</h6>
                            <h3 class="card-text" id="estoque-baixo">0</h3>
                            <small>Produtos abaixo do mínimo</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary card-zerado">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-box-seam"></i></div>
                            <h6 class="card-title">Estoque Zerado</h6>
                            <h3 class="card-text" id="estoque-zerado">0</h3>
                            <small>Produtos sem estoque</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary card-valor">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-currency-dollar"></i></div>
                            <h6 class="card-title">Valor Total</h6>
                            <h3 class="card-text" id="valor-total">R$ 0,00</h3>
                            <small>Valor total em estoque</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main Row -->
            <div class="row g-3">
                <!-- Pedidos de compra — fases -->
                <div class="col-lg-6">
                    <div class="card h-100 dash-panel">
                        <div class="card-body py-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-brand-orange-soft p-2">
                                        <i class="bi bi-cart3 brand-orange fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Pedidos de compra</h6>
                                        <small class="text-muted">Por fase do fluxo</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-aberto rounded-pill px-3 py-2">
                                        Total em aberto <span id="pedidos-pendentes">0</span>
                                    </span>
                                </div>
                            </div>
                            <p class="small text-muted mb-2">Com uma clínica selecionada acima, as quantidades são só dela. “Total em aberto” soma as fases até faturamento concluído (sem em trânsito / entregue).</p>
                            <div class="dash-pedidos-fases row row-cols-2 row-cols-sm-3 row-cols-xl-5 g-2 mb-3">
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val brand-gray" id="dash-ped-fase-em-analise">0</div>
                                        <div class="fase-nome">Aguardando cotação</div>
                                        <div class="fase-hint">Inclui pedidos criados</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val brand-orange" id="dash-ped-fase-pendente">0</div>
                                        <div class="fase-nome">Em cotação</div>
                                        <div class="fase-hint">Com pelo menos 1 cotação enviada</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val brand-orange" id="dash-ped-fase-aprovado">0</div>
                                        <div class="fase-nome">Aprovação sócio</div>
                                        <div class="fase-hint">Aguardando aprovação do sócio</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val brand-gray" id="dash-ped-fase-envio-faturamento">0</div>
                                        <div class="fase-nome">Envio p/ faturamento</div>
                                        <div class="fase-hint">Pedido enviado ao fornecedor</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val brand-red" id="dash-ped-fase-em-faturamento">0</div>
                                        <div class="fase-nome">Em faturamento</div>
                                        <div class="fase-hint">Com pelo menos 1 nota recebida</div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-brand-orange btn-sm w-100" onclick="window.location.href='pedidos-compra.php'">
                                <i class="bi bi-eye me-1"></i>Ver todos os pedidos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Alertas -->
                <div class="col-lg-2">
                    <div class="card h-100 dash-panel">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-brand-red-soft rounded-circle p-3 me-3">
                                    <i class="bi bi-exclamation-triangle brand-red fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Alertas</h6>
                                    <h3 class="card-text brand-red mb-0" id="total-alertas">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Estoque baixo e vencimentos próximos</small>
                            <div class="mt-3">
                                <button class="btn btn-brand-red btn-sm w-100" onclick="window.location.href='alertas.php'">
                                    Ver Alertas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Movimentação do Estoque -->
                <div class="col-lg-2">
                    <div class="card h-100 dash-panel">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-brand-orange-soft rounded-circle p-3 me-3">
                                    <i class="bi bi-arrow-left-right brand-orange fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Movimentações</h6>
                                    <h3 class="card-text brand-orange mb-0" id="movimentacoes-hoje">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Movimentações realizadas hoje</small>
                            <div class="mt-3">
                                <button class="btn btn-brand-orange btn-sm w-100" onclick="window.location.href='movimentacoes.php'">
                                    Ver Movimentações
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tickets Abertos -->
                <div class="col-lg-2">
                    <div class="card h-100 dash-panel">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-brand-gray-soft rounded-circle p-3 me-3">
                                    <i class="bi bi-envelope brand-gray fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Tickets Abertos</h6>
                                    <h3 class="card-text brand-gray mb-0" id="tickets-abertos">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Tickets em aberto no sistema</small>
                            <div class="mt-3">
                                <button class="btn btn-brand-gray btn-sm w-100" onclick="window.location.href='tickets.php'">
                                    Ver Tickets
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            
            <!-- Segunda Linha -->
            <div class="row g-3 mt-3">
                <!-- Produtos com Estoque Baixo -->
                <div class="col-lg-8">
                    <div class="card h-100 dash-panel">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-exclamation-triangle brand-orange me-2"></i>
                                Produtos com Estoque Baixo
                            </h5>
                            <div id="produtos-estoque-baixo">
                                <div class="text-center text-muted">
                                    <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                                    <p>Carregando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumo de Atividades -->
                <div class="col-lg-4">
                    <div class="card h-100 dash-panel">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-activity brand-gray me-2"></i>
                                Resumo de Atividades
                            </h5>
                            <div id="resumo-atividades">
                                <div class="text-center text-muted">
                                    <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                                    <p>Carregando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
<!-- Modal de Loading para troca de filial -->
<div class="modal fade" id="modalTrocaFilial" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="d-flex flex-column align-items-center justify-content-center p-4 bg-white rounded-4 shadow">
        <div class="spinner-border mb-3" role="status" style="width: 2.5rem; height: 2.5rem; color:#f57c00;"></div>
        <div class="fw-semibold text-secondary">Processando troca de filial...</div>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.USUARIO_FILIAL_ID = <?php echo json_encode(getCurrentUserFilialId() ? (int) getCurrentUserFilialId() : null); ?>;
</script>
<!-- Index JS -->
<script src="assets/js/index.js?v=<?php echo @filemtime(__DIR__ . '/assets/js/index.js') ?: time(); ?>"></script>
</body>
</html>
