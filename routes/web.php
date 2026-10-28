<?php

// 🏠 HOME
$router->get('', [HomeController::class, 'index']);
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);

// 🔐 AUTENTICAÇÃO
$router->get('/login', [AuthController::class, 'login']);
$router->get('/login.php', [AuthController::class, 'login']);

$router->post('/authenticate', [AuthController::class, 'authenticate']);

$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/logout.php', [AuthController::class, 'logout']);

// 📊 DASHBOARD
$router->get('/area-restrita', [DashboardController::class, 'index']);
$router->get('/area-restrita.php', [DashboardController::class, 'index']);

// ⚙️ CONFIG
$router->get('/config/email-template', [ConfigController::class, 'emailTemplate']);
$router->get('/config/contato', [ConfigController::class, 'contato']);
$router->get('/config/usuarios', [ConfigController::class, 'usuarios']);