<div class="container mt-4">
    <!-- Welcome Card -->
    <div class="welcome-card mb-5">
        <div class="card-body text-center">
            <h2 class="card-title mb-3">Chào mừng đến với Hệ thống Quản lý Đồ án Sinh viên</h2>
            <p class="card-text">Hệ thống này giúp sinh viên và giảng viên quản lý các đồ án một cách hiệu quả, từ việc tạo nhóm, theo dõi tiến độ đến nộp báo cáo và nhận phản hồi.</p>
        </div>
    </div>

    <!-- Features Section -->
    <h3 class="section-title">Chức năng chính</h3>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="card-body p-4">
                    <div class="card-icon">
                        <i class="bi bi-kanban"></i>
                    </div>
                    <h5 class="card-title">Quản lý Dự án</h5>
                    <p class="card-text">Theo dõi tiến độ, quản lý thông tin và tài liệu của các đồ án đang thực hiện.</p>
                    <a href="/quanlydoan/project" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i> Xem dự án
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="card-body p-4">
                    <div class="card-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="card-title">Quản lý Nhóm</h5>
                    <p class="card-text">Tạo và quản lý nhóm sinh viên, phân công công việc và theo dõi đóng góp.</p>
                    <a href="/quanlydoan/group" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i> Xem nhóm
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="card-body p-4">
                    <div class="card-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h5 class="card-title">Báo cáo & Đánh giá</h5>
                    <p class="card-text">Nộp báo cáo tiến độ và xem phản hồi, đánh giá từ giảng viên hướng dẫn.</p>
                    <a href="/quanlydoan/report" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i> Xem báo cáo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Features -->
    <h3 class="section-title mt-5">Tính năng khác</h3>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="feature-card">
                <div class="card-body p-4">
                    <div class="card-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="card-title">Lịch trình & Deadline</h5>
                    <p class="card-text">Theo dõi lịch trình nộp báo cáo và các deadline quan trọng trong quá trình thực hiện đồ án.</p>
                    <a href="/quanlydoan/schedule" class="btn btn-outline-primary">
                        <i class="bi bi-calendar3 me-1"></i> Xem lịch
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="feature-card">
                <div class="card-body p-4">
                    <div class="card-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <h5 class="card-title">Hỗ trợ & Tài liệu</h5>
                    <p class="card-text">Truy cập tài liệu hướng dẫn và nhận hỗ trợ từ giảng viên khi gặp khó khăn.</p>
                    <a href="/quanlydoan/support" class="btn btn-outline-primary">
                        <i class="bi bi-question-circle me-1"></i> Nhận hỗ trợ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --accent-color: #4895ef;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
    }

    .welcome-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .welcome-card .card-body {
        padding: 2rem;
    }

    .feature-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border: none;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }

    .card-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .card-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 1rem;
    }

    .card-text {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
    }

    .btn-outline-primary {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        border-radius: 8px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
    }

    .section-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border-radius: 3px;
    }
</style>