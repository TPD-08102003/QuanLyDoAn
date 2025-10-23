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

    public function register() {}

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit;
        }

        try {
            $account = $this->accountModel->getAccountByUsernameOrEmail($username);
            if ($account && password_verify($password, $account['password'])) {
                // Kiểm tra trạng thái tài khoản
                if ($account['status'] !== 'active') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa hoặc không hoạt động!']);
                    exit;
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['account_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                $_SESSION['username'] = $account['username'];


                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'role' => $account['role']
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng!']);
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
        }
        exit;
    }
}
