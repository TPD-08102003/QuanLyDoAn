<div class="container mt-4">
    <h1 class="mb-4">📋 Đồ án của tôi</h1>

    <div class="d-flex justify-content-between mb-3">
        <form class="d-flex" action="/quanlydoan/project/myProjects" method="GET">
            <input class="form-control me-2" type="search" placeholder="Tìm kiếm đồ án (ID, Tên)" aria-label="Search" name="keyword" value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
            <button class="btn btn-outline-primary" type="submit">Tìm</button>
        </form>
        <a href="/quanlydoan/project/create" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Thêm Đồ án Mới
        </a>
    </div>

    <?php if (!empty($projects)): ?>
        <p class="text-muted">Tổng cộng có **<?php echo $totalProjects ?? 0; ?>** đồ án bạn đang phụ trách.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Mã Đồ án</th>
                        <th>Tên Đồ án</th>
                        <th>Khoa</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td class="text-primary fw-bold"><?php echo htmlspecialchars($project['project_id']); ?></td>
                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                            <td><?php echo htmlspecialchars($project['faculty_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                // Định nghĩa các label màu sắc cho trạng thái
                                $status_class = match ($project['status']) {
                                    'Pending' => 'bg-warning',
                                    'Active' => 'bg-success',
                                    'Completed' => 'bg-info',
                                    'Canceled' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($project['status']); ?></span>
                            </td>
                            <td><?php echo isset($project['created_at']) ? date('d/m/Y', strtotime($project['created_at'])) : 'N/A'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info text-white me-1 view-project" data-id="<?php echo $project['project_id']; ?>"><i class="bi bi-eye"></i></button>
                                <a href="/quanlydoan/project/edit/<?php echo $project['project_id']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-sm btn-danger delete-project" data-id="<?php echo $project['project_id']; ?>"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php include __DIR__ . '/../partials/pagination.php'; // Giả sử có partial pagination 
        ?>

    <?php else: ?>
        <div class="alert alert-info text-center mt-5" role="alert">
            <i class="bi bi-info-circle me-2"></i> Bạn chưa có đồ án nào được phân công hoặc tạo mới.
        </div>
    <?php endif; ?>
</div>