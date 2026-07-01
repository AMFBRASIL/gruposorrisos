-- Permissões de pedidos de compra por perfil × situação (status)
CREATE TABLE IF NOT EXISTS tbl_perfil_pedido_status (
    id_perfil INT NOT NULL,
    status_pedido VARCHAR(64) NOT NULL,
    pode_ver TINYINT(1) NOT NULL DEFAULT 0,
    pode_editar TINYINT(1) NOT NULL DEFAULT 0,
    pode_avancar TINYINT(1) NOT NULL DEFAULT 0,
    pode_cancelar TINYINT(1) NOT NULL DEFAULT 0,
    pode_voltar TINYINT(1) NOT NULL DEFAULT 0,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_perfil, status_pedido),
    KEY idx_perfil_pedido_status_perfil (id_perfil),
    CONSTRAINT fk_perfil_pedido_status_perfil FOREIGN KEY (id_perfil) REFERENCES tbl_perfis (id_perfil) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
