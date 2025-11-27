<?php
// views/auth/forgot_password.php
// Trang độc lập, không dùng layout chung
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #2c5aa0;
            --accent-color: #ff6b35;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background-color: #fff;
        }

        .split-screen {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* --- LEFT PANE (Animation) --- */
        .left-pane {
            width: 50%;
            background: linear-gradient(135deg, #1e3d6f, var(--primary-color));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Các hình tròn trang trí nền */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .c1 {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -100px;
        }

        .c2 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            right: 10%;
        }

        /* Animation icon Email bay */
        .email-icon-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
            animation: pulse 3s infinite;
        }

        .bi-envelope-paper-heart {
            font-size: 4rem;
            color: #fff;
            animation: fly 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }

            70% {
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        @keyframes fly {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(-5deg);
            }

            100% {
                transform: translateY(0) rotate(0deg);
            }
        }

        .hero-text {
            text-align: center;
            padding: 0 30px;
            z-index: 2;
        }

        /* --- RIGHT PANE (Form) --- */
        .right-pane {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background-color: white;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }

        .form-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .form-text {
            color: #6c757d;
            margin-bottom: 25px;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(44, 90, 160, 0.1);
            border-color: var(--primary-color);
        }

        .btn-submit {
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #1e3d6f;
            transform: translateY(-2px);
        }

        .back-link {
            text-decoration: none;
            color: #6c757d;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .split-screen {
                flex-direction: column;
            }

            .left-pane {
                width: 100%;
                height: 30vh;
            }

            .right-pane {
                width: 100%;
                height: 70vh;
            }

            .email-icon-wrapper {
                width: 80px;
                height: 80px;
            }

            .bi-envelope-paper-heart {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="split-screen">
        <div class="left-pane">
            <div class="circle c1"></div>
            <div class="circle c2"></div>

            <div class="email-icon-wrapper">
                <i class="bi bi-envelope-paper-heart"></i>
            </div>

            <div class="hero-text">
                <h3>Bạn quên mật khẩu?</h3>
                <p class="mb-0 text-white-50">Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại quyền truy cập.</p>
            </div>
        </div>

        <div class="right-pane">
            <div class="form-container">
                <h2 class="form-title">Khôi phục tài khoản</h2>
                <p class="form-text">Nhập địa chỉ email đã đăng ký của bạn. Chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>

                <div id="alertMessage"></div>

                <form id="forgotPasswordForm" method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Email đăng ký</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-submit w-100 mb-3">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        <span class="btn-text">Gửi liên kết xác nhận</span>
                    </button>

                    <div class="text-center">
                        <a href="/quanlydoan" class="back-link">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại trang chủ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            const alertBox = document.getElementById('alertMessage');
            const submitBtn = form.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            const btnText = submitBtn.querySelector('.btn-text');
            const emailInput = document.getElementById('email');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // 1. Reset thông báo cũ
                alertBox.innerHTML = '';

                // 2. Validate sơ bộ
                if (!emailInput.value) {
                    showAlert('warning', 'Vui lòng nhập địa chỉ email!');
                    return;
                }

                // 3. Hiệu ứng Loading
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Đang gửi...';

                const formData = new FormData(form);

                // 4. Gửi Request AJAX
                fetch('/quanlydoan/auth/forgotPassword', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Thành công: Hiện thông báo xanh, clear form
                            showAlert('success', data.message);
                            form.reset();
                            // Tùy chọn: Có thể disable nút gửi để tránh spam
                            // submitBtn.disabled = true; 
                        } else {
                            // Thất bại: Hiện thông báo đỏ
                            showAlert('danger', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('danger', 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.');
                    })
                    .finally(() => {
                        // Tắt hiệu ứng loading
                        submitBtn.disabled = false;
                        spinner.classList.add('d-none');
                        btnText.textContent = 'Gửi liên kết xác nhận';
                    });
            });

            // Hàm hiển thị thông báo Bootstrap chuẩn
            function showAlert(type, message) {
                const icon = type === 'success' ? 'check-circle-fill' :
                    (type === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill');

                alertBox.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-${icon} me-2 fs-5"></i>
                            <div>${message}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        });
    </script>
</body>

</html>