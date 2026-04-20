<?php
/**
 * Página de Logs de Erro - Sistema de Controle de Acesso
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Inicializar controller de acesso
$controllerAcesso = new ControllerAcesso();

// Verificar se o usuário tem acesso a esta página
$controllerAcesso->verificarEAutorizar('visualizar', 'logs-erros.php');

// Obter logs de erro
try {
    require_once 'config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Buscar logs de erro de acesso
    $sql = "SELECT l.*, u.nome_completo, u.email 
            FROM tbl_logs_sistema l 
            LEFT JOIN tbl_usuarios u ON l.id_usuario = u.id_usuario 
            WHERE l.acao LIKE 'ERRO_ACESSO_%' 
            ORDER BY l.data_criacao DESC 
            LIMIT 100";
    
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();
    
} catch (Exception $e) {
    $logs = [];
    $erro = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Erro | Grupo Sorrisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .log-card {
            border-left: 4px solid #dc3545;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .log-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        .log-card.warning {
            border-left-color: #ffc107;
        }
        .log-card.info {
            border-left-color: #17a2b8;
        }
        .log-card.success {
            border-left-color: #28a745;
        }
        .log-timestamp {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .log-details {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center mb-2">
                <span class="page-title"><i class="bi bi-exclamation-triangle"></i>Logs de Erro</span>
            </div>
            <div class="subtitle">Monitoramento de erros de acesso e permissões</div>
            
            <!-- Filtros -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-3">
                        <label for="filtroTipo" class="form-label">Tipo de Erro</label>
                        <select class="form-select" id="filtroTipo">
                            <option value="">Todos os tipos</option>
                            <option value="403">403 - Acesso Negado</option>
                            <option value="404">404 - Página Não Encontrada</option>
                            <option value="500">500 - Erro Interno</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtroUsuario" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="filtroUsuario" placeholder="Nome do usuário">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroData" class="form-label">Data</label>
                        <input type="date" class="form-control" id="filtroData">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="bi bi-funnel me-2"></i>Aplicar Filtros
                            </button>
                            <button class="btn btn-outline-secondary" onclick="limparFiltros()">
                                <i class="bi bi-x-circle me-2"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4><?= count(array_filter($logs, function($log) { return strpos($log['acao'], 'ERRO_ACESSO_403') !== false; })) ?></h4>
                            <p class="mb-0">Acessos Negados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?= count(array_filter($logs, function($log) { return strpos($log['acao'], 'ERRO_ACESSO_404') !== false; })) ?></h4>
                            <p class="mb-0">Páginas Não Encontradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?= count(array_filter($logs, function($log) { return strpos($log['acao'], 'ERRO_ACESSO_500') !== false; })) ?></h4>
                            <p class="mb-0">Erros Internos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h4><?= count($logs) ?></h4>
                            <p class="mb-0">Total de Erros</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Logs -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-list-ul me-2"></i>Histórico de Erros</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Nenhum erro registrado</h5>
                            <p class="text-muted">O sistema está funcionando perfeitamente!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $dados = json_decode($log['dados_novos'], true);
                            $codigo = $dados['codigo'] ?? 'N/A';
                            $tipo = $dados['tipo'] ?? 'error';
                            $mensagem = $dados['mensagem'] ?? 'Erro desconhecido';
                            $urlPagina = $dados['url_pagina'] ?? 'N/A';
                            $ipUsuario = $dados['ip_usuario'] ?? 'N/A';
                            
                            $classeCard = 'log-card';
                            if ($tipo === 'warning') $classeCard .= ' warning';
                            elseif ($tipo === 'info') $classeCard .= ' info';
                            elseif ($tipo === 'success') $classeCard .= ' success';
                            ?>
                            
                            <div class="card <?= $classeCard ?>" data-codigo="<?= $codigo ?>" data-usuario="<?= strtolower($log['nome_completo'] ?? '') ?>" data-data="<?= date('Y-m-d', strtotime($log['data_criacao'])) ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title">
                                                <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                                                Erro <?= $codigo ?> - <?= ucfirst($tipo) ?>
                                            </h6>
                                            <p class="card-text"><?= htmlspecialchars($mensagem) ?></p>
                                            <div class="log-timestamp">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('d/m/Y H:i:s', strtotime($log['data_criacao'])) ?>
                                                <?php if ($log['nome_completo']): ?>
                                                    • <i class="bi bi-person me-1"></i><?= htmlspecialchars($log['nome_completo']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-sm btn-outline-info" onclick="toggleDetails('<?= $log['id_log'] ?>')">
                                                <i class="bi bi-info-circle me-1"></i>Detalhes
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="copiarLog('<?= htmlspecialchars(json_encode($log)) ?>')">
                                                <i class="bi bi-clipboard me-1"></i>Copiar
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Detalhes expandíveis -->
                                    <div class="log-details" id="details-<?= $log['id_log'] ?>" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>URL:</strong> <?= htmlspecialchars($urlPagina) ?><br>
                                                <strong>IP:</strong> <?= htmlspecialchars($ipUsuario) ?><br>
                                                <strong>Usuário:</strong> <?= htmlspecialchars($log['nome_completo'] ?? 'N/A') ?><br>
                                                <strong>Email:</strong> <?= htmlspecialchars($log['email'] ?? 'N/A') ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Ação:</strong> <?= htmlspecialchars($log['acao']) ?><br>
                                                <strong>Tabela:</strong> <?= htmlspecialchars($log['tabela']) ?><br>
                                                <strong>IP Log:</strong> <?= htmlspecialchars($log['ip_usuario']) ?><br>
                                                <strong>ID Log:</strong> <?= $log['id_log'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Botões de ação -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Voltar ao Início
                    </a>
                    
                    <a href="teste_erros.php" class="btn btn-outline-warning">
                        <i class="bi bi-bug me-2"></i>Testar Erros
                    </a>
                    
                    <button class="btn btn-outline-danger" onclick="limparLogs()">
                        <i class="bi bi-trash me-2"></i>Limpar Logs Antigos
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para expandir/recolher detalhes
        function toggleDetails(idLog) {
            const details = document.getElementById('details-' + idLog);
            if (details.style.display === 'none') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
        
        // Função para copiar log para clipboard
        function copiarLog(dados) {
            try {
                navigator.clipboard.writeText(dados).then(() => {
                    alert('Log copiado para clipboard!');
                });
            } catch (err) {
                // Fallback para navegadores mais antigos
                const textArea = document.createElement('textarea');
                textArea.value = dados;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Log copiado para clipboard!');
            }
        }
        
        // Função para aplicar filtros
        function aplicarFiltros() {
            const codigo = document.getElementById('filtroTipo').value;
            const usuario = document.getElementById('filtroUsuario').value.toLowerCase();
            const data = document.getElementById('filtroData').value;
            
            const logs = document.querySelectorAll('.log-card');
            
            logs.forEach(log => {
                let mostrar = true;
                
                if (codigo && log.dataset.codigo !== codigo) {
                    mostrar = false;
                }
                
                if (usuario && !log.dataset.usuario.includes(usuario)) {
                    mostrar = false;
                }
                
                if (data && log.dataset.data !== data) {
                    mostrar = false;
                }
                
                log.style.display = mostrar ? 'block' : 'none';
            });
        }
        
        // Função para limpar filtros
        function limparFiltros() {
            document.getElementById('filtroTipo').value = '';
            document.getElementById('filtroUsuario').value = '';
            document.getElementById('filtroData').value = '';
            
            const logs = document.querySelectorAll('.log-card');
            logs.forEach(log => {
                log.style.display = 'block';
            });
        }
        
        // Função para limpar logs antigos
        function limparLogs() {
            if (confirm('Tem certeza que deseja limpar logs com mais de 30 dias? Esta ação não pode ser desfeita.')) {
                // Aqui você pode implementar a lógica para limpar logs antigos
                alert('Funcionalidade de limpeza será implementada em breve.');
            }
        }
        
        // Aplicar filtros automaticamente quando mudar valores
        document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroUsuario').addEventListener('input', aplicarFiltros);
        document.getElementById('filtroData').addEventListener('change', aplicarFiltros);
    </script>
</body>
</html> 