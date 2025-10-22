<?php

namespace App;

use PDOException;

class ProjectController
{
    private $pdo;
    private $projectModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->projectModel = new Project($pdo);
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['account_id'])) {
            header('Location: /');
            exit;
        }

        $projects = $this->projectModel->getAllProjects();

        $title = 'Danh sách Đồ án';
        ob_start();
        require __DIR__ . '/../views/project/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /');
            exit;
        }

        $title = 'Thêm Đồ án';
        ob_start();
        require __DIR__ . '/../views/project/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền thêm đồ án!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /project/create');
                exit;
            }

            $title = trim($_POST['title'] ?? '');
            $description = $_POST['description'] ?? '';
            $lecturer_id = $_POST['lecturer_id'] ?? 0;
            $status = $_POST['status'] ?? 'DangThucHien';

            if (empty($title) || strlen($title) > 255) {
                $_SESSION['message'] = 'Tiêu đề không hợp lệ (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /project/create');
                exit;
            }
            if (!is_numeric($lecturer_id) || $lecturer_id <= 0) {
                $_SESSION['message'] = 'ID giảng viên không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /project/create');
                exit;
            }
            if (!in_array($status, ['DangThucHien', 'HoanThanh'])) {
                $_SESSION['message'] = 'Trạng thái không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /project/create');
                exit;
            }

            try {
                $this->projectModel->createProject($title, $description, $lecturer_id, $status);
                $_SESSION['message'] = 'Thêm đồ án thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /project');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /project/create');
            }
        }
    }

    public function show($project_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['account_id'])) {
            header('Location: /');
            exit;
        }

        $project = $this->projectModel->getProjectById($project_id);
        if (!$project) {
            $_SESSION['message'] = 'Đồ án không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /project');
            exit;
        }

        $title = 'Chi tiết Đồ án';
        ob_start();
        require __DIR__ . '/../views/project/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function edit($project_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /');
            exit;
        }

        $project = $this->projectModel->getProjectById($project_id);
        if (!$project) {
            $_SESSION['message'] = 'Đồ án không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /project');
            exit;
        }

        $title = 'Sửa Đồ án';
        ob_start();
        require __DIR__ . '/../views/project/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function update($project_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền sửa đồ án!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /project/edit/$project_id");
                exit;
            }

            $title = trim($_POST['title'] ?? '');
            $description = $_POST['description'] ?? '';
            $lecturer_id = $_POST['lecturer_id'] ?? 0;
            $status = $_POST['status'] ?? 'DangThucHien';

            if (empty($title) || strlen($title) > 255) {
                $_SESSION['message'] = 'Tiêu đề không hợp lệ (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /project/edit/$project_id");
                exit;
            }
            if (!is_numeric($lecturer_id) || $lecturer_id <= 0) {
                $_SESSION['message'] = 'ID giảng viên không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /project/edit/$project_id");
                exit;
            }
            if (!in_array($status, ['DangThucHien', 'HoanThanh'])) {
                $_SESSION['message'] = 'Trạng thái không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /project/edit/$project_id");
                exit;
            }

            try {
                $this->projectModel->updateProject($project_id, $title, $description, $lecturer_id, $status);
                $_SESSION['message'] = 'Cập nhật đồ án thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /project');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /project/edit/$project_id");
            }
        }
    }

    public function destroy($project_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            $_SESSION['message'] = 'Bạn không có quyền xóa đồ án!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /project');
            exit;
        }

        try {
            $this->projectModel->deleteProject($project_id);
            $_SESSION['message'] = 'Xóa đồ án thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /project');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /project');
        }
    }
}
