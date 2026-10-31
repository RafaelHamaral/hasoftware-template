<?php
/**
 * Entrada para POST do formulário de login quando a ação é /authenticate.
 * Não autentica por conta própria; encaminha ao AuthController.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login', true, 302);
    exit;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
require_once __DIR__ . '/bootstrap.php';

(new AuthController())->authenticate();
