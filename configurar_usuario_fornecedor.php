<?php
/**
 * Script para configurar usuário como fornecedor
 * Data: 2025-01-22
 * Objetivo: Vincular usuário ID 11 ao fornecedor ID 11
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>🔧 Configurando Usuário como Fornecedor</h1>";
echo "<p><strong>Objetivo:</strong> Vincular usuário ID 11 ao fornecedor ID 11</p>";
echo "<hr>";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h3>📋 Passo 1: Verificando estrutura da tabela</h3>";
    
    // Verificar se o campo id_fornecedor existe
    $stmt = $pdo->query("DESCRIBE tbl_usuarios");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('id_fornecedor', $colunas)) {
        echo "<p style='color: red;'>❌ Campo id_fornecedor não existe na tabela tbl_usuarios!</p>";
        echo "<p>Execute primeiro o script SQL: <code>corrigir_estrutura_usuarios.sql</code></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Campo id_fornecedor encontrado na tabela tbl_usuarios</p>";
    
    echo "<h3>📋 Passo 2: Verificando usuário ID 11</h3>";
    
    // Verificar se o usuário ID 11 existe
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE id_usuario = ?");
    $stmt->execute([11]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo "<p style='color: red;'>❌ Usuário ID 11 não encontrado!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Usuário ID 11 encontrado: {$usuario['nome_completo']}</p>";
    echo "<p><strong>Perfil atual:</strong> " . ($usuario['id_perfil'] ?? 'Não definido') . "</p>";
    echo "<p><strong>ID Fornecedor atual:</strong> " . ($usuario['id_fornecedor'] ?? 'NULL') . "</p>";
    
    echo "<h3>📋 Passo 3: Verificando fornecedor ID 11</h3>";
    
    // Verificar se o fornecedor ID 11 existe
    $stmt = $pdo->prepare("SELECT * FROM tbl_fornecedores WHERE id_fornecedor = ?");
    $stmt->execute([11]);
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$fornecedor) {
        echo "<p style='color: orange;'>⚠️ Fornecedor ID 11 não existe. Criando...</p>";
        
        // Criar fornecedor ID 11
        $stmt = $pdo->prepare("
            INSERT INTO tbl_fornecedores (
                id_fornecedor, razao_social, nome_fantasia, cnpj, email, telefone, 
                endereco, cidade, estado, cep, responsavel, ativo, data_criacao, data_atualizacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            11, 
            'Fornecedor ID 11 Ltda', 
            'Fornecedor ID 11', 
            '11.111.111/0001-11', 
            'contato@fornecedor11.com.br', 
            '(11) 11111-1111', 
            'Rua das Flores, 111', 
            'São Paulo', 
            'SP', 
            '01111-111', 
            'João Fornecedor 11', 
            1
        ]);
        
        echo "<p style='color: green;'>✅ Fornecedor ID 11 criado com sucesso</p>";
    } else {
        echo "<p style='color: green;'>✅ Fornecedor ID 11 já existe: {$fornecedor['razao_social']}</p>";
    }
    
    echo "<h3>📋 Passo 4: Configurando usuário como fornecedor</h3>";
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Primeiro, vamos descobrir qual é o ID do perfil 'Fornecedor' na tabela tbl_perfis
    $stmt = $pdo->prepare("SELECT id_perfil FROM tbl_perfis WHERE nome_perfil = 'Fornecedor'");
    $stmt->execute();
    $perfil = $stmt->fetch();
    
    if (!$perfil) {
        echo "<p style='color: red;'>❌ Perfil 'Fornecedor' não encontrado na tabela tbl_perfis!</p>";
        echo "<p>Verifique se o perfil existe ou crie-o primeiro.</p>";
        exit;
    }
    
    $id_perfil_fornecedor = $perfil['id_perfil'];
    echo "<p style='color: green;'>✅ Perfil 'Fornecedor' encontrado com ID: {$id_perfil_fornecedor}</p>";
    
    // Atualizar usuário ID 11 para ser fornecedor
    $stmt = $pdo->prepare("
        UPDATE tbl_usuarios 
        SET id_fornecedor = ?, 
            id_perfil = ?
        WHERE id_usuario = ?
    ");
    
    $resultado = $stmt->execute([11, $id_perfil_fornecedor, 11]);
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ Usuário ID 11 configurado como fornecedor</p>";
    } else {
        throw new Exception("Erro ao atualizar usuário");
    }
    
    // Confirmar transação
    $pdo->commit();
    
    echo "<h3>📋 Passo 5: Verificação final</h3>";
    
    // Verificar se a configuração foi aplicada
    $stmt = $pdo->prepare("
        SELECT u.id_usuario, u.nome_completo, u.id_perfil, u.id_fornecedor, f.razao_social
        FROM tbl_usuarios u
        LEFT JOIN tbl_fornecedores f ON u.id_fornecedor = f.id_fornecedor
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([11]);
    $usuario_atualizado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✅ Configuração aplicada com sucesso!</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID Usuário</td><td>{$usuario_atualizado['id_usuario']}</td></tr>";
    echo "<tr><td>Nome</td><td>{$usuario_atualizado['nome_completo']}</td></tr>";
    echo "<tr><td>Perfil</td><td>{$usuario_atualizado['id_perfil']}</td></tr>";
    echo "<tr><td>ID Fornecedor</td><td>{$usuario_atualizado['id_fornecedor']}</td></tr>";
    echo "<tr><td>Razão Social</td><td>{$usuario_atualizado['razao_social']}</td></tr>";
    echo "</table>";
    
    echo "<h3>📋 Passo 6: Criando pedidos de exemplo</h3>";
    
    // Criar alguns pedidos para o fornecedor ID 11
    $stmt = $pdo->prepare("
        INSERT INTO tbl_pedidos_compra (
            numero_pedido, id_filial, id_fornecedor, id_usuario_solicitante, 
            data_solicitacao, data_entrega_prevista, status, valor_total, 
            observacoes, ativo, data_criacao, data_atualizacao
        ) VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 'pendente', ?, ?, 1, NOW(), NOW())
    ");
    
    $pedidos = [
        ['PED-2025-011', 1, 11, 1, 12500.00, 'Pedido de materiais para fornecedor ID 11'],
        ['PED-2025-012', 2, 11, 2, 8900.00, 'Equipamentos para fornecedor ID 11'],
        ['PED-2025-013', 1, 11, 1, 15600.00, 'Materiais de limpeza para fornecedor ID 11']
    ];
    
    foreach ($pedidos as $pedido) {
        $stmt->execute($pedido);
        echo "<p style='color: green;'>✅ Pedido {$pedido[0]} criado</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>🎉 Problema Resolvido!</h2>";
    echo "<p><strong>Status:</strong> Usuário ID 11 configurado como fornecedor com sucesso</p>";
    echo "<p><strong>Próximo passo:</strong> Testar a página pedidos-fornecedores.php</p>";
    
    echo "<div style='margin: 20px 0; padding: 20px; background: #e9ecef; border-radius: 10px;'>";
    echo "<h3>🔍 Como testar:</h3>";
    echo "<ol>";
    echo "<li>Faça logout e login novamente como usuário ID 11</li>";
    echo "<li>Acesse a página <a href='pedidos-fornecedores.php'>pedidos-fornecedores.php</a></li>";
    echo "<li>Verifique se os 3 pedidos aparecem na lista</li>";
    echo "<li>Teste as funcionalidades de visualização e resposta</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='pedidos-fornecedores.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block;'>🚀 Ir para Pedidos do Fornecedor</a></p>";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo "<h2 style='color: red;'>❌ Erro durante a configuração!</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<hr>";
    echo "<p><a href='pedidos-fornecedores.php'>Voltar para Pedidos do Fornecedor</a></p>";
}
?> 