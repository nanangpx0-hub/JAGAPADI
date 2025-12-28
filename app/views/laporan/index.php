<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* ===== MASTER CHECKBOX STYLING ===== */
#checkAll {
    cursor: pointer;
    width: 20px;
    height: 20px;
    margin: 0;
    vertical-align: middle;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border: 2px solid #6c757d;
    border-radius: 4px;
    background-color: #fff;
    position: relative;
    outline: none;
}

/* Master checkbox - Checked state (all selected) */
#checkAll:checked {
    background-color: #007bff;
    border-color: #007bff;
}

#checkAll:checked::after {
    content: '';
    position: absolute;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Master checkbox - Indeterminate state (partial selection) */
#checkAll:indeterminate {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

#checkAll:indeterminate::after {
    content: '';
    position: absolute;
    left: 3px;
    top: 7px;
    width: 10px;
    height: 2px;
    background-color: white;
    transform: none;
}

/* Master checkbox - Hover effect DISABLED */
#checkAll:hover {
    /* border-color: #007bff; */
    /* box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25); */
}

#checkAll:focus {
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.35);
}

/* ===== CHILD CHECKBOX STYLING ===== */
.checkbox-item {
    cursor: pointer;
    width: 18px;
    height: 18px;
    margin: 0;
    vertical-align: middle;
}

/* ===== BULK DELETE BUTTON ===== */
#btnBulkDelete {
    display: none;
}

#btnBulkDelete.show {
    display: inline-block;
}

#btnBulkDelete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ===== ROW SELECTION STYLING ===== */
.row-selected {
    background-color: #fff3cd !important;
    border-left: 3px solid #ffc107;
}

/* Row hover effect DISABLED */
tbody tr:hover {
    /* background-color: #f8f9fa !important; */
}

/* Loading state for bulk operations */
.bulk-loading {
    position: relative;
    overflow: hidden;
}

.bulk-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Highlight rejected reports for petugas */
.rejected-report-row {
    background-color: #f8d7da !important;
    border-left: 4px solid #dc3545;
}

.rejected-report-row:hover {
    /* background-color: #f5c6cb !important; */
}

/* Rejected report actions styling */
.rejected-actions {
    background-color: #fff3cd;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ffeaa7;
}

.rejected-actions .btn {
    margin: 2px;
}

/* Mobile-specific rejected actions */
.rejected-actions-mobile {
    background-color: #fff3cd;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ffeaa7;
    margin-top: 0.5rem;
    text-align: center;
}

.rejected-actions-mobile .btn {
    margin: 2px;
    min-width: 80px;
}

/* Extra small buttons for rejected reports */
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
    line-height: 1.2;
    border-radius: 3px;
}

/* ===== FOTO THUMBNAIL STYLING ===== */
.photo-thumbnail-container {
    position: relative;
    display: inline-block;
    width: 100px;
    height: 100px;
}

.photo-thumbnail {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    border: 2px solid #dee2e6;
    cursor: pointer;
    cursor: pointer;
    background-color: #f8f9fa;
    display: block;
}

.photo-thumbnail:hover {
    /* border-color: #007bff; */
    /* box-shadow: 0 4px 8px rgba(0,0,0,0.15); */
}

.photo-thumbnail.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    color: #6c757d;
    font-size: 24px;
}

/* Photo Preview Modal */
.photo-preview-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    cursor: pointer;
    cursor: pointer;
}

    display: flex;
    align-items: center;
    justify-content: center;

.photo-preview-image {
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);

.photo-preview-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    font-weight: bold;
    cursor: pointer;
    z-index: 10000;
}

.photo-preview-close:hover {
    /* color: #ff6b6b; */
}

/* ===== TOMBOL AKSI STYLING ===== */
.btn-action-group {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-action {
    width: 32px;
    height: 32px;
    padding: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid transparent;
    font-size: 14px;
    line-height: 1;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    background-color: transparent;
    position: relative;
}

.btn-action i {
    font-size: 16px;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Enhanced hover effects for action buttons */
.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Specific button styles with improved colors */
.btn-action-info {
    color: #17a2b8;
    background-color: rgba(23, 162, 184, 0.1);
}

.btn-action-info:hover {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-action-success {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
}

.btn-action-success:hover {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
}

.btn-action-warning {
    color: #ffc107;
    background-color: rgba(255, 193, 7, 0.1);
}

.btn-action-warning:hover {
    color: #212529;
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-action-danger {
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

.btn-action-danger:hover {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Focus state for accessibility */
.btn-action:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Active state */
.btn-action:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-action:hover {
    /* box-shadow: 0 2px 4px rgba(0,0,0,0.2); */
}



.btn-action:disabled,
.btn-action.disabled {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
}

.btn-action:disabled:hover,
.btn-action.disabled:hover {
    box-shadow: none;
}

.btn-action-info {
    background-color: #17a2b8;
    color: #fff;
}

.btn-action-info:hover {
    /* background-color: #138496; */
    color: #fff;
}

.btn-action-success {
    background-color: #28a745;
    color: #fff;
}

.btn-action-success:hover {
    /* background-color: #218838; */
    color: #fff;
}

.btn-action-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-action-warning:hover {
    /* background-color: #e0a800; */
    color: #212529;
}

.btn-action-danger {
    background-color: #dc3545;
    color: #fff;
}

.btn-action-danger:hover {
    /* background-color: #c82333; */
    color: #fff;
}

/* Tooltip styling */
.btn-action {
    position: relative;
}

/* Enhanced tooltip with better styling */
.btn-action::after {
    content: attr(title);
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(4px);
    background-color: #2c3e50;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    border: 1px solid rgba(255,255,255,0.1);
}

.btn-action:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.btn-action::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 4px);
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #2c3e50;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
}

.btn-action:hover::before {
    opacity: 1;
    visibility: visible;
}


/* Loading optimization */
.photo-thumbnail {
    loading: lazy;
}

/* Mobile button group improvements */
@media (max-width: 575.98px) {
    .btn-group-horizontal-mobile {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        justify-content: center;
        align-items: center;
    }
    
    .btn-group-horizontal-mobile .btn {
        flex: 0 0 auto;
        min-width: 44px;
        min-height: 44px;
        margin: 0;
    }
    
    .rejected-actions-mobile {
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .rejected-actions-mobile .btn {
        min-width: 44px;
        min-height: 44px;
        margin: 0.125rem;
    }
    
    /* Table cell content optimization for mobile */
    .table-responsive td[data-label="Lokasi"] div {
        margin-bottom: 0.125rem;
    }
    
    .table-responsive td[data-label="Status"] .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
    }
    
    .table-responsive td[data-label="Pelapor"] div {
        margin-bottom: 0.125rem;
    }
    
    /* Photo thumbnail responsive */
    .photo-thumbnail-container {
        width: 80px;
        height: 80px;
    }
    
    .photo-thumbnail {
        width: 80px;
        height: 80px;
    }
    
    /* Action buttons responsive - optimized for mobile */
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 8px;
        font-size: 16px;
    }
    
    .btn-action i {
        font-size: 18px;
        width: 24px;
        height: 24px;
    }
    
    /* Mobile tooltip positioning */
    .btn-action::after {
        bottom: calc(100% + 12px);
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .btn-action::before {
        bottom: calc(100% + 4px);
        border-width: 4px;
    }
}

/* Responsive table adjustments */
@media (min-width: 576px) and (max-width: 991px) {
    /* Tablet adjustments */
    .btn-action {
        width: 34px;
        height: 34px;
        padding: 7px;
        font-size: 15px;
    }
    
    .btn-action i {
        font-size: 17px;
        width: 22px;
        height: 22px;
    }
    
    .photo-thumbnail-container {
        width: 75px;
        height: 75px;
    }
    
    .photo-thumbnail {
        width: 75px;
        height: 75px;
    }
}

@media (max-width: 768px) {
    .photo-thumbnail-container {
        width: 70px;
        height: 70px;
    }
    
    .photo-thumbnail {
        width: 70px;
        height: 70px;
    }
}

@media (min-width: 992px) {
    /* Desktop adjustments */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 6px;
        font-size: 14px;
    }
    
    .btn-action i {
        font-size: 16px;
        width: 20px;
        height: 20px;
    }
    
    .photo-thumbnail-container {
        width: 100px;
        height: 100px;
    }
    
    .photo-thumbnail {
        width: 100px;
        height: 100px;
    }
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Daftar Laporan Hama & Penyakit</h3>
                <div class="card-tools">
                    <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                    <button id="btnBulkDelete" class="btn btn-danger btn-sm mr-2">
                        <i class="fas fa-trash"></i> Hapus Data Terpilih (<span id="selectedCount">0</span>)
                    </button>
                    <?php endif; ?>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator', 'petugas'])): ?>
                    <a href="<?= BASE_URL ?>laporan/create" 
                       id="btnCreateLaporan" 
                       class="btn btn-success btn-sm" 
                       role="button"
                       style="text-decoration: none !important; pointer-events: auto !important; cursor: pointer !important; display: inline-block !important;">
                        <i class="fas fa-plus"></i> Buat Laporan Baru
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- User Info Badge (for petugas) -->
                <?php if(($_SESSION['role'] ?? '') === 'petugas'): ?>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Mode Petugas:</strong> Anda hanya dapat melihat laporan yang Anda buat sendiri.
                    <span class="badge badge-primary ml-2">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($currentUser['nama_lengkap'] ?? '') ?>
                    </span>
                </div>
                
                <!-- Rejected Reports Alert (for petugas) -->
                <?php if (($rejectedCount ?? 0) > 0): ?>
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian:</strong> Anda memiliki <strong><?= $rejectedCount ?></strong> laporan yang ditolak dan perlu diperbaiki.
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Laporan yang ditolak dapat Anda edit untuk memperbaiki data, atau hapus jika tidak diperlukan lagi.
                        <?php if ($status !== 'Ditolak'): ?>
                        <a href="<?= BASE_URL ?>laporan?status=Ditolak" class="alert-link">Lihat laporan yang ditolak</a>
                        <?php endif; ?>
                    </small>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Filter Status dengan Efek Timbul-Tenggelam -->
                <div class="filter-status-container mb-3">
                    <div class="btn-group-status" role="group" aria-label="Filter Status">
                        <?php
                        // Hitung jumlah per status
                        $countAll = count($laporan);
                        $countDraft = 0;
                        $countSubmitted = 0;
                        $countVerified = 0;
                        $countRejected = 0;
                        
                        foreach ($laporan as $item) {
                            switch ($item['status']) {
                                case 'Draf':
                                    $countDraft++;
                                    break;
                                case 'Submitted':
                                    $countSubmitted++;
                                    break;
                                case 'Diverifikasi':
                                    $countVerified++;
                                    break;
                                case 'Ditolak':
                                    $countRejected++;
                                    break;
                            }
                        }
                        ?>
                        
                        <a href="<?= BASE_URL ?>laporan" 
                           class="btn-filter <?= empty($status) ? 'active' : '' ?>" 
                           data-filter="semua"
                           aria-pressed="<?= empty($status) ? 'true' : 'false' ?>">
                            <i class="fas fa-list"></i> Semua
                            <span class="badge badge-secondary"><?= $countAll ?></span>
                        </a>

                        <a href="<?= BASE_URL ?>laporan?status=Draf" 
                           class="btn-filter <?= ($status === 'Draf') ? 'active' : '' ?>" 
                           data-filter="draft"
                           aria-pressed="<?= ($status === 'Draf') ? 'true' : 'false' ?>">
                            <i class="fas fa-file"></i> Draf
                            <span class="badge badge-warning"><?= $countDraft ?></span>
                        </a>

                        <a href="<?= BASE_URL ?>laporan?status=Submitted" 
                           class="btn-filter <?= ($status === 'Submitted') ? 'active' : '' ?>" 
                           data-filter="submitted"
                           aria-pressed="<?= ($status === 'Submitted') ? 'true' : 'false' ?>">
                            <i class="fas fa-paper-plane"></i> Submitted
                            <span class="badge badge-info"><?= $countSubmitted ?></span>
                        </a>

                        <a href="<?= BASE_URL ?>laporan?status=Diverifikasi" 
                           class="btn-filter <?= ($status === 'Diverifikasi') ? 'active' : '' ?>" 
                           data-filter="diverifikasi"
                           aria-pressed="<?= ($status === 'Diverifikasi') ? 'true' : 'false' ?>">
                            <i class="fas fa-check-circle"></i> Diverifikasi
                            <span class="badge badge-success"><?= $countVerified ?></span>
                        </a>

                        <a href="<?= BASE_URL ?>laporan?status=Ditolak" 
                           class="btn-filter <?= ($status === 'Ditolak') ? 'active' : '' ?>" 
                           data-filter="ditolak"
                           aria-pressed="<?= ($status === 'Ditolak') ? 'true' : 'false' ?>">
                            <i class="fas fa-times-circle"></i> Ditolak
                            <span class="badge badge-danger"><?= $countRejected ?></span>
                        </a>
                    </div>
                </div>

                <!-- Table Scroll Hint for Mobile -->
                <div class="table-scroll-hint d-md-none">
                    <i class="fas fa-arrows-alt-h"></i>
                    <span>Geser ke kiri/kanan untuk melihat semua kolom</span>
                </div>
                
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="laporanTable">
                        <thead>
                            <tr>
                                <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                                <th width="40">
                                    <input type="checkbox" id="checkAll" title="Pilih Semua">
                                </th>
                                <?php endif; ?>
                                <th width="50">No</th>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Tanggal</th>
                                <th>OPT</th>
                                <th>Lokasi</th>
                                <th>Keparahan</th>
                                <th>Populasi</th>
                                <th>Status</th>
                                <th>Pelapor</th>
                                <th>Dibuat</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($laporan)): ?>
                            <tr>
                                <td colspan="<?= ($_SESSION['role'] ?? '') == 'admin' ? '13' : '12' ?>" class="text-center">
                                    <?php if(($_SESSION['role'] ?? '') === 'petugas'): ?>
                                        Anda belum memiliki laporan<?= !empty($status) ? ' dengan status ' . $status : '' ?>
                                    <?php else: ?>
                                        Tidak ada data laporan<?= !empty($status) ? ' dengan status ' . $status : '' ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($laporan as $row): ?>
                                <tr <?= ($row['status'] === 'Ditolak' && ($_SESSION['role'] ?? '') === 'petugas') ? 'class="rejected-report-row"' : '' ?>>
                                    <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                                    <td data-label="Pilih">
                                        <input type="checkbox" class="checkbox-item" value="<?= $row['id'] ?>">
                                    </td>
                                    <?php endif; ?>
                                    <td data-label="No"><?= $no++ ?></td>
                                    <td data-label="ID">
                                        <span class="badge badge-light">#<?= $row['id'] ?></span>
                                    </td>
                                    <td data-label="Foto">
                                        <?php if(!empty($row['foto_url'])): ?>
                                        <div class="photo-thumbnail-container">
                                            <img src="<?= BASE_URL . $row['foto_url'] ?>" 
                                                 alt="Foto Laporan #<?= $row['id'] ?>"
                                                 class="photo-thumbnail"
                                                 data-full-image="<?= BASE_URL . $row['foto_url'] ?>"
                                                 loading="lazy"
                                                 onerror="this.onerror=null; this.classList.add('no-image'); this.alt='Foto tidak ditemukan'; this.style.display='none'; this.parentElement.innerHTML='<div class=\'photo-thumbnail no-image\' title=\'Foto tidak ditemukan\'><i class=\'fas fa-image\'></i></div>';">
                                        </div>
                                        <?php else: ?>
                                        <div class="photo-thumbnail-container">
                                            <div class="photo-thumbnail no-image" title="Tidak ada foto">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Tanggal"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td data-label="OPT">
                                        <strong><?= htmlspecialchars($row['nama_opt'] ?? 'N/A') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($row['jenis'] ?? '-') ?></small>
                                    </td>
                                    <td data-label="Lokasi">
                                        <div><strong>Kab. <?= htmlspecialchars($row['kabupaten'] ?? 'Jember') ?></strong></div>
                                        <div>Kec. <?= htmlspecialchars($row['kecamatan'] ?? '-') ?></div>
                                        <div>Desa <?= htmlspecialchars($row['desa'] ?? '-') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars(substr($row['alamat_lengkap'] ?? ($row['lokasi'] ?? '-'), 0, 30)) ?><?= strlen($row['alamat_lengkap'] ?? ($row['lokasi'] ?? '')) > 30 ? '...' : '' ?></div>
                                    </td>
                                    <td data-label="Keparahan">
                                        <span class="badge badge-<?= 
                                            $row['tingkat_keparahan'] == 'Berat' ? 'danger' : 
                                            ($row['tingkat_keparahan'] == 'Sedang' ? 'warning' : 'info') 
                                        ?>">
                                            <?= $row['tingkat_keparahan'] ?>
                                        </span>
                                    </td>
                                    <td data-label="Populasi">
                                        <?= $row['populasi'] ?? 0 ?>
                                        <?php if(isset($row['etl_acuan']) && $row['etl_acuan'] > 0 && ($row['populasi'] ?? 0) > $row['etl_acuan']): ?>
                                        <i class="fas fa-exclamation-triangle text-danger" title="Melampaui ETL"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge badge-<?= 
                                            $row['status'] == 'Diverifikasi' ? 'success' : 
                                            ($row['status'] == 'Submitted' ? 'warning' : 
                                            ($row['status'] == 'Ditolak' ? 'danger' : 'secondary'))
                                        ?>" title="<?= 
                                            $row['status'] == 'Diverifikasi' ? 'Laporan telah diverifikasi' : 
                                            ($row['status'] == 'Submitted' ? 'Menunggu verifikasi' : 
                                            ($row['status'] == 'Ditolak' ? 'Laporan ditolak' : 'Draft belum resmi'))
                                        ?>">
                                            <i class="fas fa-<?= 
                                                $row['status'] == 'Diverifikasi' ? 'check-circle' : 
                                                ($row['status'] == 'Submitted' ? 'paper-plane' : 
                                                ($row['status'] == 'Ditolak' ? 'times-circle' : 'file'))
                                            ?>"></i>
                                            <?= $row['status'] ?>
                                        </span>
                                        
                                        <?php if($row['status'] === 'Ditolak' && !empty($row['catatan_verifikasi'])): ?>
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-comment"></i> 
                                            <strong>Alasan:</strong> <?= htmlspecialchars(substr($row['catatan_verifikasi'], 0, 30)) ?><?= strlen($row['catatan_verifikasi']) > 30 ? '...' : '' ?>
                                        </small>
                                        <?php endif; ?>
                                        
                                        <?php if($row['status'] === 'Ditolak' && !empty($row['verified_at'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <?= date('d/m H:i', strtotime($row['verified_at'])) ?>
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Pelapor">
                                        <div><strong><?= htmlspecialchars(substr($row['pelapor_nama'] ?? '-', 0, 15)) ?><?= strlen($row['pelapor_nama'] ?? '') > 15 ? '...' : '' ?></strong></div>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($row['pelapor_username'] ?? '-') ?>
                                        </small>
                                        <br>
                                        <span class="badge badge-<?= 
                                            ($row['pelapor_role'] ?? '') == 'admin' ? 'danger' : 
                                            (($row['pelapor_role'] ?? '') == 'operator' ? 'primary' : 'secondary')
                                        ?> badge-sm">
                                            <?= ucfirst($row['pelapor_role'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td data-label="Dibuat">
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                            <br><?= date('H:i', strtotime($row['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="btn-action-group" data-row-id="<?= $row['id'] ?>">
                                            <!-- View button - always available -->
                                            <a href="<?= BASE_URL ?>laporan/detail/<?= $row['id'] ?>" 
                                               class="btn-action btn-action-info btn-action-view" 
                                               data-action="view"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if($row['status'] === 'Submitted' && in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                                            <!-- Verification buttons for Submitted reports -->
                                            <button type="button" 
                                                    class="btn-action btn-action-success" 
                                                    onclick="verifyLaporan(<?= $row['id'] ?>, 'Diverifikasi')" 
                                                    title="Verifikasi Laporan">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn-action btn-action-danger" 
                                                    onclick="rejectLaporan(<?= $row['id'] ?>)" 
                                                    title="Tolak Laporan">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if($row['status'] === 'Ditolak' && ($_SESSION['role'] ?? '') === 'petugas' && $row['user_id'] == $_SESSION['user_id']): ?>
                                            <!-- Special actions for rejected reports (petugas only) -->
                                            <div class="rejected-actions-mobile">
                                                <small class="text-danger d-block mb-1">
                                                    <i class="fas fa-exclamation-triangle"></i> Perlu Diperbaiki
                                                </small>
                                                <a href="<?= BASE_URL ?>laporan/edit/<?= $row['id'] ?>" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Perbaiki Laporan">
                                                    <i class="fas fa-edit"></i> Perbaiki
                                                </a>
                                                <a href="<?= BASE_URL ?>laporan/delete/<?= $row['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Yakin ingin menghapus laporan yang ditolak ini?')" 
                                                   title="Hapus Laporan">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                            <?php elseif(in_array($_SESSION['role'] ?? '', ['admin', 'operator', 'petugas'])): ?>
                                            <!-- Regular edit button for other statuses -->
                                            <a href="<?= BASE_URL ?>laporan/edit/<?= $row['id'] ?>" 
                                               class="btn-action btn-action-warning btn-action-edit" 
                                               data-action="edit"
                                               title="Edit Laporan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if(($_SESSION['role'] ?? '') == 'admin' || (($_SESSION['role'] ?? '') == 'petugas' && $row['user_id'] == $_SESSION['user_id'] && $row['status'] !== 'Ditolak')): ?>
                                            <!-- Regular delete button (not for rejected reports of petugas - handled above) -->
                                            <a href="<?= BASE_URL ?>laporan/delete/<?= $row['id'] ?>" 
                                               class="btn-action btn-action-danger btn-action-delete" 
                                               data-action="delete"
                                               onclick="return confirm('Yakin ingin menghapus laporan ini?')" 
                                               title="Hapus Laporan">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
<script>
/**
 * Master Checkbox & Bulk Delete - Consolidated Implementation
 * Features:
 * - Check all / uncheck all functionality
 * - Indeterminate state for partial selection
 * - Row highlighting on selection
 * - Bulk delete with proper AJAX handling
 * - Event delegation for dynamic content support
 */
(function() {
    'use strict';
    
    // DOM Elements
    let checkAllElement = null;
    let bulkDeleteButton = null;
    let selectedCountElement = null;
    let tbody = null;
    
    /**
     * Initialize bulk select functionality
     */
    function initBulkSelect() {
        // Get DOM elements
        checkAllElement = document.getElementById('checkAll');
        bulkDeleteButton = document.getElementById('btnBulkDelete');
        selectedCountElement = document.getElementById('selectedCount');
        tbody = document.querySelector('#laporanTable tbody');
        
        if (!checkAllElement) {
            console.log('[Checkbox] Master checkbox not found');
            return;
        }
        
        // Setup master checkbox handler
        setupMasterCheckbox();
        
        // Setup child checkbox handlers via event delegation
        setupChildCheckboxes();
        
        // Setup bulk delete handler
        setupBulkDeleteHandler();
        
        // Set initial state
        updateUI();
        
        console.log('[Checkbox] Bulk select initialized successfully');
    }
    
    /**
     * Setup master checkbox click handler
     */
    function setupMasterCheckbox() {
        // Remove any existing listeners by cloning
        const newCheckAll = checkAllElement.cloneNode(true);
        checkAllElement.parentNode.replaceChild(newCheckAll, checkAllElement);
        checkAllElement = newCheckAll;
        
        checkAllElement.addEventListener('change', function(e) {
            e.stopPropagation();
            
            const isChecked = this.checked;
            const checkboxes = document.querySelectorAll('.checkbox-item');
            
            // Update all child checkboxes
            checkboxes.forEach(function(cb) {
                cb.checked = isChecked;
                updateRowHighlight(cb);
            });
            
            // Clear indeterminate state since we're setting all to same value
            this.indeterminate = false;
            
            // Update UI
            updateBulkButton();
        });
    }
    
    /**
     * Setup child checkboxes using event delegation
     */
    function setupChildCheckboxes() {
        if (!tbody) return;
        
        // Use event delegation for better performance and dynamic content support
        tbody.addEventListener('change', function(e) {
            if (e.target.classList.contains('checkbox-item')) {
                updateRowHighlight(e.target);
                updateMasterState();
                updateBulkButton();
            }
        });
    }
    
    /**
     * Update row highlighting based on checkbox state
     */
    function updateRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (!row) return;
        
        // Clean up any conflicting classes first
        row.classList.remove('table-warning');
        row.style.transform = '';
        
        if (checkbox.checked) {
            row.classList.add('row-selected');
        } else {
            row.classList.remove('row-selected');
        }
    }
    
    /**
     * Update master checkbox state based on child checkboxes
     */
    function updateMasterState() {
        if (!checkAllElement) return;
        
        const allCheckboxes = document.querySelectorAll('.checkbox-item');
        const checkedCheckboxes = document.querySelectorAll('.checkbox-item:checked');
        
        const totalCount = allCheckboxes.length;
        const checkedCount = checkedCheckboxes.length;
        
        if (totalCount === 0 || checkedCount === 0) {
            // None checked or no checkboxes
            checkAllElement.checked = false;
            checkAllElement.indeterminate = false;
        } else if (checkedCount === totalCount) {
            // All checked
            checkAllElement.checked = true;
            checkAllElement.indeterminate = false;
        } else {
            // Partial selection - indeterminate state
            checkAllElement.checked = false;
            checkAllElement.indeterminate = true;
        }
    }
    
    /**
     * Update bulk delete button visibility and count
     */
    function updateBulkButton() {
        if (!bulkDeleteButton) return;
        
        const checkedCount = document.querySelectorAll('.checkbox-item:checked').length;
        
        // Update count display
        if (selectedCountElement) {
            selectedCountElement.textContent = checkedCount;
        }
        
        // Toggle visibility using class
        if (checkedCount > 0) {
            bulkDeleteButton.classList.add('show');
        } else {
            bulkDeleteButton.classList.remove('show');
        }
    }
    
    /**
     * Update all UI elements
     */
    function updateUI() {
        updateMasterState();
        updateBulkButton();
    }
    
    /**
     * Setup bulk delete button handler
     */
    function setupBulkDeleteHandler() {
        if (!bulkDeleteButton) return;
        
        // Remove existing listeners by cloning
        const newButton = bulkDeleteButton.cloneNode(true);
        bulkDeleteButton.parentNode.replaceChild(newButton, bulkDeleteButton);
        bulkDeleteButton = newButton;
        selectedCountElement = document.getElementById('selectedCount');
        
        bulkDeleteButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            handleBulkDelete();
        });
    }
    
    /**
     * Handle bulk delete operation
     */
    function handleBulkDelete() {
        const checkedBoxes = document.querySelectorAll('.checkbox-item:checked');
        const ids = Array.from(checkedBoxes)
            .map(function(cb) {
                const id = cb.value;
                return (id && /^\d+$/.test(id)) ? id : null;
            })
            .filter(function(id) {
                return id !== null;
            });
        
        if (ids.length === 0) {
            showNotification('error', 'Tidak ada data yang dipilih');
            return;
        }
        
        const confirmMessage = 'Apakah Anda yakin ingin menghapus ' + ids.length + ' laporan yang dipilih?\n\n⚠️ Tindakan ini tidak dapat dibatalkan!';
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Show loading state
        const originalHtml = bulkDeleteButton.innerHTML;
        bulkDeleteButton.disabled = true;
        bulkDeleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
        
        // Prepare form data
        const formData = new FormData();
        const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        if (!csrfToken) {
            showNotification('error', 'Token CSRF tidak ditemukan. Silakan refresh halaman.');
            restoreBulkDeleteButton(originalHtml);
            return;
        }
        
        formData.append('csrf_token', csrfToken);
        ids.forEach(function(id) {
            formData.append('ids[]', id);
        });
        
        // Send AJAX request
        const controller = new AbortController();
        const timeoutId = setTimeout(function() {
            controller.abort();
        }, 30000);
        
        fetch('<?= BASE_URL ?>laporan/bulkDelete', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        })
        .then(function(response) {
            clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            restoreBulkDeleteButton(originalHtml);
            
            if (data && data.success) {
                const message = data.message || '✅ Berhasil menghapus ' + ids.length + ' laporan';
                showNotification('success', message);
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification('error', (data && data.message) ? data.message : 'Gagal menghapus data');
            }
        })
        .catch(function(error) {
            clearTimeout(timeoutId);
            restoreBulkDeleteButton(originalHtml);
            
            let errorMsg = 'Terjadi kesalahan saat menghapus data';
            if (error.name === 'AbortError') {
                errorMsg = 'Request timeout. Silakan coba lagi.';
            } else if (error.message) {
                errorMsg = error.message;
            }
            
            showNotification('error', errorMsg);
            console.error('[BulkDelete] Error:', error);
        });
    }
    
    /**
     * Restore bulk delete button to original state
     */
    function restoreBulkDeleteButton(originalHtml) {
        if (bulkDeleteButton) {
            bulkDeleteButton.disabled = false;
            bulkDeleteButton.innerHTML = originalHtml;
            selectedCountElement = document.getElementById('selectedCount');
        }
    }
    
    /**
     * Show notification message
     */
    window.showNotification = function(type, message) {
        // Remove existing notifications
        document.querySelectorAll('.bulk-delete-notification').forEach(function(n) {
            n.remove();
        });
        
        const notification = document.createElement('div');
        const typeClass = type === 'success' ? 'success' : (type === 'info' ? 'info' : 'danger');
        const icon = type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-circle');
        
        notification.className = 'bulk-delete-notification alert alert-' + typeClass + ' alert-dismissible fade show';
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; max-width: 500px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); border-radius: 8px; border: none;';
        
        notification.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<i class="fas fa-' + icon + ' mr-2"></i> ' +
            '<strong>' + (type === 'success' ? 'Berhasil!' : (type === 'info' ? 'Info:' : 'Error!')) + '</strong> ' +
            message;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Click to dismiss
        notification.addEventListener('click', function() {
            this.remove();
        });
    };
    
    /**
     * Initialize on DOM ready
     */
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBulkSelect);
        } else {
            initBulkSelect();
        }
    }
    
    // Start initialization
    init();
    
    // Support for dynamic content updates
    window.addEventListener('laporanContentUpdated', function() {
        console.log('[Checkbox] Content updated, re-initializing...');
        initBulkSelect();
    });
})();
</script>
<?php endif; ?>

<?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
<script>
// Enhanced Verification and Rejection functions with AJAX
class LaporanVerifier {
    constructor() {
        this.csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        this.baseUrl = '<?= BASE_URL ?>';
    }
    
    /**
     * Verify a report with proper validation and AJAX
     */
    async verifyLaporan(laporanId, status) {
        try {
            // Validate status
            if (!['Diverifikasi', 'Ditolak'].includes(status)) {
                this.showNotification('error', 'Status tidak valid');
                return;
            }
            
            // Get button element for loading state
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            const originalText = btn.querySelector('i') ? 'Verifikasi' : btn.textContent.trim();
            
            // Show loading state
            this.setButtonLoading(btn, true, status === 'Diverifikasi' ? 'Memverifikasi...' : 'Menolak...');
            
            // Prepare data
            const data = {
                csrf_token: this.csrfToken,
                status: status,
                redirect_to: 'index'
            };
            
            // For rejection, get reason from user
            if (status === 'Ditolak') {
                const alasan = await this.getRejectionReason();
                if (!alasan) {
                    this.setButtonLoading(btn, false, originalText);
                    return;
                }
                data.catatan_verifikasi = alasan;
            } else {
                data.catatan_verifikasi = 'Diverifikasi dari daftar laporan';
            }
            
            // Send AJAX request
            const response = await fetch(`${this.baseUrl}laporan/verify/${laporanId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('success', result.message || (status === 'Diverifikasi' ? 'Laporan berhasil diverifikasi' : 'Laporan berhasil ditolak'));
                
                // Update UI without full page reload
                this.updateLaporanStatus(laporanId, status, data.catatan_verifikasi);
                
                // Remove the row from the table after a short delay
                setTimeout(() => {
                    this.removeTableRow(laporanId);
                }, 1000);
            } else {
                throw new Error(result.message || 'Gagal memproses permintaan');
            }
            
        } catch (error) {
            console.error('Verification error:', error);
            this.showNotification('error', error.message || 'Terjadi kesalahan saat memproses laporan');
            
            // Restore button state
            const btn = event.target.closest('button');
            this.setButtonLoading(btn, false, btn.querySelector('i') ? 'Verifikasi' : btn.textContent.trim());
        }
    }
    
    /**
     * Get rejection reason from user with validation
     */
    async getRejectionReason() {
        return new Promise((resolve) => {
            const modal = this.createRejectionModal();
            const textarea = modal.querySelector('#rejectionReason');
            const submitBtn = modal.querySelector('#submitRejection');
            const cancelBtn = modal.querySelector('#cancelRejection');
            
            // Show modal
            document.body.appendChild(modal);
            modal.style.display = 'block';
            textarea.focus();
            
            // Handle submit
            submitBtn.onclick = () => {
                const reason = textarea.value.trim();
                if (!reason) {
                    textarea.classList.add('is-invalid');
                    return;
                }
                
                modal.remove();
                resolve(reason);
            };
            
            // Handle cancel
            cancelBtn.onclick = () => {
                modal.remove();
                resolve(null);
            };
            
            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    resolve(null);
                }
            });
        });
    }
    
    /**
     * Create rejection reason modal
     */
    createRejectionModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            display: none;
        `;
        
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alasan Penolakan Laporan</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejectionReason">Masukkan alasan penolakan:</label>
                            <textarea id="rejectionReason" class="form-control" rows="4" placeholder="Jelaskan alasan penolakan laporan ini..."></textarea>
                            <div class="invalid-feedback">Alasan penolakan wajib diisi</div>
                        </div>
                        <div class="text-muted small">Alasan ini akan dikirim ke pelapor sebagai notifikasi</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelRejection" class="btn btn-secondary">Batal</button>
                        <button type="button" id="submitRejection" class="btn btn-danger">Tolak Laporan</button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }
    
    /**
     * Update table row status without full reload
     */
    updateLaporanStatus(laporanId, status, catatan) {
        const row = document.querySelector(`tr[data-row-id="${laporanId}"]`) ||
                   document.querySelector(`tr:has(a[href*="/${laporanId}"])`);
        
        if (row) {
            // Update status badge
            const statusCell = row.querySelector('td[data-label="Status"] .badge');
            if (statusCell) {
                statusCell.className = `badge badge-${status === 'Diverifikasi' ? 'success' : 'danger'}`;
                statusCell.innerHTML = `<i class="fas fa-${status === 'Diverifikasi' ? 'check-circle' : 'times-circle'}"></i> ${status}`;
                
                // Add rejection reason if applicable
                if (status === 'Ditolak' && catatan) {
                    const statusContainer = statusCell.parentElement;
                    let reasonDiv = statusContainer.querySelector('.text-danger');
                    if (!reasonDiv) {
                        reasonDiv = document.createElement('div');
                        reasonDiv.className = 'text-danger small mt-1';
                        statusContainer.appendChild(reasonDiv);
                    }
                    reasonDiv.innerHTML = `<i class="fas fa-comment"></i> <strong>Alasan:</strong> ${catatan}`;
                }
            }
            
            // Update action buttons
            const actionCell = row.querySelector('td[data-label="Aksi"]');
            if (actionCell) {
                // Remove verification buttons
                const verifyBtn = actionCell.querySelector('button[data-action="verify"]');
                const rejectBtn = actionCell.querySelector('button[data-action="reject"]');
                if (verifyBtn) verifyBtn.remove();
                if (rejectBtn) rejectBtn.remove();
                
                // Add status indicator
                const statusIndicator = document.createElement('span');
                statusIndicator.className = 'badge badge-info';
                statusIndicator.innerHTML = `<i class="fas fa-${status === 'Diverifikasi' ? 'check' : 'times'}"></i> ${status}`;
                actionCell.appendChild(statusIndicator);
            }
        }
    }
    
    /**
     * Remove table row after successful action
     */
    removeTableRow(laporanId) {
        const row = document.querySelector(`tr[data-row-id="${laporanId}"]`) ||
                   document.querySelector(`tr:has(a[href*="/${laporanId}"])`);
        
        if (row) {
            row.style.backgroundColor = '#d4edda';
            row.style.transition = 'background-color 0.5s ease';
            
            setTimeout(() => {
                row.style.opacity = '0';
                row.style.transform = 'scale(0.9)';
                row.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    row.remove();
                    
                    // Update table if empty
                    const tbody = document.querySelector('#laporanTable tbody');
                    if (tbody && tbody.children.length === 0) {
                        const emptyRow = document.createElement('tr');
                        emptyRow.innerHTML = '<td colspan="13" class="text-center">Tidak ada data laporan</td>';
                        tbody.appendChild(emptyRow);
                    }
                }, 300);
            }, 500);
        }
    }
    
    /**
     * Set button loading state
     */
    setButtonLoading(button, isLoading, loadingText) {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${loadingText}`;
        } else {
            button.disabled = false;
            button.innerHTML = loadingText;
        }
    }
    
    /**
     * Show notification message
     */
    showNotification(type, message) {
        // Remove existing notifications
        document.querySelectorAll('.laporan-notification').forEach(n => n.remove());
        
        const notification = document.createElement('div');
        const typeClass = type === 'success' ? 'success' : (type === 'info' ? 'info' : 'danger');
        const icon = type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-circle');
        
        notification.className = `laporan-notification alert alert-${typeClass} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; max-width: 500px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); border-radius: 8px; border: none;';
        
        notification.innerHTML = `
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <i class="fas fa-${icon} mr-2"></i>
            <strong>${type === 'success' ? 'Berhasil!' : (type === 'info' ? 'Info:' : 'Error!')}</strong> ${message}
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Click to dismiss
        notification.addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize verifier
const verifier = new LaporanVerifier();

// Enhanced button handlers
function verifyLaporan(laporanId, status) {
    verifier.verifyLaporan(laporanId, status);
}

function rejectLaporan(laporanId) {
    verifier.verifyLaporan(laporanId, 'Ditolak');
}
</script>
<?php endif; ?>


<?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
<script>
// Verification and Rejection functions for operators
function verifyLaporan(laporanId, status) {
    if (!confirm('Apakah Anda yakin ingin memverifikasi laporan ini?\n\nLaporan akan diubah statusnya menjadi "Diverifikasi".')) {
        return;
    }
    
    // Show loading
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    formData.append('status', status);
    formData.append('catatan_verifikasi', 'Diverifikasi dari daftar laporan');
    formData.append('redirect_to', 'index');
    
    // Send AJAX request
    fetch('<?= BASE_URL ?>laporan/verify/' + laporanId, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // If redirected, follow the redirect
            window.location.href = response.url;
            return;
        }
        return response.text();
    })
    .then(data => {
        // Reload page to show updated status
        window.location.reload();
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Terjadi kesalahan: ' + error.message);
        console.error('Error:', error);
    });
}

function rejectLaporan(laporanId) {
    const alasan = prompt('Masukkan alasan penolakan laporan:\n\n(Wajib diisi)');
    
    if (alasan === null) {
        // User cancelled
        return;
    }
    
    if (!alasan || alasan.trim() === '') {
        alert('Alasan penolakan wajib diisi!');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin menolak laporan ini?\n\nAlasan: ' + alasan)) {
        return;
    }
    
    // Show loading
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    formData.append('status', 'Ditolak');
    formData.append('catatan_verifikasi', alasan);
    formData.append('redirect_to', 'index');
    
    // Send AJAX request
    fetch('<?= BASE_URL ?>laporan/verify/' + laporanId, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // If redirected, follow the redirect
            window.location.href = response.url;
            return;
        }
        return response.text();
    })
    .then(data => {
        // Reload page to show updated status
        window.location.reload();
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Terjadi kesalahan: ' + error.message);
        console.error('Error:', error);
    });
}
</script>
<?php endif; ?>

<script>
// Simple navigation fix for "Buat Laporan Baru" button
(function() {
    'use strict';
    
    var targetUrl = '<?= BASE_URL ?>laporan/create';
    
    function setupButton() {
        var btn = document.getElementById('btnCreateLaporan');
        if (!btn) return;
        
        // Ensure href is set correctly
        btn.setAttribute('href', targetUrl);
        
        // Remove any interfering attributes
        btn.removeAttribute('onclick');
        btn.removeAttribute('data-ajax');
        
        console.log('[Navigation] Button setup complete');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupButton);
    } else {
        setupButton();
    }
})();
</script>

<!-- Photo Preview Overlay -->
<div id="photoPreviewOverlay" class="photo-preview-overlay">
    <span class="photo-preview-close">&times;</span>
    <img id="photoPreviewImage" class="photo-preview-image" src="" alt="Preview Foto">
</div>

<script>
// Photo Preview Functionality
(function() {
    'use strict';
    
    const overlay = document.getElementById('photoPreviewOverlay');
    const previewImage = document.getElementById('photoPreviewImage');
    const closeBtn = document.querySelector('.photo-preview-close');
    const thumbnails = document.querySelectorAll('.photo-thumbnail');
    
    // Open preview
    function openPreview(imageSrc) {
        if (!imageSrc || imageSrc.includes('no-image')) return;
        
        previewImage.src = imageSrc;
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    // Close preview
    function closePreview() {
        overlay.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
        previewImage.src = '';

    }
    
    // Attach click events to thumbnails
    thumbnails.forEach(thumbnail => {
        if (thumbnail.tagName === 'IMG' && thumbnail.dataset.fullImage) {
            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                openPreview(this.dataset.fullImage);
            });
        }
    });
    
    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay || e.target === closeBtn) {
            closePreview();
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) {
            closePreview();
        }
    });
    
    // Prevent image click from closing (click on image should not close)
    previewImage.addEventListener('click', function(e) {
        e.stopPropagation();
    });
})();
</script>

<!-- Filter Status Enhancement Script -->
<script src="<?= BASE_URL ?>public/js/filter-status.js"></script>

<!-- CSS untuk Menonaktifkan Efek Hover -->
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/hover-disabled.css">

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
