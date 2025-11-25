<h1 class="mb-4 text-primary"><i class="bi bi-speedometer2 me-2"></i> Dashboard Quản trị</h1>

<div class="row g-4 mb-5">
    <div class="col-md-3 col-sm-6">
        <div class="card dashboard-card card-users shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-people card-icon text-primary"></i>
                <h5 class="card-title mt-2">Tổng Sinh Viên</h5>
                <p class="card-text display-6 fw-bold text-dark my-2">
                    <?php echo number_format($stats['students'] ?? 0); ?>
                </p>
                <a href="/quanlydoan/Student/manage" class="quick-link text-decoration-none">
                    Xem chi tiết <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card dashboard-card card-documents shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-person-badge card-icon text-success"></i>
                <h5 class="card-title mt-2">Tổng Giảng Viên</h5>
                <p class="card-text display-6 fw-bold text-dark my-2">
                    <?php echo number_format($stats['lecturers'] ?? 0); ?>
                </p>
                <a href="/quanlydoan/Lecturer/manage" class="quick-link text-decoration-none">
                    Xem chi tiết <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card dashboard-card card-courses shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-folder card-icon text-info"></i>
                <h5 class="card-title mt-2">Tổng Đồ Án</h5>
                <p class="card-text display-6 fw-bold text-dark my-2">
                    <?php echo number_format($stats['projects'] ?? 0); ?>
                </p>
                <a href="/quanlydoan/Project/manage" class="quick-link text-decoration-none">
                    Xem chi tiết <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card dashboard-card card-categories shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-people-fill card-icon text-warning"></i>
                <h5 class="card-title mt-2">Tổng Nhóm</h5>
                <p class="card-text display-6 fw-bold text-dark my-2">
                    <?php echo number_format($stats['groups'] ?? 0); ?>
                </p>
                <a href="/quanlydoan/Group/manage" class="quick-link text-decoration-none">
                    Quản lý nhóm <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-4">
        <div class="card dashboard-card shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-lightning-fill card-icon text-primary fs-4"></i>
                    <h5 class="card-title ms-2 mb-0">Thao Tác Nhanh</h5>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-grid gap-2 mt-3">
                    <a href="/quanlydoan/Project/approve" class="btn btn-outline-danger d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check-circle me-2"></i>Duyệt Đồ án mới</span>
                        <?php if (($stats['pending_projects'] ?? 0) > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $stats['pending_projects']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/quanlydoan/Lecturer/manage" class="btn btn-outline-success text-start">
                        <i class="bi bi-folder-plus me-2"></i>Quản lý Giảng Viên
                    </a>
                    <a href="/quanlydoan/Student/manage" class="btn btn-outline-primary text-start">
                        <i class="bi bi-person-plus me-2"></i>Quản lý Sinh Viên
                    </a>

                    <a href="/quanlydoan/Group/manage" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-diagram-3 me-2"></i>Quản lý Nhóm học
                    </a>

                </div>
            </div>
        </div>

        <div class="card dashboard-card shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-activity card-icon text-primary fs-4"></i>
                    <h5 class="card-title ms-2 mb-0">Đồ Án Mới Tạo</h5>
                </div>
            </div>
            <div class="card-body pt-0 mt-3">
                <div class="activity-list">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item mb-3 pb-2 border-bottom">
                                <div class="d-flex">
                                    <div class="activity-icon me-3">
                                        <i class="bi bi-plus-circle text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            <?php echo date('H:i d/m/Y', strtotime($activity['created_at'])); ?>
                                        </small>
                                        <p class="mb-0 fw-medium"><?php echo htmlspecialchars($activity['title']); ?></p>
                                        <small class="text-muted">GV: <?php echo htmlspecialchars($activity['lecturer_name']); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">Chưa có hoạt động nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card dashboard-card shadow-sm mb-4 h-100">
            <div class="card-header bg-white border-0 pt-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-pie-chart-fill card-icon text-primary fs-4"></i>
                    <h5 class="card-title ms-2 mb-0">Thống Kê Trạng Thái Đồ Án</h5>
                </div>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                    <canvas id="projectStatusChart"></canvas>
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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .card-icon {
        font-size: 1.5rem;
    }

    .quick-link {
        font-size: 0.9rem;
        transition: padding-left 0.3s ease;
    }

    .quick-link:hover {
        padding-left: 5px;
    }

    .activity-list {
        max-height: 350px;
        overflow-y: auto;
    }

    .activity-list::-webkit-scrollbar {
        width: 6px;
    }

    .activity-list::-webkit-scrollbar-thumb {
        background-color: #e9ecef;
        border-radius: 3px;
    }

    /* Đảm bảo canvas responsive tốt */
    .chart-container {
        width: 100%;
        margin: 0 auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy dữ liệu từ PHP (được truyền qua Controller)
        const chartData = <?php echo json_encode($chartData); ?>;

        const ctx = document.getElementById('projectStatusChart').getContext('2d');
        const projectStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Đã hoàn thành', 'Đang thực hiện', 'Chờ duyệt', 'Đã hủy'],
                datasets: [{
                    data: [
                        chartData.HoanThanh,
                        chartData.DangThucHien,
                        chartData.ChoDuyet,
                        chartData.Huy
                    ],
                    backgroundColor: [
                        '#1cc88a', // Success
                        '#36b9cc', // Info/Primary
                        '#f6c23e', // Warning
                        '#e74a3b' // Danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 13
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                let value = context.parsed;
                                let total = context.chart._metasets[context.datasetIndex].total;
                                let percentage = ((value / total) * 100).toFixed(1) + "%";
                                return label + value + " (" + percentage + ")";
                            }
                        }
                    }
                },
                layout: {
                    padding: 20
                }
            }
        });
    });
</script>