<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'backend/controllers/ControllerLogs.php';
requireLogin();

$menuActive = 'logs';

// Carregar dados para os filtros
try {
    $controller = new ControllerLogs();
    $acoes = $controller->obterAcoes();
    $tabelas = $controller->obterTabelas();
    $usuarios = $controller->obterUsuarios();
    $filiais = $controller->obterFiliais();
    $estatisticas = $controller->obterEstatisticas();
} catch (Exception $e) {
    $acoes = [];
    $tabelas = [];
    $usuarios = [];
    $filiais = [];
    $estatisticas = [
        'total_logs' => 0,
        'total_usuarios' => 0,
        'logs_24h' => 0,
        'logs_7dias' => 0,
        'logs_30dias' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs do Sistema | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        .log-acao {
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-block;
        }
        .log-acao-login { background: #dbeafe; color: #1e40af; }
        .log-acao-create { background: #dcfce7; color: #166534; }
        .log-acao-update { background: #fef3c7; color: #92400e; }
        .log-acao-delete { background: #fee2e2; color: #991b1b; }
        .log-acao-export { background: #e0e7ff; color: #3730a3; }
        .log-acao-default { background: #f3f4f6; color: #374151; }
        
        .dados-json {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .badge-ip {
            background: #f1f5f9;
            color: #475569;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-journal-text"></i> Logs do Sistema</span>
    </div>
    <div class="subtitle">Visualize e filtre os registros de atividades do sistema</div>
    
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarLogs()">
            <i class="bi bi-download me-1"></i> Exportar CSV
        </button>
        <button class="btn btn-outline-light btn-action" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
        <button class="btn btn-outline-light btn-action" onclick="limparFiltros()">
            <i class="bi bi-arrow-clockwise me-1"></i> Atualizar
        </button>
    </div>
    
    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Logs</div>
                    <div class="card-value" id="total-logs"><?= number_format($estatisticas['total_logs'], 0, ',', '.') ?></div>
                    <div class="text-muted small">Registros no sistema</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Últimas 24h</div>
                    <div class="card-value" style="color:#3b82f6;" id="logs-24h"><?= number_format($estatisticas['logs_24h'], 0, ',', '.') ?></div>
                    <div class="text-muted small">Atividades recentes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Últimos 7 dias</div>
                    <div class="card-value" style="color:#eab308;" id="logs-7dias"><?= number_format($estatisticas['logs_7dias'], 0, ',', '.') ?></div>
                    <div class="text-muted small">Atividades semanais</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Últimos 30 dias</div>
                    <div class="card-value" style="color:#22c55e;" id="logs-30dias"><?= number_format($estatisticas['logs_30dias'], 0, ',', '.') ?></div>
                    <div class="text-muted small">Atividades mensais</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card de Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="filters-title">Filtros e Busca</div>
            <div class="filters-subtitle">Filtre os logs por diferentes critérios</div>
            <form class="mb-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-bar" id="searchInput" placeholder="Buscar por ação, tabela, usuário, IP...">
                    <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltrosAvancados()">
                        <i class="bi bi-funnel me-1"></i> Filtros Avançados
                    </button>
                </div>
                
                <div id="filtrosAvancados" style="display: none;">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small">Usuário</label>
                            <select class="form-select" id="filtroUsuario">
                                <option value="">Todos os Usuários</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_usuario'] ?>"><?= htmlspecialchars($usuario['nome_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Filial</label>
                            <select class="form-select" id="filtroFilial">
                                <option value="">Todas as Filiais</option>
                                <?php foreach ($filiais as $filial): ?>
                                    <option value="<?= $filial['id_filial'] ?>"><?= htmlspecialchars($filial['nome_filial']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Ação</label>
                            <select class="form-select" id="filtroAcao">
                                <option value="">Todas as Ações</option>
                                <?php foreach ($acoes as $acao): ?>
                                    <option value="<?= htmlspecialchars($acao) ?>"><?= htmlspecialchars($acao) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tabela</label>
                            <select class="form-select" id="filtroTabela">
                                <option value="">Todas as Tabelas</option>
                                <?php foreach ($tabelas as $tabela): ?>
                                    <option value="<?= htmlspecialchars($tabela) ?>"><?= htmlspecialchars($tabela) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Data Início</label>
                            <input type="date" class="form-control" id="filtroDataInicio">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Data Fim</label>
                            <input type="date" class="form-control" id="filtroDataFim">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">IP</label>
                            <input type="text" class="form-control" id="filtroIp" placeholder="Ex: 192.168.1.1">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabela de Logs -->
    <div class="card card-resumo">
        <div class="card-body">
            <div class="fw-bold mb-1" style="font-size:1.3rem;">Registros de Logs</div>
            <div class="text-muted mb-3">Histórico de atividades do sistema</div>
            <div class="table-responsive">
                <table class="table table-borderless table-usuarios mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 140px;">Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Tabela</th>
                            <th>Registro</th>
                            <th>IP</th>
                            <th style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        <!-- Linhas dinâmicas aqui -->
                    </tbody>
                </table>
                <div id="logs-paginacao" class="mt-3"></div>
            </div>
        </div>
    </div>
</main>

<!-- Modal de Detalhes do Log -->
<div class="modal fade" id="modalDetalhesLog" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary fs-3"></i>
                    <h4 class="modal-title fw-bold">Detalhes do Log</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="detalhesLogContent"></div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentPage = 1;
let currentSearch = '';

// Toggle filtros avançados
function toggleFiltrosAvancados() {
    const filtros = document.getElementById('filtrosAvancados');
    filtros.style.display = filtros.style.display === 'none' ? 'block' : 'none';
}

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('backend/api/logs_sistema.php?action=estatisticas');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            document.getElementById('total-logs').textContent = formatarNumero(stats.total_logs);
            document.getElementById('logs-24h').textContent = formatarNumero(stats.logs_24h);
            document.getElementById('logs-7dias').textContent = formatarNumero(stats.logs_7dias);
            document.getElementById('logs-30dias').textContent = formatarNumero(stats.logs_30dias);
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Formatar número
function formatarNumero(num) {
    return new Intl.NumberFormat('pt-BR').format(num);
}

// Carregar logs
async function carregarLogs(page = 1, search = '') {
    try {
        currentPage = page;
        currentSearch = search;
        
        // Obter filtros
        const filtroUsuario = document.getElementById('filtroUsuario').value;
        const filtroFilial = document.getElementById('filtroFilial').value;
        const filtroAcao = document.getElementById('filtroAcao').value;
        const filtroTabela = document.getElementById('filtroTabela').value;
        const filtroDataInicio = document.getElementById('filtroDataInicio').value;
        const filtroDataFim = document.getElementById('filtroDataFim').value;
        const filtroIp = document.getElementById('filtroIp').value;
        
        // Construir URL com filtros
        let url = `backend/api/logs_sistema.php?action=list&page=${page}&limit=50`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (filtroUsuario) url += `&usuario=${filtroUsuario}`;
        if (filtroFilial) url += `&filial=${filtroFilial}`;
        if (filtroAcao) url += `&acao=${encodeURIComponent(filtroAcao)}`;
        if (filtroTabela) url += `&tabela=${encodeURIComponent(filtroTabela)}`;
        if (filtroDataInicio) url += `&data_inicio=${filtroDataInicio}`;
        if (filtroDataFim) url += `&data_fim=${filtroDataFim}`;
        if (filtroIp) url += `&ip=${filtroIp}`;
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('logs-tbody');
        tbody.innerHTML = '';
        
        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(log => {
                const dataHora = formatarDataHora(log.data_log);
                const usuario = log.usuario_nome || '-';
                const acao = formatarAcao(log.acao);
                const tabela = log.tabela || '-';
                const registro = log.id_registro || '-';
                const ip = log.ip_usuario ? `<span class="badge-ip">${log.ip_usuario}</span>` : '-';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${log.id_log}</td>
                        <td><small>${dataHora}</small></td>
                        <td><small>${usuario}</small></td>
                        <td>${acao}</td>
                        <td><span class="badge bg-light text-dark">${tabela}</span></td>
                        <td>${registro}</td>
                        <td>${ip}</td>
                        <td>
                            <button class="icon-btn text-primary" title="Ver Detalhes" onclick="verDetalhes(${log.id_log})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 mb-2"></i><br>
                        Nenhum log encontrado
                    </td>
                </tr>
            `;
        }
        
        // Paginação
        renderizarPaginacao(result.pagination);
        
    } catch (error) {
        console.error('Erro ao carregar logs:', error);
        document.getElementById('logs-tbody').innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i><br>
                    Erro ao carregar logs
                </td>
            </tr>
        `;
    }
}

// Formatar data e hora
function formatarDataHora(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Formatar ação com badge colorido
function formatarAcao(acao) {
    if (!acao) return '<span class="log-acao log-acao-default">-</span>';
    
    let classe = 'log-acao-default';
    if (acao.includes('LOGIN')) classe = 'log-acao-login';
    else if (acao.includes('CREATE') || acao.includes('INSERT')) classe = 'log-acao-create';
    else if (acao.includes('UPDATE') || acao.includes('EDIT')) classe = 'log-acao-update';
    else if (acao.includes('DELETE')) classe = 'log-acao-delete';
    else if (acao.includes('EXPORT')) classe = 'log-acao-export';
    
    return `<span class="log-acao ${classe}">${acao}</span>`;
}

// Renderizar paginação
function renderizarPaginacao(pagination) {
    const pagDiv = document.getElementById('logs-paginacao');
    let pagHtml = '';
    
    if (pagination && pagination.total_pages > 1) {
        pagHtml += `<nav><ul class='pagination pagination-sm justify-content-center'>`;
        
        // Botão anterior
        if (pagination.page > 1) {
            pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarLogs(${pagination.page - 1}, "${currentSearch}");return false;'><i class="bi bi-chevron-left"></i></a></li>`;
        }
        
        // Páginas
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                pagHtml += `<li class='page-item${i === pagination.page ? ' active' : ''}'><a class='page-link' href='#' onclick='carregarLogs(${i}, "${currentSearch}");return false;'>${i}</a></li>`;
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                pagHtml += `<li class='page-item disabled'><span class='page-link'>...</span></li>`;
            }
        }
        
        // Botão próximo
        if (pagination.page < pagination.total_pages) {
            pagHtml += `<li class='page-item'><a class='page-link' href='#' onclick='carregarLogs(${pagination.page + 1}, "${currentSearch}");return false;'><i class="bi bi-chevron-right"></i></a></li>`;
        }
        
        pagHtml += `</ul></nav>`;
    }
    pagDiv.innerHTML = pagHtml;
}

// Ver detalhes do log
async function verDetalhes(id) {
    try {
        const response = await fetch(`backend/api/logs_sistema.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const log = result.data;
            
            let html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>ID:</strong> ${log.id_log}
                    </div>
                    <div class="col-md-6">
                        <strong>Data/Hora:</strong> ${formatarDataHora(log.data_log)}
                    </div>
                    <div class="col-md-6">
                        <strong>Usuário:</strong> ${log.usuario_nome || '-'} ${log.usuario_email ? '(' + log.usuario_email + ')' : ''}
                    </div>
                    <div class="col-md-6">
                        <strong>Filial:</strong> ${log.nome_filial || '-'}
                    </div>
                    <div class="col-md-6">
                        <strong>Ação:</strong> ${formatarAcao(log.acao)}
                    </div>
                    <div class="col-md-6">
                        <strong>Tabela:</strong> <span class="badge bg-light text-dark">${log.tabela || '-'}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>ID Registro:</strong> ${log.id_registro || '-'}
                    </div>
                    <div class="col-md-6">
                        <strong>IP:</strong> <span class="badge-ip">${log.ip_usuario || '-'}</span>
                    </div>
                    <div class="col-12">
                        <strong>User Agent:</strong><br>
                        <small class="text-muted">${log.user_agent || '-'}</small>
                    </div>
            `;
            
            if (log.dados_anteriores) {
                html += `
                    <div class="col-12">
                        <strong>Dados Anteriores:</strong>
                        <div class="dados-json">${formatarJSON(log.dados_anteriores)}</div>
                    </div>
                `;
            }
            
            if (log.dados_novos) {
                html += `
                    <div class="col-12">
                        <strong>Dados Novos:</strong>
                        <div class="dados-json">${formatarJSON(log.dados_novos)}</div>
                    </div>
                `;
            }
            
            html += `</div>`;
            
            document.getElementById('detalhesLogContent').innerHTML = html;
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhesLog'));
            modal.show();
        }
    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao carregar detalhes do log',
            confirmButtonText: 'OK'
        });
    }
}

// Formatar JSON para exibição
function formatarJSON(json) {
    try {
        const obj = typeof json === 'string' ? JSON.parse(json) : json;
        return JSON.stringify(obj, null, 2);
    } catch (e) {
        return json;
    }
}

// Exportar logs
function exportarLogs() {
    const filtroUsuario = document.getElementById('filtroUsuario').value;
    const filtroFilial = document.getElementById('filtroFilial').value;
    const filtroAcao = document.getElementById('filtroAcao').value;
    const filtroTabela = document.getElementById('filtroTabela').value;
    const filtroDataInicio = document.getElementById('filtroDataInicio').value;
    const filtroDataFim = document.getElementById('filtroDataFim').value;
    const filtroIp = document.getElementById('filtroIp').value;
    const search = document.getElementById('searchInput').value;
    
    let url = 'backend/api/logs_sistema.php?action=export';
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (filtroUsuario) url += `&usuario=${filtroUsuario}`;
    if (filtroFilial) url += `&filial=${filtroFilial}`;
    if (filtroAcao) url += `&acao=${encodeURIComponent(filtroAcao)}`;
    if (filtroTabela) url += `&tabela=${encodeURIComponent(filtroTabela)}`;
    if (filtroDataInicio) url += `&data_inicio=${filtroDataInicio}`;
    if (filtroDataFim) url += `&data_fim=${filtroDataFim}`;
    if (filtroIp) url += `&ip=${filtroIp}`;
    
    window.location.href = url;
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filtroUsuario').value = '';
    document.getElementById('filtroFilial').value = '';
    document.getElementById('filtroAcao').value = '';
    document.getElementById('filtroTabela').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
    document.getElementById('filtroIp').value = '';
    carregarLogs(1, '');
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', function() {
    carregarLogs(1, this.value);
});

document.getElementById('filtroUsuario').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroFilial').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroAcao').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroTabela').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroDataInicio').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroDataFim').addEventListener('change', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

document.getElementById('filtroIp').addEventListener('input', function() {
    carregarLogs(1, document.getElementById('searchInput').value);
});

// Inicial
carregarEstatisticas();
carregarLogs();
</script>
</body>
</html>





