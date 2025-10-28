<?php
// controllers/PasswordResetController.php
// Note: Not in router, but adding for completeness

namespace App\Controllers;

use PDO;
use App\Models\PasswordResetTokenModel;
use App\Models\AccountModel;

class PasswordResetController extends BaseController
{
    private PasswordResetTokenModel $tokenModel;
    private AccountModel $accountModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->tokenModel = new PasswordResetTokenModel($pdo);
        $this->accountModel = new AccountModel($pdo);
    }

    public function request(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $account = $this->accountModel->findByEmail($email);
            if ($account) {
                $token = bin2hex(random_bytes(32));
                $this->tokenModel->createToken($account['account_id'], $token);
                // Send email with token (implement email sending)
                $resetUrl = "http://yourdomain.com/reset/$token";
                // mail($email, 'Password Reset', "Reset link: $resetUrl");
                $this->jsonResponse(['success' => true, 'message' => 'Reset link sent']);
            }
        }
        $this->render('password_reset/request');
    }

    public function reset(string $token): void
    {
        $accountId = $this->tokenModel->validateToken($token);
        if ($accountId) {
            $this->render('password_reset/reset', ['token' => $token]);
        } else {
            $this->redirect('login');
        }
    }

    public function update(string $token): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $accountId = $this->tokenModel->validateToken($token);
            if ($accountId) {
                $data = ['password' => $password];
                $data = $this->accountModel->prepareData($data);
                $this->accountModel->update($accountId, $data);
                $this->redirect('login');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
    }
}
