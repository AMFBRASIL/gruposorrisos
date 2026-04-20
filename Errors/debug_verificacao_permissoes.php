<?php
/**
 * Debug de Verificação de Permissões - Teste em Tempo Real
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug de Verificação de Permissões - Teste em Tempo Real</h1>";
echo "<hr>";

try {
    // 1. Incluir configurações
    echo "<h3>1. Incluindo configurações...</h3>";
    
    require_once 'config/database.php';
    echo "✅ database.php incluída<br>";
    
    require_once 'config/session.php';
    echo "✅ session.php incluída<br>";
    
    require_once 'backend/controllers/ControllerPermissoes.php';
    echo "✅ ControllerPermissoes incluída<br>";
    
    require_once 'backend/controllers/ControllerAcesso.php';
    echo "✅ ControllerAcesso incluída<br>";
    
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
    
    // 3. Simular usuário Anderson logado
    echo "<br><h3>3. Simulando usuário Anderson logado...</h3>";
    
    $_SESSION['logged_in'] = true;
    $_SESSION['usuario_id'] = 5;
    $_SESSION['usuario_perfil_id'] = 5;
    $_SESSION['usuario_nome'] = 'Anderson TESTE';
    $_SESSION['usuario_perfil'] = 'Fornecedor';
    
    echo "✅ Sessão simulada criada:<br>";
    echo "<ul>";
    echo "<li><strong>usuario_id:</strong> {$_SESSION['usuario_id']}</li>";
    echo "<li><strong>usuario_perfil_id:</strong> {$_SESSION['usuario_perfil_id']}</li>";
    echo "<li><strong>usuario_nome:</strong> {$_SESSION['usuario_nome']}</li>";
    echo "<li><strong>usuario_perfil:</strong> {$_SESSION['usuario_perfil']}</li>";
    echo "</ul>";
    
    // 4. Testar verificação de permissão passo a passo
    echo "<br><h3>4. Testando verificação de permissão passo a passo...</h3>";
    
    $controllerPermissoes = new ControllerPermissoes();
    $controllerAcesso = new ControllerAcesso();
    
    $urlPagina = 'pedidos-compra.php';
    $acao = 'visualizar';
    
    echo "<strong>Testando acesso a:</strong> {$urlPagina}<br>";
    echo "<strong>Ação:</strong> {$acao}<br><br>";
    
    // 4.1. Verificar se está logado
    echo "<h4>4.1. Verificando se está logado...</h4>";
    $isLoggedIn = isLoggedIn();
    echo "isLoggedIn(): " . ($isLoggedIn ? '✅ Sim' : '❌ Não') . "<br>";
    
    // 4.2. Verificar ID do usuário na sessão
    echo "<h4>4.2. Verificando ID do usuário na sessão...</h4>";
    $idUsuario = $_SESSION['usuario_id'] ?? null;
    echo "usuario_id na sessão: " . ($idUsuario ? $idUsuario : '❌ Nulo') . "<br>";
    
    // 4.3. Verificar ID do perfil na sessão
    echo "<h4>4.3. Verificando ID do perfil na sessão...</h4>";
    $idPerfil = $_SESSION['usuario_perfil_id'] ?? null;
    echo "usuario_perfil_id na sessão: " . ($idPerfil ? $idPerfil : '❌ Nulo') . "<br>";
    
    // 4.4. Testar verificação de permissão direta
    echo "<h4>4.4. Testando verificação de permissão direta...</h4>";
    
    try {
        // Buscar perfil do usuário
        $sql = "SELECT u.id_perfil, p.nome_perfil 
                FROM tbl_usuarios u 
                JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
                WHERE u.id_usuario = ? AND u.ativo = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "✅ Usuário encontrado na consulta:<br>";
            echo "<ul>";
            echo "<li><strong>id_perfil:</strong> {$usuario['id_perfil']}</li>";
            echo "<li><strong>nome_perfil:</strong> {$usuario['nome_perfil']}</li>";
            echo "</ul>";
            
            // 4.5. Verificar se é administrador
            echo "<h4>4.5. Verificando se é administrador...</h4>";
            $isAdmin = strtolower($usuario['nome_perfil']) === 'administrador';
            echo "É administrador: " . ($isAdmin ? '✅ Sim' : '❌ Não') . "<br>";
            
            if (!$isAdmin) {
                // 4.6. Buscar permissão específica
                echo "<h4>4.6. Buscando permissão específica...</h4>";
                
                $sql = "SELECT pp.permissao_visualizar, pp.permissao_inserir, 
                               pp.permissao_editar, pp.permissao_excluir
                        FROM tbl_perfil_paginas pp
                        JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
                        WHERE pp.id_perfil = ? AND p.url_pagina = ? AND pp.ativo = 1";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario['id_perfil'], $urlPagina]);
                $permissao = $stmt->fetch();
                
                if ($permissao) {
                    echo "✅ Permissão encontrada:<br>";
                    echo "<ul>";
                    echo "<li><strong>permissao_visualizar:</strong> " . ($permissao['permissao_visualizar'] ? '✅ Sim' : '❌ Não') . "</li>";
                    echo "<li><strong>permissao_inserir:</strong> " . ($permissao['permissao_inserir'] ? '✅ Sim' : '❌ Não') . "</li>";
                    echo "<li><strong>permissao_editar:</strong> " . ($permissao['permissao_editar'] ? '✅ Sim' : '❌ Não') . "</li>";
                    echo "<li><strong>permissao_excluir:</strong> " . ($permissao['permissao_excluir'] ? '✅ Sim' : '❌ Não') . "</li>";
                    echo "</ul>";
                    
                    // 4.7. Verificar permissão para visualizar
                    echo "<h4>4.7. Verificando permissão para visualizar...</h4>";
                    $podeVisualizar = (bool)$permissao['permissao_visualizar'];
                    echo "Pode visualizar: " . ($podeVisualizar ? '✅ Sim' : '❌ Não') . "<br>";
                    
                } else {
                    echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Nenhuma permissão encontrada!<br>";
                    echo "<p>SQL executada: <code>{$sql}</code></p>";
                    echo "<p>Parâmetros: id_perfil = {$usuario['id_perfil']}, url_pagina = {$urlPagina}</p>";
                    
                    // Verificar se a página existe
                    $sqlPagina = "SELECT * FROM tbl_paginas WHERE url_pagina = ?";
                    $stmtPagina = $pdo->prepare($sqlPagina);
                    $stmtPagina->execute([$urlPagina]);
                    $pagina = $stmtPagina->fetch();
                    
                    if ($pagina) {
                        echo "✅ Página encontrada:<br>";
                        echo "<ul>";
                        echo "<li><strong>ID:</strong> {$pagina['id_pagina']}</li>";
                        echo "<li><strong>Nome:</strong> {$pagina['nome_pagina']}</li>";
                        echo "<li><strong>URL:</strong> {$pagina['url_pagina']}</li>";
                        echo "<li><strong>Ativo:</strong> " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
                        echo "</ul>";
                    } else {
                        echo "❌ Página não encontrada na tabela tbl_paginas<br>";
                    }
                    
                    // Verificar se há permissões para este perfil
                    $sqlPerfil = "SELECT * FROM tbl_perfil_paginas WHERE id_perfil = ? AND ativo = 1";
                    $stmtPerfil = $pdo->prepare($sqlPerfil);
                    $stmtPerfil->execute([$usuario['id_perfil']]);
                    $permissoesPerfil = $stmtPerfil->fetchAll();
                    
                    echo "✅ Permissões para este perfil ({$usuario['id_perfil']}):<br>";
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
                }
            }
            
        } else {
            echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não encontrado na consulta!<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO:</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    }
    
    // 4.8. Testar função completa do controller
    echo "<br><h3>5. Testando função completa do controller...</h3>";
    
    try {
        $resultado = $controllerPermissoes->verificarPermissaoUsuarioLogado($urlPagina, $acao);
        echo "verificarPermissaoUsuarioLogado('{$urlPagina}', '{$acao}'): " . ($resultado ? '✅ True' : '❌ False') . "<br>";
        
        if (!$resultado) {
            echo "❌ <strong>PROBLEMA:</strong> A função retornou false!<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO na função:</strong> " . $e->getMessage() . "<br>";
    }
    
    // 4.9. Testar função do ControllerAcesso
    echo "<br><h3>6. Testando função do ControllerAcesso...</h3>";
    
    try {
        $resultadoAcesso = $controllerAcesso->verificarAcessoPagina();
        echo "verificarAcessoPagina(): " . ($resultadoAcesso ? '✅ True' : '❌ False') . "<br>";
        
        if (!$resultadoAcesso) {
            echo "❌ <strong>PROBLEMA:</strong> A função retornou false!<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO na função:</strong> " . $e->getMessage() . "<br>";
    }
    
    // 5. Resultado final
    echo "<br><h2>🎯 Diagnóstico Concluído!</h2>";
    
    if ($resultadoAcesso) {
        echo "<p style='color: #28a745;'>✅ O sistema está funcionando corretamente!</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ O sistema está com problema!</p>";
        echo "<p>Verifique os detalhes acima para identificar a causa.</p>";
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
h1, h2, h3, h4 {
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