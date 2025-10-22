<?php

namespace App;

use PDOException;

class ReportController
{
    private $pdo;
    private $reportModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->reportModel = new Report($pdo);
    }

    public function index($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $reports = $this->reportModel->getReportsByGroupId($group_id);

        $title = 'Danh sách Báo cáo';
        ob_start();
        require __DIR__ . '/../views/report/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function create($group_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            header('Location: /');
            exit;
        }

        $title = 'Thêm Báo cáo';
        ob_start();
        require __DIR__ . '/../views/report/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function store($group_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
                $_SESSION['message'] = 'Bạn không có quyền thêm báo cáo!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/create/$group_id");
                exit;
            }

            $file_path = trim($_POST['file_path'] ?? '');
            $code_link = trim($_POST['code_link'] ?? '');

            if (strlen($file_path) > 255) {
                $_SESSION['message'] = 'Đường dẫn file quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/create/$group_id");
                exit;
            }
            if (strlen($code_link) > 255) {
                $_SESSION['message'] = 'Link code quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/create/$group_id");
                exit;
            }

            try {
                $this->reportModel->createReport($group_id, $file_path, $code_link);
                $_SESSION['message'] = 'Thêm báo cáo thành công!';
                $_SESSION['message_type'] = 'success';
                header("Location: /report/$group_id");
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/create/$group_id");
            }
        }
    }

    public function show($report_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $report = $this->reportModel->getReportById($report_id);
        if (!$report) {
            $_SESSION['message'] = 'Báo cáo không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /report');
            exit;
        }

        $title = 'Chi tiết Báo cáo';
        ob_start();
        require __DIR__ . '/../views/report/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function edit($report_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            header('Location: /');
            exit;
        }

        $report = $this->reportModel->getReportById($report_id);
        if (!$report) {
            $_SESSION['message'] = 'Báo cáo không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /report');
            exit;
        }

        $title = 'Sửa Báo cáo';
        ob_start();
        require __DIR__ . '/../views/report/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function update($report_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
                $_SESSION['message'] = 'Bạn không có quyền sửa báo cáo!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/edit/$report_id");
                exit;
            }

            $file_path = trim($_POST['file_path'] ?? '');
            $code_link = trim($_POST['code_link'] ?? '');

            if (strlen($file_path) > 255) {
                $_SESSION['message'] = 'Đường dẫn file quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/edit/$report_id");
                exit;
            }
            if (strlen($code_link) > 255) {
                $_SESSION['message'] = 'Link code quá dài (tối đa 255 ký tự)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/edit/$report_id");
                exit;
            }

            try {
                $this->reportModel->updateReport($report_id, $file_path, $code_link);
                $_SESSION['message'] = 'Cập nhật báo cáo thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /report');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /report/edit/$report_id");
            }
        }
    }

    public function destroy($report_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            $_SESSION['message'] = 'Bạn không có quyền xóa báo cáo!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /report');
            exit;
        }

        try {
            $this->reportModel->deleteReport($report_id);
            $_SESSION['message'] = 'Xóa báo cáo thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /report');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /report');
        }
    }
}
