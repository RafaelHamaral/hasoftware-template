<?php
/**
 * Configuração global do projeto.
 * Carregado pelo front controller e por páginas standalone (login, area-restrita, logout).
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// URL base da aplicação ('' se document root é public/; '/' ou '/pasta/' se usar rewrite).
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

return [
    'base_url' => BASE_URL,
];
