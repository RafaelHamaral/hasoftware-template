<?php
/**
 * Área restrita (conteúdo após login).
 * Espera: $title (opcional). Usa $_SESSION['user_name'] para saudação.
 */
require BASE_PATH . '/app/views/partials/header.php';
$base = defined('BASE_URL') ? BASE_URL : '';
?>

        <section class="section">
            <div class="container">
                <h1 class="section__title">Área restrita</h1>
                <p class="section__subtitle">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name'] ?? 'usuário', ENT_QUOTES, 'UTF-8') ?>.</p>
                <p>Conteúdo exclusivo da área restrita.</p>
                <a href="<?= $base ? $base . 'logout' : 'logout.php' ?>" class="btn btn--primary">Sair</a>
            </div>
        </section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
