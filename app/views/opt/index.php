<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* OPT Index Page Styles */
.stat-card {
    border-radius: 10px;
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.stat-card .icon {
    font-size: 2.5rem;
    opacity: 0.8;
}
.stat-card .count {
    font-size: 2rem;
    font-weight: bold;
}
.filter-bar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.filter-bar .form-control, .filter-bar .btn {
    border-radius: 6px;
}
.badge-karantina {
    font-size: 0.7rem;
    padding: 0.3rem 0.5rem;
}
.badge-bahaya-rendah { background: #28a745; color: white; }
.badge-bahaya-sedang { background: #ffc107; color: #333; }
.badge-bahaya-tinggi { background: #fd7e14; color: white; }
.badge-bahaya-sangat-tinggi { background: #dc3545; color: white; }
.table-opt th {
    background: #28a745;
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    white-space: nowrap;
}
.table-opt td {
    vertical-align: middle;
    font-size: 0.9rem;
}
.table-opt .nama-ilmiah {
    font-style: italic;
    color: #6c757d;
    font-size: 0.8rem;
}
.pagination-info {
    font-size: 0.9rem;
    color: #6c757d;
}
.export-buttons .btn {
    border-radius: 20px;
    padding: 0.4rem 1rem;
    font-size: 0.85rem;
}
.opt-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #dee2e6;
    transition: transform 0.3s, z-index 0.3s;
}
.opt-thumbnail:hover {
    transform: scale(3);
    z-index: 100;
    position: relative;
}
.klasifikasi-text {
    font-size: 0.75rem;
    color: #6c757d;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card stat-card bg-gradient-success text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="count"><?= $stats['total'] ?? 0 ?></div>
                    <div>Total OPT</div>
                </div>
                <div class="icon"><i class="fas fa-bug"></i></div>
            </div>
        </div>
    </div>
    <?php 
    $jenisColors = ['Hama' => 'danger', 'Penyakit' => 'warning', 'Gulma' => 'info'];
    foreach ($stats['by_jenis'] ?? [] as $stat): 
        $color = $jenisColors[$stat['jenis']] ?? 'secondary';
    ?>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card stat-card bg-gradient-<?= $color ?> text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="count"><?= $stat['total'] ?></div>
                    <div><?= htmlspecialchars($stat['jenis']) ?></div>
                </div>
                <div class="icon">
                    <i class="fas fa-<?= $stat['jenis'] == 'Hama' ? 'spider' : ($stat['jenis'] == 'Penyakit' ? 'virus' : 'seedling') ?>"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-2 mb-md-0">
                    <i class="fas fa-bug"></i> Daftar OPT (Organisme Pengganggu Tumbuhan)
                </h3>
                <div class="card-tools d-flex flex-wrap gap-2">
                    <div class="export-buttons mr-2">
                        <a href="<?= BASE_URL ?>opt/exportExcel?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="<?= BASE_URL ?>opt/exportPdf?<?= http_build_query($_GET) ?>" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                    <a href="<?= BASE_URL ?>opt/photos" class="btn btn-info btn-sm mr-2">
                        <i class="fas fa-images"></i> <span class="d-none d-md-inline">Foto</span>
                    </a>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <a href="<?= BASE_URL ?>opt/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> <span class="d-none d-md-inline">Tambah OPT</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filter Bar -->
                <form method="GET" class="filter-bar mb-3">
                    <div class="row g-2">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama/kode OPT..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <select name="jenis" class="form-control">
                                <option value="">Semua Jenis</option>
                                <?php foreach ($filter_options['jenis'] ?? [] as $jenis): ?>
                                <option value="<?= $jenis ?>" <?= ($filters['jenis'] ?? '') == $jenis ? 'selected' : '' ?>><?= $jenis ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <select name="status_karantina" class="form-control">
                                <option value="">Semua Karantina</option>
                                <?php foreach ($filter_options['status_karantina'] ?? [] as $status): ?>
                                <option value="<?= $status ?>" <?= ($filters['status_karantina'] ?? '') == $status ? 'selected' : '' ?>><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <select name="tingkat_bahaya" class="form-control">
                                <option value="">Semua Bahaya</option>
                                <?php foreach ($filter_options['tingkat_bahaya'] ?? [] as $bahaya): ?>
                                <option value="<?= $bahaya ?>" <?= ($filters['tingkat_bahaya'] ?? '') == $bahaya ? 'selected' : '' ?>><?= $bahaya ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="<?= BASE_URL ?>opt" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-opt">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th width="60">Foto</th>
                                <th width="80">Kode</th>
                                <th>Nama OPT</th>
                                <th width="80">Jenis</th>
                                <th>Klasifikasi</th>
                                <th width="90">Karantina</th>
                                <th width="90">Bahaya</th>
                                <th width="50">ETL</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data_opt)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Tidak ada data OPT ditemukan</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $no = $pagination['from'] ?? 1; foreach($data_opt as $opt): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center">
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
                                            <img src="<?= BASE_URL . $photoUrl ?>" 
                                                 class="opt-thumbnail" 
                                                 alt="<?= htmlspecialchars($opt['nama_opt']) ?>"
                                                 onerror="this.onerror=null; this.src='<?= BASE_URL ?>public/img/no-image.png'; this.style.opacity='0.5';">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image fa-2x"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($opt['kode_opt'] ?? '') ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($opt['nama_opt'] ?? '') ?></strong>
                                        <?php if (!empty($opt['nama_ilmiah'])): ?>
                                        <div class="nama-ilmiah"><?= htmlspecialchars($opt['nama_ilmiah']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($opt['nama_lokal'])): ?>
                                        <small class="text-muted">(<?= htmlspecialchars($opt['nama_lokal']) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $opt['jenis'] == 'Hama' ? 'danger' : 
                                            ($opt['jenis'] == 'Penyakit' ? 'warning' : 'info') 
                                        ?>">
                                            <?= $opt['jenis'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $klasifikasi = array_filter([
                                            $opt['kingdom'] ?? '',
                                            $opt['filum'] ?? '',
                                            $opt['famili'] ?? ''
                                        ]);
                                        if (!empty($klasifikasi)):
                                        ?>
                                        <div class="klasifikasi-text" title="<?= htmlspecialchars(implode(' › ', $klasifikasi)) ?>">
                                            <?= htmlspecialchars(implode(' › ', $klasifikasi)) ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $karantina = $opt['status_karantina'] ?? 'Tidak';
                                        $karantinaClass = $karantina == 'Tidak' ? 'secondary' : 'danger';
                                        ?>
                                        <span class="badge badge-<?= $karantinaClass ?> badge-karantina">
                                            <?= htmlspecialchars($karantina) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $bahaya = $opt['tingkat_bahaya'] ?? 'Sedang';
                                        $bahayaClass = [
                                            'Rendah' => 'bahaya-rendah',
                                            'Sedang' => 'bahaya-sedang',
                                            'Tinggi' => 'bahaya-tinggi',
                                            'Sangat Tinggi' => 'bahaya-sangat-tinggi'
                                        ][$bahaya] ?? 'bahaya-sedang';
                                        ?>
                                        <span class="badge badge-<?= $bahayaClass ?>">
                                            <?= htmlspecialchars($bahaya) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $opt['etl_acuan'] ?? 0 ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>opt/detail/<?= $opt['id'] ?>" class="btn btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                                            <a href="<?= BASE_URL ?>opt/edit/<?= $opt['id'] ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                                            <a href="<?= BASE_URL ?>opt/delete/<?= $opt['id'] ?>" class="btn btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus data OPT ini?')" title="Hapus">
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

                <!-- Pagination -->
                <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <div class="pagination-info mb-2">
                        Menampilkan <?= $pagination['from'] ?? 0 ?> - <?= $pagination['to'] ?? 0 ?> dari <?= $pagination['total'] ?? 0 ?> data
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php 
                            $currentPage = $pagination['current_page'] ?? 1;
                            $lastPage = $pagination['last_page'] ?? 1;
                            
                            // Build query string without page
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            $baseUrl = BASE_URL . 'opt?' . ($queryString ? $queryString . '&' : '');
                            ?>
                            
                            <!-- Previous -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                            
                            if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>page=1">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $lastPage): ?>
                            <?php if ($end < $lastPage - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>page=<?= $lastPage ?>"><?= $lastPage ?></a>
                            </li>
                            <?php endif; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
