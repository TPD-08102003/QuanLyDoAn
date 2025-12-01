<?php
// models/ScoreModel.php
namespace App\Models;

use PDO;

class ScoreModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'groups', 'group_id');
    }

    /**
     * Lấy bảng điểm cá nhân cho Sinh viên dựa trên User ID
     * (Cách mới: Tách query để đảm bảo chính xác 100%)
     */
    public function getStudentScoreByUserId(int $userId)
    {
        // BƯỚC 1: Lấy Student ID từ User ID trước
        $stmtStudent = $this->pdo->prepare("SELECT student_id FROM students WHERE user_id = :uid LIMIT 1");
        $stmtStudent->execute(['uid' => $userId]);
        $studentId = $stmtStudent->fetchColumn();

        // Nếu tài khoản này không phải sinh viên -> Trả về null
        if (!$studentId) {
            return null;
        }

        // BƯỚC 2: Dùng Student ID để tìm nhóm và điểm
        // (Sử dụng LEFT JOIN linh hoạt hơn để không bị mất dữ liệu nếu thiếu thông tin GV)
        $sqlGroup = "SELECT g.*, p.title as project_title, p.project_id,
                            COALESCE(u.full_name, 'Chưa cập nhật') as lecturer_name 
                     FROM group_members gm
                     JOIN groups g ON gm.group_id = g.group_id
                     JOIN projects p ON g.project_id = p.project_id
                     LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                     LEFT JOIN users u ON l.user_id = u.user_id
                     WHERE gm.student_id = :sid 
                     LIMIT 1";

        $stmt = $this->pdo->prepare($sqlGroup);
        $stmt->execute(['sid' => $studentId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        // NẾU KHÔNG TÌM THẤY NHÓM
        if (!$group) {
            return null;
        }

        // Kiểm tra trạng thái công bố điểm
        if (isset($group['is_published']) && $group['is_published'] == 0) {
            return ['status' => 'unpublished', 'project' => $group];
        }

        // BƯỚC 3: Lấy chi tiết điểm các cột thành phần
        $sqlDetails = "SELECT rt.type_name, rt.description, rt.max_score, 
                              f.score, f.comment, f.created_at as graded_at
                       FROM report_types rt
                       LEFT JOIN reports r ON rt.type_id = r.type_id AND r.group_id = :gid
                       LEFT JOIN feedback f ON r.report_id = f.report_id
                       WHERE rt.project_id = :pid
                       ORDER BY rt.deadline ASC";

        $stmtDetails = $this->pdo->prepare($sqlDetails);
        $stmtDetails->execute([
            'gid' => $group['group_id'],
            'pid' => $group['project_id']
        ]);
        $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'published',
            'group' => $group,
            'details' => $details
        ];
    }

    /**
     * Lấy bảng điểm tổng hợp cho Giảng viên (Giữ nguyên)
     */
    public function getProjectScores(int $projectId)
    {
        $stmtTypes = $this->pdo->prepare("SELECT type_id, type_name, max_score, deadline, description FROM report_types WHERE project_id = ? ORDER BY deadline ASC");
        $stmtTypes->execute([$projectId]);
        $types = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

        $sqlGroups = "SELECT g.group_id, g.total_score, g.is_published,
                             GROUP_CONCAT(CONCAT(s.mssv, ' - ', u.full_name) SEPARATOR '<br>') as members
                      FROM groups g
                      JOIN group_members gm ON g.group_id = gm.group_id
                      JOIN students s ON gm.student_id = s.student_id
                      JOIN users u ON s.user_id = u.user_id
                      WHERE g.project_id = ?
                      GROUP BY g.group_id";

        $stmtGroups = $this->pdo->prepare($sqlGroups);
        $stmtGroups->execute([$projectId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        foreach ($groups as &$group) {
            $scores = [];
            foreach ($types as $type) {
                $sqlScore = "SELECT f.score 
                             FROM reports r 
                             JOIN feedback f ON r.report_id = f.report_id 
                             WHERE r.group_id = ? AND r.type_id = ?";
                $stmtScore = $this->pdo->prepare($sqlScore);
                $stmtScore->execute([$group['group_id'], $type['type_id']]);
                $score = $stmtScore->fetchColumn();
                $scores[$type['type_id']] = $score !== false ? $score : null;
            }
            $group['scores'] = $scores;
        }

        return ['types' => $types, 'groups' => $groups];
    }

    public function togglePublish(int $groupId, int $status)
    {
        return $this->update($groupId, ['is_published' => $status]);
    }

    public function updateTotalScore(int $groupId, float $score)
    {
        return $this->update($groupId, ['total_score' => $score]);
    }
}
