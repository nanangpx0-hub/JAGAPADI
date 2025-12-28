<?php 
$pageTitle = $data['page_title'] ?? 'Data Curah Hujan';
require_once ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-cloud-rain text-primary"></i> <?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-muted mb-0">Monitoring curah hujan untuk analisis pertanian</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/curahHujan/export?year=<?= $data['currentYear'] ?>" class="btn btn-outline-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#scraperModal">
                <i class="fas fa-sync"></i> Update Data
            </button>
            <a href="<?= BASE_URL ?>/curahHujan/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Input Manual
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <!-- Source Metadata Alert -->
            <?php if(isset($data['lastScrape'])): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading font-weight-bold mb-1">Status Data Curah Hujan</h6>
                        <ul class="mb-0 pl-3 small">
                            <li><strong>Sumber Data:</strong> <?= strpos($data['lastScrape']['message'], 'BMKG') !== false ? 'BMKG (Prakiraan Cuaca)' : 'Simulasi Data (JAGAPADI Internal)' ?> - <a href="https://api.bmkg.go.id/publik/prakiraan-cuaca" target="_blank" class="text-info font-weight-bold"><u>Lihat Sumber Asli</u></a></li>
                            <li><strong>Terakhir Diperbarui:</strong> <?= date('d F Y, H:i', strtotime($data['lastScrape']['created_at'])) ?> WIB</li>
                            <li><strong>Metode Scraping:</strong> <?= ucfirst($data['lastScrape']['action']) ?> (<?= ucfirst($data['lastScrape']['status']) ?>)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select class="form-control" id="filterYear" name="year">
                        <?php foreach ($data['availableYears'] as $year): ?>
                        <option value="<?= $year ?>" <?= $year == $data['currentYear'] ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                        <?php if (empty($data['availableYears'])): ?>
                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select class="form-control" id="filterMonth" name="month">
                        <option value="">Semua Bulan</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Grafik</label>
                    <select class="form-control" id="chartType">
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Rata-rata</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statAverage">
                                <?= number_format($data['statistics']['rata_rata'] ?? 0, 2) ?> mm
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tint fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Maksimum</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statMax">
                                <?= number_format($data['statistics']['maksimum'] ?? 0, 2) ?> mm
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cloud-showers-heavy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hari Hujan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statRainyDays">
                                <?= $data['statistics']['hari_hujan'] ?? 0 ?> hari
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Data</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotal">
                                <?= $data['statistics']['total_records'] ?? 0 ?> record
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Grafik Curah Hujan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="rainfallChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Distribusi Bulanan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 320px;">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Data Curah Hujan
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Curah Hujan</th>
                            <th>Sumber</th>
                            <th>Keterangan</th>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        <?php if (!empty($data['recentData'])): ?>
                        <?php foreach ($data['recentData'] as $row): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['lokasi']) ?></td>
                            <td>
                                <span class="badge badge-<?= $row['curah_hujan'] > 50 ? 'danger' : ($row['curah_hujan'] > 20 ? 'warning' : ($row['curah_hujan'] > 0 ? 'info' : 'secondary')) ?>">
                                    <?= number_format($row['curah_hujan'], 2) ?> mm
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['sumber_data']) ?></td>
                            <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav aria-label="Page navigation" id="paginationNav">
                <ul class="pagination justify-content-center" id="pagination">
                </ul>
            </nav>
        </div>
    </div>

    <?php if ($_SESSION['role'] === 'admin' && !empty($data['recentLogs'])): ?>
    <!-- Recent Logs (Admin Only) -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-secondary">
                <i class="fas fa-history"></i> Log Aktivitas Scraping
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Aksi</th>
                            <th>Status</th>
                            <th>Pesan</th>
                            <th>Record</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recentLogs'] as $log): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td>
                                <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'partial' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($log['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['message']) ?></td>
                            <td><?= $log['records_success'] ?>/<?= $log['records_processed'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-log" onclick="deleteLog(<?= $log['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Scraper Modal -->
<div class="modal fade" id="scraperModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-sync"></i> Update Data Curah Hujan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="scraperForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                    <div class="form-group">
                        <label>Tahun</label>
                        <select class="form-control" name="year" id="scraperYear">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Bulan</label>
                        <select class="form-control" name="month" id="scraperMonth">
                            <?php 
                            $bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= $bulanNames[$m-1] ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="force_simulation" id="forceSimulation">
                        <label class="form-check-label" for="forceSimulation">
                            Gunakan data simulasi (untuk testing)
                        </label>
                    </div>
                    <div id="scraperResult" class="mt-3" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnRunScraper">
                        <i class="fas fa-play"></i> Jalankan Scraper
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= Security::generateCsrfToken() ?>';
    let rainfallChart = null;
    let distributionChart = null;
    let currentPage = 1;
    const perPage = 10;

    // Initialize charts
    initCharts();
    
    // Filter form submit
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
        loadCharts();
    });
    
    // Chart type change
    document.getElementById('chartType').addEventListener('change', function() {
        loadCharts();
    });
    
    // Scraper form submit
    document.getElementById('scraperForm').addEventListener('submit', function(e) {
        e.preventDefault();
        runScraper();
    });
    
    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Yakin ingin menghapus data ini?')) {
                deleteData(this.dataset.id);
            }
        });
    });

    function initCharts() {
        const ctxRainfall = document.getElementById('rainfallChart').getContext('2d');
        rainfallChart = new Chart(ctxRainfall, {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Curah Hujan (mm)'
                        }
                    }
                }
            }
        });

        const ctxDist = document.getElementById('distributionChart').getContext('2d');
        distributionChart = new Chart(ctxDist, {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        loadCharts();
    }

    function loadCharts() {
        const year = document.getElementById('filterYear').value;
        const type = document.getElementById('chartType').value;

        fetch(`<?= BASE_URL ?>/curahHujan/getChartData?type=${type}&year=${year}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    rainfallChart.data.labels = data.labels;
                    rainfallChart.data.datasets = data.datasets;
                    rainfallChart.update();

                    // Update distribution chart with first dataset
                    if (data.datasets.length > 0) {
                        distributionChart.data.labels = data.labels;
                        distributionChart.data.datasets = [{
                            label: 'Curah Hujan (mm)',
                            data: data.datasets[0].data,
                            backgroundColor: data.datasets[0].data.map(v => 
                                v > 50 ? 'rgba(220, 53, 69, 0.7)' : 
                                v > 20 ? 'rgba(255, 193, 7, 0.7)' : 
                                v > 0 ? 'rgba(23, 162, 184, 0.7)' : 
                                'rgba(108, 117, 125, 0.7)'
                            )
                        }];
                        distributionChart.update();
                    }
                }
            })
            .catch(err => console.error('Chart error:', err));
    }

    function loadData() {
        const year = document.getElementById('filterYear').value;
        const month = document.getElementById('filterMonth').value;
        
        let url = `<?= BASE_URL ?>/curahHujan/getData?year=${year}&limit=${perPage}&offset=${(currentPage-1)*perPage}`;
        if (month) url += `&month=${month}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateTable(data.data);
                    updateStatistics(data.statistics);
                    updatePagination(data.total);
                }
            })
            .catch(err => console.error('Data error:', err));
    }

    function updateTable(rows) {
        const tbody = document.getElementById('dataTableBody');
        const isAdmin = <?= $_SESSION['role'] === 'admin' ? 'true' : 'false' ?>;
        
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(row => {
            const badgeClass = row.curah_hujan > 50 ? 'danger' : row.curah_hujan > 20 ? 'warning' : row.curah_hujan > 0 ? 'info' : 'secondary';
            const date = new Date(row.tanggal);
            const formattedDate = date.toLocaleDateString('id-ID');
            
            return `<tr>
                <td>${formattedDate}</td>
                <td>${row.lokasi}</td>
                <td><span class="badge badge-${badgeClass}">${parseFloat(row.curah_hujan).toFixed(2)} mm</span></td>
                <td>${row.sumber_data}</td>
                <td>${row.keterangan || '-'}</td>
                ${isAdmin ? `<td><button class="btn btn-sm btn-danger btn-delete" onclick="deleteData(${row.id})"><i class="fas fa-trash"></i></button></td>` : ''}
            </tr>`;
        }).join('');
    }

    function updateStatistics(stats) {
        document.getElementById('statAverage').textContent = parseFloat(stats.rata_rata || 0).toFixed(2) + ' mm';
        document.getElementById('statMax').textContent = parseFloat(stats.maksimum || 0).toFixed(2) + ' mm';
        document.getElementById('statRainyDays').textContent = (stats.hari_hujan || 0) + ' hari';
        document.getElementById('statTotal').textContent = (stats.total_records || 0) + ' record';
    }

    function updatePagination(total) {
        const totalPages = Math.ceil(total / perPage);
        const pagination = document.getElementById('pagination');
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>`;
        }
        pagination.innerHTML = html;
    }

    window.goToPage = function(page) {
        currentPage = page;
        loadData();
    };

    window.deleteData = function(id) {
        if (!confirm('Yakin ingin menghapus data ini?')) return;

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);

        fetch(`<?= BASE_URL ?>/curahHujan/delete/${id}`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadData();
                loadCharts();
            } else {
                alert(data.error || 'Gagal menghapus data');
            }
        })
        .catch(err => alert('Error: ' + err.message));
    };

    window.deleteLog = function(id) {
        if (!confirm('Yakin ingin menghapus log aktivitas ini?')) return;

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);

        fetch(`<?= BASE_URL ?>/curahHujan/deleteLog/${id}`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Gagal menghapus log');
            }
        })
        .catch(err => alert('Error: ' + err.message));
    };

    function runScraper() {
        const btn = document.getElementById('btnRunScraper');
        const resultDiv = document.getElementById('scraperResult');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        resultDiv.style.display = 'none';

        const formData = new FormData(document.getElementById('scraperForm'));

        fetch('<?= BASE_URL ?>/curahHujan/runScraper', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'alert alert-success';
                resultDiv.innerHTML = `<strong>Berhasil!</strong><br>
                    Sumber: ${data.source}<br>
                    Record berhasil: ${data.records_success}<br>
                    Waktu: ${data.execution_time}s`;
                loadData();
                loadCharts();
            } else {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = `<strong>Gagal!</strong><br>${data.error || data.message}`;
            }
        })
        .catch(err => {
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = `<strong>Error:</strong> ${err.message}`;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Jalankan Scraper';
        });
    }
});
</script>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }

@media (max-width: 768px) {
    .chart-area, .chart-bar {
        height: 250px !important;
    }
    .btn-group {
        flex-direction: column;
    }
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}
</style>

<?php require_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>
