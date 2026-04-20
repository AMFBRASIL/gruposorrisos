<?php
/**
 * Debug para investigar problema de filtro por filial no inventário
 */

require_once 'config/config.php';
require_once 'config/session.php';
require_once 'config/conexao.php';

echo "<h1>🔍 Debug - Inventário por Filial</h1>";

try {
    // Verificar se usuário está logado
    if (!isLoggedIn()) {
        echo "<p>❌ Usuário não está logado</p>";
        exit;
    }
    
    echo "<p>✅ Usuário logado: " . $_SESSION['usuario_nome'] . "</p>";
    echo "<p>✅ Perfil: " . $_SESSION['usuario_perfil'] . "</p>";
    
    // Verificar filial do usuário
    $filialUsuario = getCurrentUserFilialId();
    echo "<p>✅ Filial do usuário (getCurrentUserFilialId): " . ($filialUsuario ?: 'NULL') . "</p>";
    
    // Conectar ao banco
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📋 Verificando dados no banco:</h2>";
    
    // 1. Verificar filiais disponíveis
    echo "<h3>1. Filiais disponíveis:</h3>";
    $stmt = $pdo->query("SELECT * FROM tbl_filiais ORDER BY id_filial");
    $filiais = $stmt->fetchAll();
    
    if ($filiais) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Ativo</th></tr>";
        foreach ($filiais as $filial) {
            echo "<tr>";
            echo "<td>{$filial['id_filial']}</td>";
            echo "<td>{$filial['nome_filial']}</td>";
            echo "<td>" . ($filial['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhuma filial encontrada</p>";
    }
    
    // 2. Verificar inventários existentes
    echo "<h3>2. Inventários existentes (todos):</h3>";
    
    // Primeiro verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_inventario'");
    $tabelaExiste = $stmt->fetch();
    
    if ($tabelaExiste) {
        echo "<p>✅ Tabela tbl_inventario existe</p>";
        $stmt = $pdo->query("
            SELECT i.id_inventario, i.numero_inventario, i.status, i.id_filial, f.nome_filial
            FROM tbl_inventario i 
            LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial 
            ORDER BY i.id_inventario DESC 
            LIMIT 10
        ");
        $inventarios = $stmt->fetchAll();
    } else {
        echo "<p>❌ Tabela tbl_inventario NÃO existe</p>";
        
        // Verificar se existe tbl_inventario (singular)
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_inventario'");
        $tabelaSingular = $stmt->fetch();
        
        if ($tabelaSingular) {
            echo "<p>✅ Tabela tbl_inventario (singular) existe</p>";
            $stmt = $pdo->query("
                SELECT i.id_inventario, i.numero_inventario, i.status, i.id_filial, f.nome_filial
                FROM tbl_inventario i 
                LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial 
                ORDER BY i.id_inventario DESC 
                LIMIT 10
            ");
            $inventarios = $stmt->fetchAll();
        } else {
            echo "<p>❌ Nenhuma tabela de inventário encontrada</p>";
            $inventarios = [];
        }
    }
    
    if ($inventarios) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Status</th><th>ID Filial</th><th>Nome Filial</th></tr>";
        foreach ($inventarios as $inv) {
            echo "<tr>";
            echo "<td>{$inv['id_inventario']}</td>";
            echo "<td>{$inv['numero_inventario']}</td>";
            echo "<td>{$inv['status']}</td>";
            echo "<td>{$inv['id_filial']}</td>";
            echo "<td>{$inv['nome_filial']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhum inventário encontrado</p>";
    }
    
    // 3. Verificar inventários por filial específica
    if ($filialUsuario) {
        echo "<h3>3. Inventários da filial do usuário (ID: {$filialUsuario}):</h3>";
        $stmt = $pdo->prepare("
            SELECT i.id_inventario, i.numero_inventario, i.status, i.id_filial, f.nome_filial
            FROM tbl_inventarios i 
            LEFT JOIN tbl_filiais f ON i.id_filial = f.id_filial 
            WHERE i.id_filial = ?
            ORDER BY i.id_inventario DESC
        ");
        $stmt->execute([$filialUsuario]);
        $inventariosFilial = $stmt->fetchAll();
        
        if ($inventariosFilial) {
            echo "<p>✅ Encontrados " . count($inventariosFilial) . " inventários para esta filial</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Número</th><th>Status</th><th>ID Filial</th><th>Nome Filial</th></tr>";
            foreach ($inventariosFilial as $inv) {
                echo "<tr>";
                echo "<td>{$inv['id_inventario']}</td>";
                echo "<td>{$inv['numero_inventario']}</td>";
                echo "<td>{$inv['status']}</td>";
                echo "<td>{$inv['id_filial']}</td>";
                echo "<td>{$inv['nome_filial']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Nenhum inventário encontrado para esta filial</p>";
        }
    }
    
    // 4. Verificar estrutura da tabela usuarios
    echo "<h3>4. Estrutura da tabela tbl_usuarios:</h3>";
    $stmt = $pdo->query("DESCRIBE tbl_usuarios");
    $colunas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Verificar dados do usuário logado
    echo "<h3>5. Dados do usuário logado na tabela:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach ($usuario as $campo => $valor) {
            echo "<tr>";
            echo "<td>{$campo}</td>";
            echo "<td>" . (is_null($valor) ? 'NULL' : htmlspecialchars($valor)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Usuário não encontrado na tabela</p>";
    }
    
    // 6. Verificar estrutura da tabela inventários
    echo "<h3>6. Estrutura da tabela tbl_inventarios:</h3>";
    $stmt = $pdo->query("DESCRIBE tbl_inventarios");
    $colunas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro no debug:</h2>";
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
</style> 