<?php

namespace App;

use PDO;
use PDOException;

class FeedbackController
{
    private $pdo;
    private $feedbackModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->feedbackModel = new Feedback($pdo);
    }

    public function index($report_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $feedbacks = $this->feedbackModel->getFeedbackByReportId($report_id);

        $title = 'Danh sách Nhận xét';
        ob_start();
        require __DIR__ . '/../views/feedback/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function create($report_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /');
            exit;
        }

        $title = 'Thêm Nhận xét';
        ob_start();
        require __DIR__ . '/../views/feedback/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function store($report_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền thêm nhận xét!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/create/$report_id");
                exit;
            }

            $lecturer_id = $_POST['lecturer_id'] ?? 0;
            $comment = trim($_POST['comment'] ?? '');
            $score = $_POST['score'] ?? null;

            if (!is_numeric($lecturer_id) || $lecturer_id <= 0) {
                $_SESSION['message'] = 'ID giảng viên không hợp lệ!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/create/$report_id");
                exit;
            }
            if ($score && !is_numeric($score) || $score < 0 || $score > 10) {
                $_SESSION['message'] = 'Điểm không hợp lệ (0-10)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/create/$report_id");
                exit;
            }

            try {
                $this->feedbackModel->createFeedback($report_id, $lecturer_id, $comment, $score);
                $_SESSION['message'] = 'Thêm nhận xét thành công!';
                $_SESSION['message_type'] = 'success';
                header("Location: /feedback/$report_id");
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/create/$report_id");
            }
        }
    }

    public function show($feedback_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /');
            exit;
        }

        $feedback = $this->feedbackModel->getFeedbackById($feedback_id);
        if (!$feedback) {
            $_SESSION['message'] = 'Nhận xét không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /feedback');
            exit;
        }

        $title = 'Chi tiết Nhận xét';
        ob_start();
        require __DIR__ . '/../views/feedback/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function edit($feedback_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /');
            exit;
        }

        $feedback = $this->feedbackModel->getFeedbackById($feedback_id);
        if (!$feedback) {
            $_SESSION['message'] = 'Nhận xét không tồn tại!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /feedback');
            exit;
        }

        $title = 'Sửa Nhận xét';
        ob_start();
        require __DIR__ . '/../views/feedback/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }

    public function update($feedback_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $_SESSION['message'] = 'Bạn không có quyền sửa nhận xét!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/edit/$feedback_id");
                exit;
            }

            $comment = trim($_POST['comment'] ?? '');
            $score = $_POST['score'] ?? null;

            if ($score && !is_numeric($score) || $score < 0 || $score > 10) {
                $_SESSION['message'] = 'Điểm không hợp lệ (0-10)!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/edit/$feedback_id");
                exit;
            }

            try {
                $this->feedbackModel->updateFeedback($feedback_id, $comment, $score);
                $_SESSION['message'] = 'Cập nhật nhận xét thành công!';
                $_SESSION['message_type'] = 'success';
                header('Location: /feedback');
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
                $_SESSION['message_type'] = 'danger';
                header("Location: /feedback/edit/$feedback_id");
            }
        }
    }

    public function destroy($feedback_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            $_SESSION['message'] = 'Bạn không có quyền xóa nhận xét!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /feedback');
            exit;
        }

        try {
            $this->feedbackModel->deleteFeedback($feedback_id);
            $_SESSION['message'] = 'Xóa nhận xét thành công!';
            $_SESSION['message_type'] = 'success';
            header('Location: /feedback');
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi cơ sở dữ liệu!';
            $_SESSION['message_type'] = 'danger';
            header('Location: /feedback');
        }
    }
}
