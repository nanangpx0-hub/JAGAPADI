<?php
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-upload mr-2"></i> Import Kecamatan Jawa Timur</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah/kabupaten">Wilayah</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>adminWilayah/kecamatan">Kecamatan</a></li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-ban"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Petunjuk</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Sumber data: <code>master-file-desa-provinsi-jawa-timur-2024.pdf</code></li>
                        <li>Anda dapat mengunggah file <strong>PDF</strong> tersebut atau file <strong>CSV</strong> hasil ekstraksi.</li>
                        <li>Format CSV: header <code>kabupaten,kecamatan,kode</code> atau <code>kabupaten_kota,nama_kecamatan,kode_kecamatan</code></li>
                        <li>Sistem mencegah duplikasi nama/kode pada kabupaten yang sama.</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-upload mr-2"></i> Unggah File</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>adminWilayah/kecamatan_import">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="form-group">
                            <label>Pilih File (PDF/CSV) <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.csv" required>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import mr-1"></i> Import
                            </button>
                            <a href="<?= BASE_URL ?>adminWilayah/kecamatan" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
