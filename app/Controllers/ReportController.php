<?php
// controllers/ReportController.php

namespace App\Controllers;

use PDO;
use App\Models\ReportModel;
use App\Models\GroupModel;

class ReportController extends BaseController
{
    private ReportModel $reportModel;
    private GroupModel $groupModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->reportModel = new ReportModel($pdo);
        $this->groupModel = new GroupModel($pdo);
    }

    public function index(): void
    {
        $reports = $this->reportModel->findAll();
        $this->render('reports/index', ['reports' => $reports]);
    }

    public function create(int $groupId): void
    {
        $group = $this->groupModel->getFullGroup($groupId);
        $this->render('reports/create', ['group' => $group]);
    }

    public function store(int $groupId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filePath = $_POST['file_path'] ?? null;
            $codeLink = $_POST['code_link'] ?? null;
            $reportId = $this->reportModel->submit($groupId, $filePath, $codeLink);
            if ($reportId) {
                $this->redirect("groups/show/$groupId");
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to submit report']);
    }

    public function show(int $id): void
    {
        $report = $this->reportModel->getFullReport($id);
        $this->render('reports/show', ['report' => $report]);
    }

    public function edit(int $id): void
    {
        $report = $this->reportModel->getFullReport($id);
        $this->render('reports/edit', ['report' => $report]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'file_path' => $_POST['file_path'] ?? null,
                'code_link' => $_POST['code_link'] ?? null
            ];
            if ($this->reportModel->update($id, $data)) {
                $this->redirect('reports');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update report']);
    }

    public function destroy(int $id): void
    {
        if ($this->reportModel->delete($id)) {
            $this->redirect('reports');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete report']);
    }
}
