<?php
/**
 * Script para atualizar o campo ultimo_acesso dos usuários existentes
 * Define a data atual como último acesso para usuários que não têm esse campo preenchido
 */

require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Atualizar Último Acesso - Sistema de Estoque</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .card { max-width: 800px; margin: 0 auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .log-item { padding: 8px; margin: 4px 0; border-left: 4px solid #0d6efd; background: #f8f9fa; }
        .log-success { border-left-color: #198754; }
        .log-error { border-left-color: #dc3545; background: #f8d7da; }
        .log-warning { border-left-color: #ffc107; background: #fff3cd; }
        .log-info { border-left-color: #0dcaf0; background: #cff4fc; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h4 class='mb-0'><i class='bi bi-clock-history'></i> Atualizar Último Acesso dos Usuários</h4>
            </div>
            <div class='card-body'>
                <div class='logs'>";
    
    // Verificar quantos usuários não têm ultimo_acesso
    echo "<div class='log-item log-info'>📊 Verificando usuários sem último acesso registrado...</div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tbl_usuarios WHERE ultimo_acesso IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $countSemAcesso = $result['count'];
    
    echo "<div class='log-item'>Encontrados <strong>{$countSemAcesso}</strong> usuários sem último acesso registrado.</div>";
    
    if ($countSemAcesso > 0) {
        // Atualizar usuários sem ultimo_acesso
        echo "<div class='log-item log-info'>🔄 Atualizando campo ultimo_acesso para data/hora atual...</div>";
        
        $updateQuery = "UPDATE tbl_usuarios 
                        SET ultimo_acesso = CURRENT_TIMESTAMP 
                        WHERE ultimo_acesso IS NULL";
        
        $pdo->exec($updateQuery);
        
        echo "<div class='log-item log-success'>✓ Campo ultimo_acesso atualizado para {$countSemAcesso} usuários!</div>";
    } else {
        echo "<div class='log-item log-success'>✓ Todos os usuários já possuem último acesso registrado.</div>";
    }
    
    // Verificar status final
    echo "<div class='log-item log-info'>📊 Verificando status final...</div>";
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(ultimo_acesso) as com_acesso,
            SUM(CASE WHEN ultimo_acesso IS NULL THEN 1 ELSE 0 END) as sem_acesso
        FROM tbl_usuarios
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='mt-3'>";
    echo "<h5>📈 Estatísticas Finais:</h5>";
    echo "<table class='table table-sm table-bordered mt-2'>";
    echo "<tr><th>Total de Usuários</th><td><strong>{$stats['total']}</strong></td></tr>";
    echo "<tr><th>Com Último Acesso</th><td class='text-success'><strong>{$stats['com_acesso']}</strong></td></tr>";
    echo "<tr><th>Sem Último Acesso</th><td class='text-danger'><strong>{$stats['sem_acesso']}</strong></td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Listar alguns usuários com seus últimos acessos
    echo "<div class='mt-4'>";
    echo "<h5>👥 Últimos 10 usuários atualizados:</h5>";
    echo "<table class='table table-sm table-striped mt-2'>";
    echo "<thead><tr><th>Nome</th><th>Email</th><th>Último Acesso</th></tr></thead><tbody>";
    
    $stmt = $pdo->query("
        SELECT nome_completo, email, ultimo_acesso 
        FROM tbl_usuarios 
        ORDER BY ultimo_acesso DESC 
        LIMIT 10
    ");
    
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dataAcesso = $user['ultimo_acesso'] ? date('d/m/Y H:i:s', strtotime($user['ultimo_acesso'])) : '-';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['nome_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $dataAcesso . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    echo "</div>";
    
    echo "<div class='alert alert-success mt-4'>
            <h5>✅ Atualização concluída com sucesso!</h5>
            <p class='mb-0'>O campo 'ultimo_acesso' foi atualizado e agora será exibido na tela de usuários.</p>
            <p class='mb-0 mt-2'><strong>Próximos passos:</strong></p>
            <ul class='mt-2'>
                <li>Faça logout e login novamente para testar a atualização automática</li>
                <li>Acesse a tela de usuários para ver o campo 'Último Acesso' preenchido</li>
                <li>Cada novo login atualizará automaticamente este campo</li>
            </ul>
          </div>";
    
    echo "<div class='mt-3 d-flex gap-2'>
            <a href='../usuarios.php' class='btn btn-primary'>Ir para Tela de Usuários</a>
            <a href='../dashboard.php' class='btn btn-secondary'>Voltar ao Dashboard</a>
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

