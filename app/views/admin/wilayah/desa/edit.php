<?php
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$desa = $data['desa'];
$old = $data['old'];
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit mr-2"></i> Edit Desa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/wilayah">Wilayah</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah/desa">Desa</a></li>
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
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Form Edit Desa</h3>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>adminWilayah/desa/edit/<?= $desa['id'] ?>" id="formDesa">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="card-body">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-info-circle"></i> Data Saat Ini</h5>
                                    <p class="mb-0">
                                        <strong>Kecamatan:</strong> <?= htmlspecialchars($desa['nama_kecamatan'] ?? 'N/A') ?><br>
                                        <strong>Nama:</strong> <?= htmlspecialchars($desa['nama_desa']) ?><br>
                                        <strong>Kode:</strong> <code><?= htmlspecialchars($desa['kode_desa']) ?></code>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="kabupaten_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" id="kabupaten_id" name="kabupaten_id" required>
                                        <?php foreach ($data['kabupaten_list'] as $kab): ?>
                                            <option value="<?= $kab['id'] ?>" <?= ($old['kabupaten_id'] ?? $desa['kabupaten_id']) == $kab['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kab['nama_kabupaten']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="kecamatan_id">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="kecamatan_id" name="kecamatan_id" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="nama_desa">Nama Desa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_desa" name="nama_desa"
                                           value="<?= htmlspecialchars($old['nama_desa'] ?? $desa['nama_desa']) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="kode_desa">Kode Desa <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="kode_desa" name="kode_desa"
                                                   value="<?= htmlspecialchars($old['kode_desa'] ?? $desa['kode_desa']) ?>"
                                                   pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="kode_pos">Kode Pos</label>
                                            <input type="text" class="form-control" id="kode_pos" name="kode_pos"
                                                   value="<?= htmlspecialchars($old['kode_pos'] ?? $desa['kode_pos']) ?>"
                                                   pattern="[0-9]{5}" maxlength="5">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="<?= BASE_URL ?>adminWilayah/desa" class="btn btn-secondary">
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
    const kabSelect = document.getElementById('kabupaten_id');
    const kecSelect = document.getElementById('kecamatan_id');
    
    // Initial load
    const initialKabId = '<?= $old['kabupaten_id'] ?? $desa['kabupaten_id'] ?>';
    const initialKecId = '<?= $old['kecamatan_id'] ?? $desa['kecamatan_id'] ?>';
    
    if (initialKabId) {
        loadKecamatan(initialKabId, initialKecId);
    }
    
    kabSelect.addEventListener('change', function() {
        loadKecamatan(this.value);
    });
    
    function loadKecamatan(kabId, selectedId = null) {
        kecSelect.innerHTML = '<option value="">Memuat...</option>';
        kecSelect.disabled = true;
        
        if (kabId) {
            fetch('<?= BASE_URL ?>adminWilayah/get_kecamatan_by_kabupaten/' + kabId)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">-- Pilih Kecamatan --</option>';
                    if (data.success && data.data) {
                        data.data.forEach(kec => {
                            const selected = selectedId == kec.id ? 'selected' : '';
                            options += `<option value="${kec.id}" ${selected}>${kec.nama_kecamatan}</option>`;
                        });
                    }
                    kecSelect.innerHTML = options;
                    kecSelect.disabled = false;
                })
                .catch(err => {
                    console.error(err);
                    kecSelect.innerHTML = '<option value="">Error memuat data</option>';
                });
        } else {
            kecSelect.innerHTML = '<option value="">-- Pilih Kabupaten Terlebih Dahulu --</option>';
            kecSelect.disabled = true;
        }
    }

    document.getElementById('kode_desa').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });
    
    document.getElementById('kode_pos').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 5);
    });
    
    document.getElementById('formDesa').addEventListener('submit', function() {
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
