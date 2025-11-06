<?php
// controllers/ProjectController.php

namespace App\Controllers;

use PDO;
use App\Models\ProjectModel;

class ProjectController extends BaseController
{
    private ProjectModel $projectModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->projectModel = new ProjectModel($pdo);
    }

    public function index(): void
    {
        $projects = $this->projectModel->getWithGroupCount();
        $this->render('projects/index', ['projects' => $projects]);
    }

    public function create(): void
    {
        $this->render('projects/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? null,
                'lecturer_id' => $_POST['lecturer_id'] ?? 0,
                'status' => $_POST['status'] ?? 'DangThucHien'
            ];
            $projectId = $this->projectModel->create($data);
            if ($projectId) {
                $this->redirect('projects');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create project']);
    }

    public function show(int $id): void
    {
        $project = $this->projectModel->getFullProject($id);
        $this->render('projects/show', ['project' => $project]);
    }

    public function edit(int $id): void
    {
        $project = $this->projectModel->getFullProject($id);
        $this->render('projects/edit', ['project' => $project]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? null,
                'status' => $_POST['status'] ?? 'DangThucHien'
            ];
            if ($this->projectModel->update($id, $data)) {
                $this->redirect('projects');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update project']);
    }

    public function destroy(int $id): void
    {
        if ($this->projectModel->delete($id)) {
            $this->redirect('projects');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete project']);
    }

    public function byStatus(string $status): void
    {
        $projects = $this->projectModel->findByStatus($status);
        $this->render('projects/by_status', ['projects' => $projects, 'status' => $status]);
    }
}
