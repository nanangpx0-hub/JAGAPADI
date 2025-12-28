<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
.chart-container {
    position: relative;
    height: 400px;
    margin-bottom: 20px;
}

.chart-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 10;
}

.data-integrity-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.data-integrity-badge.success {
    background-color: #d4edda;
    color: #155724;
}

.data-integrity-badge.warning {
    background-color: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .chart-container {
        height: 300px;
    }
}
</style>

<!-- Monthly Trends Chart -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Tren Laporan Bulanan <?= $year ?? date('Y') ?></h3>
                <div class="card-tools">
                    <span class="data-integrity-badge success" id="monthly-integrity">
                        <i class="fas fa-check-circle"></i> Data Valid
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-loading" id="monthly-loading" style="display:none;">
                        <div class="spinner-border text-primary"></div>
                        <p>Memuat data...</p>
                    </div>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Pests and Area Charts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 10 OPT</h3>
                <div class="card-tools">
                    <span class="data-integrity-badge success" id="pests-integrity">
                        <i class="fas fa-check-circle"></i> Data Valid
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topPestsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-area"></i> Luas Serangan Bulanan</h3>
                <div class="card-tools">
                    <span class="data-integrity-badge success" id="area-integrity">
                        <i class="fas fa-check-circle"></i> Data Valid
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="areaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Kecamatan Statistics -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Top 5 Kecamatan dengan Serangan Tertinggi</h3>
                <div class="card-tools">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-primary active" id="btn-count" onclick="updateKecamatanChart('count')">
                            <input type="radio" name="options" autocomplete="off" checked> 
                            <i class="fas fa-list-ol"></i> Jumlah Laporan
                        </label>
                        <label class="btn btn-sm btn-primary" id="btn-area" onclick="updateKecamatanChart('area')">
                            <input type="radio" name="options" autocomplete="off"> 
                            <i class="fas fa-ruler-combined"></i> Luas Serangan
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="kecamatanChart"></canvas>
                </div>
                <div class="text-muted text-center small mt-2">
                    <i class="fas fa-info-circle"></i> Klik tombol di atas untuk mengganti mode tampilan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Severity Distribution -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribusi Tingkat Keparahan</h3>
                <div class="card-tools">
                    <span class="data-integrity-badge success" id="severity-integrity">
                        <i class="fas fa-check-circle"></i> Data Valid
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="severityChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tingkat Keparahan</th>
                                        <th>Jumlah</th>
                                        <th>Total Luas (Ha)</th>
                                        <th>Rata-rata Populasi</th>
                                    </tr>
                                </thead>
                                <tbody id="severity-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let charts = {};
let chartData = {
    monthly: <?= json_encode($monthlyStats ?? []) ?>,
    topPests: <?= json_encode($topPests ?? []) ?>,
    severity: <?= json_encode($severityStats ?? []) ?>,
    severity: <?= json_encode($severityStats ?? []) ?>,
    area: <?= json_encode($areaStats ?? []) ?>,
    topKecamatan: <?= json_encode($topKecamatan ?? []) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        alert('Chart.js tidak berhasil dimuat. Silakan refresh halaman.');
        console.error('Chart.js not loaded');
        return;
    }
    
    console.log('Initializing charts...');
    console.log('Data:', chartData);
    
    try {
        initMonthlyChart();
        initTopPestsChart();
        initAreaChart();

        initSeverityChart();
        initKecamatanChart();
        console.log('All charts initialized successfully');
    } catch (error) {
        console.error('Chart initialization error:', error);
        alert('Error: ' + error.message);
    }
});

function initMonthlyChart() {
    const canvas = document.getElementById('monthlyChart');
    if (!canvas) {
        console.error('Monthly chart canvas not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    const totalData = chartData.monthly.map(item => item.total || 0);
    const verifiedData = chartData.monthly.map(item => item.terverifikasi || 0);
    
    charts.monthly = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthNames,
            datasets: [
                {
                    label: 'Total Laporan',
                    data: totalData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Terverifikasi',
                    data: verifiedData,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Perbandingan Total Laporan vs Terverifikasi',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    
    console.log('Monthly chart created');
}

function initTopPestsChart() {
    const canvas = document.getElementById('topPestsChart');
    if (!canvas) {
        console.error('Top pests chart canvas not found');
        return;
    }
    
    if (chartData.topPests.length === 0) {
        canvas.parentElement.innerHTML = '<div class="text-center text-muted p-5"><i class="fas fa-chart-bar fa-4x mb-3"></i><p class="lead">Belum ada data OPT terverifikasi</p></div>';
        return;
    }
    
    const ctx = canvas.getContext('2d');
    const labels = chartData.topPests.map(item => item.nama_opt || 'Unknown');
    const data = chartData.topPests.map(item => parseInt(item.total_laporan) || 0);
    
    const colors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(199, 199, 199, 0.8)',
        'rgba(83, 102, 255, 0.8)',
        'rgba(255, 99, 255, 0.8)',
        'rgba(99, 255, 132, 0.8)'
    ];
    
    charts.topPests = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length).map(c => c.replace('0.8', '1')),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'OPT dengan Laporan Terbanyak',
                    font: { size: 16 }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    
    console.log('Top pests chart created');
}

function initAreaChart() {
    const canvas = document.getElementById('areaChart');
    if (!canvas) {
        console.error('Area chart canvas not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const data = chartData.area.map(item => item.total_luas || 0);
    
    charts.area = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'Luas Serangan (Ha)',
                data: data,
                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Total Luas Serangan per Bulan',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    console.log('Area chart created');
}

function initSeverityChart() {
    const canvas = document.getElementById('severityChart');
    if (!canvas) {
        console.error('Severity chart canvas not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    const labels = chartData.severity.map(item => item.tingkat_keparahan);
    const data = chartData.severity.map(item => item.total);
    
    charts.severity = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Distribusi Berdasarkan Tingkat Keparahan',
                    font: { size: 16 }
                }
            }
        }
    });
    
    updateSeverityTable();
    console.log('Severity chart created');
}

function initKecamatanChart() {
    const canvas = document.getElementById('kecamatanChart');
    if (!canvas) {
        console.error('Kecamatan chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    
    // Initial data (Default: by_count)
    const dataCount = chartData.topKecamatan.by_count || [];
    
    if (dataCount.length === 0) {
        canvas.parentElement.innerHTML = '<div class="text-center text-muted p-5"><i class="fas fa-map-marked-alt fa-4x mb-3"></i><p class="lead">Belum ada data wilayah</p></div>';
        return;
    }

    const labels = dataCount.map(item => item.nama_kecamatan);
    const data = dataCount.map(item => parseInt(item.total_laporan));

    charts.kecamatan = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Laporan'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    console.log('Kecamatan chart created');
}

function updateKecamatanChart(type) {
    if (!charts.kecamatan) return;

    // Update active button style manual fallback if bootstrap js fails
    document.getElementById('btn-count').classList.remove('active');
    document.getElementById('btn-area').classList.remove('active');
    document.getElementById('btn-' + type).classList.add('active');

    let newData, newLabels, newLabel, newColor, newBorderColor, yTitle;

    if (type === 'area') {
        const rawData = chartData.topKecamatan.by_area || [];
        newLabels = rawData.map(item => item.nama_kecamatan);
        newData = rawData.map(item => parseFloat(item.total_luas));
        newLabel = 'Luas Serangan (Ha)';
        newColor = 'rgba(255, 99, 132, 0.7)'; // Red/Pinkish for area
        newBorderColor = 'rgba(255, 99, 132, 1)';
        yTitle = 'Luas Area (Ha)';
    } else {
        const rawData = chartData.topKecamatan.by_count || [];
        newLabels = rawData.map(item => item.nama_kecamatan);
        newData = rawData.map(item => parseInt(item.total_laporan));
        newLabel = 'Jumlah Laporan';
        newColor = 'rgba(54, 162, 235, 0.7)'; // Blue for count
        newBorderColor = 'rgba(54, 162, 235, 1)';
        yTitle = 'Jumlah Laporan';
    }

    // Update Chart
    charts.kecamatan.data.labels = newLabels;
    charts.kecamatan.data.datasets[0].data = newData;
    charts.kecamatan.data.datasets[0].label = newLabel;
    charts.kecamatan.data.datasets[0].backgroundColor = newColor;
    charts.kecamatan.data.datasets[0].borderColor = newBorderColor;
    
    // Update Scale Title
    if (charts.kecamatan.options.scales.y.title) {
        charts.kecamatan.options.scales.y.title.text = yTitle;
    }

    charts.kecamatan.update();
}

function updateSeverityTable() {
    const tbody = document.getElementById('severity-table-body');
    if (!tbody) return;
    
    if (chartData.severity.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>';
        return;
    }
    
    let html = '';
    chartData.severity.forEach(item => {
        const colorClass = item.tingkat_keparahan === 'Ringan' ? 'success' : 
                          item.tingkat_keparahan === 'Sedang' ? 'warning' : 'danger';
        
        html += `
            <tr>
                <td><span class="badge badge-${colorClass}">${item.tingkat_keparahan}</span></td>
                <td>${item.total}</td>
                <td>${item.total_luas.toFixed(2)}</td>
                <td>${item.avg_populasi.toFixed(2)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log('Charts page loaded in ' + loadTime.toFixed(2) + 'ms');
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
