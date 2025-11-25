<script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
<script src="https://unpkg.com/docx-preview/dist/docx-preview.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-clipboard-check me-2"></i>Chấm điểm Đồ án</h3>
        <a href="/quanlydoan/report/manage_progress" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">Thông tin bài nộp</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Đề tài</label>
                        <p class="fs-5 fw-bold text-primary mb-0"><?php echo htmlspecialchars($report['project_title']); ?></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold">Giai đoạn</label>
                            <p class="mb-0"><span class="badge bg-info text-dark"><?php echo $report['type_name']; ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold">Thời gian nộp</label>
                            <p class="mb-0 fw-bold <?php echo ($report['status'] == 'Tre') ? 'text-danger' : 'text-success'; ?>">
                                <?php echo date('H:i - d/m/Y', strtotime($report['submitted_at'])); ?>
                                <?php if ($report['status'] == 'Tre') echo '(Nộp trễ)'; ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3"><i class="bi bi-paperclip me-2"></i>Tài liệu đính kèm:</h6>
                    <?php if (empty($files)): ?>
                        <p class="text-muted fst-italic">Không có file nào được tải lên.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($files as $file): ?>
                                <?php
                                $fileUrl = ($file['file_type'] == 'LINK') ? $file['file_path'] : '/quanlydoan/uploads/reports/' . $file['file_path'];
                                $ext = strtoupper(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                $icon = 'bi-file-earmark-text';
                                $actionBtn = '';

                                // Logic hiển thị nút Xem
                                if ($ext === 'PDF') {
                                    $icon = 'bi-file-pdf text-danger';
                                    $actionBtn = "<button type='button' class='btn btn-sm btn-outline-primary' onclick=\"previewFile('$fileUrl', 'PDF', '{$file['file_name']}')\"><i class='bi bi-eye'></i> Xem</button>";
                                } elseif ($ext === 'DOCX') {
                                    $icon = 'bi-file-word text-primary';
                                    $actionBtn = "<button type='button' class='btn btn-sm btn-outline-primary' onclick=\"previewFile('$fileUrl', 'DOCX', '{$file['file_name']}')\"><i class='bi bi-eye'></i> Xem</button>";
                                } elseif ($file['file_type'] === 'LINK') {
                                    $icon = 'bi-link-45deg text-info';
                                    $actionBtn = "<a href='$fileUrl' target='_blank' class='btn btn-sm btn-outline-info'><i class='bi bi-box-arrow-up-right'></i> Mở Link</a>";
                                } else {
                                    $icon = ($ext === 'ZIP' || $ext === 'RAR') ? 'bi-file-zip text-warning' : 'bi-file-earmark';
                                }
                                ?>

                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-truncate" style="max-width: 65%;">
                                        <i class="bi <?php echo $icon; ?> fs-4 me-3"></i>
                                        <div class="text-truncate" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                            <div class="fw-bold"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                            <?php if ($file['file_type'] != 'LINK'): ?>
                                                <small class="text-muted"><?php echo round($file['file_size'] / 1024, 2); ?> KB</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <?php echo $actionBtn; ?>
                                        <?php if ($file['file_type'] != 'LINK'): ?>
                                            <a href="<?php echo $fileUrl; ?>" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-people me-2"></i>Thành viên nhóm:</h6>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($members as $mem): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span><?php echo htmlspecialchars($mem['full_name']); ?></span>
                                <span class="badge bg-light text-dark border"><?php echo $mem['mssv']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pen me-2"></i>Đánh giá & Cho điểm</h5>
                </div>
                <div class="card-body p-4">
                    <form id="gradingForm">
                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Điểm số (Thang 10)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" class="form-control fw-bold text-center text-primary"
                                    name="score" id="scoreInput" step="0.1" min="0" max="10"
                                    value="<?php echo $report['score'] ?? ''; ?>" required placeholder="0.0">
                                <span class="input-group-text fw-bold">/ <?php echo $report['max_score']; ?></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nhận xét chi tiết</label>
                            <textarea class="form-control" name="comment" rows="6"
                                placeholder="Nhập nhận xét..."><?php echo htmlspecialchars($report['comment'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill" id="btnSubmitGrade">
                                <i class="bi bi-check-circle-fill me-2"></i>Lưu kết quả
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
        <div class="modal-content h-100">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="previewTitle">Xem tài liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 position-relative bg-dark bg-opacity-10">
                <div id="loadingPreview" class="position-absolute top-50 start-50 translate-middle text-center d-none">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 text-dark fw-bold">Đang tải...</div>
                </div>

                <div id="pdfContainer" class="w-100 h-100 d-none d-flex flex-column align-items-center justify-content-center">
                    <iframe id="pdfFrame" class="w-100 h-100 border-0"></iframe>
                    <div id="pdfFallback" class="position-absolute top-50 start-50 translate-middle text-center d-none">
                        <div class="alert alert-warning shadow">
                            <i class="bi bi-shield-lock fs-1"></i><br>
                            <strong>Trình duyệt đã chặn hiển thị trực tiếp.</strong><br>
                            Do chính sách bảo mật (X-Frame-Options).
                            <div class="mt-3">
                                <a id="pdfNewTabBtn" href="#" target="_blank" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Mở PDF trong tab mới
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="wordContainer" class="w-100 h-100 bg-white overflow-auto p-4 d-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let previewModal, pdfFrame, wordContainer, loadingDiv, modalTitle, pdfFallback, pdfNewTabBtn, pdfContainer;

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('previewModal');
        if (typeof bootstrap !== 'undefined') previewModal = new bootstrap.Modal(modalEl);

        pdfFrame = document.getElementById('pdfFrame');
        pdfContainer = document.getElementById('pdfContainer');
        pdfFallback = document.getElementById('pdfFallback');
        pdfNewTabBtn = document.getElementById('pdfNewTabBtn');
        wordContainer = document.getElementById('wordContainer');
        loadingDiv = document.getElementById('loadingPreview');
        modalTitle = document.getElementById('previewTitle');

        // Form Submit
        document.getElementById('gradingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitGrade');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            fetch('/quanlydoan/report/store_grade', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                    else {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi kết nối.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    });

    window.previewFile = function(url, type, name) {
        if (!previewModal) return;
        modalTitle.innerText = 'Đang xem: ' + name;

        // Reset UI
        pdfContainer.classList.add('d-none');
        wordContainer.classList.add('d-none');
        loadingDiv.classList.remove('d-none');
        pdfFallback.classList.add('d-none');
        pdfFrame.classList.remove('d-none');

        previewModal.show();

        if (type === 'PDF') {
            pdfContainer.classList.remove('d-none');
            pdfNewTabBtn.href = url;
            pdfFrame.src = url;

            pdfFrame.onload = function() {
                loadingDiv.classList.add('d-none');
                setTimeout(() => {
                    pdfFallback.classList.remove('d-none');
                }, 1000);
            };
            pdfFrame.onerror = function() {
                loadingDiv.classList.add('d-none');
                pdfFrame.classList.add('d-none');
                pdfFallback.classList.remove('d-none');
            };

        } else if (type === 'DOCX') {
            fetch(url).then(res => {
                if (!res.ok) throw new Error("HTTP Lỗi " + res.status);
                return res.blob();
            }).then(blob => {
                loadingDiv.classList.add('d-none');
                wordContainer.classList.remove('d-none');
                wordContainer.innerHTML = '';

                if (typeof docx !== 'undefined') {
                    docx.renderAsync(blob, wordContainer, wordContainer, {
                        className: "docx",
                        inWrapper: true,
                        ignoreWidth: false
                    }).catch(e => {
                        wordContainer.innerHTML = `<div class='alert alert-danger'>Lỗi nội dung file: ${e.message}</div>`;
                    });
                }
            }).catch(err => {
                loadingDiv.classList.add('d-none');
                wordContainer.classList.remove('d-none');
                wordContainer.innerHTML = `<div class='alert alert-danger'>Không tải được file: ${err.message}</div>`;
            });
        }
    };
</script>