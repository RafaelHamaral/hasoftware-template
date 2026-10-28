<?php
/**
 * Página de login (acesso direto por /login.php).
 * Reutiliza partials e view para evitar duplicação.
 */
require_once __DIR__ . '/../config/config.php';

session_start();

$loginErrors = $_SESSION['login_errors'] ?? [];
$oldEmail    = $_SESSION['login_old_email'] ?? '';
unset($_SESSION['login_errors'], $_SESSION['login_old_email']);

$title     = 'Login | H.A Consultoria & Desenvolvimento';
$bodyClass = 'auth-page';

require BASE_PATH . '/app/views/login.php';