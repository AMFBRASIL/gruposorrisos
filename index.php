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
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        .dash-pedidos-fases .fase-item {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            padding: 0.5rem 0.35rem;
            background: #fafafa;
            transition: background 0.15s ease;
        }
        .dash-pedidos-fases .fase-item:hover {
            background: #f0f4ff;
        }
        .dash-pedidos-fases .fase-val {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
            color: #334155;
        }
        .dash-pedidos-fases .fase-nome {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            font-weight: 600;
            margin-top: 0.15rem;
        }
        .dash-pedidos-fases .fase-hint {
            font-size: 0.62rem;
            color: #94a3b8;
            margin-top: 0.1rem;
            line-height: 1.15;
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
                        <img src="assets/img/warehouse.svg" alt="Dashboard" style="height: 100px; opacity: 0.2;">
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
                    <div class="card card-summary bg-success">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-box-seam"></i></div>
                            <h6 class="card-title">Total de Produtos</h6>
                            <h3 class="card-text" id="total-produtos">0</h3>
                            <small>Produtos cadastrados no sistema</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary bg-warning">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                            <h6 class="card-title">Estoque Baixo</h6>
                            <h3 class="card-text" id="estoque-baixo">0</h3>
                            <small>Produtos abaixo do mínimo</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary bg-danger">
                        <div class="card-body">
                            <div class="icon"><i class="bi bi-x-circle"></i></div>
                            <h6 class="card-title">Estoque Zerado</h6>
                            <h3 class="card-text" id="estoque-zerado">0</h3>
                            <small>Produtos sem estoque</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-summary bg-info">
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
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-2">
                                        <i class="bi bi-cart-check text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Pedidos de compra</h6>
                                        <small class="text-muted">Por fase do fluxo</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        Total em aberto: <span id="pedidos-pendentes">0</span>
                                    </span>
                                </div>
                            </div>
                            <p class="small text-muted mb-2">Com uma clínica selecionada acima, as quantidades são só dela. “Total em aberto” soma as fases até faturamento concluído (sem em trânsito / entregue).</p>
                            <div class="dash-pedidos-fases row row-cols-2 row-cols-sm-3 row-cols-xl-5 g-2 mb-3">
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val text-info" id="dash-ped-fase-em-analise">0</div>
                                        <div class="fase-nome">Em análise</div>
                                        <div class="fase-hint">Novo pedido / aguardando aprovação interna</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val text-secondary" id="dash-ped-fase-pendente">0</div>
                                        <div class="fase-nome">Pendente</div>
                                        <div class="fase-hint">Aguardando resposta do fornecedor</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val text-success" id="dash-ped-fase-aprovado">0</div>
                                        <div class="fase-nome">Aprovado</div>
                                        <div class="fase-hint">Cotação aprovada pelo compras</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val text-primary" id="dash-ped-fase-envio-faturamento">0</div>
                                        <div class="fase-nome">Envio p/ faturamento</div>
                                        <div class="fase-hint">Liberado para faturar</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fase-item text-center h-100">
                                        <div class="fase-val text-danger" id="dash-ped-fase-em-faturamento">0</div>
                                        <div class="fase-nome">Em faturamento</div>
                                        <div class="fase-hint">Aprovado p/ faturar / faturado</div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100" onclick="window.location.href='pedidos-compra.php'">
                                <i class="bi bi-eye me-1"></i>Ver todos os pedidos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Alertas -->
                <div class="col-lg-2">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Alertas</h6>
                                    <h3 class="card-text text-danger" id="total-alertas">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Estoque baixo e vencimentos próximos</small>
                            <div class="mt-3">
                                <button class="btn btn-danger btn-sm w-100" onclick="window.location.href='alertas.php'">
                                    <i class="bi bi-bell me-1"></i>Ver Alertas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Movimentação do Estoque -->
                <div class="col-lg-2">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-arrow-left-right text-info fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Movimentações</h6>
                                    <h3 class="card-text text-info" id="movimentacoes-hoje">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Movimentações realizadas hoje</small>
                            <div class="mt-3">
                                <button class="btn btn-info btn-sm w-100" onclick="window.location.href='movimentacoes.php'">
                                    <i class="bi bi-arrow-left-right me-1"></i>Ver Movimentações
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tickets Abertos -->
                <div class="col-lg-2">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-ticket-detailed text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Tickets Abertos</h6>
                                    <h3 class="card-text text-primary" id="tickets-abertos">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Tickets em aberto no sistema</small>
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm w-100" onclick="window.location.href='tickets.php'">
                                    <i class="bi bi-ticket-detailed me-1"></i>Ver Tickets
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            
            <!-- Segunda Linha -->
            <div class="row g-3 mt-3">
                <!-- Produtos com Estoque Baixo -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
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
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-activity text-success me-2"></i>
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
        <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
        <div class="fw-semibold text-secondary">Processando troca de filial...</div>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Index JS -->
<script src="assets/js/index.js"></script>
</body>
</html>
