<?php
// controllers/GroupController.php

namespace App\Controllers;

use PDO;
use App\Models\GroupModel;
use App\Models\ProjectModel;
use App\Models\StudentModel;

class GroupController extends BaseController
{
    private GroupModel $groupModel;
    private ProjectModel $projectModel;
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->groupModel = new GroupModel($pdo);
        $this->projectModel = new ProjectModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function index(): void
    {
        $groups = $this->groupModel->findAll();
        $this->render('groups/index', ['groups' => $groups]);
    }

    public function create(): void
    {
        $projects = $this->projectModel->findAll();
        $students = $this->studentModel->findAll();
        $this->render('groups/create', ['projects' => $projects, 'students' => $students]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'project_id' => $_POST['project_id'] ?? 0,
                'leader_id' => $_POST['leader_id'] ?? 0
            ];
            $groupId = $this->groupModel->create($data);
            if ($groupId) {
                $this->redirect('groups');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create group']);
    }

    public function show(int $id): void
    {
        $group = $this->groupModel->getFullGroup($id);
        $this->render('groups/show', ['group' => $group]);
    }

    public function edit(int $id): void
    {
        $group = $this->groupModel->getFullGroup($id);
        $projects = $this->projectModel->findAll();
        $students = $this->studentModel->findAll();
        $this->render('groups/edit', ['group' => $group, 'projects' => $projects, 'students' => $students]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'project_id' => $_POST['project_id'] ?? 0,
                'leader_id' => $_POST['leader_id'] ?? 0
            ];
            if ($this->groupModel->update($id, $data)) {
                $this->redirect('groups');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update group']);
    }

    public function destroy(int $id): void
    {
        if ($this->groupModel->delete($id)) {
            $this->redirect('groups');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete group']);
    }
}
