<?php
// views/home/index.php

$latestProjects = $latestProjects ?? [];
$allProjects    = $allProjects ?? [];
$statistics     = $statistics ?? ['faculties' => 0, 'classes' => 0, 'students' => 0, 'projects' => 0];

// Lấy đồ án mới nhất làm "Tiêu điểm"
$featuredProject = $latestProjects[0] ?? null;

// Kiểm tra sinh viên đã đăng nhập + đã đăng ký đồ án chưa
$isStudentLoggedIn = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$hasRegisteredProject = false;
$registeredProjectId = null;

if ($isStudentLoggedIn && isset($pdo)) {
    try {
        $userModel = new \App\Models\UserModel($pdo);
        $user = $userModel->findByAccountId($_SESSION['account_id']);
        if ($user) {
            $studentModel = new \App\Models\StudentModel($pdo);
            $student = $studentModel->findByUserId($user['user_id']);
            if ($student && !empty($student['project_id'])) {
                $hasRegisteredProject = true;
                $registeredProjectId = $student['project_id'];
            }
        }
    } catch (Exception $e) {
        error_log("Lỗi kiểm tra đăng ký đồ án: " . $e->getMessage());
    }
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="/quanlydoan/assets/css/home.css">

<section class="hero-carousel position-relative mb-5">
    <div id="mainBanner" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-inner">
            <!-- Slide 1: Giới thiệu -->
            <div class="carousel-item active">
                <div class="hero-slide d-flex align-items-center justify-content-center text-white" style="background: var(--grad-primary);">
                    <div style="position: absolute; width: 100%; height: 100%; opacity: 0.1; background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 30px 30px;"></div>
                    <div class="container position-relative z-2">
                        <div class="row justify-content-center text-center" data-aos="zoom-in">
                            <div class="col-lg-10">
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase">Nền tảng số hóa 4.0</span>
                                <h1 class="banner-title-custom">QUẢN LÝ ĐỒ ÁN & NIÊN LUẬN</h1>
                                <p class="lead mb-4 opacity-75 mx-auto" style="max-width: 700px;">
                                    Giải pháp toàn diện giúp kết nối Sinh viên và Giảng viên. <br>
                                    Quản lý tiến độ, nộp bài và chấm điểm minh bạch, hiệu quả.
                                </p>
                                <div class="d-flex justify-content-center gap-3 flex-wrap">

                                    <a href="#latest-projects" class="btn btn-light text-primary hero-btn px-4">
                                        <i class="bi bi-search me-2"></i> Xem đồ án mở đăng ký
                                    </a>
                                    <?php if ($isStudentLoggedIn): ?>
                                        <?php if (!$hasRegisteredProject): ?>
                                            <a href="/quanlydoan/project" class="btn btn-success hero-btn px-4">
                                                <i class="bi bi-box-arrow-in-right me-2"></i> Đăng ký đồ án ngay
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-light hero-btn px-4" disabled>
                                                <i class="bi bi-check2-all me-2"></i> Đã đăng ký đồ án
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-light hero-btn px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                                            <i class="bi bi-person-check me-2"></i> Đăng nhập để đăng ký
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Các slide ảnh khác -->
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('/quanlydoan/assets/images/baner1.jpg'); background-size: cover; background-position: center;">
                    <div class="hero-overlay d-flex align-items-center justify-content-center">
                        <div class="container text-center text-white" data-aos="fade-up">
                            <h2 class="banner-title-custom">Nộp báo cáo – Code trực tuyến</h2>
                            <p class="lead">Hỗ trợ tải lên tài liệu lớn, tích hợp Git repo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('/quanlydoan/assets/images/baner2.jpg'); background-size: cover; background-position: center;">
                    <div class="hero-overlay d-flex align-items-center justify-content-center">
                        <div class="container text-center text-white" data-aos="fade-up">
                            <h2 class="banner-title-custom">Theo dõi tiến độ Real-time</h2>
                            <p class="lead">Giảng viên nhận xét và đánh giá trực tiếp trên hệ thống.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#mainBanner" data-bs-slide="prev">
            <span class="carousel-control-prev-icon p-3 rounded-circle bg-dark bg-opacity-25"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainBanner" data-bs-slide="next">
            <span class="carousel-control-next-icon p-3 rounded-circle bg-dark bg-opacity-25"></span>
        </button>
    </div>
</section>

<!-- Tiêu điểm tuần này -->
<?php if ($featuredProject): ?>
    <section class="my-5 py-5" data-aos="fade-up">
        <div class="section-header text-center mb-5">
            <h3 class="section-title-custom">Tiêu Điểm Tuần Này</h3>
            <div class="section-divider"></div>
        </div>

        <div class="featured-wrapper bg-white rounded-4 shadow-lg overflow-hidden" data-aos="zoom-in">
            <div class="row g-0 align-items-center">
                <div class="col-lg-5 text-white p-5 text-center" style="background: var(--grad-primary); min-height: 380px;">
                    <div class="mb-4 p-4 rounded-circle bg-white bg-opacity-25 d-inline-block">
                        <i class="bi bi-trophy-fill fs-1 text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-2">DỰ ÁN MỚI NHẤT</h4>
                    <!-- <p class="opacity-80 small">Được giảng viên hướng dẫn uy tín</p> -->
                </div>

                <div class="col-lg-7 p-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success fs-6">#<?= $featuredProject['project_id'] ?? 'N/A' ?></span>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i> Mới đăng tải</span>
                    </div>

                    <h3 class="fw-bold text-primary mb-3">
                        <?= htmlspecialchars($featuredProject['title'] ?? 'Chưa có tiêu đề') ?>
                    </h3>

                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        <?= htmlspecialchars(strlen($featuredProject['description'] ?? '') > 200
                            ? substr($featuredProject['description'], 0, 200) . '...'
                            : ($featuredProject['description'] ?? 'Chưa có mô tả chi tiết.')) ?>
                    </p>

                    <div class="d-flex align-items-center gap-4 mb-4">
                        <div>
                            <small class="text-muted d-block">Giảng viên hướng dẫn</small>
                            <strong class="text-dark"><?= htmlspecialchars($featuredProject['lecturer_name'] ?? 'Chưa có') ?></strong>
                        </div>
                    </div>

                    <?php
                    $isFeaturedRegistered = $hasRegisteredProject && $registeredProjectId == ($featuredProject['project_id'] ?? 0);
                    $featCurrent = $featuredProject['current_students'] ?? 0;
                    $featMax = $featuredProject['max_students'] ?? 3;
                    $isFeaturedFull = $featCurrent >= $featMax;
                    ?>

                    <div class="d-flex gap-3">
                        <button onclick="showProjectDetail(<?= $featuredProject['project_id'] ?? 0 ?>)"
                            class="btn btn-primary px-4">
                            <i class="bi bi-eye me-2"></i> Xem chi tiết
                        </button>

                        <?php if ($isStudentLoggedIn): ?>
                            <?php if ($isFeaturedRegistered): ?>
                                <button class="btn btn-success px-4" disabled>
                                    Bạn đã đăng ký đồ án này
                                </button>
                            <?php elseif ($isFeaturedFull): ?>
                                <button class="btn btn-secondary px-4" disabled>Đã đủ người</button>
                            <?php else: ?>
                                <button onclick="registerProject(<?= $featuredProject['project_id'] ?>)"
                                    class="btn btn-success px-4">
                                    Đăng ký ngay
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-outline-primary px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Đăng nhập để đăng ký
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="my-5" id="latest-projects" data-aos="fade-up">
    <div class="section-header text-center mb-5">
        <h3 class="section-title-custom">Đồ án mới nhất</h3>
        <div class="section-divider"></div>
        <p class="text-muted">Những đề tài đang mở đăng ký</p>
    </div>

    <?php if (empty($latestProjects)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="mt-3 text-muted fs-5">Hiện tại chưa có đồ án nào mới.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($latestProjects as $project):
                // Logic xác định trạng thái đăng ký của User hiện tại (lấy từ logic đầu file index.php)
                $isRegistered = $hasRegisteredProject && $registeredProjectId == $project['project_id'];

                // Lấy số liệu (Dữ liệu này đã có nhờ sửa ProjectModel)
                $max = $project['max_students'] ?? 3;
                $current = $project['current_students'] ?? 0;

                // Kiểm tra đầy
                $isFull = ($current >= $max);

                // Badge trạng thái
                $statusBadge = match ($project['status']) {
                    'DaDuyet' => 'info',
                    'DangThucHien' => 'primary',
                    default => 'secondary'
                };
            ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 hover-shadow-lg transition">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-<?= $statusBadge ?>">#<?= $project['project_id'] ?></span>

                                <?php if ($isRegistered): ?>
                                    <span class="badge bg-success">Đã ĐK</span>
                                <?php elseif ($isFull): ?>
                                    <span class="badge bg-danger">Đã đủ (<?= $current ?>/<?= $max ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Còn chỗ (<?= $current ?>/<?= $max ?>)</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold text-dark" style="font-size: 1.1rem;">
                                <?= htmlspecialchars($project['title']) ?>
                            </h5>

                            <p class="text-muted small flex-grow-1">
                                <?= htmlspecialchars(substr($project['description'], 0, 80)) ?>...
                            </p>

                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted text-truncate" style="max-width: 150px;">
                                        <i class="bi bi-person-video3"></i> <?= htmlspecialchars($project['lecturer_name'] ?? 'Chưa cập nhật') ?>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="fw-bold text-primary">
                                        <i class="bi bi-people-fill"></i> <?= $current ?> / <?= $max ?> SV
                                    </small>

                                    <div>
                                        <button onclick="showProjectDetail(<?= $project['project_id'] ?>)" class="btn btn-outline-info btn-sm me-1">Xem</button>

                                        <?php if ($isStudentLoggedIn): ?>
                                            <?php if ($isRegistered): ?>
                                                <button class="btn btn-success btn-sm" disabled>Đã ĐK</button>
                                            <?php elseif ($isFull): ?>
                                                <button class="btn btn-secondary btn-sm" disabled>Full</button>
                                            <?php else: ?>
                                                <button onclick="registerProject(<?= $project['project_id'] ?>)" class="btn btn-primary btn-sm">Đăng ký</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng ký</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="/quanlydoan/project/" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-grid-3x3-gap me-2"></i> Xem tất cả đồ án
            </a>
        </div>
    <?php endif; ?>
</section>

<!-- Modal chi tiết đồ án -->
<div class="modal fade" id="projectDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalProjectTitle">Đang tải...</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="projectDetailModalBody">
                <div class="p-5 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Đang tải thông tin đồ án...</p>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">Mã đồ án: <strong id="modalProjectId">...</strong></small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true
    });

    // Counter animation
    const runCounter = () => {
        document.querySelectorAll('.counter').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const increment = target / 200;
            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    counter.textContent = target.toLocaleString('vi-VN');
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.ceil(count).toLocaleString('vi-VN');
                }
            }, 20);
        });
    };

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            runCounter();
            observer.disconnect();
        }
    }, {
        threshold: 0.5
    });

    const statsSection = document.querySelector('.stat-1');
    if (statsSection) observer.observe(statsSection);

    function showProjectDetail(projectId) {
        const modal = new bootstrap.Modal(document.getElementById('projectDetailModal'));
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');
        const bodyEl = document.getElementById('projectDetailModalBody');

        // 1. Reset trạng thái loading
        titleEl.textContent = 'Đang tải...';
        idEl.textContent = '...';
        bodyEl.innerHTML = `
        <div class="p-5 text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Đang tải dữ liệu...</p>
        </div>`;
        modal.show();

        // 2. Gọi API
        fetch(`/quanlydoan/Project/getProjectDetailsIndex/${projectId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.project) {
                    const p = data.project;
                    const members = data.members || [];

                    titleEl.textContent = p.title;
                    idEl.textContent = '#' + p.project_id;

                    // Xử lý HTML danh sách thành viên
                    let membersHtml = '';
                    if (members.length > 0) {
                        // Chuyển sang grid 2 cột nhỏ bên trong để tận dụng không gian rộng (60%)
                        membersHtml = `<div class="row g-2 mt-2">`;
                        members.forEach(mem => {
                            membersHtml += `
                            <div class="col-md-6">
                                <div class="p-2 border rounded d-flex align-items-center bg-white">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:35px; height:35px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-truncate" style="font-size: 0.9rem;">${mem.full_name}</div>
                                        <div class="text-muted small" style="font-size: 0.8rem;">${mem.mssv}</div>
                                    </div>
                                </div>
                            </div>`;
                        });
                        membersHtml += `</div>`;
                    } else {
                        membersHtml = `
                        <div class="text-center py-4 bg-white rounded-3 border border-dashed mt-2">
                            <i class="bi bi-people text-muted fs-3"></i>
                            <p class="text-muted small mb-0 mt-1">Chưa có thành viên nào đăng ký.</p>
                        </div>`;
                    }

                    // 3. Render Layout: CÒN LẠI (60%) - MÔ TẢ (40%)
                    // col-lg-7 = 60% (Cho thông tin & Thành viên)
                    // col-lg-5 = 40% (Cho Mô tả)
                    bodyEl.innerHTML = `
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="d-flex flex-column h-100 gap-3">
                            <div class="bg-light rounded-4 p-3 border-start border-4 border-info">
                                <h6 class="fw-bold text-dark mb-3 text-uppercase small ls-1">
                                    <i class="bi bi-info-circle-fill me-2 text-info"></i>Thông tin đồ án
                                </h6>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Giảng viên hướng dẫn</small>
                                        <span class="fw-bold text-dark">${p.lecturer_name}</span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Khoa chuyên môn</small>
                                        <span class="fw-bold text-dark">${p.faculty_name || 'N/A'}</span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-1">Trạng thái hiện tại</small>
                                        <span class="badge bg-${p.badge_color} px-3 py-2 rounded-pill">${p.status}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-3 flex-grow-1 border-start border-4 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase small ls-1">
                                        <i class="bi bi-people-fill me-2 text-primary"></i>Nhóm thực hiện
                                    </h6>
                                    <span class="badge bg-primary rounded-pill">${members.length} Sinh viên</span>
                                </div>
                                <hr class="my-2 opacity-25">
                                ${membersHtml}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="bg-white border rounded-4 p-4 h-100 shadow-sm position-relative overflow-hidden">
                            
                            <h5 class="fw-bold text-primary mb-3 position-relative z-1">
                                Mô tả yêu cầu
                            </h5>
                            <div class="text-dark lh-base position-relative z-1" style="text-align: justify; white-space: pre-line; font-size: 0.95rem;">
                                ${p.description ? p.description : '<em class="text-muted">Chưa có mô tả chi tiết.</em>'}
                            </div>
                        </div>
                    </div>
                </div>`;
                } else {
                    bodyEl.innerHTML = `<div class="alert alert-warning text-center">Không tìm thấy thông tin.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                bodyEl.innerHTML = `<div class="alert alert-danger text-center">Lỗi kết nối server.</div>`;
            });
    }

    // Hàm đăng ký đồ án
    function registerProject(projectId) {
        if (!confirm('Bạn có chắc chắn muốn đăng ký đồ án này?\nSau khi đăng ký KHÔNG THỂ hủy hoặc thay đổi.')) {
            return;
        }

        // Sửa đường dẫn thành /quanlydoan/Project/register (Viết hoa chữ P để khớp với tên file Controller)
        fetch('/quanlydoan/Project/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'project_id=' + projectId
            })
            .then(response => {
                // Kiểm tra nếu response không phải json
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        throw new Error('Server Error: ' + text);
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload lại trang để cập nhật trạng thái
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error('Lỗi:', err);
                alert('Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại sau.\n' + err.message);
            });
    }
</script>