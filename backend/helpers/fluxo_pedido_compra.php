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
 * 7. Em Conferência          — Recebimento + conferência cega
 * 8. Finalizado              — Entrada no estoque
 */

function fluxoPedidoStatusLabels(): array {
    return [
        'aguardando_cotacao' => 'Aguardando Cotação',
        'em_cotacao' => 'Em Cotação',
        'aguardando_aprovacao_socio' => 'Aguard. Aprovação Sócio',
        'aprovado_socio' => 'Aprovado pelo Sócio',
        'aguardando_faturamento' => 'Aguard. Faturamento',
        'em_faturamento' => 'Em Faturamento',
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
        'em_transito' => 'Em Faturamento',
        'entregue' => 'Em Conferência',
        'recebido' => 'Finalizado',
        'parcialmente_recebido' => 'Em Conferência',
        'aprovado' => 'Aprovado',
        'em_producao' => 'Em Produção',
        'enviado' => 'Enviado',
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
        ['key' => 'em_conferencia', 'nome' => 'Em Conferência', 'icon' => '7', 'passo' => 7],
        ['key' => 'finalizado', 'nome' => 'Finalizado', 'icon' => '8', 'passo' => 8],
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
        'em_transito' => 'em_faturamento',
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
        'em_faturamento' => ['em_conferencia', 'aguardando_faturamento', 'cancelado'],
        'em_conferencia' => ['finalizado', 'em_faturamento', 'cancelado'],
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
