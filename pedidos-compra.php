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

// Registrar acesso à página (verificação já feita no menu.php)
$controllerAcesso->registrarAcessoPagina();

$menuActive = 'pedidos_compra';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Pedidos de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/pedidocompra.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/status-respondido.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .autocomplete-container {
            position: relative;
        }
        
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .autocomplete-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .autocomplete-item:hover {
            background-color: #f8f9fa;
        }
        
        .autocomplete-item.selected {
            background-color: #007bff;
            color: white;
        }
        
        /* Estilos simples para a tabela */
        .table-simple {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .table-simple thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table-simple th {
            border: none;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #495057;
            background-color: #f8f9fa;
        }
        
        .table-simple tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .table-simple tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .table-simple td {
            padding: 12px 16px;
            vertical-align: middle;
            border: none;
        }
        
        /* Badges simples */
        .badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
        }

        .itens-scroll-box {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        
        /* Botões de ação simples */
        .btn-action-simple {
            border-radius: 4px;
            padding: 6px 10px;
            margin: 0 2px;
            transition: background-color 0.2s ease;
        }
        
        .btn-action-simple:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .table-simple th,
            .table-simple td {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
            
            .badge {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-cart-check"></i>Pedidos de Compra</span>
    </div>
    <div class="subtitle">Gerencie os pedidos de compra do seu estoque</div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
        <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <button class="btn btn-primary btn-action" onclick="abrirModalNovoPedido()" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Pedido</button>
    </div>
    
    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Pedidos</div>
                    <div class="card-value" id="total-pedidos">0</div>
                    <div class="text-success small" id="texto-total">Carregando...</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Pedidos Pendentes</div>
                    <div class="card-value" style="color:#eab308;" id="pedidos-pendentes">0</div>
                    <div class="text-muted small">Aguardando aprovação</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Em Produção</div>
                    <div class="card-value" style="color:#3b82f6;" id="em-producao">0</div>
                    <div class="text-muted small">Em andamento</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Valor Total</div>
                    <div class="card-value" style="color:#22c55e;" id="valor-total">R$ 0,00</div>
                    <div class="text-muted small">Todos os pedidos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="filters-title">Filtros e Busca</div>
            <div class="filters-subtitle">Busque e filtre pedidos por diferentes critérios</div>
            <form id="filtrosForm" class="mb-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por número, fornecedor ou observações...">
                    <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                </div>
                <div class="row g-2" id="filtrosAvancados" style="display: none;">
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-status">
                            <option value="">Todos os Status</option>
                            <option value="pendente">Pendente</option>
                            <option value="respondido">Respondido</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="em_producao">Em Produção</option>
                            <option value="enviado">Enviado</option>
                            <option value="recebido">Recebido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-fornecedor">
                            <option value="">Todos os Fornecedores</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="data-inicio" placeholder="Data Início">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="limparFiltros()">
                            <i class="bi bi-x-circle me-1"></i> Limpar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de Pedidos -->
    <div class="card">
        <div class="card-body">
            <div id="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando pedidos...</p>
            </div>
            
            <div id="tabela-container" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover table-simple">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Fornecedor</th>
                                <th>Data Pedido</th>
                                <th>Entrega Prevista</th>
                                <th>Prioridade</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="pedidos-tbody">
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div id="paginacao" class="d-flex justify-content-between align-items-center mt-3">
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Novo Pedido -->
<div class="modal fade modal-modern" id="modalNovoPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Novo Pedido de Compra</h5>
                    <div class="text-muted">Preencha os dados do pedido</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoPedido">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required-field">Clínica</label>
                            <select class="form-select" id="novo_id_filial" name="id_filial" required>
                                <option value="">Selecione uma Clínica</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required-field">Fornecedor</label>
                            <select class="form-select" id="novo_id_fornecedor" name="id_fornecedor" required onchange="carregarMateriaisEstoqueBaixo()">
                                <option value="">Selecione um fornecedor</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de Entrega Prevista</label>
                            <input type="date" class="form-control" id="novo_data_entrega_prevista" name="data_entrega_prevista">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required-field">Prioridade</label>
                            <select class="form-select" id="novo_prioridade" name="prioridade" required onchange="ajustarPrazoEntrega()">
                                <option value="padrao">Padrão - Até 8 dias</option>
                                <option value="critico">Crítico - Até 3 dias</option>
                                <option value="urgente">Urgente - Hoje/Imediato</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prazo de Entrega (dias)</label>
                            <input type="number" class="form-control" id="novo_prazo_entrega" name="prazo_entrega" min="1" max="365" value="8" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" id="novo_observacoes" name="observacoes" rows="3" placeholder="Observações sobre o pedido"></textarea>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0" id="titulo-materiais">Materiais com Estoque Baixo/Negativo</h6>
                        <div class="d-flex align-items-center gap-2">
                            <input type="file" id="input-csv-cliente-edicao" accept=".csv,text/csv" class="d-none">
                            <button type="button" class="btn btn-outline-primary btn-sm d-none" id="btn-importar-csv-cliente-edicao">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Importar CSV Cliente
                            </button>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle d-none" id="badge-itens-importados-csv">
                                Itens importados: 0
                            </span>
                            <label for="filtro-estoque-pedido" class="form-label mb-0 small text-muted">Filtro:</label>
                            <select class="form-select form-select-sm" id="filtro-estoque-pedido" style="width: 180px;" onchange="carregarMateriaisEstoqueBaixo()">
                                <option value="critico" selected>Estoque Crítico</option>
                                <option value="normal">Estoque Normal</option>
                                <option value="todos">Todos</option>
                            </select>
                            <span class="text-muted small" id="subtitulo-materiais">Selecione uma Clínica e um fornecedor para carregar os materiais</span>
                        </div>
                    </div>

                    <!-- Filtro por nome para materiais com estoque baixo/negativo -->
                    <div id="filtro-materiais-baixo" class="mb-3" style="display: none;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="filtro-nome-material-baixo"
                                placeholder="Filtrar materiais listados por nome..."
                                oninput="filtrarMateriaisEstoqueBaixoPorNome()">
                            <button type="button" class="btn btn-outline-secondary" onclick="limparFiltroMateriaisEstoqueBaixo()">
                                <i class="bi bi-x-circle me-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Campo de pesquisa para pedidos críticos/urgentes -->
                    <div id="pesquisa-material" style="display: none;" class="mb-3">
                        <div class="autocomplete-container">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="busca-material" placeholder="Digite o código ou nome do material..." autocomplete="off">
                                <button class="btn btn-outline-primary" type="button" onclick="pesquisarMaterial()">
                                    <i class="bi bi-search"></i> Pesquisar
                                </button>
                            </div>
                            <div class="autocomplete-results" id="autocomplete-results" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 d-none" id="itens-nao-encontrados-csv-box">
                        <div class="fw-semibold mb-1">
                            <i class="bi bi-exclamation-triangle me-1"></i>Itens do CSV não encontrados no catálogo
                        </div>
                        <div class="small" id="itens-nao-encontrados-csv-lista"></div>
                    </div>
                    
                    <div id="materiais-container" class="itens-scroll-box p-2">
                        <!-- Listagem de materiais será carregada aqui -->
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <strong>Total de Itens:</strong>
                                <strong id="total-itens-modal">0</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <strong>Quantidade Total:</strong>
                                <strong id="total-quantidade-modal">0</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <strong>Total do Pedido:</strong>
                                <strong id="total-pedido-modal">R$ 0,00</strong>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarNovoPedido()">Salvar Pedido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Pedido -->
<div class="modal fade modal-modern" id="modalVisualizarPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Detalhes do Pedido de Compra</h5>
                        <small class="opacity-75">Informações completas do pedido de compra</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Cabeçalho com informações principais -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="pedido-badge" id="view-status-badge">
                                    <i class="bi bi-clock me-2"></i>
                                    <span id="view-status-text">Pendente</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-1" id="view_numero_pedido">PED-2024-001</h4>
                                <p class="text-muted mb-0" id="view_data_pedido">14/01/2024 às 10:30</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="status-card" id="view-status-card">
                            <i class="bi bi-clock text-warning me-2"></i>
                            <span class="fw-bold" id="view-status-ativo">Pendente</span>
                        </div>
                    </div>
                </div>

                <!-- Navegação por abas -->
                <ul class="nav nav-tabs mb-4" id="pedidoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>Detalhes do Pedido
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab">
                            <i class="bi bi-chat-dots me-2"></i>Chat
                            <span class="badge bg-danger ms-1 d-none" id="chat-badge">0</span>
                        </button>
                    </li>
                </ul>
                
                <!-- Conteúdo das abas -->
                <div class="tab-content" id="pedidoTabContent">
                    <!-- Aba Detalhes -->
                    <div class="tab-pane fade show active" id="detalhes" role="tabpanel">
                        <div class="mt-3" id="modal-pedido-content">

                <!-- Informações do Pedido -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            <h6 class="mb-0">Informações do Pedido</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-building me-2 text-muted"></i>
                                        Clínica
                                    </div>
                                    <div class="info-value" id="view_filial">Clínica</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-truck me-2 text-muted"></i>
                                        Fornecedor
                                    </div>
                                    <div class="info-value" id="view_fornecedor">Fornecedor</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-calendar-event me-2 text-muted"></i>
                                        Entrega Prevista
                                    </div>
                                    <div class="info-value" id="view_data_entrega">Não informado</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-person me-2 text-muted"></i>
                                        Solicitante
                                    </div>
                                    <div class="info-value" id="view_solicitante">Usuário</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-exclamation-triangle me-2 text-muted"></i>
                                        Prioridade
                                    </div>
                                    <div class="info-value" id="view_prioridade">Padrão</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-calendar-check me-2 text-muted"></i>
                                        Prazo de Entrega
                                    </div>
                                    <div class="info-value" id="view_prazo_entrega">8 dias</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo e Valores -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2 text-success"></i>
                                    <h6 class="mb-0 text-success">Itens do Pedido</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-success bg-opacity-10">
                                                <i class="bi bi-list-ul text-success"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Total de Itens</div>
                                                <div class="metric-value" id="view-total-itens">0</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-info bg-opacity-10">
                                                <i class="bi bi-box text-info"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Quantidade Total</div>
                                                <div class="metric-value" id="view-quantidade-total">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar me-2 text-primary"></i>
                                    <h6 class="mb-0 text-primary">Valores</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-primary bg-opacity-10">
                                                <i class="bi bi-tag text-primary"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Preço Médio</div>
                                                <div class="metric-value" id="view-preco-medio">R$ 0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-success bg-opacity-10">
                                                <i class="bi bi-cash-stack text-success"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Valor Total</div>
                                                <div class="metric-value" id="view_valor_total">R$ 0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itens do Pedido -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-list-ul me-2 text-primary"></i>
                            <h6 class="mb-0">Itens do Pedido</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive itens-scroll-box">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Material</th>
                                        <th class="text-center">Quantidade Solicitada</th>
                                        <th class="text-center">Quantidade Disponível</th>
                                        <th class="text-center">Preço Unit.</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="view_itens_tbody">
                                    <!-- Itens serão carregados aqui -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th class="text-center" id="view_itens_total_footer">R$ 0,00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chat-text me-2 text-primary"></i>
                                    <h6 class="mb-0">Observações</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <div class="info-value" id="view_observacoes">Nenhuma observação registrada</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nota Fiscal -->
                        <div class="card border-0 shadow-sm" id="card-nota-fiscal" style="display: none;">
                            <div class="card-header bg-light border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>
                                    <h6 class="mb-0">Nota Fiscal</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="nf-status-container">
                                    <p class="text-muted mb-2">Nota Fiscal enviada pelo fornecedor</p>
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-visualizar-nf" onclick="visualizarNFPedido()">
                                        <i class="bi bi-eye me-2"></i>Visualizar Nota Fiscal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Quadro de Fluxo de Status -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-primary bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                    <h6 class="mb-0 text-primary">Fluxo de Status</h6>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="status-flow" id="status-flow">
                                    <!-- Fluxo de status será carregado aqui -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Histórico de Status -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-warning bg-opacity-10 border-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock-history me-2 text-warning"></i>
                                        <h6 class="mb-0 text-warning">Histórico de Status</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="mostrarHistoricoCompleto()">
                                        <i class="bi bi-eye me-1"></i>Ver Completo
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="timeline-container" id="timeline-status">
                                    <!-- Timeline será carregada aqui -->
                                </div>
                                <div class="mt-3">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-calendar-plus me-2 text-muted"></i>
                                            Criado em
                                        </div>
                                        <div class="info-value" id="view-data-criacao">01/01/2024</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-calendar-check me-2 text-muted"></i>
                                            Última atualização
                                        </div>
                                        <div class="info-value" id="view-data-atualizacao">01/01/2024</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                    
                    <!-- Aba Chat -->
                    <div class="tab-pane fade" id="chat" role="tabpanel">
                        <div class="mt-3">
                            <!-- Container do Chat -->
                            <div class="chat-container" style="height: 400px; border: 1px solid #dee2e6; border-radius: 8px; display: flex; flex-direction: column;">
                                <!-- Área de mensagens -->
                                <div class="chat-messages" id="chat-messages" style="flex: 1; overflow-y: auto; padding: 15px; background-color: #f8f9fa;">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-chat-dots fs-1"></i>
                                        <p>Carregando mensagens...</p>
                                    </div>
                                </div>
                                
                                <!-- Área de digitação -->
                                <div class="chat-input" style="border-top: 1px solid #dee2e6; padding: 15px; background-color: white;">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="nova-mensagem" placeholder="Digite sua mensagem..." maxlength="500">
                                        <button class="btn btn-primary" type="button" id="btn-enviar-mensagem">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Pressione Enter para enviar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary" id="btn-editar-pedido">
                    <i class="bi bi-pencil-square me-2"></i>Editar Pedido
                </button>
                
                <!-- Botões de Ação baseados no Status -->
                <div class="btn-group" role="group" id="acoes-pedido">
                    <!-- Botão de Envio de Email (sempre visível) -->
                    <button type="button" class="btn btn-secondary" onclick="enviarEmailPedido()">
                        <i class="bi bi-envelope me-2"></i>Enviar Email
                    </button>
                    
                    <!-- Botão Aprovar para Pendente (Em Análise → Pendente) -->
                    <button type="button" class="btn btn-success d-none" id="btn-aprovar-pendente">
                        <i class="bi bi-check-circle me-2"></i>Aprovar (Gestor)
                    </button>
                    
                    <!-- Botão Aprovar Cotação (Pendente → Aprovado Cotação) -->
                    <button type="button" class="btn btn-info d-none" id="btn-aprovar-cotacao">
                        <i class="bi bi-clipboard-check me-2"></i>Aprovar Cotação (Compras)
                    </button>
                    
                    <!-- Botão Aprovar Faturamento (Enviar para Faturamento → Aprovado para Faturar) -->
                    <button type="button" class="btn btn-warning d-none" id="btn-aprovar-faturamento">
                        <i class="bi bi-receipt me-2"></i>Aprovar Faturamento (Compras)
                    </button>
                    
                    <!-- Botão Marcar como Entregue (Em Trânsito → Entregue) -->
                    <button type="button" class="btn btn-success d-none" id="btn-marcar-entregue">
                        <i class="bi bi-box-seam me-2"></i>Marcar como Entregue
                    </button>
                    
                    <!-- Botão Confirmar Recebimento (Entregue → Recebido) -->
                    <button type="button" class="btn btn-primary d-none" id="btn-confirmar-recebimento">
                        <i class="bi bi-check2-all me-2"></i>Confirmar Recebimento
                    </button>
                    
                    <!-- Botão de Cancelamento (disponível para vários status) -->
                    <button type="button" class="btn btn-danger d-none" id="btn-cancelar">
                        <i class="bi bi-x-circle me-2"></i>Cancelar Pedido
                    </button>
                    
                    <!-- Botão de Voltar Status (para reverter quando necessário) -->
                    <button type="button" class="btn btn-outline-warning d-none" id="btn-voltar-status" onclick="mostrarOpcoesVoltarStatus()">
                        <i class="bi bi-arrow-left me-2"></i>Voltar Status
                    </button>
                </div>
                
                <button type="button" class="btn btn-success" onclick="imprimirPedido()">
                    <i class="bi bi-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Histórico Completo -->
<div class="modal fade" id="modalHistoricoCompleto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-historico">
            <div class="modal-header bg-warning bg-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock-history me-2 text-warning"></i>
                    <h5 class="modal-title text-warning">Histórico Completo de Status</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Pedido: <span id="historico-numero-pedido" class="text-dark"></span></h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-primary" id="historico-status-atual"></span>
                    </div>
                </div>
                
                <div class="timeline-container" id="timeline-completa" style="max-height: 400px;">
                    <!-- Timeline completa será carregada aqui -->
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>Opções de Reversão:</strong> Você pode voltar para um status anterior clicando no botão "Voltar Status" no modal principal.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="mostrarOpcoesVoltarStatus(); $('#modalHistoricoCompleto').modal('hide');">
                    <i class="bi bi-arrow-left me-2"></i>Voltar Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Processamento -->
<div class="modal fade" id="modalProcessandoPedido" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-hourglass-split me-2"></i>
                    Processando
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Processando...</span>
                </div>
                <p class="mb-0" id="texto-modal-processando-pedido">Processando dados...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação com Observação -->
<div class="modal fade" id="modalConfirmarStatus" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-question-circle me-2"></i>
                    Confirmar Alteração de Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p id="modal-status-message">Deseja alterar o status do pedido?</p>
                <div class="mb-3">
                    <label for="observacao-status" class="form-label">Observação (opcional)</label>
                    <textarea 
                        class="form-control" 
                        id="observacao-status" 
                        rows="4" 
                        placeholder="Digite uma observação sobre a alteração de status..."
                        style="resize: vertical;"
                    ></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-status">
                    <i class="bi bi-check-lg me-2"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/pedidos-compra.js"></script>
</body>
</html>