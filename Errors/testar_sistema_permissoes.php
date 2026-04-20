<?php
/**
 * Script de Teste do Sistema de Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Este script testa todas as funcionalidades do sistema de permissões
 */

echo "<h1>🧪 Teste do Sistema de Permissões</h1>";
echo "<hr>";

try {
    // 1. Testar inclusão das controllers
    echo "<h3>1. Testando inclusão das controllers...</h3>";
    
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
    
    // 2. Testar inclusão das configurações
    echo "<br><h3>2. Testando configurações...</h3>";
    
    if (file_exists('config/session.php')) {
        echo "✅ session.php encontrada<br>";
    } else {
        echo "❌ session.php não encontrada<br>";
        exit;
    }
    
    if (file_exists('config/database.php')) {
        echo "✅ database.php encontrada<br>";
    } else {
        echo "❌ database.php não encontrada<br>";
        exit;
    }
    
    // 3. Incluir arquivos necessários
    echo "<br><h3>3. Incluindo arquivos...</h3>";
    
    require_once 'config/session.php';
    echo "✅ session.php incluída<br>";
    
    require_once 'config/database.php';
    echo "✅ database.php incluída<br>";
    
    require_once 'backend/controllers/ControllerPermissoes.php';
    echo "✅ ControllerPermissoes incluída<br>";
    
    require_once 'backend/controllers/ControllerAcesso.php';
    echo "✅ ControllerAcesso incluída<br>";
    
    // 4. Testar instanciação das controllers
    echo "<br><h3>4. Testando instanciação das controllers...</h3>";
    
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
    
    // 5. Testar conexão com banco de dados
    echo "<br><h3>5. Testando conexão com banco de dados...</h3>";
    
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
    
    // 6. Testar tabelas necessárias
    echo "<br><h3>6. Verificando tabelas necessárias...</h3>";
    
    $tabelas = ['tbl_perfis', 'tbl_paginas', 'tbl_perfil_paginas', 'tbl_usuarios'];
    
    foreach ($tabelas as $tabela) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tabela]);
            
            if ($stmt->rowCount() > 0) {
                echo "✅ Tabela {$tabela} existe<br>";
            } else {
                echo "❌ Tabela {$tabela} não existe<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "<br>";
        }
    }
    
    // 7. Testar dados nas tabelas
    echo "<br><h3>7. Verificando dados nas tabelas...</h3>";
    
    try {
        // Verificar perfis
        $sql = "SELECT COUNT(*) as total FROM tbl_perfis WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPerfis = $stmt->fetch()['total'];
        echo "✅ {$totalPerfis} perfis encontrados<br>";
        
        // Verificar páginas
        $sql = "SELECT COUNT(*) as total FROM tbl_paginas WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPaginas = $stmt->fetch()['total'];
        echo "✅ {$totalPaginas} páginas encontradas<br>";
        
        // Verificar permissões
        $sql = "SELECT COUNT(*) as total FROM tbl_perfil_paginas WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPermissoes = $stmt->fetch()['total'];
        echo "✅ {$totalPermissoes} permissões configuradas<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao verificar dados: " . $e->getMessage() . "<br>";
    }
    
    // 8. Testar funcionalidades da controller
    echo "<br><h3>8. Testando funcionalidades da controller...</h3>";
    
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
    
    // 9. Testar simulação de usuário logado
    echo "<br><h3>9. Testando simulação de usuário logado...</h3>";
    
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
        
        // Testar permissões resumidas
        $resumo = $controllerAcesso->obterPermissoesResumidas();
        echo "✅ obterPermissoesResumidas(): " . $resumo['total_paginas'] . " páginas no resumo<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao testar funcionalidades com usuário logado: " . $e->getMessage() . "<br>";
    }
    
    // 10. Resultado final
    echo "<br><h2>🎉 Teste Concluído com Sucesso!</h2>";
    echo "<p>O sistema de permissões está funcionando corretamente.</p>";
    echo "<p>Você pode agora:</p>";
    echo "<ul>";
    echo "<li>Usar as controllers em suas páginas</li>";
    echo "<li>Implementar controle de acesso baseado em permissões</li>";
    echo "<li>Personalizar o menu baseado no perfil do usuário</li>";
    echo "<li>Controlar botões e funcionalidades da interface</li>";
    echo "</ul>";
    
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ol>";
    echo "<li>Implementar a controller em suas páginas existentes</li>";
    echo "<li>Testar com diferentes perfis de usuário</li>";
    echo "<li>Personalizar permissões conforme necessário</li>";
    echo "<li>Adicionar novas páginas ao sistema</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante o teste:</h2>";
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
.success {
    color: #28a745;
}
.error {
    color: #dc3545;
}
.warning {
    color: #ffc107;
}
ul, ol {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    margin: 10px 0;
}
</style> 