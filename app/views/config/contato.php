<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Dados de contato</h1>
    <p class="config-hint">Título e descrição aparecem no formulário público. O e-mail abaixo recebe as notificações das mensagens enviadas pelo site.</p>

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

    <label>E-mail de notificação</label>
    <p class="config-hint">Destino do aviso quando um visitante envia o formulário (por exemplo, seu Outlook). O remetente autorizado fica no .env (MAIL_FROM). Ao responder no e-mail, a resposta vai para o visitante.</p>
    <input
        class="config-input"
        type="email"
        name="email"
        value="<?= htmlspecialchars($data['contato']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        required
        autocomplete="email"
    >

    <label>Telefone</label>
    <input
        class="config-input"
        type="text"
        name="tel1"
        value="<?= htmlspecialchars($data['contato']['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
    >

    <label>WhatsApp</label>
    <p class="config-hint">Número usado no botão “Agendar consultoria pelo WhatsApp” da página inicial.</p>
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
