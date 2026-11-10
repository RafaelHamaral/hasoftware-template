<?php

/**
 * Bootstrap de linha de comando: BASE_PATH, .env e autoload, sem sessão HTTP.
 */

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('O bootstrap CLI não pode ser carregado via HTTP.');
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

spl_autoload_register(function (string $class): void {
    $base = BASE_PATH;
    $paths = [
        $base . '/app/console/' . $class . '.php',
        $base . '/app/core/' . $class . '.php',
        $base . '/app/controllers/' . $class . '.php',
        $base . '/app/models/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
