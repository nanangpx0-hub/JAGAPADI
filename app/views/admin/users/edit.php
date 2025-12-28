<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit User</h3>
            </div>
            <form method="POST" action="<?= BASE_URL ?>user/update/<?= $user['id'] ?>" data-validate-form>
                <div class="card-body">
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required
                               data-validate='{"required":true,"minLength":3}'
                               value="<?= htmlspecialchars($_SESSION['old']['username'] ?? $user['username']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               data-validate='{"required":true,"email":true}'
                               value="<?= htmlspecialchars($_SESSION['old']['email'] ?? $user['email']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" required
                               data-validate='{"required":true,"minLength":3}'
                               value="<?= htmlspecialchars($_SESSION['old']['nama_lengkap'] ?? $user['nama_lengkap']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control"
                               data-validate='{"minLength":6}'
                               placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small class="form-text text-muted">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirm" class="form-control" 
                               placeholder="Ulangi password baru">
                    </div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required data-validate='{"required":true}'>
                            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="operator" <?= ($user['role'] === 'operator') ? 'selected' : '' ?>>Operator</option>
                            <option value="viewer" <?= ($user['role'] === 'viewer') ? 'selected' : '' ?>>Viewer</option>
                            <option value="petugas" <?= ($user['role'] === 'petugas') ? 'selected' : '' ?>>Petugas</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="aktif" class="form-control">
                            <option value="1" <?= (($user['aktif'] ?? 1) == 1) ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (($user['aktif'] ?? 1) == 0) ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        User dibuat pada: <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                        <?php if (!empty($user['updated_at'])): ?>
                        <br>Terakhir diupdate: <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= BASE_URL ?>user" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php unset($_SESSION['old']); ?>
<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
