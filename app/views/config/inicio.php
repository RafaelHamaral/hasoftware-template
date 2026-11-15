<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Dados para o inicio da página</h1>

    <form class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-dados-inicio" enctype="multipart/form-data" data-confirm="Confirma salvar dados?">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <label>Título</label>
    <input
        class="config-input"
        type="text"
        name="titulo"
        value="<?= htmlspecialchars($data['inicio']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
    >

    <label>Imagem</label>
    <input
        class="config-input"
        type="file"
        name="imagem"
        accept="image/*"
    >
    <?php if (!empty($data['inicio']['imagem'])): ?>
        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($data['inicio']['imagem'], ENT_QUOTES, 'UTF-8') ?>" alt="Imagem do início" width="100" height="100" class="config-image">
    <?php endif; ?>

    <label>Subtítulo</label>
    <textarea
        class="config-input"
        name="subtitulo"
        required
    ><?= htmlspecialchars($data['inicio']['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

    <label>Botão</label>
    <input
        class="config-input"
        type="text"
        name="botao_inicio"
        value="<?= htmlspecialchars($data['inicio']['botao_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
    >

    <button class="config-button" type="submit">
        <?= !empty($data['inicio']['id']) ? 'Atualizar' : 'Salvar' ?>
    </button>

</form>

</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
