<?php

class Ticket {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Gerar número único do ticket
     */
    public function gerarNumeroTicket() {
        $ano = date('Y');
        $mes = date('m');
        
        // Buscar último ticket do mês
        $stmt = $this->pdo->prepare("
            SELECT MAX(CAST(SUBSTRING(numero_ticket, 12) AS UNSIGNED)) as ultimo_numero
            FROM tbl_tickets 
            WHERE numero_ticket LIKE :padrao
        ");
        $padrao = "TKT{$ano}{$mes}%";
        $stmt->execute(['padrao' => $padrao]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $proximoNumero = ($resultado['ultimo_numero'] ?? 0) + 1;
        return "TKT{$ano}{$mes}" . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Criar novo ticket
     */
    public function criar($dados) {
        try {
            $numeroTicket = $this->gerarNumeroTicket();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_tickets (
                    numero_ticket, titulo, descricao, id_categoria, id_prioridade, 
                    id_status, id_usuario_solicitante, id_usuario_atribuido, id_filial
                ) VALUES (
                    :numero_ticket, :titulo, :descricao, :id_categoria, :id_prioridade,
                    :id_status, :id_usuario_solicitante, :id_usuario_atribuido, :id_filial
                )
            ");
            
            $stmt->execute([
                'numero_ticket' => $numeroTicket,
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'],
                'id_categoria' => $dados['id_categoria'],
                'id_prioridade' => $dados['id_prioridade'],
                'id_status' => $dados['id_status'] ?? 1, // 1 = Aberto
                'id_usuario_solicitante' => $dados['id_usuario_solicitante'],
                'id_usuario_atribuido' => $dados['id_usuario_atribuido'] ?? null,
                'id_filial' => $dados['id_filial']
            ]);
            
            $idTicket = $this->pdo->lastInsertId();
            
            // Adicionar comentário inicial
            if (!empty($dados['descricao'])) {
                $this->adicionarComentario($idTicket, $dados['id_usuario_solicitante'], $dados['descricao']);
            }
            
            return $idTicket;
        } catch (Exception $e) {
            throw new Exception("Erro ao criar ticket: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar ticket por ID
     */
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, 
                   c.nome_categoria, c.cor as cor_categoria, c.icone as icone_categoria,
                   p.nome_prioridade, p.cor as cor_prioridade, p.icone as icone_prioridade,
                   s.nome_status, s.cor as cor_status, s.icone as icone_status,
                   us.nome_completo as solicitante_nome,
                   ua.nome_completo as atribuido_nome,
                   f.nome_filial
            FROM tbl_tickets t
            LEFT JOIN tbl_categorias_ticket c ON t.id_categoria = c.id_categoria
            LEFT JOIN tbl_prioridades_ticket p ON t.id_prioridade = p.id_prioridade
            LEFT JOIN tbl_status_ticket s ON t.id_status = s.id_status
            LEFT JOIN tbl_usuarios us ON t.id_usuario_solicitante = us.id_usuario
            LEFT JOIN tbl_usuarios ua ON t.id_usuario_atribuido = ua.id_usuario
            LEFT JOIN tbl_filiais f ON t.id_filial = f.id_filial
            WHERE t.id_ticket = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar tickets com paginação e filtros
     */
    public function listar($pagina = 1, $porPagina = 10, $filtros = []) {
        $where = "WHERE t.ativo = 1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['busca'])) {
            $where .= " AND (t.numero_ticket LIKE :busca OR t.titulo LIKE :busca OR t.descricao LIKE :busca)";
            $params['busca'] = "%{$filtros['busca']}%";
        }
        
        if (!empty($filtros['status'])) {
            $where .= " AND t.id_status = :status";
            $params['status'] = $filtros['status'];
        }
        
        if (!empty($filtros['prioridade'])) {
            $where .= " AND t.id_prioridade = :prioridade";
            $params['prioridade'] = $filtros['prioridade'];
        }
        
        if (!empty($filtros['categoria'])) {
            $where .= " AND t.id_categoria = :categoria";
            $params['categoria'] = $filtros['categoria'];
        }
        
        if (!empty($filtros['usuario_solicitante'])) {
            $where .= " AND t.id_usuario_solicitante = :usuario_solicitante";
            $params['usuario_solicitante'] = $filtros['usuario_solicitante'];
        }
        
        if (!empty($filtros['usuario_atribuido'])) {
            $where .= " AND t.id_usuario_atribuido = :usuario_atribuido";
            $params['usuario_atribuido'] = $filtros['usuario_atribuido'];
        }
        
        if (!empty($filtros['filial'])) {
            $where .= " AND t.id_filial = :filial";
            $params['filial'] = $filtros['filial'];
        }
        
        // Contar total
        $stmtCount = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM tbl_tickets t {$where}
        ");
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Buscar dados
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->pdo->prepare("
            SELECT t.*, 
                   c.nome_categoria, c.cor as cor_categoria,
                   p.nome_prioridade, p.cor as cor_prioridade,
                   s.nome_status, s.cor as cor_status,
                   us.nome_completo as solicitante_nome,
                   ua.nome_completo as atribuido_nome,
                   f.nome_filial
            FROM tbl_tickets t
            LEFT JOIN tbl_categorias_ticket c ON t.id_categoria = c.id_categoria
            LEFT JOIN tbl_prioridades_ticket p ON t.id_prioridade = p.id_prioridade
            LEFT JOIN tbl_status_ticket s ON t.id_status = s.id_status
            LEFT JOIN tbl_usuarios us ON t.id_usuario_solicitante = us.id_usuario
            LEFT JOIN tbl_usuarios ua ON t.id_usuario_atribuido = ua.id_usuario
            LEFT JOIN tbl_filiais f ON t.id_filial = f.id_filial
            {$where}
            ORDER BY t.data_abertura DESC
            LIMIT {$porPagina} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'tickets' => $tickets,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total_paginas' => ceil($total / $porPagina)
        ];
    }
    
    /**
     * Atualizar ticket
     */
    public function atualizar($id, $dados) {
        try {
            $campos = [];
            $params = ['id' => $id];
            
            $camposPermitidos = [
                'titulo', 'descricao', 'id_categoria', 'id_prioridade', 
                'id_status', 'id_usuario_atribuido', 'id_filial'
            ];
            
            foreach ($camposPermitidos as $campo) {
                if (isset($dados[$campo])) {
                    $campos[] = "{$campo} = :{$campo}";
                    $params[$campo] = $dados[$campo];
                }
            }
            
            if (empty($campos)) {
                return false;
            }
            
            $sql = "UPDATE tbl_tickets SET " . implode(', ', $campos) . " WHERE id_ticket = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar ticket: " . $e->getMessage());
        }
    }
    
    /**
     * Fechar ticket
     */
    public function fechar($id, $avaliacao = null, $comentario_avaliacao = null) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE tbl_tickets 
                SET id_status = 6, data_fechamento = NOW(), 
                    tempo_resolucao = TIMESTAMPDIFF(MINUTE, data_abertura, NOW()),
                    avaliacao = :avaliacao, comentario_avaliacao = :comentario_avaliacao
                WHERE id_ticket = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'avaliacao' => $avaliacao,
                'comentario_avaliacao' => $comentario_avaliacao
            ]);
        } catch (Exception $e) {
            throw new Exception("Erro ao fechar ticket: " . $e->getMessage());
        }
    }
    
    /**
     * Adicionar comentário
     * Retorna o ID do comentário criado
     */
    public function adicionarComentario($idTicket, $idUsuario, $comentario, $tipo = 'comentario', $dadosAnteriores = null, $dadosNovos = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_comentarios_ticket (
                    id_ticket, id_usuario, comentario, tipo, dados_anteriores, dados_novos
                ) VALUES (
                    :id_ticket, :id_usuario, :comentario, :tipo, :dados_anteriores, :dados_novos
                )
            ");
            
            $resultado = $stmt->execute([
                'id_ticket' => $idTicket,
                'id_usuario' => $idUsuario,
                'comentario' => $comentario,
                'tipo' => $tipo,
                'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores) : null,
                'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null
            ]);
            
            if ($resultado) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar comentários do ticket com anexos
     */
    public function buscarComentarios($idTicket) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.nome_completo as usuario_nome
            FROM tbl_comentarios_ticket c
            LEFT JOIN tbl_usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_ticket = :id_ticket
            ORDER BY c.created_at ASC
        ");
        $stmt->execute(['id_ticket' => $idTicket]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se a coluna id_comentario existe
        $colunaExiste = false;
        try {
            $stmtCheck = $this->pdo->query("SHOW COLUMNS FROM tbl_anexos_ticket LIKE 'id_comentario'");
            $colunaExiste = $stmtCheck->rowCount() > 0;
        } catch (Exception $e) {
            // Coluna não existe
            $colunaExiste = false;
        }
        
        // Buscar anexos para cada comentário
        foreach ($comentarios as &$comentario) {
            if ($colunaExiste) {
                // Se a coluna existe, buscar por id_comentario
                $stmtAnexos = $this->pdo->prepare("
                    SELECT a.*
                    FROM tbl_anexos_ticket a
                    WHERE a.id_comentario = :id_comentario
                    ORDER BY a.created_at ASC
                ");
                $stmtAnexos->execute(['id_comentario' => $comentario['id_comentario']]);
            } else {
                // Se não existe, buscar anexos do ticket criados próximo ao comentário (dentro de 5 minutos)
                $stmtAnexos = $this->pdo->prepare("
                    SELECT a.*
                    FROM tbl_anexos_ticket a
                    WHERE a.id_ticket = :id_ticket
                    AND ABS(TIMESTAMPDIFF(MINUTE, a.created_at, :created_at)) <= 5
                    ORDER BY a.created_at ASC
                ");
                $stmtAnexos->execute([
                    'id_ticket' => $idTicket,
                    'created_at' => $comentario['created_at']
                ]);
            }
            $comentario['anexos'] = $stmtAnexos->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $comentarios;
    }
    
    /**
     * Buscar estatísticas
     */
    public function getEstatisticas() {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_tickets,
                SUM(CASE WHEN id_status IN (1,2,3,4,5) THEN 1 ELSE 0 END) as tickets_abertos,
                SUM(CASE WHEN id_status = 6 THEN 1 ELSE 0 END) as tickets_resolvidos,
                SUM(CASE WHEN id_status = 7 THEN 1 ELSE 0 END) as tickets_fechados,
                SUM(CASE WHEN id_status = 8 THEN 1 ELSE 0 END) as tickets_cancelados,
                SUM(CASE WHEN id_prioridade = 4 THEN 1 ELSE 0 END) as tickets_criticos,
                AVG(CASE WHEN tempo_resolucao IS NOT NULL THEN tempo_resolucao ELSE NULL END) as tempo_medio_resolucao,
                AVG(CASE WHEN avaliacao IS NOT NULL THEN avaliacao ELSE NULL END) as avaliacao_media
            FROM tbl_tickets 
            WHERE ativo = 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar categorias
     */
    public function buscarCategorias() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tbl_categorias_ticket WHERE ativo = 1 ORDER BY nome_categoria
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar prioridades
     */
    public function buscarPrioridades() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tbl_prioridades_ticket WHERE ativo = 1 ORDER BY id_prioridade
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar status
     */
    public function buscarStatus() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tbl_status_ticket WHERE ativo = 1 ORDER BY id_status
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Salvar anexo de ticket
     */
    public function salvarAnexo($idTicket, $idUsuario, $nomeArquivo, $nomeOriginal, $tipoArquivo, $tamanho, $caminho, $idComentario = null) {
        try {
            // Verificar se a coluna id_comentario existe
            $colunaExiste = false;
            try {
                $stmtCheck = $this->pdo->query("SHOW COLUMNS FROM tbl_anexos_ticket LIKE 'id_comentario'");
                $colunaExiste = $stmtCheck->rowCount() > 0;
            } catch (Exception $e) {
                $colunaExiste = false;
            }
            
            if ($colunaExiste) {
                // Se a coluna existe, usar ela
                $stmt = $this->pdo->prepare("
                    INSERT INTO tbl_anexos_ticket (
                        id_ticket, id_comentario, id_usuario, nome_arquivo, nome_original, tipo_arquivo, tamanho, caminho
                    ) VALUES (
                        :id_ticket, :id_comentario, :id_usuario, :nome_arquivo, :nome_original, :tipo_arquivo, :tamanho, :caminho
                    )
                ");
                
                return $stmt->execute([
                    'id_ticket' => $idTicket,
                    'id_comentario' => $idComentario,
                    'id_usuario' => $idUsuario,
                    'nome_arquivo' => $nomeArquivo,
                    'nome_original' => $nomeOriginal,
                    'tipo_arquivo' => $tipoArquivo,
                    'tamanho' => $tamanho,
                    'caminho' => $caminho
                ]);
            } else {
                // Se não existe, inserir sem id_comentario
                $stmt = $this->pdo->prepare("
                    INSERT INTO tbl_anexos_ticket (
                        id_ticket, id_usuario, nome_arquivo, nome_original, tipo_arquivo, tamanho, caminho
                    ) VALUES (
                        :id_ticket, :id_usuario, :nome_arquivo, :nome_original, :tipo_arquivo, :tamanho, :caminho
                    )
                ");
                
                return $stmt->execute([
                    'id_ticket' => $idTicket,
                    'id_usuario' => $idUsuario,
                    'nome_arquivo' => $nomeArquivo,
                    'nome_original' => $nomeOriginal,
                    'tipo_arquivo' => $tipoArquivo,
                    'tamanho' => $tamanho,
                    'caminho' => $caminho
                ]);
            }
        } catch (Exception $e) {
            throw new Exception("Erro ao salvar anexo: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar anexos do ticket
     */
    public function buscarAnexos($idTicket) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.nome_completo as usuario_nome
            FROM tbl_anexos_ticket a
            LEFT JOIN tbl_usuarios u ON a.id_usuario = u.id_usuario
            WHERE a.id_ticket = :id_ticket
            ORDER BY a.created_at ASC
        ");
        $stmt->execute(['id_ticket' => $idTicket]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Deletar anexo
     */
    public function deletarAnexo($idAnexo) {
        try {
            // Buscar informações do anexo antes de deletar
            $stmt = $this->pdo->prepare("SELECT caminho FROM tbl_anexos_ticket WHERE id_anexo = :id");
            $stmt->execute(['id' => $idAnexo]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($anexo) {
                // Converter caminho relativo para absoluto
                $caminhoArquivo = __DIR__ . '/../../' . $anexo['caminho'];
                if (file_exists($caminhoArquivo)) {
                    unlink($caminhoArquivo);
                }
            }
            
            // Deletar do banco
            $stmt = $this->pdo->prepare("DELETE FROM tbl_anexos_ticket WHERE id_anexo = :id");
            return $stmt->execute(['id' => $idAnexo]);
        } catch (Exception $e) {
            throw new Exception("Erro ao deletar anexo: " . $e->getMessage());
        }
    }
} 