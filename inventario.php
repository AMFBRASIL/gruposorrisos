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
$filialId = getCurrentUserFilialId();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Inventário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/inventario.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-clipboard-data"></i>Inventário</span>
    </div>
    <div class="subtitle">Gerencie o inventário da clínica selecionada</div>
    
    <!-- Indicador da Filial Selecionada -->
    <div class="alert alert-info d-flex align-items-center mb-3" id="filial-indicator" style="display: none;">
        <i class="bi bi-building me-2"></i>
        <div>
            <strong>Exibindo inventários da filial:</strong> 
            <span id="filial-nome">Carregando...</span>
            <small class="d-block text-muted">Apenas inventários desta filial são exibidos</small>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
        <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
        <button class="btn btn-primary btn-action" onclick="abrirModalNovoInventario()" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Inventário</button>
    </div>
    
    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Inventários</div>
                    <div class="card-value" id="total-materiais">0</div>
                    <div class="text-success small">Carregando...</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Em Andamento</div>
                    <div class="card-value" style="color:#f59e0b;" id="em-estoque">0</div>
                    <div class="text-muted small" id="percentual-estoque">0% do total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Finalizados</div>
                    <div class="card-value" style="color:#22c55e;" id="estoque-baixo">0</div>
                    <div class="text-muted small">Concluídos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Cancelados</div>
                    <div class="card-value" style="color:#ef4444;" id="sem-estoque">0</div>
                    <div class="text-muted small">Interrompidos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Inventários -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabelaInventarios">
                    <thead>
                        <tr>
                            <th width="50">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                </div>
                            </th>
                            <th>Número</th>
                            <th>Status</th>
                            <th>Filial</th>
                            <th>Responsável</th>
                            <th>Progresso</th>
                            <th>Valor Total</th>
                            <th>Data Início</th>
                            <th width="200">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-hourglass-split fs-1"></i>
                                <div class="mt-2">Carregando inventários...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div id="paginacao" class="d-flex justify-content-between align-items-center mt-3">
                <!-- Paginação será inserida aqui via JavaScript -->
            </div>
        </div>
    </div>
</main>

<!-- Modal Novo Inventário -->
<div class="modal fade" id="modalNovoInventario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Inventário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="fecharModalNovoInventario()"></button>
            </div>
            <div class="modal-body">
                <!-- Informações da Filial e Horário -->
                <div class="alert alert-info mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-building me-2"></i>
                        <strong>Clínica/Filial:</strong>
                        <span class="ms-2" id="modal-filial-nome">Carregando...</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock me-2"></i>
                        <strong>Horário de Criação:</strong>
                        <span class="ms-2" id="modal-horario-criacao">-</span>
                    </div>
                </div>
                
                <form id="formNovoInventario">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" rows="3" placeholder="Descreva o objetivo deste inventário..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarInventario" onclick="fecharModalNovoInventario()">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnCriarInventario" onclick="salvarNovoInventario()">
                    <span id="btnCriarInventarioIcon"><i class="bi bi-check-lg me-1"></i></span>
                    <span id="btnCriarInventarioText">Criar Inventário</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Inventário -->
<div class="modal fade" id="modalEditarInventario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Inventário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarInventario">
                    <input type="hidden" id="edit-id_inventario">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" id="edit-observacoes" rows="3" placeholder="Descreva o objetivo deste inventário..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="edit-status">
                                <option value="em_andamento">Em Andamento</option>
                                <option value="pausado">Pausado</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicaoInventario()">
                    <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Inventário -->
<div class="modal fade" id="modalVisualizarInventario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Inventário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Número do Inventário</label>
                        <p id="view-numero_inventario" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <p id="view-status" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Filial</label>
                        <p id="view-filial" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Responsável</label>
                        <p id="view-responsavel" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Data de Início</label>
                        <p id="view-data_inicio" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Data de Fim</label>
                        <p id="view-data_fim" class="mb-0"></p>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Observações</label>
                        <p id="view-observacoes" class="mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contagem -->
<div class="modal fade" id="modalContagem" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contagem de Itens</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idInventarioContagem">
                
                <!-- Controles de Paginação e Filtros -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <label for="itens-por-pagina-contagem" class="mb-0">Itens por página:</label>
                            <select class="form-select form-select-sm" id="itens-por-pagina-contagem" style="width: auto;" onchange="alterarItensPorPaginaContagem()">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div id="info-paginacao-contagem" class="text-muted small me-3">
                                <!-- Informações de paginação serão inseridas aqui -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="imprimirContagemItens()" title="Imprimir ou gerar PDF da listagem de contagem">
                                <i class="bi bi-printer me-1"></i> Imprimir / PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-info" id="btnAtualizarInventario" onclick="atualizarInventarioComNovosMateriais()" style="display: none;" title="Adicionar novos materiais cadastrados após a criação do inventário">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                <span id="btnAtualizarInventarioTexto">Atualizar Inventário</span>
                                <span class="badge bg-light text-dark ms-1" id="badgeNovosMateriais" style="display: none;">0</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" id="btnAjustarLote" onclick="ajustarLoteDivergentes()" style="display: none;">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <span id="btnAjustarLoteTexto">Ajustar em Lote</span>
                                <span class="badge bg-light text-dark ms-1" id="badgeDivergentes" style="display: none;">0</span>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="busca-material-contagem" class="mb-0">Buscar Material:</label>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="busca-material-contagem" 
                                   placeholder="Digite nome ou código do material..." 
                                   onkeyup="aplicarBuscaMaterialContagem()"
                                   oninput="aplicarBuscaMaterialContagem()">
                            <button type="button" class="btn btn-outline-secondary" onclick="limparBuscaMaterialContagem()" title="Limpar busca">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <label for="filtro-categoria-contagem" class="mb-0 ms-2">Filtrar por Categoria:</label>
                        <select class="form-select form-select-sm" id="filtro-categoria-contagem" style="width: auto;" onchange="aplicarFiltroCategoriaContagem()">
                            <option value="">Todas as Categorias</option>
                            <!-- Categorias serão carregadas via JavaScript -->
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparFiltroCategoriaContagem()">
                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-sm table-hover" id="tabelaContagem">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Material</th>
                                <th>Quantidade Sistema</th>
                                <th>Quantidade Contada</th>
                                <th>Status</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-hourglass-split fs-1"></i>
                                    <div class="mt-2">Carregando itens...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div id="paginacao-contagem" class="d-flex justify-content-center mt-3">
                    <!-- Paginação será inserida aqui via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
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

<!-- Modal de Processamento -->
<div class="modal fade" id="modalProcessamento" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Processando...</span>
                </div>
                <h5 class="mb-2">Processando Inventário</h5>
                <p class="text-muted mb-0" id="processamento-mensagem">
                    Aguarde enquanto processamos os dados do inventário...
                </p>
                <p class="text-muted small mt-2" id="processamento-detalhes">
                    Isso pode levar alguns minutos dependendo da quantidade de produtos.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Script com informações do usuário -->
<script>
// Informações do usuário logado
const USER_INFO = {
    id: <?php echo $user['id_usuario'] ?? 1; ?>,
    filial_id: <?php echo $filialId ?? 1; ?>,
    nome: '<?php echo addslashes($user['nome_completo'] ?? 'Usuário'); ?>'
};

// Função para obter ID da filial do usuário
function getCurrentUserFilialId() {
    return USER_INFO.filial_id;
}

// Função para obter ID do usuário
function getCurrentUserId() {
    return USER_INFO.id;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script src="assets/js/inventario.js?v=2.0"></script>

</body>
</html>
