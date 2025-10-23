<?php

namespace App;

use App\Account;
use App\User;
use App\Project;

class HomeAdminController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        $pdo = $this->pdo;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /quanlydoan');
            exit();
        }
        $accountModel = new Account($pdo);

        $userModel = new User($pdo);
        $projectModel = new Project($pdo);

        $latestProjects = array_slice($projectModel->getAllProjects(), 0, 5);
        $totalUsers = count($userModel->getAllUsers());
        $totalProjects = count($projectModel->getAllProjects());

        $title = 'Trang quản trị';
        ob_start();
        require __DIR__ . '/../views/HomeAdmin/index.php';
        $content = ob_get_clean();
        $pdo = $this->pdo;
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }
}
