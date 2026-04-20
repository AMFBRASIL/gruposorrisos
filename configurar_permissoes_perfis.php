<?php
/**
 * Script para configurar permissões específicas para cada perfil
 * Este script define quais páginas cada perfil pode acessar
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>🔐 Configuração de Permissões por Perfil</h1>";
echo "<p>Configurando acesso específico para cada perfil do sistema</p>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📊 Verificando perfis existentes...</h2>";
    
    // Listar perfis existentes
    $stmt = $pdo->query("SELECT id_perfil, nome_perfil FROM tbl_perfis WHERE ativo = 1 ORDER BY nome_perfil");
    $perfis = $stmt->fetchAll();
    
    echo "<h3>Perfis encontrados:</h3>";
    foreach ($perfis as $perfil) {
        echo "• <strong>{$perfil['nome_perfil']}</strong> (ID: {$perfil['id_perfil']})<br>";
    }
    
    echo "<h2>📋 Verificando páginas disponíveis...</h2>";
    
    // Listar páginas disponíveis
    $stmt = $pdo->query("SELECT id_pagina, nome_pagina, url_pagina, categoria FROM tbl_paginas WHERE ativo = 1 ORDER BY categoria, nome_pagina");
    $paginas = $stmt->fetchAll();
    
    echo "<h3>Páginas disponíveis:</h3>";
    $categorias = [];
    foreach ($paginas as $pagina) {
        $categoria = $pagina['categoria'];
        if (!isset($categorias[$categoria])) {
            $categorias[$categoria] = [];
        }
        $categorias[$categoria][] = $pagina;
    }
    
    foreach ($categorias as $categoria => $paginasCategoria) {
        echo "<h4>📁 {$categoria}:</h4>";
        foreach ($paginasCategoria as $pagina) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;• <strong>{$pagina['nome_pagina']}</strong> ({$pagina['url_pagina']}) - ID: {$pagina['id_pagina']}<br>";
        }
    }
    
    echo "<h2>⚙️ Configurando permissões...</h2>";
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // 1. ADMINISTRADOR - Acesso apenas a páginas de gestão e controle
    echo "<h3>👑 Configurando Administrador...</h3>";
    $adminId = null;
    foreach ($perfis as $perfil) {
        if (strtolower($perfil['nome_perfil']) === 'administrador') {
            $adminId = $perfil['id_perfil'];
            break;
        }
    }
    
    if ($adminId) {
        // Remover permissões existentes
        $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
        $stmt->execute([$adminId]);
        
        // Páginas permitidas para administrador (apenas gestão e controle)
        $categoriasAdmin = ['gestao', 'configuracoes'];
        
        $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($paginas as $pagina) {
            if (in_array($pagina['categoria'], $categoriasAdmin)) {
                $stmt->execute([$adminId, $pagina['id_pagina'], 1, 1, 1, 1]);
            }
        }
        echo "✅ Administrador configurado com acesso a " . count($categoriasAdmin) . " categorias (gestão e controle)<br>";
    }
    
    // 2. FORNECEDOR - Acesso apenas a páginas específicas
    echo "<h3>🏪 Configurando Fornecedor...</h3>";
    $fornecedorId = null;
    foreach ($perfis as $perfil) {
        if (strtolower($perfil['nome_perfil']) === 'fornecedor') {
            $fornecedorId = $perfil['id_perfil'];
            break;
        }
    }
    
    if ($fornecedorId) {
        // Remover permissões existentes
        $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
        $stmt->execute([$fornecedorId]);
        
        // Páginas permitidas para fornecedor
        $paginasFornecedor = [
            'pedidos-fornecedores.php' => ['visualizar' => 1, 'inserir' => 0, 'editar' => 1, 'excluir' => 0],
            'fornecedores.php' => ['visualizar' => 1, 'inserir' => 0, 'editar' => 0, 'excluir' => 0]
        ];
        
        $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($paginasFornecedor as $urlPagina => $permissoes) {
            // Buscar ID da página
            $stmtPagina = $pdo->prepare("SELECT id_pagina FROM tbl_paginas WHERE url_pagina = ?");
            $stmtPagina->execute([$urlPagina]);
            $pagina = $stmtPagina->fetch();
            
            if ($pagina) {
                $stmt->execute([
                    $fornecedorId, 
                    $pagina['id_pagina'],
                    $permissoes['visualizar'],
                    $permissoes['inserir'],
                    $permissoes['editar'],
                    $permissoes['excluir']
                ]);
                echo "✅ Fornecedor: {$urlPagina} configurado<br>";
            }
        }
    }
    
    // 3. GERENTE - Acesso a gestão e estoque
    echo "<h3>👔 Configurando Gerente...</h3>";
    $gerenteId = null;
    foreach ($perfis as $perfil) {
        if (strtolower($perfil['nome_perfil']) === 'gerente') {
            $gerenteId = $perfil['id_perfil'];
            break;
        }
    }
    
    if ($gerenteId) {
        // Remover permissões existentes
        $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
        $stmt->execute([$gerenteId]);
        
        // Páginas permitidas para gerente (gestão operacional)
        $categoriasGerente = ['gestao', 'estoque', 'compras', 'relatorios'];
        
        $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($paginas as $pagina) {
            if (in_array($pagina['categoria'], $categoriasGerente)) {
                $stmt->execute([$gerenteId, $pagina['id_pagina'], 1, 1, 1, 0]);
            }
        }
        echo "✅ Gerente configurado com acesso a " . count($categoriasGerente) . " categorias<br>";
    }
    
    // 4. OPERADOR - Acesso limitado
    echo "<h3>👷 Configurando Operador...</h3>";
    $operadorId = null;
    foreach ($perfis as $perfil) {
        if (strtolower($perfil['nome_perfil']) === 'operador') {
            $operadorId = $perfil['id_perfil'];
            break;
        }
    }
    
    if ($operadorId) {
        // Remover permissões existentes
        $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
        $stmt->execute([$operadorId]);
        
        // Páginas permitidas para operador
        $categoriasOperador = ['estoque', 'compras'];
        
        $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($paginas as $pagina) {
            if (in_array($pagina['categoria'], $categoriasOperador)) {
                $stmt->execute([$operadorId, $pagina['id_pagina'], 1, 1, 0, 0]);
            }
        }
        echo "✅ Operador configurado com acesso limitado<br>";
    }
    
    // 5. VISUALIZADOR - Acesso apenas para visualização
    echo "<h3>👁️ Configurando Visualizador...</h3>";
    $visualizadorId = null;
    foreach ($perfis as $perfil) {
        if (strtolower($perfil['nome_perfil']) === 'visualizador') {
            $visualizadorId = $perfil['id_perfil'];
            break;
        }
    }
    
    if ($visualizadorId) {
        // Remover permissões existentes
        $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
        $stmt->execute([$visualizadorId]);
        
        // Páginas permitidas para visualizador (apenas visualização)
        $categoriasVisualizador = ['estoque', 'compras', 'relatorios'];
        
        $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($paginas as $pagina) {
            if (in_array($pagina['categoria'], $categoriasVisualizador)) {
                $stmt->execute([$visualizadorId, $pagina['id_pagina'], 1, 0, 0, 0]);
            }
        }
        echo "✅ Visualizador configurado com acesso apenas para visualização<br>";
    }
    
    // 6. SUPERVISOR DE COMPRAS - Acesso a compras e estoque
    echo "<h3>🛒 Configurando Supervisor de Compras...</h3>";
    
    // Criar perfil Supervisor se não existir
    $stmt = $pdo->prepare("SELECT id_perfil FROM tbl_perfis WHERE nome_perfil = 'Supervisor'");
    $stmt->execute();
    $supervisor = $stmt->fetch();
    
    if (!$supervisor) {
        // Inserir perfil Supervisor
        $stmt = $pdo->prepare("INSERT INTO tbl_perfis (nome_perfil, descricao, ativo) VALUES (?, ?, 1)");
        $stmt->execute(['Supervisor', 'Supervisor de Compras e Estoque']);
        $supervisorId = $pdo->lastInsertId();
        echo "✅ Perfil Supervisor criado com ID: {$supervisorId}<br>";
    } else {
        $supervisorId = $supervisor['id_perfil'];
        echo "✅ Perfil Supervisor já existe com ID: {$supervisorId}<br>";
    }
    
    // Remover permissões existentes
    $stmt = $pdo->prepare("DELETE FROM tbl_perfil_paginas WHERE id_perfil = ?");
    $stmt->execute([$supervisorId]);
    
    // Páginas permitidas para supervisor (compras e estoque)
    $categoriasSupervisor = ['estoque', 'compras'];
    
    $sql = "INSERT INTO tbl_perfil_paginas (id_perfil, id_pagina, permissao_visualizar, permissao_inserir, permissao_editar, permissao_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($paginas as $pagina) {
        if (in_array($pagina['categoria'], $categoriasSupervisor)) {
            $stmt->execute([$supervisorId, $pagina['id_pagina'], 1, 1, 1, 1]);
        }
    }
    echo "✅ Supervisor configurado com acesso a " . count($categoriasSupervisor) . " categorias (compras e estoque)<br>";
    
    // Commit das alterações
    $pdo->commit();
    
    echo "<h2>🎯 Configuração concluída!</h2>";
    echo "<p>✅ Todas as permissões foram configuradas com sucesso!</p>";
    
    // Verificar resultado
    echo "<h3>📊 Resumo das permissões configuradas:</h3>";
    foreach ($perfis as $perfil) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_perfil_paginas WHERE id_perfil = ? AND ativo = 1");
        $stmt->execute([$perfil['id_perfil']]);
        $total = $stmt->fetch()['total'];
        
        echo "• <strong>{$perfil['nome_perfil']}</strong>: {$total} páginas permitidas<br>";
    }
    
    echo "<h3>🚀 Próximos passos:</h3>";
    echo "<p>1. ✅ Permissões configuradas</p>";
    echo "<p>2. 🔄 Faça logout e login novamente</p>";
    echo "<p>3. 📱 Teste o menu para cada perfil</p>";
    echo "<p>4. 🎯 Cada usuário verá apenas suas páginas permitidas</p>";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "<h2>❌ Erro na configuração</h2>";
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