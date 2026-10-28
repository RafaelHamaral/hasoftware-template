<?php
require BASE_PATH . '/app/views/partials/header.php';
?>

<section class="dashboard-container">
    
    <h1>Área Restrita / Dashboard</h1>
    <p>Bem-vindo ao painel administrativo, <?= htmlspecialchars($data['userName']) ?></p>

    <!-- 📊 Cards -->
    <div class="dashboard-cards">

        <div class="dashboard-card">
            <h3>Emails Hoje</h3>
            <p><?= htmlspecialchars($data['stats']['emails_hoje']) ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Acessos Hoje</h3>
            <p><?= htmlspecialchars($data['stats']['acessos_hoje']) ?></p>
        </div>

    </div>

    <!-- ⚙️ Menu -->
    <div class="dashboard-menu">

        <a href="<?= BASE_URL ?>../app/views/config/email" class="dashboard-menu-item">
            Configuração de E-mail
        </a>

        <a href="<?= BASE_URL ?>../app/views/config/whatsapp" class="dashboard-menu-item">
            Configuração de WhatsApp
        </a>

        <a href="<?= BASE_URL ?>../app/views/config/email-template" class="dashboard-menu-item">
            Template de E-mail
        </a>

        <?php if ($data['userType'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>../app/views/config/usuarios" class="dashboard-menu-item">
                Configuração de Usuários
            </a>
        <?php endif; ?>

</div>

</section>