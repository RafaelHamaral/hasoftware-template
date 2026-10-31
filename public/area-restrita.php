<?php
/**
 * Compatibilidade: não renderiza a área restrita por conta própria.
 * Encaminha ao DashboardController oficial (auth no backend).
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
require_once __DIR__ . '/bootstrap.php';

(new DashboardController())->index();
