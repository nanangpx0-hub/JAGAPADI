<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* Import Page Styles */
.import-container {
    max-width: 900px;
    margin: 0 auto;
}

.upload-zone {
    border: 3px dashed #dee2e6;
    border-radius: 12px;
    padding: 50px 30px;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover,
.upload-zone.dragover {
    border-color: #28a745;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
}

.upload-zone.dragover {
    transform: scale(1.02);
}

.upload-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.upload-zone:hover .upload-icon,
.upload-zone.dragover .upload-icon {
    color: #28a745;
}

.upload-zone h4 {
    color: #495057;
    margin-bottom: 0.5rem;
}

.upload-zone p {
    color: #6c757d;
    margin-bottom: 1rem;
}

.file-input {
    display: none;
}

/* Stats Cards */
.stats-card {
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}

.stats-card .stats-number {
    font-size: 2.5rem;
    font-weight: 700;
}

.stats-card.valid {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.stats-card.invalid {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
}

.stats-card.total {
    background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
    color: white;
}

/* Preview Table */
.preview-table {
    max-height: 400px;
    overflow-y: auto;
}

.preview-table table {
    font-size: 0.875rem;
}

.preview-table .row-valid {
    background-color: #d4edda;
}

.preview-table .row-invalid {
    background-color: #f8d7da;
}

.error-badge {
    font-size: 0.7rem;
    padding: 2px 6px;
}

/* Progress Bar */
.import-progress {
    display: none;
}

.import-progress .progress {
    height: 25px;
    border-radius: 12px;
}

.import-progress .progress-bar {
    font-size: 0.875rem;
    font-weight: 600;
    transition: width 0.5s ease;
}

/* Result Section */
.import-result {
    display: none;
}

.result-icon {
    font-size: 5rem;
}

.result-icon.success {
    color: #28a745;
}

.result-icon.error {
    color: #dc3545;
}

/* Step Indicators */
.step-indicators {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.step {
    display: flex;
    align-items: center;
    margin: 0 10px;
    opacity: 0.5;
}

.step.active {
    opacity: 1;
}

.step.completed {
    opacity: 1;
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 8px;
}

.step.active .step-number {
    background: #007bff;
}

.step.completed .step-number {
    background: #28a745;
}

.step-line {
    width: 60px;
    height: 3px;
    background: #dee2e6;
}

.step.completed + .step-line {
    background: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .upload-zone {
        padding: 30px 20px;
    }
    
    .upload-icon {
        font-size: 3rem;
    }
    
    .stats-card .stats-number {
        font-size: 1.8rem;
    }
    
    .step-line {
        width: 30px;
    }
}
</style>

<div class="import-container">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-2 mb-md-0">
                    <i class="fas fa-file-import"></i> Import User dari Excel/CSV
                </h3>
                <div>
                    <a href="<?= BASE_URL ?>user" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Step Indicators -->
    <div class="step-indicators">
        <div class="step active" id="step1">
            <span class="step-number">1</span>
            <span>Upload</span>
        </div>
        <div class="step-line"></div>
        <div class="step" id="step2">
            <span class="step-number">2</span>
            <span>Preview</span>
        </div>
        <div class="step-line"></div>
        <div class="step" id="step3">
            <span class="step-number">3</span>
            <span>Import</span>
        </div>
        <div class="step-line"></div>
        <div class="step" id="step4">
            <span class="step-number">4</span>
            <span>Selesai</span>
        </div>
    </div>
    
    <!-- Step 1: Upload -->
    <div class="card mb-4" id="uploadSection">
        <div class="card-body">
            <!-- Template Download -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <strong>Petunjuk:</strong> Download template terlebih dahulu, isi data sesuai format, lalu upload kembali.
                <br><br>
                <a href="<?= BASE_URL ?>user/downloadTemplate" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download Template
                </a>
            </div>
            
            <!-- Upload Zone -->
            <div class="upload-zone" id="uploadZone">
                <input type="file" class="file-input" id="fileInput" accept=".xlsx,.xls,.csv">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <h4>Drag & Drop file di sini</h4>
                <p>atau klik untuk memilih file</p>
                <p class="text-muted small">Format: .xlsx, .xls, .csv (Maks. 5MB)</p>
            </div>
            
            <!-- Selected File Info -->
            <div class="mt-3 text-center" id="fileInfo" style="display: none;">
                <span class="badge badge-success">
                    <i class="fas fa-file-excel"></i> 
                    <span id="fileName"></span>
                </span>
                <button type="button" class="btn btn-sm btn-link text-danger" id="removeFile">
                    <i class="fas fa-times"></i> Hapus
                </button>
            </div>
            
            <!-- Upload Button -->
            <div class="text-center mt-4" id="uploadBtnContainer" style="display: none;">
                <button type="button" class="btn btn-success btn-lg" id="uploadBtn">
                    <i class="fas fa-upload"></i> Upload & Validasi
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Spinner -->
    <div class="card mb-4" id="loadingSection" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                <span class="sr-only">Memproses...</span>
            </div>
            <h4 class="mt-4" id="loadingText">Memvalidasi data...</h4>
        </div>
    </div>
    
    <!-- Step 2: Preview -->
    <div class="card mb-4" id="previewSection" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-eye"></i> Preview Data</h5>
        </div>
        <div class="card-body">
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stats-card total">
                        <div class="stats-number" id="statTotal">0</div>
                        <div>Total Data</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card valid">
                        <div class="stats-number" id="statValid">0</div>
                        <div>Data Valid</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card invalid">
                        <div class="stats-number" id="statInvalid">0</div>
                        <div>Data Invalid</div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Table -->
            <div class="preview-table">
                <table class="table table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Row</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Validasi</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody">
                        <!-- Filled by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Actions -->
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" id="backToUpload">
                    <i class="fas fa-arrow-left"></i> Upload Ulang
                </button>
                <button type="button" class="btn btn-success btn-lg" id="confirmImport" disabled>
                    <i class="fas fa-check"></i> Konfirmasi Import (<span id="importCount">0</span> user)
                </button>
            </div>
        </div>
    </div>
    
    <!-- Step 3: Progress -->
    <div class="card mb-4 import-progress" id="progressSection">
        <div class="card-body">
            <h5 class="text-center mb-4"><i class="fas fa-cog fa-spin"></i> Mengimport Data...</h5>
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                     role="progressbar" id="importProgress" style="width: 0%">
                    0%
                </div>
            </div>
            <p class="text-center text-muted" id="progressText">Memproses...</p>
        </div>
    </div>
    
    <!-- Step 4: Result -->
    <div class="card mb-4 import-result" id="resultSection">
        <div class="card-body text-center py-5">
            <div id="resultIcon"></div>
            <h3 id="resultTitle" class="mt-4"></h3>
            <p id="resultMessage" class="lead"></p>
            
            <!-- Error Details -->
            <div id="errorDetails" class="mt-4 text-left" style="display: none;">
                <h6>Detail Error:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Username</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody id="errorTableBody"></tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="<?= BASE_URL ?>user" class="btn btn-primary btn-lg">
                    <i class="fas fa-users"></i> Lihat Daftar User
                </a>
                <button type="button" class="btn btn-outline-secondary btn-lg" id="importMore">
                    <i class="fas fa-plus"></i> Import Lagi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Import</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda akan mengimport <strong id="modalImportCount">0</strong> user baru ke sistem.</p>
                <p class="mb-1"><strong>Password default:</strong></p>
                <ul class="mb-2 small">
                    <li>Role <code>petugas</code>: <strong>Petugas3509</strong></li>
                    <li>Role <code>operator</code>: <strong>Operator3509</strong></li>
                    <li>Role <code>viewer</code>: <strong>Viewer3509</strong></li>
                    <li>Role <code>admin</code>: <strong>Admin3509</strong></li>
                </ul>
                <p class="text-muted small mb-0">User akan diminta mengganti password saat login pertama.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="doImport">
                    <i class="fas fa-check"></i> Ya, Import Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= Security::generateCsrfToken() ?>';
    let selectedFile = null;
    let previewData = null;
    
    // Elements
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const uploadBtnContainer = document.getElementById('uploadBtnContainer');
    const uploadBtn = document.getElementById('uploadBtn');
    const removeFile = document.getElementById('removeFile');
    
    // Sections
    const uploadSection = document.getElementById('uploadSection');
    const loadingSection = document.getElementById('loadingSection');
    const previewSection = document.getElementById('previewSection');
    const progressSection = document.getElementById('progressSection');
    const resultSection = document.getElementById('resultSection');
    
    // Drag & Drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.add('dragover'), false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.remove('dragover'), false);
    });
    
    uploadZone.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });
    
    uploadZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });
    
    function handleFile(file) {
        const allowedTypes = ['xlsx', 'xls', 'csv'];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(extension)) {
            showToast('Format file tidak didukung', 'error');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            showToast('Ukuran file melebihi 5MB', 'error');
            return;
        }
        
        selectedFile = file;
        fileName.textContent = file.name;
        fileInfo.style.display = 'block';
        uploadBtnContainer.style.display = 'block';
    }
    
    removeFile.addEventListener('click', function() {
        selectedFile = null;
        fileInput.value = '';
        fileInfo.style.display = 'none';
        uploadBtnContainer.style.display = 'none';
    });
    
    // Upload & Preview
    uploadBtn.addEventListener('click', async function() {
        if (!selectedFile) return;
        
        showSection('loading');
        updateStep(1);
        
        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('csrf_token', csrfToken);
        
        try {
            const response = await fetch('<?= BASE_URL ?>user/uploadPreview', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                previewData = result;
                renderPreview(result);
                showSection('preview');
                updateStep(2);
            } else {
                showToast(result.error || 'Gagal memproses file', 'error');
                showSection('upload');
            }
        } catch (error) {
            showToast('Terjadi kesalahan: ' + error.message, 'error');
            showSection('upload');
        }
    });
    
    function renderPreview(data) {
        // Stats
        document.getElementById('statTotal').textContent = data.stats.total;
        document.getElementById('statValid').textContent = data.stats.valid_count;
        document.getElementById('statInvalid').textContent = data.stats.invalid_count;
        
        // Table
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';
        
        // Valid rows
        data.preview.valid.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'row-valid';
            tr.innerHTML = `
                <td>${row.row}</td>
                <td>${escapeHtml(row.nama_lengkap)}</td>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.email)}</td>
                <td><span class="badge badge-info">${row.role}</span></td>
                <td>${row.aktif ? 'Aktif' : 'Nonaktif'}</td>
                <td><span class="badge badge-success"><i class="fas fa-check"></i> Valid</span></td>
            `;
            tbody.appendChild(tr);
        });
        
        // Invalid rows
        data.preview.invalid.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'row-invalid';
            tr.innerHTML = `
                <td>${row.row}</td>
                <td>${escapeHtml(row.nama_lengkap)}</td>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.email)}</td>
                <td><span class="badge badge-info">${row.role}</span></td>
                <td>${row.status}</td>
                <td>
                    ${row.errors.map(e => `<span class="badge badge-danger error-badge">${escapeHtml(e)}</span>`).join(' ')}
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        // Import button
        const confirmBtn = document.getElementById('confirmImport');
        const importCount = document.getElementById('importCount');
        
        if (data.canImport) {
            confirmBtn.disabled = false;
            importCount.textContent = data.stats.valid_count;
        } else {
            confirmBtn.disabled = true;
            importCount.textContent = '0';
        }
    }
    
    // Back to Upload
    document.getElementById('backToUpload').addEventListener('click', function() {
        selectedFile = null;
        fileInput.value = '';
        fileInfo.style.display = 'none';
        uploadBtnContainer.style.display = 'none';
        showSection('upload');
        updateStep(1);
    });
    
    // Confirm Import
    document.getElementById('confirmImport').addEventListener('click', function() {
        document.getElementById('modalImportCount').textContent = previewData.stats.valid_count;
        $('#confirmModal').modal('show');
    });
    
    // Do Import
    document.getElementById('doImport').addEventListener('click', async function() {
        $('#confirmModal').modal('hide');
        
        showSection('progress');
        updateStep(3);
        
        simulateProgress();
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        
        try {
            const response = await fetch('<?= BASE_URL ?>user/processImport', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            // Finish progress
            document.getElementById('importProgress').style.width = '100%';
            document.getElementById('importProgress').textContent = '100%';
            
            setTimeout(() => {
                showResult(result);
                updateStep(4);
            }, 500);
            
        } catch (error) {
            showResult({
                success: false,
                message: 'Terjadi kesalahan: ' + error.message,
                imported: 0,
                failed: 0,
                errors: []
            });
            updateStep(4);
        }
    });
    
    function simulateProgress() {
        let progress = 0;
        const progressBar = document.getElementById('importProgress');
        const progressText = document.getElementById('progressText');
        
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) {
                progress = 90;
                clearInterval(interval);
            }
            
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
            
            if (progress < 30) {
                progressText.textContent = 'Mempersiapkan data...';
            } else if (progress < 60) {
                progressText.textContent = 'Menyimpan ke database...';
            } else {
                progressText.textContent = 'Menyelesaikan...';
            }
        }, 200);
    }
    
    function showResult(result) {
        showSection('result');
        
        const icon = document.getElementById('resultIcon');
        const title = document.getElementById('resultTitle');
        const message = document.getElementById('resultMessage');
        const errorDetails = document.getElementById('errorDetails');
        
        if (result.success && result.imported > 0) {
            icon.innerHTML = '<i class="fas fa-check-circle result-icon success"></i>';
            title.textContent = 'Import Berhasil!';
            message.innerHTML = `<strong>${result.imported}</strong> user berhasil diimport.`;
            
            if (result.failed > 0) {
                message.innerHTML += `<br><span class="text-warning">${result.failed} gagal diimport.</span>`;
            }
        } else {
            icon.innerHTML = '<i class="fas fa-times-circle result-icon error"></i>';
            title.textContent = 'Import Gagal';
            message.textContent = result.message || 'Tidak ada user yang berhasil diimport.';
        }
        
        // Show error details
        if (result.errors && result.errors.length > 0) {
            errorDetails.style.display = 'block';
            const tbody = document.getElementById('errorTableBody');
            tbody.innerHTML = '';
            
            result.errors.forEach(err => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${err.row}</td>
                    <td>${escapeHtml(err.username)}</td>
                    <td class="text-danger">${escapeHtml(err.error)}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            errorDetails.style.display = 'none';
        }
    }
    
    // Import More
    document.getElementById('importMore').addEventListener('click', function() {
        location.reload();
    });
    
    // Helper functions
    function showSection(section) {
        uploadSection.style.display = section === 'upload' ? 'block' : 'none';
        loadingSection.style.display = section === 'loading' ? 'block' : 'none';
        previewSection.style.display = section === 'preview' ? 'block' : 'none';
        progressSection.style.display = section === 'progress' ? 'block' : 'none';
        resultSection.style.display = section === 'result' ? 'block' : 'none';
    }
    
    function updateStep(step) {
        for (let i = 1; i <= 4; i++) {
            const stepEl = document.getElementById('step' + i);
            stepEl.classList.remove('active', 'completed');
            
            if (i < step) {
                stepEl.classList.add('completed');
            } else if (i === step) {
                stepEl.classList.add('active');
            }
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
    
    function showToast(message, type) {
        // Simple toast using alert for now
        // Can be replaced with a proper toast library
        if (type === 'error') {
            alert('Error: ' + message);
        } else {
            alert(message);
        }
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
