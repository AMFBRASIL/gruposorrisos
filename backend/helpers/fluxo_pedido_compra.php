<?php
/**
 * Fluxo oficial de pedidos de compra (Grupo Sorrisos)
 *
 * 1. Aguardando Cotação      — Auxiliar/ASB monta o pedido
 * 2. Em Cotação              — Compras + fornecedor (preços/negociação)
 * 3. Aguard. Aprovação Sócio — Compras envia ao sócio
 * 4. Aprovado pelo Sócio     — Sócio aprova
 * 5. Aguard. Faturamento     — Compras encaminha após aprovação do sócio
 * 6. Em Faturamento          — Fornecedor / faturamento
 * 7. Em Trânsito             — Envio / transporte
 * 8. Em Conferência          — Recebimento + conferência cega
 * 9. Finalizado              — Entrada no estoque
 */

function fluxoPedidoStatusLabels(): array {
    return [
        'aguardando_cotacao' => 'Aguardando Cotação',
        'em_cotacao' => 'Em Cotação',
        'aguardando_aprovacao_socio' => 'Aguard. Aprovação Sócio',
        'aprovado_socio' => 'Aprovado pelo Sócio',
        'aguardando_faturamento' => 'Aguard. Faturamento',
        'em_faturamento' => 'Em Faturamento',
        'em_transito' => 'Em Trânsito',
        'em_conferencia' => 'Em Conferência',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
        // Legado (exibição)
        'em_analise' => 'Aguardando Cotação',
        'pendente' => 'Aguardando Cotação',
        'aguardando_aprovacao' => 'Aguardando Cotação',
        'aprovado_cotacao' => 'Em Cotação',
        'enviar_para_faturamento' => 'Aguard. Faturamento',
        'enviar_faturamento' => 'Aguard. Faturamento',
        'aprovado_para_faturar' => 'Em Faturamento',
        'faturado' => 'Em Faturamento',
        'enviado' => 'Em Trânsito',
        'entregue' => 'Em Conferência',
        'recebido' => 'Finalizado',
        'parcialmente_recebido' => 'Em Conferência',
        'aprovado' => 'Aprovado',
        'em_producao' => 'Em Produção',
    ];
}

/** Ordem das etapas no stepper horizontal */
function fluxoPedidoEtapasOrdenadas(): array {
    return [
        ['key' => 'aguardando_cotacao', 'nome' => 'Aguardando Cotação', 'icon' => '1', 'passo' => 1],
        ['key' => 'em_cotacao', 'nome' => 'Em Cotação', 'icon' => '2', 'passo' => 2],
        ['key' => 'aguardando_aprovacao_socio', 'nome' => 'Aguard. Aprovação Sócio', 'icon' => '3', 'passo' => 3],
        ['key' => 'aprovado_socio', 'nome' => 'Aprovado pelo Sócio', 'icon' => '4', 'passo' => 4],
        ['key' => 'aguardando_faturamento', 'nome' => 'Aguard. Faturamento', 'icon' => '5', 'passo' => 5],
        ['key' => 'em_faturamento', 'nome' => 'Em Faturamento', 'icon' => '6', 'passo' => 6],
        ['key' => 'em_transito', 'nome' => 'Em Trânsito', 'icon' => '7', 'passo' => 7],
        ['key' => 'em_conferencia', 'nome' => 'Em Conferência', 'icon' => '8', 'passo' => 8],
        ['key' => 'finalizado', 'nome' => 'Finalizado', 'icon' => '9', 'passo' => 9],
    ];
}

/** Normaliza status legado para o fluxo atual */
function fluxoPedidoNormalizarStatus(?string $status): string {
    if ($status === null || $status === '') {
        return 'aguardando_cotacao';
    }
    $s = strtolower(trim($status));
    $mapa = [
        'em_analise' => 'aguardando_cotacao',
        'pendente' => 'aguardando_cotacao',
        'aguardando_aprovacao' => 'aguardando_cotacao',
        'rascunho' => 'aguardando_cotacao',
        'aprovado_cotacao' => 'em_cotacao',
        'enviar_para_faturamento' => 'aguardando_faturamento',
        'enviar_faturamento' => 'aguardando_faturamento',
        'aprovado_para_faturar' => 'em_faturamento',
        'faturado' => 'em_faturamento',
        'enviado' => 'em_transito',
        'entregue' => 'em_conferencia',
        'recebido' => 'finalizado',
        'parcialmente_recebido' => 'em_conferencia',
    ];
    return $mapa[$s] ?? $s;
}

/** Transições permitidas (chave = status atual) */
function fluxoPedidoTransicoesPermitidas(): array {
    return [
        'aguardando_cotacao' => ['em_cotacao', 'cancelado'],
        'em_cotacao' => ['aguardando_aprovacao_socio', 'aguardando_cotacao', 'cancelado'],
        'aguardando_aprovacao_socio' => ['aguardando_faturamento', 'em_cotacao', 'cancelado'],
        'aprovado_socio' => ['aguardando_faturamento', 'aguardando_aprovacao_socio', 'cancelado'],
        'aguardando_faturamento' => ['em_faturamento', 'aguardando_aprovacao_socio', 'cancelado'],
        'em_faturamento' => ['em_transito', 'aguardando_faturamento', 'cancelado'],
        'em_transito' => ['em_conferencia', 'em_faturamento', 'cancelado'],
        'em_conferencia' => ['finalizado', 'em_transito', 'cancelado'],
        'finalizado' => [],
        'cancelado' => [],
    ];
}

function fluxoPedidoLabelStatus(?string $status): string {
    $norm = fluxoPedidoNormalizarStatus($status);
    $labels = fluxoPedidoStatusLabels();
    return $labels[$norm] ?? $labels[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
}

function fluxoPedidoStatusIniciaisCriacao(): string {
    return 'aguardando_cotacao';
}

function fluxoPedidoStatusFinais(): array {
    return ['finalizado', 'cancelado'];
}

function fluxoPedidoStatusComEntradaEstoque(): string {
    return 'finalizado';
}

/** Valores aceitos no ENUM de status (fluxo v2 + legado). */
function fluxoPedidoEnumStatusCompletos(): array {
    return [
        'aguardando_cotacao',
        'em_cotacao',
        'aguardando_aprovacao_socio',
        'aprovado_socio',
        'aguardando_faturamento',
        'em_faturamento',
        'em_conferencia',
        'finalizado',
        'cancelado',
        'em_analise',
        'pendente',
        'aprovado_cotacao',
        'enviar_para_faturamento',
        'enviar_faturamento',
        'aprovado_para_faturar',
        'faturado',
        'em_transito',
        'entregue',
        'recebido',
        'aguardando_aprovacao',
        'aprovado',
        'em_producao',
        'enviado',
        'parcialmente_recebido',
        'rascunho',
    ];
}

function fluxoPedidoExtrairEnumColuna(?string $columnType): array {
    if (!$columnType || stripos($columnType, 'enum') !== 0) {
        return [];
    }
    if (!preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $columnType, $matches)) {
        return [];
    }
    return $matches[1];
}

function fluxoPedidoObterEnumTabela(PDO $pdo, string $table, string $column): array {
    $stmt = $pdo->prepare("
        SELECT COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return fluxoPedidoExtrairEnumColuna($stmt->fetchColumn() ?: '');
}

function fluxoPedidoMontarSqlEnum(array $valores): string {
    $unicos = array_values(array_unique($valores));
    return implode(',', array_map(static function ($valor) {
        return "'" . str_replace("'", "''", $valor) . "'";
    }, $unicos));
}

/**
 * Garante que tbl_pedidos_compra (e histórico) aceitam os status do fluxo v2.
 * Evita SQLSTATE 1265 ao enviar para aprovação do sócio em bancos não migrados.
 */
function garantirEnumStatusPedidoCompra(PDO $pdo): void {
    static $verificado = false;
    if ($verificado) {
        return;
    }
    $verificado = true;

    $obrigatorios = fluxoPedidoEnumStatusCompletos();
    $atual = fluxoPedidoObterEnumTabela($pdo, 'tbl_pedidos_compra', 'status');
    if (empty($atual)) {
        return;
    }

    $faltando = array_diff($obrigatorios, $atual);
    if (!empty($faltando)) {
        $enumSql = fluxoPedidoMontarSqlEnum(array_merge($atual, $obrigatorios));
        $pdo->exec("ALTER TABLE tbl_pedidos_compra MODIFY COLUMN `status` ENUM({$enumSql}) NOT NULL DEFAULT 'aguardando_cotacao'");
        error_log('Enum status tbl_pedidos_compra atualizado. Novos valores: ' . implode(', ', $faltando));
    }

    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_historico_status_pedidos'");
    if (!$stmt || !$stmt->fetch()) {
        return;
    }

    $atualHist = fluxoPedidoObterEnumTabela($pdo, 'tbl_historico_status_pedidos', 'status');
    if (empty($atualHist)) {
        return;
    }

    $faltandoHist = array_diff($obrigatorios, $atualHist);
    if (!empty($faltandoHist)) {
        $enumHist = fluxoPedidoMontarSqlEnum(array_merge($atualHist, $obrigatorios));
        $pdo->exec("ALTER TABLE tbl_historico_status_pedidos MODIFY COLUMN `status` ENUM({$enumHist}) NOT NULL");
        error_log('Enum status tbl_historico_status_pedidos atualizado. Novos valores: ' . implode(', ', $faltandoHist));
    }
}
