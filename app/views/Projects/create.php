<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i> Thêm Đồ án Mới</h2>
                </div>
                <div class="card-body">
                    <form id="createProjectForm" action="/quanlydoan/project/store" method="POST">

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Tên Đồ án <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="Nhập tên đồ án (Ví dụ: Ứng dụng Quản lý Đồ án)">
                            <div class="invalid-feedback">Vui lòng nhập tên đồ án.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả chi tiết</label>
                            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Mô tả chi tiết về mục tiêu, công nghệ sử dụng..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="faculty_id" class="form-label fw-bold">Khoa Phụ trách <span class="text-danger">*</span></label>
                            <select class="form-select" id="faculty_id" name="faculty_id" required>
                                <option value="" disabled selected>Chọn Khoa</option>
                                <?php if (isset($faculties)): ?>
                                    <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?php echo htmlspecialchars($faculty['faculty_id']); ?>">
                                            <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Vui lòng chọn khoa phụ trách.</div>
                        </div>

                        <div class="mb-3">
                            <label for="lecturer_id" class="form-label fw-bold">Giảng viên Hướng dẫn</label>
                            <p class="form-control bg-light">
                                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Tự động gán cho Giảng viên hiện tại'); ?>
                            </p>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i> Lưu Đồ án
                            </button>
                            <a href="/quanlydoan/project/myProjects" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>