<title>Về Hệ Thống QLDA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
    :root {
        --primary: #0055ff;
        /* Xanh sapphire đậm */
        --secondary: #6366f1;
        /* Tím indigo đậm */
        --accent: #ec4899;
        /* Hồng magenta đậm */
        --success: #10b981;
        --dark: #1e293b;
    }

    .hero-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 60%, var(--accent) 100%);
        padding: 110px 0;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url('https://assets.codepen.io/3/kiwi.svg') repeat;
        opacity: 0.1;
        animation: float 18s infinite linear;
    }

    @keyframes float {
        from {
            transform: translateY(0);
        }

        to {
            transform: translateY(-100%);
        }
    }

    .gradient-text {
        background: linear-gradient(90deg, #00f5ff, #ff00ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 900;
    }

    .feature-icon {
        animation: pulse 2.5s infinite;
        background: linear-gradient(135deg, var(--primary), var(--accent));
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }
    }

    .feature-card {
        transition: all 0.4s ease;
        border-radius: 16px;
        overflow: hidden;
    }

    .feature-card:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    }
</style>



<!-- Hero -->
<section class="hero-section text-center" data-aos="zoom-in">
    <div class="container position-relative">
        <h1 class="display-4 fw-bold mb-4 gradient-text" data-aos="fade-up" data-aos-delay="100">
            HỆ THỐNG QUẢN LÝ ĐỒ ÁN
        </h1>
        <p class="fs-4 fw-light" data-aos="fade-up" data-aos-delay="300">
            Giải pháp toàn diện – Minh bạch – Tự động – Hiệu quả cao nhất cho trường đại học
        </p>
    </div>
</section>

<div class="container my-5">

    <!-- Sứ mệnh & Tầm nhìn -->
    <div class="row g-5 mb-5">
        <div class="col-lg-6" data-aos="fade-right">
            <h2 class="display-6 fw-bold text-primary">Sứ mệnh</h2>
            <p class="fs-5 text-muted mt-3">
                Số hóa 100 % quy trình đồ án từ đăng ký đề tài → nộp báo cáo → chấm điểm → lưu trữ, loại bỏ hoàn toàn giấy tờ và email thủ công, giúp giảng viên và sinh viên tập trung vào chất lượng nghiên cứu thay vì thủ tục hành chính.
            </p>
            <p class="fs-5">Hệ thống được xây dựng từ thực tế đau điểm của hơn 15 trường đại học tại Việt Nam trong 5 năm qua.</p>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <h2 class="display-6 fw-bold text-primary">Tầm nhìn</h2>
            <p class="fs-5 text-muted mt-3">
                Trở thành nền tảng quản lý đồ án chuẩn quốc gia, tích hợp AI đánh giá tiến độ tự động và gợi ý đề tài phù hợp với năng lực sinh viên vào năm 2027.
            </p>
            <p class="fs-5">Mục tiêu: 10.000+ đồ án được quản lý mỗi năm, giảm 80 % thời gian hành chính cho giảng viên.</p>
        </div>
    </div>

    <!-- Tính năng nổi bật (4 card) -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="display-5 fw-bold gradient-text">Tính năng nổi bật</h2>
        <p class="fs-5 text-muted">Thiết kế riêng cho từng đối tượng người dùng</p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
            <div class="card feature-card h-100 shadow">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon text-white rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-mortarboard fs-1"></i>
                    </div>
                    <h5 class="fw-bold fs-4">Sinh viên</h5>
                    <p class="text-muted small">Tìm đề tài bằng từ khóa & bộ lọc chuyên ngành → Đăng ký 1-click → Nộp báo cáo tuần → Chat trực tiếp với GVHD → Nhận thông báo deadline → Xem lịch sử đồ án cá nhân</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
            <div class="card feature-card h-100 shadow">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon text-white rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #0055ff);">
                        <i class="bi bi-person-workspace fs-1"></i>
                    </div>
                    <h5 class="fw-bold fs-4">Giảng viên</h5>
                    <p class="text-muted small">Đăng đề tài → Duyệt sinh viên → Chấm điểm rubric → Theo dõi biểu đồ tiến độ → Gửi phản hồi file → Xuất danh sách nhóm → Thông báo tự động</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="300">
            <div class="card feature-card h-100 shadow">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon text-white rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent), var(--secondary));">
                        <i class="bi bi-shield-lock fs-1"></i>
                    </div>
                    <h5 class="fw-bold fs-4">Quản trị viên</h5>
                    <p class="text-muted small">Phân quyền linh hoạt → Quản lý khoa/ngành → Theo dõi toàn trường → Xuất báo cáo Excel/PDF → Backup dữ liệu tự động → Tích hợp LDAP/SSO</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="400">
            <div class="card feature-card h-100 shadow">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon text-white rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ef4444);">
                        <i class="bi bi-graph-up-arrow fs-1"></i>
                    </div>
                    <h5 class="fw-bold fs-4">Thống kê & AI</h5>
                    <p class="text-muted small">Dashboard thời gian thực → Báo cáo tỷ lệ hoàn thành → Dự đoán rủi ro trễ deadline bằng AI → Xuất báo cáo khoa/hội đồng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng lợi ích cụ thể -->
    <div class="mt-5" data-aos="fade-up">
        <h2 class="display-5 fw-bold text-center gradient-text mb-4">Lợi ích thực tế</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Đối tượng</th>
                        <th>Trước khi dùng QLDA</th>
                        <th>Sau khi dùng QLDA</th>
                        <th>Tiết kiệm</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sinh viên</td>
                        <td>Email + giấy → dễ quên deadline</td>
                        <td>Thông báo tự động + dashboard cá nhân</td>
                        <td>70 % thời gian hành chính</td>
                    </tr>
                    <tr>
                        <td>Giảng viên</td>
                        <td>Quản lý 50–100 sinh viên bằng Excel</td>
                        <td>Dashboard trực quan + chấm điểm 1-click</td>
                        <td>85 % thời gian</td>
                    </tr>
                    <tr>
                        <td>Nhà trường</td>
                        <td>Báo cáo thủ công cuối kỳ</td>
                        <td>Báo cáo realtime + xuất file tức thì</td>
                        <td>90 % công sức</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Công nghệ -->
    <div class="row mt-5 g-5" data-aos="fade-up">
        <div class="col-lg-6">
            <h3 class="fw-bold text-primary">Công nghệ sử dụng</h3>
            <ul class="list-unstyled fs-5">
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Backend: Laravel 11 + MySQL</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Frontend: Bootstrap 5 + Livewire + Alpine.js</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Hosting: VPS riêng hoặc Cloud (tùy chọn) AWS / Azure</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Bảo mật: HTTPS, 2FA, mã hóa dữ liệu</li>
            </ul>
        </div>
        <div class="col-lg-6 text-center">
            <img src="https://via.placeholder.com/600x400/0055ff/ffffff?text=QLDA+Architecture" alt="Architecture" class="img-fluid rounded-4 shadow">
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>