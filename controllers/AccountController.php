<?php

namespace App;

use PDO;
use PDOException;

class AccountController
{
    private $pdo;
    private $accountModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->accountModel = new Account($pdo);
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

        $accounts = $this->accountModel->getAllAccounts();

        $title = 'Danh sách Tài khoản';
        ob_start();
        require __DIR__ . '/../views/account/index.php';
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

        $title = 'Thêm Tài khoản';
        ob_start();
        require __DIR__ . '/../views/account/create.php';
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
                $_SESSION['message'] = 'Bạn không có quyền thêm tài khoản!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /account/create');
                exit;
            }

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';
            $status = $_POST['status'] ?? 'active';

            try {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $this->accountModel->createAccount($username, $email, $hashedPassword, $role, $status);
                $_SESSION['message'] = 'Thêm tài khoản thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /account');
            } catch (\InvalidArgumentException $e) {
                $_SESSION['message'] = $e->getMessage();
                $_SESSION['message_type'] = 'danger';
                header('Location: /account/create');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /account/create');
            }
        }
    }

    public function show($account_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        try {
            $account = $this->accountModel->getAccountById($account_id);
            if (!$account) {
                $_SESSION['message'] = 'Tài khoản không tồn tại!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /account');
                exit;
            }

            $title = 'Chi tiết Tài khoản';
            ob_start();
            require __DIR__ . '/../views/account/show.php';
            $content = ob_get_clean();
            require __DIR__ . '/../views/layouts/admin_layout.php';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header('Location: /account');
        }
    }

    public function edit($account_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        try {
            $account = $this->accountModel->getAccountById($account_id);
            if (!$account) {
                $_SESSION['message'] = 'Tài khoản không tồn tại!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /account');
                exit;
            }

            $title = 'Sửa Tài khoản';
            ob_start();
            require __DIR__ . '/../views/account/edit.php';
            $content = ob_get_clean();
            require __DIR__ . '/../views/layouts/admin_layout.php';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header('Location: /account');
        }
    }

    public function update($account_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['message'] = 'Bạn không có quyền sửa tài khoản!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /account/edit/$account_id");
                exit;
            }

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'student';
            $status = $_POST['status'] ?? 'active';
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

            try {
                $this->accountModel->updateAccount($account_id, $username, $email, $role, $status, $password);
                $_SESSION['message'] = 'Cập nhật tài khoản thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /account');
            } catch (\InvalidArgumentException $e) {
                $_SESSION['message'] = $e->getMessage();
                $_SESSION['message_type'] = 'danger';
                header("Location: /account/edit/$account_id");
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /account/edit/$account_id");
            }
        }
    }

    public function destroy($account_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['message'] = 'Bạn không có quyền xóa tài khoản!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /account');
            exit;
        }

        try {
            $this->accountModel->deleteAccount($account_id);
            $_SESSION['message'] = 'Xóa tài khoản thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /account');
        } catch (\InvalidArgumentException $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header('Location: /account');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /account');
        }
    }
}
