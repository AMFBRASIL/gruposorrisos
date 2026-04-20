<?php
/**
 * Script para criar a tabela tbl_pedidos_itens com a nova estrutura
 * Compatível com tbl_catalogo_materiais
 */

require_once '../config/config.php';
require_once '../config/conexao.php';

echo "🔧 Criando tabela tbl_pedidos_itens...\n\n";

try {
    $pdo = Conexao::getInstance()->getPdo();
    
    // Verificar se a tabela já existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_pedidos_itens'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️ Tabela tbl_pedidos_itens já existe!\n";
        echo "🔍 Verificando estrutura...\n";
        
        $stmt = $pdo->query("DESCRIBE tbl_pedidos_itens");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Estrutura atual:\n";
        foreach ($colunas as $coluna) {
            echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
        }
        
        // Verificar se precisa de alterações
        $temIdCatalogo = false;
        foreach ($colunas as $coluna) {
            if ($coluna['Field'] === 'id_catalogo') {
                $temIdCatalogo = true;
                break;
            }
        }
        
        if (!$temIdCatalogo) {
            echo "\n⚠️ Tabela precisa ser atualizada para nova estrutura!\n";
            echo "🔧 Adicionando coluna id_catalogo...\n";
            
            $sql = "ALTER TABLE tbl_pedidos_itens ADD COLUMN id_catalogo INT(11) NULL AFTER id_item";
            $pdo->exec($sql);
            echo "✅ Coluna id_catalogo adicionada\n";
            
            // Adicionar índice
            $sql = "ALTER TABLE tbl_pedidos_itens ADD INDEX idx_id_catalogo (id_catalogo)";
            $pdo->exec($sql);
            echo "✅ Índice adicionado para id_catalogo\n";
            
            // Adicionar foreign key
            try {
                $sql = "ALTER TABLE tbl_pedidos_itens ADD CONSTRAINT fk_pedidos_itens_catalogo 
                        FOREIGN KEY (id_catalogo) REFERENCES tbl_catalogo_materiais(id_catalogo) ON DELETE SET NULL";
                $pdo->exec($sql);
                echo "✅ Foreign key adicionada\n";
            } catch (Exception $e) {
                echo "⚠️ Erro ao adicionar foreign key (pode já existir): " . $e->getMessage() . "\n";
            }
        } else {
            echo "\n✅ Tabela já está com a estrutura correta!\n";
        }
        
    } else {
        echo "🔧 Criando tabela tbl_pedidos_itens...\n";
        
        $sql = "CREATE TABLE tbl_pedidos_itens (
            id_item INT(11) NOT NULL AUTO_INCREMENT,
            id_pedido INT(11) NOT NULL,
            id_catalogo INT(11) NULL COMMENT 'Referência ao catálogo de materiais',
            quantidade DECIMAL(15,3) NOT NULL DEFAULT 0.000,
            preco_unitario DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            preco_fornecedor DECIMAL(15,4) NULL COMMENT 'Preço oferecido pelo fornecedor',
            valor_total DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            unidade_medida VARCHAR(50) NULL COMMENT 'Unidade de medida do item',
            observacoes TEXT NULL,
            disponivel TINYINT(1) DEFAULT 1 COMMENT 'Se o item está disponível',
            ativo TINYINT(1) DEFAULT 1,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_item),
            INDEX idx_id_pedido (id_pedido),
            INDEX idx_id_catalogo (id_catalogo),
            INDEX idx_ativo (ativo),
            CONSTRAINT fk_pedidos_itens_pedido FOREIGN KEY (id_pedido) REFERENCES tbl_pedidos_compra(id_pedido) ON DELETE CASCADE,
            CONSTRAINT fk_pedidos_itens_catalogo FOREIGN KEY (id_catalogo) REFERENCES tbl_catalogo_materiais(id_catalogo) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ Tabela tbl_pedidos_itens criada com sucesso!\n";
    }
    
    echo "\n🔍 Verificando estrutura final:\n";
    $stmt = $pdo->query("DESCRIBE tbl_pedidos_itens");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $coluna) {
        echo "   - {$coluna['Field']}: {$coluna['Type']} ({$coluna['Null']}) {$coluna['Key']}\n";
    }
    
    echo "\n✅ Tabela tbl_pedidos_itens configurada com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 