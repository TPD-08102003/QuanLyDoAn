<?php
$title = 'Quản Lý Đồ Án Niên Luận - Dashboard';
ob_start();
?>

<div class="dashboard-admin">
    <!-- Page Header -->
    <h1 class="mb-4 text-primary"><i class="bi bi-speedometer2 me-2"></i> Dashboard Quản trị</h1>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <!-- Total Students -->
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-users shadow-sm">
                <div class="card-body">
                    <i class="bi bi-people card-icon"></i>
                    <h5 class="card-title">Tổng Sinh Viên</h5>
                    <p class="card-text"><?php echo $totalStudents ?? '1,250'; ?></p>
                    <a href="/quanlydoan/Student/manage" class="quick-link">Quản lý sinh viên</a>
                </div>
            </div>
        </div>

        <!-- Total Lecturers -->
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-documents shadow-sm">
                <div class="card-body">
                    <i class="bi bi-person-badge card-icon"></i>
                    <h5 class="card-title">Tổng Giảng Viên</h5>
                    <p class="card-text"><?php echo $totalLecturers ?? '85'; ?></p>
                    <a href="/quanlydoan/Lecturer/manage" class="quick-link">Quản lý giảng viên</a>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-courses shadow-sm">
                <div class="card-body">
                    <i class="bi bi-folder card-icon"></i>
                    <h5 class="card-title">Đồ Án Đang Thực Hiện</h5>
                    <p class="card-text"><?php echo $totalProjects ?? '320'; ?></p>
                    <a href="/quanlydoan/Project/manage" class="quick-link">Quản lý đồ án</a>
                </div>
            </div>
        </div>

        <!-- Pending Projects -->
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-categories shadow-sm">
                <div class="card-body">
                    <i class="bi bi-clock-history card-icon"></i>
                    <h5 class="card-title">Đồ Án Chờ Duyệt</h5>
                    <p class="card-text"><?php echo $pendingProjects ?? '45'; ?></p>
                    <a href="/quanlydoan/Topic/approval" class="quick-link">Duyệt đề tài</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row g-4 mb-5">
        <!-- Quick Actions & Recent Activities -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card dashboard-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-lightning-fill card-icon text-primary"></i>
                        <h5 class="card-title ms-2">Thao Tác Nhanh</h5>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="/quanlydoan/Student/manage" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-people me-2"></i>Quản lý Sinh viên
                        </a>
                        <a href="/quanlydoan/Lecturer/manage" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-person-badge me-2"></i>Quản lý Giảng viên
                        </a>
                        <a href="/quanlydoan/Project/manage" class="btn btn-outline-info btn-sm text-start">
                            <i class="bi bi-folder me-2"></i>Quản lý Đồ án
                        </a>
                        <a href="/quanlydoan/Topic/manage" class="btn btn-outline-warning btn-sm text-start">
                            <i class="bi bi-journal-text me-2"></i>Danh mục Đề tài
                        </a>
                        <a href="/quanlydoan/Assignment/manage" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-diagram-3 me-2"></i>Phân công Đồ án
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card dashboard-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-activity card-icon text-primary"></i>
                        <h5 class="card-title ms-2">Hoạt Động Gần Đây</h5>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item mb-3">
                            <div class="d-flex">
                                <div class="activity-icon me-3">
                                    <i class="bi bi-plus-circle text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted">10 phút trước</small>
                                    <p class="mb-0">Sinh viên mới đăng ký: Nguyễn Văn A</p>
                                </div>
                            </div>
                        </div>
                        <div class="activity-item mb-3">
                            <div class="d-flex">
                                <div class="activity-icon me-3">
                                    <i class="bi bi-check-circle text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted">1 giờ trước</small>
                                    <p class="mb-0">Đồ án "Hệ thống quản lý" đã được duyệt</p>
                                </div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="d-flex">
                                <div class="activity-icon me-3">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted">2 giờ trước</small>
                                    <p class="mb-0">3 đồ án sắp đến hạn nộp</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Progress -->
        <div class="col-lg-8">
            <!-- Project Status Chart -->
            <div class="card dashboard-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-pie-chart-fill card-icon text-primary"></i>
                        <h5 class="card-title ms-2">Trạng Thái Đồ Án</h5>
                    </div>
                    <div class="chart-container">
                        <canvas id="projectStatusChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card dashboard-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-graph-up card-icon text-primary"></i>
                                <h5 class="card-title ms-2">Tiến Độ Theo Khoa</h5>
                            </div>
                            <div class="progress-list">
                                <div class="progress-item mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Công nghệ Thông tin</span>
                                        <span>75%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 75%"></div>
                                    </div>
                                </div>
                                <div class="progress-item mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Kỹ thuật Phần mềm</span>
                                        <span>60%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div class="progress-item">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Khoa học Máy tính</span>
                                        <span>45%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 45%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-star-fill card-icon text-primary"></i>
                                <h5 class="card-title ms-2">Thống Kê Đánh Giá</h5>
                            </div>
                            <div class="rating-stats">
                                <div class="rating-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Xuất sắc (9-10)</span>
                                    <span class="badge bg-success">45</span>
                                </div>
                                <div class="rating-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Giỏi (8-9)</span>
                                    <span class="badge bg-primary">89</span>
                                </div>
                                <div class="rating-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Khá (7-8)</span>
                                    <span class="badge bg-info">120</span>
                                </div>
                                <div class="rating-item d-flex justify-content-between align-items-center">
                                    <span>Trung bình (&lt;=7)</span>
                                    <span class="badge bg-warning">66</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 12px;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .card-icon {
        font-size: 2rem;
        color: #3b82f6;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0;
    }

    .card-text {
        font-size: 2rem;
        font-weight: bold;
        color: #1a202c;
        margin: 10px 0;
    }

    .quick-link {
        font-size: 0.875rem;
        color: #6b7280;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .quick-link:hover {
        color: #3b82f6;
    }

    .activity-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        font-size: 1.2rem;
    }

    .progress {
        background-color: #f1f5f9;
    }

    .rating-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rating-item:last-child {
        border-bottom: none;
    }

    canvas {
        width: 100% !important;
    }

    .content {
        padding: 0px;
    }
</style>

<script>
    // Cấu hình chung cho Chart.js
    Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";
    Chart.defaults.font.size = 14;

    // Project Status Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('projectStatusChart').getContext('2d');
        const projectStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Đã hoàn thành', 'Đang thực hiện', 'Chờ duyệt', 'Chưa bắt đầu'],
                datasets: [{
                    data: [120, 200, 45, 55],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.7)', // #1cc88a
                        'rgba(54, 185, 204, 0.7)', // #36b9cc
                        'rgba(246, 194, 62, 0.7)', // #f6c23e
                        'rgba(231, 74, 59, 0.7)' // #e74a3b
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(54, 185, 204, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(231, 74, 59, 1)'
                    ],
                    borderWidth: 2,
                    hoverBackgroundColor: [
                        'rgba(23, 166, 115, 0.8)',
                        'rgba(44, 159, 175, 0.8)',
                        'rgba(221, 162, 10, 0.8)',
                        'rgba(190, 38, 23, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 16
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 10
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
</script>