<?php
// controllers/UserController.php

namespace App\Controllers;

use PDO;
use App\Models\UserModel;
use App\Models\AccountModel;
use PDOException;

class UserController extends BaseController
{
    private UserModel $userModel;
    private AccountModel $accountModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->userModel = new UserModel($pdo);
        $this->accountModel = new AccountModel($pdo);
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
                'address' => $_POST['address'] ?? null,
                'gender' => $_POST['gender'] ?? null
            ];
            if ($this->userModel->update($id, $data)) {
                $this->redirect('users');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update user']);
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

        if (!$account || ($account['role'] !== 'student' && $account['role'] !== 'teacher')) {
            error_log("Access denied: Not a student or teacher or user not found for account_id: " . $account_id);
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập trang này!', 'redirect' => '/quanlydoan'], 403);
        }

        $userData = $this->userModel->findByAccountId($account_id);
        if (!$userData) {
            // Tạo user mặc định nếu không tồn tại
            $defaultUserData = [
                'full_name' => $account['username'] ?? ($account['role'] === 'student' ? 'Sinh viên' : 'Giảng viên'),
                'avatar' => 'profile.png',
                'date_of_birth' => null,
                'phone_number' => null,
                'address' => null,
                // Thêm gender nếu cần: 'gender' => null,
            ];
            $userId = $this->userModel->createWithAccount($account_id, $defaultUserData);
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi tạo thông tin người dùng!', 'redirect' => '/quanlydoan'], 500);
            }

            // Tạo entry cho student hoặc lecturer
            if ($account['role'] === 'student' || $account['role'] === 'teacher') {
                $mssv = $account['username']; // Giả sử username là mssv cho sinh viên
                $classId = 1; // Giả sử class_id mặc định là 1, điều chỉnh nếu cần
                if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Lỗi tạo thông tin sinh viên!', 'redirect' => '/quanlydoan'], 500);
                }
            } elseif ($account['role'] === 'lecturer') {
                if (!$this->userModel->createLecturer($userId)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Lỗi tạo thông tin giảng viên!', 'redirect' => '/quanlydoan'], 500);
                }
            }

            $userData = $this->userModel->findByAccountId($account_id);
        }

        $user = $this->userModel->getFullUser($userData['user_id']);

        $title = 'Hồ sơ ' . ($account['role'] === 'student' ? 'sinh viên' : 'giảng viên');
        $this->render('users/profile', [
            'title' => $title,
            'user' => $user
        ]); // Không cần chỉ định layout, BaseController sẽ chọn layout phù hợp
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

        if (!$account || ($account['role'] !== 'student' && $account['role'] !== 'teacher')) {
            error_log("Access denied: Not a student or teacher for account_id: " . $account_id);
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này!', 'redirect' => '/quanlydoan'], 403);
        }

        $userData = $this->userModel->findByAccountId($account_id);

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
                $this->jsonResponse(['success' => false, 'message' => 'Định dạng file không được hỗ trợ! Chỉ chấp nhận JPEG, PNG, GIF.', 'redirect' => '/quanlydoan/user/profile'], 400);
            }

            if ($fileSize > $maxFileSize) {
                $this->jsonResponse(['success' => false, 'message' => 'Kích thước file quá lớn! Tối đa 5MB.', 'redirect' => '/quanlydoan/user/profile'], 400);
            }

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $avatar = $newFileName;
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi tải lên avatar!', 'redirect' => '/quanlydoan/user/profile'], 500);
            }
        }

        // Kiểm tra dữ liệu đầu vào
        if (empty($email) || empty($full_name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Email và họ tên là bắt buộc!', 'redirect' => '/quanlydoan/user/profile'], 400);
        }

        try {
            $this->pdo->beginTransaction();

            // Kiểm tra email đã tồn tại
            $existingAccount = $this->accountModel->findByEmail($email);
            if ($existingAccount && $existingAccount['account_id'] != $account_id) {
                $this->pdo->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Email đã tồn tại!', 'redirect' => '/quanlydoan/user/profile'], 400);
            }

            // Cập nhật thông tin account
            $accountData = ['email' => $email];
            if ($password) {
                $accountData['password'] = $password;
            }
            $this->accountModel->update($account_id, $accountData);

            // Chuẩn bị dữ liệu user
            $userUpdateData = [
                'full_name' => $full_name,
                'avatar' => $avatar,
                'date_of_birth' => $date_of_birth,
                'phone_number' => $phone_number,
                'address' => $address
            ];

            if (!$userData) {
                // Tạo mới nếu không tồn tại
                $userId = $this->userModel->createWithAccount($account_id, $userUpdateData);
                if (!$userId) {
                    throw new PDOException('Lỗi tạo thông tin user');
                }

                // Tạo entry cho student hoặc teacher
                if ($account['role'] === 'student' || $account['role'] === 'teacher') {
                    $mssv = $account['username']; // Giả sử username là mssv cho sinh viên
                    $classId = 1; // Giả sử class_id mặc định là 1, điều chỉnh nếu cần
                    if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
                        throw new PDOException('Lỗi tạo thông tin sinh viên');
                    }
                } elseif ($account['role'] === 'teacher') {
                    if (!$this->userModel->createLecturer($userId)) {
                        throw new PDOException('Lỗi tạo thông tin giảng viên');
                    }
                }
            } else {
                // Cập nhật nếu tồn tại
                $this->userModel->update($userData['user_id'], $userUpdateData);
            }

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!', 'redirect' => '/quanlydoan/user/profile']);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Update profile error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage(), 'redirect' => '/quanlydoan/user/profile'], 500);
        }
    }
}
