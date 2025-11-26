<div class="container-fluid px-4">
    <h2 class="mt-4">Quản lý & Gửi Thông Báo</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/quanlydoan/HomeAdmin/index">Dashboard</a></li>
        <li class="breadcrumb-item active">Thông báo</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-paper-plane me-1"></i>
                    Soạn thông báo
                </div>
                <div class="card-body">
                    <form id="sendNotificationForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="Ví dụ: Thông báo lịch bảo vệ đồ án">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phương thức gửi:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_type" id="typeGroup" value="group" checked onchange="toggleSendType()">
                                    <label class="form-check-label" for="typeGroup">Gửi theo Nhóm (Toàn bộ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_type" id="typeIndividual" value="individual" onchange="toggleSendType()">
                                    <label class="form-check-label" for="typeIndividual">Gửi cá nhân (Chọn từng người)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="groupSelectionArea">
                            <label class="form-label fw-bold">Chọn nhóm đối tượng:</label>
                            <select class="form-select" name="target_group">
                                <option value="all">Tất cả người dùng hệ thống</option>
                                <option value="student">Tất cả Sinh viên</option>
                                <option value="teacher">Tất cả Giảng viên</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="individualSelectionArea">
                            <label class="form-label fw-bold">Tìm và chọn người nhận:</label>
                            <select class="form-control select2" name="user_ids[]" id="userSelect" multiple="multiple" style="width: 100%;">
                            </select>
                            <div class="form-text">Nhập tên, MSSV hoặc MSGV để tìm kiếm. Có thể chọn nhiều người.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="5" required placeholder="Nhập nội dung..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-2"></i>Gửi ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-lightbulb me-1"></i>
                    Hướng dẫn
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Gửi theo Nhóm:</strong> Dùng để thông báo chung (VD: Nghỉ lễ, Quy chế mới).</li>
                        <li><strong>Gửi cá nhân:</strong> Dùng để nhắc nhở riêng (VD: Nhắc nộp bài, thông báo duyệt đề tài riêng).</li>
                        <li>Hệ thống hỗ trợ tìm kiếm theo Tên hoặc Mã số (MSSV/MSGV).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Khởi tạo khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo Select2
        $('#userSelect').select2({
            theme: "bootstrap-5",
            placeholder: "Chọn người nhận...",
            allowClear: true,
            language: {
                noResults: function() {
                    return "Không tìm thấy kết quả";
                }
            }
        });

        // Load danh sách user ngay khi vào trang để sẵn sàng
        loadReceiversData();
    });

    // 2. Hàm chuyển đổi giao diện Group/Individual
    function toggleSendType() {
        const isIndividual = document.getElementById('typeIndividual').checked;
        const groupArea = document.getElementById('groupSelectionArea');
        const indArea = document.getElementById('individualSelectionArea');
        const userSelect = $('#userSelect'); // JQuery object for Select2

        if (isIndividual) {
            groupArea.classList.add('d-none');
            indArea.classList.remove('d-none');
            userSelect.prop('required', true); // Bắt buộc chọn người
        } else {
            groupArea.classList.remove('d-none');
            indArea.classList.add('d-none');
            userSelect.prop('required', false);
        }
    }

    // 3. Hàm lấy dữ liệu user qua AJAX
    function loadReceiversData() {
        fetch('/quanlydoan/Notification/getReceivers')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = $('#userSelect');
                    select.empty(); // Xóa cũ

                    // Tạo Optgroup cho Sinh viên
                    const studentGroup = $('<optgroup label="Sinh viên">');
                    data.students.forEach(st => {
                        studentGroup.append(new Option(`${st.full_name} (${st.code})`, st.user_id));
                    });
                    select.append(studentGroup);

                    // Tạo Optgroup cho Giảng viên
                    const lecturerGroup = $('<optgroup label="Giảng viên">');
                    data.lecturers.forEach(lec => {
                        lecturerGroup.append(new Option(`${lec.full_name} (${lec.code})`, lec.user_id));
                    });
                    select.append(lecturerGroup);

                    // Refresh lại Select2 để nhận dữ liệu mới
                    select.trigger('change');
                } else {
                    console.error('Lỗi tải danh sách người dùng:', data.message);
                }
            })
            .catch(err => console.error('Fetch error:', err));
    }

    // 4. Xử lý Submit Form
    document.getElementById('sendNotificationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Kiểm tra validation bổ sung
        const isIndividual = document.getElementById('typeIndividual').checked;
        if (isIndividual) {
            const selectedUsers = $('#userSelect').val();
            if (!selectedUsers || selectedUsers.length === 0) {
                alert('Vui lòng chọn ít nhất một người nhận!');
                return;
            }
        }

        const btn = this.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang gửi...';
        btn.disabled = true;

        const formData = new FormData(this);

        // Vì Select2 multi-select không tự động bind vào FormData đúng cách trong một số trường hợp,
        // nhưng với name="user_ids[]" và FormData chuẩn thì thường nó tự nhận.
        // Tuy nhiên, nếu gặp lỗi, code PHP đã xử lý $_POST['user_ids']

        fetch('/quanlydoan/Notification/send', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.reset();
                    $('#userSelect').val(null).trigger('change'); // Reset Select2
                    document.getElementById('typeGroup').checked = true; // Reset về mặc định
                    toggleSendType();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi thông báo.');
            })
            .finally(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
    });
</script>