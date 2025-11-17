<?php
// views/auth/reset_password.php
// Trang này hoạt động độc lập, không kế thừa layout chung
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Hệ thống Quản lý Đồ Án</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f0f2f5;
            /* Màu nền nhẹ nhàng */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 450px;
            /* Giới hạn chiều rộng thẻ */
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            /* Đổ bóng nhẹ */
        }

        .card-header {
            background-color: #0d6efd;
            /* Màu xanh Primary của Bootstrap */
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            border-bottom: none;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            padding: 5px;
            object-fit: contain;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 d-flex justify-content-center">

                <div class="card">
                    <div class="card-header">
                        <div class="display-6"><i class="bi bi-shield-lock"></i></div>
                        <h4 class="mb-0 mt-2">Đặt lại mật khẩu</h4>
                        <p class="mb-0 opacity-75"><small>Nhập mật khẩu mới cho tài khoản của bạn</small></p>
                    </div>

                    <div class="card-body p-4">
                        <div id="resetPasswordMessage"></div>

                        <form id="resetPasswordForm" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

                            <div class="mb-3">
                                <label for="newPassword" class="form-label fw-bold">Mật khẩu mới</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" id="newPassword" name="password" required minlength="6" placeholder="Nhập mật khẩu mới">
                                </div>
                                <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự.</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label fw-bold">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-check-circle"></i></span>
                                    <input type="password" class="form-control" id="confirmPassword" required placeholder="Nhập lại mật khẩu">
                                </div>
                                <div class="invalid-feedback">Mật khẩu xác nhận không khớp.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Đổi mật khẩu
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="/quanlydoan" class="text-decoration-none text-muted">
                                <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetPasswordForm = document.getElementById('resetPasswordForm');

            if (resetPasswordForm) {
                resetPasswordForm.addEventListener('submit', function(event) {
                    const form = this;
                    const passwordInput = document.getElementById('newPassword');
                    const confirmInput = document.getElementById('confirmPassword');
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    const spinner = btnSubmit.querySelector('.spinner-border');
                    const messageDiv = document.getElementById('resetPasswordMessage');

                    // Xóa thông báo cũ
                    messageDiv.innerHTML = '';

                    // 1. Kiểm tra mật khẩu khớp nhau
                    if (passwordInput.value !== confirmInput.value) {
                        event.preventDefault();
                        event.stopPropagation();
                        confirmInput.classList.add('is-invalid'); // Đánh dấu đỏ ô nhập lại
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Mật khẩu xác nhận không khớp!</div>';
                        return;
                    } else {
                        confirmInput.classList.remove('is-invalid');
                    }

                    // 2. Kiểm tra Validate mặc định của HTML5
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        // 3. Xử lý gửi AJAX
                        event.preventDefault();

                        // Hiển thị loading
                        btnSubmit.disabled = true;
                        spinner.classList.remove('d-none');

                        const formData = new FormData(form);

                        fetch('/quanlydoan/auth/resetPassword', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Hiển thị thông báo từ server
                                const alertClass = data.success ? 'alert-success' : 'alert-danger';
                                const icon = data.success ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';

                                messageDiv.innerHTML = `
                                <div class="alert ${alertClass} d-flex align-items-center" role="alert">
                                    <i class="bi ${icon} me-2"></i>
                                    <div>${data.message}</div>
                                </div>
                            `;

                                if (data.success) {
                                    form.reset();
                                    // Chuyển hướng sau 2 giây
                                    setTimeout(() => {
                                        window.location.href = '/quanlydoan'; // Hoặc trang đăng nhập
                                    }, 2000);
                                }
                            })
                            .catch(error => {
                                console.error('Lỗi:', error);
                                messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi kết nối đến máy chủ. Vui lòng thử lại sau.</div>';
                            })
                            .finally(() => {
                                // Tắt loading
                                btnSubmit.disabled = false;
                                spinner.classList.add('d-none');
                            });
                    }
                    form.classList.add('was-validated');
                }, false);
            }
        });
    </script>
</body>

</html>