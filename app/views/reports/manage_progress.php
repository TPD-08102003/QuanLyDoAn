<div class="container py-4">
    <h3 class="mb-4 fw-bold text-primary"><i class="bi bi-calendar-check me-2"></i>Quản lý Tiến độ Đồ án</h3>

    <?php if (empty($projectsProgress)): ?>
        <div class="alert alert-info">Bạn chưa có đồ án nào hoặc chưa thiết lập các giai đoạn báo cáo.</div>
    <?php else: ?>
        <div class="accordion" id="accordionProgress">
            <?php foreach ($projectsProgress as $projId => $project): ?>
                <div class="accordion-item shadow-sm mb-3 border-0 rounded">
                    <h2 class="accordion-header" id="heading<?php echo $projId; ?>">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $projId; ?>" aria-expanded="true">
                            <span class="fw-bold fs-5"><?php echo htmlspecialchars($project['title']); ?></span>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $projId; ?>" class="accordion-collapse collapse show" data-bs-parent="#accordionProgress">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Giai đoạn</th>
                                            <th>Mô tả yêu cầu</th>
                                            <th>Hạn nộp</th>
                                            <th class="text-center">Trạng thái SV</th>
                                            <th class="text-center">Điểm</th>
                                            <th class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($project['types'] as $type): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $type['type_name']; ?></span>
                                                </td>

                                                <td><?php echo htmlspecialchars($type['description']); ?></td>

                                                <td class="fw-bold <?php echo (strtotime($type['deadline']) < time()) ? 'text-danger' : 'text-success'; ?>">
                                                    <?php echo date('d/m/Y', strtotime($type['deadline'])); ?>
                                                </td>

                                                <td class="text-center">
                                                    <?php
                                                    // Xử lý hiển thị trạng thái
                                                    $status = $type['report_status'] ?? 'ChuaNop';
                                                    $submittedAt = $type['submitted_at'];

                                                    $statusBadge = 'bg-secondary bg-opacity-50 text-dark';
                                                    $statusText = 'Chưa nộp';

                                                    if ($status == 'DaNop' || $status == 'DaCham') {
                                                        $statusBadge = 'bg-success';
                                                        $statusText = 'Đã nộp';
                                                    } elseif ($status == 'Tre') {
                                                        $statusBadge = 'bg-danger';
                                                        $statusText = 'Nộp trễ';
                                                    } elseif (strtotime($type['deadline']) < time()) {
                                                        $statusBadge = 'bg-secondary';
                                                        $statusText = 'Quá hạn';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span>
                                                </td>

                                                <td class="text-center">
                                                    <?php
                                                    // Kiểm tra nếu có điểm (khác null)
                                                    if (isset($type['actual_score']) && $type['actual_score'] !== null) {
                                                        // Hiển thị điểm đậm
                                                        echo '<span class="fw-bold fs-5 text-primary">' . $type['actual_score'] . '</span>';
                                                        // Hiển thị thang điểm nhỏ bên cạnh (VD: / 10)
                                                        echo '<span class="text-muted small ms-1"> / ' . $type['max_score'] . '</span>';
                                                    } else {
                                                        // Nếu chưa có điểm
                                                        echo '<span class="text-muted fst-italic small">Chưa chấm</span>';
                                                    }
                                                    ?>
                                                </td>

                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-warning btn-edit"
                                                            data-id="<?php echo $type['type_id']; ?>" title="Chỉnh sửa deadline">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>

                                                        <?php if ($type['report_id']): ?>
                                                            <a href="/quanlydoan/report/grading/<?php echo $type['report_id']; ?>"
                                                                class="btn btn-sm btn-outline-primary" title="Chấm điểm & Xem file">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Cập nhật Tiến độ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editTypeForm">
                        <div class="modal-body">
                            <input type="hidden" name="type_id" id="modal_type_id">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên giai đoạn</label>
                                <input type="text" class="form-control" id="modal_type_name" disabled readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mô tả yêu cầu</label>
                                <textarea class="form-control" name="description" id="modal_description" rows="3"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Hạn nộp</label>
                                    <input type="date" class="form-control" name="deadline" id="modal_deadline" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Điểm tối đa</label>
                                    <input type="number" step="0.1" class="form-control" name="max_score" id="modal_max_score">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const editModal = new bootstrap.Modal(document.getElementById('editTypeModal'));

                // Bắt sự kiện click nút Sửa
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const typeId = this.dataset.id;

                        // Fetch dữ liệu cũ
                        fetch(`/quanlydoan/report/get_type_detail?id=${typeId}`)
                            .then(res => res.json())
                            .then(res => {
                                if (res.success) {
                                    const data = res.data;
                                    document.getElementById('modal_type_id').value = data.type_id;
                                    document.getElementById('modal_type_name').value = data.type_name;
                                    document.getElementById('modal_description').value = data.description;
                                    document.getElementById('modal_deadline').value = data.deadline;
                                    document.getElementById('modal_max_score').value = data.max_score;

                                    editModal.show();
                                }
                            });
                    });
                });

                // Xử lý submit form
                document.getElementById('editTypeForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('/quanlydoan/report/update_type', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload(); // Tải lại trang để cập nhật dữ liệu
                            } else {
                                alert('Lỗi: ' + data.message);
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
        </script>