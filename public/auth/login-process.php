<?php
/**
 * Processamento do formulário de login.
 *
 * Responsável por:
 * - Validar os campos enviados (email e senha)
 * - Buscar o usuário no banco de dados
 * - Verificar a senha usando password_verify
 * - Iniciar a sessão em caso de sucesso e redirecionar para área restrita
 * - Definir mensagens de erro amigáveis em caso de falha
 */

session_start();

require_once __DIR__ . '/../config/database.php';

// Garante que a requisição veio por POST (envio do formulário).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}

// Captura e normaliza os campos enviados.
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

$errors = [];

// ---------- Validação do email ----------
if (empty($email)) {
    $errors[] = 'Informe o seu e-mail.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Informe um e-mail válido.';
}

// ---------- Validação da senha ----------
if ($password === '') {
    $errors[] = 'Informe a sua senha.';
} else {
    // Regras: mínimo 6 caracteres, 1 maiúscula, 1 minúscula, 1 caractere especial.
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

// Se houver erros de validação, retorna para a tela de login com mensagens.
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_old_email'] = $email;
    header('Location: /login.php');
    exit;
}

try {
    $pdo = getPDO();

    // Busca o usuário pelo e-mail.
    $stmt = $pdo->prepare('SELECT id, nome, email, senha_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta.
    if (!$user || !password_verify($password, $user['senha_hash'])) {
        $_SESSION['login_errors'] = ['E-mail ou senha inválidos. Verifique os dados e tente novamente.'];
        $_SESSION['login_old_email'] = $email;
        header('Location: /login.php');
        exit;
    }

    // (Opcional) Se no futuro você alterar o algoritmo/custo de hash,
    // pode usar password_needs_rehash aqui para atualizar o hash.

    // Login bem-sucedido: regenera o ID da sessão e salva dados mínimos do usuário.
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nome'];
    $_SESSION['user_email'] = $user['email'];

    // Redireciona para a área restrita (crie essa página depois, ex: area-restrita.php).
    header('Location: /area-restrita.php');
    exit;
} catch (Throwable $e) {
    // Em produção, evite exibir detalhes do erro para o usuário.
    $_SESSION['login_errors'] = ['Ocorreu um erro ao tentar autenticar. Tente novamente em alguns instantes.'];
    header('Location: /login.php');
    exit;
}

