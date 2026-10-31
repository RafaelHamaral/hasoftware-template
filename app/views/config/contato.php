<?php require BASE_PATH . '/app/views/partials/header.php'; ?>

<div class="config-container">
    <h1>Dados de contato</h1>

    <p>E-mail: <?= htmlspecialchars($data['contato']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    <p>Telefone: <?= htmlspecialchars($data['contato']['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
