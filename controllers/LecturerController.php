<?php

namespace App;

use PDOException;

class LecturerController
{
    private $pdo;
    private $lecturerModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->lecturerModel = new Lecturer($pdo);
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

        $lecturers = $this->lecturerModel->getAllLecturers();

        $title = 'Danh sách Giảng viên';
        ob_start();
        require __DIR__ . '/../views/lecturer/index.php';
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

        $title = 'Thêm Giảng viên';
        ob_start();
        require __DIR__ . '/../views/lecturer/create.php';
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
                $_SESSION['message'] = 'Bạn không có quyền thêm giảng viên!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /lecturer/create');
                exit;
            }

            $user_id = $_POST['user_id'] ?? 0;
            $department = trim($_POST['department'] ?? '');

            if (!is_numeric($user_id) || $user_id <= 0) {
                $_SESSION['message'] = 'ID người dùng không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /lecturer/create');
                exit;
            }
            if (strlen($department) > 100) {
                $_SESSION['message'] = 'Khoa quá dài (tối đa 100 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /lecturer/create');
                exit;
            }

            try {
                $this->lecturerModel->createLecturer($user_id, $department);
                $_SESSION['message'] = 'Thêm giảng viên thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /lecturer');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /lecturer/create');
            }
        }
    }

    public function show($lecturer_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $lecturer = $this->lecturerModel->getLecturerById($lecturer_id);
        if (!$lecturer) {
            $_SESSION['message'] = 'Giảng viên không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /lecturer');
            exit;
        }

        $title = 'Chi tiết Giảng viên';
        ob_start();
        require __DIR__ . '/../views/lecturer/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function edit($lecturer_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $lecturer = $this->lecturerModel->getLecturerById($lecturer_id);
        if (!$lecturer) {
            $_SESSION['message'] = 'Giảng viên không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /lecturer');
            exit;
        }

        $title = 'Sửa Giảng viên';
        ob_start();
        require __DIR__ . '/../views/lecturer/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function update($lecturer_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['message'] = 'Bạn không có quyền sửa giảng viên!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /lecturer/edit/$lecturer_id");
                exit;
            }

            $department = trim($_POST['department'] ?? '');

            if (strlen($department) > 100) {
                $_SESSION['message'] = 'Khoa quá dài (tối đa 100 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /lecturer/edit/$lecturer_id");
                exit;
            }

            try {
                $this->lecturerModel->updateLecturer($lecturer_id, $department);
                $_SESSION['message'] = 'Cập nhật giảng viên thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /lecturer');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /lecturer/edit/$lecturer_id");
            }
        }
    }

    public function destroy($lecturer_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['message'] = 'Bạn không có quyền xóa giảng viên!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /lecturer');
            exit;
        }

        try {
            $this->lecturerModel->deleteLecturer($lecturer_id);
            $_SESSION['message'] = 'Xóa giảng viên thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /lecturer');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /lecturer');
        }
    }
}
