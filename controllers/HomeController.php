<?php

namespace App;

use PDO;
use App\Account;
use App\User;
use App\Project;

class HomeController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {

        $accountModel = new Account($this->pdo);
        $userModel = new User($this->pdo);
        $projectModel = new Project($this->pdo);

        $latestProjects = array_slice($projectModel->getAllProjects(), 0, 5);
        $totalUsers = count($userModel->getAllUsers());
        $totalProjects = count($projectModel->getAllProjects());

        $title = 'Trang chủ';
        ob_start();
        require __DIR__ . '/../views/home/index.php';
        $content = ob_get_clean();
        $pdo = $this->pdo;
        require __DIR__ . '/../views/layouts/user_layout.php';
    }
}
