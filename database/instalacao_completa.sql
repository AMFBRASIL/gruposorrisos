/*
SQLyog Community v13.2.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - gruposorrisos
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `tbl_alertas_estoque`;

CREATE TABLE `tbl_alertas_estoque` (
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
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_resolucao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_alerta`),
  KEY `fk_alerta_material` (`id_material`),
  KEY `fk_alerta_filial` (`id_filial`),
  KEY `fk_alerta_usuario` (`id_usuario_responsavel`),
  KEY `idx_tipo_alerta` (`tipo_alerta`),
  KEY `idx_status_alerta` (`status`),
  KEY `idx_prioridade_alerta` (`prioridade`),
  KEY `idx_alerta_status_prioridade` (`status`,`prioridade`,`tipo_alerta`),
  CONSTRAINT `fk_alerta_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
  CONSTRAINT `fk_alerta_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE,
  CONSTRAINT `fk_alerta_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_alertas_estoque` */

insert  into `tbl_alertas_estoque`(`id_alerta`,`id_material`,`id_filial`,`tipo_alerta`,`quantidade_atual`,`quantidade_referencia`,`data_vencimento`,`dias_vencimento`,`status`,`prioridade`,`mensagem`,`id_usuario_responsavel`,`data_criacao`,`data_resolucao`,`data_atualizacao`) values 
(3,5,1,'estoque_alto',45.000,50.000,NULL,NULL,'ativo','baixa','Estoque de furadeiras acima do máximo',NULL,'2025-07-25 16:40:21',NULL,'2025-07-25 16:40:21'),
(4,3,1,'vencido',0.000,NULL,'2025-02-01',-174,'ativo','alta','Lote vencido há 174 dias',NULL,'2025-07-25 16:49:24',NULL,'2025-07-25 16:49:24'),
(5,4,1,'vencido',0.000,NULL,'2025-02-15',-160,'ativo','alta','Lote vencido há 160 dias',NULL,'2025-07-25 16:49:24',NULL,'2025-07-25 16:49:24'),
(6,6,1,'vencido',0.000,NULL,'2025-03-15',-132,'ativo','alta','Lote vencido há 132 dias',NULL,'2025-07-25 16:49:24',NULL,'2025-07-25 16:49:24'),
(7,7,1,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS PETROLINA LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(8,7,14,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO LUCAS LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(9,7,15,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO BAHIA SORRISOS PETROLINA LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(10,7,16,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS CASA NOVA BAHIA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(11,7,17,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO BAHIA SORRISOS PAULO AFONSO LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(12,7,18,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(13,7,19,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS GARANHUNS LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(14,7,20,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO BAHIA SORRISOS RIBEIRA POMBAL LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(15,7,21,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS SANTA CRUZ',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(16,7,22,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO BAHIA SORRISOS EUCLIDES DA CUNHA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(17,7,23,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO BAHIA SORRISOS BTN LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(18,7,24,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial CENTRO ODONTOLOGICO PERNAMBUCO ARCOVERDE LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50'),
(19,7,25,'estoque_baixo',9.000,9.000,NULL,NULL,'ativo','alta','Produto MATERIAL TESTE está com estoque baixo (9.000) na filial GS SERVICOS ADM LTDA',NULL,'2025-08-11 18:19:50',NULL,'2025-08-11 18:19:50');

/*Table structure for table `tbl_auditoria_movimentacoes` */

DROP TABLE IF EXISTS `tbl_auditoria_movimentacoes`;

CREATE TABLE `tbl_auditoria_movimentacoes` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimentacao` int(11) NOT NULL,
  `acao` enum('criacao','alteracao','cancelamento','estorno') NOT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `motivo_alteracao` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_auditoria` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `fk_auditoria_movimentacao` (`id_movimentacao`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  KEY `idx_data_auditoria` (`data_auditoria`),
  CONSTRAINT `fk_auditoria_movimentacao` FOREIGN KEY (`id_movimentacao`) REFERENCES `tbl_movimentacoes` (`id_movimentacao`) ON DELETE CASCADE,
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_auditoria_movimentacoes` */

insert  into `tbl_auditoria_movimentacoes`(`id_auditoria`,`id_movimentacao`,`acao`,`dados_anteriores`,`dados_novos`,`motivo_alteracao`,`id_usuario`,`data_auditoria`,`ip_usuario`,`user_agent`) values 
(8,13,'criacao',NULL,'{\"numero_movimentacao\": \"MOV-000001\", \"tipo_movimentacao\": \"entrada\", \"quantidade\": 100.000, \"valor_total\": 1590.0000, \"status_movimentacao\": \"executada\"}',NULL,1,'2025-08-11 22:08:43',NULL,NULL),
(9,14,'criacao',NULL,'{\"numero_movimentacao\": \"MOV-000002\", \"tipo_movimentacao\": \"transferencia\", \"quantidade\": 10.000, \"valor_total\": 159.0000, \"status_movimentacao\": \"executada\"}',NULL,1,'2025-08-11 22:12:44',NULL,NULL);

/*Table structure for table `tbl_categorias` */

DROP TABLE IF EXISTS `tbl_categorias`;

CREATE TABLE `tbl_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_pai` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`),
  KEY `categoria_pai` (`categoria_pai`),
  CONSTRAINT `fk_categorias_pai` FOREIGN KEY (`categoria_pai`) REFERENCES `tbl_categorias` (`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_categorias` */

insert  into `tbl_categorias`(`id_categoria`,`nome_categoria`,`descricao`,`categoria_pai`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'Eletrônicos','Produtos eletrônicos e tecnológicos',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'Informática','Equipamentos e acessórios de informática',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,'Escritório','Materiais de escritório',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(4,'Limpeza','Produtos de limpeza',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(5,'Manutenção','Materiais de manutenção',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(6,'Alimentação','Produtos alimentícios',NULL,1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(7,'EPI','EPI',NULL,1,'2025-08-07 09:17:03','2025-08-07 09:17:03'),
(8,'PROTESE','PROTESE',NULL,1,'2025-08-07 10:27:13','2025-08-07 10:27:13');

/*Table structure for table `tbl_categorias_ticket` */

DROP TABLE IF EXISTS `tbl_categorias_ticket`;

CREATE TABLE `tbl_categorias_ticket` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#007bff',
  `icone` varchar(50) DEFAULT 'bi-tag',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tbl_categorias_ticket` */

insert  into `tbl_categorias_ticket`(`id_categoria`,`nome_categoria`,`descricao`,`cor`,`icone`,`ativo`,`created_at`,`updated_at`) values 
(1,'Suporte Técnico','Problemas técnicos e suporte','#dc3545','bi-tools',1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(2,'Sistema','Problemas com o sistema','#007bff','bi-gear',1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(3,'Estoque','Problemas relacionados ao estoque','#28a745','bi-box-seam',1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(4,'Financeiro','Problemas financeiros','#ffc107','bi-currency-dollar',1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(5,'RH','Recursos humanos','#17a2b8','bi-people',1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(6,'Outros','Outras categorias','#6c757d','bi-three-dots',1,'2025-07-25 16:39:56','2025-07-25 16:39:56');

/*Table structure for table `tbl_clientes` */

DROP TABLE IF EXISTS `tbl_clientes`;

CREATE TABLE `tbl_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_clientes` */

insert  into `tbl_clientes`(`id_cliente`,`nome_cliente`,`email`,`telefone`,`endereco`,`cidade`,`estado`,`cep`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'Cliente A','clientea@email.com','(11) 4444-4444','Rua Cliente A, 100','São Paulo','SP','01234-567',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'Cliente B','clienteb@email.com','(21) 5555-5555','Av. Cliente B, 200','Rio de Janeiro','RJ','20000-000',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,'Cliente C','clientec@email.com','(31) 6666-6666','Rua Cliente C, 300','Belo Horizonte','MG','30000-000',1,'2025-07-25 16:40:21','2025-07-25 16:40:21');

/*Table structure for table `tbl_comentarios_ticket` */

DROP TABLE IF EXISTS `tbl_comentarios_ticket`;

CREATE TABLE `tbl_comentarios_ticket` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `tipo` enum('comentario','status','atribuicao','prioridade') DEFAULT 'comentario',
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comentario`),
  KEY `idx_ticket` (`id_ticket`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_tipo` (`tipo`),
  CONSTRAINT `fk_comentarios_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tbl_tickets` (`id_ticket`),
  CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tbl_comentarios_ticket` */

insert  into `tbl_comentarios_ticket`(`id_comentario`,`id_ticket`,`id_usuario`,`comentario`,`tipo`,`dados_anteriores`,`dados_novos`,`created_at`) values 
(8,4,1,'TESTANDO TICKET NOVO.','comentario',NULL,NULL,'2025-07-29 15:21:39');

/*Table structure for table `tbl_configuracoes` */

DROP TABLE IF EXISTS `tbl_configuracoes`;

CREATE TABLE `tbl_configuracoes` (
  `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json','email','telefone','moeda','fuso_horario') DEFAULT 'texto',
  `categoria` varchar(50) DEFAULT 'geral',
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_configuracao`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_configuracoes` */

insert  into `tbl_configuracoes`(`id_configuracao`,`chave`,`valor`,`descricao`,`tipo`,`categoria`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'empresa_nome','Grupo Sorrisos Ltda','Nome da empresa','texto','empresa',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(2,'empresa_email','contato@gruposorrisos.com','E-mail principal da empresa','email','empresa',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(3,'empresa_telefone','(11) 99999-9999','Telefone principal da empresa','telefone','empresa',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(4,'empresa_moeda','BRL','Moeda padrão do sistema','moeda','empresa',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(5,'empresa_fuso','America/Sao_Paulo','Fuso horário padrão','fuso_horario','empresa',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(6,'notifica_email','1','Ativar notificações por e-mail','booleano','notificacoes',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(7,'notifica_pagamentos','1','Notificar sobre pagamentos realizados','booleano','notificacoes',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(8,'notifica_vencimentos','1','Alertas de contas próximas ao vencimento','booleano','notificacoes',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(9,'notifica_relatorios','1','Envio automático de relatórios mensais','booleano','notificacoes',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(10,'backup_automatico','1','Ativar backup automático','booleano','sistema',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(11,'backup_intervalo','diario','Intervalo do backup (diario, semanal, mensal)','texto','sistema',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(12,'backup_historico','12','Manter histórico de backup em meses','numero','sistema',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(13,'seguranca_2fa','0','Ativar autenticação em duas etapas','booleano','seguranca',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(14,'sessao_expira','120','Tempo de expiração da sessão em minutos','numero','seguranca',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(15,'log_auditoria','1','Ativar log de auditoria','booleano','seguranca',1,'2025-08-18 20:37:34','2025-08-18 20:42:45'),
(16,'estoque_alerta_baixo','1','Ativar alertas de estoque baixo','booleano','estoque',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(17,'estoque_alerta_zerado','1','Ativar alertas de estoque zerado','booleano','estoque',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(18,'estoque_alerta_excedido','1','Ativar alertas de estoque excedido','booleano','estoque',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(19,'estoque_dias_antecedencia','7','Dias de antecedência para alertas de vencimento','numero','estoque',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(20,'relatorio_paginacao','20','Itens por página nos relatórios','numero','relatorios',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(21,'relatorio_formato_padrao','pdf','Formato padrão dos relatórios','texto','relatorios',1,'2025-08-18 20:37:34','2025-08-18 20:37:34'),
(22,'relatorio_auto_gerar','0','Gerar relatórios automaticamente','booleano','relatorios',1,'2025-08-18 20:37:34','2025-08-18 20:37:34');

/*Table structure for table `tbl_configuracoes_estoque` */

DROP TABLE IF EXISTS `tbl_configuracoes_estoque`;

CREATE TABLE `tbl_configuracoes_estoque` (
  `id_configuracao` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `tipo` enum('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
  `categoria` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_configuracao`),
  UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_configuracoes_estoque` */

insert  into `tbl_configuracoes_estoque`(`id_configuracao`,`chave`,`valor`,`descricao`,`tipo`,`categoria`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'estoque_baixo_percentual','20','Percentual para considerar estoque baixo','integer','alertas',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(2,'dias_vencimento_alerta','30','Dias antes do vencimento para alertar','integer','alertas',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(3,'custo_medio_metodo','ponderado','Método de cálculo do custo médio','string','custos',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(4,'movimentacao_aprovacao_obrigatoria','false','Se movimentações precisam de aprovação','boolean','workflow',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(5,'estoque_negativo_permitido','false','Se permite estoque negativo','boolean','estoque',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(6,'decimal_estoque','3','Número de casas decimais para estoque','integer','formato',1,'2025-07-25 19:39:56','2025-07-25 19:39:56'),
(7,'decimal_valor','4','Número de casas decimais para valores','integer','formato',1,'2025-07-25 19:39:56','2025-07-25 19:39:56');

/*Table structure for table `tbl_estoque_filial` */

DROP TABLE IF EXISTS `tbl_estoque_filial`;

CREATE TABLE `tbl_estoque_filial` (
  `id_estoque_filial` int(11) NOT NULL AUTO_INCREMENT,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `estoque_atual` decimal(15,3) NOT NULL DEFAULT 0.000,
  `estoque_minimo` decimal(15,3) DEFAULT 0.000,
  `estoque_maximo` decimal(15,3) DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL COMMENT 'Localização física',
  `custo_medio` decimal(15,4) DEFAULT 0.0000,
  `ultima_movimentacao` datetime DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_estoque_filial`),
  UNIQUE KEY `uk_material_filial` (`id_material`,`id_filial`),
  KEY `fk_estoque_filial_material` (`id_material`),
  KEY `fk_estoque_filial_filial` (`id_filial`),
  KEY `idx_estoque_filial_atual` (`estoque_atual`,`id_filial`),
  CONSTRAINT `fk_estoque_filial_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
  CONSTRAINT `fk_estoque_filial_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_estoque_filial` */

insert  into `tbl_estoque_filial`(`id_estoque_filial`,`id_material`,`id_filial`,`estoque_atual`,`estoque_minimo`,`estoque_maximo`,`localizacao`,`custo_medio`,`ultima_movimentacao`,`data_criacao`,`data_atualizacao`) values 
(1,1,1,83.000,10.000,100.000,'Prateleira A1',759.9398,'2025-07-25 17:34:05','2025-07-25 16:40:21','2025-07-25 17:34:05'),
(2,2,1,15.000,5.000,50.000,'Prateleira B2',2800.0000,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,3,1,100.000,20.000,200.000,'Prateleira C3',15.0000,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(4,4,1,150.000,30.000,300.000,'Prateleira D4',8.5000,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(5,5,1,20.000,5.000,50.000,'Prateleira E5',190.0000,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(6,6,1,114.000,25.000,250.000,'Prateleira F6',15.0000,'2025-07-25 17:34:50','2025-07-25 16:40:21','2025-07-25 17:34:50'),
(12,12,23,110.000,0.000,NULL,NULL,0.0000,'2025-08-11 21:58:32','2025-08-11 21:58:32','2025-08-11 21:58:32'),
(13,7,25,0.000,0.000,NULL,NULL,15.9000,'2025-08-11 22:12:44','2025-08-11 22:06:03','2025-08-11 22:12:44'),
(16,7,23,-10.000,0.000,NULL,NULL,0.0000,'2025-08-11 22:12:44','2025-08-11 22:12:44','2025-08-11 22:12:44');

/*Table structure for table `tbl_filiais` */

DROP TABLE IF EXISTS `tbl_filiais`;

CREATE TABLE `tbl_filiais` (
  `id_filial` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_filial` varchar(10) NOT NULL COMMENT 'Código único da filial',
  `nome_filial` varchar(200) NOT NULL COMMENT 'Nome da filial',
  `razao_social` varchar(200) NOT NULL COMMENT 'Razão social da filial',
  `cnpj` varchar(18) DEFAULT NULL COMMENT 'CNPJ da filial',
  `inscricao_estadual` varchar(20) DEFAULT NULL COMMENT 'Inscrição estadual',
  `endereco` text DEFAULT NULL COMMENT 'Endereço completo',
  `cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade',
  `estado` char(2) DEFAULT NULL COMMENT 'Estado',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP',
  `telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone',
  `email` varchar(150) DEFAULT NULL COMMENT 'E-mail',
  `responsavel` varchar(200) DEFAULT NULL COMMENT 'Responsável',
  `tipo_filial` enum('matriz','filial','polo') DEFAULT 'filial',
  `filial_ativa` tinyint(1) DEFAULT 1 COMMENT 'Status ativo/inativo',
  `data_inauguracao` date DEFAULT NULL COMMENT 'Data de inauguração',
  `observacoes` text DEFAULT NULL COMMENT 'Observações',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_filial`),
  UNIQUE KEY `codigo_filial` (`codigo_filial`),
  UNIQUE KEY `cnpj` (`cnpj`),
  KEY `idx_filial_ativa` (`filial_ativa`),
  KEY `idx_cidade_estado` (`cidade`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_filiais` */

insert  into `tbl_filiais`(`id_filial`,`codigo_filial`,`nome_filial`,`razao_social`,`cnpj`,`inscricao_estadual`,`endereco`,`cidade`,`estado`,`cep`,`telefone`,`email`,`responsavel`,`tipo_filial`,`filial_ativa`,`data_inauguracao`,`observacoes`,`data_criacao`,`data_atualizacao`) values 
(1,'MATRIZ-01','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS PETROLINA LTDA','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS PETROLINA LTDA','28.495.227/0001-74','','RUA DOM VITAL','Petrolina','PE','56304260','1122564486','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 39.600 | Data de Abertura: 2017-08-24','2025-07-25 16:40:21','2025-07-29 14:05:32'),
(14,'MATRIZ-02','CENTRO ODONTOLOGICO LUCAS LTDA','Centro Odontológico Lucas Petrolina 02 – LTDA','42.221.122/0001-58','','Avenida Souza Filho','Petrolina','PE','56302-370','(75) 2101-2999','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'','2025-07-29 12:14:11','2025-07-29 13:40:24'),
(15,'MATRIZ-03','CENTRO ODONTOLOGICO BAHIA SORRISOS PETROLINA LTDA','Centro Odontológico Bahia Sorrisos Petrolina 03 – LTDA','43.830.311/0001-90','','Avenida Souza Filho','Petrolina','PE','56302-370','(75) 2101-2999','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'','2025-07-29 12:33:15','2025-07-29 13:40:19'),
(16,'MATRIZ-04','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS CASA NOVA BAHIA','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS CASA NOVA BAHIA LTDA','44.503.913/0001-04','','R QUADRA CD','Casa Nova','BA','47300-000','(75) 2101-2999','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'','2025-07-29 13:37:51','2025-07-29 14:07:50'),
(17,'MATRIZ-05','CENTRO ODONTOLOGICO BAHIA SORRISOS PAULO AFONSO LTDA','CENTRO ODONTOLOGICO BAHIA SORRISOS PAULO AFONSO LTDA','41.328.406/0001-85','','Avenida Souza Filho','Petrolina','PE','56302-370','(75) 2101-2999','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'','2025-07-29 13:41:01','2025-07-29 13:46:13'),
(18,'MATRIZ-06','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS LTDA','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS LTDA','35.471.215/0001-75','','Rua Enock Ignácio de Oliveira','Serra Talhada','PE','56903-400','(75) 2101-2999','mariana@gruposorrisos.com.br','Maria Silva','matriz',1,NULL,'','2025-07-29 13:46:45','2025-07-29 13:49:26'),
(19,'MATRIZ-07','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS GARANHUNS LTDA','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS GARANHUNS LTDA','48.788.592/0001-84','','AVENIDA SANTO ANTONIO','Garanhuns','PE','55293000','7521012999','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,NULL,'Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 20.000 | Data de Abertura: 2022-12-01','2025-07-29 14:03:07','2025-07-29 14:05:54'),
(20,'MATRIZ-08','CENTRO ODONTOLOGICO BAHIA SORRISOS RIBEIRA POMBAL LTDA','CENTRO ODONTOLOGICO BAHIA SORRISOS RIBEIRA POMBAL LTDA','45.441.415/0001-39','','AVENIDA EVENCIA BRITO','Ribeira do Pombal','BA','48400000','7521012999','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,NULL,'Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 30.000 | Data de Abertura: 2022-02-24','2025-07-29 14:03:43','2025-07-29 14:06:07'),
(21,'MATRIZ-09','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS SANTA CRUZ','CENTRO ODONTOLOGICO PERNAMBUCO SORRISOS SANTA CRUZ DO CAPIBARIBE LTDA','51.226.094/0001-52','','PRACA DA BANDEIRA','Santa Cruz do Capibaribe','PE','55192055','7521012999','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,NULL,'Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 20.000 | Data de Abertura: 2023-06-28','2025-07-29 14:06:40','2025-07-29 14:07:58'),
(22,'MATRIZ-10','CENTRO ODONTOLOGICO BAHIA SORRISOS EUCLIDES DA CUNHA','CENTRO ODONTOLOGICO BAHIA SORRISOS EUCLIDES DA CUNHA LTDA','55.616.172/0001-86','','AVENIDA RUY BARBOSA','Euclides da Cunha','BA','48500000','8738622196','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,'0000-00-00','Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 30.000 | Data de Abertura: 2024-06-20','2025-07-29 14:07:37','2025-07-29 14:07:37'),
(23,'MATRIZ-11','CENTRO ODONTOLOGICO BAHIA SORRISOS BTN LTDA','CENTRO ODONTOLOGICO BAHIA SORRISOS BTN LTDA','60.307.726/0001-01','','AVENIDA DELMIRO GOUVEIA','Paulo Afonso','BA','48609238','8738622196','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,'0000-00-00','Situação: Ativa | Porte: Empresa de Pequeno Porte | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 20.000 | Data de Abertura: 2025-04-08','2025-07-29 14:08:34','2025-07-29 14:08:34'),
(24,'MATRIZ-12','CENTRO ODONTOLOGICO PERNAMBUCO ARCOVERDE LTDA','CENTRO ODONTOLOGICO PERNAMBUCO ARCOVERDE LTDA','61.490.129/0001-10','','Avenida Coronel Antônio Japiassu','Arcoverde','PE','56506-100','','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,'0000-00-00','','2025-07-29 14:09:59','2025-07-29 14:09:59'),
(25,'MATRIZ-13','GS SERVICOS ADM LTDA','GS SERVICOS ADM LTDA','47.946.361/0001-99','','RUA DOM VITAL','Petrolina','PE','56304260','7191945253','mariana@gruposorrisos.com.br','MARIANA RIBEIRO','matriz',1,NULL,'Situação: Ativa | Porte: Micro Empresa | Natureza Jurídica: Sociedade Empresária Limitada | Capital Social: R$ 25.000 | Data de Abertura: 2022-09-14','2025-07-29 14:10:37','2025-07-29 14:10:43');

/*Table structure for table `tbl_fornecedores` */

DROP TABLE IF EXISTS `tbl_fornecedores`;

CREATE TABLE `tbl_fornecedores` (
  `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT,
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
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_fornecedor`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_fornecedores` */

insert  into `tbl_fornecedores`(`id_fornecedor`,`razao_social`,`nome_fantasia`,`cnpj`,`inscricao_estadual`,`endereco`,`cidade`,`estado`,`cep`,`telefone`,`email`,`contato_principal`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'Fornecedor ABC Ltda','ABC Fornecedor','11.111.111/0001-11',NULL,'Rua das Flores, 123','São Paulo','SP',NULL,'(11) 1111-1111','contato@abc.com.br','João Silva',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'Distribuidora XYZ S/A','XYZ Distribuidora','22.222.222/0001-22',NULL,'Av. Principal, 456','Rio de Janeiro','RJ',NULL,'(21) 2222-2222','vendas@xyz.com.br','Maria Santos',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,'Comercial 123 Ltda','123 Comercial','33.333.333/0001-33',NULL,'Rua Comercial, 789','Belo Horizonte','MG',NULL,'(31) 3333-3333','pedidos@123.com.br','Pedro Costa',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(4,'Fornecedor 222','Fornecedor 222','17.888.722/0001-27','14544455','dedededed','dedede','SP','04707-000','(11) 88484-8488','contato@fornecedor.brs','Andersonnn',1,'2025-08-14 03:18:23','2025-08-13 22:18:23'),
(5,'Fornecedor333','Fornecedor333','17.888.722/0002-02','3233232','rua teste','sao paulo','SP','04707-000','(56) 45445-4554','grupo.amf.center@gmail.com','Anderson mautone',1,'2025-08-14 03:22:02','2025-08-13 22:22:02');

/*Table structure for table `tbl_inventario` */

DROP TABLE IF EXISTS `tbl_inventario`;

CREATE TABLE `tbl_inventario` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `numero_inventario` varchar(20) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `id_usuario_responsavel` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `data_fim` datetime DEFAULT NULL,
  `status` enum('em_andamento','finalizado','cancelado') NOT NULL DEFAULT 'em_andamento',
  `observacoes` text DEFAULT NULL,
  `total_itens` int(11) DEFAULT 0,
  `itens_contados` int(11) DEFAULT 0,
  `itens_divergentes` int(11) DEFAULT 0,
  `valor_total_sistema` decimal(15,4) DEFAULT 0.0000,
  `valor_total_contado` decimal(15,4) DEFAULT 0.0000,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_inventario`),
  UNIQUE KEY `numero_inventario` (`numero_inventario`),
  KEY `fk_inventario_filial` (`id_filial`),
  KEY `fk_inventario_usuario` (`id_usuario_responsavel`),
  KEY `idx_status` (`status`),
  KEY `idx_data_inicio` (`data_inicio`),
  CONSTRAINT `fk_inventario_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_inventario_usuario` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_inventario` */

insert  into `tbl_inventario`(`id_inventario`,`numero_inventario`,`id_filial`,`id_usuario_responsavel`,`data_inicio`,`data_fim`,`status`,`observacoes`,`total_itens`,`itens_contados`,`itens_divergentes`,`valor_total_sistema`,`valor_total_contado`,`data_criacao`,`data_atualizacao`) values 
(6,'INV-2025-001',1,1,'2025-08-01 13:35:43','2025-08-18 20:03:24','finalizado','teste',0,0,0,0.0000,0.0000,'2025-08-01 13:35:43','2025-08-18 20:03:24'),
(9,'INV-2025-004',1,1,'2025-08-18 19:58:48','2025-08-18 20:03:04','finalizado','teste',0,0,0,0.0000,0.0000,'2025-08-18 19:58:48','2025-08-18 20:03:04');

/*Table structure for table `tbl_itens_inventario` */

DROP TABLE IF EXISTS `tbl_itens_inventario`;

CREATE TABLE `tbl_itens_inventario` (
  `id_item_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `id_inventario` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `quantidade_sistema` decimal(15,3) NOT NULL DEFAULT 0.000,
  `quantidade_contada` decimal(15,3) DEFAULT NULL,
  `quantidade_divergencia` decimal(15,3) DEFAULT 0.000,
  `valor_unitario` decimal(15,4) DEFAULT 0.0000,
  `valor_total_sistema` decimal(15,4) DEFAULT 0.0000,
  `valor_total_contado` decimal(15,4) DEFAULT 0.0000,
  `observacoes` text DEFAULT NULL,
  `status_item` enum('pendente','contado','divergente','ajustado') NOT NULL DEFAULT 'pendente',
  `data_contagem` datetime DEFAULT NULL,
  `id_usuario_contador` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_item_inventario`),
  UNIQUE KEY `uk_inventario_material` (`id_inventario`,`id_material`),
  KEY `fk_item_inventario_inventario` (`id_inventario`),
  KEY `fk_item_inventario_material` (`id_material`),
  KEY `fk_item_inventario_usuario` (`id_usuario_contador`),
  KEY `idx_status_item` (`status_item`),
  CONSTRAINT `fk_item_inventario_inventario` FOREIGN KEY (`id_inventario`) REFERENCES `tbl_inventario` (`id_inventario`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_inventario_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`),
  CONSTRAINT `fk_item_inventario_usuario` FOREIGN KEY (`id_usuario_contador`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_itens_inventario` */

insert  into `tbl_itens_inventario`(`id_item_inventario`,`id_inventario`,`id_material`,`quantidade_sistema`,`quantidade_contada`,`quantidade_divergencia`,`valor_unitario`,`valor_total_sistema`,`valor_total_contado`,`observacoes`,`status_item`,`data_contagem`,`id_usuario_contador`,`data_criacao`,`data_atualizacao`) values 
(23,6,1,83.000,83.000,0.000,1299.9900,107899.1700,107899.1700,NULL,'contado','2025-08-18 20:03:24',1,'2025-08-01 13:35:43','2025-08-18 20:03:24'),
(24,6,2,15.000,15.000,0.000,2899.9900,43499.8500,43499.8500,NULL,'contado','2025-08-07 08:57:23',1,'2025-08-01 13:35:43','2025-08-07 08:57:23'),
(25,6,3,100.000,100.000,0.000,15.9900,1599.0000,1599.0000,NULL,'contado','2025-08-07 08:57:30',1,'2025-08-01 13:35:43','2025-08-07 08:57:30'),
(26,6,4,150.000,150.000,0.000,8.9900,1348.5000,1348.5000,NULL,'contado','2025-08-18 18:20:59',1,'2025-08-01 13:35:43','2025-08-18 18:20:59'),
(27,6,5,20.000,20.000,0.000,199.9900,3999.8000,3999.8000,NULL,'contado','2025-08-18 18:21:09',1,'2025-08-01 13:35:43','2025-08-18 18:21:09'),
(28,6,6,114.000,114.000,0.000,12.9900,1480.8600,1480.8600,NULL,'contado','2025-08-18 18:21:14',1,'2025-08-01 13:35:43','2025-08-18 18:21:14'),
(29,9,16,1.000,1.000,0.000,15.9000,15.9000,15.9000,NULL,'contado','2025-08-18 20:03:04',1,'2025-08-18 19:58:48','2025-08-18 20:03:04');

/*Table structure for table `tbl_itens_pedido_compra` */

DROP TABLE IF EXISTS `tbl_itens_pedido_compra`;

CREATE TABLE `tbl_itens_pedido_compra` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_item`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_material` (`id_material`),
  KEY `fk_itens_pedido_fornecedor` (`id_fornecedor`),
  CONSTRAINT `fk_itens_pedido_compra_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`),
  CONSTRAINT `fk_itens_pedido_compra_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbl_pedidos_compra` (`id_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_pedido_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_itens_pedido_compra` */

insert  into `tbl_itens_pedido_compra`(`id_item`,`id_pedido`,`id_material`,`id_fornecedor`,`quantidade`,`preco_unitario`,`valor_total`,`observacoes`,`data_criacao`) values 
(1,1,3,NULL,50.000,15.00,750.00,'Papel A4','2025-07-25 16:40:21'),
(2,1,4,NULL,100.000,8.50,850.00,'Detergente','2025-07-25 16:40:21'),
(3,2,2,NULL,3.000,2899.99,8699.97,'Notebooks','2025-07-25 16:40:21'),
(5,4,5,NULL,20.000,199.99,3999.80,'Furadeiras','2025-07-25 16:40:21'),
(6,5,1,NULL,10.000,10.00,100.00,NULL,'2025-08-07 11:44:19'),
(7,6,7,NULL,30.000,18.90,567.00,NULL,'2025-08-07 11:44:29'),
(8,7,11,NULL,10.000,10.00,100.00,NULL,'2025-08-11 22:58:44'),
(9,7,14,NULL,10.000,15.80,158.00,NULL,'2025-08-11 22:58:44'),
(10,7,15,NULL,10.000,158.99,1589.90,NULL,'2025-08-11 22:58:44');

/*Table structure for table `tbl_logs_sistema` */

DROP TABLE IF EXISTS `tbl_logs_sistema`;

CREATE TABLE `tbl_logs_sistema` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_logs_usuario` (`id_usuario`),
  KEY `idx_logs_filial` (`id_filial`),
  KEY `idx_logs_data` (`data_log`),
  KEY `idx_logs_acao` (`acao`),
  CONSTRAINT `fk_logs_sistema_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE SET NULL,
  CONSTRAINT `fk_logs_sistema_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_logs_sistema` */

insert  into `tbl_logs_sistema`(`id_log`,`id_usuario`,`id_filial`,`acao`,`tabela`,`id_registro`,`dados_anteriores`,`dados_novos`,`ip_usuario`,`user_agent`,`data_log`) values 
(1,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-07-25 21:51:41\"}','::1',NULL,'2025-07-25 16:51:41'),
(2,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-07-28 03:59:54\",\"session_duration\":194893}','::1',NULL,'2025-07-27 22:59:54'),
(3,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-07-28 04:00:17\"}','::1',NULL,'2025-07-27 23:00:17'),
(4,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-07-29 02:02:43\"}','::1',NULL,'2025-07-28 21:02:43'),
(5,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-07-29 15:10:48\"}','::1',NULL,'2025-07-29 10:10:48'),
(6,1,NULL,'CRIAR','tbl_materiais',7,NULL,'{\"codigo\":\"MATERIAL TESTE\",\"codigo_barras\":\"\",\"nome\":\"MATERIAL TESTE\",\"descricao\":\"MATERIAL TESTE\",\"id_categoria\":\"6\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":15.9,\"localizacao_estoque\":\"PRATELEIRA\",\"estoque_atual\":9,\"estoque_minimo\":9,\"estoque_maximo\":10,\"observacoes\":\"teste\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-07-29 19:27:50\"}','::1',NULL,'2025-07-29 14:27:50'),
(7,NULL,NULL,'LOGIN_FAILED',NULL,NULL,NULL,'{\"email\":\"promautone@gmail.com\",\"success\":false,\"timestamp\":\"2025-08-01 18:34:14\"}','::1',NULL,'2025-08-01 13:34:14'),
(8,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-01 18:34:24\"}','::1',NULL,'2025-08-01 13:34:24'),
(9,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-07 13:45:33\"}','::1',NULL,'2025-08-07 08:45:33'),
(10,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-07 13:56:07\"}','192.168.1.76',NULL,'2025-08-07 08:56:07'),
(11,1,NULL,'ATUALIZAR','tbl_materiais',7,NULL,'{\"codigo\":\"MATERIAL TESTEs\",\"codigo_barras\":\"\",\"nome\":\"MATERIAL TESTE\",\"descricao\":\"MATERIAL TESTE\",\"id_categoria\":\"6\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":15.9,\"localizacao_estoque\":\"PRATELEIRA\",\"estoque_atual\":9,\"estoque_minimo\":9,\"estoque_maximo\":10,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 14:16:15\"}','192.168.1.76',NULL,'2025-08-07 09:16:15'),
(12,1,NULL,'ATUALIZAR','tbl_materiais',7,NULL,'{\"codigo\":\"MATERIAL TESTEs\",\"codigo_barras\":\"\",\"nome\":\"MATERIAL TESTE\",\"descricao\":\"MATERIAL TESTE\",\"id_categoria\":\"6\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":15.9,\"localizacao_estoque\":\"PRATELEIRA\",\"estoque_atual\":9,\"estoque_minimo\":9,\"estoque_maximo\":10,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 14:16:17\"}','192.168.1.76',NULL,'2025-08-07 09:16:17'),
(13,1,NULL,'CRIAR','tbl_materiais',8,NULL,'{\"codigo\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-08-07 14:19:16\"}','192.168.1.76',NULL,'2025-08-07 09:19:16'),
(14,1,NULL,'CRIAR','tbl_materiais',9,NULL,'{\"codigo\":\"086077437\",\"codigo_barras\":\"\",\"nome\":\"DENTE S50 ANT.SUP. A1\\tDELARA KULZER\",\"descricao\":\"DENTE S50 ANT.SUP. A1\\tDELARA KULZER\",\"id_categoria\":\"8\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":10,\"localizacao_estoque\":\"DENTES\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":50,\"observacoes\":\"dentes\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-08-07 15:28:07\"}','192.168.1.76',NULL,'2025-08-07 10:28:07'),
(15,1,NULL,'ATUALIZAR','tbl_materiais',8,NULL,'{\"codigo\":\"AVENTAL\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 19:45:10\"}','::1',NULL,'2025-08-07 14:45:10'),
(16,1,NULL,'ATUALIZAR','tbl_materiais',8,NULL,'{\"codigo\":\"43434343\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 19:45:22\"}','::1',NULL,'2025-08-07 14:45:22'),
(17,1,NULL,'ATUALIZAR','tbl_materiais',8,NULL,'{\"codigo\":\"43434343\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 19:45:38\"}','::1',NULL,'2025-08-07 14:45:38'),
(18,1,NULL,'ATUALIZAR','tbl_materiais',8,NULL,'{\"codigo\":\"43434343\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 19:46:00\"}','::1',NULL,'2025-08-07 14:46:00'),
(19,1,NULL,'ATUALIZAR','tbl_materiais',8,NULL,'{\"codigo\":\"43434343\",\"codigo_barras\":\"\",\"nome\":\"AVENTAL PVC IMPERMEAVEL (UND)\",\"descricao\":\"\",\"id_categoria\":\"7\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"preco_unitario\":100,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":10,\"estoque_minimo\":5,\"estoque_maximo\":40,\"observacoes\":\"teste\",\"ativo\":1,\"data_atualizacao\":\"2025-08-07 19:46:09\"}','::1',NULL,'2025-08-07 14:46:09'),
(20,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-11 23:08:26\"}','::1',NULL,'2025-08-11 18:08:26'),
(21,1,NULL,'EXCLUIR','tbl_materiais',6,NULL,NULL,'::1',NULL,'2025-08-11 18:58:06'),
(22,1,NULL,'EXCLUIR','tbl_materiais',5,NULL,NULL,'::1',NULL,'2025-08-11 18:58:10'),
(23,1,NULL,'EXCLUIR','tbl_materiais',4,NULL,NULL,'::1',NULL,'2025-08-11 18:58:14'),
(24,1,NULL,'EXCLUIR','tbl_materiais',3,NULL,NULL,'::1',NULL,'2025-08-11 18:58:18'),
(25,1,NULL,'EXCLUIR','tbl_materiais',2,NULL,NULL,'::1',NULL,'2025-08-11 18:58:22'),
(26,1,NULL,'EXCLUIR','tbl_materiais',1,NULL,NULL,'::1',NULL,'2025-08-11 18:58:25'),
(27,1,NULL,'CRIAR','tbl_materiais',11,NULL,'{\"codigo\":\"COD001\",\"codigo_barras\":\"\",\"nome\":\"Material 001 EPI\",\"id_categoria\":\"7\",\"ca\":\"CA-12345\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"descricao\":\"testee\",\"preco_unitario\":10,\"localizacao_estoque\":\"LOCAL01\",\"estoque_atual\":2,\"estoque_minimo\":5,\"estoque_maximo\":100,\"observacoes\":\"testeeeee\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-08-12 02:20:54\"}','::1',NULL,'2025-08-11 21:20:54'),
(28,1,NULL,'CRIAR','tbl_materiais',12,NULL,'{\"codigo\":\"COD002\",\"codigo_barras\":\"\",\"nome\":\"Material Bahia 23\",\"id_categoria\":\"7\",\"ca\":\"CA029393983\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"descricao\":\"\",\"preco_unitario\":10,\"localizacao_estoque\":\"10\",\"estoque_atual\":10,\"estoque_minimo\":1,\"estoque_maximo\":100,\"observacoes\":\"\",\"ativo\":1,\"id_filial\":23,\"data_criacao\":\"2025-08-12 02:24:03\"}','::1',NULL,'2025-08-11 21:24:03'),
(29,1,NULL,'CRIAR','tbl_materiais',14,NULL,'{\"codigo\":\"GS001293br\",\"codigo_barras\":\"\",\"nome\":\"Material GS SERVICOS \",\"id_categoria\":\"7\",\"ca\":\"CA43434343\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"descricao\":\"\",\"preco_unitario\":15.8,\"localizacao_estoque\":\"435454\",\"estoque_atual\":6,\"estoque_minimo\":10,\"estoque_maximo\":50,\"observacoes\":\"\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-08-12 02:28:14\"}','::1',NULL,'2025-08-11 21:28:14'),
(30,1,NULL,'CRIAR','tbl_materiais',15,NULL,'{\"codigo\":\"CAD22\",\"codigo_barras\":\"\",\"nome\":\"Material teste\",\"id_categoria\":\"7\",\"ca\":\"CA-9993003\",\"id_fornecedor\":\"3\",\"id_unidade\":\"1\",\"descricao\":\"\",\"preco_unitario\":158.99,\"localizacao_estoque\":\"PRA99\",\"estoque_atual\":9,\"estoque_minimo\":10,\"estoque_maximo\":60,\"observacoes\":\"teste\",\"ativo\":1,\"id_filial\":25,\"data_criacao\":\"2025-08-12 03:23:07\"}','::1',NULL,'2025-08-11 22:23:07'),
(31,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 22:41:52\",\"session_duration\":171206}','::1',NULL,'2025-08-13 17:41:52'),
(32,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 22:41:55\"}','::1',NULL,'2025-08-13 17:41:55'),
(33,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 22:46:43\",\"session_duration\":288}','::1',NULL,'2025-08-13 17:46:43'),
(34,NULL,NULL,'LOGIN_FAILED',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":false,\"timestamp\":\"2025-08-13 22:46:57\"}','::1',NULL,'2025-08-13 17:46:57'),
(35,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 22:47:01\"}','::1',NULL,'2025-08-13 17:47:01'),
(36,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 23:18:11\",\"session_duration\":1870}','::1',NULL,'2025-08-13 18:18:11'),
(37,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 23:18:23\"}','::1',NULL,'2025-08-13 18:18:23'),
(38,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 23:28:36\",\"session_duration\":613}','::1',NULL,'2025-08-13 18:28:36'),
(39,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 23:28:40\"}','::1',NULL,'2025-08-13 18:28:40'),
(40,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 23:29:23\",\"session_duration\":43}','::1',NULL,'2025-08-13 18:29:23'),
(41,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 23:29:26\"}','::1',NULL,'2025-08-13 18:29:26'),
(42,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:29:28\"}','::1',NULL,'2025-08-13 18:29:28'),
(43,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina debug_verificacao_permissoes.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/debug_verificacao_permissoes.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:32:37\"}','::1',NULL,'2025-08-13 18:32:37'),
(44,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina debug_verificacao_permissoes.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/debug_verificacao_permissoes.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:33:04\"}','::1',NULL,'2025-08-13 18:33:04'),
(45,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina debug_simulacao_pedidos_compra.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/debug_simulacao_pedidos_compra.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:34:44\"}','::1',NULL,'2025-08-13 18:34:44'),
(46,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina debug_simulacao_pedidos_compra.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/debug_simulacao_pedidos_compra.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:35:33\"}','::1',NULL,'2025-08-13 18:35:33'),
(47,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:39:08\"}','::1',NULL,'2025-08-13 18:39:08'),
(48,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:40:57\"}','::1',NULL,'2025-08-13 18:40:57'),
(49,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 23:42:55\",\"session_duration\":809}','::1',NULL,'2025-08-13 18:42:55'),
(50,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"ADMIN@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 23:43:01\"}','::1',NULL,'2025-08-13 18:43:01'),
(51,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-13 23:56:40\",\"session_duration\":819}','::1',NULL,'2025-08-13 18:56:40'),
(52,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-13 23:56:45\"}','::1',NULL,'2025-08-13 18:56:45'),
(53,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:56:47\"}','::1',NULL,'2025-08-13 18:56:47'),
(54,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina pedidos-fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/pedidos-fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 18:57:32\"}','::1',NULL,'2025-08-13 18:57:32'),
(55,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina pedidos-fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/pedidos-fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:13:20\"}','::1',NULL,'2025-08-13 19:13:20'),
(56,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina pedidos-fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/pedidos-fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:15:05\"}','::1',NULL,'2025-08-13 19:15:05'),
(57,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina pedidos-fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/pedidos-fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:15:44\"}','::1',NULL,'2025-08-13 19:15:44'),
(58,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:16:37\",\"session_duration\":1192}','::1',NULL,'2025-08-13 19:16:37'),
(59,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"ADMIN@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:16:42\"}','::1',NULL,'2025-08-13 19:16:42'),
(60,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:28:41\",\"session_duration\":719}','::1',NULL,'2025-08-13 19:28:41'),
(61,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:28:47\"}','::1',NULL,'2025-08-13 19:28:47'),
(62,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:28:49\"}','::1',NULL,'2025-08-13 19:28:49'),
(63,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina pedidos-fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/pedidos-fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:29:04\"}','::1',NULL,'2025-08-13 19:29:04'),
(64,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:30:38\"}','::1',NULL,'2025-08-13 19:30:38'),
(65,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina perfil-acesso.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/perfil-acesso.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:30:39\"}','::1',NULL,'2025-08-13 19:30:39'),
(66,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:30:44\",\"session_duration\":117}','::1',NULL,'2025-08-13 19:30:44'),
(67,NULL,NULL,'LOGIN_FAILED',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":false,\"timestamp\":\"2025-08-14 00:30:51\"}','::1',NULL,'2025-08-13 19:30:51'),
(68,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"ADMIN@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:30:57\"}','::1',NULL,'2025-08-13 19:30:57'),
(69,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:32:23\",\"session_duration\":86}','::1',NULL,'2025-08-13 19:32:23'),
(70,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:32:28\"}','::1',NULL,'2025-08-13 19:32:28'),
(71,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:32:30\"}','::1',NULL,'2025-08-13 19:32:30'),
(72,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:32:52\",\"session_duration\":24}','::1',NULL,'2025-08-13 19:32:52'),
(73,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"ADMIN@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:33:00\"}','::1',NULL,'2025-08-13 19:33:00'),
(74,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:33:21\",\"session_duration\":21}','::1',NULL,'2025-08-13 19:33:21'),
(75,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:33:25\"}','::1',NULL,'2025-08-13 19:33:25'),
(76,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:33:26\"}','::1',NULL,'2025-08-13 19:33:26'),
(77,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"ADMIN@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:35:57\"}','127.0.0.1',NULL,'2025-08-13 19:35:57'),
(78,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:38:28\"}','::1',NULL,'2025-08-13 19:38:28'),
(79,NULL,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 19:38:33\"}','::1',NULL,'2025-08-13 19:38:33'),
(80,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:38:40\",\"session_duration\":315}','::1',NULL,'2025-08-13 19:38:40'),
(81,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"anderson@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:38:47\"}','::1',NULL,'2025-08-13 19:38:47'),
(82,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:51:06\",\"session_duration\":739}','::1',NULL,'2025-08-13 19:51:06'),
(83,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"compras@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 00:51:25\"}','::1',NULL,'2025-08-13 19:51:25'),
(84,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 00:56:00\",\"session_duration\":275}','::1',NULL,'2025-08-13 19:56:00'),
(85,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"promautone@gmail.com\",\"success\":true,\"timestamp\":\"2025-08-14 01:05:23\"}','::1',NULL,'2025-08-13 20:05:23'),
(86,11,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina fornecedores.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/fornecedores.php\",\"ip_usuario\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\",\"timestamp\":\"2025-08-13 20:05:42\"}','::1',NULL,'2025-08-13 20:05:42'),
(87,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 01:05:44\",\"session_duration\":21}','::1',NULL,'2025-08-13 20:05:44'),
(88,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"promautone@gmail.com\",\"success\":true,\"timestamp\":\"2025-08-14 01:05:48\"}','::1',NULL,'2025-08-13 20:05:48'),
(89,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 01:25:46\",\"session_duration\":2989}','127.0.0.1',NULL,'2025-08-13 20:25:46'),
(90,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"gerente@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 01:25:49\"}','127.0.0.1',NULL,'2025-08-13 20:25:49'),
(91,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 01:26:02\",\"session_duration\":13}','127.0.0.1',NULL,'2025-08-13 20:26:02'),
(92,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 01:26:15\"}','127.0.0.1',NULL,'2025-08-13 20:26:15'),
(93,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 01:26:30\",\"session_duration\":15}','127.0.0.1',NULL,'2025-08-13 20:26:30'),
(94,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"gerente@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 01:26:39\"}','127.0.0.1',NULL,'2025-08-13 20:26:39'),
(95,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 20:28:17\"}','127.0.0.1',NULL,'2025-08-13 20:28:17'),
(96,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina material.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/material.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:57:12\"}','127.0.0.1',NULL,'2025-08-13 21:57:12'),
(97,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:57:16\"}','127.0.0.1',NULL,'2025-08-13 21:57:16'),
(98,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:57:21\"}','127.0.0.1',NULL,'2025-08-13 21:57:21'),
(99,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina index.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/index\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:57:26\"}','127.0.0.1',NULL,'2025-08-13 21:57:26'),
(100,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:58:40\"}','127.0.0.1',NULL,'2025-08-13 21:58:40'),
(101,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 02:59:10\"}','::1',NULL,'2025-08-13 21:59:10'),
(102,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 21:59:53\"}','127.0.0.1',NULL,'2025-08-13 21:59:53'),
(103,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:02:32\"}','127.0.0.1',NULL,'2025-08-13 22:02:32'),
(104,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 03:03:44\",\"session_duration\":5825}','127.0.0.1',NULL,'2025-08-13 22:03:44'),
(105,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"gerente@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 03:03:53\"}','127.0.0.1',NULL,'2025-08-13 22:03:53'),
(106,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:04:00\"}','127.0.0.1',NULL,'2025-08-13 22:04:00'),
(107,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:05:53\"}','127.0.0.1',NULL,'2025-08-13 22:05:53'),
(108,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:12:46\"}','127.0.0.1',NULL,'2025-08-13 22:12:46'),
(109,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:12:56\"}','127.0.0.1',NULL,'2025-08-13 22:12:56'),
(110,12,NULL,'ERRO_ACESSO_403','sistema',NULL,NULL,'{\"mensagem\":\"Voc\\u00ea n\\u00e3o tem permiss\\u00e3o para acessar a p\\u00e1gina addFornecedor.php\",\"codigo\":\"403\",\"tipo\":\"warning\",\"url_pagina\":\"\\/sistemas\\/_estoquegrupoSorrisos\\/addFornecedor.php\",\"ip_usuario\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko\\/20100101 Firefox\\/141.0\",\"timestamp\":\"2025-08-13 22:13:04\"}','127.0.0.1',NULL,'2025-08-13 22:13:04'),
(111,12,NULL,'CRIAR','tbl_fornecedores',4,NULL,'{\"razao_social\":\"Fornecedor 222\",\"nome_fantasia\":\"Fornecedor 222\",\"cnpj\":\"17.888.722\\/0001-27\",\"inscricao_estadual\":\"14544455\",\"endereco\":\"dedededed\",\"cidade\":\"dedede\",\"estado\":\"SP\",\"cep\":\"04707-000\",\"telefone\":\"(11) 88484-8488\",\"email\":\"contato@fornecedor.brs\",\"contato_principal\":\"Andersonnn\",\"senha\":\"102030\",\"ativo\":1,\"data_criacao\":\"2025-08-14 03:18:23\"}','127.0.0.1',NULL,'2025-08-13 22:18:23'),
(112,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 03:18:48\",\"session_duration\":1178}','::1',NULL,'2025-08-13 22:18:48'),
(113,NULL,NULL,'LOGIN_FAILED',NULL,NULL,NULL,'{\"email\":\"contato@fornecedor.brs\",\"success\":false,\"timestamp\":\"2025-08-14 03:18:52\"}','::1',NULL,'2025-08-13 22:18:52'),
(114,NULL,NULL,'LOGIN_FAILED',NULL,NULL,NULL,'{\"email\":\"contato@fornecedor.brs\",\"success\":false,\"timestamp\":\"2025-08-14 03:18:57\"}','::1',NULL,'2025-08-13 22:18:57'),
(115,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-14 03:20:18\"}','::1',NULL,'2025-08-13 22:20:18'),
(116,12,NULL,'CRIAR','tbl_fornecedores',5,NULL,'{\"razao_social\":\"Fornecedor333\",\"nome_fantasia\":\"Fornecedor333\",\"cnpj\":\"17.888.722\\/0002-02\",\"inscricao_estadual\":\"3233232\",\"endereco\":\"rua teste\",\"cidade\":\"sao paulo\",\"estado\":\"SP\",\"cep\":\"04707-000\",\"telefone\":\"(56) 45445-4554\",\"email\":\"grupo.amf.center@gmail.com\",\"contato_principal\":\"Anderson mautone\",\"senha\":\"102030\",\"ativo\":1,\"data_criacao\":\"2025-08-14 03:22:02\"}','127.0.0.1',NULL,'2025-08-13 22:22:02'),
(117,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-14 03:25:18\",\"session_duration\":300}','::1',NULL,'2025-08-13 22:25:18'),
(118,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"grupo.amf.center@gmail.com\",\"success\":true,\"timestamp\":\"2025-08-14 03:25:27\"}','::1',NULL,'2025-08-13 22:25:27'),
(119,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-18 23:16:02\"}','::1',NULL,'2025-08-18 18:16:02'),
(120,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"promautone@gmail.com\",\"success\":true,\"timestamp\":\"2025-08-18 23:17:44\"}','::1',NULL,'2025-08-18 18:17:44'),
(121,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"gerente@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-18 23:18:28\"}','127.0.0.1',NULL,'2025-08-18 18:18:28'),
(122,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-19 00:47:42\",\"session_duration\":176}','::1',NULL,'2025-08-18 19:47:42'),
(123,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"gerente@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-19 00:47:59\"}','::1',NULL,'2025-08-18 19:47:59'),
(124,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-19 00:48:03\",\"session_duration\":5375}','127.0.0.1',NULL,'2025-08-18 19:48:03'),
(125,12,1,'CRIAR','tbl_materiais',16,NULL,'{\"codigo\":\"COD9438543PER\",\"codigo_barras\":\"\",\"nome\":\"Material PERNAMBUCO \",\"id_categoria\":\"8\",\"ca\":\"\",\"id_fornecedor\":\"5\",\"id_unidade\":\"1\",\"descricao\":\"teste\",\"preco_unitario\":15.9,\"localizacao_estoque\":\"local001\",\"estoque_atual\":1,\"estoque_minimo\":5,\"estoque_maximo\":500,\"observacoes\":\"teste\",\"ativo\":1,\"id_filial\":1,\"data_criacao\":\"2025-08-19 00:58:31\"}','::1',NULL,'2025-08-18 19:58:31'),
(126,NULL,NULL,'LOGOUT',NULL,NULL,NULL,'{\"usuario_nome\":\"Desconhecido\",\"timestamp\":\"2025-08-19 01:18:00\",\"session_duration\":1801}','::1',NULL,'2025-08-18 20:18:00'),
(127,NULL,NULL,'LOGIN_SUCCESS',NULL,NULL,NULL,'{\"email\":\"admin@gruposorrisos.com.br\",\"success\":true,\"timestamp\":\"2025-08-19 01:18:07\"}','::1',NULL,'2025-08-18 20:18:07');

/*Table structure for table `tbl_lotes` */

DROP TABLE IF EXISTS `tbl_lotes`;

CREATE TABLE `tbl_lotes` (
  `id_lote` int(11) NOT NULL AUTO_INCREMENT,
  `numero_lote` varchar(50) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_filial` int(11) NOT NULL,
  `quantidade_inicial` decimal(15,3) NOT NULL,
  `quantidade_atual` decimal(15,3) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `custo_unitario` decimal(15,4) DEFAULT 0.0000,
  `fornecedor_lote` varchar(200) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('ativo','vencido','consumido','cancelado') NOT NULL DEFAULT 'ativo',
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `uk_numero_lote` (`numero_lote`),
  KEY `fk_lote_material` (`id_material`),
  KEY `fk_lote_filial` (`id_filial`),
  KEY `idx_data_validade` (`data_validade`),
  KEY `idx_status` (`status`),
  KEY `idx_lote_material_validade` (`id_material`,`data_validade`,`status`),
  CONSTRAINT `fk_lote_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`) ON DELETE CASCADE,
  CONSTRAINT `fk_lote_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_lotes` */

insert  into `tbl_lotes`(`id_lote`,`numero_lote`,`id_material`,`id_filial`,`quantidade_inicial`,`quantidade_atual`,`data_fabricacao`,`data_validade`,`custo_unitario`,`fornecedor_lote`,`observacoes`,`status`,`data_criacao`,`data_atualizacao`) values 
(1,'LOT001-2024',1,1,50.000,50.000,'2024-01-01','2026-01-01',1200.0000,'Fornecedor ABC Ltda',NULL,'ativo','2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'LOT002-2024',2,1,15.000,15.000,'2024-01-15','2027-01-15',2800.0000,'Distribuidora XYZ S/A',NULL,'ativo','2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,'LOT003-2024',3,1,100.000,100.000,'2024-02-01','2025-02-01',15.0000,'Comercial 123 Ltda',NULL,'vencido','2025-07-25 16:40:21','2025-07-25 16:49:24'),
(4,'LOT004-2024',4,1,150.000,150.000,'2024-02-15','2025-02-15',8.5000,'Comercial 123 Ltda',NULL,'vencido','2025-07-25 16:40:21','2025-07-25 16:49:24'),
(5,'LOT005-2024',5,1,20.000,20.000,'2024-03-01','2026-03-01',190.0000,'Fornecedor ABC Ltda',NULL,'ativo','2025-07-25 16:40:21','2025-07-25 16:40:21'),
(6,'LOT006-2024',6,1,80.000,80.000,'2024-03-15','2025-03-15',12.0000,'Comercial 123 Ltda',NULL,'vencido','2025-07-25 16:40:21','2025-07-25 16:49:24');

/*Table structure for table `tbl_materiais` */

DROP TABLE IF EXISTS `tbl_materiais`;

CREATE TABLE `tbl_materiais` (
  `id_material` int(11) NOT NULL AUTO_INCREMENT,
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
  `ca` varchar(50) DEFAULT NULL COMMENT 'Certificado de Aprovação (CA) para materiais EPI',
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_material`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_materiais_categoria` (`id_categoria`),
  KEY `idx_materiais_fornecedor` (`id_fornecedor`),
  KEY `idx_materiais_filial` (`id_filial`),
  KEY `idx_materiais_ativo` (`ativo`),
  KEY `idx_materiais_estoque` (`estoque_atual`),
  KEY `fk_materiais_unidade` (`id_unidade`),
  KEY `idx_materiais_ca` (`ca`),
  CONSTRAINT `fk_materiais_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias` (`id_categoria`),
  CONSTRAINT `fk_materiais_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_materiais_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`) ON DELETE SET NULL,
  CONSTRAINT `fk_materiais_unidade` FOREIGN KEY (`id_unidade`) REFERENCES `tbl_unidades_medida` (`id_unidade`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_materiais` */

insert  into `tbl_materiais`(`id_material`,`codigo`,`nome`,`descricao`,`id_categoria`,`id_fornecedor`,`id_unidade`,`id_filial`,`preco_unitario`,`estoque_minimo`,`estoque_maximo`,`estoque_atual`,`localizacao_estoque`,`codigo_barras`,`ca`,`observacoes`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'MAT001','Smartphone Galaxy A54','Smartphone Samsung Galaxy A54 128GB',1,1,1,1,1299.99,10.000,100.000,83.000,'Prateleira A1',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:25'),
(2,'MAT002','Notebook Dell Inspiron','Notebook Dell Inspiron 15 polegadas',2,2,1,1,2899.99,5.000,50.000,15.000,'Prateleira B2',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:22'),
(3,'MAT003','Papel A4 500 folhas','Papel A4 500 folhas 75g',3,3,6,1,15.99,20.000,200.000,100.000,'Prateleira C3',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:18'),
(4,'MAT004','Detergente Líquido','Detergente líquido 500ml',4,3,3,1,8.99,30.000,300.000,150.000,'Prateleira D4',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:14'),
(5,'MAT005','Furadeira Elétrica','Furadeira elétrica 500W',5,1,1,1,199.99,5.000,50.000,20.000,'Prateleira E5',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:10'),
(6,'MAT006','Café em Pó','Café em pó 500g',6,3,2,1,12.99,25.000,250.000,114.000,'Prateleira F6',NULL,NULL,NULL,0,'2025-07-25 16:40:21','2025-08-11 18:58:06'),
(7,'MATERIAL TESTEs','MATERIAL TESTE','MATERIAL TESTE',6,3,1,25,15.90,9.000,10.000,159.000,'PRATELEIRA','',NULL,'teste',1,'2025-07-29 19:27:50','2025-08-11 22:08:43'),
(8,'43434343','AVENTAL PVC IMPERMEAVEL (UND)','',7,3,1,25,100.00,5.000,40.000,10.000,'LOCAL01','',NULL,'teste',1,'2025-08-07 14:19:16','2025-08-07 19:46:09'),
(9,'086077437','DENTE S50 ANT.SUP. A1	DELARA KULZER','DENTE S50 ANT.SUP. A1	DELARA KULZER',8,3,1,25,10.00,5.000,50.000,10.000,'DENTES','',NULL,'dentes',1,'2025-08-07 15:28:07','2025-08-07 10:28:07'),
(11,'COD001','Material 001 EPI','testee',7,3,1,25,10.00,5.000,100.000,2.000,'LOCAL01','','CA-12345','testeeeee',1,'2025-08-12 02:20:54','2025-08-11 21:20:54'),
(12,'COD002','Material Bahia 23','',7,3,1,23,10.00,1.000,100.000,110.000,'10','','CA029393983','',1,'2025-08-12 02:24:03','2025-08-11 21:58:32'),
(14,'GS001293br','Material GS SERVICOS ','',7,3,1,25,15.80,10.000,50.000,6.000,'435454','','CA43434343','',1,'2025-08-12 02:28:14','2025-08-11 21:28:14'),
(15,'CAD22','Material teste','',7,3,1,25,158.99,10.000,60.000,9.000,'PRA99','','CA-9993003','teste',1,'2025-08-12 03:23:07','2025-08-11 22:23:07'),
(16,'COD9438543PER','Material PERNAMBUCO ','teste',8,5,1,1,15.90,5.000,500.000,1.000,'local001','','','teste',1,'2025-08-19 00:58:31','2025-08-18 19:58:31');

/*Table structure for table `tbl_movimentacoes` */

DROP TABLE IF EXISTS `tbl_movimentacoes`;

CREATE TABLE `tbl_movimentacoes` (
  `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `numero_movimentacao` varchar(20) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','transferencia','ajuste','devolucao','inventario') NOT NULL,
  `subtipo_movimentacao` varchar(50) DEFAULT NULL COMMENT 'Compra, Venda, Transferência, etc.',
  `id_material` int(11) NOT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `id_filial_origem` int(11) DEFAULT NULL,
  `id_filial_destino` int(11) DEFAULT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `estoque_anterior_origem` decimal(15,3) DEFAULT 0.000,
  `estoque_atual_origem` decimal(15,3) DEFAULT 0.000,
  `estoque_anterior_destino` decimal(15,3) DEFAULT 0.000,
  `estoque_atual_destino` decimal(15,3) DEFAULT 0.000,
  `valor_unitario` decimal(15,4) DEFAULT NULL,
  `valor_total` decimal(15,4) DEFAULT NULL,
  `custo_medio_anterior` decimal(15,4) DEFAULT 0.0000,
  `custo_medio_atual` decimal(15,4) DEFAULT 0.0000,
  `id_fornecedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_pedido_compra` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `motivo_ajuste` varchar(200) DEFAULT NULL,
  `status_movimentacao` enum('pendente','aprovada','executada','cancelada','estornada') NOT NULL DEFAULT 'executada',
  `id_usuario_solicitante` int(11) DEFAULT NULL,
  `id_usuario_executor` int(11) NOT NULL,
  `data_solicitacao` datetime DEFAULT NULL,
  `data_aprovacao` datetime DEFAULT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
  KEY `idx_status_movimentacao` (`status_movimentacao`),
  KEY `idx_movimentacao_data_tipo` (`data_movimentacao`,`tipo_movimentacao`),
  KEY `idx_movimentacao_material_data` (`id_material`,`data_movimentacao`),
  CONSTRAINT `fk_movimentacao_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`),
  CONSTRAINT `fk_movimentacao_filial_destino` FOREIGN KEY (`id_filial_destino`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_movimentacao_filial_origem` FOREIGN KEY (`id_filial_origem`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_movimentacao_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`),
  CONSTRAINT `fk_movimentacao_lote` FOREIGN KEY (`id_lote`) REFERENCES `tbl_lotes` (`id_lote`),
  CONSTRAINT `fk_movimentacao_material` FOREIGN KEY (`id_material`) REFERENCES `tbl_materiais` (`id_material`),
  CONSTRAINT `fk_movimentacao_usuario_executor` FOREIGN KEY (`id_usuario_executor`) REFERENCES `tbl_usuarios` (`id_usuario`),
  CONSTRAINT `fk_movimentacao_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_movimentacoes` */

insert  into `tbl_movimentacoes`(`id_movimentacao`,`numero_movimentacao`,`tipo_movimentacao`,`subtipo_movimentacao`,`id_material`,`id_lote`,`id_filial_origem`,`id_filial_destino`,`quantidade`,`estoque_anterior_origem`,`estoque_atual_origem`,`estoque_anterior_destino`,`estoque_atual_destino`,`valor_unitario`,`valor_total`,`custo_medio_anterior`,`custo_medio_atual`,`id_fornecedor`,`id_cliente`,`id_pedido_compra`,`documento`,`numero_documento`,`observacoes`,`motivo_ajuste`,`status_movimentacao`,`id_usuario_solicitante`,`id_usuario_executor`,`data_solicitacao`,`data_aprovacao`,`data_movimentacao`,`data_criacao`,`data_atualizacao`) values 
(13,'MOV-000001','entrada',NULL,7,NULL,NULL,25,100.000,0.000,0.000,59.000,159.000,15.9000,1590.0000,0.0000,0.0000,NULL,NULL,NULL,NULL,NULL,'teste',NULL,'executada',NULL,1,NULL,NULL,'2025-08-11 22:08:43','2025-08-11 22:08:43','2025-08-11 22:08:43'),
(14,'MOV-000002','transferencia',NULL,7,NULL,25,23,10.000,0.000,0.000,0.000,-10.000,15.9000,159.0000,0.0000,0.0000,NULL,NULL,NULL,'32323232',NULL,'teste',NULL,'executada',NULL,1,NULL,NULL,'2025-08-11 22:12:44','2025-08-11 22:12:44','2025-08-11 22:12:44');

/*Table structure for table `tbl_paginas` */

DROP TABLE IF EXISTS `tbl_paginas`;

CREATE TABLE `tbl_paginas` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `nome_pagina` varchar(255) NOT NULL,
  `url_pagina` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `cor` varchar(50) DEFAULT 'primary',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pagina`),
  UNIQUE KEY `uk_nome_pagina` (`nome_pagina`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_ordem` (`ordem`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_paginas` */

insert  into `tbl_paginas`(`id_pagina`,`nome_pagina`,`url_pagina`,`descricao`,`categoria`,`icone`,`cor`,`ordem`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'Dashboard','index.php','Painel principal com visão geral do sistema','gestao','bi-speedometer2','primary',1,1,'2025-08-11 23:57:48','2025-08-13 21:57:38'),
(2,'Usuários','usuarios.php','Gerenciamento de usuários e perfis de acesso','gestao','bi-people','primary',2,1,'2025-08-11 23:57:48','2025-08-13 21:57:40'),
(3,'Perfil de Acesso','perfil-acesso.php','Gerenciamento de perfis e permissões','gestao','bi-shield-lock','primary',3,1,'2025-08-11 23:57:48','2025-08-13 21:57:41'),
(4,'Filiais/Clínicas','filiais.php','Gestão de filiais e unidades','gestao','bi-hospital','danger',4,1,'2025-08-11 23:57:48','2025-08-13 21:57:48'),
(5,'Materiais','material.php','Controle de estoque e cadastro de materiais','estoque','bi-box-seam','success',5,1,'2025-08-11 23:57:48','2025-08-13 21:57:53'),
(6,'Movimentações','movimentacoes.php','Controle de entrada, saída e transferências','estoque','bi-arrow-left-right','info',6,1,'2025-08-11 23:57:48','2025-08-13 21:57:54'),
(7,'Alertas','alertas.php','Sistema de alertas de estoque','estoque','bi-exclamation-triangle','warning',7,1,'2025-08-11 23:57:48','2025-08-13 21:57:56'),
(8,'Inventário','inventario.php','Controle de inventário físico','estoque','bi-clipboard-data','info',8,1,'2025-08-11 23:57:48','2025-08-13 21:57:57'),
(9,'Pedidos de Compra Interno','pedidos-compra.php','Gestão de pedidos e cotações','compras','bi-cart-check','warning',9,1,'2025-08-11 23:57:48','2025-08-13 21:57:58'),
(10,'Fornecedores','fornecedores.php','Cadastro e gestão de fornecedores','compras','bi-building','secondary',10,1,'2025-08-11 23:57:48','2025-08-13 21:57:59'),
(11,'Relatórios','relatorios.php','Relatórios e análises do sistema','relatorios','bi-graph-up','dark',11,1,'2025-08-11 23:57:48','2025-08-13 21:58:00'),
(12,'Tickets','tickets.php','Sistema de tickets e suporte','relatorios','bi-ticket-detailed','info',12,1,'2025-08-11 23:57:48','2025-08-13 21:58:01'),
(13,'Configurações','configuracoes.php','Configurações gerais do sistema','configuracoes','bi-gear','dark',13,1,'2025-08-11 23:57:48','2025-08-13 21:58:02'),
(15,'Pedidos Compra Fornecedor','pedidos-fornecedores.php','Visão geral de todos os Pedidos realizados para aquele fornecedor.','compras','bi-cart-check','warning',14,1,'2025-08-11 23:57:48','2025-08-18 18:20:24');

/*Table structure for table `tbl_paginas_acesso` */

DROP TABLE IF EXISTS `tbl_paginas_acesso`;

CREATE TABLE `tbl_paginas_acesso` (
  `id_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `id_pagina` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `data_acesso` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_acesso` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id_acesso`),
  KEY `idx_id_pagina` (`id_pagina`),
  KEY `idx_id_usuario` (`id_usuario`),
  KEY `idx_data_acesso` (`data_acesso`),
  CONSTRAINT `fk_paginas_acesso_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE,
  CONSTRAINT `fk_paginas_acesso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_paginas_acesso` */

insert  into `tbl_paginas_acesso`(`id_acesso`,`id_pagina`,`id_usuario`,`data_acesso`,`ip_acesso`,`user_agent`) values 
(1,1,1,'2025-08-13 18:17:57','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(2,2,1,'2025-08-13 18:18:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(3,3,1,'2025-08-13 18:18:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(4,3,1,'2025-08-13 18:28:21','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(5,1,1,'2025-08-13 18:28:25','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(6,2,1,'2025-08-13 18:28:30','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(7,1,1,'2025-08-13 18:28:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(8,3,1,'2025-08-13 18:28:47','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(9,2,1,'2025-08-13 18:29:17','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(10,9,NULL,'2025-08-13 18:42:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(11,9,NULL,'2025-08-13 18:42:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(12,1,1,'2025-08-13 18:43:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(13,10,1,'2025-08-13 18:43:10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(14,5,1,'2025-08-13 18:43:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(15,1,1,'2025-08-13 18:43:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(16,3,1,'2025-08-13 18:43:19','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(17,3,1,'2025-08-13 18:48:14','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(18,3,1,'2025-08-13 18:53:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(19,3,1,'2025-08-13 18:53:10','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(20,3,1,'2025-08-13 18:53:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(21,10,1,'2025-08-13 18:53:45','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(22,9,1,'2025-08-13 18:53:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(23,9,1,'2025-08-13 18:53:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(24,9,1,'2025-08-13 18:55:30','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(25,9,1,'2025-08-13 18:55:30','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(26,3,1,'2025-08-13 18:55:37','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(27,3,1,'2025-08-13 18:56:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(28,5,1,'2025-08-13 18:56:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(29,1,1,'2025-08-13 18:56:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(30,2,1,'2025-08-13 18:56:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(31,1,1,'2025-08-13 18:56:14','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(32,5,1,'2025-08-13 18:56:27','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(33,4,1,'2025-08-13 18:56:29','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(34,6,1,'2025-08-13 18:56:31','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(35,8,1,'2025-08-13 18:56:31','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(36,10,1,'2025-08-13 18:56:32','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(37,11,1,'2025-08-13 18:56:33','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(38,7,1,'2025-08-13 18:56:34','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(39,3,1,'2025-08-13 18:56:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(40,9,NULL,'2025-08-13 18:57:22','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(41,9,NULL,'2025-08-13 18:57:22','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(42,15,NULL,'2025-08-13 18:57:32','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(43,15,NULL,'2025-08-13 19:13:20','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(44,9,NULL,'2025-08-13 19:15:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(45,9,NULL,'2025-08-13 19:15:23','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(46,9,NULL,'2025-08-13 19:15:24','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(47,9,NULL,'2025-08-13 19:15:24','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(48,9,NULL,'2025-08-13 19:16:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(49,9,NULL,'2025-08-13 19:16:28','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(50,1,1,'2025-08-13 19:16:44','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(51,2,1,'2025-08-13 19:16:47','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(52,3,1,'2025-08-13 19:16:55','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(53,3,1,'2025-08-13 19:27:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(54,3,1,'2025-08-13 19:27:14','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(55,3,1,'2025-08-13 19:28:22','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(56,3,1,'2025-08-13 19:28:26','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(57,3,1,'2025-08-13 19:28:39','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(58,1,1,'2025-08-13 19:30:58','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(59,1,1,'2025-08-13 19:31:05','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(60,9,1,'2025-08-13 19:31:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(61,9,1,'2025-08-13 19:31:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(62,3,1,'2025-08-13 19:31:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(63,15,NULL,'2025-08-13 19:32:43','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(64,15,NULL,'2025-08-13 19:32:43','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(65,1,1,'2025-08-13 19:33:01','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(66,3,1,'2025-08-13 19:33:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(67,15,NULL,'2025-08-13 19:33:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(68,15,NULL,'2025-08-13 19:33:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(69,1,1,'2025-08-13 19:35:58','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(70,15,NULL,'2025-08-13 19:38:48','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(71,15,NULL,'2025-08-13 19:38:48','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(72,1,1,'2025-08-13 19:40:53','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(73,1,1,'2025-08-13 19:44:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(74,1,1,'2025-08-13 19:44:52','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(75,1,1,'2025-08-13 19:46:18','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(76,1,1,'2025-08-13 19:47:35','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(77,1,1,'2025-08-13 19:48:42','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(78,1,1,'2025-08-13 19:48:43','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(79,3,1,'2025-08-13 19:48:54','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(80,2,1,'2025-08-13 19:49:20','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(81,3,1,'2025-08-13 19:50:05','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(82,15,NULL,'2025-08-13 19:51:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(83,15,NULL,'2025-08-13 19:51:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(84,15,NULL,'2025-08-13 19:51:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(85,15,NULL,'2025-08-13 19:51:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(86,5,6,'2025-08-13 19:51:26','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(87,6,6,'2025-08-13 19:51:32','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(88,7,6,'2025-08-13 19:51:34','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(89,8,6,'2025-08-13 19:51:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(90,9,6,'2025-08-13 19:51:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(91,9,6,'2025-08-13 19:51:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(92,10,6,'2025-08-13 19:51:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(93,12,6,'2025-08-13 19:51:47','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(94,5,6,'2025-08-13 19:51:56','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(95,13,1,'2025-08-13 19:55:39','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(96,13,1,'2025-08-13 19:55:42','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(97,2,1,'2025-08-13 19:56:10','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(98,2,1,'2025-08-13 20:01:22','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(99,2,1,'2025-08-13 20:01:56','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(100,2,1,'2025-08-13 20:01:57','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(101,2,1,'2025-08-13 20:03:17','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(102,2,1,'2025-08-13 20:04:38','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(103,3,1,'2025-08-13 20:04:39','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(104,10,11,'2025-08-13 20:05:25','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(105,15,11,'2025-08-13 20:05:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(106,15,11,'2025-08-13 20:05:49','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(107,2,1,'2025-08-13 20:24:25','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(108,3,1,'2025-08-13 20:24:44','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(109,2,1,'2025-08-13 20:24:47','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(110,1,12,'2025-08-13 20:25:50','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(111,1,1,'2025-08-13 20:26:17','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(112,3,1,'2025-08-13 20:26:18','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(113,1,12,'2025-08-13 20:26:41','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(114,9,12,'2025-08-13 20:26:49','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(115,9,12,'2025-08-13 20:26:49','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(116,10,12,'2025-08-13 20:27:29','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(117,10,12,'2025-08-13 20:28:21','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(118,1,12,'2025-08-13 21:55:36','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(119,5,12,'2025-08-13 21:55:53','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(120,10,12,'2025-08-13 21:58:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(121,10,12,'2025-08-13 21:58:10','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(122,1,12,'2025-08-13 21:58:11','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(123,10,12,'2025-08-13 21:58:38','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(124,10,12,'2025-08-13 21:58:46','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(125,1,1,'2025-08-13 21:59:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(126,3,1,'2025-08-13 21:59:15','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(127,10,12,'2025-08-13 21:59:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(128,10,12,'2025-08-13 21:59:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(129,10,12,'2025-08-13 22:02:31','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(130,10,12,'2025-08-13 22:02:35','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(131,10,12,'2025-08-13 22:02:35','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(132,10,12,'2025-08-13 22:02:36','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(133,10,12,'2025-08-13 22:02:36','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(134,10,12,'2025-08-13 22:02:38','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(135,10,12,'2025-08-13 22:02:38','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(136,10,12,'2025-08-13 22:02:42','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(137,10,12,'2025-08-13 22:02:42','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(138,10,12,'2025-08-13 22:03:08','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(139,10,12,'2025-08-13 22:03:08','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(140,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(141,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(142,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(143,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(144,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(145,10,12,'2025-08-13 22:03:09','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(146,10,12,'2025-08-13 22:03:10','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(147,10,12,'2025-08-13 22:03:10','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(148,10,12,'2025-08-13 22:03:33','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(149,10,12,'2025-08-13 22:03:34','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(150,1,12,'2025-08-13 22:03:41','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(151,1,12,'2025-08-13 22:03:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(152,10,12,'2025-08-13 22:03:58','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(153,10,12,'2025-08-13 22:03:58','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(154,10,12,'2025-08-13 22:04:01','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(155,10,12,'2025-08-13 22:04:01','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(156,10,12,'2025-08-13 22:05:05','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(157,10,12,'2025-08-13 22:05:05','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(158,10,12,'2025-08-13 22:05:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(159,10,12,'2025-08-13 22:05:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(160,10,12,'2025-08-13 22:06:05','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(161,10,12,'2025-08-13 22:06:05','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(162,10,12,'2025-08-13 22:06:06','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(163,10,12,'2025-08-13 22:06:06','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(164,10,12,'2025-08-13 22:12:44','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(165,10,12,'2025-08-13 22:12:44','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(166,10,12,'2025-08-13 22:12:45','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(167,10,12,'2025-08-13 22:12:45','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(168,10,12,'2025-08-13 22:12:47','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(169,10,12,'2025-08-13 22:12:47','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(170,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(171,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(172,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(173,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(174,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(175,10,12,'2025-08-13 22:12:55','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(176,10,12,'2025-08-13 22:12:58','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(177,10,12,'2025-08-13 22:12:58','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(178,10,12,'2025-08-13 22:13:50','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(179,10,12,'2025-08-13 22:13:50','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(180,10,12,'2025-08-13 22:13:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(181,10,12,'2025-08-13 22:13:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(182,1,12,'2025-08-13 22:13:56','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(183,10,12,'2025-08-13 22:13:59','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(184,10,12,'2025-08-13 22:13:59','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(185,2,1,'2025-08-13 22:14:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(186,10,12,'2025-08-13 22:18:28','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(187,10,12,'2025-08-13 22:18:28','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(188,2,1,'2025-08-13 22:18:34','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(189,1,1,'2025-08-13 22:20:20','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(190,2,1,'2025-08-13 22:20:20','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(191,10,12,'2025-08-13 22:22:06','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(192,10,12,'2025-08-13 22:22:06','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(193,2,1,'2025-08-13 22:22:08','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(194,15,14,'2025-08-13 22:25:29','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(195,15,14,'2025-08-13 22:25:29','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(196,1,1,'2025-08-18 18:16:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(197,3,1,'2025-08-18 18:16:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(198,3,1,'2025-08-18 18:17:21','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(199,2,1,'2025-08-18 18:17:24','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(200,15,11,'2025-08-18 18:17:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(201,15,11,'2025-08-18 18:17:46','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0'),
(202,1,12,'2025-08-18 18:18:29','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(203,1,12,'2025-08-18 18:18:46','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(204,3,12,'2025-08-18 18:18:53','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(205,3,12,'2025-08-18 18:19:01','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(206,3,12,'2025-08-18 18:20:28','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(207,4,12,'2025-08-18 18:20:29','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(208,3,12,'2025-08-18 18:20:33','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(209,5,12,'2025-08-18 18:20:34','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(210,6,12,'2025-08-18 18:20:39','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(211,7,12,'2025-08-18 18:20:42','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(212,8,12,'2025-08-18 18:20:45','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(213,8,12,'2025-08-18 18:21:18','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(214,8,12,'2025-08-18 18:21:33','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(215,1,12,'2025-08-18 18:22:43','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(216,8,12,'2025-08-18 18:22:51','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(217,8,12,'2025-08-18 18:23:06','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(218,8,12,'2025-08-18 19:37:59','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(219,8,12,'2025-08-18 19:41:17','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(220,1,12,'2025-08-18 19:41:45','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(221,8,12,'2025-08-18 19:42:02','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(222,8,12,'2025-08-18 19:47:12','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(223,8,12,'2025-08-18 19:47:13','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(224,8,12,'2025-08-18 19:47:13','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(225,2,12,'2025-08-18 19:47:48','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(226,1,12,'2025-08-18 19:48:01','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(227,8,12,'2025-08-18 19:48:06','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(228,1,12,'2025-08-18 19:48:16','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(229,8,12,'2025-08-18 19:48:21','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(230,1,12,'2025-08-18 19:48:48','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(231,8,12,'2025-08-18 19:49:00','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(232,1,12,'2025-08-18 19:49:04','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(233,8,12,'2025-08-18 19:49:16','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(234,5,12,'2025-08-18 19:57:05','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(235,5,12,'2025-08-18 19:58:36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(236,8,12,'2025-08-18 19:58:40','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(237,8,12,'2025-08-18 20:02:59','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(238,7,12,'2025-08-18 20:03:42','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(239,6,12,'2025-08-18 20:04:02','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(240,9,12,'2025-08-18 20:04:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(241,9,12,'2025-08-18 20:04:35','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(242,1,1,'2025-08-18 20:18:09','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(243,13,1,'2025-08-18 20:18:12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(244,13,1,'2025-08-18 20:38:01','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(245,13,1,'2025-08-18 20:38:11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(246,13,1,'2025-08-18 20:39:03','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(247,13,1,'2025-08-18 20:40:44','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(248,13,1,'2025-08-18 20:42:01','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(249,13,1,'2025-08-18 20:42:33','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(250,13,1,'2025-08-18 20:42:38','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(251,13,1,'2025-08-18 20:42:39','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(252,13,1,'2025-08-18 20:42:47','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(253,13,1,'2025-08-18 20:42:47','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36');

/*Table structure for table `tbl_pedidos_compra` */

DROP TABLE IF EXISTS `tbl_pedidos_compra`;

CREATE TABLE `tbl_pedidos_compra` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
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
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `id_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `id_usuario_aprovador` (`id_usuario_aprovador`),
  KEY `idx_pedidos_filial` (`id_filial`),
  KEY `idx_pedidos_fornecedor` (`id_fornecedor`),
  KEY `idx_pedidos_status` (`status`),
  KEY `idx_pedidos_data` (`data_solicitacao`),
  CONSTRAINT `fk_pedidos_compra_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_pedidos_compra_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tbl_fornecedores` (`id_fornecedor`),
  CONSTRAINT `fk_pedidos_compra_usuario_aprovador` FOREIGN KEY (`id_usuario_aprovador`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedidos_compra_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_pedidos_compra` */

insert  into `tbl_pedidos_compra`(`id_pedido`,`numero_pedido`,`id_filial`,`id_fornecedor`,`id_usuario_solicitante`,`id_usuario_aprovador`,`data_solicitacao`,`data_aprovacao`,`data_entrega_prevista`,`data_entrega_realizada`,`status`,`valor_total`,`observacoes`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'PED-2024-001',1,1,2,NULL,'2025-07-25 16:40:21',NULL,'2024-01-25',NULL,'aprovado',12500.00,'Pedido de materiais de escritório',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'PED-2024-002',1,2,2,NULL,'2025-07-25 16:40:21',NULL,'2024-01-30',NULL,'em_entrega',8900.00,'Equipamentos de informática',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(4,'PED-2024-004',1,3,2,NULL,'2025-07-25 16:40:21',NULL,'2024-01-28',NULL,'entregue',7200.00,'Ferramentas e equipamentos',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(5,'PED-2025-004',1,1,1,NULL,'2025-08-07 11:44:19',NULL,'2024-12-31',NULL,'pendente',100.00,'Teste de criação',1,'2025-08-07 11:44:19','2025-08-07 11:44:19'),
(6,'PED-2025-005',25,3,1,NULL,'2025-08-07 11:44:29',NULL,'2025-08-15',NULL,'pendente',567.00,'testeeee',1,'2025-08-07 11:44:29','2025-08-07 11:44:29'),
(7,'PED-2025-006',25,3,1,NULL,'2025-08-11 22:58:44',NULL,'2025-08-22',NULL,'pendente',1847.90,'teste',1,'2025-08-11 22:58:44','2025-08-11 22:58:44');

/*Table structure for table `tbl_perfil_paginas` */

DROP TABLE IF EXISTS `tbl_perfil_paginas`;

CREATE TABLE `tbl_perfil_paginas` (
  `id_perfil_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `permissao_visualizar` tinyint(1) DEFAULT 1,
  `permissao_inserir` tinyint(1) DEFAULT 0,
  `permissao_editar` tinyint(1) DEFAULT 0,
  `permissao_excluir` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_perfil_pagina`),
  UNIQUE KEY `uk_perfil_pagina` (`id_perfil`,`id_pagina`),
  KEY `idx_id_perfil` (`id_perfil`),
  KEY `idx_id_pagina` (`id_pagina`),
  CONSTRAINT `fk_perfil_paginas_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE,
  CONSTRAINT `fk_perfil_paginas_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_perfil_paginas` */

insert  into `tbl_perfil_paginas`(`id_perfil_pagina`,`id_perfil`,`id_pagina`,`permissao_visualizar`,`permissao_inserir`,`permissao_editar`,`permissao_excluir`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(171,3,10,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(172,3,15,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(173,3,9,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(174,3,7,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(175,3,8,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(176,3,5,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(177,3,6,1,1,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(178,4,10,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(179,4,15,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(180,4,9,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(181,4,7,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(182,4,8,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(183,4,5,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(184,4,6,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(185,4,11,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(186,4,12,1,0,0,0,1,'2025-08-13 19:48:39','2025-08-13 19:48:39'),
(194,6,7,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(195,6,10,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(196,6,8,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(197,6,5,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(198,6,6,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(199,6,9,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(200,6,12,1,1,1,1,1,'2025-08-13 19:50:45','2025-08-13 19:50:45'),
(201,5,15,1,0,1,0,1,'2025-08-13 20:05:39','2025-08-13 20:05:39'),
(215,2,7,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(216,2,1,1,1,1,0,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(217,2,4,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(218,2,10,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(219,2,8,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(220,2,5,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(222,2,6,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(223,2,9,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(224,2,3,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(225,2,11,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(226,2,12,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(227,2,2,1,1,1,1,1,'2025-08-13 21:59:45','2025-08-13 21:59:45'),
(228,1,13,1,1,1,1,1,'2025-08-18 18:17:19','2025-08-18 18:17:19'),
(229,1,1,1,1,1,1,1,'2025-08-18 18:17:19','2025-08-18 18:17:19'),
(230,1,4,1,1,1,1,1,'2025-08-18 18:17:19','2025-08-18 18:17:19'),
(232,1,3,1,1,1,1,1,'2025-08-18 18:17:19','2025-08-18 18:17:19'),
(233,1,2,1,1,1,1,1,'2025-08-18 18:17:19','2025-08-18 18:17:19');

/*Table structure for table `tbl_perfis` */

DROP TABLE IF EXISTS `tbl_perfis`;

CREATE TABLE `tbl_perfis` (
  `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
  `nome_perfil` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_perfis` */

insert  into `tbl_perfis`(`id_perfil`,`nome_perfil`,`descricao`,`ativo`,`data_criacao`,`data_atualizacao`) values 
(1,'Administrador','Acesso total ao sistema',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'Gerente','Gerencia filiais e equipes',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(3,'Operador','Operações básicas de estoque',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(4,'Visualizador','Apenas visualização de relatórios',1,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(5,'Fornecedor','Fornecedor Externo',1,'2025-08-13 11:25:44','2025-08-13 11:25:44'),
(6,'Supervisor','Supervisor de Compras e Estoque',1,'2025-08-13 19:48:39','2025-08-13 19:48:39');

/*Table structure for table `tbl_permissoes` */

DROP TABLE IF EXISTS `tbl_permissoes`;

CREATE TABLE `tbl_permissoes` (
  `id_permissao` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL,
  `id_pagina` int(11) NOT NULL,
  `pode_visualizar` tinyint(1) DEFAULT 0,
  `pode_inserir` tinyint(1) DEFAULT 0,
  `pode_editar` tinyint(1) DEFAULT 0,
  `pode_excluir` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_permissao`),
  UNIQUE KEY `uk_perfil_pagina` (`id_perfil`,`id_pagina`),
  KEY `fk_permissoes_pagina` (`id_pagina`),
  CONSTRAINT `fk_permissoes_pagina` FOREIGN KEY (`id_pagina`) REFERENCES `tbl_paginas` (`id_pagina`) ON DELETE CASCADE,
  CONSTRAINT `fk_permissoes_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_permissoes` */

insert  into `tbl_permissoes`(`id_permissao`,`id_perfil`,`id_pagina`,`pode_visualizar`,`pode_inserir`,`pode_editar`,`pode_excluir`,`ativo`,`data_criacao`) values 
(32,1,7,1,1,1,1,1,'2025-08-13 18:53:17'),
(33,1,13,1,1,1,1,1,'2025-08-13 18:53:17'),
(34,1,1,1,1,1,1,1,'2025-08-13 18:53:17'),
(35,1,4,1,1,1,1,1,'2025-08-13 18:53:17'),
(36,1,10,1,1,1,1,1,'2025-08-13 18:53:17'),
(37,1,8,1,1,1,1,1,'2025-08-13 18:53:17'),
(38,1,5,1,1,1,1,1,'2025-08-13 18:53:17'),
(40,1,6,1,1,1,1,1,'2025-08-13 18:53:17'),
(41,1,15,1,1,1,1,1,'2025-08-13 18:53:17'),
(42,1,9,1,1,1,1,1,'2025-08-13 18:53:17'),
(43,1,3,1,1,1,1,1,'2025-08-13 18:53:17'),
(44,1,11,1,1,1,1,1,'2025-08-13 18:53:17'),
(45,1,12,1,1,1,1,1,'2025-08-13 18:53:17'),
(46,1,2,1,1,1,1,1,'2025-08-13 18:53:17'),
(48,5,15,1,1,1,1,1,'2025-08-13 19:17:06');

/*Table structure for table `tbl_prioridades_ticket` */

DROP TABLE IF EXISTS `tbl_prioridades_ticket`;

CREATE TABLE `tbl_prioridades_ticket` (
  `id_prioridade` int(11) NOT NULL AUTO_INCREMENT,
  `nome_prioridade` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#6c757d',
  `icone` varchar(50) DEFAULT 'bi-flag',
  `tempo_esperado` int(11) DEFAULT 1440 COMMENT 'Tempo em minutos',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_prioridade`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tbl_prioridades_ticket` */

insert  into `tbl_prioridades_ticket`(`id_prioridade`,`nome_prioridade`,`descricao`,`cor`,`icone`,`tempo_esperado`,`ativo`,`created_at`,`updated_at`) values 
(1,'Baixa','Pode ser resolvido em até 72h','#6c757d','bi-flag',4320,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(2,'Média','Deve ser resolvido em até 24h','#ffc107','bi-flag-fill',1440,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(3,'Alta','Deve ser resolvido em até 4h','#fd7e14','bi-exclamation-triangle',240,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(4,'Crítica','Deve ser resolvido imediatamente','#dc3545','bi-exclamation-triangle-fill',60,1,'2025-07-25 16:39:56','2025-07-25 16:39:56');

/*Table structure for table `tbl_status_ticket` */

DROP TABLE IF EXISTS `tbl_status_ticket`;

CREATE TABLE `tbl_status_ticket` (
  `id_status` int(11) NOT NULL AUTO_INCREMENT,
  `nome_status` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#6c757d',
  `icone` varchar(50) DEFAULT 'bi-circle',
  `is_final` tinyint(1) DEFAULT 0 COMMENT 'Se é um status final',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tbl_status_ticket` */

insert  into `tbl_status_ticket`(`id_status`,`nome_status`,`descricao`,`cor`,`icone`,`is_final`,`ativo`,`created_at`,`updated_at`) values 
(1,'Aberto','Ticket recém aberto','#007bff','bi-circle-fill',0,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(2,'Em Análise','Ticket sendo analisado','#ffc107','bi-clock',0,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(3,'Em Andamento','Ticket sendo trabalhado','#17a2b8','bi-play-circle',0,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(4,'Aguardando Cliente','Aguardando resposta do cliente','#6c757d','bi-pause-circle',0,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(5,'Aguardando Terceiros','Aguardando terceiros','#fd7e14','bi-people',0,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(6,'Resolvido','Ticket resolvido','#28a745','bi-check-circle',1,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(7,'Fechado','Ticket fechado','#6c757d','bi-x-circle',1,1,'2025-07-25 16:39:56','2025-07-25 16:39:56'),
(8,'Cancelado','Ticket cancelado','#dc3545','bi-x-circle-fill',1,1,'2025-07-25 16:39:56','2025-07-25 16:39:56');

/*Table structure for table `tbl_tickets` */

DROP TABLE IF EXISTS `tbl_tickets`;

CREATE TABLE `tbl_tickets` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `numero_ticket` varchar(20) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `id_prioridade` int(11) DEFAULT NULL,
  `id_status` int(11) DEFAULT NULL,
  `id_usuario_solicitante` int(11) DEFAULT NULL,
  `id_usuario_atribuido` int(11) DEFAULT NULL,
  `id_filial` int(11) DEFAULT NULL,
  `data_abertura` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data_fechamento` datetime DEFAULT NULL,
  `tempo_resolucao` int(11) DEFAULT NULL COMMENT 'Tempo em minutos',
  `avaliacao` tinyint(4) DEFAULT NULL COMMENT '1-5 estrelas',
  `comentario_avaliacao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_ticket`),
  UNIQUE KEY `numero_ticket` (`numero_ticket`),
  KEY `idx_status` (`id_status`),
  KEY `idx_prioridade` (`id_prioridade`),
  KEY `idx_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `idx_usuario_atribuido` (`id_usuario_atribuido`),
  KEY `idx_filial` (`id_filial`),
  KEY `idx_data_abertura` (`data_abertura`),
  KEY `fk_tickets_categoria` (`id_categoria`),
  CONSTRAINT `fk_tickets_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias_ticket` (`id_categoria`),
  CONSTRAINT `fk_tickets_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_tickets_prioridade` FOREIGN KEY (`id_prioridade`) REFERENCES `tbl_prioridades_ticket` (`id_prioridade`),
  CONSTRAINT `fk_tickets_status` FOREIGN KEY (`id_status`) REFERENCES `tbl_status_ticket` (`id_status`),
  CONSTRAINT `fk_tickets_usuario_atribuido` FOREIGN KEY (`id_usuario_atribuido`) REFERENCES `tbl_usuarios` (`id_usuario`),
  CONSTRAINT `fk_tickets_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `tbl_usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tbl_tickets` */

insert  into `tbl_tickets`(`id_ticket`,`numero_ticket`,`titulo`,`descricao`,`id_categoria`,`id_prioridade`,`id_status`,`id_usuario_solicitante`,`id_usuario_atribuido`,`id_filial`,`data_abertura`,`data_atualizacao`,`data_fechamento`,`tempo_resolucao`,`avaliacao`,`comentario_avaliacao`,`ativo`,`created_at`,`updated_at`) values 
(4,'TKT2025070001','TESTANDO TICKET NOVO.','TESTANDO TICKET NOVO.',3,2,1,1,1,1,'2025-07-29 15:21:39','2025-07-29 15:21:39',NULL,NULL,NULL,NULL,1,'2025-07-29 15:21:39','2025-07-29 15:21:39');

/*Table structure for table `tbl_tipos_movimentacao` */

DROP TABLE IF EXISTS `tbl_tipos_movimentacao`;

CREATE TABLE `tbl_tipos_movimentacao` (
  `id_tipo_movimentacao` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tipo_movimentacao`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_tipos_movimentacao` */

insert  into `tbl_tipos_movimentacao`(`id_tipo_movimentacao`,`nome`,`descricao`,`tipo`,`ativo`,`data_criacao`) values 
(1,'Compra','Entrada por compra de fornecedor','entrada',1,'2025-07-25 16:40:21'),
(2,'Venda','Saída por venda','saida',1,'2025-07-25 16:40:21'),
(3,'Transferência','Transferência entre filiais','saida',1,'2025-07-25 16:40:21'),
(4,'Ajuste','Ajuste de inventário','saida',1,'2025-07-25 16:40:21'),
(5,'Devolução','Devolução de cliente','entrada',1,'2025-07-25 16:40:21'),
(6,'Perda','Perda ou dano','saida',1,'2025-07-25 16:40:21');

/*Table structure for table `tbl_unidades_medida` */

DROP TABLE IF EXISTS `tbl_unidades_medida`;

CREATE TABLE `tbl_unidades_medida` (
  `id_unidade` int(11) NOT NULL AUTO_INCREMENT,
  `sigla` varchar(10) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_unidade`),
  UNIQUE KEY `sigla` (`sigla`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_unidades_medida` */

insert  into `tbl_unidades_medida`(`id_unidade`,`sigla`,`nome`,`descricao`,`ativo`,`data_criacao`) values 
(1,'UN','Unidade','Unidade individual',1,'2025-07-25 16:40:21'),
(2,'KG','Quilograma','Peso em quilogramas',1,'2025-07-25 16:40:21'),
(3,'L','Litro','Volume em litros',1,'2025-07-25 16:40:21'),
(4,'M','Metro','Comprimento em metros',1,'2025-07-25 16:40:21'),
(5,'M²','Metro Quadrado','Área em metros quadrados',1,'2025-07-25 16:40:21'),
(6,'CX','Caixa','Caixa com múltiplas unidades',1,'2025-07-25 16:40:21'),
(7,'PCT','Pacote','Pacote com múltiplas unidades',1,'2025-07-25 16:40:21');

/*Table structure for table `tbl_usuarios` */

DROP TABLE IF EXISTS `tbl_usuarios`;

CREATE TABLE `tbl_usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
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
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`),
  KEY `idx_usuarios_perfil` (`id_perfil`),
  KEY `idx_usuarios_filial` (`id_filial`),
  KEY `idx_usuarios_ativo` (`ativo`),
  CONSTRAINT `fk_usuarios_filial` FOREIGN KEY (`id_filial`) REFERENCES `tbl_filiais` (`id_filial`),
  CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `tbl_perfis` (`id_perfil`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_usuarios` */

insert  into `tbl_usuarios`(`id_usuario`,`nome_completo`,`email`,`senha`,`cpf`,`telefone`,`id_perfil`,`id_filial`,`ativo`,`ultimo_acesso`,`data_criacao`,`data_atualizacao`) values 
(1,'Administrador Sistema','admin@gruposorrisos.com.br','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','123.456.789-00','(11) 99999-9999',1,1,1,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(2,'Maria Silva','maria@gruposorrisos.com.br','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','111.222.333-44','(11) 88888-8888',2,1,1,NULL,'2025-07-25 16:40:21','2025-07-25 16:40:21'),
(6,'Setor Compras','compras@gruposorrisos.com.br','$2y$10$.vF67OfG2iQlhuGfpuZNmOvD.PRW29msRYjFNUL/vopite6RNbYeW','43439437984897','932983293929',6,25,1,NULL,'2025-08-13 19:50:01','2025-08-13 19:50:01'),
(11,'Fornecedor','promautone@gmail.com','$2y$10$8ArYUXDXvktXIO1zax.uHO6.Gqcx2GN5oOEk0Ij6RCLS0z6/4gs0K','303.807.108-03','11984401158',5,NULL,1,NULL,'2025-08-13 20:03:12','2025-08-13 20:03:12'),
(12,'Gerencia','gerente@gruposorrisos.com.br','$2y$10$.73ZJ9T0taJmlBhFggjUy.prbzc7We2p1EpFajb9SkN7TirT5Qxhu','30380708088','1185888888',2,1,1,NULL,'2025-08-13 20:25:38','2025-08-18 19:46:40'),
(13,'Fornecedor 222','contato@fornecedor.brs','$2y$10$.73ZJ9T0taJmlBhFggjUy.prbzc7We2p1EpFajb9SkN7TirT5Qxhu',NULL,NULL,5,NULL,1,NULL,'2025-08-13 22:18:23','2025-08-13 22:20:58'),
(14,'Fornecedor333','grupo.amf.center@gmail.com','$2y$10$9Ni9X/sh8QVacD1CDG9/buUKGQTxDU/IfU5evMPdHfEc7xt.HV0aa',NULL,NULL,5,NULL,1,NULL,'2025-08-13 22:22:02','2025-08-13 22:22:02');

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_movimentacao_estoque_filial` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_movimentacao_estoque_filial` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    
    -- Atualizar estoque da filial origem (se houver)
    IF NEW.id_filial_origem IS NOT NULL THEN
        -- Buscar estoque atual
        SELECT COALESCE(estoque_atual, 0) INTO v_estoque_atual
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_origem;
        
        -- Inserir ou atualizar registro
        INSERT INTO tbl_estoque_filial (id_material, id_filial, estoque_atual, ultima_movimentacao)
        VALUES (NEW.id_material, NEW.id_filial_origem, NEW.estoque_atual_origem, NEW.data_movimentacao)
        ON DUPLICATE KEY UPDATE 
            estoque_atual = NEW.estoque_atual_origem,
            ultima_movimentacao = NEW.data_movimentacao;
    END IF;
    
    -- Atualizar estoque da filial destino (se houver)
    IF NEW.id_filial_destino IS NOT NULL THEN
        -- Buscar estoque atual
        SELECT COALESCE(estoque_atual, 0) INTO v_estoque_atual
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_destino;
        
        -- Inserir ou atualizar registro
        INSERT INTO tbl_estoque_filial (id_material, id_filial, estoque_atual, ultima_movimentacao)
        VALUES (NEW.id_material, NEW.id_filial_destino, NEW.estoque_atual_destino, NEW.data_movimentacao)
        ON DUPLICATE KEY UPDATE 
            estoque_atual = NEW.estoque_atual_destino,
            ultima_movimentacao = NEW.data_movimentacao;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_auditoria_movimentacao` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_auditoria_movimentacao` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    INSERT INTO tbl_auditoria_movimentacoes (
        id_movimentacao, 
        acao, 
        dados_novos, 
        id_usuario, 
        data_auditoria
    ) VALUES (
        NEW.id_movimentacao,
        'criacao',
        JSON_OBJECT(
            'numero_movimentacao', NEW.numero_movimentacao,
            'tipo_movimentacao', NEW.tipo_movimentacao,
            'quantidade', NEW.quantidade,
            'valor_total', NEW.valor_total,
            'status_movimentacao', NEW.status_movimentacao
        ),
        NEW.id_usuario_executor,
        NOW()
    );
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_atualizar_estoque_material` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_atualizar_estoque_material` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    -- Atualizar estoque na tabela de materiais (filial principal)
    IF NEW.id_filial_destino IS NOT NULL THEN
        UPDATE tbl_materiais 
        SET estoque_atual = NEW.estoque_atual_destino,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_destino;
    ELSEIF NEW.id_filial_origem IS NOT NULL THEN
        UPDATE tbl_materiais 
        SET estoque_atual = NEW.estoque_atual_origem,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_material = NEW.id_material AND id_filial = NEW.id_filial_origem;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_alerta_estoque_baixo` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_alerta_estoque_baixo` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    DECLARE v_estoque_minimo DECIMAL(15,3) DEFAULT 0;
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    DECLARE v_filial_id INT DEFAULT NULL;
    
    -- Determinar filial afetada
    IF NEW.id_filial_destino IS NOT NULL THEN
        SET v_filial_id = NEW.id_filial_destino;
        SET v_estoque_atual = NEW.estoque_atual_destino;
    ELSEIF NEW.id_filial_origem IS NOT NULL THEN
        SET v_filial_id = NEW.id_filial_origem;
        SET v_estoque_atual = NEW.estoque_atual_origem;
    END IF;
    
    -- Buscar estoque mínimo
    IF v_filial_id IS NOT NULL THEN
        SELECT COALESCE(estoque_minimo, 0) INTO v_estoque_minimo
        FROM tbl_estoque_filial 
        WHERE id_material = NEW.id_material AND id_filial = v_filial_id;
        
        -- Gerar alerta de estoque baixo
        IF v_estoque_atual <= v_estoque_minimo AND v_estoque_atual > 0 THEN
            INSERT INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual, 
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                NEW.id_material, v_filial_id, 'estoque_baixo', v_estoque_atual,
                v_estoque_minimo, 'media', 
                CONCAT('Estoque baixo detectado após movimentação. Quantidade atual: ', v_estoque_atual)
            );
        END IF;
        
        -- Gerar alerta de estoque zerado
        IF v_estoque_atual = 0 THEN
            INSERT INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                prioridade, mensagem
            ) VALUES (
                NEW.id_material, v_filial_id, 'estoque_zerado', v_estoque_atual,
                'alta', 'Estoque zerado - necessário reposição urgente'
            );
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_atualizar_custo_medio` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_atualizar_custo_medio` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    DECLARE v_custo_medio DECIMAL(15,4) DEFAULT 0;
    DECLARE v_filial_id INT DEFAULT NULL;
    
    -- Só atualizar custo médio para entradas
    IF NEW.tipo_movimentacao = 'entrada' AND NEW.valor_total > 0 AND NEW.quantidade > 0 THEN
        -- Determinar filial
        IF NEW.id_filial_destino IS NOT NULL THEN
            SET v_filial_id = NEW.id_filial_destino;
        ELSEIF NEW.id_filial_origem IS NOT NULL THEN
            SET v_filial_id = NEW.id_filial_origem;
        END IF;
        
        IF v_filial_id IS NOT NULL THEN
            -- Calcular novo custo médio
            SELECT 
                CASE 
                    WHEN SUM(quantidade) > 0 THEN SUM(valor_total) / SUM(quantidade)
                    ELSE 0 
                END INTO v_custo_medio
            FROM tbl_movimentacoes 
            WHERE id_material = NEW.id_material 
            AND id_filial_destino = v_filial_id 
            AND tipo_movimentacao = 'entrada'
            AND status_movimentacao = 'executada';
            
            -- Atualizar custo médio
            UPDATE tbl_estoque_filial 
            SET custo_medio = v_custo_medio 
            WHERE id_material = NEW.id_material AND id_filial = v_filial_id;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_atualizar_lote` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_atualizar_lote` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    IF NEW.id_lote IS NOT NULL THEN
        -- Atualizar quantidade do lote
        UPDATE tbl_lotes 
        SET quantidade_atual = quantidade_atual - NEW.quantidade,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_lote = NEW.id_lote;
        
        -- Marcar lote como consumido se quantidade_atual <= 0
        UPDATE tbl_lotes 
        SET status = 'consumido',
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id_lote = NEW.id_lote AND quantidade_atual <= 0;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `tbl_movimentacoes` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tr_verificar_vencimento_lote` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'u460638534_sorrisos'@'localhost' */ /*!50003 TRIGGER `tr_verificar_vencimento_lote` AFTER INSERT ON `tbl_movimentacoes` FOR EACH ROW 
BEGIN
    DECLARE v_data_validade DATE DEFAULT NULL;
    DECLARE v_dias_vencimento INT DEFAULT 0;
    DECLARE v_filial_id INT DEFAULT NULL;
    
    IF NEW.id_lote IS NOT NULL THEN
        -- Buscar data de validade do lote
        SELECT data_validade INTO v_data_validade
        FROM tbl_lotes 
        WHERE id_lote = NEW.id_lote;
        
        -- Determinar filial
        IF NEW.id_filial_destino IS NOT NULL THEN
            SET v_filial_id = NEW.id_filial_destino;
        ELSEIF NEW.id_filial_origem IS NOT NULL THEN
            SET v_filial_id = NEW.id_filial_origem;
        END IF;
        
        IF v_data_validade IS NOT NULL AND v_filial_id IS NOT NULL THEN
            SET v_dias_vencimento = DATEDIFF(v_data_validade, CURDATE());
            
            -- Alerta de vencimento próximo (30 dias)
            IF v_dias_vencimento <= 30 AND v_dias_vencimento > 0 THEN
                INSERT INTO tbl_alertas_estoque (
                    id_material, id_filial, tipo_alerta, quantidade_atual,
                    data_vencimento, dias_vencimento, prioridade, mensagem
                ) VALUES (
                    NEW.id_material, v_filial_id, 'vencimento_proximo', NEW.quantidade,
                    v_data_validade, v_dias_vencimento, 'media',
                    CONCAT('Lote vence em ', v_dias_vencimento, ' dias')
                );
            END IF;
            
            -- Alerta de vencido
            IF v_dias_vencimento < 0 THEN
                INSERT INTO tbl_alertas_estoque (
                    id_material, id_filial, tipo_alerta, quantidade_atual,
                    data_vencimento, dias_vencimento, prioridade, mensagem
                ) VALUES (
                    NEW.id_material, v_filial_id, 'vencido', NEW.quantidade,
                    v_data_validade, v_dias_vencimento, 'alta',
                    CONCAT('Lote vencido há ', ABS(v_dias_vencimento), ' dias')
                );
            END IF;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Function  structure for function  `fn_calcular_valor_estoque` */

/*!50003 DROP FUNCTION IF EXISTS `fn_calcular_valor_estoque` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` FUNCTION `fn_calcular_valor_estoque`(p_id_material INT,
    p_id_filial INT
) RETURNS decimal(15,4)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE v_valor_total DECIMAL(15,4) DEFAULT 0;
    
    SELECT COALESCE(estoque_atual * custo_medio, 0)
    INTO v_valor_total
    FROM tbl_estoque_filial
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    RETURN v_valor_total;
END */$$
DELIMITER ;

/* Function  structure for function  `fn_dias_ate_vencimento` */

/*!50003 DROP FUNCTION IF EXISTS `fn_dias_ate_vencimento` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` FUNCTION `fn_dias_ate_vencimento`(p_data_validade DATE
) RETURNS int(11)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    RETURN DATEDIFF(p_data_validade, CURDATE());
END */$$
DELIMITER ;

/* Function  structure for function  `fn_verificar_estoque_suficiente` */

/*!50003 DROP FUNCTION IF EXISTS `fn_verificar_estoque_suficiente` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` FUNCTION `fn_verificar_estoque_suficiente`(p_id_material INT,
    p_id_filial INT,
    p_quantidade DECIMAL(15,3)
) RETURNS tinyint(1)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE v_estoque_atual DECIMAL(15,3) DEFAULT 0;
    
    SELECT COALESCE(estoque_atual, 0)
    INTO v_estoque_atual
    FROM tbl_estoque_filial
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    RETURN v_estoque_atual >= p_quantidade;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_backup_dados_criticos` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_backup_dados_criticos` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_backup_dados_criticos`()
BEGIN
    -- Criar tabela de backup com timestamp
    SET @backup_table = CONCAT('backup_movimentacoes_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'));
    SET @sql = CONCAT('CREATE TABLE ', @backup_table, ' AS SELECT * FROM tbl_movimentacoes');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    SELECT CONCAT('Backup criado: ', @backup_table) as resultado;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_calcular_custo_medio` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_calcular_custo_medio` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_calcular_custo_medio`(
    IN p_id_material INT,
    IN p_id_filial INT
)
BEGIN
    DECLARE v_custo_medio DECIMAL(15,4);
    
    SELECT 
        CASE 
            WHEN SUM(quantidade) > 0 THEN SUM(valor_total) / SUM(quantidade)
            ELSE 0 
        END INTO v_custo_medio
    FROM tbl_movimentacoes 
    WHERE id_material = p_id_material 
    AND id_filial_destino = p_id_filial 
    AND tipo_movimentacao = 'entrada'
    AND status_movimentacao = 'executada';
    
    UPDATE tbl_estoque_filial 
    SET custo_medio = v_custo_medio 
    WHERE id_material = p_id_material AND id_filial = p_id_filial;
    
    SELECT v_custo_medio as custo_medio_calculado;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_gerar_alertas_estoque` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_gerar_alertas_estoque` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_gerar_alertas_estoque`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_material, v_id_filial INT;
    DECLARE v_estoque_atual, v_estoque_minimo, v_estoque_maximo DECIMAL(15,3);
    DECLARE v_cursor CURSOR FOR 
        SELECT id_material, id_filial, estoque_atual, estoque_minimo, estoque_maximo 
        FROM tbl_estoque_filial;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN v_cursor;
    
    read_loop: LOOP
        FETCH v_cursor INTO v_id_material, v_id_filial, v_estoque_atual, v_estoque_minimo, v_estoque_maximo;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Alerta de estoque baixo
        IF v_estoque_atual <= v_estoque_minimo AND v_estoque_atual > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual, 
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_baixo', v_estoque_atual,
                v_estoque_minimo, 'media', CONCAT('Estoque baixo: ', v_estoque_atual, ' unidades')
            );
        END IF;
        
        -- Alerta de estoque zerado
        IF v_estoque_atual = 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_zerado', v_estoque_atual,
                'alta', 'Estoque zerado - necessário reposição urgente'
            );
        END IF;
        
        -- Alerta de estoque alto
        IF v_estoque_atual > v_estoque_maximo AND v_estoque_maximo > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                quantidade_referencia, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'estoque_alto', v_estoque_atual,
                v_estoque_maximo, 'baixa', CONCAT('Estoque alto: ', v_estoque_atual, ' unidades')
            );
        END IF;
        
    END LOOP;
    
    CLOSE v_cursor;
    
    SELECT 'Alertas de estoque gerados com sucesso!' as resultado;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_gerar_numero_movimentacao` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_gerar_numero_movimentacao` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_gerar_numero_movimentacao`(
    OUT p_numero_movimentacao VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_movimentacao, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_movimentacoes 
    WHERE numero_movimentacao LIKE CONCAT('MOV-', v_ano, '-%');
    
    SET v_numero = CONCAT('MOV-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_movimentacoes WHERE numero_movimentacao = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('MOV-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_movimentacao = v_numero;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_gerar_numero_pedido` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_gerar_numero_pedido` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_gerar_numero_pedido`(
    OUT p_numero_pedido VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_pedido, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_pedidos_compra 
    WHERE numero_pedido LIKE CONCAT('PED-', v_ano, '-%');
    
    SET v_numero = CONCAT('PED-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_pedidos_compra WHERE numero_pedido = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('PED-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_pedido = v_numero;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_gerar_numero_ticket` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_gerar_numero_ticket` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_gerar_numero_ticket`(
    OUT p_numero_ticket VARCHAR(20)
)
BEGIN
    DECLARE v_ano INT DEFAULT YEAR(CURDATE());
    DECLARE v_sequencial INT DEFAULT 1;
    DECLARE v_numero VARCHAR(20);
    
    -- Buscar último sequencial do ano
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_ticket, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequencial
    FROM tbl_tickets 
    WHERE numero_ticket LIKE CONCAT('TKT-', v_ano, '-%');
    
    SET v_numero = CONCAT('TKT-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    
    -- Verificar se já existe
    WHILE EXISTS (SELECT 1 FROM tbl_tickets WHERE numero_ticket = v_numero) DO
        SET v_sequencial = v_sequencial + 1;
        SET v_numero = CONCAT('TKT-', v_ano, '-', LPAD(v_sequencial, 6, '0'));
    END WHILE;
    
    SET p_numero_ticket = v_numero;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_limpar_alertas_antigos` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_limpar_alertas_antigos` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_limpar_alertas_antigos`(
    IN p_dias INT
)
BEGIN
    DECLARE v_dias INT DEFAULT 30;
    
    -- Se p_dias for NULL, usar valor padrão
    IF p_dias IS NULL THEN
        SET v_dias = 30;
    ELSE
        SET v_dias = p_dias;
    END IF;
    
    DELETE FROM tbl_alertas_estoque 
    WHERE data_criacao < DATE_SUB(NOW(), INTERVAL v_dias DAY)
    AND status IN ('resolvido', 'ignorado');
    
    SELECT CONCAT('Alertas antigos removidos (mais de ', v_dias, ' dias)') as resultado;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_verificar_vencimento_lotes` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_verificar_vencimento_lotes` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`u460638534_sorrisos`@`localhost` PROCEDURE `sp_verificar_vencimento_lotes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_lote, v_id_material, v_id_filial INT;
    DECLARE v_data_validade DATE;
    DECLARE v_dias_vencimento INT;
    DECLARE v_cursor CURSOR FOR 
        SELECT id_lote, id_material, id_filial, data_validade
        FROM tbl_lotes 
        WHERE status = 'ativo' AND data_validade IS NOT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN v_cursor;
    
    read_loop: LOOP
        FETCH v_cursor INTO v_id_lote, v_id_material, v_id_filial, v_data_validade;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET v_dias_vencimento = DATEDIFF(v_data_validade, CURDATE());
        
        -- Alerta de vencimento próximo (30 dias)
        IF v_dias_vencimento <= 30 AND v_dias_vencimento > 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                data_vencimento, dias_vencimento, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'vencimento_proximo', 0,
                v_data_validade, v_dias_vencimento, 'media',
                CONCAT('Lote vence em ', v_dias_vencimento, ' dias')
            );
        END IF;
        
        -- Alerta de vencido
        IF v_dias_vencimento < 0 THEN
            INSERT IGNORE INTO tbl_alertas_estoque (
                id_material, id_filial, tipo_alerta, quantidade_atual,
                data_vencimento, dias_vencimento, prioridade, mensagem
            ) VALUES (
                v_id_material, v_id_filial, 'vencido', 0,
                v_data_validade, v_dias_vencimento, 'alta',
                CONCAT('Lote vencido há ', ABS(v_dias_vencimento), ' dias')
            );
            
            -- Marcar lote como vencido
            UPDATE tbl_lotes SET status = 'vencido' WHERE id_lote = v_id_lote;
        END IF;
        
    END LOOP;
    
    CLOSE v_cursor;
    
    SELECT 'Verificação de vencimento concluída!' as resultado;
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
