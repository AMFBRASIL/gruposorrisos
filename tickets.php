<?php
require_once 'config/session.php';
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'models/Ticket.php';
requireLogin();

$menuActive = 'tickets';

// Carregar dados para os filtros
try {
    $pdo = Conexao::getInstance()->getPdo();
    $ticket = new Ticket($pdo);
    
    $categorias = $ticket->buscarCategorias();
    $prioridades = $ticket->buscarPrioridades();
    $status = $ticket->buscarStatus();
    $estatisticas = $ticket->getEstatisticas();
} catch (Exception $e) {
    $categorias = [];
    $prioridades = [];
    $status = [];
    $estatisticas = [
        'total_tickets' => 0,
        'tickets_abertos' => 0,
        'tickets_criticos' => 0,
        'tempo_medio_resolucao' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
    <link rel="stylesheet" href="assets/css/tickets.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
<main class="main-content">
    <div class="d-flex align-items-center mb-2">
        <span class="page-title"><i class="bi bi-chat-dots"></i>Tickets</span>
    </div>
    <div class="subtitle">Gerencie os tickets de suporte e solicitações</div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
        <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
        <button class="btn btn-primary btn-action" onclick="abrirModalNovoTicket()" type="button"><i class="bi bi-plus-lg me-1"></i> Novo Ticket</button>
    </div>
    
    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Total de Tickets</div>
                    <div class="card-value" id="total-tickets"><?= $estatisticas['total_tickets'] ?></div>
                    <div class="text-success small" id="status-total-tickets">Tickets cadastrados</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Tickets Abertos</div>
                    <div class="card-value" style="color:#007bff;" id="tickets-abertos"><?= $estatisticas['tickets_abertos'] ?></div>
                    <div class="text-muted small" id="percentual-abertos">Tickets em andamento</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Tickets Críticos</div>
                    <div class="card-value" style="color:#dc3545;" id="tickets-criticos"><?= $estatisticas['tickets_criticos'] ?></div>
                    <div class="text-muted small" id="status-tickets-criticos">Requer atenção</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="card-title">Tempo Médio</div>
                    <div class="card-value" style="color:#28a745;" id="tempo-medio"><?= formatarTempo($estatisticas['tempo_medio_resolucao']) ?></div>
                    <div class="text-muted small" id="status-tempo-medio">Tempo de resolução</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="filters-title">Filtros e Busca</div>
            <div class="filters-subtitle">Busque e filtre tickets por diferentes critérios</div>
            <form id="filtrosForm" class="mb-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por número, título ou descrição...">
                    <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                </div>
                <div class="row g-2" id="filtrosAvancados" style="display: none;">
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-status">
                            <option value="">Todos os Status</option>
                            <?php foreach ($status as $s): ?>
                                <option value="<?= $s['id_status'] ?>"><?= htmlspecialchars($s['nome_status']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-prioridade">
                            <option value="">Todas as Prioridades</option>
                            <?php foreach ($prioridades as $p): ?>
                                <option value="<?= $p['id_prioridade'] ?>"><?= htmlspecialchars($p['nome_prioridade']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filtro-categoria">
                            <option value="">Todas as Categorias</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nome_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de Tickets -->
    <div class="card card-resumo">
        <div class="card-body">
            <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Tickets</div>
            <div class="text-muted mb-3">Todos os tickets cadastrados no sistema</div>
            
            <!-- Loading -->
            <div id="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando tickets...</p>
            </div>
            
            <!-- Lista de Tickets -->
            <div id="tickets-container" style="display: none;">
                <!-- Tickets serão carregados aqui -->
            </div>
            
            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                <div class="text-muted">
                    Mostrando <span id="inicio-pagina">1</span> a <span id="fim-pagina">10</span> de <span id="total-registros">0</span> tickets
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginacao-links">
                        <!-- Links de paginação -->
                    </ul>
                </nav>
            </div>
            
            <!-- Sem dados -->
            <div id="sem-dados" class="text-center py-4" style="display: none;">
                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                <p class="mt-2">Nenhum ticket encontrado</p>
                <button class="btn btn-primary" onclick="abrirModalNovoTicket()">
                    <i class="bi bi-plus-lg me-1"></i> Criar Primeiro Ticket
                </button>
            </div>
        </div>
    </div>
</main>

<!-- MODAL NOVO TICKET -->
<div class="modal fade" id="modalNovoTicket" tabindex="-1" aria-labelledby="modalNovoTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="formNovoTicket" onsubmit="salvarNovoTicket(event)">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-dots text-primary fs-3"></i>
                        <h4 class="modal-title fw-bold" id="modalNovoTicketLabel">Criar Novo Ticket</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body pt-0">
                    <!-- Informações Básicas -->
                    <div class="mb-4">
                        <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-info-circle me-2"></i>Informações Básicas</div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-12">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" id="titulo" placeholder="Título do ticket" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select" id="categoria" required>
                                    <option value="">Selecione a categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id_categoria'] ?>"><?= htmlspecialchars($categoria['nome_categoria']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prioridade *</label>
                                <select class="form-select" id="prioridade" required>
                                    <option value="">Selecione a prioridade</option>
                                    <?php foreach ($prioridades as $prioridade): ?>
                                        <option value="<?= $prioridade['id_prioridade'] ?>"><?= htmlspecialchars($prioridade['nome_prioridade']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Descrição -->
                    <div class="mb-4">
                        <div class="fw-semibold mb-2" style="color:#f59e42;"><i class="bi bi-chat-text me-2"></i>Descrição</div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-12">
                                <label class="form-label">Descrição *</label>
                                <textarea class="form-control" id="descricao" rows="4" placeholder="Descreva detalhadamente o problema ou solicitação..." required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Anexos -->
                    <div class="mb-4">
                        <div class="fw-semibold mb-2" style="color:#8b5cf6;"><i class="bi bi-paperclip me-2"></i>Anexos</div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-12">
                                <label class="form-label">Anexar Arquivos</label>
                                <input type="file" class="form-control" id="anexos" name="anexos[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
                                <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT. Tamanho máximo: 10MB por arquivo.</small>
                                <div id="anexos-preview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Atribuição -->
                    <div class="mb-4">
                        <div class="fw-semibold mb-2" style="color:#2563eb;"><i class="bi bi-person-check me-2"></i>Atribuição</div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">Atribuir para</label>
                                <select class="form-select" id="usuario_atribuido">
                                    <option value="">Selecione um usuário</option>
                                    <!-- Usuários serão carregados via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clínica</label>
                                <select class="form-select" id="filial">
                                    <option value="">Selecione uma Clínica</option>
                                    <!-- Filiais serão carregadas via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Criar Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- FIM MODAL NOVO TICKET -->

<!-- MODAL VISUALIZAR TICKET -->
<div class="modal fade" id="modalVisualizarTicket" tabindex="-1" aria-labelledby="modalVisualizarTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-eye text-primary fs-3"></i>
                    <h4 class="modal-title fw-bold" id="modalVisualizarTicketLabel">Visualizar Ticket</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="ticket-detalhes">
                    <!-- Detalhes do ticket serão carregados aqui -->
                </div>
                
                <!-- Seção de Comentários -->
                <div class="comentarios-section">
                    <div class="section-header">
                        <i class="bi bi-chat-text text-primary me-2"></i>
                        <h6 class="mb-0 fw-bold">Comentários</h6>
                    </div>
                    
                    <div id="comentarios-container">
                        <!-- Comentários serão carregados aqui -->
                    </div>
                    
                    <div class="mt-4">
                        <div class="form-group">
                            <label for="novo-comentario" class="form-label fw-bold">
                                <i class="bi bi-plus-circle me-1"></i>Adicionar Comentário
                            </label>
                            <textarea class="form-control" id="novo-comentario" rows="3" 
                                placeholder="Digite seu comentário aqui..." 
                                style="border-radius: 8px; border: 1px solid #e9ecef;"></textarea>
                        </div>
                        <div class="form-group mt-3">
                            <label for="anexos-comentario" class="form-label fw-bold">
                                <i class="bi bi-paperclip me-1"></i>Anexar Arquivos (Opcional)
                            </label>
                            <input type="file" class="form-control" id="anexos-comentario" name="anexos-comentario[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
                            <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT. Tamanho máximo: 10MB por arquivo.</small>
                            <div id="anexos-comentario-preview" class="mt-2"></div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-primary px-4" onclick="adicionarComentario()">
                                <i class="bi bi-send me-1"></i> Enviar Comentário
                            </button>
                            <button type="button" class="btn btn-outline-primary px-4" onclick="adicionarApenasAnexos()">
                                <i class="bi bi-paperclip me-1"></i> Anexar Arquivos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success px-4" onclick="fecharTicket()">
                    <i class="bi bi-check-circle me-1"></i> Fechar Ticket
                </button>
            </div>
        </div>
    </div>
</div>
<!-- FIM MODAL VISUALIZAR TICKET -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/tickets.js"></script>

</main>
</body>
</html>

<?php
function formatarTempo($minutos) {
    if (!$minutos || $minutos <= 0) return '0h';
    
    if ($minutos < 60) {
        return $minutos . ' min';
    } else if ($minutos < 1440) {
        $horas = floor($minutos / 60);
        return $horas . 'h';
    } else {
        $dias = floor($minutos / 1440);
        return $dias . 'd';
    }
}
?>
