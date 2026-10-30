<?php
/**
 * Destinatários de e-mail do setor de compras / pedidos.
 */

/**
 * Coleta e-mails do solicitante + usuários com acesso à página de pedidos de compra.
 *
 * @return array<int, array{email: string, nome: string}>
 */
function coletarEmailsNotificacaoCompras(PDO $pdo, int $idFilial, int $idSolicitante): array {
    $map = [];

    if ($idSolicitante > 0) {
        try {
            $st = $pdo->prepare("
                SELECT email, nome_completo
                FROM tbl_usuarios
                WHERE id_usuario = ? AND ativo = 1 AND email IS NOT NULL AND TRIM(email) <> ''
                LIMIT 1
            ");
            $st->execute([$idSolicitante]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            if ($u && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                $map[strtolower(trim($u['email']))] = [
                    'email' => trim($u['email']),
                    'nome' => $u['nome_completo'] ?? 'Compras',
                ];
            }
        } catch (Throwable $e) {
            error_log('coletarEmailsNotificacaoCompras solicitante: ' . $e->getMessage());
        }
    }

    try {
        $sql = "
            SELECT DISTINCT u.email, u.nome_completo
            FROM tbl_usuarios u
            INNER JOIN tbl_perfil_paginas pp ON pp.id_perfil = u.id_perfil
            INNER JOIN tbl_paginas pg ON pg.id_pagina = pp.id_pagina
            WHERE u.ativo = 1
              AND u.email IS NOT NULL AND TRIM(u.email) <> ''
              AND COALESCE(pp.ativo, 1) = 1
              AND COALESCE(pg.ativo, 1) = 1
              AND COALESCE(pp.permissao_visualizar, 0) = 1
              AND (
                  LOWER(TRIM(pg.nome_pagina)) = 'pedidos-compra.php'
                  OR LOWER(TRIM(pg.nome_pagina)) LIKE '%pedidos-compra.php%'
                  OR LOWER(pg.nome_pagina) LIKE '%pedido%compra%'
              )
              AND (? = 0 OR u.id_filial IS NULL OR u.id_filial = ?)
        ";
        $st = $pdo->prepare($sql);
        $st->execute([$idFilial, $idFilial]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $u) {
            if (!filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $map[strtolower(trim($u['email']))] = [
                'email' => trim($u['email']),
                'nome' => $u['nome_completo'] ?? 'Compras',
            ];
        }
    } catch (Throwable $e) {
        error_log('coletarEmailsNotificacaoCompras perfil/paginas: ' . $e->getMessage());
    }

    return array_values($map);
}
