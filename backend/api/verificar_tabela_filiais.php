<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/config.php';
require_once '../../config/conexao.php';

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Verificar se a tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_filiais'");
        $tabelaExiste = $stmt->rowCount() > 0;
        
        if (!$tabelaExiste) {
            // Criar a tabela
            $sql = "
            CREATE TABLE IF NOT EXISTS `tbl_filiais` (
              `id_filial` int(11) NOT NULL AUTO_INCREMENT,
              `codigo_filial` varchar(20) NOT NULL,
              `nome_filial` varchar(255) NOT NULL,
              `razao_social` varchar(255) DEFAULT NULL,
              `tipo_filial` enum('matriz','filial') NOT NULL DEFAULT 'filial',
              `cnpj` varchar(18) DEFAULT NULL,
              `inscricao_estadual` varchar(20) DEFAULT NULL,
              `endereco` varchar(255) DEFAULT NULL,
              `numero` varchar(10) DEFAULT NULL,
              `complemento` varchar(100) DEFAULT NULL,
              `bairro` varchar(100) DEFAULT NULL,
              `cidade` varchar(100) DEFAULT NULL,
              `estado` char(2) DEFAULT NULL,
              `cep` varchar(10) DEFAULT NULL,
              `telefone` varchar(20) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `responsavel` varchar(255) DEFAULT NULL,
              `email_responsavel` varchar(255) DEFAULT NULL,
              `telefone_responsavel` varchar(20) DEFAULT NULL,
              `data_inauguracao` date DEFAULT NULL,
              `filial_ativa` enum('1','0') NOT NULL DEFAULT '1',
              `observacoes` text DEFAULT NULL,
              `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
              `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id_filial`),
              UNIQUE KEY `codigo_filial` (`codigo_filial`),
              KEY `idx_filial_ativa` (`filial_ativa`),
              KEY `idx_tipo_filial` (`tipo_filial`),
              KEY `idx_estado` (`estado`),
              KEY `idx_cidade` (`cidade`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            $pdo->exec($sql);
            
            // Inserir dados de exemplo
            $dadosExemplo = [
                ['MAT001', 'Matriz - São Paulo', 'Grupo Sorrisos Ltda', 'matriz', '12.345.678/0001-90', 'Av. Paulista, 1000', 'São Paulo', 'SP', '(11) 3000-0000', 'contato@gruposorrisos.com.br', 'Maria Silva', 'maria@gruposorrisos.com.br', '1', 'Sede principal da empresa'],
                ['FIL001', 'Filial - Rio de Janeiro', 'Grupo Sorrisos RJ Ltda', 'filial', '12.345.678/0002-71', 'Rua do Ouvidor, 150', 'Rio de Janeiro', 'RJ', '(21) 2500-0000', 'rj@gruposorrisos.com.br', 'João Santos', 'joao@gruposorrisos.com.br', '1', 'Filial da capital carioca'],
                ['FIL002', 'Filial - Belo Horizonte', 'Grupo Sorrisos MG Ltda', 'filial', '12.345.678/0003-52', 'Av. Afonso Pena, 500', 'Belo Horizonte', 'MG', '(31) 3200-0000', 'bh@gruposorrisos.com.br', 'Ana Costa', 'ana@gruposorrisos.com.br', '1', 'Filial de Minas Gerais'],
                ['FIL003', 'Filial - Brasília', 'Grupo Sorrisos DF Ltda', 'filial', '12.345.678/0004-33', 'SQS 115, Bloco A', 'Brasília', 'DF', '(61) 3300-0000', 'bsb@gruposorrisos.com.br', 'Carlos Oliveira', 'carlos@gruposorrisos.com.br', '0', 'Filial temporariamente inativa']
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO tbl_filiais (
                    codigo_filial, nome_filial, razao_social, tipo_filial, cnpj, endereco, cidade, estado, telefone, email, 
                    responsavel, email_responsavel, filial_ativa, observacoes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($dadosExemplo as $dados) {
                $stmt->execute($dados);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Tabela criada com sucesso e dados de exemplo inseridos',
                'tabela_criada' => true
            ]);
        } else {
            // Verificar se há dados
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_filiais");
            $total = $stmt->fetch()['total'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Tabela já existe',
                'tabela_criada' => false,
                'total_registros' => $total
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
} 