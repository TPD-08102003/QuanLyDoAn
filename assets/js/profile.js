document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');

    // Initialize flatpickr for date picker
    flatpickr('#date_of_birth_display', {
        dateFormat: 'd/m/Y',
        onChange: function (selectedDates, dateStr) {
            document.getElementById('date_of_birth_hidden').value = selectedDates[0] ? selectedDates[0].toISOString().split('T')[0] : '';
        }
    });

    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    avatarInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission with AJAX
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validate form (using Bootstrap validation)
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message if needed, then redirect
                    alert(data.message); // Có thể thay bằng toast/notification đẹp hơn
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi cập nhật!');
            })
            .finally(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
    });
});