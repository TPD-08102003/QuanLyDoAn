<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng là admin thì chuyển hướng về trang admin
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
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Trang chủ'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/quanlydoan/assets/css/user_layout.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/quanlydoan">
                <img src="/quanlydoan/assets/images/sv_logo_dashboard.png" alt="Logo">
                <!-- <span>Quản lý Đồ Án</span> -->
            </a>

            <ul class="navbar-nav ms-auto">
                <?php
                // Giả sử $pdo được truyền từ controller hoặc global; ở đây dùng global nếu có
                global $pdo;
                $user = null;
                $role = null;
                $avatar = '/quanlydoan/assets/images/profile.png';
                if (isset($_SESSION['account_id']) && isset($pdo)) {
                    $userModel = new \App\Models\UserModel($pdo);
                    $userData = $userModel->findByAccountId($_SESSION['account_id']);
                    if ($userData) {
                        $user = $userModel->getFullUser($userData['user_id']);
                        $role = $user['role'] ?? null;
                        $avatar = $user['avatar'] ? '/quanlydoan/assets/images/' . htmlspecialchars($user['avatar']) : $avatar;
                    }
                }
                ?>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $uri === '' ? 'active' : ''; ?>" href="/quanlydoan">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($uri, 'project') === 0 ? 'active' : ''; ?>" href="/quanlydoan/project">Dự án</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($uri, 'group') === 0 ? 'active' : ''; ?>" href="/quanlydoan/group">Nhóm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($uri, 'about') === 0 ? 'active' : ''; ?>" href="/quanlydoan/about">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($uri, 'contact') === 0 ? 'active' : ''; ?>" href="/quanlydoan/contact">Liên hệ</a>
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
                                    <span class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></span>
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
                </div>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content flex-grow-1 py-4">
        <div class="container-fluid">
            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php else: ?>
                <!-- Fallback content nếu không có $content -->
                <div class="row">
                    <div class="col-12">
                        <h1>Chào mừng đến với Hệ thống Quản lý Đồ Án</h1>
                        <p>Đây là trang chủ. Hãy đăng nhập để bắt đầu quản lý đồ án của bạn.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="footer-logo">
                        <img src="/quanlydoan/assets/images/sv_logo_dashboard.png" alt="Logo">
                    </div>
                    <p class="footer-description">Hệ thống quản lý đồ án toàn diện dành cho sinh viên và giảng viên trong việc quản lý, theo dõi và đánh giá đồ án học phần.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="footer-title">Liên kết</h4>
                    <ul class="footer-links">
                        <li><a href="/quanlydoan">Trang chủ</a></li>
                        <li><a href="/quanlydoan/project">Dự án</a></li>
                        <li><a href="/quanlydoan/group">Nhóm</a></li>
                        <li><a href="/quanlydoan/about">Giới thiệu</a></li>
                        <li><a href="/quanlydoan/contact">Liên hệ</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
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
                    <form id="loginForm" method="POST" action="/quanlydoan/auth/login" class="needs-validation" novalidate>
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

    <!-- Modal Đăng ký -->
    <div id="registerModal" class="modal fade" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="registerModalLabel">Đăng ký</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="registerMessage"></div>
                    <form id="registerForm" method="POST" action="/quanlydoan/auth/register" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="registerUsername" name="username" required>
                            <div class="invalid-feedback">Tên đăng nhập phải từ 6-20 ký tự (chữ, số, _, -).</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                            <div class="invalid-feedback">Email không hợp lệ.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="registerPassword" name="password" required minlength="6">
                            <div class="invalid-feedback">Mật khẩu phải ít nhất 6 ký tự.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerFullName" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="registerFullName" name="full_name" required>
                            <div class="invalid-feedback">Họ và tên là bắt buộc.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerRole" class="form-label">Vai trò</label>
                            <select class="form-select" id="registerRole" name="role" required>
                                <option value="">Chọn vai trò</option>
                                <option value="student">Sinh viên</option>
                                <option value="teacher">Giảng viên</option>
                            </select>
                            <div class="invalid-feedback">Vui lòng chọn vai trò.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerDateOfBirth" class="form-label">Ngày sinh (YYYY-MM-DD)</label>
                            <input type="date" class="form-control" id="registerDateOfBirth" name="date_of_birth">
                        </div>
                        <div class="mb-3">
                            <label for="registerPhone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="registerPhone" name="phone_number">
                        </div>
                        <div class="mb-3">
                            <label for="registerAddress" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="registerAddress" name="address" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Đăng ký
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Đã có tài khoản? Đăng nhập</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quên mật khẩu -->
    <div id="forgotPasswordModal" class="modal fade" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Quên mật khẩu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgotMessage"></div>
                    <form id="forgotForm" method="POST" action="/quanlydoan/auth/forgotPassword" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="forgotEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="forgotEmail" name="email" required>
                            <div class="invalid-feedback">Vui lòng nhập email.</div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Gửi liên kết đặt lại
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Quay lại đăng nhập</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/quanlydoan/assets/js/index.js"></script>
</body>

</html>