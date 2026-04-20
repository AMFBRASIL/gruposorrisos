-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/07/2025 às 21:52
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gruposorrisos`
--
CREATE DATABASE IF NOT EXISTS `gruposorrisos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gruposorrisos`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_alertas_estoque`
--

CREATE TABLE `tbl_alertas_estoque` (
  `id_alerta` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `tipo_alerta` enum('estoque_baixo','estoque_zerado','estoque_excedido','vencimento_proximo','vencimento_vencido') NOT NULL,
  `quantidade_atual` decimal(10,3) DEFAULT NULL,
  `quantidade_limite` decimal(10,3) DEFAULT NULL,
  `dias_antecedencia` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `nivel_urgencia` enum('baixa','media','alta') DEFAULT 'media',
  `lido` tinyint(1) DEFAULT 0,
  `email_notificacao` tinyint(1) DEFAULT 0,
  `sistema_notificacao` tinyint(1) DEFAULT 1,
  `ativo` tinyint(1) DEFAULT 1,
  `data_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_leitura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_categorias`
--

CREATE TABLE `tbl_categorias` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_pai` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_contas_pagar`
--

CREATE TABLE `tbl_contas_pagar` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('pendente','pago','vencido','cancelado') DEFAULT 'pendente',
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_contas_receber`
--

CREATE TABLE `tbl_contas_receber` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `cliente_nome` varchar(255) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_recebimento` date DEFAULT NULL,
  `status` enum('pendente','recebido','vencido','cancelado') DEFAULT 'pendente',
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_filiais`
--

CREATE TABLE `tbl_filiais` (
  `id_filial` int(11) NOT NULL,
  `codigo_filial` varchar(10) NOT NULL COMMENT 'Código único da filial (ex: FIL001)',
  `nome_filial` varchar(200) NOT NULL COMMENT 'Nome da filial',
  `razao_social` varchar(200) NOT NULL COMMENT 'Razão social da filial',
  `cnpj` varchar(18) DEFAULT NULL COMMENT 'CNPJ da filial',
  `inscricao_estadual` varchar(20) DEFAULT NULL COMMENT 'Inscrição estadual da filial',
  `endereco` text DEFAULT NULL COMMENT 'Endereço completo da filial',
  `cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade da filial',
  `estado` char(2) DEFAULT NULL COMMENT 'Estado da filial',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP da filial',
  `telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone da filial',
  `email` varchar(150) DEFAULT NULL COMMENT 'E-mail da filial',
  `responsavel` varchar(200) DEFAULT NULL COMMENT 'Nome do responsável pela filial',
  `tipo_filial` enum('matriz','filial','polo') DEFAULT 'filial' COMMENT 'Tipo da filial',
  `filial_ativa` tinyint(1) DEFAULT 1 COMMENT 'Status da filial (1=ativa, 0=inativa)',
  `data_inauguracao` date DEFAULT NULL COMMENT 'Data de inauguração da filial',
  `observacoes` text DEFAULT NULL COMMENT 'Observações sobre a filial',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_fornecedores`
--

CREATE TABLE `tbl_fornecedores` (
  `id_fornecedor` int(11) NOT NULL,
  `razao_social` varchar(200) NOT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `inscricao_estadual` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contato_principal` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_itens_pedido`
--

CREATE TABLE `tbl_itens_pedido` (
  `id_item_pedido` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade_solicitada` decimal(10,3) NOT NULL,
  `quantidade_recebida` decimal(10,3) DEFAULT 0.000,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_logs_auditoria`
--

CREATE TABLE `tbl_logs_auditoria` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tabela` varchar(100) NOT NULL,
  `acao` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_logs_sistema`
--

CREATE TABLE `tbl_logs_sistema` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_materiais`
--

CREATE TABLE `tbl_materiais` (
  `id_material` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `estoque_minimo` decimal(10,3) DEFAULT 0.000,
  `estoque_maximo` decimal(10,3) DEFAULT 0.000,
  `estoque_atual` decimal(10,3) DEFAULT 0.000,
  `localizacao_estoque` varchar(100) DEFAULT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



--
-- Acionadores `tbl_materiais`
--
DELIMITER $$
CREATE TRIGGER `tr_materiais_estoque_baixo` AFTER UPDATE ON `tbl_materiais` FOR EACH ROW BEGIN
    -- Se estoque ficou baixo ou zerado
    IF (NEW.estoque_atual <= NEW.estoque_minimo AND NEW.estoque_atual > 0) OR NEW.estoque_atual = 0 THEN
        INSERT INTO tbl_alertas_estoque (
            id_filial, id_material, tipo_alerta, mensagem, nivel_urgencia, lido
        ) VALUES (
            NEW.id_filial,
            NEW.id_material,
            CASE 
                WHEN NEW.estoque_atual = 0 THEN 'estoque_zerado'
                ELSE 'estoque_baixo'
            END,
            CASE 
                WHEN NEW.estoque_atual = 0 THEN CONCAT('Material ', NEW.nome, ' com estoque zerado')
                ELSE CONCAT('Material ', NEW.nome, ' com estoque baixo (', NEW.estoque_atual, ' ', 
                           (SELECT sigla FROM tbl_unidades_medida WHERE id_unidade = NEW.id_unidade), ')')
            END,
            CASE 
                WHEN NEW.estoque_atual = 0 THEN 'alta'
                ELSE 'media'
            END,
            0
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_materiais_update` BEFORE UPDATE ON `tbl_materiais` FOR EACH ROW BEGIN
    SET NEW.data_atualizacao = NOW();
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_alerta_estoque_baixo` AFTER UPDATE ON `tbl_materiais` FOR EACH ROW BEGIN
    -- Alerta de estoque baixo
    IF NEW.estoque_atual <= NEW.estoque_minimo AND NEW.estoque_atual > 0 THEN
        INSERT INTO tbl_alertas_estoque (id_filial, id_material, tipo_alerta, quantidade_atual, quantidade_limite, mensagem, nivel_urgencia)
        VALUES (NEW.id_filial, NEW.id_material, 'estoque_baixo', NEW.estoque_atual, NEW.estoque_minimo, 
                CONCAT('Material ', NEW.nome, ' com estoque baixo. Quantidade atual: ', NEW.estoque_atual), 'media');
    END IF;
    
    -- Alerta de estoque zerado
    IF NEW.estoque_atual = 0 AND OLD.estoque_atual > 0 THEN
        INSERT INTO tbl_alertas_estoque (id_filial, id_material, tipo_alerta, quantidade_atual, quantidade_limite, mensagem, nivel_urgencia)
        VALUES (NEW.id_filial, NEW.id_material, 'estoque_zerado', NEW.estoque_atual, 0, 
                CONCAT('Material ', NEW.nome, ' com estoque zerado!'), 'alta');
    END IF;
    
    -- Alerta de estoque excedido
    IF NEW.estoque_atual > NEW.estoque_maximo AND NEW.estoque_maximo > 0 THEN
        INSERT INTO tbl_alertas_estoque (id_filial, id_material, tipo_alerta, quantidade_atual, quantidade_limite, mensagem, nivel_urgencia)
        VALUES (NEW.id_filial, NEW.id_material, 'estoque_excedido', NEW.estoque_atual, NEW.estoque_maximo, 
                CONCAT('Material ', NEW.nome, ' com estoque acima do máximo. Quantidade atual: ', NEW.estoque_atual), 'baixa');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_movimentacoes`
--

CREATE TABLE `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_tipo_movimentacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estoque_anterior` decimal(10,3) NOT NULL,
  `estoque_atual` decimal(10,3) NOT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `tbl_movimentacoes`
--
DELIMITER $$
CREATE TRIGGER `trg_atualizar_estoque_material` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW BEGIN
    IF NEW.id_tipo_movimentacao IN (SELECT id_tipo_movimentacao FROM tbl_tipos_movimentacao WHERE tipo = 'entrada') THEN
        UPDATE tbl_materiais 
        SET estoque_atual = estoque_atual + NEW.quantidade,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial;
    ELSE
        UPDATE tbl_materiais 
        SET estoque_atual = estoque_atual - NEW.quantidade,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_paginas`
--

CREATE TABLE `tbl_paginas` (
  `id_pagina` int(11) NOT NULL,
  `nome_pagina` varchar(100) NOT NULL,
  `url_pagina` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_pedidos_compra`
--

CREATE TABLE `tbl_pedidos_compra` (
  `id_pedido` int(11) NOT NULL,
  `numero_pedido` varchar(50) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `id_usuario_solicitante` int(11) NOT NULL,
  `id_usuario_aprovador` int(11) DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  `data_entrega_prevista` date DEFAULT NULL,
  `data_entrega_realizada` date DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado','em_entrega','entregue','cancelado') DEFAULT 'pendente',
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_perfis`
--

CREATE TABLE `tbl_perfis` (
  `id_perfil` int(11) NOT NULL,
  `nome_perfil` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_permissoes`
--

CREATE TABLE `tbl_permissoes` (
  `id_permissao` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `pode_visualizar` tinyint(1) DEFAULT 0,
  `pode_inserir` tinyint(1) DEFAULT 0,
  `pode_editar` tinyint(1) DEFAULT 0,
  `pode_excluir` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_sessoes_jwt`
--

CREATE TABLE `tbl_sessoes_jwt` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_tipos_movimentacao`
--

CREATE TABLE `tbl_tipos_movimentacao` (
  `id_tipo_movimentacao` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_unidades_medida`
--

CREATE TABLE `tbl_unidades_medida` (
  `id_unidade` int(11) NOT NULL,
  `sigla` varchar(10) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tbl_usuarios`
--

CREATE TABLE `tbl_usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_completo` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `id_perfil` int(11) NOT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_acesso` timestamp NULL DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_alertas_nao_lidos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_alertas_nao_lidos` (
`id_alerta` int(11)
,`tipo_alerta` enum('estoque_baixo','estoque_zerado','estoque_excedido','vencimento_proximo','vencimento_vencido')
,`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`codigo` varchar(50)
,`material` varchar(200)
,`quantidade_atual` decimal(10,3)
,`quantidade_limite` decimal(10,3)
,`mensagem` text
,`nivel_urgencia` enum('baixa','media','alta')
,`data_alerta` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_estoque_atual`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_estoque_atual` (
`id_material` int(11)
,`codigo` varchar(50)
,`nome` varchar(200)
,`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`nome_categoria` varchar(100)
,`fornecedor` varchar(200)
,`unidade` varchar(10)
,`estoque_atual` decimal(10,3)
,`estoque_minimo` decimal(10,3)
,`estoque_maximo` decimal(10,3)
,`preco_unitario` decimal(10,2)
,`valor_total_estoque` decimal(20,5)
,`status_estoque` varchar(8)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_estoque_por_filial`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_estoque_por_filial` (
`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`cidade` varchar(100)
,`estado` char(2)
,`codigo_material` varchar(50)
,`nome_material` varchar(200)
,`nome_categoria` varchar(100)
,`estoque_atual` decimal(10,3)
,`estoque_minimo` decimal(10,3)
,`estoque_maximo` decimal(10,3)
,`preco_unitario` decimal(10,2)
,`valor_total_estoque` decimal(20,5)
,`unidade` varchar(10)
,`status_estoque` varchar(8)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_movimentacoes_detalhadas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_movimentacoes_detalhadas` (
`id_movimentacao` int(11)
,`data_movimentacao` timestamp
,`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`codigo` varchar(50)
,`material` varchar(200)
,`tipo_movimentacao` varchar(100)
,`tipo` enum('entrada','saida')
,`quantidade` decimal(10,3)
,`preco_unitario` decimal(10,2)
,`valor_total` decimal(10,2)
,`usuario` varchar(200)
,`numero_documento` varchar(50)
,`observacoes` text
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_movimentacoes_por_filial`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_movimentacoes_por_filial` (
`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`codigo_material` varchar(50)
,`nome_material` varchar(200)
,`tipo_movimentacao` varchar(100)
,`tipo` enum('entrada','saida')
,`quantidade` decimal(10,3)
,`preco_unitario` decimal(10,2)
,`valor_total` decimal(10,2)
,`estoque_anterior` decimal(10,3)
,`estoque_atual` decimal(10,3)
,`numero_documento` varchar(50)
,`observacoes` text
,`data_movimentacao` timestamp
,`usuario` varchar(200)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_pedidos_por_filial`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_pedidos_por_filial` (
`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`numero_pedido` varchar(50)
,`data_solicitacao` timestamp
,`data_aprovacao` timestamp
,`data_entrega_prevista` date
,`status` enum('pendente','aprovado','rejeitado','em_entrega','entregue','cancelado')
,`valor_total` decimal(10,2)
,`observacoes` text
,`fornecedor` varchar(200)
,`solicitante` varchar(200)
,`aprovador` varchar(200)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_relatorio_consolidado_filiais`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_relatorio_consolidado_filiais` (
`codigo_filial` varchar(10)
,`nome_filial` varchar(200)
,`cidade` varchar(100)
,`estado` char(2)
,`total_materiais` bigint(21)
,`valor_total_estoque` decimal(42,5)
,`materiais_estoque_baixo` bigint(21)
,`materiais_estoque_zerado` bigint(21)
,`materiais_estoque_excedido` bigint(21)
,`total_movimentacoes` bigint(21)
,`total_pedidos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_alertas_nao_lidos`
--
DROP TABLE IF EXISTS `vw_alertas_nao_lidos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_alertas_nao_lidos`  AS SELECT `a`.`id_alerta` AS `id_alerta`, `a`.`tipo_alerta` AS `tipo_alerta`, `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `m`.`codigo` AS `codigo`, `m`.`nome` AS `material`, `a`.`quantidade_atual` AS `quantidade_atual`, `a`.`quantidade_limite` AS `quantidade_limite`, `a`.`mensagem` AS `mensagem`, `a`.`nivel_urgencia` AS `nivel_urgencia`, `a`.`data_alerta` AS `data_alerta` FROM ((`tbl_alertas_estoque` `a` join `tbl_filiais` `f` on(`a`.`id_filial` = `f`.`id_filial`)) join `tbl_materiais` `m` on(`a`.`id_material` = `m`.`id_material`)) WHERE `a`.`lido` = 0 AND `f`.`filial_ativa` = 1 ORDER BY `a`.`data_alerta` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_estoque_atual`
--
DROP TABLE IF EXISTS `vw_estoque_atual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_estoque_atual`  AS SELECT `m`.`id_material` AS `id_material`, `m`.`codigo` AS `codigo`, `m`.`nome` AS `nome`, `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `c`.`nome_categoria` AS `nome_categoria`, `forn`.`razao_social` AS `fornecedor`, `um`.`sigla` AS `unidade`, `m`.`estoque_atual` AS `estoque_atual`, `m`.`estoque_minimo` AS `estoque_minimo`, `m`.`estoque_maximo` AS `estoque_maximo`, `m`.`preco_unitario` AS `preco_unitario`, `m`.`estoque_atual`* `m`.`preco_unitario` AS `valor_total_estoque`, CASE WHEN `m`.`estoque_atual` <= `m`.`estoque_minimo` THEN 'Baixo' WHEN `m`.`estoque_atual` = 0 THEN 'Zerado' WHEN `m`.`estoque_atual` > `m`.`estoque_maximo` THEN 'Excedido' ELSE 'Normal' END AS `status_estoque` FROM ((((`tbl_materiais` `m` join `tbl_filiais` `f` on(`m`.`id_filial` = `f`.`id_filial`)) left join `tbl_categorias` `c` on(`m`.`id_categoria` = `c`.`id_categoria`)) left join `tbl_fornecedores` `forn` on(`m`.`id_fornecedor` = `forn`.`id_fornecedor`)) left join `tbl_unidades_medida` `um` on(`m`.`id_unidade` = `um`.`id_unidade`)) WHERE `m`.`ativo` = 1 AND `f`.`filial_ativa` = 1 ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_estoque_por_filial`
--
DROP TABLE IF EXISTS `vw_estoque_por_filial`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_estoque_por_filial`  AS SELECT `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `f`.`cidade` AS `cidade`, `f`.`estado` AS `estado`, `m`.`codigo` AS `codigo_material`, `m`.`nome` AS `nome_material`, `c`.`nome_categoria` AS `nome_categoria`, `m`.`estoque_atual` AS `estoque_atual`, `m`.`estoque_minimo` AS `estoque_minimo`, `m`.`estoque_maximo` AS `estoque_maximo`, `m`.`preco_unitario` AS `preco_unitario`, `m`.`estoque_atual`* `m`.`preco_unitario` AS `valor_total_estoque`, `u`.`sigla` AS `unidade`, CASE WHEN `m`.`estoque_atual` <= `m`.`estoque_minimo` AND `m`.`estoque_atual` > 0 THEN 'Baixo' WHEN `m`.`estoque_atual` = 0 THEN 'Zerado' WHEN `m`.`estoque_atual` > `m`.`estoque_maximo` AND `m`.`estoque_maximo` > 0 THEN 'Excedido' ELSE 'Normal' END AS `status_estoque` FROM (((`tbl_filiais` `f` join `tbl_materiais` `m` on(`f`.`id_filial` = `m`.`id_filial`)) left join `tbl_categorias` `c` on(`m`.`id_categoria` = `c`.`id_categoria`)) left join `tbl_unidades_medida` `u` on(`m`.`id_unidade` = `u`.`id_unidade`)) WHERE `f`.`filial_ativa` = 1 AND `m`.`ativo` = 1 ORDER BY `f`.`nome_filial` ASC, `m`.`nome` ASC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_movimentacoes_detalhadas`
--
DROP TABLE IF EXISTS `vw_movimentacoes_detalhadas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_movimentacoes_detalhadas`  AS SELECT `mov`.`id_movimentacao` AS `id_movimentacao`, `mov`.`data_movimentacao` AS `data_movimentacao`, `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `m`.`codigo` AS `codigo`, `m`.`nome` AS `material`, `tm`.`nome` AS `tipo_movimentacao`, `tm`.`tipo` AS `tipo`, `mov`.`quantidade` AS `quantidade`, `mov`.`preco_unitario` AS `preco_unitario`, `mov`.`valor_total` AS `valor_total`, `u`.`nome_completo` AS `usuario`, `mov`.`numero_documento` AS `numero_documento`, `mov`.`observacoes` AS `observacoes` FROM ((((`tbl_movimentacoes` `mov` join `tbl_filiais` `f` on(`mov`.`id_filial` = `f`.`id_filial`)) join `tbl_materiais` `m` on(`mov`.`id_material` = `m`.`id_material`)) join `tbl_tipos_movimentacao` `tm` on(`mov`.`id_tipo_movimentacao` = `tm`.`id_tipo_movimentacao`)) join `tbl_usuarios` `u` on(`mov`.`id_usuario` = `u`.`id_usuario`)) WHERE `f`.`filial_ativa` = 1 ORDER BY `mov`.`data_movimentacao` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_movimentacoes_por_filial`
--
DROP TABLE IF EXISTS `vw_movimentacoes_por_filial`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_movimentacoes_por_filial`  AS SELECT `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `m`.`codigo` AS `codigo_material`, `m`.`nome` AS `nome_material`, `tm`.`nome` AS `tipo_movimentacao`, `tm`.`tipo` AS `tipo`, `mov`.`quantidade` AS `quantidade`, `mov`.`preco_unitario` AS `preco_unitario`, `mov`.`valor_total` AS `valor_total`, `mov`.`estoque_anterior` AS `estoque_anterior`, `mov`.`estoque_atual` AS `estoque_atual`, `mov`.`numero_documento` AS `numero_documento`, `mov`.`observacoes` AS `observacoes`, `mov`.`data_movimentacao` AS `data_movimentacao`, `u`.`nome_completo` AS `usuario` FROM ((((`tbl_filiais` `f` join `tbl_movimentacoes` `mov` on(`f`.`id_filial` = `mov`.`id_filial`)) join `tbl_materiais` `m` on(`mov`.`id_material` = `m`.`id_material`)) join `tbl_tipos_movimentacao` `tm` on(`mov`.`id_tipo_movimentacao` = `tm`.`id_tipo_movimentacao`)) join `tbl_usuarios` `u` on(`mov`.`id_usuario` = `u`.`id_usuario`)) WHERE `f`.`filial_ativa` = 1 ORDER BY `mov`.`data_movimentacao` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_pedidos_por_filial`
--
DROP TABLE IF EXISTS `vw_pedidos_por_filial`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pedidos_por_filial`  AS SELECT `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `pc`.`numero_pedido` AS `numero_pedido`, `pc`.`data_solicitacao` AS `data_solicitacao`, `pc`.`data_aprovacao` AS `data_aprovacao`, `pc`.`data_entrega_prevista` AS `data_entrega_prevista`, `pc`.`status` AS `status`, `pc`.`valor_total` AS `valor_total`, `pc`.`observacoes` AS `observacoes`, `forn`.`razao_social` AS `fornecedor`, `u_sol`.`nome_completo` AS `solicitante`, `u_apr`.`nome_completo` AS `aprovador` FROM ((((`tbl_filiais` `f` join `tbl_pedidos_compra` `pc` on(`f`.`id_filial` = `pc`.`id_filial`)) join `tbl_fornecedores` `forn` on(`pc`.`id_fornecedor` = `forn`.`id_fornecedor`)) join `tbl_usuarios` `u_sol` on(`pc`.`id_usuario_solicitante` = `u_sol`.`id_usuario`)) left join `tbl_usuarios` `u_apr` on(`pc`.`id_usuario_aprovador` = `u_apr`.`id_usuario`)) WHERE `f`.`filial_ativa` = 1 AND `pc`.`ativo` = 1 ORDER BY `pc`.`data_solicitacao` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_relatorio_consolidado_filiais`
--
DROP TABLE IF EXISTS `vw_relatorio_consolidado_filiais`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_relatorio_consolidado_filiais`  AS SELECT `f`.`codigo_filial` AS `codigo_filial`, `f`.`nome_filial` AS `nome_filial`, `f`.`cidade` AS `cidade`, `f`.`estado` AS `estado`, count(distinct `m`.`id_material`) AS `total_materiais`, sum(`m`.`estoque_atual` * `m`.`preco_unitario`) AS `valor_total_estoque`, count(case when `m`.`estoque_atual` <= `m`.`estoque_minimo` and `m`.`estoque_atual` > 0 then 1 end) AS `materiais_estoque_baixo`, count(case when `m`.`estoque_atual` = 0 then 1 end) AS `materiais_estoque_zerado`, count(case when `m`.`estoque_atual` > `m`.`estoque_maximo` and `m`.`estoque_maximo` > 0 then 1 end) AS `materiais_estoque_excedido`, count(distinct `mov`.`id_movimentacao`) AS `total_movimentacoes`, count(distinct `pc`.`id_pedido`) AS `total_pedidos` FROM (((`tbl_filiais` `f` left join `tbl_materiais` `m` on(`f`.`id_filial` = `m`.`id_filial` and `m`.`ativo` = 1)) left join `tbl_movimentacoes` `mov` on(`f`.`id_filial` = `mov`.`id_filial`)) left join `tbl_pedidos_compra` `pc` on(`f`.`id_filial` = `pc`.`id_filial` and `pc`.`ativo` = 1)) WHERE `f`.`filial_ativa` = 1 GROUP BY `f`.`id_filial`, `f`.`codigo_filial`, `f`.`nome_filial`, `f`.`cidade`, `f`.`estado` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tbl_alertas_estoque`
--
ALTER TABLE `tbl_alertas_estoque`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `idx_alertas_filial` (`id_filial`),
  ADD KEY `idx_alertas_material` (`id_material`),
  ADD KEY `idx_alertas_tipo` (`tipo_alerta`),
  ADD KEY `idx_alertas_lido` (`lido`),
  ADD KEY `idx_alertas_data` (`data_alerta`);

--
-- Índices de tabela `tbl_categorias`
--
ALTER TABLE `tbl_categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `categoria_pai` (`categoria_pai`);

--
-- Índices de tabela `tbl_contas_pagar`
--
ALTER TABLE `tbl_contas_pagar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `fornecedor_id` (`fornecedor_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Índices de tabela `tbl_contas_receber`
--
ALTER TABLE `tbl_contas_receber`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `tbl_filiais`
--
ALTER TABLE `tbl_filiais`
  ADD PRIMARY KEY (`id_filial`),
  ADD UNIQUE KEY `codigo_filial` (`codigo_filial`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD KEY `idx_codigo_filial` (`codigo_filial`),
  ADD KEY `idx_filial_ativa` (`filial_ativa`),
  ADD KEY `idx_cidade_estado` (`cidade`,`estado`);

--
-- Índices de tabela `tbl_fornecedores`
--
ALTER TABLE `tbl_fornecedores`
  ADD PRIMARY KEY (`id_fornecedor`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `tbl_itens_pedido`
--
ALTER TABLE `tbl_itens_pedido`
  ADD PRIMARY KEY (`id_item_pedido`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_material` (`id_material`);

--
-- Índices de tabela `tbl_logs_auditoria`
--
ALTER TABLE `tbl_logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tbl_logs_sistema`
--
ALTER TABLE `tbl_logs_sistema`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_logs_usuario` (`id_usuario`),
  ADD KEY `idx_logs_filial` (`id_filial`),
  ADD KEY `idx_logs_data` (`data_log`),
  ADD KEY `idx_logs_acao` (`acao`);

--
-- Índices de tabela `tbl_materiais`
--
ALTER TABLE `tbl_materiais`
  ADD PRIMARY KEY (`id_material`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_unidade` (`id_unidade`),
  ADD KEY `idx_materiais_codigo` (`codigo`),
  ADD KEY `idx_materiais_categoria` (`id_categoria`),
  ADD KEY `idx_materiais_fornecedor` (`id_fornecedor`),
  ADD KEY `idx_materiais_filial` (`id_filial`),
  ADD KEY `idx_materiais_ativo` (`ativo`),
  ADD KEY `idx_materiais_estoque` (`estoque_atual`);

--
-- Índices de tabela `tbl_movimentacoes`
--
ALTER TABLE `tbl_movimentacoes`
  ADD PRIMARY KEY (`id_movimentacao`),
  ADD KEY `idx_movimentacoes_filial` (`id_filial`),
  ADD KEY `idx_movimentacoes_material` (`id_material`),
  ADD KEY `idx_movimentacoes_tipo` (`id_tipo_movimentacao`),
  ADD KEY `idx_movimentacoes_usuario` (`id_usuario`),
  ADD KEY `idx_movimentacoes_data` (`data_movimentacao`);

--
-- Índices de tabela `tbl_paginas`
--
ALTER TABLE `tbl_paginas`
  ADD PRIMARY KEY (`id_pagina`);

--
-- Índices de tabela `tbl_pedidos_compra`
--
ALTER TABLE `tbl_pedidos_compra`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `numero_pedido` (`numero_pedido`),
  ADD KEY `id_usuario_solicitante` (`id_usuario_solicitante`),
  ADD KEY `id_usuario_aprovador` (`id_usuario_aprovador`),
  ADD KEY `idx_pedidos_numero` (`numero_pedido`),
  ADD KEY `idx_pedidos_filial` (`id_filial`),
  ADD KEY `idx_pedidos_fornecedor` (`id_fornecedor`),
  ADD KEY `idx_pedidos_status` (`status`),
  ADD KEY `idx_pedidos_data` (`data_solicitacao`);

--
-- Índices de tabela `tbl_perfis`
--
ALTER TABLE `tbl_perfis`
  ADD PRIMARY KEY (`id_perfil`);

--
-- Índices de tabela `tbl_permissoes`
--
ALTER TABLE `tbl_permissoes`
  ADD PRIMARY KEY (`id_permissao`),
  ADD UNIQUE KEY `uk_perfil_pagina` (`id_perfil`,`id_pagina`),
  ADD KEY `id_pagina` (`id_pagina`);

--
-- Índices de tabela `tbl_sessoes_jwt`
--
ALTER TABLE `tbl_sessoes_jwt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tbl_tipos_movimentacao`
--
ALTER TABLE `tbl_tipos_movimentacao`
  ADD PRIMARY KEY (`id_tipo_movimentacao`);

--
-- Índices de tabela `tbl_unidades_medida`
--
ALTER TABLE `tbl_unidades_medida`
  ADD PRIMARY KEY (`id_unidade`),
  ADD UNIQUE KEY `sigla` (`sigla`);

--
-- Índices de tabela `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_cpf` (`cpf`),
  ADD KEY `idx_usuarios_perfil` (`id_perfil`),
  ADD KEY `idx_usuarios_filial` (`id_filial`),
  ADD KEY `idx_usuarios_ativo` (`ativo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tbl_alertas_estoque`
--
ALTER TABLE `tbl_alertas_estoque`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_categorias`
--
ALTER TABLE `tbl_categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_contas_pagar`
--
ALTER TABLE `tbl_contas_pagar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_contas_receber`
--
ALTER TABLE `tbl_contas_receber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_filiais`
--
ALTER TABLE `tbl_filiais`
  MODIFY `id_filial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_fornecedores`
--
ALTER TABLE `tbl_fornecedores`
  MODIFY `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_itens_pedido`
--
ALTER TABLE `tbl_itens_pedido`
  MODIFY `id_item_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_logs_auditoria`
--
ALTER TABLE `tbl_logs_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_logs_sistema`
--
ALTER TABLE `tbl_logs_sistema`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_materiais`
--
ALTER TABLE `tbl_materiais`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_movimentacoes`
--
ALTER TABLE `tbl_movimentacoes`
  MODIFY `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_paginas`
--
ALTER TABLE `tbl_paginas`
  MODIFY `id_pagina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_pedidos_compra`
--
ALTER TABLE `tbl_pedidos_compra`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_perfis`
--
ALTER TABLE `tbl_perfis`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_permissoes`
--
ALTER TABLE `tbl_permissoes`
  MODIFY `id_permissao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_sessoes_jwt`
--
ALTER TABLE `tbl_sessoes_jwt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_tipos_movimentacao`
--
ALTER TABLE `tbl_tipos_movimentacao`
  MODIFY `id_tipo_movimentacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_unidades_medida`
--
ALTER TABLE `tbl_unidades_medida`
  MODIFY `id_unidade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tbl_alertas_estoque`
--
ALTER TABLE `tbl_alertas_estoque`
  ADD CONSTRAINT `tbl_alertas_estoque_ibfk_1` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_alertas_estoque_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tbl_categorias`
--
ALTER TABLE `tbl_categorias`
  ADD CONSTRAINT `tbl_categorias_ibfk_1` FOREIGN KEY (`categoria_pai`) REFERENCES `tbl_categorias` (`id_categoria`) ON DELETE SET NULL;

--
-- Restrições para tabelas `tbl_contas_pagar`
--
ALTER TABLE `tbl_contas_pagar`
  ADD CONSTRAINT `tbl_contas_pagar_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `tbl_filiais` (`id_filial`),
  ADD CONSTRAINT `tbl_contas_pagar_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `tbl_fornecedores` (`id_fornecedor`),
  ADD CONSTRAINT `tbl_contas_pagar_ibfk_3` FOREIGN KEY (`pedido_id`) REFERENCES `tbl_pedidos_compra` (`id_pedido`);

--
-- Restrições para tabelas `tbl_contas_receber`
--
ALTER TABLE `tbl_contas_receber`
  ADD CONSTRAINT `tbl_contas_receber_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `tbl_filiais` (`id_filial`);

--
-- Restrições para tabelas `tbl_itens_pedido`
--
ALTER TABLE `tbl_itens_pedido`
  ADD CONSTRAINT `tbl_itens_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `tbl_pedidos_compra` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_itens_pedido_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`);

--
-- Restrições para tabelas `tbl_logs_auditoria`
--
ALTER TABLE `tbl_logs_auditoria`
  ADD CONSTRAINT `tbl_logs_auditoria_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `tbl_filiais` (`id_filial`),
  ADD CONSTRAINT `tbl_logs_auditoria_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id_usuario`);

--
-- Restrições para tabelas `tbl_logs_sistema`
--
ALTER TABLE `tbl_logs_sistema`
  ADD CONSTRAINT `tbl_logs_sistema_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_logs_sistema_ibfk_2` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE SET NULL;

--
-- Restrições para tabelas `tbl_materiais`
--
ALTER TABLE `tbl_materiais`
  ADD CONSTRAINT `tbl_materiais_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias` (`id_categoria`),
  ADD CONSTRAINT `tbl_materiais_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_materiais_ibfk_3` FOREIGN KEY (`id_unidade`) REFERENCES `tbl_unidades_medida` (`id_unidade`),
  ADD CONSTRAINT `tbl_materiais_ibfk_4` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`);

--
-- Restrições para tabelas `tbl_movimentacoes`
--
ALTER TABLE `tbl_movimentacoes`
  ADD CONSTRAINT `tbl_movimentacoes_ibfk_1` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  ADD CONSTRAINT `tbl_movimentacoes_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`),
  ADD CONSTRAINT `tbl_movimentacoes_ibfk_3` FOREIGN KEY (`id_tipo_movimentacao`) REFERENCES `tbl_tipos_movimentacao` (`id_tipo_movimentacao`),
  ADD CONSTRAINT `tbl_movimentacoes_ibfk_4` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`);

--
-- Restrições para tabelas `tbl_pedidos_compra`
--
ALTER TABLE `tbl_pedidos_compra`
  ADD CONSTRAINT `tbl_pedidos_compra_ibfk_1` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  ADD CONSTRAINT `tbl_pedidos_compra_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`),
  ADD CONSTRAINT `tbl_pedidos_compra_ibfk_3` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`),
  ADD CONSTRAINT `tbl_pedidos_compra_ibfk_4` FOREIGN KEY (`id_usuario_aprovador`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Restrições para tabelas `tbl_permissoes`
--
ALTER TABLE `tbl_permissoes`
  ADD CONSTRAINT `tbl_permissoes_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_permissoes_ibfk_2` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tbl_sessoes_jwt`
--
ALTER TABLE `tbl_sessoes_jwt`
  ADD CONSTRAINT `tbl_sessoes_jwt_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id_usuario`);

--
-- Restrições para tabelas `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD CONSTRAINT `tbl_usuarios_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




-- =====================================================
-- SISTEMA DE ESTOQUE ROBUSTO E AVANÇADO
-- =====================================================

-- 1. TABELA DE ESTOQUE POR FILIAL (Controle Multi-Filial)
CREATE TABLE IF NOT EXISTS `tbl_estoque_filial` (
  `id_estoque_filial` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `estoque_atual` decimal(15,3) NOT NULL DEFAULT 0,
  `estoque_minimo` decimal(15,3) DEFAULT 0,
  `estoque_maximo` decimal(15,3) DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL COMMENT 'Localização física (prateleira, corredor, etc.)',
  `custo_medio` decimal(15,4) DEFAULT 0,
  `ultima_movimentacao` datetime DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estoque_filial`),
  UNIQUE KEY `uk_material_filial` (`id_material`, `id_filial`),
  KEY `fk_estoque_filial_material` (`id_material`),
  KEY `fk_estoque_filial_filial` (`id_filial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA DE LOTES (Controle de Validade e Rastreabilidade)
CREATE TABLE IF NOT EXISTS `tbl_lotes` (
  `id_lote` int(11) NOT NULL AUTO_INCREMENT,
  `numero_lote` varchar(50) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `quantidade_inicial` decimal(15,3) NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `custo_unitario` decimal(15,4) DEFAULT 0,
  `fornecedor_lote` varchar(200) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('ativo','vencido','consumido','cancelado') NOT NULL DEFAULT 'ativo',
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `uk_numero_lote` (`numero_lote`),
  KEY `fk_lote_material` (`id_material`),
  KEY `fk_lote_filial` (`id_filial`),
  KEY `idx_data_validade` (`data_validade`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE MOVIMENTAÇÕES AVANÇADA
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste','devolucao','inventario') NOT NULL,
  `subtipo_movimentacao` varchar(50) DEFAULT NULL COMMENT 'Compra, Venda, Transferência, Ajuste, etc.',
  `id_material` int(11) NOT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `id_filial_origem` int(11) DEFAULT NULL,
  `id_filial_destino` int(11) DEFAULT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `estoque_anterior_origem` decimal(15,3) DEFAULT 0,
  `estoque_atual_origem` decimal(15,3) DEFAULT 0,
  `estoque_anterior_destino` decimal(15,3) DEFAULT 0,
  `estoque_atual_destino` decimal(15,3) DEFAULT 0,
  `valor_unitario` decimal(15,4) DEFAULT NULL,
  `valor_total` decimal(15,4) DEFAULT NULL,
  `custo_medio_anterior` decimal(15,4) DEFAULT 0,
  `custo_medio_atual` decimal(15,4) DEFAULT 0,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_pedido_compra` int(11) DEFAULT NULL,
  `id_venda` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `motivo_ajuste` varchar(200) DEFAULT NULL,
  `status_movimentacao` enum('pendente','aprovada','executada','cancelada','estornada') NOT NULL DEFAULT 'executada',
  `id_usuario_solicitante` int(11) DEFAULT NULL,
  `id_usuario_executor` int(11) NOT NULL,
  `data_solicitacao` datetime DEFAULT NULL,
  `data_aprovacao` datetime DEFAULT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimentacao`),
  UNIQUE KEY `numero_movimentacao` (`numero_movimentacao`),
  KEY `fk_movimentacao_material` (`id_material`),
  KEY `fk_movimentacao_lote` (`id_lote`),
  KEY `fk_movimentacao_filial_origem` (`id_filial_origem`),
  KEY `fk_movimentacao_filial_destino` (`id_filial_destino`),
  KEY `fk_movimentacao_fornecedor` (`id_fornecedor`),
  KEY `fk_movimentacao_cliente` (`id_cliente`),
  KEY `fk_movimentacao_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `fk_movimentacao_usuario_executor` (`id_usuario_executor`),
  KEY `idx_data_movimentacao` (`data_movimentacao`),
  KEY `idx_tipo_movimentacao` (`tipo_movimentacao`),
  KEY `idx_status_movimentacao` (`status_movimentacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABELA DE AUDITORIA DE MOVIMENTAÇÕES
CREATE TABLE IF NOT EXISTS `tbl_auditoria_movimentacoes` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimentacao` int(11) NOT NULL,
  `acao` enum('criacao','alteracao','cancelamento','estorno') NOT NULL,
  `dados_anteriores` json DEFAULT NULL,
  `dados_novos` json DEFAULT NULL,
  `motivo_alteracao` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_auditoria` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `fk_auditoria_movimentacao` (`id_movimentacao`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  KEY `idx_data_auditoria` (`data_auditoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE HISTÓRICO DE CUSTOS
CREATE TABLE IF NOT EXISTS `tbl_historico_custos` (
  `id_historico_custo` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `custo_anterior` decimal(15,4) DEFAULT 0,
  `custo_atual` decimal(15,4) DEFAULT 0,
  `tipo_alteracao` enum('entrada','ajuste','reavaliacao') NOT NULL,
  `id_movimentacao` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historico_custo`),
  KEY `fk_historico_custo_material` (`id_material`),
  KEY `fk_historico_custo_filial` (`id_filial`),
  KEY `fk_historico_custo_movimentacao` (`id_movimentacao`),
  KEY `fk_historico_custo_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABELA DE ALERTAS DE ESTOQUE
CREATE TABLE IF NOT EXISTS `tbl_alertas_estoque` (
  `id_alerta` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `tipo_alerta` enum('estoque_baixo','estoque_zerado','vencimento_proximo','vencido','estoque_alto') NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `quantidade_referencia` decimal(15,3) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `dias_vencimento` int(11) DEFAULT NULL,
  `status` enum('ativo','resolvido','ignorado') NOT NULL DEFAULT 'ativo',
  `prioridade` enum('baixa','media','alta','critica') NOT NULL DEFAULT 'media',
  `mensagem` text DEFAULT NULL,
  `id_usuario_responsavel` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_resolucao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alerta`),
  KEY `fk_alerta_material` (`id_material`),
  KEY `fk_alerta_filial` (`id_filial`),
  KEY `fk_alerta_usuario` (`id_usuario_responsavel`),
  KEY `idx_tipo_alerta` (`tipo_alerta`),
  KEY `idx_status_alerta` (`status`),
  KEY `idx_prioridade_alerta` (`prioridade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABELA DE CONFIGURAÇÕES DE ESTOQUE
CREATE TABLE IF NOT EXISTS `tbl_configuracoes_estoque` (
  `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `tipo` enum('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
  `categoria` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_configuracao`),
  UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT INTO `tbl_configuracoes_estoque` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('estoque_baixo_percentual', '20', 'Percentual para considerar estoque baixo', 'integer', 'alertas'),
('dias_vencimento_alerta', '30', 'Dias antes do vencimento para alertar', 'integer', 'alertas'),
('custo_medio_metodo', 'ponderado', 'Método de cálculo do custo médio (ponderado, fifo, lifo)', 'string', 'custos'),
('movimentacao_aprovacao_obrigatoria', 'false', 'Se movimentações precisam de aprovação', 'boolean', 'workflow'),
('estoque_negativo_permitido', 'false', 'Se permite estoque negativo', 'boolean', 'estoque'),
('decimal_estoque', '3', 'Número de casas decimais para estoque', 'integer', 'formato'),
('decimal_valor', '4', 'Número de casas decimais para valores', 'integer', 'formato');

-- 8. ADICIONAR FOREIGN KEYS APÓS CRIAR TODAS AS TABELAS

-- Foreign keys para tbl_estoque_filial
ALTER TABLE `tbl_estoque_filial` 
ADD CONSTRAINT `fk_estoque_filial_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_estoque_filial_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE;

-- Foreign keys para tbl_lotes
ALTER TABLE `tbl_lotes` 
ADD CONSTRAINT `fk_lote_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_lote_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE;

-- Foreign keys para tbl_movimentacoes
ALTER TABLE `tbl_movimentacoes` 
ADD CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_lote` FOREIGN KEY (`id_lote`) REFERENCES `tbl_lotes` (`id_lote`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT,
ADD CONSTRAINT `fk_movimentacao_usuario_executor` FOREIGN KEY (`id_usuario_executor`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_auditoria_movimentacoes
ALTER TABLE `tbl_auditoria_movimentacoes` 
ADD CONSTRAINT `fk_auditoria_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_historico_custos
ALTER TABLE `tbl_historico_custos` 
ADD CONSTRAINT `fk_historico_custo_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_historico_custo_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_historico_custo_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_historico_custo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT;

-- Foreign keys para tbl_alertas_estoque
ALTER TABLE `tbl_alertas_estoque` 
ADD CONSTRAINT `fk_alerta_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_alerta_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_alerta_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL;

-- 9. ÍNDICES ADICIONAIS PARA PERFORMANCE
CREATE INDEX `idx_movimentacao_data_tipo` ON `tbl_movimentacoes` (`data_movimentacao`, `tipo_movimentacao`);
CREATE INDEX `idx_movimentacao_material_data` ON `tbl_movimentacoes` (`id_material`, `data_movimentacao`);
CREATE INDEX `idx_lote_material_validade` ON `tbl_lotes` (`id_material`, `data_validade`, `status`);
CREATE INDEX `idx_estoque_filial_atual` ON `tbl_estoque_filial` (`estoque_atual`, `id_filial`);
CREATE INDEX `idx_alerta_status_prioridade` ON `tbl_alertas_estoque` (`status`, `prioridade`, `tipo_alerta`);

-- 10. VIEWS PARA CONSULTAS

-- View para estoque atual por filial
CREATE OR REPLACE VIEW `vw_estoque_atual` AS
SELECT 
    m.id_material,
    m.codigo as codigo_material,
    m.nome as nome_material,
    m.unidade,
    f.id_filial,
    f.nome_filial,
    COALESCE(ef.estoque_atual, 0) as estoque_atual,
    COALESCE(ef.estoque_minimo, 0) as estoque_minimo,
    COALESCE(ef.estoque_maximo, 0) as estoque_maximo,
    COALESCE(ef.custo_medio, 0) as custo_medio,
    ef.localizacao,
    ef.ultima_movimentacao,
    CASE 
        WHEN COALESCE(ef.estoque_atual, 0) <= COALESCE(ef.estoque_minimo, 0) THEN 'baixo'
        WHEN COALESCE(ef.estoque_atual, 0) = 0 THEN 'zerado'
        WHEN COALESCE(ef.estoque_atual, 0) >= COALESCE(ef.estoque_maximo, 999999) THEN 'alto'
        ELSE 'normal'
    END as status_estoque
FROM tbl_materiais m
CROSS JOIN tbl_filiais f
LEFT JOIN tbl_estoque_filial ef ON m.id_material = ef.id_material AND f.id_filial = ef.id_filial
WHERE m.ativo = 1 AND f.filial_ativa = 1;

-- View para movimentações com detalhes
CREATE OR REPLACE VIEW `vw_movimentacoes_detalhadas` AS
SELECT 
    m.*,
    mat.codigo as codigo_material,
    mat.nome as nome_material,
    mat.unidade as unidade_material,
    l.numero_lote,
    l.data_validade,
    f_origem.nome_filial as filial_origem,
    f_destino.nome_filial as filial_destino,
    forn.razao_social as nome_fornecedor,
    cli.nome_cliente,
    u_sol.nome_completo as nome_usuario_solicitante,
    u_exec.nome_completo as nome_usuario_executor
FROM tbl_movimentacoes m
LEFT JOIN tbl_materiais mat ON m.id_material = mat.id_material
LEFT JOIN tbl_lotes l ON m.id_lote = l.id_lote
LEFT JOIN tbl_filiais f_origem ON m.id_filial_origem = f_origem.id_filial
LEFT JOIN tbl_filiais f_destino ON m.id_filial_destino = f_destino.id_filial
LEFT JOIN tbl_fornecedores forn ON m.id_fornecedor = forn.id_fornecedor
LEFT JOIN tbl_clientes cli ON m.id_cliente = cli.id_cliente
LEFT JOIN tbl_usuarios u_sol ON m.id_usuario_solicitante = u_sol.id_usuario
LEFT JOIN tbl_usuarios u_exec ON m.id_usuario_executor = u_exec.id_usuario; 



-- Tabela de movimentações de estoque
CREATE TABLE IF NOT EXISTS `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste') NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `estoque_anterior` decimal(10,2) NOT NULL DEFAULT 0,
  `estoque_atual` decimal(10,2) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `id_filial_origem` int(11) DEFAULT NULL,
  `id_filial_destino` int(11) DEFAULT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimentacao`),
  UNIQUE KEY `numero_movimentacao` (`numero_movimentacao`),
  KEY `fk_movimentacao_material` (`id_material`),
  KEY `fk_movimentacao_filial_origem` (`id_filial_origem`),
  KEY `fk_movimentacao_filial_destino` (`id_filial_destino`),
  KEY `fk_movimentacao_fornecedor` (`id_fornecedor`),
  KEY `fk_movimentacao_usuario` (`id_usuario`),
  CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE RESTRICT,
  CONSTRAINT `fk_movimentacao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de clientes (se não existir)
CREATE TABLE IF NOT EXISTS `tbl_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_movimentacoes` (`numero_movimentacao`, `tipo_movimentacao`, `id_material`, `quantidade`, `estoque_anterior`, `estoque_atual`, `valor_unitario`, `valor_total`, `id_filial_origem`, `id_filial_destino`, `id_fornecedor`, `documento`, `observacoes`, `id_usuario`) VALUES
('MOV-001', 'entrada', 1, 50.00, 0.00, 50.00, 1299.99, 64999.50, NULL, 1, 1, 'NF-123456', 'Entrada de smartphones Galaxy A54', 1),
('MOV-002', 'saida', 2, 5.00, 25.00, 20.00, 299.99, 1499.95, 1, NULL, NULL, 'PED-789012', 'Saída para cliente XYZ', 1),
('MOV-003', 'entrada', 3, 15.00, 0.00, 15.00, 2899.99, 43499.85, NULL, 1, 2, 'NF-654321', 'Entrada de notebooks Lenovo', 1),
('MOV-004', 'saida', 4, 12.00, 30.00, 18.00, 149.99, 1799.88, 1, NULL, NULL, 'VEN-345678', 'Venda para loja física', 1),
('MOV-005', 'transferencia', 5, 8.00, 15.00, 7.00, 199.99, 1599.92, 1, 2, NULL, 'TRANS-001', 'Transferência entre filiais', 1),
('MOV-006', 'ajuste', 6, -3.00, 20.00, 17.00, 89.99, 269.97, 1, NULL, NULL, 'AJU-001', 'Ajuste de inventário', 1);

INSERT INTO `tbl_clientes` (`nome_cliente`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`) VALUES
('Cliente XYZ', 'contato@xyz.com', '(11) 99999-9999', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567'),
('Loja Física', 'loja@empresa.com', '(11) 88888-8888', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100'); 



-- Tabela de Pedidos de Compra
CREATE TABLE IF NOT EXISTS `tbl_pedidos_compra` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(20) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `data_pedido` date NOT NULL,
  `data_entrega_prevista` date DEFAULT NULL,
  `status` enum('pendente','aprovado','em_producao','enviado','recebido','cancelado') DEFAULT 'pendente',
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text,
  `id_usuario_criacao` int(11) DEFAULT NULL,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `id_fornecedor` (`id_fornecedor`),
  KEY `id_filial` (`id_filial`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Pedido de Compra
CREATE TABLE IF NOT EXISTS `tbl_itens_pedido_compra` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_pedidos_compra` (`numero_pedido`, `id_fornecedor`, `id_filial`, `data_pedido`, `data_entrega_prevista`, `status`, `valor_total`, `observacoes`, `id_usuario_criacao`) VALUES
('PED-2024-001', 1, 1, '2024-01-15', '2024-01-25', 'aprovado', 12500.00, 'Pedido de materiais de escritório', 1),
('PED-2024-002', 2, 1, '2024-01-16', '2024-01-30', 'em_producao', 8900.00, 'Equipamentos de informática', 1),
('PED-2024-003', 1, 2, '2024-01-17', '2024-02-05', 'pendente', 15600.00, 'Materiais de limpeza', 2),
('PED-2024-004', 3, 1, '2024-01-18', '2024-01-28', 'enviado', 7200.00, 'Ferramentas e equipamentos', 1),
('PED-2024-005', 2, 2, '2024-01-19', '2024-02-10', 'pendente', 18900.00, 'Materiais de construção', 2);

INSERT INTO `tbl_itens_pedido_compra` (`id_pedido`, `id_material`, `quantidade`, `preco_unitario`, `valor_total`, `observacoes`) VALUES
(1, 1, 50.000, 150.00, 7500.00, 'Papel A4'),
(1, 2, 100.000, 50.00, 5000.00, 'Canetas'),
(2, 3, 10.000, 890.00, 8900.00, 'Notebooks'),
(3, 4, 200.000, 78.00, 15600.00, 'Produtos de limpeza'),
(4, 5, 20.000, 360.00, 7200.00, 'Ferramentas'),
(5, 6, 30.000, 630.00, 18900.00, 'Materiais de construção'); 



-- Tabela de Filiais
CREATE TABLE IF NOT EXISTS `tbl_filiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `tipo` enum('matriz','filial') NOT NULL DEFAULT 'filial',
  `cnpj` varchar(18) DEFAULT NULL,
  `inscricao_estadual` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `responsavel` varchar(255) DEFAULT NULL,
  `email_responsavel` varchar(255) DEFAULT NULL,
  `telefone_responsavel` varchar(20) DEFAULT NULL,
  `data_abertura` date DEFAULT NULL,
  `status` enum('ativa','inativa') NOT NULL DEFAULT 'ativa',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_status` (`status`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_cidade` (`cidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO `tbl_filiais` (`codigo`, `nome`, `tipo`, `cnpj`, `endereco`, `cidade`, `estado`, `telefone`, `email`, `responsavel`, `email_responsavel`, `status`, `observacoes`) VALUES
('MAT001', 'Matriz - São Paulo', 'matriz', '12.345.678/0001-90', 'Av. Paulista, 1000', 'São Paulo', 'SP', '(11) 3000-0000', 'contato@gruposorrisos.com.br', 'Maria Silva', 'maria@gruposorrisos.com.br', 'ativa', 'Sede principal da empresa'),
('FIL001', 'Filial - Rio de Janeiro', 'filial', '12.345.678/0002-71', 'Rua do Ouvidor, 150', 'Rio de Janeiro', 'RJ', '(21) 2500-0000', 'rj@gruposorrisos.com.br', 'João Santos', 'joao@gruposorrisos.com.br', 'ativa', 'Filial da capital carioca'),
('FIL002', 'Filial - Belo Horizonte', 'filial', '12.345.678/0003-52', 'Av. Afonso Pena, 500', 'Belo Horizonte', 'MG', '(31) 3200-0000', 'bh@gruposorrisos.com.br', 'Ana Costa', 'ana@gruposorrisos.com.br', 'ativa', 'Filial de Minas Gerais'),
('FIL003', 'Filial - Brasília', 'filial', '12.345.678/0004-33', 'SQS 115, Bloco A', 'Brasília', 'DF', '(61) 3300-0000', 'bsb@gruposorrisos.com.br', 'Carlos Oliveira', 'carlos@gruposorrisos.com.br', 'inativa', 'Filial temporariamente inativa'); 






-- Tabela principal de tickets
CREATE TABLE IF NOT EXISTS `tbl_tickets` (
    `id_ticket` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_ticket` VARCHAR(20) UNIQUE NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `id_categoria` INT,
    `id_prioridade` INT,
    `id_status` INT,
    `id_usuario_solicitante` INT,
    `id_usuario_atribuido` INT,
    `id_filial` INT,
    `data_abertura` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `data_fechamento` DATETIME NULL,
    `tempo_resolucao` INT NULL COMMENT 'Tempo em minutos',
    `avaliacao` TINYINT NULL COMMENT '1-5 estrelas',
    `comentario_avaliacao` TEXT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`id_status`),
    INDEX `idx_prioridade` (`id_prioridade`),
    INDEX `idx_usuario_solicitante` (`id_usuario_solicitante`),
    INDEX `idx_usuario_atribuido` (`id_usuario_atribuido`),
    INDEX `idx_filial` (`id_filial`),
    INDEX `idx_data_abertura` (`data_abertura`)
);

-- Tabela de categorias de tickets
CREATE TABLE IF NOT EXISTS `tbl_categorias_ticket` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_categoria` VARCHAR(100) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#007bff',
    `icone` VARCHAR(50) DEFAULT 'bi-tag',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de prioridades
CREATE TABLE IF NOT EXISTS `tbl_prioridades_ticket` (
    `id_prioridade` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_prioridade` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-flag',
    `tempo_esperado` INT DEFAULT 1440 COMMENT 'Tempo em minutos (24h padrão)',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de status
CREATE TABLE IF NOT EXISTS `tbl_status_ticket` (
    `id_status` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_status` VARCHAR(50) NOT NULL,
    `descricao` TEXT,
    `cor` VARCHAR(7) DEFAULT '#6c757d',
    `icone` VARCHAR(50) DEFAULT 'bi-circle',
    `is_final` TINYINT(1) DEFAULT 0 COMMENT 'Se é um status final (fechado)',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de comentários/atualizações do ticket
CREATE TABLE IF NOT EXISTS `tbl_comentarios_ticket` (
    `id_comentario` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ticket` INT NOT NULL,
    `id_usuario` INT NOT NULL,
    `comentario` TEXT NOT NULL,
    `tipo` ENUM('comentario', 'status', 'atribuicao', 'prioridade') DEFAULT 'comentario',
    `dados_anteriores` JSON NULL COMMENT 'Dados anteriores para histórico',
    `dados_novos` JSON NULL COMMENT 'Dados novos para histórico',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`),
    INDEX `idx_tipo` (`tipo`)
);

-- Tabela de anexos do ticket
CREATE TABLE IF NOT EXISTS `tbl_anexos_ticket` (
    `id_anexo` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ticket` INT NOT NULL,
    `id_usuario` INT NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `nome_original` VARCHAR(255) NOT NULL,
    `tipo_arquivo` VARCHAR(100),
    `tamanho` INT COMMENT 'Tamanho em bytes',
    `caminho` VARCHAR(500) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`id_ticket`),
    INDEX `idx_usuario` (`id_usuario`)
);

-- Inserir dados padrão para categorias
INSERT INTO `tbl_categorias_ticket` (`nome_categoria`, `descricao`, `cor`, `icone`) VALUES
('Suporte Técnico', 'Problemas técnicos e suporte', '#dc3545', 'bi-tools'),
('Sistema', 'Problemas com o sistema', '#007bff', 'bi-gear'),
('Estoque', 'Problemas relacionados ao estoque', '#28a745', 'bi-box-seam'),
('Financeiro', 'Problemas financeiros', '#ffc107', 'bi-currency-dollar'),
('RH', 'Recursos humanos', '#17a2b8', 'bi-people'),
('Outros', 'Outras categorias', '#6c757d', 'bi-three-dots');

-- Inserir dados padrão para prioridades
INSERT INTO `tbl_prioridades_ticket` (`nome_prioridade`, `descricao`, `cor`, `icone`, `tempo_esperado`) VALUES
('Baixa', 'Pode ser resolvido em até 72h', '#6c757d', 'bi-flag', 4320),
('Média', 'Deve ser resolvido em até 24h', '#ffc107', 'bi-flag-fill', 1440),
('Alta', 'Deve ser resolvido em até 4h', '#fd7e14', 'bi-exclamation-triangle', 240),
('Crítica', 'Deve ser resolvido imediatamente', '#dc3545', 'bi-exclamation-triangle-fill', 60);

-- Inserir dados padrão para status
INSERT INTO `tbl_status_ticket` (`nome_status`, `descricao`, `cor`, `icone`, `is_final`) VALUES
('Aberto', 'Ticket recém aberto', '#007bff', 'bi-circle-fill', 0),
('Em Análise', 'Ticket sendo analisado', '#ffc107', 'bi-clock', 0),
('Em Andamento', 'Ticket sendo trabalhado', '#17a2b8', 'bi-play-circle', 0),
('Aguardando Cliente', 'Aguardando resposta do cliente', '#6c757d', 'bi-pause-circle', 0),
('Aguardando Terceiros', 'Aguardando terceiros', '#fd7e14', 'bi-people', 0),
('Resolvido', 'Ticket resolvido', '#28a745', 'bi-check-circle', 1),
('Fechado', 'Ticket fechado', '#6c757d', 'bi-x-circle', 1),
('Cancelado', 'Ticket cancelado', '#dc3545', 'bi-x-circle-fill', 1);

-- Adicionar chaves estrangeiras
ALTER TABLE `tbl_tickets` 
ADD CONSTRAINT `fk_tickets_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias_ticket`(`id_categoria`),
ADD CONSTRAINT `fk_tickets_prioridade` FOREIGN KEY (`id_prioridade`) REFERENCES `tbl_prioridades_ticket`(`id_prioridade`),
ADD CONSTRAINT `fk_tickets_status` FOREIGN KEY (`id_status`) REFERENCES `tbl_status_ticket`(`id_status`),
ADD CONSTRAINT `fk_tickets_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios`(`id_usuario`),
ADD CONSTRAINT `fk_tickets_usuario_atribuido` FOREIGN KEY (`id_usuario_atribuido`) REFERENCES `tbl_usuarios`(`id_usuario`),
ADD CONSTRAINT `fk_tickets_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais`(`id_filial`);

ALTER TABLE `tbl_comentarios_ticket` 
ADD CONSTRAINT `fk_comentarios_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets`(`id_ticket`),
ADD CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`);

ALTER TABLE `tbl_anexos_ticket` 
ADD CONSTRAINT `fk_anexos_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets`(`id_ticket`),
ADD CONSTRAINT `fk_anexos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios`(`id_usuario`); 


