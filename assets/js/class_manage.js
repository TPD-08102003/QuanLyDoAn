
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

// Thay thế hàm viewClass hiện tại trong class_manage.js bằng đoạn code sau:

function viewClass(classId) {
    // 1. Khai báo các element cần cập nhật
    const studentListDiv = document.getElementById('studentList');
    const studentCountSpan = document.getElementById('student_count');
    const noStudentMessage = document.getElementById('noStudentMessage');

    // 2. Hiển thị trạng thái tải
    studentListDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Đang tải chi tiết lớp học...</p></div>';
    studentCountSpan.textContent = '...';
    noStudentMessage.style.display = 'none';

    // 3. Gọi API (Sử dụng đường dẫn hiện tại của bạn)
    fetch(`/quanlydoan/classes/getClassInfo/${classId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const classInfo = data.data;

                // 4. Cập nhật thông tin cơ bản của lớp học
                document.getElementById('view_class_name').textContent = classInfo.class_name;
                document.getElementById('view_faculty_name').textContent = classInfo.faculty_name;
                document.getElementById('view_description').textContent = classInfo.description || 'Không có mô tả';
                document.getElementById('view_created_at').textContent = formatDateTime(classInfo.created_at);
                document.getElementById('view_updated_at').textContent = formatDateTime(classInfo.updated_at);

                // 5. Render danh sách sinh viên
                renderStudents(classInfo.students);

                // 6. Hiển thị modal
                new bootstrap.Modal(document.getElementById('viewClassModal')).show();
            } else {
                showToast('error', 'Lỗi', 'Không thể tải thông tin lớp học');
                studentListDiv.innerHTML = '<div class="text-center py-4 text-danger"><p>Không thể tải dữ liệu.</p></div>';
                studentCountSpan.textContent = '0';
            }
        })
        .catch(error => {
            console.error('Lỗi khi tải chi tiết lớp học:', error);
            showToast('error', 'Lỗi', 'Đã xảy ra lỗi khi tải thông tin');
            studentListDiv.innerHTML = '<div class="text-center py-4 text-danger"><p>Không thể tải dữ liệu.</p></div>';
            studentCountSpan.textContent = '0';
        });
}

/**
 * Hàm mới để render danh sách sinh viên và quản lý count/message
 * @param {Array<Object>} students - Danh sách sinh viên từ response JSON
 */
function renderStudents(students) {
    const studentListDiv = document.getElementById('studentList');
    const studentCountSpan = document.getElementById('student_count');
    const noStudentMessage = document.getElementById('noStudentMessage');

    studentListDiv.innerHTML = ''; // Xóa nội dung cũ
    const studentCount = students ? students.length : 0;
    studentCountSpan.textContent = studentCount; // Cập nhật số lượng

    if (studentCount === 0) {
        // Hiện thông báo không có sinh viên
        noStudentMessage.style.display = 'block';
    } else {
        // Ẩn thông báo và render danh sách
        noStudentMessage.style.display = 'none';
        let studentHtml = '<ul class="list-group list-group-flush">';

        students.forEach((student, index) => {
            // Đảm bảo các trường dữ liệu (student_name, student_code, status) khớp với cấu trúc JSON của bạn
            const statusBadge = student.status === 'active' ? 'success' : (student.status === 'inactive' ? 'secondary' : 'warning');
            const statusText = student.status === 'active' ? 'Đang học' : (student.status === 'inactive' ? 'Đã nghỉ' : 'Khác');

            studentHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${index + 1}. ${student.student_name || 'Tên không xác định'}</strong>
                        <small class="text-muted d-block">MSSV: ${student.student_code || 'N/A'}</small>
                    </div>
                    <span class="badge bg-${statusBadge}">
                        ${statusText}
                    </span>
                </li>
            `;
        });
        studentHtml += '</ul>';
        studentListDiv.innerHTML = studentHtml;
    }
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
// File: ../assets/js/class_manage.js

document.addEventListener('DOMContentLoaded', function () {
    // Khởi tạo Modal Bootstrap
    const viewClassModalElement = document.getElementById('viewClassModal');
    if (!viewClassModalElement) return;

    const viewClassModal = new bootstrap.Modal(viewClassModalElement);
    const apiBaseUrl = '/quanlydoan/classes/info/'; // URL API đã được sửa lỗi router

    // Bắt sự kiện click vào nút "Chi tiết" (có class .view-class-btn)
    document.querySelectorAll('.view-class-btn').forEach(button => {
        button.addEventListener('click', function () {
            const classId = this.getAttribute('data-class-id');
            fetchClassDetails(classId);
        });
    });

    /**
     * Hàm gọi API để lấy chi tiết lớp học và danh sách sinh viên.
     * @param {string} classId - ID của lớp học
     */
    function fetchClassDetails(classId) {
        const studentListDiv = document.getElementById('studentList');
        const studentCountSpan = document.getElementById('student_count');
        const noStudentMessage = document.getElementById('noStudentMessage');

        // Hiển thị trạng thái tải
        studentListDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Đang tải chi tiết lớp học...</p></div>';
        studentCountSpan.textContent = '...';
        noStudentMessage.style.display = 'none';

        // Gọi API
        fetch(apiBaseUrl + classId)
            .then(response => {
                if (!response.ok) {
                    // Xử lý lỗi HTTP (ví dụ: 404, 500)
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    const classInfo = result.data;

                    // 1. Cập nhật thông tin cơ bản của lớp học
                    document.getElementById('view_class_name').textContent = classInfo.class_name;
                    document.getElementById('view_faculty_name').textContent = classInfo.faculty_name || 'Chưa xác định';
                    document.getElementById('view_description').textContent = classInfo.description || 'Không có mô tả';
                    document.getElementById('view_created_at').textContent = new Date(classInfo.created_at).toLocaleString('vi-VN');
                    document.getElementById('view_updated_at').textContent = new Date(classInfo.updated_at).toLocaleString('vi-VN');

                    // 2. Cập nhật danh sách sinh viên (PHẦN QUAN TRỌNG)
                    renderStudents(classInfo.students);

                    // Hiển thị Modal sau khi đã tải xong dữ liệu
                    viewClassModal.show();
                } else {
                    alert('Lỗi tải dữ liệu lớp học: ' + result.message);
                    studentListDiv.innerHTML = '<div class="text-center py-4 text-danger"><p>Lỗi tải dữ liệu.</p></div>';
                    studentCountSpan.textContent = '0';
                }
            })
            .catch(error => {
                console.error('Lỗi khi tải chi tiết lớp học:', error);
                alert('Lỗi kết nối hoặc hệ thống. Vui lòng thử lại.');
                studentListDiv.innerHTML = '<div class="text-center py-4 text-danger"><p>Không thể tải dữ liệu.</p></div>';
                studentCountSpan.textContent = '0';
            });
    }

    /**
     * Hàm render danh sách sinh viên vào Modal.
     * @param {Array<Object>} students - Danh sách sinh viên từ response JSON
     */
    function renderStudents(students) {
        const studentListDiv = document.getElementById('studentList');
        const studentCountSpan = document.getElementById('student_count');
        const noStudentMessage = document.getElementById('noStudentMessage');

        studentListDiv.innerHTML = ''; // Xóa nội dung cũ
        studentCountSpan.textContent = students.length; // Cập nhật số lượng

        if (students.length === 0) {
            // Hiện thông báo không có sinh viên
            noStudentMessage.style.display = 'block';
        } else {
            // Ẩn thông báo và render danh sách
            noStudentMessage.style.display = 'none';
            let studentHtml = '<ul class="list-group list-group-flush">';

            students.forEach((student, index) => {
                // Tùy thuộc vào cấu trúc dữ liệu sinh viên, bạn có thể cần điều chỉnh các trường này
                // Ví dụ: student.student_name, student.student_id, student.gender
                studentHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${index + 1}. ${student.student_name || 'Tên không xác định'}</strong>
                            <small class="text-muted d-block">${student.student_id || 'ID không xác định'}</small>
                        </div>
                        <span class="badge bg-secondary">${student.gender || '---'}</span>
                    </li>
                `;
            });
            studentHtml += '</ul>';
            studentListDiv.innerHTML = studentHtml;
        }
    }
});