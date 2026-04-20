<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Filial.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Fornecedor.php';
require_once __DIR__ . '/../models/UnidadeMedida.php';
require_once __DIR__ . '/../models/TipoMovimentacao.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Usuario.php';

class SystemInitializer {
    private $pdo;
    private $filial;
    private $categoria;
    private $fornecedor;
    private $unidadeMedida;
    private $tipoMovimentacao;
    private $perfil;
    private $usuario;
    
    public function __construct() {
        $this->pdo = Conexao::getInstance()->getPdo();
        $this->filial = new Filial();
        $this->categoria = new Categoria();
        $this->fornecedor = new Fornecedor();
        $this->unidadeMedida = new UnidadeMedida();
        $this->tipoMovimentacao = new TipoMovimentacao();
        $this->perfil = new Perfil();
        $this->usuario = new Usuario();
    }
    
    /**
     * Inicializa o sistema com dados básicos
     */
    public function initialize() {
        try {
            echo "Iniciando configuração do sistema...\n";
            
            // 1. Inserir filial matriz
            $this->insertMatriz();
            
            // 2. Inserir categorias padrão
            $this->insertCategoriasPadrao();
            
            // 3. Inserir fornecedores padrão
            $this->insertFornecedoresPadrao();
            
            // 4. Inserir unidades de medida
            $this->insertUnidadesMedida();
            
            // 5. Inserir tipos de movimentação
            $this->insertTiposMovimentacao();
            
            // 6. Inserir perfis padrão
            $this->insertPerfisPadrao();
            
            // 7. Inserir usuário administrador
            $this->insertUsuarioAdmin();
            
            echo "Sistema inicializado com sucesso!\n";
            
        } catch (Exception $e) {
            echo "Erro ao inicializar sistema: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Insere a filial matriz
     */
    private function insertMatriz() {
        echo "Inserindo filial matriz...\n";
        
        $matriz = [
            'codigo_filial' => 'MATRIZ',
            'nome_filial' => 'Matriz - Grupo Sorrisos',
            'razao_social' => 'Grupo Sorrisos Ltda',
            'cnpj' => '12.345.678/0001-90',
            'inscricao_estadual' => '123456789',
            'endereco' => 'Rua das Flores, 123 - Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
            'telefone' => '(11) 1234-5678',
            'email' => 'contato@gruposorrisos.com.br',
            'responsavel' => 'Administrador do Sistema',
            'tipo_filial' => 'matriz',
            'filial_ativa' => 1,
            'data_inauguracao' => date('Y-m-d'),
            'observacoes' => 'Filial matriz do sistema'
        ];
        
        if (!$this->filial->codigoExiste('MATRIZ')) {
            $this->filial->insert($matriz);
            echo "Filial matriz criada com sucesso!\n";
        } else {
            echo "Filial matriz já existe.\n";
        }
    }
    
    /**
     * Insere categorias padrão
     */
    private function insertCategoriasPadrao() {
        echo "Inserindo categorias padrão...\n";
        
        $categorias = [
            ['nome_categoria' => 'Material de Escritório', 'descricao' => 'Materiais para uso em escritório'],
            ['nome_categoria' => 'Material de Limpeza', 'descricao' => 'Produtos de limpeza e higiene'],
            ['nome_categoria' => 'Material de Informática', 'descricao' => 'Equipamentos e acessórios de informática'],
            ['nome_categoria' => 'Material de Manutenção', 'descricao' => 'Materiais para manutenção predial'],
            ['nome_categoria' => 'Material de Segurança', 'descricao' => 'Equipamentos de segurança'],
            ['nome_categoria' => 'Material de Cozinha', 'descricao' => 'Utensílios e materiais para cozinha'],
            ['nome_categoria' => 'Material de Jardim', 'descricao' => 'Ferramentas e materiais para jardinagem'],
            ['nome_categoria' => 'Material de Construção', 'descricao' => 'Materiais para construção e reforma'],
            ['nome_categoria' => 'Material de Iluminação', 'descricao' => 'Lâmpadas e materiais de iluminação'],
            ['nome_categoria' => 'Material de Pintura', 'descricao' => 'Tintas e materiais para pintura']
        ];
        
        $inseridas = 0;
        foreach ($categorias as $categoria) {
            if (!$this->categoria->nomeExiste($categoria['nome_categoria'])) {
                $this->categoria->insert($categoria);
                $inseridas++;
            }
        }
        
        echo "Categorias inseridas: $inseridas\n";
    }
    
    /**
     * Insere fornecedores padrão
     */
    private function insertFornecedoresPadrao() {
        echo "Inserindo fornecedores padrão...\n";
        
        $fornecedores = [
            [
                'razao_social' => 'Distribuidora Central Ltda',
                'nome_fantasia' => 'Distribuidora Central',
                'cnpj' => '11.111.111/0001-11',
                'inscricao_estadual' => '111111111',
                'endereco' => 'Av. das Indústrias, 1000',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '04567-890',
                'telefone' => '(11) 2345-6789',
                'email' => 'contato@distribuidoracentral.com.br',
                'contato_principal' => 'João Silva'
            ],
            [
                'razao_social' => 'Mega Fornecedor S.A.',
                'nome_fantasia' => 'Mega Fornecedor',
                'cnpj' => '22.222.222/0001-22',
                'inscricao_estadual' => '222222222',
                'endereco' => 'Rua dos Comerciantes, 500',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '05678-901',
                'telefone' => '(11) 3456-7890',
                'email' => 'vendas@megafornecedor.com.br',
                'contato_principal' => 'Maria Santos'
            ],
            [
                'razao_social' => 'Fornecedor Express Ltda',
                'nome_fantasia' => 'Fornecedor Express',
                'cnpj' => '33.333.333/0001-33',
                'inscricao_estadual' => '333333333',
                'endereco' => 'Av. do Comércio, 750',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '06789-012',
                'telefone' => '(11) 4567-8901',
                'email' => 'pedidos@fornecedorexpress.com.br',
                'contato_principal' => 'Pedro Oliveira'
            ]
        ];
        
        $inseridos = 0;
        foreach ($fornecedores as $fornecedor) {
            if (!$this->fornecedor->cnpjExiste($fornecedor['cnpj'])) {
                $this->fornecedor->insert($fornecedor);
                $inseridos++;
            }
        }
        
        echo "Fornecedores inseridos: $inseridos\n";
    }
    
    /**
     * Insere unidades de medida
     */
    private function insertUnidadesMedida() {
        echo "Inserindo unidades de medida...\n";
        
        $inseridas = $this->unidadeMedida->inserirUnidadesPadrao();
        echo "Unidades de medida inseridas: $inseridas\n";
    }
    
    /**
     * Insere tipos de movimentação
     */
    private function insertTiposMovimentacao() {
        echo "Inserindo tipos de movimentação...\n";
        
        $inseridos = $this->tipoMovimentacao->inserirTiposPadrao();
        echo "Tipos de movimentação inseridos: $inseridos\n";
    }
    
    /**
     * Insere perfis padrão
     */
    private function insertPerfisPadrao() {
        echo "Inserindo perfis padrão...\n";
        
        $inseridos = $this->perfil->inserirPerfisPadrao();
        echo "Perfis inseridos: $inseridos\n";
    }
    
    /**
     * Insere usuário administrador
     */
    private function insertUsuarioAdmin() {
        echo "Inserindo usuário administrador...\n";
        
        $idAdmin = $this->usuario->criarUsuarioAdmin();
        if ($idAdmin) {
            echo "Usuário administrador criado com ID: $idAdmin\n";
            echo "Email: admin@sistema.com\n";
            echo "Senha: password\n";
        } else {
            echo "Usuário administrador já existe\n";
        }
    }
    
    /**
     * Verifica se o sistema já foi inicializado
     */
    public function isInitialized() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM tbl_filiais WHERE filial_ativa = 1) as filiais,
                    (SELECT COUNT(*) FROM tbl_categorias WHERE ativo = 1) as categorias,
                    (SELECT COUNT(*) FROM tbl_fornecedores WHERE ativo = 1) as fornecedores,
                    (SELECT COUNT(*) FROM tbl_unidades_medida WHERE ativo = 1) as unidades,
                    (SELECT COUNT(*) FROM tbl_tipos_movimentacao WHERE ativo = 1) as tipos,
                    (SELECT COUNT(*) FROM tbl_perfis WHERE ativo = 1) as perfis,
                    (SELECT COUNT(*) FROM tbl_usuarios WHERE ativo = 1) as usuarios";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['filiais'] > 0 && $result['categorias'] > 0 && 
               $result['fornecedores'] > 0 && $result['unidades'] > 0 && 
               $result['tipos'] > 0 && $result['perfis'] > 0 && $result['usuarios'] > 0;
    }
    
    /**
     * Mostra status do sistema
     */
    public function showStatus() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM tbl_filiais WHERE filial_ativa = 1) as filiais,
                    (SELECT COUNT(*) FROM tbl_categorias WHERE ativo = 1) as categorias,
                    (SELECT COUNT(*) FROM tbl_fornecedores WHERE ativo = 1) as fornecedores,
                    (SELECT COUNT(*) FROM tbl_unidades_medida WHERE ativo = 1) as unidades,
                    (SELECT COUNT(*) FROM tbl_tipos_movimentacao WHERE ativo = 1) as tipos,
                    (SELECT COUNT(*) FROM tbl_perfis WHERE ativo = 1) as perfis,
                    (SELECT COUNT(*) FROM tbl_usuarios WHERE ativo = 1) as usuarios,
                    (SELECT COUNT(*) FROM tbl_materiais WHERE ativo = 1) as materiais,
                    (SELECT COUNT(*) FROM tbl_movimentacoes) as movimentacoes";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "\n=== STATUS DO SISTEMA ===\n";
        echo "Filiais: " . $result['filiais'] . "\n";
        echo "Categorias: " . $result['categorias'] . "\n";
        echo "Fornecedores: " . $result['fornecedores'] . "\n";
        echo "Unidades de Medida: " . $result['unidades'] . "\n";
        echo "Tipos de Movimentação: " . $result['tipos'] . "\n";
        echo "Perfis: " . $result['perfis'] . "\n";
        echo "Usuários: " . $result['usuarios'] . "\n";
        echo "Materiais: " . $result['materiais'] . "\n";
        echo "Movimentações: " . $result['movimentacoes'] . "\n";
        echo "========================\n\n";
    }
}

// Execução do script
if (php_sapi_name() === 'cli') {
    $initializer = new SystemInitializer();
    
    if (isset($argv[1]) && $argv[1] === 'status') {
        $initializer->showStatus();
    } else {
        if ($initializer->isInitialized()) {
            echo "Sistema já foi inicializado!\n";
            $initializer->showStatus();
        } else {
            $initializer->initialize();
            $initializer->showStatus();
        }
    }
} else {
    echo "Este script deve ser executado via linha de comando.\n";
    echo "Uso: php init_system.php [status]\n";
}
?>
 