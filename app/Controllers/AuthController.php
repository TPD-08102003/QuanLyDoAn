<?php

namespace App\Controllers;
// Trong AuthController hoặc phương thức đăng nhập

use App\Models\AccountModel;
use App\Models\UserModel;
use App\Models\PasswordResetTokenModel;
use PDO;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends BaseController
{
    private AccountModel $accountModel;
    private UserModel $userModel;
    private PasswordResetTokenModel $passwordResetTokenModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->userModel = new UserModel($pdo);
        $this->passwordResetTokenModel = new PasswordResetTokenModel($pdo);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim(htmlspecialchars($_POST['username'] ?? ''));
            $email = trim(htmlspecialchars($_POST['email'] ?? ''));
            $raw_password = $_POST['password'] ?? '';
            $full_name = trim(htmlspecialchars($_POST['full_name'] ?? ''));
            $role = trim(htmlspecialchars($_POST['role'] ?? ''));
            $date_of_birth = trim(htmlspecialchars($_POST['date_of_birth'] ?? ''));
            $phone_number = trim(htmlspecialchars($_POST['phone_number'] ?? ''));
            $address = trim(htmlspecialchars($_POST['address'] ?? ''));
            $avatar = 'profile.png';

            // Kiểm tra vai trò
            if (!in_array($role, ['student', 'teacher'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Vai trò không hợp lệ!']);
            }

            // Kiểm tra tên đăng nhập
            if (!preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $username)) {
                $this->jsonResponse(['success' => false, 'message' => 'Tên đăng nhập không hợp lệ! Chỉ cho phép chữ cái, số, dấu gạch dưới hoặc gạch ngang, từ 6-20 ký tự.']);
            }

            // Kiểm tra email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(['success' => false, 'message' => 'Email không hợp lệ!']);
            }

            // Kiểm tra mật khẩu
            if (strlen($raw_password) < 6) {
                $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu phải dài ít nhất 6 ký tự!']);
            }
            $password = password_hash($raw_password, PASSWORD_BCRYPT);

            // Kiểm tra ngày sinh
            if ($date_of_birth && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
                $this->jsonResponse(['success' => false, 'message' => 'Ngày sinh không đúng định dạng (YYYY-MM-DD)!']);
            }

            // Kiểm tra số điện thoại
            if ($phone_number && !preg_match('/^\+?\d{10,15}$/', $phone_number)) {
                $this->jsonResponse(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
            }

            // Kiểm tra trùng tên đăng nhập hoặc email
            $existingByUsername = $this->accountModel->findByUsername($username);
            $existingByEmail = $this->accountModel->findByEmail($email);
            if ($existingByUsername || $existingByEmail) {
                if ($existingByUsername) {
                    $this->jsonResponse(['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng!']);
                }
                if ($existingByEmail) {
                    $this->jsonResponse(['success' => false, 'message' => 'Email đã được sử dụng!']);
                }
            }

            // Kiểm tra các trường bắt buộc
            if ($username && $email && $raw_password && $full_name && $role) {
                try {
                    $this->pdo->beginTransaction();

                    $accountData = [
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'role' => $role,
                        'status' => 'active'
                    ];
                    $account_id = $this->accountModel->create($accountData);

                    $userData = [
                        'account_id' => $account_id,
                        'full_name' => $full_name,
                        'avatar' => $avatar,
                        'date_of_birth' => $date_of_birth ?: null,
                        'phone_number' => $phone_number ?: null,
                        'address' => $address ?: null
                    ];
                    $this->userModel->create($userData);

                    $this->pdo->commit();
                    $this->jsonResponse(['success' => true, 'message' => 'Đăng ký thành công!']);
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    error_log("Register error: " . $e->getMessage());
                    $this->jsonResponse(['success' => false, 'message' => 'Lỗi đăng ký: ' . $e->getMessage()]);
                }
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            }
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        }

        try {
            $account = $this->accountModel->authenticate($username, $password);
            if ($account) {
                // Kiểm tra trạng thái tài khoản
                if ($account['status'] !== 'active') {
                    $this->jsonResponse(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa hoặc không hoạt động!']);
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['account_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                $_SESSION['username'] = $account['username'];

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'role' => $account['role']
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng!']);
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
        }
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
        }

        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập email!']);
        }

        try {
            $account = $this->accountModel->findByEmail($email);
            if (!$account) {
                $this->jsonResponse(['success' => false, 'message' => 'Email không tồn tại!']);
            }

            $token = bin2hex(random_bytes(32));
            $this->passwordResetTokenModel->createToken($account['account_id'], $token, 60); // 60 minutes

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '0022410169@student.dthu.edu.vn';
                $mail->Password = 'mebx bfpj kdzz xhqy';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->setFrom('0022410911@student.dthu.edu.vn', 'Quan Ly Do An', true);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Đặt lại mật khẩu - Quan Ly Do An';

                $resetLink = "http://localhost/quanlydoan/auth/reset_password?token=$token";

                $mail->Body = $this->getPasswordResetEmailTemplate($resetLink);

                $mail->AltBody = mb_convert_encoding(
                    "Nhấn vào liên kết sau để đặt lại mật khẩu của bạn: $resetLink\nLiên kết này có hiệu lực trong 1 giờ.",
                    'UTF-8'
                );

                $mail->send();
                $this->jsonResponse(['success' => true, 'message' => 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn!']);
            } catch (Exception $e) {
                error_log("Email error: " . $mail->ErrorInfo);
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi gửi email: ' . $mail->ErrorInfo]);
            }
        } catch (PDOException $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
        }
    }

    private function getPasswordResetEmailTemplate($resetLink)
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Đặt lại mật khẩu - Study Sharing</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f5f5f5;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background-color: #0d6efd;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .email-header img {
                    height: 50px;
                }
                .email-body {
                    padding: 30px;
                }
                .email-footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                }
                .btn-reset {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #0d6efd;
                    color: white !important;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                    margin: 20px 0;
                }
                .text-muted {
                    color: #6c757d;
                    font-size: 14px;
                }
                .divider {
                    border-top: 1px solid #e9ecef;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h2></h2>
                    <p>Đặt lại mật khẩu</p>
                </div>
                
                <div class="email-body">
                    <p>Xin chào,</p>
                    <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    
                    <div style="text-align: center;">
                        <a href="$resetLink" class="btn-reset">Đặt lại mật khẩu</a>
                    </div>
                    
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                    
                    <div class="divider"></div>
                    
                    <p class="text-muted">
                        <strong>Liên kết này sẽ hết hạn sau 1 giờ.</strong><br>
                        Nếu nút trên không hoạt động, bạn có thể sao chép và dán đường dẫn sau vào trình duyệt:<br>
                        <a href="$resetLink" style="word-break: break-all;">$resetLink</a>
                    </p>
                </div>
                
                <div class="email-footer">
                    <p>© 2025 Quản lý Đồ Án. Đã đăng ký bản quyền.</p>
                    <p>Đây là email tự động, vui lòng không trả lời.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    public function reset_password()
    {
        $title = "Đặt lại mật khẩu";
        $this->render('auth/reset_password', ['title' => $title]);
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($token) || empty($password)) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
            }

            try {
                error_log("Reset password attempt with token: $token");
                $tokenData = $this->passwordResetTokenModel->findByToken($token);
                if (!$tokenData) {
                    error_log("Token not found or expired: $token");
                    $this->jsonResponse(['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn!']);
                }

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $account_id = $tokenData['account_id'];

                $accountData = ['password' => $hashedPassword];
                $accountData = $this->accountModel->prepareData($accountData);
                $this->accountModel->update($account_id, $accountData);

                $this->passwordResetTokenModel->delete((int) $tokenData['token_id']);

                $this->jsonResponse(['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công!']);
            } catch (PDOException $e) {
                error_log("Reset password error: " . $e->getMessage());
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
            }
        }
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['account_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ hoặc chưa đăng nhập!']);
        }

        $account_id = $_SESSION['account_id'];
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        }

        if ($new_password !== $confirm_new_password) {
            $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp!']);
        }

        try {
            $account = $this->accountModel->findById($account_id);
            if (!$account || !password_verify($current_password, $account['password'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!']);
            }

            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
            $accountData = ['password' => $hashedPassword];
            $accountData = $this->accountModel->prepareData($accountData);
            $this->accountModel->update($account_id, $accountData);

            $this->jsonResponse(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        $this->redirect('/quanlydoan');
    }
}
