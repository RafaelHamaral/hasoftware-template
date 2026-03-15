<?php
/**
 * Área restrita – página protegida por sessão.
 * Redireciona para login se o usuário não estiver autenticado.
 */

require_once __DIR__ . '/../config/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (BASE_URL ?: '') . (BASE_URL ? 'login' : 'login.php'));
    exit;
}

$title = 'Área restrita';
require BASE_PATH . '/app/views/area-restrita.php';
