<?php
/**
 * Múltiplas filiais por usuário (tbl_usuario_filiais).
 */

function usuarioFiliaisTabelaExiste(?PDO $pdo = null): bool
{
    try {
        if (!$pdo) {
            require_once __DIR__ . '/conexao.php';
            $pdo = Conexao::getInstance()->getPdo();
        }
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_usuario_filiais'");
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function normalizarFiliaisUsuarioInput(array $input): array
{
    $ids = [];

    if (isset($input['filiais_ids']) && is_array($input['filiais_ids'])) {
        $ids = $input['filiais_ids'];
    } elseif (isset($input['id_filiais']) && is_array($input['id_filiais'])) {
        $ids = $input['id_filiais'];
    } elseif (!empty($input['id_filial'])) {
        $ids = [$input['id_filial']];
    }

    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) {
        return $id > 0;
    })));

    return $ids;
}

function obterIdsFiliaisDoUsuario(int $idUsuario, ?PDO $pdo = null): array
{
    if ($idUsuario <= 0) {
        return [];
    }

    if (!usuarioFiliaisTabelaExiste($pdo)) {
        return [];
    }

    try {
        if (!$pdo) {
            require_once __DIR__ . '/conexao.php';
            $pdo = Conexao::getInstance()->getPdo();
        }

        $stmt = $pdo->prepare('SELECT id_filial FROM tbl_usuario_filiais WHERE id_usuario = ? ORDER BY id_filial');
        $stmt->execute([$idUsuario]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return array_values(array_filter(array_map('intval', $rows), static function ($id) {
            return $id > 0;
        }));
    } catch (Throwable $e) {
        error_log('obterIdsFiliaisDoUsuario: ' . $e->getMessage());
        return [];
    }
}

function salvarFiliaisDoUsuario(int $idUsuario, array $filiaisIds, ?PDO $pdo = null): void
{
    if ($idUsuario <= 0 || !usuarioFiliaisTabelaExiste($pdo)) {
        return;
    }

    if (!$pdo) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }

    $filiaisIds = normalizarFiliaisUsuarioInput(['filiais_ids' => $filiaisIds]);

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare('DELETE FROM tbl_usuario_filiais WHERE id_usuario = ?');
        $del->execute([$idUsuario]);

        if (!empty($filiaisIds)) {
            $ins = $pdo->prepare('INSERT INTO tbl_usuario_filiais (id_usuario, id_filial) VALUES (?, ?)');
            foreach ($filiaisIds as $idFilial) {
                $ins->execute([$idUsuario, $idFilial]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
