<?php
// Incluir configurações
require_once 'config/config.php';
require_once 'config/session.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

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
                <!-- Pedidos Pendentes -->
                <div class="col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-clock-history text-warning fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Pedidos Pendentes</h6>
                                    <h3 class="card-text text-warning" id="pedidos-pendentes">0</h3>
                                </div>
                            </div>
                            <small class="text-muted">Aguardando aprovação ou processamento</small>
                        </div>
                    </div>
                </div>
                
                <!-- Alertas -->
                <div class="col-lg-3">
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
                        </div>
                    </div>
                </div>
                
                <!-- Movimentação do Estoque -->
                <div class="col-lg-3">
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
                        </div>
                    </div>
                </div>
                
                <!-- Tickets Abertos -->
                <div class="col-lg-3">
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
