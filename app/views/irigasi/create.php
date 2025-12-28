<?php require_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Success/Info Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Berhasil!</strong> <?= $_SESSION['success'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Informasi:</strong> <?= $_SESSION['info'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Error!</strong> <?= $_SESSION['error'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>irigasi/create" method="POST" enctype="multipart/form-data" id="formIrigasi" class="needs-validation" novalidate>
        <?= Security::getCsrfField() ?>

        <div class="row">
            <!-- Left Column: Form Fields -->
            <div class="col-lg-8">
                <!-- Info Pelapor -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-success font-weight-bold">
                            <i class="fas fa-user-edit mr-2"></i> Informasi Pelapor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal Laporan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= $data['tanggal'] ?? date('Y-m-d') ?>" required>
                                    <div class="invalid-feedback">Silakan pilih tanggal laporan.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_pelapor">
                                        Nama Pelapor <span class="text-danger">*</span>
                                        <?php 
                                        $isAutoFilled = !isset($data['nama_pelapor']) && !empty($_SESSION['nama_lengkap']);
                                        $currentValue = $data['nama_pelapor'] ?? $_SESSION['nama_lengkap'] ?? '';
                                        ?>
                                        <?php if ($isAutoFilled): ?>
                                        <span class="badge badge-info ml-2" id="autoFillBadge" title="Data diambil dari profil akun Anda">
                                            <i class="fas fa-user-check"></i> Terisi otomatis dari profil
                                        </span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               class="form-control <?= $isAutoFilled ? 'auto-filled' : '' ?>" 
                                               id="nama_pelapor" 
                                               name="nama_pelapor" 
                                               value="<?= htmlspecialchars($currentValue) ?>" 
                                               placeholder="Masukkan nama Anda" 
                                               required
                                               minlength="3"
                                               <?= $isAutoFilled ? 'readonly' : '' ?>>
                                        <?php if ($isAutoFilled): ?>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="btnEditNama" title="Klik untuk mengedit nama">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="invalid-feedback">Nama pelapor wajib diisi (minimal 3 karakter).</div>
                                    <?php if ($isAutoFilled): ?>
                                    <small class="form-text text-info">
                                        <i class="fas fa-info-circle"></i> Nama diambil dari data profil Anda. Klik tombol "Edit" jika ingin mengubah.
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Saluran -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-success font-weight-bold">
                            <i class="fas fa-water mr-2"></i> Detail Saluran Irigasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_saluran">Nama Saluran Irigasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_saluran" name="nama_saluran" value="<?= $data['nama_saluran'] ?? '' ?>" placeholder="Contoh: Saluran Primer Bondoyudo" required minlength="3">
                                    <div class="invalid-feedback">Nama saluran minimal 3 karakter.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis_saluran">Jenis Saluran <span class="text-danger">*</span></label>
                                    <select class="form-control custom-select text-capitalize" id="jenis_saluran" name="jenis_saluran" required>
                                        <option value="" disabled selected>-- Pilih Jenis Saluran --</option>
                                        <option value="Primer" <?= (isset($data['jenis_saluran']) && $data['jenis_saluran'] == 'Primer') ? 'selected' : '' ?>>Primer</option>
                                        <option value="Sekunder" <?= (isset($data['jenis_saluran']) && $data['jenis_saluran'] == 'Sekunder') ? 'selected' : '' ?>>Sekunder</option>
                                        <option value="Tersier" <?= (isset($data['jenis_saluran']) && $data['jenis_saluran'] == 'Tersier') ? 'selected' : '' ?>>Tersier</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih jenis saluran.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kondisi Saluran <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap mt-2">
                                        <div class="custom-control custom-radio mr-4">
                                            <input class="custom-control-input" type="radio" id="kondisi_baik" name="kondisi_fisik" value="Baik" <?= (!isset($data['kondisi_fisik']) || $data['kondisi_fisik'] == 'Baik') ? 'checked' : '' ?> required>
                                            <label for="kondisi_baik" class="custom-control-label font-weight-normal text-success">Baik</label>
                                        </div>
                                        <div class="custom-control custom-radio mr-4">
                                            <input class="custom-control-input" type="radio" id="kondisi_ringan" name="kondisi_fisik" value="Rusak Ringan" <?= (isset($data['kondisi_fisik']) && $data['kondisi_fisik'] == 'Rusak Ringan') ? 'selected' : '' ?>>
                                            <label for="kondisi_ringan" class="custom-control-label font-weight-normal text-warning">Rusak Ringan</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="kondisi_berat" name="kondisi_fisik" value="Rusak Berat" <?= (isset($data['kondisi_fisik']) && $data['kondisi_fisik'] == 'Rusak Berat') ? 'selected' : '' ?>>
                                            <label for="kondisi_berat" class="custom-control-label font-weight-normal text-danger">Rusak Berat</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="luas_layanan">Luas Layanan (Ha) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="luas_layanan" name="luas_layanan" value="<?= $data['luas_layanan'] ?? '' ?>" placeholder="0.00" required>
                                    <div class="invalid-feedback">Masukkan luas layanan dalam hektar.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status_perbaikan">Status <span class="text-danger">*</span></label>
                                    
                                    <!-- Input for "Normal" status (when kondisi = Baik) -->
                                    <div id="status_normal_container" style="display: none;">
                                        <input type="text" class="form-control status-normal-input" id="status_normal" value="Normal" readonly>
                                        <input type="hidden" name="status_perbaikan" id="status_perbaikan_hidden" value="Normal">
                                        <small class="form-text text-success">
                                            <i class="fas fa-check-circle"></i> Status otomatis "Normal" karena kondisi saluran baik
                                        </small>
                                    </div>
                                    
                                    <!-- Dropdown for repair status (when kondisi = Rusak) -->
                                    <div id="status_dropdown_container">
                                        <select class="form-control custom-select" id="status_perbaikan" name="status_perbaikan" required>
                                            <option value="" disabled selected>-- Pilih Status Perbaikan --</option>
                                            <option value="Selesai Diperbaiki" <?= (isset($data['status_perbaikan']) && $data['status_perbaikan'] == 'Selesai Diperbaiki') ? 'selected' : '' ?>>Selesai Diperbaiki</option>
                                            <option value="Dalam Perbaikan" <?= (isset($data['status_perbaikan']) && $data['status_perbaikan'] == 'Dalam Perbaikan') ? 'selected' : '' ?>>Dalam Perbaikan</option>
                                            <option value="Belum Ditangani" <?= (isset($data['status_perbaikan']) && $data['status_perbaikan'] == 'Belum Ditangani') ? 'selected' : '' ?>>Belum Ditangani</option>
                                        </select>
                                        <div class="invalid-feedback">Pilih status perbaikan.</div>
                                        <small class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Wajib memilih status perbaikan karena kondisi saluran rusak
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aksi_dilakukan">Aksi yang Dilakukan</label>
                                    <textarea class="form-control" id="aksi_dilakukan" name="aksi_dilakukan" rows="2" placeholder="Jelaskan tindakan yang telah diambil..."><?= $data['aksi_dilakukan'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Wilayah -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-success font-weight-bold">
                            <i class="fas fa-map-marker-alt mr-2"></i> Lokasi Irigasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kabupatenSelect">Kabupaten <span class="text-danger">*</span></label>
                                    <select class="form-control custom-select" id="kabupatenSelect" name="kabupaten_id" required>
                                        <option value="">-- Pilih Kabupaten --</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih kabupaten.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kecamatanSelect">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control custom-select" id="kecamatanSelect" name="kecamatan_id" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih kecamatan.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="desaSelect">Desa <span class="text-danger">*</span></label>
                                    <select class="form-control custom-select" id="desaSelect" name="desa_id" required>
                                        <option value="">-- Pilih Desa --</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih desa.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Coordinate Input -->
                        <div class="form-group mt-3">
                            <label>Input Koordinat</label>
                            <div class="btn-group btn-group-toggle w-100 mb-3" data-toggle="buttons">
                                <label class="btn btn-outline-success active">
                                    <input type="radio" name="coordMode" id="coordModeManual" checked> <i class="fas fa-keyboard mr-1"></i> Manual
                                </label>
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="coordMode" id="coordModeMap"> <i class="fas fa-map mr-1"></i> Peta Interaktif
                                </label>
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="coordMode" id="coordModeGPS"> <i class="fas fa-crosshairs mr-1"></i> Lokasi Saya
                                </label>
                            </div>

                            <!-- Manual Mode -->
                            <div id="modeManual" class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="latitudeInput">Latitude</label>
                                        <input type="text" class="form-control" id="latitudeInput" name="latitude" value="<?= $data['latitude'] ?? '' ?>" placeholder="Contoh: -8.123456">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="longitudeInput">Longitude</label>
                                        <input type="text" class="form-control" id="longitudeInput" name="longitude" value="<?= $data['longitude'] ?? '' ?>" placeholder="Contoh: 113.123456">
                                    </div>
                                </div>
                            </div>

                            <!-- Map Mode -->
                            <div id="modeMap" style="display:none;">
                                <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #ddd;" class="mb-2"></div>
                                <div class="alert alert-info py-2">
                                    <small><i class="fas fa-info-circle mr-1"></i> Klik pada peta untuk memilih lokasi.</small>
                                </div>
                            </div>

                            <!-- GPS Mode -->
                            <div id="modeGPS" style="display:none;" class="text-center py-4 bg-light rounded border">
                                <button type="button" class="btn btn-primary btn-lg px-4" id="btnGPS">
                                    <i class="fas fa-map-marker-alt mr-2"></i> Ambil Lokasi Saat Ini
                                </button>
                                <p id="gpsStatus" class="mt-2 text-muted mb-0"><small>Menunggu akses lokasi...</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Media and Notes -->
            <div class="col-lg-4">
                <!-- Foto Section -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-success font-weight-bold">
                            <i class="fas fa-camera mr-2"></i> Dokumentasi Foto
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="foto">Pilih Foto Irigasi <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                                <label class="custom-file-label" for="foto">Pilih file...</label>
                            </div>
                            <div class="invalid-feedback d-block mt-1" id="fotoError"></div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Format: JPG, PNG, GIF. Ukuran maks: 2MB.<br>
                                <i class="fas fa-compress-alt text-success"></i> <strong>Kompresi Otomatis:</strong> Foto yang melebihi 2MB akan dikompresi secara otomatis dengan kualitas 80% sambil mempertahankan rasio aspek.
                            </small>
                        </div>

                        <!-- Preview Area -->
                        <div id="photoPreview" class="mt-3 text-center border p-2 rounded" style="display:none; background-color: #f8f9fa;">
                            <img id="previewImg" src="#" alt="Preview" class="img-fluid rounded border shadow-sm" style="max-height: 250px;">
                            <div id="photoInfo" class="mt-2 small text-muted"></div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Section -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-success font-weight-bold">
                            <i class="fas fa-sticky-note mr-2"></i> Catatan Tambahan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" id="catatan" name="catatan" rows="5" placeholder="Berikan catatan tambahan jika diperlukan..."><?= $data['catatan'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <button type="submit" class="btn btn-success btn-lg btn-block mb-2 font-weight-bold py-3" id="btnSubmit">
                            <i class="fas fa-save mr-2"></i> SIMPAN DATA IRIGASI
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-block font-weight-bold" id="btnReset">
                            <i class="fas fa-undo mr-2"></i> RESET FORM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================================
    // STATE & ELEMENTS
    // ============================================================================
    const form = document.getElementById('formIrigasi');
    const photoInput = document.getElementById('foto');
    const photoPreview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');
    const photoInfo = document.getElementById('photoInfo');
    const photoLabel = document.querySelector('.custom-file-label');
    const btnReset = document.getElementById('btnReset');
    
    // Coordinate Elements
    const latInput = document.getElementById('latitudeInput');
    const lngInput = document.getElementById('longitudeInput');
    const btnGPS = document.getElementById('btnGPS');
    const gpsStatus = document.getElementById('gpsStatus');
    
    // Mode Elements
    const modeManual = document.getElementById('modeManual');
    const modeMap = document.getElementById('modeMap');
    const modeGPS = document.getElementById('modeGPS');
    const radioManual = document.getElementById('coordModeManual');
    const radioMap = document.getElementById('coordModeMap');
    const radioGPS = document.getElementById('coordModeGPS');

    // Kondisi Saluran & Status Elements
    const kondisiBaik = document.getElementById('kondisi_baik');
    const kondisiRingan = document.getElementById('kondisi_ringan');
    const kondisiBerat = document.getElementById('kondisi_berat');
    const statusNormalContainer = document.getElementById('status_normal_container');
    const statusDropdownContainer = document.getElementById('status_dropdown_container');
    const statusPerbaikanSelect = document.getElementById('status_perbaikan');
    const statusPerbaikanHidden = document.getElementById('status_perbaikan_hidden');

    let map = null;
    let marker = null;

    // ============================================================================
    // KONDISI SALURAN & STATUS LOGIC
    // ============================================================================
    
    /**
     * Update status field based on kondisi saluran selection
     * - Baik: Status = "Normal" (readonly, disabled dropdown)
     * - Rusak Ringan/Berat: Status = dropdown with 3 options (required)
     */
    function updateStatusField() {
        const kondisi = document.querySelector('input[name="kondisi_fisik"]:checked').value;
        
        if (kondisi === 'Baik') {
            // Show "Normal" input, hide dropdown
            statusNormalContainer.style.display = 'block';
            statusDropdownContainer.style.display = 'none';
            
            // Disable dropdown validation and clear selection
            statusPerbaikanSelect.required = false;
            statusPerbaikanSelect.value = '';
            
            // Set hidden input to "Normal"
            statusPerbaikanHidden.value = 'Normal';
            
            // Remove validation class from dropdown
            statusPerbaikanSelect.classList.remove('is-invalid');
            
            console.log('Status set to: Normal (kondisi Baik)');
        } else {
            // kondisi = Rusak Ringan or Rusak Berat
            // Hide "Normal" input, show dropdown
            statusNormalContainer.style.display = 'none';
            statusDropdownContainer.style.display = 'block';
            
            // Enable dropdown validation
            statusPerbaikanSelect.required = true;
            
            // Clear hidden input
            statusPerbaikanHidden.value = '';
            
            // Reset dropdown if no value selected
            if (!statusPerbaikanSelect.value) {
                statusPerbaikanSelect.value = '';
            }
            
            console.log('Status set to: Dropdown (kondisi Rusak)');
        }
    }
    
    // Event listeners for kondisi saluran radio buttons
    kondisiBaik.addEventListener('change', updateStatusField);
    kondisiRingan.addEventListener('change', updateStatusField);
    kondisiBerat.addEventListener('change', updateStatusField);
    
    // Initialize on page load
    updateStatusField();

    // ============================================================================
    // PHOTO PREVIEW & VALIDATION
    // ============================================================================
    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        const errorDiv = document.getElementById('fotoError');
        errorDiv.textContent = '';
        
        if (file) {
            // Validate type first
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                errorDiv.textContent = 'Format file tidak valid. Gunakan JPG, PNG, atau GIF.';
                this.value = '';
                photoPreview.style.display = 'none';
                photoLabel.textContent = 'Pilih file...';
                return;
            }
            
            // Check size and show info if will be compressed
            const maxSize = 2 * 1024 * 1024; // 2MB
            let sizeInfo = '';
            
            if (file.size > maxSize) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                sizeInfo = ` <span class="badge badge-info"><i class="fas fa-compress-alt"></i> ${fileSizeMB} MB - Akan dikompresi otomatis</span>`;
            } else {
                const fileSizeKB = (file.size / 1024).toFixed(1);
                sizeInfo = ` <span class="badge badge-success"><i class="fas fa-check"></i> ${fileSizeKB} KB - Ukuran OK</span>`;
            }

            photoLabel.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                photoPreview.style.display = 'block';
                photoInfo.innerHTML = `${file.name}${sizeInfo}`;
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.style.display = 'none';
            photoLabel.textContent = 'Pilih file...';
        }
    });

    // ============================================================================
    // WILAYAH DROPDOWNS
    // ============================================================================
    async function fetchJSON(url) {
        const response = await fetch(url);
        return response.json();
    }

    async function loadKabupaten() {
        const data = await fetchJSON('<?= BASE_URL ?>wilayah/kabupaten');
        const sel = document.getElementById('kabupatenSelect');
        data.data.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.nama_kabupaten;
            sel.appendChild(opt);
        });
    }

    async function loadKecamatan(kabupatenId) {
        const sel = document.getElementById('kecamatanSelect');
        sel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        const selDesa = document.getElementById('desaSelect');
        selDesa.innerHTML = '<option value="">-- Pilih Desa --</option>';
        if (!kabupatenId) return;
        const data = await fetchJSON('<?= BASE_URL ?>wilayah/kecamatan/' + kabupatenId);
        data.data.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.nama_kecamatan;
            sel.appendChild(opt);
        });
    }

    async function loadDesa(kecamatanId) {
        const sel = document.getElementById('desaSelect');
        sel.innerHTML = '<option value="">-- Pilih Desa --</option>';
        if (!kecamatanId) return;
        const data = await fetchJSON('<?= BASE_URL ?>wilayah/desa/' + kecamatanId);
        data.data.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.nama_desa;
            sel.appendChild(opt);
        });
    }

    document.getElementById('kabupatenSelect').addEventListener('change', e => loadKecamatan(e.target.value));
    document.getElementById('kecamatanSelect').addEventListener('change', e => loadDesa(e.target.value));
    loadKabupaten();

    // ============================================================================
    // COORDINATE LOGIC
    // ============================================================================
    function initMap() {
        if (map) return;
        const center = [-8.1706, 113.7003]; // Jember
        map = L.map('map').setView(center, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            latInput.value = pos.lat.toFixed(6);
            lngInput.value = pos.lng.toFixed(6);
        });
    }

    // Toggle Modes
    radioManual.addEventListener('change', () => {
        modeManual.style.display = 'flex';
        modeMap.style.display = 'none';
        modeGPS.style.display = 'none';
    });

    radioMap.addEventListener('change', () => {
        modeManual.style.display = 'none';
        modeMap.style.display = 'block';
        modeGPS.style.display = 'none';
        setTimeout(initMap, 200);
    });

    radioGPS.addEventListener('change', () => {
        modeManual.style.display = 'none';
        modeMap.style.display = 'none';
        modeGPS.style.display = 'block';
    });

    // GPS Logic
    btnGPS.addEventListener('click', function() {
        if (!navigator.geolocation) {
            gpsStatus.textContent = 'Geolocation tidak didukung browser ini.';
            return;
        }
        
        gpsStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari lokasi...';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                gpsStatus.innerHTML = `<span class="text-success"><i class="fas fa-check"></i> Lokasi didapatkan: ${lat.toFixed(4)}, ${lng.toFixed(4)}</span>`;
            },
            (err) => {
                gpsStatus.innerHTML = `<span class="text-danger"><i class="fas fa-times"></i> ${err.message}</span>`;
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    });

    // ============================================================================
    // VALIDATION & SUBMIT
    // ============================================================================
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            
            // Scroll to first invalid
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        form.classList.add('was-validated');
    }, false);

    btnReset.addEventListener('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin mengosongkan form?')) {
            e.preventDefault();
            return;
        }
        form.classList.remove('was-validated');
        photoPreview.style.display = 'none';
        photoLabel.textContent = 'Pilih file...';
        gpsStatus.textContent = 'Menunggu akses lokasi...';
        if (marker) map.removeLayer(marker);
        
        // Reset status field to initial state (Baik = Normal)
        setTimeout(function() {
            updateStatusField();
        }, 100);
        
        // Reset nama pelapor to original auto-filled state if applicable
        const namaPelaporInput = document.getElementById('nama_pelapor');
        const btnEditNama = document.getElementById('btnEditNama');
        const namaLengkapSession = '<?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?>';
        if (btnEditNama && namaLengkapSession) {
            namaPelaporInput.value = namaLengkapSession;
            namaPelaporInput.readOnly = true;
            namaPelaporInput.classList.add('auto-filled');
            btnEditNama.innerHTML = '<i class="fas fa-edit"></i> Edit';
            btnEditNama.classList.remove('editing');
        }
    });
    
    // ============================================================================
    // NAMA PELAPOR EDIT TOGGLE
    // ============================================================================
    const btnEditNama = document.getElementById('btnEditNama');
    if (btnEditNama) {
        btnEditNama.addEventListener('click', function() {
            const namaPelaporInput = document.getElementById('nama_pelapor');
            
            if (namaPelaporInput.readOnly) {
                // Enable editing
                namaPelaporInput.readOnly = false;
                namaPelaporInput.classList.remove('auto-filled');
                namaPelaporInput.focus();
                namaPelaporInput.select();
                this.innerHTML = '<i class="fas fa-check"></i> OK';
                this.classList.add('editing');
            } else {
                // Confirm editing - just mark as done
                namaPelaporInput.readOnly = true;
                namaPelaporInput.classList.add('auto-filled');
                this.innerHTML = '<i class="fas fa-edit"></i> Edit';
                this.classList.remove('editing');
            }
        });
    }
});
</script>

<style>
    .card-outline.card-success { border-top: 3px solid #28a745; }
    .custom-select:focus, .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .btn-outline-success:not(:disabled):not(.disabled).active, 
    .btn-outline-success:not(:disabled):not(.disabled):active {
        background-color: #28a745;
        border-color: #28a745;
    }
    .custom-control-input:checked~.custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
    .custom-file-input:focus~.custom-file-label {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .btn-success { background-color: #28a745; border-color: #28a745; }
    .btn-success:hover { background-color: #218838; border-color: #1e7e34; }
    
    /* Status Field Styling */
    .status-normal-input {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        font-weight: 600;
        cursor: not-allowed;
        text-align: center;
        font-size: 1.1rem;
        padding: 0.5rem;
    }
    
    .status-normal-input:focus {
        background-color: #d4edda;
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    #status_dropdown_container select {
        border: 2px solid #ffc107;
        background-color: #fff8e1;
    }
    
    #status_dropdown_container select:focus {
        border-color: #ff9800;
        background-color: #ffffff;
        box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
    }
    
    #status_dropdown_container .form-text {
        font-weight: 500;
        margin-top: 0.5rem;
    }
    
    #status_normal_container .form-text {
        font-weight: 500;
        margin-top: 0.5rem;
    }
    
    /* Smooth transition for container visibility */
    #status_normal_container,
    #status_dropdown_container {
        transition: opacity 0.3s ease-in-out;
    }
    
    /* Auto-filled field styling */
    .form-control.auto-filled {
        background-color: #e7f3ff;
        border-color: #17a2b8;
        color: #0c5460;
    }
    
    .form-control.auto-filled:focus {
        background-color: #ffffff;
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    
    .form-control.auto-filled[readonly] {
        background-color: #e7f3ff;
        cursor: default;
    }
    
    #autoFillBadge {
        font-size: 0.7rem;
        vertical-align: middle;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #btnEditNama {
        border-color: #6c757d;
        transition: all 0.3s ease;
    }
    
    #btnEditNama:hover {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: #ffffff;
    }
    
    #btnEditNama.editing {
        background-color: #28a745;
        border-color: #28a745;
        color: #ffffff;
    }
</style>

<?php require_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>
