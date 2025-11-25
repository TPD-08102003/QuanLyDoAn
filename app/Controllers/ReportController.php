<?php
// controllers/ReportController.php

namespace App\Controllers;

use PDO;
use App\Models\ReportModel;
use App\Models\GroupModel;
use App\Models\UserModel;
use App\Models\LecturerModel;

use Exception;


class ReportController extends BaseController
{
    private ReportModel $reportModel;
    private GroupModel $groupModel;
    private UserModel $userModel;
    private LecturerModel $lecturerModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->reportModel = new ReportModel($pdo);
        $this->groupModel = new GroupModel($pdo);
        $this->userModel = new UserModel($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
    }

    public function index(): void
    {
        $reports = $this->reportModel->findAll();
        $this->render('reports/index', ['reports' => $reports]);
    }

    public function create(int $groupId): void
    {
        $group = $this->groupModel->getFullGroup($groupId);
        $this->render('reports/create', ['group' => $group]);
    }

    public function store(int $groupId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filePath = $_POST['file_path'] ?? null;
            $codeLink = $_POST['code_link'] ?? null;
            $reportId = $this->reportModel->submit($groupId, $filePath, $codeLink);
            if ($reportId) {
                $this->redirect("groups/show/$groupId");
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to submit report']);
    }

    public function show(int $id): void
    {
        $report = $this->reportModel->getFullReport($id);
        $this->render('reports/show', ['report' => $report]);
    }

    public function edit(int $id): void
    {
        $report = $this->reportModel->getFullReport($id);
        $this->render('reports/edit', ['report' => $report]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'file_path' => $_POST['file_path'] ?? null,
                'code_link' => $_POST['code_link'] ?? null
            ];
            if ($this->reportModel->update($id, $data)) {
                $this->redirect('reports');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update report']);
    }

    public function destroy(int $id): void
    {
        if ($this->reportModel->delete($id)) {
            $this->redirect('reports');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete report']);
    }

    /**
     * Hiển thị trang quản lý tiến độ cho Giảng viên
     * URL: /quanlydoan/report/manage_progress
     */
    public function manage_progress(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra quyền Teacher
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            // 2. Lấy thông tin giảng viên từ session account_id
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

            if (!$lecturer) {
                echo "Lỗi: Không tìm thấy thông tin giảng viên.";
                return;
            }

            // 3. Lấy danh sách Report Types
            $rawTypes = $this->reportModel->getReportTypesByLecturer($lecturer['lecturer_id']);

            // 4. Gom nhóm dữ liệu theo Project để dễ hiển thị ở View
            $projectsProgress = [];
            foreach ($rawTypes as $type) {
                $projId = $type['project_id'];
                if (!isset($projectsProgress[$projId])) {
                    $projectsProgress[$projId] = [
                        'title' => $type['project_title'],
                        'types' => []
                    ];
                }
                $projectsProgress[$projId]['types'][] = $type;
            }

            $this->render('reports/manage_progress', [
                'projectsProgress' => $projectsProgress
            ]);
        } catch (Exception $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }

    /**
     * Xử lý cập nhật deadline/mô tả (Gọi từ Ajax hoặc Form POST)
     * URL: /quanlydoan/report/update_type
     */
    public function update_type(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid Method'], 405);
            return;
        }

        $typeId = $_POST['type_id'] ?? 0;
        $deadline = $_POST['deadline'] ?? '';
        $description = $_POST['description'] ?? '';
        $max_score = $_POST['max_score'] ?? '';

        if (!$typeId || !$deadline) {
            $this->jsonResponse(['success' => false, 'message' => 'Thiếu dữ liệu bắt buộc.'], 400);
            return;
        }

        $success = $this->reportModel->updateReportType($typeId, [
            'deadline' => $deadline,
            'description' => $description,
            'max_score' => $max_score
        ]);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi cập nhật CSDL.'], 500);
        }
    }

    // API lấy chi tiết để đổ vào Modal sửa
    public function get_type_detail(): void
    {
        $id = $_GET['id'] ?? 0;
        $data = $this->reportModel->getReportTypeById($id);
        if ($data) {
            $this->jsonResponse(['success' => true, 'data' => $data]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Not found'], 404);
        }
    }

    /**
     * Trang chi tiết & Chấm điểm
     * URL: /quanlydoan/report/grading/{id}
     */
    public function grading(int $id): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Check quyền Teacher
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            // 2. Lấy chi tiết báo cáo
            $report = $this->reportModel->getReportDetailForGrading($id);

            if (!$report) {
                echo "Không tìm thấy báo cáo.";
                return;
            }

            // 3. Security Check: Giảng viên chỉ được xem đồ án của mình
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

            if ($report['lecturer_id'] != $lecturer['lecturer_id']) {
                echo "Bạn không có quyền chấm bài này.";
                return;
            }

            // 4. Lấy file đính kèm
            $files = $this->reportModel->getReportFiles($id);

            // 5. Lấy thành viên nhóm (để biết ai nộp)
            $stmtMem = $this->pdo->prepare("
                SELECT u.full_name, s.mssv 
                FROM group_members gm 
                JOIN students s ON gm.student_id = s.student_id 
                JOIN users u ON s.user_id = u.user_id 
                WHERE gm.group_id = ?
            ");
            $stmtMem->execute([$report['group_id']]);
            $members = $stmtMem->fetchAll(PDO::FETCH_ASSOC);

            $this->render('reports/grading', [
                'report' => $report,
                'files' => $files,
                'members' => $members
            ]);
        } catch (Exception $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }

    /**
     * Xử lý lưu điểm
     * URL: /quanlydoan/report/store_grade
     */
    public function store_grade(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid Method'], 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $reportId = $_POST['report_id'];
            $score = (float)$_POST['score'];
            $comment = trim($_POST['comment']);

            // Lấy ID giảng viên hiện tại
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

            // Validate điểm số
            if ($score < 0 || $score > 10) { // Hoặc check theo max_score của bài
                $this->jsonResponse(['success' => false, 'message' => 'Điểm số không hợp lệ (0-10).'], 400);
                return;
            }

            $result = $this->reportModel->saveGrade($reportId, $lecturer['lecturer_id'], $score, $comment);

            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Đã chấm điểm thành công!']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu.'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}
