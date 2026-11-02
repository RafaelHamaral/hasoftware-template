<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <h1>Template de E-mail</h1>

    <form class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-email-template">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label>Assunto</label>
        <input
            class="config-input"
            type="text"
            name="assunto"
            value="<?= htmlspecialchars($data['template']['assunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label>Mensagem</label>
        <textarea
            class="config-input"
            name="mensagem"
            required
        ><?= htmlspecialchars($data['template']['mensagem'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

        <button class="config-button" type="submit">
            <?= !empty($data['template']['id']) ? 'Atualizar' : 'Salvar' ?>
        </button>
    </form>

    <h3>Preview:</h3>
    <div class="dashboard-card">
        <?= nl2br(htmlspecialchars($data['template']['mensagem'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
