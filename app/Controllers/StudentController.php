<?php
// controllers/StudentController.php

namespace App\Controllers;

use PDO;
use Exception;
use App\Models\StudentModel;
use App\Models\UserModel;

class StudentController extends BaseController
{
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function manage(): void
    {
        try {
            // 1. Nhận tham số tìm kiếm và phân trang
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 10; // Số sinh viên trên mỗi trang
            $offset = ($page - 1) * $limit;

            // 2. Lấy dữ liệu
            $result = $this->studentModel->getStudentsWithPagination($limit, $offset, $keyword);
            $students = $result['students'];
            $totalStudents = $result['total'];
            $totalPages = ceil($totalStudents / $limit);

            // 3. Render View
            $this->render('students/manage', [
                'students' => $students,
                'page' => $page,
                'totalPages' => $totalPages,
                'totalStudents' => $totalStudents,
                'keyword' => $keyword
            ]);
        } catch (Exception $e) {
            error_log("Error in StudentController::manage: " . $e->getMessage());
            // Hiển thị trang lỗi hoặc thông báo
            $this->render('students/manage', [
                'students' => [],
                'page' => 1,
                'totalPages' => 0,
                'totalStudents' => 0,
                'keyword' => '',
                'error' => 'Đã xảy ra lỗi khi tải dữ liệu sinh viên'
            ]);
        }
    }

    public function getStudentDetails(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);

        if ($student) {
            // Format lại dữ liệu giới tính
            $student['gender_display'] = $this->getGenderDisplay($student['gender'] ?? null);
            $student['gender_icon'] = $this->getGenderIcon($student['gender'] ?? null);

            $this->jsonResponse([
                'success' => true,
                'student' => $student
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên'
            ], 404);
        }
    }

    // Thêm phương thức helper để hiển thị giới tính
    private function getGenderDisplay(?string $gender): string
    {
        return match ($gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
            default => 'Chưa cập nhật'
        };
    }

    private function getGenderIcon(?string $gender): string
    {
        return match ($gender) {
            'male' => 'bi-gender-male',
            'female' => 'bi-gender-female',
            'other' => 'bi-gender-ambiguous',
            default => 'bi-gender-trans'
        };
    }

    public function index(): void
    {
        $students = $this->studentModel->findAll();
        $this->render('students/index', ['students' => $students]);
    }

    public function create(): void
    {
        $this->render('students/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $_POST['user_id'] ?? 0,
                'mssv' => $_POST['mssv'] ?? '',
                'class_id' => (int)($_POST['class_id'] ?? 0),
                'faculty_id' => (int)($_POST['faculty_id'] ?? 0),
                'academic_year' => $_POST['academic_year'] ?? '2021-2025'
            ];
            $studentId = $this->studentModel->create($data);
            if ($studentId) {
                $this->redirect('students');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create student']);
    }

    public function show(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);
        $this->render('students/show', ['student' => $student]);
    }

    public function edit(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);
        $this->render('students/edit', ['student' => $student]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Cập nhật bảng students
            $studentData = [
                'mssv' => $_POST['mssv'] ?? '',
                'class_id' => (int)($_POST['class_id'] ?? 0),
                'faculty_id' => (int)($_POST['faculty_id'] ?? 0),
                'academic_year' => $_POST['academic_year'] ?? null
            ];

            // Cập nhật bảng users
            $userData = [
                'full_name' => $_POST['full_name'] ?? '',
                'gender' => $_POST['gender'] ?? null,
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone_number' => $_POST['phone_number'] ?? null,
                'address' => $_POST['address'] ?? null
            ];

            $student = $this->studentModel->getFullStudent($id);
            $userId = $student['user_id'] ?? null;

            $success = true;
            if ($userId) {
                $userModel = new UserModel($this->pdo);
                $success = $userModel->update($userId, $userData);
            }

            if ($success && $this->studentModel->update($id, $studentData)) {
                $this->redirect('students/show/' . $id);
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại']);
    }



    public function destroy(int $id): void
    {
        if ($this->studentModel->delete($id)) {
            $this->redirect('students');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete student']);
    }

    public function available(): void
    {
        $students = $this->studentModel->findAvailableStudents();
        $this->render('students/available', ['students' => $students]);
    }

    // Thêm các phương thức mới
    public function export(): void
    {
        // Logic export Excel
        $students = $this->studentModel->findAll();
        // Implement export logic here
    }

    public function downloadTemplate(): void
    {
        // Logic download template Excel
        // Implement template download logic here
    }
}
