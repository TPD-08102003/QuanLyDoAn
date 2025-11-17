<?php
// controllers/ProjectController.php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PDO;
use Exception;
use PDOException;
use App\Models\ProjectModel;
use App\Models\LecturerModel;
use App\Models\FacultiesModel;

class ProjectController extends BaseController
{
    private ProjectModel $projectModel;
    private LecturerModel $lecturerModel;
    private FacultiesModel $facultiesModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->projectModel = new ProjectModel($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
    }

    public function manage(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 5;
            $offset = ($page - 1) * $limit;


            if (empty($keyword)) {
                $allProjects = $this->projectModel->getAllWithDetails();
                $totalProjects = count($allProjects);
                $projects = array_slice($allProjects, $offset, $limit);
                $totalPages = ceil($totalProjects / $limit);
            } else {
                // Giả sử ProjectModel có getProjectsWithPagination
                $result = $this->projectModel->getProjectsWithPagination($limit, $offset, $keyword);
                $projects = $result['projects'];
                $totalProjects = $result['total'];
                $totalPages = ceil($totalProjects / $limit);
            }

            // $lecturers = $this->projectModel->getAvailableLecturers(); // Lấy tất cả
            $faculties = $this->facultiesModel->getActiveFaculties();
            // $lecturers = $this->projectModel->getAvailableLecturers();
            // $faculties = $this->facultiesModel->getActiveFaculties();

            $this->render('projects/manage', [
                'projects' => $projects,
                'totalProjects' => $totalProjects,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
                // 'lecturers' => $lecturers,
                'faculties' => $faculties,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'manage');
        }
    }

    // public function store(): void
    // {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
    //         return;
    //     }

    //     try {
    //         $data = $_POST;
    //         $title = trim($data['title'] ?? '');
    //         $description = trim($data['description'] ?? '');
    //         $lecturer_id = (int)($data['lecturer_id'] ?? 0);
    //         $status = trim($data['status'] ?? 'ChoDuyet');
    //         $faculty_id = (int)($data['faculty_id'] ?? 0);
    //         if (empty($title) || $lecturer_id <= 0) {
    //             $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
    //             return;
    //         }

    //         // Kiểm tra trùng tiêu đề nếu cần
    //         // if ($this->projectModel->isTitleExists($title)) {
    //         //     $this->jsonResponse(['success' => false, 'message' => "Tiêu đề '{$title}' đã tồn tại."], 409);
    //         //     return;
    //         // }

    //         $this->pdo->beginTransaction();

    //         $projectData = [
    //             'title' => $title,
    //             'description' => $description,
    //             'lecturer_id' => $lecturer_id,
    //             'status' => $status,

    //         ];

    //         $projectId = $this->projectModel->createProject($projectData);

    //         if (!$projectId) {
    //             throw new Exception("Không thể tạo đồ án.");
    //         }

    //         // Tạo report types mặc định
    //         $this->createDefaultReportTypes($projectId);

    //         $this->pdo->commit();

    //         $this->jsonResponse([
    //             'success' => true,
    //             'message' => "Thêm đồ án '{$title}' thành công!",
    //         ], 201);
    //     } catch (PDOException $e) {
    //         $this->pdo->rollBack();
    //         error_log("ProjectController::store PDO Error: " . $e->getMessage());
    //         $this->jsonResponse([
    //             'success' => false,
    //             'message' => 'Lỗi Database: ' . $e->getMessage()
    //         ], 500);
    //     } catch (Exception $e) {
    //         $this->pdo->rollBack();
    //         error_log("ProjectController::store Logic Error: " . $e->getMessage());
    //         $this->jsonResponse([
    //             'success' => false,
    //             'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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
    // public function getProjectDetails(int $id): void
    // {
    //     $project = $this->projectModel->getByIdWithDetails($id);
    //     if ($project) {
    //         $this->jsonResponse([
    //             'success' => true,
    //             'project' => $project,
    //         ]);
    //     } else {
    //         $this->jsonResponse([
    //             'success' => false,
    //             'message' => 'Không tìm thấy đồ án.'
    //         ], 404);
    //     }
    // }



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

        // Format ngày tạo (giữ nguyên như cũ)
        $created_at_formatted = date('d/m/Y', strtotime($project['created_at']));

        $this->jsonResponse([
            'success' => true,
            'project' => [
                'project_id'           => $project['project_id'],               // THÊM: để biết ID khi edit
                'title'                => $project['title'],
                'description'          => $project['description'] ?? '',
                'status'               => $project['status'],
                'lecturer_name'        => $project['lecturer_name'] ?? 'Chưa phân công',
                'faculty_name'         => $project['faculty_name'] ?? '',
                'lecturer_id'          => $project['lecturer_id'] ?? null,      // THÊM: cần để chọn đúng GV
                'faculty_id'           => $project['faculty_id'] ?? null,      // THÊM: cần để chọn đúng Khoa
                'created_at_formatted' => $created_at_formatted,
                // Nếu sau này bạn thêm cột max_students vào bảng projects thì thêm dòng dưới
                // 'max_students'         => $project['max_students'] ?? 1,
            ]
        ]);
    }
}
