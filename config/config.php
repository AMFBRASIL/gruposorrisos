<?php
// Configuration file for API keys and settings
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

// Database Configuration (if not already in conexao.php)
###############################################################
# PRODUCAO DB
###############################################################

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gruposorrisos');
define('DB_USER', getenv('DB_USER') ?: 'gruposorrisos');
define('DB_PASS', getenv('DB_PASS') ?: 'YeKcbHEBYnittDWc');

#define('DB_HOST', getenv('DB_HOST') ?: '72.61.59.152');
#define('DB_NAME', getenv('DB_NAME') ?: 'gruposhomolog');
#define('DB_USER', getenv('DB_USER') ?: 'GrupoSHomolog');#
#define('DB_PASS', getenv('DB_PASS') ?: 'n2Ga3bPPDBr5Wn4e');

#PRODUCAO
#define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
#define('DB_NAME', getenv('DB_NAME') ?: 'gruposorrisos');
#define('DB_USER', getenv('DB_USER') ?: 'gruposorrisos');
#define('DB_PASS', getenv('DB_PASS') ?: 'YeKcbHEBYnittDWc');

// Application Settings
define('APP_NAME', 'Grupo Sorrisos - Estoque');
define('APP_VERSION', '3.0.0');

/** Fallback se tbl_configuracoes não tiver a chave pedidos_desconto_fornecedor_percentual */
define('PEDIDOS_DESCONTO_FORNECEDOR_PERCENTUAL', 5);

// AI Analysis Settings
define('AI_MODEL', 'o4-mini-2025-04-16');
define('AI_MAX_TOKENS', 150000); // Máximo para GPT-4-turbo (4096 - margem de segurança)
define('AI_TEMPERATURE', 0.3);

// Error Reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>