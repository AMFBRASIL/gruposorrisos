-- Vínculo usuário ↔ múltiplas filiais (unidades)
CREATE TABLE IF NOT EXISTS tbl_usuario_filiais (
    id_usuario INT NOT NULL,
    id_filial INT NOT NULL,
    data_vinculo TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_filial),
    KEY idx_usuario_filial_filial (id_filial),
    CONSTRAINT fk_usuario_filiais_usuario FOREIGN KEY (id_usuario) REFERENCES tbl_usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_usuario_filiais_filial FOREIGN KEY (id_filial) REFERENCES tbl_filiais (id_filial) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar vínculo único já existente em tbl_usuarios.id_filial
INSERT IGNORE INTO tbl_usuario_filiais (id_usuario, id_filial)
SELECT id_usuario, id_filial
FROM tbl_usuarios
WHERE id_filial IS NOT NULL AND id_filial > 0;
