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

// Obter informações do usuário logado
$user = getCurrentUser();

$menuActive = 'movimentacoes';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentação de Estoque | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/movimentacoes.css">
    <link rel="stylesheet" href="assets/css/materiais.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
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
        
        .material-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        
        .material-item:hover {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        
        .estoque-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 8px;
        }
        
        .estoque-info small {
            font-size: 0.875rem;
        }
        
        .estoque-info .text-info {
            color: #0dcaf0 !important;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-arrow-repeat"></i>Movimentação de Estoque</span>
    </div>
    <div class="subtitle">Gerencie todas as movimentações de estoque do sistema</div>
    
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
        <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <button class="btn btn-primary btn-action" onclick="abrirModalNovaMovimentacao()" type="button"><i class="bi bi-plus-lg me-1"></i> Nova Movimentação</button>
    </div>
    
    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Movimentações</div>
                    <div class="card-value" id="total-movimentacoes">0</div>
                    <div class="text-success small" id="status-total-movimentacoes">Carregando...</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Entradas</div>
                    <div class="card-value" style="color:#22c55e;" id="entradas">0</div>
                    <div class="text-muted small" id="valor-entradas">R$ 0,00</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Saídas</div>
                    <div class="card-value" style="color:#ef4444;" id="saidas">0</div>
                    <div class="text-muted small" id="valor-saidas">R$ 0,00</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Materiais Movimentados</div>
                    <div class="card-value" style="color:#2563eb;" id="materiais-movimentados">0</div>
                    <div class="text-muted small">Produtos únicos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Segunda linha de cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Materiais de Brinde</div>
                    <div class="card-value" style="color:#8b5cf6;" id="materiais-brinde">0</div>
                    <div class="text-muted small">Brindes recebidos</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Valor dos Brindes</div>
                    <div class="card-value" style="color:#10b981;" id="valor-brindes">R$ 0,00</div>
                    <div class="text-muted small">Valor estimado total</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Fornecedores de Brinde</div>
                    <div class="card-value" style="color:#f59e0b;" id="fornecedores-brinde">0</div>
                    <div class="text-muted small">Fornecedores ativos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="filters-title">Filtros e Busca</div>
            <div class="filters-subtitle">Busque e filtre movimentações por diferentes critérios</div>
            <form id="filtrosForm" class="mb-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por número, material, documento...">
                    <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                </div>
                <div class="row g-2" id="filtrosAvancados" style="display: none;">
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-tipo">
                            <option value="">Todos os Tipos</option>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                            <option value="transferencia">Transferência</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="data-inicio" placeholder="Data Início">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="data-fim" placeholder="Data Fim">
                    </div>
                    <div class="col-md-3">
                                <select class="form-select" id="filtro-status">
                                    <option value="">Todos os Status</option>
                                    <option value="estoque">Em Estoque</option>
                                    <option value="baixo">Estoque Baixo</option>
                                    <option value="zerado">Sem Estoque</option>
                                    <option value="ressuprimento">Precisa Ressuprimento</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-brinde">
                                    <option value="">Todos os Materiais</option>
                                    <option value="brinde">Apenas Brindes</option>
                                    <option value="nao-brinde">Excluir Brindes</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                            </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de Movimentações -->
    <div class="card card-resumo">
        <div class="card-body">
            <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Movimentações</div>
            <div class="text-muted mb-3">Todas as movimentações de estoque registradas no sistema</div>
            
            <!-- Loading -->
            <div id="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando movimentações...</p>
            </div>
            
            <!-- Tabela -->
            <div class="table-responsive" id="tabela-container" style="display: none;">
                <table class="table table-borderless table-movimentacoes mb-0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Material</th>
                            <th>Quantidade</th>
                            <th>Estoque Anterior</th>
                            <th>Estoque Atual</th>
                            <th>Valor Unit.</th>
                            <th>Valor Total</th>
                            <th>Usuário</th>
                            <th>Data/Hora</th>
                            <th>Origem/Destino</th>
                            <th>Documento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="movimentacoes-tbody">
                        <!-- Dados carregados via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                <div class="text-muted">
                    Mostrando <span id="inicio-pagina">1</span> a <span id="fim-pagina">10</span> de <span id="total-registros">0</span> movimentações
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginacao-links">
                        <!-- Links de paginação -->
                    </ul>
                </nav>
            </div>
            
            <!-- Sem dados -->
            <div id="sem-dados" class="text-center py-4" style="display: none;">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-2">Nenhuma movimentação encontrada</p>
                <button class="btn btn-primary" onclick="abrirModalNovaMovimentacao()">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Primeira Movimentação
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Modal Nova Movimentação -->
<div class="modal fade modal-modern" id="modalNovaMovimentacao" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaMovimentacao">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Movimentação *</label>
                            <select class="form-select" id="tipo_movimentacao" required>
                                <option value="">Selecione o tipo</option>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                                <option value="transferencia">Transferência</option>
                                <option value="ajuste">Ajuste</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento" placeholder="NF, PED, VEN, etc.">
                        </div>
                        
                        <!-- Campo para identificar se é movimentação de brinde -->
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="movimentacao-brinde">
                                <label class="form-check-label" for="movimentacao-brinde">
                                    <i class="bi bi-gift me-2 text-warning"></i>
                                    <strong>Movimentação de Brinde</strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Campos específicos para brindes -->
                        <div class="col-md-6" id="fornecedor-brinde-group" style="display: none;">
                            <label class="form-label">Fornecedor do Brinde</label>
                            <select class="form-select" id="fornecedor-brinde">
                                <option value="">Selecione o fornecedor</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="valor-estimado-brinde-group" style="display: none;">
                            <label class="form-label">Valor Estimado do Brinde</label>
                            <input type="text" class="form-control" id="valor-estimado-brinde" placeholder="R$ 0,00">
                        </div>
                        
                        <!-- Campos específicos do tipo de movimentação -->
                        <div class="col-md-6" id="filial-origem-group" style="display: none;">
                            <label class="form-label">Clínica Origem</label>
                            <select class="form-select" id="id_filial_origem">
                                <option value="">Selecione a Clínica</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="filial-destino-group" style="display: none;">
                            <label class="form-label">Clínica Destino</label>
                            <select class="form-select" id="id_filial_destino">
                                <option value="">Selecione a Clínica</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="cliente-group" style="display: none;">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" id="id_cliente">
                                <option value="">Selecione o cliente</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" rows="2" placeholder="Observações sobre a movimentação"></textarea>
                        </div>
                        
                        <hr class="my-3">
                        
                        <!-- Seção de Materiais -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Materiais da Movimentação</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="adicionarMaterial()">
                                    <i class="bi bi-plus-lg"></i> Adicionar Material
                                </button>
                            </div>
                            
                            <div id="materiais-container">
                                <!-- Materiais serão adicionados aqui -->
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6 offset-md-6">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total da Movimentação:</strong>
                                        <strong id="total-movimentacao">R$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" id="btnCancelarMovimentacao" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarMovimentacao" onclick="salvarNovaMovimentacao()">
                    <span id="btnSalvarMovimentacaoIcon"><i class="bi bi-check-lg me-1"></i></span>
                    <span id="btnSalvarMovimentacaoText">Salvar Movimentação</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Movimentação -->
<div class="modal fade modal-modern" id="modalVisualizarMovimentacao" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Detalhes da Movimentação</h5>
                        <small class="opacity-75">Informações completas da movimentação de estoque</small>
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
                                <div class="movimentacao-badge" id="view-tipo-badge">
                                    <i class="bi bi-arrow-up-circle me-2"></i>
                                    <span id="view-tipo-text">Entrada</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-1" id="view-numero_movimentacao">MOV-2024-001</h4>
                                <p class="text-muted mb-0" id="view-data_movimentacao">14/01/2024 às 10:30</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="status-card">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="fw-bold">Executada</span>
                        </div>
                    </div>
                </div>

                <!-- Informações do Material -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-box me-2 text-primary"></i>
                            <h6 class="mb-0">Informações do Material</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-tag me-2 text-muted"></i>
                                        Material
                                    </div>
                                    <div class="info-value" id="view-material">Smartphone Galaxy A54</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-person me-2 text-muted"></i>
                                        Responsável
                                    </div>
                                    <div class="info-value" id="view-usuario">Administrador Sistema</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalhes da Movimentação -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-arrow-up-circle me-2 text-success"></i>
                                    <h6 class="mb-0 text-success">Quantidades</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-success bg-opacity-10">
                                                <i class="bi bi-box-arrow-in-down text-success"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Movimentada</div>
                                                <div class="metric-value" id="view-quantidade">50 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-info bg-opacity-10">
                                                <i class="bi bi-currency-dollar text-info"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Valor Unit.</div>
                                                <div class="metric-value" id="view-valor_unitario">R$ 1.299,99</div>
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
                                    <i class="bi bi-graph-up me-2 text-primary"></i>
                                    <h6 class="mb-0 text-primary">Estoque</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-warning bg-opacity-10">
                                                <i class="bi bi-clock-history text-warning"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Estoque Anterior</div>
                                                <div class="metric-value" id="view-estoque_anterior">0 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-success bg-opacity-10">
                                                <i class="bi bi-check-circle text-success"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Estoque Atual</div>
                                                <div class="metric-value" id="view-estoque_atual">50 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-info bg-opacity-10">
                                                <i class="bi bi-calculator text-info"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Custo Médio Anterior</div>
                                                <div class="metric-value" id="view-custo_medio_anterior">R$ 0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-primary bg-opacity-10">
                                                <i class="bi bi-calculator-fill text-primary"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Custo Médio Atual</div>
                                                <div class="metric-value" id="view-custo_medio_atual">R$ 0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2 text-primary"></i>
                                    <h6 class="mb-0">Informações Adicionais</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-building me-2 text-muted"></i>
                                                Origem/Destino
                                            </div>
                                            <div class="info-value" id="view-origem_destino">Matriz - São Paulo</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-file-text me-2 text-muted"></i>
                                                Documento
                                            </div>
                                            <div class="info-value" id="view-documento">Nota Fiscal</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-chat-text me-2 text-muted"></i>
                                                Observações
                                            </div>
                                            <div class="info-value" id="view-observacoes">Entrada inicial de smartphones</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-warning bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack me-2 text-warning"></i>
                                    <h6 class="mb-0 text-warning">Valor Total</h6>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="total-value" id="view-valor_total">R$ 64.999,50</div>
                                <small class="text-muted">Valor total da movimentação</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Variáveis JavaScript do usuário logado -->
<script>
    const usuarioLogado = {
        id_usuario: <?php echo $user['id_usuario'] ?? 1; ?>,
        nome_completo: '<?php echo addslashes($user['nome_completo'] ?? 'Administrador'); ?>',
        id_filial: <?php echo $user['id_filial'] ?? 1; ?>,
        nome_filial: '<?php echo addslashes($user['nome_filial'] ?? 'Matriz'); ?>'
    };
    console.log('👤 Usuário logado:', usuarioLogado);
</script>

<script src="assets/js/movimentacoes.js"></script>
</body>
</html>