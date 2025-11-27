<?php
// Lấy token từ biến data được truyền từ Controller (hoặc fallback về GET nếu cần)
$token = $token ?? $_GET['token'] ?? '';
?>

<style>
    .reset-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .reset-header {
        background: linear-gradient(135deg, #2c5aa0, #1e3d6f);
        padding: 30px;
        text-align: center;
        color: white;
    }

    .icon-wrapper {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 2.5rem;
    }

    .reset-body {
        padding: 40px;
        background: white;
    }

    /* Hiệu ứng đếm ngược thành công */
    #successOverlay {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .countdown-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #d1e7dd;
        color: #0f5132;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0 auto 20px;
    }
</style>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-8 col-lg-6 col-xl-5">

            <div class="card reset-card">
                <div class="reset-header">
                    <div class="icon-wrapper">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Thiết lập mật khẩu mới</h4>
                    <small class="opacity-75">Bảo vệ tài khoản đồ án của bạn</small>
                </div>

                <div class="reset-body">

                    <div id="resetFormContent">
                        <div id="alertMessage"></div>

                        <form id="resetPasswordForm" class="needs-validation" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold text-secondary">Mật khẩu mới</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 bg-light" id="password" name="password" placeholder="Nhập mật khẩu mới" required minlength="6">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label fw-bold text-secondary">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-check-all"></i></span>
                                    <input type="password" class="form-control border-start-0 bg-light" id="confirmPassword" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #2c5aa0;">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                <span class="btn-text">Xác nhận thay đổi</span>
                            </button>
                        </form>
                    </div>

                    <div id="successOverlay">
                        <div class="text-success mb-3">
                            <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-success fw-bold">Thành công!</h4>
                        <p class="text-muted">Mật khẩu đã được cập nhật.</p>

                        <div class="countdown-circle">
                            <span id="countdownTimer">5</span>
                        </div>
                        <small class="text-muted">Tự động chuyển trang sau giây lát...</small>
                    </div>

                </div>
            </div>

            <div class="text-center mt-3">
                <a href="/quanlydoan" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('resetPasswordForm');
        const alertBox = document.getElementById('alertMessage');
        const formContent = document.getElementById('resetFormContent');
        const successOverlay = document.getElementById('successOverlay');
        const countdownSpan = document.getElementById('countdownTimer');
        const submitBtn = form.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('.btn-text');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.innerHTML = '';

            // 1. Validate Client-side
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password.length < 6) {
                showAlert('danger', 'Mật khẩu phải dài hơn 6 ký tự.');
                return;
            }

            if (password !== confirmPassword) {
                showAlert('danger', 'Mật khẩu xác nhận không khớp!');
                return;
            }

            // 2. UI Loading
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Đang xử lý...';

            const formData = new FormData(form);

            // 3. Gửi Fetch API
            fetch('/quanlydoan/auth/resetPassword', { // Đảm bảo URL này đúng với Route xử lý POST của bạn
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Thành công: Ẩn form, hiện thông báo thành công
                        formContent.style.display = 'none';
                        successOverlay.style.display = 'block';

                        // Đếm ngược chuyển trang
                        let timeLeft = 5;
                        const countdownInterval = setInterval(() => {
                            timeLeft--;
                            countdownSpan.textContent = timeLeft;

                            if (timeLeft <= 0) {
                                clearInterval(countdownInterval);
                                // Mở Modal Login (nếu ở trang chủ) hoặc chuyển về trang login
                                window.location.href = '/quanlydoan';
                            }
                        }, 1000);

                    } else {
                        // Lỗi
                        showAlert('danger', data.message || 'Có lỗi xảy ra.');
                        resetBtnState();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Lỗi kết nối máy chủ.');
                    resetBtnState();
                });
        });

        function resetBtnState() {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Xác nhận thay đổi';
        }

        function showAlert(type, message) {
            alertBox.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    });
</script>