<?php
/**
 * Compatibilidade: não autentica. Encaminha ao AuthController oficial.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login', true, 302);
    exit;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
require_once __DIR__ . '/../bootstrap.php';

(new AuthController())->authenticate();
