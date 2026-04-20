<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    echo "Inserindo dados de exemplo para alertas...\n";
    
    // Verificar se existem materiais e filiais
    $sqlCheckMateriais = "SELECT COUNT(*) FROM tbl_materiais WHERE ativo = 1";
    $stmtCheckMateriais = $pdo->query($sqlCheckMateriais);
    $totalMateriais = $stmtCheckMateriais->fetchColumn();
    
    $sqlCheckFiliais = "SELECT COUNT(*) FROM tbl_filiais WHERE filial_ativa = 1";
    $stmtCheckFiliais = $pdo->query($sqlCheckFiliais);
    $totalFiliais = $stmtCheckFiliais->fetchColumn();
    
    if ($totalMateriais == 0) {
        echo "Erro: Não existem materiais cadastrados. Cadastre materiais primeiro.\n";
        exit(1);
    }
    
    if ($totalFiliais == 0) {
        echo "Erro: Não existem filiais cadastradas. Cadastre filiais primeiro.\n";
        exit(1);
    }
    
    // Buscar alguns materiais e filiais para criar alertas
    $sqlMateriais = "SELECT id_material, nome, estoque_minimo, estoque_maximo FROM tbl_materiais WHERE ativo = 1 LIMIT 5";
    $stmtMateriais = $pdo->query($sqlMateriais);
    $materiais = $stmtMateriais->fetchAll();
    
    $sqlFiliais = "SELECT id_filial, nome_filial FROM tbl_filiais WHERE filial_ativa = 1 LIMIT 3";
    $stmtFiliais = $pdo->query($sqlFiliais);
    $filiais = $stmtFiliais->fetchAll();
    
    if (empty($materiais) || empty($filiais)) {
        echo "Erro: Não foi possível encontrar materiais ou filiais válidas.\n";
        exit(1);
    }
    
    // Verificar se já existem alertas
    $sqlCheckAlertas = "SELECT COUNT(*) FROM tbl_alertas_estoque WHERE ativo = 1";
    $stmtCheckAlertas = $pdo->query($sqlCheckAlertas);
    $totalAlertas = $stmtCheckAlertas->fetchColumn();
    
    if ($totalAlertas > 0) {
        echo "Alertas já existem no sistema. Pulando inserção de dados de exemplo.\n";
        exit(0);
    }
    
    // Inserir alertas de exemplo
    $sqlInsert = "INSERT INTO tbl_alertas_estoque 
                  (id_filial, id_material, tipo_alerta, quantidade_atual, quantidade_limite, 
                   mensagem, nivel_urgencia, lido, ativo, data_alerta) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
    $stmtInsert = $pdo->prepare($sqlInsert);
    
    $alertasInseridos = 0;
    
    // Alerta crítico - estoque zerado
    $stmtInsert->execute([
        $filiais[0]['id_filial'],
        $materiais[0]['id_material'],
        'estoque_zerado',
        0,
        $materiais[0]['estoque_minimo'],
        'Produto em falta: ' . $materiais[0]['nome'] . ' - Reposição urgente necessária',
        'alta',
        0
    ]);
    $alertasInseridos++;
    echo "Alerta crítico inserido para: " . $materiais[0]['nome'] . "\n";
    
    // Alerta médio - estoque baixo
    if (isset($materiais[1])) {
        $stmtInsert->execute([
            $filiais[0]['id_filial'],
            $materiais[1]['id_material'],
            'estoque_baixo',
            floor($materiais[1]['estoque_minimo'] * 0.6),
            $materiais[1]['estoque_minimo'],
            'Estoque baixo para ' . $materiais[1]['nome'] . ' - ' . floor($materiais[1]['estoque_minimo'] * 0.6) . ' unidades restantes',
            'media',
            0
        ]);
        $alertasInseridos++;
        echo "Alerta médio inserido para: " . $materiais[1]['nome'] . "\n";
    }
    
    // Alerta baixo - estoque baixo
    if (isset($materiais[2])) {
        $stmtInsert->execute([
            $filiais[1]['id_filial'],
            $materiais[2]['id_material'],
            'estoque_baixo',
            floor($materiais[2]['estoque_minimo'] * 0.8),
            $materiais[2]['estoque_minimo'],
            'Estoque baixo para ' . $materiais[2]['nome'] . ' - ' . floor($materiais[2]['estoque_minimo'] * 0.8) . ' unidades restantes',
            'baixa',
            0
        ]);
        $alertasInseridos++;
        echo "Alerta baixo inserido para: " . $materiais[2]['nome'] . "\n";
    }
    
    // Alerta de estoque excedido
    if (isset($materiais[3]) && $materiais[3]['estoque_maximo'] > 0) {
        $stmtInsert->execute([
            $filiais[1]['id_filial'],
            $materiais[3]['id_material'],
            'estoque_excedido',
            $materiais[3]['estoque_maximo'] + 10,
            $materiais[3]['estoque_maximo'],
            'Estoque excedido para ' . $materiais[3]['nome'] . ' - ' . ($materiais[3]['estoque_maximo'] + 10) . ' unidades',
            'media',
            0
        ]);
        $alertasInseridos++;
        echo "Alerta de estoque excedido inserido para: " . $materiais[3]['nome'] . "\n";
    }
    
    // Alerta resolvido (para mostrar histórico)
    if (isset($materiais[4])) {
        $stmtInsert->execute([
            $filiais[2]['id_filial'],
            $materiais[4]['id_material'],
            'estoque_baixo',
            floor($materiais[4]['estoque_minimo'] * 0.5),
            $materiais[4]['estoque_minimo'],
            'Estoque baixo para ' . $materiais[4]['nome'] . ' - ' . floor($materiais[4]['estoque_minimo'] * 0.5) . ' unidades restantes',
            'alta',
            1
        ]);
        $alertasInseridos++;
        echo "Alerta resolvido inserido para: " . $materiais[4]['nome'] . "\n";
    }
    
    echo "\nDados de exemplo inseridos com sucesso!\n";
    echo "Total de alertas inseridos: {$alertasInseridos}\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?> 