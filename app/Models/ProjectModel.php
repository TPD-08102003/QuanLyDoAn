<?php
// models/ProjectModel.php

namespace App\Models;

use Exception;
use PDO;

class ProjectModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'projects', 'project_id');
    }


    /**
     * Get all projects with lecturer information
     * @return array
     */
    public function getAllWithDetails(): array
    {
        $sql = "
            SELECT 
                p.project_id,
                p.title,
                p.description,
                p.status,
                p.created_at,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            ORDER BY p.created_at DESC
        ";

        return $this->query($sql);
    }

    /**
     * Get projects with pagination and search
     * @param int $limit
     * @param int $offset
     * @param string $keyword
     * @return array
     */
    public function getProjectsWithPagination(int $limit, int $offset, string $keyword): array
    {
        $sql = "
            SELECT 
                p.project_id,
                p.title,
                p.description,
                p.status,
                p.created_at,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR u.full_name LIKE :keyword
               OR f.faculty_name LIKE :keyword
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "
            SELECT COUNT(*) as total
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR u.full_name LIKE :keyword
               OR f.faculty_name LIKE :keyword
        ";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindValue(':keyword', "%$keyword%");
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return ['projects' => $projects, 'total' => $total];
    }

    /**
     * Get project with details by ID
     * @param int $id
     * @return array|false
     */
    public function getByIdWithDetails(int $id): array|false
    {
        $sql = "
            SELECT 
                p.*,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name,
                f.faculty_id
                
               
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.project_id = :id
        ";

        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : false;
    }

    /**
     * Find project by title
     * @param string $title
     * @return array|false
     */
    public function findByTitle(string $title): array|false
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE title = :title
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':title' => $title]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new project
     * @param array $data
     * @return int|false
     */
    public function createProject(array $data): int|false
    {
        // Validate required fields
        if (empty($data['title']) || empty($data['lecturer_id'])) {
            return false;
        }

        $data['status'] = $data['status'] ?? 'ChoDuyet';
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Update project
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProject(int $id, array $data): bool
    {
        // Remove empty fields if needed
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->update($id, $data);
    }

    /**
     * Delete project and related data (groups, reports, etc.)
     * @param int $id
     * @return bool
     */
    public function deleteProject(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Delete related report_types
            $this->pdo->prepare("DELETE FROM report_types WHERE project_id = :id")->execute([':id' => $id]);

            // Delete related groups
            $this->pdo->prepare("DELETE FROM groups WHERE project_id = :id")->execute([':id' => $id]);

            // Delete project
            $result = $this->delete($id);

            $this->pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting project: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available lecturers for assignment
     * @return array
     */
    // public function getAvailableLecturers(): array
    // {
    //     $sql = "
    //         SELECT 
    //             l.lecturer_id,
    //             u.full_name,
    //             f.faculty_name,
    //             COUNT(p.project_id) AS project_count
    //         FROM lecturers l
    //         LEFT JOIN users u ON l.user_id = u.user_id
    //         LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
    //         LEFT JOIN projects p ON l.lecturer_id = p.lecturer_id
    //         WHERE l.deleted_at IS NULL
    //         GROUP BY l.lecturer_id
    //         HAVING project_count < 10  -- Giả sử giới hạn 10 đồ án/giảng viên
    //         ORDER BY u.full_name
    //     ";

    //     return $this->query($sql);
    // }
    /**
     * Get available lecturers (with project count)
     * @param int|null $facultyId
     * @return array
     */
    public function getAvailableLecturers(?int $facultyId = null): array
    {
        $sql = "
            SELECT 
                l.lecturer_id,
                u.full_name,
                f.faculty_name,
                COUNT(p.project_id) AS project_count
            FROM lecturers l
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            LEFT JOIN projects p ON l.lecturer_id = p.lecturer_id
            WHERE l.deleted_at IS NULL
        ";
        $params = [];
        if ($facultyId !== null && $facultyId > 0) {
            $sql .= " AND l.faculty_id = :faculty_id";
            $params[':faculty_id'] = $facultyId;
        }
        $sql .= "
            GROUP BY l.lecturer_id, u.full_name, f.faculty_name
            HAVING project_count < 10  -- Giả sử giới hạn 10 đồ án/giảng viên
            ORDER BY u.full_name
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Change project status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function changeStatus(int $id, string $status): bool
    {
        // Cần kiểm tra status có hợp lệ không (logic này nằm trong ProjectModel)
        $validStatuses = ['ChoDuyet', 'DaDuyet', 'DangThucHien', 'DaNopBaoCao', 'DaBaoVe', 'HoanThanh', 'Huy'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        // ⭐ Gọi hàm update từ BaseModel (đã được thêm vào Base Model của bạn)
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Get pending projects (status = 'ChoDuyet') with pagination and search
     * @param int $limit
     * @param int $offset
     * @param string $keyword
     * @return array
     */
    // public function getPendingProjectsWithPagination(int $limit, int $offset, string $keyword): array
    // {
    //     $sql = "
    //     SELECT 
    //         p.project_id,
    //         p.title,
    //         p.description,
    //         p.status,
    //         p.created_at,
    //         l.lecturer_id,
    //         u.full_name AS lecturer_name,
    //         f.faculty_name
    //     FROM {$this->table} p
    //     LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
    //     LEFT JOIN users u ON l.user_id = u.user_id
    //     LEFT JOIN faculties f ON l.faculty_id = f.faculty_id

    //     WHERE p.status = 'ChoDuyet'
    //       AND (p.title LIKE :keyword 
    //            OR p.description LIKE :keyword 
    //            OR u.full_name LIKE :keyword
    //            OR f.faculty_name LIKE :keyword)
    //     ORDER BY p.created_at DESC
    //     LIMIT :limit OFFSET :offset
    // ";

    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->bindValue(':keyword', "%$keyword%");
    //     $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    //     $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    //     $stmt->execute();
    //     $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     $countSql = "
    //     SELECT COUNT(*) as total
    //     FROM {$this->table} p
    //     LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
    //     LEFT JOIN users u ON l.user_id = u.user_id
    //     LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
    //     WHERE p.status = 'ChoDuyet'
    //       AND (p.title LIKE :keyword 
    //            OR p.description LIKE :keyword 
    //            OR u.full_name LIKE :keyword
    //            OR f.faculty_name LIKE :keyword)
    // ";

    //     $countStmt = $this->pdo->prepare($countSql);
    //     $countStmt->bindValue(':keyword', "%$keyword%");
    //     $countStmt->execute();
    //     $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    //     return ['projects' => $projects, 'total' => $total];
    // }
    public function getPendingProjectsWithPagination(int $limit, int $offset, string $keyword = ''): array
    {
        $sql = "
        SELECT 
            p.project_id,
            p.title,
            p.description,
            p.status,
            p.created_at,
            u.full_name AS lecturer_name,
            f.faculty_name
        FROM projects p
        LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
        LEFT JOIN users u ON l.user_id = u.user_id
        LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
        WHERE p.status = 'ChoDuyet'
    ";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (p.title LIKE :keyword 
                       OR p.description LIKE :keyword 
                       OR u.full_name LIKE :keyword 
                       OR f.faculty_name LIKE :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Đếm tổng
        $countSql = "SELECT COUNT(*) FROM projects p 
                 LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                 LEFT JOIN users u ON l.user_id = u.user_id
                 LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                 WHERE p.status = 'ChoDuyet'";
        if (!empty($keyword)) {
            $countSql .= " AND (p.title LIKE :keyword 
                            OR p.description LIKE :keyword 
                            OR u.full_name LIKE :keyword 
                            OR f.faculty_name LIKE :keyword)";
        }

        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        return [
            'projects' => $projects,
            'total'    => $total
        ];
    }

    /**
     * Lấy tổng số đồ án trong hệ thống.
     */
    public function getTotalProjects(): int
    {
        // Giả sử $this->pdo là đối tượng PDO đã được khởi tạo
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'DaDuyet'");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy tất cả đồ án và nhóm theo Khoa, sau đó là Lớp.
     *
     * @return array
     */
    public function getAllProjectsGrouped(): array
    {
        // Cần đảm bảo các bảng 'projects', 'faculties', 'classes' đã được tạo
        $sql = "
        SELECT 
            p.project_id, p.title, p.description, p.created_at, p.status, 
            f.faculty_id, f.faculty_name, 
            c.class_id, c.class_name
        FROM 
            projects p
        JOIN 
            faculties f ON p.faculty_id = f.faculty_id
        JOIN 
            classes c ON p.class_id = c.class_id
        ORDER BY 
            f.faculty_name, c.class_name, p.created_at DESC
    ";

        // Giả sử $this->pdo là đối tượng PDO đã được khởi tạo
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($results as $row) {
            $faculty_id = $row['faculty_id'];
            $class_id = $row['class_id'];

            // Nhóm theo Khoa
            if (!isset($grouped[$faculty_id])) {
                $grouped[$faculty_id] = [
                    'faculty_id' => $faculty_id,
                    'faculty_name' => $row['faculty_name'],
                    'classes' => []
                ];
            }

            // Nhóm theo Lớp
            if (!isset($grouped[$faculty_id]['classes'][$class_id])) {
                $grouped[$faculty_id]['classes'][$class_id] = [
                    'class_id' => $class_id,
                    'class_name' => $row['class_name'],
                    'projects' => []
                ];
            }

            // Thêm Đồ án vào Lớp tương ứng
            $grouped[$faculty_id]['classes'][$class_id]['projects'][] = [
                'project_id' => $row['project_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
            ];
        }

        return $grouped;
    }

    // app/Models/ProjectModel.php (Sửa trong hàm getLatestProjects)

    /**
     * Lấy số lượng đồ án mới nhất cho Carousel (Chỉ lấy đồ án ĐÃ DUYỆT)
     * Cập nhật: Lấy thêm max_students và đếm số lượng sinh viên hiện tại
     */
    public function getLatestProjects(int $limit = 3): array
    {
        $sql = "
        SELECT 
            p.project_id, 
            p.title, 
            p.description, 
            p.created_at, 
            p.status,
            u.full_name as lecturer_name,
            COALESCE(p.max_students, 3) as max_students,
            (SELECT COUNT(*) 
             FROM group_members gm 
             JOIN groups g ON gm.group_id = g.group_id 
             WHERE g.project_id = p.project_id) as current_students
        FROM 
            projects p
        LEFT JOIN 
            lecturers l ON p.lecturer_id = l.lecturer_id
        LEFT JOIN
            users u ON l.user_id = u.user_id
        WHERE 
            p.status = 'DaDuyet' 
        ORDER BY 
            p.created_at DESC 
        LIMIT :limit
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * Lấy danh sách thành viên của một đồ án
     * @param int $projectId
     * @return array
     */
    public function getProjectMembers(int $projectId): array
    {
        $sql = "SELECT s.mssv, u.full_name
                FROM groups g
                JOIN group_members gm ON g.group_id = gm.group_id
                JOIN students s ON gm.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                WHERE g.project_id = :project_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // app/Models/ProjectModel.php (Sửa trong hàm getAllProjectsWithDetails)

    /**
     * Lấy tất cả đồ án ĐÃ DUYỆT, kèm tên giảng viên.
     */
    public function getAllProjectsWithDetails(): array
    {
        $sql = "
        SELECT 
            p.project_id, p.title, p.status, p.created_at,
            u.full_name as lecturer_name
        FROM 
            projects p
        LEFT JOIN 
            lecturers l ON p.lecturer_id = l.lecturer_id
        LEFT JOIN
            users u ON l.user_id = u.user_id
        WHERE 
            p.status = 'DaDuyet' 
        ORDER BY 
            p.created_at DESC
    ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Lấy danh sách đồ án của giảng viên có phân trang
     * Đã sửa lỗi:
     * 1. Join bảng users để lấy tên giảng viên (u.full_name)
     * 2. Join bảng faculties thông qua bảng lecturers (l.faculty_id) thay vì projects
     */
    /**
     * Lấy danh sách đồ án của giảng viên có phân trang
     * ĐÃ CẬP NHẬT: Thêm đếm số lượng sinh viên (current_students)
     */
    public function getProjectsByLecturerIdWithPagination(int $lecturerId, int $limit, int $offset, string $keyword = ''): array
    {
        // 1. Chuẩn bị mệnh đề WHERE
        $whereClause = "WHERE p.lecturer_id = :lecturer_id";
        $params = [':lecturer_id' => $lecturerId];

        // Kiểm tra keyword
        $isSearch = !empty(trim($keyword));

        if ($isSearch) {
            $whereClause .= " AND (p.title LIKE :keyword OR p.project_id LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        // 2. Đếm tổng số (Count Query)
        $sqlTotal = "SELECT COUNT(p.project_id) AS total FROM projects p {$whereClause}";
        $stmtTotal = $this->pdo->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $total = $stmtTotal->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

        // 3. Truy vấn lấy dữ liệu (Main Query)
        // CẬP NHẬT: Thêm dòng đếm số lượng sinh viên trong nhóm
        $sqlProjects = "SELECT 
                            p.project_id, 
                            p.title, 
                            p.description, -- Lấy thêm mô tả để hiển thị preview
                            p.status,
                            p.created_at,
                            COALESCE(p.max_students, 3) as max_students, -- Lấy max_students
                            f.faculty_name, 
                            u.full_name AS lecturer_name, 
                            
                            -- ĐẾM SỐ SINH VIÊN ĐANG THAM GIA --
                            (SELECT COUNT(*) 
                             FROM group_members gm 
                             JOIN groups g ON gm.group_id = g.group_id 
                             WHERE g.project_id = p.project_id) as current_students
                             
                        FROM projects p
                        LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                        LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                        LEFT JOIN users u ON l.user_id = u.user_id
                        {$whereClause}
                        ORDER BY p.created_at DESC
                        LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sqlProjects);

        // 4. Bind tham số
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        if ($isSearch) {
            $stmt->bindValue(':keyword', '%' . $keyword . '%', \PDO::PARAM_STR);
        }

        $stmt->execute();

        return [
            'projects' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => (int)$total,
        ];
    }

    public function registerStudentToProject($studentId, $projectId)
    {
        try {
            $this->pdo->beginTransaction();

            // Kiểm tra đồ án có tồn tại và còn trống không (tính từ COUNT students)
            $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as current_count 
            FROM students 
            WHERE project_id = ? 
            FOR UPDATE
        ");
            $stmt->execute([$projectId]);
            $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['current_count'] ?? 0;

            // Giả sử max_students = 1 (như yêu cầu, không cần sửa)
            if ($currentCount >= 1) {
                $this->pdo->rollBack();
                return false; // Đã đủ người
            }

            // Cập nhật sinh viên
            $stmt = $this->pdo->prepare("UPDATE students SET project_id = ? WHERE student_id = ?");
            $stmt->execute([$projectId, $studentId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Lỗi đăng ký đồ án: " . $e->getMessage());
            return false;
        }
    }

    // Sửa getAvailableProjectsForRegistration (thêm lọc current < 1, giả sử max=1)
    public function getAvailableProjectsForRegistration(int $limit = 10, int $offset = 0, string $keyword = ''): array
    {
        $likeKeyword = '%' . $keyword . '%';

        // 1. Truy vấn danh sách đồ án + thông tin giảng viên + số SV hiện tại
        $sql = "
        SELECT 
            p.project_id,
            p.title,
            p.description,
            p.status,
            p.max_students,  // Giữ nếu có, nhưng hardcode check <1
            p.created_at,
            l.full_name AS lecturer_name,
            f.faculty_name,
            COALESCE(reg.student_count, 0) AS current_students
        FROM projects p
        LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
        LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
        LEFT JOIN (
            SELECT project_id, COUNT(*) AS student_count 
            FROM students 
            WHERE project_id IS NOT NULL 
            GROUP BY project_id
        ) reg ON p.project_id = reg.project_id
        WHERE p.status IN ('DaDuyet', 'DangThucHien')
          AND COALESCE(reg.student_count, 0) < 1  // Sửa: Chỉ hiển thị đồ án còn chỗ (max=1)
          AND (p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR l.full_name LIKE :keyword 
               OR f.faculty_name LIKE :keyword)
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':keyword', $likeKeyword, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Đếm tổng số đồ án (cho phân trang) - Thêm lọc tương tự
        $countSql = "
        SELECT COUNT(*) 
        FROM projects p
        LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
        LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
        LEFT JOIN (
            SELECT project_id, COUNT(*) AS student_count 
            FROM students 
            WHERE project_id IS NOT NULL 
            GROUP BY project_id
        ) reg ON p.project_id = reg.project_id
        WHERE p.status IN ('DaDuyet', 'DangThucHien')
          AND COALESCE(reg.student_count, 0) < 1  // Sửa: Chỉ đếm đồ án còn chỗ
          AND (p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR l.full_name LIKE :keyword 
               OR f.faculty_name LIKE :keyword)
    ";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindValue(':keyword', $likeKeyword, PDO::PARAM_STR);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'projects' => $projects,
            'total'    => $total
        ];
    }
}
