<?php
/**
 * Script para corrigir filiais e associar usuário
 */

require_once 'config/config.php';
require_once 'config/conexao.php';

echo "<h1>🔧 Correção de Filiais e Inventário</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📋 Verificando situação atual:</h2>";
    
    // 1. Verificar usuário Gerencia
    echo "<h3>1. Usuário Gerencia:</h3>";
    $stmt = $pdo->prepare("SELECT id_usuario, nome_completo, email, id_filial FROM tbl_usuarios WHERE nome_completo LIKE '%Gerencia%'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    if ($usuarios) {
        foreach ($usuarios as $usuario) {
            echo "<p>✅ Usuário: {$usuario['nome_completo']} (ID: {$usuario['id_usuario']})</p>";
            echo "<p>📧 Email: {$usuario['email']}</p>";
            echo "<p>🏢 Filial ID: " . ($usuario['id_filial'] ?: 'NULL') . "</p>";
        }
    } else {
        echo "<p>❌ Usuário Gerencia não encontrado</p>";
    }
    
    // 2. Verificar filiais
    echo "<h3>2. Status das Filiais:</h3>";
    $stmt = $pdo->query("SELECT id_filial, nome_filial FROM tbl_filiais ORDER BY id_filial");
    $filiais = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th></tr>";
    foreach ($filiais as $filial) {
        echo "<tr>";
        echo "<td>{$filial['id_filial']}</td>";
        echo "<td>{$filial['nome_filial']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Verificar tabela de inventário
    echo "<h3>3. Tabela de Inventário:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_inventario'");
    $tabelaInventario = $stmt->fetch();
    
    if ($tabelaInventario) {
        echo "<p>✅ Tabela tbl_inventario existe</p>";
        
        // Verificar estrutura
        $stmt = $pdo->query("DESCRIBE tbl_inventario");
        $colunas = $stmt->fetchAll();
        
        echo "<p>📋 Estrutura da tabela:</p>";
        echo "<ul>";
        foreach ($colunas as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Verificar se tem campo id_filial
        $temCampoFilial = false;
        foreach ($colunas as $col) {
            if ($col['Field'] === 'id_filial') {
                $temCampoFilial = true;
                break;
            }
        }
        
        if ($temCampoFilial) {
            echo "<p>✅ Campo id_filial existe</p>";
        } else {
            echo "<p>❌ Campo id_filial NÃO existe</p>";
        }
        
    } else {
        echo "<p>❌ Tabela tbl_inventario NÃO existe</p>";
    }
    
    echo "<h2>🔧 Aplicando correções:</h2>";
    
    // 4. Verificar se usuário já tem filial associada
    echo "<h3>4. Verificando filial do usuário:</h3>";
    if ($usuarios) {
        $usuario = $usuarios[0];
        if ($usuario['id_filial']) {
            echo "<p>✅ Usuário já está associado à filial ID: {$usuario['id_filial']}</p>";
        } else {
            echo "<p>❌ Usuário não tem filial associada</p>";
        }
    }
    
    // 5. Associar usuário Gerencia a uma filial
    echo "<h3>5. Associando usuário a filial:</h3>";
    if ($usuarios) {
        $usuario = $usuarios[0]; // Primeiro usuário encontrado
        $filialPadrao = 1; // Filial padrão
        
        try {
            $stmt = $pdo->prepare("UPDATE tbl_usuarios SET id_filial = ? WHERE id_usuario = ?");
            $stmt->execute([$filialPadrao, $usuario['id_usuario']]);
            echo "<p>✅ Usuário {$usuario['nome_completo']} associado à filial ID {$filialPadrao}</p>";
        } catch (Exception $e) {
            echo "<p>❌ Erro ao associar usuário: " . $e->getMessage() . "</p>";
        }
    }
    
    // 6. Verificar se tabela inventário tem dados
    echo "<h3>6. Verificando dados de inventário:</h3>";
    if ($tabelaInventario) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_inventario");
        $total = $stmt->fetch()['total'];
        echo "<p>📊 Total de inventários: {$total}</p>";
        
        if ($total > 0) {
            $stmt = $pdo->query("SELECT id_inventario, numero_inventario, id_filial FROM tbl_inventario LIMIT 5");
            $inventarios = $stmt->fetchAll();
            
            echo "<p>📋 Primeiros inventários:</p>";
            echo "<ul>";
            foreach ($inventarios as $inv) {
                echo "<li>ID: {$inv['id_inventario']} - Número: {$inv['numero_inventario']} - Filial: {$inv['id_filial']}</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "<h2>✅ Correções aplicadas!</h2>";
    echo "<p>🔄 Agora teste novamente a página de inventário</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro no script:</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #f8f9fa; padding: 8px; }
td { padding: 6px; text-align: center; }
.success { color: #28a745; }
.error { color: #dc3545; }
ul { background: #f8f9fa; padding: 15px; border-radius: 5px; }
</style> 