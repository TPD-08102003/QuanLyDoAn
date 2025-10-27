<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//kiểm tra nếu người dùng là admin thì chuyển hướng về trang admin
if (isset($_SESSION['account_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_URI'] !== '/quanlydoan/HomeAdmin/index') {
        header('Location: /quanlydoan/HomeAdmin/index');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/user_layout.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="">
                <img src="assets/images/sv_logo_dashboard.png" alt="Logo">
                <!-- <span>Quản lý Đồ Án</span> -->
            </a>

            <!--Mobile Toggle-->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <?php
                    $user = isset($_SESSION['account_id']) ? (new \App\User($pdo))->getUserById($_SESSION['account_id']) : null;
                    $role = $user ? $user['role'] : null;
                    $avatar = $user && $user['avatar'] ? '/quanlydoan/assets/images/' . htmlspecialchars($user['avatar']) : '/quanlydoan/assets/images/profile.png';
                    ?>

                    <li class="nav-item">
                        <a class="nav-link active" href="">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/project">Dự án</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/group">Nhóm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Liên hệ</a>
                    </li>

                    <?php if ($user): ?>
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown ms-lg-2">
                            <div class="d-flex align-items-center">
                                <a class="nav-link dropdown-toggle d-flex align-items-center pe-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="avatar-container position-relative me-2">
                                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-img rounded-circle">
                                        <span class="avatar-status"></span>
                                    </div>
                                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="/quanlydoan/user/profile"><i class="bi bi-person me-2"></i> Hồ sơ</a></li>
                                    <li><a class="dropdown-item" href="/quanlydoan/notification/list"><i class="bi bi-bell me-2"></i> Thông báo</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="/quanlydoan/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php else: ?>
                        <!-- Guest Actions Dropdown -->
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar-container position-relative me-2">
                                    <img src="/quanlydoan/assets/images/profile.png" alt="Avatar" class="avatar-img rounded-circle">
                                </div>
                                <span class="user-name">Tài khoản</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="bi bi-person-plus me-2"></i> Đăng ký</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal"><i class="bi bi-question-circle me-2"></i> Quên mật khẩu</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content flex-grow-1 py-4">
        <div class="container-fluid">
            <?php echo $content; ?>
        </div>
    </main>

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

    <!-- Modal Đăng nhập -->
    <div id="loginModal" class="modal fade" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="loginModalLabel">Đăng nhập</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginMessage"></div>
                    <form id="loginForm" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Tên đăng nhập hoặc Email</label>
                            <input type="text" class="form-control" id="loginUsername" name="username" required>
                            <div class="invalid-feedback">Vui lòng nhập tên đăng nhập hoặc email.</div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Đăng nhập
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Đăng ký tài khoản</button>
                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Quên mật khẩu?</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</php>