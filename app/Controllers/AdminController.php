<?php
// app/Controllers/AdminController.php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\UserModel;
use PDOException;

class AdminController extends BaseController
{
    private AccountModel $accountModel;
    private UserModel $userModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->userModel = new UserModel($pdo);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['account_id'])) {
            error_log("Access denied: No account_id in session");
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để truy cập hồ sơ!', 'redirect' => '/quanlydoan'], 401);
        }

        $account_id = $_SESSION['account_id'];
        $account = $this->accountModel->findById($account_id);

        if (!$account || $account['role'] !== 'admin') {
            error_log("Access denied: Not an admin or user not found for account_id: " . $account_id);
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập trang này!', 'redirect' => '/quanlydoan'], 403);
        }

        $userData = $this->userModel->findByAccountId($account_id);
        if (!$userData) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng!', 'redirect' => '/quanlydoan'], 404);
        }

        $user = $this->userModel->getFullUser($userData['user_id']);

        $title = 'Hồ sơ quản trị viên';
        $this->render('admin/profile', [
            'title' => $title,
            'user' => $user
        ]); // Không cần chỉ định layout, BaseController sẽ chọn 'admin_layout'
    }

    public function updateProfile()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['account_id'])) {
            error_log("Access denied: Invalid request or no account_id in session");
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để cập nhật hồ sơ!', 'redirect' => '/quanlydoan'], 401);
        }

        $account_id = $_SESSION['account_id'];
        $account = $this->accountModel->findById($account_id);

        if (!$account || $account['role'] !== 'admin') {
            error_log("Access denied: Not an admin for account_id: " . $account_id);
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này!', 'redirect' => '/quanlydoan'], 403);
        }

        $userData = $this->userModel->findByAccountId($account_id);
        if (!$userData) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng!', 'redirect' => '/quanlydoan'], 404);
        }

        $email = trim($_POST['email'] ?? '');
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $full_name = trim($_POST['full_name'] ?? '');
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $phone_number = !empty($_POST['phone_number']) ? $_POST['phone_number'] : null;
        $address = !empty($_POST['address']) ? $_POST['address'] : null;
        $avatar = $_POST['current_avatar'] ?? 'profile.png';

        // Xử lý tải lên avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/images/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            $fileType = $_FILES['avatar']['type'];
            $fileSize = $_FILES['avatar']['size'];
            $fileTmp = $_FILES['avatar']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $newFileName = 'avatar_' . $account_id . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;

            if (!in_array($fileType, $allowedTypes)) {
                $this->jsonResponse(['success' => false, 'message' => 'Định dạng file không được hỗ trợ! Chỉ chấp nhận JPEG, PNG, GIF.', 'redirect' => '/quanlydoan/admin/profile'], 400);
            }

            if ($fileSize > $maxFileSize) {
                $this->jsonResponse(['success' => false, 'message' => 'Kích thước file quá lớn! Tối đa 5MB.', 'redirect' => '/quanlydoan/admin/profile'], 400);
            }

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $avatar = $newFileName;
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi tải lên avatar!', 'redirect' => '/quanlydoan/admin/profile'], 500);
            }
        }

        // Kiểm tra dữ liệu đầu vào
        if (empty($email) || empty($full_name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Email và họ tên là bắt buộc!', 'redirect' => '/quanlydoan/admin/profile'], 400);
        }

        try {
            $this->pdo->beginTransaction();

            // Kiểm tra email đã tồn tại
            $existingAccount = $this->accountModel->findByEmail($email);
            if ($existingAccount && $existingAccount['account_id'] != $account_id) {
                $this->pdo->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Email đã tồn tại!', 'redirect' => '/quanlydoan/admin/profile'], 400);
            }

            // Cập nhật thông tin account
            $accountData = ['email' => $email];
            if ($password) {
                $accountData['password'] = $password;
            }
            $this->accountModel->update($account_id, $accountData);

            // Cập nhật thông tin user
            $userUpdateData = [
                'full_name' => $full_name,
                'avatar' => $avatar,
                'date_of_birth' => $date_of_birth,
                'phone_number' => $phone_number,
                'address' => $address
            ];
            $this->userModel->update($userData['user_id'], $userUpdateData);

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!', 'redirect' => '/quanlydoan/admin/profile']);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Update profile error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage(), 'redirect' => '/quanlydoan/admin/profile'], 500);
        }
    }
}
