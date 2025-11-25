<?php
// views/projects/edit.php
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-warning text-dark py-3 rounded-top-4">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa Đồ án
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="editProjectForm">
                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">

                        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <small class="text-muted d-block">Giảng viên</small>
                                <strong><?php echo htmlspecialchars($project['lecturer_name']); ?></strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Khoa</small>
                                <strong><?php echo htmlspecialchars($project['faculty_name']); ?></strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Tên đề tài đồ án <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title"
                                value="<?php echo htmlspecialchars($project['title']); ?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="max_students" class="form-label fw-bold">Số lượng sinh viên tối đa</label>

                                <?php if (isset($currentCount) && $currentCount > 0): ?>
                                    <div class="text-info mb-1" style="font-size: 0.9em;">
                                        <i class="bi bi-info-circle"></i>
                                        Hiện tại đã có <strong><?php echo $currentCount; ?></strong> sinh viên đăng ký.
                                    </div>
                                <?php endif; ?>

                                <select class="form-select" id="max_students" name="max_students">
                                    <?php
                                    // Mặc định là 0 nếu không có biến (để tránh lỗi undefined variable)
                                    $registered = isset($currentCount) ? $currentCount : 0;
                                    ?>

                                    <option value="1"
                                        <?php echo ($project['max_students'] == 1) ? 'selected' : ''; ?>
                                        <?php echo ($registered > 1) ? 'disabled class="bg-light text-muted"' : ''; ?>>
                                        1 Sinh viên <?php echo ($registered > 1) ? '(Không khả dụng)' : ''; ?>
                                    </option>

                                    <option value="2"
                                        <?php echo ($project['max_students'] == 2) ? 'selected' : ''; ?>
                                        <?php echo ($registered > 2) ? 'disabled class="bg-light text-muted"' : ''; ?>>
                                        2 Sinh viên <?php echo ($registered > 2) ? '(Không khả dụng)' : ''; ?>
                                    </option>

                                    <option value="3" <?php echo ($project['max_students'] == 3) ? 'selected' : ''; ?>>
                                        3 Sinh viên
                                    </option>
                                </select>

                                <?php if (isset($currentCount) && $currentCount > 0): ?>
                                    <div class="form-text text-muted">
                                        Bạn không thể giảm số lượng tối đa thấp hơn số sinh viên hiện có.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Mô tả chi tiết / Yêu cầu</label>
                            <textarea class="form-control" id="description" name="description" rows="6"><?php echo htmlspecialchars($project['description']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/quanlydoan/project/myProjects" class="btn btn-light rounded-pill px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-4" id="submitBtn">
                                <i class="bi bi-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('editProjectForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('submitBtn');
        const originalText = btn.innerHTML;
        const projectId = document.querySelector('input[name="project_id"]').value;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

        const formData = new FormData(this);

        fetch(`/quanlydoan/project/update/${projectId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '/quanlydoan/project/myProjects';
                } else {
                    alert('Lỗi: ' + data.message);
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