<?php
// controllers/AccountController.php

namespace App\Controllers;

use PDO;
use App\Models\AccountModel;
use App\Models\UserModel;

class AccountController extends BaseController
{
    private AccountModel $accountModel;
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->userModel = new UserModel($pdo);
    }

    public function index(): void
    {
        $accounts = $this->accountModel->findAll();
        $this->render('accounts/index', ['accounts' => $accounts]);
    }

    public function create(): void
    {
        $this->render('accounts/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'student'
            ];
            $data = $this->accountModel->prepareData($data);
            $accountId = $this->accountModel->create($data);
            if ($accountId) {
                // Create user record
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $_POST['full_name'] ?? '',
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'address' => $_POST['address'] ?? null
                ];
                $this->userModel->create($userData);
                $this->redirect('accounts');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create account']);
    }

    public function show(int $id): void
    {
        $account = $this->accountModel->findById($id);
        $user = $this->userModel->findByAccountId($id);
        $this->render('accounts/show', ['account' => $account, 'user' => $user]);
    }

    public function edit(int $id): void
    {
        $account = $this->accountModel->findById($id);
        $this->render('accounts/edit', ['account' => $account]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'student'
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            $data = $this->accountModel->prepareData($data);
            if ($this->accountModel->update($id, $data)) {
                $this->redirect('accounts');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update account']);
    }

    public function destroy(int $id): void
    {
        if ($this->accountModel->delete($id)) {
            $this->redirect('accounts');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete account']);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usernameOrEmail = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $account = $this->accountModel->authenticate($usernameOrEmail, $password);
            if ($account) {
                $_SESSION['user_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                $this->redirect('home');
            } else {
                $this->render('accounts/login', ['error' => 'Invalid credentials']);
            }
        } else {
            $this->render('accounts/login');
        }
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }
}
