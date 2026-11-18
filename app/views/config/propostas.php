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

    <?php if (!empty($data['shareUrl'])): ?>
        <div class="config-share-url">
            <p>Copie o link agora. Só o hash fica salvo; esta URL não será exibida novamente.</p>
            <input class="config-input" type="text" readonly value="<?= htmlspecialchars((string) $data['shareUrl'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
    <?php endif; ?>

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

    <p class="config-proposta-toolbar">
        <button type="button" class="config-button" onclick="document.getElementById('proposta-nova').showModal()">
            Nova proposta
        </button>
        <button type="button" class="config-button" onclick="openPropostaPdfFrom(document.getElementById('proposta-nova-form'))">
            Gerar proposta PDF
        </button>
    </p>

    <dialog class="config-popup config-proposta-dialog" id="proposta-nova">
        <h2>Nova proposta</h2>
        <form id="proposta-nova-form" class="config-form" method="POST" action="<?= BASE_URL ?>config/salvar-proposta" data-confirm="Confirma salvar proposta?">
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
            <button class="config-button" type="button" onclick="openPropostaPdfFrom(document.getElementById('proposta-nova-form'))">Gerar proposta PDF</button>
            <button class="config-button" type="button" onclick="document.getElementById('proposta-nova').close()">Cancelar</button>
        </form>
    </dialog>

    <dialog class="config-popup config-proposta-pdf-dialog" id="proposta-pdf" data-estagios="<?= htmlspecialchars((string) json_encode($data['estagios'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8') ?>">
        <h2>Template da proposta</h2>
        <form class="config-form" method="POST" action="<?= BASE_URL ?>config/gerar-proposta-pdf">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <article class="proposta-pdf-sheet">
                <header class="proposta-pdf-sheet__header">
                    <img
                        class="proposta-pdf-sheet__logo"
                        src="<?= BASE_URL ?>assets/images/brand/logo-ha.png"
                        alt="H.A Consultoria e Desenvolvimento de Software"
                        width="420"
                        height="77"
                    >
                </header>
                <div class="proposta-pdf-sheet__accent"></div>

                <div class="proposta-pdf-sheet__body">
                    <div class="proposta-pdf-sheet__docrow">
                        <p class="proposta-pdf-sheet__doc">Proposta comercial</p>
                        <p class="proposta-pdf-sheet__date">Data: <?= htmlspecialchars(date('d/m/Y'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <div class="proposta-pdf-sheet__grid">
                        <div class="proposta-pdf-sheet__field">
                            <label for="pdf-nome-cliente">Nome do cliente</label>
                            <input class="config-input" type="text" id="pdf-nome-cliente" name="nome_cliente" required>
                        </div>
                        <div class="proposta-pdf-sheet__field">
                            <label for="pdf-titulo-projeto">Título do projeto</label>
                            <input class="config-input" type="text" id="pdf-titulo-projeto" name="titulo_projeto" required>
                        </div>
                        <div class="proposta-pdf-sheet__field">
                            <label for="pdf-status">Status</label>
                            <input class="config-input" type="text" id="pdf-status" name="status">
                        </div>
                        <div class="proposta-pdf-sheet__field">
                            <label for="pdf-valor">Valor da proposta</label>
                            <input class="config-input" type="text" id="pdf-valor" name="valor_proposta">
                        </div>
                        <div class="proposta-pdf-sheet__field">
                            <label for="pdf-horas">Quantidade de horas</label>
                            <input class="config-input" type="text" id="pdf-horas" name="quantidade_horas">
                        </div>
                        <div class="proposta-pdf-sheet__field proposta-pdf-sheet__field--split">
                            <div>
                                <label for="pdf-prazo-inicio">Prazo início</label>
                                <input class="config-input" type="text" id="pdf-prazo-inicio" name="prazo_inicio" placeholder="dd/mm/aaaa">
                            </div>
                            <div>
                                <label for="pdf-prazo-final">Prazo final</label>
                                <input class="config-input" type="text" id="pdf-prazo-final" name="prazo_final" placeholder="dd/mm/aaaa">
                            </div>
                        </div>
                        <div class="proposta-pdf-sheet__field proposta-pdf-sheet__field--wide">
                            <label for="pdf-resumo">Resumo do projeto</label>
                            <textarea class="config-input" id="pdf-resumo" name="resumo" rows="6" required></textarea>
                        </div>
                    </div>
                </div>

                <footer class="proposta-pdf-sheet__footer">
                    <span><?= htmlspecialchars($data['marca']['contatoLinha'] ?? 'H. A Consultoria & Desenvolvimento', ENT_QUOTES, 'UTF-8') ?></span>
                </footer>
            </article>

            <div class="proposta-pdf-actions">
                <button class="config-button" type="submit">Baixar PDF</button>
                <button class="config-button" type="button" onclick="document.getElementById('proposta-pdf').close()">Fechar</button>
            </div>
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
                            <button type="button" class="config-button" onclick="openPropostaPdfFrom(document.querySelector('#<?= $dialogId ?> .config-form'))">
                                Gerar PDF
                            </button>
                            <form class="config-inline-form" method="POST" action="<?= BASE_URL ?>config/excluir-proposta" data-confirm="Confirma excluir proposta?">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" class="config-button">Excluir</button>
                            </form>
                            <form class="config-inline-form" method="POST" action="<?= BASE_URL ?>config/gerar-link-proposta" data-confirm="Confirma gerar link privado?">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <label class="config-filter__label" for="validade-<?= $id ?>">Validade</label>
                                <select class="config-input config-select" id="validade-<?= $id ?>" name="validade_dias">
                                    <option value="1">1 dia</option>
                                    <option value="7" selected>7 dias</option>
                                    <option value="30">30 dias</option>
                                </select>
                                <button type="submit" class="config-button">Gerar link do cliente</button>
                            </form>
                        </div>
                        <?php
                        $links = $data['linksPorProposta'][$id] ?? [];
                        $ajustes = $data['ajustesPorProposta'][$id] ?? [];
                        ?>
                        <?php if ($links !== []): ?>
                            <p class="funnel-card__meta">Links do cliente</p>
                            <ul class="funnel-card__list">
                                <?php foreach ($links as $link): ?>
                                    <?php
                                    $linkId = (int) ($link['id'] ?? 0);
                                    $revoked = !empty($link['revoked_at']);
                                    $expires = (string) ($link['expires_at'] ?? '');
                                    $expired = false;
                                    if ($expires !== '') {
                                        $expDate = DateTime::createFromFormat('Y-m-d H:i:s', $expires);
                                        $expired = $expDate instanceof DateTime && $expDate < new DateTime();
                                    }
                                    $state = $revoked ? 'Revogado' : ($expired ? 'Expirado' : 'Ativo até ' . $expires);
                                    ?>
                                    <li>
                                        <?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!$revoked && !$expired): ?>
                                            <form class="config-inline-form" method="POST" action="<?= BASE_URL ?>config/revogar-link-proposta" data-confirm="Confirma revogar este link?">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <input type="hidden" name="link_id" value="<?= $linkId ?>">
                                                <button type="submit" class="config-button">Revogar</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if ($ajustes !== []): ?>
                            <p class="funnel-card__meta">Pedidos de ajuste</p>
                            <ul class="funnel-card__list">
                                <?php foreach ($ajustes as $ajuste): ?>
                                    <li>
                                        <time><?= htmlspecialchars((string) ($ajuste['criado_em'] ?? ''), ENT_QUOTES, 'UTF-8') ?></time>
                                        — <?= htmlspecialchars((string) ($ajuste['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>

                    <dialog class="config-popup config-proposta-dialog" id="<?= $dialogId ?>">
                        <h2>Editar proposta</h2>
                        <form class="config-form" method="POST" action="<?= BASE_URL ?>config/atualizar-proposta" data-confirm="Confirma salvar proposta?">
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

<script>
function propostaPdfFormatDate(value) {
    var raw = String(value || '');
    var match = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) {
        return raw;
    }
    return match[3] + '/' + match[2] + '/' + match[1];
}

function openPropostaPdfFrom(form) {
    var dialog = document.getElementById('proposta-pdf');
    if (!form || !dialog) {
        return;
    }

    var data = new FormData(form);
    var stages = {};
    try {
        stages = JSON.parse(dialog.getAttribute('data-estagios') || '{}');
    } catch (error) {
        stages = {};
    }

    var estagio = String(data.get('estagio') || '');
    document.getElementById('pdf-nome-cliente').value = String(data.get('nome_cliente') || '');
    document.getElementById('pdf-titulo-projeto').value = String(data.get('titulo_projeto') || '');
    document.getElementById('pdf-status').value = stages[estagio] || estagio;
    document.getElementById('pdf-valor').value = String(data.get('valor_proposta') || '');
    document.getElementById('pdf-horas').value = String(data.get('quantidade_horas') || '');
    document.getElementById('pdf-prazo-inicio').value = propostaPdfFormatDate(data.get('prazo_inicio'));
    document.getElementById('pdf-prazo-final').value = propostaPdfFormatDate(data.get('prazo_final'));
    document.getElementById('pdf-resumo').value = String(data.get('resumo') || '');
    dialog.showModal();
}
</script>
<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
