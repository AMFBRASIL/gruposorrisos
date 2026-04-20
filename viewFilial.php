<?php
// Incluir configurações
require_once 'config/config.php';
require_once 'config/session.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verificar se ID foi fornecido
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: filiais.php');
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
    <title><?php echo APP_NAME; ?> - Visualizar Filial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/materiais.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'menu.php'; ?>
        <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-building"></i>Visualizar Filial</span>
            </div>
            <div class="subtitle">Detalhes da filial</div>
            
            <!-- Loading -->
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando dados da filial...</p>
            </div>
            
            <!-- Conteúdo -->
            <div id="conteudo" style="display: none;">
                <!-- Cabeçalho -->
                <div class="card card-resumo mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1" id="nome-filial">Nome da Filial</h4>
                                <p class="text-muted mb-0" id="codigo-filial">Código: -</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge fs-6" id="status-badge">Status</span>
                                <span class="badge fs-6 ms-2" id="tipo-badge">Tipo</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informações -->
                <div class="row">
                    <!-- Informações Básicas -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações Básicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Código:</strong></div>
                                    <div class="col-8" id="codigo">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Nome:</strong></div>
                                    <div class="col-8" id="nome">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Tipo:</strong></div>
                                    <div class="col-8" id="tipo">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Status:</strong></div>
                                    <div class="col-8" id="status">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Data Abertura:</strong></div>
                                    <div class="col-8" id="data_abertura">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações Fiscais -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Informações Fiscais</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>CNPJ:</strong></div>
                                    <div class="col-8" id="cnpj">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Inscrição Estadual:</strong></div>
                                    <div class="col-8" id="inscricao_estadual">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Endereço -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Endereço</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Endereço:</strong></div>
                                    <div class="col-8" id="endereco">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Número:</strong></div>
                                    <div class="col-8" id="numero">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Complemento:</strong></div>
                                    <div class="col-8" id="complemento">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Bairro:</strong></div>
                                    <div class="col-8" id="bairro">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Cidade:</strong></div>
                                    <div class="col-8" id="cidade">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Estado:</strong></div>
                                    <div class="col-8" id="estado">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>CEP:</strong></div>
                                    <div class="col-8" id="cep">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contato -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Contato</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Telefone:</strong></div>
                                    <div class="col-8" id="telefone">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8" id="email">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Responsável -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Responsável</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Nome:</strong></div>
                                    <div class="col-8" id="responsavel">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8" id="email_responsavel">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Telefone:</strong></div>
                                    <div class="col-8" id="telefone_responsavel">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estatísticas -->
                    <div class="col-md-6 mb-4">
                        <div class="card card-resumo h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Estatísticas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Funcionários:</strong></div>
                                    <div class="col-8" id="total_funcionarios">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Criado em:</strong></div>
                                    <div class="col-8" id="created_at">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Última atualização:</strong></div>
                                    <div class="col-8" id="updated_at">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Observações -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card card-resumo">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-chat-text me-2"></i>Observações</h6>
                            </div>
                            <div class="card-body">
                                <p id="observacoes" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='filiais.php'">
                                <i class="bi bi-arrow-left me-1"></i> Voltar
                            </button>
                            <button type="button" class="btn btn-primary" id="btn-editar">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Erro -->
            <div id="erro" class="text-center py-5" style="display: none;">
                <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                <p class="mt-2 text-danger" id="mensagem-erro">Erro ao carregar filial</p>
                <button class="btn btn-primary" onclick="window.location.href='filiais.php'">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </button>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/viewFilial.js"></script>

</body>
</html> 