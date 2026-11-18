<?php
require BASE_PATH . '/app/views/partials/header.php';
?>

<section class="dashboard-container">
    
    <h1>Área Restrita / Dashboard</h1>
    <p>Bem-vindo ao painel administrativo, <?= htmlspecialchars($data['userName']) ?></p>

    <!-- 📊 Cards -->
    <div class="dashboard-cards">

        <div class="dashboard-card">
            <h3>Mensagens hoje</h3>
            <p><?= htmlspecialchars($data['stats']['emails_hoje']) ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Acessos Hoje</h3>
            <p><?= htmlspecialchars($data['stats']['acessos_hoje']) ?></p>
        </div>

    </div>

    <!-- ⚙️ Menu (a restrição real está no ConfigController) -->
    <div class="dashboard-menu">
        <?php if (in_array($data['userType'], ['admin', 'editor', 'viewer'], true)): ?>
            <a href="<?= BASE_URL ?>config/mensagens" class="dashboard-menu-item">
                Solicitações
            </a>
        <?php endif; ?>
        <?php if (in_array($data['userType'], ['admin', 'editor'], true)): ?>
            <a href="<?= BASE_URL ?>config/inicio" class="dashboard-menu-item">
                Dados de início
            </a>
        <?php endif; ?>

        <?php if (in_array($data['userType'], ['admin', 'editor'], true)): ?>
            <a href="<?= BASE_URL ?>config/contato" class="dashboard-menu-item">
                Dados de contato
            </a>
            <a href="<?= BASE_URL ?>config/sobre" class="dashboard-menu-item">
                Sobre nós
            </a>
            <a href="<?= BASE_URL ?>config/propostas" class="dashboard-menu-item">
                Propostas de clientes
            </a>
        <?php endif; ?>

        <?php if ($data['userType'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>config/usuarios" class="dashboard-menu-item">
                Configuração de Usuários
            </a>
        <?php endif; ?>
    </div>

</section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
