<?php

require_once BASE_PATH . '/app/models/ConfigModel.php';

class ConfigController
{
    private function checkAuth()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
    }

    private function isAdmin()
    {
        return ($_SESSION['user_type'] ?? 'viewer') === 'admin';
    }

    public function emailTemplate()
    {
        $this->checkAuth();

        $model = new ConfigModel();
        $data = [
            'template' => $model->getEmailTemplate(),
            'userName' => $_SESSION['user_name']
        ];

        require BASE_PATH . '/app/views/config/email-template.php';
    }

    public function contato()
    {
        $this->checkAuth();

        $model = new ConfigModel();
        $data = [
            'contato' => $model->getContato(),
            'userName' => $_SESSION['user_name']
        ];

        require BASE_PATH . '/app/views/config/contato.php';
    }

    public function usuarios()
    {
        $this->checkAuth();

        if (!$this->isAdmin()) {
            header("Location: " . BASE_URL . "area-restrita");
            exit;
        }

        $model = new ConfigModel();
        $data = [
            'usuarios' => $model->getUsuarios(),
            'userName' => $_SESSION['user_name']
        ];

        require BASE_PATH . '/app/views/config/usuarios.php';
    }
}