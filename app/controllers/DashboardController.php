<?php

require_once BASE_PATH . '/app/models/DashboardModel.php';

class DashboardController
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $userName = $_SESSION['user_name'] ?? 'Usuário';
        // Ausência de type não concede admin.
        $userType = $_SESSION['user_type'] ?? 'viewer';

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