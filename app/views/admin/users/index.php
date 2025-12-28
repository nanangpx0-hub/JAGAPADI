<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-users"></i> Manajemen User</h3>
            <div class="btn-group mb-2 mb-md-0">
                <a href="<?= BASE_URL ?>user/exportCsv?search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="<?= BASE_URL ?>user/exportExcel?search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="<?= BASE_URL ?>user/import" class="btn btn-info btn-sm">
                    <i class="fas fa-file-import"></i> Import User
                </a>
                <a href="<?= BASE_URL ?>user/create" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah User
                </a>
            </div>
        </div>
        <div class="mt-3">
            <form method="GET" action="<?= BASE_URL ?>user" class="form-row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/username/email" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="operator" <?= ($roleFilter === 'operator') ? 'selected' : '' ?>>Operator</option>
                        <option value="viewer" <?= ($roleFilter === 'viewer') ? 'selected' : '' ?>>Viewer</option>
                        <option value="petugas" <?= ($roleFilter === 'petugas') ? 'selected' : '' ?>>Petugas</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="users-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Registrasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <span class="badge badge-primary badge-sm">You</span>
                            <?php endif; ?>
                            <div class="text-muted small"><?= htmlspecialchars($user['email'] ?? '-') ?></div>
                        </td>
                        <td>
                            <?php
                            $roleColors = [
                                'admin' => 'danger',
                                'operator' => 'warning',
                                'viewer' => 'info',
                                'petugas' => 'success'
                            ];
                            $roleColor = $roleColors[$user['role']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?= $roleColor ?>"><?= ucfirst($user['role']) ?></span>
                        </td>
                        <td>
                            <?php if (($user['aktif'] ?? 1) == 1): ?>
                            <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="<?= BASE_URL ?>user/edit/<?= $user['id'] ?>" class="btn btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>user/toggleStatus/<?= $user['id'] ?>" 
                                   class="btn btn-<?= ($user['aktif'] ?? 1) == 1 ? 'warning' : 'success' ?>" 
                                   title="<?= ($user['aktif'] ?? 1) == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                   data-confirm-delete
                                   data-message="Yakin ingin mengubah status user ini?">
                                    <i class="fas fa-<?= ($user['aktif'] ?? 1) == 1 ? 'ban' : 'check' ?>"></i>
                                </a>
                                <a href="<?= BASE_URL ?>user/delete/<?= $user['id'] ?>" 
                                   class="btn btn-danger" 
                                   title="Hapus"
                                   data-confirm-delete
                                   data-message="Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan!">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php else: ?>
                                <button class="btn btn-secondary" disabled title="Tidak dapat mengubah user sendiri">
                                    <i class="fas fa-lock"></i>
                                </button>
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

<!-- DataTables Assets -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#users-table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        pageLength: 20,
        lengthChange: true,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                title: 'users',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'excel',
                title: 'users',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-outline-secondary btn-sm'
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        columnDefs: [
            { targets: 0, width: '5%', className: 'text-center' },
            { targets: 6, orderable: false, searchable: false, width: '15%', className: 'text-center' }
        ]
    });
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
