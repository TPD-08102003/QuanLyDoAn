<?php

namespace App;

use PDO;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class AuthController
{
    private $pdo;
    private $accountModel;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->accountModel = new Account($pdo);
        $this->userModel = new User($pdo);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'student';
            $date_of_birth = $_POST['date_of_birth'] ?? null;
            $phone_number = trim($_POST['phone_number'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $avatar = 'profile.png';

            if (!in_array($role, ['student', 'teacher', 'admin'])) {
                $this->sendJsonResponse(false, 'Vai trò không hợp lệ!');
            }

            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                $this->sendJsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $existingAccount = $this->accountModel->getAccountByUsernameOrEmail($username);
            if ($existingAccount && $existingAccount['username'] === $username) {
                $this->sendJsonResponse(false, 'Tên đăng nhập đã tồn tại!');
            }
            $existingAccount = $this->accountModel->getAccountByUsernameOrEmail($email);
            if ($existingAccount && $existingAccount['email'] === $email) {
                $this->sendJsonResponse(false, 'Email đã tồn tại!');
            }

            try {
                $this->pdo->beginTransaction();
                $account_id = $this->accountModel->createAccount($username, $email, $hashedPassword, $role);
                $this->userModel->createUser($account_id, $full_name, $avatar, $date_of_birth, $phone_number, $address);
                $this->pdo->commit();
                $this->sendJsonResponse(true, 'Đăng ký thành công!');
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                $this->sendJsonResponse(false, 'Lỗi đăng ký!');
            }
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $this->sendJsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
            }

            $account = $this->accountModel->getAccountByUsernameOrEmail($username);
            if ($account && password_verify($password, $account['password']) && $account['status'] === 'active') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['account_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                $this->sendJsonResponse(true, 'Đăng nhập thành công!', ['role' => $account['role']]);
            } else {
                $this->sendJsonResponse(false, 'Thông tin đăng nhập không đúng hoặc tài khoản bị khóa!');
            }
        }
    }

    private function sendJsonResponse($success, $message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
        exit;
    }

    // Các phương thức khác như forgotPassword, resetPassword, changePassword, logout tương tự ví dụ đã cho, điều chỉnh nếu cần.
}
