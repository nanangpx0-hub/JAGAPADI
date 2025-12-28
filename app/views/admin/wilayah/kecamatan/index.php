<?php
$title = 'Manajemen Kecamatan';
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
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Kecamatan</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="<?= BASE_URL ?>adminWilayah/kecamatan/create" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Kecamatan
                            </a>
                            <a href="<?= BASE_URL ?>adminWilayah/kecamatan_import" class="btn btn-primary btn-sm ml-2">
                                <i class="fas fa-file-upload mr-1"></i> Import Kecamatan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filter Form - Fungsi pencarian dihapus, hanya filter kabupaten -->
                    <form id="filterForm" class="mb-3">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label id="kabupatenFilterLabel"><i class="fas fa-filter"></i> Filter Berdasarkan Kabupaten:</label>
                                    <select id="kabupatenFilter" name="kabupatenFilter" class="form-control" aria-labelledby="kabupatenFilterLabel">
                                        <option value="">-- Semua Kabupaten --</option>
                                        <?php
                                        $kabModel = new MasterKabupaten();
                                        $kabupatenList = $kabModel->getAllForDropdown();
                                        foreach ($kabupatenList as $kab): ?>
                                            <option value="<?= $kab['id'] ?>">
                                                <?= htmlspecialchars($kab['kode_kabupaten'] . ' - ' . $kab['nama_kabupaten']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" id="resetFilter" class="btn btn-secondary btn-block" aria-label="Reset filter kabupaten">
                                    <i class="fas fa-redo"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Status Info - Menampilkan kabupaten yang dipilih -->
                    <div id="tableStatus" class="alert alert-info" style="display: none;" role="status" aria-live="polite" aria-busy="false">
                        <i class="fas fa-info-circle"></i>
                        <span id="statusText"></span>
                        <a href="#" id="btnTambahKecamatanFiltered" class="btn btn-success btn-sm ml-3" style="display: none;" aria-label="Tambah kecamatan untuk kabupaten yang dipilih">
                            <i class="fas fa-plus"></i> Tambah Kecamatan untuk Kabupaten Ini
                        </a>
                    </div>

                    <!-- Selection Info -->
                    <div id="selectionBar" class="alert alert-warning d-flex align-items-center justify-content-between" style="display: none;" role="status" aria-live="polite">
                        <div>
                            <i class="fas fa-check-square mr-2"></i>
                            <span id="selectedCount">0</span> item terpilih
                        </div>
                        <div>
                            <button type="button" id="btnBulkDelete" class="btn btn-danger btn-sm" disabled>
                                <i class="fas fa-trash"></i> Hapus Terpilih (<span id="selectedCountBtn">0</span>)
                            </button>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="tabelKecamatan" class="table table-bordered table-striped table-hover wilayah-table w-kecamatan">
                            <thead class="table-header">
                                <tr>
                                    <th class="text-center" style="width:40px;">
                                        <input type="checkbox" id="selectAll" aria-label="Pilih semua kecamatan">
                                    </th>
                                    <th class="text-center">#</th>
                                    <th>Kode Wilayah (BPS)</th>
                                    <th>Nama Kecamatan</th>
                                    <th class="text-center">Kabupaten (BPS)</th>
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
                    <i class="fas fa-edit mr-2"></i> Edit Kecamatan
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
                        <label for="editNamaKecamatan">
                            Nama Kecamatan <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="editNamaKecamatan" 
                               name="nama_kecamatan"
                               placeholder="Contoh: Jember" 
                               required
                               autofocus>
                        <div class="invalid-feedback">Nama kecamatan wajib diisi</div>
                    </div>

                    <div class="form-group">
                        <label for="editKodeKecamatan">
                            Kode Wilayah (BPS) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="editKodeKecamatan" 
                               name="kode_kecamatan"
                               placeholder="Contoh: 350910" 
                               readonly>
                        <small class="form-text text-muted">
                            Kode wilayah tidak dapat diubah melalui edit. Gunakan proses khusus jika perlu koreksi.
                        </small>
                        <div class="invalid-feedback">Kode wilayah tidak dapat diubah.</div>
                    </div>

                    <div class="form-group">
                        <label for="editKabupaten">
                            Kabupaten <span class="text-danger">*</span>
                        </label>
                        <select id="editKabupaten" name="kabupaten_id" class="form-control" disabled>
                            <option value="">-- Pilih Kabupaten --</option>
                            <?php
                            $kabModel = new MasterKabupaten();
                            $kabupatenList = $kabModel->getAllForDropdown();
                            foreach ($kabupatenList as $kab): ?>
                                <option value="<?= $kab['id'] ?>">
                                    <?= htmlspecialchars($kab['kode_kabupaten'] . ' - ' . $kab['nama_kabupaten']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Kabupaten tidak dapat diubah.</div>
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
                <p>Apakah Anda yakin ingin menghapus kecamatan:</p>
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
    console.log('Initializing DataTable for Kecamatan...');
    console.log('BASE_URL:', '<?= BASE_URL ?>');
    console.log('API URL:', '<?= BASE_URL ?>adminWilayah/kecamatan_api');

    let deleteId = null;
    const selectedRows = new Map(); // id => name
    const bulkDeleteDefaultHtml = $('#btnBulkDelete').html();

    // Initialize DataTable with server-side processing
    const table = $('#tabelKecamatan').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= BASE_URL ?>adminWilayah/kecamatan_api",
            "type": "GET",
            "data": function(d) {
                // Map DataTables parameters to our API
                // Fungsi pencarian dihapus - hanya filter kabupaten
                const params = {
                    draw: d.draw,
                    page: Math.floor(d.start / d.length) + 1,
                    limit: d.length,
                    search: '', // Search disabled
                    order_column: d.order[0].column,
                    order_dir: d.order[0].dir,
                    kabupaten_id: $('#kabupatenFilter').val()
                };
                console.log('DataTables request params:', params);
                return params;
            },
            "dataSrc": function(json) {
                console.log('API Response:', json);
                // Use server-provided draw/recordsTotal/recordsFiltered when available
                console.log('Records total:', json.recordsTotal ?? json.total ?? 0);
                console.log('Records filtered:', json.recordsFiltered ?? json.total ?? 0);
                console.log('Data rows returned:', json.data ? json.data.length : 0);
                return json.data || [];
            },
            "error": function(xhr, error, code) {
                console.error('DataTables AJAX error:', error, code);
                console.error('XHR:', xhr);
                console.error('Response Text:', xhr.responseText);

                let errorMessage = 'Gagal memuat data kecamatan.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage += ' ' + response.message;
                    }
                } catch (e) {
                    // Ignore JSON parse error
                }

                if (xhr.status === 401) {
                    errorMessage += ' Sesi login telah berakhir. Silakan login kembali.';
                    window.location.href = '<?= BASE_URL ?>auth/login';
                } else if (xhr.status === 403) {
                    errorMessage += ' Anda tidak memiliki akses ke halaman ini.';
                } else if (xhr.status === 500) {
                    errorMessage += ' Terjadi kesalahan server. Silakan coba lagi nanti.';
                } else {
                    errorMessage += ' Silakan cek koneksi internet dan coba lagi.';
                }

                alert(errorMessage);
            }
        },
        "columns": [
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center align-middle",
                "render": function(data, type, row) {
                    const safeName = $('<div>').text(row.nama_kecamatan || '-').html();
                    return `<input type="checkbox" class="row-select" data-id="${row.id}" data-name="${safeName}" aria-label="Pilih kecamatan ${safeName}">`;
                }
            },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "render": function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                "className": "text-center dtr-control align-middle"
            },
            {
                "data": "kode_kecamatan",
                "orderable": true,
                "render": function(data, type, row) {
                    return '<code class="bg-primary text-white px-2 py-1 rounded">' + (data || '') + '</code><small class="text-muted ml-1">(BPS)</small>';
                }
            },
            {
                "data": "nama_kecamatan",
                "orderable": true,
                "render": function(data, type, row) {
                    return '<strong>' + (data || '') + '</strong>';
                }
            },
            {
                "data": "kode_kabupaten",
                "orderable": true,
                "className": "text-center",
                "render": function(data, type, row) {
                    return data ? `<span class="badge badge-info">${data}</span>` : '-';
                }
            },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    const safeName = $('<div>').text(row.nama_kecamatan || '-').html();
                    const namaKec = safeName.replace(/"/g, '&quot;');
                    const kodeKab = row.kode_kabupaten || '';
                    const kabupatenId = row.kabupaten_id || '';

                    return `<div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-edit" 
                                data-id="${row.id}" 
                                data-nama="${namaKec}" 
                                data-kode="${row.kode_kecamatan || ''}"
                                data-kabupaten="${kabupatenId}"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-delete"
                                data-id="${row.id}"
                                data-name="${namaKec}"
                                title="Hapus" aria-label="Hapus Kecamatan">
                            <i class="fas fa-trash"></i><span class="sr-only">Hapus</span>
                        </button>
                    </div>`;
                }
            }
        ],
        "order": [[2, "asc"]], // Default sort by Kode (column index 2) ascending
        "pageLength": 20,
        "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
        "deferRender": true,
        "searchDelay": 300,
        "language": {
            "processing": '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat data...',
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": function() {
                const kabupatenFilter = $('#kabupatenFilter').val();
                let message = 'Tidak ada data kecamatan ditemukan';

                if (kabupatenFilter) {
                    message += ' untuk kabupaten: ' + $('#kabupatenFilter option:selected').text();
                }

                return '<div class="text-center py-4"><i class="fas fa-search fa-3x mb-3 text-muted"></i><p class="mb-0 text-muted">' + message + '</p></div>';
            },
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data yang sesuai",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "", // Disable built-in search
            "paginate": {
                "first": '<i class="fas fa-angle-double-left"></i>',
                "last": '<i class="fas fa-angle-double-right"></i>',
                "next": '<i class="fas fa-chevron-right"></i>',
                "previous": '<i class="fas fa-chevron-left"></i>'
            },
            "emptyTable": '<div class="text-center py-4"><i class="fas fa-inbox fa-3x mb-3 text-muted"></i><p class="mb-0 text-muted">Belum ada data kecamatan. <a href="<?= BASE_URL ?>adminWilayah/kecamatan/create">Tambah kecamatan pertama</a></p></div>'
        },
        "responsive": {
            details: {
                type: 'column',
                target: 1,
                renderer: function(api, rowIdx, columns) {
                    const data = columns
                        .filter(col => col.hidden && col.data)
                        .map(col => `<tr><td>${col.title}</td><td>${col.data}</td></tr>`) 
                        .join('');
                    return data ? `<table class="table mb-0">${data}</table>` : false;
                }
            }
        },
        "autoWidth": false,
        "columnDefs": [
            { "width": "6%", "targets": 0 },
            { "width": "6%", "targets": 1 },
            { "width": "20%", "targets": 2 },
            { "width": "28%", "targets": 3 },
            { "width": "15%", "targets": 4 },
            { "width": "25%", "targets": 5 }
        ],
        "drawCallback": function(settings) {
            // Attach delete handlers after each draw
            attachDeleteHandlers();
            attachSelectionHandlers();
            applySelectionState();

            // Add custom styling to pagination
            $('.dataTables_paginate .pagination').addClass('justify-content-center');
        },
        "initComplete": function(settings, json) {
            console.log('DataTable initialized successfully');
            console.log('Total records:', json.total);
        }
    });

    // Function to attach delete button handlers
    function updateSelectionUI() {
        const count = selectedRows.size;
        $('#selectedCount, #selectedCountBtn').text(count);
        $('#selectionBar').toggle(count > 0);
        $('#btnBulkDelete').prop('disabled', count === 0);

        const disableEdit = count > 0;
        $('.btn-edit').prop('disabled', disableEdit).toggleClass('disabled', disableEdit);
    }

    function applySelectionState() {
        $('#tabelKecamatan tbody tr').each(function() {
            const rowData = table.row(this).data();
            if (!rowData || typeof rowData.id === 'undefined') return;
            const id = String(rowData.id);
            const isSelected = selectedRows.has(id);
            $(this).toggleClass('row-selected', isSelected);
            $(this).find('.row-select').prop('checked', isSelected);
        });

        const totalRows = table.rows({ page: 'current' }).data().length;
        const selectedOnPage = $('#tabelKecamatan tbody .row-select:checked').length;
        $('#selectAll').prop('checked', totalRows > 0 && selectedOnPage === totalRows);

        updateSelectionUI();
    }

    function attachSelectionHandlers() {
        // Select/Deselect all on current page
        $('#selectAll').off('change').on('change', function() {
            const checked = $(this).is(':checked');
            table.rows({ page: 'current' }).every(function() {
                const rowData = this.data();
                if (!rowData || typeof rowData.id === 'undefined') return;
                const id = String(rowData.id);
                if (checked) {
                    selectedRows.set(id, rowData.nama_kecamatan || '-');
                } else {
                    selectedRows.delete(id);
                }
            });
            applySelectionState();
        });

        // Individual row select
        $('#tabelKecamatan').off('change', '.row-select').on('change', '.row-select', function() {
            const id = String($(this).data('id'));
            const name = $(this).data('name') || '-';
            if ($(this).is(':checked')) {
                selectedRows.set(id, name);
            } else {
                selectedRows.delete(id);
            }
            applySelectionState();
        });
    }

    function clearSelection() {
        selectedRows.clear();
        applySelectionState();
    }

    function attachDeleteHandlers() {
        $('#tabelKecamatan').off('click', '.btn-delete').on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            const namaKec = $(this).data('name');
            $('#deleteName').text(namaKec);
            $('#deleteModal').modal('show');
        });
        
        // Attach edit button handlers
        $('#tabelKecamatan').off('click', '.btn-edit').on('click', '.btn-edit', function() {
            if (selectedRows.size > 0) {
                showNotification('error', 'Batalkan seleksi massal sebelum melakukan edit.');
                return;
            }

            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const kode = $(this).data('kode');
            const kabupatenId = $(this).data('kabupaten');
            
            // Populate modal form
            $('#editId').val(id);
            $('#editNamaKecamatan').val(nama);
            $('#editKodeKecamatan').val(kode);
            $('#editKabupaten').val(kabupatenId);
            
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
        const namaKecamatan = $('#editNamaKecamatan').val().trim();
        const id = $('#editId').val();
        
        let isValid = true;
        
        // Validate nama kecamatan
        if (!namaKecamatan) {
            $('#editNamaKecamatan').addClass('is-invalid');
            $('#editNamaKecamatan').siblings('.invalid-feedback').show();
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
            url: '<?= BASE_URL ?>adminWilayah/kecamatan_update/' + id,
            type: 'POST',
            data: {
                id: id,
                nama_kecamatan: namaKecamatan,
                csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide modal
                    $('#editModal').modal('hide');
                    
                    // Show success notification
                    showNotification('success', response.message || 'Kecamatan berhasil diperbarui');
                    
                    // Reload DataTable
                    table.ajax.reload(null, false);
                } else {
                    // Show error notification
                    showNotification('error', response.message || 'Gagal memperbarui kecamatan');
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
            url: '<?= BASE_URL ?>adminWilayah/kecamatan_delete/' + deleteId,
            type: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                
                if (response.success) {
                    // Remove from selection if it was selected
                    selectedRows.delete(String(deleteId));
                    applySelectionState();

                    // Show success notification
                    showNotification('success', response.message || 'Kecamatan berhasil dihapus');
                    
                    // Reload DataTable
                    table.ajax.reload(null, false);
                } else {
                    // Show error notification with specific message
                    showNotification('error', response.message || 'Gagal menghapus kecamatan');
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
                
                if (xhr.status === 400) {
                    errorMessage += '. Pastikan kecamatan tidak memiliki desa terkait.';
                } else if (xhr.status === 403) {
                    errorMessage += '. Sesi atau akses tidak valid.';
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

    // Bulk delete handler
    $('#btnBulkDelete').on('click', function() {
        const ids = Array.from(selectedRows.keys());
        if (ids.length === 0) return;

        const sampleNames = Array.from(selectedRows.values()).slice(0, 5).join(', ');
        const extra = ids.length > 5 ? ` dan ${ids.length - 5} lainnya` : '';
        const confirmMsg = `Anda akan menghapus ${ids.length} kecamatan.\n${sampleNames}${extra}\nLanjutkan?`;

        if (!confirm(confirmMsg)) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');

        $.ajax({
            url: '<?= BASE_URL ?>adminWilayah/kecamatan_bulk_delete',
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            data: JSON.stringify({ ids: ids }),
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message || 'Kecamatan terpilih berhasil dihapus');
                    clearSelection();
                    table.ajax.reload(null, false);
                } else {
                    showNotification('error', response.message || 'Gagal menghapus data terpilih');
                }

                if (response.skipped && response.skipped.length) {
                    showNotification('error', 'Data dilewati: ' + response.skipped.join('; '));
                }
            },
            error: function(xhr) {
                console.error('Bulk delete error:', xhr.responseText);
                let errorMessage = 'Terjadi kesalahan saat menghapus data terpilih';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.message) errorMessage = resp.message;
                } catch (e) {
                    // ignore
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                btn.html(bulkDeleteDefaultHtml);
                updateSelectionUI();
            }
        });
    });

    // Hide DataTables built-in search box (search functionality removed)
    $('.dataTables_filter').hide();
    $('.dataTables_length select').addClass('form-control-sm');

    // Filter kabupaten handler with real-time AJAX
    let filterTimeout;
    let isFiltering = false;
    
    function showLoadingState() {
        if (!isFiltering) {
            isFiltering = true;
            $('#tableStatus').addClass('alert-warning').removeClass('alert-info');
            $('#statusText').html('<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data kecamatan...');
            $('#tableStatus').attr('aria-busy','true');
            $('#tableStatus').show();
        }
    }
    
    function hideLoadingState() {
        isFiltering = false;
        $('#tableStatus').attr('aria-busy','false');
        updateStatusInfo();
    }
    
    function updateStatusInfo() {
        const kabupatenFilter = $('#kabupatenFilter').val();
        const $statusDiv = $('#tableStatus');
        const $statusText = $('#statusText');
        const $btnTambah = $('#btnTambahKecamatanFiltered');

        if (!isFiltering) {
            $statusDiv.removeClass('alert-warning').addClass('alert-info');
        }

        if (kabupatenFilter) {
            const kabupatenName = $('#kabupatenFilter option:selected').text();
            $statusText.html('<strong>Filter Aktif:</strong> Menampilkan kecamatan di ' + kabupatenName);
            
            // Update link tombol tambah dengan parameter kabupaten_id
            $btnTambah.attr('href', '<?= BASE_URL ?>adminWilayah/kecamatan/create?kabupaten_id=' + kabupatenFilter);
            $btnTambah.show();
            
            $statusDiv.show();
        } else {
            $btnTambah.hide();
            if (!isFiltering) {
                $statusDiv.hide();
            }
        }
    }

    // Real-time filter handler - AJAX reload tanpa page refresh
    function applyKabupatenFilter() {
        showLoadingState();
        table.ajax.reload(function() {
            hideLoadingState();
        }, false); // false = stay on current page
    }

    // Event handler untuk filter kabupaten - real-time filtering
    $('#kabupatenFilter').on('change', function() {
        clearTimeout(filterTimeout);
        // Debounce untuk performa optimal
        filterTimeout = setTimeout(applyKabupatenFilter, 100);
    });

    // Reset filter handler
    $('#resetFilter').on('click', function() {
        $('#kabupatenFilter').val('');
        showLoadingState();
        table.ajax.reload(function() {
            hideLoadingState();
        }, false);
    });

    // Initial status update
    updateStatusInfo();
    updateSelectionUI();
});
</script>

<style>
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

/* Selected row feedback */
#tabelKecamatan tbody tr.row-selected {
    background-color: #fff3cd !important;
}

#tabelKecamatan tbody tr.row-selected td {
    border-color: #ffeeba !important;
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
    
    .filter-controls .col-md-10,
    .filter-controls .col-md-2 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Enhanced table styling for mobile */
@media (max-width: 768px) {
    #tabelKecamatan {
        font-size: 0.8rem;
    }
    
    #tabelKecamatan th,
    #tabelKecamatan td {
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

/* DataTables custom styling */
#tabelKecamatan_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}

#tabelKecamatan_wrapper .dataTables_length {
    float: left;
}

#tabelKecamatan_wrapper .dataTables_info {
    padding-top: 8px;
}

#tabelKecamatan_wrapper .dataTables_paginate {
    padding-top: 8px;
}

/* Sortable column headers */
#tabelKecamatan thead th.sorting,
#tabelKecamatan thead th.sorting_asc,
#tabelKecamatan thead th.sorting_desc {
    cursor: pointer;
    position: relative;
    padding-right: 30px;
}

#tabelKecamatan thead th.sorting:after,
#tabelKecamatan thead th.sorting_asc:after,
#tabelKecamatan thead th.sorting_desc:after {
    position: absolute;
    right: 10px;
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    opacity: 0.5;
}

#tabelKecamatan thead th.sorting:after {
    content: '\f0dc'; /* fa-sort */
}

#tabelKecamatan thead th.sorting_asc:after {
    content: '\f0de'; /* fa-sort-up */
    opacity: 1;
}

#tabelKecamatan thead th.sorting_desc:after {
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
    #tabelKecamatan_wrapper .dataTables_filter,
    #tabelKecamatan_wrapper .dataTables_length {
        float: none;
        text-align: center;
        margin-bottom: 10px;
    }
}
</style>
