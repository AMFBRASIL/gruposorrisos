<?php
/**
 * Garante colunas de metadados do envio da Nota Fiscal em tbl_pedidos_compra.
 */
function garantirMetadadosNotaFiscalPedido(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }

    try {
        $listCols = static function () use ($pdo): array {
            $st = $pdo->query("
                SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'tbl_pedidos_compra'
            ");
            return array_map('strtolower', $st->fetchAll(PDO::FETCH_COLUMN));
        };

        $cols = $listCols();
        $anchor = in_array('url_nota_fiscal', $cols, true) ? 'url_nota_fiscal' : 'observacoes';

        if (!in_array('nf_nome_arquivo_original', $cols, true)) {
            $pdo->exec("ALTER TABLE tbl_pedidos_compra ADD COLUMN nf_nome_arquivo_original VARCHAR(255) NULL COMMENT 'Nome original do arquivo NF' AFTER {$anchor}");
            $cols = $listCols();
        }
        if (!in_array('nf_data_envio', $cols, true)) {
            $pdo->exec('ALTER TABLE tbl_pedidos_compra ADD COLUMN nf_data_envio DATETIME NULL COMMENT \'Data/hora envio NF\' AFTER nf_nome_arquivo_original');
            $cols = $listCols();
        }
        if (!in_array('nf_id_usuario_envio', $cols, true)) {
            $pdo->exec('ALTER TABLE tbl_pedidos_compra ADD COLUMN nf_id_usuario_envio INT NULL COMMENT \'Usuário que enviou a NF\' AFTER nf_data_envio');
            $cols = $listCols();
        }
        if (!in_array('nf_tamanho_bytes', $cols, true)) {
            $pdo->exec('ALTER TABLE tbl_pedidos_compra ADD COLUMN nf_tamanho_bytes BIGINT UNSIGNED NULL AFTER nf_id_usuario_envio');
            $cols = $listCols();
        }

        $ok = in_array('nf_nome_arquivo_original', $cols, true)
            && in_array('nf_data_envio', $cols, true)
            && in_array('nf_id_usuario_envio', $cols, true)
            && in_array('nf_tamanho_bytes', $cols, true);
        if ($ok) {
            $done = true;
        }
    } catch (Throwable $e) {
        error_log('garantirMetadadosNotaFiscalPedido: ' . $e->getMessage());
    }
}

/**
 * ID do usuário logado para gravar nf_id_usuario_envio (sessão já iniciada).
 */
function obterIdUsuarioSessaoParaMetadadosNf(): ?int {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    foreach (['usuario_id', 'id_usuario'] as $chave) {
        if (!isset($_SESSION[$chave])) {
            continue;
        }
        $id = (int) $_SESSION[$chave];
        if ($id > 0) {
            return $id;
        }
    }
    return null;
}

/**
 * Persiste URL + metadados do último envio da NF (uso único nos fluxos de upload).
 *
 * @param int|null $tamanhoBytes Tamanho em bytes; null para gravar NULL no banco
 */
function salvarMetadadosEnvioNotaFiscalPedido(
    PDO $pdo,
    int $pedidoId,
    string $urlNotaFiscal,
    string $nomeArquivoOriginal,
    ?int $idUsuarioEnvio,
    ?int $tamanhoBytes,
    ?string $dataEnvioMysql = null
): void {
    garantirMetadadosNotaFiscalPedido($pdo);

    if ($dataEnvioMysql === null || $dataEnvioMysql === '') {
        $dataEnvioMysql = date('Y-m-d H:i:s');
    }

    $stmt = $pdo->prepare('UPDATE tbl_pedidos_compra SET
        url_nota_fiscal = ?,
        nf_nome_arquivo_original = ?,
        nf_data_envio = ?,
        nf_id_usuario_envio = ?,
        nf_tamanho_bytes = ?
        WHERE id_pedido = ?');

    $stmt->execute([
        $urlNotaFiscal,
        $nomeArquivoOriginal,
        $dataEnvioMysql,
        $idUsuarioEnvio,
        $tamanhoBytes,
        $pedidoId,
    ]);
}
