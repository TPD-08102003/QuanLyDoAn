<?php
// views/projects/approve.php
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
?>

<h1 class="page-title h2">Duyệt Đồ Án</h1>

<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap gap-3 mb-3 align-items-center">
            <div class="me-auto">
                <form method="GET" action="/quanlydoan/Project/approve" class="d-inline-flex align-items-center gap-2">
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm tiêu đề, giảng viên, khoa..."
                        value="<?= htmlspecialchars($keyword ?? '') ?>" style="width: 380px;">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    <?php if (!empty($keyword)): ?>
                        <a href="/quanlydoan/Project/approve" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </form>
            </div>
            <div>
                <button class="btn btn-success btn-sm" onclick="batchApprove()"><i class="bi bi-check-lg"></i> Duyệt hàng loạt</button>
                <button class="btn btn-danger btn-sm" onclick="batchReject()"><i class="bi bi-x-lg"></i> Từ chối hàng loạt</button>
            </div>
        </div>

        <!-- Bảng -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Tiêu đề đồ án</th>
                        <th>Giảng viên</th>
                        <th>Khoa</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Không có đồ án chờ duyệt</td>
                        </tr>
                        <?php else: foreach ($projects as $project): ?>
                            <tr>
                                <td><input type="checkbox" class="project-checkbox" value="<?= $project['project_id'] ?>"></td>
                                <td><?= $project['project_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($project['title']) ?></strong>
                                    <?php if (!empty($project['description'])): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($project['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công') ?></td>
                                <td><?= htmlspecialchars($project['faculty_name'] ?? '') ?></td>
                                <td><?= date('d/m/Y', strtotime($project['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewProject(<?= $project['project_id'] ?>)"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-success" onclick="approveProject(<?= $project['project_id'] ?>)"><i class="bi bi-check"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectProject(<?= $project['project_id'] ?>)"><i class="bi bi-x"></i></button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang – ĐÃ SỬA HOÀN TOÀN ĐỂ GIỮ KEYWORD -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Trước -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="/quanlydoan/Project/approve?page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>">
                            Trước
                        </a>
                    </li>

                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);

                    if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="/quanlydoan/Project/approve?page=1&keyword=<?= urlencode($keyword ?? '') ?>">1</a></li>
                        <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="/quanlydoan/Project/approve?page=<?= $i ?>&keyword=<?= urlencode($keyword ?? '') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="/quanlydoan/Project/approve?page=<?= $totalPages ?>&keyword=<?= urlencode($keyword ?? '') ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>

                    <!-- Sau -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="/quanlydoan/Project/approve?page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>">
                            Sau
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal chi tiết -->
<div class="modal fade" id="projectDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đồ án</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="projectDetailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showAlert(message, type = 'success') {
        const html = `<div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top:10px;right:10px;z-index:9999;">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
        document.body.insertAdjacentHTML('afterbegin', html);
        setTimeout(() => document.querySelector('.alert.position-fixed')?.remove(), 6000);
    }

    // Select all
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.project-checkbox').forEach(cb => cb.checked = this.checked);
    });

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.project-checkbox:checked')).map(cb => cb.value);
    }

    // Duyệt / Từ chối hàng loạt
    function batchApprove() {
        const ids = getSelectedIds();
        if (!ids.length) return showAlert('Chưa chọn đồ án', 'warning');
        changeStatusBatch(ids, 'DaDuyet');
    }

    function batchReject() {
        const ids = getSelectedIds();
        if (!ids.length) return showAlert('Chưa chọn đồ án', 'warning');
        changeStatusBatch(ids, 'Huy');
    }

    // Thay đổi trạng thái hàng loạt
    function changeStatusBatch(ids, status) {
        const fd = new FormData();
        fd.append('ids', JSON.stringify(ids));
        fd.append('status', status);

        fetch('/quanlydoan/Project/changeProjectStatusBatch', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'danger');
                if (data.success) setTimeout(() => location.reload(), 1500);
            });
    }

    // Duyệt / Từ chối đơn lẻ
    function approveProject(id) {
        changeStatusSingle(id, 'DaDuyet');
    }

    function rejectProject(id) {
        changeStatusSingle(id, 'Huy');
    }

    function changeStatusSingle(id, status) {
        const fd = new FormData();
        fd.append('status', status);

        fetch(`/quanlydoan/Project/changeProjectStatus/${id}`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'danger');
                if (data.success) setTimeout(() => location.reload(), 1500);
            });
    }

    // Xem chi tiết
    function viewProject(id) {
        fetch(`/quanlydoan/Project/getProjectDetails/${id}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return showAlert('Không tải được chi tiết', 'danger');
                const p = data.project;
                const html = `
                <table class="table table-bordered">
                    <tr><th width="180">Tiêu đề</th><td>${p.title}</td></tr>
                    <tr><th>Mô tả</th><td>${p.description || '<em>Không có</em>'}</td></tr>
                    <tr><th>Giảng viên</th><td>${p.lecturer_name}</td></tr>
                    <tr><th>Khoa</th><td>${p.faculty_name || '-'} </td></tr>
                    <tr><th>Trạng thái</th><td><span class="badge bg-warning">Chờ duyệt</span></td></tr>
                    <tr><th>Ngày tạo</th><td>${p.created_at_formatted}</td></tr>
                </table>`;
                document.getElementById('projectDetailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('projectDetailsModal')).show();
            });
    }
</script>