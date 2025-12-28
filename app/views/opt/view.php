<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
.detail-card {
    border-radius: 10px;
    overflow: hidden;
}
.detail-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}
.detail-header .kode {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}
.detail-header .nama {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}
.detail-header .nama-ilmiah {
    font-style: italic;
    opacity: 0.9;
}
.detail-photo {
    max-width: 100%;
    max-height: 300px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.info-section-title {
    font-weight: 600;
    color: #28a745;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #28a745;
    font-size: 1rem;
}
.info-row {
    display: flex;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.info-row:last-child {
    border-bottom: none;
}
.info-label {
    flex: 0 0 40%;
    font-weight: 500;
    color: #495057;
}
.info-value {
    flex: 1;
    color: #212529;
}
.taxonomy-tree {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.taxonomy-item {
    background: #e9ecef;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
}
.taxonomy-arrow {
    color: #6c757d;
}
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card detail-card">
            <!-- Header with photo and basic info -->
            <div class="detail-header">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <?php 
                        $photoPath = $opt['foto_url'] ?? $opt['gambar'] ?? null;
                        if (!empty($photoPath)):
                            $photoUrl = $photoPath;
                            if (strpos($photoUrl, 'http') !== 0) {
                                $photoUrl = ltrim($photoUrl, '/');
                                if (strpos($photoUrl, 'public/') !== 0) {
                                    $photoUrl = 'public/' . $photoUrl;
                                }
                            }
                        ?>
                        <img src="<?= BASE_URL . $photoUrl ?>" class="detail-photo" alt="<?= htmlspecialchars($opt['nama_opt']) ?>"
                             onerror="this.src='<?= BASE_URL ?>public/img/no-image.png'">
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-image fa-5x opacity-50"></i>
                            <p class="mt-2 opacity-75">Tidak ada gambar</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8 text-md-left text-center">
                        <div class="kode"><i class="fas fa-barcode"></i> <?= htmlspecialchars($opt['kode_opt'] ?? '') ?></div>
                        <div class="nama"><?= htmlspecialchars($opt['nama_opt'] ?? '') ?></div>
                        <?php if (!empty($opt['nama_ilmiah'])): ?>
                        <div class="nama-ilmiah"><?= htmlspecialchars($opt['nama_ilmiah']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($opt['nama_lokal'])): ?>
                        <div class="mt-1"><small>Nama lokal: <?= htmlspecialchars($opt['nama_lokal']) ?></small></div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <?php 
                            $jenisBadge = $opt['jenis'] == 'Hama' ? 'danger' : ($opt['jenis'] == 'Penyakit' ? 'warning' : 'info');
                            ?>
                            <span class="badge badge-<?= $jenisBadge ?> badge-lg mr-2">
                                <i class="fas fa-<?= $opt['jenis'] == 'Hama' ? 'spider' : ($opt['jenis'] == 'Penyakit' ? 'virus' : 'seedling') ?>"></i>
                                <?= htmlspecialchars($opt['jenis'] ?? '') ?>
                            </span>
                            
                            <?php 
                            $karantina = $opt['status_karantina'] ?? 'Tidak';
                            if ($karantina != 'Tidak'):
                            ?>
                            <span class="badge badge-danger badge-lg mr-2">
                                <i class="fas fa-shield-alt"></i> <?= htmlspecialchars($karantina) ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php 
                            $bahaya = $opt['tingkat_bahaya'] ?? 'Sedang';
                            $bahayaColors = [
                                'Rendah' => 'success',
                                'Sedang' => 'warning',
                                'Tinggi' => 'orange',
                                'Sangat Tinggi' => 'danger'
                            ];
                            $bahayaClass = $bahayaColors[$bahaya] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?= $bahayaClass ?> badge-lg">
                                <i class="fas fa-exclamation-triangle"></i> Bahaya: <?= htmlspecialchars($bahaya) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Classification -->
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-sitemap"></i> Klasifikasi Taksonomi</h5>
                            
                            <?php
                            $taxonomy = [
                                'Kingdom' => $opt['kingdom'] ?? '-',
                                'Filum' => $opt['filum'] ?? '-',
                                'Kelas' => $opt['kelas'] ?? '-',
                                'Ordo' => $opt['ordo'] ?? '-',
                                'Famili' => $opt['famili'] ?? '-',
                                'Genus' => $opt['genus'] ?? '-'
                            ];
                            
                            // Display as tree
                            $taxonomyItems = array_filter($taxonomy, fn($v) => $v !== '-' && !empty($v));
                            if (!empty($taxonomyItems)):
                            ?>
                            <div class="taxonomy-tree mb-3">
                                <?php $i = 0; foreach ($taxonomyItems as $level => $value): ?>
                                <?php if ($i > 0): ?>
                                <span class="taxonomy-arrow"><i class="fas fa-chevron-right"></i></span>
                                <?php endif; ?>
                                <span class="taxonomy-item" title="<?= $level ?>">
                                    <?= htmlspecialchars($value) ?>
                                </span>
                                <?php $i++; endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Full list -->
                            <?php foreach ($taxonomy as $level => $value): ?>
                            <div class="info-row">
                                <div class="info-label"><?= $level ?></div>
                                <div class="info-value"><?= htmlspecialchars($value) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Status -->
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-info-circle"></i> Status & Informasi</h5>
                            <div class="info-row">
                                <div class="info-label">Status Karantina</div>
                                <div class="info-value">
                                    <?php 
                                    $karantina = $opt['status_karantina'] ?? 'Tidak';
                                    $karantinaExplain = [
                                        'Tidak' => 'Bukan OPTK',
                                        'OPTK A1' => 'Belum ada di Indonesia',
                                        'OPTK A2' => 'Terbatas penyebarannya',
                                        'OPTK B' => 'Sudah tersebar luas'
                                    ];
                                    ?>
                                    <strong><?= htmlspecialchars($karantina) ?></strong>
                                    <small class="text-muted d-block"><?= $karantinaExplain[$karantina] ?? '' ?></small>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Tingkat Bahaya</div>
                                <div class="info-value">
                                    <span class="badge badge-<?= $bahayaClass ?>"><?= htmlspecialchars($bahaya) ?></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">ETL Acuan</div>
                                <div class="info-value">
                                    <?= $opt['etl_acuan'] ?? 0 ?> ekor/rumpun
                                    <?php if (($opt['etl_acuan'] ?? 0) == 0): ?>
                                    <small class="text-muted">(Tidak berlaku)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Description -->
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-file-alt"></i> Deskripsi</h5>
                            <p><?= nl2br(htmlspecialchars($opt['deskripsi'] ?? 'Tidak ada deskripsi')) ?></p>
                        </div>
                        
                        <!-- Recommendation -->
                        <?php if (!empty($opt['rekomendasi'])): ?>
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-lightbulb"></i> Rekomendasi Pengendalian</h5>
                            <p><?= nl2br(htmlspecialchars($opt['rekomendasi'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Reference -->
                        <?php if (!empty($opt['referensi'])): ?>
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-book"></i> Referensi</h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($opt['referensi'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Timestamps -->
                        <div class="info-section">
                            <h5 class="info-section-title"><i class="fas fa-clock"></i> Metadata</h5>
                            <div class="info-row">
                                <div class="info-label">Dibuat</div>
                                <div class="info-value"><?= date('d/m/Y H:i', strtotime($opt['created_at'] ?? 'now')) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Diperbarui</div>
                                <div class="info-value"><?= date('d/m/Y H:i', strtotime($opt['updated_at'] ?? 'now')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <a href="<?= BASE_URL ?>opt" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
                <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                <a href="<?= BASE_URL ?>opt/edit/<?= $opt['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <?php endif; ?>
                <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                <a href="<?= BASE_URL ?>opt/delete/<?= $opt['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('Yakin ingin menghapus data OPT ini?')">
                    <i class="fas fa-trash"></i> Hapus
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
