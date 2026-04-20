<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'models/Alerta.php';
requireLogin();

$menuActive = 'alertas';

try {
    $pdo = Conexao::getInstance()->getPdo();
    $alerta = new Alerta($pdo);
    
    $estatisticas = $alerta->getEstatisticas();
    $alertasCriticos = $alerta->getAlertasCriticos(3);
} catch (Exception $e) {
    $estatisticas = [
        'total_alertas' => 0,
        'alertas_ativos' => 0,
        'alertas_resolvidos' => 0,
        'alertas_criticos' => 0,
        'alertas_medios' => 0,
        'alertas_baixos' => 0,
        'alertas_hoje' => 0,
        'resolvidos_hoje' => 0,
        'produtos_afetados' => 0,
        'filiais_afetadas' => 0
    ];
    $alertasCriticos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Estoque | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/alertas.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    <main class="main-content">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size:2rem;color:#f59e42;"></i>
            <div>
                <h2 class="fw-bold mb-0">Alertas de Estoque</h2>
                <div class="text-muted" style="font-size: 1rem;">Monitore alertas e notificações importantes sobre o estoque</div>
            </div>
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-outline-success" onclick="marcarTodosLidos()">
                    <i class="bi bi-check2-circle me-1"></i> Marcar Todos como Lidos
                </button>
                <button class="btn btn-outline-primary" onclick="gerarAlertasAutomaticos()">
                    <i class="bi bi-lightning me-1"></i> Gerar Alertas
                </button>
            </div>
        </div>
            <div class="row g-3 mb-4 mt-2">
                
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Alertas Ativos</div>
                            <div class="card-value"><?= $estatisticas['alertas_ativos'] ?></div>
                            <div class="text-muted small">Requerem atenção</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Críticos</div>
                                <div class="card-value" style="color:#ef4444;"><?= $estatisticas['alertas_criticos'] ?></div>
                                <div class="text-muted small">Ação imediata necessária</div>
                            </div>
                            <i class="bi bi-exclamation-octagon card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Produtos Afetados</div>
                                <div class="card-value" style="color:#f59e42;"><?= $estatisticas['produtos_afetados'] ?></div>
                                <div class="text-muted small">Diferentes produtos</div>
                            </div>
                            <i class="bi bi-gear card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="card-title">Resolvidos Hoje</div>
                                <div class="card-value" style="color:#22c55e;"><?= $estatisticas['resolvidos_hoje'] ?></div>
                                <div class="text-muted small">Ações realizadas</div>
                            </div>
                            <i class="bi bi-check2-circle card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Alertas Críticos -->
            <?php if (!empty($alertasCriticos)): ?>
            <div class="alert-critico-box mb-4">
                <div class="alert-critico-header">
                    <i class="bi bi-exclamation-triangle-fill icon-alerta-critico"></i>
                    Alertas Críticos - Ação Imediata
                </div>
                <?php foreach ($alertasCriticos as $alertaCritico): ?>
                <div class="alert-critico-body">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($alertaCritico['nome_material']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($alertaCritico['mensagem']) ?></div>
                        <div class="text-muted small">Filial: <?= htmlspecialchars($alertaCritico['nome_filial']) ?></div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-nivel badge-critico">Crítico</span>
                        <button class="btn-resolver" onclick="marcarComoLido(<?= $alertaCritico['id_alerta'] ?>)">Resolver</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="card filters-card mb-4">
                <div class="card-body">
                    <div class="filters-title">Filtros e Busca</div>
                    <div class="filters-subtitle">Filtre alertas por diferentes critérios</div>
                    <form class="mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" id="buscaAlerta" placeholder="Buscar por produto...">
                            <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="limparBusca()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Limpar
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select class="form-select" id="filtroFilial">
                                    <option value="">Todas as Filiais</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos os Tipos</option>
                                    <option value="estoque_baixo">Estoque Baixo</option>
                                    <option value="estoque_zerado">Sem Estoque</option>
                                    <option value="estoque_excedido">Estoque Excedido</option>
                                    <option value="vencimento_proximo">Vencimento Próximo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos os Níveis</option>
                                    <option value="alta">Crítico</option>
                                    <option value="media">Médio</option>
                                    <option value="baixa">Baixo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroStatus">
                                    <option value="">Todos os Status</option>
                                    <option value="ativo">Ativos</option>
                                    <option value="resolvido">Resolvidos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="aplicarFiltros()">
                                    <i class="bi bi-funnel me-1"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Todos os Alertas</div>
                    <div class="text-muted mb-3">Histórico completo de alertas e notificações</div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-alertas mb-0" id="tabela-alertas">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Produto</th>
                                    <th>Filial</th>
                                    <th>Estoque</th>
                                    <th>Nível</th>
                                    <th>Data/Hora</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Alertas serão carregados via JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted">
                                Mostrando <span id="inicio-pagina">1</span> a <span id="fim-pagina">10</span> de <span id="total-registros">0</span> alertas
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
                </div>
            </div>
        </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variáveis globais
let paginaAtual = 1;
let limitePorPagina = parseInt(localStorage.getItem('alertas_limite_por_pagina')) || 10;
let currentFilters = {};

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    // Configurar seletor de itens por página
    const selectItensPorPagina = document.getElementById('itens-por-pagina');
    if (selectItensPorPagina) {
        selectItensPorPagina.value = limitePorPagina;
        selectItensPorPagina.addEventListener('change', function() {
            limitePorPagina = parseInt(this.value);
            localStorage.setItem('alertas_limite_por_pagina', limitePorPagina);
            paginaAtual = 1; // Voltar para primeira página ao mudar limite
            carregarAlertas();
        });
    }
    
    carregarFiliais();
    carregarAlertas();
});

// Carregar filiais
async function carregarFiliais() {
    try {
        const response = await fetch('backend/api/filiais.php?action=list');
        const result = await response.json();
        
        if (result.success && result.filiais) {
            const select = document.getElementById('filtroFilial');
            result.filiais.forEach(filial => {
                const option = document.createElement('option');
                option.value = filial.id;
                option.textContent = filial.nome;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar filiais:', error);
    }
}

// Carregar alertas
async function carregarAlertas(page = null, filters = null) {
    try {
        // Se page não foi passado, usar paginaAtual
        if (page !== null) {
            paginaAtual = page;
        }
        
        // Se filters não foi passado, usar currentFilters
        if (filters !== null) {
            currentFilters = filters;
        }
        
        let url = `backend/api/alertas.php?action=list&page=${paginaAtual}&limit=${limitePorPagina}`;
        
        // Usar currentFilters se filters não foi passado
        const filtersToUse = filters !== null ? filters : currentFilters;
        
        // Adicionar filtros
        if (filtersToUse.search) url += `&search=${encodeURIComponent(filtersToUse.search)}`;
        if (filtersToUse.tipo) url += `&tipo=${encodeURIComponent(filtersToUse.tipo)}`;
        if (filtersToUse.nivel) url += `&nivel=${encodeURIComponent(filtersToUse.nivel)}`;
        if (filtersToUse.status) url += `&status=${encodeURIComponent(filtersToUse.status)}`;
        if (filtersToUse.filial) url += `&filial=${encodeURIComponent(filtersToUse.filial)}`; // Adicionar filtro de filial
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.querySelector('#tabela-alertas tbody');
        tbody.innerHTML = '';
        
        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(alerta => {
                const dataAlerta = alerta.data_alerta ? 
                    new Date(alerta.data_alerta).toLocaleString('pt-BR') : '-';
                
                const tipoIcon = getTipoIcon(alerta.tipo_alerta);
                const nivelBadge = getNivelBadge(alerta.nivel_urgencia);
                const statusBadge = getStatusBadge(alerta.lido);
                
                tbody.innerHTML += `
                    <tr>
                        <td>${tipoIcon} ${getTipoLabel(alerta.tipo_alerta)}</td>
                        <td>
                            <strong>${alerta.nome_material || 'N/A'}</strong><br>
                            <small class="text-muted">${alerta.codigo_material || ''}</small>
                        </td>
                        <td>
                            <strong>${alerta.nome_filial || 'N/A'}</strong><br>
                            <small class="text-muted">${alerta.cidade_filial || ''} - ${alerta.estado_filial || ''}</small>
                        </td>
                        <td>${alerta.quantidade_atual || 0} / ${alerta.quantidade_limite || 0}</td>
                        <td>${nivelBadge}</td>
                        <td>${dataAlerta}</td>
                        <td>${statusBadge}</td>
                        <td>
                            ${alerta.lido == 0 ? `
                                <button class="icon-btn text-success me-2" title="Marcar como resolvido" onclick="marcarComoLido(${alerta.id_alerta})">
                                    <i class="bi bi-check2"></i>
                                </button>
                            ` : ''}
                            <button class="icon-btn text-danger" title="Excluir" onclick="excluirAlerta(${alerta.id_alerta})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-bell-slash fs-1 mb-2"></i><br>
                        Nenhum alerta encontrado
                    </td>
                </tr>
            `;
        }
        
        // Renderizar paginação
        if (result.pagination) {
            renderizarPaginacao(result.pagination);
        } else {
            // Esconder paginação se não houver dados
            const container = document.getElementById('paginacao');
            if (container) {
                container.style.display = 'none';
            }
        }
        
    } catch (error) {
        console.error('Erro ao carregar alertas:', error);
        document.querySelector('#tabela-alertas tbody').innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i><br>
                    Erro ao carregar alertas
                </td>
            </tr>
        `;
        // Esconder paginação em caso de erro
        const container = document.getElementById('paginacao');
        if (container) {
            container.style.display = 'none';
        }
    }
}

// Funções auxiliares
function getTipoIcon(tipo) {
    const icons = {
        'estoque_baixo': '<i class="bi bi-exclamation-triangle text-warning"></i>',
        'estoque_zerado': '<i class="bi bi-x-circle text-danger"></i>',
        'estoque_excedido': '<i class="bi bi-arrow-up-circle text-info"></i>',
        'vencimento_proximo': '<i class="bi bi-calendar-event text-warning"></i>'
    };
    return icons[tipo] || '<i class="bi bi-bell text-secondary"></i>';
}

function getTipoLabel(tipo) {
    const labels = {
        'estoque_baixo': 'Estoque Baixo',
        'estoque_zerado': 'Sem Estoque',
        'estoque_excedido': 'Estoque Excedido',
        'vencimento_proximo': 'Vencimento Próximo'
    };
    return labels[tipo] || tipo;
}

function getNivelBadge(nivel) {
    const badges = {
        'alta': '<span class="badge-nivel badge-critico">Crítico</span>',
        'media': '<span class="badge-nivel badge-medio">Médio</span>',
        'baixa': '<span class="badge-nivel badge-baixo">Baixo</span>'
    };
    return badges[nivel] || '<span class="badge-nivel badge-baixo">Baixo</span>';
}

function getStatusBadge(lido) {
    return lido == 1 ? 
        '<span class="badge-status-resolvido">Resolvido</span>' : 
        '<span class="badge-status-ativo">Ativo</span>';
}

// Buscar alertas
document.getElementById('buscaAlerta').addEventListener('input', function() {
    const filters = { ...currentFilters, search: this.value };
    paginaAtual = 1;
    carregarAlertas(1, filters);
});

function limparBusca() {
    document.getElementById('buscaAlerta').value = '';
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroNivel').value = '';
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroFilial').value = ''; // Limpar filtro de filial
    paginaAtual = 1;
    currentFilters = {};
    carregarAlertas();
}

function aplicarFiltros() {
    const filters = {
        search: document.getElementById('buscaAlerta').value,
        tipo: document.getElementById('filtroTipo').value,
        nivel: document.getElementById('filtroNivel').value,
        status: document.getElementById('filtroStatus').value,
        filial: document.getElementById('filtroFilial').value // Adicionar filtro de filial
    };
    paginaAtual = 1;
    carregarAlertas(1, filters);
}

// Renderizar paginação
function renderizarPaginacao(pagination) {
    const container = document.getElementById('paginacao');
    const links = document.getElementById('paginacao-links');
    
    if (!container || !links) {
        return;
    }
    
    // Converter para números para evitar problemas de tipo
    const page = parseInt(pagination.page) || 1;
    const limit = parseInt(pagination.limit) || limitePorPagina;
    const total = parseInt(pagination.total) || 0;
    const totalPages = parseInt(pagination.total_pages) || 1;
    
    // Calcular início e fim da página atual
    const start = total > 0 ? ((page - 1) * limit) + 1 : 0;
    const end = Math.min(page * limit, total);
    
    document.getElementById('inicio-pagina').textContent = start;
    document.getElementById('fim-pagina').textContent = end;
    document.getElementById('total-registros').textContent = total;
    
    links.innerHTML = '';
    
    // Se só tem 1 página, mostrar apenas informação
    if (totalPages <= 1) {
        links.innerHTML = '<li class="page-item disabled"><span class="page-link">Página 1 de 1</span></li>';
        container.style.display = 'flex';
        return;
    }
    
    // Botão anterior
    if (page > 1) {
        links.appendChild(createPageLink(page - 1, '« Anterior'));
    }
    
    // Páginas numeradas (mostrar 2 páginas antes e depois)
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(totalPages, page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        links.appendChild(createPageLink(i, i, i === page));
    }
    
    // Botão próximo
    if (page < totalPages) {
        links.appendChild(createPageLink(page + 1, 'Próximo »'));
    }
    
    container.style.display = 'flex';
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
        carregarAlertas();
        
        // Scroll suave para o topo da tabela
        document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    
    li.appendChild(a);
    return li;
}

// Marcar como lido
async function marcarComoLido(id) {
    try {
        const response = await fetch(`backend/api/alertas.php?action=marcar_lido&id=${id}`, {
            method: 'PUT'
        });
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Alerta marcado como resolvido!',
                confirmButtonText: 'OK'
            });
            carregarAlertas();
            // Recarregar estatísticas
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao marcar alerta como lido',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    }
}

// Marcar todos como lidos
async function marcarTodosLidos() {
    Swal.fire({
        title: 'Confirmar ação',
        text: "Deseja marcar todos os alertas como resolvidos?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, marcar todos!',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('backend/api/alertas.php?action=marcar_todos_lidos', {
                    method: 'PUT'
                });
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Todos os alertas foram marcados como resolvidos!',
                        confirmButtonText: 'OK'
                    });
                    carregarAlertas();
                    // Recarregar estatísticas
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: result.error || 'Erro ao marcar alertas como lidos',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão. Verifique sua internet e tente novamente.',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
}

// Gerar alertas automáticos
async function gerarAlertasAutomaticos() {
    try {
        const response = await fetch('backend/api/alertas.php?action=gerar_alertas', {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Alertas automáticos gerados com sucesso!',
                confirmButtonText: 'OK'
            });
            carregarAlertas();
            // Recarregar estatísticas
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao gerar alertas automáticos',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    }
}

// Excluir alerta
function excluirAlerta(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não pode ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            excluirAlertaAPI(id);
        }
    });
}

async function excluirAlertaAPI(id) {
    try {
        const response = await fetch(`backend/api/alertas.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Alerta excluído com sucesso!',
                confirmButtonText: 'OK'
            });
            carregarAlertas();
            // Recarregar estatísticas
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: result.error || 'Erro ao excluir alerta',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro de conexão. Verifique sua internet e tente novamente.',
            confirmButtonText: 'OK'
        });
    }
}
</script>
</body>
</html> 