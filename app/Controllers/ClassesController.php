<?php
// app/Controllers/ClassesController.php

namespace App\Controllers;

use App\Models\ClassesModel;
use App\Models\FacultiesModel;
use App\Models\StudentModel;
use PDO;
use Exception;
use Throwable;

class ClassesController extends BaseController
{
    private ClassesModel $model;
    private FacultiesModel $facultiesModel;
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new ClassesModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function manage(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $keyword = $_GET['keyword'] ?? '';

        $classes = $this->model->getClassesWithPagination($limit, $offset, $keyword);
        $total = $this->model->getTotalClasses($keyword);
        $totalPages = ceil($total / $limit);

        // Lấy danh sách khoa cho popup
        $faculties = $this->facultiesModel->findAll();

        $this->render('classes/manage', [
            'classes' => $classes,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'keyword' => $keyword,
            'limit' => $limit,
            'faculties' => $faculties
        ]);
    }

    /**
     * Display a listing of classes with pagination and search.
     */
    public function index(): void
    {
        $this->manage();
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(): void
    {
        $faculties = $this->facultiesModel->findAll();

        $this->render('classes/create', [
            'faculties' => $faculties
        ]);
    }

    /**
     * Lấy thông tin lớp học cho popup (AJAX/JSON)
     * @param int $classId
     */
    // app/Controllers/ClassesController.php
    // ...

    public function getClassInfo($classId)
    {
        header('Content-Type: application/json');

        try {
            $classInfo = $this->model->findWithFaculty((int)$classId);
            if (!$classInfo) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Class not found']);
                return;
            }

            // Đảm bảo StudentModel có hàm getStudentsByClass và nó trả về array
            $students = [];
            if (isset($this->studentModel) && method_exists($this->studentModel, 'getStudentsByClass')) {
                $students = $this->studentModel->getStudentsByClass((int)$classId);
            }

            $classInfo['students'] = $students;

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $classInfo]);
        } catch (\Throwable $e) {
            error_log("Fatal Error in getClassInfo: " . $e->getMessage()); // Ghi log lỗi
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal Server Error', 'debug' => $e->getMessage()]);
        }
    }
    /**
     * Xử lý AJAX request để thêm lớp học
     */
    public function store()
    {
        try {
            // Kiểm tra method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Method không hợp lệ',
                    'data' => []
                ], 405);
            }

            // Lấy dữ liệu từ form
            $class_name = trim($_POST['class_name'] ?? '');
            $faculty_id = $_POST['faculty_id'] ?? '';
            $description = trim($_POST['description'] ?? '');

            // Validate dữ liệu
            $errors = [];

            if (empty($class_name)) {
                $errors['class_name'] = ['Tên lớp học là bắt buộc'];
            } elseif (strlen($class_name) > 255) {
                $errors['class_name'] = ['Tên lớp học không được vượt quá 255 ký tự'];
            }

            if (empty($faculty_id)) {
                $errors['faculty_id'] = ['Vui lòng chọn khoa'];
            }

            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Vui lòng kiểm tra lại thông tin',
                    'data' => $errors
                ], 422); // HTTP 422 Unprocessable Entity
            }

            // Kiểm tra trùng tên lớp
            $existingClass = $this->model->getClassByName($class_name);
            if ($existingClass) {
                $errors['class_name'] = ['Tên lớp học đã tồn tại'];
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Tên lớp học đã tồn tại',
                    'data' => $errors
                ], 422);
            }

            // Dữ liệu hợp lệ để tạo lớp
            $classData = [
                'class_name' => $class_name,
                'faculty_id' => $faculty_id,
                'description' => $description,

            ];

            // Thực hiện tạo lớp học
            $result = $this->model->create($classData);

            if ($result !== false) {
                // $result là ID (int) nếu thành công, hoặc true/false tùy thuộc vào ClassesModel::create
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Thêm lớp học thành công',
                    'data' => []
                ], 200);
            } else {
                // Lỗi DB (có thể do ClassesModel::create trả về false)
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Thêm lớp học thất bại',
                    'data' => []
                ], 500);
            }
        } catch (Exception $e) {
            error_log("ClassesController::store error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Xử lý AJAX request để cập nhật lớp học
     */
    public function update($classId)
    {
        try {
            // Kiểm tra method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Method không hợp lệ',
                    'data' => []
                ], 405);
            }

            // Kiểm tra lớp học tồn tại
            $existingClass = $this->model->getClassById($classId);

            if (!$existingClass) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Lớp học không tồn tại',
                    'data' => []
                ], 404);
            }

            // Lấy dữ liệu từ form
            $class_name = trim($_POST['class_name'] ?? '');
            $faculty_id = $_POST['faculty_id'] ?? '';
            $description = trim($_POST['description'] ?? '');

            // Validate dữ liệu
            $errors = [];

            if (empty($class_name)) {
                $errors['class_name'] = ['Tên lớp học là bắt buộc'];
            } elseif (strlen($class_name) > 255) {
                $errors['class_name'] = ['Tên lớp học không được vượt quá 255 ký tự'];
            }

            if (empty($faculty_id)) {
                $errors['faculty_id'] = ['Vui lòng chọn khoa'];
            }

            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Vui lòng kiểm tra lại thông tin',
                    'data' => $errors
                ], 422);
            }

            // Kiểm tra trùng tên lớp (trừ lớp hiện tại)
            $existingClassByName = $this->model->getClassByName($class_name);
            if ($existingClassByName && $existingClassByName['class_id'] != $classId) {
                $errors['class_name'] = ['Tên lớp học đã tồn tại'];
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Tên lớp học đã tồn tại',
                    'data' => $errors
                ], 422);
            }

            // Cập nhật lớp học
            $classData = [
                'class_name' => $class_name,
                'faculty_id' => $faculty_id,
                'description' => $description
            ];

            $result = $this->model->update($classId, $classData);

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cập nhật lớp học thành công',
                    'data' => []
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cập nhật lớp học thất bại',
                    'data' => []
                ], 500);
            }
        } catch (\Throwable $e) {
            error_log("ClassesController::update Fatal Error: " . $e->getMessage());

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống nghiêm trọng: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Xóa lớp học
     */
    public function destroy($classId)
    {
        try {
            // Kiểm tra lớp học tồn tại
            $class = $this->model->getClassById($classId);
            if (!$class) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Lớp học không tồn tại',
                    'data' => []
                ], 404);
            }

            // Kiểm tra xem lớp có sinh viên không
            $students = $this->studentModel->getStudentsByClass($classId);
            if (!empty($students)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Không thể xóa lớp học vì có sinh viên đang thuộc lớp này',
                    'data' => []
                ], 400);
            }

            // Thực hiện xóa
            $result = $this->model->delete($classId);

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Xóa lớp học thành công',
                    'data' => []
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Xóa lớp học thất bại',
                    'data' => []
                ], 500);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Show class details
     */
    public function show($classId): void
    {
        $class = $this->model->getClassById($classId);
        if (!$class) {
            $this->redirect('/classes/manage');
            return;
        }

        $students = $this->studentModel->getStudentsByClass($classId);

        $this->render('classes/show', [
            'class' => $class,
            'students' => $students
        ]);
    }

    /**
     * Show edit class form
     */
    public function edit($classId): void
    {
        $class = $this->model->getClassById($classId);
        if (!$class) {
            $this->redirect('/classes/manage');
            return;
        }

        $faculties = $this->facultiesModel->findAll();

        $this->render('classes/edit', [
            'class' => $class,
            'faculties' => $faculties
        ]);
    }
}
