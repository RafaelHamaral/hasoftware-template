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
