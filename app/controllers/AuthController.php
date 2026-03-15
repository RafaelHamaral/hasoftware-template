<?php
/**
 * Controller de autenticação: login, processar login, área restrita, logout.
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

        $title = 'Login | Rafael H. A Consultoria & Desenvolvimento';
        require BASE_PATH . '/app/views/login.php';
    }

    public function authenticate(): void
    {
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
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            require_once BASE_PATH . '/config/database.php';
            $pdo  = getPDO();
            $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['login_errors']    = ['E-mail ou senha inválidos. Verifique os dados e tente novamente.'];
                $_SESSION['login_old_email'] = $email;
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: ' . BASE_URL . 'area-restrita');
            exit;
        } catch (Throwable $e) {
            $_SESSION['login_errors'] = ['Ocorreu um erro ao tentar autenticar. Tente novamente em alguns instantes.'];
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function dashboard(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $title = 'Área restrita';
        require BASE_PATH . '/app/views/area-restrita.php';
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
