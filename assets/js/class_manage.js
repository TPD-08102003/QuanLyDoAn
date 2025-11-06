
// View Class Function
document.addEventListener('DOMContentLoaded', function () {
    // View Class Details
    document.querySelectorAll('.view-class-btn').forEach(button => {
        button.addEventListener('click', function () {
            const classId = this.getAttribute('data-class-id');
            viewClass(classId);
        });
    });

    // Edit Class
    document.querySelectorAll('.edit-class-btn').forEach(button => {
        button.addEventListener('click', function () {
            const classId = this.getAttribute('data-class-id');
            editClass(classId);
        });
    });

    // Add Class Form Submission
    document.getElementById('addClassForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitAddClassForm();
    });

    // Edit Class Form Submission
    document.getElementById('editClassForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitEditClassForm();
    });

    // Reset add form when modal is hidden
    document.getElementById('addClassModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('addClassForm').reset();
        clearValidationErrors('addClassForm');
    });
});

function viewClass(classId) {
    // Show loading state
    document.getElementById('studentList').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Đang tải...</div>';

    fetch(`/quanlydoan/classes/getClassInfo/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const classInfo = data.data;

                // Populate modal with class information
                document.getElementById('view_class_name').textContent = classInfo.class_name;
                document.getElementById('view_faculty_name').textContent = classInfo.faculty_name;
                document.getElementById('view_description').textContent = classInfo.description || 'Không có mô tả';
                document.getElementById('view_created_at').textContent = formatDateTime(classInfo.created_at);
                document.getElementById('view_updated_at').textContent = formatDateTime(classInfo.updated_at);

                // Populate student list
                let studentListHTML = '';
                // Thay thế toàn bộ đoạn này trong <script>
                if (classInfo.students && classInfo.students.length > 0) {
                    classInfo.students.forEach((student, index) => {
                        studentListHTML += `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong>${index + 1}. ${student.student_name || 'Chưa có tên'}</strong>
                    <br>
                    <small class="text-muted">MSSV: ${student.student_code || 'N/A'}</small>
                </div>
                <span class="badge bg-${student.status === 'active' ? 'success' : 'secondary'}">
                    ${student.status === 'active' ? 'Đang học' : (student.status === 'inactive' ? 'Đã nghỉ' : 'Không xác định')}
                </span>
            </div>
        `;
                    });
                } else {
                    studentListHTML = '<p class="text-muted text-center">Chưa có sinh viên nào trong lớp này.</p>';
                }
                document.getElementById('studentList').innerHTML = studentListHTML;

                // Show modal
                new bootstrap.Modal(document.getElementById('viewClassModal')).show();
            } else {
                showToast('error', 'Lỗi', 'Không thể tải thông tin lớp học');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi khi tải thông tin');
        });
}

function editClass(classId) {
    fetch(`/quanlydoan/classes/getClassInfo/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const classInfo = data.data;

                // Populate edit form
                document.getElementById('edit_class_id').value = classInfo.class_id;
                document.getElementById('edit_class_name').value = classInfo.class_name;
                document.getElementById('edit_faculty_id').value = classInfo.faculty_id;
                document.getElementById('edit_description').value = classInfo.description || '';

                // Set form action
                document.getElementById('editClassForm').action = `/quanlydoan/classes/update/${classId}`;

                // Show modal
                new bootstrap.Modal(document.getElementById('editClassModal')).show();
            } else {
                showToast('error', 'Lỗi', 'Không thể tải thông tin lớp học');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi khi tải thông tin');
        });
}

function submitAddClassForm() {
    const form = document.getElementById('addClassForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Thành công', 'Thêm lớp học thành công');
                document.getElementById('addClassModal').querySelector('.btn-close').click();
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // ĐÃ SỬA: Lấy lỗi từ data.data
                showValidationErrors(data.data, 'addClassForm');
                showToast('error', 'Lỗi', 'Vui lòng kiểm tra lại thông tin');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi khi thêm lớp học');
        });
}

function submitEditClassForm() {
    const form = document.getElementById('editClassForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Thành công', 'Cập nhật lớp học thành công');
                document.getElementById('editClassModal').querySelector('.btn-close').click();
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // ĐÃ SỬA: Lấy lỗi từ data.data
                showValidationErrors(data.data, 'editClassForm');
                showToast('error', 'Lỗi', 'Vui lòng kiểm tra lại thông tin');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi khi cập nhật lớp học');
        });
}

function showValidationErrors(errors, formType) {
    // Clear previous errors
    clearValidationErrors(formType);

    // Show new errors
    if (errors) {
        for (const field in errors) {
            const input = document.querySelector(`#${formType} [name="${field}"]`);
            const errorDiv = document.getElementById(`${formType}_${field}_error`);

            if (input && errorDiv) {
                input.classList.add('is-invalid');
                errorDiv.textContent = errors[field][0];
            }
        }
    }
}

function clearValidationErrors(formType) {
    const form = document.getElementById(formType);
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    const date = new Date(dateTimeString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}

function showToast(type, title, message) {
    // You can implement a toast notification system here
    // For now, using alert for simplicity
    alert(`${title}: ${message}`);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
/**
 * Xử lý việc xác nhận trước khi xóa lớp học
 * @param {HTMLButtonElement} element Nút Xóa được click
    */
function confirmDelete(element) {
    const classId = element.getAttribute('data-id');
    const className = element.getAttribute('data-name');

    if (confirm(`Bạn có chắc chắn muốn xóa lớp học "${className}" không?`)) {
        deleteClass(classId);
    }
}

// ... (các hàm JS khác) ...

/**
 * Xử lý việc xác nhận trước khi xóa lớp học
 * @param {string} classId ID của lớp học cần xóa
    * @param {string} className Tên lớp học
    */
function confirmDelete(classId, className) { // <-- ĐÃ SỬA: Nhận 2 tham số classId và className

    // Đã loại bỏ: const classId = element.getAttribute('data-id');
    // Đã loại bỏ: const className = element.getAttribute('data-name');

    if (confirm(`Bạn có chắc chắn muốn xóa lớp học "${className}" không?`)) {
        deleteClass(classId);
    }
}

/**
 * Gửi yêu cầu AJAX để xóa lớp học
 * @param {string} classId ID của lớp học cần xóa
    */
function deleteClass(classId) {
    fetch(`/quanlydoan/classes/destroy/${classId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Thành công', data.message || 'Xóa lớp học thành công');
                // Tải lại trang sau khi xóa thành công
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Hiển thị lỗi từ Controller (ví dụ: Lớp có sinh viên)
                showToast('error', 'Lỗi', data.message || 'Xóa lớp học thất bại.');
            }
        })
        .catch(error => {
            console.error('Error deleting class:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi hệ thống khi xóa.');
        });
}
