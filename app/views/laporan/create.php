<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* Validation styles for luas serangan vs populasi */
.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-valid {
    border-color: #28a745;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

.invalid-feedback:empty {
    display: none;
}

#btnSubmitForm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}



/* Coordinate Input Styles */
.coord-mode-content {
    min-height: 100px;
}

#coordinateMap {
    cursor: crosshair;
}

#coordinateMap.leaflet-container {
    z-index: 1;
}

.coord-mode-content .form-control:read-only {
    background-color: #e9ecef;
    cursor: not-allowed;
}

#selectedCoordinates {
    word-break: break-all;
}

/* Reset Button Styles */
#btnResetCoordinates {
    transition: all 0.2s ease;
}

#btnResetCoordinates:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#btnResetCoordinates:active {
    transform: translateY(0);
}

@media (max-width: 576px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    #btnResetCoordinates {
        margin-top: 0.5rem;
        width: 100%;
    }
}
</style>

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i> Buat Laporan Hama/Penyakit Baru
                    <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                        <span class="badge badge-danger ml-2">Mode Admin</span>
                    <?php elseif(($_SESSION['role'] ?? '') === 'operator'): ?>
                        <span class="badge badge-primary ml-2">Mode Operator</span>
                    <?php elseif(($_SESSION['role'] ?? '') === 'petugas'): ?>
                        <span class="badge badge-success ml-2">Mode Petugas</span>
                    <?php endif; ?>
                </h3>
            </div>
            <form action="<?= BASE_URL ?>laporan/create" method="POST" enctype="multipart/form-data" id="formCreateLaporan">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card-body">
                    <?php if(($_SESSION['role'] ?? '') === 'admin' && !empty($users)): ?>
                    <!-- Admin: Select user to create report on behalf of -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Mode Admin:</strong> Anda dapat membuat laporan atas nama user lain.
                    </div>
                    <div class="form-group">
                        <label>Buat Laporan Atas Nama <span class="text-danger">*</span></label>
                        <select name="target_user_id" class="form-control" id="targetUserId">
                            <option value="<?= $currentUser['id'] ?>">Saya sendiri (<?= htmlspecialchars($currentUser['nama_lengkap']) ?>)</option>
                            <?php foreach($users as $u): ?>
                                <?php if($u['id'] != $currentUser['id'] && $u['aktif'] == 1): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['nama_lengkap']) ?> (<?= htmlspecialchars($u['username']) ?> - <?= ucfirst($u['role']) ?>)
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih user yang akan menjadi pelapor laporan ini</small>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Pelaporan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>OPT <span class="text-danger">*</span></label>
                                <select name="master_opt_id" class="form-control" required>
                                    <option value="">-- Pilih OPT --</option>
                                    <?php foreach($data_opt as $opt): ?>
                                    <option value="<?= $opt['id'] ?>"><?= htmlspecialchars($opt['nama_opt']) ?> (<?= $opt['jenis'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kabupaten <span class="text-danger">*</span></label>
                                <select name="kabupaten_id" id="kabupatenSelect" class="form-control" required>
                                    <option value="">-- Pilih Kabupaten --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kecamatan <span class="text-danger">*</span></label>
                                <select name="kecamatan_id" id="kecamatanSelect" class="form-control" required>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Desa <span class="text-danger">*</span></label>
                                <select name="desa_id" id="desaSelect" class="form-control" required>
                                    <option value="">-- Pilih Desa --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Alamat Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="alamat_lengkap" class="form-control" placeholder="Contoh: Blok Kedawung No.12 RT 02 RW 03" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coordinate Input System -->
                    <div class="form-group">
                        <label>Koordinat GPS</label>
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marker-alt"></i> Input Koordinat
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Mode Toggle -->
                                <div class="btn-group btn-group-toggle mb-3" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active">
                                        <input type="radio" name="coordMode" id="coordModeManual" value="manual" checked>
                                        <i class="fas fa-keyboard"></i> Manual
                                    </label>
                                    <label class="btn btn-outline-success">
                                        <input type="radio" name="coordMode" id="coordModeAuto" value="auto">
                                        <i class="fas fa-map"></i> Peta Interaktif
                                    </label>
                                    <label class="btn btn-outline-info">
                                        <input type="radio" name="coordMode" id="coordModeCurrent" value="current">
                                        <i class="fas fa-crosshairs"></i> Lokasi Saya
                                    </label>
                                </div>
                                
                                <!-- Manual Mode -->
                                <div id="manualCoordMode" class="coord-mode-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                                <label>Latitude <small class="text-muted">(Lintang)</small></label>
                                                <input type="number" 
                                                       name="latitude" 
                                                       id="latitudeInput" 
                                                       class="form-control" 
                                                       placeholder="Contoh: -8.174381" 
                                                       step="any"
                                                       min="-90"
                                                       max="90"
                                                       inputmode="decimal">
                                                <small class="text-muted">Range: -90.000000 sampai 90.000000</small>
                                                <div class="invalid-feedback" id="latitudeError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                                <label>Longitude <small class="text-muted">(Bujur)</small></label>
                                                <input type="number" 
                                                       name="longitude" 
                                                       id="longitudeInput" 
                                                       class="form-control" 
                                                       placeholder="Contoh: 113.701399" 
                                                       step="any"
                                                       min="-180"
                                                       max="180"
                                                       inputmode="decimal">
                                                <small class="text-muted">Range: -180.000000 sampai 180.000000</small>
                                                <div class="invalid-feedback" id="longitudeError"></div>
                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Contoh koordinat Jember:</strong> Latitude: -8.174381, Longitude: 113.701399
                                    </div>
                                </div>
                                
                                <!-- Auto Mode - Interactive Map -->
                                <div id="autoCoordMode" class="coord-mode-content" style="display: none;">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-mouse-pointer"></i> 
                                        <strong>Petunjuk:</strong> Klik pada peta untuk memilih lokasi. Koordinat akan otomatis terisi.
                                    </div>
                                    <div id="coordinateMap" style="height: 400px; width: 100%; border-radius: 0.25rem; border: 1px solid #dee2e6;"></div>
                                    <div class="mt-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Latitude (Otomatis dari Peta)</label>
                                                    <input type="text" 
                                                           id="latitudeFromMap" 
                                                           class="form-control" 
                                                           readonly
                                                           placeholder="Klik pada peta...">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Longitude (Otomatis dari Peta)</label>
                                                    <input type="text" 
                                                           id="longitudeFromMap" 
                                                           class="form-control" 
                                                           readonly
                                                           placeholder="Klik pada peta...">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success mt-2" id="btnApplyMapCoordinates">
                                            <i class="fas fa-check"></i> Gunakan Koordinat dari Peta
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Current Location Mode -->
                                <div id="currentLocationMode" class="coord-mode-content" style="display: none;">
                                    <div class="alert alert-warning mb-3">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Perhatian:</strong> Fitur ini memerlukan izin akses lokasi dari browser Anda.
                                    </div>
                                    <button type="button" class="btn btn-info" id="btnGetCurrentLocation">
                                        <i class="fas fa-crosshairs"></i> Ambil Lokasi Saat Ini
                                    </button>
                                    <div id="currentLocationStatus" class="mt-3"></div>
                                </div>
                                
                                <!-- Coordinate Display (Summary) -->
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="mb-0"><strong>Koordinat Terpilih:</strong></label>
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary" 
                                                id="btnResetCoordinates"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                title="Reset koordinat ke keadaan awal">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>
                                    <div id="selectedCoordinates" class="alert alert-secondary mb-0">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <span id="coordDisplay">Belum ada koordinat dipilih</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tingkat Keparahan <span class="text-danger">*</span></label>
                                <select name="tingkat_keparahan" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Ringan">Ringan</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Berat">Berat</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Populasi/Intensitas</label>
                                <input type="number" name="populasi" id="populasiInput" class="form-control" value="0" min="0" step="0.01" inputmode="numeric" pattern="[0-9]*\.?[0-9]*">
                                <small class="text-muted">Jumlah individu per area</small>
                                <div class="invalid-feedback" id="populasiError"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Luas Serangan (Ha)</label>
                                <input type="number" name="luas_serangan" id="luasSeranganInput" class="form-control" value="0" min="0" step="0.01" inputmode="numeric" pattern="[0-9]*\.?[0-9]*">
                                <small class="text-muted">Tidak boleh melebihi Populasi/Intensitas</small>
                                <div class="invalid-feedback" id="luasSeranganError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" id="catatanTextarea" class="form-control" rows="3" placeholder="Deskripsi kondisi, gejala, atau informasi tambahan"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Foto</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="foto" class="custom-file-input" id="fotoInput" accept="image/jpeg,image/png,image/jpg,image/webp">
                                <label class="custom-file-label" for="fotoInput">Pilih foto...</label>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Format: JPG, PNG, WEBP. 
                            <strong>File > 2MB akan dikompresi otomatis</strong>
                        </small>
                        <div id="fotoPreview" class="mt-2" style="display: none;">
                            <img id="previewImg" src="" style="max-width: 300px; max-height: 300px;" class="img-thumbnail">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearFotoPreview()">
                                <i class="fas fa-times"></i> Hapus Foto
                            </button>
                        </div>
                    </div>
                    
                    <!-- Status Controls (Role-Based) -->
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <?php if(($_SESSION['role'] ?? '') === 'petugas'): ?>
                        <!-- Petugas: Dropdown (Draf and Submitted only) -->
                        <select name="status" class="form-control" required id="statusSelect">
                            <option value="Draf">Simpan sebagai Draf</option>
                            <option value="Submitted" selected>Submit untuk Verifikasi</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Petugas dapat menyimpan sebagai Draf atau Submit untuk verifikasi oleh operator/admin.
                        </small>
                        <?php elseif(($_SESSION['role'] ?? '') === 'operator'): ?>
                        <!-- Operator: Dropdown with Draf, Submitted, or Diverifikasi -->
                        <select name="status" class="form-control" required id="statusSelect">
                            <option value="Draf">Simpan sebagai Draf</option>
                            <option value="Submitted" selected>Submit untuk Verifikasi</option>
                            <option value="Diverifikasi">Langsung Diverifikasi (Approve)</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Operator dapat langsung memverifikasi laporan yang dibuat.
                        </small>
                        <?php else: ?>
                        <!-- Admin: Full control -->
                        <select name="status" class="form-control" required id="statusSelect">
                            <option value="Draf">Simpan sebagai Draf</option>
                            <option value="Submitted">Submit untuk Verifikasi</option>
                            <option value="Diverifikasi" selected>Langsung Diverifikasi (Approve)</option>
                            <option value="Ditolak">Tandai sebagai Ditolak</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Admin memiliki kontrol penuh atas status laporan.
                        </small>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="btnSubmitForm">
                        <i class="fas fa-save"></i> Simpan Laporan
                    </button>
                    <a href="<?= BASE_URL ?>laporan" class="btn btn-secondary" id="btnCancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <!-- Loading indicator -->
                    <span id="loadingIndicator" style="display: none; margin-left: 10px;">
                        <i class="fas fa-spinner fa-spin"></i> Menyimpan data...
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File input label update with compression info
document.getElementById('fotoInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Pilih foto...';
    const label = document.querySelector('.custom-file-label');
    label.textContent = fileName;
    
    const file = e.target.files[0];
    if (file) {
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        // Show file size info
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        if (file.size > maxSize) {
            // Show info that file will be compressed
            const infoDiv = document.createElement('div');
            infoDiv.className = 'alert alert-info mt-2';
            infoDiv.innerHTML = `<i class="fas fa-info-circle"></i> Ukuran file: ${fileSizeMB} MB. File akan dikompresi otomatis saat upload.`;
            
            // Remove old info if exists
            const oldInfo = document.querySelector('.file-size-info');
            if (oldInfo) oldInfo.remove();
            
            infoDiv.className += ' file-size-info';
            document.querySelector('.custom-file').parentElement.appendChild(infoDiv);
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('fotoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function clearFotoPreview() {
    document.getElementById('fotoInput').value = '';
    document.querySelector('.custom-file-label').textContent = 'Pilih foto...';
    document.getElementById('fotoPreview').style.display = 'none';
}

// Validation state
let isFormValid = true;
let luasSeranganValid = true;
let populasiValid = true;

// Validation function for luas serangan vs populasi
function validateLuasSeranganVsPopulasi() {
    const populasiInput = document.getElementById('populasiInput');
    const luasSeranganInput = document.getElementById('luasSeranganInput');
    const populasiError = document.getElementById('populasiError');
    const luasSeranganError = document.getElementById('luasSeranganError');
    const btnSubmit = document.getElementById('btnSubmitForm');
    
    // Get values
    const populasi = parseFloat(populasiInput.value) || 0;
    const luasSerangan = parseFloat(luasSeranganInput.value) || 0;
    
    // Reset previous states
    populasiInput.classList.remove('is-invalid', 'is-valid');
    luasSeranganInput.classList.remove('is-invalid', 'is-valid');
    populasiError.textContent = '';
    luasSeranganError.textContent = '';
    
    // Validate populasi is numeric
    if (populasiInput.value && (isNaN(populasi) || populasi < 0)) {
        populasiInput.classList.add('is-invalid');
        populasiError.textContent = 'Populasi harus berupa angka positif';
        populasiValid = false;
    } else {
        populasiValid = true;
        if (populasiInput.value) {
            populasiInput.classList.add('is-valid');
        }
    }
    
    // Validate luas serangan is numeric
    if (luasSeranganInput.value && (isNaN(luasSerangan) || luasSerangan < 0)) {
        luasSeranganInput.classList.add('is-invalid');
        luasSeranganError.textContent = 'Luas serangan harus berupa angka positif atau nol';
        luasSeranganValid = false;
    } else if (luasSeranganInput.value && luasSerangan > 0 && populasi > 0 && luasSerangan > populasi) {
        // Main validation: luas serangan tidak boleh melebihi populasi (boleh sama dengan)
        luasSeranganInput.classList.add('is-invalid');
        luasSeranganError.textContent = 'Luas Serangan (' + luasSerangan.toFixed(2) + ' Ha) tidak boleh melebihi Populasi/Intensitas (' + populasi.toFixed(2) + ' Ha). Nilai maksimal yang diizinkan: ' + populasi.toFixed(2) + ' Ha';
        luasSeranganValid = false;
    } else {
        luasSeranganValid = true;
        if (luasSeranganInput.value) {
            luasSeranganInput.classList.add('is-valid');
        }
    }
    
    // Update form validity
    isFormValid = populasiValid && luasSeranganValid;
    
    // Enable/disable submit button
    if (btnSubmit) {
        btnSubmit.disabled = !isFormValid;
    }
    
    return isFormValid;
}

// Real-time validation on input change
document.addEventListener('DOMContentLoaded', function() {
    const populasiInput = document.getElementById('populasiInput');
    const luasSeranganInput = document.getElementById('luasSeranganInput');
    const btnSubmit = document.getElementById('btnSubmitForm');
    
    if (populasiInput && luasSeranganInput) {
        // Validate on input change
        populasiInput.addEventListener('input', function() {
            validateLuasSeranganVsPopulasi();
        });
        
        populasiInput.addEventListener('blur', function() {
            validateLuasSeranganVsPopulasi();
        });
        
        luasSeranganInput.addEventListener('input', function() {
            validateLuasSeranganVsPopulasi();
        });
        
        luasSeranganInput.addEventListener('blur', function() {
            validateLuasSeranganVsPopulasi();
        });
        
        // Validate on paste
        populasiInput.addEventListener('paste', function() {
            setTimeout(validateLuasSeranganVsPopulasi, 10);
        });
        
        luasSeranganInput.addEventListener('paste', function() {
            setTimeout(validateLuasSeranganVsPopulasi, 10);
        });
        
        // Initial validation
        validateLuasSeranganVsPopulasi();
    }
    
    // Ensure numeric input only (prevent non-numeric characters)
    if (populasiInput) {
        populasiInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9.]/.test(char)) {
                e.preventDefault();
            }
            // Prevent multiple decimal points
            if (char === '.' && this.value.indexOf('.') !== -1) {
                e.preventDefault();
            }
        });
    }
    
    if (luasSeranganInput) {
        luasSeranganInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9.]/.test(char)) {
                e.preventDefault();
            }
            // Prevent multiple decimal points
            if (char === '.' && this.value.indexOf('.') !== -1) {
                e.preventDefault();
            }
        });
    }
});

// Form validation and submission
document.querySelector('form').addEventListener('submit', function(e) {
    // Note: File size validation removed - server will handle compression automatically
    
    // Validate required fields
    const requiredFields = [
        { name: 'tanggal', label: 'Tanggal Pelaporan' },
        { name: 'master_opt_id', label: 'OPT' },
        { name: 'kabupaten_id', label: 'Kabupaten' },
        { name: 'kecamatan_id', label: 'Kecamatan' },
        { name: 'desa_id', label: 'Desa' },
        { name: 'alamat_lengkap', label: 'Alamat Lengkap' },
        { name: 'tingkat_keparahan', label: 'Tingkat Keparahan' }
    ];
    
    for (let field of requiredFields) {
        const input = document.querySelector(`[name="${field.name}"]`);
        if (!input || !input.value || input.value === '' || input.value === 'unknown') {
            e.preventDefault();
            alert(`Field "${field.label}" wajib diisi!`);
            input.focus();
            return false;
        }
    }
    
    // Validate luas serangan vs populasi before submit
    if (!validateLuasSeranganVsPopulasi()) {
        e.preventDefault();
        const luasSeranganInput = document.getElementById('luasSeranganInput');
        if (luasSeranganInput) {
            luasSeranganInput.focus();
            luasSeranganInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        alert('Luas Serangan tidak boleh melebihi Populasi/Intensitas. Silakan perbaiki input sebelum melanjutkan.');
        return false;
    }
    
    // Get status value (now always from select dropdown)
    const statusSelect = document.querySelector('[name="status"]');
    const statusValue = statusSelect ? statusSelect.value : '';
    
    // Log status for debugging
    console.log('Status being submitted:', statusValue);
    
    // Show loading indicator
    const btnSubmit = document.getElementById('btnSubmitForm');
    const btnCancel = document.getElementById('btnCancel');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    if (btnSubmit && loadingIndicator) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        if (btnCancel) btnCancel.style.display = 'none';
        loadingIndicator.style.display = 'inline';
    }
    
    // Show confirmation message based on status
    let statusText = '';
    switch(statusValue) {
        case 'Draf':
            statusText = 'disimpan sebagai draf';
            break;
        case 'Submitted':
            statusText = 'disubmit untuk verifikasi';
            break;
        case 'Diverifikasi':
            statusText = 'langsung diverifikasi (approved)';
            break;
        case 'Ditolak':
            statusText = 'ditandai sebagai ditolak';
            break;
        default:
            statusText = 'disimpan';
    }
    console.log(`Laporan akan ${statusText}`);
});

// Status dropdown is now used for all roles (petugas, operator, admin)
// No special JavaScript handling needed - standard form select element

// ============================================================================
// COORDINATE INPUT SYSTEM - Manual, Map, and Current Location
// ============================================================================
(function() {
    'use strict';
    
    // State
    let map = null;
    let marker = null;
    let currentLat = null;
    let currentLng = null;
    
    // Elements
    const coordModeManual = document.getElementById('coordModeManual');
    const coordModeAuto = document.getElementById('coordModeAuto');
    const coordModeCurrent = document.getElementById('coordModeCurrent');
    const manualCoordMode = document.getElementById('manualCoordMode');
    const autoCoordMode = document.getElementById('autoCoordMode');
    const currentLocationMode = document.getElementById('currentLocationMode');
    const latitudeInput = document.getElementById('latitudeInput');
    const longitudeInput = document.getElementById('longitudeInput');
    const latitudeFromMap = document.getElementById('latitudeFromMap');
    const longitudeFromMap = document.getElementById('longitudeFromMap');
    const btnApplyMapCoordinates = document.getElementById('btnApplyMapCoordinates');
    const btnGetCurrentLocation = document.getElementById('btnGetCurrentLocation');
    const coordDisplay = document.getElementById('coordDisplay');
    const selectedCoordinates = document.getElementById('selectedCoordinates');
    
    // ============================================================================
    // COORDINATE VALIDATION
    // ============================================================================
    
    /**
     * Validate coordinate format and range
     */
    function validateCoordinate(lat, lng) {
        const errors = [];
        
        // Validate latitude
        const latitude = parseFloat(lat);
        if (lat && isNaN(latitude)) {
            errors.push('Latitude harus berupa angka');
        } else if (lat && (latitude < -90 || latitude > 90)) {
            errors.push('Latitude harus antara -90 dan 90');
        }
        
        // Validate longitude
        const longitude = parseFloat(lng);
        if (lng && isNaN(longitude)) {
            errors.push('Longitude harus berupa angka');
        } else if (lng && (longitude < -180 || longitude > 180)) {
            errors.push('Longitude harus antara -180 dan 180');
        }
        
        // Validate both coordinates are provided if one is filled
        if ((lat && !lng) || (!lat && lng)) {
            errors.push('Kedua koordinat (Latitude dan Longitude) harus diisi bersama');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors,
            latitude: latitude,
            longitude: longitude
        };
    }
    
    /**
     * Update coordinate display
     */
    function updateCoordinateDisplay(lat, lng) {
        if (lat && lng) {
            const latFloat = parseFloat(lat);
            const lngFloat = parseFloat(lng);
            if (!isNaN(latFloat) && !isNaN(lngFloat)) {
                coordDisplay.textContent = `${latFloat.toFixed(6)}, ${lngFloat.toFixed(6)}`;
                currentLat = latFloat;
                currentLng = lngFloat;
            } else {
                coordDisplay.textContent = 'Belum ada koordinat dipilih';
                currentLat = null;
                currentLng = null;
            }
        } else {
            coordDisplay.textContent = 'Belum ada koordinat dipilih';
            currentLat = null;
            currentLng = null;
        }
    }
    
    // ============================================================================
    // MANUAL MODE
    // ============================================================================
    
    if (latitudeInput && longitudeInput) {
        latitudeInput.addEventListener('blur', function() {
            const lat = this.value.trim();
            const lng = longitudeInput.value.trim();
            const validation = validateCoordinate(lat, lng);
            
            if (lat !== '' || lng !== '') {
                if (!validation.valid) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    const errorEl = document.getElementById('latitudeError');
                    if (errorEl) {
                        errorEl.textContent = validation.errors.find(e => e.includes('Latitude')) || '';
                    }
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    const errorEl = document.getElementById('latitudeError');
                    if (errorEl) errorEl.textContent = '';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                const errorEl = document.getElementById('latitudeError');
                if (errorEl) errorEl.textContent = '';
            }
            
            updateCoordinateDisplay(lat, lng);
        });
        
        longitudeInput.addEventListener('blur', function() {
            const lat = latitudeInput.value.trim();
            const lng = this.value.trim();
            const validation = validateCoordinate(lat, lng);
            
            if (lat !== '' || lng !== '') {
                if (!validation.valid) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    const errorEl = document.getElementById('longitudeError');
                    if (errorEl) {
                        errorEl.textContent = validation.errors.find(e => e.includes('Longitude')) || '';
                    }
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    const errorEl = document.getElementById('longitudeError');
                    if (errorEl) errorEl.textContent = '';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                const errorEl = document.getElementById('longitudeError');
                if (errorEl) errorEl.textContent = '';
            }
            
            updateCoordinateDisplay(lat, lng);
        });
        
        // Update display on input change
        latitudeInput.addEventListener('input', function() {
            updateCoordinateDisplay(this.value, longitudeInput.value);
        });
        
        longitudeInput.addEventListener('input', function() {
            updateCoordinateDisplay(latitudeInput.value, this.value);
        });
    }
    
    // ============================================================================
    // AUTO MODE - INTERACTIVE MAP
    // ============================================================================
    
    /**
     * Initialize Leaflet map
     */
    function initMap() {
        if (typeof L === 'undefined') {
            const mapContainer = document.getElementById('coordinateMap');
            if (mapContainer) {
                mapContainer.innerHTML = '<div class="alert alert-warning m-3">Leaflet.js tidak dapat dimuat. Periksa koneksi internet Anda.</div>';
            }
            return;
        }
        
        // Center on Jember
        const jemberCenter = [-8.1706, 113.7003];
        const mapContainer = document.getElementById('coordinateMap');
        if (!mapContainer) return;
        
        // Initialize map
        map = L.map('coordinateMap').setView(jemberCenter, 12);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // If there are existing coordinates, show them
        if (latitudeInput && longitudeInput && latitudeInput.value && longitudeInput.value) {
            const lat = parseFloat(latitudeInput.value);
            const lng = parseFloat(longitudeInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                setMapMarker(lat, lng);
            }
        }
        
        // Handle map click
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // Update map inputs
            if (latitudeFromMap) latitudeFromMap.value = lat.toFixed(6);
            if (longitudeFromMap) longitudeFromMap.value = lng.toFixed(6);
            
            // Set marker
            setMapMarker(lat, lng);
        });
        
        // Prevent map initialization errors
        setTimeout(function() {
            if (map) {
                map.invalidateSize();
            }
        }, 100);
    }
    
    /**
     * Set marker on map
     */
    function setMapMarker(lat, lng) {
        if (!map) return;
        
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Create custom icon
        const customIcon = L.divIcon({
            className: 'custom-coordinate-marker',
            html: `<div style="background-color: #007bff; width: 30px; height: 30px; border-radius: 50%; border: 4px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        
        // Add marker
        marker = L.marker([lat, lng], { 
            icon: customIcon,
            draggable: true
        }).addTo(map);
        
        // Handle marker drag
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            if (latitudeFromMap) latitudeFromMap.value = position.lat.toFixed(6);
            if (longitudeFromMap) longitudeFromMap.value = position.lng.toFixed(6);
        });
        
        // Center map on marker
        map.setView([lat, lng], map.getZoom());
    }
    
    // Apply map coordinates to form
    if (btnApplyMapCoordinates) {
        btnApplyMapCoordinates.addEventListener('click', function() {
            if (!latitudeFromMap || !longitudeFromMap) return;
            
            const lat = latitudeFromMap.value.trim();
            const lng = longitudeFromMap.value.trim();
            
            if (!lat || !lng) {
                alert('Silakan klik pada peta untuk memilih lokasi terlebih dahulu.');
                return;
            }
            
            if (latitudeInput) latitudeInput.value = parseFloat(lat).toFixed(6);
            if (longitudeInput) longitudeInput.value = parseFloat(lng).toFixed(6);
            
            updateCoordinateDisplay(lat, lng);
        });
    }
    
    // ============================================================================
    // CURRENT LOCATION MODE
    // ============================================================================
    
    if (btnGetCurrentLocation) {
        btnGetCurrentLocation.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Geolocation tidak didukung oleh browser Anda.');
                return;
            }
            
            btnGetCurrentLocation.disabled = true;
            btnGetCurrentLocation.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari lokasi...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    
                    if (latitudeInput) latitudeInput.value = lat;
                    if (longitudeInput) longitudeInput.value = lng;
                    
                    updateCoordinateDisplay(lat, lng);
                    
                    btnGetCurrentLocation.disabled = false;
                    btnGetCurrentLocation.innerHTML = '<i class="fas fa-crosshairs"></i> Ambil Lokasi Saat Ini';
                    
                    // Show success message
                    const statusDiv = document.getElementById('currentLocationStatus');
                    if (statusDiv) {
                        statusDiv.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> Lokasi ditemukan: ${lat}, ${lng}</div>`;
                    }
                },
                function(error) {
                    let errorMessage = 'Gagal mengambil lokasi Anda.';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Akses lokasi ditolak. Mohon izinkan akses lokasi di browser Anda.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Permintaan lokasi habis waktu.';
                            break;
                    }
                    
                    btnGetCurrentLocation.disabled = false;
                    btnGetCurrentLocation.innerHTML = '<i class="fas fa-crosshairs"></i> Ambil Lokasi Saat Ini';
                    
                    const statusDiv = document.getElementById('currentLocationStatus');
                    if (statusDiv) {
                        statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${errorMessage}</div>`;
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }
    
    // ============================================================================
    // MODE TOGGLE
    // ============================================================================
    
    function showCoordMode(mode) {
        if (manualCoordMode) manualCoordMode.style.display = 'none';
        if (autoCoordMode) autoCoordMode.style.display = 'none';
        if (currentLocationMode) currentLocationMode.style.display = 'none';
        
        if (map) {
            map.remove();
            map = null;
            marker = null;
        }
        
        if (mode === 'manual' && manualCoordMode) {
            manualCoordMode.style.display = 'block';
        } else if (mode === 'auto' && autoCoordMode) {
            autoCoordMode.style.display = 'block';
            setTimeout(initMap, 100);
        } else if (mode === 'current' && currentLocationMode) {
            currentLocationMode.style.display = 'block';
        }
    }
    
    if (coordModeManual) {
        coordModeManual.addEventListener('change', function() {
            if (this.checked) {
                showCoordMode('manual');
            }
        });
    }
    
    if (coordModeAuto) {
        coordModeAuto.addEventListener('change', function() {
            if (this.checked) {
                showCoordMode('auto');
            }
        });
    }
    
    if (coordModeCurrent) {
        coordModeCurrent.addEventListener('change', function() {
            if (this.checked) {
                showCoordMode('current');
            }
        });
    }
    
    // Initialize
    if (latitudeInput && longitudeInput) {
        updateCoordinateDisplay(latitudeInput.value, longitudeInput.value);
    }
    
    // Initialize mode
    if (coordModeManual && coordModeManual.checked) {
        showCoordMode('manual');
    } else if (coordModeAuto && coordModeAuto.checked) {
        showCoordMode('auto');
    } else if (coordModeCurrent && coordModeCurrent.checked) {
        showCoordMode('current');
    }
})();
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
<script>
async function fetchJSON(url){ const r = await fetch(url); return r.json(); }
async function loadKabupaten(){
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/kabupaten');
  const sel = document.getElementById('kabupatenSelect');
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_kabupaten; sel.appendChild(opt); });
}
async function loadKecamatan(kabupatenId){
  const sel = document.getElementById('kecamatanSelect'); sel.innerHTML = '<option value="">-- Pilih Kecamatan --</option><option value="unknown">Tidak Diketahui</option>';
  const selDesa = document.getElementById('desaSelect'); selDesa.innerHTML = '<option value="">-- Pilih Desa --</option><option value="unknown">Tidak Diketahui</option>';
  if(!kabupatenId || kabupatenId==='unknown') return;
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/kecamatan/'+kabupatenId);
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_kecamatan; sel.appendChild(opt); });
}
async function loadDesa(kecamatanId){
  const sel = document.getElementById('desaSelect'); sel.innerHTML = '<option value="">-- Pilih Desa --</option><option value="unknown">Tidak Diketahui</option>';
  if(!kecamatanId || kecamatanId==='unknown') return;
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/desa/'+kecamatanId);
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_desa; sel.appendChild(opt); });
}
document.getElementById('kabupatenSelect').addEventListener('change', e=> loadKecamatan(e.target.value));
document.getElementById('kecamatanSelect').addEventListener('change', e=> loadDesa(e.target.value));
loadKabupaten();
(function() {
    'use strict';
    
    // State
    let map = null;
    let marker = null;
    let currentLat = null;
    let currentLng = null;
    
    // Elements
    const coordModeManual = document.getElementById('coordModeManual');
    const coordModeAuto = document.getElementById('coordModeAuto');
    const coordModeCurrent = document.getElementById('coordModeCurrent');
    const manualCoordMode = document.getElementById('manualCoordMode');
    const autoCoordMode = document.getElementById('autoCoordMode');
    const currentLocationMode = document.getElementById('currentLocationMode');
    const latitudeInput = document.getElementById('latitudeInput');
    const longitudeInput = document.getElementById('longitudeInput');
    const latitudeFromMap = document.getElementById('latitudeFromMap');
    const longitudeFromMap = document.getElementById('longitudeFromMap');
    const btnApplyMapCoordinates = document.getElementById('btnApplyMapCoordinates');
    const btnGetCurrentLocation = document.getElementById('btnGetCurrentLocation');
    const coordDisplay = document.getElementById('coordDisplay');
    const selectedCoordinates = document.getElementById('selectedCoordinates');
    
    // ============================================================================
    // COORDINATE VALIDATION
    // ============================================================================
    
    /**
     * Validate coordinate format and range
     */
    function validateCoordinate(lat, lng) {
        const errors = [];
        
        // Validate latitude
        if (lat === '' || lat === null) {
            // Empty is allowed (optional field)
            return { valid: true, errors: [] };
        }
        
        const latitude = parseFloat(lat);
        if (isNaN(latitude)) {
            errors.push('Latitude harus berupa angka');
        } else if (latitude < -90 || latitude > 90) {
            errors.push('Latitude harus antara -90 dan 90');
        }
        
        // Validate longitude
        const longitude = parseFloat(lng);
        if (isNaN(longitude)) {
            errors.push('Longitude harus berupa angka');
        } else if (longitude < -180 || longitude > 180) {
            errors.push('Longitude harus antara -180 dan 180');
        }
        
        // Validate both coordinates are provided if one is filled
        if ((lat && !lng) || (!lat && lng)) {
            errors.push('Kedua koordinat (Latitude dan Longitude) harus diisi bersama');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors,
            latitude: latitude,
            longitude: longitude
        };
    }
    
    /**
     * Update coordinate display
     */
    function updateCoordinateDisplay(lat, lng) {
        if (lat && lng) {
            const latFloat = parseFloat(lat);
            const lngFloat = parseFloat(lng);
            if (!isNaN(latFloat) && !isNaN(lngFloat)) {
                coordDisplay.textContent = `${latFloat.toFixed(6)}, ${lngFloat.toFixed(6)}`;
                selectedCoordinates.className = 'alert alert-success mb-0';
            } else {
                coordDisplay.textContent = 'Belum ada koordinat dipilih';
                selectedCoordinates.className = 'alert alert-secondary mb-0';
            }
        } else {
            coordDisplay.textContent = 'Belum ada koordinat dipilih';
            selectedCoordinates.className = 'alert alert-secondary mb-0';
        }
    }
    
    /**
     * Set coordinates to form inputs
     */
    function setCoordinates(lat, lng, validate = true) {
        if (validate) {
            const validation = validateCoordinate(lat, lng);
            if (!validation.valid && lat !== '' && lng !== '') {
                // Show errors but still allow setting
                console.warn('Coordinate validation errors:', validation.errors);
            }
        }
        
        // Remove validation classes
        if (latitudeInput) {
            latitudeInput.classList.remove('is-invalid', 'is-valid');
            if (document.getElementById('latitudeError')) {
                document.getElementById('latitudeError').textContent = '';
            }
        }
        if (longitudeInput) {
            longitudeInput.classList.remove('is-invalid', 'is-valid');
            if (document.getElementById('longitudeError')) {
                document.getElementById('longitudeError').textContent = '';
            }
        }
        
        // Set values
        if (latitudeInput) latitudeInput.value = lat || '';
        if (longitudeInput) longitudeInput.value = lng || '';
        
        // Update display
        updateCoordinateDisplay(lat, lng);
        
        // Store current coordinates
        currentLat = lat ? parseFloat(lat) : null;
        currentLng = lng ? parseFloat(lng) : null;
    }
    
    // ============================================================================
    // MANUAL MODE
    // ============================================================================
    
    if (latitudeInput && longitudeInput) {
        // Validate on blur
        latitudeInput.addEventListener('blur', function() {
            const lat = this.value.trim();
            const lng = longitudeInput.value.trim();
            const validation = validateCoordinate(lat, lng);
            
            if (lat !== '' || lng !== '') {
                if (!validation.valid) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    const errorEl = document.getElementById('latitudeError');
                    if (errorEl) {
                        errorEl.textContent = validation.errors.find(e => e.includes('Latitude')) || '';
                    }
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    const errorEl = document.getElementById('latitudeError');
                    if (errorEl) errorEl.textContent = '';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                const errorEl = document.getElementById('latitudeError');
                if (errorEl) errorEl.textContent = '';
            }
            
            updateCoordinateDisplay(lat, lng);
        });
        
        longitudeInput.addEventListener('blur', function() {
            const lat = latitudeInput.value.trim();
            const lng = this.value.trim();
            const validation = validateCoordinate(lat, lng);
            
            if (lat !== '' || lng !== '') {
                if (!validation.valid) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    const errorEl = document.getElementById('longitudeError');
                    if (errorEl) {
                        errorEl.textContent = validation.errors.find(e => e.includes('Longitude')) || '';
                    }
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    const errorEl = document.getElementById('longitudeError');
                    if (errorEl) errorEl.textContent = '';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                const errorEl = document.getElementById('longitudeError');
                if (errorEl) errorEl.textContent = '';
            }
            
            updateCoordinateDisplay(lat, lng);
        });
        
        // Update display on input change
        latitudeInput.addEventListener('input', function() {
            updateCoordinateDisplay(this.value, longitudeInput.value);
        });
        
        longitudeInput.addEventListener('input', function() {
            updateCoordinateDisplay(latitudeInput.value, this.value);
        });
    }
    
    // ============================================================================
    // AUTO MODE - INTERACTIVE MAP
    // ============================================================================
    
    /**
     * Initialize Leaflet map
     */
    function initMap() {
        if (typeof L === 'undefined') {
            console.error('Leaflet.js not loaded');
            const mapContainer = document.getElementById('coordinateMap');
            if (mapContainer) {
                mapContainer.innerHTML = '<div class="alert alert-warning m-3">Leaflet.js tidak dapat dimuat. Periksa koneksi internet Anda.</div>';
            }
            return;
        }
        
        // Center on Jember
        const jemberCenter = [-8.1706, 113.7003];
        const mapContainer = document.getElementById('coordinateMap');
        if (!mapContainer) return;
        
        // Initialize map
        map = L.map('coordinateMap').setView(jemberCenter, 12);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // If there are existing coordinates, show them
        if (latitudeInput && longitudeInput && latitudeInput.value && longitudeInput.value) {
            const lat = parseFloat(latitudeInput.value);
            const lng = parseFloat(longitudeInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                setMapMarker(lat, lng);
            }
        }
        
        // Handle map click
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // Update map inputs
            if (latitudeFromMap) latitudeFromMap.value = lat.toFixed(6);
            if (longitudeFromMap) longitudeFromMap.value = lng.toFixed(6);
            
            // Set marker
            setMapMarker(lat, lng);
        });
        
        // Prevent map initialization errors
        setTimeout(function() {
            if (map) {
                map.invalidateSize();
            }
        }, 100);
    }
    
    /**
     * Set marker on map
     */
    function setMapMarker(lat, lng) {
        if (!map) return;
        
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Create custom icon
        const customIcon = L.divIcon({
            className: 'custom-coordinate-marker',
            html: `<div style="background-color: #007bff; width: 30px; height: 30px; border-radius: 50%; border: 4px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        
        // Add marker
        marker = L.marker([lat, lng], { 
            icon: customIcon,
            draggable: true
        }).addTo(map);
        
        // Handle marker drag
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            if (latitudeFromMap) latitudeFromMap.value = position.lat.toFixed(6);
            if (longitudeFromMap) longitudeFromMap.value = position.lng.toFixed(6);
        });
        
        // Center map on marker
        map.setView([lat, lng], map.getZoom());
    }
    
    // Apply map coordinates to form
    if (btnApplyMapCoordinates) {
        btnApplyMapCoordinates.addEventListener('click', function() {
            if (!latitudeFromMap || !longitudeFromMap) return;
            
            const lat = latitudeFromMap.value.trim();
            const lng = longitudeFromMap.value.trim();
            
            if (!lat || !lng) {
                alert('Silakan klik pada peta untuk memilih lokasi terlebih dahulu.');
                return;
            }
            
            setCoordinates(lat, lng);
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-2';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> Koordinat berhasil diterapkan!
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            this.parentNode.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        });
    }
    
    // ============================================================================
    // CURRENT LOCATION MODE
    // ============================================================================
    
    if (btnGetCurrentLocation) {
        btnGetCurrentLocation.addEventListener('click', function() {
            const statusDiv = document.getElementById('currentLocationStatus');
            const btn = this;
            
            if (!navigator.geolocation) {
                if (statusDiv) {
                    statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Browser Anda tidak mendukung Geolocation API.</div>';
                }
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengambil lokasi...';
            if (statusDiv) {
                statusDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Meminta izin akses lokasi...</div>';
            }
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    
                    setCoordinates(lat, lng);
                    
                    if (statusDiv) {
                        statusDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Lokasi berhasil didapatkan!</strong><br>
                                Latitude: ${lat}<br>
                                Longitude: ${lng}
                            </div>
                        `;
                    }
                    
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-crosshairs"></i> Ambil Lokasi Saat Ini';
                },
                function(error) {
                    let errorMsg = 'Tidak dapat mendapatkan lokasi. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg += 'Izin akses lokasi ditolak.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg += 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMsg += 'Permintaan lokasi timeout.';
                            break;
                        default:
                            errorMsg += 'Error tidak diketahui.';
                            break;
                    }
                    
                    if (statusDiv) {
                        statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ${errorMsg}</div>`;
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-crosshairs"></i> Ambil Lokasi Saat Ini';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }
    
    // ============================================================================
    // MODE TOGGLE
    // ============================================================================
    
    if (coordModeManual && coordModeAuto && coordModeCurrent && manualCoordMode && autoCoordMode && currentLocationMode) {
        coordModeManual.addEventListener('change', function() {
            if (this.checked) {
                manualCoordMode.style.display = 'block';
                autoCoordMode.style.display = 'none';
                currentLocationMode.style.display = 'none';
            }
        });
        
        coordModeAuto.addEventListener('change', function() {
            if (this.checked) {
                manualCoordMode.style.display = 'none';
                autoCoordMode.style.display = 'block';
                currentLocationMode.style.display = 'none';
                
                // Initialize map when switching to auto mode
                if (!map) {
                    setTimeout(initMap, 100);
                } else {
                    setTimeout(function() {
                        if (map) map.invalidateSize();
                    }, 100);
                }
            }
        });
        
        coordModeCurrent.addEventListener('change', function() {
            if (this.checked) {
                manualCoordMode.style.display = 'none';
                autoCoordMode.style.display = 'none';
                currentLocationMode.style.display = 'block';
            }
        });
    }
    
    // ============================================================================
    // RESET FUNCTIONALITY
    // ============================================================================
    
    /**
     * Reset coordinates to initial state
     */
    function resetCoordinates() {
        // Clear coordinate inputs
        if (latitudeInput) latitudeInput.value = '';
        if (longitudeInput) longitudeInput.value = '';
        
        // Clear map coordinate inputs
        if (latitudeFromMap) latitudeFromMap.value = '';
        if (longitudeFromMap) longitudeFromMap.value = '';
        
        // Reset current location status
        const currentLocationStatus = document.getElementById('currentLocationStatus');
        if (currentLocationStatus) {
            currentLocationStatus.innerHTML = '';
        }
        
        // Reset to Manual mode (default)
        if (coordModeManual && !coordModeManual.checked) {
            coordModeManual.checked = true;
            coordModeManual.dispatchEvent(new Event('change'));
        }
        
        // Reset map to initial state
        if (map) {
            // Remove marker
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            
            // Reset map view to Jember center
            const jemberCenter = [-8.1706, 113.7003];
            map.setView(jemberCenter, 12);
        }
        
        // Clear stored coordinates
        currentLat = null;
        currentLng = null;
        
        // Update display
        updateCoordinateDisplay('', '');
        
        // Remove validation classes
        if (latitudeInput) {
            latitudeInput.classList.remove('is-invalid', 'is-valid');
            const errorEl = document.getElementById('latitudeError');
            if (errorEl) errorEl.textContent = '';
        }
        if (longitudeInput) {
            longitudeInput.classList.remove('is-invalid', 'is-valid');
            const errorEl = document.getElementById('longitudeError');
            if (errorEl) errorEl.textContent = '';
        }
    }
    
    // Reset button event listener
    const btnResetCoordinates = document.getElementById('btnResetCoordinates');
    if (btnResetCoordinates) {
        btnResetCoordinates.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Confirm reset if coordinates are filled
            const hasCoordinates = (latitudeInput && latitudeInput.value) || (longitudeInput && longitudeInput.value);
            
            if (hasCoordinates) {
                if (confirm('Apakah Anda yakin ingin mereset koordinat? Semua koordinat yang sudah diisi akan dihapus.')) {
                    resetCoordinates();
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show mt-2';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle"></i> Koordinat berhasil direset!
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    `;
                    
                    // Insert after selectedCoordinates
                    const selectedCoordinates = document.getElementById('selectedCoordinates');
                    if (selectedCoordinates && selectedCoordinates.parentNode) {
                        selectedCoordinates.parentNode.appendChild(alert);
                    }
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                }
            } else {
                // No coordinates to reset, just reset anyway
                resetCoordinates();
            }
        });
        
        // Initialize Bootstrap tooltip when document is ready
        function initResetTooltip() {
            if (typeof $ !== 'undefined' && $.fn.tooltip) {
                $('#btnResetCoordinates').tooltip();
            } else if (document.readyState === 'loading') {
                // If document is still loading, wait for it
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof $ !== 'undefined' && $.fn.tooltip) {
                        $('#btnResetCoordinates').tooltip();
                    }
                });
            } else {
                // Fallback: Use native title attribute
                btnResetCoordinates.setAttribute('title', 'Reset koordinat ke keadaan awal');
            }
        }
        
        // Try to initialize immediately
        initResetTooltip();
        
        // Also try after a delay in case jQuery loads later
        setTimeout(initResetTooltip, 500);
    }
    
    // Initialize coordinate display
    if (latitudeInput && longitudeInput) {
        updateCoordinateDisplay(latitudeInput.value, longitudeInput.value);
    }
})();
</script>
