<?php

/**
 * Controller de autenticação: login, processar login, logout.
 */

class AuthController
{
    public function login(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'area-restrita');
            exit;
        }

        $loginErrors = $_SESSION['login_errors'] ?? [];
        $oldEmail    = $_SESSION['login_old_email'] ?? '';
        unset($_SESSION['login_errors'], $_SESSION['login_old_email']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];

        $title = 'Login | H. A Consultoria & Desenvolvimento';
        require BASE_PATH . '/app/views/login.php';
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // 1. CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['login_errors'] = ['Sessão expirada. Tente novamente.'];
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // 2. Rate limit (ANTES de consultar o banco)
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $last     = $_SESSION['login_last'] ?? 0;

        if ($attempts >= 5 && (time() - $last) < 300) {
            $_SESSION['login_errors'] = ['Muitas tentativas. Aguarde alguns minutos.'];
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        // Janela expirada: zera o contador
        if ((time() - $last) >= 300) {
            $attempts = 0;
            $_SESSION['login_attempts'] = 0;
        }

        $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $errors   = [];

        if ($email === '') {
            $errors[] = 'Informe o seu e-mail.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        }

        if ($password === '') {
            $errors[] = 'Informe a sua senha.';
        }

        if (!empty($errors)) {
            $_SESSION['login_errors']    = $errors;
            $_SESSION['login_old_email'] = $email;
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            require_once BASE_PATH . '/config/database.php';
            $pdo  = getPDO();
            $stmt = $pdo->prepare('SELECT id, name, email, password, type FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                // Conta a falha
                $_SESSION['login_attempts'] = $attempts + 1;
                $_SESSION['login_last']     = time();

                $_SESSION['login_errors']    = ['E-mail ou senha inválidos. Verifique os dados e tente novamente.'];
                $_SESSION['login_old_email'] = $email;
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            // Sucesso
            session_regenerate_id(true);
            unset($_SESSION['csrf_token'], $_SESSION['login_attempts'], $_SESSION['login_last']);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $type = (string) ($user['type'] ?? '');
            $_SESSION['user_type'] = ($type === 'admin') ? 'admin' : 'viewer';

            header('Location: ' . BASE_URL . 'area-restrita');
            exit;
        } catch (Throwable $e) {
            error_log('Erro no login: ' . $e->getMessage());
            $_SESSION['login_errors'] = ['Ocorreu um erro ao tentar autenticar. Tente novamente em alguns instantes.'];
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        header('Location: ' . BASE_URL);
        exit;
    }
}
