<?php
/**
 * Filiais que o usuário logado pode acessar.
 *
 * Regras (alinhadas ao seletor do dashboard em backend/api/filiais.php):
 * - Administrador: todas as clínicas ativas
 * - Usuário com id_filial definido: somente essa clínica
 * - Usuário sem filial vinculada: todas as clínicas ativas (perfil corporativo/compras)
 */

function obterFiliaisPermitidasUsuario(?PDO $pdo = null): array
{
    if (!isLoggedIn()) {
        return [];
    }

    if ($pdo === null) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }

    $sqlBase = "
        SELECT id_filial, codigo_filial, nome_filial
        FROM tbl_filiais
        WHERE filial_ativa = 1
    ";

    if (isAdmin()) {
        $stmt = $pdo->query($sqlBase . ' ORDER BY nome_filial');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    $filialUsuarioId = (int) (getCurrentUserFilialId() ?? 0);
    if ($filialUsuarioId > 0) {
        $stmt = $pdo->prepare($sqlBase . ' AND id_filial = ? ORDER BY nome_filial');
        $stmt->execute([$filialUsuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    $stmt = $pdo->query($sqlBase . ' ORDER BY nome_filial');
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function usuarioPodeAcessarFilial(int $idFilial, ?PDO $pdo = null): bool
{
    if ($idFilial <= 0) {
        return false;
    }
    foreach (obterFiliaisPermitidasUsuario($pdo) as $filial) {
        if ((int) ($filial['id_filial'] ?? 0) === $idFilial) {
            return true;
        }
    }
    return false;
}

function exigirFilialPermitida(int $idFilial, ?PDO $pdo = null): void
{
    if (!usuarioPodeAcessarFilial($idFilial, $pdo)) {
        throw new Exception('Você não tem permissão para operar nesta clínica.');
    }
}
