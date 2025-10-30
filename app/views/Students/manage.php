<h1 class="page-title h2">Quản lý Sinh viên</h1>


<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Tìm kiếm & Lọc</h5>
        <div class="row g-3 align-items-center">

            <div class="col-12 col-md-auto me-auto">
                <form method="GET" action="/quanlydoan/Student/manage" class="row g-3 align-items-center">
                    <div class="col-md-auto" style="width: 250px;">
                        <input type="text" class="form-control" name="keyword" placeholder="Tìm kiếm theo MSSV hoặc Họ tên" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-info text-white"><i class="bi bi-search me-1"></i> Tìm kiếm</button>
                    </div>

                    <div class="col-auto">
                        <?php if (isset($_GET['keyword']) && $_GET['keyword']): ?>
                            <a href="/quanlydoan/Student/manage" class="btn btn-outline-secondary">Xóa tìm kiếm</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="col-12 col-md-auto ms-md-auto">
                <div class="d-flex gap-2 justify-content-end">

                    <a href="/quanlydoan/Student/add" class="btn btn-primary">
                        <i class="bi bi-person-add me-1"></i> Thêm SV
                    </a>

                    <a href="/quanlydoan/Student/export" class="btn btn-success" title="Xuất file Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </a>

                    <a href="#" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importStudentModal" title="Nhập file Excel">
                        <i class="bi bi-cloud-upload me-1"></i> Nhập Excel
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        Danh sách (<?php echo count($students) ?? 0; ?>)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">MSSV</th>
                        <th scope="col">Họ và Tên</th>
                        <th scope="col">Lớp</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($student['mssv'] ?? 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    $status = $student['status'] ?? 'active';
                                    $badge_class = $status === 'active' ? 'bg-success' : 'bg-danger';
                                    $status_text = $status === 'active' ? 'Hoạt động' : 'Khóa';
                                    echo '<span class="badge ' . $badge_class . '">' . $status_text . '</span>';
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="/quanlydoan/Student/edit/<?php echo $student['student_id']; ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="/quanlydoan/Student/destroy/<?php echo $student['student_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên <?php echo htmlspecialchars($student['full_name'] ?? ''); ?> (<?php echo htmlspecialchars($student['mssv'] ?? ''); ?>)?');" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Không tìm thấy sinh viên nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php if (isset($totalPages) && $totalPages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php
            $currentPage = $page ?? 1;
            $keyword = htmlspecialchars($_GET['keyword'] ?? '');
            ?>
            <li class="page-item <?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $currentPage - 1; ?>&keyword=<?php echo $keyword; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $i; ?>&keyword=<?php echo $keyword; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $currentPage + 1; ?>&keyword=<?php echo $keyword; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>