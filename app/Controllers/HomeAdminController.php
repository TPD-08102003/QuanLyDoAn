<?php
// controllers/HomeAdminController.php

namespace App\Controllers;

use PDO;

class HomeAdminController extends BaseController
{
    public function index(): void
    {
        // 1. Thống kê số lượng tổng quan
        $stats = [
            'students' => 0,
            'lecturers' => 0,
            'projects' => 0,
            'groups' => 0,
            'pending_projects' => 0
        ];

        try {
            // Đếm sinh viên
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM students");
            $stats['students'] = $stmt->fetchColumn();

            // Đếm giảng viên
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM lecturers");
            $stats['lecturers'] = $stmt->fetchColumn();

            // Đếm tổng đồ án
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects");
            $stats['projects'] = $stmt->fetchColumn();

            // Đếm nhóm
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM groups");
            $stats['groups'] = $stmt->fetchColumn();

            // Đếm đồ án đang chờ duyệt
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'ChoDuyet'");
            $stats['pending_projects'] = $stmt->fetchColumn();

            // 2. Lấy dữ liệu cho biểu đồ tròn (Trạng thái đồ án)
            // Kết quả trả về dạng: ['ChoDuyet' => 5, 'DangThucHien' => 10, ...]
            $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
            $rawChartData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Chuẩn hóa dữ liệu cho Chart.js
            $chartData = [
                'HoanThanh' => $rawChartData['HoanThanh'] ?? 0,
                'DangThucHien' => $rawChartData['DangThucHien'] ?? 0,
                'ChoDuyet' => $rawChartData['ChoDuyet'] ?? 0,
                'Huy' => $rawChartData['Huy'] ?? 0,
                'Khac' => 0 // Các trạng thái còn lại (DaDuyet, DaNopBaoCao...) gộp vào đây hoặc tách riêng tùy nhu cầu
            ];

            // Cộng dồn các trạng thái đang tiến hành khác vào 'DangThucHien' hoặc để riêng
            $others = 0;
            foreach ($rawChartData as $status => $count) {
                if (!in_array($status, ['HoanThanh', 'DangThucHien', 'ChoDuyet', 'Huy'])) {
                    // Ví dụ: DaDuyet, DaNopBaoCao, DaBaoVe -> coi như đang thực hiện hoặc xử lý
                    $chartData['DangThucHien'] += $count;
                }
            }

            // 3. Lấy hoạt động gần đây (5 đồ án mới nhất được tạo)
            $sqlRecent = "SELECT p.title, p.created_at, u.full_name as lecturer_name 
                          FROM projects p 
                          JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                          JOIN users u ON l.user_id = u.user_id
                          ORDER BY p.created_at DESC LIMIT 5";
            $recentActivities = $this->pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Dashboard Error: " . $e->getMessage());
        }

        // Render view và truyền dữ liệu
        $this->render('HomeAdmin/index', [
            'stats' => $stats,
            'chartData' => $chartData,
            'recentActivities' => $recentActivities
        ]);
    }
}
