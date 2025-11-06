<?php
// controllers/LecturerController.php

namespace App\Controllers;

use PDO;
use App\Models\LecturerModel;

class LecturerController extends BaseController
{
    private LecturerModel $lecturerModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
    }

    public function manage(): void
    {
        $lecturers = $this->lecturerModel->getWithProjectCount();
        $this->render('lecturers/manage', ['lecturers' => $lecturers]);
    }

    public function create(): void
    {
        $this->render('lecturers/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $_POST['user_id'] ?? 0,
                'department' => $_POST['department'] ?? null
            ];
            $lecturerId = $this->lecturerModel->create($data);
            if ($lecturerId) {
                $this->redirect('lecturers');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create lecturer']);
    }

    public function show(int $id): void
    {
        $lecturer = $this->lecturerModel->getFullLecturer($id);
        $this->render('lecturers/show', ['lecturer' => $lecturer]);
    }

    public function edit(int $id): void
    {
        $lecturer = $this->lecturerModel->getFullLecturer($id);
        $this->render('lecturers/edit', ['lecturer' => $lecturer]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'department' => $_POST['department'] ?? null
            ];
            if ($this->lecturerModel->update($id, $data)) {
                $this->redirect('lecturers');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update lecturer']);
    }

    public function destroy(int $id): void
    {
        if ($this->lecturerModel->delete($id)) {
            $this->redirect('lecturers');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete lecturer']);
    }
}
