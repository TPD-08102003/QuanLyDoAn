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

// Kiểm tra xem có được phép hủy đăng ký không (Không được hủy khi đã nộp báo cáo hoặc hoàn thành)
$canLeave = !in_array($project['status'], ['DaNopBaoCao', 'DaBaoVe', 'HoanThanh', 'Huy']);
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
                    <div class="d-flex flex-column align-items-end gap-2">

                        <?php if ($isLeader && $project['status'] == 'DangThucHien'): ?>
                            <button class="btn btn-success shadow-sm w-100" onclick="confirmFinishProject(<?= $project['project_id'] ?>)">
                                <i class="bi bi-check-circle-fill me-2"></i> Báo cáo hoàn thành
                            </button>
                        <?php endif; ?>

                        <?php if ($canLeave): ?>
                            <?php if (!$isLeader): ?>
                                <button class="btn btn-outline-danger shadow-sm w-100" onclick="cancelRegistration(<?= $project['group_id'] ?>)">
                                    <i class="bi bi-x-circle me-2"></i> Hủy đăng ký / Rời nhóm
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary shadow-sm w-100" disabled title="Trưởng nhóm phải chuyển quyền trước khi rời nhóm">
                                    <i class="bi bi-lock-fill me-2"></i> Hủy đăng ký (Leader)
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
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
                            <img src="/quanlydoan/assets/images/<?= !empty($mem['avatar']) ? $mem['avatar'] : 'profile.png' ?>" class="rounded-circle me-3 border" width="45" height="45" style="object-fit: cover;">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">
                                    <?= htmlspecialchars($mem['full_name']) ?>
                                    <?php if ($mem['student_id'] == $studentId): ?> <span class="badge bg-light text-dark border ms-1">Tôi</span> <?php endif; ?>
                                    <?php if ($isMemLeader): ?> <i class="bi bi-star-fill text-warning small ms-1" title="Trưởng nhóm"></i><?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            Swal.fire('Thông báo', 'Vui lòng chọn file hoặc nhập link!', 'warning');
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
                if (data.success) {
                    Swal.fire('Thành công', data.message, 'success').then(() => window.location.reload());
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Lỗi', 'Lỗi kết nối server', 'error'))
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    // Leader báo cáo hoàn thành
    function confirmFinishProject(projectId) {
        Swal.fire({
            title: 'Xác nhận hoàn thành?',
            text: "Sau khi xác nhận, đồ án sẽ chuyển sang trạng thái chờ giảng viên duyệt bảo vệ.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/quanlydoan/project/finishProject', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'project_id=' + projectId
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Thành công', data.message, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Lỗi', 'Lỗi hệ thống', 'error'));
            }
        });
    }

    // Hủy đăng ký / Rời nhóm
    function cancelRegistration(groupId) {
        Swal.fire({
            title: 'Hủy đăng ký?',
            text: "Bạn có chắc chắn muốn hủy đăng ký đồ án này? Bạn sẽ bị xóa khỏi nhóm ngay lập tức.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý hủy',
            cancelButtonText: 'Giữ lại'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('group_id', groupId);

                // Gọi đến GroupController để xử lý rời nhóm
                fetch('/quanlydoan/group/leave', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Đã hủy!', 'Bạn đã rời khỏi nhóm thành công.', 'success')
                                .then(() => {
                                    // Chuyển hướng về trang danh sách đồ án đăng ký
                                    window.location.href = '/quanlydoan/project';
                                });
                        } else {
                            Swal.fire('Không thể hủy', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Lỗi', 'Có lỗi xảy ra khi kết nối server', 'error');
                    });
            }
        });
    }
</script>