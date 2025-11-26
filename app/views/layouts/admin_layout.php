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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css">

    <link href="/quanlydoan/assets/css/custom.css" rel="stylesheet">
    <link href="/quanlydoan/assets/css/admin.css" rel="stylesheet">

    <style>
        /* ==========================================================================
            ENHANCED MODERN THEME VARIABLES
            ========================================================================== */
        :root {
            /* Kích thước */
            --sidebar-width: 300px;
            --sidebar-collapsed-width: 85px;
            --top-navbar-height: 75px;

            /* Màu sắc mặc định (Enhanced Blue Theme) */
            --primary-gradient: linear-gradient(135deg, #4361ee 0%, #4895ef 50%, #4cc9f0 100%);
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --bg-body: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            --bg-sidebar: #ffffff;
            --text-sidebar: #525b75;
            --text-sidebar-hover: #4361ee;
            --bg-sidebar-hover: rgba(67, 97, 238, 0.08);

            /* Shadows & Border */
            --shadow-sm: 0 2px 12px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 15px 50px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
            --border-radius-sm: 12px;
        }

        /* Enhanced Theme Overrides */
        body[data-theme="purple"] {
            --primary-gradient: linear-gradient(135deg, #7209b7 0%, #b5179e 50%, #f72585 100%);
            --primary-color: #7209b7;
            --primary-light: #fcefff;
            --text-sidebar-hover: #7209b7;
            --bg-sidebar-hover: rgba(114, 9, 183, 0.08);
        }

        body[data-theme="green"] {
            --primary-gradient: linear-gradient(135deg, #2d6a4f 0%, #40916c 50%, #52b788 100%);
            --primary-color: #2d6a4f;
            --primary-light: #e8f7ee;
            --text-sidebar-hover: #2d6a4f;
            --bg-sidebar-hover: rgba(45, 106, 79, 0.08);
        }

        body[data-theme="orange"] {
            --primary-gradient: linear-gradient(135deg, #e85d04 0%, #f48c06 50%, #faa307 100%);
            --primary-color: #e85d04;
            --primary-light: #fff4e6;
            --text-sidebar-hover: #e85d04;
            --bg-sidebar-hover: rgba(232, 93, 4, 0.08);
        }

        body[data-theme="dark"] {
            --bg-sidebar: #1a1a2e;
            --text-sidebar: #b8b8d0;
            --bg-body: linear-gradient(135deg, #0f0f1a 0%, #16213e 100%);
            --text-sidebar-hover: #ffffff;
            --bg-sidebar-hover: rgba(255, 255, 255, 0.12);
            --primary-gradient: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        }

        body[data-theme="red"] {
            --primary-gradient: linear-gradient(135deg, #d00000 0%, #dc2f02 50%, #e85d04 100%);
            --primary-color: #d00000;
            --primary-light: #ffeaea;
            --text-sidebar-hover: #d00000;
            --bg-sidebar-hover: rgba(208, 0, 0, 0.08);
        }

        body[data-theme="teal"] {
            --primary-gradient: linear-gradient(135deg, #0077b6 0%, #00b4d8 50%, #90e0ef 100%);
            --primary-color: #0077b6;
            --primary-light: #e6f7ff;
            --text-sidebar-hover: #0077b6;
            --bg-sidebar-hover: rgba(0, 119, 182, 0.08);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg-body);
            overflow-x: hidden;
            transition: background 0.5s ease;
        }

        a {
            text-decoration: none;
        }

        /* ==========================================================================
            ENHANCED SIDEBAR STYLES
            ========================================================================== */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-sidebar);
            box-shadow: var(--shadow-lg);
            z-index: 1040;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Enhanced Logo Area with Animation */
        .sidebar-header {
            height: var(--top-navbar-height);
            display: flex;
            align-items: center;
            /* Cập nhật: Căn giữa logo khi ẩn nút toggle trong sidebar */
            justify-content: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            background: var(--bg-sidebar);
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0.05;
            transition: left 0.6s ease;
        }

        .sidebar-header:hover::before {
            left: 0;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            /* Đảm bảo logo luôn nằm giữa container */
            justify-content: center;
            width: 100%;
        }

        /* Logo chính (Lớn) */
        .logo-img {
            max-height: 45px;
            width: auto;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0 !important;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
            display: block;
            /* Mặc định hiện */
        }

        /* Logo thu gọn (Nhỏ) */
        .logo-collapsed {
            display: none;
            /* Mặc định ẩn */
            max-height: 40px;
            width: auto;
            border-radius: 0 !important;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .logo-text {
            font-weight: 800;
            font-size: 1.25rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.4s ease;
            white-space: nowrap;
            /* Ngăn xuống dòng */
        }

        /* Enhanced Menu Items with Ripple Effect */
        .nav-link {
            color: var(--text-sidebar);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            margin-bottom: 4px;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: var(--primary-gradient);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
            opacity: 0.1;
            z-index: 0;
        }

        .nav-link:hover::before {
            width: 100px;
            height: 100px;
        }

        .nav-link i {
            font-size: 1.3rem;
            margin-right: 1rem;
            width: 24px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }

        .nav-link span {
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            color: var(--text-sidebar-hover);
            background-color: var(--bg-sidebar-hover);
            transform: translateX(8px);
        }

        .nav-link:hover span {
            transform: translateX(3px);
        }

        .nav-link.active {
            color: #fff;
            background: var(--primary-gradient);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            border-left: 4px solid rgba(255, 255, 255, 0.3);
            transform: translateX(0);
        }

        .nav-link.active i {
            transform: scale(1.15);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .nav-link.active span {
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Dropdown Submenu */
        .sidebar .dropdown-menu {
            position: relative !important;
            transform: none !important;
            border: none;
            background: transparent;
            padding-left: 3.5rem;
            box-shadow: none;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar .dropdown-item {
            color: var(--text-sidebar);
            font-size: 0.9rem;
            padding: 0.7rem 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 2px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar .dropdown-item:hover,
        .sidebar .dropdown-item.active {
            color: var(--primary-color);
            background-color: var(--bg-sidebar-hover);
            padding-left: 1.5rem;
            font-weight: 700;
        }

        .sidebar .dropdown-item:hover::before,
        .sidebar .dropdown-item.active::before {
            transform: scaleY(1);
        }

        .sidebar .dropdown-toggle::after {
            margin-left: auto;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* Enhanced Sidebar Bottom (Theme Settings) */
        .sidebar-footer {
            padding: 1.5rem 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            background: var(--bg-sidebar);
            position: relative;
        }

        .sidebar-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-gradient);
            opacity: 0.3;
        }

        .theme-selector {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .theme-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #adb5bd;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
            display: block;
            letter-spacing: 1px;
        }

        .theme-btn {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .theme-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .theme-btn:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .theme-btn:hover::before {
            opacity: 1;
        }

        .theme-btn.active {
            border-color: #fff;
            transform: scale(1.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .theme-btn.active::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .t-blue {
            background: linear-gradient(135deg, #4361ee, #4895ef);
        }

        .t-purple {
            background: linear-gradient(135deg, #7209b7, #b5179e);
        }

        .t-green {
            background: linear-gradient(135deg, #2d6a4f, #40916c);
        }

        .t-orange {
            background: linear-gradient(135deg, #e85d04, #f48c06);
        }

        .t-red {
            background: linear-gradient(135deg, #d00000, #dc2f02);
        }

        .t-teal {
            background: linear-gradient(135deg, #0077b6, #00b4d8);
        }

        .t-dark {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
        }

        /* ==========================================================================
            ENHANCED COLLAPSED SIDEBAR
            ========================================================================== */
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .sidebar-header {
            padding: 0 0.5rem;
            justify-content: center;
        }

        /* Ẩn logo lớn và text khi thu gọn */
        .sidebar.collapsed .logo-img,
        .sidebar.collapsed .logo-text {
            display: none !important;
        }

        /* Hiện logo nhỏ khi thu gọn */
        .sidebar.collapsed .logo-collapsed {
            display: block !important;
            animation: bounceIn 0.6s ease;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .dropdown-toggle::after,
        .sidebar.collapsed .sidebar-footer .theme-title {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 1.1rem 0;
            border-radius: var(--border-radius-sm);
            margin: 0 8px 6px 8px;
            border-left: 4px solid transparent;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.4rem;
        }

        .sidebar.collapsed .sidebar-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0.5rem;
        }

        .sidebar.collapsed .theme-selector {
            gap: 8px;
        }

        .sidebar.collapsed .theme-btn {
            width: 20px;
            height: 20px;
            border-radius: 6px;
        }

        /* ==========================================================================
            ENHANCED TOP NAVBAR
            ========================================================================== */
        .top-navbar {
            height: var(--top-navbar-height);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1030;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .main-content.expanded .top-navbar {
            left: var(--sidebar-collapsed-width);
        }

        /* Style cho nút toggle trên Navbar */
        .btn-toggle-sidebar {
            color: var(--text-sidebar);
            font-size: 1.6rem;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.3s ease;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-toggle-sidebar:hover {
            color: var(--primary-color);
            background: var(--primary-light);
            transform: scale(1.1);
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: var(--text-sidebar-hover);
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        /* Enhanced User Dropdown */
        .user-avatar {
            transition: all 0.3s ease;
            border: 3px solid transparent;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .user-avatar:hover {
            border-color: var(--primary-color);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* ==========================================================================
            ENHANCED MAIN CONTENT
            ========================================================================== */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--top-navbar-height) + 20px);
            min-height: 100vh;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .content {
            flex: 1;
            padding: 0 2rem 2rem 2rem;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Card Customization */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-8px);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        /* Enhanced Footer */
        .footer {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-top: auto;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-gradient);
            opacity: 0.3;
        }

        /* Enhanced Alert Messages */
        .alert {
            border: none;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid;
            backdrop-filter: blur(10px);
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ==========================================================================
            ENHANCED RESPONSIVE DESIGN
            ========================================================================== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
                animation: slideInLeft 0.4s ease-out;
            }

            @keyframes slideInLeft {
                from {
                    transform: translateX(-100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .main-content,
            .top-navbar {
                margin-left: 0 !important;
                left: 0 !important;
                width: 100%;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                z-index: 1035;
                animation: fadeIn 0.3s ease-out;
            }

            .overlay.show {
                display: block;
            }

            .content {
                padding: 0 1rem 1rem 1rem;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Page Action Buttons Enhancement */
        .page-actions {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        .page-actions .btn {
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .page-actions .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .page-actions .btn:hover::before {
            left: 100%;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
</head>

<body class="bg-light" data-theme="blue">
    <div class="overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>

    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <a href="/quanlydoan/HomeAdmin/index" class="d-flex align-items-center text-decoration-none">
                    <img src="/quanlydoan/assets/images/sv_logo_dashboard.png" alt="Logo" class="logo-img">
                    <img src="/quanlydoan/assets/images/Logo-DThU.png" alt="Logo Small" class="logo-collapsed">
                    <span class="logo-text ms-2">QL Đồ Án</span>
                </a>
            </div>
        </div>

        <div class="sidebar-content">
            <nav class="nav flex-column">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/HomeAdmin/index') !== false ? 'active' : ''; ?>" href="/quanlydoan/HomeAdmin/index">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Account/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Account/manage">
                    <i class="bi bi-person-gear"></i>
                    <span>Quản lý Tài khoản</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Lecturer/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Lecturer/manage">
                    <i class="bi bi-person-badge"></i>
                    <span>Quản lý Giảng viên</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Student/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Student/manage">
                    <i class="bi bi-people-fill"></i>
                    <span>Quản lý Sinh viên</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Faculties/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Faculties/manage">
                    <i class="bi bi-building"></i>
                    <span>Quản lý Khoa</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Classes/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Classes/manage">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Quản lý Lớp học</span>
                </a>

                <a class="nav-link dropdown-toggle <?php echo stripos($_SERVER['REQUEST_URI'], '/project/') !== false ? 'active' : ''; ?>" href="#projectSubmenu" data-bs-toggle="collapse" role="button">
                    <i class="bi bi-folder2-open"></i>
                    <span>Quản lý Đồ án</span>
                </a>
                <div class="collapse <?php echo stripos($_SERVER['REQUEST_URI'], '/project/') !== false ? 'show' : ''; ?>" id="projectSubmenu">
                    <div class="dropdown-menu show w-100">
                        <a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/manage') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/manage">
                            <i class="bi bi-list-ul me-2"></i>Danh sách Đồ án
                        </a>
                        <a class="dropdown-item <?php echo strpos($_SERVER['REQUEST_URI'], '/Project/approve') !== false ? 'active' : ''; ?>" href="/quanlydoan/Project/approve">
                            <i class="bi bi-check-circle me-2"></i>Duyệt Đồ án
                        </a>
                    </div>
                </div>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Group/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Group/manage">
                    <i class="bi bi-diagram-3"></i>
                    <span>Quản lý Nhóm</span>
                </a>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Progress/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Progress/manage">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Theo dõi Tiến độ</span>
                </a>

                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Evaluation/') !== false ? 'active' : ''; ?>" href="#evalSubmenu" data-bs-toggle="collapse" role="button">
                    <i class="bi bi-star"></i>
                    <span>Đánh giá & Điểm</span>
                </a>
                <div class="collapse <?php echo stripos($_SERVER['REQUEST_URI'], '/Evaluation/') !== false ? 'show' : ''; ?>" id="evalSubmenu">
                    <div class="dropdown-menu show w-100">
                        <a class="dropdown-item" href="/quanlydoan/Evaluation/manage">
                            <i class="bi bi-clipboard-check me-2"></i>Quản lý Đánh giá
                        </a>
                        <a class="dropdown-item" href="/quanlydoan/Evaluation/criteria">
                            <i class="bi bi-list-stars me-2"></i>Tiêu chí Đánh giá
                        </a>
                        <a class="dropdown-item" href="/quanlydoan/Evaluation/results">
                            <i class="bi bi-bar-chart me-2"></i>Kết quả
                        </a>
                    </div>
                </div>

                <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/Report/') !== false ? 'active' : ''; ?>" href="#reportSubmenu" data-bs-toggle="collapse" role="button">
                    <i class="bi bi-pie-chart"></i>
                    <span>Báo cáo & Thống kê</span>
                </a>
                <div class="collapse <?php echo stripos($_SERVER['REQUEST_URI'], '/Report/') !== false ? 'show' : ''; ?>" id="reportSubmenu">
                    <div class="dropdown-menu show w-100">
                        <a class="dropdown-item" href="/quanlydoan/Report/dashboard">
                            <i class="bi bi-speedometer me-2"></i>Tổng quan
                        </a>
                        <a class="dropdown-item" href="/quanlydoan/Report/projects">
                            <i class="bi bi-folder me-2"></i>Thống kê Đồ án
                        </a>
                        <a class="dropdown-item" href="/quanlydoan/Report/students">
                            <i class="bi bi-people me-2"></i>Thống kê Sinh viên
                        </a>
                        <a class="dropdown-item" href="/quanlydoan/Report/lecturers">
                            <i class="bi bi-person-badge me-2"></i>Thống kê Giảng viên
                        </a>
                    </div>
                </div>

                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/Notification/') !== false ? 'active' : ''; ?>" href="/quanlydoan/Notification/manage">
                    <i class="bi bi-bell"></i>
                    <span>Thông báo</span>
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <span class="theme-title">Giao diện</span>
            <div class="theme-selector">
                <div class="theme-btn t-blue active" onclick="setTheme('blue')" title="Xanh dương"></div>
                <div class="theme-btn t-purple" onclick="setTheme('purple')" title="Tím"></div>
                <div class="theme-btn t-green" onclick="setTheme('green')" title="Xanh lá"></div>
                <div class="theme-btn t-orange" onclick="setTheme('orange')" title="Cam"></div>
                <div class="theme-btn t-red" onclick="setTheme('red')" title="Đỏ"></div>
                <div class="theme-btn t-teal" onclick="setTheme('teal')" title="Xanh ngọc"></div>
                <div class="theme-btn t-dark" onclick="setTheme('dark')" title="Tối"></div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <button class="btn-toggle-sidebar me-3 d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <button class="btn-toggle-sidebar me-3 d-none d-lg-flex" onclick="toggleSidebar()" title="Thu gọn/Mở rộng menu">
                <i class="bi bi-list"></i>
            </button>

            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/quanlydoan/HomeAdmin/index">Trang chủ</a></li>
                    <?php if (isset($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $item): ?>
                            <li class="breadcrumb-item active"><?php echo $item; ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3 d-none d-sm-block position-relative">
                    <a href="/quanlydoan/notification/list_admin" class="position-relative text-secondary p-2 rounded-circle bg-light" style="font-size: 1.3rem; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.6rem; padding: 0.25em 0.4em;">
                        </span>
                    </a>
                </li>

                <?php
                global $pdo;
                $user = null;
                $avatar = '/quanlydoan/assets/images/profile.png';
                if (isset($_SESSION['account_id']) && isset($pdo)) {
                    $userModel = new \App\Models\UserModel($pdo);
                    $userData = $userModel->findByAccountId($_SESSION['account_id']);
                    if ($userData) {
                        $user = $userModel->getFullUser($userData['user_id']);
                        $avatar = $user['avatar'] ? '/quanlydoan/assets/images/' . htmlspecialchars($user['avatar']) : $avatar;
                    }
                }
                ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center p-2 rounded-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: var(--primary-light); transition: all 0.3s ease;">
                        <div class="d-none d-md-block text-end me-3" style="line-height: 1.2;">
                            <small class="d-block fw-bold text-dark"><?php echo htmlspecialchars($user['full_name'] ?? 'Administrator'); ?></small>
                            <small class="text-muted" style="font-size: 0.75rem;">Quản trị viên</small>
                        </div>
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="rounded-circle user-avatar" style="height: 45px; width: 45px; object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="min-width: 220px; border-radius: var(--border-radius);">
                        <li>
                            <div class="px-3 py-2 text-muted border-bottom" style="font-size: 0.8rem; background: var(--primary-light);">TÀI KHOẢN</div>
                        </li>
                        <li><a class="dropdown-item py-2" href="/quanlydoan/admin/profile"><i class="bi bi-person me-2 text-primary"></i>Hồ sơ cá nhân</a></li>
                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-shield-lock me-2 text-warning"></i>Đổi mật khẩu</a></li>
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                        <li><a class="dropdown-item py-2 text-danger fw-bold" href="/quanlydoan/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <main class="content">
            <?php if (isset($page_actions)): ?>
                <div class="page-actions mb-4">
                    <div class="d-flex justify-content-end">
                        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                            <?php foreach ($page_actions as $action): ?>
                                <a href="<?php echo $action['url']; ?>" class="btn <?php echo $action['class'] ?? 'btn-primary'; ?> position-relative overflow-hidden">
                                    <i class="<?php echo $action['icon']; ?> me-2"></i><?php echo $action['text']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div class="flex-grow-1"><?php echo $_SESSION['success']; ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div class="flex-grow-1"><?php echo $_SESSION['error']; ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php echo $content; ?>
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

    <div id="changePasswordModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--border-radius); overflow: hidden;">
                <div class="modal-header border-0 text-white" style="background: var(--primary-gradient);">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="changePasswordMessage"></div>
                    <form id="changePasswordForm" method="POST" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control border-0 bg-light rounded-3" id="currentPassword" name="current_password" placeholder="Mật khẩu hiện tại" required style="padding: 1rem 1.5rem;">
                            <label for="currentPassword" class="text-muted"><i class="bi bi-key me-2"></i>Mật khẩu hiện tại</label>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu hiện tại.</div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control border-0 bg-light rounded-3" id="newPassword" name="new_password" placeholder="Mật khẩu mới" required minlength="6" style="padding: 1rem 1.5rem;">
                            <label for="newPassword" class="text-muted"><i class="bi bi-key-fill me-2"></i>Mật khẩu mới</label>
                            <div class="invalid-feedback">Mật khẩu mới phải có ít nhất 6 ký tự.</div>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control border-0 bg-light rounded-3" id="confirmNewPassword" name="confirm_new_password" placeholder="Xác nhận mật khẩu" required style="padding: 1rem 1.5rem;">
                            <label for="confirmNewPassword" class="text-muted"><i class="bi bi-shield-check me-2"></i>Xác nhận mật khẩu mới</label>
                            <div class="invalid-feedback">Vui lòng xác nhận mật khẩu mới.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm position-relative overflow-hidden border-0" style="background: var(--primary-gradient); padding: 1rem;">
                                <span class="loading-spinner d-none me-2"></span>
                                <span class="position-relative">Đổi mật khẩu</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/quanlydoan/assets/js/admin.js"></script>

    <script>
        // --- Enhanced Sidebar Logic ---
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const topNavbar = document.querySelector('.top-navbar');
            const overlay = document.getElementById('mobileOverlay');
            // Cập nhật selector để tìm nút trên navbar (nếu bạn muốn thay đổi icon nút)
            // Hiện tại chúng ta dùng chung 1 hàm, icon trên navbar là bi-list cố định

            if (window.innerWidth <= 992) {
                // Mobile behavior
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
            } else {
                // Desktop behavior
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                topNavbar.classList.toggle('expanded');

                // Lưu trạng thái vào localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        }

        // --- Enhanced Theme Switcher Logic ---
        function setTheme(themeName) {
            document.body.setAttribute('data-theme', themeName);
            localStorage.setItem('adminTheme', themeName);

            // Update active class on buttons with animation
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            const activeBtn = document.querySelector(`.t-${themeName}`);
            if (activeBtn) {
                activeBtn.classList.add('active');

                // Add bounce animation
                activeBtn.style.animation = 'bounceIn 0.6s ease';
                setTimeout(() => {
                    activeBtn.style.animation = '';
                }, 600);
            }
        }

        // --- Enhanced Loading States ---
        function showLoading(button) {
            const spinner = button.querySelector('.loading-spinner');
            const text = button.querySelector('.position-relative');

            spinner.classList.remove('d-none');
            text.textContent = 'Đang xử lý...';
            button.disabled = true;
        }

        function hideLoading(button, originalText) {
            const spinner = button.querySelector('.loading-spinner');
            const text = button.querySelector('.position-relative');

            spinner.classList.add('d-none');
            text.textContent = originalText;
            button.disabled = false;
        }

        // --- Enhanced Init on Load ---
        document.addEventListener('DOMContentLoaded', function() {
            // Restore Sidebar State
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isSidebarCollapsed && window.innerWidth > 992) {
                document.querySelector('.sidebar').classList.add('collapsed');
                document.querySelector('.main-content').classList.add('expanded');
                document.querySelector('.top-navbar').classList.add('expanded');
            }

            // Restore Theme
            const savedTheme = localStorage.getItem('adminTheme') || 'blue';
            setTheme(savedTheme);

            // Enhanced DataTables Init
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 25,
                stateSave: true
            });

            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Enhanced Password Change Form
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.find('.position-relative').text();

                if (!form[0].checkValidity()) {
                    e.stopPropagation();
                    form.addClass('was-validated');
                    return;
                }

                showLoading(submitBtn[0]);

                // Simulate API call
                setTimeout(() => {
                    hideLoading(submitBtn[0], originalText);

                    // Show success message
                    $('#changePasswordMessage').html(
                        '<div class="alert alert-success alert-dismissible fade show">' +
                        '<i class="bi bi-check-circle-fill me-2"></i>Đổi mật khẩu thành công!' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>'
                    );

                    // Reset form
                    form[0].reset();
                    form.removeClass('was-validated');

                    // Auto hide modal after success
                    setTimeout(() => {
                        $('#changePasswordMessage').empty();
                        $('#changePasswordModal').modal('hide');
                    }, 2000);
                }, 1500);
            });

            // Add hover effects to all cards
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Enhanced mobile menu handling
            function handleResize() {
                if (window.innerWidth > 992) {
                    document.getElementById('mobileOverlay').classList.remove('show');
                    document.querySelector('.sidebar').classList.remove('mobile-open');
                    document.body.style.overflow = '';
                }
            }

            window.addEventListener('resize', handleResize);
        });

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.6);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                    `;

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                .btn { position: relative; overflow: hidden; }
            `;
            document.head.appendChild(style);
        });

        // --- LOGIC CẬP NHẬT SỐ LƯỢNG THÔNG BÁO ---
        function updateAdminBadgeCount() {
            fetch('/quanlydoan/Notification/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.nav-item .badge.bg-danger');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.classList.remove('d-none');
                            badge.style.display = 'inline-block'; // Đảm bảo hiển thị
                        } else {
                            badge.classList.add('d-none');
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Lỗi cập nhật badge:', error));
        }

        // Gọi ngay khi load trang
        updateAdminBadgeCount();

        // Tự động cập nhật mỗi 30 giây (Realtime polling)
        setInterval(updateAdminBadgeCount, 30000);
    </script>
</body>

</html>