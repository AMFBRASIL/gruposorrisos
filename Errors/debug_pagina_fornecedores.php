<?php
/**
 * Debug da página pedidos-fornecedores.php
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h2>🔍 Debug da Página pedidos-fornecedores.php</h2>";

// 1. Verificar se o arquivo existe
if (!file_exists('pedidos-fornecedores.php')) {
    echo "<p>❌ Arquivo pedidos-fornecedores.php não encontrado!</p>";
    exit;
}

echo "<p>✅ Arquivo encontrado</p>";

// 2. Verificar se as dependências existem
$dependencias = [
    'config/config.php' => 'Configuração',
    'config/session.php' => 'Sessão',
    'backend/controllers/ControllerAcesso.php' => 'Controller de Acesso',
    'menu_dinamico.php' => 'Menu Dinâmico'
];

echo "<h3>📋 Verificando Dependências:</h3>";
foreach ($dependencias as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "<p>✅ {$descricao}: {$arquivo}</p>";
    } else {
        echo "<p>❌ {$descricao}: {$arquivo} - <strong>ARQUIVO NÃO ENCONTRADO!</strong></p>";
    }
}

// 3. Verificar se há erros de sintaxe
echo "<h3>🔍 Verificando Sintaxe PHP:</h3>";
$output = [];
$return_var = 0;
exec("php -l pedidos-fornecedores.php 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "<p>✅ Sintaxe PHP está correta</p>";
} else {
    echo "<p>❌ Erro de sintaxe PHP:</p>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
}

// 4. Verificar se há erros de inclusão
echo "<h3>🔍 Testando Inclusões:</h3>";

// Testar config.php
if (file_exists('config/config.php')) {
    try {
        require_once 'config/config.php';
        echo "<p>✅ config.php incluído com sucesso</p>";
        if (defined('APP_NAME')) {
            echo "<p>✅ APP_NAME definido: " . APP_NAME . "</p>";
        } else {
            echo "<p>⚠️ APP_NAME não está definido</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro ao incluir config.php: " . $e->getMessage() . "</p>";
    }
}

// Testar session.php
if (file_exists('config/session.php')) {
    try {
        require_once 'config/session.php';
        echo "<p>✅ session.php incluído com sucesso</p>";
        if (function_exists('isLoggedIn')) {
            echo "<p>✅ Função isLoggedIn() existe</p>";
        } else {
            echo "<p>⚠️ Função isLoggedIn() não existe</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro ao incluir session.php: " . $e->getMessage() . "</p>";
    }
}

// Testar ControllerAcesso
if (file_exists('backend/controllers/ControllerAcesso.php')) {
    try {
        require_once 'backend/controllers/ControllerAcesso.php';
        echo "<p>✅ ControllerAcesso.php incluído com sucesso</p>";
        if (class_exists('ControllerAcesso')) {
            echo "<p>✅ Classe ControllerAcesso existe</p>";
        } else {
            echo "<p>⚠️ Classe ControllerAcesso não existe</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro ao incluir ControllerAcesso.php: " . $e->getMessage() . "</p>";
    }
}

// 5. Verificar se há erros de sessão
echo "<h3>🔍 Verificando Sessão:</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>✅ Sessão iniciada</p>";
} else {
    echo "<p>✅ Sessão já está ativa</p>";
}

// Simular usuário logado para teste
$_SESSION['logged_in'] = true;
$_SESSION['usuario_id'] = 5;
$_SESSION['usuario_perfil'] = 'Fornecedor';
$_SESSION['usuario_perfil_id'] = 5;

echo "<p>✅ Sessão simulada criada</p>";
echo "<ul>";
echo "<li><strong>logged_in:</strong> " . ($_SESSION['logged_in'] ? 'true' : 'false') . "</li>";
echo "<li><strong>usuario_id:</strong> {$_SESSION['usuario_id']}</li>";
echo "<li><strong>usuario_perfil:</strong> {$_SESSION['usuario_perfil']}</li>";
echo "<li><strong>usuario_perfil_id:</strong> {$_SESSION['usuario_perfil_id']}</li>";
echo "</ul>";

// 6. Testar verificação de perfil
if (function_exists('isLoggedIn')) {
    $isLoggedIn = isLoggedIn();
    echo "<p>isLoggedIn() retornou: " . ($isLoggedIn ? '✅ true' : '❌ false') . "</p>";
}

// 7. Verificar se o perfil é fornecedor
if ($_SESSION['usuario_perfil'] === 'Fornecedor') {
    echo "<p>✅ Perfil verificado: É fornecedor</p>";
} else {
    echo "<p>❌ Perfil incorreto: {$_SESSION['usuario_perfil']}</p>";
}

// 8. Testar criação do ControllerAcesso
echo "<h3>🔍 Testando ControllerAcesso:</h3>";
try {
    if (class_exists('ControllerAcesso')) {
        $controllerAcesso = new ControllerAcesso();
        echo "<p>✅ ControllerAcesso instanciado com sucesso</p>";
        
        // Testar método obterMenuUsuario
        if (method_exists($controllerAcesso, 'obterMenuUsuario')) {
            $menuUsuario = $controllerAcesso->obterMenuUsuario();
            echo "<p>✅ obterMenuUsuario() executado</p>";
            echo "<p>📊 Menu retornou " . count($menuUsuario) . " categorias</p>";
            
            // Mostrar debug do menu
            echo "<details>";
            echo "<summary>🔍 Ver conteúdo do menu (clique para expandir)</summary>";
            echo "<pre>" . print_r($menuUsuario, true) . "</pre>";
            echo "</details>";
        } else {
            echo "<p>❌ Método obterMenuUsuario() não existe</p>";
        }
    } else {
        echo "<p>❌ Classe ControllerAcesso não existe</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao instanciar ControllerAcesso: " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}

// 9. Verificar se há erros de output
echo "<h3>🔍 Verificando Output:</h3>";
ob_start();
include 'pedidos-fornecedores.php';
$output = ob_get_clean();

if (empty(trim($output))) {
    echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> A página não está gerando nenhum output!</p>";
    echo "<p>Isso pode indicar:</p>";
    echo "<ul>";
    echo "<li>Erro fatal que impede a execução</li>";
    echo "<li>Redirecionamento que não está funcionando</li>";
    echo "<li>Problema com o menu_dinamico.php</li>";
    echo "<li>Erro na verificação de perfil</li>";
    echo "</ul>";
} else {
    echo "<p>✅ Página está gerando output</p>";
    echo "<p>📏 Tamanho do output: " . strlen($output) . " caracteres</p>";
    
    // Mostrar primeiras linhas do output
    $primeirasLinhas = explode("\n", $output);
    $primeirasLinhas = array_slice($primeirasLinhas, 0, 10);
    
    echo "<details>";
    echo "<summary>🔍 Ver primeiras linhas do output (clique para expandir)</summary>";
    echo "<pre>" . implode("\n", $primeirasLinhas) . "</pre>";
    echo "</details>";
}

echo "<hr>";
echo "<p><strong>🎯 Resumo do Debug:</strong></p>";
echo "<ol>";
echo "<li>✅ Arquivo existe</li>";
echo "<li>✅ Dependências verificadas</li>";
echo "<li>✅ Sintaxe PHP verificada</li>";
echo "<li>✅ Sessão configurada</li>";
echo "<li>✅ Perfil verificado</li>";
echo "<li>✅ ControllerAcesso testado</li>";
echo "<li>🔍 Output analisado</li>";
echo "</ol>";

echo "<p><strong>💡 Dica:</strong> Se a página ainda estiver em branco, verifique o console do navegador (F12) para erros JavaScript</p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
h2, h3 {
    color: #333;
}
ul, ol {
    background: white;
    padding: 15px 20px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
p {
    background: white;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 10px 0;
}
details {
    background: white;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}
summary {
    cursor: pointer;
    font-weight: bold;
    color: #007bff;
}
pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
    font-size: 12px;
}
</style> 