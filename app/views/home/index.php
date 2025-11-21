<?php
// views/home/index.php

// 1. KHỞI TẠO DỮ LIỆU MẶC ĐỊNH (Tránh lỗi Undefined Variable)
$latestProjects = $latestProjects ?? [];
$allProjects = $allProjects ?? [];
$statistics = $statistics ?? ['faculties' => 0, 'classes' => 0, 'students' => 0, 'projects' => 0];

// Dữ liệu giả định cho Feature Project
$featuredProject = !empty($allProjects) ? $allProjects[0] : [
    'title' => 'Hệ thống Quản lý Đồ án Tốt nghiệp (Demo)',
    'description' => 'Nghiên cứu xây dựng hệ thống hỗ trợ quản lý quy trình đăng ký, báo cáo tiến độ và chấm điểm đồ án trực tuyến. Tích hợp thông báo Email và Dashboard thống kê.',
    'status' => 'Đã hoàn thành',
    'lecturer_name' => 'PGS.TS Nguyễn Văn A',
    'project_id' => 999,
    'tags' => ['Fullstack', 'PHP Laravel', 'VueJS', 'MySQL']
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="/quanlydoan/assets/css/home.css">

<style>

</style>

<section class="hero-carousel position-relative mb-5">
    <div id="mainBanner" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero-slide d-flex align-items-center justify-content-center text-white"
                    style="background: var(--grad-primary);">
                    <div style="position: absolute; width: 100%; height: 100%; opacity: 0.1; background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 30px 30px;"></div>

                    <div class="container position-relative z-2">
                        <div class="row justify-content-center text-center" data-aos="zoom-in" data-aos-duration="1000">
                            <div class="col-lg-10">
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase">Nền tảng số hóa 4.0</span>
                                <h1 class="banner-title-custom">QUẢN LÝ ĐỒ ÁN & NIÊN LUẬN</h1>
                                <p class="lead mb-4 opacity-75 mx-auto" style="max-width: 700px;">
                                    Giải pháp toàn diện giúp kết nối Sinh viên và Giảng viên. <br>
                                    Quản lý tiến độ, nộp bài và chấm điểm minh bạch, hiệu quả.
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="#latest-projects" class="btn btn-light text-primary hero-btn"><i class="bi bi-search me-2"></i>Tra cứu đồ án</a>
                                    <button type="button" class="btn btn-outline-light hero-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập ngay</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('/quanlydoan/assets/images/baner1.jpg');">
                    <div class="hero-overlay d-flex align-items-center justify-content-center">
                        <div class="container text-center text-white" data-aos="fade-up">
                            <h2 class="banner-title-custom">Nộp báo cáo – Code trực tuyến</h2>
                            <p class="lead">Hỗ trợ tải lên tài liệu dung lượng lớn, tích hợp Git repo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('/quanlydoan/assets/images/baner2.jpg');">
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

<div class="container py-4">

    <section class="my-5" data-aos="fade-up">
        <div class="section-header text-center">
            <h2 class="section-title-custom">Thống Kê Tổng Quan</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-3" data-aos="flip-left" data-aos-delay="100">
                <div class="stat-card-modern stat-1">
                    <i class="bi bi-buildings bg-icon"></i>
                    <div class="content">
                        <div class="fs-1 fw-bold counter" data-target="<?= $statistics['faculties'] ?>">0</div>
                        <div class="fw-medium opacity-75 text-uppercase small ls-1">Khoa / Viện</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="flip-left" data-aos-delay="200">
                <div class="stat-card-modern stat-2">
                    <i class="bi bi-easel bg-icon"></i>
                    <div class="content">
                        <div class="fs-1 fw-bold counter" data-target="<?= $statistics['classes'] ?>">0</div>
                        <div class="fw-medium opacity-75 text-uppercase small ls-1">Lớp học phần</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="flip-left" data-aos-delay="300">
                <div class="stat-card-modern stat-3">
                    <i class="bi bi-people-fill bg-icon"></i>
                    <div class="content">
                        <div class="fs-1 fw-bold counter" data-target="<?= $statistics['students'] ?>">0</div>
                        <div class="fw-medium opacity-75 text-uppercase small ls-1">Sinh viên</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="flip-left" data-aos-delay="400">
                <div class="stat-card-modern stat-4">
                    <i class="bi bi-folder2-open bg-icon"></i>
                    <div class="content">
                        <div class="fs-1 fw-bold counter" data-target="<?= $statistics['projects'] ?>">0</div>
                        <div class="fw-medium opacity-75 text-uppercase small ls-1">Đồ án lưu trữ</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="my-5 py-4" data-aos="fade-up">
        <div class="section-header text-center">
            <h3 class="section-title-custom">Tiêu Điểm Tuần Này</h3>
            <div class="section-divider"></div>
        </div>

        <div class="featured-wrapper" data-aos="zoom-in" data-aos-delay="100">
            <div class="row g-0 align-items-center">
                <div class="col-lg-5 text-white p-5 d-flex flex-column justify-content-center align-items-center text-center"
                    style="background: var(--grad-primary); min-height: 350px;">
                    <div class="mb-4 p-4 rounded-circle bg-white bg-opacity-25 d-inline-block">
                        <i class="bi bi-trophy-fill fs-1 text-warning drop-shadow"></i>
                    </div>
                    <h4 class="fw-bold mb-2">DỰ ÁN MỚI NHẤT</h4>
                    <!--<p class="opacity-75 small">Được hội đồng đánh giá cao nhất</p>-->
                </div>

                <div class="col-lg-7 p-4 p-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-<?php echo $featuredProject['status'] === 'Đã hoàn thành' ? 'success' : 'warning'; ?> badge-custom">
                            <?php echo htmlspecialchars($featuredProject['status']); ?>
                        </span>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i> Cập nhật mới nhất</span>
                    </div>

                    <h3 class="fw-bold text-primary mb-3">
                        <?php echo htmlspecialchars($featuredProject['title']); ?>
                    </h3>



                    <div class="mb-4">
                        <?php if (!empty($featuredProject['tags'])): ?>
                            <?php foreach ($featuredProject['tags'] as $tag): ?>
                                <span class="badge bg-light text-dark border me-1 mb-1 fw-normal px-3 py-2">
                                    #<?php echo htmlspecialchars($tag); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <p class="small text-muted mb-1">Giảng viên hướng dẫn</p>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($featuredProject['lecturer_name'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                        <button onclick="showProjectDetail(<?php echo $featuredProject['project_id']; ?>)" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            Xem chi tiết <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php if (!empty($latestProjects)): ?>
        <section class="my-5 pt-4" id="latest-projects" data-aos="fade-up">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0 border-start border-4 border-primary ps-3 d-flex align-items-center">
                    <i class="bi bi-stars text-warning me-2 fs-4"></i> Đồ Án Mới Cập Nhật
                </h3>
            </div>

            <div class="row g-4">
                <?php
                $colorThemes = ['card-theme-blue', 'card-theme-green', 'card-theme-purple', 'card-theme-orange'];

                foreach ($latestProjects as $i => $project):
                    $themeClass = $colorThemes[$i % 4];
                    $title = htmlspecialchars($project['title'] ?? 'Chưa đặt tên');
                    $desc = $project['description'] ?? '';
                    $status = $project['status'] ?? 'ChoDuyet';

                    // 1. LOGIC CHỌN MÀU BADGE
                    $badgeClass = match ($status) {
                        'Đã hoàn thành'  => 'bg-success text-white',
                        'Đang thực hiện' => 'bg-warning text-dark',
                        'ChoDuyet'       => 'bg-secondary text-white',
                        default          => 'bg-info text-dark'
                    };

                    // 2. LOGIC CHỌN ICON TRẠNG THÁI (MỚI)
                    $statusIcon = match ($status) {
                        'Đã hoàn thành'  => 'bi-check-circle-fill',
                        'Đang thực hiện' => 'bi-hourglass-split',
                        'ChoDuyet'       => 'bi-clock-history',
                        default          => 'bi-info-circle-fill'
                    };
                ?>
                    <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <div class="project-card-modern <?= $themeClass ?>">

                            <div class="project-thumb-wrapper position-relative">
                                <?php if (!empty($project['thumbnail']) && file_exists("assets/uploads/projects/{$project['thumbnail']}")): ?>
                                    <img src="/quanlydoan/assets/uploads/projects/<?= htmlspecialchars($project['thumbnail']) ?>"
                                        alt="<?= $title ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-light text-secondary opacity-50">
                                        <i class="bi bi-journal-code" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <span class="badge card-status-badge rounded-pill <?= $badgeClass ?> fw-bold shadow-sm d-flex align-items-center">
                                    <i class="bi <?= $statusIcon ?> me-1"></i>
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </div>

                            <div class="card-body-custom">
                                <h5 class="project-title" title="<?= $title ?>">
                                    <i class="bi bi-bookmark-star-fill text-primary me-1 opacity-75 fs-6"></i>
                                    <?= $title ?>
                                </h5>

                                <p class="project-desc text-secondary">
                                    <?= strip_tags($desc) ?: 'Chưa có mô tả ngắn...' ?>
                                </p>

                                <div class="mt-auto pt-3 border-top border-black border-opacity-10">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center text-dark opacity-75 small">
                                            <div class="bg-white rounded-circle p-1 border d-flex me-2">
                                                <i class="bi bi-person-video3 text-primary"></i>
                                            </div>
                                            <span class="text-truncate fw-medium" style="max-width: 200px;">
                                                <?= htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <button onclick="showProjectDetail(<?= $project['project_id'] ?>)"
                                        class="btn btn-theme-action w-100 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-eye me-2"></i> Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="my-5 py-5 px-4 rounded-4 text-center text-white position-relative overflow-hidden"
        style="background: var(--grad-dark);" data-aos="zoom-in-up">
        <div class="position-absolute top-0 start-0 p-5 bg-white opacity-10 rounded-circle" style="transform: translate(-30%, -30%); width: 200px; height: 200px;"></div>
        <div class="position-absolute bottom-0 end-0 p-5 bg-primary opacity-25 rounded-circle" style="transform: translate(30%, 30%); width: 300px; height: 300px;"></div>

        <div class="position-relative z-2">
            <h2 class="fw-bold mb-3">Sẵn sàng bắt đầu học kỳ mới?</h2>
            <p class="lead opacity-75 mb-4">Tham gia cùng hàng ngàn sinh viên và giảng viên để quản lý đồ án hiệu quả.</p>
            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-success btn-lg px-5 rounded-pill shadow-lg fw-bold" data-bs-toggle="modal" data-bs-target="#registerModal">
                    <i class="bi bi-person-plus-fill me-2"></i> Đăng ký
                </button>
                <button class="btn btn-outline-light btn-lg px-5 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Đăng nhập
                </button>
            </div>
        </div>
    </section>

</div>

<!-- Modal Chi Tiết Đồ Án (Đẹp – Hiện Đại – Responsive) -->
<div class="modal fade" id="projectDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <!-- Header với gradient động -->
            <div class="modal-header text-white position-relative overflow-hidden" id="modalHeaderBg">
                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: url('/quanlydoan/assets/images/pattern.png') repeat;"></div>
                <div class="position-relative z-2 p-4">
                    <h4 class="modal-title fw-bold mb-2" id="modalProjectTitle">Đang tải...</h4>
                    <p class="mb-0 opacity-90 small">
                        <i class="bi bi-hash"></i> Mã đồ án: <span id="modalProjectId" class="fw-bold">--</span>
                        <span class="mx-2">•</span>
                        <span id="modalProjectDate">--</span>
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 mt-3 me-4" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4 p-lg-5" id="projectDetailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                    <p class="mt-4 text-muted fs-5">Đang tải chi tiết đồ án...</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-secondary px-5 rounded-pill" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm showProjectDetail – Đã tối ưu, đẹp, đầy đủ thông tin
    function showProjectDetail(projectId) {
        const modal = new bootstrap.Modal('#projectDetailModal');
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');
        const dateEl = document.getElementById('modalProjectDate');
        const headerBg = document.getElementById('modalHeaderBg');
        const bodyEl = document.getElementById('projectDetailModalBody');

        // Reset + loading
        titleEl.textContent = 'Đang tải...';
        idEl.textContent = projectId;
        dateEl.textContent = '';
        headerBg.style.background = 'var(--gradient-primary)';
        bodyEl.innerHTML = `<div class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;"></div>
        <p class="mt-4 text-muted fs-5">Đang tải chi tiết đồ án...</p>
    </div>`;
        modal.show();

        fetch(`/quanlydoan/Project/getProjectDetailsIndex/${projectId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Không tải được');

                const p = data.project;

                // Đổi màu header theo trạng thái
                const gradients = {
                    success: 'linear-gradient(135deg, #43e97b, #38f9d7)',
                    warning: 'linear-gradient(135deg, #fa709a, #fee140)',
                    secondary: 'linear-gradient(135deg, #667eea, #764ba2)',
                    danger: 'linear-gradient(135deg, #ff6b6b, #ee525)',
                    info: 'linear-gradient(135deg, #4facfe, #00f2fe)'
                };
                headerBg.style.background = gradients[p.badge_color] || 'var(--gradient-primary)';

                // Cập nhật tiêu đề
                titleEl.textContent = p.title;
                idEl.textContent = '#' + p.project_id;
                dateEl.textContent = p.created_at_formatted;

                // Nội dung chi tiết đẹp
                bodyEl.innerHTML = `
                <div class="row g-4">
                    <!-- Cột trái: Thông tin chính -->
                    <div class="col-lg-8">
                        <div class="bg-light rounded-4 p-4 border-start border-5 border-primary">
                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-text me-2"></i> Mô tả đồ án</h5>
                            <div class="text-dark lh-lg" style="text-align: justify;">
                                ${p.description.replace(/\n/g, '<br>')}
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center bg-white rounded-3 p-3 shadow-sm">
                                    <i class="bi bi-person-badge-fill text-primary fs-3 me-3"></i>
                                    <div>
                                        <small class="text-muted">GV Hướng dẫn</small>
                                        <p class="fw-bold mb-0">${p.lecturer_name}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center bg-white rounded-3 p-3 shadow-sm">
                                    <i class="bi bi-building-fill text-info fs-3 me-3"></i>
                                    <div>
                                        <small class="text-muted">Khoa / Ngành</small>
                                        <p class="fw-bold mb-0">${p.faculty_name}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center bg-white rounded-3 p-3 shadow-sm">
                                    <i class="bi bi-clock-history text-warning fs-3 me-3"></i>
                                    <div>
                                        <small class="text-muted">Trạng thái</small>
                                        <span class="badge bg-${p.badge_color} fs-6 px-3 py-2">${p.status}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Icon lớn + mã đồ án -->
                    <div class="col-lg-4 text-center d-flex flex-column justify-content-center">
                        <div class="bg-gradient-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 140px; height: 140px; box-shadow: 0 10px 30px rgba(67,97,238,0.3);">
                            <i class="bi bi-journal-code" style="font-size: 4.5rem;"></i>
                        </div>
                        <p class="text-muted mb-2">Mã đồ án</p>
                        <h3 class="text-primary fw-bold">#${p.project_id}</h3>
                       
                    </div>
                </div>`;
            })
            .catch(err => {
                headerBg.style.background = 'linear-gradient(135deg, #ff6b6b, #ee5253)';
                titleEl.textContent = 'Lỗi tải dữ liệu';
                bodyEl.innerHTML = `<div class="alert alert-danger text-center py-4 fs-5">${err.message}</div>`;
            });
    }
</script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // 1. Khởi tạo Animation
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });

    // 2. Hiệu ứng số chạy (Counter)
    const runCounter = () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 400; // Tốc độ càng nhỏ càng nhanh

        counters.forEach(counter => {
            const animate = () => {
                const value = +counter.getAttribute('data-target');
                const data = +counter.innerText.replace(/\./g, '');
                const time = value / speed;

                if (data < value) {
                    counter.innerText = Math.ceil(data + time).toLocaleString('vi-VN');
                    setTimeout(animate, 20);
                } else {
                    counter.innerText = value.toLocaleString('vi-VN');
                }
            }
            animate();
        });
    };

    // Trigger counter khi cuộn tới
    let hasRun = false;
    const statsSection = document.querySelector('.stat-1'); // Chọn phần tử để trigger
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !hasRun) {
                runCounter();
                hasRun = true;
            }
        });
        observer.observe(statsSection);
    }

    // 3. Hàm gọi API Modal chi tiết
    function showProjectDetail(projectId) {
        const modal = new bootstrap.Modal(document.getElementById('projectDetailModal'));
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');
        const bodyEl = document.getElementById('projectDetailModalBody');

        // Reset nội dung loading
        titleEl.textContent = 'Đang tải...';
        idEl.textContent = '...';
        bodyEl.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Đang lấy dữ liệu từ server...</p>
            </div>
        `;

        modal.show();

        // Gọi API (Giả lập đường dẫn)
        fetch(`/quanlydoan/Project/getProjectDetailsIndex/${projectId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.project) {
                    const p = data.project;

                    titleEl.textContent = p.title;
                    idEl.textContent = p.project_id;

                    // Render giao diện đẹp trong Modal
                    bodyEl.innerHTML = `
                        <div class="row g-0">
                            <div class="col-md-8 p-4 p-lg-5 border-end">
                                <h6 class="fw-bold text-primary text-uppercase mb-3"><i class="bi bi-file-earmark-text me-2"></i>Mô tả dự án</h6>
                                <div class="text-muted small" style="line-height: 1.8;">
                                    ${p.description ? p.description.replace(/\n/g, '<br>') : 'Chưa có mô tả chi tiết.'}
                                </div>
                            </div>
                            <div class="col-md-4 bg-light p-4 p-lg-5">
                                <h6 class="fw-bold text-primary text-uppercase mb-3"><i class="bi bi-info-circle me-2"></i>Thông tin</h6>
                                
                                <div class="mb-4">
                                    <label class="small text-muted d-block">Trạng thái</label>
                                    <span class="badge bg-${p.status === 'Đã hoàn thành' ? 'success' : 'warning'} fs-6 px-3 py-2 rounded-pill">
                                        ${p.status}
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <label class="small text-muted d-block">Giảng viên HD</label>
                                    <span class="fw-bold text-dark">${p.lecturer_name || 'Chưa cập nhật'}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="small text-muted d-block">Sinh viên thực hiện</label>
                                    <span class="fw-bold text-dark">${p.student_name || 'Chưa cập nhật'}</span>
                                </div>

                                <div class="mb-0">
                                    <label class="small text-muted d-block">Ngày đăng ký</label>
                                    <span class="fw-medium">${p.created_at ? new Date(p.created_at).toLocaleDateString('vi-VN') : '-'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    bodyEl.innerHTML = `<div class="alert alert-warning m-4">Không tìm thấy dữ liệu đồ án này.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                bodyEl.innerHTML = `<div class="alert alert-danger m-4">Lỗi kết nối server. Vui lòng thử lại sau.</div>`;
            });
    }
</script>