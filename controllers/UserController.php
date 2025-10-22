<?php

namespace App;

use PDOException;

class UserController
{

    private $pdo;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $users = $this->userModel->getAllUsers();

        $title = 'Danh sách Người dùng';
        ob_start();
        require __DIR__ . '/../views/user/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $title = 'Thêm Người dùng';
        ob_start();
        require __DIR__ . '/../views/user/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['message'] = 'Bạn không có quyền thêm người dùng!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }

            $account_id = $_POST['account_id'] ?? 0;
            $full_name = trim($_POST['full_name'] ?? '');
            $avatar = $_POST['avatar'] ?? 'profile.png';
            $date_of_birth = $_POST['date_of_birth'] ?? null;
            $phone_number = trim($_POST['phone_number'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (!is_numeric($account_id) || $account_id <= 0) {
                $_SESSION['message'] = 'ID tài khoản không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }
            if (empty($full_name) || strlen($full_name) > 100) {
                $_SESSION['message'] = 'Họ tên không hợp lệ (tối đa 100 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }
            if (strlen($avatar) > 255) {
                $_SESSION['message'] = 'Avatar quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }
            if ($date_of_birth && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
                $_SESSION['message'] = 'Ngày sinh không đúng định dạng (YYYY-MM-DD)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }
            if ($phone_number && !preg_match('/^\d{10,20}$/', $phone_number)) {
                $_SESSION['message'] = 'Số điện thoại không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }
            if (strlen($address) > 255) {
                $_SESSION['message'] = 'Địa chỉ quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
                exit;
            }

            try {
                $this->userModel->createUser($account_id, $full_name, $avatar, $date_of_birth, $phone_number, $address);
                $_SESSION['message'] = 'Thêm người dùng thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /user');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /user/create');
            }
        }
    }

    public function show($user_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            $_SESSION['message'] = 'Người dùng không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /user');
            exit;
        }

        $title = 'Chi tiết Người dùng';
        ob_start();
        require __DIR__ . '/../views/user/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function edit($user_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            $_SESSION['message'] = 'Người dùng không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /user');
            exit;
        }

        $title = 'Sửa Người dùng';
        ob_start();
        require __DIR__ . '/../views/user/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function update($user_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['message'] = 'Bạn không có quyền sửa người dùng!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }

            $full_name = trim($_POST['full_name'] ?? '');
            $avatar = $_POST['avatar'] ?? 'profile.png';
            $date_of_birth = $_POST['date_of_birth'] ?? null;
            $phone_number = trim($_POST['phone_number'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($full_name) || strlen($full_name) > 100) {
                $_SESSION['message'] = 'Họ tên không hợp lệ (tối đa 100 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }
            if (strlen($avatar) > 255) {
                $_SESSION['message'] = 'Avatar quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }
            if ($date_of_birth && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
                $_SESSION['message'] = 'Ngày sinh không đúng định dạng (YYYY-MM-DD)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }
            if ($phone_number && !preg_match('/^\d{10,20}$/', $phone_number)) {
                $_SESSION['message'] = 'Số điện thoại không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }
            if (strlen($address) > 255) {
                $_SESSION['message'] = 'Địa chỉ quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
                exit;
            }

            try {
                $this->userModel->updateUser($user_id, $full_name, $avatar, $date_of_birth, $phone_number, $address);
                $_SESSION['message'] = 'Cập nhật người dùng thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /user');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /user/edit/$user_id");
            }
        }
    }

    public function destroy($user_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['message'] = 'Bạn không có quyền xóa người dùng!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /user');
            exit;
        }

        try {
            $this->userModel->deleteUser($user_id);
            $_SESSION['message'] = 'Xóa người dùng thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /user');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /user');
        }
    }
}
