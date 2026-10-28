<?php require BASE_PATH . '/app/views/partials/header.php'; ?>

<div class="config-container">
    <h1>Template de E-mail</h1>

    <form class="config-form">
        <label>Assunto</label>
        <input class="config-input" 
            value="<?= htmlspecialchars($data['template']['assunto']) ?>">

        <label>Mensagem</label>
        <textarea class="config-input"><?= htmlspecialchars($data['template']['mensagem']) ?></textarea>

        <button class="config-button">Salvar</button>
    </form>

    <h3>Preview:</h3>
    <div class="dashboard-card">
        <?= nl2br(htmlspecialchars($data['template']['mensagem'])) ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>