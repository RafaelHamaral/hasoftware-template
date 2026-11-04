<?php

/**
 * Cabeçalho compartilhado (head + menu).
 * Usa $title e BASE_URL (definidos por quem inclui).
 */
$title = $title ?? 'H. A Consultoria & Desenvolvimento';
$base = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLogged = !empty($_SESSION['user_id']);
$isRestricted = !empty($isRestricted);

$bodyClasses = [];
if (!empty($bodyClass)) {
    $bodyClasses[] = $bodyClass;
}
if ($isRestricted) {
    $bodyClasses[] = 'is-restricted';
    $bodyClasses[] = 'is-header-hidden';
}
$bodyClassAttr = $bodyClasses !== [] ? ' class="' . htmlspecialchars(implode(' ', $bodyClasses), ENT_QUOTES, 'UTF-8') . '"' : '';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site institucional - Conheça nossos serviços e entre em contato.">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <link rel="stylesheet" href="<?= $base ?>css/style.css">
    <link rel="icon" href="<?= $base ?>assets/images/brand/logo-ha-icon.png" type="image/png">
    <link rel="icon" href="<?= $base ?>assets/images/brand/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= $base ?>assets/images/brand/apple-touch-icon.png">
    <meta name="theme-color" content="#0d0d0f">
</head>
<body<?= $bodyClassAttr ?>>
    <?php if ($isRestricted): ?>
        <button
            type="button"
            class="header-visibility-toggle"
            id="headerVisibilityToggle"
            aria-controls="header"
            aria-expanded="false"
            aria-label="Mostrar menu"
        >
            Mostrar menu
        </button>
    <?php endif; ?>
    <header class="header" id="header">
        <div class="container header__container">
            <a href="<?= $base ? $base . '#inicio' : 'index.php#inicio' ?>" class="header__logo" aria-label="H.A Consultoria e Desenvolvimento de Software — início">
                <img
                    class="header__logo-mark"
                    src="<?= $base ?>assets/images/brand/logo-ha-icon.png"
                    alt=""
                    width="118"
                    height="42"
                    decoding="async"
                >
                <span class="header__logo-text">
                    <span class="header__logo-name">Consultoria</span>
                    <span class="header__logo-sub">&amp; Desenvolvimento de Software</span>
                </span>
            </a>

            <button class="header__toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false">
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
            </button>

            <nav class="nav" id="mainNav">
                <ul class="nav__list">
                    <li><a href="<?= $base ? $base . '#inicio' : 'index.php#inicio' ?>" class="nav__link">Início</a></li>
                    <li><a href="<?= $base ? $base . '#sobre' : 'index.php#sobre' ?>" class="nav__link">Sobre</a></li>
                    <li><a href="<?= $base ? $base . '#servicos' : 'index.php#servicos' ?>" class="nav__link">Serviços</a></li>
                    <li><a href="<?= $base ? $base . '#contato' : 'index.php#contato' ?>" class="nav__link">Contato</a></li>
                    <li><a href="<?= $base ? $base . '#agendar' : 'index.php#agendar' ?>" class="nav__link nav__link--cta">Agendar consultoria</a></li>
                    <li>
                        <?php if ($isLogged): ?>
                            <a href="<?= $base ?>area-restrita"
                                class="nav__link"
                                title="Área restrita"
                                aria-label="Voltar à área restrita">
                                <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
                            </a>
                            <a href="<?= $base ?>logout"
                                class="nav__link"
                                title="Sair"
                                aria-label="Sair da área restrita"
                                onclick="return confirm('Deseja realmente sair da área restrita?');">
                                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= $base ?>login"
                                class="nav__link"
                                title="Área restrita"
                                aria-label="Área restrita">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>