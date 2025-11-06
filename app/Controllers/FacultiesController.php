<?php

namespace App\Controllers;

use App\Models\FacultiesModel;
use PDO;
use Exception;

class FacultiesController extends BaseController
{
    private FacultiesModel $facultyModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
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

        $this->render('faculties/manage', [  // ĐÚNG: views/faculties/manage.php
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

    // /**
    //  * Xử lý thêm khoa mới
    //  */
    // public function store(): void
    // {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         $this->redirect('/faculty');
    //     }

    //     $name = trim($_POST['faculty_name'] ?? '');
    //     $description = trim($_POST['description'] ?? '');

    //     if (empty($name)) {
    //         $_SESSION['message'] = 'Tên khoa không được để trống!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect('/faculty/create');
    //     }

    //     if ($this->facultyModel->isNameExists($name)) {
    //         $_SESSION['message'] = 'Tên khoa "' . htmlspecialchars($name) . '" đã tồn tại!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect('/faculty/create');
    //     }

    //     try {
    //         $data = [
    //             'faculty_name' => $name,
    //             'description' => $description
    //         ];

    //         if ($this->facultyModel->create($data)) {
    //             $_SESSION['message'] = 'Thêm khoa thành công!';
    //             $_SESSION['message_type'] = 'success';
    //         } else {
    //             $_SESSION['message'] = 'Thêm khoa thất bại!';
    //             $_SESSION['message_type'] = 'danger';
    //         }
    //     } catch (Exception $e) {
    //         $_SESSION['message'] = 'Lỗi: ' . $e->getMessage();
    //         $_SESSION['message_type'] = 'danger';
    //     }

    //     $this->redirect('/faculty');
    // }

    // /**
    //  * Hiển thị form chỉnh sửa khoa
    //  */
    // public function edit(int $id): void
    // {
    //     $faculty = $this->facultyModel->findById($id);
    //     if (!$faculty || $faculty['deleted_at'] !== null) {
    //         $_SESSION['message'] = 'Khoa không tồn tại hoặc đã bị xóa!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect('/faculty');
    //     }

    //     $this->render('faculties/edit', [
    //         'title' => 'Sửa Khoa',
    //         'faculty' => $faculty
    //     ]);
    // }

    // /**
    //  * Xử lý cập nhật khoa
    //  */
    // public function update(int $id): void
    // {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         $this->redirect('/faculty');
    //     }

    //     $faculty = $this->facultyModel->findById($id);
    //     if (!$faculty || $faculty['deleted_at'] !== null) {
    //         $_SESSION['message'] = 'Khoa không tồn tại!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect('/faculty');
    //     }

    //     $name = trim($_POST['faculty_name'] ?? '');
    //     $description = trim($_POST['description'] ?? '');

    //     if (empty($name)) {
    //         $_SESSION['message'] = 'Tên khoa không được để trống!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect("/faculty/edit/$id");
    //     }

    //     if ($name !== $faculty['faculty_name'] && $this->facultyModel->isNameExists($name, $id)) {
    //         $_SESSION['message'] = 'Tên khoa "' . htmlspecialchars($name) . '" đã tồn tại!';
    //         $_SESSION['message_type'] = 'danger';
    //         $this->redirect("/faculty/edit/$id");
    //     }

    //     $data = [
    //         'faculty_name' => $name,
    //         'description' => $description
    //     ];

    //     if ($this->facultyModel->update($id, $data)) {
    //         $_SESSION['message'] = 'Cập nhật khoa thành công!';
    //         $_SESSION['message_type'] = 'success';
    //     } else {
    //         $_SESSION['message'] = 'Không có thay đổi hoặc cập nhật thất bại!';
    //         $_SESSION['message_type'] = 'warning';
    //     }

    //     $this->redirect('/faculty');
    // }

    /**
     * Xử lý xóa mềm khoa (AJAX)
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        $faculty = $this->facultyModel->findById($id);
        if (!$faculty || $faculty['deleted_at'] !== null) {
            $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại hoặc đã bị xóa.']);
        }

        // Kiểm tra xem khoa có đang được sử dụng không (có lớp học, giảng viên, sinh viên)
        $checkSql = "SELECT 
                        (SELECT COUNT(*) FROM classes WHERE faculty_id = :id) AS class_count,
                        (SELECT COUNT(*) FROM lecturers WHERE faculty_id = :id) AS lecturer_count,
                        (SELECT COUNT(*) FROM students WHERE faculty_id = :id) AS student_count";
        $stmt = $this->pdo->prepare($checkSql);
        $stmt->execute([':id' => $id]);
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

            // Sử dụng model để lấy dữ liệu
            $faculty = $this->facultyModel->findById($id);

            error_log("Faculty found: " . ($faculty ? 'YES' : 'NO'));

            if (!$faculty) {
                error_log("Faculty not found with ID: {$id}");
                $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại'], 404);
                return;
            }

            error_log("Faculty data: " . print_r($faculty, true));

            // Kiểm tra nếu khoa đã bị xóa mềm
            if ($faculty['deleted_at'] !== null) {
                error_log("Faculty is soft deleted: {$id}");
                $this->jsonResponse(['success' => false, 'message' => 'Khoa đã bị xóa'], 404);
                return;
            }

            // Đảm bảo dữ liệu trả về đúng định dạng - chỉ trả về các cột có trong bảng
            $response = [
                'success' => true,
                'faculty' => [
                    'faculty_id' => (int)$faculty['faculty_id'],
                    'faculty_name' => $faculty['faculty_name'],
                    'description' => $faculty['description'] ?? '',
                    'created_at' => $faculty['created_at']
                    // Bỏ updated_at vì bảng không có cột này
                ]
            ];

            error_log("Sending response: " . json_encode($response));
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
    // public function get(int $id): void
    // {
    //     $faculty = $this->facultyModel->findById($id);

    //     if (!$faculty || $faculty['deleted_at'] !== null) {
    //         $this->jsonResponse(['success' => false, 'message' => 'Khoa không tồn tại'], 404);
    //         return;
    //     }

    //     $this->jsonResponse([
    //         'success' => true,
    //         'faculty_id' => $faculty['faculty_id'],
    //         'faculty_name' => $faculty['faculty_name'],
    //         'description' => $faculty['description'],
    //         'created_at' => $faculty['created_at'],
    //         'updated_at' => $faculty['updated_at']
    //     ]);
    // }

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

        $data = [
            'faculty_name' => $name,
            'description' => $description
        ];

        if ($this->facultyModel->update($id, $data)) {
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật khoa thành công!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Không có thay đổi hoặc cập nhật thất bại!']);
        }
    }
}
