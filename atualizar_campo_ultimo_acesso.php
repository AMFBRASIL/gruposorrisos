<?php
/**
 * Script para adicionar o campo ultimo_acesso na tabela tbl_usuarios
 * Execute este script apenas uma vez para garantir que o campo exista
 */

require_once 'config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Atualização de Estrutura - Sistema de Estoque</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .card { max-width: 800px; margin: 0 auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .log-item { padding: 8px; margin: 4px 0; border-left: 4px solid #0d6efd; background: #f8f9fa; }
        .log-success { border-left-color: #198754; }
        .log-error { border-left-color: #dc3545; background: #f8d7da; }
        .log-warning { border-left-color: #ffc107; background: #fff3cd; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h4 class='mb-0'><i class='bi bi-database-gear'></i> Atualização de Estrutura do Banco de Dados</h4>
            </div>
            <div class='card-body'>
                <h5>Adicionando campo ultimo_acesso na tabela tbl_usuarios</h5>
                <hr>
                <div class='logs'>";
    
    // Verificar se o campo já existe
    echo "<div class='log-item'>Verificando existência do campo ultimo_acesso...</div>";
    
    $query = "SELECT COUNT(*) as count 
              FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE table_schema = DATABASE() 
              AND table_name = 'tbl_usuarios' 
              AND column_name = 'ultimo_acesso'";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "<div class='log-item log-warning'>⚠️ Campo 'ultimo_acesso' já existe na tabela tbl_usuarios.</div>";
    } else {
        echo "<div class='log-item'>Campo 'ultimo_acesso' não encontrado. Adicionando...</div>";
        
        // Adicionar o campo
        $alterQuery = "ALTER TABLE tbl_usuarios ADD COLUMN ultimo_acesso timestamp NULL AFTER ativo";
        $pdo->exec($alterQuery);
        
        echo "<div class='log-item log-success'>✓ Campo 'ultimo_acesso' adicionado com sucesso!</div>";
    }
    
    // Verificar se o índice já existe
    echo "<div class='log-item'>Verificando índice idx_ultimo_acesso...</div>";
    
    $query = "SELECT COUNT(*) as count 
              FROM INFORMATION_SCHEMA.STATISTICS 
              WHERE table_schema = DATABASE() 
              AND table_name = 'tbl_usuarios' 
              AND index_name = 'idx_ultimo_acesso'";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "<div class='log-item log-warning'>⚠️ Índice 'idx_ultimo_acesso' já existe.</div>";
    } else {
        echo "<div class='log-item'>Índice 'idx_ultimo_acesso' não encontrado. Adicionando...</div>";
        
        // Adicionar o índice
        $indexQuery = "ALTER TABLE tbl_usuarios ADD INDEX idx_ultimo_acesso (ultimo_acesso)";
        $pdo->exec($indexQuery);
        
        echo "<div class='log-item log-success'>✓ Índice 'idx_ultimo_acesso' adicionado com sucesso!</div>";
    }
    
    // Verificar estrutura atual
    echo "<div class='log-item'>Verificando estrutura atual da tabela...</div>";
    
    $query = "DESCRIBE tbl_usuarios";
    $stmt = $pdo->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='log-item log-success'>✓ Estrutura da tabela verificada com sucesso!</div>";
    echo "<div class='mt-3'><strong>Colunas da tabela tbl_usuarios:</strong></div>";
    echo "<table class='table table-sm table-bordered mt-2'>";
    echo "<thead><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Padrão</th></tr></thead><tbody>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<div class='alert alert-success mt-4'>
            <h5>✓ Atualização concluída com sucesso!</h5>
            <p class='mb-0'>O campo 'ultimo_acesso' agora está disponível na tabela tbl_usuarios e será exibido na tela de usuários.</p>
          </div>";
    
    echo "<div class='mt-3'>
            <a href='usuarios.php' class='btn btn-primary'>Ir para Tela de Usuários</a>
            <a href='dashboard.php' class='btn btn-secondary'>Voltar ao Dashboard</a>
          </div>";
    
    echo "</div></div></div></div></body></html>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Erro ao atualizar banco de dados</h5>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='mb-0'>Verifique as permissões do banco de dados e tente novamente.</p>";
    echo "</div>";
    echo "</div></div></div></body></html>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Erro inesperado</h5>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echo "</div></div></div></body></html>";
}
?>

