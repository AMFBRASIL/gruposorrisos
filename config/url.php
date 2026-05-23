<?php
/**
 * URLs amigáveis (sem .php na barra do navegador).
 * Banco/permissões continuam usando material.php internamente.
 */

if (!function_exists('app_url')) {
    /**
     * URL pública de uma página (ex.: material.php → material).
     */
    function app_url($pagina = '', array $query = []) {
        $pagina = trim((string) $pagina);
        if ($pagina === '' || $pagina === '/') {
            return './';
        }
        $pagina = str_replace('\\', '/', $pagina);
        if (preg_match('#^(backend|api|fornecedor|vendor|uploads|assets)/#i', $pagina)) {
            return $pagina;
        }
        $pagina = basename($pagina);
        $pagina = preg_replace('/\.php$/i', '', $pagina);
        if (!empty($query)) {
            $pagina .= '?' . http_build_query($query);
        }
        return $pagina;
    }
}

if (!function_exists('pagina_url_banco')) {
    /**
     * Nome do arquivo para permissões (sempre com .php).
     */
    function pagina_url_banco($url = null) {
        if ($url === null || $url === '') {
            $url = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'index.php';
        }
        $url = basename($url);
        if (!preg_match('/\.php$/i', $url)) {
            $url .= '.php';
        }
        return $url;
    }
}

if (!function_exists('pagina_atual_slug')) {
    function pagina_atual_slug() {
        $self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'index.php';
        return preg_replace('/\.php$/i', '', basename($self));
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to($pagina, array $query = []) {
        header('Location: ' . app_url($pagina, $query));
        exit;
    }
}
