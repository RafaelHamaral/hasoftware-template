<?php

define('BASE_URL', '/');
require_once __DIR__ . '/bootstrap.php';

$router = new Router();
require BASE_PATH . '/routes/web.php';

// 🔧 NORMALIZAÇÃO DA URL (ESSENCIAL)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// remove barra final (exceto raiz)
$uri = rtrim($uri, '/') ?: '/';

// dispatch corrigido
$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);