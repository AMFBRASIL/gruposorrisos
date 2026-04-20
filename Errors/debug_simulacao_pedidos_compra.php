<?php
/**
 * Debug de Simulação - Página pedidos-compra.php
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Este script simula exatamente o que acontece na página pedidos-compra.php
 */

echo "<h1>🔍 Debug de Simulação - Página pedidos-compra.php</h1>";
echo "<hr>";

try {
    // 1. Simular exatamente o que acontece na página pedidos-compra.php
    echo "<h3>1. Simulando página pedidos-compra.php...</h3>";
    
    // 1.1. Incluir arquivos (como na página real)
    echo "<h4>1.1. Incluindo arquivos...</h4>";
    
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "✅ config.php incluída<br>";
    } else {
        echo "❌ config.php não encontrada<br>";
    }
    
    if (file_exists('config/session.php')) {
        require_once 'config/session.php';
        echo "✅ session.php incluída<br>";
    } else {
        echo "❌ session.php não encontrada<br>";
    }
    
    if (file_exists('backend/controllers/ControllerAcesso.php')) {
        require_once 'backend/controllers/ControllerAcesso.php';
        echo "✅ ControllerAcesso.php incluída<br>";
    } else {
        echo "❌ ControllerAcesso.php não encontrada<br>";
    }
    
    // 1.2. Verificar se está logado (como na página real)
    echo "<h4>1.2. Verificando se está logado...</h4>";
    if (!isLoggedIn()) {
        echo "❌ Usuário não está logado - redirecionando para login.php<br>";
        // header('Location: login.php');
        // exit;
    } else {
        echo "✅ Usuário está logado<br>";
    }
    
    // 1.3. Inicializar controller de acesso (como na página real)
    echo "<h4>1.3. Inicializando ControllerAcesso...</h4>";
    try {
        $controllerAcesso = new ControllerAcesso();
        echo "✅ ControllerAcesso instanciado com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar ControllerAcesso: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 1.4. Verificar acesso à página (como na página real)
    echo "<h4>1.4. Verificando acesso à página...</h4>";
    echo "<strong>URL atual:</strong> " . basename($_SERVER['PHP_SELF']) . "<br>";
    echo "<strong>Usuário logado:</strong> " . ($_SESSION['usuario_nome'] ?? 'N/A') . "<br>";
    echo "<strong>Perfil:</strong> " . ($_SESSION['usuario_perfil'] ?? 'N/A') . "<br>";
    
    try {
        $temAcesso = $controllerAcesso->verificarAcessoPagina();
        echo "verificarAcessoPagina() retornou: " . ($temAcesso ? '✅ true' : '❌ false') . "<br>";
        
        if (!$temAcesso) {
            echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Acesso negado!<br>";
            echo "O usuário deveria ter acesso mas a função retornou false.<br>";
        } else {
            echo "✅ Acesso permitido!<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO na verificação:</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    }
    
    // 2. Debug detalhado da função verificarAcessoPagina
    echo "<br><h3>2. Debug detalhado da função verificarAcessoPagina...</h3>";
    
    // 2.1. Verificar se está logado
    echo "<h4>2.1. Verificando isLoggedIn()...</h4>";
    $isLoggedIn = isLoggedIn();
    echo "isLoggedIn(): " . ($isLoggedIn ? '✅ true' : '❌ false') . "<br>";
    
    if (!$isLoggedIn) {
        echo "❌ <strong>PROBLEMA:</strong> Usuário não está logado!<br>";
        exit;
    }
    
    // 2.2. Obter URL da página
    echo "<h4>2.2. Obtendo URL da página...</h4>";
    $urlPagina = basename($_SERVER['PHP_SELF']);
    echo "URL da página: {$urlPagina}<br>";
    
    // 2.3. Verificar permissão específica
    echo "<h4>2.3. Verificando permissão específica...</h4>";
    
    try {
        // Simular a consulta que está sendo feita
        require_once 'config/database.php';
        $database = new Database();
        $pdo = $database->getConnection();
        
        $idUsuario = $_SESSION['usuario_id'];
        $acao = 'visualizar';
        
        echo "Verificando permissão para:<br>";
        echo "- ID Usuário: {$idUsuario}<br>";
        echo "- URL Página: {$urlPagina}<br>";
        echo "- Ação: {$acao}<br><br>";
        
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
            
            // Verificar se é administrador
            $isAdmin = strtolower($usuario['nome_perfil']) === 'administrador';
            echo "É administrador: " . ($isAdmin ? '✅ Sim' : '❌ Não') . "<br>";
            
            if (!$isAdmin) {
                // Buscar permissão específica
                echo "<h4>2.4. Buscando permissão específica...</h4>";
                
                $sql = "SELECT pp.permissao_visualizar, pp.permissao_inserir, 
                               pp.permissao_editar, pp.permissao_excluir
                        FROM tbl_perfil_paginas pp
                        JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina
                        WHERE pp.id_perfil = ? AND p.url_pagina = ? AND pp.ativo = 1";
                
                echo "SQL executada: <code>{$sql}</code><br>";
                echo "Parâmetros: id_perfil = {$usuario['id_perfil']}, url_pagina = {$urlPagina}<br><br>";
                
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
                    
                    // Verificar permissão para visualizar
                    $podeVisualizar = (bool)$permissao['permissao_visualizar'];
                    echo "Pode visualizar: " . ($podeVisualizar ? '✅ Sim' : '❌ Não') . "<br>";
                    
                    if (!$podeVisualizar) {
                        echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não tem permissão de visualizar!<br>";
                    }
                    
                } else {
                    echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> Nenhuma permissão encontrada!<br>";
                    
                    // Debug adicional
                    echo "<h4>2.5. Debug adicional...</h4>";
                    
                    // Verificar se a página existe
                    $sqlPagina = "SELECT * FROM tbl_paginas WHERE url_pagina = ?";
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
                        echo "❌ Página NÃO encontrada na tabela tbl_paginas!<br>";
                    }
                    
                    // Verificar permissões para este perfil
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
    
    // 3. Resultado final
    echo "<br><h2>🎯 Simulação Concluída!</h2>";
    
    if (isset($temAcesso) && $temAcesso) {
        echo "<p style='color: #28a745;'>✅ A simulação funcionou corretamente!</p>";
        echo "<p>O problema pode estar em outro lugar.</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ A simulação falhou!</p>";
        echo "<p>Verifique os detalhes acima para identificar a causa.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante a simulação:</h2>";
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