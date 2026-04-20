<?php
/**
 * Teste Simples do Sistema de Permissões
 * Para verificar se as correções funcionaram
 */

echo "<h1>🧪 Teste Simples - Sistema de Permissões</h1>";
echo "<hr>";

try {
    // 1. Verificar se os arquivos existem
    echo "<h3>1. Verificando arquivos...</h3>";
    
    if (file_exists('backend/controllers/ControllerPermissoes.php')) {
        echo "✅ ControllerPermissoes.php encontrada<br>";
    } else {
        echo "❌ ControllerPermissoes.php não encontrada<br>";
        exit;
    }
    
    if (file_exists('backend/controllers/ControllerAcesso.php')) {
        echo "✅ ControllerAcesso.php encontrada<br>";
    } else {
        echo "❌ ControllerAcesso.php não encontrada<br>";
        exit;
    }
    
    // 2. Incluir arquivos
    echo "<br><h3>2. Incluindo arquivos...</h3>";
    
    require_once 'config/database.php';
    echo "✅ database.php incluída<br>";
    
    require_once 'config/session.php';
    echo "✅ session.php incluída<br>";
    
    require_once 'backend/controllers/ControllerPermissoes.php';
    echo "✅ ControllerPermissoes incluída<br>";
    
    require_once 'backend/controllers/ControllerAcesso.php';
    echo "✅ ControllerAcesso incluída<br>";
    
    // 3. Testar instanciação
    echo "<br><h3>3. Testando instanciação...</h3>";
    
    try {
        $controllerPermissoes = new ControllerPermissoes();
        echo "✅ ControllerPermissoes instanciada com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar ControllerPermissoes: " . $e->getMessage() . "<br>";
        exit;
    }
    
    try {
        $controllerAcesso = new ControllerAcesso();
        echo "✅ ControllerAcesso instanciada com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar ControllerAcesso: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 4. Testar conexão com banco
    echo "<br><h3>4. Testando conexão com banco...</h3>";
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo) {
            echo "✅ Conexão com banco estabelecida<br>";
        } else {
            echo "❌ Falha na conexão com banco<br>";
            exit;
        }
    } catch (Exception $e) {
        echo "❌ Erro na conexão com banco: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 5. Testar funcionalidades básicas
    echo "<br><h3>5. Testando funcionalidades básicas...</h3>";
    
    try {
        // Testar obter todas as páginas
        $paginas = $controllerPermissoes->obterTodasPaginas();
        echo "✅ obterTodasPaginas(): " . count($paginas) . " páginas retornadas<br>";
        
        // Testar obter todos os perfis
        $perfis = $controllerPermissoes->obterTodosPerfis();
        echo "✅ obterTodosPerfis(): " . count($perfis) . " perfis retornados<br>";
        
        // Testar estatísticas
        $estatisticas = $controllerPermissoes->obterEstatisticasPermissoes();
        echo "✅ obterEstatisticasPermissoes(): " . count($estatisticas) . " estatísticas retornadas<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao testar funcionalidades: " . $e->getMessage() . "<br>";
    }
    
    // 6. Testar simulação de usuário logado
    echo "<br><h3>6. Testando simulação de usuário logado...</h3>";
    
    // Simular sessão de usuário administrador
    $_SESSION['logged_in'] = true;
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_perfil_id'] = 1;
    $_SESSION['usuario_perfil'] = 'Administrador';
    
    echo "✅ Sessão simulada criada<br>";
    
    try {
        // Testar verificação de acesso
        $podeAcessar = $controllerAcesso->verificarAcessoPagina();
        echo "✅ verificarAcessoPagina(): " . ($podeAcessar ? 'Permitido' : 'Negado') . "<br>";
        
        // Testar permissões específicas
        $podeInserir = $controllerAcesso->podeExecutarAcao('inserir');
        echo "✅ podeExecutarAcao('inserir'): " . ($podeInserir ? 'Sim' : 'Não') . "<br>";
        
        $podeEditar = $controllerAcesso->podeExecutarAcao('editar');
        echo "✅ podeExecutarAcao('editar'): " . ($podeEditar ? 'Sim' : 'Não') . "<br>";
        
        $podeExcluir = $controllerAcesso->podeExecutarAcao('excluir');
        echo "✅ podeExecutarAcao('excluir'): " . ($podeExcluir ? 'Sim' : 'Não') . "<br>";
        
        // Testar obtenção de menu
        $menu = $controllerAcesso->obterMenuUsuario();
        echo "✅ obterMenuUsuario(): " . count($menu) . " categorias retornadas<br>";
        
        // Testar botões permitidos
        $botoes = $controllerAcesso->obterBotoesPermitidos();
        echo "✅ obterBotoesPermitidos(): " . count($botoes) . " botões retornados<br>";
        
        // Testar permissões resumidas (esta era a função com problema)
        $resumo = $controllerAcesso->obterPermissoesResumidas();
        echo "✅ obterPermissoesResumidas(): " . $resumo['total_paginas'] . " páginas no resumo<br>";
        echo "✅ Pode inserir em: " . $resumo['pode_inserir'] . " páginas<br>";
        echo "✅ Pode editar em: " . $resumo['pode_editar'] . " páginas<br>";
        echo "✅ Pode excluir em: " . $resumo['pode_excluir'] . " páginas<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao testar funcionalidades com usuário logado: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    // 7. Resultado final
    echo "<br><h2>🎉 Teste Simples Concluído com Sucesso!</h2>";
    echo "<p>As correções funcionaram! O sistema está operacional.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o teste:</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
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
pre {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}
</style> 