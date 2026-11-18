<?php
require BASE_PATH . '/app/views/partials/header.php';

$formatDate = static function (?string $value): string {
    if ($value === null || $value === '' || $value === '0000-00-00') {
        return '—';
    }
    $date = DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
    return $date instanceof DateTime ? $date->format('d/m/Y') : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$formatMoney = static function ($value): string {
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
};

$ok = ($status ?? '') === 'ok';
$token = (string) ($_GET['t'] ?? $_POST['t'] ?? '');
?>

<section class="section client-proposal">
    <div class="container">
        <?php if (!empty($saveSuccess)): ?>
            <p class="client-proposal__flash client-proposal__flash--ok"><?= htmlspecialchars($saveSuccess, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($saveError)): ?>
            <p class="client-proposal__flash client-proposal__flash--error"><?= htmlspecialchars($saveError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!$ok): ?>
            <h1 class="section__title">Proposta indisponível</h1>
            <p class="client-proposal__status"><?= htmlspecialchars($errorMessage ?? 'Este link é inválido.', ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <p class="client-proposal__kicker"><?= htmlspecialchars($marca['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <h1 class="section__title"><?= htmlspecialchars((string) ($proposta['titulo_projeto'] ?? 'Proposta'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="client-proposal__client">Preparado para <?= htmlspecialchars((string) ($proposta['nome_cliente'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

            <dl class="client-proposal__facts">
                <div>
                    <dt>Valor</dt>
                    <dd><?= htmlspecialchars($formatMoney($proposta['valor_proposta'] ?? 0), ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Prazo</dt>
                    <dd>
                        <?= htmlspecialchars($formatDate($proposta['prazo_inicio'] ?? null), ENT_QUOTES, 'UTF-8') ?>
                        a
                        <?= htmlspecialchars($formatDate($proposta['prazo_final'] ?? null), ENT_QUOTES, 'UTF-8') ?>
                    </dd>
                </div>
            </dl>

            <h2 class="client-proposal__subtitle">Escopo</h2>
            <p class="client-proposal__scope"><?= nl2br(htmlspecialchars((string) ($proposta['resumo'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>

            <form class="client-proposal__form" method="post" action="<?= BASE_URL ?>proposta/solicitar-ajuste">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="t" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <label for="ajuste">Solicitar ajuste</label>
                <textarea class="contact__form-input contact__form-textarea" id="ajuste" name="ajuste" rows="4" maxlength="2000" required placeholder="Descreva o que gostaria de ajustar no escopo, prazo ou valor."></textarea>
                <button type="submit" class="btn btn--primary">Enviar pedido de ajuste</button>
            </form>
            <?php if (!empty($marca['contatoLinha'])): ?>
                <p class="client-proposal__foot"><?= htmlspecialchars($marca['contatoLinha'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
