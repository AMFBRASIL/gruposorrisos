<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "Inserindo novo administrador...\n";
    
    // Buscar o ID do perfil Administrador
    $sqlPerfil = "SELECT id_perfil FROM tbl_perfis WHERE nome_perfil = 'Administrador' AND ativo = 1 LIMIT 1";
    $stmtPerfil = $pdo->prepare($sqlPerfil);
    $stmtPerfil->execute();
    $perfil = $stmtPerfil->fetch();
    
    if (!$perfil) {
        echo "Erro: Perfil 'Administrador' não encontrado!\n";
        exit(1);
    }
    
    $idPerfil = $perfil['id_perfil'];
    echo "Perfil Administrador encontrado com ID: {$idPerfil}\n";
    
    // Verificar se o email já existe
    $sqlCheck = "SELECT COUNT(*) FROM tbl_usuarios WHERE email = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['promautone@gmail.com']);
    
    if ($stmtCheck->fetchColumn() > 0) {
        echo "Erro: Usuário com email 'promautone@gmail.com' já existe!\n";
        exit(1);
    }
    
    // Hash da senha
    $senhaHash = password_hash('102030', PASSWORD_DEFAULT);
    
    // Inserir o novo administrador
    $sqlInsert = "INSERT INTO tbl_usuarios (nome_completo, email, senha, id_perfil, ativo, data_criacao) 
                  VALUES (?, ?, ?, ?, 1, NOW())";
    $stmtInsert = $pdo->prepare($sqlInsert);
    
    $result = $stmtInsert->execute([
        'Administrador Sistema',
        'promautone@gmail.com',
        $senhaHash,
        $idPerfil
    ]);
    
    if ($result) {
        $idUsuario = $pdo->lastInsertId();
        echo "Administrador inserido com sucesso!\n";
        echo "ID do usuário: {$idUsuario}\n";
        echo "Email: promautone@gmail.com\n";
        echo "Senha: 102030\n";
        echo "Perfil: Administrador\n";
    } else {
        echo "Erro ao inserir administrador!\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?> 