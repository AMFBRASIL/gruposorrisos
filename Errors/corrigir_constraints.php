<?php
/**
 * Script para corrigir constraints de foreign key incorretas
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

require_once 'config/conexao.php';

echo "<h1>🔧 Correção de Constraints de Foreign Key</h1>";
echo "<hr>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    echo "✅ Conexão estabelecida<br><br>";
    
    // 1. Verificar tabelas existentes
    echo "<h3>📋 Verificando tabelas existentes...</h3>";
    
    $sqlTables = "SHOW TABLES";
    $stmt = $pdo->query($sqlTables);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas encontradas:<br>";
    foreach ($tables as $table) {
        echo "- {$table}<br>";
    }
    echo "<br>";
    
    // 2. Verificar se tbl_permissoes existe
    echo "<h3>🔍 Verificando tabela tbl_permissoes...</h3>";
    
    if (in_array('tbl_permissoes', $tables)) {
        echo "✅ Tabela tbl_permissoes existe<br>";
        
        // Verificar estrutura
        $sqlDescribe = "DESCRIBE tbl_permissoes";
        $stmt = $pdo->query($sqlDescribe);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<br>Estrutura atual:<br>";
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
        
        // Verificar constraints
        echo "<br><h4>🔗 Constraints atuais:</h4>";
        $sqlConstraints = "SELECT 
                            CONSTRAINT_NAME,
                            TABLE_NAME,
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                          FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'tbl_permissoes'
                          AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $stmt = $pdo->query($sqlConstraints);
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($constraints) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Constraint</th><th>Tabela</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
            
            foreach ($constraints as $constraint) {
                echo "<tr>";
                echo "<td>{$constraint['CONSTRAINT_NAME']}</td>";
                echo "<td>{$constraint['TABLE_NAME']}</td>";
                echo "<td>{$constraint['COLUMN_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_TABLE_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_COLUMN_NAME']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ Nenhuma constraint encontrada<br>";
        }
        
    } else {
        echo "❌ Tabela tbl_permissoes não existe<br>";
    }
    
    // 3. Verificar se tbl_paginas existe
    echo "<br><h3>📄 Verificando tabela tbl_paginas...</h3>";
    
    if (in_array('tbl_paginas', $tables)) {
        echo "✅ Tabela tbl_paginas existe<br>";
        
        // Verificar estrutura
        $sqlDescribe = "DESCRIBE tbl_paginas";
        $stmt = $pdo->query($sqlDescribe);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<br>Estrutura de tbl_paginas:<br>";
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
        
    } else {
        echo "❌ Tabela tbl_paginas não existe<br>";
    }
    
    // 4. Verificar se tbl_paginas_old existe
    echo "<br><h3>📄 Verificando tabela tbl_paginas_old...</h3>";
    
    if (in_array('tbl_paginas_old', $tables)) {
        echo "⚠️ Tabela tbl_paginas_old existe (tabela antiga)<br>";
        echo "💡 Esta tabela deve ser removida após corrigir as constraints<br>";
    } else {
        echo "✅ Tabela tbl_paginas_old não existe<br>";
    }
    
    // 5. Corrigir constraints incorretas
    echo "<br><h3>🔧 Corrigindo constraints...</h3>";
    
    if (in_array('tbl_permissoes', $tables)) {
        try {
            // Remover constraints incorretas
            echo "🗑️ Removendo constraints incorretas...<br>";
            
            $sqlDropConstraints = "ALTER TABLE tbl_permissoes 
                                  DROP FOREIGN KEY fk_permissoes_pagina,
                                  DROP FOREIGN KEY fk_permissoes_perfil";
            
            $pdo->exec($sqlDropConstraints);
            echo "✅ Constraints incorretas removidas<br>";
            
        } catch (Exception $e) {
            echo "⚠️ Erro ao remover constraints (pode não existir): " . $e->getMessage() . "<br>";
        }
        
        // Adicionar constraints corretas
        echo "🔗 Adicionando constraints corretas...<br>";
        
        try {
            $sqlAddConstraints = "ALTER TABLE tbl_permissoes 
                                 ADD CONSTRAINT fk_permissoes_perfil 
                                 FOREIGN KEY (id_perfil) REFERENCES tbl_perfis (id_perfil) ON DELETE CASCADE,
                                 ADD CONSTRAINT fk_permissoes_pagina 
                                 FOREIGN KEY (id_pagina) REFERENCES tbl_paginas (id_pagina) ON DELETE CASCADE";
            
            $pdo->exec($sqlAddConstraints);
            echo "✅ Constraints corretas adicionadas<br>";
            
        } catch (Exception $e) {
            echo "❌ Erro ao adicionar constraints: " . $e->getMessage() . "<br>";
            
            // Verificar se as colunas existem
            echo "<br>🔍 Verificando colunas necessárias...<br>";
            
            $sqlCheckColumns = "SHOW COLUMNS FROM tbl_permissoes";
            $stmt = $pdo->query($sqlCheckColumns);
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "Colunas existentes: " . implode(', ', $existingColumns) . "<br>";
            
            // Verificar se precisa criar colunas
            if (!in_array('id_perfil', $existingColumns)) {
                echo "➕ Adicionando coluna id_perfil...<br>";
                $pdo->exec("ALTER TABLE tbl_permissoes ADD COLUMN id_perfil int(11) NOT NULL");
            }
            
            if (!in_array('id_pagina', $existingColumns)) {
                echo "➕ Adicionando coluna id_pagina...<br>";
                $pdo->exec("ALTER TABLE tbl_permissoes ADD COLUMN id_pagina int(11) NOT NULL");
            }
            
            // Tentar adicionar constraints novamente
            try {
                $pdo->exec($sqlAddConstraints);
                echo "✅ Constraints adicionadas na segunda tentativa<br>";
            } catch (Exception $e2) {
                echo "❌ Erro persistente: " . $e2->getMessage() . "<br>";
            }
        }
    }
    
    // 6. Verificar se tbl_perfil_paginas existe
    echo "<br><h3>🔐 Verificando tabela tbl_perfil_paginas...</h3>";
    
    if (in_array('tbl_perfil_paginas', $tables)) {
        echo "✅ Tabela tbl_perfil_paginas existe<br>";
        
        // Verificar constraints
        $sqlConstraints = "SELECT 
                            CONSTRAINT_NAME,
                            TABLE_NAME,
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                          FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'tbl_perfil_paginas'
                          AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $stmt = $pdo->query($sqlConstraints);
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($constraints) > 0) {
            echo "<br>Constraints de tbl_perfil_paginas:<br>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Constraint</th><th>Tabela</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
            
            foreach ($constraints as $constraint) {
                echo "<tr>";
                echo "<td>{$constraint['CONSTRAINT_NAME']}</td>";
                echo "<td>{$constraint['TABLE_NAME']}</td>";
                echo "<td>{$constraint['COLUMN_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_TABLE_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_COLUMN_NAME']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "❌ Tabela tbl_perfil_paginas não existe<br>";
        echo "💡 Execute configurar_permissoes.php para criar esta tabela<br>";
    }
    
    // 7. Limpeza final
    echo "<br><h3>🧹 Limpeza final...</h3>";
    
    if (in_array('tbl_paginas_old', $tables)) {
        echo "🗑️ Removendo tabela antiga tbl_paginas_old...<br>";
        
        try {
            $pdo->exec("DROP TABLE IF EXISTS tbl_paginas_old");
            echo "✅ Tabela antiga removida<br>";
        } catch (Exception $e) {
            echo "⚠️ Erro ao remover tabela antiga: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><h2>🎉 Correção Concluída!</h2>";
    echo "<p>Agora tente executar novamente a operação que estava falhando.</p>";
    echo "<p>Se ainda houver problemas, execute:</p>";
    echo "<ul>";
    echo "<li><code>php configurar_permissoes.php</code> - Para configurar permissões</li>";
    echo "<li><code>php verificar_tabela_perfis.php</code> - Para verificar estrutura</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante a correção:</h2>";
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
h1, h2, h3, h4 {
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
code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style> 