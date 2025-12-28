<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<!-- Debug Info (only in development) -->
<?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong><i class="fas fa-info-circle"></i> Debug Info:</strong>
    <ul class="mb-0 mt-2">
        <li>Total Laporan: <?= $stats['total_laporan'] ?? 0 ?></li>
        <li>Recent Reports Count: <?= count($recentReports ?? []) ?></li>
        <li>Monthly Stats Count: <?= count($monthlyStats ?? []) ?></li>
        <li>Top Pests Count: <?= count($topPests ?? []) ?></li>
    </ul>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 data-stat="total_laporan"><?= $stats['total_laporan'] ?? 0 ?></h3>
                <p>Total Laporan</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="<?= BASE_URL ?>laporan" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 data-stat="pending_verifikasi"><?= $stats['pending_verifikasi'] ?? 0 ?></h3>
                <p>Pending Verifikasi</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="<?= BASE_URL ?>laporan?status=Submitted" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 data-stat="terverifikasi"><?= $stats['terverifikasi'] ?? 0 ?></h3>
                <p>Terverifikasi</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="<?= BASE_URL ?>laporan?status=Diverifikasi" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 data-stat="keparahan_berat"><?= $stats['keparahan_berat'] ?? 0 ?></h3>
                <p>Serangan Berat</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="<?= BASE_URL ?>laporan?keparahan=Berat" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Tren Laporan Bulanan (<?= date('Y') ?>)</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-ol"></i> Top 5 OPT Terbanyak</h3>
            </div>
            <div class="card-body">
                <canvas id="topPestsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Laporan Terbaru</h3>
                <div class="card-tools">
                    <a href="<?= BASE_URL ?>laporan" class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <style>
                    /* Dashboard Recent Reports Table Styles */
                    .dashboard-table {
                        table-layout: fixed;
                        width: 100%;
                    }
                    .dashboard-table th,
                    .dashboard-table td {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                        vertical-align: middle;
                    }
                    /* Column widths */
                    .dashboard-table .col-tanggal { width: 10%; min-width: 80px; }
                    .dashboard-table .col-kabupaten { width: 15%; min-width: 100px; }
                    .dashboard-table .col-kecamatan { width: 15%; min-width: 100px; }
                    .dashboard-table .col-desa { width: 15%; min-width: 100px; }
                    .dashboard-table .col-opt { width: 12%; min-width: 80px; }
                    .dashboard-table .col-lokasi { width: 15%; min-width: 100px; }
                    .dashboard-table .col-keparahan { width: 8%; min-width: 60px; text-align: center; }
                    .dashboard-table .col-status { width: 8%; min-width: 60px; text-align: center; }
                    .dashboard-table .col-pelapor { width: 12%; min-width: 80px; }
                    
                    /* Badge in table */
                    .dashboard-table .badge {
                        font-size: 0.75rem;
                        padding: 0.35em 0.6em;
                        white-space: nowrap;
                    }
                    
                    /* Mobile adjustments */
                    @media (max-width: 767.98px) {
                        .dashboard-table {
                            font-size: 0.85rem;
                        }
                        .dashboard-table th,
                        .dashboard-table td {
                            padding: 0.5rem 0.4rem;
                        }
                        .dashboard-table .badge {
                            font-size: 0.7rem;
                            padding: 0.25em 0.4em;
                        }
                    }
                    
                    @media (max-width: 575.98px) {
                        .dashboard-table {
                            font-size: 0.8rem;
                        }
                        .dashboard-table th,
                        .dashboard-table td {
                            padding: 0.4rem 0.3rem;
                        }
                    }
                </style>
                <div class="table-responsive">
                    <table class="table table-striped dashboard-table">
                        <thead>
                            <tr>
                                <th class="col-tanggal">Tanggal</th>
                                <th class="col-kabupaten">Kabupaten/Kota</th>
                                <th class="col-kecamatan">Kecamatan</th>
                                <th class="col-desa">Desa/Kelurahan</th>
                                <th class="col-opt">OPT</th>
                                <th class="col-lokasi">Lokasi</th>
                                <th class="col-keparahan">Keparahan</th>
                                <th class="col-status">Status</th>
                                <th class="col-pelapor">Pelapor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentReports)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada laporan</p>
                                    <small>Data laporan akan muncul di sini setelah ada input</small>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($recentReports as $report): ?>
                                <tr>
                                    <td class="col-tanggal" title="<?= date('d/m/Y', strtotime($report['tanggal'] ?? 'now')) ?>"><?= date('d/m/Y', strtotime($report['tanggal'] ?? 'now')) ?></td>
                                    <td class="col-kabupaten" title="<?= htmlspecialchars($report['nama_kabupaten'] ?? '-') ?>"><?= htmlspecialchars($report['nama_kabupaten'] ?? '-') ?></td>
                                    <td class="col-kecamatan" title="<?= htmlspecialchars($report['nama_kecamatan'] ?? '-') ?>"><?= htmlspecialchars($report['nama_kecamatan'] ?? '-') ?></td>
                                    <td class="col-desa" title="<?= htmlspecialchars($report['nama_desa'] ?? '-') ?>"><?= htmlspecialchars($report['nama_desa'] ?? '-') ?></td>
                                    <td class="col-opt" title="<?= htmlspecialchars($report['nama_opt'] ?? '-') ?>"><?= htmlspecialchars($report['nama_opt'] ?? '-') ?></td>
                                    <td class="col-lokasi" title="<?= htmlspecialchars($report['lokasi'] ?? $report['alamat_lengkap'] ?? '-') ?>"><?= htmlspecialchars($report['lokasi'] ?? $report['alamat_lengkap'] ?? '-') ?></td>
                                    <td class="col-keparahan">
                                        <span class="badge badge-<?=
                                            ($report['tingkat_keparahan'] ?? '') == 'Berat' ? 'danger' :
                                            (($report['tingkat_keparahan'] ?? '') == 'Sedang' ? 'warning' : 'info')
                                        ?>">
                                            <?= $report['tingkat_keparahan'] ?? 'Ringan' ?>
                                        </span>
                                    </td>
                                    <td class="col-status">
                                        <span class="badge badge-<?=
                                            ($report['status'] ?? '') == 'Diverifikasi' ? 'success' :
                                            (($report['status'] ?? '') == 'Submitted' ? 'warning' :
                                            (($report['status'] ?? '') == 'Ditolak' ? 'danger' : 'secondary'))
                                        ?>">
                                            <?= $report['status'] ?? 'Draf' ?>
                                        </span>
                                    </td>
                                    <td class="col-pelapor" title="<?= htmlspecialchars($report['pelapor_nama'] ?? 'Unknown') ?>"><?= htmlspecialchars($report['pelapor_nama'] ?? 'Unknown') ?></td>
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

<script>
// Wait for Chart.js to load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh statistics every 60 seconds
    setInterval(function() {
        refreshDashboardStats();
    }, 60000);

    function refreshDashboardStats() {
        fetch('<?= BASE_URL ?>dashboard/getChartData?type=stats')
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data) {
                    updateStatCard('total_laporan', data.data.total_laporan);
                    updateStatCard('pending_verifikasi', data.data.pending_verifikasi);
                    updateStatCard('terverifikasi', data.data.terverifikasi);
                    updateStatCard('keparahan_berat', data.data.keparahan_berat);
                }
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }

    function updateStatCard(id, value) {
        // Find elements by content context since they don't have unique IDs
        // This is a temporary solution until we add IDs to the HTML
        const valueElement = document.querySelector(`.small-box h3[data-stat="${id}"]`);
        if(valueElement) {
            valueElement.textContent = value;
        }
    }

    // Monthly Chart
    const monthlyData = <?= json_encode($monthlyStats ?? []) ?>;
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Generate all 12 months with default 0
    const allMonths = [];
    const allValues = [];
    for (let i = 1; i <= 12; i++) {
        allMonths.push(monthNames[i - 1]);
        // Support both formats: bulan/total and month/total_reports
        const found = monthlyData.find(item => {
            const monthValue = parseInt(item.bulan || item.month);
            return monthValue === i;
        });
        const totalValue = found ? parseInt(found.total || found.total_reports || 0) : 0;
        allValues.push(totalValue);
    }
    
    const monthlyChartCanvas = document.getElementById('monthlyChart');
    if (monthlyChartCanvas && typeof Chart !== 'undefined') {
        new Chart(monthlyChartCanvas, {
            type: 'line',
            data: {
                labels: allMonths,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: allValues,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Top Pests Chart
    const topPestsData = <?= json_encode($topPests ?? []) ?>;
    
    if (topPestsData.length === 0) {
        // Show "No data" message
        const chartContainer = document.getElementById('topPestsChart').parentElement;
        if(chartContainer) {
            chartContainer.innerHTML = 
                '<div class="text-center text-muted p-4"><i class="fas fa-chart-bar fa-3x mb-3"></i><p>Belum ada data terverifikasi</p></div>';
        }
    } else {
        const pestLabels = topPestsData.map(item => item.nama_opt || 'Unknown');
        const pestValues = topPestsData.map(item => parseInt(item.total_laporan) || 0);
        
        const topPestsChartCanvas = document.getElementById('topPestsChart');
        if (topPestsChartCanvas && typeof Chart !== 'undefined') {
            new Chart(topPestsChartCanvas, {
                type: 'bar',
                data: {
                    labels: pestLabels,
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: pestValues,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' laporan';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
