<?php
// controllers/StudentController.php

namespace App\Controllers;

use PDO;
use App\Models\StudentModel;

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
            'keyword' => $keyword
        ]);
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
                'class' => $_POST['class'] ?? null
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
            $data = [
                'mssv' => $_POST['mssv'] ?? '',
                'class' => $_POST['class'] ?? null
            ];
            if ($this->studentModel->update($id, $data)) {
                $this->redirect('students');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update student']);
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
}
