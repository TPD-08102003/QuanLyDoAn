<?php
// controllers/NotificationController.php
// Note: Not in router, but adding for completeness

namespace App\Controllers;

use PDO;
use App\Models\NotificationModel;
use App\Models\UserModel;

class NotificationController extends BaseController
{
    private NotificationModel $notificationModel;
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->notificationModel = new NotificationModel($pdo);
        $this->userModel = new UserModel($pdo);
    }

    public function manage(): void
    {
        $this->checkAdminRole();
        $this->render('notifications/manage');
    }

    // --- DÀNH CHO ADMIN (Xem danh sách thông báo) ---
    public function list_admin(): void
    {
        $this->checkAdminRole();

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Lấy thông tin User ID của Admin từ session
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);

        if (!$user) {
            echo "Lỗi: Không tìm thấy thông tin tài khoản Admin.";
            return;
        }

        // Lấy thông báo của chính Admin đó (bao gồm thông báo duyệt đồ án)
        // unreadOnly = false (lấy tất cả)
        $notifications = $this->notificationModel->findByUser($user['user_id'], false);

        $this->render('notifications/list_admin', ['notifications' => $notifications]);
    }

    // --- DÀNH CHO USER (Xem danh sách thông báo) ---
    // URL: /quanlydoan/notification/list
    public function list(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['account_id'])) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        // Lấy User ID từ session
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        if (!$user) {
            echo "Lỗi: Không tìm thấy thông tin người dùng.";
            return;
        }

        // Lấy danh sách thông báo
        $notifications = $this->notificationModel->findByUser($user['user_id']);

        // Render view
        // Đảm bảo bạn đã tạo file view tại: App/Views/notifications/list.php
        $this->render('notifications/list', ['notifications' => $notifications]);
    }

    public function getReceivers(): void
    {
        $this->checkAdminRole();

        try {
            // Lấy danh sách Sinh viên (kèm MSSV)
            $sqlStudent = "SELECT u.user_id, u.full_name, s.mssv as code, 'Sinh viên' as role_name 
                           FROM students s 
                           JOIN users u ON s.user_id = u.user_id";
            $stmtStudent = $this->pdo->prepare($sqlStudent);
            $stmtStudent->execute();
            $students = $stmtStudent->fetchAll(PDO::FETCH_ASSOC);

            // Lấy danh sách Giảng viên (kèm MSGV)
            $sqlLecturer = "SELECT u.user_id, u.full_name, l.lecturer_code as code, 'Giảng viên' as role_name 
                            FROM lecturers l 
                            JOIN users u ON l.user_id = u.user_id";
            $stmtLecturer = $this->pdo->prepare($sqlLecturer);
            $stmtLecturer->execute();
            $lecturers = $stmtLecturer->fetchAll(PDO::FETCH_ASSOC);

            // Gộp lại
            $this->jsonResponse([
                'success' => true,
                'students' => $students,
                'lecturers' => $lecturers
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Xử lý gửi thông báo
    public function send(): void
    {
        $this->checkAdminRole();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $sendType = $_POST['send_type'] ?? 'group'; // 'group' hoặc 'individual'

        if (empty($title) || empty($message)) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập tiêu đề và nội dung.'], 400);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            $receiverIds = [];

            if ($sendType === 'individual') {
                // Trường hợp gửi cho từng cá nhân (Mảng user_id)
                if (isset($_POST['user_ids']) && is_array($_POST['user_ids'])) {
                    $receiverIds = $_POST['user_ids'];
                }
            } else {
                // Trường hợp gửi theo nhóm (All, Teacher, Student)
                $targetGroup = $_POST['target_group'] ?? 'all';
                $sql = "SELECT u.user_id FROM users u JOIN accounts a ON u.account_id = a.account_id WHERE a.status = 'active'";

                if ($targetGroup === 'teacher') {
                    $sql .= " AND a.role = 'teacher'";
                } elseif ($targetGroup === 'student') {
                    $sql .= " AND a.role = 'student'";
                }

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN); // Lấy mảng ID phẳng
                $receiverIds = $users;
            }

            if (empty($receiverIds)) {
                $this->pdo->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy người nhận nào.'], 400);
                return;
            }

            // Gửi thông báo (Loop)
            $count = 0;
            foreach ($receiverIds as $uid) {
                // Đảm bảo không gửi trùng lặp nếu frontend gửi ID trùng
                $this->notificationModel->createNotification((int)$uid, $title, $message);
                $count++;
            }

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => "Đã gửi thông báo thành công cho $count người dùng."]);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    private function checkAdminRole()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /quanlydoan/auth/login');
            exit;
        }
    }

    public function index(int $userId): void
    {
        $notifications = $this->notificationModel->findByUser($userId);
        $this->render('notifications/index', ['notifications' => $notifications]);
    }

    public function unread(int $userId): void
    {
        $notifications = $this->notificationModel->findByUser($userId, true);
        $this->render('notifications/unread', ['notifications' => $notifications]);
    }

    public function markRead(int $id): void
    {
        if ($this->notificationModel->markAsRead($id)) {
            $this->jsonResponse(['success' => true]);
        }
        $this->jsonResponse(['success' => false]);
    }

    public function markAllRead(int $userId): void
    {
        $this->notificationModel->markAllAsRead($userId);
        $this->redirect('notifications');
    }

    public function count(int $userId): void
    {
        $count = $this->notificationModel->getUnreadCount($userId);
        $this->jsonResponse(['count' => $count]);
    }
}
