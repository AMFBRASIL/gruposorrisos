<?php
/**
 * Autoloader para o sistema de estoque
 * Carrega automaticamente as classes quando necessário
 */

spl_autoload_register(function ($class) {
    // Mapeamento de classes para arquivos
    $classMap = [
        // Models
        'Material' => 'models/Material.php',
        'Movimentacao' => 'models/Movimentacao.php',
        'Filial' => 'models/Filial.php',
        'Categoria' => 'models/Categoria.php',
        'Fornecedor' => 'models/Fornecedor.php',
        'UnidadeMedida' => 'models/UnidadeMedida.php',
        'TipoMovimentacao' => 'models/TipoMovimentacao.php',
        'Usuario' => 'models/Usuario.php',
        'Perfil' => 'models/Perfil.php',
        'BaseModel' => 'models/BaseModel.php',
        
        // Controllers (quando criados)
        'MaterialController' => 'backend/controllers/MaterialController.php',
        'MovimentacaoController' => 'backend/controllers/MovimentacaoController.php',
        'FilialController' => 'backend/controllers/FilialController.php',
        'CategoriaController' => 'backend/controllers/CategoriaController.php',
        'FornecedorController' => 'backend/controllers/FornecedorController.php',
        'UnidadeMedidaController' => 'backend/controllers/UnidadeMedidaController.php',
        'TipoMovimentacaoController' => 'backend/controllers/TipoMovimentacaoController.php',
        
        // Utils (quando criados)
        'Response' => 'utils/Response.php',
        'Validator' => 'utils/Validator.php',
        'Logger' => 'utils/Logger.php'
    ];
    
    // Verifica se a classe está no mapeamento
    if (isset($classMap[$class])) {
        $file = __DIR__ . '/../' . $classMap[$class];
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Tenta carregar por convenção de nomenclatura
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../backend/controllers/',
        __DIR__ . '/../utils/',
        __DIR__ . '/../backend/api/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/**
 * Função para carregar configurações
 */
function loadConfig() {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/conexao.php';
}

/**
 * Função para carregar todos os models
 */
function loadModels() {
    $models = [
        'BaseModel',
        'Material',
        'Movimentacao',
        'Filial',
        'Categoria',
        'Fornecedor',
        'UnidadeMedida',
        'TipoMovimentacao',
        'Usuario',
        'Perfil'
    ];
    
    foreach ($models as $model) {
        if (class_exists($model)) {
            // Model já foi carregado pelo autoloader
        }
    }
}

/**
 * Função para verificar se uma classe existe
 */
function classExists($className) {
    return class_exists($className);
}

/**
 * Função para obter informações sobre uma classe
 */
function getClassInfo($className) {
    if (!class_exists($className)) {
        return null;
    }
    
    $reflection = new ReflectionClass($className);
    
    return [
        'name' => $reflection->getName(),
        'methods' => array_map(function($method) {
            return $method->getName();
        }, $reflection->getMethods(ReflectionMethod::IS_PUBLIC)),
        'properties' => array_map(function($property) {
            return $property->getName();
        }, $reflection->getProperties()),
        'file' => $reflection->getFileName()
    ];
}
?> 