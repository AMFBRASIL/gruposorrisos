<?php
/**
 * Debug Simples de Sessão - Sem Sistema de Permissões
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug Simples de Sessão</h1>";
echo "<hr>";

// 1. Verificar se a sessão está ativa
echo "<h3>1. Verificando sessão...</h3>";
if (session_status() === PHP_SESSION_NONE) {
    echo "❌ Nenhuma sessão ativa<br>";
    session_start();
    echo "✅ Sessão iniciada<br>";
} else {
    echo "✅ Sessão já está ativa<br>";
}

// 2. Verificar variáveis de sessão
echo "<br><h3>2. Variáveis de sessão atuais:</h3>";
echo "<ul>";
foreach ($_SESSION as $key => $value) {
    echo "<li><strong>{$key}:</strong> " . (is_array($value) ? json_encode($value) : htmlspecialchars($value)) . "</li>";
}
echo "</ul>";

// 3. Verificar se está logado
echo "<br><h3>3. Verificando se está logado...</h3>";
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "✅ Sessão logged_in = true<br>";
} else {
    echo "❌ Sessão logged_in não está definida ou é false<br>";
}

// 4. Verificar ID do usuário
echo "<br><h3>4. Verificando ID do usuário...</h3>";
if (isset($_SESSION['usuario_id'])) {
    echo "✅ usuario_id = {$_SESSION['usuario_id']}<br>";
} else {
    echo "❌ usuario_id não está definido<br>";
}

// 5. Verificar ID do perfil
echo "<br><h3>5. Verificando ID do perfil...</h3>";
if (isset($_SESSION['usuario_perfil_id'])) {
    echo "✅ usuario_perfil_id = {$_SESSION['usuario_perfil_id']}<br>";
} else {
    echo "❌ usuario_perfil_id não está definido<br>";
}

// 6. Verificar nome do usuário
echo "<br><h3>6. Verificando nome do usuário...</h3>";
if (isset($_SESSION['usuario_nome'])) {
    echo "✅ usuario_nome = {$_SESSION['usuario_nome']}<br>";
} else {
    echo "❌ usuario_nome não está definido<br>";
}

// 7. Verificar perfil do usuário
echo "<br><h3>7. Verificando perfil do usuário...</h3>";
if (isset($_SESSION['usuario_perfil'])) {
    echo "✅ usuario_perfil = {$_SESSION['usuario_perfil']}<br>";
} else {
    echo "❌ usuario_perfil não está definido<br>";
}

// 8. Verificar função isLoggedIn()
echo "<br><h3>8. Verificando função isLoggedIn()...</h3>";
if (file_exists('config/session.php')) {
    require_once 'config/session.php';
    $isLoggedIn = isLoggedIn();
    echo "isLoggedIn() retornou: " . ($isLoggedIn ? '✅ true' : '❌ false') . "<br>";
} else {
    echo "❌ arquivo session.php não encontrado<br>";
}

// 9. Verificar se há algum problema com o banco
echo "<br><h3>9. Verificando conexão com banco...</h3>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo) {
            echo "✅ Conexão com banco estabelecida<br>";
            
            // Verificar se o usuário existe no banco
            if (isset($_SESSION['usuario_id'])) {
                $sql = "SELECT u.*, p.nome_perfil FROM tbl_usuarios u JOIN tbl_perfis p ON u.id_perfil = p.id_perfil WHERE u.id_usuario = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['usuario_id']]);
                $usuario = $stmt->fetch();
                
                if ($usuario) {
                    echo "✅ Usuário encontrado no banco:<br>";
                    echo "<ul>";
                    echo "<li><strong>ID:</strong> {$usuario['id_usuario']}</li>";
                    echo "<li><strong>Nome:</strong> {$usuario['nome_completo']}</li>";
                    echo "<li><strong>Email:</strong> {$usuario['email']}</li>";
                    echo "<li><strong>Perfil ID:</strong> {$usuario['id_perfil']}</li>";
                    echo "<li><strong>Perfil:</strong> {$usuario['nome_perfil']}</li>";
                    echo "<li><strong>Ativo:</strong> " . ($usuario['ativo'] ? 'Sim' : 'Não') . "</li>";
                    echo "</ul>";
                } else {
                    echo "❌ Usuário não encontrado no banco<br>";
                }
            }
        } else {
            echo "❌ Falha na conexão com banco<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro na conexão com banco: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ arquivo database.php não encontrado<br>";
}

// 10. Resultado final
echo "<br><h2>🎯 Diagnóstico de Sessão Concluído!</h2>";

$problemas = [];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $problemas[] = "Sessão não está marcada como logada";
}
if (!isset($_SESSION['usuario_id'])) {
    $problemas[] = "ID do usuário não está definido";
}
if (!isset($_SESSION['usuario_perfil_id'])) {
    $problemas[] = "ID do perfil não está definido";
}

if (empty($problemas)) {
    echo "<p style='color: #28a745;'>✅ A sessão está configurada corretamente!</p>";
    echo "<p>O problema pode estar no sistema de permissões.</p>";
} else {
    echo "<p style='color: #dc3545;'>❌ Problemas encontrados na sessão:</p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>{$problema}</li>";
    }
    echo "</ul>";
    echo "<p>Para resolver, faça login novamente.</p>";
}

// 11. Botões de ação
echo "<br><h3>11. Ações disponíveis:</h3>";
echo "<a href='login.php' class='btn btn-primary'>Fazer Login</a> ";
echo "<a href='index.php' class='btn btn-secondary'>Voltar ao Início</a> ";
echo "<a href='debug_verificacao_permissoes.php' class='btn btn-warning'>Testar Permissões</a>";
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
ul {
    background-color: white;
    padding: 15px 20px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 4px solid #007bff;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
}
.btn-primary {
    background-color: #007bff;
}
.btn-secondary {
    background-color: #6c757d;
}
.btn-warning {
    background-color: #ffc107;
    color: #333;
}
.success {
    color: #28a745;
}
.error {
    color: #dc3545;
}
</style> 