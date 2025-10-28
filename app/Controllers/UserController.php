<?php
// controllers/UserController.php

namespace App\Controllers;

use PDO;
use App\Models\UserModel;

class UserController extends BaseController
{
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->userModel = new UserModel($pdo);
    }

    public function index(): void
    {
        $users = $this->userModel->findAll();
        $this->render('users/index', ['users' => $users]);
    }

    public function show(int $id): void
    {
        $user = $this->userModel->getFullUser($id);
        $this->render('users/show', ['user' => $user]);
    }

    public function edit(int $id): void
    {
        $user = $this->userModel->getFullUser($id);
        $this->render('users/edit', ['user' => $user]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone_number' => $_POST['phone_number'] ?? null,
                'address' => $_POST['address'] ?? null
            ];
            if ($this->userModel->update($id, $data)) {
                $this->redirect('users');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update user']);
    }

    public function profile(): void
    {
        if (isset($_SESSION['user_id'])) {
            $user = $this->userModel->getFullUser($_SESSION['user_id']);
            $this->render('users/profile', ['user' => $user]);
        } else {
            $this->redirect('login');
        }
    }
}
