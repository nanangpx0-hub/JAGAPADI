<?php
$title = 'Manajemen Kabupaten';
require_once __DIR__ . '/../../../layouts/header.php';
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
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
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Kabupaten/Kota</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="<?= BASE_URL ?>admin/wilayah/kabupaten/create" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Kabupaten
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Controls -->
                    <!-- Filter provinsi dan status master telah dihapus sesuai requirement -->
                    <div class="filter-controls">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="customSearch" placeholder="Cari nama atau kode wilayah (35XX)...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary btn-block" id="applyFilters">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-block" id="resetFilters">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="tabelKabupaten" class="table table-bordered table-striped table-hover wilayah-table w-kabupaten">
                            <thead class="table-header">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">ID</th>
                                    <th>Kode Wilayah (BPS)</th>
                                    <th>Nama Kabupaten/Kota</th>
                                    <th class="text-center">Provinsi</th>
                                    <th class="text-center">Master ID</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i> Edit Kabupaten/Kota
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editNamaKabupaten">
                            Nama Kabupaten/Kota <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="editNamaKabupaten" 
                               name="nama_kabupaten"
                               placeholder="Contoh: Jember" 
                               required
                               autofocus>
                        <div class="invalid-feedback">Nama kabupaten wajib diisi</div>
                    </div>

                    <div class="form-group">
                        <label for="editKodeKabupaten">
                            Kode Wilayah (BPS) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="editKodeKabupaten" 
                               name="kode_kabupaten"
                               placeholder="Contoh: 3509" 
                               pattern="35[0-9]{2}"
                               maxlength="4"
                               required>
                        <small class="form-text text-muted">
                            Kode wilayah BPS format 35XX (contoh: 3501 untuk Pacitan, 3509 untuk Jember)
                        </small>
                        <div class="invalid-feedback">Kode wilayah harus 4 digit angka dengan format 35XX</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-info" id="btnEditSubmit">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
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
                <p>Apakah Anda yakin ingin menghapus kabupaten:</p>
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
<!-- DataTables CSS - Load in head -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>

<!-- DataTables JS - Load AFTER jQuery (from footer) -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Initializing DataTable for Kabupaten...');
    console.log('BASE_URL:', '<?= BASE_URL ?>');
    console.log('API URL:', '<?= BASE_URL ?>adminWilayah/kabupaten_api');
    
    let deleteId = null;
    
    // Initialize DataTable with server-side processing
    const table = $('#tabelKabupaten').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= BASE_URL ?>adminWilayah/kabupaten_api",
            "type": "GET",
            "data": function(d) {
                // Map DataTables parameters to our API
                const params = {
                    page: Math.floor(d.start / d.length) + 1,
                    limit: d.length,
                    search: d.search.value,
                    order_column: d.order[0].column,
                    order_dir: d.order[0].dir
                };
                console.log('DataTables request params:', params);
                return params;
            },
            "dataSrc": function(json) {
                console.log('API Response:', json);
                // Map our API response to DataTables format
                json.recordsTotal = json.total || 0;
                json.recordsFiltered = json.total || 0;
                console.log('Records total:', json.recordsTotal);
                console.log('Data rows:', json.data ? json.data.length : 0);
                return json.data || [];
            },
            "error": function(xhr, error, code) {
                console.error('DataTables AJAX error:', error, code);
                console.error('XHR:', xhr);
                console.error('Response Text:', xhr.responseText);
                alert('Gagal memuat data kabupaten. Silakan cek console untuk detail error.');
            }
        },
        "columns": [
            { 
                "data": null,
                "orderable": false,
                "searchable": false,
                "render": function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                "className": "text-center",
                "width": "5%"
            },
            { 
                "data": "id",
                "orderable": true,
                "searchable": true,
                "className": "text-center",
                "width": "8%",
                "render": function(data, type, row) {
                    return '<span class="badge badge-secondary">' + (data || '') + '</span>';
                }
            },
            { 
                "data": "kode_kabupaten",
                "orderable": true,
                "searchable": true,
                "width": "15%",
                "render": function(data, type, row) {
                    return '<code class="bg-primary text-white px-2 py-1 rounded">' + (data || '') + '</code><small class="text-muted ml-1">(BPS)</small>';
                }
            },
            { 
                "data": "nama_kabupaten",
                "orderable": true,
                "searchable": true,
                "width": "35%",
                "render": function(data, type, row) {
                    return '<strong>' + (data || '') + '</strong>';
                }
            },
            { 
                "data": "provinsi",
                "orderable": true,
                "searchable": true,
                "className": "text-center",
                "width": "15%",
                "render": function(data, type, row) {
                    return '<span class="badge badge-info">' + (data || '-') + '</span>';
                }
            },
            { 
                "data": "master_id",
                "orderable": true,
                "searchable": false,
                "className": "text-center",
                "width": "15%",
                "render": function(data, type, row) {
                    if (data) {
                        return '<span class="badge badge-success" title="Tersedia di Master Data"><i class="fas fa-check"></i> ' + data + '</span>';
                    } else {
                        return '<span class="badge badge-warning" title="Tidak tersedia di Master Data"><i class="fas fa-exclamation-triangle"></i> N/A</span>';
                    }
                }
            },
            { 
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "width": "12%",
                "render": function(data, type, row) {
                    const masterId = row.master_id || row.id;
                    const editUrl = '<?= BASE_URL ?>admin/wilayah/kabupaten/edit/' + masterId;
                    const namaKab = (row.nama_kabupaten || '').replace(/'/g, '&#39;');
                    
                    return `<div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-edit" 
                                data-id="${masterId}" 
                                data-nama="${namaKab}" 
                                data-kode="${row.kode_kabupaten || ''}"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-delete" 
                                data-id="${masterId}" 
                                data-name="${namaKab}" 
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        "order": [[1, "asc"]], // Default sort by Kode (column index 1) ascending
        "pageLength": 20,
        "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
        "language": {
            "processing": '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat data...',
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": '<div class="text-center py-4"><i class="fas fa-inbox fa-3x mb-3 text-muted"></i><p class="mb-0 text-muted">Tidak ada data kabupaten</p></div>',
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": '<i class="fas fa-angle-double-left"></i>',
                "last": '<i class="fas fa-angle-double-right"></i>',
                "next": '<i class="fas fa-chevron-right"></i>',
                "previous": '<i class="fas fa-chevron-left"></i>'
            },
            "emptyTable": '<div class="text-center py-4"><i class="fas fa-inbox fa-3x mb-3 text-muted"></i><p class="mb-0 text-muted">Tidak ada data kabupaten</p></div>'
        },
        "responsive": true,
        "autoWidth": false,
        "columnDefs": [
            { "width": "5%", "targets": 0 },
            { "width": "15%", "targets": 1 },
            { "width": "45%", "targets": 2 },
            { "width": "20%", "targets": 3 },
            { "width": "15%", "targets": 4 }
        ],
        "drawCallback": function(settings) {
            // Attach delete handlers after each draw
            attachDeleteHandlers();
            
            // Add custom styling to pagination
            $('.dataTables_paginate .pagination').addClass('justify-content-center');
        },
        "initComplete": function(settings, json) {
            console.log('DataTable initialized successfully');
            console.log('Total records:', json.total);
        }
    });
    
    // Function to attach delete button handlers
    function attachDeleteHandlers() {
        $('#tabelKabupaten').off('click', '.btn-delete').on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            const namaKab = $(this).data('name');
            $('#deleteName').text(namaKab);
            $('#deleteModal').modal('show');
        });
        
        // Attach edit button handlers
        $('#tabelKabupaten').off('click', '.btn-edit').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const kode = $(this).data('kode');
            
            // Populate modal form
            $('#editId').val(id);
            $('#editNamaKabupaten').val(nama);
            $('#editKodeKabupaten').val(kode);
            
            // Clear any previous validation errors
            $('#editForm .form-control').removeClass('is-invalid');
            $('#editForm .invalid-feedback').hide();
            
            // Show modal
            $('#editModal').modal('show');
        });
    }
    
    // Edit form validation and submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        $(this).find('.form-control').removeClass('is-invalid');
        $(this).find('.invalid-feedback').hide();
        
        // Get form values
        const namaKabupaten = $('#editNamaKabupaten').val().trim();
        const kodeKabupaten = $('#editKodeKabupaten').val().trim();
        const id = $('#editId').val();
        
        let isValid = true;
        
        // Validate nama kabupaten
        if (!namaKabupaten) {
            $('#editNamaKabupaten').addClass('is-invalid');
            $('#editNamaKabupaten').siblings('.invalid-feedback').show();
            isValid = false;
        }
        
        // Validate kode kabupaten
        if (!kodeKabupaten || kodeKabupaten.length !== 4 || !/^35[0-9]{2}$/.test(kodeKabupaten)) {
            $('#editKodeKabupaten').addClass('is-invalid');
            $('#editKodeKabupaten').siblings('.invalid-feedback').show();
            isValid = false;
        }
        
        if (!isValid) {
            return false;
        }
        
        // Show loading state
        const btnSubmit = $('#btnEditSubmit');
        btnSubmit.prop('disabled', true);
        btnSubmit.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        // AJAX request
        $.ajax({
            url: '<?= BASE_URL ?>adminWilayah/kabupaten_update/' + id,
            type: 'POST',
            data: {
                id: id,
                nama_kabupaten: namaKabupaten,
                kode_kabupaten: kodeKabupaten,
                csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide modal
                    $('#editModal').modal('hide');
                    
                    // Show success notification
                    showNotification('success', response.message || 'Kabupaten berhasil diperbarui');
                    
                    // Reload DataTable
                    table.ajax.reload(null, false);
                } else {
                    // Show error notification
                    showNotification('error', response.message || 'Gagal memperbarui kabupaten');
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error:', error);
                console.error('Response:', xhr.responseText);
                showNotification('error', 'Terjadi kesalahan saat memperbarui data');
            },
            complete: function() {
                // Reset button state
                btnSubmit.prop('disabled', false);
                btnSubmit.html('<i class="fas fa-save"></i> Update');
            }
        });
    });
    
    // Auto-format kode input
    $('#editKodeKabupaten').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });
    
    // Notification function
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check' : 'fas fa-ban';
        
        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="icon ${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Insert at the top of the main card
        $('.card').first().before(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
    // Confirm delete handler
    $('#confirmDelete').on('click', function() {
        if (!deleteId) return;
        
        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');
        
        $.ajax({
            url: '<?= BASE_URL ?>adminWilayah/kabupaten_delete/' + deleteId,
            type: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                
                if (response.success) {
                    // Show success notification
                    showNotification('success', response.message || 'Kabupaten berhasil dihapus');
                    
                    // Reload DataTable
                    table.ajax.reload(null, false);
                } else {
                    // Show error notification with specific message
                    showNotification('error', response.message || 'Gagal menghapus kabupaten');
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                console.error('Response:', xhr.responseText);
                
                // Try to parse error response
                let errorMessage = 'Terjadi kesalahan saat menghapus data';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Use default error message
                }
                
                showNotification('error', errorMessage);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-trash"></i> Ya, Hapus');
                deleteId = null;
            }
        });
    });
    
    // Custom search styling
    $('.dataTables_filter input').addClass('form-control-sm').attr('placeholder', 'Cari nama atau kode wilayah (35XX)...');
    $('.dataTables_length select').addClass('form-control-sm');
    
    // Custom filter functionality - Simplified (provinsi dan master filter dihapus)
    $('#applyFilters').on('click', function() {
        const searchTerm = $('#customSearch').val();
        
        // Apply search only - no provinsi or master filter
        table.search(searchTerm).draw();
    });
    
    // Reset filters on clear button
    $('#resetFilters').on('click', function() {
        $('#customSearch').val('');
        table.search('').draw();
    });
    
    // Add export functionality
    $('<button class="btn btn-success btn-sm ml-2" id="exportBtn"><i class="fas fa-download"></i> Export</button>')
        .insertAfter('#tabelKabupaten_wrapper .dataTables_length');
    
    $('#exportBtn').on('click', function() {
        const data = table.data().toArray();
        const csv = [
            'ID,Kode Wilayah,Nama Kabupaten,Provinsi,Master ID',
            ...data.map(row => [
                row.id,
                row.kode_kabupaten,
                `"${row.nama_kabupaten}"`,
                row.provinsi,
                row.master_id || 'N/A'
            ].join(','))
        ].join('\n');
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'kabupaten_data.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
});
</script>

<style>
/* DataTables custom styling */
#tabelKabupaten_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}

#tabelKabupaten_wrapper .dataTables_length {
    float: left;
}

#tabelKabupaten_wrapper .dataTables_info {
    padding-top: 8px;
}

#tabelKabupaten_wrapper .dataTables_paginate {
    padding-top: 8px;
}

/* Sortable column headers */
#tabelKabupaten thead th.sorting,
#tabelKabupaten thead th.sorting_asc,
#tabelKabupaten thead th.sorting_desc {
    cursor: pointer;
    position: relative;
    padding-right: 30px;
}

#tabelKabupaten thead th.sorting:after,
#tabelKabupaten thead th.sorting_asc:after,
#tabelKabupaten thead th.sorting_desc:after {
    position: absolute;
    right: 10px;
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    opacity: 0.5;
}

#tabelKabupaten thead th.sorting:after {
    content: '\f0dc'; /* fa-sort */
}

#tabelKabupaten thead th.sorting_asc:after {
    content: '\f0de'; /* fa-sort-up */
    opacity: 1;
}

#tabelKabupaten thead th.sorting_desc:after {
    content: '\f0dd'; /* fa-sort-down */
    opacity: 1;
}

/* Processing indicator */
.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -50px;
    text-align: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #tabelKabupaten_wrapper .dataTables_filter,
    #tabelKabupaten_wrapper .dataTables_length {
        float: none;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .filter-controls .row > div {
        margin-bottom: 10px;
    }
    
    #tabelKabupaten {
        font-size: 0.85em;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
    
    #tabelKabupaten th,
    #tabelKabupaten td {
        padding: 0.5rem 0.25rem;
        font-size: 0.8em;
    }
    
    .badge {
        font-size: 0.7em;
        padding: 0.2rem 0.4rem;
    }
}

/* Enhanced styling for badges */
.badge {
    font-size: 0.8em;
    padding: 0.3rem 0.6rem;
}

.bg-primary {
    background-color: #007bff !important;
}

/* Filter controls styling */
.filter-controls {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.filter-controls .input-group-text {
    background: #007bff;
    color: white;
    border: 1px solid #007bff;
}

/* Export button styling */
#exportBtn {
    margin-left: 10px;
}

/* Table header styling */
.table-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-header th {
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

/* Row hover effect */
#tabelKabupaten tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}

/* Action buttons styling */
.btn-group .btn {
    margin: 0 1px;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

/* Status indicators */
.status-available {
    color: #28a745;
}

.status-unavailable {
    color: #ffc107;
}

/* Loading spinner */
.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

/* Enhanced modal styling */
.modal {
    z-index: 1050;
}

.modal-dialog {
    max-width: 500px;
    margin: 1.75rem auto;
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,.125);
}

/* Form validation styling */
.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    padding-right: calc(1.5em + 0.75rem);
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 80%;
    color: #dc3545;
}

.form-control.is-invalid ~ .invalid-feedback {
    display: block;
}

/* Radio button group styling */
.btn-group-toggle .btn {
    border-radius: 0;
}

.btn-group-toggle .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group-toggle .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.btn-group-toggle .btn.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Alert notification styling */
.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Enhanced button styling */
.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.btn:hover {
    text-decoration: none;
}

.btn:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Loading spinner styling */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
    
    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
    }
}

@media (max-width: 576px) {
    .modal-footer {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .filter-controls .row {
        gap: 0.5rem;
    }
    
    .filter-controls .col-md-8,
    .filter-controls .col-md-2 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Enhanced table styling for mobile */
@media (max-width: 768px) {
    #tabelKabupaten {
        font-size: 0.8rem;
    }
    
    #tabelKabupaten th,
    #tabelKabupaten td {
        padding: 0.5rem 0.25rem;
        vertical-align: middle;
    }
    
    .btn-group-sm {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group-sm .btn {
        margin: 0;
        border-radius: 0.25rem;
    }
}

/* Focus and accessibility improvements */
.btn:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .btn {
        border-width: 2px;
    }
    
    .form-control {
        border-width: 2px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
