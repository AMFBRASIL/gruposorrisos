<?php
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verificar se é fornecedor
if ($_SESSION['usuario_perfil'] !== 'Fornecedor') {
    header('Location: error.php?message=Acesso negado&codigo=403&tipo=warning');
    exit;
}

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem acesso à página atual
if (!$controllerAcesso->verificarAcessoPagina()) {
    // Se não tiver acesso, será redirecionado automaticamente
    exit;
}

// Registrar acesso à página
$controllerAcesso->registrarAcessoPagina();

$menuActive = 'pedidos_fornecedores';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Pedidos para Fornecedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .pedidos-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .pedido-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 4px solid #e2e8f0;
        }
        
        .pedido-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .pedido-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .pedido-numero {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .pedido-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-em_analise { background: #fef3c7; color: #92400e; }
        .status-pendente { background: #fef3c7; color: #92400e; }
        .status-aprovado { background: #d1fae5; color: #065f46; }
        .status-aprovado_cotacao { background: #d1fae5; color: #065f46; }
        .status-aprovado_para_faturar { background: #dcfce7; color: #166534; }
        .status-em_producao { background: #dbeafe; color: #1e40af; }
        .status-enviado { background: #e0e7ff; color: #3730a3; }
        .status-entregue { background: #dcfce7; color: #166534; }
        .status-atrasado { background: #fee2e2; color: #991b1b; }
        .status-urgente { background: #fef2f2; color: #dc2626; }
        .status-em_transito { background: #e0f2fe; color: #0277bd; }
        .status-aguardando_aprovacao { background: #fff3cd; color: #856404; }
        .status-parcialmente_recebido { background: #e8f5e8; color: #2e7d32; }
        .status-recebido { background: #dcfce7; color: #166534; }
        .status-cancelado { background: #f3e5f5; color: #7b1fa2; }
        .status-respondido { background: #dbeafe; color: #1e40af; }
        
        .pedido-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #718096;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .pedido-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        .modal-xl {
            max-width: 90%;
        }
        
        .item-row {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .itens-scroll-container {
            max-height: 45vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 6px;
        }
        
        .price-input {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .price-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .resumo-final-box {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            border: 2px solid #667eea;
            border-radius: 14px;
            padding: 1rem 1.25rem;
        }

        .resumo-final-valor {
            font-size: 2rem;
            font-weight: 800;
            color: #1e40af;
            line-height: 1.1;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>

<main class="main-content">
    <!-- Header da Página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-truck me-3"></i>
            Pedidos para Fornecedor
        </h1>
        <p class="page-subtitle">
            Visualize e responda aos pedidos de compra enviados para sua empresa
        </p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="stat-value" id="total-pedidos">0</div>
            <div class="stat-label">Total de Pedidos</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-value" id="pedidos-pendentes">0</div>
            <div class="stat-label">Aguardando Resposta</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value" id="pedidos-respondidos">0</div>
            <div class="stat-label">Respondidos</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-value" id="valor-total">R$ 0,00</div>
            <div class="stat-label">Valor Total</div>
        </div>
    </div>

    <!-- Container dos Pedidos -->
    <div class="pedidos-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Lista de Pedidos</h3>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="atualizarPedidos()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-primary" onclick="exportarPedidos()">
                    <i class="bi bi-download me-2"></i>Exportar
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" id="filtro-busca" placeholder="Buscar por número do pedido...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filtro-status">
                    <option value="">Todos os Status</option>
                    <option value="aprovado_cotacao">Aprovado Cotação</option>
                    <option value="aprovado_para_faturar">Aprovado para Faturar</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="filtro-data">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" onclick="aplicarFiltros()">
                    <i class="bi bi-funnel me-2"></i>Filtrar
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Carregando pedidos...</p>
        </div>

        <!-- Lista de Pedidos -->
        <div id="lista-pedidos">
            <!-- Pedidos serão carregados aqui via JavaScript -->
        </div>
    </div>
</main>

<!-- Modal de Visualização do Pedido -->
<div class="modal fade" id="modalVisualizarPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Detalhes do Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Navegação por abas -->
                <ul class="nav nav-tabs" id="pedidoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>Detalhes do Pedido
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab" onclick="carregarChat()">
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
                            <!-- Conteúdo será carregado via JavaScript -->
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
                                        <button class="btn btn-primary" type="button" onclick="enviarMensagem()">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportarItensRespostaFornecedor()">
                    <i class="bi bi-download me-2"></i>Exportar Itens
                </button>
                <button type="button" class="btn btn-primary" id="btn-responder-modal" onclick="responderPedido()" style="display: none;">
                    <i class="bi bi-reply me-2"></i>Responder com Preços
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Nota Fiscal -->
<div class="modal fade" id="modalUploadNF" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Enviar Nota Fiscal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecione o arquivo da Nota Fiscal</label>
                    <input type="file" class="form-control" id="input-nota-fiscal" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <small class="text-muted">Formatos aceitos: PDF, JPG, PNG, GIF (máximo 10MB)</small>
                </div>
                <div id="nf-upload-progress" class="progress d-none mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="nf-upload-message" class="alert d-none"></div>
                <div id="nf-uploaded-info" class="alert alert-info d-none">
                    <i class="bi bi-check-circle me-2"></i>
                    <span id="nf-uploaded-text"></span>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="visualizarNF()">
                        <i class="bi bi-eye me-1"></i>Visualizar
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btn-upload-nf" onclick="uploadNotaFiscal()">
                    <i class="bi bi-upload me-2"></i>Enviar Nota Fiscal
                </button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<!-- Modal de Resposta com Preços -->
<div class="modal fade" id="modalResponderPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-reply me-2"></i>
                    Responder Pedido com Preços
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Número do Pedido:</strong> <span id="pedido-numero-resposta"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Data:</strong> <span id="pedido-data-resposta"></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observações:</label>
                    <textarea class="form-control" id="observacoes-fornecedor" rows="3" 
                              placeholder="Adicione observações sobre preços, prazos ou condições..."></textarea>
                </div>
                
                <h6 class="mb-3">Itens do Pedido</h6>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <input type="file" id="input-csv-resposta-fornecedor" accept=".csv,text/csv" class="d-none">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-exportar-csv-resposta-fornecedor">
                        <i class="bi bi-download me-1"></i>Exportar Itens
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-importar-csv-resposta-fornecedor">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i>Importar CSV
                    </button>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" id="badge-csv-resposta-fornecedor">
                        Itens atualizados via CSV: 0
                    </span>
                </div>
                <div class="alert alert-danger py-2 px-3 d-none mb-2" id="itens-nao-encontrados-csv-fornecedor-box">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>Itens do CSV não encontrados no pedido
                    </div>
                    <div class="small" id="itens-nao-encontrados-csv-fornecedor-lista"></div>
                </div>
                <div class="itens-scroll-container">
                    <div id="itens-resposta">
                        <!-- Itens serão carregados aqui -->
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <strong>Prazo de Entrega:</strong>
                        <input type="date" class="form-control mt-2" id="prazo-entrega">
                    </div>
                    <div class="col-md-6">
                        <strong>Condições de Pagamento:</strong>
                        <select class="form-select mt-2" id="condicoes-pagamento">
                            <option value="">Selecione...</option>
                            <option value="30_dias">30 dias</option>
                            <option value="45_dias">45 dias</option>
                            <option value="60_dias">60 dias</option>
                            <option value="a_vista">À vista</option>
                        </select>
                    </div>
                </div>

                <div class="resumo-final-box mt-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1"><strong>Subtotal dos Itens</strong></label>
                            <input type="text" class="form-control" id="subtotal-itens-resposta" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1"><strong>Desconto Final</strong></label>
                            <select class="form-select" id="desconto-final-tipo" onchange="alterarTipoDescontoFinal()">
                                <option value="">Nenhum</option>
                                <option value="valor">Valor</option>
                                <option value="percentual">Percentual</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1"><strong>Valor</strong></label>
                            <input type="text" class="form-control price-input" id="desconto-final-valor" placeholder="0,00" oninput="aplicarMascaraDescontoFinal(this)" onblur="calcularResumoFinal()">
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="text-muted small">Valor Final</div>
                            <div class="resumo-final-valor" id="valor-final-resposta">R$ 0,00</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-resposta" onclick="salvarResposta()">
                    <i class="bi bi-check-lg me-2" id="icon-salvar-resposta"></i>
                    <span id="text-salvar-resposta">Salvar Resposta</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal do Chat -->
<div class="modal fade" id="modalChatPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots me-2"></i>
                    Chat - Pedido <span id="chat-pedido-numero"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Container do Chat -->
                <div class="chat-container" style="height: 500px; display: flex; flex-direction: column;">
                    <!-- Área de mensagens -->
                    <div class="chat-messages" id="chat-messages-modal" style="flex: 1; overflow-y: auto; padding: 20px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                        <div class="text-center text-muted">
                            <i class="bi bi-chat-dots fs-1"></i>
                            <p>Carregando mensagens...</p>
                        </div>
                    </div>
                    
                    <!-- Área de digitação -->
                    <div class="chat-input" style="padding: 20px; background-color: white;">
                        <div class="input-group">
                            <input type="text" class="form-control" id="nova-mensagem-modal" placeholder="Digite sua mensagem..." maxlength="500">
                            <button class="btn btn-primary" type="button" onclick="enviarMensagemModal()">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variáveis globais
let pedidosData = [];
let pedidoAtual = null;

// Funções de prioridade
function getPrioridadeClass(prioridade) {
    const prioridadeMap = {
        'padrao': 'bg-secondary',
        'critico': 'bg-warning',
        'urgente': 'bg-danger'
    };
    return prioridadeMap[prioridade] || 'bg-secondary';
}

function getPrioridadeText(prioridade) {
    const prioridadeMap = {
        'padrao': 'Padrão',
        'critico': 'Crítico',
        'urgente': 'Urgente'
    };
    return prioridadeMap[prioridade] || 'Padrão';
}

function calcularValorTotalPedido(pedido) {
    return parseFloat(pedido?.valor_total) || 0;
}

// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    carregarPedidos();
    configurarFiltros();
});

// Configurar filtros
function configurarFiltros() {
    document.getElementById('filtro-busca').addEventListener('input', aplicarFiltros);
    document.getElementById('filtro-status').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-data').addEventListener('change', aplicarFiltros);
}

// Carregar pedidos
async function carregarPedidos() {
    mostrarLoading(true);
    
    try {
        const response = await fetch('backend/api/pedidos-fornecedor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                fornecedor_id: <?php echo $_SESSION['usuario_id']; ?>,
                action: 'listar_pedidos'
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                pedidosData = data.pedidos || [];
                atualizarEstatisticas();
                renderizarPedidos();
            } else {
                throw new Error(data.error || 'Erro ao carregar pedidos');
            }
        } else {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || `Erro HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Erro ao carregar pedidos:', error);
        
        // Mostrar mensagem de erro para o usuário
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível carregar os pedidos do servidor. Por favor, tente novamente.',
            confirmButtonText: 'OK'
        });
        
        // Limpar dados e mostrar mensagem de erro
        pedidosData = [];
        atualizarEstatisticas();
        renderizarPedidos();
    } finally {
        mostrarLoading(false);
    }
}


// Atualizar estatísticas
function atualizarEstatisticas() {
    const total = pedidosData.length;
    const pendentes = pedidosData.filter(p => ['em_analise', 'pendente', 'aguardando_aprovacao'].includes(p.status)).length;
    const respondidos = pedidosData.filter(p => ['aprovado', 'em_producao', 'enviado', 'entregue', 'em_transito', 'parcialmente_recebido', 'recebido'].includes(p.status)).length;
    const valorTotal = pedidosData.reduce((sum, p) => sum + calcularValorTotalPedido(p), 0);
    
    document.getElementById('total-pedidos').textContent = total;
    document.getElementById('pedidos-pendentes').textContent = pendentes;
    document.getElementById('pedidos-respondidos').textContent = respondidos;
    document.getElementById('valor-total').textContent = `R$ ${valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
}

// Renderizar pedidos
function renderizarPedidos() {
    const container = document.getElementById('lista-pedidos');
    
    if (pedidosData.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Nenhum pedido encontrado</h4>
                <p class="text-muted">Não há pedidos de compra para sua empresa no momento.</p>
            </div>
        `;
        return;
    }
    
    const pedidosFiltrados = filtrarPedidos();
    
    container.innerHTML = pedidosFiltrados.map(pedido => {
        const valorTotalPedido = calcularValorTotalPedido(pedido);
        return `
        <div class="pedido-card">
            <div class="pedido-header">
                <div class="pedido-numero">${pedido.numero}</div>
                <span class="pedido-status status-${pedido.status}">${pedido.status}</span>
            </div>
            
            <div class="pedido-info">
                <div class="info-item">
                    <span class="info-label">Cliente</span>
                    <span class="info-value">${pedido.cliente || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Solicitante</span>
                    <span class="info-value">${pedido.solicitante || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Data</span>
                    <span class="info-value">${formatarData(pedido.data)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Prioridade</span>
                    <span class="info-value">
                        <span class="badge ${getPrioridadeClass(pedido.prioridade)}">
                            ${getPrioridadeText(pedido.prioridade)}
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Valor Total</span>
                    <span class="info-value">R$ ${valorTotalPedido.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Itens</span>
                    <span class="info-value">${pedido.total_itens || pedido.itens.length} item(ns)</span>
                </div>
            </div>
            
            ${pedido.observacoes ? `
            <div class="mb-3">
                <span class="info-label">Observações:</span>
                <span class="info-value">${pedido.observacoes}</span>
            </div>
            ` : ''}
            
            <div class="pedido-actions">
                <button class="btn btn-outline-primary btn-action" onclick="visualizarPedido(${pedido.id})">
                    <i class="bi bi-eye me-2"></i>Visualizar
                </button>
                <button class="btn btn-outline-info btn-action" onclick="abrirChatPedido(${pedido.id}, '${pedido.numero}')">
                    <i class="bi bi-chat-dots me-2"></i>Chat
                </button>
                ${(['em_analise', 'pendente', 'aprovado_cotacao', 'aprovado_para_faturar'].includes(pedido.status)) ? `
                    <button class="btn btn-success btn-action" onclick="responderPedido(${pedido.id})">
                        <i class="bi bi-reply me-2"></i>Responder
                    </button>
                ` : ''}
                ${(['aprovado_para_faturar', 'em_transito', 'entregue', 'aprovado_cotacao'].includes(pedido.status)) ? `
                    <button class="btn btn-info btn-action" onclick="abrirModalUploadNF(${pedido.id})">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Enviar NF
                    </button>
                ` : ''}
            </div>
        </div>
    `;
    }).join('');
}

// Filtrar pedidos
function filtrarPedidos() {
    let filtrados = [...pedidosData];
    
    const busca = document.getElementById('filtro-busca').value.toLowerCase();
    const status = document.getElementById('filtro-status').value;
    const data = document.getElementById('filtro-data').value;
    
    if (busca) {
        filtrados = filtrados.filter(p => 
            p.numero.toLowerCase().includes(busca) ||
            p.cliente.toLowerCase().includes(busca)
        );
    }
    
    if (status) {
        filtrados = filtrados.filter(p => p.status === status);
    }
    
    if (data) {
        filtrados = filtrados.filter(p => p.data === data);
    }
    
    return filtrados;
}

// Aplicar filtros
function aplicarFiltros() {
    renderizarPedidos();
}

// Visualizar pedido
function visualizarPedido(pedidoId) {
    const pedido = pedidosData.find(p => p.id === pedidoId);
    if (!pedido) return;
    
    pedidoAtual = pedido;
    const valorTotalDetalhes = calcularValorTotalPedido(pedido);
    
    const content = document.getElementById('modal-pedido-content');
    content.innerHTML = `
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Número:</strong> ${pedido.numero}
            </div>
            <div class="col-md-6">
                <strong>Data:</strong> ${formatarData(pedido.data)}
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Cliente:</strong> ${pedido.cliente || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Solicitante:</strong> ${pedido.solicitante || 'N/A'}
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Status:</strong> 
                <span class="pedido-status status-${pedido.status}">${pedido.status}</span>
            </div>
            <div class="col-md-6">
                <strong>Prioridade:</strong> 
                <span class="badge ${getPrioridadeClass(pedido.prioridade)}">
                    ${getPrioridadeText(pedido.prioridade)}
                </span>
            </div>
        </div>
        
        ${pedido.observacoes ? `
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-chat-text me-2"></i>Observações</h6>
                    </div>
                    <div class="card-body">
                        ${pedido.observacoes}
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        
        <!-- Seção do Chat -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Conversa do Pedido</h6>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div id="chat-conversa-${pedido.id}" class="chat-conversa">
                            <div class="text-center text-muted">
                                <i class="bi bi-chat-dots fs-4"></i>
                                <p class="mb-0">Carregando conversa...</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="input-group">
                            <input type="text" class="form-control" id="nova-mensagem-${pedido.id}" placeholder="Digite sua mensagem..." maxlength="500">
                            <button class="btn btn-primary" type="button" onclick="enviarMensagemDetalhes(${pedido.id})">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                        <small class="text-muted">Pressione Enter para enviar</small>
                    </div>
                </div>
            </div>
        </div>
        
        <h6 class="mb-3">Itens do Pedido</h6>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Código</th>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                        <th>Preço Unitário</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${renderizarLinhasItensDetalhes(pedido)}
                </tbody>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <strong>Valor Total:</strong> R$ ${valorTotalDetalhes.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
            </div>
            <div class="col-md-6">
                <strong>Total de Itens:</strong> ${pedido.total_itens || pedido.itens.length}
            </div>
        </div>
        
        ${(['aprovado_para_faturar', 'em_transito', 'entregue', 'aprovado_cotacao'].includes(pedido.status)) ? `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Nota Fiscal</h6>
                    </div>
                    <div class="card-body">
                        <div id="nf-status-${pedido.id}">
                            <p class="text-muted">Nenhuma Nota Fiscal enviada ainda.</p>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalUploadNF(${pedido.id})">
                                <i class="bi bi-upload me-2"></i>Enviar Nota Fiscal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    // Mostrar/ocultar botão "Responder com Preços" baseado no status
    setTimeout(() => {
        const btnResponderModal = document.getElementById('btn-responder-modal');
        if (btnResponderModal) {
            if (['em_analise', 'pendente', 'aprovado_cotacao', 'aprovado_para_faturar'].includes(pedido.status)) {
                btnResponderModal.style.display = 'inline-block';
            } else {
                btnResponderModal.style.display = 'none';
            }
        }
    }, 100);
    
    const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPedido'));
    modal.show();
}

// Responder pedido
function responderPedido(pedidoId = null) {
    const pedido = pedidoId ? pedidosData.find(p => p.id === pedidoId) : pedidoAtual;
    if (!pedido) return;
    
    pedidoAtual = pedido;
    
    // Fechar modal de visualização se estiver aberto
    const modalVisualizar = bootstrap.Modal.getInstance(document.getElementById('modalVisualizarPedido'));
    if (modalVisualizar) {
        modalVisualizar.hide();
    }
    
    // Preencher dados do modal de resposta
    document.getElementById('pedido-numero-resposta').textContent = pedido.numero;
    document.getElementById('pedido-data-resposta').textContent = formatarData(pedido.data);
    document.getElementById('desconto-final-tipo').value = '';
    document.getElementById('desconto-final-valor').value = '';
    document.getElementById('desconto-final-valor').placeholder = '0,00';
    document.getElementById('subtotal-itens-resposta').value = 'R$ 0,00';
    document.getElementById('valor-final-resposta').textContent = 'R$ 0,00';
    atualizarBadgeCsvFornecedor(0);
    renderizarItensNaoEncontradosCsvFornecedor([]);
    const inputCsvResposta = document.getElementById('input-csv-resposta-fornecedor');
    if (inputCsvResposta) {
        inputCsvResposta.value = '';
    }
    
    // Renderizar itens para resposta
    const itensContainer = document.getElementById('itens-resposta');
    itensContainer.innerHTML = pedido.itens.map((item, index) => `
        <div class="item-row mb-3">
            <div class="row align-items-start">
                <div class="col-md-4 mb-2">
                    <strong>${item.nome}</strong>
                    <div class="text-muted small mt-1">
                        <strong>Código:</strong> ${item.codigo || 'N/A'} | 
                        <strong>Categoria:</strong> ${item.categoria || 'N/A'}
                    </div>
                    <div class="text-muted small mt-1">
                        <strong>Solicitado:</strong> <span class="badge bg-info">${item.quantidade} ${item.unidade}</span>
                    </div>
                    ${item.observacoes ? `<div class="text-muted small mt-1"><em>${item.observacoes}</em></div>` : ''}
                    <div class="mt-1">
                        <small class="text-muted">Preço atual: R$ ${item.preco_unitario.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small">Quantidade Disponível</label>
                            <input type="number" class="form-control price-input" 
                                   id="quantidade-${index}" step="0.01" min="0" 
                                   value="${item.quantidade}" 
                                   placeholder="0,00" 
                                   onchange="calcularTotalItem(${index})"
                                   oninput="validarQuantidade(${index}, ${item.quantidade})">
                            <small class="text-muted">Máx: ${item.quantidade} ${item.unidade}</small>
                            <small class="text-info d-block mt-1" id="info-quantidade-${index}"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Preço Unitário (R$)</label>
                            <input type="text" class="form-control price-input" 
                                   id="preco-${index}" 
                                   placeholder="R$ 0,00" 
                                   oninput="aplicarMascaraMoeda(this, ${index})"
                                   onblur="calcularTotalItem(${index})"
                                   onfocus="this.select()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Desconto</label>
                            <select class="form-select" id="desconto-tipo-${index}" onchange="alterarTipoDesconto(${index})">
                                <option value="">Nenhum</option>
                                <option value="valor">Valor</option>
                                <option value="percentual">Percentual</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Desc. Aplicado</label>
                            <input type="text" class="form-control price-input"
                                   id="desconto-valor-${index}"
                                   placeholder="0,00"
                                   oninput="aplicarMascaraDesconto(this, ${index})"
                                   onblur="calcularTotalItem(${index})">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Total (R$)</label>
                            <input type="text" class="form-control" id="total-${index}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Disponível</label>
                            <select class="form-select" id="disponivel-${index}" onchange="atualizarDisponibilidade(${index})">
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    // Abrir modal de resposta
    const modal = new bootstrap.Modal(document.getElementById('modalResponderPedido'));
    modal.show();
    
    // Aplicar máscaras nos campos de preço após o modal ser exibido
    setTimeout(() => {
        pedido.itens.forEach((item, index) => {
            const quantidadeInput = document.getElementById(`quantidade-${index}`);
            const precoInput = document.getElementById(`preco-${index}`);
            const disponivelInput = document.getElementById(`disponivel-${index}`);
            const descontoTipoInput = document.getElementById(`desconto-tipo-${index}`);
            const descontoValorInput = document.getElementById(`desconto-valor-${index}`);

            const disponivelRaw = item?.disponivel;
            const disponivel = (disponivelRaw !== null && disponivelRaw !== undefined && disponivelRaw !== '') ? parseInt(disponivelRaw, 10) : 1;
            const quantidadeSolicitada = parseFloat(item?.quantidade) || 0;
            const quantidadeDisponivelRaw = item?.quantidade_disponivel;
            const quantidadeDisponivel = (quantidadeDisponivelRaw !== null && quantidadeDisponivelRaw !== undefined && quantidadeDisponivelRaw !== '')
                ? parseFloat(quantidadeDisponivelRaw)
                : null;

            // Resetar desconto ao abrir, mantendo subtotal baseado no preço salvo
            if (descontoTipoInput) descontoTipoInput.value = '';
            if (descontoValorInput) descontoValorInput.value = '';

            if (disponivelInput) {
                disponivelInput.value = (disponivel === 0) ? 'nao' : 'sim';
            }

            // Se já houver resposta salva, usar quantidade disponível como base
            if (quantidadeInput) {
                if (disponivel === 0) {
                    quantidadeInput.value = 0;
                } else {
                    quantidadeInput.value = (quantidadeDisponivel !== null && !Number.isNaN(quantidadeDisponivel))
                        ? quantidadeDisponivel
                        : quantidadeSolicitada;
                }
            }

            if (precoInput) {
                // Priorizar preço já respondido pelo fornecedor, com fallback para preço original
                const precoRespondido = parseFloat(item.preco_fornecedor);
                const precoOriginal = parseFloat(item.preco_unitario);
                const precoParaPreencher = (!Number.isNaN(precoRespondido) && precoRespondido > 0)
                    ? precoRespondido
                    : ((!Number.isNaN(precoOriginal) && precoOriginal > 0) ? precoOriginal : 0);

                if (precoParaPreencher > 0) {
                    const valorFormatado = precoParaPreencher.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                    precoInput.value = valorFormatado;
                }
            }

            if (disponivelInput && disponivelInput.value === 'nao') {
                atualizarDisponibilidade(index);
            } else {
                calcularTotalItem(index);
            }
        });
        calcularResumoFinal();
    }, 300);
}

// Validar quantidade
function validarQuantidade(index, quantidadeSolicitada) {
    const quantidadeInput = document.getElementById(`quantidade-${index}`);
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    const infoQuantidade = document.getElementById(`info-quantidade-${index}`);
    
    if (quantidade > quantidadeSolicitada) {
        quantidadeInput.value = quantidadeSolicitada;
        infoQuantidade.textContent = 'Quantidade ajustada para o máximo solicitado';
        infoQuantidade.className = 'text-warning d-block mt-1';
    } else if (quantidade < quantidadeSolicitada && quantidade > 0) {
        infoQuantidade.textContent = `Disponível: ${quantidade} de ${quantidadeSolicitada} solicitados`;
        infoQuantidade.className = 'text-warning d-block mt-1';
    } else if (quantidade === quantidadeSolicitada) {
        infoQuantidade.textContent = 'Quantidade completa disponível';
        infoQuantidade.className = 'text-success d-block mt-1';
    } else {
        infoQuantidade.textContent = '';
    }
    
    calcularTotalItem(index);
}

// Atualizar disponibilidade
function atualizarDisponibilidade(index) {
    const disponivel = document.getElementById(`disponivel-${index}`).value;
    const quantidadeInput = document.getElementById(`quantidade-${index}`);
    const precoInput = document.getElementById(`preco-${index}`);
    const descontoTipoInput = document.getElementById(`desconto-tipo-${index}`);
    const descontoValorInput = document.getElementById(`desconto-valor-${index}`);
    const infoQuantidade = document.getElementById(`info-quantidade-${index}`);
    
    if (disponivel === 'nao') {
        quantidadeInput.value = 0;
        quantidadeInput.disabled = true;
        precoInput.value = '';
        precoInput.disabled = true;
        descontoTipoInput.value = '';
        descontoTipoInput.disabled = true;
        descontoValorInput.value = '';
        descontoValorInput.disabled = true;
        infoQuantidade.textContent = 'Item não disponível';
        infoQuantidade.className = 'text-danger d-block mt-1';
    } else {
        const pedido = pedidosData.find(p => p.id === pedidoAtual.id);
        const item = pedido.itens[index];
        quantidadeInput.value = item.quantidade;
        quantidadeInput.disabled = false;
        precoInput.disabled = false;
        precoInput.placeholder = 'R$ 0,00';
        descontoTipoInput.disabled = false;
        descontoValorInput.disabled = false;
        descontoValorInput.placeholder = '0,00';
        infoQuantidade.textContent = '';
    }
    
    calcularTotalItem(index);
}

// Aplicar máscara de moeda brasileira
function aplicarMascaraMoeda(input, index) {
    let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    if (value.length === 0) {
        input.value = '';
        calcularTotalItem(index);
        return;
    }
    
    // Converte para número e formata
    const number = parseFloat(value) / 100;
    const valorFormatado = number.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Adicionar prefixo "R$ "
    input.value = `R$ ${valorFormatado}`;
    
    // Calcular total enquanto digita
    calcularTotalItem(index);
}

// Remover máscara e obter valor numérico
function removerMascaraMoeda(valorFormatado) {
    if (!valorFormatado) return 0;
    // Remove R$, espaços e converte vírgula para ponto
    const valorLimpo = valorFormatado.replace(/R\$\s?/g, '').replace(/\./g, '').replace(',', '.');
    return parseFloat(valorLimpo) || 0;
}

function normalizarTextoCsvFornecedor(valor) {
    return (valor || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function parseNumeroCsvFornecedor(valor) {
    if (valor === null || valor === undefined) return 0;

    let texto = String(valor).trim();
    if (!texto) return 0;

    texto = texto.replace(/\s/g, '').replace(/R\$/gi, '');

    if (texto.includes(',')) {
        texto = texto.replace(/\./g, '').replace(',', '.');
        return parseFloat(texto) || 0;
    }

    if (/^\d{1,3}(\.\d{3})+$/.test(texto)) {
        texto = texto.replace(/\./g, '');
    }

    return parseFloat(texto) || 0;
}

function renderizarItensNaoEncontradosCsvFornecedor(itensNaoEncontrados = []) {
    const box = document.getElementById('itens-nao-encontrados-csv-fornecedor-box');
    const lista = document.getElementById('itens-nao-encontrados-csv-fornecedor-lista');
    if (!box || !lista) return;

    const itensUnicos = [...new Set((itensNaoEncontrados || []).map(item => (item || '').toString().trim()).filter(Boolean))];
    if (itensUnicos.length === 0) {
        box.classList.add('d-none');
        lista.innerHTML = '';
        return;
    }

    lista.innerHTML = itensUnicos.map(item => `<span class="badge bg-danger me-1 mb-1">${item}</span>`).join('');
    box.classList.remove('d-none');
}

function atualizarBadgeCsvFornecedor(totalAtualizados = 0) {
    const badge = document.getElementById('badge-csv-resposta-fornecedor');
    if (!badge) return;
    badge.textContent = `Itens atualizados via CSV: ${totalAtualizados}`;
}

function processarCsvRespostaFornecedor(textoCsv) {
    const pedido = pedidoAtual ? pedidosData.find(p => p.id === pedidoAtual.id) : null;
    if (!pedido || !Array.isArray(pedido.itens) || pedido.itens.length === 0) {
        Swal.fire('Erro', 'Não há itens carregados para aplicar a importação.', 'error');
        return;
    }

    const linhas = textoCsv.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
    if (linhas.length < 2) {
        Swal.fire('Erro', 'CSV inválido: arquivo sem linhas de itens.', 'error');
        return;
    }

    const delimitador = linhas[0].includes(';') ? ';' : ',';
    const cabecalho = linhas[0].split(delimitador).map(col => normalizarTextoCsvFornecedor(col).replace(/[^a-z0-9]/g, ''));

    let idxModelo = -1;
    let idxProduto = -1;
    let idxQuant = -1;
    let idxUnitario = -1;
    let idxTotal = -1;

    cabecalho.forEach((coluna, indice) => {
        if (idxModelo === -1 && coluna.startsWith('modelo')) idxModelo = indice;
        if (idxProduto === -1 && coluna.startsWith('produto')) idxProduto = indice;
        if (idxQuant === -1 && (coluna.startsWith('quant') || coluna.startsWith('quat'))) idxQuant = indice;
        if (idxUnitario === -1 && coluna.startsWith('unit')) idxUnitario = indice;
        if (idxTotal === -1 && coluna.startsWith('total')) idxTotal = indice;
    });

    if (idxModelo === -1 || idxProduto === -1 || idxQuant === -1 || idxUnitario === -1 || idxTotal === -1) {
        Swal.fire('Erro', 'CSV inválido: colunas esperadas não encontradas (Modelo, Produto, Quant, Unitario, Total).', 'error');
        return;
    }

    const mapaPorCodigo = new Map();
    const mapaPorNome = new Map();
    pedido.itens.forEach((item, index) => {
        const codigo = (item.codigo || '').toString().trim();
        const nomeNormalizado = normalizarTextoCsvFornecedor(item.nome || '');
        if (codigo) mapaPorCodigo.set(codigo, index);
        if (nomeNormalizado) mapaPorNome.set(nomeNormalizado, index);
    });

    const itensNaoEncontrados = [];
    const indicesAtualizados = new Set();

    for (let i = 1; i < linhas.length; i++) {
        const colunas = linhas[i].split(delimitador);
        if (!colunas.length) continue;

        const codigoCsv = (colunas[idxModelo] || '').toString().trim();
        const nomeCsv = (colunas[idxProduto] || '').toString().trim();
        const quantidadeCsv = parseNumeroCsvFornecedor(colunas[idxQuant]);
        const unitarioCsv = parseNumeroCsvFornecedor(colunas[idxUnitario]);

        if (!codigoCsv && !nomeCsv) continue;

        let itemIndex = mapaPorCodigo.get(codigoCsv);
        if (itemIndex === undefined && nomeCsv) {
            itemIndex = mapaPorNome.get(normalizarTextoCsvFornecedor(nomeCsv));
        }

        if (itemIndex === undefined) {
            itensNaoEncontrados.push(codigoCsv || nomeCsv);
            continue;
        }

        const quantidadeInput = document.getElementById(`quantidade-${itemIndex}`);
        const precoInput = document.getElementById(`preco-${itemIndex}`);
        const disponivelInput = document.getElementById(`disponivel-${itemIndex}`);
        const descontoTipoInput = document.getElementById(`desconto-tipo-${itemIndex}`);
        const descontoValorInput = document.getElementById(`desconto-valor-${itemIndex}`);

        if (!quantidadeInput || !precoInput || !disponivelInput) {
            itensNaoEncontrados.push(codigoCsv || nomeCsv);
            continue;
        }

        if (descontoTipoInput) descontoTipoInput.value = '';
        if (descontoValorInput) descontoValorInput.value = '';

        disponivelInput.value = 'sim';

        if (quantidadeCsv > 0) {
            quantidadeInput.value = quantidadeCsv;
        }
        if (unitarioCsv > 0) {
            precoInput.value = `R$ ${unitarioCsv.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        const quantidadeSolicitada = parseFloat(pedido.itens[itemIndex].quantidade) || 0;
        validarQuantidade(itemIndex, quantidadeSolicitada);
        calcularTotalItem(itemIndex);
        indicesAtualizados.add(itemIndex);
    }

    calcularResumoFinal();
    atualizarBadgeCsvFornecedor(indicesAtualizados.size);
    renderizarItensNaoEncontradosCsvFornecedor(itensNaoEncontrados);

    if (indicesAtualizados.size === 0) {
        Swal.fire('Erro', 'Nenhum item do CSV foi encontrado entre os itens do pedido.', 'error');
        return;
    }

    const qtdNaoEncontrados = itensNaoEncontrados.length;
    if (qtdNaoEncontrados > 0) {
        Swal.fire('Atenção', `CSV aplicado com sucesso em ${indicesAtualizados.size} item(ns). ${qtdNaoEncontrados} item(ns) não foram encontrados.`, 'warning');
    } else {
        Swal.fire('Sucesso', `CSV aplicado com sucesso em ${indicesAtualizados.size} item(ns).`, 'success');
    }
}

function importarCsvRespostaFornecedor(arquivo) {
    if (!arquivo) return;
    const nomeArquivo = (arquivo.name || '').toLowerCase();
    if (!nomeArquivo.endsWith('.csv')) {
        Swal.fire('Erro', 'Selecione um arquivo CSV válido.', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        try {
            processarCsvRespostaFornecedor(event.target?.result || '');
        } catch (error) {
            console.error('Erro ao processar CSV:', error);
            Swal.fire('Erro', 'Não foi possível processar o CSV informado.', 'error');
        }
    };
    reader.onerror = () => Swal.fire('Erro', 'Erro ao ler o arquivo CSV.', 'error');
    reader.readAsText(arquivo, 'ISO-8859-1');
}

function formatarNumeroCsvFornecedor(valor) {
    const numero = parseFloat(valor) || 0;
    return numero.toString().replace('.', ',');
}

function escaparCampoCsvFornecedor(valor) {
    const texto = (valor ?? '').toString();
    if (texto.includes(';') || texto.includes('"') || texto.includes('\n')) {
        return `"${texto.replace(/"/g, '""')}"`;
    }
    return texto;
}

function exportarItensRespostaFornecedor() {
    if (!pedidoAtual || !Array.isArray(pedidoAtual.itens) || pedidoAtual.itens.length === 0) {
        Swal.fire('Atenção', 'Nenhum item disponível para exportação.', 'warning');
        return;
    }

    const linhas = ['Modelo;Produto;Quant.;Unitario;Total'];

    pedidoAtual.itens.forEach((item, index) => {
        const codigo = item.codigo || '';
        const nome = item.nome || '';

        const quantidadeInput = document.getElementById(`quantidade-${index}`);
        const precoInput = document.getElementById(`preco-${index}`);

        const quantidade = quantidadeInput ? (parseFloat(quantidadeInput.value) || 0) : (parseFloat(item.quantidade_disponivel) || parseFloat(item.quantidade) || 0);
        let precoUnitario = precoInput ? removerMascaraMoeda(precoInput.value) : 0;
        if (!precoUnitario) {
            const precoFornecedor = parseFloat(item.preco_fornecedor);
            const precoOriginal = parseFloat(item.preco_unitario);
            precoUnitario = (!Number.isNaN(precoFornecedor) && precoFornecedor > 0)
                ? precoFornecedor
                : ((!Number.isNaN(precoOriginal) && precoOriginal > 0) ? precoOriginal : 0);
        }

        const total = quantidade * precoUnitario;

        linhas.push([
            escaparCampoCsvFornecedor(codigo),
            escaparCampoCsvFornecedor(nome),
            formatarNumeroCsvFornecedor(quantidade),
            formatarNumeroCsvFornecedor(precoUnitario),
            formatarNumeroCsvFornecedor(total)
        ].join(';'));
    });

    const csvContent = linhas.join('\r\n');
    const blob = new Blob([`\uFEFF${csvContent}`], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const numeroPedido = (pedidoAtual.numero || 'pedido').toString().replace(/[^\w-]/g, '_');
    link.href = url;
    link.download = `itens_${numeroPedido}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Alterar tipo de desconto por item
function alterarTipoDesconto(index) {
    const descontoTipoInput = document.getElementById(`desconto-tipo-${index}`);
    const descontoValorInput = document.getElementById(`desconto-valor-${index}`);
    const tipo = descontoTipoInput.value;

    descontoValorInput.value = '';
    descontoValorInput.classList.remove('is-invalid');

    if (tipo === 'valor') {
        descontoValorInput.placeholder = 'R$ 0,00';
    } else if (tipo === 'percentual') {
        descontoValorInput.placeholder = '0,00%';
    } else {
        descontoValorInput.placeholder = '0,00';
    }

    calcularTotalItem(index);
}

// Aplicar máscara de desconto de acordo com o tipo selecionado
function aplicarMascaraDesconto(input, index) {
    const tipo = document.getElementById(`desconto-tipo-${index}`).value;

    if (!tipo) {
        input.value = '';
        calcularTotalItem(index);
        return;
    }

    if (tipo === 'valor') {
        aplicarMascaraMoeda(input, index);
        return;
    }

    if (tipo === 'percentual') {
        let value = input.value.replace(/[^\d,]/g, '');
        if (!value) {
            input.value = '';
            calcularTotalItem(index);
            return;
        }

        const partes = value.split(',');
        if (partes.length > 2) {
            value = `${partes[0]},${partes.slice(1).join('')}`;
        }

        input.value = value;
        calcularTotalItem(index);
    }
}

function removerMascaraPercentual(valorFormatado) {
    if (!valorFormatado) return 0;
    const valorLimpo = valorFormatado.replace('%', '').replace(/\./g, '').replace(',', '.').trim();
    return parseFloat(valorLimpo) || 0;
}

function obterDescontoItem(index, precoUnitario) {
    const descontoTipo = document.getElementById(`desconto-tipo-${index}`).value;
    const descontoValorInput = document.getElementById(`desconto-valor-${index}`);

    let descontoValor = 0;
    if (descontoTipo === 'valor') {
        descontoValor = removerMascaraMoeda(descontoValorInput.value);
    } else if (descontoTipo === 'percentual') {
        descontoValor = removerMascaraPercentual(descontoValorInput.value);
    }

    descontoValorInput.classList.remove('is-invalid');

    if (descontoTipo === 'percentual' && descontoValor > 100) {
        descontoValorInput.classList.add('is-invalid');
        descontoValor = 100;
    }

    let descontoUnitario = 0;
    if (descontoTipo === 'valor') {
        descontoUnitario = descontoValor;
    } else if (descontoTipo === 'percentual') {
        descontoUnitario = (precoUnitario * descontoValor) / 100;
    }

    if (descontoUnitario > precoUnitario) {
        descontoUnitario = precoUnitario;
    }

    const precoFinalUnitario = Math.max(precoUnitario - descontoUnitario, 0);

    return {
        desconto_tipo: descontoTipo || null,
        desconto_valor: descontoValor,
        desconto_unitario: descontoUnitario,
        preco_final_unitario: precoFinalUnitario
    };
}

function alterarTipoDescontoFinal() {
    const tipoInput = document.getElementById('desconto-final-tipo');
    const valorInput = document.getElementById('desconto-final-valor');
    const tipo = tipoInput.value;

    valorInput.value = '';
    valorInput.classList.remove('is-invalid');

    if (tipo === 'valor') {
        valorInput.placeholder = 'R$ 0,00';
    } else if (tipo === 'percentual') {
        valorInput.placeholder = '0,00%';
    } else {
        valorInput.placeholder = '0,00';
    }

    calcularResumoFinal();
}

function aplicarMascaraDescontoFinal(input) {
    const tipo = document.getElementById('desconto-final-tipo').value;

    if (!tipo) {
        input.value = '';
        calcularResumoFinal();
        return;
    }

    if (tipo === 'valor') {
        let value = input.value.replace(/\D/g, '');
        if (!value) {
            input.value = '';
            calcularResumoFinal();
            return;
        }
        const number = parseFloat(value) / 100;
        input.value = `R$ ${number.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        calcularResumoFinal();
        return;
    }

    let value = input.value.replace(/[^\d,]/g, '');
    if (!value) {
        input.value = '';
        calcularResumoFinal();
        return;
    }

    const partes = value.split(',');
    if (partes.length > 2) {
        value = `${partes[0]},${partes.slice(1).join('')}`;
    }

    input.value = value;
    calcularResumoFinal();
}

function calcularResumoFinal() {
    if (!pedidoAtual) return null;

    const pedido = pedidosData.find(p => p.id === pedidoAtual.id);
    if (!pedido) return null;

    let subtotal = 0;
    for (let i = 0; i < pedido.itens.length; i++) {
        const quantidadeEl = document.getElementById(`quantidade-${i}`);
        const precoEl = document.getElementById(`preco-${i}`);
        if (!quantidadeEl || !precoEl) continue;

        const quantidade = parseFloat(quantidadeEl.value) || 0;
        const preco = removerMascaraMoeda(precoEl.value);
        const desconto = obterDescontoItem(i, preco);
        subtotal += quantidade * desconto.preco_final_unitario;
    }

    const descontoFinalTipo = document.getElementById('desconto-final-tipo').value;
    const descontoFinalValorInput = document.getElementById('desconto-final-valor');
    let descontoFinalValor = 0;

    if (descontoFinalTipo === 'valor') {
        descontoFinalValor = removerMascaraMoeda(descontoFinalValorInput.value);
    } else if (descontoFinalTipo === 'percentual') {
        descontoFinalValor = removerMascaraPercentual(descontoFinalValorInput.value);
    }

    descontoFinalValorInput.classList.remove('is-invalid');
    if (descontoFinalTipo === 'percentual' && descontoFinalValor > 100) {
        descontoFinalValorInput.classList.add('is-invalid');
        descontoFinalValor = 100;
    }

    let descontoFinalTotal = 0;
    if (descontoFinalTipo === 'valor') {
        descontoFinalTotal = descontoFinalValor;
    } else if (descontoFinalTipo === 'percentual') {
        descontoFinalTotal = (subtotal * descontoFinalValor) / 100;
    }

    if (descontoFinalTotal > subtotal) {
        descontoFinalTotal = subtotal;
    }

    const totalFinal = Math.max(subtotal - descontoFinalTotal, 0);

    document.getElementById('subtotal-itens-resposta').value = subtotal.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
    document.getElementById('valor-final-resposta').textContent = totalFinal.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    return {
        subtotal_itens: subtotal,
        desconto_final_tipo: descontoFinalTipo || null,
        desconto_final_valor: descontoFinalValor,
        desconto_final_total: descontoFinalTotal,
        total_final: totalFinal
    };
}

// Calcular total do item
function calcularTotalItem(index) {
    const quantidade = parseFloat(document.getElementById(`quantidade-${index}`).value) || 0;
    const precoInput = document.getElementById(`preco-${index}`);
    const preco = removerMascaraMoeda(precoInput.value);
    const desconto = obterDescontoItem(index, preco);
    const total = quantidade * desconto.preco_final_unitario;
    
    document.getElementById(`total-${index}`).value = total.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    calcularResumoFinal();
}

// Salvar resposta
async function salvarResposta() {
    // Obter referências do botão
    const btnSalvar = document.getElementById('btn-salvar-resposta');
    const iconSalvar = document.getElementById('icon-salvar-resposta');
    const textSalvar = document.getElementById('text-salvar-resposta');
    
    // Verificar se já está processando
    if (btnSalvar.disabled) {
        return;
    }
    
    try {
        // Validar campos obrigatórios
        const observacoes = document.getElementById('observacoes-fornecedor').value;
        const prazoEntrega = document.getElementById('prazo-entrega').value;
        const condicoesPagamento = document.getElementById('condicoes-pagamento').value;
        const resumoFinal = calcularResumoFinal();
        
        if (!prazoEntrega || !condicoesPagamento) {
            Swal.fire('Erro', 'Preencha todos os campos obrigatórios', 'error');
            return;
        }
        
        // Ativar estado de loading
        btnSalvar.disabled = true;
        iconSalvar.className = 'spinner-border spinner-border-sm me-2';
        textSalvar.textContent = 'Carregando...';
        
        // Coletar preços dos itens
        const itensResposta = [];
        const pedido = pedidosData.find(p => p.id === pedidoAtual.id);
        
        for (let i = 0; i < pedido.itens.length; i++) {
            const quantidade = parseFloat(document.getElementById(`quantidade-${i}`).value) || 0;
            const precoInput = document.getElementById(`preco-${i}`);
            const preco = removerMascaraMoeda(precoInput.value);
            const desconto = obterDescontoItem(i, preco);
            const disponivel = document.getElementById(`disponivel-${i}`).value;
            
            if (disponivel === 'sim') {
                if (preco <= 0) {
                    // Restaurar botão em caso de erro de validação
                    btnSalvar.disabled = false;
                    iconSalvar.className = 'bi bi-check-lg me-2';
                    textSalvar.textContent = 'Salvar Resposta';
                    Swal.fire('Erro', `Informe o preço para o item "${pedido.itens[i].nome}"`, 'error');
                    return;
                }
                if (quantidade <= 0) {
                    // Restaurar botão em caso de erro de validação
                    btnSalvar.disabled = false;
                    iconSalvar.className = 'bi bi-check-lg me-2';
                    textSalvar.textContent = 'Salvar Resposta';
                    Swal.fire('Erro', `Informe a quantidade disponível para o item "${pedido.itens[i].nome}"`, 'error');
                    return;
                }
            }
            
            itensResposta.push({
                item_id: pedido.itens[i].id || i,
                quantidade_solicitada: pedido.itens[i].quantidade,
                quantidade_disponivel: quantidade,
                preco_original: preco,
                desconto_tipo: desconto.desconto_tipo,
                desconto_valor: desconto.desconto_valor,
                desconto_unitario: desconto.desconto_unitario,
                preco: desconto.preco_final_unitario,
                disponivel: disponivel
            });
        }
        
        // Simular envio para API
        const response = await fetch('backend/api/pedidos-fornecedor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'responder_pedido',
                pedido_id: pedidoAtual.id,
                fornecedor_id: <?php echo $_SESSION['usuario_id']; ?>,
                observacoes: observacoes,
                prazo_entrega: prazoEntrega,
                condicoes_pagamento: condicoesPagamento,
                itens: itensResposta,
                subtotal_itens: resumoFinal?.subtotal_itens || 0,
                desconto_final_tipo: resumoFinal?.desconto_final_tipo || null,
                desconto_final_valor: resumoFinal?.desconto_final_valor || 0,
                desconto_final_total: resumoFinal?.desconto_final_total || 0,
                total_final: resumoFinal?.total_final || 0
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                // Atualizar status do pedido para 'pendente'
                pedido.status = 'pendente';
                atualizarEstatisticas();
                renderizarPedidos();
                
                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalResponderPedido'));
                modal.hide();
                
                Swal.fire('Sucesso', 'Resposta enviada com sucesso! O pedido está aguardando aprovação.', 'success');
            } else {
                throw new Error(data.error || 'Erro ao enviar resposta');
            }
        } else {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || 'Erro ao enviar resposta');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        
        // Restaurar botão em caso de erro
        if (btnSalvar) {
            btnSalvar.disabled = false;
            if (iconSalvar) iconSalvar.className = 'bi bi-check-lg me-2';
            if (textSalvar) textSalvar.textContent = 'Salvar Resposta';
        }
        
        Swal.fire('Erro', error.message || 'Erro ao enviar resposta. Tente novamente.', 'error');
    }
}

// Atualizar pedidos
function atualizarPedidos() {
    carregarPedidos();
}

// Exportar pedidos
function exportarPedidos() {
    // Implementar exportação para Excel/PDF
    Swal.fire('Info', 'Funcionalidade de exportação será implementada em breve!', 'info');
}

// Mostrar/ocultar loading
function mostrarLoading(show) {
    const loading = document.getElementById('loading');
    if (show) {
        loading.classList.add('show');
    } else {
        loading.classList.remove('show');
    }
}

// Formatar data
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function obterQuantidadeParaCalculoItem(item) {
    const disponivelRaw = item?.disponivel;
    const disponivel = (disponivelRaw !== null && disponivelRaw !== undefined && disponivelRaw !== '') ? parseInt(disponivelRaw, 10) : null;
    const quantidadeSolicitada = parseFloat(item?.quantidade) || 0;
    const quantidadeDisponivelRaw = item?.quantidade_disponivel;
    const quantidadeDisponivel = (quantidadeDisponivelRaw !== null && quantidadeDisponivelRaw !== undefined && quantidadeDisponivelRaw !== '') ? parseFloat(quantidadeDisponivelRaw) : null;

    if (disponivel === 0) return 0;
    if (disponivel === 1 && quantidadeDisponivel !== null && !Number.isNaN(quantidadeDisponivel)) return quantidadeDisponivel;
    return quantidadeSolicitada;
}

function obterPrecoParaCalculoItem(item) {
    const precoFornecedorRaw = item?.preco_fornecedor;
    const precoFornecedor = (precoFornecedorRaw !== null && precoFornecedorRaw !== undefined && precoFornecedorRaw !== '') ? parseFloat(precoFornecedorRaw) : null;
    if (precoFornecedor !== null && !Number.isNaN(precoFornecedor) && precoFornecedor > 0) {
        return precoFornecedor;
    }

    return parseFloat(item?.preco_unitario) || 0;
}

function renderizarLinhasItensDetalhes(pedido) {
    if (!pedido?.itens?.length) return '';

    return pedido.itens.map(item => {
        const quantidadeCalculo = obterQuantidadeParaCalculoItem(item);
        const precoCalculo = obterPrecoParaCalculoItem(item);
        const totalItem = quantidadeCalculo * precoCalculo;
        const quantidadeSolicitada = parseFloat(item.quantidade) || 0;

        return `
            <tr>
                <td>
                    <strong>${item.nome}</strong>
                    ${item.observacoes ? `<br><small class="text-muted">${item.observacoes}</small>` : ''}
                </td>
                <td>${item.codigo || 'N/A'}</td>
                <td>${item.categoria || 'N/A'}</td>
                <td>
                    ${quantidadeCalculo}
                    ${(quantidadeCalculo !== quantidadeSolicitada) ? `<br><small class="text-muted">Solicitado: ${quantidadeSolicitada}</small>` : ''}
                </td>
                <td>${item.unidade}</td>
                <td>R$ ${precoCalculo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td>R$ ${totalItem.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
    }).join('');
}

function calcularValorTotalDetalhesPedido(pedido) {
    if (!pedido?.itens?.length) return 0;

    return pedido.itens.reduce((sum, item) => {
        const quantidade = obterQuantidadeParaCalculoItem(item);
        const preco = obterPrecoParaCalculoItem(item);
        return sum + (quantidade * preco);
    }, 0);
}

// ===== FUNÇÕES DO CHAT =====
let chatInterval;
let pedidoIdAtual;

// Carregar mensagens do chat
async function carregarChat() {
    if (!pedidoAtual) return;
    
    pedidoIdAtual = pedidoAtual.id;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoIdAtual
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagens(data.mensagens);
            marcarMensagensComoLidas();
            
            // Iniciar atualização automática
            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(() => {
                if (document.getElementById('chat').classList.contains('active')) {
                    carregarNovasMensagens();
                }
            }, 3000);
        }
    } catch (error) {
        console.error('Erro ao carregar chat:', error);
        document.getElementById('chat-messages').innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle fs-1"></i>
                <p>Erro ao carregar mensagens</p>
            </div>
        `;
    }
}

// Renderizar mensagens
function renderizarMensagens(mensagens) {
    const container = document.getElementById('chat-messages');
    
    if (!mensagens || mensagens.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-chat-dots fs-1"></i>
                <p>Nenhuma mensagem ainda. Inicie a conversa!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = mensagens.map(msg => {
        const isUsuario = msg.tipo_usuario === 'fornecedor';
        const dataFormatada = new Date(msg.data_envio).toLocaleString('pt-BR');
        
        return `
            <div class="message mb-3 ${isUsuario ? 'text-end' : 'text-start'}">
                <div class="d-inline-block p-3 rounded ${isUsuario ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                    <div class="message-content">${msg.mensagem}</div>
                    <small class="${isUsuario ? 'text-light' : 'text-muted'} d-block mt-1">
                        ${isUsuario ? 'Você' : 'Cliente'} • ${dataFormatada}
                    </small>
                </div>
            </div>
        `;
    }).join('');
    
    // Scroll para o final
    container.scrollTop = container.scrollHeight;
}

// Carregar apenas novas mensagens
async function carregarNovasMensagens() {
    if (!pedidoIdAtual) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoIdAtual
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagens(data.mensagens);
            marcarMensagensComoLidas();
        }
    } catch (error) {
        console.error('Erro ao carregar novas mensagens:', error);
    }
}

// Enviar mensagem
async function enviarMensagem() {
    const input = document.getElementById('nova-mensagem');
    const mensagem = input.value.trim();
    
    if (!mensagem || !pedidoIdAtual) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'enviar_mensagem',
                pedido_id: pedidoIdAtual,
                mensagem: mensagem
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            carregarNovasMensagens();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao enviar mensagem', 'error');
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        Swal.fire('Erro', 'Erro ao enviar mensagem. Tente novamente.', 'error');
    }
}

// Marcar mensagens como lidas
async function marcarMensagensComoLidas() {
    if (!pedidoIdAtual) return;
    
    try {
        await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'marcar_como_lida',
                pedido_id: pedidoIdAtual
            })
        });
    } catch (error) {
        console.error('Erro ao marcar mensagens como lidas:', error);
    }
}

// ===== FUNÇÕES DO CHAT MODAL =====
let chatModalInterval;
let pedidoIdChatModal;

// Abrir chat do pedido
function abrirChatPedido(pedidoId, numeroPedido) {
    pedidoIdChatModal = pedidoId;
    
    // Atualizar título do modal
    document.getElementById('chat-pedido-numero').textContent = numeroPedido;
    
    // Carregar chat automaticamente
    setTimeout(() => {
        carregarChatDetalhes(pedidoId);
    }, 500);
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPedido'));
    modal.show();
    
    // Carregar mensagens
    carregarChatModal();
}

// Carregar mensagens do chat modal
async function carregarChatModal() {
    if (!pedidoIdChatModal) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoIdChatModal
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagensModal(data.mensagens);
            marcarMensagensComoLidasModal();
            
            // Iniciar atualização automática
            if (chatModalInterval) clearInterval(chatModalInterval);
            chatModalInterval = setInterval(() => {
                carregarNovasMensagensModal();
            }, 3000);
        }
    } catch (error) {
        console.error('Erro ao carregar chat:', error);
        document.getElementById('chat-messages-modal').innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle fs-1"></i>
                <p>Erro ao carregar mensagens</p>
            </div>
        `;
    }
}

// Renderizar mensagens no modal
function renderizarMensagensModal(mensagens) {
    const container = document.getElementById('chat-messages-modal');
    
    if (!mensagens || mensagens.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-chat-dots fs-1"></i>
                <p>Nenhuma mensagem ainda. Inicie a conversa!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = mensagens.map(msg => {
        const isUsuario = msg.tipo_usuario === 'fornecedor';
        const dataFormatada = new Date(msg.data_envio).toLocaleString('pt-BR');
        
        return `
            <div class="message mb-3 ${isUsuario ? 'text-end' : 'text-start'}">
                <div class="d-inline-block p-3 rounded ${isUsuario ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                    <div class="message-content">${msg.mensagem}</div>
                    <small class="${isUsuario ? 'text-light' : 'text-muted'} d-block mt-1">
                        ${isUsuario ? 'Você' : 'Cliente'} • ${dataFormatada}
                    </small>
                </div>
            </div>
        `;
    }).join('');
    
    // Scroll para o final
    container.scrollTop = container.scrollHeight;
}

// Carregar apenas novas mensagens no modal
async function carregarNovasMensagensModal() {
    if (!pedidoIdChatModal) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoIdChatModal
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagensModal(data.mensagens);
            marcarMensagensComoLidasModal();
        }
    } catch (error) {
        console.error('Erro ao carregar novas mensagens:', error);
    }
}

// Enviar mensagem no modal
async function enviarMensagemModal() {
    const input = document.getElementById('nova-mensagem-modal');
    const mensagem = input.value.trim();
    
    if (!mensagem || !pedidoIdChatModal) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'enviar_mensagem',
                pedido_id: pedidoIdChatModal,
                mensagem: mensagem
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            carregarNovasMensagensModal();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao enviar mensagem', 'error');
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        Swal.fire('Erro', 'Erro ao enviar mensagem. Tente novamente.', 'error');
    }
}

// Marcar mensagens como lidas no modal
async function marcarMensagensComoLidasModal() {
    if (!pedidoIdChatModal) return;
    
    try {
        await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'marcar_como_lida',
                pedido_id: pedidoIdChatModal
            })
        });
    } catch (error) {
        console.error('Erro ao marcar mensagens como lidas:', error);
    }
}

// Carregar chat nos detalhes do pedido
async function carregarChatDetalhes(pedidoId) {
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'listar_mensagens',
                pedido_id: pedidoId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarMensagensDetalhes(pedidoId, data.mensagens);
        }
    } catch (error) {
        console.error('Erro ao carregar chat:', error);
        document.getElementById(`chat-conversa-${pedidoId}`).innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle fs-4"></i>
                <p class="mb-0">Erro ao carregar conversa</p>
            </div>
        `;
    }
}

// Renderizar mensagens nos detalhes
function renderizarMensagensDetalhes(pedidoId, mensagens) {
    const container = document.getElementById(`chat-conversa-${pedidoId}`);
    
    if (!mensagens || mensagens.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-chat-dots fs-4"></i>
                <p class="mb-0">Nenhuma mensagem ainda. Inicie a conversa!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = mensagens.map(msg => {
        const isUsuario = msg.tipo_usuario === 'fornecedor';
        const dataFormatada = new Date(msg.data_envio).toLocaleString('pt-BR');
        
        return `
            <div class="message ${isUsuario ? 'message-user' : 'message-other'} mb-2">
                <div class="message-content p-2 rounded" style="background-color: ${isUsuario ? '#007bff' : '#f8f9fa'}; color: ${isUsuario ? 'white' : 'black'}; max-width: 80%; margin-left: ${isUsuario ? 'auto' : '0'}; margin-right: ${isUsuario ? '0' : 'auto'};">
                    <div class="message-text">${msg.mensagem}</div>
                    <div class="message-time small" style="opacity: 0.7; margin-top: 4px;">
                        ${msg.nome_usuario} - ${dataFormatada}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Scroll para o final
    container.scrollTop = container.scrollHeight;
}

// Enviar mensagem nos detalhes
async function enviarMensagemDetalhes(pedidoId) {
    const input = document.getElementById(`nova-mensagem-${pedidoId}`);
    const mensagem = input.value.trim();
    
    if (!mensagem) return;
    
    try {
        const response = await fetch('backend/api/chat-pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'enviar_mensagem',
                pedido_id: pedidoId,
                mensagem: mensagem
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            carregarChatDetalhes(pedidoId); // Recarregar mensagens
        } else {
            Swal.fire('Erro', data.message || 'Erro ao enviar mensagem', 'error');
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        Swal.fire('Erro', 'Erro ao enviar mensagem. Tente novamente.', 'error');
    }
}

// Event listener para Enter no input de mensagem
document.addEventListener('DOMContentLoaded', function() {
    const inputMensagem = document.getElementById('nova-mensagem');
    if (inputMensagem) {
        inputMensagem.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                enviarMensagem();
            }
        });
    }
    
    // Event listener para Enter no input do modal
    const inputMensagemModal = document.getElementById('nova-mensagem-modal');
    if (inputMensagemModal) {
        inputMensagemModal.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                enviarMensagemModal();
            }
        });
    }
    
    // Event listeners para Enter nos inputs de mensagem dos detalhes
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.id && e.target.id.startsWith('nova-mensagem-')) {
            const pedidoId = e.target.id.replace('nova-mensagem-', '');
            enviarMensagemDetalhes(parseInt(pedidoId));
        }
    });
    
    // Observer para detectar novos inputs de mensagem criados dinamicamente
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const inputs = node.querySelectorAll ? node.querySelectorAll('input[id^="nova-mensagem-"]') : [];
                    inputs.forEach(function(input) {
                        input.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                const pedidoId = input.id.replace('nova-mensagem-', '');
                                enviarMensagemDetalhes(parseInt(pedidoId));
                            }
                        });
                    });
                }
            });
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Limpar interval quando modal for fechado
    const modal = document.getElementById('modalVisualizarPedido');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (chatInterval) {
                clearInterval(chatInterval);
                chatInterval = null;
            }
        });
    }
    
    // Limpar interval quando modal do chat for fechado
    const modalChat = document.getElementById('modalChatPedido');
    if (modalChat) {
        modalChat.addEventListener('hidden.bs.modal', function() {
            if (chatModalInterval) {
                clearInterval(chatModalInterval);
                chatModalInterval = null;
            }
            pedidoIdChatModal = null;
        });
    }

    const btnImportarCsvResposta = document.getElementById('btn-importar-csv-resposta-fornecedor');
    const inputCsvResposta = document.getElementById('input-csv-resposta-fornecedor');
    if (btnImportarCsvResposta && inputCsvResposta) {
        btnImportarCsvResposta.addEventListener('click', () => inputCsvResposta.click());
        inputCsvResposta.addEventListener('change', (event) => {
            const arquivo = event.target?.files?.[0];
            importarCsvRespostaFornecedor(arquivo);
            event.target.value = '';
        });
    }

    const btnExportarCsvResposta = document.getElementById('btn-exportar-csv-resposta-fornecedor');
    if (btnExportarCsvResposta) {
        btnExportarCsvResposta.addEventListener('click', exportarItensRespostaFornecedor);
    }
});

// ===== FUNÇÕES DE UPLOAD DE NOTA FISCAL =====
let pedidoIdNF = null;

// Abrir modal de upload de NF
function abrirModalUploadNF(pedidoId) {
    pedidoIdNF = pedidoId;
    
    // Limpar formulário
    document.getElementById('input-nota-fiscal').value = '';
    document.getElementById('nf-upload-progress').classList.add('d-none');
    document.getElementById('nf-upload-message').classList.add('d-none');
    document.getElementById('nf-uploaded-info').classList.add('d-none');
    
    // Verificar se já existe NF
    verificarNFExistente(pedidoId);
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalUploadNF'));
    modal.show();
}

// Verificar se já existe NF
async function verificarNFExistente(pedidoId) {
    try {
        const response = await fetch(`backend/api/pedidos-fornecedor.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_pedido',
                pedido_id: pedidoId
            })
        });
        
        const data = await response.json();
        if (data.success && data.pedido && data.pedido.url_nota_fiscal) {
            document.getElementById('nf-uploaded-info').classList.remove('d-none');
            document.getElementById('nf-uploaded-text').textContent = 'Nota Fiscal já enviada. Você pode enviar uma nova para substituir.';
        }
    } catch (error) {
        console.error('Erro ao verificar NF:', error);
    }
}

// Upload de Nota Fiscal
async function uploadNotaFiscal() {
    const fileInput = document.getElementById('input-nota-fiscal');
    const file = fileInput.files[0];
    
    if (!file) {
        Swal.fire('Atenção', 'Selecione um arquivo para enviar', 'warning');
        return;
    }
    
    // Validar tamanho (10MB)
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire('Erro', 'Arquivo muito grande. Tamanho máximo: 10MB', 'error');
        return;
    }
    
    // Validar tipo
    const allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowedTypes.includes(extension)) {
        Swal.fire('Erro', 'Tipo de arquivo não permitido. Apenas: PDF, JPG, PNG, GIF', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('nota_fiscal', file);
    formData.append('pedido_id', pedidoIdNF);
    
    const btnUpload = document.getElementById('btn-upload-nf');
    const progressBar = document.getElementById('nf-upload-progress');
    const progressBarInner = progressBar.querySelector('.progress-bar');
    const messageDiv = document.getElementById('nf-upload-message');
    
    // Desabilitar botão e mostrar progresso
    btnUpload.disabled = true;
    progressBar.classList.remove('d-none');
    messageDiv.classList.add('d-none');
    progressBarInner.style.width = '0%';
    
    try {
        const xhr = new XMLHttpRequest();
        
        // Progresso do upload
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                progressBarInner.style.width = percent + '%';
            }
        });
        
        // Resposta
        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    progressBarInner.style.width = '100%';
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = 'Nota Fiscal enviada com sucesso!';
                    messageDiv.classList.remove('d-none');
                    
                    document.getElementById('nf-uploaded-info').classList.remove('d-none');
                    document.getElementById('nf-uploaded-text').textContent = 'Nota Fiscal enviada com sucesso!';
                    
                    // Atualizar status na lista
                    setTimeout(() => {
                        carregarPedidos();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalUploadNF'));
                        modal.hide();
                    }, 1500);
                } else {
                    throw new Error(response.error || 'Erro ao enviar arquivo');
                }
            } else {
                throw new Error('Erro HTTP ' + xhr.status);
            }
        });
        
        xhr.addEventListener('error', () => {
            throw new Error('Erro de conexão');
        });
        
        xhr.open('POST', 'backend/api/upload-nota-fiscal.php');
        xhr.send(formData);
        
    } catch (error) {
        console.error('Erro ao fazer upload:', error);
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Erro ao enviar arquivo: ' + error.message;
        messageDiv.classList.remove('d-none');
        btnUpload.disabled = false;
    }
}

// Visualizar Nota Fiscal
async function visualizarNF() {
    if (!pedidoIdNF) return;
    
    try {
        const response = await fetch(`backend/api/get-nota-fiscal.php?pedido_id=${pedidoIdNF}`);
        const data = await response.json();
        
        if (data.success) {
            // A API já retorna a URL completa com o caminho base
            // Abrir diretamente em nova aba
            window.open(data.url, '_blank');
        } else {
            Swal.fire('Erro', data.error || 'Erro ao carregar Nota Fiscal', 'error');
        }
    } catch (error) {
        console.error('Erro ao visualizar NF:', error);
        Swal.fire('Erro', 'Erro ao carregar Nota Fiscal', 'error');
    }
}
</script>
</body>
</html>