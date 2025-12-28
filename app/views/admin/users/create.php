<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
            </div>
            <form method="POST" action="<?= BASE_URL ?>user/store" data-validate-form>
                <div class="card-body">
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required
                               data-validate='{"required":true,"minLength":3}'
                               value="<?= htmlspecialchars($_SESSION['old']['username'] ?? '') ?>"
                               placeholder="Masukkan username">
                        <small class="form-text text-muted">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               data-validate='{"required":true,"email":true}'
                               value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>"
                               placeholder="Masukkan email">
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" required
                               data-validate='{"required":true,"minLength":3}'
                               value="<?= htmlspecialchars($_SESSION['old']['nama_lengkap'] ?? '') ?>"
                               placeholder="Masukkan nama lengkap">
                    </div>
                    
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required
                               data-validate='{"required":true,"minLength":6}'
                               placeholder="Masukkan password">
                        <small class="form-text text-muted">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirm" class="form-control" required
                               placeholder="Ulangi password">
                    </div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required data-validate='{"required":true}'>
                            <option value="">Pilih Role</option>
                            <option value="admin" <?= ($_SESSION['old']['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="operator" <?= ($_SESSION['old']['role'] ?? '') === 'operator' ? 'selected' : '' ?>>Operator</option>
                            <option value="viewer" <?= ($_SESSION['old']['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            <option value="petugas" <?= ($_SESSION['old']['role'] ?? '') === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="aktif" class="form-control">
                            <option value="1" <?= ($_SESSION['old']['aktif'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= ($_SESSION['old']['aktif'] ?? 1) == 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
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
