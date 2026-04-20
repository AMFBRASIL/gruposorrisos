<?php
/**
 * Debug SQL Direto - Teste de Consulta sem Sistema de Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug SQL Direto - Teste de Consulta</h1>";
echo "<hr>";

try {
    // 1. Incluir configurações básicas
    echo "<h3>1. Incluindo configurações...</h3>";
    
    require_once 'config/database.php';
    echo "✅ database.php incluída<br>";
    
    // 2. Testar conexão
    echo "<br><h3>2. Testando conexão com banco...</h3>";
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Conexão com banco estabelecida<br>";
    } else {
        echo "❌ Falha na conexão com banco<br>";
        exit;
    }
    
    // 3. Simular dados do usuário Anderson
    echo "<br><h3>3. Dados do usuário Anderson...</h3>";
    
    $idUsuario = 5; // ID do Anderson
    $urlPagina = 'pedidos-compra.php';
    $acao = 'visualizar';
    
    echo "✅ Dados de teste:<br>";
    echo "<ul>";
    echo "<li><strong>ID Usuário:</strong> {$idUsuario}</li>";
    echo "<li><strong>URL Página:</strong> {$urlPagina}</li>";
    echo "<li><strong>Ação:</strong> {$acao}</li>";
    echo "</ul>";
    
    // 4. Testar consulta 1: Buscar usuário e perfil
    echo "<br><h3>4. Testando consulta 1: Buscar usuário e perfil...</h3>";
    
    $sql1 = "SELECT u.id_perfil, p.nome_perfil 
              FROM tbl_usuarios u 
              JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
              WHERE u.id_usuario = ? AND u.ativo = 1";
    
    echo "SQL 1: <code>{$sql1}</code><br>";
    echo "Parâmetro: id_usuario = {$idUsuario}<br><br>";
    
    try {
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$idUsuario]);
        $usuario = $stmt1->fetch();
        
        if ($usuario) {
            echo "✅ Usuário encontrado:<br>";
            echo "<ul>";
            echo "<li><strong>id_perfil:</strong> {$usuario['id_perfil']}</li>";
            echo "<li><strong>nome_perfil:</strong> {$usuario['nome_perfil']}</li>";
            echo "</ul>";
            
            $idPerfil = $usuario['id_perfil'];
            
        } else {
            echo "❌ <strong>PROBLEMA:</strong> Usuário não encontrado!<br>";
            exit;
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO na consulta 1:</strong> " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 5. Testar consulta 2: Verificar se é administrador
    echo "<br><h3>5. Testando consulta 2: Verificar se é administrador...</h3>";
    
    $isAdmin = strtolower($usuario['nome_perfil']) === 'administrador';
    echo "É administrador: " . ($isAdmin ? '✅ Sim' : '❌ Não') . "<br>";
    
    if ($isAdmin) {
        echo "✅ Administrador tem acesso total!<br>";
        echo "<p style='color: #28a745;'>🎉 PROBLEMA RESOLVIDO: Usuário é administrador!</p>";
        exit;
    }
    
    // 6. Testar consulta 3: Buscar permissão específica
    echo "<br><h3>6. Testando consulta 3: Buscar permissão específica...</h3>";
    
    $sql3 = "SELECT pp.permissao_visualizar, pp.permissao_inserir, 
                     pp.permissao_editar, pp.permissao_excluir
              FROM tbl_perfil_paginas pp
              JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
              WHERE pp.id_perfil = ? AND p.url_pagina = ? AND pp.ativo = 1";
    
    echo "SQL 3: <code>{$sql3}</code><br>";
    echo "Parâmetros: id_perfil = {$idPerfil}, url_pagina = {$urlPagina}<br><br>";
    
    try {
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute([$idPerfil, $urlPagina]);
        $permissao = $stmt3->fetch();
        
        if ($permissao) {
            echo "✅ Permissão encontrada:<br>";
            echo "<ul>";
            echo "<li><strong>permissao_visualizar:</strong> " . ($permissao['permissao_visualizar'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li><strong>permissao_inserir:</strong> " . ($permissao['permissao_inserir'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li><strong>permissao_editar:</strong> " . ($permissao['permissao_editar'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li><strong>permissao_excluir:</strong> " . ($permissao['permissao_excluir'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "</ul>";
            
            // Verificar permissão para visualizar
            $podeVisualizar = (bool)$permissao['permissao_visualizar'];
            echo "Pode visualizar: " . ($podeVisualizar ? '✅ Sim' : '❌ Não') . "<br>";
            
            if ($podeVisualizar) {
                echo "<p style='color: #28a745;'>🎉 PROBLEMA RESOLVIDO: Usuário tem permissão de visualizar!</p>";
            } else {
                echo "<p style='color: #dc3545;'>❌ PROBLEMA IDENTIFICADO: Usuário não tem permissão de visualizar!</p>";
            }
            
        } else {
            echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Nenhuma permissão encontrada!<br>";
            
            // 7. Debug adicional: Verificar se a página existe
            echo "<br><h3>7. Debug adicional: Verificar se a página existe...</h3>";
            
            $sqlPagina = "SELECT * FROM tbl_paginas WHERE url_pagina = ?";
            echo "SQL Página: <code>{$sqlPagina}</code><br>";
            echo "Parâmetro: url_pagina = {$urlPagina}<br><br>";
            
            $stmtPagina = $pdo->prepare($sqlPagina);
            $stmtPagina->execute([$urlPagina]);
            $pagina = $stmtPagina->fetch();
            
            if ($pagina) {
                echo "✅ Página encontrada na tabela tbl_paginas:<br>";
                echo "<ul>";
                echo "<li><strong>ID:</strong> {$pagina['id_pagina']}</li>";
                echo "<li><strong>Nome:</strong> {$pagina['nome_pagina']}</li>";
                echo "<li><strong>URL:</strong> {$pagina['url_pagina']}</li>";
                echo "<li><strong>Ativo:</strong> " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
                echo "</ul>";
            } else {
                echo "❌ <strong>PROBLEMA CRÍTICO:</strong> Página NÃO encontrada na tabela tbl_paginas!<br>";
                echo "<p>Esta é a causa do problema!</p>";
            }
            
            // 8. Debug adicional: Verificar permissões para este perfil
            echo "<br><h3>8. Debug adicional: Verificar permissões para este perfil...</h3>";
            
            $sqlPerfil = "SELECT * FROM tbl_perfil_paginas WHERE id_perfil = ? AND ativo = 1";
            echo "SQL Perfil: <code>{$sqlPerfil}</code><br>";
            echo "Parâmetro: id_perfil = {$idPerfil}<br><br>";
            
            $stmtPerfil = $pdo->prepare($sqlPerfil);
            $stmtPerfil->execute([$idPerfil]);
            $permissoesPerfil = $stmtPerfil->fetchAll();
            
            echo "Permissões para este perfil ({$idPerfil}):<br>";
            if ($permissoesPerfil) {
                foreach ($permissoesPerfil as $perm) {
                    echo "<ul>";
                    echo "<li><strong>ID Página:</strong> {$perm['id_pagina']}</li>";
                    echo "<li><strong>Visualizar:</strong> " . ($perm['permissao_visualizar'] ? 'Sim' : 'Não') . "</li>";
                    echo "<li><strong>Inserir:</strong> " . ($perm['permissao_inserir'] ? 'Sim' : 'Não') . "</li>";
                    echo "<li><strong>Editar:</strong> " . ($perm['permissao_editar'] ? 'Sim' : 'Não') . "</li>";
                    echo "<li><strong>Excluir:</strong> " . ($perm['permissao_excluir'] ? 'Sim' : 'Não') . "</li>";
                    echo "</ul>";
                }
            } else {
                echo "❌ Nenhuma permissão encontrada para este perfil<br>";
            }
            
            // 9. Debug adicional: Verificar todas as páginas
            echo "<br><h3>9. Debug adicional: Verificar todas as páginas...</h3>";
            
            $sqlTodasPaginas = "SELECT * FROM tbl_paginas WHERE ativo = 1 ORDER BY categoria, nome_pagina";
            $stmtTodasPaginas = $pdo->query($sqlTodasPaginas);
            $todasPaginas = $stmtTodasPaginas->fetchAll();
            
            if ($todasPaginas) {
                echo "Todas as páginas cadastradas:<br>";
                $categorias = [];
                foreach ($todasPaginas as $pag) {
                    $cat = $pag['categoria'] ?? 'sem categoria';
                    if (!isset($categorias[$cat])) {
                        $categorias[$cat] = [];
                    }
                    $categorias[$cat][] = $pag;
                }
                
                foreach ($categorias as $categoria => $paginasCat) {
                    echo "<br><strong>📁 {$categoria}:</strong><br>";
                    echo "<ul>";
                    foreach ($paginasCat as $pag) {
                        echo "<li>{$pag['nome_pagina']} ({$pag['url_pagina']}) - ID: {$pag['id_pagina']}</li>";
                    }
                    echo "</ul>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO na consulta 3:</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    }
    
    // 10. Resultado final
    echo "<br><h2>🎯 Diagnóstico SQL Concluído!</h2>";
    
    if (isset($permissao) && $permissao && $permissao['permissao_visualizar']) {
        echo "<p style='color: #28a745;'>✅ A consulta SQL está funcionando corretamente!</p>";
        echo "<p>O problema pode estar em outro lugar.</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ A consulta SQL está falhando!</p>";
        echo "<p>Verifique os detalhes acima para identificar a causa.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o diagnóstico SQL:</h2>";
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
ul {
    background-color: white;
    padding: 15px 20px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 4px solid #007bff;
}
code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
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