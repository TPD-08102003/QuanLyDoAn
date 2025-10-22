<?php

namespace App;

use PDOException;

class GroupController
{
    private $pdo;
    private $groupModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->groupModel = new Group($pdo);
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $groups = $this->groupModel->getAllGroups();

        $title = 'Danh sách Nhóm';
        ob_start();
        require __DIR__ . '/../views/group/index.php';
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

        $title = 'Thêm Nhóm';
        ob_start();
        require __DIR__ . '/../views/group/create.php';
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
                $_SESSION['message'] = 'Bạn không có quyền thêm nhóm!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /group/create');
                exit;
            }

            $project_id = $_POST['project_id'] ?? 0;
            $leader_id = $_POST['leader_id'] ?? 0;

            if (!is_numeric($project_id) || $project_id <= 0) {
                $_SESSION['message'] = 'ID đồ án không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /group/create');
                exit;
            }
            if (!is_numeric($leader_id) || $leader_id <= 0) {
                $_SESSION['message'] = 'ID trưởng nhóm không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /group/create');
                exit;
            }

            try {
                $this->groupModel->createGroup($project_id, $leader_id);
                $_SESSION['message'] = 'Thêm nhóm thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /group');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header('Location: /group/create');
            }
        }
    }

    public function show($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $group = $this->groupModel->getGroupById($group_id);
        if (!$group) {
            $_SESSION['message'] = 'Nhóm không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /group');
            exit;
        }

        $title = 'Chi tiết Nhóm';
        ob_start();
        require __DIR__ . '/../views/group/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function edit($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /');
            exit;
        }

        $group = $this->groupModel->getGroupById($group_id);
        if (!$group) {
            $_SESSION['message'] = 'Nhóm không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /group');
            exit;
        }

        $title = 'Sửa Nhóm';
        ob_start();
        require __DIR__ . '/../views/group/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function update($group_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền sửa nhóm!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group/edit/$group_id");
                exit;
            }

            $project_id = $_POST['project_id'] ?? 0;
            $leader_id = $_POST['leader_id'] ?? 0;

            if (!is_numeric($project_id) || $project_id <= 0) {
                $_SESSION['message'] = 'ID đồ án không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group/edit/$group_id");
                exit;
            }
            if (!is_numeric($leader_id) || $leader_id <= 0) {
                $_SESSION['message'] = 'ID trưởng nhóm không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group/edit/$group_id");
                exit;
            }

            try {
                $this->groupModel->updateGroup($group_id, $project_id, $leader_id);
                $_SESSION['message'] = 'Cập nhật nhóm thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /group');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group/edit/$group_id");
            }
        }
    }

    public function destroy($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            $_SESSION['message'] = 'Bạn không có quyền xóa nhóm!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /group');
            exit;
        }

        try {
            $this->groupModel->deleteGroup($group_id);
            $_SESSION['message'] = 'Xóa nhóm thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /group');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /group');
        }
    }
}
