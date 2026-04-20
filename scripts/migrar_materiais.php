<?php
/**
 * SCRIPT DE MIGRAÇÃO DE MATERIAIS
 * Grupo Sorrisos - Nova Estrutura Centralizada
 * 
 * Este script migra os dados da estrutura antiga (tbl_materiais)
 * para a nova estrutura (tbl_catalogo_materiais + tbl_estoque_filiais)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexao.php';

class MigradorMateriais {
    private $pdo;
    private $log = [];
    
    public function __construct() {
        try {
            $this->pdo = Conexao::getInstance()->getPdo();
            $this->pdo->beginTransaction();
            echo "🚀 Iniciando migração de materiais...\n";
        } catch (Exception $e) {
            die("❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * Executa a migração completa
     */
    public function executarMigracao() {
        try {
            echo "\n📋 ETAPA 1: Verificando estrutura atual...\n";
            $this->verificarEstruturaAtual();
            
            echo "\n📋 ETAPA 2: Criando novas tabelas...\n";
            $this->criarNovasTabelas();
            
            echo "\n📋 ETAPA 3: Migrando dados para o catálogo...\n";
            $this->migrarParaCatalogo();
            
            echo "\n📋 ETAPA 4: Migrando estoques por filial...\n";
            $this->migrarEstoques();
            
            echo "\n📋 ETAPA 5: Verificando integridade...\n";
            $this->verificarIntegridade();
            
            echo "\n📋 ETAPA 6: Finalizando migração...\n";
            $this->finalizarMigracao();
            
            $this->pdo->commit();
            echo "\n✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
            $this->exibirResumo();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "\n❌ ERRO NA MIGRAÇÃO: " . $e->getMessage() . "\n";
            echo "🔙 Rollback executado. Dados originais preservados.\n";
        }
    }
    
    /**
     * Verifica a estrutura atual do banco
     */
    private function verificarEstruturaAtual() {
        // Verificar se a tabela antiga existe
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'tbl_materiais'");
        if ($stmt->rowCount() == 0) {
            throw new Exception("Tabela tbl_materiais não encontrada!");
        }
        
        // Contar materiais existentes
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM tbl_materiais WHERE ativo = 1");
        $total = $stmt->fetch()['total'];
        echo "   📊 Materiais ativos encontrados: $total\n";
        
        // Verificar filiais
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM tbl_filiais WHERE filial_ativa = 1");
        $filiais = $stmt->fetch()['total'];
        echo "   🏢 Filiais ativas encontradas: $filiais\n";
        
        $this->log['materiais_existentes'] = $total;
        $this->log['filiais_ativas'] = $filiais;
    }
    
    /**
     * Cria as novas tabelas
     */
    private function criarNovasTabelas() {
        $sql = file_get_contents('../database/nova_estrutura_materiais.sql');
        
        // Executar apenas as criações de tabelas
        $comandos = explode(';', $sql);
        foreach ($comandos as $comando) {
            $comando = trim($comando);
            if (!empty($comando) && strpos($comando, 'CREATE TABLE') !== false) {
                $this->pdo->exec($comando);
                echo "   ✅ Tabela criada/verificada\n";
            }
        }
        
        echo "   🗄️ Estrutura de tabelas criada com sucesso\n";
    }
    
    /**
     * Migra dados para o catálogo centralizado
     */
    private function migrarParaCatalogo() {
        echo "   🔄 Processando materiais únicos...\n";
        
        // Buscar materiais únicos por código
        $sql = "SELECT DISTINCT 
                    m.codigo,
                    m.nome,
                    m.descricao,
                    m.id_categoria,
                    m.id_fornecedor,
                    m.id_unidade,
                    m.preco_unitario,
                    m.estoque_minimo,
                    m.estoque_maximo,
                    m.codigo_barras,
                    m.observacoes
                FROM tbl_materiais m 
                WHERE m.ativo = 1 
                GROUP BY m.codigo";
        
        $stmt = $this->pdo->query($sql);
        $materiais = $stmt->fetchAll();
        
        $migrados = 0;
        foreach ($materiais as $material) {
            // Inserir no catálogo
            $sql = "INSERT INTO tbl_catalogo_materiais (
                        codigo, nome, descricao, id_categoria, id_fornecedor, id_unidade,
                        preco_unitario_padrao, estoque_minimo_padrao, estoque_maximo_padrao,
                        codigo_barras, observacoes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Tratar código de barras vazio
            $codigoBarras = !empty($material['codigo_barras']) ? $material['codigo_barras'] : null;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $material['codigo'],
                $material['nome'],
                $material['descricao'],
                $material['id_categoria'] ?: 1,
                $material['id_fornecedor'],
                $material['id_unidade'] ?: 1,
                $material['preco_unitario'] ?: 0.00,
                $material['estoque_minimo'] ?: 0.00,
                $material['estoque_maximo'] ?: 0.00,
                $codigoBarras,
                $material['observacoes']
            ]);
            
            $migrados++;
            if ($migrados % 10 == 0) {
                echo "      📦 $migrados materiais processados...\n";
            }
        }
        
        echo "   ✅ $migrados materiais migrados para o catálogo\n";
        $this->log['materiais_migrados'] = $migrados;
    }
    
    /**
     * Migra estoques por filial
     */
    private function migrarEstoques() {
        echo "   🔄 Migrando estoques por filial...\n";
        
        // Buscar todos os materiais com estoque
        $sql = "SELECT 
                    m.id_material,
                    m.codigo,
                    m.id_filial,
                    m.estoque_atual,
                    m.estoque_minimo,
                    m.estoque_maximo,
                    m.preco_unitario
                FROM tbl_materiais m 
                WHERE m.ativo = 1";
        
        $stmt = $this->pdo->query($sql);
        $materiais = $stmt->fetchAll();
        
        $estoques_migrados = 0;
        foreach ($materiais as $material) {
            // Buscar ID do catálogo
            $sql = "SELECT id_catalogo FROM tbl_catalogo_materiais WHERE codigo = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$material['codigo']]);
            $catalogo = $stmt->fetch();
            
            if ($catalogo) {
                // Inserir estoque da filial
                $sql = "INSERT INTO tbl_estoque_filiais (
                            id_catalogo, id_filial, estoque_atual, estoque_minimo, 
                            estoque_maximo, preco_unitario
                        ) VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $catalogo['id_catalogo'],
                    $material['id_filial'],
                    $material['estoque_atual'] ?: 0.00,
                    $material['estoque_minimo'] ?: 0.00,
                    $material['estoque_maximo'] ?: 0.00,
                    $material['preco_unitario'] ?: 0.00
                ]);
                
                $estoques_migrados++;
            }
        }
        
        echo "   ✅ $estoques_migrados estoques migrados por filial\n";
        $this->log['estoques_migrados'] = $estoques_migrados;
    }
    
    /**
     * Verifica a integridade dos dados migrados
     */
    private function verificarIntegridade() {
        echo "   🔍 Verificando integridade dos dados...\n";
        
        // Verificar total de materiais no catálogo
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM tbl_catalogo_materiais WHERE ativo = 1");
        $total_catalogo = $stmt->fetch()['total'];
        
        // Verificar total de estoques
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM tbl_estoque_filiais WHERE ativo = 1");
        $total_estoques = $stmt->fetch()['total'];
        
        echo "      📊 Catálogo: $total_catalogo materiais\n";
        echo "      📦 Estoques: $total_estoques registros\n";
        
        if ($total_catalogo != $this->log['materiais_migrados']) {
            throw new Exception("Divergência no número de materiais migrados!");
        }
        
        echo "   ✅ Integridade verificada com sucesso\n";
    }
    
    /**
     * Finaliza a migração
     */
    private function finalizarMigracao() {
        echo "   🎯 Finalizando migração...\n";
        
        // Criar backup da tabela antiga
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS tbl_materiais_backup_" . date('Y_m_d_H_i_s') . " AS SELECT * FROM tbl_materiais");
        echo "      💾 Backup da tabela antiga criado\n";
        
        // Marcar tabela antiga como inativa (não deletar ainda)
        $this->pdo->exec("UPDATE tbl_materiais SET ativo = 0");
        echo "      🚫 Tabela antiga marcada como inativa\n";
        
        echo "   ✅ Migração finalizada\n";
    }
    
    /**
     * Exibe resumo da migração
     */
    private function exibirResumo() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESUMO DA MIGRAÇÃO\n";
        echo str_repeat("=", 60) . "\n";
        echo "📦 Materiais existentes: " . $this->log['materiais_existentes'] . "\n";
        echo "🏢 Filiais ativas: " . $this->log['filiais_ativas'] . "\n";
        echo "✅ Materiais migrados: " . $this->log['materiais_migrados'] . "\n";
        echo "📦 Estoques migrados: " . $this->log['estoques_migrados'] . "\n";
        echo str_repeat("=", 60) . "\n";
        echo "🎉 Sistema migrado com sucesso para nova estrutura!\n";
        echo "💡 Próximos passos:\n";
        echo "   1. Testar funcionalidades básicas\n";
        echo "   2. Atualizar modelos PHP\n";
        echo "   3. Atualizar APIs\n";
        echo "   4. Atualizar frontend\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Executar migração
if (php_sapi_name() === 'cli') {
    echo "🚀 INICIANDO MIGRAÇÃO DE MATERIAIS\n";
    echo "📅 Data/Hora: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 60) . "\n";
    
    $migrador = new MigradorMateriais();
    $migrador->executarMigracao();
} else {
    echo "❌ Este script deve ser executado via linha de comando!\n";
    echo "💡 Use: php scripts/migrar_materiais.php\n";
} 