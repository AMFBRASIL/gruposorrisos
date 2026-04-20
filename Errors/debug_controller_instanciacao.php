<?php
/**
 * Debug de Instanciação do ControllerAcesso
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug de Instanciação do ControllerAcesso</h1>";
echo "<hr>";

try {
    // 1. Verificar sessão
    echo "<h3>1. Verificando sessão...</h3>";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✅ Sessão iniciada<br>";
    } else {
        echo "✅ Sessão já está ativa<br>";
    }
    
    // Simular usuário Anderson logado
    $_SESSION['logged_in'] = true;
    $_SESSION['usuario_id'] = 5;
    $_SESSION['usuario_perfil_id'] = 5;
    $_SESSION['usuario_nome'] = 'Anderson TESTE';
    $_SESSION['usuario_perfil'] = 'Fornecedor';
    
    echo "✅ Sessão simulada criada<br>";
    echo "<ul>";
    echo "<li><strong>logged_in:</strong> " . ($_SESSION['logged_in'] ? 'true' : 'false') . "</li>";
    echo "<li><strong>usuario_id:</strong> {$_SESSION['usuario_id']}</li>";
    echo "<li><strong>usuario_perfil_id:</strong> {$_SESSION['usuario_perfil_id']}</li>";
    echo "<li><strong>usuario_nome:</strong> {$_SESSION['usuario_nome']}</li>";
    echo "<li><strong>usuario_perfil:</strong> {$_SESSION['usuario_perfil']}</li>";
    echo "</ul>";
    
    // 2. Verificar função isLoggedIn()
    echo "<br><h3>2. Verificando função isLoggedIn()...</h3>";
    
    if (file_exists('config/session.php')) {
        require_once 'config/session.php';
        $isLoggedIn = isLoggedIn();
        echo "isLoggedIn() retornou: " . ($isLoggedIn ? '✅ true' : '❌ false') . "<br>";
        
        if (!$isLoggedIn) {
            echo "❌ <strong>PROBLEMA IDENTIFICADO:</strong> isLoggedIn() retornou false!<br>";
            echo "Verifique a função isLoggedIn() em config/session.php<br>";
        }
    } else {
        echo "❌ arquivo session.php não encontrado<br>";
        exit;
    }
    
    // 3. Testar inclusão dos arquivos
    echo "<br><h3>3. Testando inclusão dos arquivos...</h3>";
    
    $arquivos = [
        'config/database.php' => 'Database',
        'config/session.php' => 'isLoggedIn',
        'backend/controllers/ControllerPermissoes.php' => 'ControllerPermissoes',
        'backend/controllers/ControllerAcesso.php' => 'ControllerAcesso'
    ];
    
    foreach ($arquivos as $arquivo => $classe) {
        if (file_exists($arquivo)) {
            echo "✅ {$arquivo} encontrado<br>";
        } else {
            echo "❌ {$arquivo} não encontrado<br>";
            exit;
        }
    }
    
    // 4. Testar inclusão do ControllerPermissoes
    echo "<br><h3>4. Testando inclusão do ControllerPermissoes...</h3>";
    
    try {
        require_once 'backend/controllers/ControllerPermissoes.php';
        echo "✅ ControllerPermissoes.php incluída<br>";
        
        // Verificar se a classe existe
        if (class_exists('ControllerPermissoes')) {
            echo "✅ Classe ControllerPermissoes existe<br>";
        } else {
            echo "❌ <strong>PROBLEMA:</strong> Classe ControllerPermissoes não existe!<br>";
            exit;
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO ao incluir ControllerPermissoes:</strong> " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 5. Testar instanciação do ControllerPermissoes
    echo "<br><h3>5. Testando instanciação do ControllerPermissoes...</h3>";
    
    try {
        $controllerPermissoes = new ControllerPermissoes();
        echo "✅ ControllerPermissoes instanciado com sucesso<br>";
        
        // Testar método básico
        $paginas = $controllerPermissoes->obterTodasPaginas();
        echo "✅ obterTodasPaginas() retornou " . count($paginas) . " páginas<br>";
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO ao instanciar ControllerPermissoes:</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        exit;
    }
    
    // 6. Testar inclusão do ControllerAcesso
    echo "<br><h3>6. Testando inclusão do ControllerAcesso...</h3>";
    
    try {
        require_once 'backend/controllers/ControllerAcesso.php';
        echo "✅ ControllerAcesso.php incluída<br>";
        
        // Verificar se a classe existe
        if (class_exists('ControllerAcesso')) {
            echo "✅ Classe ControllerAcesso existe<br>";
        } else {
            echo "❌ <strong>PROBLEMA:</strong> Classe ControllerAcesso não existe!<br>";
            exit;
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO ao incluir ControllerAcesso:</strong> " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 7. Testar instanciação do ControllerAcesso
    echo "<br><h3>7. Testando instanciação do ControllerAcesso...</h3>";
    
    try {
        $controllerAcesso = new ControllerAcesso();
        echo "✅ ControllerAcesso instanciado com sucesso<br>";
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO ao instanciar ControllerAcesso:</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        exit;
    }
    
    // 8. Testar método verificarAcessoPagina()
    echo "<br><h3>8. Testando método verificarAcessoPagina()...</h3>";
    
    try {
        // Simular que estamos na página pedidos-compra.php
        $_SERVER['PHP_SELF'] = '/sistemas/_estoquegrupoSorrisos/pedidos-compra.php';
        
        echo "✅ URL simulada: " . $_SERVER['PHP_SELF'] . "<br>";
        echo "✅ basename(): " . basename($_SERVER['PHP_SELF']) . "<br>";
        
        $resultado = $controllerAcesso->verificarAcessoPagina();
        echo "verificarAcessoPagina() retornou: " . ($resultado ? '✅ true' : '❌ false') . "<br>";
        
        if ($resultado) {
            echo "✅ <strong>SUCESSO:</strong> O método funcionou corretamente!<br>";
        } else {
            echo "❌ <strong>PROBLEMA:</strong> O método retornou false!<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERRO no método verificarAcessoPagina():</strong> " . $e->getMessage() . "<br>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    // 9. Resultado final
    echo "<br><h2>🎯 Diagnóstico de Instanciação Concluído!</h2>";
    
    if (isset($resultado) && $resultado) {
        echo "<p style='color: #28a745;'>✅ A instanciação e execução funcionaram corretamente!</p>";
        echo "<p>O problema pode estar em outro lugar.</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ A instanciação ou execução falhou!</p>";
        echo "<p>Verifique os detalhes acima para identificar a causa.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o diagnóstico:</h2>";
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
ul {
    background-color: white;
    padding: 15px 20px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 4px solid #007bff;
}
pre {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
    font-size: 12px;
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