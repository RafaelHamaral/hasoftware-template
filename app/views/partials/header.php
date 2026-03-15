<?php
/**
 * Cabeçalho compartilhado (head + menu).
 * Usa $title e BASE_URL (definidos por quem inclui).
 */
$title = $title ?? 'Rafael H. A Consultoria & Desenvolvimento';
$base = defined('BASE_URL') ? BASE_URL : '';
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
</head>
<body<?= isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <header class="header" id="header">
        <div class="container header__container">
            <a href="<?= $base ? $base . '#inicio' : 'index.php#inicio' ?>" class="header__logo" aria-label="Ir para início">
                <span class="header__logo-text">Rafael H. A Consultoria & Desenvolvimento</span>
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
                        <a href="<?= $base ? $base . 'login' : 'login.php' ?>" class="nav__link" aria-label="Área restrita">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
