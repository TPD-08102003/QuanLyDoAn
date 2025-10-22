// Xử lý form đổi mật khẩu
(function () {
    'use strict';
    const changePasswordForm = document.querySelector('#changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function (event) {
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

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.main-content').classList.toggle('collapsed');
    document.querySelector('.top-navbar').classList.toggle('collapsed');
}

function toggleDropdown(element) {
    const dropdownMenu = element.nextElementSibling;
    const isShown = dropdownMenu.classList.contains("show");

    // Đóng tất cả dropdown menu khác
    document
        .querySelectorAll(".sidebar .dropdown-menu.show")
        .forEach((menu) => {
            if (menu !== dropdownMenu) {
                menu.classList.remove("show");
            }
        });

    // Toggle dropdown hiện tại
    dropdownMenu.classList.toggle("show", !isShown);
}