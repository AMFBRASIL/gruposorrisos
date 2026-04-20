<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../models/PedidoCompra.php';
require_once __DIR__ . '/../models/Fornecedor.php';
require_once __DIR__ . '/../models/Material.php';

// Verificar se o fornecedor está logado (implementar autenticação conforme necessário)
if (!isset($_SESSION['fornecedor_id'])) {
    // Redirecionar para login ou mostrar mensagem de erro
    header('Location: login.php');
    exit;
}

$fornecedorId = $_SESSION['fornecedor_id'];
$pedidoCompra = new PedidoCompra();
$fornecedorModel = new Fornecedor();
$materialModel = new Material();

// Buscar dados do fornecedor
$fornecedor = $fornecedorModel->buscarPorId($fornecedorId);

// Buscar pedidos pendentes para este fornecedor
$pedidos = $pedidoCompra->buscarPorFornecedor($fornecedorId, 'pendente');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Preços - Fornecedor</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #16a34a;
            --warning-color: #ca8a04;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .main-container {
            padding: 2rem 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .pedido-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .pedido-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }
        
        .pedido-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .pedido-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .pedido-date {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .pedido-status {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pendente {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .items-table th {
            background-color: var(--light-bg);
            padding: 0.75rem;
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .price-input {
            width: 120px;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            text-align: right;
            font-weight: 600;
        }
        
        .price-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .total-row {
            background-color: var(--light-bg);
            font-weight: 600;
        }
        
        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--secondary-color);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .pedido-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-building me-2"></i>
                <?php echo htmlspecialchars($fornecedor['razao_social'] ?? 'Fornecedor'); ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo htmlspecialchars($fornecedor['razao_social'] ?? 'Fornecedor'); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <!-- Header da Página -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">Análise de Preços</h1>
                    <p class="page-subtitle">
                        Analise e defina os preços para os pedidos de compra pendentes
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="stats-icon bg-white bg-opacity-20 mx-auto mx-md-0">
                        <i class="bi bi-calculator text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Principal -->
    <div class="main-container">
        <div class="container">
            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary mx-auto">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="stats-number text-primary">
                            <?php echo count($pedidos); ?>
                        </div>
                        <div class="stats-label">Pedidos Pendentes</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success mx-auto">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stats-number text-success">
                            <?php 
                            $pedidosAprovados = $pedidoCompra->buscarPorFornecedor($fornecedorId, 'aprovado');
                            echo count($pedidosAprovados);
                            ?>
                        </div>
                        <div class="stats-label">Pedidos Aprovados</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning mx-auto">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="stats-number text-warning">
                            <?php 
                            $pedidosEmProducao = $pedidoCompra->buscarPorFornecedor($fornecedorId, 'em_producao');
                            echo count($pedidosEmProducao);
                            ?>
                        </div>
                        <div class="stats-label">Em Produção</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info mx-auto">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="stats-number text-info">
                            <?php 
                            $pedidosEnviados = $pedidoCompra->buscarPorFornecedor($fornecedorId, 'enviado');
                            echo count($pedidosEnviados);
                            ?>
                        </div>
                        <div class="stats-label">Enviados</div>
                    </div>
                </div>
            </div>

            <!-- Lista de Pedidos -->
            <?php if (empty($pedidos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h3>Nenhum pedido pendente</h3>
                    <p>Você não possui pedidos de compra pendentes para análise de preços.</p>
                </div>
            <?php else: ?>
                <h2 class="mb-4">Pedidos Pendentes para Análise</h2>
                
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="pedido-card" data-pedido-id="<?php echo $pedido['id_pedido']; ?>">
                        <div class="pedido-header">
                            <div>
                                <div class="pedido-number">
                                    <?php echo htmlspecialchars($pedido['numero_pedido']); ?>
                                </div>
                                <div class="pedido-date">
                                    <i class="bi bi-calendar me-1"></i>
                                    Solicitado em: <?php echo date('d/m/Y H:i', strtotime($pedido['data_solicitacao'])); ?>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <span class="pedido-status status-pendente">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </span>
                                
                                <button class="btn btn-primary btn-action" onclick="analisarPrecos(<?php echo $pedido['id_pedido']; ?>)">
                                    <i class="bi bi-calculator me-2"></i>
                                    Analisar Preços
                                </button>
                            </div>
                        </div>
                        
                        <!-- Informações do Pedido -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Clínica:</strong> <?php echo htmlspecialchars($pedido['nome_filial']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Entrega Prevista:</strong> 
                                <?php 
                                if ($pedido['data_entrega_prevista']) {
                                    echo date('d/m/Y', strtotime($pedido['data_entrega_prevista']));
                                } else {
                                    echo 'Não informado';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($pedido['observacoes'])): ?>
                            <div class="mb-3">
                                <strong>Observações:</strong>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($pedido['observacoes']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Itens do Pedido -->
                        <h6 class="mb-3">
                            <i class="bi bi-list-ul me-2"></i>
                            Itens Solicitados
                        </h6>
                        
                        <div class="table-responsive">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Material</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unitário</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $itens = $pedidoCompra->buscarItens($pedido['id_pedido']);
                                    $totalPedido = 0;
                                    
                                    foreach ($itens as $item): 
                                        $totalPedido += $item['valor_total'];
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['codigo_material']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['nome_material']); ?></td>
                                            <td><?php echo $item['quantidade']; ?></td>
                                            <td>
                                                <input type="text" 
                                                       class="price-input" 
                                                       value="<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>"
                                                       data-item-id="<?php echo $item['id_item']; ?>"
                                                       data-quantidade="<?php echo $item['quantidade']; ?>"
                                                       onchange="calcularTotalItem(this)"
                                                       placeholder="0,00">
                                            </td>
                                            <td class="item-total">
                                                R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end"><strong>Total do Pedido:</strong></td>
                                        <td class="pedido-total">
                                            <strong>R$ <?php echo number_format($totalPedido, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Análise de Preços -->
    <div class="modal fade" id="modalAnalisePrecos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calculator me-2"></i>
                        Análise de Preços
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <!-- Conteúdo será carregado dinamicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" onclick="salvarPrecos()">
                        <i class="bi bi-check-circle me-2"></i>
                        Salvar Preços
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let pedidoAtual = null;
        
        /**
         * Abrir modal de análise de preços
         */
        function analisarPrecos(pedidoId) {
            pedidoAtual = pedidoId;
            
            // Buscar dados do pedido
            fetch(`/sistemas/_estoquegrupoSorrisos/backend/api/pedidos_compra.php?action=get&id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        preencherModalAnalise(data.data);
                        const modal = new bootstrap.Modal(document.getElementById('modalAnalisePrecos'));
                        modal.show();
                    } else {
                        Swal.fire('Erro', 'Erro ao carregar dados do pedido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire('Erro', 'Erro interno do servidor', 'error');
                });
        }
        
        /**
         * Preencher modal com dados do pedido
         */
        function preencherModalAnalise(pedido) {
            const modalContent = document.getElementById('modalContent');
            
            let itensHtml = '';
            if (pedido.itens && pedido.itens.length > 0) {
                pedido.itens.forEach(item => {
                    itensHtml += `
                        <tr>
                            <td><strong>${item.codigo_material}</strong></td>
                            <td>${item.nome_material}</td>
                            <td>${item.quantidade}</td>
                            <td>
                                <input type="text" 
                                       class="form-control price-input-modal" 
                                       value="${parseFloat(item.preco_unitario).toFixed(2).replace('.', ',')}"
                                       data-item-id="${item.id_item}"
                                       data-quantidade="${item.quantidade}"
                                       onchange="calcularTotalItemModal(this)"
                                       placeholder="0,00">
                            </td>
                            <td class="item-total-modal">
                                R$ ${parseFloat(item.valor_total).toFixed(2).replace('.', ',')}
                            </td>
                        </tr>
                    `;
                });
            }
            
            modalContent.innerHTML = `
                <div class="mb-4">
                    <h6><i class="bi bi-info-circle me-2"></i>Informações do Pedido</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Número:</strong> ${pedido.numero_pedido}<br>
                            <strong>Clínica:</strong> ${pedido.nome_filial}<br>
                            <strong>Data:</strong> ${new Date(pedido.data_solicitacao).toLocaleDateString('pt-BR')}
                        </div>
                        <div class="col-md-6">
                            <strong>Entrega:</strong> ${pedido.data_entrega_prevista ? new Date(pedido.data_entrega_prevista).toLocaleDateString('pt-BR') : 'Não informado'}<br>
                            <strong>Status:</strong> <span class="badge bg-warning">${pedido.status}</span>
                        </div>
                    </div>
                </div>
                
                <h6><i class="bi bi-list-ul me-2"></i>Itens e Preços</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Material</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itensHtml}
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Total do Pedido:</strong></td>
                                <td class="pedido-total-modal">
                                    <strong>R$ ${parseFloat(pedido.valor_total).toFixed(2).replace('.', ',')}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Instruções:</strong> Defina os preços unitários para cada item. O sistema calculará automaticamente os totais.
                </div>
            `;
        }
        
        /**
         * Calcular total de um item no modal
         */
        function calcularTotalItemModal(input) {
            const quantidade = parseFloat(input.dataset.quantidade);
            const preco = parseFloat(input.value.replace(',', '.'));
            
            if (!isNaN(preco) && !isNaN(quantidade)) {
                const total = quantidade * preco;
                const row = input.closest('tr');
                const totalCell = row.querySelector('.item-total-modal');
                totalCell.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
                
                atualizarTotalPedidoModal();
            }
        }
        
        /**
         * Atualizar total do pedido no modal
         */
        function atualizarTotalPedidoModal() {
            const totais = Array.from(document.querySelectorAll('.item-total-modal'))
                .map(cell => parseFloat(cell.textContent.replace('R$ ', '').replace(',', '.')))
                .filter(total => !isNaN(total));
            
            const totalPedido = totais.reduce((sum, total) => sum + total, 0);
            const totalCell = document.querySelector('.pedido-total-modal');
            totalCell.innerHTML = `<strong>R$ ${totalPedido.toFixed(2).replace('.', ',')}</strong>`;
        }
        
        /**
         * Calcular total de um item na lista principal
         */
        function calcularTotalItem(input) {
            const quantidade = parseFloat(input.dataset.quantidade);
            const preco = parseFloat(input.value.replace(',', '.'));
            
            if (!isNaN(preco) && !isNaN(quantidade)) {
                const total = quantidade * preco;
                const row = input.closest('tr');
                const totalCell = row.querySelector('.item-total');
                totalCell.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
                
                atualizarTotalPedido(input);
            }
        }
        
        /**
         * Atualizar total do pedido na lista principal
         */
        function atualizarTotalPedido(input) {
            const pedidoCard = input.closest('.pedido-card');
            const totais = Array.from(pedidoCard.querySelectorAll('.item-total'))
                .map(cell => parseFloat(cell.textContent.replace('R$ ', '').replace(',', '.')))
                .filter(total => !isNaN(total));
            
            const totalPedido = totais.reduce((sum, total) => sum + total, 0);
            const totalCell = pedidoCard.querySelector('.pedido-total');
            totalCell.innerHTML = `<strong>R$ ${totalPedido.toFixed(2).replace('.', ',')}</strong>`;
        }
        
        /**
         * Salvar preços definidos
         */
        function salvarPrecos() {
            if (!pedidoAtual) {
                Swal.fire('Erro', 'Pedido não identificado', 'error');
                return;
            }
            
            // Coletar preços dos itens
            const precos = [];
            document.querySelectorAll('.price-input-modal').forEach(input => {
                precos.push({
                    id_item: input.dataset.itemId,
                    preco_unitario: parseFloat(input.value.replace(',', '.'))
                });
            });
            
            // Validar preços
            if (precos.some(p => isNaN(p.preco_unitario) || p.preco_unitario <= 0)) {
                Swal.fire('Erro', 'Todos os preços devem ser valores válidos maiores que zero', 'error');
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Salvando preços...',
                text: 'Aguarde enquanto salvamos as alterações',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Enviar para API
            fetch('/sistemas/_estoquegrupoSorrisos/backend/api/pedidos_compra.php?action=atualizar-precos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_pedido: pedidoAtual,
                    precos: precos
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Preços Salvos!',
                        text: 'Os preços foram atualizados com sucesso',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Fechar modal e recarregar página
                        bootstrap.Modal.getInstance(document.getElementById('modalAnalisePrecos')).hide();
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.error || 'Erro ao salvar preços', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro', 'Erro interno do servidor', 'error');
            });
        }
        
        // Aplicar máscara de moeda nos inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para inputs de preço
            const priceInputs = document.querySelectorAll('.price-input');
            priceInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = (parseFloat(value) / 100).toFixed(2);
                    e.target.value = value.replace('.', ',');
                });
            });
        });
    </script>
</body>
</html> 