<?php
/**
 * Processamento do formulário de login (POST para auth/login-process.php).
 * Usa config e database fora de public/; redireciona com BASE_URL quando definido.
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . (BASE_URL ? BASE_URL . 'login' : '../login.php'));
    exit;
}

$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';
$errors   = [];

if (empty($email)) {
    $errors[] = 'Informe o seu e-mail.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Informe um e-mail válido.';
}

if ($password === '') {
    $errors[] = 'Informe a sua senha.';
} else {
    if (strlen($password) < 6) {
        $errors[] = 'A senha deve ter no mínimo 6 caracteres.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A senha deve conter ao menos 1 letra maiúscula.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A senha deve conter ao menos 1 letra minúscula.';
    }
    if (!preg_match('/[!@#$%^&*()_\-+=\{\}\[\]:;,.<>?~]/', $password)) {
        $errors[] = 'A senha deve conter ao menos 1 caractere especial.';
    }
}

if (!empty($errors)) {
    $_SESSION['login_errors']    = $errors;
    $_SESSION['login_old_email'] = $email;
    header('Location: ' . (BASE_URL ? BASE_URL . 'login' : '../login.php'));
    exit;
}

try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT id, name, email, password, type FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_errors']    = ['E-mail ou senha inválidos. Verifique os dados e tente novamente.'];
        $_SESSION['login_old_email'] = $email;
        header('Location: ' . (BASE_URL ? BASE_URL . 'login' : '../login.php'));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type']  = $user['type'] ?? 'viewer';
    
    header('Location: ' . (BASE_URL ? BASE_URL . 'area-restrita' : 'area-restrita.php'));
    exit;
} catch (Throwable $e) {
    $_SESSION['login_errors'] = ['Ocorreu um erro ao tentar autenticar. Tente novamente em alguns instantes.'];
    header('Location: ' . (BASE_URL ? BASE_URL . 'login' : 'login.php'));
    exit;
}
