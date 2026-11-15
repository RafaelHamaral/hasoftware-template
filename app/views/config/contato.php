<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Dados de contato</h1>

    <form class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-contato" data-confirm="Confirma salvar dados?">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <label>Título</label>
    <input
        class="config-input"
        type="text"
        name="titulo"
        value="<?= htmlspecialchars($data['contato']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
    >


    <label>Descrição</label>
    <textarea
        class="config-input"
        name="descricao"
        required
    ><?= htmlspecialchars($data['contato']['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

    <label>E-mail</label>
    <input
        class="config-input"
        type="email"
        name="email"
        value="<?= htmlspecialchars($data['contato']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
    >

    <label>Telefone</label>
    <input
        class="config-input"
        type="text"
        name="tel1"
        value="<?= htmlspecialchars($data['contato']['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
    >

    <label>WhatsApp</label>
    <input
        class="config-input"
        type="text"
        name="tel2"
        value="<?= htmlspecialchars($data['contato']['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
    >

    <button class="config-button" type="submit">
        <?= !empty($data['contato']['id']) ? 'Atualizar' : 'Salvar' ?>
    </button>

</form>

</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
