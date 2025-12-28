<?php
$title = 'Manajemen Desa';
require_once __DIR__ . '/../../../layouts/header.php';
?>
            <!-- Alert Messages -->
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

            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Desa</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="<?= BASE_URL ?>adminWilayah/desa_create" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Desa
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter & Search Form (AJAX-driven) -->
                    <div class="filter-container mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-map-marker-alt mr-1"></i> Filter Kabupaten:</label>
                                    <select name="kabupaten_id" id="filter_kabupaten" class="form-control">
                                        <option value="">-- Semua Kabupaten --</option>
                                        <?php foreach ($data['kabupaten_list'] as $kab): ?>
                                            <option value="<?= $kab['id'] ?>" <?= ($data['selected_kabupaten'] ?? '') == $kab['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kab['nama_kabupaten']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-building mr-1"></i> Filter Kecamatan:</label>
                                    <select name="kecamatan_id" id="filter_kecamatan" class="form-control">
                                        <option value="">-- Semua Kecamatan --</option>
                                        <?php if (!empty($data['kecamatan_list'])): ?>
                                            <?php foreach ($data['kecamatan_list'] as $kec): ?>
                                                <option value="<?= $kec['id'] ?>" <?= ($data['selected_kecamatan'] ?? '') == $kec['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kec['nama_kecamatan']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label><i class="fas fa-search mr-1"></i> Pencarian:</label>
                                <div class="input-group">
                                    <div class="position-relative flex-grow-1">
                                        <input type="text" id="search_input" class="form-control" 
                                               placeholder="Cari nama desa, kode desa, atau kode pos..." 
                                               value="<?= htmlspecialchars($data['search'] ?? '') ?>"
                                               autocomplete="off">
                                        <div id="autocomplete_dropdown" class="autocomplete-dropdown"></div>
                                    </div>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="btn_search">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <button class="btn btn-secondary" type="button" id="btn_reset" 
                                                style="<?= (empty($data['search']) && empty($data['selected_kabupaten']) && empty($data['selected_kecamatan'])) ? 'display:none;' : '' ?>">
                                            <i class="fas fa-times"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Filter Status Info -->
                        <div id="filter_status" class="mt-2" style="display: none;">
                            <small class="text-muted">
                                <i class="fas fa-filter mr-1"></i>
                                <span id="filter_info"></span>
                            </small>
                        </div>
                    </div>

                    <!-- Loading Overlay -->
                    <div id="loading_overlay" class="loading-overlay" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Memuat...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive" id="table_container">
                        <table class="table table-bordered table-striped table-hover" id="desa_table">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="12%" class="sortable-header" data-sort="kode_kecamatan">
                                        Kode Kecamatan
                                        <span class="sort-icon">
                                            <?php if (($data['sort_by'] ?? 'kode_kecamatan') === 'kode_kecamatan'): ?>
                                                <i class="fas fa-sort-<?= ($data['sort_dir'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort text-muted"></i>
                                            <?php endif; ?>
                                        </span>
                                    </th>
                                    <th width="15%" class="sortable-header" data-sort="nama_kecamatan">
                                        Kecamatan
                                        <span class="sort-icon">
                                            <?php if (($data['sort_by'] ?? '') === 'nama_kecamatan'): ?>
                                                <i class="fas fa-sort-<?= ($data['sort_dir'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort text-muted"></i>
                                            <?php endif; ?>
                                        </span>
                                    </th>
                                    <th width="12%" class="sortable-header" data-sort="kode_desa">
                                        Kode Desa
                                        <span class="sort-icon">
                                            <?php if (($data['sort_by'] ?? '') === 'kode_desa'): ?>
                                                <i class="fas fa-sort-<?= ($data['sort_dir'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort text-muted"></i>
                                            <?php endif; ?>
                                        </span>
                                    </th>
                                    <th width="20%" class="sortable-header" data-sort="nama_desa">
                                        Nama Desa
                                        <span class="sort-icon">
                                            <?php if (($data['sort_by'] ?? '') === 'nama_desa'): ?>
                                                <i class="fas fa-sort-<?= ($data['sort_dir'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort text-muted"></i>
                                            <?php endif; ?>
                                        </span>
                                    </th>
                                    <th width="10%">Kode Pos</th>
                                    <th width="10%" class="text-center">Kabupaten</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="desa_tbody">
                                <?php if (empty($data['desa'])): ?>
                                    <tr class="no-data-row">
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="mb-0">Tidak ada data desa<?= !empty($data['search']) ? ' yang cocok dengan pencarian' : '' ?></p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = ($data['page'] - 1) * $data['limit'] + 1;
                                    foreach ($data['desa'] as $row): 
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><code class="text-primary"><?= htmlspecialchars($row['kode_kecamatan'] ?? '') ?></code></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($row['nama_kecamatan'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td><code class="text-success"><?= htmlspecialchars($row['kode_desa'] ?? '') ?></code></td>
                                            <td><strong><?= htmlspecialchars($row['nama_desa'] ?? '') ?></strong></td>
                                            <td><?= htmlspecialchars($row['kode_pos'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($row['nama_kabupaten'] ?? '') ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= BASE_URL ?>adminWilayah/desa_edit/<?= $row['id'] ?>" 
                                                       class="btn btn-info" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-delete" 
                                                            data-id="<?= $row['id'] ?>"
                                                            data-name="<?= htmlspecialchars($row['nama_desa'] ?? '') ?>"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination_container">
                        <?php if ($data['total'] > $data['limit']): ?>
                            <div class="mt-3">
                                <?php
                                $totalPages = ceil($data['total'] / $data['limit']);
                                $currentPage = $data['page'];
                                ?>
                                
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0" id="pagination_list">
                                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $currentPage - 1 ?>">
                                                <i class="fas fa-chevron-left"></i> Prev
                                            </a>
                                        </li>

                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $currentPage + 1 ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>

                                <div class="text-center mt-2">
                                    <small class="text-muted" id="pagination_info">
                                        Menampilkan <?= ($currentPage - 1) * $data['limit'] + 1 ?> - 
                                        <?= min($currentPage * $data['limit'], $data['total']) ?> 
                                        dari <?= $data['total'] ?> data
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus desa:</p>
                <p class="font-weight-bold text-center" id="deleteName"></p>
                <p class="text-muted"><small>Data tidak akan dihapus permanen (soft delete)</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Loading Overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
    min-height: 200px;
}

#table_container {
    position: relative;
    min-height: 200px;
}

/* Search Highlight */
.search-highlight, mark.search-highlight {
    background-color: #fff3cd;
    padding: 0.1em 0.2em;
    border-radius: 2px;
    font-weight: 600;
}

/* Autocomplete Dropdown */
.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.autocomplete-dropdown.show {
    display: block;
}

.autocomplete-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background 0.15s;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item.active {
    background-color: #f8f9fa;
}

.autocomplete-item .desa-name {
    font-weight: 600;
    color: #333;
}

.autocomplete-item .desa-location {
    font-size: 0.85em;
    color: #666;
}

.autocomplete-item .desa-code {
    font-size: 0.8em;
    color: #999;
}

/* Filter status badge */
.filter-badge {
    display: inline-block;
    padding: 0.25em 0.5em;
    background: #e9ecef;
    border-radius: 4px;
    margin-right: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-container .row > div {
        margin-bottom: 10px;
    }
    
    .input-group-append {
        flex-wrap: wrap;
    }
    
    .input-group-append .btn {
        flex: 1;
    }
}

/* Sortable column headers */
.sortable-header {
    cursor: pointer;
    user-select: none;
    position: relative;
    white-space: nowrap;
    transition: background-color 0.2s ease;
}

.sortable-header:hover {
    background-color: #495057 !important;
}

.sortable-header .sort-icon {
    margin-left: 5px;
    opacity: 0.7;
}

.sortable-header:hover .sort-icon {
    opacity: 1;
}

.sortable-header.active {
    background-color: #495057 !important;
}

.sortable-header.active .sort-icon {
    opacity: 1;
    color: #17a2b8;
}

/* Better code display */
code.text-primary {
    background-color: rgba(0, 123, 255, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
}

code.text-success {
    background-color: rgba(40, 167, 69, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
}

/* Table responsive enhancements */
@media (max-width: 992px) {
    #desa_table th, #desa_table td {
        font-size: 0.85rem;
        padding: 0.5rem;
    }
    
    .sortable-header {
        font-size: 0.8rem;
    }
}

/* Sticky header for better scrolling */
#table_container {
    max-height: 70vh;
    overflow-y: auto;
}

#desa_table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = '<?= BASE_URL ?>';
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    // State management
    let currentRequestId = 0;
    let debounceTimer = null;
    let autocompleteTimer = null;
    let currentPage = <?= (int)$data['page'] ?>;
    
    // Sort state - load from localStorage or use defaults
    const SORT_STORAGE_KEY = 'desa_table_sort';
    let currentSort = loadSortPreference();
    
    function loadSortPreference() {
        const saved = localStorage.getItem(SORT_STORAGE_KEY);
        if (saved) {
            try {
                return JSON.parse(saved);
            } catch (e) {
                return { by: 'kode_kecamatan', dir: 'asc' };
            }
        }
        return { 
            by: '<?= $data['sort_by'] ?? 'kode_kecamatan' ?>', 
            dir: '<?= $data['sort_dir'] ?? 'asc' ?>' 
        };
    }
    
    function saveSortPreference() {
        localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify(currentSort));
    }
    
    // DOM Elements
    const kabSelect = document.getElementById('filter_kabupaten');
    const kecSelect = document.getElementById('filter_kecamatan');
    const searchInput = document.getElementById('search_input');
    const btnSearch = document.getElementById('btn_search');
    const btnReset = document.getElementById('btn_reset');
    const loadingOverlay = document.getElementById('loading_overlay');
    const tableContainer = document.getElementById('table_container');
    const desaTbody = document.getElementById('desa_tbody');
    const autocompleteDropdown = document.getElementById('autocomplete_dropdown');
    const filterStatus = document.getElementById('filter_status');
    const filterInfo = document.getElementById('filter_info');
    const sortableHeaders = document.querySelectorAll('.sortable-header');
    
    
    // Show/Hide loading
    function showLoading() {
        loadingOverlay.style.display = 'flex';
    }
    
    function hideLoading() {
        loadingOverlay.style.display = 'none';
    }
    
    // Generate request ID for race condition handling
    function getRequestId() {
        return ++currentRequestId;
    }
    
    // Fetch kecamatan list when kabupaten changes
    async function fetchKecamatan(kabupatenId) {
        kecSelect.innerHTML = '<option value="">Memuat...</option>';
        kecSelect.disabled = true;
        
        if (!kabupatenId) {
            kecSelect.innerHTML = '<option value="">-- Semua Kecamatan --</option>';
            kecSelect.disabled = false;
            return;
        }
        
        try {
            const response = await fetch(`${BASE_URL}adminWilayah/get_kecamatan_by_kabupaten/${kabupatenId}`);
            const data = await response.json();
            
            let options = '<option value="">-- Semua Kecamatan --</option>';
            if (data.success && data.data) {
                data.data.forEach(kec => {
                    options += `<option value="${kec.id}">${escapeHtml(kec.nama_kecamatan)}</option>`;
                });
            }
            kecSelect.innerHTML = options;
        } catch (err) {
            console.error('Error loading kecamatan:', err);
            kecSelect.innerHTML = '<option value="">Error memuat data</option>';
        } finally {
            kecSelect.disabled = false;
        }
    }
    
    // Fetch desa data with filters
    async function fetchDesaData(page = 1) {
        const requestId = getRequestId();
        showLoading();
        
        const kabupatenId = kabSelect.value;
        const kecamatanId = kecSelect.value;
        const search = searchInput.value.trim();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            request_id: requestId,
            sort_by: currentSort.by,
            sort_dir: currentSort.dir
        });
        
        if (kabupatenId) params.append('kabupaten_id', kabupatenId);
        if (kecamatanId) params.append('kecamatan_id', kecamatanId);
        if (search) params.append('search', search);
        
        try {
            const response = await fetch(`${BASE_URL}adminWilayah/desa_api?${params.toString()}`);
            const result = await response.json();
            
            // Check for race condition
            if (result.request_id && parseInt(result.request_id) !== currentRequestId) {
                return; // Ignore stale response
            }
            
            if (result.success) {
                renderTable(result.data, result.pagination, search);
                renderPagination(result.pagination);
                updateFilterStatus(kabupatenId, kecamatanId, search, result.pagination.total);
                updateURL(kabupatenId, kecamatanId, search, page);
                currentPage = page;
            } else {
                showNoData(result.error || 'Terjadi kesalahan');
            }
        } catch (err) {
            console.error('Error fetching desa:', err);
            showNoData('Terjadi kesalahan saat memuat data');
        } finally {
            hideLoading();
        }
    }
    
    // Render table rows
    function renderTable(data, pagination, searchTerm) {
        if (!data || data.length === 0) {
            showNoData('Tidak ada data desa' + (searchTerm ? ' yang cocok dengan pencarian' : ''));
            return;
        }
        
        const startNo = (pagination.page - 1) * pagination.limit + 1;
        let html = '';
        
        data.forEach((row, index) => {
            const namaDesa = searchTerm && row.nama_desa_highlighted 
                ? row.nama_desa_highlighted 
                : escapeHtml(row.nama_desa || '');
            const kodeDesa = searchTerm && row.kode_desa_highlighted 
                ? row.kode_desa_highlighted 
                : escapeHtml(row.kode_desa || '');
            
            html += `
                <tr>
                    <td class="text-center">${startNo + index}</td>
                    <td>
                        <span class="badge badge-info">${escapeHtml(row.nama_kecamatan || 'N/A')}</span>
                        <br>
                        <small class="text-muted">${escapeHtml(row.nama_kabupaten || '')}</small>
                    </td>
                    <td><code>${kodeDesa}</code></td>
                    <td><strong>${namaDesa}</strong></td>
                    <td>${escapeHtml(row.kode_pos || '-')}</td>
                    <td class="text-center">
                        <small class="text-muted">${formatDate(row.created_at)}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="${BASE_URL}adminWilayah/desa_edit/${row.id}" 
                               class="btn btn-info" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-delete" 
                                    data-id="${row.id}"
                                    data-name="${escapeHtml(row.nama_desa || '')}"
                                    title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        desaTbody.innerHTML = html;
        attachDeleteHandlers();
    }
    
    // Show no data message
    function showNoData(message) {
        desaTbody.innerHTML = `
            <tr class="no-data-row">
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">${escapeHtml(message)}</p>
                </td>
            </tr>
        `;
    }
    
    // Render pagination
    function renderPagination(pagination) {
        const container = document.getElementById('pagination_container');
        
        if (pagination.total <= pagination.limit) {
            container.innerHTML = '';
            return;
        }
        
        const totalPages = pagination.total_pages;
        const currentPage = pagination.page;
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        let paginationHtml = `
            <div class="mt-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0" id="pagination_list">
                        <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        </li>
        `;
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        paginationHtml += `
                        <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Menampilkan ${(currentPage - 1) * pagination.limit + 1} - 
                        ${Math.min(currentPage * pagination.limit, pagination.total)} 
                        dari ${pagination.total} data
                    </small>
                </div>
            </div>
        `;
        
        container.innerHTML = paginationHtml;
        attachPaginationHandlers();
    }
    
    // Update filter status display
    function updateFilterStatus(kabupatenId, kecamatanId, search, total) {
        const parts = [];
        
        if (kabupatenId) {
            const kabName = kabSelect.options[kabSelect.selectedIndex].text;
            parts.push(`<span class="filter-badge">Kabupaten: ${escapeHtml(kabName)}</span>`);
        }
        
        if (kecamatanId) {
            const kecName = kecSelect.options[kecSelect.selectedIndex].text;
            parts.push(`<span class="filter-badge">Kecamatan: ${escapeHtml(kecName)}</span>`);
        }
        
        if (search) {
            parts.push(`<span class="filter-badge">Pencarian: "${escapeHtml(search)}"</span>`);
        }
        
        if (parts.length > 0) {
            filterInfo.innerHTML = parts.join(' ') + ` - ${total} hasil ditemukan`;
            filterStatus.style.display = 'block';
            btnReset.style.display = 'inline-block';
        } else {
            filterStatus.style.display = 'none';
            btnReset.style.display = 'none';
        }
    }
    
    // Update URL without reload (for bookmarking/sharing)
    function updateURL(kabupatenId, kecamatanId, search, page) {
        const params = new URLSearchParams();
        if (kabupatenId) params.set('kabupaten_id', kabupatenId);
        if (kecamatanId) params.set('kecamatan_id', kecamatanId);
        if (search) params.set('search', search);
        if (page > 1) params.set('page', page);
        if (currentSort.by !== 'kode_kecamatan') params.set('sort_by', currentSort.by);
        if (currentSort.dir !== 'asc') params.set('sort_dir', currentSort.dir);
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState({}, '', newUrl);
    }
    
    // Wrapper function for sort handler
    function updateUrl() {
        const kabupatenId = kabSelect.value;
        const kecamatanId = kecSelect.value;
        const search = searchInput.value.trim();
        updateURL(kabupatenId, kecamatanId, search, currentPage);
    }
    
    // Fetch autocomplete suggestions
    async function fetchAutocompleteSuggestions(query) {
        if (query.length < 2) {
            hideAutocomplete();
            return;
        }
        
        const kabupatenId = kabSelect.value;
        const kecamatanId = kecSelect.value;
        
        const params = new URLSearchParams({ q: query });
        if (kabupatenId) params.append('kabupaten_id', kabupatenId);
        if (kecamatanId) params.append('kecamatan_id', kecamatanId);
        
        try {
            const response = await fetch(`${BASE_URL}adminWilayah/desa_autocomplete?${params.toString()}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                showAutocomplete(result.data);
            } else {
                hideAutocomplete();
            }
        } catch (err) {
            console.error('Autocomplete error:', err);
            hideAutocomplete();
        }
    }
    
    // Show autocomplete dropdown
    function showAutocomplete(suggestions) {
        let html = '';
        suggestions.forEach((item, index) => {
            html += `
                <div class="autocomplete-item" data-value="${escapeHtml(item.value)}" data-index="${index}">
                    <div class="desa-name">${escapeHtml(item.value)}</div>
                    <div class="desa-location">${escapeHtml(item.nama_kecamatan || '')}, ${escapeHtml(item.nama_kabupaten || '')}</div>
                    <div class="desa-code">Kode: ${escapeHtml(item.kode_desa || '-')}</div>
                </div>
            `;
        });
        autocompleteDropdown.innerHTML = html;
        autocompleteDropdown.classList.add('show');
        
        // Add click handlers
        document.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.dataset.value;
                hideAutocomplete();
                fetchDesaData(1);
            });
        });
    }
    
    // Hide autocomplete dropdown
    function hideAutocomplete() {
        autocompleteDropdown.classList.remove('show');
        autocompleteDropdown.innerHTML = '';
    }
    
    // Attach pagination click handlers
    function attachPaginationHandlers() {
        document.querySelectorAll('#pagination_list .page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.parentElement.classList.contains('disabled')) return;
                const page = parseInt(this.dataset.page);
                if (page && page > 0) {
                    fetchDesaData(page);
                }
            });
        });
    }
    
    // Attach delete handlers
    function attachDeleteHandlers() {
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteId = this.dataset.id;
                document.getElementById('deleteName').textContent = this.dataset.name;
                $('#deleteModal').modal('show');
            });
        });
    }
    
    // Helper functions
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    
    // Event Listeners
    
    // Kabupaten change
    kabSelect.addEventListener('change', async function() {
        const kabId = this.value;
        searchInput.value = ''; // Reset search
        kecSelect.value = ''; // Reset kecamatan selection
        
        await fetchKecamatan(kabId);
        fetchDesaData(1);
    });
    
    // Kecamatan change
    kecSelect.addEventListener('change', function() {
        searchInput.value = ''; // Reset search
        fetchDesaData(1);
    });
    
    // Search input with debounce
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Autocomplete with debounce
        clearTimeout(autocompleteTimer);
        autocompleteTimer = setTimeout(() => {
            fetchAutocompleteSuggestions(query);
        }, 200);
    });
    
    // Search on Enter key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            hideAutocomplete();
            fetchDesaData(1);
        } else if (e.key === 'Escape') {
            hideAutocomplete();
        }
    });
    
    // Search button click
    btnSearch.addEventListener('click', function() {
        hideAutocomplete();
        fetchDesaData(1);
    });
    
    // Reset button click
    btnReset.addEventListener('click', function() {
        kabSelect.value = '';
        kecSelect.innerHTML = '<option value="">-- Semua Kecamatan --</option>';
        kecSelect.value = '';
        searchInput.value = '';
        hideAutocomplete();
        fetchDesaData(1);
    });
    
    // Close autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
            hideAutocomplete();
        }
    });
    
    // Sortable header functionality
    function updateSortIcons() {
        sortableHeaders.forEach(header => {
            const sortKey = header.dataset.sort;
            const icon = header.querySelector('.sort-icon i');
            
            if (sortKey === currentSort.by) {
                header.classList.add('active');
                icon.className = 'fas fa-sort-' + (currentSort.dir === 'asc' ? 'up' : 'down');
                icon.classList.remove('text-muted');
            } else {
                header.classList.remove('active');
                icon.className = 'fas fa-sort text-muted';
            }
        });
    }
    
    function handleSortClick(e) {
        const header = e.currentTarget;
        const sortKey = header.dataset.sort;
        
        // Toggle direction if same column, otherwise default to ASC
        if (currentSort.by === sortKey) {
            currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.by = sortKey;
            currentSort.dir = 'asc';
        }
        
        // Save preference to localStorage
        saveSortPreference();
        
        // Update UI
        updateSortIcons();
        
        // Refresh data with new sort
        currentPage = 1;
        fetchDesaData(1);
        
        // Update URL
        updateUrl();
    }
    
    // Initialize sort handlers
    sortableHeaders.forEach(header => {
        header.addEventListener('click', handleSortClick);
    });
    
    // Initial sort icon update
    updateSortIcons();
    
    // Delete Logic
    let deleteId = null;
    
    attachDeleteHandlers();
    attachPaginationHandlers();
    
    document.getElementById('confirmDelete').addEventListener('click', async function() {
        if (!deleteId) return;
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
        
        try {
            const response = await fetch(`${BASE_URL}adminWilayah/desa_delete/${deleteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                $('#deleteModal').modal('hide');
                // Refresh current page
                fetchDesaData(currentPage);
                // Show success toast/alert
                alert(data.message || 'Desa berhasil dihapus');
            } else {
                alert(data.message || 'Gagal menghapus desa');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-trash"></i> Ya, Hapus';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
