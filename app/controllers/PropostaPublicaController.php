<?php

require_once BASE_PATH . '/app/models/PropostaModel.php';
require_once BASE_PATH . '/app/models/ConfigModel.php';

class PropostaPublicaController
{
    public function mostrar(): void
    {
        $token = (string) ($_GET['t'] ?? '');
        $model = new PropostaModel();
        $resolved = $model->resolveShareToken($token);
        $csrfToken = $this->csrfToken();
        $saveError = $_SESSION['proposta_public_error'] ?? '';
        $saveSuccess = $_SESSION['proposta_public_success'] ?? '';
        unset($_SESSION['proposta_public_error'], $_SESSION['proposta_public_success']);

        $title = 'Proposta';
        $status = $resolved['status'];

        if ($status !== 'ok') {
            $errorMessage = match ($status) {
                'expired' => 'Este link expirou.',
                'revoked' => 'Este link foi revogado.',
                default => 'Este link é inválido.',
            };
            require BASE_PATH . '/app/views/proposta-publica.php';
            return;
        }

        $proposta = $resolved['proposta'];
        $link = $resolved['link'];
        $marca = $this->marca();
        $errorMessage = '';
        require BASE_PATH . '/app/views/proposta-publica.php';
    }

    public function solicitarAjuste(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $token = (string) ($_POST['t'] ?? '');
        $redirect = BASE_URL . 'proposta?t=' . rawurlencode($token);

        $posted = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $posted)) {
            $_SESSION['proposta_public_error'] = 'Sessão expirada. Tente novamente.';
            header('Location: ' . $redirect);
            exit;
        }

        $model = new PropostaModel();
        $resolved = $model->resolveShareToken($token);
        if (($resolved['status'] ?? '') !== 'ok') {
            $_SESSION['proposta_public_error'] = 'Não foi possível enviar o pedido. O link não está mais válido.';
            header('Location: ' . $redirect);
            exit;
        }

        $mensagem = trim((string) ($_POST['ajuste'] ?? ''));
        if ($mensagem === '') {
            $_SESSION['proposta_public_error'] = 'Descreva o ajuste desejado.';
            header('Location: ' . $redirect);
            exit;
        }
        if (mb_strlen($mensagem) > 2000) {
            $_SESSION['proposta_public_error'] = 'O pedido de ajuste é longo demais.';
            header('Location: ' . $redirect);
            exit;
        }

        $ok = $model->addAdjustmentRequest(
            (int) $resolved['proposta']['id'],
            (int) $resolved['link']['id'],
            $mensagem
        );

        if (!$ok) {
            $_SESSION['proposta_public_error'] = 'Não foi possível registrar o pedido de ajuste.';
            header('Location: ' . $redirect);
            exit;
        }

        $_SESSION['proposta_public_success'] = 'Pedido de ajuste enviado. A equipe verá na área restrita.';
        header('Location: ' . $redirect);
        exit;
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * @return array{titulo: string, contatoLinha: string}
     */
    private function marca(): array
    {
        $config = new ConfigModel();
        $inicio = $config->getDadosInicio();
        $contato = $config->getContato();
        $titulo = trim((string) ($inicio['titulo'] ?? ''));
        if ($titulo === '') {
            $titulo = 'H. A Consultoria & Desenvolvimento';
        }
        $parts = array_filter([
            trim((string) ($contato['email'] ?? '')),
            trim((string) ($contato['telefone'] ?? '')),
        ]);

        return [
            'titulo' => $titulo,
            'contatoLinha' => $parts !== [] ? implode(' · ', $parts) : $titulo,
        ];
    }
}
