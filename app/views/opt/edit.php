<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
.form-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.form-section-title {
    font-weight: 600;
    color: #28a745;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #28a745;
}
.required::after {
    content: ' *';
    color: #dc3545;
}
.photo-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}
.current-photo {
    position: relative;
    display: inline-block;
}
.current-photo .badge {
    position: absolute;
    top: 5px;
    left: 5px;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Edit Data OPT
                </h3>
                <div class="card-tools">
                    <a href="<?= BASE_URL ?>opt" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <form action="<?= BASE_URL ?>opt/edit/<?= $opt['id'] ?>" method="POST" enctype="multipart/form-data" id="formOpt">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="foto_url" id="foto_url" value="">
                
                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="form-section-title"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Kode OPT</label>
                                    <input type="text" name="kode_opt" class="form-control" required
                                           value="<?= htmlspecialchars($opt['kode_opt'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Nama OPT</label>
                                    <input type="text" name="nama_opt" class="form-control" required
                                           value="<?= htmlspecialchars($opt['nama_opt'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Jenis OPT</label>
                                    <select name="jenis" class="form-control" required id="jenisOpt">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="Hama" <?= ($opt['jenis'] ?? '') == 'Hama' ? 'selected' : '' ?>>Hama</option>
                                        <option value="Penyakit" <?= ($opt['jenis'] ?? '') == 'Penyakit' ? 'selected' : '' ?>>Penyakit</option>
                                        <option value="Gulma" <?= ($opt['jenis'] ?? '') == 'Gulma' ? 'selected' : '' ?>>Gulma</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Ilmiah</label>
                                    <input type="text" name="nama_ilmiah" class="form-control"
                                           value="<?= htmlspecialchars($opt['nama_ilmiah'] ?? '') ?>"
                                           placeholder="Nama latin (italic)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lokal/Umum</label>
                                    <input type="text" name="nama_lokal" class="form-control"
                                           value="<?= htmlspecialchars($opt['nama_lokal'] ?? '') ?>"
                                           placeholder="Nama daerah atau nama lain">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classification -->
                    <div class="form-section">
                        <h5 class="form-section-title"><i class="fas fa-sitemap"></i> Klasifikasi Taksonomi</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kingdom</label>
                                    <select name="kingdom" class="form-control" id="kingdomSelect">
                                        <option value="">-- Pilih Kingdom --</option>
                                        <option value="Animalia" <?= ($opt['kingdom'] ?? '') == 'Animalia' ? 'selected' : '' ?>>Animalia (Hewan)</option>
                                        <option value="Fungi" <?= ($opt['kingdom'] ?? '') == 'Fungi' ? 'selected' : '' ?>>Fungi (Jamur)</option>
                                        <option value="Plantae" <?= ($opt['kingdom'] ?? '') == 'Plantae' ? 'selected' : '' ?>>Plantae (Tumbuhan)</option>
                                        <option value="Bacteria" <?= ($opt['kingdom'] ?? '') == 'Bacteria' ? 'selected' : '' ?>>Bacteria</option>
                                        <option value="Chromista" <?= ($opt['kingdom'] ?? '') == 'Chromista' ? 'selected' : '' ?>>Chromista</option>
                                        <option value="Virus" <?= ($opt['kingdom'] ?? '') == 'Virus' ? 'selected' : '' ?>>Virus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filum/Divisi</label>
                                    <input type="text" name="filum" class="form-control"
                                           value="<?= htmlspecialchars($opt['filum'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kelas</label>
                                    <input type="text" name="kelas" class="form-control"
                                           value="<?= htmlspecialchars($opt['kelas'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ordo</label>
                                    <input type="text" name="ordo" class="form-control"
                                           value="<?= htmlspecialchars($opt['ordo'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Famili</label>
                                    <input type="text" name="famili" class="form-control"
                                           value="<?= htmlspecialchars($opt['famili'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Genus</label>
                                    <input type="text" name="genus" class="form-control"
                                           value="<?= htmlspecialchars($opt['genus'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Danger -->
                    <div class="form-section">
                        <h5 class="form-section-title"><i class="fas fa-exclamation-triangle"></i> Status & Tingkat Bahaya</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Karantina</label>
                                    <select name="status_karantina" class="form-control">
                                        <option value="Tidak" <?= ($opt['status_karantina'] ?? '') == 'Tidak' ? 'selected' : '' ?>>Tidak (Bukan OPTK)</option>
                                        <option value="OPTK A1" <?= ($opt['status_karantina'] ?? '') == 'OPTK A1' ? 'selected' : '' ?>>OPTK A1 - Belum ada di Indonesia</option>
                                        <option value="OPTK A2" <?= ($opt['status_karantina'] ?? '') == 'OPTK A2' ? 'selected' : '' ?>>OPTK A2 - Terbatas penyebarannya</option>
                                        <option value="OPTK B" <?= ($opt['status_karantina'] ?? '') == 'OPTK B' ? 'selected' : '' ?>>OPTK B - Sudah tersebar luas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tingkat Bahaya</label>
                                    <select name="tingkat_bahaya" class="form-control">
                                        <option value="Rendah" <?= ($opt['tingkat_bahaya'] ?? '') == 'Rendah' ? 'selected' : '' ?>>🟢 Rendah</option>
                                        <option value="Sedang" <?= ($opt['tingkat_bahaya'] ?? 'Sedang') == 'Sedang' ? 'selected' : '' ?>>🟡 Sedang</option>
                                        <option value="Tinggi" <?= ($opt['tingkat_bahaya'] ?? '') == 'Tinggi' ? 'selected' : '' ?>>🟠 Tinggi</option>
                                        <option value="Sangat Tinggi" <?= ($opt['tingkat_bahaya'] ?? '') == 'Sangat Tinggi' ? 'selected' : '' ?>>🔴 Sangat Tinggi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ETL Acuan (per rumpun)</label>
                                    <input type="number" name="etl_acuan" class="form-control" min="0"
                                           value="<?= htmlspecialchars($opt['etl_acuan'] ?? '0') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description & Photo -->
                    <div class="form-section">
                        <h5 class="form-section-title"><i class="fas fa-file-alt"></i> Deskripsi & Gambar</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($opt['deskripsi'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Rekomendasi Pengendalian</label>
                                    <textarea name="rekomendasi" class="form-control" rows="3"><?= htmlspecialchars($opt['rekomendasi'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gambar/Foto OPT</label>
                                    <?php 
                                    $photoPath = $opt['foto_url'] ?? $opt['gambar'] ?? '';
                                    if (!empty($photoPath)):
                                        $photoUrl = $photoPath;
                                        if (strpos($photoUrl, 'http') !== 0) {
                                            $photoUrl = ltrim($photoUrl, '/');
                                            if (strpos($photoUrl, 'public/') !== 0) {
                                                $photoUrl = 'public/' . $photoUrl;
                                            }
                                        }
                                    ?>
                                    <div class="current-photo mb-2">
                                        <span class="badge badge-info">Foto Saat Ini</span>
                                        <img src="<?= BASE_URL . $photoUrl ?>" class="photo-preview d-block" alt="Current Photo"
                                             onerror="this.style.display='none'">
                                    </div>
                                    <?php endif; ?>
                                    <div class="custom-file mb-2">
                                        <input type="file" class="custom-file-input" id="gambar" name="gambar" accept="image/*">
                                        <label class="custom-file-label" for="gambar">Pilih file baru...</label>
                                    </div>
                                    <small class="text-muted">Biarkan kosong jika tidak ingin mengganti foto</small>
                                    <div class="mt-2">
                                        <img id="photoPreview" class="photo-preview d-none" src="" alt="Preview">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Referensi/Sumber Data</label>
                                    <textarea name="referensi" class="form-control" rows="2"><?= htmlspecialchars($opt['referensi'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Data OPT
                    </button>
                    <a href="<?= BASE_URL ?>opt" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input label update
    document.getElementById('gambar').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file baru...';
        this.nextElementSibling.textContent = fileName;
        
        // Preview image
        if (this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
