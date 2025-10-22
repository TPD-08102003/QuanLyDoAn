<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .content {
        padding-top: 0;
    }

    .status-active {
        color: green;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .status-inactive {
        color: orange;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .status-banned {
        color: red;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .container.py-5 {
        padding-top: auto;
        padding-bottom: 0 !important;
    }

    .modal-content {
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        background: #fff;
    }

    .modal-header {
        background: linear-gradient(90deg, #007bff, #0056b3);
        color: white;
        border-bottom: none;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-body {
        padding: 2rem;
        background: #f8f9fa;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 2rem;
        background: #f8f9fa;
    }

    .input-icon .form-control,
    .input-icon .form-select {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px;
    }

    .input-icon .form-label {
        font-weight: 600;
        color: #343a40;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .btn-primary,
    .btn-success,
    .btn-secondary {
        border-radius: 8px;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover,
    .btn-success:hover,
    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    #viewAccountModal .modal-body {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    #viewAccountModal .info-item {
        flex: 1 1 45%;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #viewAccountModal .info-item strong {
        font-weight: 600;
        color: #007bff;
        min-width: 120px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #viewAccountModal .info-item span {
        color: #495057;
    }

    @media (max-width: 768px) {
        #viewAccountModal .info-item {
            flex: 1 1 100%;
        }

        .modal-dialog {
            margin: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.justify-content-between form {
            width: 100%;
        }
    }
</style>

<h1 class="mb-4 text-primary"><?php echo htmlspecialchars($title); ?></h1>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" action="/account" class="w-50">
        <div class="input-group">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên đăng nhập hoặc email" value="<?php echo isset($keyword) ? htmlspecialchars($keyword) : ''; ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="bi bi-plus-circle"></i> Thêm tài khoản</button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($accounts)): ?>
                <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['account_id']); ?></td>
                        <td><?php echo htmlspecialchars($account['username']); ?></td>
                        <td><?php echo htmlspecialchars($account['email']); ?></td>
                        <td><?php echo htmlspecialchars($account['role']); ?></td>
                        <td>
                            <span class="status-<?php echo htmlspecialchars($account['status']); ?>">
                                <?php if ($account['status'] === 'active'): ?>
                                    <i class="fa fa-check-circle"></i> Hoạt động
                                <?php elseif ($account['status'] === 'inactive'): ?>
                                    <i class="fa fa-pause-circle"></i> Không hoạt động
                                <?php else: ?>
                                    <i class="fa fa-ban"></i> Bị khóa
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons d-flex gap-1">
                                <button type="button" class="btn btn-outline-info btn-sm" title="Xem" onclick='showAccountDetails(<?php echo json_encode($account); ?>)'>
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" title="Sửa" data-bs-toggle="modal" data-bs-target="#editAccountModal" onclick="fillEditModal(<?php echo htmlspecialchars(json_encode($account)); ?>)">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <?php if ($account['role'] !== 'admin'): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="<?php echo $account['status'] === 'banned' ? 'Mở khóa' : 'Khóa'; ?>" onclick="lockAccount(<?php echo $account['account_id']; ?>, '<?php echo $account['status'] === 'banned' ? 'active' : 'banned'; ?>')">
                                        <i class="fa <?php echo $account['status'] === 'banned' ? 'fa-unlock' : 'fa-lock'; ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Không có tài khoản nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/account/store">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalLabel"><i class="fas fa-user-plus"></i> Thêm tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 input-icon">
                                <label for="username" class="form-label"><i class="fas fa-user"></i> Tên đăng nhập</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="password" class="form-label"><i class="fas fa-lock"></i> Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 input-icon">
                                <label for="role" class="form-label"><i class="fas fa-user-tag"></i> Vai trò</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="student">Sinh viên</option>
                                    <option value="teacher">Giảng viên</option>
                                    <option value="admin">Quản trị viên</option>
                                </select>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="status" class="form-label"><i class="fas fa-info-circle"></i> Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                    <option value="banned">Bị khóa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/account/update">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAccountModalLabel"><i class="fas fa-user-edit"></i> Sửa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_account_id" name="account_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 input-icon">
                                <label for="edit_username" class="form-label"><i class="fas fa-user"></i> Tên đăng nhập</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="edit_email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="edit_password" class="form-label"><i class="fas fa-lock"></i> Mật khẩu mới (để trống nếu không đổi)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 input-icon">
                                <label for="edit_role" class="form-label"><i class="fas fa-user-tag"></i> Vai trò</label>
                                <select class="form-select" id="edit_role" name="role">
                                    <option value="student">Sinh viên</option>
                                    <option value="teacher">Giảng viên</option>
                                    <option value="admin">Quản trị viên</option>
                                </select>
                            </div>
                            <div class="mb-3 input-icon">
                                <label for="edit_status" class="form-label"><i class="fas fa-info-circle"></i> Trạng thái</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                    <option value="banned">Bị khóa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Account Modal -->
<div class="modal fade" id="viewAccountModal" tabindex="-1" aria-labelledby="viewAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAccountModalLabel"><i class="fas fa-user"></i> Thông tin tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="info-item">
                    <strong><i class="fas fa-id-badge"></i> ID:</strong>
                    <span id="view_account_id"></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-user"></i> Tên đăng nhập:</strong>
                    <span id="view_username"></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-envelope"></i> Email:</strong>
                    <span id="view_email"></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-user-tag"></i> Vai trò:</strong>
                    <span id="view_role"></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-info-circle"></i> Trạng thái:</strong>
                    <span id="view_status"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillEditModal(account) {
        document.getElementById('edit_account_id').value = account.account_id;
        document.getElementById('edit_username').value = account.username;
        document.getElementById('edit_email').value = account.email;
        document.getElementById('edit_role').value = account.role;
        document.getElementById('edit_status').value = account.status;
    }

    function showAccountDetails(account) {
        document.getElementById('view_account_id').textContent = account.account_id;
        document.getElementById('view_username').textContent = account.username;
        document.getElementById('view_email').textContent = account.email;
        document.getElementById('view_role').textContent = account.role;
        document.getElementById('view_status').textContent = account.status;

        var viewModal = new bootstrap.Modal(document.getElementById('viewAccountModal'));
        viewModal.show();
    }

    function lockAccount(accountId, status) {
        if (confirm('Bạn có chắc chắn muốn ' + (status === 'banned' ? 'mở khóa' : 'khóa') + ' tài khoản này?')) {
            fetch('/account/update/' + accountId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'account_id=' + accountId + '&status=' + status + '&_token=' + '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('Lỗi server: ' + error.message);
                });
        }
    }
</script>