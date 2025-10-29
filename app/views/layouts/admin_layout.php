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
    <title><?php echo isset($title) ? $title : 'Quản trị Đồ Án Niên Luận'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/quanlydoan/assets/css/custom.css" rel="stylesheet">
    <link href="/quanlydoan/assets/css/admin.css" rel="stylesheet">
    <link href="/quanlydoan/assets/css/sidebar.css" rel="stylesheet">


    <style>
        /* Cải thiện layout tổng thể */
        :root {
            --primary-color: #2c5aa0;
            --primary-light: #3a6bc5;
            --primary-dark: #1e3d6f;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b35;
            --text-dark: #343a40;
            --text-light: #6c757d;
            --border-color: #dee2e6;
            --sidebar-width: 290px;
            --sidebar-collapsed-width: 70px;
            --top-navbar-height: 65px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Cải thiện Top Navbar */
        .top-navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            height: var(--top-navbar-height);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1030;
        }

        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .breadcrumb {
            background: transparent;
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-item a:hover {
            color: white;
        }

        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.7);
        }

        .user-avatar {
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            border-color: white;
            transform: scale(1.05);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        /* Cải thiện Sidebar */
        .sidebar {
            background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: var(--top-navbar-height);
            left: 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1020;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .logo-container {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container img {
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .logo-container img {
            height: 40px;
            width: 40px;
        }

        .sidebar.collapsed .logo-container .fw-bold {
            display: none;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin: 0.15rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .sidebar.collapsed .dropdown-toggle::after {
            display: none;
        }

        .dropdown-menu {
            background-color: white;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .dropdown-item {
            color: var(--text-dark);
            padding: 0.5rem 1rem;
        }

        .dropdown-item.active,
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        /* Cải thiện Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--top-navbar-height);
            transition: all 0.3s ease;
            min-height: calc(100vh - var(--top-navbar-height));
            display: flex;
            flex-direction: column;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .content {
            flex: 1;
            padding: 1.5rem;
        }

        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.95rem;
        }

        /* Cải thiện Alert */
        .alert {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Footer */
        .footer {
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            padding: 2rem 0 1.5rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: auto;
            /* Đẩy footer xuống dưới cùng */
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Footer columns adjustment */
        .footer .container {
            transition: all 0.3s ease;
        }

        .main-content.expanded .footer .container {
            max-width: 95%;
            margin: 0 auto;
        }

        /* Responsive footer columns */
        .footer .row {
            transition: all 0.3s ease;
        }

        /* Adjust footer content when sidebar collapsed */
        .main-content.expanded .footer-logo img {
            max-height: 40px;
        }

        .main-content.expanded .footer-description {
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        .main-content.expanded .footer-title {
            font-size: 1rem;
        }

        .main-content.expanded .footer-links {
            font-size: 0.85rem;
        }

        .main-content.expanded .footer-links li {
            margin-bottom: 0.3rem;
        }

        .main-content.expanded .social-links {
            gap: 0.5rem;
        }

        .main-content.expanded .social-link {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }

        /* Compact footer when sidebar collapsed */
        .footer.sidebar-collapsed {
            padding: 1.5rem 0 1rem;
        }

        .footer.sidebar-collapsed .footer-logo img {
            max-height: 35px;
        }

        .footer.sidebar-collapsed .footer-description {
            font-size: 0.85rem;
        }

        .footer.sidebar-collapsed .footer-title {
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .footer.sidebar-collapsed .footer-links {
            font-size: 0.8rem;
        }

        .footer.sidebar-collapsed .footer-links li {
            margin-bottom: 0.25rem;
        }

        .footer.sidebar-collapsed .footer-bottom {
            padding-top: 1rem;
            margin-top: 1.5rem;
            font-size: 0.8rem;
        }

        .footer-logo img {
            max-height: 50px;
            width: auto;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .footer-description {
            margin-bottom: 1.5rem;
            opacity: 0.8;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            transition: all 0.3s ease;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .footer-links a {
            color: rgba(0, 0, 0, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .footer-links a:hover {
            color: rgb(255, 145, 0);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .social-link {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(0, 8, 255);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            opacity: 0.7;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .footer {
                margin-left: 0 !important;
            }

            .main-content.expanded .footer {
                margin-left: 0 !important;
            }

            .footer .row {
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }
        }


        .footer-logo img {
            max-height: 50px;
            width: auto;
            margin-bottom: 1rem;
        }

        .footer-description {
            margin-bottom: 1.5rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .footer-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: rgba(0, 0, 0, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .footer-links a:hover {
            color: rgb(255, 145, 0);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(0, 8, 255);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            opacity: 0.7;
            font-weight: 500;
        }

        /* Cải thiện Modal */
        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .page-header .row {
                flex-direction: column;
            }

            .page-header .col-auto {
                margin-top: 1rem;
            }
        }

        /* Custom scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark top-navbar shadow-sm fixed-top">

        <div class="bg-white rounded p-2 shadow-sm mb-2 mt-2">
            <img src="/quanlydoan/assets/images/sv_logo_dashboard.png" alt="Logo" style="height: 50px; width: auto;">
        </div>

        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button class="sidebar-toggle btn btn-outline-light border-0" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="d-none d-md-block ms-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/quanlydoan/HomeAdmin/index" class="text-white">Dashboard</a></li>
                    <?php if (isset($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $item): ?>
                            <li class="breadcrumb-item text-white"><?php echo $item; ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>

            <!-- User Dropdown -->
            <ul class="navbar-nav ms-auto">
                <?php
                global $pdo;
                $user = null;
                $role = null;
                $avatar = '/quanlydoan/assets/images/profile.png';

                if (isset($_SESSION['account_id']) && isset($pdo)) {
                    // Giả sử có UserModel để lấy thông tin user
                    $userModel = new \App\Models\UserModel($pdo);
                    $userData = $userModel->findByAccountId($_SESSION['account_id']);
                    if ($userData) {
                        $user = $userModel->getFullUser($userData['user_id']);
                        $role = $user['role'] ?? null;
                        $avatar = $user['avatar'] ? '/quanlydoan/assets/images/' . htmlspecialchars($user['avatar']) : $avatar;
                    }
                }
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-md-inline"><?php echo htmlspecialchars($user['full_name'] ?? 'Admin'); ?></span>
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="rounded-circle user-avatar" style="height: 36px; width: 36px; object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="/quanlydoan/admin/profile"><i class="bi bi-person me-2"></i>Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="/quanlydoan/notification/list_admin"><i class="bi bi-bell me-2"></i>Thông báo</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key me-2"></i>Đổi mật khẩu</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/quanlydoan/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo -->
        <div class="logo-container text-center">
            <a href="/quanlydoan/HomeAdmin/index" class="text-decoration-none d-flex flex-column align-items-center">

                <div class="fw-bold text-white">QL Đồ Án Niên Luận</div>
            </a>
        </div>

        <nav class="nav flex-column px-2 mt-3">
            <!-- Dashboard -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/HomeAdmin/index') !== false ? 'active' : ''; ?>" href="/quanlydoan/HomeAdmin/index">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <!-- Quản lý Tài khoản -->
            <div class="nav-item">
                <a class="nav-link 
        <?php echo strpos($_SERVER['REQUEST_URI'], '/Account/') !== false ? 'active' : ''; ?>
        " href="/quanlydoan/Account/manage">
                    <i class="bi bi-person-gear"></i>
                    <span>Quản lý Tài khoản</span>
                </a>
            </div>

            <!-- Quản lý Giảng viên -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-badge"></i>
                    <span>Quản lý Giảng viên</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Lecturer/manage"><i class="bi bi-list-ul me-2"></i>Danh sách Giảng viên</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/add') !== false ? 'active' : ''; ?>" href="/quanlydoan/Lecturer/add"><i class="bi bi-person-add me-2"></i>Thêm Giảng viên</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/statistics') !== false ? 'active' : ''; ?>" href="/quanlydoan/Lecturer/statistics"><i class="bi bi-bar-chart me-2"></i>Thống kê</a></li>
                </ul>
            </div>

            <!-- Quản lý Sinh viên -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-people-fill"></i>
                    <span>Quản lý Sinh viên</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/manage"><i class="bi bi-list-ul me-2"></i>Danh sách Sinh viên</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/add') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/add"><i class="bi bi-person-add me-2"></i>Thêm Sinh viên</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/import') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/import"><i class="bi bi-upload me-2"></i>Import Excel</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/statistics') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/statistics"><i class="bi bi-bar-chart me-2"></i>Thống kê</a></li>
                </ul>
            </div>

            <!-- Quản lý Đồ án -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-folder"></i>
                    <span>Quản lý Đồ án</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/manage"><i class="bi bi-list-task me-2"></i>Danh sách Đồ án</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/create') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/create"><i class="bi bi-plus-circle me-2"></i>Tạo Đồ án</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/categories') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/categories"><i class="bi bi-tags me-2"></i>Danh mục Đồ án</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/approval') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/approval"><i class="bi bi-check-circle me-2"></i>Duyệt Đồ án</a></li>
                </ul>
            </div>

            <!-- Quản lý Nhóm & Phân công -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Group/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-diagram-3"></i>
                    <span>Nhóm & Phân công</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Group/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Group/manage"><i class="bi bi-people me-2"></i>Quản lý Nhóm</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Group/assign') !== false ? 'active' : ''; ?>" href="/quanlydoan/Group/assign"><i class="bi bi-arrow-left-right me-2"></i>Phân công GVHD</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Group/schedule') !== false ? 'active' : ''; ?>" href="/quanlydoan/Group/schedule"><i class="bi bi-calendar-event me-2"></i>Lịch Phân công</a></li>
                </ul>
            </div>

            <!-- Theo dõi Tiến độ -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Progress/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Progress/manage">
                <i class="bi bi-graph-up"></i>
                <span>Theo dõi Tiến độ</span>
            </a>

            <!-- Đánh giá & Chấm điểm -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-star"></i>
                    <span>Đánh giá & Chấm điểm</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Evaluation/manage"><i class="bi bi-clipboard-check me-2"></i>Quản lý Đánh giá</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/criteria') !== false ? 'active' : ''; ?>" href="/quanlydoan/Evaluation/criteria"><i class="bi bi-list-stars me-2"></i>Tiêu chí Đánh giá</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/results') !== false ? 'active' : ''; ?>" href="/quanlydoan/Evaluation/results"><i class="bi bi-bar-chart me-2"></i>Kết quả Chấm điểm</a></li>
                </ul>
            </div>

            <!-- Báo cáo & Thống kê -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/') !== false ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bar-chart"></i>
                    <span>Báo cáo & Thống kê</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/dashboard') !== false ? 'active' : ''; ?>" href="/quanlydoan/Report/dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/projects') !== false ? 'active' : ''; ?>" href="/quanlydoan/Report/projects"><i class="bi bi-folder me-2"></i>Thống kê Đồ án</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/students') !== false ? 'active' : ''; ?>" href="/quanlydoan/Report/students"><i class="bi bi-people me-2"></i>Thống kê Sinh viên</a></li>
                    <li><a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/lecturers') !== false ? 'active' : ''; ?>" href="/quanlydoan/Report/lecturers"><i class="bi bi-person-badge me-2"></i>Thống kê Giảng viên</a></li>
                </ul>
            </div>

            <!-- Quản lý Thông báo -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Notification/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Notification/manage">
                <i class="bi bi-bell"></i>
                <span>Quản lý Thông báo</span>
            </a>

            <!-- Cài đặt Hệ thống -->
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/System/') !== false ? 'active' : ''; ?>" href="/quanlydoan/System/settings">
                <i class="bi bi-gear"></i>
                <span>Cài đặt Hệ thống</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <main class="content">
            <div class="container-fluid py-4">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                            <p class="page-subtitle text-muted mb-0"><?php echo isset($page_subtitle) ? $page_subtitle : 'Quản lý đồ án niên luận'; ?></p>
                        </div>
                        <div class="col-auto">
                            <?php if (isset($page_actions)): ?>
                                <div class="btn-group">
                                    <?php foreach ($page_actions as $action): ?>
                                        <a href="<?php echo $action['url']; ?>" class="btn <?php echo $action['class'] ?? 'btn-primary'; ?>">
                                            <i class="<?php echo $action['icon']; ?> me-2"></i><?php echo $action['text']; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Main Content -->
                <?php echo $content; ?>
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
                        <h4 class="footer-title">Liên kết nhanh</h4>
                        <ul class="footer-links">
                            <li><a href="/quanlydoan/HomeAdmin/index">Dashboard</a></li>
                            <li><a href="/quanlydoan/Account/manage">Quản lý Tài khoản</a></li>
                            <li><a href="/quanlydoan/Project/manage">Quản lý Đồ án</a></li>
                            <li><a href="/quanlydoan/Report/dashboard">Báo cáo & Thống kê</a></li>
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
    </div>

    <!-- Modal Đổi mật khẩu -->
    <div id="changePasswordModal" class="modal fade" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="changePasswordModalLabel">Đổi mật khẩu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                            <div class="invalid-feedback">Mật khẩu mới phải có ít nhất 6 ký tự.</div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Admin JS -->
    <script src="/quanlydoan/assets/js/admin.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Initialize components
        $(document).ready(function() {
            // Initialize DataTables
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                responsive: true
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Password change form handling
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');

                // Show loading
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                // Simulate API call - replace with actual API call
                setTimeout(() => {
                    // Hide loading
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');

                    // Show success message
                    $('#changePasswordMessage').html(
                        '<div class="alert alert-success">Đổi mật khẩu thành công!</div>'
                    );

                    // Reset form
                    form[0].reset();

                    // Hide message after 3 seconds
                    setTimeout(() => {
                        $('#changePasswordMessage').empty();
                        $('#changePasswordModal').modal('hide');
                    }, 3000);
                }, 1500);
            });

            // Mobile sidebar toggle
            if (window.innerWidth <= 768) {
                $('.sidebar-toggle').on('click', function() {
                    $('.sidebar').toggleClass('mobile-open');
                });

                // Close sidebar when clicking outside on mobile
                $(document).on('click', function(e) {
                    if ($(window).width() <= 768 &&
                        !$(e.target).closest('.sidebar').length &&
                        !$(e.target).closest('.sidebar-toggle').length) {
                        $('.sidebar').removeClass('mobile-open');
                    }
                });
            }
        });

        // Sidebar toggle function
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const footer = document.querySelector('.footer');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Force reflow để đảm bảo transition hoạt động
            footer.offsetHeight;

            // Thêm class expanded cho footer
            if (mainContent.classList.contains('expanded')) {
                footer.classList.add('sidebar-collapsed');
            } else {
                footer.classList.remove('sidebar-collapsed');
            }
        }

        // Initialize sidebar state từ localStorage (nếu có)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const footer = document.querySelector('.footer');

            // Kiểm tra trạng thái sidebar từ localStorage
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            if (isSidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                footer.classList.add('sidebar-collapsed');
            }

            // Lưu trạng thái sidebar khi toggle
            document.querySelector('.sidebar-toggle').addEventListener('click', function() {
                setTimeout(() => {
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                }, 300);
            });
        });
    </script>

</body>

</html>