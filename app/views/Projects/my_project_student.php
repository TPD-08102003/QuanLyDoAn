<?php
// views/Projects/my_project_student.php

// Giả sử $project, $members, $reports, $studentId được truyền từ controller
if (empty($project)) {
    echo '<div class="container py-5"><div class="alert alert-warning text-center">Bạn chưa tham gia đồ án nào. <a href="/quanlydoan/project" class="alert-link">Đăng ký ngay</a></div></div>';
    return;
}

// Mapping trạng thái
$statusMap = [
    'ChoDuyet' => ['Chờ duyệt', 'secondary'],
    'DaDuyet' => ['Đã duyệt', 'info'],
    'DangThucHien' => ['Đang thực hiện', 'primary'],
    'DaNopBaoCao' => ['Đã nộp báo cáo (Chờ bảo vệ)', 'warning'],
    'DaBaoVe' => ['Đã bảo vệ', 'success'],
    'HoanThanh' => ['Hoàn thành', 'success'],
    'Huy' => ['Đã hủy', 'danger']
];
$statusInfo = $statusMap[$project['status']] ?? [$project['status'], 'secondary'];

$isLeader = ($project['leader_id'] == $studentId); // $studentId từ controller
?>

<div class="container py-4">
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-<?= $statusInfo[1] ?> me-2"><?= $statusInfo[0] ?></span>
                        <span class="text-muted small"><i class="bi bi-calendar3"></i> Tạo ngày: <?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                    </div>
                    <h2 class="fw-bold text-primary mb-3"><?= htmlspecialchars($project['title']) ?></h2>
                    <div class="text-muted">
                        <i class="bi bi-person-video3 me-1"></i> GVHD: <strong><?= htmlspecialchars($project['lecturer_name']) ?></strong>
                        <span class="mx-2">|</span>
                        <i class="bi bi-building"></i> Khoa: <?= htmlspecialchars($project['faculty_name']) ?>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php if ($isLeader && $project['status'] == 'DangThucHien'): ?>
                        <button class="btn btn-success shadow-sm" onclick="confirmFinishProject(<?= $project['project_id'] ?>)">
                            <i class="bi bi-check-circle-fill me-2"></i> Báo cáo hoàn thành
                        </button>
                        <div class="form-text text-end mt-1 small">Chỉ trưởng nhóm mới thấy nút này</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Thành viên nhóm</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($members as $mem):
                        $isMemLeader = ($mem['student_id'] == $project['leader_id']);
                    ?>
                        <div class="list-group-item py-3 d-flex align-items-center">
                            <img src="/quanlydoan/assets/images/<?= $mem['avatar'] ?: 'profile.png' ?>" class="rounded-circle me-3 border" width="45" height="45" style="object-fit: cover;">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">
                                    <?= htmlspecialchars($mem['full_name']) ?>
                                    <?php if ($isMemLeader): ?> <i class="bi bi-star-fill text-warning small" title="Trưởng nhóm"></i><?php endif; ?>
                                </h6>
                                <small class="text-muted"><?= $mem['mssv'] ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Tiến độ báo cáo</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Giai đoạn</th>
                                <th>Hạn nộp</th>
                                <th>Trạng thái</th>
                                <th>File/Code</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Giảng viên chưa tạo mốc báo cáo nào.</td>
                                </tr>
                                <?php else: foreach ($reports as $rep):
                                    $isLate = (strtotime($rep['deadline']) < time()) && !$rep['submitted_at'];
                                    $repStatus = $rep['report_status'] == 'DaNop' ? 'success' : ($isLate ? 'danger' : 'secondary');
                                    $repLabel = $rep['report_status'] == 'DaNop' ? 'Đã nộp' : ($isLate ? 'Trễ hạn' : 'Chưa nộp');
                                ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?= htmlspecialchars($rep['description']) ?></div>
                                            <small class="text-muted"><?= $rep['type_name'] ?></small>
                                        </td>
                                        <td>
                                            <span class="<?= $isLate ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($rep['deadline'])) ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-<?= $repStatus ?>"><?= $repLabel ?></span></td>
                                        <td>
                                            <?php if (!empty($rep['file_path'])): ?>
                                                <?php if ($rep['file_type'] === 'LINK'): ?>
                                                    <a href="<?= htmlspecialchars($rep['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Mở Link">
                                                        <i class="bi bi-link-45deg"></i> Link
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/quanlydoan/assets/uploads/reports/<?= $rep['file_path'] ?>" download class="btn btn-sm btn-outline-secondary" title="Tải về">
                                                        <i class="bi bi-download"></i> <?= strtoupper($rep['file_type']) ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-primary"
                                                onclick="openUploadModal(<?= $rep['type_id'] ?>, '<?= htmlspecialchars($rep['description']) ?>')"
                                                <?= ($project['status'] == 'HoanThanh' || $project['status'] == 'Huy' || $project['status'] == 'DaBaoVe') ? 'disabled' : '' ?>>
                                                <i class="bi bi-upload"></i> Nộp
                                            </button>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nộp Báo Cáo/Code -->
<div class="modal fade" id="uploadReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Nộp bài: <span id="modalReportName" class="fw-bold text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="group_id" value="<?= $project['group_id'] ?>">
                    <input type="hidden" name="type_id" id="modalTypeId">

                    <div class="mb-3">
                        <label class="form-label">Tải file/code lên (PDF, DOCX, ZIP...)</label>
                        <input type="file" class="form-control" name="report_file">
                    </div>
                    <div class="text-center text-muted my-2 fw-bold" style="font-size: 0.8rem;">HOẶC</div>
                    <div class="mb-3">
                        <label class="form-label">Dán đường dẫn (Google Drive, Github...)</label>
                        <input type="url" class="form-control" name="link_url" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitReport">
                        <span class="spinner-border spinner-border-sm d-none"></span> Nộp ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openUploadModal(typeId, name) {
        document.getElementById('modalTypeId').value = typeId;
        document.getElementById('modalReportName').textContent = name;
        new bootstrap.Modal(document.getElementById('uploadReportModal')).show();
    }

    // Submit nộp báo cáo
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitReport');
        const spinner = btn.querySelector('.spinner-border');

        if (!this.report_file.value && !this.link_url.value) {
            alert('Vui lòng chọn file hoặc nhập link!');
            return;
        }

        btn.disabled = true;
        spinner.classList.remove('d-none');

        fetch('/quanlydoan/project/submitReport', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) window.location.reload();
            })
            .catch(() => alert('Lỗi kết nối.'))
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    // Leader báo cáo hoàn thành
    function confirmFinishProject(projectId) {
        if (!confirm('Xác nhận đồ án đã hoàn thành? Sau khi xác nhận, chờ GV duyệt.')) return;

        fetch('/quanlydoan/project/finishProject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'project_id=' + projectId
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) window.location.reload();
            })
            .catch(() => alert('Lỗi hệ thống.'));
    }
</script>