/**
 * QUAN LY LOP HOC - MAIN JAVASCRIPT
 * File: assets/js/class_manage.js
 */

document.addEventListener('DOMContentLoaded', function () {
    // ============================================================
    // 1. KHỞI TẠO CHUNG
    // ============================================================

    // Khởi tạo tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Khởi tạo Modal Objects
    const viewClassModalEl = document.getElementById('viewClassModal');
    const editClassModalEl = document.getElementById('editClassModal');

    let viewClassModal = viewClassModalEl ? new bootstrap.Modal(viewClassModalEl) : null;
    let editClassModal = editClassModalEl ? new bootstrap.Modal(editClassModalEl) : null;

    // ============================================================
    // 2. BẮT SỰ KIỆN (EVENT LISTENERS)
    // ============================================================

    // Nút Xem chi tiết
    document.querySelectorAll('.view-class-btn').forEach(button => {
        button.addEventListener('click', function () {
            const classId = this.getAttribute('data-class-id');
            fetchClassDetails(classId);
        });
    });

    // Nút Sửa
    document.querySelectorAll('.edit-class-btn').forEach(button => {
        button.addEventListener('click', function () {
            const classId = this.getAttribute('data-class-id');
            prepareEditClass(classId);
        });
    });

    // Submit Form Thêm mới
    const addForm = document.getElementById('addClassForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitAddClassForm();
        });

        // Reset form khi đóng modal
        const addClassModal = document.getElementById('addClassModal');
        addClassModal.addEventListener('hidden.bs.modal', function () {
            addForm.reset();
            clearValidationErrors('addClassForm');
        });
    }

    // Submit Form Cập nhật
    const editForm = document.getElementById('editClassForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitEditClassForm();
        });
    }

    // ============================================================
    // 3. CÁC HÀM XỬ LÝ LOGIC
    // ============================================================

    /**
     * XEM CHI TIẾT: Gọi API lấy thông tin lớp và sinh viên
     */
    function fetchClassDetails(classId) {
        const studentListDiv = document.getElementById('studentList');
        const studentCountSpan = document.getElementById('student_count');
        const noStudentMessage = document.getElementById('noStudentMessage');

        // Reset UI loading
        studentListDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Đang tải dữ liệu...</p></div>';
        studentCountSpan.textContent = '...';
        if (noStudentMessage) noStudentMessage.style.display = 'none';

        // Gọi API: Đảm bảo đường dẫn này đúng với router của bạn
        fetch(`/quanlydoan/classes/getClassInfo/${classId}`)
            .then(response => {
                if (!response.ok) throw new Error('Lỗi kết nối');
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    const data = result.data;

                    // 1. Điền thông tin lớp học
                    document.getElementById('view_class_name').textContent = data.class_name;
                    document.getElementById('view_faculty_name').textContent = data.faculty_name || 'Chưa xác định';
                    document.getElementById('view_description').textContent = data.description || 'Không có mô tả';
                    document.getElementById('view_created_at').textContent = formatDateTime(data.created_at);
                    document.getElementById('view_updated_at').textContent = formatDateTime(data.updated_at);

                    // 2. Render danh sách sinh viên (Đã sửa theo Model PHP)
                    renderStudents(data.students);

                    // 3. Hiện Modal
                    if (viewClassModal) viewClassModal.show();
                } else {
                    alert('Lỗi: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                studentListDiv.innerHTML = '<div class="text-center text-danger py-3">Không thể tải dữ liệu.</div>';
            });
    }

    /**
     * RENDER SINH VIÊN: Khớp với dữ liệu trả về từ StudentModel::getStudentsByClass
     * Dữ liệu nhận được: { full_name, mssv, email, student_id }
     */
    function renderStudents(students) {
        const studentListDiv = document.getElementById('studentList');
        const studentCountSpan = document.getElementById('student_count');
        const noStudentMessage = document.getElementById('noStudentMessage');

        studentListDiv.innerHTML = '';
        const count = students ? students.length : 0;
        studentCountSpan.textContent = count;

        if (count === 0) {
            if (noStudentMessage) noStudentMessage.style.display = 'block';
        } else {
            if (noStudentMessage) noStudentMessage.style.display = 'none';

            let html = '<ul class="list-group list-group-flush">';

            students.forEach((st, index) => {
                // ÁNH XẠ DỮ LIỆU TỪ SQL
                const fullName = st.full_name || 'Chưa có tên';
                const mssv = st.mssv || 'N/A';
                const email = st.email || '';

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center px-2">
                        <div>
                            <div class="fw-bold text-dark">${index + 1}. ${fullName}</div>
                            <div class="small text-muted">
                                <i class="bi bi-card-heading me-1"></i>MSSV: ${mssv}
                                ${email ? ` | <i class="bi bi-envelope me-1"></i>${email}` : ''}
                            </div>
                        </div>
                        </li>
                `;
            });
            html += '</ul>';
            studentListDiv.innerHTML = html;
        }
    }

    /**
     * SỬA LỚP: Lấy dữ liệu đổ vào form sửa
     */
    function prepareEditClass(classId) {
        fetch(`/quanlydoan/classes/getClassInfo/${classId}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    document.getElementById('edit_class_id').value = data.class_id;
                    document.getElementById('edit_class_name').value = data.class_name;
                    document.getElementById('edit_faculty_id').value = data.faculty_id;
                    document.getElementById('edit_description').value = data.description || '';

                    // Cập nhật Action URL
                    document.getElementById('editClassForm').action = `/quanlydoan/classes/update/${classId}`;

                    if (editClassModal) editClassModal.show();
                }
            })
            .catch(err => console.error(err));
    }

    // --- XỬ LÝ SUBMIT FORM (ADD & EDIT) ---

    function submitAddClassForm() {
        handleFormSubmit('addClassForm', 'addClassModal');
    }

    function submitEditClassForm() {
        handleFormSubmit('editClassForm', 'editClassModal');
    }

    function handleFormSubmit(formId, modalId) {
        const form = document.getElementById(formId);
        const formData = new FormData(form);

        fetch(form.action, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('success', 'Thành công', data.message);
                    document.querySelector(`#${modalId} .btn-close`).click();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showValidationErrors(data.data, formId);
                    showToast('error', 'Lỗi', data.message);
                }
            })
            .catch(err => console.error(err));
    }
});

// ============================================================
// 4. CÁC HÀM TIỆN ÍCH (GLOBAL)
// ============================================================

function confirmDelete(classId, className) {
    if (confirm(`Bạn có chắc chắn muốn xóa lớp học "${className}" không?`)) {
        fetch(`/quanlydoan/classes/destroy/${classId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa thành công!');
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert('Lỗi hệ thống khi xóa.'));
    }
}

function showValidationErrors(errors, formId) {
    clearValidationErrors(formId);
    if (!errors) return;

    for (const [field, msgs] of Object.entries(errors)) {
        const input = document.querySelector(`#${formId} [name="${field}"]`);
        const errorDiv = document.getElementById(`${formId}_${field}_error`) || document.getElementById(`${field}_error`);

        if (input) input.classList.add('is-invalid');
        if (errorDiv) errorDiv.textContent = msgs[0];
    }
}

function clearValidationErrors(formId) {
    const form = document.getElementById(formId);
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}

function showToast(type, title, message) {
    // Thay thế bằng thư viện Toast nếu có, hiện tại dùng alert cho đơn giản
    if (type === 'error') console.error(message);
    // alert(`${title}: ${message}`);
}