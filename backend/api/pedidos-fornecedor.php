<?php
/**
 * API para Fornecedores Gerenciarem Pedidos de Compra
 * Grupo Sorrisos - Sistema de Gestão de Estoque
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once __DIR__ . '/../helpers/nf_pedido_metadados.php';

/** Separador entre observação do solicitante e do fornecedor no campo `observacoes` do item */
if (!defined('OBS_ITEM_SEP_FORNECEDOR')) {
    define('OBS_ITEM_SEP_FORNECEDOR', "\n\n--- Fornecedor ---\n");
}

/**
 * @return array [solicitacao, fornecedor]
 */
function splitObservacoesItemPedido($raw) {
    $sep = OBS_ITEM_SEP_FORNECEDOR;
    $raw = $raw ?? '';
    if ($raw === '') {
        return ['', ''];
    }
    $pos = strpos($raw, $sep);
    if ($pos === false) {
        return [trim($raw), ''];
    }
    return [trim(substr($raw, 0, $pos)), trim(substr($raw, $pos + strlen($sep)))];
}

function mergeObservacoesItemPedido($buyerPart, $supplierPart) {
    $sep = OBS_ITEM_SEP_FORNECEDOR;
    $b = trim((string)($buyerPart ?? ''));
    $s = trim((string)($supplierPart ?? ''));
    if ($s === '') {
        return $b;
    }
    if ($b === '') {
        return $sep . $s;
    }
    return $b . $sep . $s;
}

function garantirColunasPedidoFornecedor(PDO $pdo): void {
    $stmtCols = $pdo->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'tbl_pedidos_compra'
    ");
    $stmtCols->execute();
    $colunas = array_map('strtolower', $stmtCols->fetchAll(PDO::FETCH_COLUMN));

    if (!in_array('observacoes_fornecedor', $colunas, true)) {
        $pdo->exec("ALTER TABLE tbl_pedidos_compra ADD COLUMN observacoes_fornecedor TEXT NULL AFTER observacoes");
    }
    if (!in_array('condicoes_pagamento', $colunas, true)) {
        $pdo->exec("ALTER TABLE tbl_pedidos_compra ADD COLUMN condicoes_pagamento VARCHAR(255) NULL AFTER data_entrega_prevista");
    }

    $stmtItens = $pdo->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'tbl_itens_pedido_compra'
    ");
    $stmtItens->execute();
    $colunasItens = array_map('strtolower', $stmtItens->fetchAll(PDO::FETCH_COLUMN));

    if (!in_array('novo_pos_resposta', $colunasItens, true)) {
        $pdo->exec("ALTER TABLE tbl_itens_pedido_compra ADD COLUMN novo_pos_resposta TINYINT(1) NOT NULL DEFAULT 0 AFTER observacoes");
    }
    if (!in_array('data_resposta_novo_item', $colunasItens, true)) {
        $pdo->exec("ALTER TABLE tbl_itens_pedido_compra ADD COLUMN data_resposta_novo_item DATETIME NULL AFTER novo_pos_resposta");
    }

    garantirMetadadosNotaFiscalPedido($pdo);
}

function obterColunasItensPedido(PDO $pdo): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = $pdo->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'tbl_itens_pedido_compra'
    ");
    $stmt->execute();
    $cache = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
    return $cache;
}

// Verificar se é uma requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit();
}

// Verificar se é fornecedor
if ($_SESSION['usuario_perfil'] !== 'Fornecedor') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado - Apenas fornecedores']);
    exit();
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    garantirColunasPedidoFornecedor($pdo);

    $stmt = $pdo->prepare("
        SELECT u.id_usuario, u.id_perfil, u.id_fornecedor, f.razao_social
        FROM tbl_usuarios u
        LEFT JOIN tbl_fornecedores f ON u.id_fornecedor = f.id_fornecedor
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if (!$usuario || !$usuario['id_fornecedor']) {
        http_response_code(403);
        echo json_encode(['error' => 'Usuário não é fornecedor válido ou não está vinculado a um fornecedor']);
        exit();
    }

    $fornecedor_id = (int)$usuario['id_fornecedor'];

    // Multipart (aprovação + NF opcional) — não usar JSON body
    if (isset($_POST['action']) && $_POST['action'] === 'aprovar_faturamento') {
        require_once __DIR__ . '/../../models/PedidoCompra.php';
        require_once __DIR__ . '/../utils/EmailUtils.php';
        $resultado = aprovarFaturamentoFornecedorMultipart($pdo, $fornecedor_id, $usuario);
        if (empty($resultado['success'])) {
            http_response_code(400);
        }
        echo json_encode($resultado);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = [];
    }
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'listar_pedidos':
            $pedidos = listarPedidosFornecedor($pdo, $fornecedor_id);
            echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            break;
            
        case 'responder_pedido':
            $resultado = responderPedido($pdo, $input, $fornecedor_id);
            echo json_encode($resultado);
            break;
            
        case 'obter_detalhes_pedido':
            $pedido_id = $input['pedido_id'] ?? 0;
            $detalhes = obterDetalhesPedido($pdo, $pedido_id, $fornecedor_id);
            echo json_encode(['success' => true, 'pedido' => $detalhes]);
            break;
            
        case 'get_pedido':
            $pedido_id = $input['pedido_id'] ?? 0;
            $stmt = $pdo->prepare("
                SELECT p.id_pedido, p.url_nota_fiscal, p.status,
                       p.nf_nome_arquivo_original, p.nf_data_envio, p.nf_id_usuario_envio,
                       p.nf_tamanho_bytes, un.nome_completo AS nf_usuario_nome
                FROM tbl_pedidos_compra p
                LEFT JOIN tbl_usuarios un ON un.id_usuario = p.nf_id_usuario_envio
                WHERE p.id_pedido = ? AND p.id_fornecedor = ?
            ");
            $stmt->execute([$pedido_id, $fornecedor_id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($pedido) {
                echo json_encode(['success' => true, 'pedido' => $pedido]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Pedido não encontrado']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não reconhecida']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

/**
 * Lista pedidos para um fornecedor específico
 */
function listarPedidosFornecedor($pdo, $fornecedor_id) {
    try {
        // Listar TODOS os pedidos do fornecedor para permitir comunicação via chat
        // O chat deve estar disponível independente do status do pedido
        $sql = "SELECT 
                    p.id_pedido,
                    p.numero_pedido,
                    p.data_criacao as data_pedido,
                    p.data_solicitacao as data_pedido_alt,
                    p.status,
                    p.valor_total,
                    p.observacoes,
                    p.observacoes_fornecedor,
                    p.url_nota_fiscal,
                    p.nf_nome_arquivo_original,
                    p.nf_data_envio,
                    p.nf_id_usuario_envio,
                    p.nf_tamanho_bytes,
                    un.nome_completo AS nf_usuario_nome,
                    COALESCE(NULLIF(f.razao_social, ''), f.nome_filial) as cliente,
                    f.cnpj as cliente_cnpj,
                    u.nome_completo as solicitante,
                    COUNT(DISTINCT pi.id_item) as total_itens
                FROM tbl_pedidos_compra p
                LEFT JOIN tbl_filiais f ON p.id_filial = f.id_filial
                LEFT JOIN tbl_usuarios u ON p.id_usuario_solicitante = u.id_usuario
                LEFT JOIN tbl_usuarios un ON un.id_usuario = p.nf_id_usuario_envio
                LEFT JOIN tbl_itens_pedido_compra pi ON p.id_pedido = pi.id_pedido 
                    AND (pi.ativo = 1 OR pi.ativo IS NULL)
                WHERE p.id_fornecedor = ? 
                AND (p.ativo = 1 OR p.ativo IS NULL)
                GROUP BY p.id_pedido, p.numero_pedido, p.data_criacao, p.data_solicitacao, 
                         p.status, p.valor_total, p.observacoes, p.observacoes_fornecedor,
                         p.url_nota_fiscal, p.nf_nome_arquivo_original, p.nf_data_envio,
                         p.nf_id_usuario_envio, p.nf_tamanho_bytes,
                         f.razao_social, f.nome_filial, f.cnpj, u.nome_completo, un.nome_completo
                ORDER BY COALESCE(p.data_criacao, p.data_solicitacao) DESC";
        
        error_log("Buscando pedidos para fornecedor ID: {$fornecedor_id}");
        error_log("SQL: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fornecedor_id]);
    
        $pedidos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obter itens do pedido
            $itens = obterItensPedido($pdo, $row['id_pedido']);
            $cliente = trim((string)($row['cliente'] ?: 'N/A'));
            $clienteCnpj = trim((string)($row['cliente_cnpj'] ?? ''));
            $clienteComCnpj = $clienteCnpj !== '' && $cliente !== 'N/A'
                ? "{$cliente} - {$clienteCnpj}"
                : $cliente;
            
            $pedidos[] = [
                'id' => $row['id_pedido'],
                'numero' => $row['numero_pedido'] ?: 'N/A',
                'data' => $row['data_pedido'] ?: $row['data_pedido_alt'] ?: date('Y-m-d'),
                'status' => $row['status'] ?: 'pendente',
                'cliente' => $clienteComCnpj,
                'cliente_cnpj' => $clienteCnpj,
                'solicitante' => $row['solicitante'] ?: 'N/A',
                'valor_total' => floatval($row['valor_total'] ?: 0),
                'observacoes' => $row['observacoes'] ?: '',
                'observacoes_fornecedor' => $row['observacoes_fornecedor'] ?: '',
                'total_itens' => intval($row['total_itens'] ?: 0),
                'url_nota_fiscal' => $row['url_nota_fiscal'] ?? '',
                'nf_nome_arquivo_original' => $row['nf_nome_arquivo_original'] ?? '',
                'nf_data_envio' => $row['nf_data_envio'] ?? '',
                'nf_id_usuario_envio' => isset($row['nf_id_usuario_envio']) ? (int)$row['nf_id_usuario_envio'] : null,
                'nf_tamanho_bytes' => isset($row['nf_tamanho_bytes']) ? (int)$row['nf_tamanho_bytes'] : null,
                'nf_usuario_nome' => $row['nf_usuario_nome'] ?? '',
                'itens' => $itens
            ];
        }
        
        // Log para debug
        error_log("Pedidos encontrados para fornecedor {$fornecedor_id}: " . count($pedidos));
        
        // Retornar pedidos do banco de dados (mesmo que vazio)
        return $pedidos;
        
    } catch (Exception $e) {
        error_log("Erro ao listar pedidos do fornecedor {$fornecedor_id}: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Retornar array vazio em caso de erro
        return [];
    }
}

/**
 * Obtém itens de um pedido específico
 */
function obterItensPedido($pdo, $pedido_id) {
    $colunasItens = obterColunasItensPedido($pdo);
    $selectNovoPosResposta = in_array('novo_pos_resposta', $colunasItens, true)
        ? "pi.novo_pos_resposta"
        : "0 as novo_pos_resposta";
    $selectDisponivel = in_array('disponivel', $colunasItens, true)
        ? "pi.disponivel"
        : "NULL as disponivel";
    $selectQtdDisp = in_array('quantidade_disponivel', $colunasItens, true)
        ? "pi.quantidade_disponivel"
        : "NULL as quantidade_disponivel";

    // Buscar itens na tabela tbl_itens_pedido_compra
    $sql = "SELECT 
                pi.id_item,
                pi.quantidade,
                pi.preco_unitario,
                pi.preco_fornecedor,
                pi.observacoes,
                {$selectNovoPosResposta},
                {$selectDisponivel},
                {$selectQtdDisp},
                pi.unidade_medida,
                cm.nome as nome_material,
                cm.codigo as codigo_material,
                c.nome_categoria,
                um.sigla as unidade_medida_sigla
            FROM tbl_itens_pedido_compra pi
            LEFT JOIN tbl_catalogo_materiais cm ON pi.id_catalogo = cm.id_catalogo
            LEFT JOIN tbl_categorias c ON cm.id_categoria = c.id_categoria
            LEFT JOIN tbl_unidades_medida um ON cm.id_unidade = um.id_unidade
            WHERE pi.id_pedido = ? AND (pi.ativo = 1 OR pi.ativo IS NULL)
            ORDER BY pi.id_item";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id]);
    
    $itens = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rawObs = $row['observacoes'] ?? '';
        list($obsSolic, $obsForn) = splitObservacoesItemPedido($rawObs);
        $precoFornecedorVal = $row['preco_fornecedor'];
        $precoFornecedorJson = ($precoFornecedorVal !== null && $precoFornecedorVal !== '')
            ? floatval($precoFornecedorVal)
            : null;

        $dispRaw = $row['disponivel'] ?? null;
        $disponivelJson = ($dispRaw !== null && $dispRaw !== '')
            ? intval($dispRaw)
            : null;

        $qtdDispRaw = $row['quantidade_disponivel'] ?? null;
        $quantidadeDisponivelJson = ($qtdDispRaw !== null && $qtdDispRaw !== '')
            ? floatval($qtdDispRaw)
            : null;

        $itens[] = [
            'id' => $row['id_item'],
            'nome' => $row['nome_material'] ?: 'Material não encontrado',
            'codigo' => $row['codigo_material'] ?: 'N/A',
            'quantidade' => floatval($row['quantidade'] ?: 0),
            'unidade' => $row['unidade_medida_sigla'] ?: ($row['unidade_medida'] ?: 'un'),
            'preco_unitario' => floatval($row['preco_unitario'] ?: 0),
            'preco_fornecedor' => $precoFornecedorJson,
            'disponivel' => $disponivelJson,
            'quantidade_disponivel' => $quantidadeDisponivelJson,
            'categoria' => $row['nome_categoria'] ?: 'Sem categoria',
            'observacoes' => $rawObs,
            'observacoes_solicitacao' => $obsSolic,
            'observacoes_item_fornecedor' => $obsForn,
            'novo_pos_resposta' => intval($row['novo_pos_resposta'] ?? 0)
        ];
    }
    
    // Log para debug
    error_log("Itens encontrados para pedido ID {$pedido_id}: " . count($itens));
    
    return $itens;
}

/**
 * Responde a um pedido com preços e condições
 */
function responderPedido($pdo, $input, $fornecedor_id) {
    $pedido_id = $input['pedido_id'] ?? 0;
    $observacoes = $input['observacoes'] ?? '';
    $prazo_entrega = $input['prazo_entrega'] ?? '';
    $data_entrega_prevista = $input['data_entrega_prevista'] ?? '';
    $condicoes_pagamento = $input['condicoes_pagamento'] ?? '';
    $itens = $input['itens'] ?? [];
    
    // Validar dados obrigatórios
    if (!$pedido_id) {
        return ['success' => false, 'error' => 'ID do pedido é obrigatório'];
    }
    
    // Se não foi enviada data específica, usar o prazo em dias para calcular
    if (empty($data_entrega_prevista) && !empty($prazo_entrega)) {
        // Verificar se prazo_entrega é um número (dias)
        if (is_numeric($prazo_entrega)) {
            $data_entrega_prevista = date('Y-m-d', strtotime("+{$prazo_entrega} days"));
        } else {
            // Se for uma data, usar diretamente
            $data_entrega_prevista = $prazo_entrega;
        }
    }
    
    // Verificar se o pedido pertence ao fornecedor
    $sql = "SELECT id_pedido, status FROM tbl_pedidos_compra 
            WHERE id_pedido = ? AND id_fornecedor = ? AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id, $fornecedor_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        return ['success' => false, 'error' => 'Pedido não encontrado ou não pertence ao fornecedor'];
    }
    
    // Após enviar para faturamento, o fornecedor apenas acompanha o pedido.
    $statusPermitidos = ['em_analise', 'pendente', 'aprovado_cotacao'];
    if (!in_array(strtolower($pedido['status'] ?? ''), $statusPermitidos)) {
        return ['success' => false, 'error' => 'Pedido não está disponível para resposta. Status atual: ' . $pedido['status']];
    }
    
    try {
        $pdo->beginTransaction();

        // Após resposta: manter em aprovado_cotacao quando compras já liberou essa etapa,
        // para o fornecedor seguir com "Aprovar Faturamento"; caso contrário volta para pendente (fluxo anterior).
        $statusAposResposta = 'pendente';
        if (strtolower((string)($pedido['status'] ?? '')) === 'aprovado_cotacao') {
            $statusAposResposta = 'aprovado_cotacao';
        }

        $sql = "UPDATE tbl_pedidos_compra 
                SET status = ?, 
                    data_atualizacao = NOW()
                WHERE id_pedido = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statusAposResposta, $pedido_id]);
        
        // Atualizar campos específicos do fornecedor (campos separados)
        try {
            $sql_campos = "UPDATE tbl_pedidos_compra
                           SET observacoes_fornecedor = ?,
                               data_entrega_prevista = ?,
                               condicoes_pagamento = ?
                           WHERE id_pedido = ?";
            $stmt_campos = $pdo->prepare($sql_campos);
            $stmt_campos->execute([
                $observacoes,
                $prazo_entrega,
                $condicoes_pagamento,
                $pedido_id
            ]);
            
            error_log("Pedido {$pedido_id}: observacao do fornecedor processada com sucesso.");
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar campos do fornecedor no pedido {$pedido_id}: " . $e->getMessage());
        }
        
        // Atualizar preços dos itens na tabela tbl_itens_pedido_compra
        foreach ($itens as $item) {
            $item_id = $item['item_id'];
            $preco = $item['preco'];
            $disponivel_raw = $item['disponivel'] ?? 'nao';
            $quantidade_disponivel = floatval($item['quantidade_disponivel'] ?? 0);
            $obsItemFornecedor = isset($item['observacoes_item']) ? trim((string)$item['observacoes_item']) : '';
            
            // Converter 'sim'/'nao' para 1/0 (TINYINT)
            // Aceita: 'sim', true, 1, '1' -> converte para 1
            // Aceita: 'nao', false, 0, '0', null -> converte para 0
            $disponivel = ($disponivel_raw === 'sim' || $disponivel_raw === true || $disponivel_raw === 1 || $disponivel_raw === '1') ? 1 : 0;
            
            // Se não disponível, garantir que quantidade seja 0
            if ($disponivel == 0) {
                $quantidade_disponivel = 0;
            }
            
            try {
                $stmtObsAtual = $pdo->prepare("SELECT observacoes FROM tbl_itens_pedido_compra WHERE id_item = ? AND id_pedido = ? LIMIT 1");
                $stmtObsAtual->execute([$item_id, $pedido_id]);
                $rowObs = $stmtObsAtual->fetch(PDO::FETCH_ASSOC);
                $obsAtual = $rowObs['observacoes'] ?? '';
                list($buyerPart, $_oldForn) = splitObservacoesItemPedido($obsAtual);
                $novasObservacoes = mergeObservacoesItemPedido($buyerPart, $obsItemFornecedor);

                // Atualizar campos do item
                // quantidade: mantém a quantidade solicitada no pedido (não altera)
                // quantidade_disponivel: quantidade que o fornecedor informou que tem disponível
                // disponivel: indica se o item está disponível (1=sim, 0=não)
                // preco_fornecedor: preço oferecido pelo fornecedor
                $sql = "UPDATE tbl_itens_pedido_compra 
                        SET preco_fornecedor = ?,
                            disponivel = ?,
                            quantidade_disponivel = ?,
                            observacoes = ?,
                            novo_pos_resposta = 0,
                            data_resposta_novo_item = NOW(),
                            data_atualizacao = NOW()
                        WHERE id_item = ? AND id_pedido = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$preco, $disponivel, $quantidade_disponivel, $novasObservacoes, $item_id, $pedido_id]);
                
                // Verificar se a atualização foi bem-sucedida
                if ($stmt->rowCount() > 0) {
                    error_log("✅ Item {$item_id} atualizado com sucesso: disponivel={$disponivel} (valor original: '{$disponivel_raw}'), quantidade_disponivel={$quantidade_disponivel}, preco={$preco}");
                } else {
                    error_log("⚠️ Nenhuma linha foi atualizada para o item {$item_id} (id_item={$item_id}, id_pedido={$pedido_id})");
                }
            } catch (Exception $e) {
                // Log do erro para debug
                error_log("❌ Erro ao atualizar item {$item_id} do pedido {$pedido_id}: " . $e->getMessage());
                error_log("Valores: disponivel_raw='{$disponivel_raw}', disponivel={$disponivel}, quantidade_disponivel={$quantidade_disponivel}, preco={$preco}");
                // Continuar com o próximo item
            }
        }

        // Recalcular valor total do pedido com base na resposta do fornecedor
        // Regras:
        // - item indisponível (disponivel = 0) não soma
        // - item disponível usa quantidade_disponivel; se ausente, usa quantidade solicitada
        // - preço usa preco_fornecedor; se ausente, usa preco_unitario original
        $sql_total = "SELECT COALESCE(SUM(
                        CASE 
                            WHEN COALESCE(disponivel, 1) = 0 THEN 0
                            ELSE COALESCE(NULLIF(quantidade_disponivel, 0), quantidade, 0) 
                                 * COALESCE(NULLIF(preco_fornecedor, 0), preco_unitario, 0)
                        END
                    ), 0) AS novo_total
                    FROM tbl_itens_pedido_compra
                    WHERE id_pedido = ?";
        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->execute([$pedido_id]);
        $novo_total = floatval($stmt_total->fetchColumn() ?: 0);

        $sql_update_total = "UPDATE tbl_pedidos_compra 
                             SET valor_total = ?,
                                 data_atualizacao = NOW()
                             WHERE id_pedido = ?";
        $stmt_update_total = $pdo->prepare($sql_update_total);
        $stmt_update_total->execute([$novo_total, $pedido_id]);
        error_log("Pedido {$pedido_id}: valor_total recalculado para {$novo_total}");
        
        // Registrar log da resposta
        // Usar o ID do usuário logado (da sessão), não o ID do fornecedor
        $sql = "INSERT INTO tbl_logs_sistema (id_usuario, acao, tabela, dados_novos, ip_usuario) 
                VALUES (?, ?, ?, ?, ?)";
        
        $dados_log = [
            'pedido_id' => $pedido_id,
            'fornecedor_id' => $fornecedor_id,
            'observacoes' => $observacoes,
            'prazo_entrega' => $prazo_entrega,
            'data_entrega_prevista' => $data_entrega_prevista,
            'condicoes_pagamento' => $condicoes_pagamento,
            'itens_respondidos' => count($itens)
        ];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['usuario_id'], // ID do usuário logado (da sessão)
            'RESPOSTA_PEDIDO',
            'tbl_pedidos_compra',
            json_encode($dados_log),
            $_SERVER['REMOTE_ADDR'] ?? 'N/A'
        ]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Resposta enviada com sucesso'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Obtém detalhes completos de um pedido
 */
function obterDetalhesPedido($pdo, $pedido_id, $fornecedor_id) {
    $sql = "SELECT 
                p.*,
                COALESCE(NULLIF(f.razao_social, ''), f.nome_filial) as nome_filial,
                f.cnpj as cnpj_filial,
                f.endereco as endereco_filial,
                f.telefone as telefone_filial,
                u.nome_completo as solicitante,
                u.email as email_solicitante,
                u_nf.nome_completo AS nf_usuario_nome
            FROM tbl_pedidos_compra p
            LEFT JOIN tbl_filiais f ON p.id_filial = f.id_filial
            LEFT JOIN tbl_usuarios u ON p.id_usuario_solicitante = u.id_usuario
            LEFT JOIN tbl_usuarios u_nf ON u_nf.id_usuario = p.nf_id_usuario_envio
            WHERE p.id_pedido = ? AND p.id_fornecedor = ? AND (p.ativo = 1 OR p.ativo IS NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id, $fornecedor_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        return null;
    }

    $cnpjFilial = trim((string)($pedido['cnpj_filial'] ?? ''));
    if ($cnpjFilial !== '' && !empty($pedido['nome_filial'])) {
        $pedido['nome_filial'] = trim($pedido['nome_filial']) . ' - ' . $cnpjFilial;
    }
    
    // Obter itens
    $pedido['itens'] = obterItensPedido($pdo, $pedido_id);
    
    return $pedido;
}

/**
 * E-mails do solicitante + usuários com acesso à tela de pedidos de compra (mesma filial).
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

/**
 * Fornecedor aprova faturamento: opcional NF, status → em_transito, e-mail ao setor de compras.
 *
 * @param array{id_usuario:int, razao_social?:string|null} $usuarioRow
 * @return array{success: bool, message?: string, error?: string, email_enviado?: bool}
 */
function aprovarFaturamentoFornecedorMultipart(PDO $pdo, int $fornecedor_id, array $usuarioRow): array {
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $detalhes = trim((string)($_POST['detalhes_aprovacao'] ?? ''));

    if ($pedidoId <= 0) {
        return ['success' => false, 'error' => 'Pedido inválido'];
    }
    if ($detalhes === '') {
        return ['success' => false, 'error' => 'Informe os detalhes da aprovação do faturamento.'];
    }

    $stmt = $pdo->prepare("
        SELECT p.*,
               fn.razao_social AS nome_fornecedor,
               COALESCE(fil.nome_filial, fil.razao_social, '') AS nome_filial
        FROM tbl_pedidos_compra p
        LEFT JOIN tbl_fornecedores fn ON fn.id_fornecedor = p.id_fornecedor
        LEFT JOIN tbl_filiais fil ON fil.id_filial = p.id_filial
        WHERE p.id_pedido = ? AND p.id_fornecedor = ?
          AND (p.ativo = 1 OR p.ativo IS NULL)
    ");
    $stmt->execute([$pedidoId, $fornecedor_id]);
    $pedidoRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedidoRow) {
        return ['success' => false, 'error' => 'Pedido não encontrado ou não pertence ao fornecedor'];
    }

    $statusAtual = strtolower((string)($pedidoRow['status'] ?? ''));
    $statusPermitemAprovacaoFat = ['aprovado_cotacao', 'aprovado_para_faturar'];
    if (!in_array($statusAtual, $statusPermitemAprovacaoFat, true)) {
        return [
            'success' => false,
            'error' => 'Este pedido não está disponível para aprovação de faturamento (requer Cotação aprovada ou Aprovado para faturar). Status atual: ' . ($pedidoRow['status'] ?? ''),
        ];
    }

    $urlNf = null;

    if (!empty($_FILES['nota_fiscal']['tmp_name']) && (int)($_FILES['nota_fiscal']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $helperPath = __DIR__ . '/../../helpers/S3Uploader.php';
        if (!is_readable($helperPath)) {
            return ['success' => false, 'error' => 'Upload indisponível (configuração do sistema).'];
        }
        require_once $helperPath;

        $file = $_FILES['nota_fiscal'];
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        if (!S3Uploader::validateFileType($file['name'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo de arquivo não permitido para a NF.'];
        }
        if (!S3Uploader::validateFileSize($file['size'], 10)) {
            return ['success' => false, 'error' => 'Arquivo da NF muito grande (máx. 10MB).'];
        }

        $uploader = new S3Uploader();
        $up = $uploader->uploadFile(
            $file['tmp_name'],
            $file['name'],
            'notas-fiscais/pedido-' . $pedidoId
        );
        if (empty($up['success'])) {
            return ['success' => false, 'error' => $up['error'] ?? 'Erro ao enviar a Nota Fiscal'];
        }
        $urlNf = $up['url'] ?? null;

        if (!empty($pedidoRow['url_nota_fiscal'])) {
            $oldPath = ltrim((string)$pedidoRow['url_nota_fiscal'], '/');
            $oldFullPath = __DIR__ . '/../../' . $oldPath;
            if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

        $bytes = isset($up['size_bytes']) ? (int) $up['size_bytes'] : (int) $file['size'];
        if ($bytes <= 0 && !empty($up['path']) && is_readable($up['path'])) {
            $sz = @filesize($up['path']);
            if ($sz !== false) {
                $bytes = (int) $sz;
            }
        }

        salvarMetadadosEnvioNotaFiscalPedido(
            $pdo,
            $pedidoId,
            (string) $urlNf,
            $file['name'],
            obterIdUsuarioSessaoParaMetadadosNf(),
            $bytes > 0 ? $bytes : null
        );
    }

    $nomeFornecedor = $pedidoRow['nome_fornecedor'] ?? ($usuarioRow['razao_social'] ?? 'Fornecedor');
    $obsHist = "[Fornecedor] Aprovação de faturamento — pedido em trânsito.\n\n" . $detalhes;
    if ($urlNf) {
        $obsHist .= "\n\nNota fiscal anexada: " . $urlNf;
    }

    try {
        $pedidoModel = new PedidoCompra();
        $pedidoModel->atualizarStatus($pedidoId, 'em_transito', $obsHist);
    } catch (Throwable $e) {
        error_log('aprovarFaturamentoFornecedorMultipart atualizarStatus: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }

    $obsFornAtual = trim((string)($pedidoRow['observacoes_fornecedor'] ?? ''));
    $bloco = "\n\n[" . date('d/m/Y H:i') . "] Aprovação de faturamento (Em trânsito):\n" . $detalhes;
    if ($urlNf) {
        $bloco .= "\nNF anexada.";
    }
    $novaObsForn = trim($obsFornAtual . $bloco);
    try {
        $stObs = $pdo->prepare('UPDATE tbl_pedidos_compra SET observacoes_fornecedor = ?, data_atualizacao = NOW() WHERE id_pedido = ?');
        $stObs->execute([$novaObsForn, $pedidoId]);
    } catch (Throwable $e) {
        error_log('aprovarFaturamentoFornecedorMultipart observacoes_fornecedor: ' . $e->getMessage());
    }

    $destinatarios = coletarEmailsNotificacaoCompras(
        $pdo,
        (int)($pedidoRow['id_filial'] ?? 0),
        (int)($pedidoRow['id_usuario_solicitante'] ?? 0)
    );

    $emailEnviado = EmailUtils::enviarEmailComprasPedidoEmTransito([
        'numero_pedido' => $pedidoRow['numero_pedido'] ?? ('#' . $pedidoId),
        'id_pedido' => $pedidoId,
        'nome_filial' => $pedidoRow['nome_filial'] ?? '',
        'nome_fornecedor' => $nomeFornecedor,
        'detalhes' => $detalhes,
        'url_nota_fiscal' => $urlNf,
        'destinatarios' => $destinatarios,
    ]);

    return [
        'success' => true,
        'message' => 'Faturamento aprovado. O pedido foi atualizado para Em trânsito.',
        'email_enviado' => $emailEnviado,
    ];
}
?>