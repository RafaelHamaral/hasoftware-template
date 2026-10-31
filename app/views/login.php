<?php
/**
 * Página de login (apenas o conteúdo central; header/footer vêm dos partials).
 * Espera: $loginErrors (array), $oldEmail (string), $title (opcional).
 */
$bodyClass = $bodyClass ?? 'auth-page';
require BASE_PATH . '/app/views/partials/header.php';
?>

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

                    <form class="auth-form" action="<?= BASE_URL ?>authenticate" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">                        <label for="email" class="auth-form__label">E-mail</label>
                        <input type="email" id="email" name="email" class="auth-form__input" placeholder="seu@email.com" required value="<?= htmlspecialchars($oldEmail ?? '', ENT_QUOTES, 'UTF-8') ?>">

                        <label for="password" class="auth-form__label">Senha</label>
                        <input type="password" id="password" name="password" class="auth-form__input" placeholder="Sua senha" required>

                        <button type="submit" class="btn btn--primary auth-form__btn">Entrar</button>

                        <!-- <p class="auth-form__hint">Sua senha deve ter pelo menos 6 caracteres, incluindo letra maiúscula, minúscula e caractere especial.</p> -->
                    </form>
                </div>
            </div>
        </section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
