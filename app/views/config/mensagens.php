<?php
require BASE_PATH . '/app/views/partials/header.php';
require BASE_PATH . '/app/views/partials/config-flash.php';

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

$formatDate = static function (?string $value): string {
    if ($value === null || $value === '' || $value === '0000-00-00') {
        return '—';
    }
    $date = DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
    return $date instanceof DateTime ? $date->format('d/m/Y') : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$statusLabels = $data['statusLabels'] ?? [];
$filtroStatus = (string) ($data['filtroStatus'] ?? '');
$filtroRetornos = !empty($data['filtroRetornos']);
?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Solicitações</h1>
    <p>Envios do formulário público, com acompanhamento de atendimento.</p>

    <form class="config-filter" method="get" action="<?= BASE_URL ?>config/mensagens">
        <label class="config-filter__label" for="filtro-status">Status</label>
        <select class="config-input config-select" id="filtro-status" name="status">
            <option value="">Todos</option>
            <?php foreach ($statusLabels as $value => $label): ?>
                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $filtroStatus === $value ? ' selected' : '' ?>>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label class="config-filter__check">
            <input type="checkbox" name="retornos" value="1"<?= $filtroRetornos ? ' checked' : '' ?>>
            Retornos pendentes
        </label>
        <button class="config-button" type="submit">Filtrar</button>
    </form>
    <p class="config-hint">
        Retornos pendentes: solicitações abertas (Novo, Em conversa ou Proposta enviada)
        com próximo contato hoje ou em atraso.
    </p>

    <?php if (empty($data['mensagens'])): ?>
        <p>Nenhuma solicitação encontrada para este filtro.</p>
    <?php else: ?>
        <div class="config-table-wrap">
        <table class="config-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th>Próximo contato</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['mensagens'] as $item): ?>
                    <?php
                    $status = (string) ($item['status'] ?? 'novo');
                    $statusLabel = $statusLabels[$status] ?? $status;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($formatDateTime($item['criado_em'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </td>
                        <td>
                            <span class="status-badge status-badge--<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($formatDate($item['proximo_contato'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a class="config-button" href="<?= BASE_URL ?>config/mensagem?id=<?= (int) ($item['id'] ?? 0) ?>">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
