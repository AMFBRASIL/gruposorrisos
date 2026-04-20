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
    <title><?php echo APP_NAME; ?> - Editar Filial</title>
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
                <span class="page-title"><i class="bi bi-building"></i>Editar Filial</span>
            </div>
            <div class="subtitle">Edite as informações da filial</div>
            
            <!-- Loading -->
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando dados da filial...</p>
            </div>
            
            <!-- Formulário -->
            <div id="formulario" class="card card-resumo" style="display: none;">
                <div class="card-body">
                    <form id="formFilial" class="needs-validation" novalidate>
                        <input type="hidden" id="id" name="id">
                        
                        <!-- Informações Básicas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Informações Básicas
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código da Filial *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                                <div class="invalid-feedback">Código é obrigatório</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome da Filial *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                                <div class="invalid-feedback">Nome é obrigatório</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="filial">Filial</option>
                                    <option value="matriz">Matriz</option>
                                </select>
                                <div class="invalid-feedback">Tipo é obrigatório</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Selecione...</option>
                                    <option value="ativa">Ativa</option>
                                    <option value="inativa">Inativa</option>
                                </select>
                                <div class="invalid-feedback">Status é obrigatório</div>
                            </div>
                        </div>

                        <!-- Informações Fiscais -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-file-earmark-text me-2"></i>Informações Fiscais
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cnpj" class="form-label">CNPJ</label>
                                <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                                <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual">
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-geo-alt me-2"></i>Endereço
                                </h5>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complemento" name="complemento">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="bairro" name="bairro">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">UF</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000">
                            </div>
                        </div>

                        <!-- Contato -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone me-2"></i>Contato
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <!-- Responsável -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-person me-2"></i>Responsável
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="responsavel" class="form-label">Nome do Responsável</label>
                                <input type="text" class="form-control" id="responsavel" name="responsavel">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email_responsavel" class="form-label">Email do Responsável</label>
                                <input type="email" class="form-control" id="email_responsavel" name="email_responsavel">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefone_responsavel" class="form-label">Telefone do Responsável</label>
                                <input type="text" class="form-control" id="telefone_responsavel" name="telefone_responsavel" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_abertura" class="form-label">Data de Abertura</label>
                                <input type="date" class="form-control" id="data_abertura" name="data_abertura">
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-chat-text me-2"></i>Observações
                                </h5>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='filiais.php'">
                                        <i class="bi bi-arrow-left me-1"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Atualizar Filial
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
<script src="assets/js/editFilial.js"></script>

</body>
</html> 