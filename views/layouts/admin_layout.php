<?php
// views/layouts/admin_layout.php
// Layout cho trang admin trong hệ thống Quản lý Đồ Án

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Bảng điều khiển Quản trị - Quản lý Đồ Án'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/assets/images/logo.png">
    <style>
        /* Custom styles for admin sidebar and layout */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .top-navbar {
            z-index: 999;
        }

        .avatar-img {
            height: 36px;
            width: 36px;
            object-fit: cover;
        }

        .nav-link.active {
            background-color: #495057;
        }

        .dropdown-menu {
            position: static;
            float: none;
            width: 100%;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary top-navbar">
        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button class="navbar-toggler sidebar-toggle" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <!-- Brand -->
            <a class="navbar-brand ms-2" href="/home-admin">Quản lý Đồ Án Admin</a>
            <!-- User Dropdown -->
            <ul class="navbar-nav ms-auto">
                <?php
                $full_name = $_SESSION['full_name'] ?? 'Admin';
                $avatar = '/assets/images/profile.png';
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline"><?php echo htmlspecialchars($full_name); ?></span>
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-img rounded-circle ms-1">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/account/profile"><i class="bi bi-person"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key"></i> Đổi mật khẩu</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/account/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Logo -->
        <div class="logo-container p-3 text-center">
            <img src="/assets/images/logo.png" alt="Logo" class="rounded-circle mb-2" style="height: 60px; width: 60px; object-fit: cover;">
            <div class="text-white">Admin Panel</div>
        </div>
        <nav class="nav flex-column px-2">
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/home-admin') !== false) ? 'active' : ''; ?>" href="/home-admin">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/account') !== false) ? 'active' : ''; ?>" href="/account">
                <i class="bi bi-people me-2"></i> Quản lý tài khoản
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/user') !== false) ? 'active' : ''; ?>" href="/user">
                <i class="bi bi-person-badge me-2"></i> Quản lý người dùng
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/student') !== false) ? 'active' : ''; ?>" href="/student">
                <i class="bi bi-mortarboard me-2"></i> Quản lý sinh viên
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/lecturer') !== false) ? 'active' : ''; ?>" href="/lecturer">
                <i class="bi bi-easel me-2"></i> Quản lý giảng viên
            </a>
            <!-- Dự án Dropdown -->
            <a class="nav-link dropdown-toggle <?php echo (strpos($_SERVER['REQUEST_URI'], '/project') !== false) ? 'active' : ''; ?>" href="#" role="button" onclick="toggleDropdown(this)">
                <i class="bi bi-diagram-3 me-2"></i> Quản lý dự án
            </a>
            <ul class="dropdown-menu show" id="projectDropdown">
                <li><a class="dropdown-item ps-4" href="/project"><i class="bi bi-list-ul me-2"></i> Danh sách dự án</a></li>
                <li><a class="dropdown-item ps-4" href="/project/create"><i class="bi bi-plus-circle me-2"></i> Thêm dự án</a></li>
            </ul>
            <!-- Nhóm Dropdown -->
            <a class="nav-link dropdown-toggle <?php echo (strpos($_SERVER['REQUEST_URI'], '/group') !== false) ? 'active' : ''; ?>" href="#" role="button" onclick="toggleDropdown(this)">
                <i class="bi bi-people me-2"></i> Quản lý nhóm
            </a>
            <ul class="dropdown-menu show" id="groupDropdown">
                <li><a class="dropdown-item ps-4" href="/group"><i class="bi bi-list-ul me-2"></i> Danh sách nhóm</a></li>
                <li><a class="dropdown-item ps-4" href="/group-member"><i class="bi bi-person-plus me-2"></i> Thành viên nhóm</a></li>
            </ul>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/report') !== false) ? 'active' : ''; ?>" href="/report">
                <i class="bi bi-file-earmark-text me-2"></i> Quản lý báo cáo
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/feedback') !== false) ? 'active' : ''; ?>" href="/feedback">
                <i class="bi bi-chat-dots me-2"></i> Quản lý nhận xét
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <main class="content py-4">
            <div class="container-fluid">
                <?php echo $content; ?>
            </div>
        </main>

        <!-- Footer (tương tự user layout) -->
        <footer class="bg-dark text-white py-4 mt-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p>&copy; 2025 Quản lý Đồ Án Admin. Tất cả quyền được bảo lưu.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="/admin/contact" class="text-white me-3">Hỗ trợ</a>
                        <a href="/admin/logs" class="text-white">Nhật ký hệ thống</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modal Đổi mật khẩu -->
    <div id="changePasswordModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Đổi mật khẩu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="/account/change-password" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }

        function toggleDropdown(element) {
            const dropdown = element.nextElementSibling;
            dropdown.classList.toggle('show');
        }
    </script>
</body>

</html>