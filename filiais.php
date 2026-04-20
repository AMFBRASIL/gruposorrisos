<?php
// Incluir configurações
require_once 'config/config.php';
require_once 'config/session.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verificar horário de funcionamento
require_once 'middleware/horario_middleware.php';

// Obter informações do usuário logado
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Filiais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/materiais.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Melhorias para os modais */
        .modal-xl {
            max-width: 95%;
        }
        
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .form-control-plaintext {
            padding: 0.375rem 0.75rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        
        .form-control-plaintext:empty::before {
            content: "-";
            color: #6c757d;
        }
        
        /* Melhorar espaçamento dos campos */
        .row.g-3 > div {
            margin-bottom: 0.5rem;
        }
        
        /* Destaque para campos obrigatórios */
        .form-label:has(+ input[required])::after {
            content: " *";
            color: #dc3545;
        }
        

    </style>
</head>
<body>
        <?php include 'menu.php'; ?>
<main class="main-content">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-building"></i>Clínicas</span>
            </div>
            <div class="subtitle">Gerencie as clínicas da sua empresa</div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                <button class="btn btn-outline-light btn-action" onclick="exportarXLS()"><i class="bi bi-download me-1"></i> Exportar XLS</button>
                <button class="btn btn-outline-light btn-action" onclick="imprimir()"><i class="bi bi-printer me-1"></i> Imprimir</button>
                <button class="btn btn-duplicate btn-action" onclick="duplicarSelecionados()"><i class="bi bi-files me-1"></i> Duplicar</button>
                <button class="btn btn-primary btn-action" onclick="abrirModalNovaFilial()" type="button"><i class="bi bi-plus-lg me-1"></i> Nova Filial</button>
            </div>
            
            <!-- Cards de Resumo -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Total de Clínicas</div>
                            <div class="card-value" id="total-filiais">0</div>
                            <div class="text-success small" id="status-total-filiais">Carregando...</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Clínicas Ativas</div>
                            <div class="card-value" style="color:#22c55e;" id="filiais-ativas">0</div>
                            <div class="text-muted small" id="percentual-ativas">0% do total</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Clínicas Inativas</div>
                            <div class="card-value" style="color:#eab308;" id="filiais-inativas">0</div>
                            <div class="text-muted small" id="status-filiais-inativas">Requer atenção</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-resumo">
                        <div class="card-body">
                            <div class="card-title">Funcionários</div>
                            <div class="card-value" style="color:#3b82f6;" id="total-funcionarios">0</div>
                            <div class="text-muted small" id="status-funcionarios">Total de colaboradores</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="card filters-card mb-4">
                <div class="card-body">
                    <div class="filters-title">Filtros e Busca</div>
                    <div class="filters-subtitle">Busque e filtre clínicas por diferentes critérios</div>
                    <form id="filtrosForm" class="mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-bar" id="busca" placeholder="Buscar por nome, cidade ou responsável...">
                            <button type="button" class="btn btn-outline-light d-flex align-items-center ms-2" onclick="toggleFiltros()"><i class="bi bi-funnel me-1"></i> Mais Filtros</button>
                        </div>
                        <div class="row g-2" id="filtrosAvancados" style="display: none;">
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-estado">
                                    <option value="">Todos os Estados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-status">
                                    <option value="">Todos os Status</option>
                                    <option value="ativa">Ativa</option>
                                    <option value="inativa">Inativa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtro-tipo">
                                    <option value="">Todos os Tipos</option>
                                    <option value="matriz">Matriz</option>
                                    <option value="filial">Filial</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-light w-100" onclick="limparFiltros()"><i class="bi bi-arrow-clockwise me-1"></i> Limpar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lista de Filiais -->
            <div class="card card-resumo">
                <div class="card-body">
                    <div class="fw-bold mb-1" style="font-size:1.3rem;">Lista de Filiais</div>
                    <div class="text-muted mb-3">Todas as clínicas cadastradas no sistema</div>
                    
                    <!-- Loading -->
                    <div id="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando filiais...</p>
                    </div>
                    
                    <!-- Tabela -->
                    <div class="table-responsive" id="tabela-container" style="display: none;">
                        <table class="table table-borderless table-materials mb-0">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                                    <th>Código</th>
                                    <th>Nome da Clínica</th>
                                    <th>Tipo</th>
                                    <th>Cidade/Estado</th>
                                    <th>Responsável</th>
                                    <th>Telefone</th>
                                    <th>Funcionários</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="filiais-tbody">
                                <!-- Dados carregados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3" id="paginacao" style="display: none;">
                        <div class="text-muted">
                            Mostrando <span id="inicio-pagina">1</span> a <span id="fim-pagina">10</span> de <span id="total-registros">0</span> filiais
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginacao-links">
                                <!-- Links de paginação -->
                            </ul>
                        </nav>
                    </div>
                    
                    <!-- Sem dados -->
                    <div id="sem-dados" class="text-center py-4" style="display: none;">
                        <i class="bi bi-building fs-1 text-muted"></i>
                        <p class="mt-2">Nenhuma clínica encontrada</p>
                        <button class="btn btn-primary" onclick="abrirModalNovaFilial()">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar Primeira Clínica
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Nova Filial -->
<div class="modal fade" id="modalNovaFilial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-building me-2"></i>Nova Clínica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaFilial">
                    <div class="row g-3">
                        <!-- Informações Básicas -->
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código *</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome da Clínica *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <!-- Razão Social -->
                        <div class="col-md-12">
                            <label for="razao_social" class="form-label">Razão Social</label>
                            <input type="text" class="form-control" id="razao_social" name="razao_social">
                        </div>
                        
                        <!-- Tipo e Status -->
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="filial">Filial</option>
                                <option value="matriz">Matriz</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="ativa">Ativa</option>
                                <option value="inativa">Inativa</option>
                            </select>
                        </div>
                        
                        <!-- CNPJ e Inscrição Estadual -->
                        <div class="col-md-6">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cnpj" name="cnpj" data-mask="00.000.000/0000-00">
                                <button type="button" class="btn btn-outline-primary" onclick="consultarCNPJ(document.getElementById('cnpj').value, false)" title="Consultar CNPJ">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                            <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual">
                        </div>
                        
                        <!-- Endereço -->
                        <div class="col-md-8">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco">
                        </div>
                        <div class="col-md-4">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero">
                        </div>
                        
                        <!-- Complemento e Bairro -->
                        <div class="col-md-6">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento">
                        </div>
                        <div class="col-md-6">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro">
                        </div>
                        
                        <!-- Cidade e Estado -->
                        <div class="col-md-6">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade">
                        </div>
                        <div class="col-md-6">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Selecione...</option>
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
                        
                        <!-- CEP e Telefone -->
                        <div class="col-md-6">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" data-mask="00000-000">
                        </div>
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" data-mask="(00) 00000-0000">
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <!-- Responsável -->
                        <div class="col-md-6">
                            <label for="responsavel" class="form-label">Responsável</label>
                            <input type="text" class="form-control" id="responsavel" name="responsavel">
                        </div>
                        <div class="col-md-6">
                            <label for="email_responsavel" class="form-label">Email do Responsável</label>
                            <input type="email" class="form-control" id="email_responsavel" name="email_responsavel">
                        </div>
                        
                        <!-- Telefone do Responsável -->
                        <div class="col-md-6">
                            <label for="telefone_responsavel" class="form-label">Telefone do Responsável</label>
                            <input type="text" class="form-control" id="telefone_responsavel" name="telefone_responsavel" data-mask="(00) 00000-0000">
                        </div>
                        <div class="col-md-6">
                            <label for="data_abertura" class="form-label">Data de Abertura</label>
                            <input type="date" class="form-control" id="data_abertura" name="data_abertura">
                        </div>
                        
                        <!-- Observações -->
                        <div class="col-md-12">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="4"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarNovaFilial()">
                        <i class="bi bi-check-lg me-1"></i> Salvar Clínica
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Filial -->
<div class="modal fade" id="modalEditarFilial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Clínica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarFilial">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row g-3">
                        <!-- Informações Básicas -->
                        <div class="col-md-4">
                            <label for="edit_codigo" class="form-label">Código *</label>
                            <input type="text" class="form-control" id="edit_codigo" name="codigo" required>
                        </div>
                        <div class="col-md-8">
                            <label for="edit_nome" class="form-label">Nome da Clínica *</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        
                        <!-- Tipo e Status -->
                        <div class="col-md-6">
                            <label for="edit_tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="edit_tipo" name="tipo">
                                <option value="filial">Filial</option>
                                <option value="matriz">Matriz</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="ativa">Ativa</option>
                                <option value="inativa">Inativa</option>
                            </select>
                        </div>
                        
                        <!-- Razão Social -->
                        <div class="col-md-12">
                            <label for="edit_razao_social" class="form-label">Razão Social</label>
                            <input type="text" class="form-control" id="edit_razao_social" name="razao_social">
                        </div>
                        
                        <!-- CNPJ e Inscrição Estadual -->
                        <div class="col-md-6">
                            <label for="edit_cnpj" class="form-label">CNPJ</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit_cnpj" name="cnpj" data-mask="00.000.000/0000-00">
                                <button type="button" class="btn btn-outline-primary" onclick="consultarCNPJ(document.getElementById('edit_cnpj').value, true)" title="Consultar CNPJ">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_inscricao_estadual" class="form-label">Inscrição Estadual</label>
                            <input type="text" class="form-control" id="edit_inscricao_estadual" name="inscricao_estadual">
                        </div>
                        
                        <!-- Endereço -->
                        <div class="col-md-8">
                            <label for="edit_endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="edit_endereco" name="endereco">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="edit_numero" name="numero">
                        </div>
                        
                        <!-- Complemento e Bairro -->
                        <div class="col-md-6">
                            <label for="edit_complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="edit_complemento" name="complemento">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="edit_bairro" name="bairro">
                        </div>
                        
                        <!-- Cidade e Estado -->
                        <div class="col-md-6">
                            <label for="edit_cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="edit_cidade" name="cidade">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_estado" class="form-label">Estado</label>
                            <select class="form-select" id="edit_estado" name="estado">
                                <option value="">Selecione...</option>
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
                        
                        <!-- CEP e Telefone -->
                        <div class="col-md-6">
                            <label for="edit_cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="edit_cep" name="cep" data-mask="00000-000">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="edit_telefone" name="telefone" data-mask="(00) 00000-0000">
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-12">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        
                        <!-- Responsável -->
                        <div class="col-md-6">
                            <label for="edit_responsavel" class="form-label">Responsável</label>
                            <input type="text" class="form-control" id="edit_responsavel" name="responsavel">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email_responsavel" class="form-label">Email do Responsável</label>
                            <input type="email" class="form-control" id="edit_email_responsavel" name="email_responsavel">
                        </div>
                        
                        <!-- Telefone do Responsável -->
                        <div class="col-md-6">
                            <label for="edit_telefone_responsavel" class="form-label">Telefone do Responsável</label>
                            <input type="text" class="form-control" id="edit_telefone_responsavel" name="telefone_responsavel" data-mask="(00) 00000-0000">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_data_inauguracao" class="form-label">Data de Inauguração</label>
                            <input type="date" class="form-control" id="edit_data_inauguracao" name="data_inauguracao">
                        </div>
                        
                        <!-- Observações -->
                        <div class="col-md-12">
                            <label for="edit_observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="edit_observacoes" name="observacoes" rows="4"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEditarFilial()">
                    <i class="bi bi-check-lg me-1"></i> Atualizar Filial
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Filial -->
<div class="modal fade" id="modalVisualizarFilial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Visualizar Filial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Informações Básicas -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Código</label>
                        <p class="form-control-plaintext" id="view_codigo">-</p>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nome da Filial</label>
                        <p class="form-control-plaintext" id="view_nome">-</p>
                    </div>
                    
                    <!-- Tipo e Status -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tipo</label>
                        <p class="form-control-plaintext" id="view_tipo">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <p class="form-control-plaintext" id="view_status">-</p>
                    </div>
                    
                    <!-- CNPJ e Inscrição Estadual -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">CNPJ</label>
                        <p class="form-control-plaintext" id="view_cnpj">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Inscrição Estadual</label>
                        <p class="form-control-plaintext" id="view_inscricao_estadual">-</p>
                    </div>
                    
                    <!-- Endereço -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Endereço</label>
                        <p class="form-control-plaintext" id="view_endereco">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Número</label>
                        <p class="form-control-plaintext" id="view_numero">-</p>
                    </div>
                    
                    <!-- Complemento e Bairro -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Complemento</label>
                        <p class="form-control-plaintext" id="view_complemento">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bairro</label>
                        <p class="form-control-plaintext" id="view_bairro">-</p>
                    </div>
                    
                    <!-- Cidade e Estado -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cidade</label>
                        <p class="form-control-plaintext" id="view_cidade">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="form-control-plaintext" id="view_estado">-</p>
                    </div>
                    
                    <!-- CEP e Telefone -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">CEP</label>
                        <p class="form-control-plaintext" id="view_cep">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Telefone</label>
                        <p class="form-control-plaintext" id="view_telefone">-</p>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Email</label>
                        <p class="form-control-plaintext" id="view_email">-</p>
                    </div>
                    
                    <!-- Responsável -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Responsável</label>
                        <p class="form-control-plaintext" id="view_responsavel">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email do Responsável</label>
                        <p class="form-control-plaintext" id="view_email_responsavel">-</p>
                    </div>
                    
                    <!-- Telefone do Responsável -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Telefone do Responsável</label>
                        <p class="form-control-plaintext" id="view_telefone_responsavel">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Data de Abertura</label>
                        <p class="form-control-plaintext" id="view_data_abertura">-</p>
                    </div>
                    
                    <!-- Observações -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Observações</label>
                        <p class="form-control-plaintext" id="view_observacoes">-</p>
                    </div>
                    
                    <!-- Informações do Sistema -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Data de Criação</label>
                        <p class="form-control-plaintext" id="view_created_at">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Última Atualização</label>
                        <p class="form-control-plaintext" id="view_updated_at">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="editarFilialAtual()">
                    <i class="bi bi-pencil-square me-1"></i> Editar Filial
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

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/filiais.js"></script>

</body>
</html>
