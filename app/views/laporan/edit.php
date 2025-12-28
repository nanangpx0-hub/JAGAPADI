<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Laporan</h3>
            </div>
            <form action="<?= BASE_URL ?>laporan/edit/<?= $laporan['id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Pelaporan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="<?= $laporan['tanggal'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>OPT <span class="text-danger">*</span></label>
                                <select name="master_opt_id" class="form-control" required>
                                    <option value="">-- Pilih OPT --</option>
                                    <?php foreach($data_opt as $opt): ?>
                                    <option value="<?= $opt['id'] ?>" <?= $opt['id'] == $laporan['master_opt_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt['nama_opt']) ?> (<?= $opt['jenis'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php 
                                // Cek role user untuk menentukan apakah field wajib
                                $isRequired = ($_SESSION['role'] ?? '') === 'petugas';
                                $requiredAttr = $isRequired ? 'required' : '';
                                $requiredMark = $isRequired ? '<span class="text-danger">*</span>' : '';
                                ?>
                                <label>Kabupaten <?= $requiredMark ?></label>
                                <select name="kabupaten_id" id="kabupatenSelect" class="form-control" <?= $requiredAttr ?>>
                                    <option value="">-- Pilih Kabupaten --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                                <?php if($isRequired): ?>
                                <div class="invalid-feedback">Kabupaten wajib dipilih</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kecamatan <?= $requiredMark ?></label>
                                <select name="kecamatan_id" id="kecamatanSelect" class="form-control" <?= $requiredAttr ?>>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                                <?php if($isRequired): ?>
                                <div class="invalid-feedback">Kecamatan wajib dipilih</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Desa <?= $requiredMark ?></label>
                                <select name="desa_id" id="desaSelect" class="form-control" <?= $requiredAttr ?>>
                                    <option value="">-- Pilih Desa --</option>
                                    <option value="unknown">Tidak Diketahui</option>
                                </select>
                                <?php if($isRequired): ?>
                                <div class="invalid-feedback">Desa wajib dipilih</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Alamat Lengkap <?= $requiredMark ?></label>
                                <input type="text" name="alamat_lengkap" class="form-control" value="<?= htmlspecialchars($laporan['alamat_lengkap'] ?? ($laporan['lokasi'] ?? '')) ?>" <?= $requiredAttr ?>>
                                <?php if($isRequired): ?>
                                <div class="invalid-feedback">Alamat lengkap wajib diisi</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="text" name="latitude" class="form-control" value="<?= $laporan['latitude'] ?>" step="any">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="text" name="longitude" class="form-control" value="<?= $laporan['longitude'] ?>" step="any">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tingkat Keparahan <span class="text-danger">*</span></label>
                                <select name="tingkat_keparahan" class="form-control" required>
                                    <option value="Ringan" <?= $laporan['tingkat_keparahan'] == 'Ringan' ? 'selected' : '' ?>>Ringan</option>
                                    <option value="Sedang" <?= $laporan['tingkat_keparahan'] == 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                                    <option value="Berat" <?= $laporan['tingkat_keparahan'] == 'Berat' ? 'selected' : '' ?>>Berat</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Populasi/Intensitas</label>
                                <input type="number" name="populasi" class="form-control" value="<?= $laporan['populasi'] ?>" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Luas Serangan (Ha)</label>
                                <input type="number" name="luas_serangan" class="form-control" value="<?= $laporan['luas_serangan'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"><?= htmlspecialchars($laporan['catatan'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Foto</label>
                        <?php if(!empty($laporan['foto_url'])): ?>
                        <div class="mb-2">
                            <strong>Foto Saat Ini:</strong><br>
                            <img src="<?= BASE_URL . $laporan['foto_url'] ?>" style="max-width: 300px; max-height: 300px;" class="img-thumbnail mt-2">
                        </div>
                        <?php endif; ?>
                        
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="foto" class="custom-file-input" id="fotoInput" accept="image/jpeg,image/png,image/jpg">
                                <label class="custom-file-label" for="fotoInput">
                                    <?= !empty($laporan['foto_url']) ? 'Ganti foto...' : 'Pilih foto...' ?>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB. <?= !empty($laporan['foto_url']) ? 'Biarkan kosong jika tidak ingin mengganti foto.' : '' ?></small>
                        
                        <div id="fotoPreview" class="mt-2" style="display: none;">
                            <strong>Preview Foto Baru:</strong><br>
                            <img id="previewImg" src="" style="max-width: 300px; max-height: 300px;" class="img-thumbnail mt-2">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearFotoPreview()">
                                <i class="fas fa-times"></i> Batalkan
                            </button>
                        </div>
                    </div>
                    
                    <!-- Status Controls (Role-Based) -->
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <?php 
                        $userRole = $_SESSION['role'] ?? '';
                        $currentStatus = $laporan['status'];
                        $canChangeStatus = ($userRole === 'petugas' && $currentStatus === 'Draf') || 
                                           in_array($userRole, ['admin', 'operator']);
                        ?>
                        
                        <?php if($userRole === 'petugas' && $currentStatus === 'Draf'): ?>
                        <!-- Petugas with Draft: Can save as draft or submit -->
                        <input type="hidden" name="status" id="statusInput" value="<?= $currentStatus ?>">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn <?= $currentStatus === 'Draf' ? 'btn-secondary active' : 'btn-outline-secondary' ?> status-btn" data-status="Draf" id="btnDraf">
                                <i class="fas fa-file"></i> Simpan sebagai Draf
                            </button>
                            <button type="button" class="btn <?= $currentStatus === 'Submitted' ? 'btn-success active' : 'btn-outline-success' ?> status-btn" data-status="Submitted" id="btnSubmit">
                                <i class="fas fa-paper-plane"></i> Submit untuk Verifikasi
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Draf: belum resmi, dapat diedit. Submit: kirim untuk diverifikasi.
                        </small>
                        
                        <?php elseif($userRole === 'petugas' && $currentStatus !== 'Draf'): ?>
                        <!-- Petugas with non-Draft: Read-only status -->
                        <input type="hidden" name="status" value="<?= $currentStatus ?>">
                        <div class="alert alert-info mb-0">
                            <span class="badge badge-lg badge-<?= 
                                $currentStatus == 'Diverifikasi' ? 'success' : 
                                ($currentStatus == 'Submitted' ? 'warning' : 
                                ($currentStatus == 'Ditolak' ? 'danger' : 'secondary'))
                            ?>">
                                <i class="fas fa-<?= 
                                    $currentStatus == 'Diverifikasi' ? 'check-circle' : 
                                    ($currentStatus == 'Submitted' ? 'paper-plane' : 
                                    ($currentStatus == 'Ditolak' ? 'times-circle' : 'file'))
                                ?>"></i>
                                <?= $currentStatus ?>
                            </span>
                            <small class="d-block mt-1">Status tidak dapat diubah karena laporan sudah di-submit.</small>
                        </div>
                        
                        <?php else: ?>
                        <!-- Admin/Operator: Can see status but edit via verification panel on detail page -->
                        <input type="hidden" name="status" value="<?= $currentStatus ?>">
                        <div class="alert alert-info mb-0">
                            <span class="badge badge-lg badge-<?= 
                                $currentStatus == 'Diverifikasi' ? 'success' : 
                                ($currentStatus == 'Submitted' ? 'warning' : 
                                ($currentStatus == 'Ditolak' ? 'danger' : 'secondary'))
                            ?>">
                                <i class="fas fa-<?= 
                                    $currentStatus == 'Diverifikasi' ? 'check-circle' : 
                                    ($currentStatus == 'Submitted' ? 'paper-plane' : 
                                    ($currentStatus == 'Ditolak' ? 'times-circle' : 'file'))
                                ?>"></i>
                                <?= $currentStatus ?>
                            </span>
                            <?php if($currentStatus === 'Submitted'): ?>
                            <small class="d-block mt-1">
                                <a href="<?= BASE_URL ?>laporan/detail/<?= $laporan['id'] ?>" class="text-primary">
                                    <i class="fas fa-check-circle"></i> Verifikasi laporan di halaman detail
                                </a>
                            </small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Laporan
                    </button>
                    <a href="<?= BASE_URL ?>laporan" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File input label update
document.getElementById('fotoInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || '<?= !empty($laporan['foto_url']) ? 'Ganti foto...' : 'Pilih foto...' ?>';
    const label = document.querySelector('.custom-file-label');
    label.textContent = fileName;
    
    // Validate file size (2MB)
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            e.target.value = '';
            label.textContent = '<?= !empty($laporan['foto_url']) ? 'Ganti foto...' : 'Pilih foto...' ?>';
            document.getElementById('fotoPreview').style.display = 'none';
            return;
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
    document.querySelector('.custom-file-label').textContent = '<?= !empty($laporan['foto_url']) ? 'Ganti foto...' : 'Pilih foto...' ?>';
    document.getElementById('fotoPreview').style.display = 'none';
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const file = document.getElementById('fotoInput').files[0];
    if (file && file.size > 2 * 1024 * 1024) {
        e.preventDefault();
        alert('Ukuran file terlalu besar! Maksimal 2MB');
        return false;
    }
    
    // Role-based validation for petugas
    const userRole = '<?= $_SESSION['role'] ?? '' ?>';
    if (userRole === 'petugas') {
        const kabupaten = document.getElementById('kabupatenSelect').value;
        const kecamatan = document.getElementById('kecamatanSelect').value;
        const desa = document.getElementById('desaSelect').value;
        const alamatLengkap = document.querySelector('input[name="alamat_lengkap"]').value.trim();
        
        let errors = [];
        
        if (!kabupaten || kabupaten === '') {
            errors.push('Kabupaten wajib dipilih');
        }
        
        if (!kecamatan || kecamatan === '') {
            errors.push('Kecamatan wajib dipilih');
        }
        
        if (!desa || desa === '') {
            errors.push('Desa wajib dipilih');
        }
        
        if (!alamatLengkap) {
            errors.push('Alamat lengkap wajib diisi');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Validasi gagal:\n\n' + errors.join('\n'));
            return false;
        }
    }
});

// Status button toggle for petugas
const statusButtons = document.querySelectorAll('.status-btn');
const statusInput = document.getElementById('statusInput');
if (statusButtons.length > 0 && statusInput) {
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active from all
            statusButtons.forEach(b => {
                b.classList.remove('active', 'btn-success', 'btn-secondary');
                b.classList.add('btn-outline-secondary');
            });
            // Add active to clicked
            this.classList.remove('btn-outline-secondary', 'btn-outline-success');
            this.classList.add('active');
            if (this.dataset.status === 'Submitted') {
                this.classList.add('btn-success');
            } else {
                this.classList.add('btn-secondary');
            }
            // Update hidden input
            statusInput.value = this.dataset.status;
        });
    });
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
<script>
async function fetchJSON(url){ const r = await fetch(url); return r.json(); }
async function loadKabupaten(selected){
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/kabupaten');
  const sel = document.getElementById('kabupatenSelect');
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_kabupaten; if(String(row.id)===String(selected)) opt.selected=true; sel.appendChild(opt); });
}
async function loadKecamatan(kabupatenId, selected){
  const sel = document.getElementById('kecamatanSelect'); sel.innerHTML = '<option value="">-- Pilih Kecamatan --</option><option value="unknown">Tidak Diketahui</option>';
  const selDesa = document.getElementById('desaSelect'); selDesa.innerHTML = '<option value="">-- Pilih Desa --</option><option value="unknown">Tidak Diketahui</option>';
  if(!kabupatenId || kabupatenId==='unknown') return;
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/kecamatan/'+kabupatenId);
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_kecamatan; if(String(row.id)===String(selected)) opt.selected=true; sel.appendChild(opt); });
}
async function loadDesa(kecamatanId, selected){
  const sel = document.getElementById('desaSelect'); sel.innerHTML = '<option value="">-- Pilih Desa --</option><option value="unknown">Tidak Diketahui</option>';
  if(!kecamatanId || kecamatanId==='unknown') return;
  const data = await fetchJSON('<?= BASE_URL ?>wilayah/desa/'+kecamatanId);
  data.data.forEach(row=>{ const opt=document.createElement('option'); opt.value=row.id; opt.textContent=row.nama_desa; if(String(row.id)===String(selected)) opt.selected=true; sel.appendChild(opt); });
}
document.getElementById('kabupatenSelect').addEventListener('change', e=> loadKecamatan(e.target.value));
document.getElementById('kecamatanSelect').addEventListener('change', e=> loadDesa(e.target.value));
loadKabupaten(<?= json_encode($laporan['kabupaten_id'] ?? '') ?>);
loadKecamatan(<?= json_encode($laporan['kabupaten_id'] ?? '') ?>, <?= json_encode($laporan['kecamatan_id'] ?? '') ?>);
loadDesa(<?= json_encode($laporan['kecamatan_id'] ?? '') ?>, <?= json_encode($laporan['desa_id'] ?? '') ?>);
</script>
