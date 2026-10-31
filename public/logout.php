<?php
/**
 * Compatibilidade: não encerra a sessão por conta própria.
 * Encaminha ao AuthController oficial.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
require_once __DIR__ . '/bootstrap.php';

(new AuthController())->logout();
