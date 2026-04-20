<?php
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verificar horário de funcionamento
require_once 'middleware/horario_middleware.php';

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem acesso à página atual
if (!$controllerAcesso->verificarAcessoPagina()) {
    // Se não tiver acesso, será redirecionado automaticamente
    exit;
}

// Registrar acesso à página
$controllerAcesso->registrarAcessoPagina();

$menuActive = 'fornecedores';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/fornecedores.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>

<?php include 'menu.php'; ?>

<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <i class="bi bi-truck me-2" style="font-size:2rem;color:#2563eb;"></i>
        <div>
            <h2 class="fw-bold mb-0">Fornecedores</h2>
            <div class="text-muted" style="font-size: 1rem;">Gerencie sua rede de fornecedores e parceiros comerciais</div>
        </div>
        <div class="ms-auto">
            <?php if ($controllerAcesso->verificarEAutorizar('inserir', 'fornecedores.php', false)): ?>
                <button class="btn btn-primary btn-novo" onclick="window.location.href='addFornecedor.php'" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Fornecedor</button>
            <?php endif; ?>
        </div>
    </div>
            
            <!-- Indicadores -->
            <div class="row g-3 mb-4 mt-2" id="indicadores">
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total Fornecedores</div>
                            <div class="card-value" id="total-fornecedores">-</div>
                            <div class="text-muted small" id="status-fornecedores">Carregando...</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Fornecedores Ativos</div>
                                <div class="card-value" style="color:#22c55e;" id="fornecedores-ativos">-</div>
                                <div class="text-muted small" id="percentual-ativos">-</div>
                            </div>
                            <i class="bi bi-graph-up card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Produtos Fornecidos</div>
                                <div class="card-value" style="color:#2563eb;" id="produtos-fornecidos">-</div>
                                <div class="text-muted small">Produtos únicos</div>
                            </div>
                            <i class="bi bi-box2 card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Pedidos Este Mês</div>
                                <div class="card-value" style="color:#2563eb;" id="pedidos-mes">-</div>
                                <div class="text-muted small">Pedidos realizados</div>
                            </div>
                            <i class="bi bi-clipboard-data card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Busca -->
            <div class="card card-resumo mb-4">
                <div class="card-body pb-2">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Buscar Fornecedores</div>
                    <div class="text-muted mb-3">Encontre fornecedores por nome, CNPJ ou cidade</div>
                    <form class="d-flex align-items-center gap-2" id="formBusca">
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar fornecedores..." onkeyup="buscarFornecedores()">
                        </div>
                        <button type="button" class="btn btn-outline-secondary" onclick="limparBusca()">
                            <i class="bi bi-x-lg"></i> Limpar
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Lista -->
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Fornecedores</div>
                            <div class="text-muted">Todos os fornecedores cadastrados no sistema</div>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="filtroStatus" onchange="buscarFornecedores()">
                                <option value="">Todos os status</option>
                                <option value="1">Ativos</option>
                                <option value="0">Inativos</option>
                            </select>
                            <select class="form-select form-select-sm" id="filtroEstado" onchange="buscarFornecedores()">
                                <option value="">Todos os estados</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-borderless table-fornecedores mb-0">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Contato</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Localização</th>
                                    <th>Produtos</th>
                                    <th>Pedidos</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-fornecedores">
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                        <div class="text-muted">
                            Mostrando <span id="mostrando-inicio">0</span> a <span id="mostrando-fim">0</span> de <span id="total-registros">0</span> fornecedores
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-controls">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este fornecedor?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentPage = 1;
let fornecedorParaExcluir = null;

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarFornecedores();
});

// Carregar estatísticas
function carregarEstatisticas() {
    fetch('api/fornecedores.php?action=estatisticas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                document.getElementById('total-fornecedores').textContent = stats.total;
                document.getElementById('fornecedores-ativos').textContent = stats.ativos;
                document.getElementById('produtos-fornecidos').textContent = stats.produtos_fornecidos;
                document.getElementById('pedidos-mes').textContent = stats.pedidos_mes;
                
                const inativos = stats.total - stats.ativos;
                const percentual = stats.total > 0 ? Math.round((stats.ativos / stats.total) * 100) : 0;
                
                document.getElementById('status-fornecedores').textContent = `${stats.ativos} ativos, ${inativos} inativos`;
                document.getElementById('percentual-ativos').textContent = `${percentual}% do total`;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar estatísticas:', error);
        });
}

// Carregar fornecedores
function carregarFornecedores(page = 1) {
    currentPage = page;
    const busca = document.getElementById('busca').value;
    const status = document.getElementById('filtroStatus').value;
    const estado = document.getElementById('filtroEstado').value;
    
    let url = `api/fornecedores.php?action=list&page=${page}&limit=10`;
    
    if (busca) url += `&razao_social=${encodeURIComponent(busca)}`;
    if (status !== '') url += `&ativo=${status}`;
    if (estado) url += `&estado=${estado}`;
    
    console.log('Carregando fornecedores:', url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API:', data);
            if (data.data) {
                renderizarTabela(data.data);
                renderizarPaginacao(data);
            } else {
                renderizarTabela([]);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar fornecedores:', error);
            renderizarTabela([]);
        });
}

// Renderizar tabela
function renderizarTabela(fornecedores) {
    const tbody = document.getElementById('tabela-fornecedores');
    
    if (fornecedores.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Nenhum fornecedor encontrado</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = fornecedores.map(fornecedor => `
        <tr>
            <td>
                <div class="fw-bold">${fornecedor.razao_social}</div>
                ${fornecedor.nome_fantasia ? `<div class="text-muted small">${fornecedor.nome_fantasia}</div>` : ''}
                ${fornecedor.cnpj ? `<div class="text-muted-strong">${fornecedor.cnpj}</div>` : ''}
            </td>
            <td>${fornecedor.contato_principal || '-'}</td>
            <td>${fornecedor.telefone || '-'}</td>
            <td>${fornecedor.email || '-'}</td>
            <td>
                <i class="bi bi-geo-alt me-1"></i>
                ${fornecedor.cidade || ''}${fornecedor.estado ? `, ${fornecedor.estado}` : ''}
            </td>
            <td>
                <span class="badge bg-primary">${fornecedor.total_materiais || 0}</span>
            </td>
            <td>
                <span class="badge bg-success">${fornecedor.total_pedidos || 0}</span>
            </td>
            <td>
                <span class="status-badge ${fornecedor.ativo ? 'status-ativo' : 'status-inativo'}">
                    ${fornecedor.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td>
                <button class="icon-btn text-success me-2" title="Editar" onclick="editarFornecedor(${fornecedor.id_fornecedor})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="icon-btn text-danger" title="Excluir" onclick="confirmarExclusao(${fornecedor.id_fornecedor})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Renderizar paginação
function renderizarPaginacao(data) {
    const paginacao = document.getElementById('paginacao');
    const controls = document.getElementById('pagination-controls');
    
    if (data.pages <= 1) {
        paginacao.style.display = 'none';
        return;
    }
    
    paginacao.style.display = 'flex';
    
    const inicio = ((data.page - 1) * data.limit) + 1;
    const fim = Math.min(data.page * data.limit, data.total);
    
    document.getElementById('mostrando-inicio').textContent = inicio;
    document.getElementById('mostrando-fim').textContent = fim;
    document.getElementById('total-registros').textContent = data.total;
    
    let html = '';
    
    // Botão anterior
    html += `
        <li class="page-item ${data.page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="carregarFornecedores(${data.page - 1})">Anterior</a>
        </li>
    `;
    
    // Páginas
    for (let i = Math.max(1, data.page - 2); i <= Math.min(data.pages, data.page + 2); i++) {
        html += `
            <li class="page-item ${i === data.page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="carregarFornecedores(${i})">${i}</a>
            </li>
        `;
    }
    
    // Botão próximo
    html += `
        <li class="page-item ${data.page >= data.pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="carregarFornecedores(${data.page + 1})">Próximo</a>
        </li>
    `;
    
    controls.innerHTML = html;
}

// Buscar fornecedores
function buscarFornecedores() {
    carregarFornecedores(1);
}

// Limpar busca
function limparBusca() {
    document.getElementById('busca').value = '';
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroEstado').value = '';
    carregarFornecedores(1);
}

// Editar fornecedor
function editarFornecedor(id) {
    window.location.href = `addFornecedor.php?id=${id}`;
}

// Confirmar exclusão
function confirmarExclusao(id) {
    fornecedorParaExcluir = id;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
    modal.show();
}

// Excluir fornecedor
document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
    if (!fornecedorParaExcluir) return;
    
    fetch(`api/fornecedores.php?action=delete&id=${fornecedorParaExcluir}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmacao')).hide();
            
            // Recarregar dados
            carregarEstatisticas();
            carregarFornecedores(currentPage);
            
            // Mostrar mensagem de sucesso
            alert('Fornecedor excluído com sucesso!');
        } else {
            alert('Erro ao excluir fornecedor: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro ao excluir fornecedor:', error);
        alert('Erro ao excluir fornecedor');
    });
});
</script>
</body>
</html>