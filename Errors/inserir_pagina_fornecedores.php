<?php
/**
 * Script para inserir a página pedidos-fornecedores.php
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>🔧 Inserindo Página para Fornecedores</h2>";
    
    // Verificar se a página já existe
    $sql = "SELECT id_pagina, nome_pagina FROM tbl_paginas WHERE url_pagina = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pedidos-fornecedores.php']);
    $paginaExistente = $stmt->fetch();
    
    if ($paginaExistente) {
        echo "<p>⚠️ A página <strong>{$paginaExistente['nome_pagina']}</strong> já existe com ID: {$paginaExistente['id_pagina']}</p>";
        echo "<p>✅ Não é necessário inserir novamente.</p>";
    } else {
        // Inserir nova página
        $sql = "INSERT INTO tbl_paginas (
                    nome_pagina, 
                    url_pagina, 
                    categoria, 
                    icone, 
                    descricao, 
                    ativo, 
                    data_criacao
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            'Pedidos para Fornecedor',
            'pedidos-fornecedores.php',
            'compras',
            'bi-truck',
            'Página para fornecedores visualizarem e responderem pedidos de compra'
        ]);
        
        if ($resultado) {
            $idPagina = $pdo->lastInsertId();
            echo "<p>✅ Página inserida com sucesso!</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$idPagina}</li>";
            echo "<li><strong>Nome:</strong> Pedidos para Fornecedor</li>";
            echo "<li><strong>URL:</strong> pedidos-fornecedores.php</li>";
            echo "<li><strong>Categoria:</strong> compras</li>";
            echo "<li><strong>Ícone:</strong> bi-truck</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ Erro ao inserir página</p>";
        }
    }
    
    // Mostrar todas as páginas da categoria 'compras'
    echo "<hr>";
    echo "<h3>📋 Páginas da Categoria 'Compras':</h3>";
    
    $sql = "SELECT id_pagina, nome_pagina, url_pagina, ativo FROM tbl_paginas WHERE categoria = 'compras' ORDER BY nome_pagina";
    $stmt = $pdo->query($sql);
    $paginasCompras = $stmt->fetchAll();
    
    if ($paginasCompras) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nome da Página</th>";
        echo "<th style='padding: 8px;'>URL</th>";
        echo "<th style='padding: 8px;'>Status</th>";
        echo "</tr>";
        
        foreach ($paginasCompras as $pagina) {
            $status = $pagina['ativo'] ? '✅ Ativo' : '❌ Inativo';
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$pagina['id_pagina']}</td>";
            echo "<td style='padding: 8px;'>{$pagina['nome_pagina']}</td>";
            echo "<td style='padding: 8px;'>{$pagina['url_pagina']}</td>";
            echo "<td style='padding: 8px;'>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhuma página encontrada na categoria 'compras'</p>";
    }
    
    // Mostrar todas as categorias disponíveis
    echo "<hr>";
    echo "<h3>🏷️ Categorias Disponíveis:</h3>";
    
    $sql = "SELECT DISTINCT categoria FROM tbl_paginas WHERE ativo = 1 ORDER BY categoria";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll();
    
    if ($categorias) {
        echo "<ul>";
        foreach ($categorias as $cat) {
            echo "<li><strong>{$cat['categoria']}</strong></li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<p><strong>🎯 Próximos passos:</strong></p>";
    echo "<ol>";
    echo "<li>✅ Página inserida no banco</li>";
    echo "<li>🔐 Configurar permissões para o perfil 'Fornecedor'</li>";
    echo "<li>📱 Testar se aparece no menu</li>";
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