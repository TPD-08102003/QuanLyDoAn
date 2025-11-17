<?php
// views/auth/reset_password.php (hoặc đường dẫn tương ứng của bạn)

$title = "Đặt lại mật khẩu"; // Đặt tiêu đề cho trang

// Bắt đầu bộ đệm đầu ra để lưu nội dung vào biến $content
ob_start();
?>

<div class="container py-5" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-lock me-2"></i>Đặt lại mật khẩu
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div id="resetPasswordMessage"></div>

                    <form id="resetPasswordForm" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="newPassword" name="password" required minlength="6">
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu mới (tối thiểu 6 ký tự).</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                            <div class="invalid-feedback">Vui lòng xác nhận lại mật khẩu.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Xác nhận thay đổi
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="/quanlydoan" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetPasswordForm = document.querySelector('#resetPasswordForm');

        if (resetPasswordForm) {
            resetPasswordForm.addEventListener('submit', function(event) {
                const form = this;
                const password = document.querySelector('#newPassword').value;
                const confirmPassword = document.querySelector('#confirmPassword').value;
                const btnSubmit = form.querySelector('button[type="submit"]');
                const spinner = btnSubmit.querySelector('.spinner-border');
                const messageDiv = document.querySelector('#resetPasswordMessage');

                // Reset thông báo cũ
                messageDiv.innerHTML = '';

                // Validate mật khẩu khớp nhau
                if (password !== confirmPassword) {
                    event.preventDefault();
                    event.stopPropagation();
                    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Mật khẩu xác nhận không khớp!</div>';
                    return;
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    event.preventDefault();

                    // Hiệu ứng loading
                    btnSubmit.disabled = true;
                    spinner.classList.remove('d-none');

                    const formData = new FormData(form);

                    fetch('/quanlydoan/auth/resetPassword', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}"><i class="bi bi-${data.success ? 'check-circle' : 'exclamation-circle'} me-2"></i>${data.message}</div>`;

                            if (data.success) {
                                form.reset();
                                setTimeout(() => {
                                    // Chuyển hướng về trang chủ hoặc mở modal đăng nhập
                                    window.location.href = '/quanlydoan';
                                }, 2000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi kết nối máy chủ, vui lòng thử lại sau!</div>';
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