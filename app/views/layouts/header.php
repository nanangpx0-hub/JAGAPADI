<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Dashboard' ?> - JAGAPADI</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/loading.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/responsive.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/filter-status.css">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>public/manifest.json">
    <meta name="theme-color" content="#28a745">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="JAGAPADI">
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgdmlld0JveD0iMCAwIDE5MiAxOTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxOTIiIGhlaWdodD0iMTkyIiByeD0iMzIiIGZpbGw9IiMyOGE3NDUiLz4KPHBhdGggZD0iTTQ4IDY0SDE0NFYxMjhINDhWNjRaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNNjQgODBIMTI4VjExMkg2NFY4MFoiIGZpbGw9IiMyOGE3NDUiLz4KPGNpcmNsZSBjeD0iOTYiIGN5PSI5NiIgcj0iOCIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg==">
    
    <style>
        .brand-text {
            font-weight: bold;
            color: #28a745 !important;
        }
        .navbar-success {
            background-color: #28a745 !important;
        }
        .main-header .navbar-nav .nav-link {
            color: white !important;
        }
        .sidebar-dark-success .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #28a745 !important;
        }
        #map { height: 500px; width: 100%; }
        
        /* Wilayah Dropdown Styles */
        #wilayahDropdown .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            padding: 1rem;
        }
        #wilayahDropdown .list-group-item {
            border: 1px solid #e0e0e0;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        #wilayahDropdown .list-group-item:hover {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        #wilayahDropdown .list-group-item:focus {
            outline: 2px solid #28a745;
            outline-offset: 2px;
        }
        #wilayahDropdown .btn-outline-light {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            #wilayahDropdown .dropdown-menu {
                min-width: 95vw !important;
                max-width: 95vw !important;
                left: 2.5vw !important;
            }
            #wilayahDropdown .d-flex {
                flex-direction: column !important;
            }
            #wilayahDropdown .flex-fill {
                min-width: 100% !important;
                margin-right: 0 !important;
                margin-bottom: 1rem;
            }
        }
        .wilayah-column-header {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Standardisasi tabel wilayah */
        .table.wilayah-table {
            font-size: 0.95rem;
            border-color: #dee2e6;
            min-width: 780px;
        }
        .table.wilayah-table th,
        .table.wilayah-table td {
            padding: 0.65rem 0.75rem;
            vertical-align: middle;
        }
        .table.wilayah-table thead.table-header th {
            background-color: #28a745;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-color: #28a745;
        }
        /* Pagination brand color */
        .pagination .page-item.active .page-link {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
        .pagination .page-link {
            color: #28a745;
        }
        .pagination .page-link:hover {
            color: #1e7e34;
        }
        .table.wilayah-table tbody tr:hover {
            background-color: #f8fff9;
        }
        .table.wilayah-table code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.9em;
            color: #343a40;
        }
        /* Alignment by data type */
        .table.wilayah-table td.text-right-numeric {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .table.wilayah-table td.text-center-date {
            text-align: center;
            white-space: nowrap;
        }
        .table.wilayah-table td.text-center-actions {
            text-align: center;
            white-space: nowrap;
        }
        /* Lebar kolom konsisten per modul */
        /* Kabupaten: #, Kode, Nama, Tanggal, Aksi */
        .table.wilayah-table.w-kabupaten thead th:nth-child(1),
        .table.wilayah-table.w-kabupaten tbody td:nth-child(1) { width: 5%; }
        .table.wilayah-table.w-kabupaten thead th:nth-child(2),
        .table.wilayah-table.w-kabupaten tbody td:nth-child(2) { width: 15%; }
        .table.wilayah-table.w-kabupaten thead th:nth-child(3),
        .table.wilayah-table.w-kabupaten tbody td:nth-child(3) { width: 45%; }
        .table.wilayah-table.w-kabupaten thead th:nth-child(4),
        .table.wilayah-table.w-kabupaten tbody td:nth-child(4) { width: 20%; }
        .table.wilayah-table.w-kabupaten thead th:nth-child(5),
        .table.wilayah-table.w-kabupaten tbody td:nth-child(5) { width: 15%; }
        /* Kecamatan: #, Kabupaten, Kode, Nama, Tanggal, Aksi */
        .table.wilayah-table.w-kecamatan thead th:nth-child(1),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(1) { width: 5%; }
        .table.wilayah-table.w-kecamatan thead th:nth-child(2),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(2) { width: 20%; }
        .table.wilayah-table.w-kecamatan thead th:nth-child(3),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(3) { width: 12%; }
        .table.wilayah-table.w-kecamatan thead th:nth-child(4),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(4) { width: 33%; }
        .table.wilayah-table.w-kecamatan thead th:nth-child(5),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(5) { width: 15%; }
        .table.wilayah-table.w-kecamatan thead th:nth-child(6),
        .table.wilayah-table.w-kecamatan tbody td:nth-child(6) { width: 15%; }
        /* Desa: #, Kecamatan, Kode, Nama, Kode Pos, Tanggal, Aksi */
        .table.wilayah-table.w-desa thead th:nth-child(1),
        .table.wilayah-table.w-desa tbody td:nth-child(1) { width: 5%; }
        .table.wilayah-table.w-desa thead th:nth-child(2),
        .table.wilayah-table.w-desa tbody td:nth-child(2) { width: 20%; }
        .table.wilayah-table.w-desa thead th:nth-child(3),
        .table.wilayah-table.w-desa tbody td:nth-child(3) { width: 12%; }
        .table.wilayah-table.w-desa thead th:nth-child(4),
        .table.wilayah-table.w-desa tbody td:nth-child(4) { width: 28%; }
        .table.wilayah-table.w-desa thead th:nth-child(5),
        .table.wilayah-table.w-desa tbody td:nth-child(5) { width: 8%; }
        .table.wilayah-table.w-desa thead th:nth-child(6),
        .table.wilayah-table.w-desa tbody td:nth-child(6) { width: 12%; }
        .table.wilayah-table.w-desa thead th:nth-child(7),
        .table.wilayah-table.w-desa tbody td:nth-child(7) { width: 15%; }
        /* Alignment by column index per modul */
        .table.wilayah-table.w-kabupaten tbody td:nth-child(2) { text-align: right; font-variant-numeric: tabular-nums; }
        .table.wilayah-table.w-kabupaten tbody td:nth-child(4) { text-align: center; white-space: nowrap; }
        .table.wilayah-table.w-kabupaten tbody td:nth-child(5) { text-align: center; white-space: nowrap; }
        .table.wilayah-table.w-kecamatan tbody td:nth-child(3) { text-align: right; font-variant-numeric: tabular-nums; }
        .table.wilayah-table.w-kecamatan tbody td:nth-child(5) { text-align: center; white-space: nowrap; }
        .table.wilayah-table.w-kecamatan tbody td:nth-child(6) { text-align: center; white-space: nowrap; }
        .table.wilayah-table.w-desa tbody td:nth-child(3) { text-align: right; font-variant-numeric: tabular-nums; }
        .table.wilayah-table.w-desa tbody td:nth-child(5) { text-align: right; font-variant-numeric: tabular-nums; }
        .table.wilayah-table.w-desa tbody td:nth-child(6) { text-align: center; white-space: nowrap; }
        .table.wilayah-table.w-desa tbody td:nth-child(7) { text-align: center; white-space: nowrap; }
        /* Header & cell text alignment for first column */
        .table.wilayah-table thead th:first-child,
        .table.wilayah-table tbody td:first-child { text-align: center; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-success navbar-dark">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL ?>" class="nav-link">Home</a>
            </li>
            
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>auth/logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <!-- Brand Logo -->
        <a href="<?= BASE_URL ?>" class="brand-link">
            <i class="fas fa-leaf brand-image" style="font-size: 2rem; margin-left: 10px;"></i>
            <span class="brand-text font-weight-light">JAGAPADI</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= $_SESSION['nama_lengkap'] ?? 'User' ?></a>
                    <small class="text-muted"><?= ucfirst($_SESSION['role'] ?? 'viewer') ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>dashboard" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>dashboard/map" class="nav-link">
                            <i class="nav-icon fas fa-map-marked-alt"></i>
                            <p>Peta Sebaran</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>dashboard/charts" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Grafik & Statistik</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>laporan" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan Hama</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>irigasi" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/irigasi') !== false) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-water"></i>
                            <p>Sebaran Irigasi</p>
                        </a>
                    </li>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>opt" class="nav-link">
                            <i class="nav-icon fas fa-bug"></i>
                            <p>Master Data OPT</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin'])): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>curahHujan" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/curahHujan') !== false) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-cloud-showers-heavy"></i>
                            <p>Curah Hujan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-map"></i>
                            <p>
                                Master Wilayah
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= BASE_URL ?>adminWilayah/kabupaten" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kabupaten</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= BASE_URL ?>adminWilayah/kecamatan" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kecamatan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= BASE_URL ?>adminWilayah/desa" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Desa</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>user" class="nav-link">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Manajemen User</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <li class="nav-header">EXPORT</li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>export/csv" class="nav-link">
                            <i class="nav-icon fas fa-file-csv"></i>
                            <p>Export CSV</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>export/excel" class="nav-link">
                            <i class="nav-icon fas fa-file-excel"></i>
                            <p>Export Excel</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>export/pdf" class="nav-link">
                            <i class="nav-icon fas fa-file-pdf"></i>
                            <p>Export PDF</p>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= $title ?? 'Dashboard' ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                            <li class="breadcrumb-item active"><?= $title ?? 'Dashboard' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-info-circle"></i> <?= $_SESSION['info']; unset($_SESSION['info']); ?>
                </div>
                <?php endif; ?>

                <script>
                // Wilayah Navigation Module
                (function() {
                    const cache = { kabupaten: null, kecamatan: {}, desa: {} };
                    const els = {
                        kab: document.getElementById('listKabupaten'),
                        kec: document.getElementById('listKecamatan'),
                        desa: document.getElementById('listDesa'),
                        load: document.getElementById('wilayahLoading'),
                        err: document.getElementById('wilayahError'),
                        search: document.getElementById('wilayahSearch')
                    };
                    
                    let selectedKabupaten = null;
                    let selectedKecamatan = null;
                    
                    async function fetchJSON(url) {
                        const response = await fetch(url);
                        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        return response.json();
                    }
                    
                    function showLoading(show) {
                        if (els.load) els.load.style.display = show ? 'block' : 'none';
                    }
                    
                    function showError(message) {
                        if (els.err) {
                            els.err.textContent = message || '';
                            els.err.style.display = message ? 'block' : 'none';
                        }
                    }
                    
                    function createListItem(label, value, onClick, metadata = {}) {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = label;
                        item.setAttribute('role', 'menuitem');
                        item.setAttribute('data-id', value);
                        item.tabIndex = 0;
                        
                        if (metadata.kode) {
                            item.title = `Kode: ${metadata.kode}`;
                        }
                        
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            onClick(value, label);
                        });
                        
                        item.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                onClick(value, label);
                            }
                        });
                        
                        return item;
                    }
                    
                    async function loadKabupaten() {
                        try {
                            showLoading(true);
                            showError('');
                            
                            let data = cache.kabupaten;
                            if (!data) {
                                const result = await fetchJSON('<?= BASE_URL ?>wilayah/kabupaten');
                                data = result.data;
                                cache.kabupaten = data;
                            }
                            
                            els.kab.innerHTML = '';
                            
                            if (data.length === 0) {
                                els.kab.innerHTML = '<small class="text-muted d-block p-2 text-center">Tidak ada data</small>';
                                return;
                            }
                            
                            data.forEach(row => {
                                const item = createListItem(
                                    row.nama_kabupaten,
                                    row.id,
                                    (id, nama) => selectKabupaten(id, nama),
                                    { kode: row.kode_kabupaten }
                                );
                                els.kab.appendChild(item);
                            });
                        } catch (error) {
                            console.error('Error loading kabupaten:', error);
                            showError('Gagal memuat data kabupaten. Silakan coba lagi.');
                        } finally {
                            showLoading(false);
                        }
                    }
                    
                    async function selectKabupaten(id, nama) {
                        try {
                            showLoading(true);
                            showError('');
                            selectedKabupaten = { id, nama };
                            
                            // Highlight selected
                            Array.from(els.kab.children).forEach(c => c.classList.remove('active'));
                            const selected = els.kab.querySelector(`[data-id="${id}"]`);
                            if (selected) selected.classList.add('active');
                            
                            // Clear subsequent levels
                            els.kec.innerHTML = '';
                            els.desa.innerHTML = '<small class="text-muted d-block p-2 text-center">Pilih kecamatan terlebih dahulu</small>';
                            selectedKecamatan = null;
                            
                            const key = String(id);
                            let data = cache.kecamatan[key];
                            
                            if (!data) {
                                const result = await fetchJSON('<?= BASE_URL ?>wilayah/kecamatan/' + id);
                                data = result.data;
                                cache.kecamatan[key] = data;
                            }
                            
                            if (data.length === 0) {
                                els.kec.innerHTML = '<small class="text-muted d-block p-2 text-center">Tidak ada kecamatan</small>';
                                return;
                            }
                            
                            data.forEach(row => {
                                const item = createListItem(
                                    row.nama_kecamatan,
                                    row.id,
                                    (kid, knama) => selectKecamatan(kid, knama),
                                    { kode: row.kode_kecamatan }
                                );
                                els.kec.appendChild(item);
                            });
                        } catch (error) {
                            console.error('Error loading kecamatan:', error);
                            showError('Gagal memuat data kecamatan. Silakan coba lagi.');
                        } finally {
                            showLoading(false);
                        }
                    }
                    
                    async function selectKecamatan(id, nama) {
                        try {
                            showLoading(true);
                            showError('');
                            selectedKecamatan = { id, nama };
                            
                            // Highlight selected
                            Array.from(els.kec.children).forEach(c => c.classList.remove('active'));
                            const selected = els.kec.querySelector(`[data-id="${id}"]`);
                            if (selected) selected.classList.add('active');
                            
                            els.desa.innerHTML = '';
                            
                            const key = String(id);
                            let data = cache.desa[key];
                            
                            if (!data) {
                                const result = await fetchJSON('<?= BASE_URL ?>wilayah/desa/' + id);
                                data = result.data;
                                cache.desa[key] = data;
                            }
                            
                            if (data.length === 0) {
                                els.desa.innerHTML = '<small class="text-muted d-block p-2 text-center">Tidak ada desa</small>';
                                return;
                            }
                            
                            data.forEach(row => {
                                const item = createListItem(
                                    row.nama_desa,
                                    row.id,
                                    (did, dnama) => selectDesa(did, dnama),
                                    { kode: row.kode_desa }
                                );
                                els.desa.appendChild(item);
                            });
                        } catch (error) {
                            console.error('Error loading desa:', error);
                            showError('Gagal memuat data desa. Silakan coba lagi.');
                        } finally {
                            showLoading(false);
                        }
                    }
                    
                    function selectDesa(id, nama) {
                        // Highlight selected
                        Array.from(els.desa.children).forEach(c => c.classList.remove('active'));
                        const selected = els.desa.querySelector(`[data-id="${id}"]`);
                        if (selected) selected.classList.add('active');
                        
                        // Can be used for navigation or filtering
                        console.log('Selected wilayah:', {
                            kabupaten: selectedKabupaten,
                            kecamatan: selectedKecamatan,
                            desa: { id, nama }
                        });
                    }
                    
                    function filterLists(query) {
                        const q = query.toLowerCase().trim();
                        
                        [els.kab, els.kec, els.desa].forEach(list => {
                            if (!list) return;
                            
                            let visibleCount = 0;
                            Array.from(list.children).forEach(child => {
                                if (child.tagName === 'A') {
                                    const text = child.textContent.toLowerCase();
                                    const matches = text.includes(q);
                                    child.style.display = matches ? '' : 'none';
                                    if (matches) visibleCount++;
                                }
                            });
                            
                            // Show "no results" message if needed
                            if (q && visibleCount === 0 && list.children.length > 0) {
                                const noResult = list.querySelector('.no-results');
                                if (!noResult) {
                                    const msg = document.createElement('small');
                                    msg.className = 'text-muted d-block p-2 text-center no-results';
                                    msg.textContent = 'Tidak ada hasil';
                                    list.appendChild(msg);
                                }
                            } else {
                                const noResult = list.querySelector('.no-results');
                                if (noResult) noResult.remove();
                            }
                        });
                    }
                    
                    // Debounced search
                    let searchTimer = null;
                    if (els.search) {
                        els.search.addEventListener('input', (e) => {
                            const value = e.target.value;
                            clearTimeout(searchTimer);
                            searchTimer = setTimeout(() => filterLists(value), 200);
                        });
                    }
                    
                    // Load kabupaten on dropdown open
                    const dropdownToggle = document.getElementById('wilayahMenuToggle');
                    if (dropdownToggle) {
                        dropdownToggle.addEventListener('click', () => {
                            if (!cache.kabupaten) {
                                loadKabupaten();
                            }
                        });
                    }
                    
                    // Expose to global for form integration if needed
                    window.WilayahNav = {
                        getSelected: () => ({
                            kabupaten: selectedKabupaten,
                            kecamatan: selectedKecamatan
                        }),
                        reload: loadKabupaten
                    };
                })();
                </script>
