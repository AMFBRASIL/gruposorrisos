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

// Bloquear se o fornecedor vinculado estiver inativo
try {
    require_once __DIR__ . '/config/conexao.php';
    $pdoAcesso = Conexao::getInstance()->getPdo();
    $stAtivo = $pdoAcesso->prepare('
        SELECT f.ativo
        FROM tbl_usuarios u
        INNER JOIN tbl_fornecedores f ON f.id_fornecedor = u.id_fornecedor
        WHERE u.id_usuario = ?
        LIMIT 1
    ');
    $stAtivo->execute([(int) ($_SESSION['usuario_id'] ?? 0)]);
    $rowAtivo = $stAtivo->fetch(PDO::FETCH_ASSOC);
    if (!$rowAtivo || (int) ($rowAtivo['ativo'] ?? 0) !== 1) {
        session_destroy();
        header('Location: login.php?erro=fornecedor_inativo');
        exit;
    }
} catch (Throwable $e) {
    // mantém fluxo se consulta falhar
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

$razaoSocialFornecedorSessao = '';
try {
    require_once __DIR__ . '/config/conexao.php';
    $pdoCtx = Conexao::getInstance()->getPdo();
    $stFn = $pdoCtx->prepare('SELECT COALESCE(NULLIF(TRIM(f.razao_social), \'\'), NULLIF(TRIM(f.nome_fantasia), \'\'), \'\') AS nome_fornecedor
        FROM tbl_usuarios u
        LEFT JOIN tbl_fornecedores f ON f.id_fornecedor = u.id_fornecedor
        WHERE u.id_usuario = ? LIMIT 1');
    $stFn->execute([(int) ($_SESSION['usuario_id'] ?? 0)]);
    $rowFn = $stFn->fetch(PDO::FETCH_ASSOC);
    if ($rowFn && !empty($rowFn['nome_fornecedor'])) {
        $razaoSocialFornecedorSessao = $rowFn['nome_fornecedor'];
    }
} catch (Throwable $e) {
    // mantém vazio
}

$menuActive = 'pedidos_fornecedores';

require_once __DIR__ . '/backend/helpers/pedido_compra_config.php';
$descontoFornecedorPercentual = obterDescontoFornecedorPercentual();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Pedidos para Fornecedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/index.css') ?: time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --brand-orange: #f57c00;
            --brand-red: #e53935;
            --brand-gray: #757575;
            --page-bg: #f5f7fa;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            background: var(--page-bg);
            min-height: 100vh;
        }
        
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        .page-header {
            background: linear-gradient(90deg, #ff9800 0%, #f57c00 55%, #ef6c00 100%);
            border-radius: 18px;
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 28px rgba(245, 124, 0, 0.28);
            border: none;
            color: #fff;
        }

        .page-header-inner {
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .page-header-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: none;
            border: 1px solid rgba(255,255,255,0.25);
        }

        .page-header-text {
            min-width: 0;
            flex: 1;
        }
        
        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.15rem;
            line-height: 1.3;
            letter-spacing: -0.02em;
        }
        
        .page-subtitle {
            font-size: 0.86rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
            line-height: 1.4;
        }

        @media (max-width: 575.98px) {
            .page-header {
                padding: 0.85rem 1rem;
            }
            .page-title {
                font-size: 1.1rem;
            }
            .page-header-icon {
                width: 38px;
                height: 38px;
                font-size: 1rem;
            }
        }
        
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 0.85rem 0.65rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            flex: 1 1 130px;
            min-width: 120px;
            max-width: 165px;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .stat-card.stat-card-ativo {
            border-color: #f57c00;
            box-shadow: 0 0 0 1px rgba(245, 124, 0, 0.25);
        }
        
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.1rem;
            color: white;
        }
        
        .stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            line-height: 1.1;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.72rem;
            line-height: 1.25;
        }

        .stats-section-label {
            color: #757575;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.75rem;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff9800 0%, #f57c00 55%, #ef6c00 100%) !important;
            border: none !important;
            color: #fff !important;
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(90deg, #fb8c00 0%, #ef6c00 55%, #e65100 100%) !important;
            color: #fff !important;
        }
        .btn-outline-primary {
            color: #f57c00 !important;
            border-color: #f57c00 !important;
            background: transparent !important;
        }
        .btn-outline-primary:hover {
            background: #f57c00 !important;
            color: #fff !important;
        }

        .stats-kpi-section {
            margin-bottom: 1.5rem;
        }

        .stats-status-section {
            margin-bottom: 2rem;
        }

        .stats-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
            .stats-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .stats-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .kpi-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            border: 2px solid #eef0f3;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .kpi-card.kpi-card-ativo {
            border-color: #f57c00;
            box-shadow: 0 0 0 1px rgba(245, 124, 0, 0.25);
        }

        .kpi-card.kpi-card-alerta {
            border-color: rgba(245, 158, 11, 0.35);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.65rem;
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: white;
            flex-shrink: 0;
        }

        .kpi-title {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.2;
        }

        .kpi-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.15;
            margin-bottom: 0.2rem;
        }

        .kpi-value.kpi-valor-destaque {
            font-size: 1.55rem;
            color: #f57c00;
        }

        .kpi-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            line-height: 1.3;
        }
        
        .pedidos-container {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            border: 1px solid #eef0f3;
        }
        
        .pedido-card {
            background: white;
            border-radius: 14px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: none;
            transition: all 0.3s ease;
            border: 1px solid #eef0f3;
            border-left: 4px solid #f57c00;
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
        
        .status-aguardando_cotacao, .status-em_analise, .status-pendente { background: #fff3e0; color: #ef6c00; }
        .status-em_cotacao, .status-aprovado_cotacao { background: #fff8e1; color: #f9a825; }
        .status-aguardando_aprovacao_socio { background: #fff3cd; color: #856404; }
        .status-aprovado_socio { background: #fff3e0; color: #f57c00; }
        .status-aguardando_faturamento, .status-enviar_para_faturamento { background: #f5f5f5; color: #757575; }
        .status-em_faturamento, .status-aprovado_para_faturar { background: #ffebee; color: #e53935; }
        .status-em_transito, .status-enviado { background: #fff3e0; color: #ef6c00; }
        .status-em_conferencia, .status-entregue, .status-parcialmente_recebido { background: #f5f5f5; color: #616161; }
        .status-finalizado, .status-recebido { background: #e8f5e9; color: #2e7d32; }
        .status-cancelado { background: #ffebee; color: #c62828; }
        .status-respondido { background: #fff3e0; color: #f57c00; }
        
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
        
        /* Modais fornecedor (faturamento / transporte) — largura extra em telas grandes */
        #modalAprovarFaturamento .modal-dialog,
        #modalTransporteFrete .modal-dialog {
            max-width: min(1320px, calc(100vw - 2rem));
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 575.98px) {
            #modalAprovarFaturamento .modal-dialog,
            #modalTransporteFrete .modal-dialog {
                max-width: calc(100vw - 1rem);
            }
        }

        /* Modais fornecedor — resumo e formulário */
        #modalAprovarFaturamento .aprovar-fat-resumo,
        #modalTransporteFrete .aprovar-fat-resumo {
            background: linear-gradient(135deg, #f8fafc 0%, #fff3e0 100%);
            border: 1px solid rgba(245, 124, 0, 0.25);
            border-radius: 14px;
            padding: 1rem 1.25rem;
        }
        #modalAprovarFaturamento .aprovar-fat-resumo dl,
        #modalTransporteFrete .aprovar-fat-resumo dl {
            margin-bottom: 0;
        }
        #modalAprovarFaturamento .aprovar-fat-resumo dt,
        #modalTransporteFrete .aprovar-fat-resumo dt {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.15rem;
        }
        #modalAprovarFaturamento .aprovar-fat-resumo dd,
        #modalTransporteFrete .aprovar-fat-resumo dd {
            font-size: 0.92rem;
            color: #1e293b;
            margin-bottom: 0.75rem;
            word-break: break-word;
        }
        #modalAprovarFaturamento .aprovar-fat-resumo dd:last-child,
        #modalTransporteFrete .aprovar-fat-resumo dd:last-child {
            margin-bottom: 0;
        }
        #modalAprovarFaturamento .aprovar-fat-resumo .aprovar-fat-icon,
        #modalTransporteFrete .aprovar-fat-resumo .aprovar-fat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f57c00 0%, #e53935 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        #modalAprovarFaturamento .aprovar-fat-form-card,
        #modalTransporteFrete .aprovar-fat-form-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 1rem 1.15rem;
        }
        #modalAprovarFaturamento .modal-header {
            background: linear-gradient(135deg, #f57c00 0%, #e53935 100%);
            color: #fff;
            border-bottom: none;
            border-radius: 0;
        }
        #modalTransporteFrete .modal-header {
            background: linear-gradient(135deg, #757575 0%, #f57c00 100%);
            color: #fff;
            border-bottom: none;
            border-radius: 0;
        }
        #modalAprovarFaturamento .modal-header .btn-close,
        #modalTransporteFrete .modal-header .btn-close {
            filter: invert(1);
        }
        #modalAprovarFaturamento .modal-content,
        #modalTransporteFrete .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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
            border-color: #f57c00;
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.1);
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
            border-top: 4px solid #f57c00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .resumo-final-box {
            background: linear-gradient(135deg, #f8fafc 0%, #fff3e0 100%);
            border: 2px solid #f57c00;
            border-radius: 14px;
            padding: 1rem 1.25rem;
        }

        .resumo-final-valor {
            font-size: 2rem;
            font-weight: 800;
            color: #ef6c00;
            line-height: 1.1;
        }

        #modalResponderPedido .modal-body {
            padding-top: 1rem;
        }

        #modalResponderPedido .item-row {
            padding: 0.65rem 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        #modalResponderPedido .item-row.item-novo-pos-resposta {
            border-color: #dc3545;
            background: #fff5f5;
            box-shadow: inset 0 0 0 1px rgba(220, 53, 69, 0.15);
        }
        #modalResponderPedido .badge-item-novo-pos-resposta {
            background: #dc3545;
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-left: 8px;
            display: inline-block;
            vertical-align: middle;
        }

        #modalResponderPedido .item-title {
            font-size: 0.95rem;
            line-height: 1.2;
        }

        #modalResponderPedido .item-meta {
            font-size: 0.76rem;
            line-height: 1.25;
        }

        #modalResponderPedido .form-label {
            margin-bottom: 0.2rem;
        }

        #modalResponderPedido .compact-field label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        #modalResponderPedido .compact-field .form-control,
        #modalResponderPedido .compact-field .form-select,
        #modalResponderPedido .price-input {
            min-height: 32px;
            padding: 0.25rem 0.45rem;
            font-size: 0.86rem;
            border-radius: 6px;
        }

        #modalResponderPedido .compact-help {
            font-size: 0.72rem;
            line-height: 1.15;
        }

        #modalResponderPedido .obs-solicitacao-box {
            background: #f8fafc;
            border-left: 3px solid #cbd5e1;
            padding: 0.35rem 0.5rem;
            border-radius: 6px;
        }

        #modalResponderPedido .obs-item-compact {
            min-height: 34px;
            resize: vertical;
            font-size: 0.84rem;
        }

        #modalResponderPedido .qtd-field {
            max-width: 105px;
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
        <div class="page-header-inner">
            <div class="page-header-icon" aria-hidden="true">
                <i class="bi bi-truck"></i>
            </div>
            <div class="page-header-text">
                <h1 class="page-title mb-0">Pedidos para Fornecedor</h1>
                <p class="page-subtitle mb-0">
                    <?php if ($razaoSocialFornecedorSessao !== ''): ?>
                        <?php echo htmlspecialchars($razaoSocialFornecedorSessao); ?> ·
                    <?php endif; ?>
                    Cotações, faturamento e acompanhamento dos pedidos
                </p>
            </div>
        </div>
    </div>

    <!-- Cards de ação -->
    <div class="stats-kpi-section">
        <div class="stats-kpi-grid" id="stats-resumo-kpi">
            <!-- Preenchido via JavaScript -->
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
                <div id="modal-pedido-content">
                    <!-- Conteúdo será carregado via JavaScript -->
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

<!-- Modal Aprovar Faturamento (fornecedor → Em trânsito + e-mail compras) -->
<div class="modal fade" id="modalAprovarFaturamento" tabindex="-1" aria-labelledby="modalAprovarFaturamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalAprovarFaturamentoLabel">
                    <i class="bi bi-check2-circle"></i>
                    <span>Aprovar Faturamento</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body bg-light-subtle">
                <div class="alert alert-primary border-0 d-flex gap-2 mb-3 py-2 px-3" role="alert" style="background: rgba(245, 124, 0, 0.12); color: #ef6c00;">
                    <i class="bi bi-info-circle flex-shrink-0 mt-1"></i>
                    <div class="small mb-0">
                        Ao confirmar, o pedido passa para <strong>Em Faturamento</strong>, as informações abaixo ficam registradas nas <strong>observações do fornecedor</strong> e no histórico. A Nota Fiscal neste passo é <strong>opcional</strong>.
                    </div>
                </div>

                <div class="aprovar-fat-resumo mb-3">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="aprovar-fat-icon d-none d-sm-flex">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <p class="small fw-semibold text-secondary mb-2 text-uppercase" style="letter-spacing: 0.06em;">Resumo do envio</p>
                            <dl class="row small g-2 mb-0">
                                <div class="col-sm-6">
                                    <dt>Pedido</dt>
                                    <dd id="aprovar-fat-pedido-numero" class="fw-semibold">—</dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Cliente (comprador)</dt>
                                    <dd id="aprovar-fat-pedido-cliente">—</dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Enviado por</dt>
                                    <dd>
                                        <span id="aprovar-fat-usuario-nome"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($_SESSION['usuario_email'])): ?>
                                            <br><span class="text-muted" id="aprovar-fat-usuario-email"><?php echo htmlspecialchars($_SESSION['usuario_email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php else: ?>
                                            <span id="aprovar-fat-usuario-email" class="d-none"></span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Fornecedor (empresa)</dt>
                                    <dd id="aprovar-fat-fornecedor-razao"><?php echo htmlspecialchars($razaoSocialFornecedorSessao !== '' ? $razaoSocialFornecedorSessao : '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                                <div class="col-12">
                                    <dt>Data e hora deste envio</dt>
                                    <dd class="mb-0">
                                        <span id="aprovar-fat-data-hora" class="fw-medium text-primary">—</span>
                                        <span class="text-muted small d-block mt-1">Referência do momento em que você abriu esta janela; o servidor registra o horário oficial ao confirmar.</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="aprovar-fat-form-card mb-3">
                    <label class="form-label fw-semibold" for="detalhes-aprovacao-faturamento">
                        Detalhes da aprovação <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="detalhes-aprovacao-faturamento" rows="5" maxlength="4000"
                              placeholder="Ex.: prazo de emissão da NF, transportadora, número do pedido interno, volumes, observações fiscais ou comerciais acordadas..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Máximo 4.000 caracteres. Este texto é salvo em <strong>observações do fornecedor</strong> e no <strong>histórico do pedido</strong>.</small>
                        <small class="text-muted"><span id="aprovar-fat-char-count">0</span>/4000</small>
                    </div>
                </div>

                <div class="aprovar-fat-form-card mb-0">
                    <label class="form-label fw-semibold d-flex align-items-center gap-2" for="input-nota-fiscal-aprovacao">
                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                        Nota Fiscal <span class="badge bg-secondary-subtle text-secondary-emphasis border fw-normal">opcional</span>
                    </label>
                    <input type="file" class="form-control" id="input-nota-fiscal-aprovacao" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <small class="text-muted">PDF, JPG, PNG ou GIF — até 10 MB. Se anexar, a NF ficará vinculada ao pedido com data de envio e responsável.</small>
                    <div id="aprovar-fat-nf-preview" class="small text-success mt-2 d-none"></div>
                </div>

                <div id="aprovar-fat-progress" class="progress d-none mt-3 mb-0">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="aprovar-fat-message" class="alert d-none mt-3 mb-0"></div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success px-4" id="btn-confirmar-aprovacao-faturamento" onclick="confirmarAprovacaoFaturamento()">
                    <i class="bi bi-check-lg me-1"></i>Confirmar e enviar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Transporte / Frete (fornecedor → Em trânsito) -->
<div class="modal fade" id="modalTransporteFrete" tabindex="-1" aria-labelledby="modalTransporteFreteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalTransporteFreteLabel">
                    <i class="bi bi-truck"></i>
                    <span>Transporte / Frete</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body bg-light-subtle">
                <div class="alert alert-info border-0 d-flex gap-2 mb-3 py-2 px-3" role="alert" style="background: rgba(245, 124, 0, 0.12); color: #ef6c00;">
                    <i class="bi bi-info-circle flex-shrink-0 mt-1"></i>
                    <div class="small mb-0">
                        Informe os dados do envio (transportadora, rastreio, previsão de entrega, volumes, etc.). Ao confirmar, o pedido passa para <strong>Em trânsito</strong> e o texto é salvo nas <strong>observações do fornecedor</strong> e no histórico.
                    </div>
                </div>

                <div class="aprovar-fat-resumo mb-3">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="aprovar-fat-icon d-none d-sm-flex">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <p class="small fw-semibold text-secondary mb-2 text-uppercase" style="letter-spacing: 0.06em;">Resumo do envio</p>
                            <dl class="row small g-2 mb-0">
                                <div class="col-sm-6">
                                    <dt>Pedido</dt>
                                    <dd id="transp-fat-pedido-numero" class="fw-semibold">—</dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Cliente (comprador)</dt>
                                    <dd id="transp-fat-pedido-cliente">—</dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Informado por</dt>
                                    <dd>
                                        <span id="transp-fat-usuario-nome"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($_SESSION['usuario_email'])): ?>
                                            <br><span class="text-muted" id="transp-fat-usuario-email"><?php echo htmlspecialchars($_SESSION['usuario_email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php else: ?>
                                            <span id="transp-fat-usuario-email" class="d-none"></span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="col-sm-6">
                                    <dt>Fornecedor (empresa)</dt>
                                    <dd id="transp-fat-fornecedor-razao"><?php echo htmlspecialchars($razaoSocialFornecedorSessao !== '' ? $razaoSocialFornecedorSessao : '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                                <div class="col-12">
                                    <dt>Data e hora desta informação</dt>
                                    <dd class="mb-0">
                                        <span id="transp-fat-data-hora" class="fw-medium text-primary">—</span>
                                        <span class="text-muted small d-block mt-1">Referência do momento em que você abriu esta janela; o servidor registra o horário oficial ao confirmar.</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="aprovar-fat-form-card mb-0">
                    <label class="form-label fw-semibold" for="observacao-transporte-frete">
                        Observações sobre transporte / frete <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="observacao-transporte-frete" rows="5" maxlength="4000"
                              placeholder="Ex.: transportadora, código de rastreio, data prevista de entrega, tipo de frete, volumes, contato do motorista..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Máximo 4.000 caracteres. Este texto é salvo em <strong>observações do fornecedor</strong> e no <strong>histórico do pedido</strong>.</small>
                        <small class="text-muted"><span id="transp-fat-char-count">0</span>/4000</small>
                    </div>
                </div>

                <div id="transp-fat-message" class="alert d-none mt-3 mb-0"></div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="btn-confirmar-transporte-frete" onclick="confirmarTransporteFrete()">
                    <i class="bi bi-truck me-1"></i>Confirmar e enviar
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
                <div class="alert alert-danger py-2 px-3 d-none mb-3" id="alerta-itens-pendentes-resposta-fornecedor">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span id="alerta-itens-pendentes-resposta-fornecedor-texto"></span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observações:</label>
                    <textarea class="form-control form-control-sm" id="observacoes-fornecedor" rows="2" 
                              placeholder="Adicione observações sobre preços, prazos ou condições..."></textarea>
                </div>
                
                <h6 class="mb-3">Itens do Pedido</h6>
                <div class="mb-3">
                    <label class="form-label">Pesquisar Itens</label>
                    <input type="text" class="form-control" id="filtro-itens-resposta"
                           placeholder="Digite nome ou código do item..."
                           oninput="filtrarItensRespostaFornecedor()">
                </div>
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
                <div class="alert alert-info py-2 px-3 d-none mb-2" id="csv-validacao-local-fornecedor-box">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-calculator me-1"></i>Validação local da importação (CSV x Sistema)
                    </div>
                    <div class="small">
                        <div><strong>Total linhas CSV (coluna Total):</strong> <span id="csv-validacao-total-coluna">R$ 0,00</span></div>
                        <div><strong>Total linhas CSV (Qtd x Unit.):</strong> <span id="csv-validacao-total-calculado">R$ 0,00</span></div>
                        <div><strong>Total aplicado no sistema:</strong> <span id="csv-validacao-total-sistema">R$ 0,00</span></div>
                        <div><strong>Desconto aplicado:</strong> <span id="csv-validacao-desconto">R$ 0,00</span></div>
                        <div><strong>Valor final (modal):</strong> <span id="csv-validacao-valor-final">R$ 0,00</span></div>
                        <div><strong>Diferença (CSV col. Total - Sistema):</strong> <span id="csv-validacao-diferenca">R$ 0,00</span></div>
                        <div><strong>Itens ajustados no sistema:</strong> <span id="csv-validacao-itens-ajustados">0</span></div>
                    </div>
                </div>
                <div class="itens-scroll-container">
                    <div id="itens-resposta">
                        <!-- Itens serão carregados aqui -->
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <strong>Prazo de Entrega:</strong>
                        <span class="text-muted small">(opcional)</span>
                        <input type="date" class="form-control mt-2" id="prazo-entrega">
                    </div>
                    <div class="col-md-6">
                        <strong>Condições de Pagamento:</strong>
                        <span class="text-muted small">(opcional)</span>
                        <select class="form-select mt-2" id="condicoes-pagamento">
                            <option value="">Não informado</option>
                            <option value="30_dias">30 dias</option>
                            <option value="45_dias">45 dias</option>
                            <option value="60_dias">60 dias</option>
                            <option value="a_vista">À vista</option>
                        </select>
                    </div>
                </div>

                <div class="resumo-final-box mt-4">
                    <div class="alert alert-light border small py-2 mb-3" id="alerta-desconto-padrao-resposta">
                        <i class="bi bi-percent me-1"></i>
                        Desconto padrão de <strong id="texto-desconto-padrao-percentual"><?php echo htmlspecialchars((string) $descontoFornecedorPercentual); ?>%</strong> aplicado automaticamente em cada item sobre o preço informado.
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1"><strong>Subtotal (bruto)</strong></label>
                            <input type="text" class="form-control" id="subtotal-bruto-resposta" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1"><strong id="label-desconto-itens-resposta">Desconto itens (<?php echo htmlspecialchars((string) $descontoFornecedorPercentual); ?>%)</strong></label>
                            <input type="text" class="form-control" id="desconto-itens-resposta" readonly>
                        </div>
                        <div class="col-md-3">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variáveis globais
let pedidosData = [];
let pedidoAtual = null;

const MAPA_STATUS_FORNECEDOR = {
    em_analise: 'aguardando_cotacao',
    pendente: 'aguardando_cotacao',
    aguardando_aprovacao: 'aguardando_cotacao',
    aprovado_cotacao: 'em_cotacao',
    enviar_para_faturamento: 'aguardando_faturamento',
    enviar_faturamento: 'aguardando_faturamento',
    aprovado_para_faturar: 'em_faturamento',
    faturado: 'em_faturamento',
    enviado: 'em_transito',
    entregue: 'em_conferencia',
    recebido: 'finalizado',
    parcialmente_recebido: 'em_conferencia'
};

function normalizarStatusFornecedor(status) {
    const s = (status || '').toLowerCase();
    return MAPA_STATUS_FORNECEDOR[s] || s;
}

const LABELS_STATUS_FORNECEDOR = {
    aguardando_cotacao: 'Aguardando Cotação',
    em_cotacao: 'Em Cotação',
    aguardando_aprovacao_socio: 'Aguard. Aprovação Sócio',
    aprovado_socio: 'Aprovado pelo Sócio',
    aguardando_faturamento: 'Aguard. Faturamento',
    em_faturamento: 'Em Faturamento',
    em_transito: 'Em Trânsito',
    em_conferencia: 'Em Conferência',
    finalizado: 'Finalizado',
    cancelado: 'Cancelado'
};

/** Desconto comercial padrão — valor vindo de Configurações > Pedidos */
const DESCONTO_PADRAO_ITEM_PERCENTUAL = <?php echo json_encode((float) $descontoFornecedorPercentual); ?>;

const STATUS_FORNECEDOR_ORDEM = [
    'aguardando_cotacao',
    'em_cotacao',
    'aguardando_aprovacao_socio',
    'aprovado_socio',
    'aguardando_faturamento',
    'em_faturamento',
    'em_transito',
    'em_conferencia',
    'finalizado',
    'cancelado'
];

const ICONES_STATUS_FORNECEDOR = {
    aguardando_cotacao: 'bi-inbox',
    em_cotacao: 'bi-pencil-square',
    aguardando_aprovacao_socio: 'bi-person-check',
    aprovado_socio: 'bi-check-circle',
    aguardando_faturamento: 'bi-hourglass-split',
    em_faturamento: 'bi-receipt',
    em_transito: 'bi-truck',
    em_conferencia: 'bi-clipboard-check',
    finalizado: 'bi-flag-fill',
    cancelado: 'bi-x-circle'
};

const CORES_STATUS_FORNECEDOR = {
    aguardando_cotacao: 'linear-gradient(135deg, #ffb74d, #f57c00)',
    em_cotacao: 'linear-gradient(135deg, #ff9800, #ef6c00)',
    aguardando_aprovacao_socio: 'linear-gradient(135deg, #ffcc80, #f57c00)',
    aprovado_socio: 'linear-gradient(135deg, #ff9800, #e65100)',
    aguardando_faturamento: 'linear-gradient(135deg, #9e9e9e, #757575)',
    em_faturamento: 'linear-gradient(135deg, #ef5350, #e53935)',
    em_transito: 'linear-gradient(135deg, #ff9800, #f57c00)',
    em_conferencia: 'linear-gradient(135deg, #757575, #616161)',
    finalizado: 'linear-gradient(135deg, #81c784, #43a047)',
    cancelado: 'linear-gradient(135deg, #e57373, #c62828)'
};

let filtroGrupoCardAtivo = '';

const GRUPOS_CARD_FORNECEDOR = {
    a_responder: ['aguardando_cotacao', 'em_cotacao'],
    a_faturar: ['aguardando_faturamento', 'em_faturamento'],
    em_transporte: ['em_transito']
};

function popularFiltroStatusFornecedor() {
    const sel = document.getElementById('filtro-status');
    if (!sel) return;
    const atual = sel.value;
    sel.innerHTML = '<option value="">Todos os Status</option>';
    STATUS_FORNECEDOR_ORDEM.forEach((key) => {
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = LABELS_STATUS_FORNECEDOR[key] || key;
        sel.appendChild(opt);
    });
    if (atual && [...sel.options].some(o => o.value === atual)) {
        sel.value = atual;
    }
}

function filtrarPorCardGrupo(grupo) {
    if (grupo === 'todos') {
        filtroGrupoCardAtivo = '';
    } else {
        filtroGrupoCardAtivo = (filtroGrupoCardAtivo === grupo) ? '' : grupo;
    }
    const sel = document.getElementById('filtro-status');
    if (sel) sel.value = '';
    renderizarCardsResumoKpiFornecedor();
    aplicarFiltros();
}

function labelStatusFornecedor(status) {
    const norm = normalizarStatusFornecedor(status);
    return LABELS_STATUS_FORNECEDOR[norm] || status;
}

function pedidoFornecedorPodeResponder(status) {
    return ['aguardando_cotacao', 'em_cotacao'].includes(normalizarStatusFornecedor(status));
}

function pedidoFornecedorPodeFaturar(status) {
    return ['em_faturamento', 'aguardando_faturamento'].includes(normalizarStatusFornecedor(status));
}

function pedidoFornecedorPodeInformarTransporte(status) {
    return normalizarStatusFornecedor(status) === 'em_faturamento';
}

/** Item que ainda exige preenchimento pelo fornecedor (lista / cartão). */
function itemSemRespostaFornecedor(item) {
    if (parseInt(item?.novo_pos_resposta || 0, 10) === 1) {
        return true;
    }
    const disp = item?.disponivel;
    const dispDefined = disp !== null && disp !== undefined && disp !== '';
    const precoRaw = item?.preco_fornecedor;
    const precoDefined = precoRaw !== null && precoRaw !== undefined && precoRaw !== '';

    if (dispDefined) {
        const d = parseInt(disp, 10);
        if (d === 0) {
            return false;
        }
        if (d === 1) {
            return !precoDefined || Number.isNaN(parseFloat(precoRaw));
        }
        return true;
    }

    if (precoDefined) {
        return false;
    }
    return true;
}

function contarItensSemRespostaFornecedor(pedido) {
    const itens = pedido?.itens || [];
    return itens.filter(itemSemRespostaFornecedor).length;
}

function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
}

function extrairNomeArquivoDaUrlNfFornecedor(url) {
    if (!url || typeof url !== 'string') return '';
    try {
        const semQuery = url.split('?')[0];
        const partes = semQuery.replace(/\\/g, '/').split('/');
        return decodeURIComponent(partes.pop() || '');
    } catch (e) {
        return '';
    }
}

function formatarTamanhoBytesNfFornecedor(bytes) {
    const n = Number(bytes);
    if (!Number.isFinite(n) || n < 0) return '—';
    if (n === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let v = n;
    while (v >= 1024 && i < units.length - 1) {
        v /= 1024;
        i++;
    }
    const decimals = v >= 10 || i === 0 ? 0 : 1;
    return `${v.toFixed(decimals)} ${units[i]}`;
}

function formatarDataHoraEnvioNfFornecedor(val) {
    if (!val) return '';
    const d = new Date(val);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/** Card de NF no detalhe do pedido: metadados do último envio + envio/substituição quando permitido */
function montarHtmlSecaoNotaFiscalDetalhesFornecedor(pedido) {
    const podeEnviar = ['em_transito', 'entregue'].includes(pedido.status);
    const urlNF = pedido.url_nota_fiscal;
    const temNf = urlNF != null && String(urlNF).trim() !== '';
    if (!podeEnviar && !temNf) return '';

    let corpoMeta = '';
    if (temNf) {
        const nomeArq = String(pedido.nf_nome_arquivo_original || '').trim()
            || extrairNomeArquivoDaUrlNfFornecedor(String(urlNF));
        const dataFmt = formatarDataHoraEnvioNfFornecedor(pedido.nf_data_envio);
        const por = String(pedido.nf_usuario_nome || '').trim();
        const tamFmt = formatarTamanhoBytesNfFornecedor(pedido.nf_tamanho_bytes);
        corpoMeta = `
                        <p class="text-muted small mb-2">Registro do último envio:</p>
                        <dl class="row small mb-3 g-2">
                            <dt class="col-sm-4 text-muted">Enviado em</dt>
                            <dd class="col-sm-8 mb-1">${escapeHtml(dataFmt || '—')}</dd>
                            <dt class="col-sm-4 text-muted">Enviado por</dt>
                            <dd class="col-sm-8 mb-1">${escapeHtml(por || '—')}</dd>
                            <dt class="col-sm-4 text-muted">Nome do arquivo</dt>
                            <dd class="col-sm-8 mb-1 text-break">${escapeHtml(nomeArq || '—')}</dd>
                            <dt class="col-sm-4 text-muted">Tamanho</dt>
                            <dd class="col-sm-8 mb-0">${escapeHtml(tamFmt)}</dd>
                        </dl>
                        <button type="button" class="btn btn-outline-primary btn-sm me-2 mb-2" onclick="visualizarNFDoPedido(${pedido.id})">
                            <i class="bi bi-eye me-1"></i>Visualizar NF
                        </button>`;
    } else {
        corpoMeta = '<p class="text-muted small mb-2">Nenhuma nota fiscal foi enviada ainda.</p>';
    }

    const botoesUpload = podeEnviar
        ? `<button type="button" class="btn btn-primary btn-sm" onclick="abrirModalUploadNF(${pedido.id})">
                            <i class="bi bi-upload me-2"></i>${temNf ? 'Substituir Nota Fiscal' : 'Enviar Nota Fiscal'}
                        </button>`
        : '';

    return `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Nota Fiscal</h6>
                    </div>
                    <div class="card-body">
                        ${corpoMeta}
                        ${botoesUpload}
                    </div>
                </div>
            </div>
        </div>`;
}

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

function calcularMetricasResumoFornecedor() {
    let aResponder = 0;
    let aFaturar = 0;
    let emTransporte = 0;

    pedidosData.forEach((pedido) => {
        const status = normalizarStatusFornecedor(pedido.status);
        if (GRUPOS_CARD_FORNECEDOR.a_responder.includes(status)) aResponder++;
        if (GRUPOS_CARD_FORNECEDOR.a_faturar.includes(status)) aFaturar++;
        if (GRUPOS_CARD_FORNECEDOR.em_transporte.includes(status)) emTransporte++;
    });

    return { total: pedidosData.length, aResponder, aFaturar, emTransporte };
}

function renderizarCardsResumoKpiFornecedor() {
    const container = document.getElementById('stats-resumo-kpi');
    if (!container) return;

    const m = calcularMetricasResumoFornecedor();

    const cards = [
        {
            grupo: 'todos',
            icon: 'bi-collection',
            cor: 'linear-gradient(135deg, #9e9e9e, #757575)',
            titulo: 'Todos',
            valor: String(m.total),
            sub: 'Ver todos os pedidos'
        },
        {
            grupo: 'a_responder',
            icon: 'bi-pencil-square',
            cor: 'linear-gradient(135deg, #ff9800, #f57c00)',
            titulo: 'A responder',
            valor: String(m.aResponder),
            sub: 'Aguardando cotação ou em cotação'
        },
        {
            grupo: 'a_faturar',
            icon: 'bi-receipt',
            cor: 'linear-gradient(135deg, #ef5350, #e53935)',
            titulo: 'A faturar',
            valor: String(m.aFaturar),
            sub: 'Aguardando ou em faturamento'
        },
        {
            grupo: 'em_transporte',
            icon: 'bi-truck',
            cor: 'linear-gradient(135deg, #ff9800, #ef6c00)',
            titulo: 'Em transporte',
            valor: String(m.emTransporte),
            sub: 'Pedidos a caminho'
        }
    ];

    container.innerHTML = cards.map((card) => {
        const ativo = (card.grupo === 'todos' && filtroGrupoCardAtivo === '') || filtroGrupoCardAtivo === card.grupo
            ? ' kpi-card-ativo'
            : '';
        const alerta = card.grupo === 'a_responder' && m.aResponder > 0 ? ' kpi-card-alerta' : '';

        return `
            <div class="kpi-card${ativo}${alerta}" onclick="filtrarPorCardGrupo('${card.grupo}')" title="Clique para filtrar">
                <div class="kpi-header">
                    <div class="kpi-icon" style="background: ${card.cor};">
                        <i class="bi ${card.icon}"></i>
                    </div>
                    <div class="kpi-title">${card.titulo}</div>
                </div>
                <div class="kpi-value">${card.valor}</div>
                <div class="kpi-sub">${card.sub}</div>
            </div>
        `;
    }).join('');
}

// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    popularFiltroStatusFornecedor();
    carregarPedidos();
    configurarFiltros();
});

// Configurar filtros
function configurarFiltros() {
    document.getElementById('filtro-busca').addEventListener('input', aplicarFiltros);
    document.getElementById('filtro-status').addEventListener('change', function () {
        filtroGrupoCardAtivo = '';
        renderizarCardsResumoKpiFornecedor();
        aplicarFiltros();
    });
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


// Atualizar estatísticas (cards de ação)
function atualizarEstatisticas() {
    renderizarCardsResumoKpiFornecedor();
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
        const qtdSemResposta = contarItensSemRespostaFornecedor(pedido);
        const alertaItensSemResposta = (qtdSemResposta > 0 && pedidoFornecedorPodeResponder(pedido.status))
            ? `
            <div class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-start gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                <div class="small">
                    Este pedido tem <strong>${qtdSemResposta}</strong> item(ns) sem sua resposta.
                    Use <strong>Responder</strong> para informar preço e disponibilidade.
                </div>
            </div>`
            : '';
        return `
        <div class="pedido-card">
            <div class="pedido-header">
                <div class="pedido-numero">${pedido.numero}</div>
                <span class="pedido-status status-${normalizarStatusFornecedor(pedido.status)}">${labelStatusFornecedor(pedido.status)}</span>
            </div>
            ${alertaItensSemResposta}
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
            ${pedido.observacoes_fornecedor ? `
            <div class="mb-3">
                <span class="info-label">Sua Observação (Fornecedor):</span>
                <span class="info-value">${pedido.observacoes_fornecedor}</span>
            </div>
            ` : ''}
            
            <div class="pedido-actions">
                <button class="btn btn-outline-primary btn-action" onclick="visualizarPedido(${pedido.id})">
                    <i class="bi bi-eye me-2"></i>Visualizar
                </button>
                ${pedidoFornecedorPodeResponder(pedido.status) ? `
                    <button class="btn btn-success btn-action" onclick="responderPedido(${pedido.id})">
                        <i class="bi bi-reply me-2"></i>Responder
                    </button>
                ` : ''}
                ${pedidoFornecedorPodeFaturar(pedido.status) ? `
                    <button class="btn btn-success btn-action" onclick="abrirModalAprovarFaturamento(${pedido.id})">
                        <i class="bi bi-check2-circle me-2"></i>Aprovar Faturamento / NF
                    </button>
                ` : ''}
                ${pedidoFornecedorPodeFaturar(pedido.status) ? `
                    <button class="btn btn-outline-info btn-action" onclick="abrirModalUploadNF(${pedido.id})">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Enviar NF
                    </button>
                ` : ''}
                ${pedidoFornecedorPodeInformarTransporte(pedido.status) ? `
                    <button class="btn btn-primary btn-action" onclick="abrirModalTransporteFrete(${pedido.id})">
                        <i class="bi bi-truck me-2"></i>Transporte / Frete
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
    
    if (filtroGrupoCardAtivo && GRUPOS_CARD_FORNECEDOR[filtroGrupoCardAtivo]) {
        const statusesGrupo = GRUPOS_CARD_FORNECEDOR[filtroGrupoCardAtivo];
        filtrados = filtrados.filter(p => statusesGrupo.includes(normalizarStatusFornecedor(p.status)));
    } else if (status) {
        filtrados = filtrados.filter(p => normalizarStatusFornecedor(p.status) === status || p.status === status);
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
    const qtdSemRespostaDetalhes = contarItensSemRespostaFornecedor(pedido);
    const alertaItensSemRespostaDetalhes = (qtdSemRespostaDetalhes > 0 && pedidoFornecedorPodeResponder(pedido.status))
        ? `
        <div class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
            <div class="small mb-0">
                <strong>${qtdSemRespostaDetalhes}</strong> item(ns) sem sua resposta. Clique em <strong>Responder com Preços</strong> para concluir.
            </div>
        </div>`
        : '';

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
                <span class="pedido-status status-${normalizarStatusFornecedor(pedido.status)}">${labelStatusFornecedor(pedido.status)}</span>
            </div>
            <div class="col-md-6">
                <strong>Prioridade:</strong>
                <span class="badge ${getPrioridadeClass(pedido.prioridade)}">
                    ${getPrioridadeText(pedido.prioridade)}
                </span>
            </div>
        </div>

        ${alertaItensSemRespostaDetalhes}

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
        ${pedido.observacoes_fornecedor ? `
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success-subtle">
                        <h6 class="mb-0"><i class="bi bi-chat-right-text me-2"></i>Sua Observação (Fornecedor)</h6>
                    </div>
                    <div class="card-body">
                        ${pedido.observacoes_fornecedor}
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        
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
        
        ${pedidoFornecedorPodeFaturar(pedido.status) ? `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success-subtle">
                        <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Aprovar Faturamento</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">Após aprovação do sócio, confirme o faturamento e anexe a NF. O pedido seguirá em <strong>Em Faturamento</strong> até a clínica confirmar o recebimento.</p>
                        <button class="btn btn-success btn-sm" onclick="abrirModalAprovarFaturamento(${pedido.id})">
                            <i class="bi bi-check2-circle me-2"></i>Aprovar Faturamento
                        </button>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        ${pedidoFornecedorPodeInformarTransporte(pedido.status) ? `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary-subtle">
                        <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Transporte / Frete</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">Informe transportadora, rastreio e demais dados do envio. O pedido passará para <strong>Em trânsito</strong>.</p>
                        <button class="btn btn-primary btn-sm" onclick="abrirModalTransporteFrete(${pedido.id})">
                            <i class="bi bi-truck me-2"></i>Informar transporte / frete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        ${montarHtmlSecaoNotaFiscalDetalhesFornecedor(pedido)}
    `;
    
    // Mostrar/ocultar botão "Responder com Preços" baseado no status
    setTimeout(() => {
        const btnResponderModal = document.getElementById('btn-responder-modal');
        if (btnResponderModal) {
            if (pedidoFornecedorPodeResponder(pedido.status)) {
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

    if (!pedidoFornecedorPodeResponder(pedido.status)) {
        Swal.fire('Acompanhamento', 'Este pedido já foi enviado para faturamento e não pode mais ser editado.', 'info');
        return;
    }
    
    pedidoAtual = pedido;
    
    // Fechar modal de visualização se estiver aberto
    const modalVisualizar = bootstrap.Modal.getInstance(document.getElementById('modalVisualizarPedido'));
    if (modalVisualizar) {
        modalVisualizar.hide();
    }
    
    // Preencher dados do modal de resposta
    document.getElementById('pedido-numero-resposta').textContent = pedido.numero;
    document.getElementById('pedido-data-resposta').textContent = formatarData(pedido.data);
    document.getElementById('observacoes-fornecedor').value = pedido.observacoes_fornecedor || '';
    document.getElementById('desconto-final-tipo').value = '';
    document.getElementById('desconto-final-valor').value = '';
    document.getElementById('desconto-final-valor').placeholder = '0,00';
    document.getElementById('subtotal-itens-resposta').value = 'R$ 0,00';
    const subBrutoEl = document.getElementById('subtotal-bruto-resposta');
    const descItensEl = document.getElementById('desconto-itens-resposta');
    if (subBrutoEl) subBrutoEl.value = 'R$ 0,00';
    if (descItensEl) descItensEl.value = 'R$ 0,00';
    document.getElementById('valor-final-resposta').textContent = 'R$ 0,00';
    document.getElementById('filtro-itens-resposta').value = '';
    atualizarBadgeCsvFornecedor(0);
    renderizarItensNaoEncontradosCsvFornecedor([]);
    limparValidacaoCsvLocalFornecedor();
    const alertaPendencias = document.getElementById('alerta-itens-pendentes-resposta-fornecedor');
    const alertaPendenciasTexto = document.getElementById('alerta-itens-pendentes-resposta-fornecedor-texto');
    if (alertaPendencias) alertaPendencias.classList.add('d-none');
    if (alertaPendenciasTexto) alertaPendenciasTexto.textContent = '';
    const inputCsvResposta = document.getElementById('input-csv-resposta-fornecedor');
    if (inputCsvResposta) {
        inputCsvResposta.value = '';
    }
    
    // Renderizar itens para resposta
    const itensContainer = document.getElementById('itens-resposta');
    itensContainer.innerHTML = pedido.itens.map((item, index) => `
        <div class="item-row ${item.novo_pos_resposta == 1 ? 'item-novo-pos-resposta' : ''}" data-item-nome="${(item.nome || '').toLowerCase()}" data-item-codigo="${(item.codigo || '').toString().toLowerCase()}">
            <div class="row g-2 align-items-start">
                <div class="col-lg-5">
                    <div class="item-title fw-semibold">
                        ${item.nome}
                        ${item.novo_pos_resposta == 1 ? '<span class="badge-item-novo-pos-resposta">Novo item - responder</span>' : ''}
                    </div>
                    <div class="text-muted item-meta mt-1">
                        <strong>Cód.:</strong> ${item.codigo || 'N/A'} |
                        <strong>Categoria:</strong> ${item.categoria || 'N/A'} |
                        <strong>Preço atual:</strong> R$ ${item.preco_unitario.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                    <div class="item-meta mt-1">
                        <span class="text-muted">Solicitado:</span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">${item.quantidade} ${item.unidade}</span>
                        <span class="text-info ms-2" id="info-quantidade-${index}"></span>
                    </div>
                    <div id="obs-solicitacao-wrap-${index}" class="obs-solicitacao-box mt-2" style="display:none">
                        <div class="item-meta text-muted fw-semibold">Obs. solicitação</div>
                        <div id="obs-solicitacao-${index}" class="item-meta text-body-secondary"></div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-2 compact-field qtd-field">
                            <label class="form-label" for="quantidade-${index}">Qtd. Disp.</label>
                            <input type="number" class="form-control form-control-sm price-input" 
                                   id="quantidade-${index}" step="0.01" min="0" 
                                   value="${item.quantidade}" 
                                   placeholder="0,00" 
                                   onchange="calcularTotalItem(${index})"
                                   oninput="validarQuantidade(${index}, ${item.quantidade})">
                            <div class="text-muted compact-help">Máx: ${item.quantidade} ${item.unidade}</div>
                        </div>
                        <div class="col-6 col-md-2 compact-field">
                            <label class="form-label" for="preco-${index}">Preço (bruto)</label>
                            <input type="text" class="form-control form-control-sm price-input" 
                                   id="preco-${index}" 
                                   placeholder="R$ 0,00" 
                                   oninput="aplicarMascaraMoeda(this, ${index})"
                                   onblur="calcularTotalItem(${index})"
                                   onfocus="this.select()">
                            <div class="text-muted compact-help" id="hint-desconto-item-percentual">−<?php echo htmlspecialchars((string) $descontoFornecedorPercentual); ?>% no total</div>
                        </div>
                        <div class="col-6 col-md-2 compact-field">
                            <label class="form-label" for="total-${index}">Total c/ desc.</label>
                            <input type="text" class="form-control form-control-sm" id="total-${index}" readonly>
                        </div>
                        <div class="col-6 col-md-2 compact-field">
                            <label class="form-label" for="disponivel-${index}">Disponível</label>
                            <select class="form-select form-select-sm" id="disponivel-${index}" onchange="atualizarDisponibilidade(${index})">
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 compact-field">
                            <label class="form-label" for="observacoes-item-${index}">Obs. do item</label>
                            <textarea class="form-control form-control-sm obs-item-compact" id="observacoes-item-${index}" rows="1" maxlength="2000"
                                placeholder="Prazo, substituição, lote ou observação curta..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    const totalPendentesResposta = (pedido.itens || []).filter(item => parseInt(item?.novo_pos_resposta || 0, 10) === 1).length;
    if (alertaPendencias && alertaPendenciasTexto) {
        if (totalPendentesResposta > 0) {
            alertaPendenciasTexto.textContent = `Atenção: este pedido possui ${totalPendentesResposta} item(ns) novo(s) incluído(s) após a resposta anterior. Revise e responda esses itens destacados em vermelho.`;
            alertaPendencias.classList.remove('d-none');
        } else {
            alertaPendencias.classList.add('d-none');
            alertaPendenciasTexto.textContent = '';
        }
    }
    
    // Abrir modal de resposta
    const modal = new bootstrap.Modal(document.getElementById('modalResponderPedido'));
    modal.show();
    
    // Aplicar máscaras nos campos de preço após o modal ser exibido
    setTimeout(() => {
        pedido.itens.forEach((item, index) => {
            const quantidadeInput = document.getElementById(`quantidade-${index}`);
            const precoInput = document.getElementById(`preco-${index}`);
            const disponivelInput = document.getElementById(`disponivel-${index}`);

            const disponivelRaw = item?.disponivel;
            const disponivel = (disponivelRaw !== null && disponivelRaw !== undefined && disponivelRaw !== '') ? parseInt(disponivelRaw, 10) : 1;
            const quantidadeSolicitada = parseFloat(item?.quantidade) || 0;
            const quantidadeDisponivelRaw = item?.quantidade_disponivel;
            const quantidadeDisponivel = (quantidadeDisponivelRaw !== null && quantidadeDisponivelRaw !== undefined && quantidadeDisponivelRaw !== '')
                ? parseFloat(quantidadeDisponivelRaw)
                : null;

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
                // Priorizar preço já respondido (líquido) e converter para bruto no campo
                const precoRespondido = parseFloat(item.preco_fornecedor);
                const precoOriginal = parseFloat(item.preco_unitario);
                let precoParaPreencher = 0;

                if (!Number.isNaN(precoRespondido) && precoRespondido > 0) {
                    precoParaPreencher = precoBrutoAPartirDoLiquido(precoRespondido);
                } else if (!Number.isNaN(precoOriginal) && precoOriginal > 0) {
                    precoParaPreencher = precoOriginal;
                }

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

            const wrapSol = document.getElementById(`obs-solicitacao-wrap-${index}`);
            const elSol = document.getElementById(`obs-solicitacao-${index}`);
            const obsSol = (item.observacoes_solicitacao !== undefined && item.observacoes_solicitacao !== null)
                ? String(item.observacoes_solicitacao)
                : (item.observacoes || '');
            if (wrapSol && elSol) {
                if (obsSol.trim()) {
                    elSol.textContent = obsSol;
                    wrapSol.style.display = '';
                } else {
                    wrapSol.style.display = 'none';
                }
            }
            const obsFornEl = document.getElementById(`observacoes-item-${index}`);
            if (obsFornEl) {
                const obsForn = (item.observacoes_item_fornecedor !== undefined && item.observacoes_item_fornecedor !== null)
                    ? String(item.observacoes_item_fornecedor)
                    : '';
                obsFornEl.value = obsForn;
            }
        });
        calcularResumoFinal();
    }, 300);
}

function filtrarItensRespostaFornecedor() {
    const filtroInput = document.getElementById('filtro-itens-resposta');
    const termo = (filtroInput?.value || '').trim().toLowerCase();
    const itens = document.querySelectorAll('#itens-resposta .item-row');

    itens.forEach((itemRow) => {
        const nome = itemRow.getAttribute('data-item-nome') || '';
        const codigo = itemRow.getAttribute('data-item-codigo') || '';
        const exibir = !termo || nome.includes(termo) || codigo.includes(termo);
        itemRow.style.display = exibir ? '' : 'none';
    });
}

// Validar quantidade
function validarQuantidade(index, quantidadeSolicitada) {
    const quantidadeInput = document.getElementById(`quantidade-${index}`);
    const quantidade = parseFloat(quantidadeInput.value) || 0;
    const infoQuantidade = document.getElementById(`info-quantidade-${index}`);
    
    if (quantidade > quantidadeSolicitada) {
        quantidadeInput.value = quantidadeSolicitada;
        infoQuantidade.textContent = 'Quantidade ajustada para o máximo solicitado';
        infoQuantidade.className = 'text-warning ms-2';
    } else if (quantidade < quantidadeSolicitada && quantidade > 0) {
        infoQuantidade.textContent = `Disponível: ${quantidade} de ${quantidadeSolicitada} solicitados`;
        infoQuantidade.className = 'text-warning ms-2';
    } else if (quantidade === quantidadeSolicitada) {
        infoQuantidade.textContent = 'Quantidade completa disponível';
        infoQuantidade.className = 'text-success ms-2';
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
    const infoQuantidade = document.getElementById(`info-quantidade-${index}`);
    
    if (disponivel === 'nao') {
        quantidadeInput.value = 0;
        quantidadeInput.disabled = true;
        precoInput.value = '';
        precoInput.disabled = true;
        infoQuantidade.textContent = 'Item não disponível';
        infoQuantidade.className = 'text-danger ms-2';
    } else {
        const pedido = pedidosData.find(p => p.id === pedidoAtual.id);
        const item = pedido.itens[index];
        quantidadeInput.value = item.quantidade;
        quantidadeInput.disabled = false;
        precoInput.disabled = false;
        precoInput.placeholder = 'R$ 0,00';
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

    // Suportar formatos BR e EN:
    // BR: 1.234,56 | EN: 1,234.56 | simples: 1234,56 / 1234.56
    const ultimoPonto = texto.lastIndexOf('.');
    const ultimaVirgula = texto.lastIndexOf(',');

    if (ultimoPonto !== -1 && ultimaVirgula !== -1) {
        // O último separador encontrado tende a ser o decimal.
        if (ultimoPonto > ultimaVirgula) {
            // EN: remove vírgulas de milhar
            texto = texto.replace(/,/g, '');
        } else {
            // BR: remove pontos de milhar e troca vírgula decimal
            texto = texto.replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(texto) || 0;
    }

    if (ultimaVirgula !== -1) {
        // Apenas vírgula: tratar como decimal
        texto = texto.replace(/\./g, '').replace(',', '.');
        return parseFloat(texto) || 0;
    }

    // Apenas ponto: pode ser decimal ou milhar.
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

function ambienteLocalDebugCsvFornecedor() {
    const host = (window.location.hostname || '').toLowerCase();
    return host === 'localhost' || host === '127.0.0.1' || host === '::1';
}

function formatarMoedaCsvValidacaoFornecedor(valor) {
    const numero = parseFloat(valor) || 0;
    return numero.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function limparValidacaoCsvLocalFornecedor() {
    const box = document.getElementById('csv-validacao-local-fornecedor-box');
    if (!box) return;
    box.classList.add('d-none');
    const campos = [
        'csv-validacao-total-coluna',
        'csv-validacao-total-calculado',
        'csv-validacao-total-sistema',
        'csv-validacao-desconto',
        'csv-validacao-valor-final',
        'csv-validacao-diferenca',
        'csv-validacao-itens-ajustados'
    ];
    campos.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = id === 'csv-validacao-itens-ajustados' ? '0' : 'R$ 0,00';
    });
}

function atualizarValidacaoCsvLocalFornecedor({
    totalColuna = 0,
    totalCalculado = 0,
    totalSistema = 0,
    desconto = 0,
    valorFinal = 0,
    itensAjustados = 0
}) {
    if (!ambienteLocalDebugCsvFornecedor()) return;
    const box = document.getElementById('csv-validacao-local-fornecedor-box');
    if (!box) return;

    const diferenca = totalColuna - totalSistema;
    box.classList.remove('d-none');
    document.getElementById('csv-validacao-total-coluna').textContent = formatarMoedaCsvValidacaoFornecedor(totalColuna);
    document.getElementById('csv-validacao-total-calculado').textContent = formatarMoedaCsvValidacaoFornecedor(totalCalculado);
    document.getElementById('csv-validacao-total-sistema').textContent = formatarMoedaCsvValidacaoFornecedor(totalSistema);
    document.getElementById('csv-validacao-desconto').textContent = formatarMoedaCsvValidacaoFornecedor(desconto);
    document.getElementById('csv-validacao-valor-final').textContent = formatarMoedaCsvValidacaoFornecedor(valorFinal);
    document.getElementById('csv-validacao-diferenca').textContent = formatarMoedaCsvValidacaoFornecedor(diferenca);
    document.getElementById('csv-validacao-itens-ajustados').textContent = String(itensAjustados);
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
    const agregadosPorItem = new Map();
    let totalCsvColunaTotal = 0;
    let totalCsvCalculado = 0;
    let totalAplicadoSistema = 0;
    let itensAjustadosSistema = 0;

    if (ambienteLocalDebugCsvFornecedor()) {
        limparValidacaoCsvLocalFornecedor();
    }

    // Ao importar CSV, sempre zera desconto final para evitar divergência silenciosa.
    const descontoTipoEl = document.getElementById('desconto-final-tipo');
    const descontoValorEl = document.getElementById('desconto-final-valor');
    if (descontoTipoEl) descontoTipoEl.value = '';
    if (descontoValorEl) descontoValorEl.value = '';

    for (let i = 1; i < linhas.length; i++) {
        const colunas = linhas[i].split(delimitador);
        if (!colunas.length) continue;

        const codigoCsv = (colunas[idxModelo] || '').toString().trim();
        const nomeCsv = (colunas[idxProduto] || '').toString().trim();
        const quantidadeCsv = parseNumeroCsvFornecedor(colunas[idxQuant]);
        const unitarioCsv = parseNumeroCsvFornecedor(colunas[idxUnitario]);
        const totalCsv = parseNumeroCsvFornecedor(colunas[idxTotal]);
        totalCsvColunaTotal += totalCsv > 0 ? totalCsv : 0;
        if (quantidadeCsv > 0 && unitarioCsv > 0) {
            totalCsvCalculado += quantidadeCsv * unitarioCsv;
        } else if (totalCsv > 0) {
            totalCsvCalculado += totalCsv;
        }

        if (!codigoCsv && !nomeCsv) continue;

        let itemIndex = mapaPorCodigo.get(codigoCsv);
        if (itemIndex === undefined && nomeCsv) {
            itemIndex = mapaPorNome.get(normalizarTextoCsvFornecedor(nomeCsv));
        }

        if (itemIndex === undefined) {
            itensNaoEncontrados.push(codigoCsv || nomeCsv);
            continue;
        }

        if (!agregadosPorItem.has(itemIndex)) {
            agregadosPorItem.set(itemIndex, {
                quantidadeSomada: 0,
                totalSomadoCsv: 0,
                totalSomadoCalculado: 0,
                ultimoUnitario: 0
            });
        }

        const agregado = agregadosPorItem.get(itemIndex);
        if (quantidadeCsv > 0) {
            agregado.quantidadeSomada += quantidadeCsv;
        }
        if (totalCsv > 0) {
            agregado.totalSomadoCsv += totalCsv;
        }
        if (quantidadeCsv > 0 && unitarioCsv > 0) {
            agregado.totalSomadoCalculado += quantidadeCsv * unitarioCsv;
        }
        if (unitarioCsv > 0) {
            agregado.ultimoUnitario = unitarioCsv;
        }
    }

    agregadosPorItem.forEach((agregado, itemIndex) => {
        const quantidadeInput = document.getElementById(`quantidade-${itemIndex}`);
        const precoInput = document.getElementById(`preco-${itemIndex}`);
        const disponivelInput = document.getElementById(`disponivel-${itemIndex}`);
        if (!quantidadeInput || !precoInput || !disponivelInput) {
            return;
        }

        disponivelInput.value = 'sim';

        const quantidadeParaAplicar = agregado.quantidadeSomada > 0
            ? agregado.quantidadeSomada
            : (parseFloat(quantidadeInput.value) || 0);

        let unitarioParaAplicar = 0;
        if (agregado.totalSomadoCsv > 0 && quantidadeParaAplicar > 0) {
            unitarioParaAplicar = agregado.totalSomadoCsv / quantidadeParaAplicar;
        } else if (agregado.totalSomadoCalculado > 0 && quantidadeParaAplicar > 0) {
            unitarioParaAplicar = agregado.totalSomadoCalculado / quantidadeParaAplicar;
        } else if (agregado.ultimoUnitario > 0) {
            unitarioParaAplicar = agregado.ultimoUnitario;
        }

        if (quantidadeParaAplicar > 0) {
            quantidadeInput.value = quantidadeParaAplicar;
        }
        if (unitarioParaAplicar > 0) {
            precoInput.value = `R$ ${unitarioParaAplicar.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        const quantidadeSolicitada = parseFloat(pedido.itens[itemIndex].quantidade) || 0;
        validarQuantidade(itemIndex, quantidadeSolicitada);
        calcularTotalItem(itemIndex);
        indicesAtualizados.add(itemIndex);

        const quantidadeAplicada = parseFloat(quantidadeInput.value) || 0;
        const precoAplicado = removerMascaraMoeda(precoInput.value);
        totalAplicadoSistema += (quantidadeAplicada * precoAplicado);

        const teveAjusteQuantidade = Math.abs(quantidadeAplicada - (quantidadeParaAplicar || 0)) > 0.0001;
        const teveAjustePreco = Math.abs(precoAplicado - (unitarioParaAplicar || 0)) > 0.0001;
        if (teveAjusteQuantidade || teveAjustePreco) {
            itensAjustadosSistema++;
        }
    });

    const resumo = calcularResumoFinal();
    atualizarBadgeCsvFornecedor(indicesAtualizados.size);
    renderizarItensNaoEncontradosCsvFornecedor(itensNaoEncontrados);
    totalAplicadoSistema = obterSubtotalAtualItensRespostaFornecedor(pedido);
    atualizarValidacaoCsvLocalFornecedor({
        totalColuna: totalCsvColunaTotal,
        totalCalculado: totalCsvCalculado,
        totalSistema: totalAplicadoSistema,
        desconto: (resumo?.desconto_itens_total || 0) + (resumo?.desconto_final_total || 0),
        valorFinal: resumo?.total_final || totalAplicadoSistema,
        itensAjustados: itensAjustadosSistema
    });

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

function removerMascaraPercentual(valorFormatado) {
    if (!valorFormatado) return 0;
    const valorLimpo = valorFormatado.replace('%', '').replace(/\./g, '').replace(',', '.').trim();
    return parseFloat(valorLimpo) || 0;
}

function obterDescontoItem(index, precoUnitario) {
    const preco = Math.max(parseFloat(precoUnitario) || 0, 0);
    const pct = DESCONTO_PADRAO_ITEM_PERCENTUAL;
    const descontoUnitario = preco * (pct / 100);
    const precoFinalUnitario = Math.max(preco - descontoUnitario, 0);

    return {
        desconto_tipo: 'percentual',
        desconto_valor: pct,
        desconto_unitario: descontoUnitario,
        preco_final_unitario: precoFinalUnitario
    };
}

/** Converte preço líquido (já com desconto) para bruto para exibição no campo. */
function precoBrutoAPartirDoLiquido(precoLiquido) {
    const liquido = parseFloat(precoLiquido);
    if (!Number.isFinite(liquido) || liquido <= 0) return 0;
    const fator = 1 - (DESCONTO_PADRAO_ITEM_PERCENTUAL / 100);
    if (fator <= 0) return liquido;
    return liquido / fator;
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

function obterSubtotaisRespostaFornecedor(pedido) {
    if (!pedido || !Array.isArray(pedido.itens)) {
        return { subtotalBruto: 0, descontoItens: 0, subtotalLiquido: 0 };
    }

    let subtotalBruto = 0;
    let subtotalLiquido = 0;

    for (let i = 0; i < pedido.itens.length; i++) {
        const quantidadeEl = document.getElementById(`quantidade-${i}`);
        const precoEl = document.getElementById(`preco-${i}`);
        const disponivelEl = document.getElementById(`disponivel-${i}`);
        if (!quantidadeEl || !precoEl) continue;

        if (disponivelEl && disponivelEl.value === 'nao') {
            continue;
        }

        const quantidade = parseFloat(quantidadeEl.value) || 0;
        const preco = removerMascaraMoeda(precoEl.value);
        const desconto = obterDescontoItem(i, preco);
        subtotalBruto += quantidade * preco;
        subtotalLiquido += quantidade * desconto.preco_final_unitario;
    }

    return {
        subtotalBruto,
        descontoItens: Math.max(subtotalBruto - subtotalLiquido, 0),
        subtotalLiquido
    };
}

function obterSubtotalAtualItensRespostaFornecedor(pedido) {
    return obterSubtotaisRespostaFornecedor(pedido).subtotalLiquido;
}

function calcularResumoFinal() {
    if (!pedidoAtual) return null;

    const pedido = pedidosData.find(p => p.id === pedidoAtual.id);
    if (!pedido) return null;

    const subtotais = obterSubtotaisRespostaFornecedor(pedido);
    const subtotal = subtotais.subtotalLiquido;

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

    const subtotalEl = document.getElementById('subtotal-itens-resposta');
    if (subtotalEl) {
        subtotalEl.value = subtotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
    const subtotalBrutoEl = document.getElementById('subtotal-bruto-resposta');
    if (subtotalBrutoEl) {
        subtotalBrutoEl.value = subtotais.subtotalBruto.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
    const descontoItensEl = document.getElementById('desconto-itens-resposta');
    if (descontoItensEl) {
        descontoItensEl.value = subtotais.descontoItens.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
    document.getElementById('valor-final-resposta').textContent = totalFinal.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    return {
        subtotal_bruto: subtotais.subtotalBruto,
        desconto_itens_total: subtotais.descontoItens,
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
        // Campos de prazo e pagamento são opcionais.
        const observacoes = document.getElementById('observacoes-fornecedor').value;
        const prazoEntrega = document.getElementById('prazo-entrega').value;
        const condicoesPagamento = document.getElementById('condicoes-pagamento').value;
        const resumoFinal = calcularResumoFinal();
        
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
            
            const obsItemEl = document.getElementById(`observacoes-item-${i}`);
            const observacoes_item = obsItemEl ? obsItemEl.value.trim() : '';

            itensResposta.push({
                item_id: pedido.itens[i].id || i,
                quantidade_solicitada: pedido.itens[i].quantidade,
                quantidade_disponivel: quantidade,
                preco_original: preco,
                desconto_tipo: desconto.desconto_tipo,
                desconto_valor: desconto.desconto_valor,
                desconto_unitario: desconto.desconto_unitario,
                preco: desconto.preco_final_unitario,
                disponivel: disponivel,
                observacoes_item: observacoes_item
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
                subtotal_bruto: resumoFinal?.subtotal_bruto || 0,
                desconto_itens_total: resumoFinal?.desconto_itens_total || 0,
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
                pedido.status = 'em_cotacao';
                pedido.observacoes_fornecedor = (observacoes || '').trim();
                if (resumoFinal?.total_final != null) {
                    pedido.valor_total = resumoFinal.total_final;
                }
                if (Array.isArray(pedido.itens)) {
                    pedido.itens.forEach((item) => {
                        item.novo_pos_resposta = 0;
                    });
                }
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

        const obsSol = (item.observacoes_solicitacao !== undefined && item.observacoes_solicitacao !== null)
            ? String(item.observacoes_solicitacao)
            : (item.observacoes || '');
        const obsForn = (item.observacoes_item_fornecedor !== undefined && item.observacoes_item_fornecedor !== null)
            ? String(item.observacoes_item_fornecedor)
            : '';

        return `
            <tr>
                <td>
                    <strong>${item.nome}</strong>
                    ${obsSol.trim() ? `<br><small class="text-muted">Solicitação: ${escapeHtml(obsSol)}</small>` : ''}
                    ${obsForn.trim() ? `<br><small class="text-primary">Fornecedor: ${escapeHtml(obsForn)}</small>` : ''}
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

document.addEventListener('DOMContentLoaded', function() {
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

    const modalAprovFat = document.getElementById('modalAprovarFaturamento');
    if (modalAprovFat) {
        modalAprovFat.addEventListener('shown.bs.modal', function () {
            const elDh = document.getElementById('aprovar-fat-data-hora');
            if (elDh && typeof formatarDataHoraReferenciaAprovacaoFat === 'function') {
                elDh.textContent = formatarDataHoraReferenciaAprovacaoFat(new Date());
            }
        });
    }

    const taAprovFat = document.getElementById('detalhes-aprovacao-faturamento');
    if (taAprovFat) {
        taAprovFat.addEventListener('input', atualizarContadorDetalhesAprovacaoFat);
    }

    const inputNfAprov = document.getElementById('input-nota-fiscal-aprovacao');
    const nfPrevEl = document.getElementById('aprovar-fat-nf-preview');
    if (inputNfAprov && nfPrevEl) {
        inputNfAprov.addEventListener('change', function () {
            const f = inputNfAprov.files?.[0];
            if (!f) {
                nfPrevEl.classList.add('d-none');
                nfPrevEl.textContent = '';
                return;
            }
            const kb = f.size / 1024;
            const tam = kb >= 1024 ? (kb / 1024).toFixed(2) + ' MB' : kb.toFixed(1) + ' KB';
            nfPrevEl.innerHTML = '<i class="bi bi-paperclip me-1"></i><strong>' + escapeHtml(f.name) + '</strong> <span class="text-muted">(' + tam + ')</span>';
            nfPrevEl.classList.remove('d-none');
        });
    }

    const modalTransp = document.getElementById('modalTransporteFrete');
    if (modalTransp) {
        modalTransp.addEventListener('shown.bs.modal', function () {
            const elDh = document.getElementById('transp-fat-data-hora');
            if (elDh && typeof formatarDataHoraReferenciaAprovacaoFat === 'function') {
                elDh.textContent = formatarDataHoraReferenciaAprovacaoFat(new Date());
            }
        });
    }

    const taTransp = document.getElementById('observacao-transporte-frete');
    if (taTransp) {
        taTransp.addEventListener('input', atualizarContadorTransporteFrete);
    }
});

// ===== APROVAR FATURAMENTO (→ Em trânsito + e-mail compras) =====
let pedidoIdAprovacaoFat = null;

function formatarDataHoraReferenciaAprovacaoFat(d) {
    return d.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function atualizarContadorDetalhesAprovacaoFat() {
    const ta = document.getElementById('detalhes-aprovacao-faturamento');
    const ctr = document.getElementById('aprovar-fat-char-count');
    if (!ta || !ctr) return;
    ctr.textContent = String((ta.value || '').length);
}

function abrirModalAprovarFaturamento(pedidoId) {
    pedidoIdAprovacaoFat = pedidoId;
    const ta = document.getElementById('detalhes-aprovacao-faturamento');
    const file = document.getElementById('input-nota-fiscal-aprovacao');
    const msg = document.getElementById('aprovar-fat-message');
    const prog = document.getElementById('aprovar-fat-progress');
    const nfPrev = document.getElementById('aprovar-fat-nf-preview');

    const pedido = (typeof pedidosData !== 'undefined' && pedidosData && pedidosData.length)
        ? pedidosData.find(p => Number(p.id) === Number(pedidoId))
        : null;

    const elNum = document.getElementById('aprovar-fat-pedido-numero');
    const elCli = document.getElementById('aprovar-fat-pedido-cliente');
    const elDh = document.getElementById('aprovar-fat-data-hora');
    if (elNum) elNum.textContent = pedido?.numero ? String(pedido.numero) : ('#' + pedidoId);
    if (elCli) elCli.textContent = pedido?.cliente ? String(pedido.cliente) : '—';
    if (elDh) elDh.textContent = formatarDataHoraReferenciaAprovacaoFat(new Date());

    if (ta) ta.value = '';
    if (file) file.value = '';
    if (nfPrev) {
        nfPrev.textContent = '';
        nfPrev.classList.add('d-none');
    }
    atualizarContadorDetalhesAprovacaoFat();

    if (msg) {
        msg.classList.add('d-none');
        msg.textContent = '';
    }
    if (prog) prog.classList.add('d-none');
    const btn = document.getElementById('btn-confirmar-aprovacao-faturamento');
    if (btn) btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAprovarFaturamento')).show();
}

async function confirmarAprovacaoFaturamento() {
    const detalhes = (document.getElementById('detalhes-aprovacao-faturamento')?.value || '').trim();
    if (!pedidoIdAprovacaoFat) {
        Swal.fire('Erro', 'Pedido não identificado.', 'error');
        return;
    }
    if (!detalhes) {
        Swal.fire('Atenção', 'Informe os detalhes da aprovação do faturamento.', 'warning');
        return;
    }

    const fileInput = document.getElementById('input-nota-fiscal-aprovacao');
    const nf = fileInput?.files?.[0];
    if (nf) {
        if (nf.size > 10 * 1024 * 1024) {
            Swal.fire('Erro', 'Arquivo muito grande. Tamanho máximo: 10MB', 'error');
            return;
        }
        const ext = nf.name.split('.').pop().toLowerCase();
        if (!['pdf', 'jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
            Swal.fire('Erro', 'Tipo de arquivo não permitido para a NF.', 'error');
            return;
        }
    }

    const formData = new FormData();
    formData.append('action', 'aprovar_faturamento');
    formData.append('pedido_id', String(pedidoIdAprovacaoFat));
    formData.append('detalhes_aprovacao', detalhes);
    if (nf) {
        formData.append('nota_fiscal', nf);
    }

    const btn = document.getElementById('btn-confirmar-aprovacao-faturamento');
    const msgEl = document.getElementById('aprovar-fat-message');
    if (btn) btn.disabled = true;
    if (msgEl) {
        msgEl.classList.add('d-none');
    }

    try {
        const res = await fetch('backend/api/pedidos-fornecedor.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            throw new Error(data.error || data.message || ('Erro HTTP ' + res.status));
        }

        const modalEl = document.getElementById('modalAprovarFaturamento');
        bootstrap.Modal.getInstance(modalEl)?.hide();

        let texto = data.message || 'Faturamento registrado.';
        if (data.email_enviado === false) {
            texto += ' Não foi possível enviar e-mail ao setor de compras (confira SMTP nas configurações e se há usuários com e-mail na tela Pedidos de Compra).';
        }

        await Swal.fire({
            icon: 'success',
            title: 'Concluído',
            text: texto,
            confirmButtonText: 'OK'
        });
        await carregarPedidos();
    } catch (err) {
        if (msgEl) {
            msgEl.className = 'alert alert-danger';
            msgEl.textContent = err.message || String(err);
            msgEl.classList.remove('d-none');
        } else {
            Swal.fire('Erro', err.message || String(err), 'error');
        }
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ===== TRANSPORTE / FRETE (→ Em trânsito) =====
let pedidoIdTransporteFrete = null;

function atualizarContadorTransporteFrete() {
    const ta = document.getElementById('observacao-transporte-frete');
    const ctr = document.getElementById('transp-fat-char-count');
    if (!ta || !ctr) return;
    ctr.textContent = String((ta.value || '').length);
}

function abrirModalTransporteFrete(pedidoId) {
    pedidoIdTransporteFrete = pedidoId;
    const ta = document.getElementById('observacao-transporte-frete');
    const msg = document.getElementById('transp-fat-message');

    const pedido = (typeof pedidosData !== 'undefined' && pedidosData && pedidosData.length)
        ? pedidosData.find(p => Number(p.id) === Number(pedidoId))
        : null;

    const elNum = document.getElementById('transp-fat-pedido-numero');
    const elCli = document.getElementById('transp-fat-pedido-cliente');
    const elDh = document.getElementById('transp-fat-data-hora');
    if (elNum) elNum.textContent = pedido?.numero ? String(pedido.numero) : ('#' + pedidoId);
    if (elCli) elCli.textContent = pedido?.cliente ? String(pedido.cliente) : '—';
    if (elDh) elDh.textContent = formatarDataHoraReferenciaAprovacaoFat(new Date());

    if (ta) ta.value = '';
    atualizarContadorTransporteFrete();

    if (msg) {
        msg.classList.add('d-none');
        msg.textContent = '';
    }
    const btn = document.getElementById('btn-confirmar-transporte-frete');
    if (btn) btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTransporteFrete')).show();
}

async function confirmarTransporteFrete() {
    const observacao = (document.getElementById('observacao-transporte-frete')?.value || '').trim();
    if (!pedidoIdTransporteFrete) {
        Swal.fire('Erro', 'Pedido não identificado.', 'error');
        return;
    }
    if (!observacao) {
        Swal.fire('Atenção', 'Informe as observações sobre transporte / frete.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-confirmar-transporte-frete');
    const msgEl = document.getElementById('transp-fat-message');
    if (btn) btn.disabled = true;
    if (msgEl) msgEl.classList.add('d-none');

    try {
        const res = await fetch('backend/api/pedidos-fornecedor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                action: 'informar_transporte_frete',
                pedido_id: pedidoIdTransporteFrete,
                observacao_transporte: observacao
            })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            throw new Error(data.error || data.message || ('Erro HTTP ' + res.status));
        }

        bootstrap.Modal.getInstance(document.getElementById('modalTransporteFrete'))?.hide();

        let texto = data.message || 'Pedido atualizado para Em trânsito.';
        if (data.email_enviado === false) {
            texto += ' Não foi possível enviar e-mail ao setor de compras.';
        }

        await Swal.fire({
            icon: 'success',
            title: 'Concluído',
            text: texto,
            confirmButtonText: 'OK'
        });
        await carregarPedidos();
    } catch (err) {
        if (msgEl) {
            msgEl.className = 'alert alert-danger';
            msgEl.textContent = err.message || String(err);
            msgEl.classList.remove('d-none');
        } else {
            Swal.fire('Erro', err.message || String(err), 'error');
        }
    } finally {
        if (btn) btn.disabled = false;
    }
}

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
function visualizarNFDoPedido(pedidoId) {
    pedidoIdNF = pedidoId;
    visualizarNF();
}

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