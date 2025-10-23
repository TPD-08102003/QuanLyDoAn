<?php
$title = 'Quản Lý Đồ Án Niên Luận - Dashboard';
ob_start();
?>

<div class="dashboard-admin">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clipboard-data me-2"></i>Quản Lý Đồ Án Niên Luận
        </h1>
        <div class="text-muted">
            <i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y'); ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Students -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Sinh Viên</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalStudents ?? '1,250'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Lecturers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng Giảng Viên</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalLecturers ?? '85'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đồ Án Đang Thực Hiện</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalProjects ?? '320'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Projects -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Đồ Án Chờ Duyệt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingProjects ?? '45'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao Tác Nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/quanlydoan/Student/manage" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-people me-2"></i>Quản lý Sinh viên
                        </a>
                        <a href="/quanlydoan/Lecturer/manage" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-person-badge me-2"></i>Quản lý Giảng viên
                        </a>
                        <a href="/quanlydoan/Project/manage" class="btn btn-outline-info btn-sm text-start">
                            <i class="bi bi-folder me-2"></i>Quản lý Đề tài
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
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hoạt Động Gần Đây</h6>
                </div>
                <div class="card-body">
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng Thái Đồ Án</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tiến Độ Theo Khoa</h6>
                        </div>
                        <div class="card-body">
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
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thống Kê Đánh Giá</h6>
                        </div>
                        <div class="card-body">
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
                                    <span>Trung bình (<=7)< /span>
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

<script>
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
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    hoverBackgroundColor: [
                        '#17a673',
                        '#2c9faf',
                        '#dda20a',
                        '#be2617'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include 'layouts/admin.php';
?>