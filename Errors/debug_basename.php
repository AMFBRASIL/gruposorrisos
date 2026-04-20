<?php
/**
 * Debug de Função basename() e Variáveis de URL
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

echo "<h1>🔍 Debug de Função basename() e Variáveis de URL</h1>";
echo "<hr>";

// 1. Testar função basename()
echo "<h3>1. Testando função basename()...</h3>";

$currentFile = __FILE__;
$basename = basename($currentFile);
$basenameWithoutExt = basename($currentFile, '.php');

echo "✅ <strong>Arquivo atual:</strong> {$currentFile}<br>";
echo "✅ <strong>basename(__FILE__):</strong> {$basename}<br>";
echo "✅ <strong>basename(__FILE__, '.php'):</strong> {$basenameWithoutExt}<br>";

// 2. Testar variáveis de servidor
echo "<br><h3>2. Testando variáveis de servidor...</h3>";

echo "✅ <strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "<br>";
echo "✅ <strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "<br>";
echo "✅ <strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "✅ <strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'N/A') . "<br>";

// 3. Testar basename com diferentes variáveis
echo "<br><h3>3. Testando basename com diferentes variáveis...</h3>";

if (isset($_SERVER['PHP_SELF'])) {
    $basenamePHP_SELF = basename($_SERVER['PHP_SELF']);
    $basenamePHP_SELF_WithoutExt = basename($_SERVER['PHP_SELF'], '.php');
    
    echo "✅ <strong>basename(\$_SERVER['PHP_SELF']):</strong> {$basenamePHP_SELF}<br>";
    echo "✅ <strong>basename(\$_SERVER['PHP_SELF'], '.php'):</strong> {$basenamePHP_SELF_WithoutExt}<br>";
}

if (isset($_SERVER['SCRIPT_NAME'])) {
    $basenameSCRIPT_NAME = basename($_SERVER['SCRIPT_NAME']);
    $basenameSCRIPT_NAME_WithoutExt = basename($_SERVER['SCRIPT_NAME'], '.php');
    
    echo "✅ <strong>basename(\$_SERVER['SCRIPT_NAME']):</strong> {$basenameSCRIPT_NAME}<br>";
    echo "✅ <strong>basename(\$_SERVER['SCRIPT_NAME'], '.php'):</strong> {$basenameSCRIPT_NAME_WithoutExt}<br>";
}

// 4. Simular o que acontece na página pedidos-compra.php
echo "<br><h3>4. Simulando página pedidos-compra.php...</h3>";

// Simular que estamos na página pedidos-compra.php
$_SERVER['PHP_SELF'] = '/sistemas/_estoquegrupoSorrisos/pedidos-compra.php';
$_SERVER['SCRIPT_NAME'] = '/sistemas/_estoquegrupoSorrisos/pedidos-compra.php';

echo "✅ <strong>PHP_SELF simulado:</strong> " . $_SERVER['PHP_SELF'] . "<br>";
echo "✅ <strong>SCRIPT_NAME simulado:</strong> " . $_SERVER['SCRIPT_NAME'] . "<br>";

$basenameSimulado = basename($_SERVER['PHP_SELF']);
$basenameSimuladoWithoutExt = basename($_SERVER['PHP_SELF'], '.php');

echo "✅ <strong>basename(\$_SERVER['PHP_SELF']) simulado:</strong> {$basenameSimulado}<br>";
echo "✅ <strong>basename(\$_SERVER['PHP_SELF'], '.php') simulado:</strong> {$basenameSimuladoWithoutExt}<br>";

// 5. Testar com diferentes formatos de URL
echo "<br><h3>5. Testando com diferentes formatos de URL...</h3>";

$urlsTeste = [
    'pedidos-compra.php',
    'pedidos-compra',
    '/pedidos-compra.php',
    '/sistemas/_estoquegrupoSorrisos/pedidos-compra.php',
    'pedidos_compra.php',
    'pedidos_compra'
];

foreach ($urlsTeste as $url) {
    $basenameTeste = basename($url);
    $basenameTesteWithoutExt = basename($url, '.php');
    
    echo "✅ <strong>URL:</strong> {$url}<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>basename():</strong> {$basenameTeste}<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>basename('.php'):</strong> {$basenameTesteWithoutExt}<br><br>";
}

// 6. Verificar se há diferença entre o que está no banco e o que está sendo verificado
echo "<br><h3>6. Verificando diferença entre banco e verificação...</h3>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Conexão com banco estabelecida<br>";
        
        // Buscar a página pedidos-compra no banco
        $sql = "SELECT * FROM tbl_paginas WHERE url_pagina LIKE '%pedidos%' OR url_pagina LIKE '%compra%'";
        $stmt = $pdo->query($sql);
        $paginas = $stmt->fetchAll();
        
        if ($paginas) {
            echo "✅ Páginas relacionadas encontradas no banco:<br>";
            foreach ($paginas as $pagina) {
                echo "<ul>";
                echo "<li><strong>ID:</strong> {$pagina['id_pagina']}</li>";
                echo "<li><strong>Nome:</strong> {$pagina['nome_pagina']}</li>";
                echo "<li><strong>URL:</strong> {$pagina['url_pagina']}</li>";
                echo "<li><strong>Categoria:</strong> {$pagina['categoria']}</li>";
                echo "<li><strong>Ativo:</strong> " . ($pagina['ativo'] ? 'Sim' : 'Não') . "</li>";
                echo "</ul>";
                
                // Verificar se o basename da URL do banco corresponde ao esperado
                $basenameBanco = basename($pagina['url_pagina']);
                $basenameBancoWithoutExt = basename($pagina['url_pagina'], '.php');
                
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>basename da URL do banco:</strong> {$basenameBanco}<br>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>basename da URL do banco (sem .php):</strong> {$basenameBancoWithoutExt}<br>";
                
                // Comparar com o esperado
                $esperado = 'pedidos-compra.php';
                $esperadoSemExt = 'pedidos-compra';
                
                if ($basenameBanco === $esperado) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;✅ <strong>CORRESPONDE ao esperado!</strong><br>";
                } else {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;❌ <strong>NÃO CORRESPONDE ao esperado!</strong><br>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Esperado: {$esperado}<br>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Encontrado: {$basenameBanco}<br>";
                }
            }
        } else {
            echo "❌ Nenhuma página relacionada encontrada no banco<br>";
        }
        
    } else {
        echo "❌ Falha na conexão com banco<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na verificação do banco: " . $e->getMessage() . "<br>";
}

// 7. Resultado final
echo "<br><h2>🎯 Diagnóstico de basename() Concluído!</h2>";

$problemas = [];
if (isset($basenameSimulado) && $basenameSimulado !== 'pedidos-compra.php') {
    $problemas[] = "A função basename() está retornando uma URL diferente do esperado";
}

if (empty($problemas)) {
    echo "<p style='color: #28a745;'>✅ A função basename() está funcionando corretamente!</p>";
    echo "<p>O problema pode estar em outro lugar.</p>";
} else {
    echo "<p style='color: #dc3545;'>❌ Problemas encontrados:</p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>{$problema}</li>";
    }
    echo "</ul>";
}

// 8. Botões de ação
echo "<br><h3>8. Ações disponíveis:</h3>";
echo "<a href='debug_sql_direto.php' class='btn btn-primary'>Testar SQL Direto</a> ";
echo "<a href='debug_simulacao_pedidos_compra.php' class='btn btn-secondary'>Testar Simulação</a> ";
echo "<a href='pedidos-compra.php' class='btn btn-warning'>Acessar Página Real</a>";
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
.warning {
    color: #ffc107;
}
</style> 