<?php

require_once BASE_PATH . '/app/models/DashboardModel.php';

class DashboardController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔐 Verificação de autenticação (compatível com seu login)
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // 👤 Nome do usuário
        $userName = $_SESSION['user_name'] ?? 'Usuário';

        // 👤 Tipo (por enquanto mockado)
        $userType = $_SESSION['user_type'] ?? 'admin'; // ou 'viewer'

        // 📊 Dados
        $model = new DashboardModel();
        $stats = $model->getDashboardStats();

        $data = [
            'stats' => $stats,
            'userName' => $userName,
            'userType' => $userType
        ];

        require BASE_PATH . '/app/views/dashboard/area-restrita.php';
    }
}