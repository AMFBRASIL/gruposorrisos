<?php
/**
 * Script para verificar permissões do perfil Fornecedor
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>🔍 Verificando Permissões do Perfil 'Fornecedor'</h2>";
    
    // 1. Verificar se o perfil existe
    $sql = "SELECT * FROM tbl_perfis WHERE nome_perfil = 'Fornecedor'";
    $stmt = $pdo->query($sql);
    $perfil = $stmt->fetch();
    
    if (!$perfil) {
        echo "<p>❌ Perfil 'Fornecedor' não encontrado!</p>";
        exit;
    }
    
    echo "<p>✅ Perfil encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> {$perfil['id_perfil']}</li>";
    echo "<li><strong>Nome:</strong> {$perfil['nome_perfil']}</li>";
    echo "<li><strong>Ativo:</strong> " . ($perfil['ativo'] ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    
    // 2. Verificar se a página existe
    $sql = "SELECT * FROM tbl_paginas WHERE url_pagina = 'pedidos-fornecedores.php'";
    $stmt = $pdo->query($sql);
    $pagina = $stmt->fetch();
    
    if (!$pagina) {
        echo "<p>❌ Página 'pedidos-fornecedores.php' não encontrada!</p>";
        exit;
    }
    
    echo "<p>✅ Página encontrada:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> {$pagina['id_pagina']}</li>";
    echo "<li><strong>Nome:</strong> {$pagina['nome_pagina']}</li>";
    echo "<li><strong>URL:</strong> {$pagina['url_pagina']}</li>";
    echo "<li><strong>Categoria:</strong> {$pagina['categoria']}</li>";
    echo "<li><strong>Ativo:</strong> " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    
    // 3. Verificar permissões do perfil para esta página
    $sql = "SELECT * FROM tbl_perfil_paginas 
            WHERE id_perfil = ? AND id_pagina = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$perfil['id_perfil'], $pagina['id_pagina']]);
    $permissao = $stmt->fetch();
    
    if (!$permissao) {
        echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> Não há permissões configuradas!</p>";
        echo "<p>O perfil 'Fornecedor' não tem acesso à página 'pedidos-fornecedores.php'</p>";
        
        // 4. Inserir permissões automaticamente
        echo "<p>🔧 Inserindo permissões automaticamente...</p>";
        
        $sql = "INSERT INTO tbl_perfil_paginas (
                    id_perfil, 
                    id_pagina, 
                    permissao_visualizar, 
                    permissao_inserir, 
                    permissao_editar, 
                    permissao_excluir, 
                    ativo, 
                    data_criacao
                ) VALUES (?, ?, 1, 0, 1, 0, 1, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $perfil['id_perfil'],
            $pagina['id_pagina']
        ]);
        
        if ($resultado) {
            echo "<p>✅ Permissões inseridas com sucesso!</p>";
            echo "<ul>";
            echo "<li><strong>Visualizar:</strong> ✅ Sim</li>";
            echo "<li><strong>Inserir:</strong> ❌ Não</li>";
            echo "<li><strong>Editar:</strong> ✅ Sim</li>";
            echo "<li><strong>Excluir:</strong> ❌ Não</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ Erro ao inserir permissões</p>";
        }
        
    } else {
        echo "<p>✅ Permissões encontradas:</p>";
        echo "<ul>";
        echo "<li><strong>Visualizar:</strong> " . ($permissao['permissao_visualizar'] ? '✅ Sim' : '❌ Não') . "</li>";
        echo "<li><strong>Inserir:</strong> " . ($permissao['permissao_inserir'] ? '✅ Sim' : '❌ Não') . "</li>";
        echo "<li><strong>Editar:</strong> " . ($permissao['permissao_editar'] ? '✅ Sim' : '❌ Não') . "</li>";
        echo "<li><strong>Excluir:</strong> " . ($permissao['permissao_excluir'] ? '✅ Sim' : '❌ Não') . "</li>";
        echo "<li><strong>Ativo:</strong> " . ($permissao['ativo'] ? '✅ Sim' : '❌ Não') . "</li>";
        echo "</ul>";
    }
    
    // 5. Verificar todas as permissões do perfil Fornecedor
    echo "<hr>";
    echo "<h3>📋 Todas as Permissões do Perfil 'Fornecedor':</h3>";
    
    $sql = "SELECT pp.*, p.nome_pagina, p.url_pagina, p.categoria 
            FROM tbl_perfil_paginas pp 
            JOIN tbl_paginas p ON pp.id_pagina = p.id_pagina 
            WHERE pp.id_perfil = ? AND pp.ativo = 1 
            ORDER BY p.categoria, p.nome_pagina";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$perfil['id_perfil']]);
    $todasPermissoes = $stmt->fetchAll();
    
    if ($todasPermissoes) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>Página</th>";
        echo "<th style='padding: 8px;'>URL</th>";
        echo "<th style='padding: 8px;'>Categoria</th>";
        echo "<th style='padding: 8px;'>Visualizar</th>";
        echo "<th style='padding: 8px;'>Inserir</th>";
        echo "<th style='padding: 8px;'>Editar</th>";
        echo "<th style='padding: 8px;'>Excluir</th>";
        echo "</tr>";
        
        foreach ($todasPermissoes as $perm) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$perm['nome_pagina']}</td>";
            echo "<td style='padding: 8px;'>{$perm['url_pagina']}</td>";
            echo "<td style='padding: 8px;'>{$perm['categoria']}</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . ($perm['permissao_visualizar'] ? '✅' : '❌') . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . ($perm['permissao_inserir'] ? '✅' : '❌') . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . ($perm['permissao_editar'] ? '✅' : '❌') . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . ($perm['permissao_excluir'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhuma permissão encontrada para este perfil</p>";
    }
    
    // 6. Verificar se há usuários com este perfil
    echo "<hr>";
    echo "<h3>👥 Usuários com Perfil 'Fornecedor':</h3>";
    
    $sql = "SELECT id_usuario, nome_completo, email, ativo FROM tbl_usuarios WHERE id_perfil = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$perfil['id_perfil']]);
    $usuarios = $stmt->fetchAll();
    
    if ($usuarios) {
        echo "<ul>";
        foreach ($usuarios as $user) {
            $status = $user['ativo'] ? '✅ Ativo' : '❌ Inativo';
            echo "<li><strong>{$user['nome_completo']}</strong> ({$user['email']}) - {$status}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ Nenhum usuário encontrado com este perfil</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>🎯 Próximos passos após corrigir permissões:</strong></p>";
    echo "<ol>";
    echo "<li>✅ Permissões configuradas</li>";
    echo "<li>🔄 Fazer logout e login novamente</li>";
    echo "<li>📱 Verificar se a página aparece no menu</li>";
    echo "<li>🔍 Verificar se a página carrega corretamente</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
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
h2, h3 {
    color: #333;
}
table {
    margin: 15px 0;
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
</style> 