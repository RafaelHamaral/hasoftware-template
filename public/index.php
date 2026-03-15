<?php
/**
 * Front controller: todas as requisições passam por aqui quando o servidor usa rewrite.
 * Define BASE_URL para rotas bonitas e despacha o Router.
 *
 * Para usar rotas /login, /area-restrita etc., configure o servidor para enviar
 * todas as requisições para index.php (ex.: PHP built-in: php -S localhost:8000 -t public public/index.php).
 */

define('BASE_URL', '/');
require_once __DIR__ . '/bootstrap.php';

$router = new Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
