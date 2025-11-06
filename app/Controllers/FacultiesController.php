<?php

namespace App\Controllers;

use App\Models\FacultiesModel;
use PDO;
use Exception;

class FacultiesController extends BaseController
{
    private FacultiesModel $facultyModel;
    protected PDO $pdo;

    // Trong App/Controllers/FacultiesController
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
        $this->facultyModel = new FacultiesModel($pdo);
    }

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $keyword = trim($_GET['keyword'] ?? '');
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $result = $this->facultyModel->getFacultiesWithPagination($limit, $offset, $keyword);
        $faculties = $result['faculties'];
        $total = $result['total'];
        $totalPages = ceil($total / $limit);

        $this->render('faculties/manage', [
            'title' => 'Quản lý Khoa',
            'faculties' => $faculties,
            'keyword' => $keyword,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'offset' => $offset
        ]);
    }

    /**
     * ALIAS: Cho URL /Faculties/manage
     */
    public function manage(): void
    {
        $this->index(); // Gọi lại index
    }

    /**
     * Hiển thị form thêm khoa mới
     */
    public function create(): void
    {
        $this->render('faculties/create', ['title' => 'Thêm Khoa Mới']);
    }



    /**
     * Xử lý xóa mềm khoa (AJAX)
     */
    public function delete(int $id): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Invalid delete method: " . $_SERVER['REQUEST_METHOD']);
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }


        $faculty = $this->facultyModel->findById($id);
        if (!$faculty || $faculty['deleted_at'] !== null) {
            $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại hoặc đã bị xóa.']);
            return;
        }


        $checkSql = "SELECT 
                        (SELECT COUNT(*) FROM classes WHERE faculty_id = :class_id) AS class_count,
                        (SELECT COUNT(*) FROM lecturers WHERE faculty_id = :lecturer_id) AS lecturer_count,
                        (SELECT COUNT(*) FROM students WHERE faculty_id = :student_id) AS student_count";
        $stmt = $this->pdo->prepare($checkSql);
        // Gán cùng giá trị $id cho TẤT CẢ các tham số duy nhất
        $stmt->execute([
            ':class_id' => $id,
            ':lecturer_id' => $id,
            ':student_id' => $id
        ]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($counts['class_count'] > 0 || $counts['lecturer_count'] > 0 || $counts['student_count'] > 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể xóa khoa vì đang có dữ liệu liên quan (lớp, giảng viên, sinh viên).'
            ]);
        }

        if ($this->facultyModel->softDelete($id)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đã xóa khoa thành công!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Xóa khoa thất bại.']);
        }
    }

    /**
     * Lấy thông tin khoa theo ID (cho AJAX)
     */
    /**
     * Lấy thông tin khoa theo ID (cho AJAX)
     */
    public function get(int $id): void
    {
        try {
            error_log("=== GET FACULTY ID: {$id} ===");

            $faculty = $this->facultyModel->findById($id);
            error_log("Faculty found: " . ($faculty ? 'YES' : 'NO'));

            if (!$faculty) {
                $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại'], 404);
                return;
            }

            $response = [
                'success' => true,
                'faculty' => [
                    'faculty_id' => (int)$faculty['faculty_id'],
                    'faculty_name' => $faculty['faculty_name'],
                    'description' => $faculty['description'] ?? '',
                    'created_at' => $faculty['created_at'] ?? '',
                    'updated_at' => $faculty['updated_at'] ?? ''
                ]
            ];

            $this->jsonResponse($response);
        } catch (Exception $e) {
            error_log("Exception in get: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Debug method to test database connection
     */
    public function debug(): void
    {
        try {
            $id = 1;
            echo "<h3>Testing Faculty Model</h3>";

            // Test 1: Direct SQL với đúng tên cột
            echo "<h4>Test 1: Direct SQL với đúng cột</h4>";
            $sql = "SELECT faculty_id, faculty_name, description, deleted_at, created_at 
                FROM faculties 
                WHERE faculty_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "Direct SQL Result: <pre>" . print_r($result, true) . "</pre>";

            // Test 2: Using Model
            echo "<h4>Test 2: Using Model</h4>";
            $faculty = $this->facultyModel->findById($id);
            echo "Model Result: <pre>" . print_r($faculty, true) . "</pre>";

            // Test 3: Check all faculties
            echo "<h4>Test 3: All Faculties</h4>";
            $sql = "SELECT faculty_id, faculty_name, description, deleted_at, created_at FROM faculties";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $allFaculties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "All Faculties: <pre>" . print_r($allFaculties, true) . "</pre>";

            // Test 4: Check table structure
            echo "<h4>Test 4: Table Structure</h4>";
            $sql = "DESCRIBE faculties";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Table Structure: <pre>" . print_r($structure, true) . "</pre>";
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage();
            echo "<br>Stack trace: " . $e->getTraceAsString();
        }
        exit;
    }

    /**
     * Xử lý thêm khoa mới (AJAX)
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $name = trim($_POST['faculty_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tên khoa không được để trống!']);
            return;
        }

        if ($this->facultyModel->isNameExists($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tên khoa "' . htmlspecialchars($name) . '" đã tồn tại!']);
            return;
        }

        try {
            $data = [
                'faculty_name' => $name,
                'description' => $description
            ];

            if ($this->facultyModel->create($data)) {
                $this->jsonResponse(['success' => true, 'message' => 'Thêm khoa thành công!']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Thêm khoa thất bại!']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }



    /**
     * Xử lý cập nhật khoa (AJAX)
     */
    public function update(int $id): void
    {
        // BỌC TOÀN BỘ LOGIC TRONG TRY...CATCH
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
                return;
            }

            $faculty = $this->facultyModel->findById($id);
            if (!$faculty || $faculty['deleted_at'] !== null) {
                $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại!']);
                return;
            }

            $name = trim($_POST['faculty_name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $this->jsonResponse(['success' => false, 'message' => 'Tên khoa không được để trống!']);
                return;
            }

            if ($name !== $faculty['faculty_name'] && $this->facultyModel->isNameExists($name, $id)) {
                $this->jsonResponse(['success' => false, 'message' => 'Tên khoa "' . htmlspecialchars($name) . '" đã tồn tại!']);
                return;
            }

            // Chỉ cập nhật nếu có thay đổi
            $dataToUpdate = [];
            if ($name !== $faculty['faculty_name']) {
                $dataToUpdate['faculty_name'] = $name;
            }
            if ($description !== ($faculty['description'] ?? null)) {
                $dataToUpdate['description'] = $description;
            }

            if (empty($dataToUpdate)) {
                $this->jsonResponse(['success' => true, 'message' => 'Không có gì thay đổi.']);
                return;
            }


            if ($this->facultyModel->update($id, $dataToUpdate)) {
                $this->jsonResponse(['success' => true, 'message' => 'Cập nhật khoa thành công!']);
            } else {
                // Lỗi này là lỗi mà Model đã ghi log (ví dụ: Lỗi SQL)
                $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại! Vui lòng kiểm tra server log (Lỗi SQL).']);
            }
        } catch (Exception $e) {
            // Bắt các lỗi không mong muốn (ví dụ: lỗi logic PHP)
            error_log("!!! Exception in FacultiesController::update: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server nghiêm trọng: ' . $e->getMessage()]);
        }
    }
}
