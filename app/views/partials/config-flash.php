<?php
$flashMessage = $saveSuccess ?? '';
$flashType = 'success';
if ($flashMessage === '' && !empty($saveError)) {
    $flashMessage = $saveError;
    $flashType = 'error';
}
?>
<?php if ($flashMessage !== ''): ?>
    <dialog class="config-popup config-popup--<?= htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8') ?>" id="configFlash" open>
        <p class="config-popup__text"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <form method="dialog">
            <button type="submit" class="config-button">Fechar</button>
        </form>
    </dialog>
<?php endif; ?>

<dialog class="config-popup config-popup--confirm" id="configConfirm">
    <p class="config-popup__text" id="configConfirmMessage"></p>
    <div class="config-popup__actions">
        <button type="button" class="config-button" id="configConfirmCancel">Cancelar</button>
        <button type="button" class="config-button" id="configConfirmOk">Confirmar</button>
    </div>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var dialog = document.getElementById('configConfirm');
    var message = document.getElementById('configConfirmMessage');
    var ok = document.getElementById('configConfirmOk');
    var cancel = document.getElementById('configConfirmCancel');
    var pending = null;
    var pendingSubmitter = null;

    if (!dialog || !message || !ok || !cancel) {
        return;
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        var submitter = event.submitter || null;
        var text = (submitter && submitter.getAttribute('data-confirm')) || form.getAttribute('data-confirm');
        if (!text || form.getAttribute('data-confirm-ok') === '1') {
            form.removeAttribute('data-confirm-ok');
            return;
        }

        event.preventDefault();
        pending = form;
        pendingSubmitter = submitter;
        message.textContent = text;
        dialog.showModal();
    });

    ok.addEventListener('click', function () {
        var form = pending;
        var submitter = pendingSubmitter;
        pending = null;
        pendingSubmitter = null;
        dialog.close();
        if (!form) {
            return;
        }
        form.setAttribute('data-confirm-ok', '1');
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter || undefined);
        } else {
            form.submit();
        }
    });

    cancel.addEventListener('click', function () {
        pending = null;
        pendingSubmitter = null;
        dialog.close();
    });
});
</script>
