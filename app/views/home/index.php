<?php
// views/home/index.php

// Kiểm tra biến đã được truyền từ Controller
if (!isset($allProjects)) $allProjects = [];
if (!isset($latestProjects)) $latestProjects = [];
if (!isset($statistics)) $statistics = ['faculties' => 0, 'classes' => 0, 'students' => 0, 'projects' => 0];

?>

<link rel="stylesheet" href="/quanlydoan/assets/css/home.css">

<div class="container mt-4">

    <section class="hero-section position-relative mb-5">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php if (!empty($latestProjects)): ?>
                    <?php $i = 0; ?>
                    <?php foreach ($latestProjects as $project): ?>
                        <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                            <img src="/quanlydoan/assets/images/baner<?php echo ($i % 2) + 1; ?>.jpg" class="d-block w-100 banner-img" alt="Banner <?php echo $i + 1; ?>">
                            <div class="banner-overlay text-start">
                                <div class="container">
                                    <h1 class="banner-title display-4 fw-bold"><?php echo htmlspecialchars($project['title']); ?></h1>
                                    <p class="banner-desc lead mb-4"><?php echo htmlspecialchars($project['description']); ?></p>
                                    <button
                                        class="btn btn-warning btn-lg me-2"
                                        onclick="showProjectDetail(<?php echo $project['project_id']; ?>)">
                                        <i class="bi bi-eye me-2"></i> Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="carousel-item active">
                        <img src="/quanlydoan/assets/images/baner1.jpg" class="d-block w-100 banner-img" alt="Banner Mặc định">
                        <div class="banner-overlay text-start">
                            <div class="container">
                                <h1 class="banner-title display-4 fw-bold">Hệ thống Quản lý Đồ Án</h1>
                                <p class="banner-desc lead mb-4">Giúp sinh viên và giảng viên theo dõi tiến độ và quản lý đồ án một cách hiệu quả nhất.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <h3 class="section-title text-center mt-5 mb-4">Thống Kê Tổng Quan</h3>
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-primary text-white shadow-sm">
                <i class="bi bi-buildings-fill me-3"></i>
                <div>
                    <h5>Khoa</h5>
                    <h3><?php echo number_format($statistics['faculties']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-info text-white shadow-sm">
                <i class="bi bi-journals me-3"></i>
                <div>
                    <h5>Lớp Học</h5>
                    <h3><?php echo number_format($statistics['classes']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-warning text-white shadow-sm">
                <i class="bi bi-people-fill me-3"></i>
                <div>
                    <h5>Sinh Viên</h5>
                    <h3><?php echo number_format($statistics['students']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-success text-white shadow-sm">
                <i class="bi bi-journal-code me-3"></i>
                <div>
                    <h5>Đồ Án</h5>
                    <h3><?php echo number_format($statistics['projects']); ?></h3>
                </div>
            </div>
        </div>
    </div>


    <h3 class="section-title text-center mt-5 mb-4">Danh Sách Đồ Án Tiêu Biểu</h3>
    <div class="row" id="project-list-container">
        <?php if (!empty($allProjects)): ?>
            <?php foreach ($allProjects as $project): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="project-card shadow-sm h-100">
                        <div class="card-body">
                            <?php
                            $badge_class = 'primary';
                            if ($project['status'] === 'Đã hoàn thành') $badge_class = 'success';
                            elseif ($project['status'] === 'Đang thực hiện') $badge_class = 'warning';
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?> mb-3">
                                <?php echo htmlspecialchars($project['status']); ?>
                            </span>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text text-muted mb-3">GVHD: <?php echo htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công'); ?></p>



                            <button
                                class="btn btn-primary btn-sm mt-2 view-detail-btn"
                                data-project-id="<?php echo $project['project_id']; ?>"
                                onclick="showProjectDetail(<?php echo $project['project_id']; ?>)">
                                <i class="bi bi-eye me-1"></i> Xem chi tiết
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">Không có đồ án nào được tìm thấy.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="projectDetailModal" tabindex="-1" aria-labelledby="projectDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="projectDetailModalLabel">Chi tiết đồ án</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" id="projectDetailModalBody">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải chi tiết đồ án...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Hàm gọi API để lấy chi tiết đồ án và hiển thị trong Modal
     * @param {number} projectId ID của đồ án cần xem chi tiết
     */
    function showProjectDetail(projectId) {
        const modalElement = document.getElementById('projectDetailModal');
        const modalBody = document.getElementById('projectDetailModalBody');
        const modalTitle = document.getElementById('projectDetailModalLabel');
        const projectModal = new bootstrap.Modal(modalElement);

        // 1. Hiển thị loading state và mở modal
        modalTitle.textContent = 'Đang tải...';
        modalBody.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Đang tải chi tiết đồ án...</p>
            </div>
        `;
        projectModal.show();

        // 2. Gọi API để lấy chi tiết đồ án (Giả định URL API là /Project/getProjectDetails/{id})
        fetch(`/quanlydoan/Project/getProjectDetailsIndex/${projectId}`)
            .then(response => {
                if (!response.ok) {
                    // Xử lý lỗi HTTP (ví dụ: 404 Not Found)
                    return response.json().then(error => {
                        throw new Error(error.message || 'Lỗi mạng hoặc không tìm thấy API.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.project) {
                    const p = data.project;

                    // Xác định màu badge
                    let badgeClass = 'primary';
                    if (p.status === 'Đã hoàn thành') badgeClass = 'success';
                    else if (p.status === 'Đang thực hiện') badgeClass = 'warning';

                    // 3. Cập nhật nội dung Modal
                    modalTitle.textContent = p.title;
                    modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-8">
                                <div class="project-details-container">
                                    <div class="project-detail-item border-bottom pb-2 mb-3">
                                        <h6><i class="bi bi-person-badge me-2 text-info"></i> Giảng viên Hướng dẫn</h6>
                                        <p class="mb-0 text-dark">${p.lecturer_name || 'Chưa phân công'}</p>
                                    </div>
                                    <div class="project-detail-item border-bottom pb-2 mb-3">
                                        <h6><i class="bi bi-building-fill me-2 text-info"></i> Khoa</h6>
                                        <p class="mb-0 text-dark">${p.faculty_name || 'Mặc định'}</p>
                                    </div>
                                    <div class="project-detail-item border-bottom pb-2 mb-3">
                                        <h6><i class="bi bi-clock-history me-2 text-info"></i> Trạng thái</h6>
                                        <p class="mb-0"><span class="badge bg-${badgeClass}">${p.status}</span></p>
                                    </div>
                                    <div class="project-detail-item border-bottom pb-2 mb-3">
                                        <h6><i class="bi bi-calendar-event me-2 text-info"></i> Ngày đăng ký</h6>
                                        <p class="mb-0 text-dark">${p.created_at_formatted}</p>
                                    </div>
                                    <div class="project-detail-item">
                                        <h6><i class="bi bi-file-text me-2 text-info"></i> Mô tả chi tiết</h6>
                                        <div class="project-description p-3 border rounded bg-light">
                                            <p class="mb-0 text-dark">${p.description.replace(/\n/g, '<br>') || 'Không có mô tả chi tiết.'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-center border-start">
                                <i class="bi bi-journal-code text-primary mb-3" style="font-size: 4rem;"></i>
                                <p class="text-muted">Mã đồ án:</p>
                                <h4 class="fw-bold text-primary">#${p.project_id}</h4>
                            </div>
                        </div>
                    `;
                } else {
                    modalTitle.textContent = 'Lỗi Tải Dữ Liệu';
                    modalBody.innerHTML = `<p class="alert alert-danger text-center">${data.message || 'Không tìm thấy chi tiết đồ án.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Lỗi khi fetch chi tiết đồ án:', error);
                modalTitle.textContent = 'Lỗi Hệ Thống';
                modalBody.innerHTML = `<p class="alert alert-danger text-center">Đã xảy ra lỗi khi kết nối đến máy chủ. Vui lòng thử lại sau. (${error.message})</p>`;
            });
    }
</script>