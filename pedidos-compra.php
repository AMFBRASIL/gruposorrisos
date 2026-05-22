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
$usuarioEhAdmin = isAdmin();
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
        .item-pendente-resposta {
            background: #fff5f5 !important;
            border-left: 4px solid #dc3545;
        }
        .badge-item-pendente-resposta {
            background: #dc3545;
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-left: 8px;
            display: inline-block;
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
            overflow-x: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        /* Cabeçalho fixo ao rolar itens (Novo Pedido + Visualizar) */
        #modalNovoPedido .itens-scroll-box table,
        #modalVisualizarPedido .itens-scroll-box table {
            margin-bottom: 0;
        }

        /* table-responsive quebra sticky; o scroll fica no .itens-scroll-box */
        #modalNovoPedido .itens-scroll-box .table-responsive,
        #modalVisualizarPedido .itens-scroll-box .table-responsive {
            overflow: visible;
        }

        #modalNovoPedido .itens-scroll-box thead th,
        #modalVisualizarPedido .itens-scroll-box thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #f8f9fa;
            box-shadow: 0 1px 0 #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }

        #modalVisualizarPedido .itens-scroll-box tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background-color: #f8f9fa;
            box-shadow: 0 -1px 0 #dee2e6;
            vertical-align: middle;
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
                            <option value="aguardando_cotacao">Aguardando Cotação</option>
                            <option value="em_cotacao">Em Cotação</option>
                            <option value="aguardando_aprovacao_socio">Aguard. Aprovação Sócio</option>
                            <option value="aprovado_socio">Aprovado pelo Sócio</option>
                            <option value="aguardando_faturamento">Aguard. Faturamento</option>
                            <option value="em_faturamento">Em Faturamento</option>
                            <option value="em_conferencia">Em Conferência</option>
                            <option value="finalizado">Finalizado</option>
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

                    <div id="busca-itens-pedido-existente" class="mb-3 d-none">
                        <label for="filtro-itens-pedido-existente" class="form-label mb-1">
                            Buscar item na lista já criada
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="filtro-itens-pedido-existente"
                                placeholder="Digite código ou nome para conferir se o item existe no pedido..."
                                oninput="filtrarItensPedidoExistente()">
                            <button type="button" class="btn btn-outline-secondary" onclick="limparBuscaItensPedidoExistente()">
                                <i class="bi bi-x-circle me-1"></i>Limpar
                            </button>
                        </div>
                        <small class="text-muted" id="resultado-busca-itens-pedido-existente">
                            Informe um termo para conferir os itens deste pedido.
                        </small>
                    </div>

                    <div class="alert alert-danger py-2 px-3 d-none mb-3" id="edit-alerta-itens-pendentes-resposta">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <span id="edit-alerta-itens-pendentes-resposta-texto"></span>
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

                <!-- Fluxo de Status (horizontal, largura total) -->
                <div class="card mb-4 border-0 shadow-sm" id="card-fluxo-status-pedido">
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                <h6 class="mb-0 text-primary">Fluxo de Status</h6>
                            </div>
                            <small class="text-muted">Acompanhe a evolução do pedido</small>
                        </div>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <div class="status-flow status-flow--horizontal" id="status-flow">
                            <!-- Fluxo de status será carregado aqui -->
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
                        <div class="alert alert-danger py-2 px-3 d-none mb-3" id="view-alerta-itens-pendentes-resposta">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <span id="view-alerta-itens-pendentes-resposta-texto"></span>
                        </div>
                        <div class="itens-scroll-box">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Material</th>
                                        <th class="text-center">Quantidade Solicitada</th>
                                        <th class="text-center">Quantidade Disponível</th>
                                        <th class="text-center">Preço Unit.</th>
                                        <th class="text-center">Total</th>
                                        <th>Observação do Item</th>
                                    </tr>
                                </thead>
                                <tbody id="view_itens_tbody">
                                    <!-- Itens serão carregados aqui -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total:</th>
                                        <th class="text-center" id="view_itens_total_footer">R$ 0,00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Observações, NF e Histórico -->
                <div class="row g-3 g-lg-4">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chat-text me-2 text-primary"></i>
                                    <h6 class="mb-0">Observações</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-item mb-0">
                                    <div class="info-value" id="view_observacoes">Nenhuma observação registrada</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-warning bg-opacity-10 border-0 py-2">
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
                            <div class="card-body py-2">
                                <div class="timeline-container timeline-compact" id="timeline-status" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Timeline será carregada aqui -->
                                </div>
                                <div class="row g-2 mt-2 pt-2 border-top small">
                                    <div class="col-6">
                                        <div class="text-muted">Criado em</div>
                                        <div class="fw-semibold" id="view-data-criacao">—</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Última atualização</div>
                                        <div class="fw-semibold" id="view-data-atualizacao">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
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
                                    <p class="text-muted mb-3 small">Registro do último envio da Nota Fiscal pelo fornecedor.</p>
                                    <dl class="row small mb-3 g-2" id="nf-detalhes-lista">
                                        <dt class="col-sm-4 text-muted">Enviado em</dt>
                                        <dd class="col-sm-8 mb-1" id="view-nf-data-envio">—</dd>
                                        <dt class="col-sm-4 text-muted">Enviado por</dt>
                                        <dd class="col-sm-8 mb-1" id="view-nf-enviado-por">—</dd>
                                        <dt class="col-sm-4 text-muted">Nome do arquivo</dt>
                                        <dd class="col-sm-8 mb-1 text-break" id="view-nf-nome-arquivo">—</dd>
                                        <dt class="col-sm-4 text-muted">Tamanho</dt>
                                        <dd class="col-sm-8 mb-0" id="view-nf-tamanho">—</dd>
                                    </dl>
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-visualizar-nf" onclick="visualizarNFPedido()">
                                        <i class="bi bi-eye me-2"></i>Visualizar Nota Fiscal
                                    </button>
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
                    
                    <!-- Passo 2: Compras inicia cotação -->
                    <button type="button" class="btn btn-info d-none" id="btn-iniciar-cotacao">
                        <i class="bi bi-clipboard2-check me-2"></i>Iniciar Cotação (Compras)
                    </button>
                    
                    <!-- Passo 2→3: Compras envia ao sócio -->
                    <button type="button" class="btn btn-primary d-none" id="btn-enviar-aprovacao-socio">
                        <i class="bi bi-send me-2"></i>Enviar para Aprovação do Sócio
                    </button>
                    
                    <!-- Passo 3: Sócio aprova -->
                    <button type="button" class="btn btn-success d-none" id="btn-aprovar-socio">
                        <i class="bi bi-check-circle me-2"></i>Aprovar (Sócio)
                    </button>
                    
                    <!-- Passo 4: Compras encaminha faturamento -->
                    <button type="button" class="btn btn-warning d-none" id="btn-encaminhar-faturamento">
                        <i class="bi bi-receipt me-2"></i>Encaminhar para Faturamento (Compras)
                    </button>
                    
                    <!-- Passo 5: Clínica confirma recebimento → conferência -->
                    <button type="button" class="btn btn-primary d-none" id="btn-confirmar-recebimento">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Confirmar Recebimento (Conferência)
                    </button>
                    
                    <!-- Passo 5→6: Finalizar e entrada no estoque -->
                    <button type="button" class="btn btn-success d-none" id="btn-finalizar-pedido">
                        <i class="bi bi-check2-all me-2"></i>Finalizar Pedido
                    </button>
                    
                    <!-- Botão de Cancelamento (disponível para vários status) -->
                    <button type="button" class="btn btn-danger d-none" id="btn-cancelar">
                        <i class="bi bi-x-circle me-2"></i>Cancelar Pedido
                    </button>
                    
                    <!-- Botão de Voltar Status (para reverter quando necessário) -->
                    <button type="button" class="btn btn-outline-warning d-none" id="btn-voltar-status" onclick="mostrarOpcoesVoltarStatus()">
                        <i class="bi bi-arrow-left me-2"></i>Voltar Status
                    </button>

                    <!-- Reversão completa do pedido (admin) — disponível inclusive em status recebido -->
                    <?php if ($usuarioEhAdmin): ?>
                    <button type="button" class="btn btn-danger d-none" id="btn-reverter-pedido-admin" onclick="abrirModalReverterPedidoFromVisualizar()" title="Reverter pedido e estornar estoque">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reverter Pedido
                    </button>
                    <?php endif; ?>
                </div>
                
                <button type="button" class="btn btn-success" onclick="imprimirPedido()">
                    <i class="bi bi-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bloqueio de Edição -->
<div class="modal fade modal-modern" id="modalEdicaoBloqueadaPedido" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning bg-opacity-10 border-0">
                <div>
                    <h5 class="modal-title text-warning mb-1">
                        <i class="bi bi-lock-fill me-2"></i>Edição não permitida
                    </h5>
                    <div class="text-muted">Este pedido está em uma fase somente para acompanhamento.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Não é permitido editar este pedido.
                    </div>
                    <div id="bloqueio-edicao-mensagem">
                        O pedido está em uma etapa do processo que não permite alterações.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Pedido</div>
                                <div class="fw-bold" id="bloqueio-numero-pedido">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Situação atual</div>
                                <div class="fw-bold" id="bloqueio-status-atual">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Valor total</div>
                                <div class="fw-bold text-success" id="bloqueio-valor-total">R$ 0,00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Itens</div>
                                <div class="fw-bold" id="bloqueio-total-itens">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detalhes do Pedido</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 small">
                                    <div class="col-md-6"><strong>Clínica:</strong> <span id="bloqueio-filial">-</span></div>
                                    <div class="col-md-6"><strong>Fornecedor:</strong> <span id="bloqueio-fornecedor">-</span></div>
                                    <div class="col-md-6"><strong>Solicitante:</strong> <span id="bloqueio-solicitante">-</span></div>
                                    <div class="col-md-6"><strong>Data:</strong> <span id="bloqueio-data-pedido">-</span></div>
                                    <div class="col-md-6"><strong>Entrega prevista:</strong> <span id="bloqueio-data-entrega">-</span></div>
                                    <div class="col-md-6"><strong>Prioridade:</strong> <span id="bloqueio-prioridade">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Itens e Valores</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 320px;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Material</th>
                                                <th class="text-center">Qtd.</th>
                                                <th class="text-center">Preço</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bloqueio-itens-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Trajetória do Pedido</h6>
                            </div>
                            <div class="card-body">
                                <div id="bloqueio-fluxo-status"></div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0">
                                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timelapse / Histórico</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline-container" id="bloqueio-timeline-status" style="max-height: 420px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary" onclick="acompanharPedidoBloqueado()">
                    <i class="bi bi-eye me-2"></i>Acompanhar Pedido
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

<!-- Modal de Reversão de Pedido (apenas administradores) -->
<div class="modal fade modal-modern" id="modalReverterPedido" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger bg-opacity-10 border-0">
                <div>
                    <h5 class="modal-title text-danger mb-1">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reverter Pedido de Compra
                    </h5>
                    <div class="text-muted">
                        Confira <strong>tudo</strong> que será desfeito antes de confirmar. Esta ação é
                        <strong>irreversível</strong>.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="reverter-loading" class="text-center py-5">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Carregando prévia...</span>
                    </div>
                    <p class="mt-3 mb-0 text-muted">Calculando o que será revertido...</p>
                </div>

                <div id="reverter-erro" class="alert alert-danger d-none" role="alert"></div>

                <div id="reverter-conteudo" class="d-none">
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                        <div>
                            <div class="fw-semibold mb-1">Atenção: o pedido será excluído por completo.</div>
                            <div class="small">
                                Todas as movimentações de estoque originadas por este pedido serão
                                <strong>estornadas</strong> e os registros relacionados (itens, histórico de
                                status e mensagens de chat) serão <strong>apagados</strong>.
                                Use somente quando o pedido foi feito de forma errada ou em ambiente de teste.
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="rev-alerta-recebido">
                        <i class="bi bi-box-seam me-2"></i>
                        <strong>Pedido com status Recebido:</strong>
                        este pedido já gerou <strong><span class="rev-alerta-recebido-qtd">0</span> entrada(s)</strong>
                        no estoque. A reversão irá <strong>subtrair essas quantidades</strong> do saldo da clínica
                        antes de excluir o pedido.
                    </div>

                    <!-- Resumo do pedido -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Dados do Pedido</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 small">
                                <div class="col-md-4"><strong>Número:</strong> <span id="rev-numero-pedido">-</span></div>
                                <div class="col-md-4"><strong>Status atual:</strong> <span id="rev-status-atual">-</span></div>
                                <div class="col-md-4"><strong>Valor total:</strong> <span id="rev-valor-total">-</span></div>
                                <div class="col-md-4"><strong>Clínica:</strong> <span id="rev-filial">-</span></div>
                                <div class="col-md-4"><strong>Fornecedor:</strong> <span id="rev-fornecedor">-</span></div>
                                <div class="col-md-4"><strong>Solicitante:</strong> <span id="rev-solicitante">-</span></div>
                                <div class="col-md-4"><strong>Data do pedido:</strong> <span id="rev-data-pedido">-</span></div>
                                <div class="col-md-4"><strong>Entrega prevista:</strong> <span id="rev-data-entrega">-</span></div>
                                <div class="col-md-4"><strong>Nota fiscal:</strong> <span id="rev-tem-nf">-</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards de resumo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Itens do pedido</div>
                                    <div class="fs-4 fw-bold text-danger" id="rev-qtd-itens">0</div>
                                    <div class="small text-muted">serão apagados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Movimentações de estoque</div>
                                    <div class="fs-4 fw-bold text-danger" id="rev-qtd-movimentacoes">0</div>
                                    <div class="small text-muted">serão estornadas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Histórico de status</div>
                                    <div class="fs-4 fw-bold text-danger" id="rev-qtd-historico">0</div>
                                    <div class="small text-muted">registros apagados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Mensagens de chat</div>
                                    <div class="fs-4 fw-bold text-danger" id="rev-qtd-chat">0</div>
                                    <div class="small text-muted">serão apagadas</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Impacto no estoque -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0">
                                <i class="bi bi-box-seam me-2"></i>Impacto no Estoque
                            </h6>
                            <small class="text-muted">Estes saldos serão ajustados ao reverter o pedido.</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 260px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Material</th>
                                            <th>Clínica</th>
                                            <th class="text-center">Estoque atual</th>
                                            <th class="text-center">Será estornado</th>
                                            <th class="text-center">Estoque após reversão</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rev-tbody-impacto">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma movimentação de estoque vinculada a este pedido.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Movimentações detalhadas -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0">
                                <i class="bi bi-list-check me-2"></i>Movimentações que serão apagadas
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 240px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Material</th>
                                            <th class="text-center">Quantidade</th>
                                            <th>Clínica</th>
                                            <th>Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rev-tbody-movimentacoes">
                                        <tr><td colspan="6" class="text-center text-muted py-3">Sem movimentações.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Itens do pedido -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0"><i class="bi bi-box me-2"></i>Itens do pedido que serão apagados</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 220px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Material</th>
                                            <th class="text-center">Quantidade</th>
                                            <th class="text-center">Preço unitário</th>
                                            <th class="text-center">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rev-tbody-itens">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Sem itens.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Histórico de status -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Histórico de status que será apagado</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 200px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th>Usuário</th>
                                            <th>Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rev-tbody-historico">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Sem histórico.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Campo de observação obrigatória -->
                    <div class="mb-3">
                        <label for="rev-observacao" class="form-label fw-semibold">
                            <i class="bi bi-pencil-square me-1"></i>
                            Motivo da reversão <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="rev-observacao" rows="3"
                                  placeholder="Descreva por que este pedido está sendo revertido (ex.: pedido criado de forma errada, teste, duplicidade)."></textarea>
                        <small class="text-muted">Este motivo ficará registrado nos logs de auditoria do sistema.</small>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="rev-confirmacao">
                        <label class="form-check-label" for="rev-confirmacao">
                            Estou ciente de que esta ação é <strong>irreversível</strong> e desejo prosseguir.
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-reverter-pedido" disabled>
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reverter Pedido Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.usuarioEhAdmin = <?php echo $usuarioEhAdmin ? 'true' : 'false'; ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/pedidos-compra.js"></script>
</body>
</html>