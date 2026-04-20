<?php
/**
 * Script para remover a tabela antiga tbl_paginas_old
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

require_once 'config/conexao.php';

echo "<h1>🗑️ Remoção da Tabela Antiga tbl_paginas_old</h1>";
echo "<hr>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    echo "✅ Conexão estabelecida<br><br>";
    
    // 1. Verificar se a tabela existe
    echo "<h3>🔍 Verificando existência da tabela...</h3>";
    
    $sqlCheck = "SHOW TABLES LIKE 'tbl_paginas_old'";
    $stmt = $pdo->query($sqlCheck);
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "⚠️ Tabela tbl_paginas_old encontrada<br>";
        
        // 2. Verificar se há dados na tabela
        echo "<br><h3>📊 Verificando dados da tabela...</h3>";
        
        $sqlCount = "SELECT COUNT(*) as total FROM tbl_paginas_old";
        $stmt = $pdo->query($sqlCount);
        $totalRows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "Total de registros: <strong>{$totalRows}</strong><br>";
        
        if ($totalRows > 0) {
            echo "⚠️ A tabela contém {$totalRows} registros<br>";
            echo "💡 Todos os dados serão perdidos permanentemente!<br>";
        }
        
        // 3. Mostrar estrutura da tabela (para referência)
        echo "<br><h3>🏗️ Estrutura da tabela (para referência):</h3>";
        
        $sqlDescribe = "DESCRIBE tbl_paginas_old";
        $stmt = $pdo->query($sqlDescribe);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 4. Verificar se há constraints dependentes
        echo "<br><h3>🔗 Verificando constraints dependentes...</h3>";
        
        $sqlConstraints = "SELECT 
                            CONSTRAINT_NAME,
                            TABLE_NAME,
                            COLUMN_NAME
                          FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND REFERENCED_TABLE_NAME = 'tbl_paginas_old'";
        
        $stmt = $pdo->query($sqlConstraints);
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($constraints) > 0) {
            echo "⚠️ Encontradas constraints que referenciam tbl_paginas_old:<br>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Constraint</th><th>Tabela</th><th>Coluna</th></tr>";
            
            foreach ($constraints as $constraint) {
                echo "<tr>";
                echo "<td>{$constraint['CONSTRAINT_NAME']}</td>";
                echo "<td>{$constraint['TABLE_NAME']}</td>";
                echo "<td>{$constraint['COLUMN_NAME']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<br>💡 Estas constraints devem ser removidas antes de excluir a tabela<br>";
        } else {
            echo "✅ Nenhuma constraint dependente encontrada<br>";
        }
        
        // 5. Confirmar remoção
        echo "<br><h3>🗑️ Confirmando remoção...</h3>";
        
        if (count($constraints) > 0) {
            echo "⚠️ <strong>ATENÇÃO:</strong> Existem constraints dependentes!<br>";
            echo "🔧 Removendo constraints primeiro...<br>";
            
            foreach ($constraints as $constraint) {
                try {
                    $sqlDropConstraint = "ALTER TABLE {$constraint['TABLE_NAME']} 
                                         DROP FOREIGN KEY {$constraint['CONSTRAINT_NAME']}";
                    $pdo->exec($sqlDropConstraint);
                    echo "✅ Constraint {$constraint['CONSTRAINT_NAME']} removida<br>";
                } catch (Exception $e) {
                    echo "❌ Erro ao remover constraint {$constraint['CONSTRAINT_NAME']}: " . $e->getMessage() . "<br>";
                }
            }
        }
        
        // 6. Remover a tabela
        echo "<br><h3>🗑️ Removendo tabela tbl_paginas_old...</h3>";
        
        try {
            $sqlDrop = "DROP TABLE IF EXISTS tbl_paginas_old";
            $pdo->exec($sqlDrop);
            echo "✅ Tabela tbl_paginas_old removida com sucesso!<br>";
            
        } catch (Exception $e) {
            echo "❌ Erro ao remover tabela: " . $e->getMessage() . "<br>";
            echo "💡 Tente remover manualmente via phpMyAdmin ou MySQL<br>";
        }
        
    } else {
        echo "✅ Tabela tbl_paginas_old não existe<br>";
        echo "💡 Nada a ser removido<br>";
    }
    
    // 7. Verificar se tbl_paginas existe
    echo "<br><h3>📄 Verificando tabela atual tbl_paginas...</h3>";
    
    $sqlCheckPaginas = "SHOW TABLES LIKE 'tbl_paginas'";
    $stmt = $pdo->query($sqlCheckPaginas);
    $paginasExists = $stmt->rowCount() > 0;
    
    if ($paginasExists) {
        echo "✅ Tabela tbl_paginas existe (tabela atual)<br>";
        
        // Contar registros
        $sqlCountPaginas = "SELECT COUNT(*) as total FROM tbl_paginas WHERE ativo = 1";
        $stmt = $pdo->query($sqlCountPaginas);
        $totalPaginas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "Total de páginas ativas: <strong>{$totalPaginas}</strong><br>";
        
    } else {
        echo "❌ Tabela tbl_paginas não existe<br>";
        echo "💡 Execute configurar_paginas.php para criar a tabela<br>";
    }
    
    echo "<br><h2>🎉 Processo Concluído!</h2>";
    echo "<p>A tabela antiga foi removida e o sistema está limpo.</p>";
    
    if ($paginasExists) {
        echo "<p>✅ Use a tabela tbl_paginas (atual) para o sistema</p>";
    } else {
        echo "<p>⚠️ Execute configurar_paginas.php para configurar as páginas</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o processo:</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
h1, h2, h3 {
    color: #333;
}
table {
    background-color: white;
    margin: 10px 0;
}
th {
    background-color: #f0f0f0;
    padding: 8px;
}
td {
    padding: 6px 8px;
}
.success {
    color: #28a745;
}
.error {
    color: #dc3545;
}
.warning {
    color: #ffc107;
}
</style> 