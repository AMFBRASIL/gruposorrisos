<?php
/**
 * Script para atualizar os status dos pedidos de compra
 * Data: 2025-01-22
 * Objetivo: Adequar os status ao novo modelo solicitado
 */

require_once 'config/conexao.php';

try {
    echo "<h2>🔄 Atualizando Status dos Pedidos de Compra</h2>";
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px;'>";
    
    // 1. Verificar estrutura atual do campo status
    echo "<h3>📋 Passo 1: Verificando estrutura atual do campo status</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status'");
    $estruturaAtual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Estrutura atual:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    print_r($estruturaAtual);
    echo "</pre>";
    
    // 2. Verificar dados atuais
    echo "<h3>📊 Passo 2: Verificando status atuais na tabela</h3>";
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM tbl_pedidos_compra GROUP BY status");
    $statusAtuais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Status atuais:</strong></p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Status</th><th>Total</th></tr>";
    foreach ($statusAtuais as $status) {
        echo "<tr><td>{$status['status']}</td><td>{$status['total']}</td></tr>";
    }
    echo "</table>";
    
    // 3. Fazer backup dos dados atuais
    echo "<h3>💾 Passo 3: Criando backup dos dados atuais</h3>";
    $pdo->exec("DROP TABLE IF EXISTS tbl_pedidos_compra_backup_status");
    $pdo->exec("CREATE TABLE tbl_pedidos_compra_backup_status AS 
                SELECT id_pedido, numero_pedido, status, data_atualizacao 
                FROM tbl_pedidos_compra");
    echo "<p>✅ Backup criado com sucesso na tabela 'tbl_pedidos_compra_backup_status'</p>";
    
    // 4. Mostrar mapeamento
    echo "<h3>🔄 Passo 4: Mapeamento de status antigos para novos</h3>";
    $mapeamento = [
        'em_analise' => 'em_analise (mantém)',
        'pendente' => 'pendente (mantém)',
        'aprovado' => 'aprovado_cotacao',
        'em_producao' => 'enviar_faturamento',
        'enviado' => 'em_transito',
        'recebido' => 'recebido (mantém)',
        'cancelado' => 'cancelado (mantém)'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Status Antigo</th><th>Status Novo</th></tr>";
    foreach ($mapeamento as $antigo => $novo) {
        echo "<tr><td>{$antigo}</td><td>{$novo}</td></tr>";
    }
    echo "</table>";
    
    // 5. Alterar o campo status para incluir os novos valores
    echo "<h3>🔧 Passo 5: Alterando estrutura do campo status</h3>";
    $alterQuery = "ALTER TABLE tbl_pedidos_compra 
                   MODIFY COLUMN status ENUM(
                       'em_analise',
                       'pendente', 
                       'aprovado_cotacao',
                       'enviar_faturamento',
                       'faturado',
                       'em_transito',
                       'recebido',
                       'cancelado'
                   ) DEFAULT 'em_analise'";
    
    $pdo->exec($alterQuery);
    echo "<p>✅ Estrutura do campo status alterada com sucesso!</p>";
    
    // 6. Atualizar dados existentes para o novo modelo
    echo "<h3>📝 Passo 6: Atualizando dados existentes</h3>";
    
    // Mapear 'aprovado' para 'aprovado_cotacao'
    $stmt = $pdo->prepare("UPDATE tbl_pedidos_compra 
                          SET status = 'aprovado_cotacao', data_atualizacao = NOW()
                          WHERE status = 'aprovado'");
    $stmt->execute();
    $count1 = $stmt->rowCount();
    echo "<p>• Atualizados {$count1} pedidos de 'aprovado' para 'aprovado_cotacao'</p>";
    
    // Mapear 'em_producao' para 'enviar_faturamento'
    $stmt = $pdo->prepare("UPDATE tbl_pedidos_compra 
                          SET status = 'enviar_faturamento', data_atualizacao = NOW()
                          WHERE status = 'em_producao'");
    $stmt->execute();
    $count2 = $stmt->rowCount();
    echo "<p>• Atualizados {$count2} pedidos de 'em_producao' para 'enviar_faturamento'</p>";
    
    // Mapear 'enviado' para 'em_transito'
    $stmt = $pdo->prepare("UPDATE tbl_pedidos_compra 
                          SET status = 'em_transito', data_atualizacao = NOW()
                          WHERE status = 'enviado'");
    $stmt->execute();
    $count3 = $stmt->rowCount();
    echo "<p>• Atualizados {$count3} pedidos de 'enviado' para 'em_transito'</p>";
    
    // 7. Verificar resultado final
    echo "<h3>✅ Passo 7: Verificando resultado final</h3>";
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM tbl_pedidos_compra GROUP BY status");
    $statusFinais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Status após atualização:</strong></p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Status</th><th>Total</th></tr>";
    foreach ($statusFinais as $status) {
        echo "<tr><td>{$status['status']}</td><td>{$status['total']}</td></tr>";
    }
    echo "</table>";
    
    // 8. Mostrar estrutura atualizada
    echo "<h3>🔍 Passo 8: Estrutura atualizada do campo status</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_pedidos_compra LIKE 'status'");
    $estruturaFinal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Estrutura final:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    print_r($estruturaFinal);
    echo "</pre>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 Atualização concluída com sucesso!</h4>";
    echo "<p><strong>Novos status implementados:</strong></p>";
    echo "<ul>";
    echo "<li><strong>em_analise</strong> - Quando o gerente analisa</li>";
    echo "<li><strong>pendente</strong> - Quando o gerente já analisou mas o setor de compras também vai aprovar</li>";
    echo "<li><strong>aprovado_cotacao</strong> - Quando o setor de compras aprovou e foi para o fornecedor dar preços</li>";
    echo "<li><strong>enviar_faturamento</strong> - Quando o fornecedor já colocou os preços e está tudo em ordem para faturar</li>";
    echo "<li><strong>faturado</strong> - Quando o pedido está pronto para enviar</li>";
    echo "<li><strong>em_transito</strong> - Quando o fornecedor já enviou e está em trânsito</li>";
    echo "<li><strong>recebido</strong> - Quando o material foi recebido com sucesso e validado pela empresa</li>";
    echo "<li><strong>cancelado</strong> - Quando houve algum problema e foi cancelado</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h4>❌ Erro durante a atualização:</h4>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>