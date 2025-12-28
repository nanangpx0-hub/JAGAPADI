<?php
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$kec = $data['kecamatan'];
$old = $data['old'];
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit mr-2"></i> Edit Kecamatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah">Wilayah</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah/kecamatan">Kecamatan</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Form Edit Kecamatan</h3>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>adminWilayah/kecamatan/edit/<?= $kec['id'] ?>" id="formKecamatan">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="card-body">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-info-circle"></i> Data Saat Ini</h5>
                                    <p class="mb-0">
                                        <strong>Kabupaten:</strong> <?= htmlspecialchars($kec['nama_kabupaten'] ?? 'N/A') ?><br>
                                        <strong>Nama:</strong> <?= htmlspecialchars($kec['nama_kecamatan']) ?><br>
                                        <strong>Kode:</strong> <code><?= htmlspecialchars($kec['kode_kecamatan']) ?></code>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="kabupaten_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" id="kabupaten_id" name="kabupaten_id" required>
                                        <?php foreach ($data['kabupaten_list'] as $kab): ?>
                                            <option value="<?= $kab['id'] ?>" <?= ($old['kabupaten_id'] ?? $kec['kabupaten_id']) == $kab['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kab['nama_kabupaten']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="nama_kecamatan">Nama Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_kecamatan" name="nama_kecamatan"
                                           value="<?= htmlspecialchars($old['nama_kecamatan'] ?? $kec['nama_kecamatan']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="kode_kecamatan">Kode Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode_kecamatan" name="kode_kecamatan"
                                           value="<?= htmlspecialchars($old['kode_kecamatan'] ?? $kec['kode_kecamatan']) ?>"
                                           pattern="[0-9]{6,7}" maxlength="7" required>
                                    <small class="form-text text-muted">Hanya angka, panjang 6–7 digit sesuai kode BPS.</small>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="<?= BASE_URL ?>adminWilayah/kecamatan" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="reset" class="btn btn-default"><i class="fas fa-undo"></i> Reset</button>
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
    document.getElementById('kode_kecamatan').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 7);
    });
    
    document.getElementById('formKecamatan').addEventListener('submit', function() {
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
