<?php
/**
 * Controller da página inicial.
 */

class HomeController
{
    public function index(): void
    {
        require_once BASE_PATH . '/app/helpers/experience.php';

        $companyStartYear = 2024;
        $experienceYears  = company_experience_years($companyStartYear);
        $experienceLabel  = 'Mais de ' . $experienceYears . ' anos';

        $whatsAppNumber  = '5511999999999';
        $whatsAppMessage = 'Olá, gostaria de agendar uma consultoria.';
        $whatsAppUrl     = 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode($whatsAppMessage);

        $title = 'Rafael Holanda do Amaral | Soluções Profissionais';

        require BASE_PATH . '/app/views/home.php';
    }
}
