<?php
/**
 * Filiais que o usuário logado pode acessar.
 *
 * Regras (alinhadas ao seletor do dashboard em backend/api/filiais.php):
 * - Administrador: todas as clínicas ativas
 * - Usuário com vínculos em tbl_usuario_filiais: somente essas clínicas
 * - Usuário com id_filial definido (legado): somente essa clínica
 * - Usuário sem filial vinculada: todas as clínicas ativas (perfil corporativo/compras)
 */

require_once __DIR__ . '/usuario_filiais.php';

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

    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $filiaisVinculadas = $usuarioId > 0 ? obterIdsFiliaisDoUsuario($usuarioId, $pdo) : [];

    if (!empty($filiaisVinculadas)) {
        $placeholders = implode(',', array_fill(0, count($filiaisVinculadas), '?'));
        $stmt = $pdo->prepare($sqlBase . " AND id_filial IN ($placeholders) ORDER BY nome_filial");
        $stmt->execute($filiaisVinculadas);
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

/**
 * Filial efetiva para consultas do dashboard (index).
 * Prioriza filial_id da URL; valida contra as unidades permitidas ao usuário.
 */
function resolverFilialIdRequisicao(?PDO $pdo = null): ?int
{
    if (!isLoggedIn()) {
        return null;
    }

    if ($pdo === null) {
        require_once __DIR__ . '/conexao.php';
        $pdo = Conexao::getInstance()->getPdo();
    }

    $requested = isset($_GET['filial_id']) ? (int) $_GET['filial_id'] : 0;
    if ($requested > 0) {
        exigirFilialPermitida($requested, $pdo);
        return $requested;
    }

    $sessionFilial = (int) (getCurrentUserFilialId() ?? 0);
    if ($sessionFilial > 0 && usuarioPodeAcessarFilial($sessionFilial, $pdo)) {
        return $sessionFilial;
    }

    return null;
}
