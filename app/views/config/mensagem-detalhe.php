<?php
require BASE_PATH . '/app/views/partials/header.php';
require BASE_PATH . '/app/views/partials/config-flash.php';

$item = $data['item'] ?? [];
$statusLabels = $data['statusLabels'] ?? [];
$canEdit = !empty($data['canEdit']);
$currentStatus = (string) ($item['status'] ?? 'novo');

$formatDateTime = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '—';
    }
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    if ($date instanceof DateTime) {
        return $date->format('d/m/Y H:i');
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

$campoLabels = [
    'status' => 'Status',
    'anotacao_interna' => 'Anotação interna',
    'proximo_contato' => 'Próximo contato',
];

$formatHistoryValue = static function (?string $campo, ?string $value) use ($statusLabels): string {
    if ($value === null || $value === '') {
        return '—';
    }
    if ($campo === 'status') {
        return $statusLabels[$value] ?? $value;
    }
    if ($campo === 'proximo_contato') {
        $date = DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
        return $date instanceof DateTime ? $date->format('d/m/Y') : $value;
    }

    return $value;
};

$nextContact = substr((string) ($item['proximo_contato'] ?? ''), 0, 10);
if ($nextContact === '0000-00-00') {
    $nextContact = '';
}
?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <p class="config-back">
        <a class="config-button config-back__link" href="<?= BASE_URL ?>config/mensagens">Voltar às solicitações</a>
    </p>
    <h1>Solicitação #<?= (int) ($item['id'] ?? 0) ?></h1>

    <section class="config-detail">
        <h2>Mensagem do visitante</h2>
        <p><strong>Data:</strong> <?= htmlspecialchars($formatDateTime($item['criado_em'] ?? null), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Nome:</strong> <?= htmlspecialchars((string) ($item['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <p>
            <strong>E-mail:</strong>
            <a href="mailto:<?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </a>
        </p>
        <p class="config-message-cell"><?= nl2br(htmlspecialchars((string) ($item['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    </section>

    <?php if ($canEdit): ?>
        <form
            class="config-form"
            method="POST"
            action="<?= BASE_URL ?>config/salvar-mensagem-atendimento"
            data-confirm="Confirma salvar o atendimento?"
        >
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int) ($item['id'] ?? 0) ?>">

            <label for="status">Status</label>
            <select class="config-input config-select" id="status" name="status" required>
                <?php foreach ($statusLabels as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $currentStatus === $value ? ' selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="anotacao_interna">Anotação interna</label>
            <textarea
                class="config-input"
                id="anotacao_interna"
                name="anotacao_interna"
                rows="5"
                maxlength="5000"
            ><?= htmlspecialchars((string) ($item['anotacao_interna'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

            <label for="proximo_contato">Data do próximo contato</label>
            <input
                class="config-input"
                type="date"
                id="proximo_contato"
                name="proximo_contato"
                value="<?= htmlspecialchars($nextContact, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button class="config-button" type="submit">Salvar atendimento</button>
        </form>
    <?php else: ?>
        <section class="config-detail">
            <h2>Atendimento</h2>
            <p>
                <strong>Status:</strong>
                <span class="status-badge status-badge--<?= htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($statusLabels[$currentStatus] ?? $currentStatus, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </p>
            <p><strong>Próximo contato:</strong>
                <?php
                $shownNext = '—';
                if ($nextContact !== '') {
                    $parsedNext = DateTime::createFromFormat('Y-m-d', $nextContact);
                    $shownNext = $parsedNext instanceof DateTime ? $parsedNext->format('d/m/Y') : $nextContact;
                }
                echo htmlspecialchars($shownNext, ENT_QUOTES, 'UTF-8');
                ?>
            </p>
            <p><strong>Anotação interna:</strong></p>
            <p class="config-message-cell"><?= $item['anotacao_interna'] !== null && $item['anotacao_interna'] !== ''
                ? nl2br(htmlspecialchars((string) $item['anotacao_interna'], ENT_QUOTES, 'UTF-8'))
                : '—'; ?></p>
            <p class="config-hint">Seu perfil pode consultar as solicitações, mas não alterar o atendimento.</p>
        </section>
    <?php endif; ?>

    <section class="config-detail">
        <h2>Histórico de atendimento</h2>
        <?php if (empty($data['historico'])): ?>
            <p>Nenhuma alteração registrada ainda. Solicitações antigas começam neste ponto, com o status atual.</p>
        <?php else: ?>
            <div class="config-table-wrap">
            <table class="config-table">
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Campo</th>
                        <th>De</th>
                        <th>Para</th>
                        <th>Quem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['historico'] as $row): ?>
                        <?php $campo = (string) ($row['campo'] ?? ''); ?>
                        <tr>
                            <td><?= htmlspecialchars($formatDateTime($row['criado_em'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($campoLabels[$campo] ?? $campo, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="config-message-cell"><?= nl2br(htmlspecialchars($formatHistoryValue($campo, $row['valor_anterior'] ?? null), ENT_QUOTES, 'UTF-8')) ?></td>
                            <td class="config-message-cell"><?= nl2br(htmlspecialchars($formatHistoryValue($campo, $row['valor_novo'] ?? null), ENT_QUOTES, 'UTF-8')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['usuario_nome'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
