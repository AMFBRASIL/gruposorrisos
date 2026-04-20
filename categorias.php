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
    <title><?php echo APP_NAME; ?> - Categorias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/categorias.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-tags"></i>Categorias</span>
    </div>
    <div class="subtitle">Gerencie as categorias de materiais do seu estoque</div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
        <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
        <button class="btn btn-primary btn-action" onclick="abrirModalNovaCategoria()" type="button"><i class="bi bi-plus-lg me-1"></i> Nova Categoria</button>
    </div>
    
    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Categorias</div>
                    <div class="card-value" id="total-categorias">0</div>
                    <div class="text-success small" id="texto-total">Carregando...</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Materiais Categorizados</div>
                    <div class="card-value" style="color:#22c55e;" id="materiais-categorizados">0</div>
                    <div class="text-muted small" id="percentual-categorizados">0% do total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Sem Categoria</div>
                    <div class="card-value" style="color:#eab308;" id="sem-categoria">0</div>
                    <div class="text-muted small">Requer atenção</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Categorias Ativas</div>
                    <div class="card-value" style="color:#3b82f6;" id="categorias-ativas">0</div>
                    <div class="text-muted small">Em uso</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="filters-title">Filtros e Busca</div>
            <div class="filters-subtitle">Busque e filtre categorias por diferentes critérios</div>
            <form id="filtrosForm" class="mb-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por nome ou descrição...">
                    <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                </div>
                <div class="row g-2" id="filtrosAvancados" style="display: none;">
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-status">
                            <option value="">Todos os Status</option>
                            <option value="1">Ativas</option>
                            <option value="0">Inativas</option>
                        </select>
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
    
    <!-- Lista de Categorias -->
    <div class="card">
        <div class="card-body">
            <div id="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando categorias...</p>
            </div>
            
            <div id="tabela-container" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome da Categoria</th>
                                <th>Descrição</th>
                                <th>Materiais</th>
                                <th>Status</th>
                                <th>Data Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="categorias-tbody">
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <nav aria-label="Navegação de páginas" id="paginacao">
                </nav>
            </div>
            
            <div id="sem-dados" class="text-center py-4" style="display: none;">
                <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">Nenhuma categoria encontrada</h5>
                <p class="text-muted">Clique em "Nova Categoria" para começar</p>
            </div>
        </div>
    </div>
</main>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaCategoria">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome_categoria" class="form-label required-field">Nome da Categoria</label>
                                <input type="text" class="form-control" id="nome_categoria" name="nome_categoria" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="ativo">
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Descreva a categoria..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarNovaCategoria()">
                    <i class="bi bi-check-lg me-1"></i>Salvar Categoria
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Categoria -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCategoria">
                    <input type="hidden" id="edit_id_categoria" name="id_categoria">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nome_categoria" class="form-label required-field">Nome da Categoria</label>
                                <input type="text" class="form-control" id="edit_nome_categoria" name="nome_categoria" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="ativo">
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_descricao" name="descricao" rows="4" placeholder="Descreva a categoria..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEditarCategoria()">
                    <i class="bi bi-check-lg me-1"></i>Atualizar Categoria
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Categoria -->
<div class="modal fade" id="modalVisualizarCategoria" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Visualizar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Categoria</label>
                            <div class="form-control-plaintext" id="view_nome_categoria"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-control-plaintext" id="view_status"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descrição</label>
                    <div class="form-control-plaintext" id="view_descricao"></div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data de Criação</label>
                            <div class="form-control-plaintext" id="view_data_criacao"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Última Atualização</label>
                            <div class="form-control-plaintext" id="view_data_atualizacao"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total de Materiais</label>
                            <div class="form-control-plaintext" id="view_total_materiais"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="editarCategoriaAtual()">
                    <i class="bi bi-pencil-square me-1"></i>Editar Categoria
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
                <h5 class="modal-title">Confirmar Ação</h5>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/categorias.js"></script>
</body>
</html>
