<?php
// views/projects/create.php

// XÁC ĐỊNH URL XỬ LÝ FORM
// Nếu là giảng viên ($isLecturer = true), gửi đến storeByLecturer
// Nếu là Admin, gửi đến store (hoặc bạn có thể sửa thành storeByAdmin tùy logic của bạn)
$submitUrl = ($isLecturer ?? false) ? '/quanlydoan/project/storeByLecturer' : '/quanlydoan/project/store';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Đăng ký Đồ án Mới
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="createProjectForm">

                        <h6 class="text-primary fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Thông tin Giảng viên</h6>

                        <?php if (isset($isLecturer) && $isLecturer && isset($currentLecturer)): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Giảng viên hướng dẫn</label>
                                    <input type="text" class="form-control bg-light fw-bold text-dark"
                                        value="<?php echo htmlspecialchars($currentLecturer['full_name']); ?>" readonly>
                                    <input type="hidden" name="lecturer_id" value="<?php echo $currentLecturer['lecturer_id']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Khoa / Bộ môn</label>
                                    <input type="text" class="form-control bg-light"
                                        value="<?php echo htmlspecialchars($currentLecturer['faculty_name']); ?>" readonly>
                                    <input type="hidden" name="faculty_id" value="<?php echo $currentLecturer['faculty_id']; ?>">
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info border-0 bg-info bg-opacity-10">
                                <i class="bi bi-info-circle me-2"></i>Bạn đang tạo đồ án với quyền Quản trị viên.
                            </div>
                            <div class="mb-3">
                                <label for="lecturer_id" class="form-label fw-bold">Chọn Giảng viên <span class="text-danger">*</span></label>
                                <select class="form-select" id="lecturer_id" name="lecturer_id" required>
                                    <option value="">-- Chọn Giảng viên --</option>
                                    <?php if (isset($lecturers)): ?>
                                        <?php foreach ($lecturers as $lec): ?>
                                            <option value="<?php echo $lec['lecturer_id']; ?>">
                                                <?php echo htmlspecialchars($lec['full_name']); ?> - <?php echo htmlspecialchars($lec['faculty_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4 text-muted opacity-25">

                        <h6 class="text-primary fw-bold mb-3"><i class="bi bi-journal-text me-2"></i>Chi tiết Đồ án</h6>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Tên đề tài đồ án <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title"
                                placeholder="Ví dụ: Xây dựng hệ thống quản lý..." required>
                        </div>

                        <div class="mb-3">
                            <label for="max_students" class="form-label fw-bold">Số lượng sinh viên tối đa</label>
                            <select class="form-select w-auto" id="max_students" name="max_students">
                                <option value="1">1 Sinh viên</option>
                                <option value="2">2 Sinh viên</option>
                                <option value="3" selected>3 Sinh viên</option>
                            </select>
                            <div class="form-text">Số lượng thành viên tối đa cho phép trong một nhóm thực hiện đề tài này.</div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Mô tả chi tiết / Yêu cầu</label>
                            <textarea class="form-control" id="description" name="description" rows="5"
                                placeholder="Mô tả mục tiêu, yêu cầu công nghệ, phạm vi của đồ án..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/quanlydoan/project/myProjects" class="btn btn-light rounded-pill px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn">
                                <i class="bi bi-save me-2"></i>Lưu Đồ án
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('createProjectForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('submitBtn');
        const originalText = btn.innerHTML;

        // Hiển thị trạng thái loading
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

        const formData = new FormData(this);

        // Gửi đến URL đã được xác định bởi PHP ở đầu file ($submitUrl)
        fetch('<?php echo $submitUrl; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Hoặc dùng SweetAlert nếu có
                    // Chuyển hướng về trang danh sách đồ án
                    window.location.href = '/quanlydoan/project/myProjects';
                } else {
                    alert('Lỗi: ' + data.message);
                    // Reset nút bấm nếu lỗi
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi kết nối server.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });
</script>