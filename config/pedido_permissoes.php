<?php
/**
 * Permissões de pedidos de compra por perfil e situação (status).
 *
 * Sem registros em tbl_perfil_pedido_status para o perfil = acesso total (legado).
 * Com registros = aplica somente o que estiver configurado.
 */

require_once __DIR__ . '/session.php';

function pedidoPermissoesCarregarFluxo(): void
{
    static $loaded = false;
    if (!$loaded) {
        require_once __DIR__ . '/../backend/helpers/fluxo_pedido_compra.php';
        $loaded = true;
    }
}

function pedidoPermissoesTabelaExiste(?PDO $pdo = null): bool
{
    try {
        if (!$pdo) {
            require_once __DIR__ . '/conexao.php';
            $pdo = Conexao::getInstance()->getPdo();
        }
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_perfil_pedido_status'");
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function pedidoPermissoesStatusConfiguraveis(): array
{
    pedidoPermissoesCarregarFluxo();
    $etapas = fluxoPedidoEtapasOrdenadas();
    $etapas[] = ['key' => 'cancelado', 'nome' => 'Cancelado', 'icon' => '—', 'passo' => 0];
    return $etapas;
}

/** Presets para preenchimento rápido na tela de perfil */
function pedidoPermissoesPresets(): array
{
    $todosVer = static function (array $keys): array {
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0];
        }
        return $out;
    };

    return [
        'auxiliar' => [
            'label' => 'Auxiliar / ASB',
            'permissoes' => array_merge(
                $todosVer(['aguardando_cotacao']),
                [
                    'aguardando_cotacao' => [
                        'pode_ver' => 1, 'pode_editar' => 1, 'pode_avancar' => 1, 'pode_cancelar' => 1, 'pode_voltar' => 0,
                    ],
                ]
            ),
        ],
        'compras' => [
            'label' => 'Compras',
            'permissoes' => [
                'aguardando_cotacao' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0],
                'em_cotacao' => ['pode_ver' => 1, 'pode_editar' => 1, 'pode_avancar' => 1, 'pode_cancelar' => 1, 'pode_voltar' => 1],
                'aguardando_aprovacao_socio' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 1],
                'aprovado_socio' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0],
                'aguardando_faturamento' => ['pode_ver' => 1, 'pode_editar' => 1, 'pode_avancar' => 1, 'pode_cancelar' => 1, 'pode_voltar' => 1],
                'em_faturamento' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 1, 'pode_cancelar' => 1, 'pode_voltar' => 1],
                'em_transito' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 1, 'pode_cancelar' => 0, 'pode_voltar' => 1],
                'em_conferencia' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0],
                'finalizado' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0],
                'cancelado' => ['pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 0, 'pode_cancelar' => 0, 'pode_voltar' => 0],
            ],
        ],
        'socio' => [
            'label' => 'Sócio',
            'permissoes' => array_merge(
                $todosVer(['aguardando_aprovacao_socio', 'aprovado_socio', 'finalizado', 'cancelado']),
                [
                    'aguardando_aprovacao_socio' => [
                        'pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 1, 'pode_cancelar' => 0, 'pode_voltar' => 1,
                    ],
                ]
            ),
        ],
        'recebimento' => [
            'label' => 'Recebimento / Conferência',
            'permissoes' => array_merge(
                $todosVer(['em_transito', 'em_conferencia', 'finalizado']),
                [
                    'em_conferencia' => [
                        'pode_ver' => 1, 'pode_editar' => 0, 'pode_avancar' => 1, 'pode_cancelar' => 0, 'pode_voltar' => 1,
                    ],
                ]
            ),
        ],
    ];
}

function pedidoPermissoesObterPerfilIdUsuario(?PDO $pdo = null): int
{
    if (!isLoggedIn()) {
        return 0;
    }
    return (int) ($_SESSION['usuario_perfil_id'] ?? 0);
}

function pedidoPermissoesPerfilTemConfiguracao(int $idPerfil, ?PDO $pdo = null): bool
{
    if ($idPerfil <= 0 || !pedidoPermissoesTabelaExiste($pdo)) {
        return false;
    }
    if (!$pdo) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tbl_perfil_pedido_status WHERE id_perfil = ?');
    $stmt->execute([$idPerfil]);
    return ((int) $stmt->fetchColumn()) > 0;
}

function pedidoPermissoesObterPorPerfil(int $idPerfil, ?PDO $pdo = null): array
{
    if ($idPerfil <= 0 || !pedidoPermissoesTabelaExiste($pdo)) {
        return [];
    }
    if (!$pdo) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }
    $stmt = $pdo->prepare('SELECT * FROM tbl_perfil_pedido_status WHERE id_perfil = ? ORDER BY status_pedido');
    $stmt->execute([$idPerfil]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $map = [];
    foreach ($rows as $row) {
        $map[$row['status_pedido']] = [
            'pode_ver' => (int) $row['pode_ver'],
            'pode_editar' => (int) $row['pode_editar'],
            'pode_avancar' => (int) $row['pode_avancar'],
            'pode_cancelar' => (int) $row['pode_cancelar'],
            'pode_voltar' => (int) $row['pode_voltar'],
        ];
    }
    return $map;
}

function pedidoPermissoesNormalizarEntrada(array $input): array
{
    $campos = ['pode_ver', 'pode_editar', 'pode_avancar', 'pode_cancelar', 'pode_voltar'];
    $out = [];
    pedidoPermissoesCarregarFluxo();
    $validos = array_column(pedidoPermissoesStatusConfiguraveis(), 'key');

    foreach ($input as $status => $flags) {
        if (!is_array($flags)) {
            continue;
        }
        pedidoPermissoesCarregarFluxo();
        $statusNorm = fluxoPedidoNormalizarStatus((string) $status);
        if (!in_array($statusNorm, $validos, true)) {
            continue;
        }
        $row = [];
        foreach ($campos as $campo) {
            $row[$campo] = !empty($flags[$campo]) ? 1 : 0;
        }
        if (array_sum($row) > 0) {
            $out[$statusNorm] = $row;
        }
    }
    return $out;
}

function pedidoPermissoesSalvarPerfil(int $idPerfil, array $permissoes, ?PDO $pdo = null): void
{
    if ($idPerfil <= 0 || !pedidoPermissoesTabelaExiste($pdo)) {
        return;
    }
    if (!$pdo) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }

    $permissoes = pedidoPermissoesNormalizarEntrada($permissoes);

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare('DELETE FROM tbl_perfil_pedido_status WHERE id_perfil = ?');
        $del->execute([$idPerfil]);

        if (!empty($permissoes)) {
            $ins = $pdo->prepare(
                'INSERT INTO tbl_perfil_pedido_status
                (id_perfil, status_pedido, pode_ver, pode_editar, pode_avancar, pode_cancelar, pode_voltar)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($permissoes as $status => $flags) {
                $ins->execute([
                    $idPerfil,
                    $status,
                    $flags['pode_ver'],
                    $flags['pode_editar'],
                    $flags['pode_avancar'],
                    $flags['pode_cancelar'],
                    $flags['pode_voltar'],
                ]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function pedidoPermissoesUsuarioBypassTotal(): bool
{
    return isAdmin();
}

function pedidoPermissoesMapaUsuario(?PDO $pdo = null): array
{
    if (pedidoPermissoesUsuarioBypassTotal()) {
        return ['__bypass' => true];
    }

    $idPerfil = pedidoPermissoesObterPerfilIdUsuario($pdo);
    if ($idPerfil <= 0) {
        return [];
    }

    if (!pedidoPermissoesPerfilTemConfiguracao($idPerfil, $pdo)) {
        return ['__legacy_full' => true];
    }

    return pedidoPermissoesObterPorPerfil($idPerfil, $pdo);
}

function pedidoPermissoesTemFlag(string $status, string $flag, ?PDO $pdo = null): bool
{
    if (pedidoPermissoesUsuarioBypassTotal()) {
        return true;
    }

    pedidoPermissoesCarregarFluxo();
    $statusNorm = fluxoPedidoNormalizarStatus($status);
    $mapa = pedidoPermissoesMapaUsuario($pdo);

    if (!empty($mapa['__legacy_full'])) {
        return true;
    }

    if (empty($mapa[$statusNorm])) {
        return false;
    }

    $flagsValidas = ['pode_ver', 'pode_editar', 'pode_avancar', 'pode_cancelar', 'pode_voltar'];
    if (!in_array($flag, $flagsValidas, true)) {
        return false;
    }

    return !empty($mapa[$statusNorm][$flag]);
}

function pedidoPermissoesStatusSqlVisiveis(?PDO $pdo = null): ?array
{
    if (pedidoPermissoesUsuarioBypassTotal()) {
        return null;
    }

    $idPerfil = pedidoPermissoesObterPerfilIdUsuario($pdo);
    if ($idPerfil <= 0 || !pedidoPermissoesPerfilTemConfiguracao($idPerfil, $pdo)) {
        return null;
    }

    pedidoPermissoesCarregarFluxo();
    $mapa = pedidoPermissoesObterPorPerfil($idPerfil, $pdo);
    $valoresSql = [];

    foreach ($mapa as $statusCanon => $flags) {
        if (empty($flags['pode_ver'])) {
            continue;
        }
        foreach (fluxoPedidoExpandirStatusCanonico($statusCanon) as $raw) {
            $valoresSql[] = $raw;
        }
    }

    return array_values(array_unique($valoresSql));
}

function fluxoPedidoExpandirStatusCanonico(string $canon): array
{
    pedidoPermissoesCarregarFluxo();
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (fluxoPedidoEnumStatusCompletos() as $raw) {
            $c = fluxoPedidoNormalizarStatus($raw);
            $cache[$c][] = $raw;
        }
        foreach (array_keys($cache) as $c) {
            if (!in_array($c, $cache[$c], true)) {
                $cache[$c][] = $c;
            }
            $cache[$c] = array_values(array_unique($cache[$c]));
        }
    }
    return $cache[$canon] ?? [$canon];
}

function pedidoPermissoesExigirVer(?string $status, ?PDO $pdo = null): void
{
    if (!pedidoPermissoesTemFlag((string) $status, 'pode_ver', $pdo)) {
        throw new Exception('Sem permissão para visualizar pedidos nesta situação.');
    }
}

function pedidoPermissoesExigirEditar(?string $status, ?PDO $pdo = null): void
{
    if (!pedidoPermissoesTemFlag((string) $status, 'pode_editar', $pdo)) {
        throw new Exception('Sem permissão para editar pedidos nesta situação.');
    }
}

function pedidoPermissoesValidarMudancaStatus(?string $statusAtual, ?string $statusNovo, ?PDO $pdo = null): void
{
    if (pedidoPermissoesUsuarioBypassTotal()) {
        return;
    }

    pedidoPermissoesCarregarFluxo();
    $atual = fluxoPedidoNormalizarStatus((string) $statusAtual);
    $novo = fluxoPedidoNormalizarStatus((string) $statusNovo);

    if ($novo === 'cancelado') {
        if (!pedidoPermissoesTemFlag($atual, 'pode_cancelar', $pdo)) {
            throw new Exception('Sem permissão para cancelar pedidos nesta situação.');
        }
        return;
    }

    $ordem = array_column(fluxoPedidoEtapasOrdenadas(), 'key');
    $idxAtual = array_search($atual, $ordem, true);
    $idxNovo = array_search($novo, $ordem, true);

    if ($idxAtual !== false && $idxNovo !== false && $idxNovo < $idxAtual) {
        if (!pedidoPermissoesTemFlag($atual, 'pode_voltar', $pdo)) {
            throw new Exception('Sem permissão para voltar o status nesta situação.');
        }
        return;
    }

    if (!pedidoPermissoesTemFlag($atual, 'pode_avancar', $pdo)) {
        throw new Exception('Sem permissão para avançar pedidos nesta situação.');
    }
}

function pedidoPermissoesParaFrontend(?PDO $pdo = null): array
{
    if (pedidoPermissoesUsuarioBypassTotal()) {
        return ['bypass' => true, 'status' => new stdClass()];
    }

    $mapa = pedidoPermissoesMapaUsuario($pdo);
    if (!empty($mapa['__legacy_full'])) {
        return ['legacy_full' => true, 'status' => new stdClass()];
    }

    unset($mapa['__legacy_full'], $mapa['__bypass']);
    return ['status' => $mapa];
}
