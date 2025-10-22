<?php

namespace App;

use PDOException;

class GroupMemberController
{
    private $pdo;
    private $groupMemberModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->groupMemberModel = new GroupMember($pdo);
    }

    public function index($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $members = $this->groupMemberModel->getMembersByGroupId($group_id);

        $title = 'Danh sách Thành viên Nhóm';
        ob_start();
        require __DIR__ . '/../views/group_member/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function add($group_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền thêm thành viên!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group_member/$group_id");
                exit;
            }

            $student_id = $_POST['student_id'] ?? 0;

            if (!is_numeric($student_id) || $student_id <= 0) {
                $_SESSION['message'] = 'ID sinh viên không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group_member/$group_id");
                exit;
            }

            try {
                $this->groupMemberModel->addMember($group_id, $student_id);
                $_SESSION['message'] = 'Thêm thành viên thành công!';
                $_SESSION['message_type'] = 'success';
                header("Location: /group_member/$group_id");
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /group_member/$group_id");
            }
        }
    }

    public function remove($group_id, $student_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            $_SESSION['message'] = 'Bạn không có quyền xóa thành viên!';
            $_SESSION['message_type'] = 'danger';
            header("Location: /group_member/$group_id");
            exit;
        }

        try {
            $this->groupMemberModel->removeMember($group_id, $student_id);
            $_SESSION['message'] = 'Xóa thành viên thành công!';
            $_SESSION['message_type'] = 'success';
            header("Location: /group_member/$group_id");
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header("Location: /group_member/$group_id");
        }
    }
}
