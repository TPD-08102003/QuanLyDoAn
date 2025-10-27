<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Quản trị Admin'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="/quanlydoan/assets/css/custom.css" rel="stylesheet">
    <link href="/quanlydoan/assets/css/admin.css" rel="stylesheet">
    <link href="/quanlydoan/assets/css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/quanlydoan/assets/images/logo.png">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary top-navbar">
        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <!-- User Dropdown -->
            <ul class="navbar-nav ms-auto">
                <?php
                $user = isset($_SESSION['account_id']) ? (new \App\User($pdo))->getUserById($_SESSION['account_id']) : null;
                $avatar = $user && $user['avatar'] ? '/quanlydoan/assets/images/' . htmlspecialchars($user['avatar']) : '/quanlydoan/assets/images/profile.png';
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline"><?php echo htmlspecialchars($user['full_name'] ?? 'Admin'); ?></span>
                        <span class="avatar-container">
                            <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-img rounded-circle" style="height: 36px; width: 36px; object-fit: cover;">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/quanlydoan/admin/profile"><i class="bi bi-person"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="/quanlydoan/notification/list_admin"><i class="bi bi-bell"></i> Thông báo</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key"></i> Đổi mật khẩu</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/quanlydoan/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo -->
        <div class="logo-container">
            <a href="/quanlydoan/HomeAdmin/index">
                <img src="/quanlydoan/assets/images/logo.png" alt="Logo" class="rounded-circle" style="height: 60px; width: 60px; object-fit: cover;">
                <div class="mt-2 text-white">QL Đồ Án Niên Luận</div>
            </a>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/HomeAdmin/index') !== false ? 'active' : ''; ?>" href="/quanlydoan/HomeAdmin/index">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <!-- Quản lý Tài khoản -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Account/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Account/manage">
                <i class="bi bi-person-gear"></i> Quản lý Tài khoản
            </a>

            <!-- Quản lý Sinh viên -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/manage">
                <i class="bi bi-people"></i> Quản lý Sinh viên
            </a>

            <!-- Quản lý Giảng viên -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Lecturer/manage">
                <i class="bi bi-person-badge"></i> Quản lý Giảng viên
            </a>

            <!-- Quản lý Đề tài Dropdown -->
            <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '#') !== false ? 'active' : ''; ?>" href="#" role="button" onclick="toggleDropdown(this)">
                <i class="bi bi-journal-text"></i> Quản lý Đề tài
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/quanlydoan/Topic/manage"><i class="bi bi-list-check"></i> Danh sách Đề tài</a></li>
                <li><a class="dropdown-item" href="/quanlydoan/Topic/categories"><i class="bi bi-tags"></i> Phân loại Đề tài</a></li>
                <li><a class="dropdown-item" href="/quanlydoan/Topic/approval"><i class="bi bi-check-circle"></i> Duyệt Đề tài</a></li>
            </ul>

            <!-- Quản lý Đồ án -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/manage">
                <i class="bi bi-folder"></i> Quản lý Đồ án
            </a>

            <!-- Phân công Dropdown -->
            <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Assignment/') !== false ? 'active' : ''; ?>" href="#" role="button" onclick="toggleDropdown(this)">
                <i class="bi bi-diagram-3"></i> Phân công
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/quanlydoan/Assignment/manage"><i class="bi bi-arrow-left-right"></i> Phân công Đồ án</a></li>
                <li><a class="dropdown-item" href="/quanlydoan/Assignment/groups"><i class="bi bi-people-fill"></i> Quản lý Nhóm</a></li>
                <li><a class="dropdown-item" href="/quanlydoan/Assignment/schedule"><i class="bi bi-calendar-event"></i> Lịch Phân công</a></li>
            </ul>

            <!-- Đánh giá & Chấm điểm -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Evaluation/manage">
                <i class="bi bi-star"></i> Đánh giá & Chấm điểm
            </a>

            <!-- Báo cáo & Thống kê -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Report/dashboard">
                <i class="bi bi-bar-chart"></i> Báo cáo & Thống kê
            </a>

            <!-- Cài đặt hệ thống -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/System/') !== false ? 'active' : ''; ?>" href="/quanlydoan/System/settings">
                <i class="bi bi-gear"></i> Cài đặt Hệ thống
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <main class="content">
            <div class="container py-5">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-logo">
                        <img src="assets/images/sv_logo_dashboard.png" alt="Logo">

                    </div>
                    <p class="footer-description">Hệ thống quản lý đồ án toàn diện dành cho sinh viên và giảng viên trong việc quản lý, theo dõi và đánh giá đồ án học phần.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">Liên kết</h4>
                    <ul class="footer-links">
                        <li><a href="">Trang chủ</a></li>
                        <li><a href="/project">Dự án</a></li>
                        <li><a href="/group">Nhóm</a></li>
                        <li><a href="/about">Giới thiệu</a></li>
                        <li><a href="/contact">Liên hệ</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">Hỗ trợ</h4>
                    <ul class="footer-links">
                        <li><a href="/help">Trung tâm trợ giúp</a></li>
                        <li><a href="/faq">Câu hỏi thường gặp</a></li>
                        <li><a href="/tutorial">Hướng dẫn sử dụng</a></li>
                        <li><a href="/policy">Chính sách bảo mật</a></li>
                        <li><a href="/terms">Điều khoản sử dụng</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4">
                    <h4 class="footer-title">Liên hệ</h4>
                    <ul class="footer-links">
                        <li><i class="bi bi-geo-alt me-2"></i> Đường Phạm Hữu Lầu, Phường Cao Lãnh, Đồng Tháp</li>
                        <li><i class="bi bi-telephone me-2"></i> (0123) 456 789</li>
                        <li><i class="bi bi-envelope me-2"></i> contact@ql-da.edu.vn</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Hệ thống Quản lý Đồ Án.</p>
            </div>
        </div>
    </footer>



    <!-- Modal Đổi mật khẩu -->
    <div id="changePasswordModal" class="modal fade" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="changePasswordModalLabel">Đổi mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="changePasswordMessage"></div>
                    <form id="changePasswordForm" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu hiện tại.</div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu mới.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmNewPassword" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required>
                            <div class="invalid-feedback">Vui lòng xác nhận mật khẩu mới.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <!-- Admin JS -->
    <script src="/quanlydoan/assets/js/admin.js"></script>
</body>

</html>