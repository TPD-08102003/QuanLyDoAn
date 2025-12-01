<div class="container-fluid py-4">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-primary">Bảng điểm: <?= htmlspecialchars($project['title']) ?></h5>
                <small class="text-muted">GVHD: <?= htmlspecialchars($project['lecturer_name']) ?></small>
            </div>

            <?php
            $backLink = ($role === 'student') ? '/quanlydoan/project/myProjects' : '/quanlydoan/project/myProjects';
            ?>
            <a href="<?= $backLink ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 60px;">Nhóm</th>
                            <th class="text-start" style="min-width: 250px;">Thành viên</th>

                            <?php foreach ($types as $type): ?>
                                <th style="min-width: 120px;">
                                    <?= htmlspecialchars($type['type_name']) ?>
                                    <br><span class="badge bg-light text-dark border fw-normal" style="font-size: 0.7rem;">Max: <?= $type['max_score'] ?></span>
                                </th>
                            <?php endforeach; ?>

                            <th class="table-primary" style="width: 100px;">Tổng kết</th>
                            <th style="width: 140px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="100%" class="text-center py-4">Chưa có nhóm nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $group): ?>
                                <tr>
                                    <td class="fw-bold text-secondary">#<?= $group['group_id'] ?></td>

                                    <td class="text-start">
                                        <div class="small"><?= $group['members'] ?></div>
                                    </td>

                                    <?php foreach ($types as $type):
                                        $score = $group['scores'][$type['type_id']] ?? null;
                                        // Logic hiển thị điểm thành phần
                                        $showScore = false;
                                        if ($role === 'teacher') {
                                            $showScore = true; // Giảng viên luôn thấy
                                        } elseif ($group['is_published'] == 1) {
                                            $showScore = true; // Sinh viên thấy nếu đã công bố
                                        }
                                    ?>
                                        <td>
                                            <?php if ($showScore): ?>
                                                <?php if ($score !== null): ?>
                                                    <span class="fw-bold <?= $score < 5 ? 'text-danger' : 'text-dark' ?>">
                                                        <?= $score ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="bi bi-eye-slash-fill"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="table-primary p-1">
                                        <?php if ($role === 'teacher'): ?>
                                            <input type="number" step="0.1" min="0" max="10"
                                                class="form-control form-control-sm fw-bold text-center border-0 bg-transparent total-score-input"
                                                style="font-size: 1.1rem;"
                                                data-group-id="<?= $group['group_id'] ?>"
                                                value="<?= $group['total_score'] ?>"
                                                placeholder="...">
                                        <?php else: ?>
                                            <?php if ($group['is_published'] == 1): ?>
                                                <span class="fw-bold fs-5 <?= (!isset($group['total_score']) || $group['total_score'] === '') ? 'text-muted' : ($group['total_score'] < 5 ? 'text-danger' : 'text-primary') ?>">
                                                    <?= (isset($group['total_score']) && $group['total_score'] !== '') ? $group['total_score'] : '--' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic small">Chưa công bố</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($role === 'teacher'): ?>
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input toggle-publish" type="checkbox"
                                                    style="cursor: pointer;"
                                                    data-group-id="<?= $group['group_id'] ?>"
                                                    <?= $group['is_published'] ? 'checked' : '' ?>>
                                            </div>
                                            <small class="status-text fw-bold mt-1 d-block" style="font-size: 0.75rem; color: <?= $group['is_published'] ? '#198754' : '#6c757d' ?>">
                                                <?= $group['is_published'] ? 'Đã công bố' : 'Đang ẩn' ?>
                                            </small>
                                        <?php else: ?>
                                            <?php if ($group['is_published']): ?>
                                                <span class="badge bg-success rounded-pill">Đã công bố</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill">Đang ẩn</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3 d-flex align-items-center shadow-sm rounded-3">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Chú thích:</strong><br>
                    <small>
                        1. Điểm thành phần được hệ thống cập nhật tự động.<br>
                        <?php if ($role === 'teacher'): ?>
                            2. <strong>Điểm Tổng kết</strong>: Nhập trực tiếp vào ô và click ra ngoài để lưu.<br>
                            3. <strong>Trạng thái</strong>: Gạt nút sang xanh để Sinh viên xem được điểm.
                        <?php else: ?>
                            2. Nếu trạng thái là "Đang ẩn", bạn sẽ không thấy điểm số chi tiết.<br>
                            3. Điểm số chỉ hiển thị khi Giảng viên bật công bố.
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($role === 'teacher'): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Cập nhật thành công!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastEl);
            const toastBody = toastEl.querySelector('.toast-body');

            function showToast(message, isError = false) {
                toastBody.textContent = message;
                toastEl.className = `toast align-items-center text-white border-0 ${isError ? 'bg-danger' : 'bg-success'}`;
                toast.show();
            }

            // 1. Xử lý cập nhật điểm tổng kết
            document.querySelectorAll('.total-score-input').forEach(input => {
                input.addEventListener('change', function() {
                    const groupId = this.dataset.groupId;
                    const score = this.value;

                    fetch('/quanlydoan/score/update_total', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `group_id=${groupId}&total_score=${score}`
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) showToast('Đã lưu điểm tổng kết!');
                            else showToast(data.message, true);
                        });
                });
            });

            // 2. Xử lý Toggle Publish
            document.querySelectorAll('.toggle-publish').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const groupId = this.dataset.groupId;
                    const status = this.checked ? 1 : 0;
                    const label = this.closest('td').querySelector('.status-text');

                    fetch('/quanlydoan/score/toggle_publish', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `group_id=${groupId}&status=${status}`
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                label.textContent = status ? 'Đã công bố' : 'Đang ẩn';
                                label.style.color = status ? '#198754' : '#6c757d';
                                showToast(status ? 'Đã công bố điểm cho nhóm!' : 'Đã ẩn điểm của nhóm!');
                            } else {
                                this.checked = !status; // Revert checkbox
                                showToast(data.message, true);
                            }
                        });
                });
            });
        });
    </script>
<?php endif; ?>