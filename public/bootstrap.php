<?php
/**
 * Bootstrap: config, sessão e autoload.
 * Carregado pelo front controller (index.php) e por authenticate.php.
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

spl_autoload_register(function (string $class): void {
    $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
    $paths = [
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
