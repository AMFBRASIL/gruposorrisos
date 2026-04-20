<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "Inserindo dados de exemplo para perfis de acesso...\n";
    
    // Inserir páginas do sistema
    $paginas = [
        ['nome_pagina' => 'Dashboard', 'url_pagina' => 'index.php', 'descricao' => 'Página principal do sistema'],
        ['nome_pagina' => 'Materiais', 'url_pagina' => 'materiais.php', 'descricao' => 'Gestão de materiais e produtos'],
        ['nome_pagina' => 'Fornecedores', 'url_pagina' => 'fornecedores.php', 'descricao' => 'Gestão de fornecedores'],
        ['nome_pagina' => 'Usuários', 'url_pagina' => 'usuarios.php', 'descricao' => 'Gestão de usuários do sistema'],
        ['nome_pagina' => 'Perfil de Acesso', 'url_pagina' => 'perfil-acesso.php', 'descricao' => 'Gestão de perfis de acesso'],
        ['nome_pagina' => 'Filiais', 'url_pagina' => 'filiais.php', 'descricao' => 'Gestão de filiais'],
        ['nome_pagina' => 'Movimentações', 'url_pagina' => 'movimentacoes.php', 'descricao' => 'Controle de movimentações de estoque'],
        ['nome_pagina' => 'Relatórios', 'url_pagina' => 'relatorios.php', 'descricao' => 'Relatórios do sistema'],
        ['nome_pagina' => 'Alertas', 'url_pagina' => 'alertas.php', 'descricao' => 'Sistema de alertas'],
        ['nome_pagina' => 'Configurações', 'url_pagina' => 'configuracoes.php', 'descricao' => 'Configurações do sistema']
    ];
    
    $sqlPaginas = "INSERT INTO tbl_paginas (nome_pagina, url_pagina, descricao, ativo) VALUES (?, ?, ?, 1)";
    $stmtPaginas = $pdo->prepare($sqlPaginas);
    
    foreach ($paginas as $pagina) {
        // Verificar se já existe
        $checkSql = "SELECT COUNT(*) FROM tbl_paginas WHERE nome_pagina = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$pagina['nome_pagina']]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $stmtPaginas->execute([$pagina['nome_pagina'], $pagina['url_pagina'], $pagina['descricao']]);
            echo "Página '{$pagina['nome_pagina']}' inserida.\n";
        } else {
            echo "Página '{$pagina['nome_pagina']}' já existe.\n";
        }
    }
    
    // Inserir perfis padrão
    $perfis = [
        [
            'nome_perfil' => 'Administrador',
            'descricao' => 'Acesso total ao sistema, pode gerenciar usuários, configurações e todos os módulos'
        ],
        [
            'nome_perfil' => 'Gerente',
            'descricao' => 'Acesso gerencial, pode visualizar relatórios e gerenciar estoque'
        ],
        [
            'nome_perfil' => 'Operador',
            'descricao' => 'Acesso operacional, pode registrar movimentações e consultar estoque'
        ],
        [
            'nome_perfil' => 'Visualizador',
            'descricao' => 'Acesso apenas para visualização, não pode fazer alterações'
        ]
    ];
    
    $sqlPerfis = "INSERT INTO tbl_perfis (nome_perfil, descricao, ativo) VALUES (?, ?, 1)";
    $stmtPerfis = $pdo->prepare($sqlPerfis);
    
    foreach ($perfis as $perfil) {
        // Verificar se já existe
        $checkSql = "SELECT COUNT(*) FROM tbl_perfis WHERE nome_perfil = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$perfil['nome_perfil']]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $stmtPerfis->execute([$perfil['nome_perfil'], $perfil['descricao']]);
            echo "Perfil '{$perfil['nome_perfil']}' inserido.\n";
        } else {
            echo "Perfil '{$perfil['nome_perfil']}' já existe.\n";
        }
    }
    
    // Buscar IDs dos perfis e páginas
    $perfilIds = [];
    $paginaIds = [];
    
    $sqlGetPerfis = "SELECT id_perfil, nome_perfil FROM tbl_perfis WHERE ativo = 1";
    $stmtGetPerfis = $pdo->query($sqlGetPerfis);
    while ($row = $stmtGetPerfis->fetch()) {
        $perfilIds[$row['nome_perfil']] = $row['id_perfil'];
    }
    
    $sqlGetPaginas = "SELECT id_pagina, nome_pagina FROM tbl_paginas WHERE ativo = 1";
    $stmtGetPaginas = $pdo->query($sqlGetPaginas);
    while ($row = $stmtGetPaginas->fetch()) {
        $paginaIds[$row['nome_pagina']] = $row['id_pagina'];
    }
    
    // Definir permissões para cada perfil
    $permissoes = [
        'Administrador' => [
            'Dashboard' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Materiais' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Fornecedores' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Usuários' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Perfil de Acesso' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Filiais' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Movimentações' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Relatórios' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Alertas' => ['visualizar', 'inserir', 'editar', 'excluir'],
            'Configurações' => ['visualizar', 'inserir', 'editar', 'excluir']
        ],
        'Gerente' => [
            'Dashboard' => ['visualizar'],
            'Materiais' => ['visualizar', 'inserir', 'editar'],
            'Fornecedores' => ['visualizar', 'inserir', 'editar'],
            'Usuários' => ['visualizar'],
            'Perfil de Acesso' => ['visualizar'],
            'Filiais' => ['visualizar'],
            'Movimentações' => ['visualizar', 'inserir', 'editar'],
            'Relatórios' => ['visualizar'],
            'Alertas' => ['visualizar', 'editar'],
            'Configurações' => ['visualizar']
        ],
        'Operador' => [
            'Dashboard' => ['visualizar'],
            'Materiais' => ['visualizar'],
            'Fornecedores' => ['visualizar'],
            'Usuários' => ['visualizar'],
            'Perfil de Acesso' => ['visualizar'],
            'Filiais' => ['visualizar'],
            'Movimentações' => ['visualizar', 'inserir'],
            'Relatórios' => ['visualizar'],
            'Alertas' => ['visualizar'],
            'Configurações' => ['visualizar']
        ],
        'Visualizador' => [
            'Dashboard' => ['visualizar'],
            'Materiais' => ['visualizar'],
            'Fornecedores' => ['visualizar'],
            'Usuários' => ['visualizar'],
            'Perfil de Acesso' => ['visualizar'],
            'Filiais' => ['visualizar'],
            'Movimentações' => ['visualizar'],
            'Relatórios' => ['visualizar'],
            'Alertas' => ['visualizar'],
            'Configurações' => ['visualizar']
        ]
    ];
    
    // Inserir permissões
    $sqlPermissoes = "INSERT INTO tbl_permissoes (id_perfil, id_pagina, pode_visualizar, pode_inserir, pode_editar, pode_excluir, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmtPermissoes = $pdo->prepare($sqlPermissoes);
    
    foreach ($permissoes as $perfilNome => $paginasPerm) {
        $idPerfil = $perfilIds[$perfilNome] ?? null;
        
        if ($idPerfil) {
            foreach ($paginasPerm as $paginaNome => $permissoesPagina) {
                $idPagina = $paginaIds[$paginaNome] ?? null;
                
                if ($idPagina) {
                    // Verificar se já existe
                    $checkSql = "SELECT COUNT(*) FROM tbl_permissoes WHERE id_perfil = ? AND id_pagina = ?";
                    $checkStmt = $pdo->prepare($checkSql);
                    $checkStmt->execute([$idPerfil, $idPagina]);
                    
                    if ($checkStmt->fetchColumn() == 0) {
                        $podeVisualizar = in_array('visualizar', $permissoesPagina) ? 1 : 0;
                        $podeInserir = in_array('inserir', $permissoesPagina) ? 1 : 0;
                        $podeEditar = in_array('editar', $permissoesPagina) ? 1 : 0;
                        $podeExcluir = in_array('excluir', $permissoesPagina) ? 1 : 0;
                        
                        $stmtPermissoes->execute([$idPerfil, $idPagina, $podeVisualizar, $podeInserir, $podeEditar, $podeExcluir]);
                        echo "Permissão para perfil '{$perfilNome}' na página '{$paginaNome}' inserida.\n";
                    } else {
                        echo "Permissão para perfil '{$perfilNome}' na página '{$paginaNome}' já existe.\n";
                    }
                }
            }
        }
    }
    
    echo "\nDados de exemplo inseridos com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?> 