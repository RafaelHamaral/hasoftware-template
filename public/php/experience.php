<?php
/**
 * Funções reutilizáveis relacionadas à empresa.
 *
 * A ideia é manter pequenas lógicas (como "anos de experiência") aqui,
 * para que possam ser incluídas em qualquer página no futuro.
 */

/**
 * Calcula quantos anos completos se passaram desde um ano inicial até o ano atual.
 *
 * Exemplo:
 * - Ano inicial: 2019
 * - Ano atual: 2026
 * - Retorno: 7
 *
 * @param int $startYear Ano em que a empresa iniciou (ex: 2019).
 * @param int|null $currentYear Ano atual (se null, usa o ano do sistema).
 * @return int Anos de experiência (nunca negativo).
 */
function company_experience_years(int $startYear, ?int $currentYear = null): int
{
    // Se não vier um ano atual, usamos o ano do sistema (mantém automático).
    $yearNow = $currentYear ?? (int)date('Y');

    // Garante retorno seguro caso o ano inicial seja maior que o ano atual.
    return max(0, $yearNow - $startYear);
}

