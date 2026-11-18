<?php
/**
 * Página pública de projetos / demonstração do painel de vendas.
 */

class ProjetosController
{
    public function index(): void
    {
        require_once BASE_PATH . '/app/helpers/demo_sales.php';

        $month = demo_sales_normalize_month(isset($_GET['mes']) ? (string) $_GET['mes'] : null);
        $metrics = demo_sales_metrics(demo_sales_opportunities(), $month);
        $months = demo_sales_months();
        $ctaMessage = demo_sales_cta_message();
        $ctaUrl = BASE_URL . '?interesse=painel#contato';

        $title = 'Projetos | Painel de vendas (demonstração)';

        require BASE_PATH . '/app/views/projetos.php';
    }
}
