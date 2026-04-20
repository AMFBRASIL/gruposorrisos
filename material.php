<?php
// Incluir configurações
require_once 'config/config.php';
require_once 'config/session.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obter informações do usuário logado
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/materiais.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        /* Estilos personalizados para o modal de estoque */
        .modal-modern .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }
        
        .modal-modern .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .modal-modern .form-floating > .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .modal-modern .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .modal-modern .form-floating > label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .modal-modern .btn-lg {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .modal-modern .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .modal-modern .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .modal-modern .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .modal-modern .badge {
            border-radius: 20px;
            font-weight: 500;
        }
        
        /* Animação de entrada do modal */
        .modal.fade .modal-dialog {
            transform: scale(0.8);
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: scale(1);
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

        <main class="main-content">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-box-seam"></i>Material</span>
            </div>
            <div class="subtitle">Gerencie o catálogo de materiais do seu estoque</div>
            
            <!-- Indicador da Filial Selecionada -->
            <div class="alert alert-info d-flex align-items-center mb-3" id="filial-indicator" style="display: none;">
                <i class="bi bi-building me-2"></i>
                <div>
                    <strong>Exibindo materiais da filial:</strong> 
                    <span id="filial-nome">Carregando...</span>
                    <small class="d-block text-muted">Apenas materiais desta filial são exibidos</small>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
                <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
                <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
                <button class="btn btn-primary btn-action" onclick="window.location.href='addMaterial'" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Material</button>
            </div>
            
            <!-- Cards de Resumo -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total de Materiais</div>
                            <div class="card-value" id="total-materiais">0</div>
                            <div class="text-success small">Carregando...</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Em Estoque</div>
                            <div class="card-value" style="color:#22c55e;" id="em-estoque">0</div>
                            <div class="text-muted small" id="percentual-estoque">0% do total</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Estoque Baixo</div>
                            <div class="card-value" style="color:#eab308;" id="estoque-baixo">0</div>
                            <div class="text-muted small">Requer atenção</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Sem Estoque</div>
                            <div class="card-value" style="color:#ef4444;" id="sem-estoque">0</div>
                            <div class="text-muted small">Necessário reposição</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card de Ressuprimento -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card card-resumo border-warning">
                        <div class="card-body">
                            <div class="card-title text-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Precisa Ressuprimento
                            </div>
                            <div class="card-value text-warning" id="precisa-ressuprimento">0</div>
                            <div class="text-muted small">Materiais com estoque crítico</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-resumo border-info">
                        <div class="card-body">
                            <div class="card-title text-info">
                                <i class="bi bi-arrow-up-circle me-2"></i>
                                Ressuprimento Preventivo
                            </div>
                            <div class="card-value text-info" id="ressuprimento-preventivo">0</div>
                            <div class="text-muted small">Materiais para reposição preventiva</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="card filters-card mb-4">
                <div class="card-body">
                    <div class="filters-title">Filtros e Busca</div>
                    <div class="filters-subtitle">Busque e filtre materiais por diferentes critérios</div>
                    <form id="filtrosForm" class="mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por código, descrição ou marca...">
                            <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                        </div>
                        <div class="row g-2" id="filtrosAvancados" style="display: none;">
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-categoria">
                                    <option value="">Todas as Categorias</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-fornecedor">
                                    <option value="">Todos os Fornecedores</option>
                                </select>
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
                            <div class="col-md-3 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lista de Materiais -->
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Materiais</div>
                    <div class="text-muted mb-3">Todos os materiais cadastrados no sistema</div>
                    
                    <!-- Loading -->
                    <div id="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando materiais...</p>
                    </div>
                    
                    <!-- Tabela -->
                    <div class="table-responsive" id="tabela-container" style="display: none;">
                        <table class="table table-borderless table-materials mb-0">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Unidade</th>
                                    <th>Preço Unitário</th>
                                    <th>-</th>
                                    <th>Estoque</th>
                                    <th>Ressuprimento</th>
                                    <th>Fornecedor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="materiais-tbody">
                                <!-- Dados carregados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted">
                                Mostrando <span id="inicio-pagina">1</span> a <span id="fim-pagina">10</span> de <span id="total-registros">0</span> materiais
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="itens-por-pagina" class="text-muted small mb-0">Itens por página:</label>
                                <select class="form-select form-select-sm" id="itens-por-pagina" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
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
                        <p class="mt-2">Nenhum material encontrado</p>
                        <button class="btn btn-primary" onclick="window.location.href='addMaterial'">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar Primeiro Material
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Visualizar Material -->
<div class="modal fade modal-modern" id="modalVisualizarMaterial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Detalhes do Material</h5>
                        <small class="opacity-75">Informações completas do material cadastrado</small>
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
                                <div class="material-badge" id="view-status-badge">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span id="view-status-text">Em Estoque</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-1" id="view-codigo">MAT001</h4>
                                <p class="text-muted mb-0" id="view-nome">Nome do Material</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="status-card">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="fw-bold" id="view-status-ativo">Ativo</span>
                        </div>
                    </div>
                </div>

                <!-- Informações Básicas -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            <h6 class="mb-0">Informações Básicas</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-tag me-2 text-muted"></i>
                                        Código
                                    </div>
                                    <div class="info-value" id="view-codigo-detail">MAT001</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-box me-2 text-muted"></i>
                                        Nome
                                    </div>
                                    <div class="info-value" id="view-nome-detail">Nome do Material</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-collection me-2 text-muted"></i>
                                        Categoria
                                    </div>
                                    <div class="info-value" id="view-categoria">Categoria</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-building me-2 text-muted"></i>
                                        Filial
                                    </div>
                                    <div class="info-value" id="view-filial">Filial</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-truck me-2 text-muted"></i>
                                        Fornecedor
                                    </div>
                                    <div class="info-value" id="view-fornecedor">Fornecedor</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-rulers me-2 text-muted"></i>
                                        Unidade de Medida
                                    </div>
                                    <div class="info-value" id="view-unidade">UN</div>
                                </div>
                            </div>
                            <div class="col-md-6" id="view-ca-container" style="display: none;">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-shield-check me-2 text-muted"></i>
                                        Certificado de Aprovação (CA)
                                    </div>
                                    <div class="info-value" id="view-ca">CA-12345</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-upc-scan me-2 text-muted"></i>
                                        Código de Barras
                                    </div>
                                    <div class="info-value" id="view-codigo-barras">7891234567890</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estoque e Valores -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2 text-success"></i>
                                    <h6 class="mb-0 text-success">Estoque</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-success bg-opacity-10">
                                                <i class="bi bi-box text-success"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Atual</div>
                                                <div class="metric-value" id="view-estoque-atual">0 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-warning bg-opacity-10">
                                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Mínimo</div>
                                                <div class="metric-value" id="view-estoque-minimo">0 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-info bg-opacity-10">
                                                <i class="bi bi-arrow-up-circle text-info"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Máximo</div>
                                                <div class="metric-value" id="view-estoque-maximo">0 UN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-card">
                                            <div class="metric-icon bg-primary bg-opacity-10">
                                                <i class="bi bi-calculator text-primary"></i>
                                            </div>
                                            <div class="metric-content">
                                                <div class="metric-label">Ressuprimento</div>
                                                <div class="metric-value" id="view-ressuprimento">0 UN</div>
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
                                                <div class="metric-label">Preço Unit.</div>
                                                <div class="metric-value" id="view-preco-unitario">R$ 0,00</div>
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
                                                <div class="metric-value" id="view-valor-total">R$ 0,00</div>
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
                                    <i class="bi bi-clock-history me-2 text-primary"></i>
                                    <h6 class="mb-0">Histórico</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-calendar-plus me-2 text-muted"></i>
                                                Data de Criação
                                            </div>
                                            <div class="info-value" id="view-data-criacao">01/01/2024</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-calendar-check me-2 text-muted"></i>
                                                Última Atualização
                                            </div>
                                            <div class="info-value" id="view-data-atualizacao">01/01/2024</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-person me-2 text-muted"></i>
                                                Criado por
                                            </div>
                                            <div class="info-value" id="view-usuario-criacao">Usuário</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                                Localização
                                            </div>
                                            <div class="info-value" id="view-localizacao">Localização</div>
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
                                    <i class="bi bi-graph-up me-2 text-warning"></i>
                                    <h6 class="mb-0 text-warning">Status do Estoque</h6>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="status-indicator" id="view-status-indicator">
                                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                    <div class="mt-2 fw-bold">Em Estoque</div>
                                </div>
                                <small class="text-muted">Status atual do material</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary" id="btn-editar-material">
                    <i class="bi bi-pencil me-2"></i>Editar Material
                </button>
                <button type="button" class="btn btn-success" id="btn-duplicar-material">
                    <i class="bi bi-files me-2"></i>Duplicar
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
<script>
// Variáveis globais
let paginaAtual = 1;
let limitePorPagina = parseInt(localStorage.getItem('materiais_limite_por_pagina')) || 10;
let materiaisSelecionados = [];
let confirmModal;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    // Carregar dados iniciais
    carregarFilialSelecionada();
    carregarEstatisticas();
    carregarCategorias();
    carregarFornecedores();
    carregarMateriais(); // Adicionar esta chamada
    
    // Event listeners
    document.getElementById('busca').addEventListener('input', debounce(carregarMateriais, 500));
    document.getElementById('filtro-categoria').addEventListener('change', carregarMateriais);
    document.getElementById('filtro-fornecedor').addEventListener('change', carregarMateriais);
    document.getElementById('filtro-status').addEventListener('change', carregarMateriais);
    document.getElementById('select-all').addEventListener('change', toggleSelectAll);
    
    // Seletor de itens por página
    const selectItensPorPagina = document.getElementById('itens-por-pagina');
    if (selectItensPorPagina) {
        selectItensPorPagina.value = limitePorPagina;
        selectItensPorPagina.addEventListener('change', function() {
            limitePorPagina = parseInt(this.value);
            localStorage.setItem('materiais_limite_por_pagina', limitePorPagina);
            paginaAtual = 1; // Voltar para primeira página ao mudar limite
            carregarMateriais();
        });
    }
});

// Funções de carregamento
async function carregarFilialSelecionada() {
    const filialId = localStorage.getItem('filialSelecionada');
    const indicator = document.getElementById('filial-indicator');
    const filialNome = document.getElementById('filial-nome');
    
    if (filialId) {
        try {
            // Buscar informações da filial na API
            const response = await fetch(`backend/api/filiais.php?action=list`);
            const data = await response.json();
            
            if (data.success && data.filiais) {
                const filial = data.filiais.find(f => f.id == filialId);
                if (filial) {
                    filialNome.textContent = filial.nome;
                    indicator.style.display = 'flex';
                    console.log('✅ Filial exibida:', filial.nome);
                } else {
                    indicator.style.display = 'none';
                }
            } else {
                indicator.style.display = 'none';
            }
        } catch (error) {
            console.error('Erro ao carregar filial:', error);
            indicator.style.display = 'none';
        }
    } else {
        // Nenhuma filial selecionada
        filialNome.textContent = 'Nenhuma filial selecionada';
        indicator.style.display = 'flex';
        indicator.className = 'alert alert-warning d-flex align-items-center mb-3';
        console.log('⚠️ Nenhuma filial selecionada');
    }
}

async function carregarEstatisticas() {
    try {
        console.log('Carregando estatísticas...');
        
        // Obter filial selecionada do localStorage
        const filialId = localStorage.getItem('filialSelecionada');
        console.log('🔍 Filial selecionada para estatísticas:', filialId);
        
        const params = new URLSearchParams({
            action: 'estatisticas'
        });
        
        // Adicionar filtro de filial se estiver selecionada
        if (filialId) {
            params.append('filial_id', filialId);
        }
        
        console.log('📡 Parâmetros das estatísticas:', params.toString());
        
        const response = await fetch(`api/materiais_nova_estrutura.php?${params}`, {
            method: 'GET',
            credentials: 'same-origin', // Incluir cookies de sessão
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        console.log('Status da resposta:', response.status);
        console.log('Headers da resposta:', response.headers);
        
        const data = await response.json();
        console.log('Resposta da API de estatísticas:', data);
        
        if (data.success) {
            const stats = data.data;
            console.log('Estatísticas recebidas:', stats);
            
            // Mapear os campos corretos da API
            document.getElementById('total-materiais').textContent = stats.total_materiais || 0;
            document.getElementById('em-estoque').textContent = stats.em_estoque || 0;
            document.getElementById('estoque-baixo').textContent = stats.estoque_baixo || 0;
            document.getElementById('sem-estoque').textContent = stats.sem_estoque || 0;
            document.getElementById('precisa-ressuprimento').textContent = stats.precisa_ressuprimento || 0;
            
            // Calcular ressuprimento preventivo (materiais em estoque - estoque baixo)
            const ressuprimentoPreventivo = Math.max(0, (stats.em_estoque || 0) - (stats.estoque_baixo || 0));
            document.getElementById('ressuprimento-preventivo').textContent = ressuprimentoPreventivo;
            
            // Calcular percentual
            const total = stats.total_materiais || 0;
            const emEstoque = stats.em_estoque || 0;
            const percentual = total > 0 ? Math.round((emEstoque / total) * 100) : 0;
            document.getElementById('percentual-estoque').textContent = `${percentual}% do total`;
            
            console.log('✅ Indicadores atualizados com sucesso:', {
                total: stats.total_materiais,
                em_estoque: stats.em_estoque,
                estoque_baixo: stats.estoque_baixo,
                sem_estoque: stats.sem_estoque,
                precisa_ressuprimento: stats.precisa_ressuprimento,
                percentual: percentual
            });
        } else {
            console.error('API retornou erro:', data.error);
            // Mostrar erro visualmente
            document.getElementById('total-materiais').textContent = 'Erro';
            document.getElementById('em-estoque').textContent = 'Erro';
            document.getElementById('estoque-baixo').textContent = 'Erro';
            document.getElementById('sem-estoque').textContent = 'Erro';
            document.getElementById('precisa-ressuprimento').textContent = 'Erro';
            document.getElementById('ressuprimento-preventivo').textContent = 'Erro';
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
        // Mostrar erro visualmente
        document.getElementById('total-materiais').textContent = 'Erro';
        document.getElementById('em-estoque').textContent = 'Erro';
        document.getElementById('estoque-baixo').textContent = 'Erro';
        document.getElementById('sem-estoque').textContent = 'Erro';
        document.getElementById('precisa-ressuprimento').textContent = 'Erro';
        document.getElementById('ressuprimento-preventivo').textContent = 'Erro';
    }
}

async function carregarCategorias() {
    try {
        const response = await fetch('api/materiais_nova_estrutura.php?action=categorias');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filtro-categoria');
            data.data.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = categoria.nome_categoria;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

async function carregarFornecedores() {
    try {
        const response = await fetch('api/materiais_nova_estrutura.php?action=fornecedores');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filtro-fornecedor');
            data.data.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.razao_social;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores:', error);
    }
}

async function carregarMateriais() {
    const loading = document.getElementById('loading');
    const tabela = document.getElementById('tabela-container');
    const semDados = document.getElementById('sem-dados');
    
    loading.style.display = 'block';
    tabela.style.display = 'none';
    semDados.style.display = 'none';
    
    try {
        // Obter filial selecionada do localStorage
        const filialId = localStorage.getItem('filialSelecionada');
        console.log('🔍 Filial selecionada para filtro:', filialId);
        
        const params = new URLSearchParams({
            action: 'list',
            page: paginaAtual,
            limit: limitePorPagina,
            busca: document.getElementById('busca').value,
            categoria: document.getElementById('filtro-categoria').value,
            fornecedor: document.getElementById('filtro-fornecedor').value
        });
        
        // Adicionar filtro de filial se estiver selecionada
        if (filialId) {
            params.append('filial_id', filialId);
        }
        
        const status = document.getElementById('filtro-status').value;
        if (status === 'estoque') params.append('em_estoque', '1');
        if (status === 'baixo') params.append('estoque_baixo', '1');
        if (status === 'zerado') params.append('estoque_zerado', '1');
        if (status === 'ressuprimento') params.append('precisa_ressuprimento', '1');
        
        console.log('📡 Parâmetros da requisição:', params.toString());
        
        const response = await fetch(`api/materiais_nova_estrutura.php?${params}`);
        console.log('📡 Resposta da API:', response.status, response.statusText);
        
        const data = await response.json();
        console.log('📋 Dados recebidos:', data);
        
        loading.style.display = 'none';
        
        if (data.success && data.data.length > 0) {
            console.log('✅ Materiais recebidos:', data.data.length);
            console.log('📊 Dados de paginação:', {
                page: data.page,
                total: data.total,
                limit: data.limit,
                total_pages: data.total_pages
            });
            
            renderizarTabela(data.data);
            
            // Sempre renderizar paginação se houver dados
            renderizarPaginacao(data);
            
            tabela.style.display = 'block';
        } else {
            console.log('❌ Nenhum material encontrado ou erro:', data);
            semDados.style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao carregar materiais:', error);
        loading.style.display = 'none';
        semDados.style.display = 'block';
    }
}

function renderizarTabela(materiais) {
    console.log('🔧 Iniciando renderização da tabela com', materiais.length, 'materiais');
    
    const tbody = document.getElementById('materiais-tbody');
    if (!tbody) {
        console.error('❌ Elemento materiais-tbody não encontrado!');
        return;
    }
    
    tbody.innerHTML = '';
    
    materiais.forEach((material, index) => {
        console.log(`🔧 Renderizando material ${index + 1}:`, material.nome);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="checkbox" class="form-check-input material-checkbox" value="${material.id_material}"></td>
            <td>${material.codigo}</td>
            <td>
                <strong>${material.nome}</strong><br>
                <span class="text-muted small"> Clínica: 
                    ${material.nome_filial || 'Sem filial'} 
                    ${material.fornecedor_nome ? `<br> Fornecedor:  ${material.fornecedor_nome}` : ''}
                </span>
            </td>
            <td>${material.nome_categoria || 'Sem categoria'}</td>
            <td>${material.unidade_sigla || 'UN'}</td>
            <td>R$ ${parseFloat(material.preco_unitario || 0).toFixed(2)}</td>
            <td>-</td>
            <td>${material.estoque_atual || 0} <span class="text-muted small">(min: ${material.estoque_minimo || 0})</span></td>
            <td>${calcularRessuprimento(material)}</td>
            <td>${material.fornecedor_nome || 'Sem fornecedor'}</td>
            <td>${getStatusBadge(material)}</td>
            <td>
                <button class="icon-btn text-primary" title="Visualizar" onclick="visualizarMaterial(${material.id_material})"><i class="bi bi-eye"></i></button>
                <button class="icon-btn text-success" title="Editar" onclick="editarMaterial(${material.id_material})"><i class="bi bi-pencil"></i></button>
                <button class="icon-btn text-info" title="Gerenciar Estoque" onclick="abrirModalEstoque(${material.id_catalogo}, '${material.nome}', '${material.codigo}')"><i class="bi bi-box-seam"></i></button>
                <button class="icon-btn text-warning" title="Duplicar" onclick="duplicarMaterial(${material.id_material})"><i class="bi bi-files"></i></button>
                <button class="icon-btn text-danger" title="Excluir" onclick="excluirMaterial(${material.id_material})"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    // Event listeners para checkboxes
    document.querySelectorAll('.material-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateMateriaisSelecionados);
    });
    
    console.log('✅ Tabela renderizada com sucesso!', materiais.length, 'materiais adicionados');
}

function getStatusBadge(material) {
    const estoque = parseFloat(material.estoque_atual || 0);
    const minimo = parseFloat(material.estoque_minimo || 0);
    
    if (estoque === 0) {
        return '<span class="status-badge status-zerado">Sem Estoque</span>';
    } else if (estoque <= minimo) {
        return '<span class="status-badge status-baixo">Estoque Baixo</span>';
    } else {
        return '<span class="status-badge status-estoque">Em Estoque</span>';
    }
}

function calcularRessuprimento(material) {
    const estoqueAtual = parseFloat(material.estoque_atual || 0);
    const estoqueMinimo = parseFloat(material.estoque_minimo || 0);
    
    // Se o estoque está abaixo do mínimo, calcular quantidade para repor
    if (estoqueAtual <= estoqueMinimo) {
        // Calcular quantidade necessária para atingir o estoque mínimo + margem de segurança
        const margemSeguranca = estoqueMinimo * 0.5; // 50% do estoque mínimo como margem
        const quantidadeNecessaria = Math.max(0, (estoqueMinimo + margemSeguranca) - estoqueAtual);
        
        if (quantidadeNecessaria > 0) {
            return `<span class="text-danger fw-bold">${quantidadeNecessaria.toFixed(2)}</span> <span class="text-muted small">(reposição urgente)</span>`;
        } else {
            return `<span class="text-warning">0</span> <span class="text-muted small">(no limite)</span>`;
        }
    } else {
        // Estoque está acima do mínimo, mas pode precisar de reposição preventiva
        const estoqueMaximo = parseFloat(material.estoque_maximo || (estoqueMinimo * 3)); // 3x o mínimo como padrão
        const quantidadePreventiva = Math.max(0, estoqueMaximo - estoqueAtual);
        
        if (quantidadePreventiva > 0) {
            return `<span class="text-info">${quantidadePreventiva.toFixed(2)}</span> <span class="text-muted small">(reposição preventiva)</span>`;
        } else {
            return `<span class="text-success">0</span> <span class="text-muted small">(estoque adequado)</span>`;
        }
    }
}

function renderizarPaginacao(pagination) {
    console.log('🔧 Renderizando paginação:', pagination);
    
    const container = document.getElementById('paginacao');
    const links = document.getElementById('paginacao-links');
    
    if (!container || !links) {
        console.error('❌ Elementos de paginação não encontrados!');
        return;
    }
    
    // Se só tem 1 página, esconder paginação mas mostrar contadores
    if (pagination.total_pages <= 1) {
        // Ainda mostrar informações de total
        const start = 1;
        const end = Math.min(pagination.limit, pagination.total);
        
        document.getElementById('inicio-pagina').textContent = start;
        document.getElementById('fim-pagina').textContent = end;
        document.getElementById('total-registros').textContent = pagination.total;
        
        // Esconder apenas os links de navegação, mas mostrar o container
        links.innerHTML = '<li class="page-item disabled"><span class="page-link">Página 1 de 1</span></li>';
        container.style.display = 'flex';
        
        console.log('📄 Apenas 1 página - mostrando contadores');
        return;
    }
    
    // Converter para números para evitar problemas de tipo
    const page = parseInt(pagination.page) || 1;
    const limit = parseInt(pagination.limit) || 10;
    const total = parseInt(pagination.total) || 0;
    const totalPages = parseInt(pagination.total_pages) || 1;
    
    console.log('📄 Paginação (convertido):', { page, limit, total, totalPages });
    
    // Calcular início e fim da página atual
    const start = ((page - 1) * limit) + 1;
    const end = Math.min(page * limit, total);
    
    document.getElementById('inicio-pagina').textContent = start;
    document.getElementById('fim-pagina').textContent = end;
    document.getElementById('total-registros').textContent = total;
    
    links.innerHTML = '';
    
    // Botão anterior
    if (page > 1) {
        links.appendChild(createPageLink(page - 1, '« Anterior'));
    }
    
    // Páginas numeradas
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(totalPages, page + 2);
    
    console.log(`📄 Renderizando páginas de ${startPage} até ${endPage}`);
    
    for (let i = startPage; i <= endPage; i++) {
        links.appendChild(createPageLink(i, i, i === page));
    }
    
    // Botão próximo
    if (page < totalPages) {
        links.appendChild(createPageLink(page + 1, 'Próximo »'));
    }
    
    container.style.display = 'flex';
    
    console.log('✅ Paginação renderizada com sucesso!');
}

function createPageLink(page, text, active = false) {
    const li = document.createElement('li');
    li.className = `page-item ${active ? 'active' : ''}`;
    
    const a = document.createElement('a');
    a.className = 'page-link';
    a.href = '#';
    a.textContent = text;
    a.addEventListener('click', (e) => {
        e.preventDefault();
        paginaAtual = page;
        console.log('🔄 Mudando para página:', page);
        carregarMateriais();
        
        // Scroll suave para o topo da tabela
        document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    
    li.appendChild(a);
    return li;
}

// Funções de ação
function visualizarMaterial(id) {
    if (!id) return;
    
    // Buscar dados completos do material
            fetch(`api/materiais_nova_estrutura.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                preencherModalVisualizacao(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizarMaterial'));
                modal.show();
            } else {
                console.error('Erro ao carregar material:', data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar material:', error);
        });
}

function preencherModalVisualizacao(material) {
    // Cabeçalho
    document.getElementById('view-codigo').textContent = material.codigo;
    document.getElementById('view-nome').textContent = material.nome;
    
    // Status badge
    const statusBadge = document.getElementById('view-status-badge');
    const statusText = document.getElementById('view-status-text');
    const estoque = parseFloat(material.estoque_atual || 0);
    const minimo = parseFloat(material.estoque_minimo || 0);
    
    if (estoque === 0) {
        statusBadge.className = 'material-badge';
        statusBadge.style.background = 'rgba(239, 68, 68, 0.1)';
        statusBadge.style.color = '#dc2626';
        statusBadge.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        statusText.textContent = 'Sem Estoque';
        statusBadge.querySelector('i').className = 'bi bi-exclamation-triangle me-2';
    } else if (estoque <= minimo) {
        statusBadge.className = 'material-badge';
        statusBadge.style.background = 'rgba(234, 179, 8, 0.1)';
        statusBadge.style.color = '#d97706';
        statusBadge.style.borderColor = 'rgba(234, 179, 8, 0.2)';
        statusText.textContent = 'Estoque Baixo';
        statusBadge.querySelector('i').className = 'bi bi-exclamation-triangle me-2';
    } else {
        statusBadge.className = 'material-badge';
        statusBadge.style.background = 'rgba(34, 197, 94, 0.1)';
        statusBadge.style.color = '#16a34a';
        statusBadge.style.borderColor = 'rgba(34, 197, 94, 0.2)';
        statusText.textContent = 'Em Estoque';
        statusBadge.querySelector('i').className = 'bi bi-check-circle me-2';
    }
    
    // Status ativo
    const statusAtivo = document.getElementById('view-status-ativo');
    if (material.ativo == 1) {
        statusAtivo.textContent = 'Ativo';
        statusAtivo.className = 'fw-bold text-success';
    } else {
        statusAtivo.textContent = 'Inativo';
        statusAtivo.className = 'fw-bold text-danger';
    }
    
    // Informações básicas
    document.getElementById('view-codigo-detail').textContent = material.codigo;
    document.getElementById('view-nome-detail').textContent = material.nome;
    document.getElementById('view-categoria').textContent = material.nome_categoria || 'Sem categoria';
    document.getElementById('view-filial').textContent = material.nome_filial || 'Sem filial';
    document.getElementById('view-fornecedor').textContent = material.fornecedor_nome || 'Sem fornecedor';
    document.getElementById('view-unidade').textContent = material.unidade_sigla || 'UN';
    document.getElementById('view-codigo-barras').textContent = material.codigo_barras || 'Não informado';
    
    // Campo CA (se for EPI)
    const caContainer = document.getElementById('view-ca-container');
    if (material.ca && material.ca.trim()) {
        document.getElementById('view-ca').textContent = material.ca;
        caContainer.style.display = 'block';
    } else {
        caContainer.style.display = 'none';
    }
    
    // Estoque e valores
    document.getElementById('view-estoque-atual').textContent = `${material.estoque_atual || 0} ${material.unidade_sigla || 'UN'}`;
    document.getElementById('view-estoque-minimo').textContent = `${material.estoque_minimo || 0} ${material.unidade_sigla || 'UN'}`;
    document.getElementById('view-estoque-maximo').textContent = `${material.estoque_maximo || 0} ${material.unidade_sigla || 'UN'}`;
    
    // Calcular ressuprimento
    const ressuprimento = calcularRessuprimentoQuantidade(material);
    document.getElementById('view-ressuprimento').textContent = `${ressuprimento} ${material.unidade_sigla || 'UN'}`;
    
    // Valores
    const precoUnitario = parseFloat(material.preco_unitario || 0);
    const estoqueAtual = parseFloat(material.estoque_atual || 0);
    const valorTotal = precoUnitario * estoqueAtual;
    
    document.getElementById('view-preco-unitario').textContent = `R$ ${precoUnitario.toFixed(2).replace('.', ',')}`;
    document.getElementById('view-valor-total').textContent = `R$ ${valorTotal.toFixed(2).replace('.', ',')}`;
    
    // Histórico
    document.getElementById('view-data-criacao').textContent = material.data_criacao ? new Date(material.data_criacao).toLocaleDateString('pt-BR') : 'Não informado';
    document.getElementById('view-data-atualizacao').textContent = material.data_atualizacao ? new Date(material.data_atualizacao).toLocaleDateString('pt-BR') : 'Não informado';
    document.getElementById('view-usuario-criacao').textContent = material.usuario_criacao || 'Sistema';
    document.getElementById('view-localizacao').textContent = material.localizacao || 'Não informado';
    
    // Status indicator
    const statusIndicator = document.getElementById('view-status-indicator');
    if (estoque === 0) {
        statusIndicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i><div class="mt-2 fw-bold text-danger">Sem Estoque</div>';
    } else if (estoque <= minimo) {
        statusIndicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning fs-1"></i><div class="mt-2 fw-bold text-warning">Estoque Baixo</div>';
    } else {
        statusIndicator.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-1"></i><div class="mt-2 fw-bold text-success">Em Estoque</div>';
    }
    
    // Configurar botões de ação
    document.getElementById('btn-editar-material').onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('modalVisualizarMaterial')).hide();
        editarMaterial(material.id_material);
    };
    
    document.getElementById('btn-duplicar-material').onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('modalVisualizarMaterial')).hide();
        duplicarMaterial(material.id_material);
    };
}

function calcularRessuprimentoQuantidade(material) {
    const estoqueAtual = parseFloat(material.estoque_atual || 0);
    const estoqueMinimo = parseFloat(material.estoque_minimo || 0);
    
    if (estoqueAtual <= estoqueMinimo) {
        const margemSeguranca = estoqueMinimo * 0.5;
        const quantidadeNecessaria = Math.max(0, (estoqueMinimo + margemSeguranca) - estoqueAtual);
        return quantidadeNecessaria.toFixed(2);
    } else {
        const estoqueMaximo = parseFloat(material.estoque_maximo || (estoqueMinimo * 3));
        const quantidadePreventiva = Math.max(0, estoqueMaximo - estoqueAtual);
        return quantidadePreventiva.toFixed(2);
    }
}

function editarMaterial(id) {
    window.open(`addMaterial?id=${id}`, '_blank');
}

async function duplicarMaterial(id) {
    if (!id) return;
    if (!confirm('Deseja realmente duplicar este material?')) return;
    try {
        const res = await fetch(`api/materiais_nova_estrutura.php?action=duplicar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if (data.success) {
            alert('Material duplicado com sucesso!');
            carregarMateriais();
            carregarEstatisticas();
        } else {
            alert(data.error || 'Erro ao duplicar material.');
        }
    } catch (e) {
        alert('Erro de conexão ao duplicar material.');
    }
}

function excluirMaterial(id) {
    document.getElementById('confirmMessage').textContent = 'Tem certeza que deseja excluir este material?';
    document.getElementById('confirmAction').onclick = () => confirmarExclusao(id);
    confirmModal.show();
}

async function confirmarExclusao(id) {
    try {
        const response = await fetch(`api/materiais_nova_estrutura.php?action=delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            confirmModal.hide();
            carregarMateriais();
            carregarEstatisticas();
            alert('Material excluído com sucesso!');
        } else {
            alert('Erro ao excluir material: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao excluir material:', error);
        alert('Erro ao excluir material');
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.material-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateMateriaisSelecionados();
}

function updateMateriaisSelecionados() {
    materiaisSelecionados = Array.from(document.querySelectorAll('.material-checkbox:checked'))
        .map(checkbox => checkbox.value);
}

function duplicarSelecionados() {
    if (materiaisSelecionados.length === 0) {
        alert('Selecione pelo menos um material para duplicar');
        return;
    }
    
    if (confirm(`Deseja duplicar ${materiaisSelecionados.length} material(is)?`)) {
        // Implementar duplicação em lote
        alert('Funcionalidade de duplicação em lote em desenvolvimento');
    }
}

function toggleFiltros() {
    const filtros = document.getElementById('filtrosAvancados');
    filtros.style.display = filtros.style.display === 'none' ? 'block' : 'none';
}

function limparFiltros() {
    document.getElementById('busca').value = '';
    document.getElementById('filtro-categoria').value = '';
    document.getElementById('filtro-fornecedor').value = '';
    document.getElementById('filtro-status').value = '';
    paginaAtual = 1;
    carregarMateriais();
}

function exportarXLS() {
    alert('Funcionalidade de exportação em desenvolvimento');
}

function imprimir() {
    const linhas = Array.from(document.querySelectorAll('#materiais-tbody tr'));
    if (!linhas.length) {
        alert('Não há materiais para imprimir com os filtros atuais.');
        return;
    }

    const busca = document.getElementById('busca')?.value?.trim() || 'Sem filtro';
    const categoria = document.getElementById('filtro-categoria')?.selectedOptions?.[0]?.textContent || 'Todas';
    const fornecedor = document.getElementById('filtro-fornecedor')?.selectedOptions?.[0]?.textContent || 'Todos';
    const status = document.getElementById('filtro-status')?.selectedOptions?.[0]?.textContent || 'Todos';
    const filial = document.getElementById('filial-nome')?.textContent?.trim() || 'Não informada';
    const dataHora = new Date().toLocaleString('pt-BR');

    const cabecalho = `
        <tr>
            <th>Código</th>
            <th>Descrição</th>
            <th>Categoria</th>
            <th>Unidade</th>
            <th>Preço Unitário</th>
            <th>Estoque</th>
            <th>Ressuprimento</th>
            <th>Fornecedor</th>
            <th>Status</th>
        </tr>
    `;

    const corpo = linhas.map((linha) => {
        const tds = linha.querySelectorAll('td');
        if (tds.length < 11) return '';

        const codigo = tds[1]?.textContent?.trim() || '';
        const descricao = tds[2]?.querySelector('strong')?.textContent?.trim() || tds[2]?.textContent?.trim() || '';
        const categoriaTxt = tds[3]?.textContent?.trim() || '';
        const unidade = tds[4]?.textContent?.trim() || '';
        const preco = tds[5]?.textContent?.trim() || '';
        const estoque = tds[7]?.textContent?.trim() || '';
        const ressuprimento = tds[8]?.textContent?.trim() || '';
        const fornecedorTxt = tds[9]?.textContent?.trim() || '';
        const statusTxt = tds[10]?.textContent?.trim() || '';

        return `
            <tr>
                <td>${codigo}</td>
                <td>${descricao}</td>
                <td>${categoriaTxt}</td>
                <td>${unidade}</td>
                <td>${preco}</td>
                <td>${estoque}</td>
                <td>${ressuprimento}</td>
                <td>${fornecedorTxt}</td>
                <td>${statusTxt}</td>
            </tr>
        `;
    }).join('');

    const html = `
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Lista de Materiais - Impressão</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #222; }
        h1 { margin: 0 0 8px; font-size: 20px; }
        .meta { margin-bottom: 14px; font-size: 12px; color: #444; }
        .meta div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        @media print {
            body { margin: 8mm; }
            tr, td, th { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h1>Lista de Materiais (Filtrada)</h1>
    <div class="meta">
        <div><strong>Filial:</strong> ${filial}</div>
        <div><strong>Busca:</strong> ${busca}</div>
        <div><strong>Categoria:</strong> ${categoria}</div>
        <div><strong>Fornecedor:</strong> ${fornecedor}</div>
        <div><strong>Status:</strong> ${status}</div>
        <div><strong>Total de itens na listagem:</strong> ${linhas.length}</div>
        <div><strong>Gerado em:</strong> ${dataHora}</div>
    </div>
    <table>
        <thead>${cabecalho}</thead>
        <tbody>${corpo}</tbody>
    </table>
</body>
</html>`;

    const win = window.open('', '_blank');
    if (!win) {
        alert('Não foi possível abrir a janela de impressão. Verifique bloqueador de pop-up.');
        return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    win.print();
}

// Função para abrir modal de estoque
async function abrirModalEstoque(idCatalogo, nomeMaterial, codigoMaterial) {
    try {
        // Preencher informações do material
        document.getElementById('estoque-material-nome').textContent = nomeMaterial;
        document.getElementById('estoque-material-codigo').textContent = codigoMaterial;
        document.getElementById('estoque-id-material').value = idCatalogo;
        
        // Obter a filial já selecionada na tela
        const filialSelecionada = localStorage.getItem('filialSelecionada');
        const filialNome = document.getElementById('filial-nome')?.textContent || 'Filial Selecionada';
        
        // Preencher informações da filial
        document.getElementById('estoque-filial-info').textContent = filialNome;
        document.getElementById('estoque-id-filial').value = filialSelecionada;
        
        // Buscar dados atuais do estoque
        console.log('🔍 Buscando dados atuais do estoque...');
        const response = await fetch(`api/materiais_nova_estrutura.php?action=buscar-estoque&id_catalogo=${idCatalogo}&id_filial=${filialSelecionada}`);
        
        if (response.ok) {
            const data = await response.json();
            console.log('📦 Dados do estoque recebidos:', data);
            
            if (data.success && data.estoque) {
                // Preencher campos com dados existentes
                const estoque = data.estoque;
                
                // Aplicar máscaras aos campos
                aplicarMascarasEstoque();
                
                // Preencher campos com valores existentes (após aplicar máscaras)
                setTimeout(() => {
                    if (estoque.estoque_atual !== null) {
                        document.getElementById('estoque-atual').value = formatarNumero(estoque.estoque_atual);
                    }
                    // Usar valor da filial se definido, senão usar padrão do catálogo
                    const estoqueMinimo = estoque.estoque_minimo !== null && estoque.estoque_minimo !== undefined 
                        ? estoque.estoque_minimo 
                        : (estoque.estoque_minimo_padrao || estoque.estoque_minimo_exibicao || 0);
                    const estoqueMaximo = estoque.estoque_maximo !== null && estoque.estoque_maximo !== undefined 
                        ? estoque.estoque_maximo 
                        : (estoque.estoque_maximo_padrao || estoque.estoque_maximo_exibicao || 0);
                    
                    document.getElementById('estoque-minimo').value = formatarNumero(estoqueMinimo);
                    document.getElementById('estoque-maximo').value = formatarNumero(estoqueMaximo);
                    if (estoque.preco_unitario !== null) {
                        document.getElementById('estoque-preco').value = formatarPreco(estoque.preco_unitario);
                    }
                    if (estoque.localizacao_estoque) {
                        document.getElementById('estoque-localizacao').value = estoque.localizacao_estoque;
                    }
                    if (estoque.observacoes_estoque) {
                        document.getElementById('estoque-observacoes').value = estoque.observacoes_estoque;
                    }
                }, 100);
                
            } else {
                // Se não existir estoque, limpar campos
                limparCamposEstoque();
            }
        } else {
            console.error('❌ Erro ao buscar estoque:', response.status);
            limparCamposEstoque();
        }
        
    } catch (error) {
        console.error('❌ Erro ao abrir modal de estoque:', error);
        limparCamposEstoque();
    }
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEstoque'));
    modal.show();
}

// Função para limpar campos do estoque
function limparCamposEstoque() {
    document.getElementById('estoque-atual').value = '';
    document.getElementById('estoque-minimo').value = '';
    document.getElementById('estoque-maximo').value = '';
    document.getElementById('estoque-preco').value = '';
    document.getElementById('estoque-localizacao').value = '';
    document.getElementById('estoque-observacoes').value = '';
}

// Função para formatar número (0,00)
function formatarNumero(valor) {
    if (valor === null || valor === undefined) return '';
    return parseFloat(valor).toFixed(2).replace('.', ',');
}

// Função para formatar preço (R$ 0,00)
function formatarPreco(valor) {
    if (valor === null || valor === undefined) return '';
    return 'R$ ' + parseFloat(valor).toFixed(2).replace('.', ',');
}

// Função para aplicar máscaras aos campos de estoque
function aplicarMascarasEstoque() {
    // Verificar se o jQuery Mask Plugin está disponível
    if (typeof $ !== 'undefined' && typeof $.fn.mask !== 'undefined') {
        // Máscara para preço unitário (formato: R$ 0,00)
        $('#estoque-preco').mask('R$ #.##0,00', {
            reverse: true,
            placeholder: 'R$ 0,00'
        });
        
        // Máscara para campos de quantidade (formato: 0,00)
        $('#estoque-atual').mask('#.##0,00', {
            reverse: true,
            placeholder: '0,00'
        });
        
        $('#estoque-minimo').mask('#.##0,00', {
            reverse: true,
            placeholder: '0,00'
        });
        
        $('#estoque-maximo').mask('#.##0,00', {
            reverse: true,
            placeholder: '0,00'
        });
        
        console.log('✅ Máscaras aplicadas com jQuery Mask Plugin');
    } else {
        // Solução alternativa: aplicar máscaras básicas com JavaScript puro
        aplicarMascarasAlternativas();
        console.log('⚠️ jQuery Mask Plugin não disponível, usando máscaras alternativas');
    }
}

// Função alternativa para aplicar máscaras sem jQuery Mask Plugin
function aplicarMascarasAlternativas() {
    const campos = ['estoque-atual', 'estoque-minimo', 'estoque-maximo', 'estoque-preco'];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.addEventListener('input', function(e) {
                let valor = e.target.value.replace(/\D/g, ''); // Remove tudo exceto números
                
                if (campoId === 'estoque-preco') {
                    // Formato: R$ 0,00
                    if (valor.length > 0) {
                        valor = 'R$ ' + (parseInt(valor) / 100).toFixed(2).replace('.', ',');
                    }
                } else {
                    // Formato: 0,00
                    if (valor.length > 0) {
                        valor = (parseInt(valor) / 100).toFixed(2).replace('.', ',');
                    }
                }
                
                e.target.value = valor;
            });
        }
    });
}


// Função para salvar estoque
async function salvarEstoque() {
    const idCatalogo = document.getElementById('estoque-id-material').value;
    const idFilial = document.getElementById('estoque-id-filial').value;
    
    if (!idFilial) {
        alert('Filial não selecionada. Selecione uma filial na tela de materiais primeiro.');
        return;
    }
    
    // Remover máscaras e converter valores
    const estoqueAtual = removerMascaraNumero(document.getElementById('estoque-atual').value);
    const estoqueMinimo = removerMascaraNumero(document.getElementById('estoque-minimo').value);
    const estoqueMaximo = removerMascaraNumero(document.getElementById('estoque-maximo').value);
    const precoUnitario = removerMascaraPreco(document.getElementById('estoque-preco').value);
    
    const estoqueData = {
        id_catalogo: idCatalogo,
        id_filial: idFilial,
        estoque_atual: estoqueAtual,
        estoque_minimo: estoqueMinimo,
        estoque_maximo: estoqueMaximo,
        preco_unitario: precoUnitario,
        localizacao_estoque: document.getElementById('estoque-localizacao').value || '',
        observacoes: document.getElementById('estoque-observacoes').value || ''
    };
    
    try {
        const response = await fetch('api/materiais_nova_estrutura.php?action=estoque', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(estoqueData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Estoque salvo com sucesso!');
            
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('modalEstoque')).hide();
            
            // Recarregar dados
            carregarMateriais();
            carregarEstatisticas();
        } else {
            alert('Erro ao salvar estoque: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao salvar estoque:', error);
        alert('Erro ao salvar estoque');
    }
}

// Função para remover máscara de número (formato: 0,00)
function removerMascaraNumero(valor) {
    if (!valor) return 0;
    // Remove tudo exceto números e vírgula, depois converte vírgula para ponto
    return parseFloat(valor.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
}

// Função para remover máscara de preço (formato: R$ 0,00)
function removerMascaraPreco(valor) {
    if (!valor) return 0;
    // Remove R$, espaços e converte vírgula para ponto
    return parseFloat(valor.replace(/R\$|\s/g, '').replace(',', '.')) || 0;
}

// Utilitários
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
</main>

<!-- Modal de Estoque -->
<div class="modal fade modal-modern" id="modalEstoque" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0 py-4">
                <div class="d-flex align-items-center w-100">
                    <div class="me-3">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                            <i class="bi bi-box-seam fs-2 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="modal-title mb-1 fw-bold">Configurar Estoque</h4>
                        <p class="mb-0 opacity-75 small">Defina o estoque para a filial selecionada</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Card de Informações do Material -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-box-seam text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark" id="estoque-material-nome">-</h6>
                                        <small class="text-muted">Código: <span class="fw-semibold" id="estoque-material-codigo">-</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    <i class="bi bi-building me-1"></i>
                                    <span id="estoque-filial-info">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="estoque-id-material">
                <input type="hidden" id="estoque-id-filial">
                
                <!-- Formulário de Estoque -->
                <form id="formEstoque">
                    <div class="row g-4">
                        <!-- Estoque Atual -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control form-control-lg" id="estoque-atual" 
                                       placeholder="0,00">
                                <label for="estoque-atual" class="fw-semibold">
                                    <i class="bi bi-box me-2 text-primary"></i>Estoque Atual
                                </label>
                            </div>
                        </div>
                        
                        <!-- Preço Unitário -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control form-control-lg" id="estoque-preco" 
                                       placeholder="R$ 0,00">
                                <label for="estoque-preco" class="fw-semibold">
                                    <i class="bi bi-currency-dollar me-2 text-success"></i>Preço Unitário
                                </label>
                            </div>
                        </div>
                        
                        <!-- Estoque Mínimo -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="estoque-minimo" 
                                       placeholder="0,00">
                                <label for="estoque-minimo" class="fw-semibold">
                                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Estoque Mínimo
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Configurado por filial. Se vazio, usa valor padrão do catálogo.
                            </small>
                        </div>
                        
                        <!-- Estoque Máximo -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="estoque-maximo" 
                                       placeholder="0,00">
                                <label for="estoque-maximo" class="fw-semibold">
                                    <i class="bi bi-arrow-up-circle me-2 text-info"></i>Estoque Máximo
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Configurado por filial. Se vazio, usa valor padrão do catálogo.
                            </small>
                        </div>
                        
                        <!-- Localização -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="estoque-localizacao" 
                                       placeholder="Ex: Prateleira A, Gaveta 3">
                                <label for="estoque-localizacao" class="fw-semibold">
                                    <i class="bi bi-geo-alt me-2 text-secondary"></i>Localização
                                </label>
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="estoque-observacoes" 
                                          placeholder="Observações adicionais" style="height: 80px"></textarea>
                                <label for="estoque-observacoes" class="fw-semibold">
                                    <i class="bi bi-chat-text me-2 text-muted"></i>Observações
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-light btn-lg px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-lg px-4" onclick="salvarEstoque()">
                    <i class="bi bi-check-lg me-2"></i>Configurar Estoque
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
