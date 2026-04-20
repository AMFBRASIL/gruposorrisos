<?php
/**
 * Exemplo de uso da Controller de Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Este arquivo demonstra como usar a controller de permissões
 * para controlar o acesso às páginas e funcionalidades
 */

require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário pode acessar esta página
if (!$controllerAcesso->verificarAcessoPagina()) {
    $controllerAcesso->redirecionarSemPermissao('Acesso negado a esta página');
}

// Registrar acesso à página
$controllerAcesso->registrarAcessoPagina();

// Verificar permissões específicas para ações
$podeInserir = $controllerAcesso->podeExecutarAcao('inserir');
$podeEditar = $controllerAcesso->podeExecutarAcao('editar');
$podeExcluir = $controllerAcesso->podeExecutarAcao('excluir');

// Obter botões permitidos
$botoesPermitidos = $controllerAcesso->obterBotoesPermitidos();

// Obter permissões resumidas
$permissoesResumidas = $controllerAcesso->obterPermissoesResumidas();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplo de Uso da Controller | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <h1>Exemplo de Uso da Controller de Permissões</h1>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Permissões do Usuário</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Pode Inserir:</strong> <?= $podeInserir ? 'Sim' : 'Não' ?></p>
                            <p><strong>Pode Editar:</strong> <?= $podeEditar ? 'Sim' : 'Não' ?></p>
                            <p><strong>Pode Excluir:</strong> <?= $podeExcluir ? 'Sim' : 'Não' ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Botões Permitidos</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Botões disponíveis:</strong></p>
                            <ul>
                                <?php foreach ($botoesPermitidos as $botao): ?>
                                    <li><?= ucfirst($botao) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Resumo de Permissões</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Total de páginas:</strong> <?= $permissoesResumidas['total_paginas'] ?></p>
                            <p><strong>Pode inserir em:</strong> <?= $permissoesResumidas['pode_inserir'] ?> páginas</p>
                            <p><strong>Pode editar em:</strong> <?= $permissoesResumidas['pode_editar'] ?> páginas</p>
                            <p><strong>Pode excluir em:</strong> <?= $permissoesResumidas['pode_excluir'] ?> páginas</p>
                            
                            <h6>Categorias disponíveis:</h6>
                            <ul>
                                <?php foreach ($permissoesResumidas['categorias'] as $categoria => $quantidade): ?>
                                    <li><?= ucfirst($categoria) ?>: <?= $quantidade ?> páginas</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Exemplos de Controle de Interface</h5>
                        </div>
                        <div class="card-body">
                            <!-- Botão Novo - só aparece se tiver permissão -->
                            <?php if ($controllerAcesso->deveMostrar('botao_novo')): ?>
                                <button class="btn btn-primary me-2">
                                    <i class="bi bi-plus-lg me-1"></i>Novo Registro
                                </button>
                            <?php endif; ?>
                            
                            <!-- Botão Editar - só aparece se tiver permissão -->
                            <?php if ($controllerAcesso->deveMostrar('botao_editar')): ?>
                                <button class="btn btn-warning me-2">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </button>
                            <?php endif; ?>
                            
                            <!-- Botão Excluir - só aparece se tiver permissão -->
                            <?php if ($controllerAcesso->deveMostrar('botao_excluir')): ?>
                                <button class="btn btn-danger me-2">
                                    <i class="bi bi-trash me-1"></i>Excluir
                                </button>
                            <?php endif; ?>
                            
                            <!-- Botões sempre permitidos -->
                            <button class="btn btn-info me-2">
                                <i class="bi bi-eye me-1"></i>Visualizar
                            </button>
                            <button class="btn btn-secondary me-2">
                                <i class="bi bi-download me-1"></i>Exportar
                            </button>
                            <button class="btn btn-dark">
                                <i class="bi bi-printer me-1"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Formulário Condicional</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($controllerAcesso->deveMostrar('formulario')): ?>
                                <form>
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nome" name="nome">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Você não tem permissão para inserir ou editar registros nesta página.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 