<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Sobre nós</h1>

    <form class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-sobre-nos">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label>Título</label>
        <input
            class="config-input"
            type="text"
            name="titulo"
            value="<?= htmlspecialchars($data['sobre']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

    <label>Subtítulo</label>
    <input
        class="config-input"
        type="text"
        name="subtitulo"
        value="<?= htmlspecialchars($data['sobre']['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
    >

    <label>Descrição</label>
    <textarea
        class="config-input"
        name="descricao"
        required
    ><?= htmlspecialchars($data['sobre']['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

    <button class="config-button" type="submit" onclick="return confirm('Tem certeza que deseja salvar os dados?')">
        <?= !empty($data['sobre']['id']) ? 'Atualizar' : 'Salvar' ?>
    </button>

</form>

</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
