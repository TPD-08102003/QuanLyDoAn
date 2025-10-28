<?php
// controllers/FeedbackController.php

namespace App\Controllers;

use PDO;
use App\Models\FeedbackModel;
use App\Models\ReportModel;

class FeedbackController extends BaseController
{
    private FeedbackModel $feedbackModel;
    private ReportModel $reportModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->feedbackModel = new FeedbackModel($pdo);
        $this->reportModel = new ReportModel($pdo);
    }

    public function index(): void
    {
        $feedbacks = $this->feedbackModel->findAll();
        $this->render('feedbacks/index', ['feedbacks' => $feedbacks]);
    }

    public function create(int $reportId): void
    {
        $report = $this->reportModel->getFullReport($reportId);
        $this->render('feedbacks/create', ['report' => $report]);
    }

    public function store(int $reportId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lecturerId = $_SESSION['user_id'] ?? 0; // Assuming lecturer is logged in
            $comment = $_POST['comment'] ?? null;
            $score = $_POST['score'] ?? null;
            $feedbackId = $this->feedbackModel->addFeedback($reportId, $lecturerId, $comment, $score);
            if ($feedbackId) {
                $this->redirect("reports/show/$reportId");
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to add feedback']);
    }

    public function show(int $id): void
    {
        $feedback = $this->feedbackModel->getFullFeedback($id);
        $this->render('feedbacks/show', ['feedback' => $feedback]);
    }

    public function destroy(int $id): void
    {
        if ($this->feedbackModel->delete($id)) {
            $this->redirect('feedbacks');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete feedback']);
    }
}
