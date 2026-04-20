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

$menuActive = 'relatorios';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/relatorios.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head> 
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-file-earmark-text me-2" style="font-size:2rem;color:#2563eb;"></i>
                <div>
                    <h2 class="fw-bold mb-0">Relatórios</h2>
                    <div class="text-muted" style="font-size: 1rem;">Gere e analise relatórios financeiros</div>
                </div>
            </div>

            <!-- Filtros de Relatório -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filtros de Relatório</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="periodo" class="form-label">Período</label>
                            <select class="form-select" id="periodo">
                                <option value="">Selecione o período</option>
                                <option value="hoje">Hoje</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes">Este Mês</option>
                                <option value="trimestre">Este Trimestre</option>
                                <option value="ano">Este Ano</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo">
                                <option value="">Todos os tipos</option>
                                <option value="financeiro">Financeiro</option>
                                <option value="estoque">Estoque</option>
                                <option value="movimentacao">Movimentação</option>
                                <option value="fornecedor">Fornecedor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="data-especifica" class="form-label">Data Específica</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="data-especifica">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                                <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards de Relatórios -->
            <div class="row g-4">
                <!-- Card 1: Demonstrativo de Resultados -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-bar-chart text-primary" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Financeiro</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Demonstrativo de Resultados</h5>
                            <p class="card-text text-muted small mb-3">Receitas, despesas e lucro líquido do período</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 14/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('demonstrativo')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('demonstrativo')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Fluxo de Caixa -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-graph-up text-success" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Caixa</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Fluxo de Caixa</h5>
                            <p class="card-text text-muted small mb-3">Entradas e saídas de dinheiro detalhadas</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 14/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('fluxo-caixa')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('fluxo-caixa')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Contas a Pagar -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-file-text text-warning" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Contas</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Contas a Pagar</h5>
                            <p class="card-text text-muted small mb-3">Relatório detalhado das obrigações financeiras</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 13/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('contas-pagar')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('contas-pagar')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Contas a Receber -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-currency-dollar text-info" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Recebimentos</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Contas a Receber</h5>
                            <p class="card-text text-muted small mb-3">Relatório de recebimentos pendentes e realizados</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 13/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('contas-receber')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('contas-receber')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Análise por Categoria -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-pie-chart text-danger" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Categoria</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Análise por Categoria</h5>
                            <p class="card-text text-muted small mb-3">Gastos e receitas organizados por categoria</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 12/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('analise-categoria')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('analise-categoria')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Relatório Mensal -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-file-earmark-text text-secondary" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Mensal</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Relatório Mensal</h5>
                            <p class="card-text text-muted small mb-3">Resumo completo das movimentações do mês</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 29/02/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('mensal')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('mensal')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 7: Curva ABC -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-diagram-3 text-purple" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-light text-dark">Análise</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Curva ABC</h5>
                            <p class="card-text text-muted small mb-3">Classificação de produtos por valor e importância</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Última atualização: 15/03/2024</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorio('curva-abc')">
                                        <i class="bi bi-file-earmark-text me-1"></i>Gerar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="baixarRelatorio('curva-abc')">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- Modal Filtros Curva ABC -->
<div class="modal fade" id="modalFiltrosCurvaABC" tabindex="-1" aria-labelledby="modalFiltrosCurvaABCLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalFiltrosCurvaABCLabel">
                    <i class="bi bi-diagram-3 text-purple me-2"></i>Filtros - Curva ABC
                </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="filial-curva-abc" class="form-label">Filial</label>
                        <select class="form-select" id="filial-curva-abc">
                            <option value="">Todas as filiais</option>
                            <option value="1">Filial 1</option>
                            <option value="2">Filial 2</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="categoria-curva-abc" class="form-label">Categoria</label>
                        <select class="form-select" id="categoria-curva-abc">
                            <option value="">Todas as categorias</option>
                            <option value="1">Eletrônicos</option>
                            <option value="2">Móveis</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="periodo-curva-abc" class="form-label">Período de Análise</label>
                        <select class="form-select" id="periodo-curva-abc">
                            <option value="30">Últimos 30 dias</option>
                            <option value="60">Últimos 60 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="180">Últimos 6 meses</option>
                            <option value="365">Último ano</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="criterio-curva-abc" class="form-label">Critério de Classificação</label>
                        <select class="form-select" id="criterio-curva-abc">
                            <option value="valor">Valor em Estoque</option>
                            <option value="movimentacao">Movimentação</option>
                            <option value="lucro">Lucro Gerado</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="data-inicio-curva-abc" class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="data-inicio-curva-abc">
                    </div>
                    <div class="col-md-6">
                        <label for="data-fim-curva-abc" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="data-fim-curva-abc">
                    </div>
        </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Dica:</strong> A Curva ABC classifica os produtos em três categorias: A (alto valor), B (valor médio) e C (baixo valor).
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="gerarRelatorioCurvaABC()">
                    <i class="bi bi-file-earmark-text me-1"></i>Gerar Relatório
                </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Processamento -->
<div class="modal fade" id="modalProcessamento" tabindex="-1" aria-labelledby="modalProcessamentoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <h5 class="fw-bold text-primary mb-2">Processando dados do relatório...</h5>
                <p class="text-muted mb-0">Aguarde enquanto geramos sua análise</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resultado Curva ABC -->
<div class="modal fade" id="modalResultadoCurvaABC" tabindex="-1" aria-labelledby="modalResultadoCurvaABCLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalResultadoCurvaABCLabel">
                    <i class="bi bi-diagram-3 text-purple me-2"></i>Relatório Curva ABC
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="imprimirRelatorio()">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="baixarPDF()">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="baixarExcel()">
                        <i class="bi bi-file-excel me-1"></i>Excel
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
      </div>
      <div class="modal-body">
                <!-- Cabeçalho do Relatório -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-primary">Curva ABC - Análise de Produtos</h4>
                        <p class="text-muted mb-1">Período: <span id="periodo-relatorio">Últimos 30 dias</span></p>
                        <p class="text-muted mb-0">Critério: <span id="criterio-relatorio">Valor em Estoque</span></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="text-muted small">Gerado em: <span id="data-geracao">15/03/2024 às 14:30</span></div>
                    </div>
                </div>

                <!-- Resumo Executivo -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Categoria A</h6>
                                <h3 class="mb-1">15</h3>
                                <small>Produtos (20%)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Categoria B</h6>
                                <h3 class="mb-1">22</h3>
                                <small>Produtos (30%)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Categoria C</h6>
                                <h3 class="mb-1">37</h3>
                                <small>Produtos (50%)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Resultados -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Posição</th>
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Valor Total</th>
                                <th>% Acumulado</th>
                                <th>Classificação</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-curva-abc">
                            <!-- Dados serão carregados dinamicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Gráfico (placeholder) -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Distribuição por Categoria</h6>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-bar-chart" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Gráfico da Curva ABC</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
      </div>
      <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Função para aplicar filtros
    function aplicarFiltros() {
        const periodo = document.getElementById('periodo').value;
        const tipo = document.getElementById('tipo').value;
        const dataEspecifica = document.getElementById('data-especifica').value;
        
        console.log('Filtros aplicados:', { periodo, tipo, dataEspecifica });
        
        // Aqui você pode implementar a lógica para aplicar os filtros
        // Por exemplo, fazer uma requisição AJAX para buscar dados filtrados
        
        // Por enquanto, apenas mostra um alerta
        alert('Filtros aplicados com sucesso!');
    }

    // Função para gerar relatório
    function gerarRelatorio(tipo) {
        console.log('Gerando relatório:', tipo);
        
        if (tipo === 'curva-abc') {
            // Abrir modal de filtros para Curva ABC
            const modal = new bootstrap.Modal(document.getElementById('modalFiltrosCurvaABC'));
            modal.show();
        } else {
            // Para outros relatórios, manter comportamento atual
            alert(`Gerando relatório: ${tipo}`);
        }
    }

    // Função para baixar relatório
    function baixarRelatorio(tipo) {
        console.log('Baixando relatório:', tipo);
        
        // Aqui você pode implementar a lógica para baixar o relatório
        // Por exemplo, fazer uma requisição AJAX para baixar o arquivo
        
        // Por enquanto, apenas mostra um alerta
        alert(`Baixando relatório: ${tipo}`);
    }

    // Função para gerar relatório Curva ABC
    function gerarRelatorioCurvaABC() {
        // Coletar dados dos filtros
        const filtros = {
            filial: document.getElementById('filial-curva-abc').value,
            categoria: document.getElementById('categoria-curva-abc').value,
            periodo: document.getElementById('periodo-curva-abc').value,
            criterio: document.getElementById('criterio-curva-abc').value,
            dataInicio: document.getElementById('data-inicio-curva-abc').value,
            dataFim: document.getElementById('data-fim-curva-abc').value
        };

        console.log('Filtros Curva ABC:', filtros);

        // Fechar modal de filtros
        const modalFiltros = bootstrap.Modal.getInstance(document.getElementById('modalFiltrosCurvaABC'));
        modalFiltros.hide();

        // Mostrar modal de processamento
        const modalProcessamento = new bootstrap.Modal(document.getElementById('modalProcessamento'));
        modalProcessamento.show();

        // Simular processamento por 2 segundos
        setTimeout(() => {
            // Fechar modal de processamento
            modalProcessamento.hide();

            // Mostrar SweetAlert de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Relatório Gerado!',
                text: 'O relatório Curva ABC foi processado com sucesso.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Carregar dados do relatório
                carregarDadosCurvaABC(filtros);
                
                // Abrir modal de resultado
                const modalResultado = new bootstrap.Modal(document.getElementById('modalResultadoCurvaABC'));
                modalResultado.show();
            });
        }, 2000);
    }

    // Função para carregar dados da Curva ABC
    function carregarDadosCurvaABC(filtros) {
        // Atualizar cabeçalho do relatório
        document.getElementById('periodo-relatorio').textContent = 
            filtros.periodo ? `Últimos ${filtros.periodo} dias` : 'Período personalizado';
        document.getElementById('criterio-relatorio').textContent = 
            filtros.criterio === 'valor' ? 'Valor em Estoque' : 
            filtros.criterio === 'movimentacao' ? 'Movimentação' : 'Lucro Gerado';
        document.getElementById('data-geracao').textContent = 
            new Date().toLocaleString('pt-BR');

        // Dados simulados da Curva ABC
        const dadosCurvaABC = [
            { posicao: 1, codigo: 'MAT001', produto: 'Notebook Dell', categoria: 'Eletrônicos', valorTotal: 125000.00, percentualAcumulado: 15.2, classificacao: 'A' },
            { posicao: 2, codigo: 'MAT002', produto: 'Impressora HP', categoria: 'Eletrônicos', valorTotal: 98000.00, percentualAcumulado: 27.1, classificacao: 'A' },
            { posicao: 3, codigo: 'MAT003', produto: 'Mesa Escritório', categoria: 'Móveis', valorTotal: 75000.00, percentualAcumulado: 36.2, classificacao: 'A' },
            { posicao: 4, codigo: 'MAT004', produto: 'Cadeira Ergonômica', categoria: 'Móveis', valorTotal: 65000.00, percentualAcumulado: 44.1, classificacao: 'B' },
            { posicao: 5, codigo: 'MAT005', produto: 'Monitor LG', categoria: 'Eletrônicos', valorTotal: 58000.00, percentualAcumulado: 51.2, classificacao: 'B' },
            { posicao: 6, codigo: 'MAT006', produto: 'Teclado Mecânico', categoria: 'Eletrônicos', valorTotal: 45000.00, percentualAcumulado: 56.7, classificacao: 'B' },
            { posicao: 7, codigo: 'MAT007', produto: 'Mouse Gamer', categoria: 'Eletrônicos', valorTotal: 38000.00, percentualAcumulado: 61.3, classificacao: 'B' },
            { posicao: 8, codigo: 'MAT008', produto: 'Papel A4', categoria: 'Papelaria', valorTotal: 25000.00, percentualAcumulado: 64.4, classificacao: 'C' },
            { posicao: 9, codigo: 'MAT009', produto: 'Caneta Bic', categoria: 'Papelaria', valorTotal: 18000.00, percentualAcumulado: 66.6, classificacao: 'C' },
            { posicao: 10, codigo: 'MAT010', produto: 'Clips de Papel', categoria: 'Papelaria', valorTotal: 12000.00, percentualAcumulado: 68.1, classificacao: 'C' }
        ];

        // Preencher tabela
        const tbody = document.getElementById('tabela-curva-abc');
        tbody.innerHTML = '';

        dadosCurvaABC.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${item.posicao}</strong></td>
                <td>${item.codigo}</td>
                <td>${item.produto}</td>
                <td>${item.categoria}</td>
                <td>R$ ${item.valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td>${item.percentualAcumulado.toFixed(1)}%</td>
                <td>
                    <span class="badge ${item.classificacao === 'A' ? 'bg-primary' : item.classificacao === 'B' ? 'bg-warning' : 'bg-info'}">
                        ${item.classificacao}
                    </span>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Função para imprimir relatório
    function imprimirRelatorio() {
        window.print();
    }

    // Função para baixar PDF
    function baixarPDF() {
        alert('Funcionalidade de download PDF será implementada');
        // Aqui você pode implementar a geração e download do PDF
    }

    // Função para baixar Excel
    function baixarExcel() {
        alert('Funcionalidade de download Excel será implementada');
        // Aqui você pode implementar a geração e download do Excel
    }
</script>

</body>
</html>