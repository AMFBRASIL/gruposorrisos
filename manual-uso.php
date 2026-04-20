<?php
$menuActive = 'manual-uso';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Uso | Sistema de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .badge.bg-purple {
            background-color: #6f42c1 !important;
        }
        .bg-purple {
            background-color: #6f42c1 !important;
        }
        .btn-purple {
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            color: white !important;
        }
        .btn-purple:hover {
            background-color: #5a32a3 !important;
            border-color: #5a32a3 !important;
            color: white !important;
        }
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
    </style>
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
                <i class="bi bi-book me-2" style="font-size:2rem;color:#2563eb;"></i>
                <div>
                    <h2 class="fw-bold mb-0">Manual de Uso</h2>
                    <div class="text-muted" style="font-size: 1rem;">Guia completo de utilização do sistema</div>
                </div>
            </div>

            <!-- Introdução -->
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Bem-vindo ao Manual de Uso!</h6>
                        <p class="mb-0">Clique em "Visualizar Conteúdo" em qualquer módulo para aprender como utilizá-lo corretamente.</p>
                    </div>
                </div>
            </div>

            <!-- Módulos do Sistema -->
            <div class="row g-4">
                <!-- Módulo 1: Dashboard -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-speedometer2 text-primary" style="font-size: 2rem;"></i>
                                <span class="badge bg-primary">Dashboard</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Dashboard Principal</h5>
                            <p class="card-text text-muted small mb-3">Visão geral do sistema com indicadores e estatísticas</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Visão Geral</div>
                                <button class="btn btn-primary w-100" onclick="visualizarManual('dashboard')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 2: Materiais -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-box-seam text-success" style="font-size: 2rem;"></i>
                                <span class="badge bg-success">Materiais</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Gestão de Materiais</h5>
                            <p class="card-text text-muted small mb-3">Cadastro, edição e controle de materiais do sistema</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Cadastros</div>
                                <button class="btn btn-success w-100" onclick="visualizarManual('materiais')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 3: Estoque -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-archive text-warning" style="font-size: 2rem;"></i>
                                <span class="badge bg-warning">Estoque</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Controle de Estoque</h5>
                            <p class="card-text text-muted small mb-3">Monitoramento e controle de estoque por filial</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Operacional</div>
                                <button class="btn btn-warning w-100" onclick="visualizarManual('estoque')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 4: Movimentações -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-arrow-left-right text-info" style="font-size: 2rem;"></i>
                                <span class="badge bg-info">Movimentações</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Movimentações de Estoque</h5>
                            <p class="card-text text-muted small mb-3">Entradas, saídas, transferências e ajustes</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Operacional</div>
                                <button class="btn btn-info w-100" onclick="visualizarManual('movimentacoes')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 5: Pedidos de Compra -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-cart-check text-danger" style="font-size: 2rem;"></i>
                                <span class="badge bg-danger">Compras</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Pedidos de Compra</h5>
                            <p class="card-text text-muted small mb-3">Solicitação, aprovação e gestão de compras</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Compras</div>
                                <button class="btn btn-danger w-100" onclick="visualizarManual('pedidos-compra')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 6: Inventário -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-clipboard-check text-purple" style="font-size: 2rem;"></i>
                                <span class="badge bg-purple">Inventário</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Controle de Inventário</h5>
                            <p class="card-text text-muted small mb-3">Contagem física e reconciliação de estoque</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Operacional</div>
                                <button class="btn btn-purple w-100" onclick="visualizarManual('inventario')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 7: Fornecedores -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-truck text-secondary" style="font-size: 2rem;"></i>
                                <span class="badge bg-secondary">Fornecedores</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Gestão de Fornecedores</h5>
                            <p class="card-text text-muted small mb-3">Cadastro e controle de fornecedores</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mt-auto">
                                    <div class="text-muted small mb-2">Módulo: Cadastros</div>
                                    <button class="btn btn-secondary w-100" onclick="visualizarManual('fornecedores')">
                                        <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 8: Filiais -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-building text-dark" style="font-size: 2rem;"></i>
                                <span class="badge bg-dark">Filiais</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Gestão de Filiais</h5>
                            <p class="card-text text-muted small mb-3">Controle de filiais e unidades da empresa</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Cadastros</div>
                                <button class="btn btn-dark w-100" onclick="visualizarManual('filiais')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 9: Usuários -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                                <span class="badge bg-primary">Usuários</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Gestão de Usuários</h5>
                            <p class="card-text text-muted small mb-3">Controle de acesso e permissões do sistema</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Administrativo</div>
                                <button class="btn btn-primary w-100" onclick="visualizarManual('usuarios')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 10: Relatórios -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <i class="bi bi-file-earmark-text text-success" style="font-size: 2rem;"></i>
                                <span class="badge bg-success">Relatórios</span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">Relatórios e Análises</h5>
                            <p class="card-text text-muted small mb-3">Geração de relatórios e análises gerenciais</p>
                            <div class="mt-auto">
                                <div class="text-muted small mb-2">Módulo: Gerencial</div>
                                <button class="btn btn-success w-100" onclick="visualizarManual('relatorios')">
                                    <i class="bi bi-eye me-2"></i>Visualizar Conteúdo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- Modal Manual de Uso -->
<div class="modal fade" id="modalManual" tabindex="-1" aria-labelledby="modalManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalManualLabel">
                    <i class="bi bi-book me-2"></i>Manual de Uso - <span id="tituloModulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="conteudoManual">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirManual()">
                    <i class="bi bi-printer me-2"></i>Imprimir Manual
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Função para visualizar manual de uso
    function visualizarManual(modulo) {
        console.log('Visualizando manual do módulo:', modulo);
        
        // Definir título do módulo
        const titulos = {
            'dashboard': 'Dashboard Principal',
            'materiais': 'Gestão de Materiais',
            'estoque': 'Controle de Estoque',
            'movimentacoes': 'Movimentações de Estoque',
            'pedidos-compra': 'Pedidos de Compra',
            'inventario': 'Controle de Inventário',
            'fornecedores': 'Gestão de Fornecedores',
            'filiais': 'Gestão de Filiais',
            'usuarios': 'Gestão de Usuários',
            'relatorios': 'Relatórios e Análises'
        };
        
        document.getElementById('tituloModulo').textContent = titulos[modulo] || 'Módulo';
        
        // Carregar conteúdo do manual
        carregarConteudoManual(modulo);
        
        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalManual'));
        modal.show();
    }

    // Função para carregar conteúdo do manual
    function carregarConteudoManual(modulo) {
        const conteudoManual = document.getElementById('conteudoManual');
        
        // Conteúdo específico para cada módulo
        const manuais = {
            'dashboard': {
                titulo: 'Dashboard Principal',
                descricao: 'Painel de controle central com visão estratégica de todo o sistema de estoque',
                funcionalidades: [
                    'Indicadores de estoque em tempo real por filial',
                    'Estatísticas de movimentações (entradas, saídas, transferências)',
                    'Alertas automáticos de estoque baixo e crítico',
                    'Gráficos interativos de performance e tendências',
                    'Resumo financeiro com valores em estoque e custos',
                    'Contadores de materiais por categoria e status',
                    'Últimas movimentações realizadas no sistema',
                    'Status de inventários em andamento'
                ],
                instrucoes: [
                    '1. Acesse o dashboard através do menu principal "Dashboard"',
                    '2. Visualize os indicadores principais no topo da tela (estoque total, materiais, valor)',
                    '3. Use os filtros de período (hoje, semana, mês, trimestre, ano) para análises temporais',
                    '4. Clique nos gráficos circulares para expandir detalhes por categoria',
                    '5. Configure alertas personalizados clicando no ícone de sino',
                    '6. Use os filtros de filial para analisar dados específicos',
                    '7. Acesse relatórios rápidos clicando nos cards de resumo',
                    '8. Monitore o histórico de movimentações na seção inferior'
                ],
                dicas: [
                    'Mantenha o dashboard sempre visível em uma aba para monitoramento contínuo',
                    'Configure alertas para estoque baixo (abaixo de 20% do mínimo) e movimentações críticas',
                    'Use os filtros de período para análises comparativas entre meses/trimestres',
                    'Personalize os widgets do dashboard arrastando e redimensionando conforme sua necessidade',
                    'Exporte dados do dashboard para Excel/PDF clicando no botão de exportação',
                    'Configure notificações por email para alertas importantes'
                ],
                fluxoOperacional: [
                    'Entrada no sistema → Verificação de alertas → Análise de indicadores → Ação corretiva se necessário',
                    'Monitoramento contínuo → Identificação de tendências → Planejamento de ações → Execução de melhorias'
                ]
            },
            'materiais': {
                titulo: 'Gestão de Materiais',
                descricao: 'Sistema completo de cadastro, controle e manutenção de todos os materiais utilizados pela empresa',
                funcionalidades: [
                    'Cadastro completo de novos materiais com validações',
                    'Edição e atualização de informações existentes',
                    'Sistema de categorização hierárquica (categoria principal → subcategoria)',
                    'Gestão de preços com histórico de alterações',
                    'Controle de fornecedores preferenciais por material',
                    'Sistema de códigos automáticos e personalizados',
                    'Controle de estoque mínimo e máximo por filial',
                    'Gestão de unidades de medida (unidade, kg, litros, metros)',
                    'Sistema de alertas para materiais com estoque baixo',
                    'Histórico completo de alterações e movimentações',
                    'Importação em lote via planilha Excel',
                    'Exportação de catálogo completo'
                ],
                instrucoes: [
                    '1. Acesse "Materiais" no menu "Cadastros" → "Materiais"',
                    '2. Para novo material: Clique em "Novo Material" (botão verde no canto superior direito)',
                    '3. Preencha campos obrigatórios: Código, Nome, Categoria, Unidade de Medida, Preço Unitário',
                    '4. Campos opcionais: Descrição detalhada, Fornecedor preferencial, Estoque mínimo/máximo',
                    '5. Selecione a categoria principal e subcategoria (ex: Eletrônicos → Computadores)',
                    '6. Configure estoque inicial por filial (se aplicável)',
                    '7. Adicione imagens do material (opcional) - formatos aceitos: JPG, PNG, até 2MB',
                    '8. Clique em "Salvar" para cadastrar ou "Salvar e Novo" para continuar cadastrando',
                    '9. Para editar: Use a busca por código/nome, clique no material e depois em "Editar"',
                    '10. Para inativar: Use o botão "Inativar" (não exclui, apenas desabilita)'
                ],
                dicas: [
                    'Padronize códigos: Use prefixos por categoria (ELET para eletrônicos, MOB para móveis)',
                    'Configure estoque mínimo = 20% do consumo mensal médio para alertas automáticos',
                    'Use descrições detalhadas para facilitar busca e identificação',
                    'Mantenha preços atualizados mensalmente para relatórios financeiros precisos',
                    'Configure fornecedores preferenciais para agilizar pedidos de compra',
                    'Use categorias para facilitar relatórios e análises por segmento',
                    'Importe materiais em lote usando a planilha modelo disponível no sistema',
                    'Configure alertas para materiais com estoque abaixo do mínimo'
                ],
                fluxoOperacional: [
                    'Identificação de necessidade → Criação do material → Configuração de parâmetros → Ativação no sistema',
                    'Manutenção regular → Atualização de preços → Revisão de categorias → Ajuste de estoques mínimos'
                ],
                camposObrigatorios: [
                    'Código do Material (único no sistema)',
                    'Nome/Descrição do Material',
                    'Categoria Principal',
                    'Unidade de Medida',
                    'Preço Unitário Padrão'
                ],
                camposOpcionais: [
                    'Subcategoria',
                    'Descrição Detalhada',
                    'Fornecedor Preferencial',
                    'Estoque Mínimo por Filial',
                    'Estoque Máximo por Filial',
                    'Imagem do Material',
                    'Observações Gerais'
                ]
            },
            'estoque': {
                titulo: 'Controle de Estoque',
                descricao: 'Sistema avançado de monitoramento, controle e gestão de estoque em tempo real por filial',
                funcionalidades: [
                    'Visualização de estoque atual em tempo real por filial',
                    'Controle granular de estoque por localização dentro da filial',
                    'Alertas automáticos de estoque baixo, crítico e zero',
                    'Histórico completo de movimentações com rastreabilidade',
                    'Relatórios de estoque com filtros avançados',
                    'Sistema de alertas por email e notificações push',
                    'Controle de estoque mínimo, máximo e ideal',
                    'Análise de rotatividade e obsolescência',
                    'Controle de lotes e datas de validade',
                    'Integração com inventários físicos',
                    'Dashboard de estoque com indicadores visuais',
                    'Exportação de dados para Excel e PDF'
                ],
                instrucoes: [
                    '1. Acesse "Estoque" no menu "Operacional" → "Controle de Estoque"',
                    '2. Selecione a filial desejada no filtro superior (padrão: filial do usuário logado)',
                    '3. Use os filtros avançados: Categoria, Status (ativo/inativo), Estoque (baixo/normal/alto)',
                    '4. Visualize o estoque atual de cada material na tabela principal',
                    '5. Para detalhes: Clique no nome do material para abrir modal com informações completas',
                    '6. Use a busca rápida digitando código ou nome do material',
                    '7. Para alertas: Configure estoque mínimo no cadastro do material',
                    '8. Para histórico: Clique em "Histórico" para ver todas as movimentações',
                    '9. Para relatórios: Use os botões de exportação (Excel/PDF)',
                    '10. Para inventário: Use o botão "Iniciar Inventário" para contagem física'
                ],
                dicas: [
                    'Monitore diariamente os níveis de estoque através do dashboard principal',
                    'Configure alertas para estoque mínimo = 20% do consumo mensal médio',
                    'Mantenha histórico de movimentações para auditoria e análise de tendências',
                    'Use os filtros por categoria para análises segmentadas',
                    'Configure notificações por email para estoque baixo e crítico',
                    'Realize inventários físicos mensalmente para reconciliação',
                    'Use o sistema de lotes para materiais com validade',
                    'Monitore a rotatividade para identificar materiais obsoletos',
                    'Configure estoque máximo para evitar excessos e custos desnecessários',
                    'Use as localizações para organizar estoque por setor/área'
                ],
                fluxoOperacional: [
                    'Entrada no sistema → Verificação de alertas → Análise de estoque → Ação corretiva se necessário',
                    'Monitoramento contínuo → Identificação de tendências → Planejamento de reposição → Execução de pedidos'
                ],
                indicadoresImportantes: [
                    'Estoque Atual vs. Estoque Mínimo',
                    'Valor Total em Estoque por Filial',
                    'Quantidade de Materiais com Estoque Baixo',
                    'Rotatividade de Materiais (FEFO - First Expired, First Out)',
                    'Custo Médio de Estoque',
                    'Índice de Giro de Estoque'
                ],
                alertasSistema: [
                    'Estoque Baixo: Abaixo do mínimo configurado',
                    'Estoque Crítico: Abaixo de 10% do mínimo',
                    'Estoque Zero: Material sem estoque',
                    'Estoque Alto: Acima do máximo configurado',
                    'Material Obsoleto: Sem movimentação há mais de 6 meses'
                ]
            },
            'movimentacoes': {
                titulo: 'Movimentações de Estoque',
                descricao: 'Sistema completo de controle e rastreabilidade de todas as movimentações de estoque',
                funcionalidades: [
                    'Registro de entradas de materiais (compras, doações, devoluções)',
                    'Controle de saídas (consumo, venda, transferência)',
                    'Transferências entre filiais com controle de custos',
                    'Ajustes de inventário (correções, perdas, danos)',
                    'Histórico completo com rastreabilidade total',
                    'Sistema de aprovação para movimentações de alto valor',
                    'Controle de lotes e datas de validade',
                    'Integração com pedidos de compra e vendas',
                    'Relatórios de movimentações por período e tipo',
                    'Controle de custos médios e impacto financeiro',
                    'Sistema de alertas para movimentações anômalas',
                    'Exportação de dados para auditoria externa'
                ],
                instrucoes: [
                    '1. Acesse "Movimentações" no menu "Operacional" → "Movimentações de Estoque"',
                    '2. Para nova movimentação: Clique em "Nova Movimentação" (botão azul no canto superior direito)',
                    '3. Selecione o tipo de movimentação: Entrada, Saída, Transferência, Ajuste',
                    '4. Preencha dados obrigatórios: Filial, Material, Quantidade, Data, Motivo',
                    '5. Para entradas: Informe fornecedor, número da nota fiscal, valor unitário',
                    '6. Para saídas: Selecione destino (consumo interno, venda, transferência)',
                    '7. Para transferências: Selecione filial origem e destino, confirme custos',
                    '8. Para ajustes: Documente motivo (inventário, perda, correção)',
                    '9. Adicione observações detalhadas para auditoria',
                    '10. Clique em "Confirmar" para executar a movimentação',
                    '11. Para histórico: Use filtros por período, tipo, material ou filial',
                    '12. Para relatórios: Use botões de exportação (Excel/PDF)'
                ],
                dicas: [
                    'Sempre documente o motivo das movimentações para auditoria e controle',
                    'Use transferências para movimentar entre filiais (não crie saída + entrada)',
                    'Mantenha histórico para auditoria, controle e análise de tendências',
                    'Configure aprovações para movimentações acima de R$ 1.000,00',
                    'Use o sistema de lotes para materiais com validade (FEFO)',
                    'Monitore custos médios após cada movimentação',
                    'Configure alertas para movimentações anômalas (quantidades muito altas)',
                    'Realize conciliação mensal entre sistema e inventário físico',
                    'Use códigos de motivo padronizados para facilitar relatórios',
                    'Mantenha backup das movimentações críticas'
                ],
                fluxoOperacional: [
                    'Identificação da necessidade → Seleção do tipo → Preenchimento de dados → Aprovação (se necessário) → Execução → Confirmação',
                    'Controle contínuo → Análise de movimentações → Identificação de padrões → Otimização de processos'
                ],
                tiposMovimentacao: [
                    'Entrada: Compra, Doação, Devolução, Ajuste positivo',
                    'Saída: Consumo interno, Venda, Transferência, Ajuste negativo',
                    'Transferência: Entre filiais (origem → destino)',
                    'Ajuste: Correção de inventário, perda, dano, obsolescência'
                ],
                camposObrigatorios: [
                    'Tipo de Movimentação',
                    'Filial (origem e/ou destino)',
                    'Material',
                    'Quantidade',
                    'Data da Movimentação',
                    'Motivo/Justificativa'
                ],
                camposOpcionais: [
                    'Fornecedor (para entradas)',
                    'Número da Nota Fiscal',
                    'Valor Unitário',
                    'Lote (se aplicável)',
                    'Data de Validade',
                    'Observações Detalhadas',
                    'Documentos Anexos'
                ]
            },
            'pedidos-compra': {
                titulo: 'Pedidos de Compra',
                descricao: 'Sistema integrado de gestão de compras com controle completo do ciclo de vida dos pedidos',
                funcionalidades: [
                    'Criação de pedidos de compra com múltiplos itens',
                    'Sistema de aprovação hierárquica (solicitante → aprovador → comprador)',
                    'Controle de fornecedores com histórico de preços e performance',
                    'Acompanhamento de status em tempo real (pendente → aprovado → em produção → enviado → recebido)',
                    'Gestão de preços com histórico de alterações e comparações',
                    'Integração com controle de estoque e movimentações',
                    'Sistema de alertas para prazos de entrega e aprovações pendentes',
                    'Relatórios de compras por período, fornecedor e material',
                    'Controle de orçamentos e limites de compra por usuário/filial',
                    'Sistema de cotação automática entre fornecedores',
                    'Gestão de contratos e condições comerciais',
                    'Exportação de dados para análise e auditoria'
                ],
                instrucoes: [
                    '1. Acesse "Pedidos de Compra" no menu "Compras" → "Pedidos de Compra"',
                    '2. Para novo pedido: Clique em "Novo Pedido" (botão verde no canto superior direito)',
                    '3. Preencha dados básicos: Filial, Fornecedor, Data de Entrega Prevista, Prioridade',
                    '4. Adicione materiais: Clique em "Adicionar Material" e selecione do catálogo',
                    '5. Para cada material: Informe quantidade, preço unitário, observações específicas',
                    '6. Configure prioridade: Baixa (7 dias), Média (5 dias), Alta (3 dias), Urgente (1 dia)',
                    '7. Adicione observações gerais e instruções para o fornecedor',
                    '8. Clique em "Salvar Rascunho" para guardar ou "Enviar para Aprovação"',
                    '9. Para aprovação: Aprovador recebe notificação e pode aprovar/rejeitar com justificativa',
                    '10. Após aprovação: Pedido é enviado automaticamente para o fornecedor',
                    '11. Acompanhe status: Use filtros por status, fornecedor, filial ou período',
                    '12. Para recebimento: Quando mercadoria chegar, marque como "Recebido"'
                ],
                dicas: [
                    'Sempre compare preços entre fornecedores antes de criar o pedido',
                    'Mantenha histórico de preços para negociações futuras e análise de tendências',
                    'Use o sistema de aprovação para controle de gastos e compliance',
                    'Configure alertas para prazos de entrega (3 dias antes do vencimento)',
                    'Use prioridades adequadas para evitar pedidos urgentes desnecessários',
                    'Mantenha observações detalhadas para facilitar aprovação e execução',
                    'Configure limites de compra por usuário para controle de gastos',
                    'Use o sistema de cotação para materiais de alto valor (acima de R$ 5.000,00)',
                    'Monitore performance dos fornecedores (prazo, qualidade, preço)',
                    'Realize análise mensal de compras para identificar oportunidades de economia'
                ],
                fluxoOperacional: [
                    'Identificação da necessidade → Criação do pedido → Aprovação → Envio ao fornecedor → Acompanhamento → Recebimento → Entrada no estoque',
                    'Gestão contínua → Análise de performance → Otimização de processos → Renegociação de contratos'
                ],
                statusPedido: [
                    'Pendente: Aguardando aprovação',
                    'Aprovado: Aprovado pelo responsável, aguardando envio ao fornecedor',
                    'Em Produção: Fornecedor confirmou e está produzindo/enviando',
                    'Enviado: Mercadoria foi enviada pelo fornecedor',
                    'Recebido: Mercadoria foi recebida e entrada no estoque',
                    'Cancelado: Pedido foi cancelado (não pode ser cancelado após "Enviado")'
                ],
                camposObrigatorios: [
                    'Filial solicitante',
                    'Fornecedor',
                    'Data de Entrega Prevista',
                    'Materiais (pelo menos um)',
                    'Quantidades',
                    'Preços unitários'
                ],
                camposOpcionais: [
                    'Prioridade',
                    'Observações gerais',
                    'Observações por material',
                    'Condições comerciais',
                    'Forma de pagamento',
                    'Documentos anexos'
                ],
                niveisAprovacao: [
                    'Usuário solicitante: Pode criar e editar pedidos',
                    'Aprovador: Pode aprovar/rejeitar pedidos (configurado por filial)',
                    'Comprador: Pode enviar pedidos aprovados para fornecedores',
                    'Recebedor: Pode marcar pedidos como recebidos'
                ]
            },
            'inventario': {
                titulo: 'Controle de Inventário',
                descricao: 'Sistema avançado de inventário físico com controle de divergências e reconciliação automática',
                funcionalidades: [
                    'Criação de inventários programados e emergenciais',
                    'Contagem física de materiais com dispositivos móveis',
                    'Reconciliação automática com sistema de estoque',
                    'Controle de divergências com análise de causas',
                    'Relatórios de inventário com indicadores de precisão',
                    'Sistema de alertas para inventários vencidos',
                    'Controle de usuários contadores e supervisores',
                    'Gestão de lotes e datas de validade',
                    'Integração com movimentações de ajuste',
                    'Sistema de aprovação para ajustes de alto valor',
                    'Histórico completo de inventários realizados',
                    'Exportação de dados para auditoria externa'
                ],
                instrucoes: [
                    '1. Acesse "Inventário" no menu "Operacional" → "Controle de Inventário"',
                    '2. Para novo inventário: Clique em "Novo Inventário" (botão verde no canto superior direito)',
                    '3. Configure dados básicos: Filial, Tipo (programado/emergencial), Período, Responsável',
                    '4. Selecione materiais: Todos os materiais da filial ou apenas categorias específicas',
                    '5. Defina equipe: Adicione usuários contadores e supervisores',
                    '6. Configure alertas: Estoque mínimo, materiais críticos, divergências significativas',
                    '7. Inicie o inventário: Sistema gera lista de materiais para contagem',
                    '8. Realize contagem física: Use dispositivos móveis ou planilhas de contagem',
                    '9. Para cada material: Informe quantidade contada, observações, fotos (se necessário)',
                    '10. Sistema calcula automaticamente divergências (contado vs. sistema)',
                    '11. Analise divergências: Identifique causas (perda, erro de sistema, roubo, etc.)',
                    '12. Aprove ajustes: Supervisor aprova ajustes acima de R$ 500,00',
                    '13. Finalize inventário: Sistema gera relatório final com indicadores de precisão'
                ],
                dicas: [
                    'Realize inventários programados mensalmente para materiais de alto valor',
                    'Documente todas as divergências encontradas com fotos e descrições detalhadas',
                    'Use o sistema para controle e auditoria, nunca faça ajustes manuais no estoque',
                    'Configure inventários por categoria para facilitar execução (ex: eletrônicos, móveis, papelaria)',
                    'Use dispositivos móveis para contagem em campo (tablets, smartphones)',
                    'Configure alertas para divergências acima de 10% em valor ou quantidade',
                    'Realize inventários emergenciais quando houver suspeita de perda ou roubo',
                    'Mantenha equipe treinada para contagem precisa e identificação de materiais',
                    'Use códigos de barras/QR Code para agilizar contagem e reduzir erros',
                    'Configure inventários rotativos (materiais diferentes a cada mês) para cobertura anual'
                ],
                fluxoOperacional: [
                    'Planejamento → Criação → Execução → Contagem → Análise de divergências → Aprovação → Ajustes → Finalização',
                    'Controle contínuo → Análise de tendências → Identificação de problemas → Implementação de melhorias'
                ],
                tiposInventario: [
                    'Programado: Mensal, trimestral ou anual conforme política da empresa',
                    'Emergencial: Quando há suspeita de perda, roubo ou erro significativo',
                    'Por Categoria: Materiais específicos (eletrônicos, móveis, etc.)',
                    'Por Localização: Área específica da filial (almoxarifado, setor, etc.)',
                    'Rotativo: Materiais diferentes a cada período para cobertura anual'
                ],
                statusInventario: [
                    'Em Planejamento: Inventário criado, aguardando início',
                    'Em Execução: Contagem em andamento',
                    'Em Análise: Contagem concluída, analisando divergências',
                    'Aguardando Aprovação: Ajustes pendentes de aprovação',
                    'Finalizado: Inventário concluído com relatório gerado',
                    'Cancelado: Inventário cancelado por motivo específico'
                ],
                indicadoresPrecisao: [
                    'Precisão Geral: % de materiais sem divergência',
                    'Precisão por Valor: % de valor em estoque sem divergência',
                    'Divergências por Categoria: Análise segmentada',
                    'Tendência de Precisão: Evolução ao longo do tempo',
                    'Tempo de Execução: Eficiência do processo'
                ]
            },
            'fornecedores': {
                titulo: 'Gestão de Fornecedores',
                descricao: 'Sistema completo de gestão de fornecedores com controle de performance e histórico de relacionamento',
                funcionalidades: [
                    'Cadastro completo de fornecedores com validações',
                    'Controle de informações cadastrais e fiscais',
                    'Histórico completo de compras e performance',
                    'Sistema de avaliação e classificação de fornecedores',
                    'Controle de contratos e condições comerciais',
                    'Gestão de contatos e responsáveis por fornecedor',
                    'Sistema de alertas para contratos vencendo',
                    'Relatórios de performance por fornecedor',
                    'Controle de documentos (certificados, alvarás, etc.)',
                    'Sistema de blacklist para fornecedores problemáticos',
                    'Integração com pedidos de compra e movimentações',
                    'Exportação de dados para análise e auditoria'
                ],
                instrucoes: [
                    '1. Acesse "Fornecedores" no menu "Cadastros" → "Fornecedores"',
                    '2. Para novo fornecedor: Clique em "Novo Fornecedor" (botão verde no canto superior direito)',
                    '3. Preencha dados cadastrais: Razão Social, Nome Fantasia, CNPJ, Inscrição Estadual',
                    '4. Informações de contato: Endereço completo, telefone, email, site',
                    '5. Dados fiscais: Regime tributário, CNAE, responsável fiscal',
                    '6. Contatos: Adicione responsáveis por vendas, financeiro, técnico',
                    '7. Condições comerciais: Prazo de entrega, forma de pagamento, desconto',
                    '8. Categorias: Selecione categorias de materiais que o fornecedor atende',
                    '9. Documentos: Anexe certificados, alvarás, ISO, etc.',
                    '10. Avaliação inicial: Classifique qualidade, prazo, preço, atendimento',
                    '11. Clique em "Salvar" para cadastrar ou "Salvar e Novo" para continuar',
                    '12. Para editar: Use busca por CNPJ/nome, clique no fornecedor e depois em "Editar"',
                    '13. Para inativar: Use o botão "Inativar" (não exclui, apenas desabilita)'
                ],
                dicas: [
                    'Mantenha dados sempre atualizados, especialmente CNPJ e inscrições',
                    'Avalie fornecedores mensalmente baseado em performance real',
                    'Documente contratos e condições para evitar problemas futuros',
                    'Configure alertas para contratos vencendo (30 dias antes)',
                    'Use sistema de classificação: A (excelente), B (bom), C (regular), D (ruim)',
                    'Monitore performance: Prazo de entrega, qualidade, preço, atendimento',
                    'Mantenha histórico de problemas e soluções para cada fornecedor',
                    'Configure fornecedores preferenciais por categoria de material',
                    'Use sistema de blacklist para fornecedores com problemas recorrentes',
                    'Realize auditoria anual de fornecedores ativos'
                ],
                fluxoOperacional: [
                    'Identificação de necessidade → Cadastro → Avaliação inicial → Contrato → Acompanhamento → Avaliação contínua → Renovação/Substituição',
                    'Gestão contínua → Monitoramento de performance → Identificação de problemas → Ações corretivas → Melhoria do relacionamento'
                ],
                camposObrigatorios: [
                    'Razão Social',
                    'Nome Fantasia',
                    'CNPJ (válido e único)',
                    'Endereço completo',
                    'Telefone de contato',
                    'Email principal'
                ],
                camposOpcionais: [
                    'Inscrição Estadual',
                    'Inscrição Municipal',
                    'Regime Tributário',
                    'CNAE',
                    'Site institucional',
                    'Responsável fiscal',
                    'Condições comerciais',
                    'Documentos anexos'
                ],
                sistemaAvaliacao: [
                    'Qualidade: % de materiais sem devolução',
                    'Prazo: % de entregas no prazo',
                    'Preço: Comparação com mercado',
                    'Atendimento: Tempo de resposta e solução de problemas',
                    'Classificação geral: Média ponderada dos critérios'
                ],
                tiposContrato: [
                    'Contrato anual com renovação automática',
                    'Contrato por projeto específico',
                    'Contrato por categoria de material',
                    'Acordo de fornecimento contínuo',
                    'Contrato emergencial para situações específicas'
                ]
            },
            'filiais': {
                titulo: 'Gestão de Filiais',
                descricao: 'Sistema centralizado de gestão de filiais com controle de estoque, usuários e operações por unidade',
                funcionalidades: [
                    'Cadastro completo de filiais com validações',
                    'Controle de endereços e informações de contato',
                    'Gestão de usuários por filial com permissões específicas',
                    'Controle de estoque independente por filial',
                    'Relatórios consolidados e por filial',
                    'Sistema de hierarquia entre filiais (matriz e filiais)',
                    'Controle de centros de custo por filial',
                    'Gestão de responsáveis e supervisores por filial',
                    'Sistema de alertas para filiais com problemas',
                    'Integração com movimentações e transferências',
                    'Controle de inventários por filial',
                    'Exportação de dados para análise e auditoria'
                ],
                instrucoes: [
                    '1. Acesse "Filiais" no menu "Cadastros" → "Filiais"',
                    '2. Para nova filial: Clique em "Nova Filial" (botão verde no canto superior direito)',
                    '3. Preencha dados básicos: Nome da Filial, Código, Tipo (matriz/filial)',
                    '4. Informações de contato: Endereço completo, telefone, email, responsável',
                    '5. Configurações operacionais: Centro de custo, região, timezone',
                    '6. Usuários: Adicione usuários que trabalham nesta filial',
                    '7. Permissões: Configure permissões específicas por usuário/filial',
                    '8. Estoque: Configure estoque inicial para materiais existentes',
                    '9. Responsáveis: Defina supervisores e responsáveis por área',
                    '10. Configurações avançadas: Horário de funcionamento, feriados locais',
                    '11. Clique em "Salvar" para cadastrar ou "Salvar e Novo" para continuar',
                    '12. Para editar: Use busca por código/nome, clique na filial e depois em "Editar"',
                    '13. Para inativar: Use o botão "Inativar" (não exclui, apenas desabilita)',
                    '14. Para estoque: Configure estoque inicial através do menu "Estoque" → "Configuração por Filial"'
                ],
                dicas: [
                    'Configure corretamente as permissões por filial para controle de acesso',
                    'Mantenha endereços sempre atualizados para relatórios e logística',
                    'Use o sistema para controle centralizado com operação descentralizada',
                    'Configure centros de custo para análise financeira por filial',
                    'Defina responsáveis claros para cada filial e área',
                    'Configure estoque mínimo e máximo específicos por filial',
                    'Use sistema de hierarquia para transferências entre filiais',
                    'Monitore performance de cada filial através de relatórios específicos',
                    'Configure alertas para filiais com estoque baixo ou problemas operacionais',
                    'Realize auditoria trimestral de configurações e permissões por filial'
                ],
                fluxoOperacional: [
                    'Identificação de necessidade → Criação da filial → Configuração de usuários → Configuração de estoque → Ativação → Operação → Monitoramento',
                    'Gestão contínua → Análise de performance → Identificação de problemas → Ações corretivas → Otimização de processos'
                ],
                tiposFilial: [
                    'Matriz: Filial principal com controle central',
                    'Filial: Unidade operacional com autonomia limitada',
                    'Depósito: Unidade apenas para armazenamento',
                    'Loja: Unidade para atendimento ao cliente',
                    'Escritório: Unidade administrativa sem estoque'
                ],
                camposObrigatorios: [
                    'Nome da Filial',
                    'Código da Filial (único)',
                    'Tipo de Filial',
                    'Endereço completo',
                    'Responsável principal',
                    'Centro de custo'
                ],
                camposOpcionais: [
                    'Telefone de contato',
                    'Email institucional',
                    'Site da filial',
                    'Horário de funcionamento',
                    'Feriados locais',
                    'Observações gerais',
                    'Documentos anexos'
                ],
                niveisPermissao: [
                    'Administrador: Acesso total ao sistema',
                    'Gerente de Filial: Controle total da filial específica',
                    'Supervisor: Controle de operações da filial',
                    'Operador: Execução de operações básicas',
                    'Visualizador: Apenas consultas e relatórios'
                ]
            },
            'usuarios': {
                titulo: 'Gestão de Usuários',
                descricao: 'Sistema avançado de controle de acesso com perfis, permissões e auditoria de atividades',
                funcionalidades: [
                    'Cadastro completo de usuários com validações',
                    'Controle de perfis predefinidos e personalizados',
                    'Gestão granular de permissões por módulo e ação',
                    'Controle de acesso por filial e horário',
                    'Histórico completo de atividades e acessos',
                    'Sistema de autenticação segura com senhas criptografadas',
                    'Controle de sessões e timeouts automáticos',
                    'Sistema de recuperação de senha por email',
                    'Integração com controle de filiais',
                    'Relatórios de acesso e atividades por usuário',
                    'Sistema de alertas para acessos suspeitos',
                    'Exportação de dados para auditoria externa'
                ],
                instrucoes: [
                    '1. Acesse "Usuários" no menu "Administrativo" → "Gestão de Usuários"',
                    '2. Para novo usuário: Clique em "Novo Usuário" (botão verde no canto superior direito)',
                    '3. Preencha dados pessoais: Nome completo, CPF, email, telefone',
                    '4. Dados de acesso: Login (único), senha inicial, confirmação de senha',
                    '5. Filial: Selecione a filial onde o usuário trabalha',
                    '6. Perfil: Selecione perfil predefinido ou crie perfil personalizado',
                    '7. Permissões: Configure permissões específicas por módulo (visualizar, inserir, editar, excluir)',
                    '8. Configurações de segurança: Força de senha, expiração, tentativas de login',
                    '9. Horário de acesso: Configure horários permitidos para acesso',
                    '10. Configurações avançadas: Timeout de sessão, IPs permitidos',
                    '11. Clique em "Salvar" para cadastrar ou "Salvar e Novo" para continuar',
                    '12. Para editar: Use busca por CPF/login, clique no usuário e depois em "Editar"',
                    '13. Para inativar: Use o botão "Inativar" (não exclui, apenas desabilita)',
                    '14. Para reset de senha: Use "Resetar Senha" e envie nova senha por email'
                ],
                dicas: [
                    'Use perfis predefinidos para facilitar gestão de permissões (Administrador, Gerente, Operador)',
                    'Mantenha senhas seguras: mínimo 8 caracteres, com letras, números e símbolos',
                    'Monitore atividades dos usuários através de relatórios de acesso',
                    'Configure expiração de senha a cada 90 dias para segurança',
                    'Use sistema de perfis para grupos de usuários com necessidades similares',
                    'Configure timeouts de sessão para usuários que esquecem de fazer logout',
                    'Monitore tentativas de login falhadas para identificar tentativas de invasão',
                    'Configure alertas para acessos fora do horário permitido',
                    'Realize auditoria mensal de permissões e acessos',
                    'Mantenha backup das configurações de usuários críticos'
                ],
                fluxoOperacional: [
                    'Identificação de necessidade → Criação do usuário → Configuração de perfil → Definição de permissões → Ativação → Monitoramento → Manutenção',
                    'Gestão contínua → Análise de acessos → Identificação de problemas → Ações corretivas → Otimização de segurança'
                ],
                perfisPredefinidos: [
                    'Administrador: Acesso total ao sistema, pode criar/editar usuários e perfis',
                    'Gerente: Acesso total à filial específica, pode gerenciar operações',
                    'Supervisor: Controle de operações da filial, pode aprovar movimentações',
                    'Operador: Execução de operações básicas (movimentações, consultas)',
                    'Visualizador: Apenas consultas e relatórios, sem alterações'
                ],
                camposObrigatorios: [
                    'Nome completo',
                    'CPF (válido e único)',
                    'Email (válido e único)',
                    'Login (único no sistema)',
                    'Senha inicial',
                    'Filial de trabalho'
                ],
                camposOpcionais: [
                    'Telefone de contato',
                    'Endereço residencial',
                    'Data de nascimento',
                    'Cargo/função',
                    'Departamento',
                    'Observações gerais',
                    'Foto do usuário'
                ],
                niveisPermissao: [
                    'Visualizar: Pode ver dados mas não alterar',
                    'Inserir: Pode criar novos registros',
                    'Editar: Pode modificar registros existentes',
                    'Excluir: Pode remover registros (com confirmação)',
                    'Aprovar: Pode aprovar operações que requerem autorização'
                ],
                configuracoesSeguranca: [
                    'Força de senha: Mínimo 8 caracteres, com letras, números e símbolos',
                    'Expiração de senha: 90 dias por padrão',
                    'Tentativas de login: Máximo 3 tentativas antes de bloqueio',
                    'Timeout de sessão: 30 minutos de inatividade',
                    'IPs permitidos: Restrição de acesso por endereço IP (opcional)'
                ]
            },
            'relatorios': {
                titulo: 'Relatórios e Análises',
                descricao: 'Sistema completo de relatórios com análises avançadas, gráficos interativos e exportação para múltiplos formatos',
                funcionalidades: [
                    'Relatórios de estoque com indicadores visuais',
                    'Análises de movimentações com gráficos de tendência',
                    'Relatórios financeiros com análise de custos e valores',
                    'Relatórios de compras com performance de fornecedores',
                    'Exportação de dados para Excel, PDF e CSV',
                    'Relatórios automáticos com agendamento',
                    'Dashboard executivo com KPIs principais',
                    'Análise de curva ABC para classificação de materiais',
                    'Relatórios de inventário com indicadores de precisão',
                    'Análise de rotatividade e obsolescência',
                    'Relatórios consolidados por filial e período',
                    'Sistema de alertas para indicadores críticos'
                ],
                instrucoes: [
                    '1. Acesse "Relatórios" no menu principal → "Relatórios e Análises"',
                    '2. Selecione o tipo de relatório desejado na lista disponível',
                    '3. Configure filtros: Período, Filial, Categoria, Status, etc.',
                    '4. Para relatórios avançados: Configure parâmetros específicos (ex: curva ABC)',
                    '5. Clique em "Gerar Relatório" para processar os dados',
                    '6. Visualize o relatório na tela com opções de zoom e navegação',
                    '7. Use os controles de visualização: Gráfico, Tabela, Resumo',
                    '8. Para exportação: Clique em "Exportar" e escolha formato (Excel, PDF, CSV)',
                    '9. Para impressão: Use "Imprimir" para versão otimizada para papel',
                    '10. Para agendamento: Configure relatórios automáticos com "Agendar"',
                    '11. Para compartilhamento: Use "Compartilhar" para enviar por email',
                    '12. Para histórico: Acesse "Histórico de Relatórios" para versões anteriores'
                ],
                dicas: [
                    'Use filtros específicos para relatórios mais precisos e relevantes',
                    'Configure relatórios automáticos para análise regular (semanal/mensal)',
                    'Mantenha histórico de relatórios gerados para análise de tendências',
                    'Use gráficos interativos para identificar padrões e anomalias',
                    'Configure alertas para indicadores que ultrapassem limites críticos',
                    'Exporte dados para Excel para análises mais detalhadas e personalizadas',
                    'Use relatórios consolidados para visão executiva da empresa',
                    'Configure dashboards personalizados para diferentes níveis de usuário',
                    'Monitore performance através de relatórios de tendência',
                    'Use análise de curva ABC para otimização de estoque e compras'
                ],
                fluxoOperacional: [
                    'Seleção do relatório → Configuração de filtros → Geração → Visualização → Análise → Exportação/Compartilhamento',
                    'Configuração de agendamento → Geração automática → Notificação → Análise → Ação corretiva se necessário'
                ],
                tiposRelatorio: [
                    'Relatórios de Estoque: Posição atual, movimentações, alertas',
                    'Relatórios Financeiros: Valores em estoque, custos, análise de gastos',
                    'Relatórios de Compras: Performance de fornecedores, análise de preços',
                    'Relatórios de Inventário: Precisão, divergências, tendências',
                    'Relatórios de Movimentação: Entradas, saídas, transferências por período',
                    'Relatórios de Usuários: Acessos, atividades, permissões'
                ],
                filtrosDisponiveis: [
                    'Período: Hoje, semana, mês, trimestre, ano, personalizado',
                    'Filial: Todas as filiais ou filial específica',
                    'Categoria: Todas as categorias ou categoria específica',
                    'Status: Ativo, inativo, pendente, aprovado, etc.',
                    'Usuário: Todos os usuários ou usuário específico',
                    'Fornecedor: Todos os fornecedores ou fornecedor específico'
                ],
                formatosExportacao: [
                    'Excel (.xlsx): Para análises detalhadas e manipulação de dados',
                    'PDF (.pdf): Para impressão e compartilhamento formal',
                    'CSV (.csv): Para importação em outros sistemas',
                    'HTML: Para visualização em navegadores web',
                    'Impressão: Versão otimizada para papel'
                ],
                relatoriosAutomaticos: [
                    'Relatório diário de estoque baixo',
                    'Relatório semanal de movimentações',
                    'Relatório mensal de performance de fornecedores',
                    'Relatório trimestral de inventário',
                    'Relatório anual de análise de estoque'
                ]
            }
        };
        
        const manual = manuais[modulo] || {};
        
        // Gerar HTML do manual
        const html = `
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-primary">
                        <h6 class="alert-heading fw-bold mb-2">${manual.titulo}</h6>
                        <p class="mb-0">${manual.descricao}</p>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>Funcionalidades Principais
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.funcionalidades ? manual.funcionalidades.map(f => `<li><i class="bi bi-check-circle text-success me-2"></i>${f}</li>`).join('') : ''}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-lightbulb me-2"></i>Dicas Importantes
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.dicas ? manual.dicas.map(d => `<li><i class="bi bi-info-circle text-info me-2"></i>${d}</li>`).join('') : ''}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-list-ol me-2"></i>Instruções de Uso
                            </h6>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                ${manual.instrucoes ? manual.instrucoes.map(i => `<li>${i}</li>`).join('') : ''}
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            ${manual.fluxoOperacional ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-arrow-repeat me-2"></i>Fluxo Operacional
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.fluxoOperacional.map(f => `<li><i class="bi bi-arrow-right text-info me-2"></i>${f}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.camposObrigatorios ? `
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Campos Obrigatórios
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.camposObrigatorios.map(c => `<li><i class="bi bi-asterisk text-danger me-2"></i>${c}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-plus-circle me-2"></i>Campos Opcionais
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.camposOpcionais.map(c => `<li><i class="bi bi-circle text-secondary me-2"></i>${c}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.statusPedido || manual.statusInventario ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-list-check me-2"></i>Status e Classificações
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${(manual.statusPedido || manual.statusInventario || []).map(s => `<li><i class="bi bi-tag text-dark me-2"></i>${s}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.tiposInventario || manual.tiposFilial || manual.tiposMovimentacao ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-purple text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-collection me-2"></i>Tipos e Categorias
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${(manual.tiposInventario || manual.tiposFilial || manual.tiposMovimentacao || []).map(t => `<li><i class="bi bi-collection text-purple me-2"></i>${t}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.indicadoresPrecisao || manual.indicadoresImportantes ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>Indicadores e Métricas
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${(manual.indicadoresPrecisao || manual.indicadoresImportantes || []).map(i => `<li><i class="bi bi-graph-up text-success me-2"></i>${i}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.alertasSistema ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-bell me-2"></i>Sistema de Alertas
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.alertasSistema.map(a => `<li><i class="bi bi-bell text-warning me-2"></i>${a}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.niveisAprovacao || manual.niveisPermissao ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-shield-check me-2"></i>Níveis de Acesso e Permissões
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${(manual.niveisAprovacao || manual.niveisPermissao || []).map(n => `<li><i class="bi bi-shield-check text-info me-2"></i>${n}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.sistemaAvaliacao ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-star me-2"></i>Sistema de Avaliação
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.sistemaAvaliacao.map(a => `<li><i class="bi bi-star text-success me-2"></i>${a}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.tiposContrato ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>Tipos de Contrato
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.tiposContrato.map(t => `<li><i class="bi bi-file-earmark-text text-primary me-2"></i>${t}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.tiposRelatorio ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Tipos de Relatório
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.tiposRelatorio.map(t => `<li><i class="bi bi-file-earmark-bar-graph text-info me-2"></i>${t}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.filtrosDisponiveis ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i>Filtros Disponíveis
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.filtrosDisponiveis.map(f => `<li><i class="bi bi-funnel text-secondary me-2"></i>${f}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.formatosExportacao ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-download me-2"></i>Formatos de Exportação
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.formatosExportacao.map(f => `<li><i class="bi bi-download text-success me-2"></i>${f}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.relatoriosAutomaticos ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-clock me-2"></i>Relatórios Automáticos
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.relatoriosAutomaticos.map(r => `<li><i class="bi bi-clock text-warning me-2"></i>${r}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${manual.configuracoesSeguranca ? `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Configurações de Segurança
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                ${manual.configuracoesSeguranca.map(c => `<li><i class="bi bi-shield-lock text-danger me-2"></i>${c}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        `;
        
        conteudoManual.innerHTML = html;
    }

    // Função para imprimir manual
    function imprimirManual() {
        window.print();
    }
</script>

</body>
</html> 