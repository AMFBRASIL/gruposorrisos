<?php
/**
 * Script para migrar dados de tbl_permissoes para tbl_perfil_paginas
 * Este script resolve a inconsistência entre as duas tabelas de permissões
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>🔄 Migração de Permissões</h1>";
echo "<p>Migrando dados de <code>tbl_permissoes</code> para <code>tbl_perfil_paginas</code></p>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📊 Verificando tabelas...</h2>";
    
    // Verificar se tbl_permissoes existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_permissoes'");
    $tblPermissoesExiste = $stmt->rowCount() > 0;
    
    // Verificar se tbl_perfil_paginas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_perfil_paginas'");
    $tblPerfilPaginasExiste = $stmt->rowCount() > 0;
    
    echo "✅ <code>tbl_permissoes</code> existe: " . ($tblPermissoesExiste ? 'Sim' : 'Não') . "<br>";
    echo "✅ <code>tbl_perfil_paginas</code> existe: " . ($tblPerfilPaginasExiste ? 'Sim' : 'Não') . "<br>";
    
    if (!$tblPermissoesExiste) {
        echo "<p>❌ <code>tbl_permissoes</code> não existe. Nada para migrar.</p>";
        exit;
    }
    
    if (!$tblPerfilPaginasExiste) {
        echo "<p>❌ <code>tbl_perfil_paginas</code> não existe. Criando...</p>";
        
        // Criar tabela tbl_perfil_paginas
        $sql = "CREATE TABLE tbl_perfil_paginas (
            id_perfil_pagina int(11) NOT NULL AUTO_INCREMENT,
            id_perfil int(11) NOT NULL,
            id_pagina int(11) NOT NULL,
            permissao_visualizar tinyint(1) DEFAULT 0,
            permissao_inserir tinyint(1) DEFAULT 0,
            permissao_editar tinyint(1) DEFAULT 0,
            permissao_excluir tinyint(1) DEFAULT 0,
            ativo tinyint(1) DEFAULT 1,
            data_criacao timestamp DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_perfil_pagina),
            UNIQUE KEY unique_perfil_pagina (id_perfil, id_pagina),
            KEY fk_perfil_paginas_perfil (id_perfil),
            KEY fk_perfil_paginas_pagina (id_pagina)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ Tabela <code>tbl_perfil_paginas</code> criada com sucesso!<br>";
    }
    
    echo "<h2>📋 Contando registros...</h2>";
    
    // Contar registros em tbl_permissoes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_permissoes");
    $totalPermissoes = $stmt->fetch()['total'];
    echo "📊 Total de registros em <code>tbl_permissoes</code>: <strong>{$totalPermissoes}</strong><br>";
    
    // Contar registros em tbl_perfil_paginas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_perfil_paginas");
    $totalPerfilPaginas = $stmt->fetch()['total'];
    echo "📊 Total de registros em <code>tbl_perfil_paginas</code>: <strong>{$totalPerfilPaginas}</strong><br>";
    
    if ($totalPermissoes == 0) {
        echo "<p>✅ Nenhum registro para migrar em <code>tbl_permissoes</code></p>";
        exit;
    }
    
    echo "<h2>🔄 Iniciando migração...</h2>";
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Migrar dados
    $sql = "INSERT INTO tbl_perfil_paginas (
                id_perfil, 
                id_pagina, 
                permissao_visualizar, 
                permissao_inserir, 
                permissao_editar, 
                permissao_excluir, 
                ativo
            ) 
            SELECT 
                id_perfil,
                id_pagina,
                pode_visualizar,
                pode_inserir,
                pode_editar,
                pode_excluir,
                ativo
            FROM tbl_permissoes
            ON DUPLICATE KEY UPDATE
                permissao_visualizar = VALUES(permissao_visualizar),
                permissao_inserir = VALUES(permissao_inserir),
                permissao_editar = VALUES(permissao_editar),
                permissao_excluir = VALUES(permissao_excluir),
                ativo = VALUES(ativo),
                data_atualizacao = NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $registrosMigrados = $stmt->rowCount();
    
    // Commit da transação
    $pdo->commit();
    
    echo "✅ <strong>{$registrosMigrados}</strong> registros migrados com sucesso!<br>";
    
    // Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_perfil_paginas");
    $novoTotal = $stmt->fetch()['total'];
    echo "📊 Novo total em <code>tbl_perfil_paginas</code>: <strong>{$novoTotal}</strong><br>";
    
    echo "<h2>🎯 Migração concluída!</h2>";
    echo "<p>✅ Todos os dados foram migrados de <code>tbl_permissoes</code> para <code>tbl_perfil_paginas</code></p>";
    echo "<p>🔧 Agora o sistema está consistente e funcionará corretamente!</p>";
    
    // Opcional: remover tabela antiga (comentado por segurança)
    echo "<h3>⚠️ Opcional: Remover tabela antiga</h3>";
    echo "<p>Para remover a tabela <code>tbl_permissoes</code> (não mais necessária), execute:</p>";
    echo "<code>DROP TABLE tbl_permissoes;</code>";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "<h2>❌ Erro na migração</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
</style> 