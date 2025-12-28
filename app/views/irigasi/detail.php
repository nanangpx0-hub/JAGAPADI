<?php 
require_once ROOT_PATH . '/app/helpers/ErrorMessage.php';
require_once ROOT_PATH . '/app/views/layouts/header.php'; 

$successMsg = ErrorMessage::flashSuccess();
$errorMsg = ErrorMessage::flash();
?>

<style>
.detail-card {
    border-radius: 10px;
    overflow: hidden;
}
.detail-header {
    background: linear-gradient(135deg, #17a2b8 0%, #3498db 100%);
    color: white;
    padding: 1.5rem;
}
.detail-header .no-laporan {
    font-size: 0.9rem;
    opacity: 0.9;
}
.detail-header .title {
    font-size: 1.5rem;
    font-weight: bold;
}
.info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.info-section-title {
    font-weight: 600;
    color: #17a2b8;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #17a2b8;
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
.photo-container {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.photo-container img {
    max-width: 100%;
    height: auto;
}
</style>

<div class="container-fluid">
    <?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="icon fas fa-check"></i> <?= htmlspecialchars($successMsg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="icon fas fa-ban"></i> <?= htmlspecialchars($errorMsg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php endif; ?>

    <div class="card detail-card">
        <div class="detail-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="no-laporan">
                        <i class="fas fa-hashtag"></i> <?= htmlspecialchars($data['no_laporan'] ?? 'N/A') ?>
                    </div>
                    <div class="title">
                        <i class="fas fa-water"></i> <?= htmlspecialchars($data['nama_saluran'] ?? 'Detail Irigasi') ?>
                    </div>
                    <div class="mt-2">
                        <?php 
                        $statusClass = [
                            'Draf' => 'secondary',
                            'Submitted' => 'primary',
                            'Diverifikasi' => 'success',
                            'Ditolak' => 'danger'
                        ];
                        $cls = $statusClass[$data['status'] ?? 'Draf'] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $cls ?> badge-lg">
                            <i class="fas fa-<?= $data['status'] == 'Diverifikasi' ? 'check-circle' : ($data['status'] == 'Ditolak' ? 'times-circle' : 'clock') ?>"></i>
                            <?= htmlspecialchars($data['status'] ?? 'Draf') ?>
                        </span>
                        <?php 
                        $repairStatusClass = [
                            'Selesai Diperbaiki' => 'success',
                            'Dalam Perbaikan' => 'warning',
                            'Belum Ditangani' => 'danger',
                            'Normal' => 'success'
                        ];
                        $rsCls = $repairStatusClass[$data['status_perbaikan'] ?? 'Normal'] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $rsCls ?>">
                            <?= htmlspecialchars($data['status_perbaikan'] ?? 'Normal') ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <a href="<?= BASE_URL ?>irigasi" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <?php if(in_array($userRole, ['admin', 'operator', 'petugas']) && in_array($data['status'], ['Draf', 'Ditolak'])): ?>
                    <a href="<?= BASE_URL ?>irigasi/edit/<?= $data['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <!-- Informasi Umum -->
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-info-circle"></i> Informasi Umum</h5>
                        <div class="info-row">
                            <div class="info-label">No Laporan</div>
                            <div class="info-value"><code><?= htmlspecialchars($data['no_laporan'] ?? '-') ?></code></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tanggal Laporan</div>
                            <div class="info-value"><?= date('d/m/Y', strtotime($data['tanggal'] ?? 'now')) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Pelapor</div>
                            <div class="info-value">
                                <?= htmlspecialchars($data['nama_pelapor'] ?? $data['pelapor_nama'] ?? '-') ?>
                                <span class="badge badge-secondary"><?= ucfirst($data['pelapor_role'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-map-marker-alt"></i> Lokasi</h5>
                        <div class="info-row">
                            <div class="info-label">Kabupaten</div>
                            <div class="info-value"><?= htmlspecialchars($data['nama_kabupaten'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Kecamatan</div>
                            <div class="info-value"><?= htmlspecialchars($data['nama_kecamatan'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Desa</div>
                            <div class="info-value"><?= htmlspecialchars($data['nama_desa'] ?? '-') ?></div>
                        </div>
                        <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                        <div class="info-row">
                            <div class="info-label">Koordinat</div>
                            <div class="info-value">
                                <a href="https://www.google.com/maps?q=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>" target="_blank">
                                    <?= $data['latitude'] ?>, <?= $data['longitude'] ?>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Detail Saluran -->
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-water"></i> Detail Saluran</h5>
                        <div class="info-row">
                            <div class="info-label">Nama Saluran</div>
                            <div class="info-value"><strong><?= htmlspecialchars($data['nama_saluran'] ?? '-') ?></strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jenis Saluran</div>
                            <div class="info-value"><?= htmlspecialchars($data['jenis_saluran'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jenis Irigasi</div>
                            <div class="info-value"><?= htmlspecialchars($data['jenis_irigasi'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Luas Layanan</div>
                            <div class="info-value"><?= number_format($data['luas_layanan'] ?? 0, 2) ?> Ha</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Kondisi -->
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-heartbeat"></i> Kondisi & Status</h5>
                        <div class="info-row">
                            <div class="info-label">Kondisi Fisik</div>
                            <div class="info-value">
                                <?php 
                                $kondisiClass = $data['kondisi_fisik'] == 'Baik' ? 'success' : 
                                    ($data['kondisi_fisik'] == 'Rusak Ringan' ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?= $kondisiClass ?>"><?= htmlspecialchars($data['kondisi_fisik'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Debit Air</div>
                            <div class="info-value"><?= htmlspecialchars($data['debit_air'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status Perbaikan</div>
                            <div class="info-value">
                                <span class="badge badge-<?= $rsCls ?>"><?= htmlspecialchars($data['status_perbaikan'] ?? '-') ?></span>
                            </div>
                        </div>
                        <?php if (!empty($data['aksi_dilakukan'])): ?>
                        <div class="info-row">
                            <div class="info-label">Aksi Dilakukan</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($data['aksi_dilakukan'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Catatan -->
                    <?php if (!empty($data['catatan'])): ?>
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-sticky-note"></i> Catatan</h5>
                        <p><?= nl2br(htmlspecialchars($data['catatan'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Verifikasi -->
                    <?php if ($data['status'] == 'Diverifikasi' || $data['status'] == 'Ditolak'): ?>
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-clipboard-check"></i> Verifikasi</h5>
                        <div class="info-row">
                            <div class="info-label">Status</div>
                            <div class="info-value"><span class="badge badge-<?= $cls ?>"><?= $data['status'] ?></span></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Verifikator</div>
                            <div class="info-value"><?= htmlspecialchars($data['verifikator_nama'] ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tanggal Verifikasi</div>
                            <div class="info-value"><?= $data['verified_at'] ? date('d/m/Y H:i', strtotime($data['verified_at'])) : '-' ?></div>
                        </div>
                        <?php if (!empty($data['catatan_verifikasi'])): ?>
                        <div class="info-row">
                            <div class="info-label">Catatan Verifikasi</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($data['catatan_verifikasi'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Foto -->
                    <?php if (!empty($data['foto_url'])): ?>
                    <div class="info-section">
                        <h5 class="info-section-title"><i class="fas fa-image"></i> Foto Dokumentasi</h5>
                        <div class="photo-container text-center">
                            <img src="<?= BASE_URL . $data['foto_url'] ?>" alt="Foto Irigasi" class="img-fluid rounded"
                                 onerror="this.style.display='none'; this.parentNode.innerHTML='<p class=\'text-muted\'>Foto tidak tersedia</p>';">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <small class="text-muted">
                <i class="fas fa-clock"></i> Dibuat: <?= date('d/m/Y H:i', strtotime($data['created_at'] ?? 'now')) ?>
                | Diperbarui: <?= date('d/m/Y H:i', strtotime($data['updated_at'] ?? 'now')) ?>
            </small>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>
