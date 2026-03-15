<?php
/**
 * Definição das rotas (requer $router já instanciado).
 */

$router->get('', [HomeController::class, 'index']);
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->get('/login.php', [AuthController::class, 'login']);

$router->post('/authenticate', [AuthController::class, 'authenticate']);

$router->get('/area-restrita', [AuthController::class, 'dashboard']);
$router->get('/area-restrita.php', [AuthController::class, 'dashboard']);

$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/logout.php', [AuthController::class, 'logout']);
