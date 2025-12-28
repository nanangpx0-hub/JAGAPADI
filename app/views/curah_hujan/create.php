<?php 
$pageTitle = $data['page_title'] ?? 'Tambah Data Curah Hujan';
require_once ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> <?= htmlspecialchars($pageTitle) ?></h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['error']); endif; ?>

                    <form action="<?= BASE_URL ?>/curahHujan/store" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                        
                        <div class="form-group">
                            <label for="tanggal"><i class="fas fa-calendar"></i> Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="lokasi"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                   value="Jember" placeholder="Nama lokasi">
                        </div>

                        <div class="form-group">
                            <label for="kode_wilayah"><i class="fas fa-code"></i> Kode Wilayah</label>
                            <input type="text" class="form-control" id="kode_wilayah" name="kode_wilayah" 
                                   value="35.09" placeholder="Kode wilayah BMKG">
                            <small class="form-text text-muted">Kode wilayah Jember: 35.09</small>
                        </div>

                        <div class="form-group">
                            <label for="curah_hujan"><i class="fas fa-tint"></i> Curah Hujan (mm) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="curah_hujan" name="curah_hujan" 
                                   min="0" max="500" step="0.01" required placeholder="Masukkan nilai curah hujan">
                            <small class="form-text text-muted">Nilai antara 0 - 500 mm</small>
                        </div>

                        <div class="form-group">
                            <label for="keterangan"><i class="fas fa-sticky-note"></i> Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" 
                                      placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/curahHujan" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>
