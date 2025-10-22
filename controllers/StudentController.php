<?php

namespace App;

use PDOException;

class StudentController
{
    private $pdo;
    private $studentModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->studentModel = new Student($pdo);
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

        $students = $this->studentModel->getAllStudents();

        $title = 'Danh sách Sinh viên';
        ob_start();
        require __DIR__ . '/../views/student/index.php';
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

        $title = 'Thêm Sinh viên';
        ob_start();
        require __DIR__ . '/../views/student/create.php';
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
                $_SESSION['message'] = 'Bạn không có quyền thêm sinh viên!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /student/create');
                exit;
            }

            $user_id = $_POST['user_id'] ?? 0;
            $mssv = trim($_POST['mssv'] ?? '');
            $class = trim($_POST['class'] ?? '');

            if (!is_numeric($user_id) || $user_id <= 0) {
                $_SESSION['message'] = 'ID người dùng không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /student/create');
                exit;
            }
            if (empty($mssv) || strlen($mssv) > 20) {
                $_SESSION['message'] = 'MSSV không hợp lệ (tối đa 20 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /student/create');
                exit;
            }
            if (strlen($class) > 50) {
                $_SESSION['message'] = 'Lớp quá dài (tối đa 50 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /student/create');
                exit;
            }

            try {
                $this->studentModel->createStudent($user_id, $mssv, $class);
                $_SESSION['message'] = 'Thêm sinh viên thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /student');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /student/create');
            }
        }
    }

    public function show($student_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $student = $this->studentModel->getStudentById($student_id);
        if (!$student) {
            $_SESSION['message'] = 'Sinh viên không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /student');
            exit;
        }

        $title = 'Chi tiết Sinh viên';
        ob_start();
        require __DIR__ . '/../views/student/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function edit($student_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }

        $student = $this->studentModel->getStudentById($student_id);
        if (!$student) {
            $_SESSION['message'] = 'Sinh viên không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /student');
            exit;
        }

        $title = 'Sửa Sinh viên';
        ob_start();
        require __DIR__ . '/../views/student/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function update($student_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['message'] = 'Bạn không có quyền sửa sinh viên!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /student/edit/$student_id");
                exit;
            }

            $mssv = trim($_POST['mssv'] ?? '');
            $class = trim($_POST['class'] ?? '');

            if (empty($mssv) || strlen($mssv) > 20) {
                $_SESSION['message'] = 'MSSV không hợp lệ (tối đa 20 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /student/edit/$student_id");
                exit;
            }
            if (strlen($class) > 50) {
                $_SESSION['message'] = 'Lớp quá dài (tối đa 50 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /student/edit/$student_id");
                exit;
            }

            try {
                $this->studentModel->updateStudent($student_id, $mssv, $class);
                $_SESSION['message'] = 'Cập nhật sinh viên thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /student');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /student/edit/$student_id");
            }
        }
    }

    public function destroy($student_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['message'] = 'Bạn không có quyền xóa sinh viên!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /student');
            exit;
        }

        try {
            $this->studentModel->deleteStudent($student_id);
            $_SESSION['message'] = 'Xóa sinh viên thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /student');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /student');
        }
    }
}
