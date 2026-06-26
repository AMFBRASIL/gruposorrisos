<?php
/**
 * Configurações de pedidos de compra / cotação fornecedor.
 */

if (!defined('PEDIDOS_DESCONTO_FORNECEDOR_PERCENTUAL')) {
    define('PEDIDOS_DESCONTO_FORNECEDOR_PERCENTUAL', 5);
}

const CHAVE_CONFIG_DESCONTO_FORNECEDOR = 'pedidos_desconto_fornecedor_percentual';

/**
 * Garante que a chave de desconto exista em tbl_configuracoes.
 */
function garantirConfigDescontoFornecedor(?PDO $pdo = null, ?float $padrao = null): void
{
    static $garantido = false;
    if ($garantido) {
        return;
    }

    $valorPadrao = $padrao ?? (float) PEDIDOS_DESCONTO_FORNECEDOR_PERCENTUAL;

    try {
        if (!$pdo) {
            require_once __DIR__ . '/../../config/conexao.php';
            $pdo = Conexao::getInstance()->getPdo();
        }

        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO tbl_configuracoes (chave, valor, descricao, tipo, categoria)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            CHAVE_CONFIG_DESCONTO_FORNECEDOR,
            (string) $valorPadrao,
            'Desconto padrão (%) aplicado na cotação do fornecedor sobre o preço bruto de cada item',
            'numero',
            'pedidos',
        ]);
        $garantido = true;
    } catch (Throwable $e) {
        error_log('garantirConfigDescontoFornecedor: ' . $e->getMessage());
    }
}

/**
 * Percentual de desconto padrão na cotação do fornecedor (0–100).
 */
function obterDescontoFornecedorPercentual(?PDO $pdo = null): float
{
    $padrao = (float) PEDIDOS_DESCONTO_FORNECEDOR_PERCENTUAL;

    try {
        if (!$pdo) {
            require_once __DIR__ . '/../../config/conexao.php';
            $pdo = Conexao::getInstance()->getPdo();
        }

        garantirConfigDescontoFornecedor($pdo, $padrao);

        require_once __DIR__ . '/../../models/Configuracao.php';
        $config = new Configuracao();
        $valor = $config->getValor(CHAVE_CONFIG_DESCONTO_FORNECEDOR, null);

        if ($valor === null || $valor === '') {
            return $padrao;
        }

        $pct = (float) str_replace(',', '.', (string) $valor);
        if ($pct < 0) {
            $pct = 0;
        }
        if ($pct > 100) {
            $pct = 100;
        }

        return $pct;
    } catch (Throwable $e) {
        error_log('obterDescontoFornecedorPercentual: ' . $e->getMessage());
        return $padrao;
    }
}

/**
 * Percentual salvo no pedido ou o padrão atual do sistema.
 */
function obterDescontoCotacaoPedido(array $pedido, ?PDO $pdo = null): float
{
    if (isset($pedido['desconto_cotacao_percentual']) && $pedido['desconto_cotacao_percentual'] !== null && $pedido['desconto_cotacao_percentual'] !== '') {
        $pct = (float) $pedido['desconto_cotacao_percentual'];
        if ($pct >= 0 && $pct <= 100) {
            return $pct;
        }
    }

    return obterDescontoFornecedorPercentual($pdo);
}

/**
 * Persiste o percentual de desconto usado na cotação (ignora se coluna não existir).
 */
function salvarDescontoCotacaoPedido(PDO $pdo, int $pedidoId, float $percentual): void
{
    try {
        $stmt = $pdo->prepare(
            'UPDATE tbl_pedidos_compra SET desconto_cotacao_percentual = ? WHERE id_pedido = ?'
        );
        $stmt->execute([round($percentual, 2), $pedidoId]);
    } catch (Throwable $e) {
        error_log('salvarDescontoCotacaoPedido (coluna pode não existir): ' . $e->getMessage());
    }
}
