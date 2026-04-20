<?php
require_once 'config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Ativar controle de horário
    $stmt = $pdo->prepare('UPDATE tbl_configuracoes SET valor = ? WHERE chave = ?');
    $stmt->execute(['1', 'horario_funcionamento_ativo']);
    
    echo "✅ Controle de horário ATIVADO\n";
    
    // Verificar se foi ativado
    $stmt = $pdo->prepare('SELECT valor FROM tbl_configuracoes WHERE chave = ?');
    $stmt->execute(['horario_funcionamento_ativo']);
    $result = $stmt->fetch();
    
    echo "📊 Status atual: " . ($result['valor'] == '1' ? 'ATIVO' : 'INATIVO') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>