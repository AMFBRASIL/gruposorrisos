<?php
/**
 * Debug de Permissões - Usuário Anderson
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug de Permissões - Usuário Anderson</h1>";
echo "<hr>";

try {
    // 1. Incluir configurações
    echo "<h3>1. Incluindo configurações...</h3>";
    
    require_once 'config/database.php';
    echo "✅ database.php incluída<br>";
    
    require_once 'config/session.php';
    echo "✅ session.php incluída<br>";
    
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
    
    // 3. Buscar usuário Anderson
    echo "<br><h3>3. Buscando usuário Anderson...</h3>";
    
    $sql = "SELECT u.*, p.nome_perfil, p.descricao as descricao_perfil 
            FROM tbl_usuarios u 
            JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
            WHERE u.email = ? AND u.ativo = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['anderson@gruposorrisos.com.br']);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "✅ Usuário encontrado:<br>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$usuario['id_usuario']}</li>";
        echo "<li><strong>Nome:</strong> {$usuario['nome_completo']}</li>";
        echo "<li><strong>Email:</strong> {$usuario['email']}</li>";
        echo "<li><strong>Perfil ID:</strong> {$usuario['id_perfil']}</li>";
        echo "<li><strong>Perfil:</strong> {$usuario['nome_perfil']}</li>";
        echo "<li><strong>Descrição Perfil:</strong> {$usuario['descricao_perfil']}</li>";
        echo "</ul>";
        
        $idUsuario = $usuario['id_usuario'];
        $idPerfil = $usuario['id_perfil'];
        
    } else {
        echo "❌ Usuário Anderson não encontrado<br>";
        exit;
    }
    
    // 4. Verificar perfil "fornecedor"
    echo "<br><h3>4. Verificando perfil 'fornecedor'...</h3>";
    
    $sql = "SELECT * FROM tbl_perfis WHERE nome_perfil LIKE '%fornecedor%' OR nome_perfil LIKE '%Fornecedor%'";
    $stmt = $pdo->query($sql);
    $perfisFornecedor = $stmt->fetchAll();
    
    if ($perfisFornecedor) {
        echo "✅ Perfis relacionados a 'fornecedor' encontrados:<br>";
        foreach ($perfisFornecedor as $perfil) {
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$perfil['id_perfil']}</li>";
            echo "<li><strong>Nome:</strong> {$perfil['nome_perfil']}</li>";
            echo "<li><strong>Descrição:</strong> {$perfil['descricao']}</li>";
            echo "</ul>";
        }
    } else {
        echo "❌ Nenhum perfil relacionado a 'fornecedor' encontrado<br>";
    }
    
    // 5. Verificar página pedidos-compra
    echo "<br><h3>5. Verificando página 'pedidos-compra'...</h3>";
    
    $sql = "SELECT * FROM tbl_paginas WHERE url_pagina = 'pedidos-compra.php' OR nome_pagina LIKE '%pedidos%' OR nome_pagina LIKE '%compra%'";
    $stmt = $pdo->query($sql);
    $paginasPedidos = $stmt->fetchAll();
    
    if ($paginasPedidos) {
        echo "✅ Páginas relacionadas a 'pedidos-compra' encontradas:<br>";
        foreach ($paginasPedidos as $pagina) {
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$pagina['id_pagina']}</li>";
            echo "<li><strong>Nome:</strong> {$pagina['nome_pagina']}</li>";
            echo "<li><strong>URL:</strong> {$pagina['url_pagina']}</li>";
            echo "<li><strong>Categoria:</strong> {$pagina['categoria']}</li>";
            echo "<li><strong>Ativo:</strong> " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
            echo "</ul>";
        }
    } else {
        echo "❌ Nenhuma página relacionada a 'pedidos-compra' encontrada<br>";
    }
    
    // 6. Verificar permissões específicas do usuário Anderson
    echo "<br><h3>6. Verificando permissões do usuário Anderson...</h3>";
    
    $sql = "SELECT pp.*, p.nome_pagina, p.url_pagina, p.categoria 
            FROM tbl_perfil_paginas pp 
            JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina 
            WHERE pp.id_perfil = ? AND pp.ativo = 1 AND p.ativo = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idPerfil]);
    $permissoes = $stmt->fetchAll();
    
    if ($permissoes) {
        echo "✅ Permissões encontradas para o perfil {$usuario['nome_perfil']}:<br>";
        foreach ($permissoes as $permissao) {
            echo "<ul>";
            echo "<li><strong>Página:</strong> {$permissao['nome_pagina']} ({$permissao['url_pagina']})</li>";
            echo "<li><strong>Categoria:</strong> {$permissao['categoria']}</li>";
            echo "<li><strong>Visualizar:</strong> " . ($permissao['permissao_visualizar'] ? 'Sim' : 'Não') . "</li>";
            echo "<li><strong>Inserir:</strong> " . ($permissao['permissao_inserir'] ? 'Sim' : 'Não') . "</li>";
            echo "<li><strong>Editar:</strong> " . ($permissao['permissao_editar'] ? 'Sim' : 'Não') . "</li>";
            echo "<li><strong>Excluir:</strong> " . ($permissao['permissao_excluir'] ? 'Sim' : 'Não') . "</li>";
            echo "</ul>";
        }
    } else {
        echo "❌ Nenhuma permissão encontrada para o perfil {$usuario['nome_perfil']}<br>";
    }
    
    // 7. Verificar permissão específica para pedidos-compra
    echo "<br><h3>7. Verificando permissão específica para 'pedidos-compra'...</h3>";
    
    $sql = "SELECT pp.*, p.nome_pagina, p.url_pagina 
            FROM tbl_perfil_paginas pp 
            JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina 
            WHERE pp.id_perfil = ? AND p.url_pagina = 'pedidos-compra.php' AND pp.ativo = 1 AND p.ativo = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idPerfil]);
    $permissaoPedidos = $stmt->fetch();
    
    if ($permissaoPedidos) {
        echo "✅ Permissão para 'pedidos-compra' encontrada:<br>";
        echo "<ul>";
        echo "<li><strong>Página:</strong> {$permissaoPedidos['nome_pagina']}</li>";
        echo "<li><strong>Visualizar:</strong> " . ($permissaoPedidos['permissao_visualizar'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Inserir:</strong> " . ($permissaoPedidos['permissao_inserir'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Editar:</strong> " . ($permissaoPedidos['permissao_editar'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Excluir:</strong> " . ($permissaoPedidos['permissao_excluir'] ? 'Sim' : 'Não') . "</li>";
        echo "</ul>";
        
        if (!$permissaoPedidos['permissao_visualizar']) {
            echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não tem permissão de visualizar!<br>";
        }
    } else {
        echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Nenhuma permissão encontrada para 'pedidos-compra'!<br>";
    }
    
    // 8. Verificar se a página existe na tabela de páginas
    echo "<br><h3>8. Verificando se 'pedidos-compra' está cadastrada...</h3>";
    
    $sql = "SELECT * FROM tbl_paginas WHERE url_pagina = 'pedidos-compra.php'";
    $stmt = $pdo->query($sql);
    $paginaPedidos = $stmt->fetch();
    
    if ($paginaPedidos) {
        echo "✅ Página 'pedidos-compra' está cadastrada:<br>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$paginaPedidos['id_pagina']}</li>";
        echo "<li><strong>Nome:</strong> {$paginaPedidos['nome_pagina']}</li>";
        echo "<li><strong>URL:</strong> {$paginaPedidos['url_pagina']}</li>";
        echo "<li><strong>Ativo:</strong> " . ($paginaPedidos['ativo'] ? 'Sim' : 'Não') . "</li>";
        echo "</ul>";
    } else {
        echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Página 'pedidos-compra' não está cadastrada!<br>";
    }
    
    // 9. Verificar todas as páginas do sistema
    echo "<br><h3>9. Listando todas as páginas do sistema...</h3>";
    
    $sql = "SELECT * FROM tbl_paginas WHERE ativo = 1 ORDER BY categoria, nome_pagina";
    $stmt = $pdo->query($sql);
    $todasPaginas = $stmt->fetchAll();
    
    if ($todasPaginas) {
        echo "✅ Páginas cadastradas no sistema:<br>";
        $categorias = [];
        foreach ($todasPaginas as $pagina) {
            $cat = $pagina['categoria'] ?? 'sem categoria';
            if (!isset($categorias[$cat])) {
                $categorias[$cat] = [];
            }
            $categorias[$cat][] = $pagina;
        }
        
        foreach ($categorias as $categoria => $paginasCat) {
            echo "<br><strong>📁 {$categoria}:</strong><br>";
            echo "<ul>";
            foreach ($paginasCat as $pagina) {
                echo "<li>{$pagina['nome_pagina']} ({$pagina['url_pagina']})</li>";
            }
            echo "</ul>";
        }
    }
    
    // 10. Resultado final
    echo "<br><h2>🎯 Diagnóstico Concluído!</h2>";
    
    if ($permissaoPedidos && $permissaoPedidos['permissao_visualizar']) {
        echo "<p style='color: #28a745;'>✅ O usuário Anderson TEM permissão para visualizar 'pedidos-compra'</p>";
        echo "<p>Se ainda está sendo redirecionado, verifique:</p>";
        echo "<ul>";
        echo "<li>Se o arquivo pedidos-compra.php existe</li>";
        echo "<li>Se há algum erro no código da página</li>";
        echo "<li>Se há conflito com outras verificações de permissão</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: #dc3545;'>❌ O usuário Anderson NÃO TEM permissão para visualizar 'pedidos-compra'</p>";
        echo "<p>Para resolver:</p>";
        echo "<ol>";
        echo "<li>Verificar se a página está cadastrada em tbl_paginas</li>";
        echo "<li>Verificar se as permissões estão configuradas em tbl_perfil_paginas</li>";
        echo "<li>Executar configurar_paginas.php e configurar_permissoes.php</li>";
        echo "</ol>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o diagnóstico:</h2>";
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