<?php

// 🏠 HOME
$router->get('', [HomeController::class, 'index']);
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);
$router->post('/contato/enviar', [HomeController::class, 'enviarContato']);

// 🔐 AUTENTICAÇÃO
$router->get('/login', [AuthController::class, 'login']);
$router->get('/login.php', [AuthController::class, 'login']);

$router->post('/authenticate', [AuthController::class, 'authenticate']);
$router->post('/authenticate.php', [AuthController::class, 'authenticate']);

$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/logout.php', [AuthController::class, 'logout']);

// 📊 DASHBOARD
$router->get('/area-restrita', [DashboardController::class, 'index']);
$router->get('/area-restrita.php', [DashboardController::class, 'index']);

// ⚙️ CONFIG
$router->get('/config/mensagens', [ConfigController::class, 'mensagens']);
$router->get('/config/email-template', [ConfigController::class, 'emailTemplate']);
$router->get('/config/contato', [ConfigController::class, 'contato']);
$router->post('/config/salvar-contato', [ConfigController::class, 'salvarContato']);
$router->get('/config/sobre', [ConfigController::class, 'sobre']);
$router->post('/config/salvar-sobre-nos', [ConfigController::class, 'salvarSobreNos']);
$router->get('/config/usuarios', [ConfigController::class, 'usuarios']);
$router->post('/config/salvar-usuario-tipo', [ConfigController::class, 'salvarUsuarioTipo']);
$router->post('/config/criar-usuario', [ConfigController::class, 'criarUsuario']);
$router->get('/config/propostas', [ConfigController::class, 'propostas']);
$router->post('/config/salvar-proposta', [ConfigController::class, 'salvarProposta']);
$router->post('/config/atualizar-proposta', [ConfigController::class, 'atualizarProposta']);
$router->post('/config/excluir-proposta', [ConfigController::class, 'excluirProposta']);
$router->get('/config/inicio', [ConfigController::class, 'inicio']);
$router->post('/config/salvar-dados-inicio', [ConfigController::class, 'salvarInicio']);