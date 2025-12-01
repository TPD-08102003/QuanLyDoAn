<?php
// controllers/ScoreController.php
namespace App\Models; // Lưu ý: Namespace gốc của bạn có thể là App\Controllers, hãy kiểm tra lại file gốc
namespace App\Controllers;

use PDO;
use App\Models\ScoreModel;
use App\Models\UserModel;
use App\Models\ProjectModel;
use App\Models\StudentModel;

class ScoreController extends BaseController
{
    private ScoreModel $scoreModel;
    private UserModel $userModel;
    private ProjectModel $projectModel;
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->scoreModel = new ScoreModel($pdo);
        $this->userModel = new UserModel($pdo);
        $this->projectModel = new ProjectModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function manage($projectId = null) // [SỬA] Bỏ type int để chấp nhận null
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['role'])) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        $role = $_SESSION['role'];

        // 2. Chặn role không hợp lệ
        if ($role !== 'teacher' && $role !== 'student' && $role !== 'admin') {
            header('Location: /quanlydoan');
            exit;
        }

        // 3. XỬ LÝ KHI KHÔNG CÓ PROJECT ID TRÊN URL
        if ($projectId === null) {
            if ($role === 'student') {
                // Nếu là Sinh viên: Tự động tìm đồ án của mình
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                // Tận dụng hàm getStudentScoreByUserId để lấy thông tin nhóm/đồ án
                $studentData = $this->scoreModel->getStudentScoreByUserId($user['user_id']);

                // --- ĐOẠN SỬA LỖI ---
                // Model trả về key 'group' khi đã công bố, và 'project' khi chưa công bố.
                // Ta cần lấy cái nào tồn tại.
                $foundGroup = $studentData['group'] ?? $studentData['project'] ?? null;

                if ($foundGroup && !empty($foundGroup['project_id'])) {
                    $projectId = $foundGroup['project_id'];
                } else {
                    // Sinh viên chưa có đồ án -> Hiển thị thông báo hoặc redirect
                    echo "<div class='container py-5'><div class='alert alert-warning text-center shadow-sm rounded-4 p-5'>
                            <h4>Chưa có dữ liệu đồ án</h4>
                            <p>Bạn chưa tham gia đồ án nào để xem điểm.</p>
                            <a href='/quanlydoan/project' class='btn btn-warning rounded-pill px-4'>Đăng ký ngay</a>
                          </div></div>";
                    return;
                }
            } else {
                // Nếu là Giảng viên: Bắt buộc phải chọn đồ án từ danh sách -> Redirect về trang QL Đồ án
                header('Location: /quanlydoan/project/myProjects');
                exit;
            }
        }

        // Ép kiểu về int sau khi đã xử lý
        $projectId = (int)$projectId;

        // 4. Lấy thông tin đồ án
        $project = $this->projectModel->getByIdWithDetails($projectId);
        if (!$project) {
            echo "Đồ án không tồn tại.";
            return;
        }

        // 5. Lấy dữ liệu điểm số
        $data = $this->scoreModel->getProjectScores($projectId);

        // 6. Render View
        $this->render('scores/manage', [
            'project' => $project,
            'types' => $data['types'],
            'groups' => $data['groups'],
            'role' => $role
        ]);
    }

    /**
     * API: Cập nhật trạng thái Công bố/Ẩn điểm (AJAX)
     * VẪN GIỮ QUYỀN TEACHER ĐỂ BẢO MẬT
     */
    public function toggle_publish(): void
    {
        $this->checkRole('teacher'); // Sinh viên gọi vào đây sẽ bị chặn

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $groupId = $_POST['group_id'] ?? 0;
        $status = (int)($_POST['status'] ?? 0);

        if ($this->scoreModel->togglePublish($groupId, $status)) {
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        }
        $this->jsonResponse(['success' => false, 'message' => 'Lỗi cập nhật'], 500);
    }

    /**
     * API: Cập nhật điểm Tổng kết (AJAX)
     * VẪN GIỮ QUYỀN TEACHER ĐỂ BẢO MẬT
     */
    public function update_total(): void
    {
        $this->checkRole('teacher'); // Sinh viên gọi vào đây sẽ bị chặn

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $groupId = $_POST['group_id'] ?? 0;
        $score = (float)($_POST['total_score'] ?? 0);

        if ($score < 0 || $score > 10) {
            $this->jsonResponse(['success' => false, 'message' => 'Điểm không hợp lệ (0-10)'], 400);
            return;
        }

        if ($this->scoreModel->updateTotalScore($groupId, $score)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đã lưu điểm tổng kết']);
        }
        $this->jsonResponse(['success' => false, 'message' => 'Lỗi lưu điểm'], 500);
    }

    /**
     * Helper kiểm tra quyền truy cập
     */
    private function checkRole(string $role): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            // Trả về JSON lỗi nếu là gọi AJAX, hoặc redirect nếu là gọi thường
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            header('Location: /quanlydoan/auth/login');
            exit;
        }
    }
}
