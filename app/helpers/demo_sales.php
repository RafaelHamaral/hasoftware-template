<?php
/**
 * Dados fictícios e indicadores do painel de vendas de demonstração.
 * Não consulta o banco. Valores em centavos para evitar arredondamento.
 */

/**
 * @return list<array{occurred_on:string,cliente:string,status:string,valor_centavos:int}>
 */
function demo_sales_opportunities(): array
{
    return [
        ['occurred_on' => '2026-09-02', 'cliente' => 'Cliente Alfa (fictício)', 'status' => 'concluida', 'valor_centavos' => 320000],
        ['occurred_on' => '2026-09-05', 'cliente' => 'Cliente Beta (fictício)', 'status' => 'perdida', 'valor_centavos' => 410000],
        ['occurred_on' => '2026-09-08', 'cliente' => 'Cliente Gama (fictício)', 'status' => 'concluida', 'valor_centavos' => 480000],
        ['occurred_on' => '2026-09-12', 'cliente' => 'Cliente Delta (fictício)', 'status' => 'em_aberto', 'valor_centavos' => 250000],
        ['occurred_on' => '2026-09-15', 'cliente' => 'Cliente Épsilon (fictício)', 'status' => 'concluida', 'valor_centavos' => 600000],
        ['occurred_on' => '2026-09-20', 'cliente' => 'Cliente Zeta (fictício)', 'status' => 'perdida', 'valor_centavos' => 180000],
        ['occurred_on' => '2026-09-24', 'cliente' => 'Cliente Eta (fictício)', 'status' => 'concluida', 'valor_centavos' => 600000],
        ['occurred_on' => '2026-09-28', 'cliente' => 'Cliente Teta (fictício)', 'status' => 'em_aberto', 'valor_centavos' => 330000],
        ['occurred_on' => '2026-10-03', 'cliente' => 'Cliente Iota (fictício)', 'status' => 'concluida', 'valor_centavos' => 900000],
        ['occurred_on' => '2026-10-10', 'cliente' => 'Cliente Kappa (fictício)', 'status' => 'em_aberto', 'valor_centavos' => 440000],
        ['occurred_on' => '2026-10-14', 'cliente' => 'Cliente Lambda (fictício)', 'status' => 'perdida', 'valor_centavos' => 270000],
        ['occurred_on' => '2026-10-21', 'cliente' => 'Cliente Mi (fictício)', 'status' => 'concluida', 'valor_centavos' => 600000],
        ['occurred_on' => '2026-10-29', 'cliente' => 'Cliente Ni (fictício)', 'status' => 'em_aberto', 'valor_centavos' => 510000],
    ];
}

/**
 * Meses oferecidos no filtro, na ordem de exibição.
 *
 * @return array<string, string> chave YYYY-MM => rótulo
 */
function demo_sales_months(): array
{
    return [
        '2026-09' => 'Setembro de 2026',
        '2026-10' => 'Outubro de 2026',
        '2026-11' => 'Novembro de 2026',
    ];
}

function demo_sales_default_month(): string
{
    return '2026-10';
}

function demo_sales_normalize_month(?string $month): string
{
    $months = demo_sales_months();
    if ($month !== null && isset($months[$month])) {
        return $month;
    }

    return demo_sales_default_month();
}

/**
 * @param list<array{occurred_on:string,cliente:string,status:string,valor_centavos:int}> $opportunities
 * @return array{
 *   month:string,
 *   items:list<array{occurred_on:string,cliente:string,status:string,valor_centavos:int}>,
 *   opportunities:int,
 *   completed:int,
 *   revenue_centavos:int,
 *   ticket_centavos:?int,
 *   conversion:?float
 * }
 */
function demo_sales_metrics(array $opportunities, string $month): array
{
    $month = demo_sales_normalize_month($month);
    $items = [];

    foreach ($opportunities as $row) {
        if (!isset($row['occurred_on']) || !str_starts_with((string) $row['occurred_on'], $month)) {
            continue;
        }
        $items[] = $row;
    }

    $completed = 0;
    $revenue = 0;
    foreach ($items as $row) {
        if (($row['status'] ?? '') !== 'concluida') {
            continue;
        }
        $completed++;
        $revenue += (int) ($row['valor_centavos'] ?? 0);
    }

    $total = count($items);

    return [
        'month' => $month,
        'items' => $items,
        'opportunities' => $total,
        'completed' => $completed,
        'revenue_centavos' => $revenue,
        'ticket_centavos' => $completed > 0 ? intdiv($revenue, $completed) : null,
        'conversion' => $total > 0 ? $completed / $total : null,
    ];
}

function demo_sales_format_money(int $centavos): string
{
    $reais = $centavos / 100;

    return 'R$ ' . number_format($reais, 2, ',', '.');
}

function demo_sales_format_percent(?float $ratio): string
{
    if ($ratio === null) {
        return '—';
    }

    return number_format($ratio * 100, 1, ',', '.') . '%';
}

function demo_sales_status_label(string $status): string
{
    return match ($status) {
        'concluida' => 'Venda concluída',
        'perdida' => 'Oportunidade perdida',
        'em_aberto' => 'Oportunidade em aberto',
        default => 'Oportunidade',
    };
}

function demo_sales_cta_message(): string
{
    return 'Olá, vi a demonstração do painel de vendas em Projetos e gostaria de um painel assim para o meu negócio.';
}
