<?php

require_once BASE_PATH . '/app/models/ConfigModel.php';

class ConfigController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    private function sessionType(): string
    {
        $type = (string) ($_SESSION['user_type'] ?? '');
        return in_array($type, ['admin', 'editor', 'viewer'], true) ? $type : 'viewer';
    }

    private function isAdmin()
    {
        return $this->sessionType() === 'admin';
    }

    private function canEditContato(): bool
    {
        return in_array($this->sessionType(), ['admin', 'editor'], true);
    }

    private function requireAdmin()
    {
        $this->checkAuth();

        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'area-restrita');
            exit;
        }
    }

    private function requireContatoEditor()
    {
        $this->checkAuth();

        if (!$this->canEditContato()) {
            header('Location: ' . BASE_URL . 'area-restrita');
            exit;
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function consumeFlash(): array
    {
        $saveError = $_SESSION['config_flash_error'] ?? '';
        $saveSuccess = $_SESSION['config_flash_success'] ?? '';
        unset($_SESSION['config_flash_error'], $_SESSION['config_flash_success']);

        return [$saveError, $saveSuccess];
    }

    private function requirePostCsrf(string $redirectPath): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . $redirectPath);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['config_flash_error'] = 'Sessão expirada. Tente novamente.';
            header('Location: ' . BASE_URL . $redirectPath);
            exit;
        }
    }

    public function mensagens()
    {
        $this->checkAuth();

        require_once BASE_PATH . '/app/models/MensagemModel.php';
        $model = new MensagemModel();
        [$saveError, $saveSuccess] = $this->consumeFlash();

        $data = [
            'mensagens' => $model->listAll(),
            'userName' => $_SESSION['user_name']
        ];

        $isRestricted = true;
        require BASE_PATH . '/app/views/config/mensagens.php';
    }

    public function emailTemplate()
    {
        header('Location: ' . BASE_URL . 'config/mensagens');
        exit;
    }

    public function contato()
    {
        $this->requireContatoEditor();

        $model = new ConfigModel();
        $csrfToken = $this->csrfToken();
        [$saveError, $saveSuccess] = $this->consumeFlash();

        $data = [
            'contato' => $model->getContato(),
            'userName' => $_SESSION['user_name']
        ];

        $isRestricted = true;
        require BASE_PATH . '/app/views/config/contato.php';
    }

    public function salvarContato()
    {
        $this->requireContatoEditor();
        $this->requirePostCsrf('config/contato');

        $payload = [
            'titulo'    => trim((string) ($_POST['titulo'] ?? '')),
            'descricao' => trim((string) ($_POST['descricao'] ?? '')),
            'email'     => trim((string) ($_POST['email'] ?? '')),
            'telefone'  => trim((string) ($_POST['tel1'] ?? '')),
            'whatsapp'  => trim((string) ($_POST['tel2'] ?? '')),
        ];

        if ($payload['titulo'] === '' || $payload['descricao'] === '' || $payload['email'] === '') {
            $_SESSION['config_flash_error'] = 'Preencha título, descrição e e-mail.';
            header('Location: ' . BASE_URL . 'config/contato');
            exit;
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['config_flash_error'] = 'Informe um e-mail válido.';
            header('Location: ' . BASE_URL . 'config/contato');
            exit;
        }

        $model = new ConfigModel();
        $saved = $model->saveContato($payload);

        if (!$saved) {
            $_SESSION['config_flash_error'] = 'Não foi possível salvar os dados de contato.';
            header('Location: ' . BASE_URL . 'config/contato');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Dados de contato salvos com sucesso.';
        header('Location: ' . BASE_URL . 'config/contato');
        exit;
    }

    public function usuarios()
    {
        $this->requireAdmin();

        $model = new ConfigModel();
        $csrfToken = $this->csrfToken();
        [$saveError, $saveSuccess] = $this->consumeFlash();

        $data = [
            'usuarios' => $model->getUsuarios(),
            'userName' => $_SESSION['user_name'],
            'currentUserId' => (int) ($_SESSION['user_id'] ?? 0),
        ];

        $isRestricted = true;
        require BASE_PATH . '/app/views/config/usuarios.php';
    }

    public function salvarUsuarioTipo()
    {
        $this->requireAdmin();
        $this->requirePostCsrf('config/usuarios');

        $userId = (int) ($_POST['user_id'] ?? 0);
        $type = (string) ($_POST['type'] ?? '');
        $currentId = (int) ($_SESSION['user_id'] ?? 0);

        if ($userId <= 0 || !in_array($type, (new ConfigModel())->allowedUserTypes(), true)) {
            $_SESSION['config_flash_error'] = 'Selecione um tipo válido (admin, editor ou viewer).';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        if ($userId === $currentId) {
            $_SESSION['config_flash_error'] = 'Você não pode alterar o próprio tipo de acesso.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        $model = new ConfigModel();
        $saved = $model->updateUserType($userId, $type);

        if (!$saved) {
            $_SESSION['config_flash_error'] = 'Não foi possível atualizar o tipo do usuário.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Tipo de usuário atualizado com sucesso.';
        header('Location: ' . BASE_URL . 'config/usuarios');
        exit;
    }

    public function criarUsuario()
    {
        $this->requireAdmin();
        $this->requirePostCsrf('config/usuarios');

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $type = (string) ($_POST['type'] ?? '');
        $model = new ConfigModel();

        if ($name === '' || $email === '' || $password === '') {
            $_SESSION['config_flash_error'] = 'Preencha nome, e-mail e senha.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['config_flash_error'] = 'Informe um e-mail válido.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['config_flash_error'] = 'A senha deve ter no mínimo 8 caracteres.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        if (!in_array($type, $model->allowedUserTypes(), true)) {
            $_SESSION['config_flash_error'] = 'Selecione um tipo válido (admin, editor ou viewer).';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        if ($model->emailExists($email)) {
            $_SESSION['config_flash_error'] = 'Já existe um usuário com este e-mail.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $created = $model->createUser($name, $email, $hash, $type);

        if (!$created) {
            $_SESSION['config_flash_error'] = 'Não foi possível criar o usuário.';
            header('Location: ' . BASE_URL . 'config/usuarios');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Usuário criado com sucesso.';
        header('Location: ' . BASE_URL . 'config/usuarios');
        exit;
    }

    public function propostas()
    {
        $this->requireContatoEditor();

        require_once BASE_PATH . '/app/models/PropostaModel.php';
        $model = new PropostaModel();
        $csrfToken = $this->csrfToken();
        [$saveError, $saveSuccess] = $this->consumeFlash();

        $estagios = $model->allowedStages();
        $porEstagio = [];
        $contagemStatus = [];
        foreach (array_keys($estagios) as $chave) {
            $porEstagio[$chave] = [];
            $contagemStatus[$chave] = 0;
        }

        $faturado = 0.0;
        $horasTrabalhadas = 0.0;

        foreach ($model->listAll() as $item) {
            $chave = (string) ($item['estagio'] ?? 'aguardando_confirmacao');
            if ($chave === 'excluido') {
                continue;
            }
            if (!isset($porEstagio[$chave])) {
                $chave = 'aguardando_confirmacao';
            }
            $porEstagio[$chave][] = $item;
            $contagemStatus[$chave]++;

            if ($chave === 'fechado') {
                $faturado += (float) ($item['valor_proposta'] ?? 0);
                $horasTrabalhadas += (float) ($item['quantidade_horas'] ?? 0);
            }
        }

        $data = [
            'propostasPorEstagio' => $porEstagio,
            'estagios' => $estagios,
            'contagemStatus' => $contagemStatus,
            'faturado' => $faturado,
            'horasTrabalhadas' => $horasTrabalhadas,
            'userName' => $_SESSION['user_name'],
        ];

        $isRestricted = true;
        require BASE_PATH . '/app/views/config/propostas.php';
    }

    public function salvarProposta()
    {
        $this->requireContatoEditor();
        $this->requirePostCsrf('config/propostas');

        require_once BASE_PATH . '/app/models/PropostaModel.php';
        $model = new PropostaModel();
        $payload = $this->propostaPayload($model);

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        if (!$model->create($payload)) {
            $_SESSION['config_flash_error'] = 'Não foi possível salvar a proposta.';
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Proposta registrada com sucesso.';
        header('Location: ' . BASE_URL . 'config/propostas');
        exit;
    }

    public function atualizarProposta()
    {
        $this->requireContatoEditor();
        $this->requirePostCsrf('config/propostas');

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['config_flash_error'] = 'Proposta inválida.';
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        require_once BASE_PATH . '/app/models/PropostaModel.php';
        $model = new PropostaModel();
        $payload = $this->propostaPayload($model);

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        if (!$model->update($id, $payload)) {
            $_SESSION['config_flash_error'] = 'Não foi possível atualizar a proposta.';
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Proposta atualizada com sucesso.';
        header('Location: ' . BASE_URL . 'config/propostas');
        exit;
    }

    public function excluirProposta()
    {
        $this->requireContatoEditor();
        $this->requirePostCsrf('config/propostas');

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['config_flash_error'] = 'Proposta inválida.';
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        require_once BASE_PATH . '/app/models/PropostaModel.php';
        $model = new PropostaModel();

        if (!$model->softDelete($id)) {
            $_SESSION['config_flash_error'] = 'Não foi possível excluir a proposta.';
            header('Location: ' . BASE_URL . 'config/propostas');
            exit;
        }

        $_SESSION['config_flash_success'] = 'Proposta excluída do acompanhamento.';
        header('Location: ' . BASE_URL . 'config/propostas');
        exit;
    }

    private function propostaPayload(PropostaModel $model): ?array
    {
        $nome = trim((string) ($_POST['nome_cliente'] ?? ''));
        $tituloProjeto = trim((string) ($_POST['titulo_projeto'] ?? ''));
        $estagio = (string) ($_POST['estagio'] ?? '');
        $resumo = trim((string) ($_POST['resumo'] ?? ''));
        $valorRaw = $this->parseDecimal((string) ($_POST['valor_proposta'] ?? ''));
        $horasRaw = $this->parseDecimal((string) ($_POST['quantidade_horas'] ?? ''));
        $prazoInicio = $this->parseDate((string) ($_POST['prazo_inicio'] ?? ''));
        $prazoFinal = $this->parseDate((string) ($_POST['prazo_final'] ?? ''));

        if ($nome === '' || $tituloProjeto === '' || $resumo === '') {
            $_SESSION['config_flash_error'] = 'Preencha o nome do cliente, o título e o resumo do projeto.';
            return null;
        }

        if (!isset($model->allowedStages()[$estagio])) {
            $_SESSION['config_flash_error'] = 'Selecione um estágio válido.';
            return null;
        }

        if ($valorRaw === null || $valorRaw < 0) {
            $_SESSION['config_flash_error'] = 'Informe um valor de proposta válido.';
            return null;
        }

        if ($horasRaw === null || $horasRaw < 0) {
            $_SESSION['config_flash_error'] = 'Informe a quantidade de horas.';
            return null;
        }

        if ($prazoInicio === null || $prazoFinal === null) {
            $_SESSION['config_flash_error'] = 'Informe o prazo de início e o prazo final.';
            return null;
        }

        if ($prazoFinal < $prazoInicio) {
            $_SESSION['config_flash_error'] = 'O prazo final não pode ser anterior ao início.';
            return null;
        }

        return [
            'nome_cliente'     => $nome,
            'titulo_projeto'   => $tituloProjeto,
            'estagio'          => $estagio,
            'valor_proposta'   => number_format($valorRaw, 2, '.', ''),
            'quantidade_horas' => number_format($horasRaw, 2, '.', ''),
            'resumo'           => $resumo,
            'prazo_inicio'     => $prazoInicio,
            'prazo_final'      => $prazoFinal,
        ];
    }

    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $raw);
        if ($date === false) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function parseDecimal(string $raw): ?float
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }
}
