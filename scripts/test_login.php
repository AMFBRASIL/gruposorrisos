<?php
/**
 * Script de teste do sistema de login
 * Testa autenticação, criação de usuários e perfis
 */

require_once __DIR__ . '/../config/autoload.php';
loadConfig();

class LoginTester {
    private $usuario;
    private $perfil;
    
    public function __construct() {
        $this->usuario = new Usuario();
        $this->perfil = new Perfil();
    }
    
    /**
     * Executa todos os testes de login
     */
    public function runAllTests() {
        echo "=== TESTE DO SISTEMA DE LOGIN ===\n\n";
        
        $this->testPerfis();
        $this->testUsuarios();
        $this->testAutenticacao();
        $this->testValidacoes();
        
        echo "\n=== TESTES CONCLUÍDOS ===\n";
    }
    
    /**
     * Testa criação e busca de perfis
     */
    private function testPerfis() {
        echo "1. Testando perfis...\n";
        
        try {
            // Insere perfis padrão
            $inseridos = $this->perfil->inserirPerfisPadrao();
            echo "   ✅ Perfis inseridos: $inseridos\n";
            
            // Busca todos os perfis
            $perfis = $this->perfil->findAllWithUserCount();
            echo "   ✅ Perfis encontrados: " . count($perfis) . "\n";
            
            // Testa busca por nome
            $admin = $this->perfil->findByNome('Administrador');
            if ($admin) {
                echo "   ✅ Perfil Administrador encontrado\n";
            } else {
                echo "   ❌ Perfil Administrador não encontrado\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Erro nos testes de perfil: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Testa criação e busca de usuários
     */
    private function testUsuarios() {
        echo "2. Testando usuários...\n";
        
        try {
            // Cria usuário administrador
            $idAdmin = $this->usuario->criarUsuarioAdmin();
            if ($idAdmin) {
                echo "   ✅ Usuário admin criado com ID: $idAdmin\n";
            } else {
                echo "   ℹ️  Usuário admin já existe\n";
            }
            
            // Busca usuário por email
            $admin = $this->usuario->findByEmail('admin@sistema.com');
            if ($admin) {
                echo "   ✅ Usuário admin encontrado: {$admin['nome_completo']}\n";
            } else {
                echo "   ❌ Usuário admin não encontrado\n";
            }
            
            // Testa validação de email
            $emailExiste = $this->usuario->emailExiste('admin@sistema.com');
            echo "   ✅ Email admin existe: " . ($emailExiste ? 'Sim' : 'Não') . "\n";
            
        } catch (Exception $e) {
            echo "   ❌ Erro nos testes de usuário: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Testa autenticação
     */
    private function testAutenticacao() {
        echo "3. Testando autenticação...\n";
        
        try {
            // Testa login com credenciais corretas
            $userData = $this->usuario->autenticar('admin@sistema.com', 'password');
            if ($userData) {
                echo "   ✅ Login bem-sucedido para admin\n";
                echo "   - Nome: {$userData['nome_completo']}\n";
                echo "   - Perfil: {$userData['nome_perfil']}\n";
                echo "   - Filial: {$userData['nome_filial']}\n";
            } else {
                echo "   ❌ Login falhou para admin\n";
            }
            
            // Testa login com senha incorreta
            $userData = $this->usuario->autenticar('admin@sistema.com', 'senhaerrada');
            if (!$userData) {
                echo "   ✅ Login corretamente rejeitado com senha errada\n";
            } else {
                echo "   ❌ Login aceito com senha errada\n";
            }
            
            // Testa login com email inexistente
            $userData = $this->usuario->autenticar('inexistente@teste.com', 'password');
            if (!$userData) {
                echo "   ✅ Login corretamente rejeitado com email inexistente\n";
            } else {
                echo "   ❌ Login aceito com email inexistente\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Erro nos testes de autenticação: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Testa validações
     */
    private function testValidacoes() {
        echo "4. Testando validações...\n";
        
        try {
            // Testa criação de usuário duplicado
            $dadosDuplicado = [
                'nome_completo' => 'Teste Duplicado',
                'email' => 'admin@sistema.com', // Email já existe
                'senha' => 'teste123',
                'cpf' => '111.111.111-11',
                'telefone' => '(11) 11111-1111',
                'id_perfil' => 1,
                'id_filial' => 1,
                'ativo' => 1
            ];
            
            $emailExiste = $this->usuario->emailExiste('admin@sistema.com');
            echo "   ✅ Validação de email duplicado: " . ($emailExiste ? 'Funcionando' : 'Falhou') . "\n";
            
            // Testa criação de perfil duplicado
            $perfilExiste = $this->perfil->nomeExiste('Administrador');
            echo "   ✅ Validação de perfil duplicado: " . ($perfilExiste ? 'Funcionando' : 'Falhou') . "\n";
            
        } catch (Exception $e) {
            echo "   ❌ Erro nos testes de validação: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Testa criação de usuário de teste
     */
    public function createTestUser() {
        echo "\n=== CRIANDO USUÁRIO DE TESTE ===\n";
        
        try {
            // Busca perfil operador
            $perfilOperador = $this->perfil->findByNome('Operador');
            if (!$perfilOperador) {
                echo "❌ Perfil Operador não encontrado\n";
                return;
            }
            
            // Busca filial matriz
            $sql = "SELECT id_filial FROM tbl_filiais WHERE codigo_filial = 'MATRIZ' LIMIT 1";
            $pdo = Conexao::getInstance()->getPdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $filial = $stmt->fetch();
            
            if (!$filial) {
                echo "❌ Filial matriz não encontrada\n";
                return;
            }
            
            // Verifica se o usuário já existe
            if ($this->usuario->emailExiste('teste@sistema.com')) {
                echo "ℹ️  Usuário de teste já existe\n";
                return;
            }
            
            $dadosTeste = [
                'nome_completo' => 'Usuário de Teste',
                'email' => 'teste@sistema.com',
                'senha' => 'teste123',
                'cpf' => '222.222.222-22',
                'telefone' => '(11) 22222-2222',
                'id_perfil' => $perfilOperador['id_perfil'],
                'id_filial' => $filial['id_filial'],
                'ativo' => 1
            ];
            
            $idTeste = $this->usuario->criarUsuario($dadosTeste);
            if ($idTeste) {
                echo "✅ Usuário de teste criado com ID: $idTeste\n";
                echo "Email: teste@sistema.com\n";
                echo "Senha: teste123\n";
                
                // Testa login do usuário de teste
                $userData = $this->usuario->autenticar('teste@sistema.com', 'teste123');
                if ($userData) {
                    echo "✅ Login do usuário de teste funcionando\n";
                } else {
                    echo "❌ Login do usuário de teste falhou\n";
                }
            } else {
                echo "❌ Erro ao criar usuário de teste\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao criar usuário de teste: " . $e->getMessage() . "\n";
        }
    }
}

// Execução do script
if (php_sapi_name() === 'cli') {
    $tester = new LoginTester();
    
    if (isset($argv[1]) && $argv[1] === 'create-user') {
        $tester->createTestUser();
    } else {
        $tester->runAllTests();
    }
} else {
    echo "Este script deve ser executado via linha de comando.\n";
    echo "Uso: php test_login.php [create-user]\n";
}
?> 