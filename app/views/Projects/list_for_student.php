<?php
$keyword = $keyword ?? '';
$page = $page ?? 1;
$isLoggedIn = isset($_SESSION['account_id']);
$isStudent = $_SESSION['role'] ?? null === 'student';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="fw-bold text-primary mb-3">Đăng ký Đồ án Tốt nghiệp</h2>
            <p class="lead text-muted">Chọn đồ án phù hợp với định hướng của bạn. Mỗi sinh viên chỉ được đăng ký <strong>một đồ án</strong>.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="row g-3 align-items-center">
                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto">
                    <form id="filterForm" method="GET" action="" class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                            <span class="input-group-text bg-white border-0 ps-3 text-primary"><i class="bi bi-building"></i></span>
                            <select name="faculty_id" class="form-select border-0 py-2 shadow-none" style="min-width: 180px; cursor: pointer;" onchange="this.form.submit()">
                                <option value="0">-- Tất cả Khoa --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?= $f['faculty_id'] ?>" <?= $f['faculty_id'] == $facultyId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['faculty_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                            <span class="input-group-text bg-white border-0 ps-3 text-primary"><i class="bi bi-person-badge"></i></span>
                            <select name="lecturer_id" class="form-select border-0 py-2 shadow-none" style="min-width: 180px; cursor: pointer;" onchange="this.form.submit()">
                                <option value="0">-- Tất cả Giảng viên --</option>
                                <?php foreach ($lecturers as $l): ?>
                                    <option value="<?= $l['lecturer_id'] ?>" <?= $l['lecturer_id'] == $lecturerId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($facultyId > 0 || $lecturerId > 0): ?>
                            <a href="?page=1" class="btn btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center text-danger" title="Xóa bộ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>

                    <!-- Chuyển đổi hiển thị -->
                    <div class="col-md-4 text-md-end">
                        <div class="btn-group" role="group" aria-label="Chế độ hiển thị">
                            <button type="button" id="cardViewBtn" class="btn btn-outline-primary active" onclick="switchView('card')">
                                <i class="bi bi-grid-3x3-gap"></i> Card
                            </button>
                            <button type="button" id="listViewBtn" class="btn btn-outline-primary" onclick="switchView('list')">
                                <i class="bi bi-list-task"></i> List
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="mt-3 text-muted fs-5">Hiện tại chưa có đồ án nào mở đăng ký.</p>
        </div>
    <?php else: ?>
        <!-- Card View (Mặc định) -->
        <div id="cardView" class="view-mode">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($projects as $project):
                    $isRegistered = in_array($project['project_id'], $registeredProjectIds ?? []);
                    $max = $project['max_students'] ?? 3;
                    $current = $project['current_students'] ?? 0;
                    $isFull = ($current >= $max);

                    $statusBadge = match ($project['status']) {
                        'DaDuyet' => 'info',
                        'DangThucHien' => 'primary',
                        default => 'secondary'
                    };
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 hover-shadow-lg transition">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-<?= $statusBadge ?>">#<?= $project['project_id'] ?></span>

                                    <?php if ($isFull): ?>
                                        <span class="badge bg-danger">Đã đủ (<?= $current ?>/<?= $max ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Còn chỗ (<?= $current ?>/<?= $max ?>)</span>
                                    <?php endif; ?>
                                </div>

                                <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($project['title']) ?></h5>

                                <p class="text-muted small flex-grow-1">
                                    <?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...
                                </p>

                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-person-video3"></i> <?= htmlspecialchars($project['lecturer_name']) ?>
                                        </small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="fw-bold text-primary">
                                            <i class="bi bi-people-fill"></i> <?= $current ?> / <?= $max ?> SV
                                        </small>

                                        <div>
                                            <button onclick="showProjectDetail(<?= $project['project_id'] ?>)" class="btn btn-outline-info btn-sm me-1">
                                                <i class="bi bi-eye"></i> Xem
                                            </button>

                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                                                <?php if ($isRegistered): ?>
                                                    <button class="btn btn-success btn-sm" disabled>
                                                        <i class="bi bi-check-circle"></i> Đã ĐK
                                                    </button>
                                                <?php elseif ($isFull): ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="bi bi-slash-circle"></i> Đầy
                                                    </button>
                                                <?php else: ?>
                                                    <button onclick="registerProject(<?= $project['project_id'] ?>)" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-plus-circle"></i> Đăng ký
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- List View (Ẩn ban đầu) -->
        <div id="listView" class="view-mode" style="display: none;">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Mã số</th>
                                    <th>Tiêu đề</th>
                                    <th width="150">Giảng viên</th>
                                    <th width="120">Số lượng</th>
                                    <th width="120">Trạng thái</th>
                                    <th width="180">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project):
                                    $isRegistered = in_array($project['project_id'], $registeredProjectIds ?? []);
                                    $max = $project['max_students'] ?? 3;
                                    $current = $project['current_students'] ?? 0;
                                    $isFull = ($current >= $max);

                                    $statusBadge = match ($project['status']) {
                                        'DaDuyet' => 'info',
                                        'DangThucHien' => 'primary',
                                        default => 'secondary'
                                    };
                                ?>
                                    <tr class="align-middle">
                                        <td>
                                            <span class="badge bg-<?= $statusBadge ?>">#<?= $project['project_id'] ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($project['title']) ?></div>
                                            <div class="text-muted small mt-1">
                                                <?= htmlspecialchars(substr($project['description'], 0, 150)) ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-video3 me-2 text-muted"></i>
                                                <span><?= htmlspecialchars($project['lecturer_name']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <span class="fw-bold <?= $isFull ? 'text-danger' : 'text-success' ?>">
                                                        <?= $current ?>/<?= $max ?>
                                                    </span>
                                                </div>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar <?= $isFull ? 'bg-danger' : 'bg-success' ?>"
                                                        style="width: <?= min(100, ($current / $max) * 100) ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($isFull): ?>
                                                <span class="badge bg-danger">Đã đầy</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Còn chỗ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button onclick="showProjectDetail(<?= $project['project_id'] ?>)" class="btn btn-outline-info">
                                                    <i class="bi bi-eye"></i> Xem
                                                </button>

                                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                                                    <?php if ($isRegistered): ?>
                                                        <button class="btn btn-success" disabled>
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    <?php elseif ($isFull): ?>
                                                        <button class="btn btn-secondary" disabled>
                                                            <i class="bi bi-slash-circle"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="registerProject(<?= $project['project_id'] ?>)" class="btn btn-primary">
                                                            <i class="bi bi-plus-circle"></i> ĐK
                                                        </button>
                                                    <?php endif; ?>
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
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modal chi tiết đồ án (giữ nguyên) -->
    <div class="modal fade" id="projectDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content overflow-hidden">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalProjectTitle">Đang tải...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="projectDetailModalBody">
                    <div class="p-5 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Đang tải thông tin đồ án...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Mã đồ án: <strong id="modalProjectId">...</strong></small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lưu trạng thái view vào localStorage
    function switchView(view) {
        const cardView = document.getElementById('cardView');
        const listView = document.getElementById('listView');
        const cardBtn = document.getElementById('cardViewBtn');
        const listBtn = document.getElementById('listViewBtn');

        if (view === 'card') {
            cardView.style.display = 'block';
            listView.style.display = 'none';
            cardBtn.classList.add('active');
            listBtn.classList.remove('active');
            localStorage.setItem('projectView', 'card');
        } else {
            cardView.style.display = 'none';
            listView.style.display = 'block';
            cardBtn.classList.remove('active');
            listBtn.classList.add('active');
            localStorage.setItem('projectView', 'list');
        }
    }

    // Khôi phục trạng thái view khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('projectView') || 'card';
        switchView(savedView);
    });

    function showProjectDetail(projectId) {
        const modal = new bootstrap.Modal(document.getElementById('projectDetailModal'));
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');
        const bodyEl = document.getElementById('projectDetailModalBody');

        // 1. Reset trạng thái loading
        titleEl.textContent = 'Đang tải...';
        idEl.textContent = '...';
        bodyEl.innerHTML = `
        <div class="p-5 text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Đang tải dữ liệu...</p>
        </div>`;
        modal.show();

        // 2. Gọi API
        fetch(`/quanlydoan/Project/getProjectDetailsIndex/${projectId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.project) {
                    const p = data.project;
                    const members = data.members || [];

                    titleEl.textContent = p.title;
                    idEl.textContent = '#' + p.project_id;

                    let membersHtml = '';
                    if (members.length > 0) {
                        membersHtml = `<div class="row g-2 mt-2">`;
                        members.forEach(mem => {
                            membersHtml += `
                            <div class="col-md-6">
                                <div class="p-2 border rounded d-flex align-items-center bg-white">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:35px; height:35px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-truncate" style="font-size: 0.9rem;">${mem.full_name}</div>
                                        <div class="text-muted small" style="font-size: 0.8rem;">${mem.mssv}</div>
                                    </div>
                                </div>
                            </div>`;
                        });
                        membersHtml += `</div>`;
                    } else {
                        membersHtml = `
                        <div class="text-center py-4 bg-white rounded-3 border border-dashed mt-2">
                            <i class="bi bi-people text-muted fs-3"></i>
                            <p class="text-muted small mb-0 mt-1">Chưa có thành viên nào đăng ký.</p>
                        </div>`;
                    }

                    bodyEl.innerHTML = `
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="d-flex flex-column h-100 gap-3">
                            <div class="bg-light rounded-4 p-3 border-start border-4 border-info">
                                <h6 class="fw-bold text-dark mb-3 text-uppercase small ls-1">
                                    <i class="bi bi-info-circle-fill me-2 text-info"></i>Thông tin đồ án
                                </h6>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Giảng viên hướng dẫn</small>
                                        <span class="fw-bold text-dark">${p.lecturer_name}</span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Khoa chuyên môn</small>
                                        <span class="fw-bold text-dark">${p.faculty_name || 'N/A'}</span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-1">Trạng thái hiện tại</small>
                                        <span class="badge bg-${p.badge_color} px-3 py-2 rounded-pill">${p.status}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-3 flex-grow-1 border-start border-4 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase small ls-1">
                                        <i class="bi bi-people-fill me-2 text-primary"></i>Nhóm thực hiện
                                    </h6>
                                    <span class="badge bg-primary rounded-pill">${members.length} Sinh viên</span>
                                </div>
                                <hr class="my-2 opacity-25">
                                ${membersHtml}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="bg-white border rounded-4 p-4 h-100 shadow-sm position-relative overflow-hidden">
                            <h5 class="fw-bold text-primary mb-3 position-relative z-1">
                                Mô tả yêu cầu
                            </h5>
                            <div class="text-dark lh-base position-relative z-1" style="text-align: justify; white-space: pre-line; font-size: 0.95rem;">
                                ${p.description ? p.description : '<em class="text-muted">Chưa có mô tả chi tiết.</em>'}
                            </div>
                        </div>
                    </div>
                </div>`;
                } else {
                    bodyEl.innerHTML = `<div class="alert alert-warning text-center">Không tìm thấy thông tin.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                bodyEl.innerHTML = `<div class="alert alert-danger text-center">Lỗi kết nối server.</div>`;
            });
    }

    function registerProject(projectId) {
        if (!confirm('Bạn có chắc chắn muốn đăng ký đồ án này?\nSau khi đăng ký không thể thay đổi.')) return;

        fetch('/quanlydoan/Project/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'project_id=' + projectId
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.href = '/quanlydoan/Project/index';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối hoặc lỗi server.');
            });
    }

    function handleSearch(e) {
        e.preventDefault();
        const keyword = document.getElementById('searchInput').value.trim();
        const url = new URL(window.location.href);
        url.searchParams.set('keyword', keyword);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>

<style>
    .hover-shadow-lg:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
    }

    .transition {
        transition: all 0.3s ease;
    }

    /* Nút chuyển đổi view */
    .btn-group .btn-outline-primary.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    /* Table styling cho list view */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        color: #6c757d;
        border-bottom-width: 2px;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .table .progress {
        width: 80px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            width: 100%;
            margin-top: 1rem;
        }

        .btn-group .btn {
            flex: 1;
        }

        .table-responsive {
            font-size: 0.9rem;
        }

        .table .btn-group {
            flex-wrap: wrap;
        }

        .table .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
    }

    @media (min-width: 992px) {
        .search-form {
            width: 350px !important;
        }
    }
</style>