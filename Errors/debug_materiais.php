<?php
require_once 'config/config.php';
require_once 'config/conexao.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    
    echo "<h1>🔍 Debug - Materiais e Inventário</h1>";
    
    // 1. Verificar total de materiais
    echo "<h2>1. Total de Materiais:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_materiais");
    $total = $stmt->fetch()['total'];
    echo "<p>Total de materiais: <strong>{$total}</strong></p>";
    
    if ($total > 0) {
        // 2. Materiais por filial
        echo "<h2>2. Materiais por Filial:</h2>";
        $stmt = $pdo->query("SELECT id_filial, COUNT(*) as qtd FROM tbl_materiais GROUP BY id_filial ORDER BY id_filial");
        $filiais = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID Filial</th><th>Quantidade</th></tr>";
        foreach ($filiais as $filial) {
            echo "<tr>";
            echo "<td>{$filial['id_filial']}</td>";
            echo "<td>{$filial['qtd']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 3. Verificar estrutura da tabela materiais
        echo "<h2>3. Estrutura da Tabela Materiais:</h2>";
        $stmt = $pdo->query("DESCRIBE tbl_materiais");
        $colunas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>{$coluna['Field']}</td>";
            echo "<td>{$coluna['Type']}</td>";
            echo "<td>{$coluna['Null']}</td>";
            echo "<td>{$coluna['Key']}</td>";
            echo "<td>{$coluna['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 4. Verificar alguns materiais de exemplo
        echo "<h2>4. Exemplo de Materiais:</h2>";
        $stmt = $pdo->query("SELECT id_material, nome, id_filial, estoque_atual, preco_unitario, ativo FROM tbl_materiais LIMIT 5");
        $materiais = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Filial</th><th>Estoque</th><th>Preço</th><th>Ativo</th></tr>";
        foreach ($materiais as $material) {
            echo "<tr>";
            echo "<td>{$material['id_material']}</td>";
            echo "<td>{$material['nome']}</td>";
            echo "<td>{$material['id_filial']}</td>";
            echo "<td>{$material['estoque_atual']}</td>";
            echo "<td>{$material['preco_unitario']}</td>";
            echo "<td>" . ($material['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 5. Verificar inventários existentes
        echo "<h2>5. Inventários Existentes:</h2>";
        $stmt = $pdo->query("SELECT id_inventario, numero_inventario, id_filial, status, data_inicio FROM tbl_inventario ORDER BY id_inventario DESC LIMIT 5");
        $inventarios = $stmt->fetchAll();
        
        if ($inventarios) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Número</th><th>Filial</th><th>Status</th><th>Data Início</th></tr>";
            foreach ($inventarios as $inv) {
                echo "<tr>";
                echo "<td>{$inv['id_inventario']}</td>";
                echo "<td>{$inv['numero_inventario']}</td>";
                echo "<td>{$inv['id_filial']}</td>";
                echo "<td>{$inv['status']}</td>";
                echo "<td>{$inv['data_inicio']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Nenhum inventário encontrado</p>";
        }
        
        // 6. Verificar itens de inventário
        echo "<h2>6. Itens de Inventário:</h2>";
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_itens_inventario");
        $totalItens = $stmt->fetch()['total'];
        echo "<p>Total de itens de inventário: <strong>{$totalItens}</strong></p>";
        
        if ($totalItens > 0) {
            $stmt = $pdo->query("SELECT id_inventario, COUNT(*) as qtd FROM tbl_itens_inventario GROUP BY id_inventario ORDER BY id_inventario DESC LIMIT 5");
            $itensPorInventario = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID Inventário</th><th>Quantidade de Itens</th></tr>";
            foreach ($itensPorInventario as $item) {
                echo "<tr>";
                echo "<td>{$item['id_inventario']}</td>";
                echo "<td>{$item['qtd']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p>❌ <strong>PROBLEMA ENCONTRADO:</strong> Não existem materiais cadastrados no sistema!</p>";
        echo "<p>Para criar inventários, você precisa primeiro cadastrar materiais na página de Materiais.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
table { margin: 10px 0; border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background: #f8f9fa; }
</style> 