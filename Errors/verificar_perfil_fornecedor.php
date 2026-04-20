<?php
require_once 'config/config.php';
require_once 'config/conexao.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Verificando Perfil Fornecedor</h2>";
    
    $stmt = $pdo->query("SELECT id_perfil, nome_perfil FROM tbl_perfis WHERE nome_perfil = 'Fornecedor'");
    $perfil = $stmt->fetch();
    
    if ($perfil) {
        echo "<p>✅ <strong>Perfil Fornecedor encontrado:</strong></p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$perfil['id_perfil']}</li>";
        echo "<li><strong>Nome:</strong> {$perfil['nome_perfil']}</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ <strong>Perfil Fornecedor NÃO encontrado!</strong></p>";
        
        echo "<p>📋 <strong>Perfis disponíveis:</strong></p>";
        $stmt = $pdo->query("SELECT id_perfil, nome_perfil FROM tbl_perfis ORDER BY id_perfil");
        $perfis = $stmt->fetchAll();
        
        echo "<ul>";
        foreach ($perfis as $p) {
            echo "<li>ID: {$p['id_perfil']} - {$p['nome_perfil']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
.success { color: #28a745; }
.error { color: #dc3545; }
ul { background: #f8f9fa; padding: 15px; border-radius: 5px; }
</style> 