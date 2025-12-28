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
    border: 2px dashed #dee2e6;
    display: none;
}
.photo-preview.has-image {
    display: block;
    border-style: solid;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle"></i> Tambah Data OPT
                </h3>
                <div class="card-tools">
                    <a href="<?= BASE_URL ?>opt" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <form action="<?= BASE_URL ?>opt/create" method="POST" enctype="multipart/form-data" id="formOpt">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="foto_url" id="foto_url" value="<?= htmlspecialchars($form_data['foto_url'] ?? '') ?>">
                
                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="form-section-title"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Kode OPT</label>
                                    <input type="text" name="kode_opt" class="form-control" required
                                           value="<?= htmlspecialchars($form_data['kode_opt'] ?? '') ?>"
                                           placeholder="Contoh: H001, P002, G001">
                                    <small class="text-muted">Format: H=Hama, P=Penyakit, G=Gulma + nomor urut</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Nama OPT</label>
                                    <input type="text" name="nama_opt" class="form-control" required
                                           value="<?= htmlspecialchars($form_data['nama_opt'] ?? '') ?>"
                                           placeholder="Nama umum OPT">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Jenis OPT</label>
                                    <select name="jenis" class="form-control" required id="jenisOpt">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="Hama" <?= ($form_data['jenis'] ?? '') == 'Hama' ? 'selected' : '' ?>>Hama</option>
                                        <option value="Penyakit" <?= ($form_data['jenis'] ?? '') == 'Penyakit' ? 'selected' : '' ?>>Penyakit</option>
                                        <option value="Gulma" <?= ($form_data['jenis'] ?? '') == 'Gulma' ? 'selected' : '' ?>>Gulma</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Ilmiah</label>
                                    <input type="text" name="nama_ilmiah" class="form-control"
                                           value="<?= htmlspecialchars($form_data['nama_ilmiah'] ?? '') ?>"
                                           placeholder="Nama latin (italic)">
                                    <small class="text-muted">Contoh: Nilaparvata lugens, Pyricularia oryzae</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lokal/Umum</label>
                                    <input type="text" name="nama_lokal" class="form-control"
                                           value="<?= htmlspecialchars($form_data['nama_lokal'] ?? '') ?>"
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
                                        <option value="Animalia" <?= ($form_data['kingdom'] ?? '') == 'Animalia' ? 'selected' : '' ?>>Animalia (Hewan)</option>
                                        <option value="Fungi" <?= ($form_data['kingdom'] ?? '') == 'Fungi' ? 'selected' : '' ?>>Fungi (Jamur)</option>
                                        <option value="Plantae" <?= ($form_data['kingdom'] ?? '') == 'Plantae' ? 'selected' : '' ?>>Plantae (Tumbuhan)</option>
                                        <option value="Bacteria" <?= ($form_data['kingdom'] ?? '') == 'Bacteria' ? 'selected' : '' ?>>Bacteria</option>
                                        <option value="Chromista" <?= ($form_data['kingdom'] ?? '') == 'Chromista' ? 'selected' : '' ?>>Chromista</option>
                                        <option value="Virus" <?= ($form_data['kingdom'] ?? '') == 'Virus' ? 'selected' : '' ?>>Virus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filum/Divisi</label>
                                    <input type="text" name="filum" class="form-control"
                                           value="<?= htmlspecialchars($form_data['filum'] ?? '') ?>"
                                           placeholder="Contoh: Arthropoda, Ascomycota">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kelas</label>
                                    <input type="text" name="kelas" class="form-control"
                                           value="<?= htmlspecialchars($form_data['kelas'] ?? '') ?>"
                                           placeholder="Contoh: Insecta, Sordariomycetes">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ordo</label>
                                    <input type="text" name="ordo" class="form-control"
                                           value="<?= htmlspecialchars($form_data['ordo'] ?? '') ?>"
                                           placeholder="Contoh: Hemiptera, Magnaporthales">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Famili</label>
                                    <input type="text" name="famili" class="form-control"
                                           value="<?= htmlspecialchars($form_data['famili'] ?? '') ?>"
                                           placeholder="Contoh: Delphacidae, Pyriculariaceae">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Genus</label>
                                    <input type="text" name="genus" class="form-control"
                                           value="<?= htmlspecialchars($form_data['genus'] ?? '') ?>"
                                           placeholder="Contoh: Nilaparvata, Pyricularia">
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
                                        <option value="Tidak" <?= ($form_data['status_karantina'] ?? '') == 'Tidak' ? 'selected' : '' ?>>Tidak (Bukan OPTK)</option>
                                        <option value="OPTK A1" <?= ($form_data['status_karantina'] ?? '') == 'OPTK A1' ? 'selected' : '' ?>>OPTK A1 - Belum ada di Indonesia</option>
                                        <option value="OPTK A2" <?= ($form_data['status_karantina'] ?? '') == 'OPTK A2' ? 'selected' : '' ?>>OPTK A2 - Terbatas penyebarannya</option>
                                        <option value="OPTK B" <?= ($form_data['status_karantina'] ?? '') == 'OPTK B' ? 'selected' : '' ?>>OPTK B - Sudah tersebar luas</option>
                                    </select>
                                    <small class="text-muted">OPTK = Organisme Pengganggu Tumbuhan Karantina</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tingkat Bahaya</label>
                                    <select name="tingkat_bahaya" class="form-control">
                                        <option value="Rendah" <?= ($form_data['tingkat_bahaya'] ?? '') == 'Rendah' ? 'selected' : '' ?>>🟢 Rendah</option>
                                        <option value="Sedang" <?= ($form_data['tingkat_bahaya'] ?? 'Sedang') == 'Sedang' ? 'selected' : '' ?>>🟡 Sedang</option>
                                        <option value="Tinggi" <?= ($form_data['tingkat_bahaya'] ?? '') == 'Tinggi' ? 'selected' : '' ?>>🟠 Tinggi</option>
                                        <option value="Sangat Tinggi" <?= ($form_data['tingkat_bahaya'] ?? '') == 'Sangat Tinggi' ? 'selected' : '' ?>>🔴 Sangat Tinggi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ETL Acuan (per rumpun)</label>
                                    <input type="number" name="etl_acuan" class="form-control" min="0"
                                           value="<?= htmlspecialchars($form_data['etl_acuan'] ?? '0') ?>"
                                           placeholder="Economic Threshold Level">
                                    <small class="text-muted">0 untuk penyakit/gulma</small>
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
                                    <textarea name="deskripsi" class="form-control" rows="4"
                                              placeholder="Deskripsi singkat tentang OPT"><?= htmlspecialchars($form_data['deskripsi'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Rekomendasi Pengendalian</label>
                                    <textarea name="rekomendasi" class="form-control" rows="3"
                                              placeholder="Cara pengendalian yang direkomendasikan"><?= htmlspecialchars($form_data['rekomendasi'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gambar/Foto OPT</label>
                                    <div class="custom-file mb-2">
                                        <input type="file" class="custom-file-input" id="gambar" name="gambar" accept="image/*">
                                        <label class="custom-file-label" for="gambar">Pilih file...</label>
                                    </div>
                                    <small class="text-muted">Format: JPG, PNG, GIF. Max: 2MB</small>
                                    <div class="mt-2">
                                        <img id="photoPreview" class="photo-preview img-thumbnail" src="" alt="Preview">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Referensi/Sumber Data</label>
                                    <textarea name="referensi" class="form-control" rows="2"
                                              placeholder="Sumber referensi data OPT (jurnal, buku, website)"><?= htmlspecialchars($form_data['referensi'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Data OPT
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
    // Auto-select kingdom based on jenis
    const jenisSelect = document.getElementById('jenisOpt');
    const kingdomSelect = document.getElementById('kingdomSelect');
    
    jenisSelect.addEventListener('change', function() {
        const jenis = this.value;
        if (jenis === 'Hama') {
            kingdomSelect.value = 'Animalia';
        } else if (jenis === 'Penyakit') {
            kingdomSelect.value = 'Fungi';
        } else if (jenis === 'Gulma') {
            kingdomSelect.value = 'Plantae';
        }
    });
    
    // File input label update
    document.getElementById('gambar').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file...';
        this.nextElementSibling.textContent = fileName;
        
        // Preview image
        if (this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.src = e.target.result;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>