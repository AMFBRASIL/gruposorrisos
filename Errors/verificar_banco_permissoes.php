<?php
/**
 * Verificar Banco de Dados - Sistema de Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 * 
 * Este script verifica se as tabelas necessárias existem no banco
 */

echo "<h1>🔍 Verificação do Banco de Dados - Sistema de Permissões</h1>";
echo "<hr>";

try {
    // 1. Incluir configurações
    echo "<h3>1. Incluindo configurações...</h3>";
    
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        echo "✅ database.php incluída<br>";
    } else {
        echo "❌ database.php não encontrada<br>";
        exit;
    }
    
    // 2. Testar conexão
    echo "<br><h3>2. Testando conexão com banco...</h3>";
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Conexão com banco estabelecida<br>";
    } else {
        echo "❌ Falha na conexão com banco<br>";
        exit;
    }
    
    // 3. Verificar tabelas necessárias
    echo "<br><h3>3. Verificando tabelas necessárias...</h3>";
    
    $tabelas = [
        'tbl_perfis' => 'Perfis de usuário',
        'tbl_usuarios' => 'Usuários do sistema',
        'tbl_paginas' => 'Páginas do sistema',
        'tbl_perfil_paginas' => 'Permissões de perfis por página',
        'tbl_paginas_acesso' => 'Log de acessos às páginas'
    ];
    
    foreach ($tabelas as $tabela => $descricao) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tabela]);
            
            if ($stmt->rowCount() > 0) {
                echo "✅ Tabela <strong>{$tabela}</strong> existe ({$descricao})<br>";
            } else {
                echo "❌ Tabela <strong>{$tabela}</strong> não existe ({$descricao})<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "<br>";
        }
    }
    
    // 4. Verificar estrutura das tabelas existentes
    echo "<br><h3>4. Verificando estrutura das tabelas...</h3>";
    
    $tabelasParaVerificar = ['tbl_perfis', 'tbl_paginas', 'tbl_perfil_paginas'];
    
    foreach ($tabelasParaVerificar as $tabela) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tabela]);
            
            if ($stmt->rowCount() > 0) {
                echo "<br><strong>📋 Estrutura da tabela {$tabela}:</strong><br>";
                
                $sql = "DESCRIBE {$tabela}";
                $stmt = $pdo->query($sql);
                $colunas = $stmt->fetchAll();
                
                echo "<table border='1' style='border-collapse: collapse; margin: 5px 0; font-size: 12px;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th style='padding: 5px;'>Campo</th>";
                echo "<th style='padding: 5px;'>Tipo</th>";
                echo "<th style='padding: 5px;'>Nulo</th>";
                echo "<th style='padding: 5px;'>Chave</th>";
                echo "<th style='padding: 5px;'>Padrão</th>";
                echo "<th style='padding: 5px;'>Extra</th>";
                echo "</tr>";
                
                foreach ($colunas as $coluna) {
                    echo "<tr>";
                    echo "<td style='padding: 5px;'>{$coluna['Field']}</td>";
                    echo "<td style='padding: 5px;'>{$coluna['Type']}</td>";
                    echo "<td style='padding: 5px;'>{$coluna['Null']}</td>";
                    echo "<td style='padding: 5px;'>{$coluna['Key']}</td>";
                    echo "<td style='padding: 5px;'>{$coluna['Default']}</td>";
                    echo "<td style='padding: 5px;'>{$coluna['Extra']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "❌ Erro ao verificar estrutura da tabela {$tabela}: " . $e->getMessage() . "<br>";
        }
    }
    
    // 5. Verificar dados nas tabelas
    echo "<br><h3>5. Verificando dados nas tabelas...</h3>";
    
    try {
        // Verificar perfis
        $sql = "SELECT COUNT(*) as total FROM tbl_perfis WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPerfis = $stmt->fetch()['total'];
        echo "✅ <strong>{$totalPerfis}</strong> perfis encontrados<br>";
        
        if ($totalPerfis > 0) {
            $sql = "SELECT nome_perfil, descricao FROM tbl_perfis WHERE ativo = 1 ORDER BY nome_perfil";
            $stmt = $pdo->query($sql);
            $perfis = $stmt->fetchAll();
            
            echo "<ul>";
            foreach ($perfis as $perfil) {
                echo "<li><strong>{$perfil['nome_perfil']}</strong>: {$perfil['descricao']}</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao verificar perfis: " . $e->getMessage() . "<br>";
    }
    
    try {
        // Verificar páginas
        $sql = "SELECT COUNT(*) as total FROM tbl_paginas WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPaginas = $stmt->fetch()['total'];
        echo "✅ <strong>{$totalPaginas}</strong> páginas encontradas<br>";
        
        if ($totalPaginas > 0) {
            $sql = "SELECT nome_pagina, categoria, url_pagina FROM tbl_paginas WHERE ativo = 1 ORDER BY categoria, ordem";
            $stmt = $pdo->query($sql);
            $paginas = $stmt->fetchAll();
            
            $categorias = [];
            foreach ($paginas as $pagina) {
                $cat = $pagina['categoria'] ?? 'sem categoria';
                if (!isset($categorias[$cat])) {
                    $categorias[$cat] = [];
                }
                $categorias[$cat][] = $pagina;
            }
            
            foreach ($categorias as $categoria => $paginasCat) {
                echo "<br><strong>📁 {$categoria}:</strong><br>";
                echo "<ul>";
                foreach ($paginasCat as $pagina) {
                    echo "<li>{$pagina['nome_pagina']} ({$pagina['url_pagina']})</li>";
                }
                echo "</ul>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao verificar páginas: " . $e->getMessage() . "<br>";
    }
    
    try {
        // Verificar permissões
        $sql = "SELECT COUNT(*) as total FROM tbl_perfil_paginas WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalPermissoes = $stmt->fetch()['total'];
        echo "✅ <strong>{$totalPermissoes}</strong> permissões configuradas<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao verificar permissões: " . $e->getMessage() . "<br>";
    }
    
    // 6. Verificar usuários
    echo "<br><h3>6. Verificando usuários...</h3>";
    
    try {
        $sql = "SELECT COUNT(*) as total FROM tbl_usuarios WHERE ativo = 1";
        $stmt = $pdo->query($sql);
        $totalUsuarios = $stmt->fetch()['total'];
        echo "✅ <strong>{$totalUsuarios}</strong> usuários encontrados<br>";
        
        if ($totalUsuarios > 0) {
            $sql = "SELECT u.nome_completo, u.email, p.nome_perfil 
                    FROM tbl_usuarios u 
                    JOIN tbl_perfis p ON u.id_perfil = p.id_perfil 
                    WHERE u.ativo = 1 
                    ORDER BY p.nome_perfil, u.nome_completo";
            $stmt = $pdo->query($sql);
            $usuarios = $stmt->fetchAll();
            
            $perfisUsuarios = [];
            foreach ($usuarios as $usuario) {
                $perfil = $usuario['nome_perfil'];
                if (!isset($perfisUsuarios[$perfil])) {
                    $perfisUsuarios[$perfil] = [];
                }
                $perfisUsuarios[$perfil][] = $usuario;
            }
            
            foreach ($perfisUsuarios as $perfil => $usuariosPerfil) {
                echo "<br><strong>👥 {$perfil}:</strong><br>";
                echo "<ul>";
                foreach ($usuariosPerfil as $usuario) {
                    echo "<li>{$usuario['nome_completo']} ({$usuario['email']})</li>";
                }
                echo "</ul>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao verificar usuários: " . $e->getMessage() . "<br>";
    }
    
    // 7. Resultado final
    echo "<br><h2>🎯 Verificação Concluída!</h2>";
    
    if ($totalPerfis > 0 && $totalPaginas > 0 && $totalPermissoes > 0) {
        echo "<p style='color: #28a745;'>✅ O banco de dados está configurado corretamente para o sistema de permissões!</p>";
        echo "<p>Você pode agora:</p>";
        echo "<ul>";
        echo "<li>Testar o sistema com <a href='teste_simples_permissoes.php'>teste_simples_permissoes.php</a></li>";
        echo "<li>Acessar o menu principal que agora usa permissões dinâmicas</li>";
        echo "<li>Implementar controle de acesso em suas páginas</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: #dc3545;'>❌ O banco de dados não está completamente configurado.</p>";
        echo "<p>Execute os seguintes scripts para configurar:</p>";
        echo "<ol>";
        echo "<li><a href='configurar_paginas.php'>configurar_paginas.php</a> - Para configurar páginas</li>";
        echo "<li><a href='configurar_permissoes.php'>configurar_permissoes.php</a> - Para configurar permissões</li>";
        echo "</ol>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erro durante a verificação:</h2>";
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
table {
    background-color: white;
    margin: 10px 0;
}
th {
    background-color: #f0f0f0;
    padding: 8px;
}
td {
    padding: 6px 8px;
}
ul, ol {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    margin: 10px 0;
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