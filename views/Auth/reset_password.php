<?php
$title = "Đặt lại mật khẩu";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .card {
            max-width: 500px;
            width: 100%;
        }

        .logo {
            max-width: 60px;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <img src="/study_sharing/assets/images/logo.png" alt="Logo" class="logo rounded-circle">
                        <h5 class="mb-0">Đặt lại mật khẩu - Study Sharing</h5>
                    </div>
                    <div class="card-body">
                        <div id="resetPasswordMessage"></div>
                        <form id="resetPasswordForm" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="newPassword" name="password" required>
                                <div class="invalid-feedback">Vui lòng nhập mật khẩu mới.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <div class="invalid-feedback">Vui lòng xác nhận mật khẩu.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đặt lại mật khẩu</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="/study_sharing">Quay lại trang chủ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        (function() {
            'use strict';
            const resetPasswordForm = document.querySelector('#resetPasswordForm');
            if (resetPasswordForm) {
                resetPasswordForm.addEventListener('submit', function(event) {
                    const form = this;
                    const password = document.querySelector('#newPassword').value;
                    const confirmPassword = document.querySelector('#confirmPassword').value;

                    if (password !== confirmPassword) {
                        event.preventDefault();
                        document.querySelector('#resetPasswordMessage').innerHTML = '<div class="alert alert-danger">Mật khẩu xác nhận không khớp!</div>';
                        return;
                    }

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        event.preventDefault();
                        const formData = new FormData(form);
                        fetch('/study_sharing/auth/resetPassword', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                const messageDiv = document.querySelector('#resetPasswordMessage');
                                messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                                if (data.success) {
                                    setTimeout(() => {
                                        window.location.href = '/study_sharing';
                                    }, 2000);
                                }
                            })
                            .catch(error => {
                                const messageDiv = document.querySelector('#resetPasswordMessage');
                                messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
                            });
                    }
                    form.classList.add('was-validated');
                }, false);
            }
        })();
    </script>
</body>

</html>