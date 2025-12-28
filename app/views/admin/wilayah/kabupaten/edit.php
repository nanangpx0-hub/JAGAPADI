<?php
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$kab = $data['kabupaten'];
$old = $data['old'];
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit mr-2"></i> Edit Kabupaten/Kota</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/wilayah">Wilayah</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/wilayah/kabupaten">Kabupaten</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Error Messages -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Card -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Form Edit Kabupaten</h3>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>admin/wilayah/kabupaten/edit/<?= $kab['id'] ?>" id="formKabupaten">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="card-body">
                                <!-- Info Current Data -->
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-info-circle"></i> Data Saat Ini</h5>
                                    <p class="mb-0">
                                        <strong>Nama:</strong> <?= htmlspecialchars($kab['nama_kabupaten']) ?><br>
                                        <strong>Kode:</strong> <code><?= htmlspecialchars($kab['kode_kabupaten']) ?></code><br>
                                        <strong>Dibuat:</strong> <?= date('d/m/Y H:i', strtotime($kab['tanggal_dibuat'] ?? 'now')) ?>
                                    </p>
                                </div>

                                <!-- Nama Kabupaten -->
                                <div class="form-group">
                                    <label for="nama_kabupaten">
                                        Nama Kabupaten/Kota <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nama_kabupaten" 
                                           name="nama_kabupaten"
                                           placeholder="Contoh: Jember" 
                                           value="<?= htmlspecialchars($old['nama_kabupaten'] ?? $kab['nama_kabupaten']) ?>"
                                           required
                                           autofocus>
                                </div>

                                <!-- Kode Kabupaten -->
                                <div class="form-group">
                                    <label for="kode_kabupaten">
                                        Kode Wilayah (BPS) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="kode_kabupaten" 
                                           name="kode_kabupaten"
                                           placeholder="Contoh: 3501" 
                                           value="<?= htmlspecialchars($old['kode_kabupaten'] ?? $kab['kode_kabupaten']) ?>"
                                           pattern="35[0-9]{2}"
                                           maxlength="4"
                                           required>
                                    <small class="form-text text-muted">
                                        Kode wilayah BPS format 35XX (contoh: 3501 untuk Pacitan, 3509 untuk Jember)
                                    </small>
                                </div>

                                <!-- Warning Box -->
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                                    <ul class="mb-0">
                                        <li>Pastikan data yang diubah sudah benar</li>
                                        <li>Perubahan akan tercatat di audit log</li>
                                        <li>Format kode: 35XX (contoh: 3501 untuk Pacitan, 3509 untuk Jember)</li>
                                        <li>Kode wilayah harus tetap unik</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="<?= BASE_URL ?>admin/wilayah/kabupaten" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="reset" class="btn btn-default">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-warning" id="btnSubmit">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formKabupaten');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Auto-format kode
    document.getElementById('kode_kabupaten').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const namaKabupaten = document.getElementById('nama_kabupaten').value.trim();
        const kodeKabupaten = document.getElementById('kode_kabupaten').value.trim();
        
        if (!namaKabupaten) {
            e.preventDefault();
            alert('Nama kabupaten wajib diisi');
            return false;
        }
        
        if (!kodeKabupaten || kodeKabupaten.length !== 4) {
            e.preventDefault();
            alert('Kode wilayah harus 4 digit angka');
            return false;
        }
        
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
