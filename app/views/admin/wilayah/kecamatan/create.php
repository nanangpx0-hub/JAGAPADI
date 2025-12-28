<?php
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-plus-circle mr-2"></i> Tambah Kecamatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah">Wilayah</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah/kecamatan">Kecamatan</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
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

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Form Tambah Kecamatan</h3>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>adminWilayah/kecamatan/create" id="formKecamatan">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="card-body">
                                <!-- Kabupaten Selection -->
                                <div class="form-group">
                                    <label for="kabupaten_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <?php 
                                    $preselectedKabupaten = $_GET['kabupaten_id'] ?? ($data['old']['kabupaten_id'] ?? '');
                                    $isPreselected = !empty($preselectedKabupaten);
                                    ?>
                                    <select class="form-control <?= $isPreselected ? 'bg-light' : '' ?>" 
                                            id="kabupaten_id" 
                                            name="kabupaten_id" 
                                            required 
                                            <?= $isPreselected ? '' : 'autofocus' ?>>
                                        <option value="">-- Pilih Kabupaten --</option>
                                        <?php foreach ($data['kabupaten_list'] as $kab): ?>
                                            <option value="<?= $kab['id'] ?>" 
                                                    data-kode="<?= $kab['kode_kabupaten'] ?>"
                                                    <?= $preselectedKabupaten == $kab['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kab['kode_kabupaten'] . ' - ' . $kab['nama_kabupaten']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isPreselected): ?>
                                    <small class="form-text text-info">
                                        <i class="fas fa-info-circle"></i> Kabupaten telah dipilih dari filter sebelumnya
                                    </small>
                                    <?php endif; ?>
                                </div>

                                <!-- Nama Kecamatan -->
                                <div class="form-group">
                                    <label for="nama_kecamatan">Nama Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nama_kecamatan" 
                                           name="nama_kecamatan"
                                           placeholder="Contoh: Kaliwates, Sumbersari, Patrang" 
                                           value="<?= htmlspecialchars($data['old']['nama_kecamatan'] ?? '') ?>" 
                                           required
                                           <?= $isPreselected ? 'autofocus' : '' ?>>
                                    <small class="form-text text-muted">Nama kecamatan tanpa kata "Kecamatan"</small>
                                </div>

                                <!-- Kode Kecamatan -->
                                <div class="form-group">
                                    <label for="kode_kecamatan">Kode Kecamatan (BPS) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="kode_prefix">
                                                <i class="fas fa-barcode"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               class="form-control" 
                                               id="kode_kecamatan" 
                                               name="kode_kecamatan"
                                               placeholder="Contoh: 350919" 
                                               value="<?= htmlspecialchars($data['old']['kode_kecamatan'] ?? '') ?>"
                                               pattern="[0-9]{6,7}" 
                                               maxlength="7" 
                                               required>
                                    </div>
                                    <small class="form-text text-muted">
                                        Format: <strong>35</strong> (Jatim) + <strong>09</strong> (Kab) + <strong>XX</strong> (Urut)
                                        <span id="kode_hint" class="text-info ml-2"></span>
                                    </small>
                                </div>

                                <!-- Informasi & Tips -->
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info-circle"></i> Panduan Pengisian</h5>
                                    <ul class="mb-0">
                                        <li><strong>Kabupaten:</strong> Pilih kabupaten/kota terlebih dahulu</li>
                                        <li><strong>Nama:</strong> Tulis nama kecamatan tanpa kata "Kecamatan"</li>
                                        <li><strong>Kode:</strong> Kode BPS 6 digit (harus unik)</li>
                                        <li><strong>Format Kode:</strong> 35 (Jawa Timur) + Kode Kabupaten (2 digit) + Nomor Urut (2 digit)</li>
                                    </ul>
                                </div>

                                <!-- Preview Card -->
                                <div id="previewCard" class="card bg-light" style="display: none;">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-eye"></i> Preview Data</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td width="150"><strong>Kabupaten:</strong></td>
                                                <td id="preview_kabupaten">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kecamatan:</strong></td>
                                                <td id="preview_kecamatan">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kode BPS:</strong></td>
                                                <td><code id="preview_kode">-</code></td>
                                            </tr>
                                        </table>
                                    </div>
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
                                        <button type="reset" class="btn btn-warning"><i class="fas fa-undo"></i> Reset</button>
                                        <button type="submit" class="btn btn-success" id="btnSubmit">
                                            <i class="fas fa-save"></i> Simpan
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
    const kabupatenSelect = document.getElementById('kabupaten_id');
    const namaKecamatanInput = document.getElementById('nama_kecamatan');
    const kodeKecamatanInput = document.getElementById('kode_kecamatan');
    const previewCard = document.getElementById('previewCard');
    const kodeHint = document.getElementById('kode_hint');
    
    // Auto-format kode kecamatan (only numbers, max 7 digits)
    kodeKecamatanInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 7);
        updatePreview();
    });
    
    // Update hint when kabupaten changes
    kabupatenSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const kodeKabupaten = selectedOption.getAttribute('data-kode');
        
        if (kodeKabupaten) {
            // Extract last 2 digits of kabupaten code
            const kabCode = kodeKabupaten.substring(2, 4);
            kodeHint.innerHTML = `<i class="fas fa-lightbulb"></i> Gunakan awalan: <strong>35${kabCode}XX</strong>`;
            
            // Auto-fill first 4 digits if kode is empty
            if (!kodeKecamatanInput.value) {
                kodeKecamatanInput.value = `35${kabCode}`;
                kodeKecamatanInput.focus();
            }
        } else {
            kodeHint.innerHTML = '';
        }
        
        updatePreview();
    });
    
    // Update preview on nama kecamatan change
    namaKecamatanInput.addEventListener('input', updatePreview);
    
    // Function to update preview card
    function updatePreview() {
        const kabupatenText = kabupatenSelect.options[kabupatenSelect.selectedIndex].text;
        const namaKecamatan = namaKecamatanInput.value;
        const kodeKecamatan = kodeKecamatanInput.value;
        
        if (kabupatenSelect.value && namaKecamatan && kodeKecamatan.length === 6) {
            document.getElementById('preview_kabupaten').textContent = kabupatenText;
            document.getElementById('preview_kecamatan').textContent = namaKecamatan;
            document.getElementById('preview_kode').textContent = kodeKecamatan;
            previewCard.style.display = 'block';
        } else {
            previewCard.style.display = 'none';
        }
    }
    
    // Form submission handler
    document.getElementById('formKecamatan').addEventListener('submit', function(e) {
        const btnSubmit = document.getElementById('btnSubmit');
        
        // Validate kode format
        const kode = kodeKecamatanInput.value;
        if (kode.length !== 6) {
            e.preventDefault();
            alert('Kode kecamatan harus 6 digit!');
            kodeKecamatanInput.focus();
            return false;
        }
        
        // Validate kode starts with 35
        if (!kode.startsWith('35')) {
            e.preventDefault();
            alert('Kode kecamatan harus dimulai dengan 35 (Jawa Timur)!');
            kodeKecamatanInput.focus();
            return false;
        }
        
        // Show loading state
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        // Show confirmation
        const kabupatenText = kabupatenSelect.options[kabupatenSelect.selectedIndex].text;
        const namaKecamatan = namaKecamatanInput.value;
        
        if (!confirm(`Simpan kecamatan "${namaKecamatan}" di ${kabupatenText}?`)) {
            e.preventDefault();
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-save"></i> Simpan';
            return false;
        }
    });
    
    // Initialize preview if form has values
    if (kabupatenSelect.value) {
        kabupatenSelect.dispatchEvent(new Event('change'));
    }
    
    // Character counter for nama kecamatan
    namaKecamatanInput.addEventListener('input', function() {
        const length = this.value.length;
        if (length > 50) {
            this.value = this.value.substring(0, 50);
        }
    });
});
</script>

<style>
#previewCard {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

#kode_hint {
    font-weight: 600;
}
</style>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
