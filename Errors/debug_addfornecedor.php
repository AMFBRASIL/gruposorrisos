<?php
/**
 * Debug específico para addFornecedor.php
 * Para identificar exatamente onde está o problema
 */

require_once 'config/config.php';
require_once 'config/session.php';
require_once 'backend/controllers/ControllerAcesso.php';

echo "<h1>🔍 Debug Específico - addFornecedor.php</h1>";

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
    echo "<h2>🔧 Inicializando ControllerAcesso...</h2>";
    $controllerAcesso = new ControllerAcesso();
    echo "<p>✅ ControllerAcesso instanciado com sucesso</p>";
    
    echo "<h2>📋 Testando permissões específicas...</h2>";
    
    // Testar permissão de inserir fornecedores
    echo "<h3>1. Testando permissão de INSERIR fornecedores:</h3>";
    echo "<p>Verificando permissão de 'inserir' em 'fornecedores.php'...</p>";
    
    $podeInserir = $controllerAcesso->verificarEAutorizar('inserir', 'fornecedores.php', false);
    echo "<p>Resultado: " . ($podeInserir ? '✅ PODE inserir' : '❌ NÃO pode inserir') . "</p>";
    
    if (!$podeInserir) {
        echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não tem permissão para inserir fornecedores</p>";
        echo "<p>🔧 <strong>SOLUÇÃO:</strong> Verificar se o perfil '{$_SESSION['usuario_perfil']}' tem permissão de inserir na tabela tbl_perfil_paginas</p>";
    } else {
        echo "<p>✅ Usuário pode inserir fornecedores</p>";
    }
    
    // Testar se a página fornecedores.php existe na tabela
    echo "<h3>2. Verificando se 'fornecedores.php' existe na tabela de páginas:</h3>";
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM tbl_paginas WHERE url_pagina = ?");
        $stmt->execute(['fornecedores.php']);
        $pagina = $stmt->fetch();
        
        if ($pagina) {
            echo "<p>✅ Página 'fornecedores.php' encontrada na tabela:</p>";
            echo "<ul>";
            echo "<li>ID: {$pagina['id_pagina']}</li>";
            echo "<li>Nome: {$pagina['nome_pagina']}</li>";
            echo "<li>Categoria: {$pagina['categoria']}</li>";
            echo "<li>Ativo: " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ <strong>PROBLEMA CRÍTICO:</strong> Página 'fornecedores.php' NÃO encontrada na tabela tbl_paginas!</p>";
        }
        
        // Verificar permissões específicas do usuário
        echo "<h3>3. Verificando permissões do usuário na tabela tbl_perfil_paginas:</h3>";
        
        $stmt = $pdo->prepare("
            SELECT pp.*, p.nome_pagina 
            FROM tbl_perfil_paginas pp 
            JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina 
            WHERE pp.id_perfil = ? AND p.url_pagina = ? AND pp.ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_perfil_id'], 'fornecedores.php']);
        $permissoes = $stmt->fetch();
        
        if ($permissoes) {
            echo "<p>✅ Permissões encontradas para o usuário:</p>";
            echo "<ul>";
            echo "<li>Visualizar: " . ($permissoes['permissao_visualizar'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li>Inserir: " . ($permissoes['permissao_inserir'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li>Editar: " . ($permissoes['permissao_editar'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "<li>Excluir: " . ($permissoes['permissao_excluir'] ? '✅ Sim' : '❌ Não') . "</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ <strong>PROBLEMA CRÍTICO:</strong> Nenhuma permissão encontrada para o usuário na página 'fornecedores.php'!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Erro ao consultar banco: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎯 Diagnóstico Final:</h2>";
    
    if ($podeInserir) {
        echo "<p>✅ <strong>PERMISSÕES OK:</strong> Usuário tem todas as permissões necessárias</p>";
        echo "<p>🔍 <strong>PRÓXIMO PASSO:</strong> O problema pode estar em outro lugar do código</p>";
    } else {
        echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> Usuário não tem permissão para inserir fornecedores</p>";
        echo "<p>🔧 <strong>SOLUÇÃO:</strong> Configurar permissões corretas na tabela tbl_perfil_paginas</p>";
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
h1, h2, h3 { color: #333; }
.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
</style> 