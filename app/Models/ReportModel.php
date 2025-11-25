<?php
// models/ReportModel.php

namespace App\Models;

use App\Models\FeedbackModel;
use PDO;
use Exception;

class ReportModel extends BaseModel
{
    protected FeedbackModel $feedbackModel;
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'reports');
        $this->feedbackModel = new FeedbackModel($pdo);
    }

    /**
     * Get full report info with group.
     * @param int $reportId
     * @return array|false
     */
    public function getFullReport(int $reportId): array|false
    {
        $sql = "SELECT r.*, g.group_id, p.title as project_title 
                FROM {$this->table} r 
                JOIN groups g ON r.group_id = g.group_id 
                JOIN projects p ON g.project_id = p.project_id 
                WHERE r.report_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find reports by group ID.
     * @param int $groupId
     * @return array
     */
    public function findByGroup(int $groupId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Submit new report.
     * @param int $groupId
     * @param string|null $filePath
     * @param string|null $codeLink
     * @return int|false
     */
    public function submit(int $groupId, ?string $filePath = null, ?string $codeLink = null): int|false
    {
        $data = [
            'group_id' => $groupId,
            'file_path' => $filePath,
            'code_link' => $codeLink
        ];
        return $this->create($data);
    }

    public function getReportTypesByLecturer(int $lecturerId): array
    {
        // Sửa câu truy vấn: Join thêm bảng groups và reports
        // Logic: Lấy thông tin loại báo cáo + Trạng thái nộp của nhóm (nếu có)
        $sql = "SELECT rt.*, 
                   p.title as project_title, 
                   p.project_id,
                   r.status as report_status,     -- Lấy trạng thái (ChuaNop, DaNop...)
                   r.submitted_at,                -- Lấy thời gian nộp
                   r.report_id,                   -- Lấy ID bài nộp (để chấm điểm/tải file)
                   g.group_id,
                   fb.score as actual_score                    -- Lấy ID nhóm
            FROM report_types rt
            JOIN projects p ON rt.project_id = p.project_id
            -- Tìm nhóm đang nhận đồ án này (nếu có)
            LEFT JOIN groups g ON p.project_id = g.project_id 
            -- Tìm bài nộp của nhóm đó cho giai đoạn này
            LEFT JOIN reports r ON rt.type_id = r.type_id AND g.group_id = r.group_id
            -- Lấy điểm thực tế nếu đã có feedback
            LEFT JOIN feedback fb ON r.report_id = fb.report_id
            WHERE p.lecturer_id = :lecturer_id
            ORDER BY p.project_id DESC, rt.deadline ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật thông tin loại báo cáo (Deadline, mô tả)
     */
    public function updateReportType(int $typeId, array $data): bool
    {
        // Xây dựng câu query update động
        $fields = [];
        $params = [':type_id' => $typeId];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) return false;

        $sql = "UPDATE report_types SET " . implode(', ', $fields) . " WHERE type_id = :type_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Lấy chi tiết 1 report type để hiện lên form sửa
     */
    public function getReportTypeById(int $typeId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM report_types WHERE type_id = ?");
        $stmt->execute([$typeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết bài nộp để chấm điểm
     */
    public function getReportDetailForGrading(int $reportId): array|false
    {
        $sql = "SELECT r.*, 
                       rt.type_name, rt.max_score, rt.deadline, rt.description as type_desc,
                       p.title as project_title, p.lecturer_id,
                       g.group_id,
                       fb.score, fb.comment, fb.feedback_id
                FROM reports r
                JOIN report_types rt ON r.type_id = rt.type_id
                JOIN groups g ON r.group_id = g.group_id
                JOIN projects p ON g.project_id = p.project_id
                LEFT JOIN feedback fb ON r.report_id = fb.report_id
                WHERE r.report_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách file đính kèm của báo cáo
     */
    public function getReportFiles(int $reportId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM uploads WHERE report_id = ?");
        $stmt->execute([$reportId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lưu điểm và nhận xét (Dùng Transaction)
     */
    public function saveGrade(int $reportId, int $lecturerId, float $score, string $comment): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Kiểm tra xem đã có feedback chưa để Insert hoặc Update
            $stmtCheck = $this->pdo->prepare("SELECT feedback_id FROM feedback WHERE report_id = ?");
            $stmtCheck->execute([$reportId]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists) {
                // Update
                $sqlFb = "UPDATE feedback SET score = ?, comment = ?, lecturer_id = ?, created_at = NOW() WHERE report_id = ?";
                $stmtFb = $this->pdo->prepare($sqlFb);
                $stmtFb->execute([$score, $comment, $lecturerId, $reportId]);
            } else {
                // Insert
                $sqlFb = "INSERT INTO feedback (report_id, lecturer_id, score, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmtFb = $this->pdo->prepare($sqlFb);
                $stmtFb->execute([$reportId, $lecturerId, $score, $comment]);
            }

            // 2. Cập nhật trạng thái report thành 'DaCham'
            $stmtRep = $this->pdo->prepare("UPDATE reports SET status = 'DaCham' WHERE report_id = ?");
            $stmtRep->execute([$reportId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
