<?php
/**
 * Controller da página inicial.
 */

require_once BASE_PATH . '/app/models/ConfigModel.php';
require_once BASE_PATH . '/app/models/MensagemModel.php';

class HomeController
{
    public function index(): void
    {
        require_once BASE_PATH . '/app/helpers/experience.php';

        $companyStartYear = 2024;
        $experienceYears = company_experience_years($companyStartYear);
        $experienceLabel = 'Mais de ' . $experienceYears . ' anos';

        $title = 'H.A Consultoria & Desenvolvimento | Soluções Profissionais';

        $model = new ConfigModel();

        $data = [
            'contato' => $model->getContato(),
            'sobre' => $model->getSobreNos(),
            'inicio' => $model->getDadosInicio(),
        ];

        $whatsAppNumber = preg_replace(
            '/\D/',
            '',
            $data['contato']['whatsapp'] ?? ''
        );

        $whatsAppMessage = 'Olá, gostaria de agendar uma consultoria.';

        $whatsAppUrl = !empty($whatsAppNumber)
            ? 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode($whatsAppMessage)
            : '#contato';

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        $saveError = $_SESSION['contato_form_error'] ?? '';
        $saveSuccess = $_SESSION['contato_form_success'] ?? '';
        unset($_SESSION['contato_form_error'], $_SESSION['contato_form_success']);

        require_once BASE_PATH . '/app/helpers/demo_sales.php';
        $painelCtaMessage = demo_sales_cta_message();
        $automacaoCtaMessage = 'Olá, quero automatizar uma tarefa repetitiva do meu negócio.';
        $interesse = (string) ($_GET['interesse'] ?? '');
        $contactMessage = '';
        if ($interesse === 'painel') {
            $contactMessage = $painelCtaMessage;
        } elseif ($interesse === 'automacao') {
            $contactMessage = $automacaoCtaMessage;
        }

        require BASE_PATH . '/app/views/home.php';
    }

    public function enviarContato(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['contato_form_error'] = 'Sessão expirada. Tente novamente.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        $attempts = $_SESSION['contato_form_attempts'] ?? 0;
        $last = $_SESSION['contato_form_last'] ?? 0;
        if ($attempts >= 5 && (time() - $last) < 300) {
            $_SESSION['contato_form_error'] = 'Muitas tentativas. Aguarde alguns minutos.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }
        if ((time() - $last) >= 300) {
            $attempts = 0;
        }

        $nome = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? $_POST['email_login'] ?? ''));
        $mensagem = trim((string) ($_POST['message'] ?? ''));

        if ($nome === '' || $email === '' || $mensagem === '') {
            $_SESSION['contato_form_error'] = 'Preencha nome, e-mail e mensagem.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contato_form_error'] = 'Informe um e-mail válido.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        if (mb_strlen($mensagem) > 5000) {
            $_SESSION['contato_form_error'] = 'A mensagem é longa demais.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        $payload = [
            'nome'     => $nome,
            'email'    => $email,
            'mensagem' => $mensagem,
        ];

        try {
            $mensagemModel = new MensagemModel();
            $saved = $mensagemModel->save($payload);
        } catch (Throwable $e) {
            error_log('Erro ao salvar mensagem de contato: ' . $e->getMessage());
            $saved = false;
        }

        if (!$saved) {
            $_SESSION['contato_form_attempts'] = $attempts + 1;
            $_SESSION['contato_form_last'] = time();
            $_SESSION['contato_form_error'] = 'Não foi possível enviar a mensagem. Tente novamente.';
            header('Location: ' . BASE_URL . '#contato');
            exit;
        }

        require_once BASE_PATH . '/app/helpers/contact_mail.php';
        $config = new ConfigModel();
        contact_notification_send($payload, $config->getContato());

        unset($_SESSION['contato_form_attempts'], $_SESSION['contato_form_last']);
        $_SESSION['contato_form_success'] = 'Mensagem enviada com sucesso.';
        header('Location: ' . BASE_URL . '#contato');
        exit;
    }
}
