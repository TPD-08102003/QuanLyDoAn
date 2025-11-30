<?php
// controllers/ProjectController.php

namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PDO;
use Exception;
use PDOException;
use App\Models\StudentModel;
use App\Models\ProjectModel;
use App\Models\LecturerModel;
use App\Models\FacultiesModel;
use App\Models\UserModel;

class ProjectController extends BaseController
{
    private ProjectModel $projectModel;
    private LecturerModel $lecturerModel;
    private FacultiesModel $facultiesModel;
    private StudentModel $studentModel;
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->projectModel = new ProjectModel($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
        $this->studentModel = new StudentModel($pdo);
        $this->userModel = new UserModel($pdo);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }


    /**
     * Hiển thị trang Đồ án của tôi
     * URL: /quanlydoan/project/myProjects
     */
    public function myProjects(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['account_id'])) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        // Tự động lấy Role nếu thiếu
        if (!isset($_SESSION['role'])) {
            $userData = $this->userModel->findByAccountId($_SESSION['account_id']);
            if ($userData) {
                $userFull = $this->userModel->getFullUser($userData['user_id']);
                $_SESSION['role'] = $userFull['role'] ?? null;
            }
        }
        $role = $_SESSION['role'];

        // --- TRƯỜNG HỢP 1: GIẢNG VIÊN ---
        if ($role === 'teacher') {
            try {
                $userData = $this->userModel->findByAccountId($_SESSION['account_id']);
                $lecturer = $this->lecturerModel->findByUserIdLecturer($userData['user_id']);
                if (!$lecturer) {
                    echo "Lỗi: Tài khoản chưa có thông tin giảng viên.";
                    return;
                }

                // 1. Lấy tham số từ URL
                $page = max(1, (int)($_GET['page'] ?? 1));
                $keyword = trim($_GET['keyword'] ?? '');
                $statusFilter = trim($_GET['status'] ?? '');
                $limit = 10;

                // 2. Lấy dữ liệu từ Model
                // Lưu ý: Lấy số lượng lớn (1000) để về lọc Status bằng PHP sau đó mới phân trang
                // Vì Model hiện tại có thể chưa hỗ trợ lọc Status trong SQL
                $result = $this->projectModel->getProjectsByLecturerIdWithPagination($lecturer['lecturer_id'], 1000, 0, $keyword);
                $projects = $result['projects'];

                // 3. Lọc theo trạng thái (Status) bằng PHP
                if (!empty($statusFilter)) {
                    $projects = array_filter($projects, function ($p) use ($statusFilter) {
                        return $p['status'] === $statusFilter;
                    });
                }

                // 4. Phân trang thủ công (Array Slice) sau khi đã lọc
                $totalProjects = count($projects);
                $totalPages = ceil($totalProjects / $limit);
                $offset = ($page - 1) * $limit;

                // Cắt mảng để lấy đúng dữ liệu trang hiện tại
                $pagedProjects = array_slice($projects, $offset, $limit);

                // 5. Render View
                $this->render('Projects/my_projects', [
                    'projects' => $pagedProjects,
                    'totalProjects' => $totalProjects,
                    'totalPages' => $totalPages,
                    'page' => $page,
                    'keyword' => $keyword,        // Truyền lại để hiển thị trên ô input
                    'selectedStatus' => $statusFilter // Truyền lại để giữ select box
                ]);
            } catch (Exception $e) {
                die("Lỗi Teacher: " . $e->getMessage());
            }
            return;
        }

        // --- TRƯỜNG HỢP 2: SINH VIÊN ---
        if ($role === 'student') {
            try {
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                $student = $this->studentModel->findByUserId($user['user_id']);

                if (!$student) {
                    echo "Lỗi: Không tìm thấy thông tin sinh viên.";
                    return;
                }

                // Query lấy thông tin đồ án
                // Lưu ý: Cần có cột max_students trong DB, dùng COALESCE để tránh lỗi nếu null
                $sql = "SELECT p.*, g.group_id, g.leader_id,
                               u.full_name as lecturer_name, f.faculty_name,
                               COALESCE(p.max_students, 3) as max_students
                        FROM group_members gm
                        JOIN groups g ON gm.group_id = g.group_id
                        JOIN projects p ON g.project_id = p.project_id
                        LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                        LEFT JOIN users u ON l.user_id = u.user_id
                        LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                        WHERE gm.student_id = :student_id
                        LIMIT 1";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':student_id' => $student['student_id']]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$project) {
                    // Chưa có đồ án -> Render view trống
                    $this->render('Projects/my_project_student', ['project' => null]);
                    return;
                }

                // Lấy thành viên nhóm
                $sqlMembers = "SELECT s.mssv, u.full_name, u.avatar, s.student_id
                               FROM group_members gm
                               JOIN students s ON gm.student_id = s.student_id
                               JOIN users u ON s.user_id = u.user_id
                               WHERE gm.group_id = :group_id";
                $stmtMem = $this->pdo->prepare($sqlMembers);
                $stmtMem->execute([':group_id' => $project['group_id']]);
                $members = $stmtMem->fetchAll(PDO::FETCH_ASSOC);

                // Lấy báo cáo
                $sqlReports = "SELECT rt.*, 
                                      r.report_id, r.status as report_status, r.submitted_at,
                                      up.file_name, up.file_path, up.file_type
                               FROM report_types rt
                               LEFT JOIN reports r ON rt.type_id = r.type_id AND r.group_id = :group_id
                               LEFT JOIN uploads up ON r.report_id = up.report_id
                               WHERE rt.project_id = :project_id
                               ORDER BY rt.deadline ASC";

                $stmtRep = $this->pdo->prepare($sqlReports);
                $stmtRep->execute([':group_id' => $project['group_id'], ':project_id' => $project['project_id']]);
                $reports = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

                // Render View
                $this->render('Projects/my_project_student', [
                    'project' => $project,
                    'members' => $members,
                    'reports' => $reports,
                    'studentId' => $student['student_id']
                ]);
            } catch (Exception $e) {
                // QUAN TRỌNG: In lỗi chi tiết ra màn hình để bạn thấy
                die("Lỗi Student: " . $e->getMessage());
            }
            return;
        }

        header('Location: /quanlydoan');
    }

    /**
     * Chức năng: Trưởng nhóm báo cáo hoàn thành đồ án
     * URL: /quanlydoan/project/finishProject
     */
    public function finishProject(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 405);
        }

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $student = $this->studentModel->findByUserId($user['user_id']);

            $projectId = (int)$_POST['project_id'];

            // 1. Kiểm tra quyền Trưởng nhóm
            // Chỉ leader_id trong bảng groups mới được phép đổi trạng thái
            $stmtCheck = $this->pdo->prepare("SELECT 1 FROM groups WHERE project_id = ? AND leader_id = ?");
            $stmtCheck->execute([$projectId, $student['student_id']]);

            if (!$stmtCheck->fetchColumn()) {
                $this->jsonResponse(['success' => false, 'message' => 'Bạn không phải trưởng nhóm của đồ án này.'], 403);
            }

            // 2. Cập nhật trạng thái dự án sang 'DaNopBaoCao' (Chờ giảng viên duyệt bảo vệ)
            $stmtUpdate = $this->pdo->prepare("UPDATE projects SET status = 'DaNopBaoCao' WHERE project_id = ? AND status = 'DangThucHien'");
            $stmtUpdate->execute([$projectId]);

            if ($stmtUpdate->rowCount() > 0) {
                $this->jsonResponse(['success' => true, 'message' => 'Đã xác nhận hoàn thành! Đồ án đang chờ giảng viên duyệt.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Không thể cập nhật trạng thái (Có thể đồ án đã hoàn thành hoặc bị hủy).']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function submitReport(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 405);
        }

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $student = $this->studentModel->findByUserId($user['user_id']);

            $typeId = (int)$_POST['type_id'];
            $groupId = (int)$_POST['group_id'];
            $linkUrl = trim($_POST['link_url'] ?? '');

            // Kiểm tra sinh viên có thuộc nhóm này không
            $stmtCheck = $this->pdo->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND student_id = ?");
            $stmtCheck->execute([$groupId, $student['student_id']]);
            if (!$stmtCheck->fetchColumn()) {
                $this->jsonResponse(['success' => false, 'message' => 'Bạn không thuộc nhóm này.'], 403);
            }

            $this->pdo->beginTransaction();

            // Tìm report đã tồn tại chưa
            $stmtRepCheck = $this->pdo->prepare("SELECT report_id FROM reports WHERE group_id = ? AND type_id = ?");
            $stmtRepCheck->execute([$groupId, $typeId]);
            $existingReportId = $stmtRepCheck->fetchColumn();

            if ($existingReportId) {
                $reportId = $existingReportId;
                $stmtUpd = $this->pdo->prepare("UPDATE reports SET submitted_at = NOW(), status = 'DaNop' WHERE report_id = ?");
                $stmtUpd->execute([$reportId]);
            } else {
                $stmtIns = $this->pdo->prepare("INSERT INTO reports (group_id, type_id, status, submitted_at) VALUES (?, ?, 'DaNop', NOW())");
                $stmtIns->execute([$groupId, $typeId]);
                $reportId = $this->pdo->lastInsertId();
            }

            // Xử lý File Upload
            if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['report_file'];
                $uploadDir = __DIR__ . '/../../uploads/reports/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newFileName = 'report_' . $reportId . '_' . time() . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                    $fileType = in_array($ext, ['zip', 'rar']) ? 'ZIP' : (in_array($ext, ['doc', 'docx']) ? 'DOCX' : 'PDF');
                    $stmtFile = $this->pdo->prepare("INSERT INTO uploads (report_id, file_name, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtFile->execute([$reportId, $file['name'], $newFileName, $fileType, $file['size'], $user['user_id']]);
                }
            }

            // Xử lý Link
            if (!empty($linkUrl)) {
                $stmtLink = $this->pdo->prepare("INSERT INTO uploads (report_id, file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, 'LINK', ?)");
                $stmtLink->execute([$reportId, 'Liên kết đính kèm', $linkUrl, $user['user_id']]);
            }

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Nộp báo cáo thành công!']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Hiển thị form tạo đồ án mới
     * URL: /project/create
     */
    public function create(): void
    {
        // Kiểm tra vai trò Giảng viên (teacher) hoặc Admin
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            // Lấy danh sách khoa/phòng ban nếu cần thiết cho form (Ví dụ: để chọn khoa cho đồ án)
            $faculties = $this->facultiesModel->getActiveFaculties();

            $this->render('projects/create', [
                'faculties' => $faculties,
                'isLecturer' => $_SESSION['role'] === 'teacher', // Truyền trạng thái Giảng viên
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'create');
        }
    }

    public function getProjectDetailsIndex(int $id): void
    {
        $project = $this->projectModel->getByIdWithDetails($id);

        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đồ án'], 404);
            return;
        }
        $members = $this->projectModel->getProjectMembers($id);

        // Xác định màu badge
        $statusColors = [
            'Đã hoàn thành' => 'success',
            'Đang thực hiện' => 'warning',
            'ChoDuyet'      => 'secondary',
            'Đã hủy'        => 'danger'

        ];
        $badgeColor = $statusColors[$project['status']] ?? 'info';

        $this->jsonResponse([
            'success' => true,
            'project' => [
                'project_id'           => (int)$project['project_id'],
                'title'                => $project['title'],
                'description'          => $project['description'] ?: 'Không có mô tả chi tiết.',
                'status'               => $project['status'],
                'badge_color'          => $badgeColor,
                'lecturer_name'        => $project['lecturer_name'] ?? 'Chưa phân công',
                'faculty_name'         => $project['faculty_name'] ?? 'Chưa xác định',
            ],
            'members' => $members
        ]);
    }

    // public function manage(): void
    // {
    //     try {
    //         $page = (int)($_GET['page'] ?? 1);
    //         $keyword = trim($_GET['keyword'] ?? '');
    //         $limit = 5;
    //         $offset = ($page - 1) * $limit;


    //         if (empty($keyword)) {
    //             $allProjects = $this->projectModel->getAllWithDetails();
    //             $totalProjects = count($allProjects);
    //             $projects = array_slice($allProjects, $offset, $limit);
    //             $totalPages = ceil($totalProjects / $limit);
    //         } else {
    //             // Giả sử ProjectModel có getProjectsWithPagination
    //             $result = $this->projectModel->getProjectsWithPagination($limit, $offset, $keyword);
    //             $projects = $result['projects'];
    //             $totalProjects = $result['total'];
    //             $totalPages = ceil($totalProjects / $limit);
    //         }

    //         // $lecturers = $this->projectModel->getAvailableLecturers(); // Lấy tất cả
    //         $faculties = $this->facultiesModel->getActiveFaculties();
    //         // $lecturers = $this->projectModel->getAvailableLecturers();
    //         // $faculties = $this->facultiesModel->getActiveFaculties();

    //         $this->render('projects/manage', [
    //             'projects' => $projects,
    //             'totalProjects' => $totalProjects,
    //             'totalPages' => $totalPages,
    //             'page' => $page,
    //             'keyword' => $keyword,
    //             // 'lecturers' => $lecturers,
    //             'faculties' => $faculties,
    //         ]);
    //     } catch (Exception $e) {
    //         $this->handleError($e, 'manage');
    //     }
    // }

    public function manage(): void
    {
        try {
            // 1. Lấy tham số phân trang
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 5;
            $offset = ($page - 1) * $limit;

            // 2. Lấy tham số tìm kiếm (Xử lý trim để xóa khoảng trắng thừa)
            $keyword = trim($_GET['keyword'] ?? '');
            $facultyId = trim($_GET['faculty_id'] ?? '');
            $status = trim($_GET['status'] ?? '');

            // 3. Gọi Model tìm kiếm
            $result = $this->projectModel->searchProjects($limit, $offset, $keyword, $facultyId, $status);

            // 4. Lấy danh sách Khoa để đổ vào Dropdown lọc
            $faculties = $this->facultiesModel->getActiveFaculties();

            // 5. Render View và truyền lại các biến để giữ trạng thái Selected
            $this->render('projects/manage', [
                'projects' => $result['projects'],
                'totalProjects' => $result['total'],
                'totalPages' => ceil($result['total'] / $limit),
                'page' => $page,
                'keyword' => $keyword,
                'selectedFaculty' => $facultyId, // Biến này để giữ lựa chọn Khoa
                'selectedStatus' => $status,     // Biến này để giữ lựa chọn Status
                'faculties' => $faculties,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'manage');
        }
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        try {
            $data = $_POST;
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $lecturer_id = (int)($data['lecturer_id'] ?? 0);
            $status = trim($data['status'] ?? 'ChoDuyet');
            $faculty_id = (int)($data['faculty_id'] ?? 0); // Sử dụng để validate lecturer thuộc faculty
            $max_students = (int)($data['max_students'] ?? 3);
            if ($max_students < 1 || $max_students > 3) $max_students = 3;

            if (empty($title) || $lecturer_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
                return;
            }

            // Kiểm tra lecturer tồn tại và thuộc faculty nếu có chọn
            $lecturer = $this->lecturerModel->getById($lecturer_id);
            if (!$lecturer) {
                $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không tồn tại.'], 400);
                return;
            }
            if ($faculty_id > 0 && $lecturer['faculty_id'] != $faculty_id) {
                $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không thuộc khoa đã chọn.'], 400);
                return;
            }
            $lecturer_name = $lecturer['full_name'];

            $this->pdo->beginTransaction();

            $projectData = [
                'title' => $title,
                'description' => $description,
                'lecturer_id' => $lecturer_id,
                'status' => $status,
                'max_students' => $max_students
            ];

            $projectId = $this->projectModel->createProject($projectData);

            if (!$projectId) {
                throw new Exception("Không thể tạo đồ án.");
            }

            // Tạo report types mặc định
            $this->createDefaultReportTypes($projectId);

            $this->pdo->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => "Thêm đồ án '{$title}' cho giảng viên '{$lecturer_name}' thành công!",
            ], 201);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("ProjectController::store PDO Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi Database: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("ProjectController::store Logic Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    // Thêm endpoint mới để lấy giảng viên theo khoa (cho AJAX)
    public function getLecturersByFaculty(int $facultyId): void
    {
        $lecturers = $this->projectModel->getAvailableLecturers($facultyId);
        $this->jsonResponse(['success' => true, 'lecturers' => $lecturers]);
    }


    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }


        try {
            $project = $this->projectModel->getByIdWithDetails($id);
            if (!$project) {
                $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đồ án'], 404);
                return;
            }

            $data = [];
            if (isset($_POST['title']) && !empty($_POST['title'])) $data['title'] = $_POST['title'];
            if (isset($_POST['description'])) $data['description'] = $_POST['description'];
            if (isset($_POST['lecturer_id']) && !empty($_POST['lecturer_id'])) $data['lecturer_id'] = (int)$_POST['lecturer_id'];
            if (isset($_POST['status']) && !empty($_POST['status'])) $data['status'] = $_POST['status'];



            // --- SỬA LOGIC KIỂM TRA SỐ LƯỢNG SINH VIÊN ---
            if (isset($_POST['max_students'])) {
                $newMax = (int)$_POST['max_students'];

                // Validate cơ bản (1-3)
                if ($newMax < 1 || $newMax > 3) $newMax = 3;

                // Đếm số lượng sinh viên thực tế đang có trong nhóm
                $stmtCount = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM group_members gm 
                JOIN groups g ON gm.group_id = g.group_id 
                WHERE g.project_id = ?
            ");
                $stmtCount->execute([$id]);
                $currentCount = (int)$stmtCount->fetchColumn();

                // KIỂM TRA: Nếu số lượng mới nhỏ hơn số sinh viên hiện có -> BÁO LỖI
                if ($newMax < $currentCount) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => "Không thể giảm xuống $newMax sinh viên vì hiện tại đang có $currentCount sinh viên đã đăng ký."
                    ], 400);
                    return; // Dừng thực hiện ngay lập tức
                }

                $data['max_students'] = $newMax;
            }

            // Sửa: Nếu có lecturer_id mới, từ ID lấy tên và kiểm tra tồn tại; nếu không, dùng tên cũ từ project
            $lecturer_name = $project['lecturer_name'] ?? 'Chưa phân công'; // Tên cũ
            if (isset($data['lecturer_id'])) {
                $lecturer_id = $data['lecturer_id'];
                if ($lecturer_id <= 0) {
                    $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không hợp lệ.'], 400);
                    return;
                }
                $lecturer = $this->lecturerModel->getById($lecturer_id);
                if (!$lecturer) {
                    $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không tồn tại.'], 400);
                    return;
                }
                $lecturer_name = $lecturer['full_name'];
            }

            if (!empty($data)) {
                $updated = $this->projectModel->updateProject($id, $data);
                if ($updated) {
                    // Sửa: Sử dụng tên giảng viên (mới hoặc cũ) trong message
                    $this->jsonResponse(['success' => true, 'message' => "Cập nhật đồ án thành công cho giảng viên '{$lecturer_name}'"]);
                    return;
                }
            } else {
                $this->jsonResponse(['success' => true, 'message' => 'Không có thay đổi']);
                return;
            }

            $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại']);
        } catch (Exception $e) {
            $this->handleError($e, 'update');
        }
    }

    public function destroy(int $id): void
    {
        try {
            $success = $this->projectModel->deleteProject($id);
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Xóa đồ án thành công']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Xóa thất bại'], 500);
            }
        } catch (Exception $e) {
            $this->handleError($e, 'destroy');
        }
    }

    public function export(): void
    {
        try {
            $projects = $this->projectModel->getAllWithDetails();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh sách Đồ án');

            $headers = ['ID', 'Tiêu đề', 'Mô tả', 'Giảng viên', 'Khoa', 'Trạng thái', 'Ngày tạo'];
            $sheet->fromArray($headers, null, 'A1');

            $row = 2;
            foreach ($projects as $project) {
                $data = [
                    $project['project_id'],
                    $project['title'],
                    $project['description'],
                    $project['lecturer_name'] ?? 'Chưa phân công',
                    $project['faculty_name'] ?? '',
                    $project['status'],
                    date('d/m/Y H:i', strtotime($project['created_at']))
                ];
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="danh_sach_do_an.xlsx"');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'export');
        }
    }

    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 400);
            return;
        }

        try {
            $file = $_FILES['excelFile']['tmp_name'];
            $updateExisting = !empty($_POST['update_existing']);

            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            array_shift($rows); // Bỏ header

            $this->pdo->beginTransaction();
            $countSuccess = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $title = trim($row['A'] ?? '');
                $description = trim($row['B'] ?? '');
                $lecturer_name = trim($row['C'] ?? '');
                $status = trim($row['D'] ?? 'ChoDuyet');
                //$max_students = (int)($row['E'] ?? 1) ?: 1;

                if (empty($title)) {
                    $errors[] = "Dòng " . ($index + 1) . ": Thiếu tiêu đề";
                    continue;
                }
                if (empty($lecturer_name)) {
                    $errors[] = "Dòng " . ($index + 1) . ": Thiếu tên giảng viên";
                    continue;
                }

                // Tìm lecturer bằng query trực tiếp (không cần method findByName)
                $stmt = $this->pdo->prepare("
                SELECT l.lecturer_id 
                FROM lecturers l 
                JOIN users u ON l.user_id = u.user_id 
                WHERE u.full_name = :full_name 
                LIMIT 1
            ");
                $stmt->execute([':full_name' => $lecturer_name]);
                $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$lecturer) {
                    $errors[] = "Dòng " . ($index + 1) . ": Không tìm thấy giảng viên '$lecturer_name'";
                    continue;
                }
                $lecturer_id = $lecturer['lecturer_id'];

                // Kiểm tra đồ án đã tồn tại chưa
                $existing = $this->projectModel->findByTitle($title);

                if ($existing) {
                    if (!$updateExisting) {
                        $errors[] = "Dòng " . ($index + 1) . ": Đồ án '$title' đã tồn tại (bỏ qua)";
                        continue;
                    }
                    // Cập nhật
                    $this->projectModel->updateProject($existing['project_id'], [
                        'description' => $description,
                        'lecturer_id' => $lecturer_id,
                        'status' => $status,
                        // 'max_students' => $max_students
                    ]);
                    $countSuccess++;
                    continue;
                }

                // Tạo mới
                $projectId = $this->projectModel->createProject([
                    'title' => $title,
                    'description' => $description,
                    'lecturer_id' => $lecturer_id,
                    'status' => $status,
                    //'max_students' => $max_students
                ]);

                if ($projectId) {
                    $this->createDefaultReportTypes($projectId);
                    $countSuccess++;
                } else {
                    $errors[] = "Dòng " . ($index + 1) . ": Tạo đồ án thất bại";
                }
            }

            $this->pdo->commit();

            $message = "Nhập thành công $countSuccess đồ án.";
            if (!empty($errors)) {
                $message .= " Một số lỗi: " . implode('; ', $errors);
            }

            $this->jsonResponse(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['Tiêu đề', 'Mô tả', 'Giảng viên (Họ tên)', 'Trạng thái'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['Đồ án mẫu', 'Mô tả chi tiết', 'Nguyễn Văn A', 'ChoDuyet'], null, 'A2');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="mau_nhap_do_an.xlsx"');
        $writer->save('php://output');
        exit;
    }

    private function createDefaultReportTypes(int $projectId): void
    {
        $defaultTypes = [
            [
                'type_name' => 'DeCuong',
                'description' => 'Nộp đề cương đồ án',
                'deadline' => date('Y-m-d', strtotime('+1 month')),
                'max_score' => 10.00
            ],
            [
                'type_name' => 'TienDo1',
                'description' => 'Báo cáo tiến độ 1',
                'deadline' => date('Y-m-d', strtotime('+2 months')),
                'max_score' => 10.00
            ],
            [
                'type_name' => 'TienDo2',
                'description' => 'Báo cáo tiến độ 2',
                'deadline' => date('Y-m-d', strtotime('+4 months')),
                'max_score' => 10.00
            ],
            [
                'type_name' => 'BaoCaoCuoi',
                'description' => 'Báo cáo hoàn chỉnh',
                'deadline' => date('Y-m-d', strtotime('+6 months')),
                'max_score' => 70.00
            ]
        ];

        foreach ($defaultTypes as $type) {
            $type['project_id'] = $projectId;
            $type['created_at'] = date('Y-m-d H:i:s');

            $columns = implode(', ', array_keys($type));
            $placeholders = ':' . implode(', :', array_keys($type));

            $stmt = $this->pdo->prepare("INSERT INTO report_types ({$columns}) VALUES ({$placeholders})");
            $stmt->execute($type);
        }
    }
    // Thêm vào ProjectController
    private function getStatusBadgeClass(string $status): string
    {
        $classes = [
            'ChoDuyet' => 'bg-warning text-dark',
            'DaDuyet' => 'bg-info text-dark',
            'DangThucHien' => 'bg-primary',
            'DaNopBaoCao' => 'bg-secondary',
            'DaBaoVe' => 'bg-success',
            'HoanThanh' => 'bg-success',
            'Huy' => 'bg-danger'
        ];
        return $classes[$status] ?? 'bg-secondary';
    }

    private function getStatusText(string $status): string
    {
        $texts = [
            'ChoDuyet' => 'Chờ duyệt',
            'DaDuyet' => 'Đã duyệt',
            'DangThucHien' => 'Đang thực hiện',
            'DaNopBaoCao' => 'Đã nộp báo cáo',
            'DaBaoVe' => 'Đã bảo vệ',
            'HoanThanh' => 'Hoàn thành',
            'Huy' => 'Hủy'
        ];
        return $texts[$status] ?? $status;
    }

    public function approve(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 5;
            $offset = ($page - 1) * $limit;

            // Gọi hàm từ Model để lấy danh sách đồ án CHỜ DUYỆT
            $result = $this->projectModel->getPendingProjectsWithPagination($limit, $offset, $keyword);
            $projects = $result['projects'];
            $totalProjects = $result['total'];
            $totalPages = ceil($totalProjects / $limit);

            // Render view approve.php
            $this->render('projects/approve', [
                'projects' => $projects,
                'totalProjects' => $totalProjects,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'approve');
        }
    }
    public function changeProjectStatus(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $status = $_POST['status'] ?? 'DaDuyet';

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đồ án.'], 404);
            return;
        }

        // Thực hiện thay đổi trạng thái (sẽ gọi đến ProjectModel::changeStatus)
        $success = $this->projectModel->changeStatus($id, $status);

        // Giả sử hàm getStatusText có tồn tại, nếu không, dùng $status
        $statusText = $this->getStatusText($status) ?? $status;

        // Sửa phản hồi để chính xác hơn
        $message = $success
            ? "Đồ án '{$project['title']}' đã được chuyển sang trạng thái: **{$statusText}**"
            : 'Thay đổi trạng thái thất bại';

        $this->jsonResponse(['success' => $success, 'message' => $message]);
    }

    // ProjectController.php (Thay thế toàn bộ hàm changeProjectStatusBatch)

    public function changeProjectStatusBatch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $idsJson = $_POST['ids'] ?? '[]';
        $ids = json_decode($idsJson, true) ?? [];
        $status = $_POST['status'] ?? 'DaDuyet';

        if (empty($ids)) {
            $this->jsonResponse(['success' => false, 'message' => 'Không có đồ án nào được chọn.'], 400);
            return;
        }

        $successCount = 0;
        $total = count($ids);
        $statusText = $this->getStatusText($status) ?? $status;

        try {
            // Bắt đầu transaction
            $this->pdo->beginTransaction();
            foreach ($ids as $id) {
                // Gọi Model để thay đổi trạng thái
                if ($this->projectModel->changeStatus((int)$id, $status)) {
                    $successCount++;
                }
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("changeProjectStatusBatch PDO Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi Database khi cập nhật hàng loạt.'], 500);
            return;
        }

        // Tạo thông báo phản hồi chính xác sau khi thực thi
        $message = "Đã chuyển thành công **$successCount/$total** đồ án sang trạng thái **{$statusText}**.";
        if ($successCount < $total) {
            $message .= " Vui lòng kiểm tra lại các đồ án còn lại (có thể đã bị xóa hoặc ở trạng thái khác).";
        }

        $this->jsonResponse([
            'success' => $successCount > 0,
            'message' => $message
        ]);
    }

    // ProjectController.php (Thay thế toàn bộ hàm getProjectDetails)

    public function getProjectDetails(int $id): void
    {
        $project = $this->projectModel->getByIdWithDetails($id);

        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đồ án'], 404);
            return;
        }

        // Lấy danh sách thành viên
        $members = $this->projectModel->getProjectMembers($id);

        // Định nghĩa màu sắc cho badge (giống list_for_student)
        $statusColors = [
            'ChoDuyet'      => 'warning',
            'DaDuyet'       => 'info',
            'DangThucHien'  => 'primary',
            'DaNopBaoCao'   => 'secondary',
            'DaBaoVe'       => 'success',
            'HoanThanh'     => 'success',
            'Huy'           => 'danger'
        ];
        $badgeColor = $statusColors[$project['status']] ?? 'secondary';

        // Format ngày
        $created_at_formatted = date('d/m/Y', strtotime($project['created_at']));

        $this->jsonResponse([
            'success' => true,
            'project' => [
                'project_id'           => $project['project_id'],
                'title'                => $project['title'],
                'description'          => $project['description'] ?? '',
                'status'               => $project['status'],
                'badge_color'          => $badgeColor, // Màu sắc cho view
                'lecturer_name'        => $project['lecturer_name'] ?? 'Chưa phân công',
                'faculty_name'         => $project['faculty_name'] ?? '',
                'lecturer_id'          => $project['lecturer_id'] ?? null,
                'faculty_id'           => $project['faculty_id'] ?? null,
                'created_at_formatted' => $created_at_formatted,
                'max_students'         => $project['max_students'] ?? 3,
                'current_students'     => count($members) // Đếm số lượng thực tế
            ],
            'members' => $members // Trả về danh sách sinh viên
        ]);
    }

    public function edit(int $id): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Kiểm tra quyền (Giảng viên hoặc Admin)
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            $project = $this->projectModel->getByIdWithDetails($id);
            if (!$project) {
                echo "Đồ án không tồn tại.";
                return;
            }

            // Nếu là giảng viên, kiểm tra xem đồ án có thuộc về mình không
            if ($_SESSION['role'] === 'teacher') {
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

                if ($project['lecturer_id'] != $lecturer['lecturer_id']) {
                    echo "Bạn không có quyền chỉnh sửa đồ án này.";
                    return;
                }
            }
            // --- BỔ SUNG: Đếm số lượng sinh viên hiện tại của đồ án ---
            $stmtCount = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM group_members gm 
            JOIN groups g ON gm.group_id = g.group_id 
            WHERE g.project_id = ?
        ");
            $stmtCount->execute([$id]);
            $currentCount = $stmtCount->fetchColumn();

            $this->render('projects/edit', [
                'project' => $project,
                'currentCount' => $currentCount // Truyền biến này sang view
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'edit');
        }
    }



    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Chuyển hướng nếu là Admin
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header('Location: /quanlydoan/HomeAdmin/index');
            exit;
        }

        try {
            $page = max(1, (int)($_GET['page'] ?? 1)); // Đảm bảo page >= 1
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 9;
            $offset = ($page - 1) * $limit;
            $params = [];

            // 1. QUERY CHÍNH: Lấy danh sách đồ án
            $sql = "SELECT p.*, 
                           u.full_name as lecturer_name, 
                           f.faculty_name,
                           COALESCE(p.max_students, 3) as max_students,
                           (SELECT COUNT(*) 
                            FROM group_members gm 
                            JOIN groups g ON gm.group_id = g.group_id 
                            WHERE g.project_id = p.project_id) as current_students
                    FROM projects p
                    LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                    LEFT JOIN users u ON l.user_id = u.user_id
                    LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                    WHERE p.status IN ('DaDuyet', 'DangThucHien')";

            // Logic tìm kiếm: Tiêu đề OR Tên giảng viên OR Tên Khoa
            if (!empty($keyword)) {
                $sql .= " AND (p.title LIKE :keyword 
                               OR u.full_name LIKE :keyword 
                               OR f.faculty_name LIKE :keyword)";
                $params[':keyword'] = "%$keyword%";
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. QUERY ĐẾM: Tính tổng trang (Phải join giống hệt query chính để đếm đúng)
            $countSql = "SELECT COUNT(*) 
                         FROM projects p 
                         LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                         LEFT JOIN users u ON l.user_id = u.user_id
                         LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                         WHERE p.status IN ('DaDuyet', 'DangThucHien')";

            if (!empty($keyword)) {
                $countSql .= " AND (p.title LIKE :keyword 
                                    OR u.full_name LIKE :keyword 
                                    OR f.faculty_name LIKE :keyword)";
            }

            $stmtCount = $this->pdo->prepare($countSql);
            $stmtCount->execute($params);
            $totalProjects = $stmtCount->fetchColumn();
            $totalPages = ceil($totalProjects / $limit);

            // 3. CHECK ĐĂNG KÝ: Kiểm tra sinh viên đã đăng ký đồ án nào chưa
            $registeredProjectIds = [];
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                if ($user) {
                    $student = $this->studentModel->findByUserId($user['user_id']);
                    if ($student) {
                        $stmtCheck = $this->pdo->prepare("
                            SELECT g.project_id 
                            FROM group_members gm
                            JOIN groups g ON gm.group_id = g.group_id
                            WHERE gm.student_id = ?
                        ");
                        $stmtCheck->execute([$student['student_id']]);
                        $pid = $stmtCheck->fetchColumn();
                        if ($pid) $registeredProjectIds[] = $pid;
                    }
                }
            }

            // Render View
            $this->render('Projects/list_for_student', [
                'projects' => $projects,
                'totalProjects' => $totalProjects,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
                'registeredProjectIds' => $registeredProjectIds,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'index');
        }
    }


    /**
     * Action: register
     * Đăng ký đồ án (Có kiểm tra trùng lặp và hiển thị tên đồ án cũ)
     */
    public function register(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập quyền Sinh viên.']);
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);

        try {
            // 1. Lấy thông tin sinh viên
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $student = $user ? $this->studentModel->findByUserId($user['user_id']) : null;

            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin sinh viên.']);
                exit;
            }

            // 2. KIỂM TRA: Sinh viên đã tham gia nhóm nào chưa?
            // Nếu có, lấy luôn tên đồ án đó để thông báo cho rõ ràng.
            $sqlCheck = "SELECT p.title 
                         FROM group_members gm
                         JOIN groups g ON gm.group_id = g.group_id
                         JOIN projects p ON g.project_id = p.project_id
                         WHERE gm.student_id = :student_id
                         LIMIT 1";

            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':student_id' => $student['student_id']]);
            $existingProjectTitle = $stmtCheck->fetchColumn();

            if ($existingProjectTitle) {
                // NẾU ĐÃ CÓ ĐỒ ÁN => CHẶN ĐĂNG KÝ VÀ THÔNG BÁO
                echo json_encode([
                    'success' => false,
                    'message' => "Đăng ký thất bại! Bạn đã tham gia đồ án: \"{$existingProjectTitle}\".\nMỗi sinh viên chỉ được thực hiện 1 đồ án."
                ]);
                exit;
            }

            // 3. Kiểm tra Đồ án muốn đăng ký có tồn tại và mở không
            $stmtProj = $this->pdo->prepare("SELECT status, title, max_students FROM projects WHERE project_id = ?");
            $stmtProj->execute([$projectId]);
            $project = $stmtProj->fetch(PDO::FETCH_ASSOC);

            if (!$project || !in_array($project['status'], ['DaDuyet', 'DangThucHien'])) {
                echo json_encode(['success' => false, 'message' => 'Đồ án chưa mở đăng ký hoặc không tồn tại.']);
                exit;
            }

            // Bắt đầu Transaction xử lý đăng ký
            $this->pdo->beginTransaction();

            // 4. Logic ghép nhóm (Tìm nhóm cũ hoặc tạo nhóm mới)
            $stmtGetGroup = $this->pdo->prepare("SELECT group_id FROM groups WHERE project_id = ? LIMIT 1");
            $stmtGetGroup->execute([$projectId]);
            $groupId = $stmtGetGroup->fetchColumn();

            if ($groupId) {
                // --- Trường hợp: Đã có nhóm ---
                $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM group_members WHERE group_id = ?");
                $stmtCount->execute([$groupId]);
                $currentCount = $stmtCount->fetchColumn();

                // Kiểm tra số lượng tối đa
                $maxAllowed = $project['max_students'] ?? 3;
                if ($currentCount >= $maxAllowed) {
                    $this->pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => "Nhóm này đã đủ thành viên ({$maxAllowed}/{$maxAllowed}). Vui lòng chọn đồ án khác."]);
                    exit;
                }

                // Thêm vào nhóm
                $stmtJoin = $this->pdo->prepare("INSERT INTO group_members (group_id, student_id) VALUES (?, ?)");
                $stmtJoin->execute([$groupId, $student['student_id']]);
            } else {
                // --- Trường hợp: Chưa có nhóm -> Tạo mới ---
                $stmtCreateGroup = $this->pdo->prepare("INSERT INTO groups (project_id, leader_id) VALUES (?, ?)");
                $stmtCreateGroup->execute([$projectId, $student['student_id']]);
                $newGroupId = $this->pdo->lastInsertId();

                $stmtJoin = $this->pdo->prepare("INSERT INTO group_members (group_id, student_id) VALUES (?, ?)");
                $stmtJoin->execute([$newGroupId, $student['student_id']]);
            }

            $this->pdo->commit();
            echo json_encode(['success' => true, 'message' => "Đăng ký thành công đồ án: \"{$project['title']}\"!"]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();

            // Xử lý lỗi Trigger (nếu Database có trigger chặn)
            if (strpos($e->getMessage(), 'Nhóm đã đủ') !== false) {
                echo json_encode(['success' => false, 'message' => 'Nhóm đã đủ thành viên.']);
            } else {
                error_log("Register Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
            }
        }
    }

    /**
     * Hiển thị form tạo đồ án mới
     * Tự động lấy thông tin nếu là Giảng viên
     */
    public function createByLecturer(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Kiểm tra quyền
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            $currentLecturer = null;
            $faculties = []; // Chỉ cần thiết nếu là Admin

            // Nếu là Giảng viên: Lấy thông tin cố định
            if ($_SESSION['role'] === 'teacher') {
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                if ($user) {
                    $currentLecturer = $this->lecturerModel->getLecturerInfoByUserId($user['user_id']);
                }

                if (!$currentLecturer) {
                    echo "Lỗi: Tài khoản giảng viên chưa được cấu hình đầy đủ.";
                    return;
                }
            }
            // Nếu là Admin: Lấy danh sách để chọn
            else {
                $faculties = $this->facultiesModel->getActiveFaculties();
            }

            $this->render('projects/create', [
                'faculties' => $faculties,
                'isLecturer' => $_SESSION['role'] === 'teacher',
                'currentLecturer' => $currentLecturer // Truyền thông tin giảng viên vào view
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'create');
        }
    }

    /**
     * Xử lý lưu đồ án
     */
    public function storeByLecturer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $data = $_POST;
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $max_students = (int)($data['max_students'] ?? 3);

            // Validate số lượng sinh viên (1-3)
            if ($max_students < 1 || $max_students > 3) $max_students = 3;

            // --- XỬ LÝ QUYỀN VÀ THÔNG TIN GIẢNG VIÊN ---
            $lecturer_id = 0;
            $status = 'ChoDuyet'; // Mặc định là chờ duyệt

            if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
                // Nếu là Giảng viên: Bắt buộc lấy ID từ session (Tránh hack form)
                $user = $this->userModel->findByAccountId($_SESSION['account_id']);
                $lecturerInfo = $this->lecturerModel->getLecturerInfoByUserId($user['user_id']);

                if (!$lecturerInfo) {
                    $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy thông tin giảng viên.'], 403);
                    return;
                }
                $lecturer_id = $lecturerInfo['lecturer_id'];
                $lecturer_name = $lecturerInfo['full_name'];

                // Giảng viên tạo thì mặc định là 'ChoDuyet', trừ khi có logic khác
                $status = 'ChoDuyet';
            } else {
                // Nếu là Admin: Lấy từ form
                $lecturer_id = (int)($data['lecturer_id'] ?? 0);
                $status = trim($data['status'] ?? 'ChoDuyet');
                $lecturer = $this->lecturerModel->getById($lecturer_id);
                $lecturer_name = $lecturer ? $lecturer['full_name'] : '';
            }

            // Validate chung
            if (empty($title) || $lecturer_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập tiêu đề và thông tin giảng viên.'], 400);
                return;
            }

            $this->pdo->beginTransaction();

            $projectData = [
                'title' => $title,
                'description' => $description,
                'lecturer_id' => $lecturer_id,
                'status' => $status,
                'max_students' => $max_students
            ];

            $projectId = $this->projectModel->createProject($projectData);

            if (!$projectId) {
                throw new Exception("Không thể tạo đồ án.");
            }

            // Tạo các loại báo cáo mặc định (Đề cương, Tiến độ...)
            $this->createDefaultReportTypes($projectId);



            // --- ĐOẠN CODE THÊM MỚI: GỬI THÔNG BÁO CHO ADMIN ---
            // 1. Tìm danh sách Admin
            $stmtAdmin = $this->pdo->prepare("
                SELECT u.user_id 
                FROM users u 
                JOIN accounts a ON u.account_id = a.account_id 
                WHERE a.role = 'admin' AND a.status = 'active'
            ");
            $stmtAdmin->execute();
            $admins = $stmtAdmin->fetchAll(\PDO::FETCH_ASSOC);

            // 2. Nội dung thông báo
            $notifTitle = "Yêu cầu duyệt đồ án mới";
            $notifMessage = "Giảng viên " . htmlspecialchars($lecturer_name) . " vừa tạo đồ án: " . htmlspecialchars($title) . ". Vui lòng kiểm tra và phê duyệt.";

            // 3. Khởi tạo model thông báo và gửi
            $notificationModel = new \App\Models\NotificationModel($this->pdo);
            foreach ($admins as $admin) {
                $notificationModel->createNotification($admin['user_id'], $notifTitle, $notifMessage);
            }

            $this->pdo->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => "Đã tạo đồ án thành công! Đã tạo đồ án thành công và gửi yêu cầu duyệt tới Admin!
                (Trạng thái: " . ($status == 'ChoDuyet' ? 'Chờ duyệt' : 'Đã duyệt') . ")",
            ], 201);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("ProjectController::store error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // app/Controllers/ProjectController.php

    /**
     * [ĐÃ SỬA] Xuất file Excel chỉ dành cho Giảng viên
     * - Fix lỗi không hiện tên
     * - Đổi font sang Times New Roman
     */
    public function exportByLecturer(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra quyền
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        try {
            // 2. Lấy thông tin Giảng viên
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);

            // Tìm thông tin giảng viên (để lấy ID dùng cho query đồ án)
            $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

            if (!$lecturer) {
                die("Không tìm thấy thông tin giảng viên.");
            }

            // 3. Lấy danh sách đồ án (Lấy hết)
            $result = $this->projectModel->getProjectsByLecturerIdWithPagination($lecturer['lecturer_id'], 10000, 0, '');
            $projects = $result['projects'];

            // 4. Khởi tạo Excel
            $spreadsheet = new Spreadsheet();

            // --- CÀI ĐẶT FONT CHỮ TIMES NEW ROMAN CHO TOÀN BỘ FILE ---
            $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
            // -----------------------------------------------------------

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Đồ án của tôi');

            // --- ĐỊNH DẠNG HEADER ---

            // Dòng 1: Tiêu đề lớn
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'DANH SÁCH ĐỒ ÁN HƯỚNG DẪN');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF0000FF']], // Chữ xanh
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Dòng 2: Thông tin giảng viên & Ngày xuất
            // SỬA LỖI: Lấy tên từ biến $user['full_name'] thay vì $lecturer
            $lecturerName = $user['full_name'] ?? 'Không xác định';

            $sheet->mergeCells('A2:G2');
            $sheet->setCellValue('A2', "Giảng viên: " . $lecturerName . " - Ngày xuất: " . date('d/m/Y H:i'));
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 12], // Times New Roman nghiêng
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Dòng 4: Tiêu đề cột
            $headers = ['ID', 'Tiêu đề đồ án', 'Mô tả/Yêu cầu', 'Số SV tối đa', 'Đã đăng ký', 'Trạng thái', 'Ngày tạo'];
            $sheet->fromArray($headers, null, 'A4');

            // Style cho dòng tiêu đề cột
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']], // Nền xanh dương
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(30); // Tăng chiều cao dòng tiêu đề chút cho thoáng

            // --- ĐỔ DỮ LIỆU ---
            $row = 5;
            foreach ($projects as $p) {
                $currentSV = $p['current_students'] ?? 0;
                $statusText = $this->getStatusText($p['status']);

                $data = [
                    $p['project_id'],
                    $p['title'],
                    $p['description'],
                    $p['max_students'],
                    $currentSV,
                    $statusText,
                    date('d/m/Y', strtotime($p['created_at']))
                ];
                $sheet->fromArray($data, null, 'A' . $row);

                // Căn chỉnh dòng dữ liệu này
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // ID giữa
                $sheet->getStyle('D' . $row . ':G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Các số liệu giữa
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true); // Tiêu đề tự xuống dòng
                $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true); // Mô tả tự xuống dòng

                $row++;
            }

            // Kẻ bảng cho vùng dữ liệu
            $lastRow = $row - 1;
            if ($lastRow >= 5) {
                $sheet->getStyle('A5:G' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A5:G' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            // Auto width (Điều chỉnh độ rộng)
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(35);
            $sheet->getColumnDimension('C')->setWidth(50);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(15);

            // Output
            $filename = "DS_DoAn_" . date('Ymd_Hi') . ".xlsx";

            if (ob_get_length()) ob_end_clean();

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'exportByLecturer');
        }
    }

    /**
     * [SỬA LỖI] Hàm Import dành riêng cho Giảng viên
     * - Thêm try-catch chặt chẽ
     * - Xóa output buffer để tránh lỗi JSON
     */
    public function importByLecturer(): void
    {
        // 1. Tắt hiển thị lỗi HTML, nhưng bật log lỗi hệ thống
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);

        // 2. Thiết lập Header JSON ngay từ đầu
        header('Content-Type: application/json; charset=utf-8');

        // Bắt đầu bộ đệm để hứng bất kỳ output rác nào (nếu có)
        ob_start();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
                throw new Exception("Yêu cầu không hợp lệ hoặc không có file.");
            }

            if (session_status() === PHP_SESSION_NONE) session_start();

            // Kiểm tra upload file
            if ($_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải file lên server (Mã lỗi: " . $_FILES['excelFile']['error'] . ")");
            }

            // Kiểm tra quyền
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Chức năng chỉ dành cho Giảng viên.');
            }

            // Lấy ID giảng viên
            $user = $this->userModel->findByAccountId($_SESSION['account_id']);
            $currentLecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

            if (!$currentLecturer) {
                throw new Exception('Không tìm thấy thông tin giảng viên.');
            }
            $lecturerId = $currentLecturer['lecturer_id'];

            // Kiểm tra thư viện Excel
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new Exception('Thư viện PhpSpreadsheet chưa được cài đặt hoặc chưa load autoload.php.');
            }

            // Đọc file
            $file = $_FILES['excelFile']['tmp_name'];
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            array_shift($rows); // Bỏ header

            $this->pdo->beginTransaction();
            $countSuccess = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                $title = trim($row['A'] ?? '');
                $description = trim($row['B'] ?? '');
                $max_students = (int)($row['C'] ?? 3);
                $status = trim($row['D'] ?? 'ChoDuyet');

                if (empty($title)) continue; // Bỏ dòng trống

                // Logic tạo/cập nhật...
                $existing = $this->projectModel->findByTitle($title);
                $updateExisting = !empty($_POST['update_existing']);

                if ($existing) {
                    if ($existing['lecturer_id'] != $lecturerId) {
                        $errors[] = "Dòng $rowNumber: Đồ án '$title' đã tồn tại của GV khác.";
                        continue;
                    }
                    if (!$updateExisting) {
                        $errors[] = "Dòng $rowNumber: Đồ án '$title' đã tồn tại (Bỏ qua).";
                        continue;
                    }
                    // Cập nhật
                    $this->projectModel->updateProject($existing['project_id'], [
                        'description' => $description,
                        'status' => $status,
                        'max_students' => $max_students
                    ]);
                    $countSuccess++;
                } else {
                    // Tạo mới
                    $projectId = $this->projectModel->createProject([
                        'title' => $title,
                        'description' => $description,
                        'lecturer_id' => $lecturerId,
                        'status' => $status,
                        'max_students' => $max_students
                    ]);

                    if ($projectId) {
                        $this->createDefaultReportTypes($projectId);
                        $countSuccess++;
                    } else {
                        $errors[] = "Dòng $rowNumber: Lỗi lưu vào CSDL.";
                    }
                }
            }

            $this->pdo->commit();

            // Xóa sạch bộ đệm trước khi echo JSON
            ob_end_clean();

            $message = "Nhập thành công $countSuccess đồ án.";
            if (!empty($errors)) {
                $message .= "\nLỗi: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) $message .= "...";
            }

            echo json_encode(['success' => true, 'message' => $message]);
        } catch (\Throwable $e) { // SỬ DỤNG \Throwable ĐỂ BẮT CẢ FATAL ERROR
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Xóa sạch bộ đệm để không bị lẫn HTML lỗi vào JSON
            if (ob_get_length()) ob_end_clean();

            // Ghi log lỗi để dev kiểm tra
            error_log("Import Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit;
    }


    /**
     * Tải file mẫu Excel mới dành cho Giảng viên (Không có cột Tên GV)
     */
    public function downloadTemplateForLecturer(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header mới
        $headers = ['Tiêu đề đồ án (*)', 'Mô tả chi tiết', 'Số SV tối đa (1-3)', 'Trạng thái (ChoDuyet)'];
        $sheet->fromArray($headers, null, 'A1');

        // Dữ liệu mẫu
        $sampleData = [
            ['Website bán hàng', 'Yêu cầu: PHP, MVC...', 3, 'ChoDuyet'],
            ['Ứng dụng Mobile App', 'Yêu cầu: React Native...', 2, 'ChoDuyet']
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto width
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="mau_do_an_giang_vien.xlsx"');
        $writer->save('php://output');
        exit;
    }
}
