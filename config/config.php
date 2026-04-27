<?php
// Configuration file for API keys and settings
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

// Database Configuration (if not already in conexao.php)
###############################################################
# PRODUCAO DB
###############################################################
#PRODUCAO
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u293057343_sorrisos');
define('DB_USER', getenv('DB_USER') ?: 'u293057343_sorrisos');
define('DB_PASS', getenv('DB_PASS') ?: 'ean7G#lc7X+');

// Application Settings
define('APP_NAME', 'Grupo Sorrisos - Estoque');
define('APP_VERSION', '1.0.0');

// AI Analysis Settings
define('AI_MODEL', 'o4-mini-2025-04-16');
define('AI_MAX_TOKENS', 150000); // Máximo para GPT-4-turbo (4096 - margem de segurança)
define('AI_TEMPERATURE', 0.3);

// Error Reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>