<?php
require_once __DIR__ . '/config.php';

class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Criar conexão global para compatibilidade com APIs existentes
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    error_log("Erro de conexão com banco: " . $exception->getMessage());
    $pdo = null;
}

// Configurações globais
define('TIMEZONE', 'America/Sao_Paulo');
define('JWT_SECRET', 'sua_chave_secreta_jwt_aqui_2024');
define('JWT_EXPIRATION', 3600); // 1 hora

date_default_timezone_set(TIMEZONE);

define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'xls']);

define('BACKUP_PATH', 'backups/');
define('BACKUP_RETENTION_DAYS', 30);
?> 