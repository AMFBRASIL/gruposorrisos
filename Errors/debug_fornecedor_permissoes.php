<?php
/**
 * Debug das permissões de fornecedor
 * Para identificar por que está redirecionando para error.php
 */

require_once 'config/config.php';
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

echo "<h1>🔍 Debug de Permissões - Fornecedor</h1>";

try {
    // Verificar se usuário está logado
    if (!isLoggedIn()) {
        echo "<p>❌ Usuário não está logado</p>";
        exit;
    }
    
    echo "<p>✅ Usuário logado: " . $_SESSION['usuario_nome'] . "</p>";
    echo "<p>✅ Perfil: " . $_SESSION['usuario_perfil'] . "</p>";
    echo "<p>✅ ID Perfil: " . $_SESSION['usuario_perfil_id'] . "</p>";
    
    // Inicializar controller de acesso
    $controllerAcesso = new ControllerAcesso();
    
    echo "<h2>📋 Testando permissões...</h2>";
    
    // Testar permissão de visualizar fornecedores.php
    echo "<h3>1. Testando permissão de VISUALIZAR fornecedores.php:</h3>";
    $podeVisualizar = $controllerAcesso->verificarEAutorizar('visualizar', 'fornecedores.php', false);
    echo "Resultado: " . ($podeVisualizar ? '✅ PODE visualizar' : '❌ NÃO pode visualizar') . "<br>";
    
    // Testar permissão de inserir fornecedores
    echo "<h3>2. Testando permissão de INSERIR fornecedores:</h3>";
    $podeInserir = $controllerAcesso->verificarEAutorizar('inserir', 'fornecedores.php', false);
    echo "Resultado: " . ($podeInserir ? '✅ PODE inserir' : '❌ NÃO pode inserir') . "<br>";
    
    // Testar permissão de editar fornecedores
    echo "<h3>3. Testando permissão de EDITAR fornecedores:</h3>";
    $podeEditar = $controllerAcesso->verificarEAutorizar('editar', 'fornecedores.php', false);
    echo "Resultado: " . ($podeEditar ? '✅ PODE editar' : '❌ NÃO pode editar') . "<br>";
    
    // Testar permissão de excluir fornecedores
    echo "<h3>4. Testando permissão de EXCLUIR fornecedores:</h3>";
    $podeExcluir = $controllerAcesso->verificarEAutorizar('excluir', 'fornecedores.php', false);
    echo "Resultado: " . ($podeExcluir ? '✅ PODE excluir' : '❌ NÃO pode excluir') . "<br>";
    
    // Verificar menu do usuário
    echo "<h3>5. Menu disponível para o usuário:</h3>";
    $menu = $controllerAcesso->obterMenuUsuario();
    if (!empty($menu)) {
        foreach ($menu as $categoria => $dados) {
            echo "<h4>📁 {$dados['nome']}:</h4>";
            if (isset($dados['paginas'])) {
                foreach ($dados['paginas'] as $pagina) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;• {$pagina['nome_pagina']} ({$pagina['url_pagina']})<br>";
                }
            }
        }
    } else {
        echo "<p>❌ Nenhuma página disponível no menu</p>";
    }
    
    echo "<h2>🎯 Diagnóstico:</h2>";
    
    if (!$podeVisualizar) {
        echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não tem permissão para VISUALIZAR fornecedores.php</p>";
        echo "<p>🔧 <strong>SOLUÇÃO:</strong> Verificar se o perfil '{$_SESSION['usuario_perfil']}' tem permissão de visualizar na tabela tbl_perfil_paginas</p>";
    } else {
        echo "<p>✅ Usuário pode visualizar fornecedores.php</p>";
    }
    
    if (!$podeInserir) {
        echo "<p>⚠️ <strong>AVISO:</strong> Usuário não tem permissão para INSERIR fornecedores</p>";
        echo "<p>🔧 <strong>SOLUÇÃO:</strong> Verificar se o perfil '{$_SESSION['usuario_perfil']}' tem permissão de inserir na tabela tbl_perfil_paginas</p>";
    } else {
        echo "<p>✅ Usuário pode inserir fornecedores</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro no debug:</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
</style> 