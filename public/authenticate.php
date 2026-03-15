<?php
/**
 * Entrada para POST do formulário de login quando a ação é /authenticate.
 * Mantém compatibilidade com o uso de rotas (BASE_URL = '/').
 */
define('BASE_URL', '/');
require_once __DIR__ . '/bootstrap.php';

(new AuthController())->authenticate();
