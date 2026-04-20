<?php
/**
 * Script de teste do sistema de estoque
 * Verifica se todos os componentes estão funcionando corretamente
 */

require_once __DIR__ . '/../config/autoload.php';
loadConfig();

class SystemTester {
    private $pdo;
    private $testResults = [];
    
    public function __construct() {
        $this->pdo = Conexao::getInstance()->getPdo();
    }
    
    /**
     * Executa todos os testes
     */
    public function runAllTests() {
        echo "=== TESTE DO SISTEMA DE ESTOQUE ===\n\n";
        
        $this->testDatabaseConnection();
        $this->testModels();
        $this->testMaterialOperations();
        $this->testMovimentacaoOperations();
        $this->testRelationships();
        
        $this->showResults();
    }
    
    /**
     * Testa conexão com banco de dados
     */
    private function testDatabaseConnection() {
        echo "1. Testando conexão com banco de dados...\n";
        
        try {
            $stmt = $this->pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result && $result['test'] == 1) {
                $this->addResult('Conexão com banco', 'PASSOU', 'Conexão estabelecida com sucesso');
            } else {
                $this->addResult('Conexão com banco', 'FALHOU', 'Não foi possível conectar ao banco');
            }
        } catch (Exception $e) {
            $this->addResult('Conexão com banco', 'FALHOU', $e->getMessage());
        }
    }
    
    /**
     * Testa carregamento dos modelos
     */
    private function testModels() {
        echo "2. Testando carregamento dos modelos...\n";
        
        $models = [
            'BaseModel',
            'Material',
            'Movimentacao',
            'Filial',
            'Categoria',
            'Fornecedor',
            'UnidadeMedida',
            'TipoMovimentacao'
        ];
        
        foreach ($models as $model) {
            if (class_exists($model)) {
                $this->addResult("Modelo $model", 'PASSOU', 'Classe carregada com sucesso');
            } else {
                $this->addResult("Modelo $model", 'FALHOU', 'Classe não encontrada');
            }
        }
    }
    
    /**
     * Testa operações com materiais
     */
    private function testMaterialOperations() {
        echo "3. Testando operações com materiais...\n";
        
        try {
            $material = new Material();
            
            // Testa busca de materiais
            $materiais = $material->findAllWithRelations();
            $this->addResult('Busca de materiais', 'PASSOU', 'Encontrados ' . count($materiais) . ' materiais');
            
            // Testa busca com filtros
            $filtros = ['id_filial' => 1];
            $resultado = $material->findWithFilters($filtros, 1, 5);
            $this->addResult('Filtros de materiais', 'PASSOU', 'Filtros funcionando');
            
            // Testa busca de estoque baixo
            $estoqueBaixo = $material->findEstoqueBaixo();
            $this->addResult('Estoque baixo', 'PASSOU', 'Encontrados ' . count($estoqueBaixo) . ' materiais com estoque baixo');
            
        } catch (Exception $e) {
            $this->addResult('Operações com materiais', 'FALHOU', $e->getMessage());
        }
    }
    
    /**
     * Testa operações com movimentações
     */
    private function testMovimentacaoOperations() {
        echo "4. Testando operações com movimentações...\n";
        
        try {
            $movimentacao = new Movimentacao();
            
            // Testa busca de movimentações
            $movimentacoes = $movimentacao->findAllWithRelations();
            $this->addResult('Busca de movimentações', 'PASSOU', 'Encontradas ' . count($movimentacoes) . ' movimentações');
            
            // Testa busca por período
            $dataInicio = date('Y-m-01');
            $dataFim = date('Y-m-t');
            $porPeriodo = $movimentacao->findByPeriodo($dataInicio, $dataFim);
            $this->addResult('Movimentações por período', 'PASSOU', 'Busca por período funcionando');
            
        } catch (Exception $e) {
            $this->addResult('Operações com movimentações', 'FALHOU', $e->getMessage());
        }
    }
    
    /**
     * Testa relacionamentos entre entidades
     */
    private function testRelationships() {
        echo "5. Testando relacionamentos...\n";
        
        try {
            // Testa filiais
            $filial = new Filial();
            $filiais = $filial->findAtivas();
            $this->addResult('Filiais ativas', 'PASSOU', 'Encontradas ' . count($filiais) . ' filiais ativas');
            
            // Testa categorias
            $categoria = new Categoria();
            $categorias = $categoria->findAllWithMaterialCount();
            $this->addResult('Categorias com contagem', 'PASSOU', 'Encontradas ' . count($categorias) . ' categorias');
            
            // Testa fornecedores
            $fornecedor = new Fornecedor();
            $fornecedores = $fornecedor->findAllWithMaterialCount();
            $this->addResult('Fornecedores com contagem', 'PASSOU', 'Encontrados ' . count($fornecedores) . ' fornecedores');
            
            // Testa unidades de medida
            $unidade = new UnidadeMedida();
            $unidades = $unidade->findAllWithMaterialCount();
            $this->addResult('Unidades de medida', 'PASSOU', 'Encontradas ' . count($unidades) . ' unidades');
            
            // Testa tipos de movimentação
            $tipo = new TipoMovimentacao();
            $tipos = $tipo->findAllWithUsageCount();
            $this->addResult('Tipos de movimentação', 'PASSOU', 'Encontrados ' . count($tipos) . ' tipos');
            
        } catch (Exception $e) {
            $this->addResult('Relacionamentos', 'FALHOU', $e->getMessage());
        }
    }
    
    /**
     * Adiciona resultado do teste
     */
    private function addResult($test, $status, $message) {
        $this->testResults[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message
        ];
    }
    
    /**
     * Mostra resultados dos testes
     */
    private function showResults() {
        echo "\n=== RESULTADOS DOS TESTES ===\n\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $result) {
            $statusIcon = $result['status'] === 'PASSOU' ? '✅' : '❌';
            echo "{$statusIcon} {$result['test']}: {$result['message']}\n";
            
            if ($result['status'] === 'PASSOU') {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "\n=== RESUMO ===\n";
        echo "Total de testes: " . count($this->testResults) . "\n";
        echo "Passaram: {$passed}\n";
        echo "Falharam: {$failed}\n";
        
        if ($failed === 0) {
            echo "\n🎉 Todos os testes passaram! Sistema funcionando corretamente.\n";
        } else {
            echo "\n⚠️  Alguns testes falharam. Verifique os erros acima.\n";
        }
        
        echo "\n========================\n";
    }
    
    /**
     * Testa inserção de dados de exemplo
     */
    public function testDataInsertion() {
        echo "\n=== TESTE DE INSERÇÃO DE DADOS ===\n\n";
        
        try {
            // Testa inserção de material
            $material = new Material();
            
            // Busca dados necessários
            $filial = new Filial();
            $categoria = new Categoria();
            $unidade = new UnidadeMedida();
            
            $filiais = $filial->findAtivas();
            $categorias = $categoria->findAll();
            $unidades = $unidade->findAll();
            
            if (empty($filiais) || empty($categorias) || empty($unidades)) {
                echo "❌ Dados básicos não encontrados. Execute primeiro: php scripts/init_system.php\n";
                return;
            }
            
            $dadosMaterial = [
                'codigo' => 'TEST001',
                'nome' => 'Material de Teste',
                'descricao' => 'Material criado para teste do sistema',
                'id_categoria' => $categorias[0]['id_categoria'],
                'id_filial' => $filiais[0]['id_filial'],
                'id_unidade' => $unidades[0]['id_unidade'],
                'preco_unitario' => 10.50,
                'estoque_minimo' => 5,
                'estoque_maximo' => 100,
                'estoque_atual' => 0,
                'localizacao_estoque' => 'Prateleira A1',
                'observacoes' => 'Material de teste'
            ];
            
            // Verifica se o código já existe
            if (!$material->codigoExiste($dadosMaterial['codigo'], $dadosMaterial['id_filial'])) {
                $idMaterial = $material->insert($dadosMaterial);
                echo "✅ Material de teste inserido com ID: $idMaterial\n";
                
                // Testa movimentação
                $movimentacao = new Movimentacao();
                $tipos = $movimentacao->getTipoMovimentacao(1); // Assumindo que ID 1 é entrada
                
                if ($tipos) {
                    $dadosMovimentacao = [
                        'id_filial' => $dadosMaterial['id_filial'],
                        'id_material' => $idMaterial,
                        'id_tipo_movimentacao' => 1, // Primeiro tipo de entrada
                        'id_usuario' => 1, // Usuário padrão
                        'quantidade' => 50,
                        'preco_unitario' => 10.50,
                        'numero_documento' => 'TEST001',
                        'observacoes' => 'Movimentação de teste'
                    ];
                    
                    $idMovimentacao = $movimentacao->registrarMovimentacao($dadosMovimentacao);
                    echo "✅ Movimentação de teste registrada com ID: $idMovimentacao\n";
                    
                    // Verifica se o estoque foi atualizado
                    $materialAtualizado = $material->findById($idMaterial);
                    if ($materialAtualizado['estoque_atual'] == 50) {
                        echo "✅ Estoque atualizado corretamente: {$materialAtualizado['estoque_atual']}\n";
                    } else {
                        echo "❌ Erro na atualização do estoque\n";
                    }
                }
            } else {
                echo "ℹ️  Material de teste já existe\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro no teste de inserção: " . $e->getMessage() . "\n";
        }
    }
}

// Execução dos testes
if (php_sapi_name() === 'cli') {
    $tester = new SystemTester();
    
    if (isset($argv[1]) && $argv[1] === 'insert') {
        $tester->testDataInsertion();
    } else {
        $tester->runAllTests();
    }
} else {
    echo "Este script deve ser executado via linha de comando.\n";
    echo "Uso: php test_system.php [insert]\n";
}
?> 