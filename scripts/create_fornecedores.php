<?php
/**
 * Script para criar fornecedores de exemplo
 * Execute este script para popular o banco com dados de teste
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Fornecedor.php';

echo "<h2>Criando Fornecedores de Exemplo</h2>";

try {
    $pdo = Conexao::getInstance()->getPdo();
    $fornecedor = new Fornecedor($pdo);
    
    // Dados de fornecedores de exemplo
    $fornecedores = [
        [
            'razao_social' => 'TechDistribuidor Ltda',
            'nome_fantasia' => 'TechDist',
            'cnpj' => '12.345.678/0001-90',
            'inscricao_estadual' => '123.456.789',
            'endereco' => 'Rua das Tecnologias, 123, Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
            'telefone' => '(11) 99999-9999',
            'email' => 'joao@techdist.com.br',
            'contato_principal' => 'João Silva',
            'ativo' => 1
        ],
        [
            'razao_social' => 'Eletrônicos BR Ltda',
            'nome_fantasia' => 'Eletrônicos BR',
            'cnpj' => '98.765.432/0001-10',
            'inscricao_estadual' => '987.654.321',
            'endereco' => 'Av. dos Eletrônicos, 456, Copacabana',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22070-001',
            'telefone' => '(21) 88888-8888',
            'email' => 'maria@eletronicbr.com',
            'contato_principal' => 'Maria Santos',
            'ativo' => 1
        ],
        [
            'razao_social' => 'InfoParts Distribuidora Ltda',
            'nome_fantasia' => 'InfoParts',
            'cnpj' => '11.222.333/0001-44',
            'inscricao_estadual' => '111.222.333',
            'endereco' => 'Rua das Informações, 789, Vila Madalena',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '05433-000',
            'telefone' => '(11) 77777-7777',
            'email' => 'carlos@infoparts.com.br',
            'contato_principal' => 'Carlos Oliveira',
            'ativo' => 0
        ],
        [
            'razao_social' => 'Games & Cia Ltda',
            'nome_fantasia' => 'Games & Cia',
            'cnpj' => '55.666.777/0001-88',
            'inscricao_estadual' => '555.666.777',
            'endereco' => 'Rua dos Jogos, 321, Centro',
            'cidade' => 'Blumenau',
            'estado' => 'SC',
            'cep' => '89010-000',
            'telefone' => '(47) 66666-6666',
            'email' => 'ana@gamesecia.com.br',
            'contato_principal' => 'Ana Costa',
            'ativo' => 1
        ],
        [
            'razao_social' => 'Construção ABC Ltda',
            'nome_fantasia' => 'Construção ABC',
            'cnpj' => '12.345.678/0001-91',
            'inscricao_estadual' => '123.456.790',
            'endereco' => 'Av. da Construção, 100, Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01310-100',
            'telefone' => '(11) 55555-5555',
            'email' => 'contato@construcaoabc.com.br',
            'contato_principal' => 'Pedro Construtor',
            'ativo' => 1
        ],
        [
            'razao_social' => 'Materiais Silva Ltda',
            'nome_fantasia' => 'Materiais Silva',
            'cnpj' => '98.765.432/0001-11',
            'inscricao_estadual' => '987.654.322',
            'endereco' => 'Rua dos Materiais, 200, Barra',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22640-000',
            'telefone' => '(21) 44444-4444',
            'email' => 'vendas@materiaissilva.com.br',
            'contato_principal' => 'Silva Santos',
            'ativo' => 1
        ],
        [
            'razao_social' => 'Ferro e Aço Ltda',
            'nome_fantasia' => 'Ferro e Aço',
            'cnpj' => '11.222.333/0001-45',
            'inscricao_estadual' => '111.222.334',
            'endereco' => 'Av. do Ferro, 300, Centro',
            'cidade' => 'Belo Horizonte',
            'estado' => 'MG',
            'cep' => '30112-000',
            'telefone' => '(31) 33333-3333',
            'email' => 'comercial@ferroaço.com.br',
            'contato_principal' => 'João Ferro',
            'ativo' => 1
        ]
    ];
    
    $inseridos = 0;
    $erros = 0;
    
    foreach ($fornecedores as $dados) {
        try {
            // Verificar se CNPJ já existe
            if ($fornecedor->cnpjExiste($dados['cnpj'])) {
                echo "⚠️ Fornecedor com CNPJ {$dados['cnpj']} já existe<br>";
                continue;
            }
            
            // Inserir fornecedor
            $id = $fornecedor->create($dados);
            if ($id) {
                echo "✅ Fornecedor criado: {$dados['razao_social']} (ID: $id)<br>";
                $inseridos++;
            } else {
                echo "❌ Erro ao criar fornecedor: {$dados['razao_social']}<br>";
                $erros++;
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao criar fornecedor {$dados['razao_social']}: " . $e->getMessage() . "<br>";
            $erros++;
        }
    }
    
    echo "<h3>Resumo</h3>";
    echo "✅ Fornecedores inseridos: $inseridos<br>";
    echo "❌ Erros: $erros<br>";
    
    if ($inseridos > 0) {
        echo "<p><strong>Fornecedores criados com sucesso!</strong></p>";
        echo "<a href='../fornecedores.php'>Clique aqui para ver a lista de fornecedores</a>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão com o banco: " . $e->getMessage();
}
?> 