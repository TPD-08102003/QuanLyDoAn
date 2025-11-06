<?php
// models/FeedbackModel.php

namespace App\Models;

use PDO;

class FeedbackModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'feedback');
    }

    /**
     * Get full feedback with report and lecturer.
     * @param int $feedbackId
     * @return array|false
     */
    public function getFullFeedback(int $feedbackId): array|false
    {
        $sql = "SELECT f.*, r.file_path, r.code_link, l.department, u.full_name as lecturer_name 
                FROM {$this->table} f 
                JOIN reports r ON f.report_id = r.report_id 
                JOIN lecturers l ON f.lecturer_id = l.lecturer_id 
                JOIN users u ON l.user_id = u.user_id 
                WHERE f.feedback_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $feedbackId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find feedbacks by report ID.
     * @param int $reportId
     * @return array
     */
    public function findByReport(int $reportId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE report_id = :report_id");
        $stmt->execute(['report_id' => $reportId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find feedbacks by lecturer ID.
     * @param int $lecturerId
     * @return array
     */
    public function findByLecturer(int $lecturerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE lecturer_id = :lecturer_id");
        $stmt->execute(['lecturer_id' => $lecturerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add feedback to report.
     * @param int $reportId
     * @param int $lecturerId
     * @param string|null $comment
     * @param float|null $score
     * @return int|false
     */
    public function addFeedback(int $reportId, int $lecturerId, ?string $comment = null, ?float $score = null): int|false
    {
        $data = [
            'report_id' => $reportId,
            'lecturer_id' => $lecturerId,
            'comment' => $comment,
            'score' => $score
        ];
        return $this->create($data);
    }

    /**
     * Get average score for report.
     * @param int $reportId
     * @return float|null
     */
    public function getAverageScore(int $reportId): ?float
    {
        $stmt = $this->pdo->prepare("SELECT AVG(score) as avg_score FROM {$this->table} WHERE report_id = :report_id");
        $stmt->execute(['report_id' => $reportId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_score'] ? (float) $result['avg_score'] : null;
    }
}
