<?php
/**
 * Página de login da área restrita.
 *
 * Reaproveita o mesmo visual da página inicial (header, tema dark, tipografia)
 * e exibe um card centralizado com o formulário de autenticação.
 */

session_start();

// Lê mensagens de erro da sessão, se existirem, e limpa em seguida.
$loginErrors = $_SESSION['login_errors'] ?? [];
$oldEmail = $_SESSION['login_old_email'] ?? '';

unset($_SESSION['login_errors'], $_SESSION['login_old_email']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configuração básica e meta tags para SEO e responsividade -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login - Área restrita Rafael H. A Consultoria & Desenvolvimento">
    <title>Login | Rafael H. A Consultoria &amp; Desenvolvimento</title>

    <!-- Google Fonts - Tipografia elegante (Outfit + DM Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome - Ícones (usado em todo o site) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer">

    <!-- Folha de estilos principal -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <!-- ========== HEADER / MENU DE NAVEGAÇÃO ========== -->
    <header class="header" id="header">
        <div class="container header__container">
            <a href="index.php#inicio" class="header__logo" aria-label="Ir para início">
                <span class="header__logo-text">Rafael H. A Consultoria &amp; Desenvolvimento</span>
            </a>

            <button class="header__toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false">
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
            </button>

            <nav class="nav" id="mainNav">
                <ul class="nav__list">
                    <li><a href="index.php#inicio" class="nav__link">Início</a></li>
                    <li><a href="index.php#sobre" class="nav__link">Sobre</a></li>
                    <li><a href="index.php#servicos" class="nav__link">Serviços</a></li>
                    <li><a href="index.php#contato" class="nav__link">Contato</a></li>
                    <li><a href="index.php#agendar" class="nav__link nav__link--cta">Agendar consultoria</a></li>
                    <li>
                        <a href="login.php" class="nav__link" aria-label="Área restrita">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Seção de login com layout consistente com o restante do site -->
        <section class="section auth-section">
            <div class="container auth-section__container">
                <div class="auth-card">
                    <h1 class="section__title section__title--center">Acessar área restrita</h1>
                    <p class="section__subtitle">Faça login com seus dados cadastrados para acessar a área exclusiva.</p>

                    <?php if (!empty($loginErrors)): ?>
                        <div class="auth-alert">
                            <ul>
                                <?php foreach ($loginErrors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de login -->
                    <form class="auth-form" action="auth/login-process.php" method="post" novalidate>
                        <label for="email" class="auth-form__label">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="auth-form__input"
                            placeholder="seu@email.com"
                            required
                            value="<?= htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <label for="password" class="auth-form__label">Senha</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="auth-form__input"
                            placeholder="Sua senha"
                            required
                        >

                        <button type="submit" class="btn btn--primary auth-form__btn">
                            Entrar
                        </button>

                        <p class="auth-form__hint">
                            Sua senha deve ter pelo menos 6 caracteres, incluindo letra maiúscula, minúscula e caractere especial.
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container footer__container">
            <div class="footer__links">
                <a href="index.php#inicio">Início</a>
                <a href="index.php#sobre">Sobre</a>
                <a href="index.php#servicos">Serviços</a>
                <a href="index.php#contato">Contato</a>
            </div>
            <div class="footer__social">
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="LinkedIn">LinkedIn</a>
                <a href="#" aria-label="Facebook">Facebook</a>
            </div>
            <p class="footer__copy">&copy; <span id="currentYear"></span> Sua Empresa. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Script principal (menu mobile, animações, etc.) -->
    <script src="js/script.js"></script>
</body>
</html>

