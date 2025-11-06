<?php
// models/ReportModel.php

namespace App\Models;

use PDO;

class ReportModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'reports');
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
}
