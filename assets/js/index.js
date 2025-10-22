function getBaseURL() {
    return window.location.pathname.split('/').slice(0, -1).join('/') + '/';
}

// Xử lý form đăng nhập
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    let spinner = submitButton.querySelector('.spinner-border');

    if (!spinner) {
        spinner = document.createElement('span');
        spinner.classList.add('spinner-border', 'spinner-border-sm', 'me-2', 'd-none');
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-hidden', 'true');
        submitButton.prepend(spinner);
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    submitButton.disabled = true;
    spinner.classList.remove('d-none');

    fetch('/study_sharing/auth/login', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('loginMessage').innerHTML = `<div class="alert ${data.success ? 'alert-success' : 'alert-danger'}">${data.message}</div>`;
        if (data.success) {
            if (data.role === 'admin') {
                setTimeout(() => window.location.href = '/study_sharing/HomeAdmin/index', 1000);
            } else {
                setTimeout(() => location.reload(), 1000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loginMessage').innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
    })
    .finally(() => {
        submitButton.disabled = false;
        spinner.classList.add('d-none');
    });

    form.classList.remove('was-validated');
});

// Xử lý form đăng ký
(function() {
    'use strict';
    const registerForm = document.querySelector('#registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            let spinner = submitButton.querySelector('.spinner-border') || document.createElement('span');
            spinner.classList.add('spinner-border', 'spinner-border-sm', 'me-2', 'd-none');
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-hidden', 'true');
            if (!spinner.parentElement) {
                submitButton.prepend(spinner);
            }
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                event.preventDefault();
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                const formData = new FormData(form);
                fetch('/study_sharing/auth/register', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.querySelector('#registerMessage');
                    messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                    if (data.success) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    const messageDiv = document.querySelector('#registerMessage');
                    messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
                })
                .finally(() => {
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                });
            }
            form.classList.add('was-validated');
        }, false);

        const optionalFields = ['registerDateOfBirth', 'registerPhoneNumber', 'registerAddress'];
        optionalFields.forEach(id => {
            const field = document.querySelector(`#${id}`);
            if (field) {
                field.addEventListener('input', function() {
                    if (this.value) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
        });
    }
})();

// Xử lý form quên mật khẩu
(function() {
    'use strict';
    const forgotPasswordForm = document.querySelector('#forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(event) {
            const form = this;
            const submitButton = form.querySelector('#forgotPasswordSubmit');
            let spinner = submitButton.querySelector('.spinner-border');

            if (!spinner) {
                spinner = document.createElement('span');
                spinner.classList.add('spinner-border', 'spinner-border-sm', 'me-2', 'd-none');
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-hidden', 'true');
                submitButton.prepend(spinner);
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                event.preventDefault();
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                const formData = new FormData(form);
                fetch('/study_sharing/auth/forgotPassword', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.querySelector('#forgotPasswordMessage');
                    messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                    if (data.success) {
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                        }, 2000);
                    }
                })
                .catch(error => {
                    const messageDiv = document.querySelector('#forgotPasswordMessage');
                    messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
                })
                .finally(() => {
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                });
            }
            form.classList.add('was-validated');
        }, false);
    }
})();

// Xử lý form đổi mật khẩu
(function() {
    'use strict';
    const changePasswordForm = document.querySelector('#changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(event) {
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            let spinner = submitButton.querySelector('.spinner-border');

            if (!spinner) {
                spinner = document.createElement('span');
                spinner.classList.add('spinner-border', 'spinner-border-sm', 'me-2', 'd-none');
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-hidden', 'true');
                submitButton.prepend(spinner);
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                event.preventDefault();
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                const formData = new FormData(form);
                fetch('/study_sharing/auth/changePassword', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.querySelector('#changePasswordMessage');
                    messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                    if (data.success) {
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                            location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    const messageDiv = document.querySelector('#changePasswordMessage');
                    messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
                })
                .finally(() => {
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                });
            }
            form.classList.add('was-validated');
        }, false);
    }
})();