<?php
require BASE_PATH . '/app/views/partials/header.php';
require BASE_PATH . '/app/views/partials/config-flash.php';

$formatDate = static function (?string $value): string {
    if ($value === null || $value === '' || $value === '0000-00-00') {
        return '—';
    }
    $date = DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
    return $date instanceof DateTime ? $date->format('d/m/Y') : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};
?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Propostas de clientes</h1>

    <div class="dashboard-cards">
        <?php foreach ($data['estagios'] as $status => $label): ?>
            <div class="dashboard-card">
                <h3>
                    <span class="status-badge status-badge--<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </h3>
                <p><?= (int) ($data['contagemStatus'][$status] ?? 0) ?></p>
            </div>
        <?php endforeach; ?>
        <div class="dashboard-card">
            <h3>Faturado (fechados)</h3>
            <p>R$ <?= htmlspecialchars(number_format((float) ($data['faturado'] ?? 0), 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="dashboard-card">
            <h3>Horas trabalhadas (fechados)</h3>
            <p><?= htmlspecialchars(number_format((float) ($data['horasTrabalhadas'] ?? 0), 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <p>
        <button type="button" class="config-button" onclick="document.getElementById('proposta-nova').showModal()">
            Nova proposta
        </button>
    </p>

    <dialog class="config-popup config-proposta-dialog" id="proposta-nova">
        <h2>Nova proposta</h2>
        <form class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-proposta">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label>Nome do cliente</label>
            <input class="config-input" type="text" name="nome_cliente" required>

            <label>Título do projeto</label>
            <input class="config-input" type="text" name="titulo_projeto" required>

            <label>Status</label>
            <select class="config-input config-select" name="estagio" required>
                <?php foreach ($data['estagios'] as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Valor da proposta</label>
            <input class="config-input" type="text" name="valor_proposta" inputmode="decimal" placeholder="0,00" required>

            <label>Quantidade de horas</label>
            <input class="config-input" type="text" name="quantidade_horas" inputmode="decimal" placeholder="0" required>

            <label>Prazo início</label>
            <input class="config-input" type="date" name="prazo_inicio" required>

            <label>Prazo final</label>
            <input class="config-input" type="date" name="prazo_final" required>

            <label>Resumo do projeto</label>
            <textarea class="config-input" name="resumo" required></textarea>

            <button class="config-button" type="submit">Salvar proposta</button>
            <button class="config-button" type="button" onclick="document.getElementById('proposta-nova').close()">Cancelar</button>
        </form>
    </dialog>

    <h2>Acompanhamento</h2>
    <div class="funnel">
        <?php foreach ($data['estagios'] as $estagio => $label): ?>
            <?php $itens = $data['propostasPorEstagio'][$estagio] ?? []; ?>
            <section class="funnel-column">
                <header class="funnel-column__header">
                    <span class="status-badge status-badge--<?= htmlspecialchars($estagio, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="funnel-column__count"><?= count($itens) ?></span>
                </header>

                <?php if ($itens === []): ?>
                    <p class="funnel-column__empty">Nenhum cliente</p>
                <?php endif; ?>

                <?php foreach ($itens as $item): ?>
                    <?php
                    $id = (int) ($item['id'] ?? 0);
                    $dialogId = 'proposta-editar-' . $id;
                    $status = (string) ($item['estagio'] ?? '');
                    $statusLabel = $data['estagios'][$status] ?? $status;
                    ?>
                    <article class="funnel-card">
                        <h3 class="funnel-card__client"><?= htmlspecialchars((string) ($item['nome_cliente'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Projeto: <?= htmlspecialchars((string) ($item['titulo_projeto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p>Horas: <?= htmlspecialchars((string) ($item['quantidade_horas'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p>Início: <?= $formatDate($item['prazo_inicio'] ?? null) ?></p>
                        <p>Final: <?= $formatDate($item['prazo_final'] ?? null) ?></p>
                        <p>
                            Status:
                            <span class="status-badge status-badge--<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </p>
                        <div class="funnel-card__actions">
                            <button type="button" class="config-button" onclick="document.getElementById('<?= $dialogId ?>').showModal()">
                                Editar
                            </button>
                            <form class="config-inline-form" method="POST" action="<?= BASE_URL ?>config/excluir-proposta" onsubmit="return confirm('Excluir este projeto do acompanhamento? Ele sai da tela, mas permanece no banco como excluído.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" class="config-button">Excluir</button>
                            </form>
                        </div>
                    </article>

                    <dialog class="config-popup config-proposta-dialog" id="<?= $dialogId ?>">
                        <h2>Editar proposta</h2>
                        <form class="config-form" method="POST" action="<?= BASE_URL ?>config/atualizar-proposta">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">

                            <label>Nome do cliente</label>
                            <input class="config-input" type="text" name="nome_cliente" value="<?= htmlspecialchars((string) ($item['nome_cliente'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Título do projeto</label>
                            <input class="config-input" type="text" name="titulo_projeto" value="<?= htmlspecialchars((string) ($item['titulo_projeto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Status</label>
                            <select class="config-input config-select" name="estagio" required>
                                <?php foreach ($data['estagios'] as $value => $optionLabel): ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label>Valor da proposta</label>
                            <input class="config-input" type="text" name="valor_proposta" value="<?= htmlspecialchars((string) ($item['valor_proposta'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Quantidade de horas</label>
                            <input class="config-input" type="text" name="quantidade_horas" value="<?= htmlspecialchars((string) ($item['quantidade_horas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Prazo início</label>
                            <input class="config-input" type="date" name="prazo_inicio" value="<?= htmlspecialchars(substr((string) ($item['prazo_inicio'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Prazo final</label>
                            <input class="config-input" type="date" name="prazo_final" value="<?= htmlspecialchars(substr((string) ($item['prazo_final'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?>" required>

                            <label>Resumo do projeto</label>
                            <textarea class="config-input" name="resumo" required><?= htmlspecialchars((string) ($item['resumo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

                            <button class="config-button" type="submit">Atualizar</button>
                            <button class="config-button" type="button" onclick="document.getElementById('<?= $dialogId ?>').close()">Cancelar</button>
                        </form>
                    </dialog>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
