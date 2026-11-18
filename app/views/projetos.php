<?php
/**
 * Demonstração pública do painel de vendas.
 * Espera: $metrics, $months, $month, $ctaUrl, $ctaMessage.
 */
require BASE_PATH . '/app/views/partials/header.php';

$isEmpty = ((int) ($metrics['opportunities'] ?? 0)) === 0;
$ticketLabel = $metrics['ticket_centavos'] === null
    ? '—'
    : demo_sales_format_money((int) $metrics['ticket_centavos']);
$conversionLabel = demo_sales_format_percent($metrics['conversion'] ?? null);
$revenueLabel = demo_sales_format_money((int) ($metrics['revenue_centavos'] ?? 0));
$monthLabel = $months[$month] ?? $month;
?>

<section class="section demo-panel" id="projetos">
    <div class="container">
        <p class="demo-panel__badge">Demonstração com dados fictícios</p>
        <h1 class="section__title">Projetos</h1>
        <p class="section__subtitle demo-panel__lead">
            Painel de vendas de exemplo para um comércio fictício. Use o filtro para ver como faturamento,
            ticket médio e conversão mudam no período. Nada disto é dado real de cliente.
        </p>

        <form class="demo-panel__filter" method="get" action="<?= BASE_URL ?>projetos">
            <label class="demo-panel__filter-label" for="mes">Filtrar por mês</label>
            <div class="demo-panel__filter-row">
                <select class="demo-panel__select" id="mes" name="mes">
                    <?php foreach ($months as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $value === $month ? ' selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn--primary demo-panel__filter-btn" type="submit">Aplicar</button>
            </div>
        </form>

        <p class="demo-panel__period">Período: <?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></p>

        <div class="demo-panel__kpis">
            <article class="demo-panel__kpi">
                <h2 class="demo-panel__kpi-label">Faturamento</h2>
                <p class="demo-panel__kpi-value"><?= htmlspecialchars($revenueLabel, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="demo-panel__kpi-hint">Soma das vendas concluídas no mês filtrado.</p>
            </article>
            <article class="demo-panel__kpi">
                <h2 class="demo-panel__kpi-label">Ticket médio</h2>
                <p class="demo-panel__kpi-value"><?= htmlspecialchars($ticketLabel, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="demo-panel__kpi-hint">
                    Faturamento dividido pelo número de vendas concluídas.
                    <?php if ($metrics['ticket_centavos'] === null): ?>
                        Sem vendas concluídas neste mês, o ticket não é calculado.
                    <?php endif; ?>
                </p>
            </article>
            <article class="demo-panel__kpi">
                <h2 class="demo-panel__kpi-label">Conversão</h2>
                <p class="demo-panel__kpi-value"><?= htmlspecialchars($conversionLabel, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="demo-panel__kpi-hint">
                    Vendas concluídas (<?= (int) $metrics['completed'] ?>)
                    divididas pelo total de oportunidades do período
                    (<?= (int) $metrics['opportunities'] ?>).
                    <?php if ($metrics['conversion'] === null): ?>
                        Sem oportunidades neste mês, a conversão não é calculada.
                    <?php endif; ?>
                </p>
            </article>
        </div>

        <p class="demo-panel__formula">
            Conversão = vendas concluídas ÷ total de oportunidades do mês.
            O denominador é a quantidade de oportunidades fictícias com data neste período
            (concluídas, em aberto e perdidas). Não entram meses anteriores nem posteriores.
        </p>

        <?php if ($isEmpty): ?>
            <p class="demo-panel__empty" role="status">
                Não há oportunidades fictícias neste mês. Os indicadores acima não reutilizam outro período
                e não mostram 0% de conversão nem ticket médio inventado.
            </p>
        <?php else: ?>
            <div class="demo-panel__table-wrap">
                <table class="demo-panel__table">
                    <caption>Oportunidades fictícias de <?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></caption>
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Situação</th>
                            <th scope="col">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metrics['items'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['occurred_on'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(demo_sales_status_label($row['status']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(demo_sales_format_money((int) $row['valor_centavos']), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="cta-row demo-panel__cta">
            <a
                class="btn btn--primary"
                id="demoPanelCta"
                href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') ?>"
                data-painel-message="<?= htmlspecialchars($ctaMessage, ENT_QUOTES, 'UTF-8') ?>"
            >
                Quero um painel assim
            </a>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
