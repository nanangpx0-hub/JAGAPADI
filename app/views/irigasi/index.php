<?php 
require_once ROOT_PATH . '/app/helpers/ErrorMessage.php';
require_once ROOT_PATH . '/app/views/layouts/header.php'; 

$successMsg = ErrorMessage::flashSuccess();
$errorMsg = ErrorMessage::flash();
?>

<style>
/* ===== TOMBOL AKSI STYLING (Matching laporan page design) ===== */
.btn-action-group {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-action {
    width: 32px;
    height: 32px;
    padding: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
}

.btn-action i {
    font-size: 16px;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Enhanced hover effects for action buttons */
.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Specific button styles */
.btn-action-info {
    background-color: #17a2b8;
    color: #fff;
}

.btn-action-info:hover {
    background-color: #138496;
    color: #fff;
}

.btn-action-success {
    background-color: #28a745;
    color: #fff;
}

.btn-action-success:hover {
    background-color: #218838;
    color: #fff;
}

.btn-action-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-action-warning:hover {
    background-color: #e0a800;
    color: #212529;
}

.btn-action-danger {
    background-color: #dc3545;
    color: #fff;
}

.btn-action-danger:hover {
    background-color: #c82333;
    color: #fff;
}

/* Focus state for accessibility */
.btn-action:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Active state */
.btn-action:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-action:disabled,
.btn-action.disabled {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
}

/* Enhanced tooltip with better styling */
.btn-action::after {
    content: attr(title);
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(4px);
    background-color: #2c3e50;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    border: 1px solid rgba(255,255,255,0.1);
}

.btn-action:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.btn-action::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 4px);
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #2c3e50;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
}

.btn-action:hover::before {
    opacity: 1;
    visibility: visible;
}

/* ===== ACTION CONTROL PANEL ===== */
.action-control-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    margin-bottom: 20px;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #dee2e6;
}

.auto-approve-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
    display: inline-block;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #ccc;
    border-radius: 26px;
    transition: 0.3s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #28a745;
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(24px);
}

.toggle-label {
    line-height: 1.3;
}

.toggle-label strong {
    display: block;
    font-size: 14px;
    color: #343a40;
}

.toggle-label small {
    font-size: 11px;
    color: #6c757d;
}

/* Auto Run Controls */
.auto-run-section {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-left: 16px;
    border-left: 2px solid #dee2e6;
}

.btn-auto-run {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-start-run {
    background: #28a745;
    color: white;
}

.btn-start-run:hover:not(:disabled) {
    background: #218838;
    transform: translateY(-1px);
}

.btn-pause-run {
    background: #ffc107;
    color: #212529;
}

.btn-pause-run:hover:not(:disabled) {
    background: #e0a800;
    transform: translateY(-1px);
}

.btn-cancel-run {
    background: #dc3545;
    color: white;
}

.btn-cancel-run:hover:not(:disabled) {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-auto-run:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

/* Progress Indicator */
.progress-section {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 200px;
}

.progress-wrapper {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
    min-width: 60px;
    font-weight: 500;
}

/* Running indicator animation */
.running-indicator {
    display: none;
    width: 16px;
    height: 16px;
    border: 2px solid #28a745;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.is-running .running-indicator {
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast Notifications */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    max-width: 350px;
}

.toast-notification.show {
    transform: translateX(0);
}

.toast-notification.toast-success {
    border-left: 4px solid #28a745;
}

.toast-notification.toast-success i {
    color: #28a745;
}

.toast-notification.toast-warning {
    border-left: 4px solid #ffc107;
}

.toast-notification.toast-warning i {
    color: #ffc107;
}

.toast-notification.toast-info {
    border-left: 4px solid #17a2b8;
}

.toast-notification.toast-info i {
    color: #17a2b8;
}

.toast-notification.toast-error {
    border-left: 4px solid #dc3545;
}

.toast-notification.toast-error i {
    color: #dc3545;
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
    .btn-action-group {
        justify-content: center;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 8px;
        font-size: 16px;
    }
    
    .btn-action i {
        font-size: 18px;
        width: 24px;
        height: 24px;
    }
    
    .btn-action::after {
        bottom: calc(100% + 12px);
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .btn-action::before {
        bottom: calc(100% + 4px);
        border-width: 4px;
    }
    
    .action-control-panel {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .auto-run-section {
        padding-left: 0;
        border-left: none;
        padding-top: 12px;
        border-top: 1px solid #dee2e6;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .progress-section {
        min-width: 100%;
    }
    
    .toast-notification {
        left: 10px;
        right: 10px;
        max-width: none;
    }
}

@media (min-width: 576px) and (max-width: 991px) {
    .btn-action {
        width: 34px;
        height: 34px;
        padding: 7px;
        font-size: 15px;
    }
    
    .btn-action i {
        font-size: 17px;
        width: 22px;
        height: 22px;
    }
    
    .action-control-panel {
        flex-wrap: wrap;
    }
    
    .progress-section {
        min-width: 150px;
    }
}
</style>

<div class="container-fluid">
    <!-- Alert Messages -->
    <?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon fas fa-check"></i> <strong>Sukses!</strong> <?= htmlspecialchars($successMsg) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icon fas fa-ban"></i> <strong>Error!</strong> <?= htmlspecialchars($errorMsg) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <h1><i class="fas fa-water text-primary"></i> Sebaran Irigasi</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?= BASE_URL ?>irigasi/create" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Tambah Data
            </a>
        </div>
    </div>

    <!-- Action Control Panel -->
    <?php if(in_array($userRole, ['admin', 'operator'])): ?>
    <div class="action-control-panel" id="actionControlPanel">
        <!-- Auto Approve Toggle -->
        <div class="auto-approve-section">
            <label class="toggle-switch">
                <input type="checkbox" id="autoApproveToggle" checked>
                <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">
                <strong>Auto Approve</strong>
                <small class="d-block text-muted">Skip konfirmasi verifikasi</small>
            </span>
        </div>
        
        <!-- Auto Run Controls -->
        <div class="auto-run-section">
            <button type="button" class="btn-auto-run btn-start-run" id="btnStartAutoRun" title="Mulai proses verifikasi otomatis">
                <i class="fas fa-play"></i> Auto Run
            </button>
            <button type="button" class="btn-auto-run btn-pause-run" id="btnPauseAutoRun" disabled title="Pause proses">
                <i class="fas fa-pause"></i> Pause
            </button>
            <button type="button" class="btn-auto-run btn-cancel-run" id="btnCancelAutoRun" disabled title="Batalkan proses">
                <i class="fas fa-stop"></i> Cancel
            </button>
            <span class="running-indicator"></span>
        </div>
        
        <!-- Progress Indicator -->
        <div class="progress-section">
            <div class="progress-wrapper">
                <div class="progress-fill" id="autoRunProgress"></div>
            </div>
            <span class="progress-text" id="progressText">0 / 0</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th>No Laporan</th>
                            <th>Tanggal</th>
                            <th>Pelapor</th>
                            <th>Lokasi</th>
                            <th>Saluran & Detail</th>
                            <th>Status Perbaikan</th>
                            <th>Status Laporan</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan as $item): ?>
                        <tr>
                            <td>
                                <span class="badge badge-light border"><?= htmlspecialchars($item['no_laporan'] ?? '-') ?></span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                            <td>
                                <?= htmlspecialchars($item['nama_pelapor'] ?? $item['pelapor_nama']) ?><br>
                                <small class="text-muted"><?= ucfirst($item['pelapor_role']) ?></small>
                            </td>
                            <td>
                                <strong>Desa:</strong> <?= htmlspecialchars($item['nama_desa']) ?><br>
                                <small>Kec: <?= htmlspecialchars($item['nama_kecamatan']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['nama_saluran']) ?></strong><br>
                                <span class="badge badge-info"><?= $item['jenis_saluran'] ?? $item['jenis_irigasi'] ?></span><br>
                                <small class="text-muted">Luas: <?= number_format($item['luas_layanan'], 2) ?> Ha</small>
                            </td>
                            <td>
                                <?php 
                                    $repairStatusClass = [
                                        'Selesai Diperbaiki' => 'success',
                                        'Dalam Perbaikan' => 'warning',
                                        'Belum Ditangani' => 'danger',
                                        'Normal' => 'success'
                                    ];
                                    $rsCls = $repairStatusClass[$item['status_perbaikan'] ?? 'Belum Ditangani'] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $rsCls ?>"><?= $item['status_perbaikan'] ?? 'Belum Ditangani' ?></span>
                                <?php if($item['kondisi_fisik']): ?>
                                    <br><small>Kondisi: <?= $item['kondisi_fisik'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = [
                                        'Draf' => 'secondary',
                                        'Submitted' => 'primary',
                                        'Diverifikasi' => 'success',
                                        'Ditolak' => 'danger'
                                    ];
                                    $cls = $statusClass[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $cls ?>"><?= $item['status'] ?></span>
                            </td>
                            <td>
                                <div class="btn-action-group" data-row-id="<?= $item['id'] ?>">
                                    <!-- View/Detail button - always available -->
                                    <a href="<?= BASE_URL ?>irigasi/detail/<?= $item['id'] ?>" 
                                       class="btn-action btn-action-info" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if(in_array($userRole, ['admin', 'operator']) && $item['status'] == 'Submitted'): ?>
                                    <!-- Verification button -->
                                    <button type="button" 
                                            class="btn-action btn-action-success" 
                                            data-toggle="modal" 
                                            data-target="#verifyModal<?= $item['id'] ?>"
                                            title="Verifikasi">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <!-- Reject button -->
                                    <button type="button" 
                                            class="btn-action btn-action-danger btn-reject" 
                                            data-toggle="modal" 
                                            data-target="#verifyModal<?= $item['id'] ?>"
                                            data-action="reject"
                                            title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if(in_array($userRole, ['admin', 'operator', 'petugas']) && in_array($item['status'], ['Draf', 'Ditolak'])): ?>
                                    <!-- Edit button for draft/rejected -->
                                    <a href="<?= BASE_URL ?>irigasi/edit/<?= $item['id'] ?>" 
                                       class="btn-action btn-action-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($userRole == 'admin'): ?>
                                    <!-- Delete button - admin only -->
                                    <a href="<?= BASE_URL ?>irigasi/delete/<?= $item['id'] ?>" 
                                       class="btn-action btn-action-danger" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')"
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Modal Verifikasi -->
                                <?php if(in_array($userRole, ['admin', 'operator']) && $item['status'] == 'Submitted'): ?>
                                <div class="modal fade" id="verifyModal<?= $item['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= BASE_URL ?>irigasi/verify/<?= $item['id'] ?>" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Verifikasi Laporan</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                                    <div class="form-group">
                                                        <label>Aksi</label>
                                                        <select name="status" class="form-control" id="verifyStatus<?= $item['id'] ?>">
                                                            <option value="Diverifikasi">Terima (Verifikasi)</option>
                                                            <option value="Ditolak">Tolak</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Catatan</label>
                                                        <textarea name="catatan_verifikasi" class="form-control" rows="3" placeholder="Tambahkan catatan verifikasi..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT_START_MARKER -->
<div id="script-marker-test" style="display:none;">Script should follow</div>
<script>
/**
 * Irigasi Action Controller
 * Handles auto approve, auto run, and confirmation dialogs
 * @version 2.0.0
 * @author JAGAPADI System
 */
const IrigasiActionController = (function() {
    'use strict';
    
    // State management
    const state = {
        autoApprove: true,
        autoRunning: false,
        autoRunPaused: false,
        currentQueue: [],
        processedCount: 0,
        totalCount: 0
    };
    
    // LocalStorage keys
    const STORAGE_KEYS = {
        AUTO_APPROVE: 'jagapadi_irigasi_autoApprove',
        LAST_RUN: 'jagapadi_irigasi_lastAutoRun'
    };
    
    // DOM Elements cache
    let elements = {};
    
    /**
     * Initialize the controller
     */
    function init() {
        // Cache DOM elements
        elements = {
            autoApproveToggle: document.getElementById('autoApproveToggle'),
            btnStart: document.getElementById('btnStartAutoRun'),
            btnPause: document.getElementById('btnPauseAutoRun'),
            btnCancel: document.getElementById('btnCancelAutoRun'),
            progressFill: document.getElementById('autoRunProgress'),
            progressText: document.getElementById('progressText'),
            controlPanel: document.getElementById('actionControlPanel')
        };
        
        // Load preferences from localStorage
        loadPreferences();
        
        // Bind event listeners
        bindEvents();
        
        // Initialize modal handlers
        initModals();
        
        // Initialize delete handlers
        initDeleteHandlers();
        
        // Log initialization
        logAction('Controller initialized', { autoApprove: state.autoApprove });
    }
    
    /**
     * Load user preferences from localStorage
     */
    function loadPreferences() {
        const savedAutoApprove = localStorage.getItem(STORAGE_KEYS.AUTO_APPROVE);
        
        // Default to true if not set
        if (savedAutoApprove === null) {
            state.autoApprove = true;
            localStorage.setItem(STORAGE_KEYS.AUTO_APPROVE, 'true');
        } else {
            state.autoApprove = savedAutoApprove === 'true';
        }
        
        // Update toggle state
        if (elements.autoApproveToggle) {
            elements.autoApproveToggle.checked = state.autoApprove;
        }
    }
    
    /**
     * Save preferences to localStorage
     */
    function savePreferences() {
        localStorage.setItem(STORAGE_KEYS.AUTO_APPROVE, String(state.autoApprove));
        logAction('Preferences saved', { autoApprove: state.autoApprove });
    }
    
    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Auto Approve toggle
        if (elements.autoApproveToggle) {
            elements.autoApproveToggle.addEventListener('change', function() {
                state.autoApprove = this.checked;
                savePreferences();
                showToast(
                    state.autoApprove ? 
                    'Auto Approve diaktifkan - verifikasi akan langsung diproses' : 
                    'Auto Approve dinonaktifkan - konfirmasi diperlukan',
                    state.autoApprove ? 'success' : 'info'
                );
            });
        }
        
        // Auto Run buttons
        if (elements.btnStart) {
            elements.btnStart.addEventListener('click', startAutoRun);
        }
        if (elements.btnPause) {
            elements.btnPause.addEventListener('click', togglePauseAutoRun);
        }
        if (elements.btnCancel) {
            elements.btnCancel.addEventListener('click', cancelAutoRun);
        }
        
        // Handle reject button click to pre-select "Ditolak" in modal
        document.querySelectorAll('.btn-reject').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const targetModal = this.getAttribute('data-target');
                const modalId = targetModal.replace('#verifyModal', '');
                const selectElement = document.getElementById('verifyStatus' + modalId);
                if (selectElement) {
                    selectElement.value = 'Ditolak';
                }
            });
        });
        
        // Enhanced verification button handlers
        document.querySelectorAll('.btn-action-success[data-toggle="modal"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (state.autoApprove && !state.autoRunning) {
                    e.preventDefault();
                    e.stopPropagation();
                    autoApproveItem(this);
                }
            });
        });
    }
    
    /**
     * Initialize Bootstrap modals with proper handling
     */
    function initModals() {
        // Check if jQuery and Bootstrap are available
        if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
            logAction('Bootstrap modal system ready');
            
            // Ensure all modal triggers work (for non-auto-approve cases)
            $('[data-toggle="modal"]').each(function() {
                const $btn = $(this);
                
                // Skip if auto approve is handling this
                if ($btn.hasClass('btn-action-success')) {
                    $btn.off('click.modalTrigger').on('click.modalTrigger', function(e) {
                        // Only show modal if auto approve is OFF
                        if (!state.autoApprove || state.autoRunning) {
                            e.preventDefault();
                            var target = $(this).attr('data-target');
                            if (target) {
                                $(target).modal('show');
                            }
                        }
                    });
                } else {
                    $btn.off('click.modalTrigger').on('click.modalTrigger', function(e) {
                        e.preventDefault();
                        var target = $(this).attr('data-target');
                        if (target) {
                            $(target).modal('show');
                        }
                    });
                }
            });
            
            // Handle modal close buttons
            $('.modal .close, .modal [data-dismiss="modal"]').off('click.modalClose').on('click.modalClose', function(e) {
                e.preventDefault();
                $(this).closest('.modal').modal('hide');
            });
        } else {
            // Fallback: Use vanilla JavaScript for modals
            logAction('Using vanilla JS modal fallback');
            initVanillaModals();
        }
    }
    
    /**
     * Vanilla JS modal fallback
     */
    function initVanillaModals() {
        document.querySelectorAll('[data-toggle="modal"]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                // Skip verify buttons when auto approve is on
                if (this.classList.contains('btn-action-success') && state.autoApprove && !state.autoRunning) {
                    return;
                }
                
                e.preventDefault();
                var targetId = this.getAttribute('data-target');
                if (targetId) {
                    var modal = document.querySelector(targetId);
                    if (modal) {
                        modal.classList.add('show');
                        modal.style.display = 'block';
                        modal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                        
                        // Create backdrop
                        var backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modal-backdrop';
                        document.body.appendChild(backdrop);
                    }
                }
            });
        });
        
        // Close modal handlers
        document.querySelectorAll('.modal .close, .modal [data-dismiss="modal"]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeVanillaModal(this.closest('.modal'));
            });
        });
    }
    
    /**
     * Close vanilla modal
     */
    function closeVanillaModal(modal) {
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            
            var backdrop = document.getElementById('modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    }
    
    /**
     * Initialize delete confirmation handlers
     */
    function initDeleteHandlers() {
        document.querySelectorAll('.btn-action-danger[title="Hapus"]').forEach(function(link) {
            // Remove inline onclick
            link.removeAttribute('onclick');
            
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                
                logAction('Delete requested', { url: href });
                
                showConfirmDialog({
                    title: 'Konfirmasi Hapus',
                    message: 'Yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.',
                    confirmText: 'Hapus',
                    confirmClass: 'btn-danger',
                    icon: 'trash-alt'
                }).then(confirmed => {
                    if (confirmed) {
                        logAction('Delete confirmed', { url: href });
                        window.location.href = href;
                    } else {
                        logAction('Delete cancelled', { url: href });
                    }
                });
            });
        });
    }
    
    /**
     * Auto approve a single item
     * @param {Element} triggerBtn - The button that triggered the action
     * @returns {Promise<boolean>}
     */
    async function autoApproveItem(triggerBtn) {
        const targetModal = triggerBtn.getAttribute('data-target');
        const itemId = targetModal.replace('#verifyModal', '');
        const form = document.querySelector(`${targetModal} form`);
        
        if (!form) {
            logAction('Form not found for auto approve', { itemId });
            showToast('Error: Form tidak ditemukan', 'error');
            return false;
        }
        
        try {
            // Set status to Diverifikasi
            const statusSelect = form.querySelector('select[name="status"]');
            if (statusSelect) {
                statusSelect.value = 'Diverifikasi';
            }
            
            // Add auto-approve note
            const catatanField = form.querySelector('textarea[name="catatan_verifikasi"]');
            if (catatanField && !catatanField.value.trim()) {
                const timestamp = new Date().toLocaleString('id-ID');
                catatanField.value = `[Auto Approved] Diverifikasi otomatis oleh sistem pada ${timestamp}`;
            }
            
            logAction('Auto approving item', { itemId });
            
            // Visual feedback
            triggerBtn.classList.add('disabled');
            triggerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Submit form
            form.submit();
            return true;
        } catch (error) {
            logAction('Error in auto approve', { itemId, error: error.message });
            showToast(`Error: ${error.message}`, 'error');
            return false;
        }
    }
    
    /**
     * Start auto run process
     */
    async function startAutoRun() {
        // Get all pending verification items
        const pendingItems = document.querySelectorAll(
            '.btn-action-success[data-toggle="modal"]:not(.disabled)'
        );
        
        if (pendingItems.length === 0) {
            showToast('Tidak ada item yang perlu diverifikasi', 'warning');
            return;
        }
        
        // Confirm start
        const confirmed = await showConfirmDialog({
            title: 'Mulai Auto Run',
            message: `Akan memproses ${pendingItems.length} laporan secara otomatis dengan status "Diverifikasi". Lanjutkan?`,
            confirmText: 'Mulai Proses',
            confirmClass: 'btn-success',
            icon: 'play-circle'
        });
        
        if (!confirmed) return;
        
        // Initialize state
        state.autoRunning = true;
        state.autoRunPaused = false;
        state.currentQueue = Array.from(pendingItems);
        state.processedCount = 0;
        state.totalCount = state.currentQueue.length;
        
        // Update UI
        updateAutoRunUI();
        updateProgress();
        elements.controlPanel?.classList.add('is-running');
        
        logAction('Auto run started', { total: state.totalCount });
        showToast(`Memulai proses auto run untuk ${state.totalCount} item...`, 'info');
        
        // Start processing
        processNextItem();
    }
    
    /**
     * Process next item in queue
     */
    async function processNextItem() {
        if (!state.autoRunning || state.currentQueue.length === 0) {
            finishAutoRun();
            return;
        }
        
        if (state.autoRunPaused) {
            logAction('Auto run paused, waiting...');
            return;
        }
        
        const item = state.currentQueue.shift();
        
        try {
            await autoApproveItem(item);
            state.processedCount++;
            updateProgress();
            
            // Small delay between items to prevent server overload
            setTimeout(processNextItem, 800);
        } catch (error) {
            logAction('Error processing item', { error: error.message });
            showToast(`Error pada item: ${error.message}`, 'error');
            
            // Continue with next item
            setTimeout(processNextItem, 800);
        }
    }
    
    /**
     * Toggle pause/resume auto run
     */
    function togglePauseAutoRun() {
        state.autoRunPaused = !state.autoRunPaused;
        
        if (elements.btnPause) {
            elements.btnPause.innerHTML = state.autoRunPaused ? 
                '<i class="fas fa-play"></i> Resume' : 
                '<i class="fas fa-pause"></i> Pause';
        }
        
        if (!state.autoRunPaused) {
            showToast('Auto run dilanjutkan', 'info');
            processNextItem();
        } else {
            showToast('Auto run di-pause', 'warning');
        }
        
        logAction(state.autoRunPaused ? 'Auto run paused' : 'Auto run resumed', {
            processed: state.processedCount,
            remaining: state.currentQueue.length
        });
    }
    
    /**
     * Cancel auto run
     */
    async function cancelAutoRun() {
        const confirmed = await showConfirmDialog({
            title: 'Batalkan Auto Run',
            message: `Sudah memproses ${state.processedCount} dari ${state.totalCount} item. Yakin ingin membatalkan proses?`,
            confirmText: 'Ya, Batalkan',
            confirmClass: 'btn-danger',
            icon: 'stop-circle'
        });
        
        if (confirmed) {
            finishAutoRun(true);
        }
    }
    
    /**
     * Finish auto run process
     * @param {boolean} cancelled - Whether the process was cancelled
     */
    function finishAutoRun(cancelled = false) {
        const wasRunning = state.autoRunning;
        
        state.autoRunning = false;
        state.autoRunPaused = false;
        state.currentQueue = [];
        
        elements.controlPanel?.classList.remove('is-running');
        updateAutoRunUI();
        
        if (wasRunning) {
            if (cancelled) {
                showToast(`Auto run dibatalkan. ${state.processedCount} item telah diproses.`, 'warning');
            } else if (state.processedCount > 0) {
                showToast(`Auto run selesai! ${state.processedCount} item berhasil diverifikasi.`, 'success');
            }
        }
        
        logAction('Auto run finished', { 
            processed: state.processedCount,
            total: state.totalCount,
            cancelled 
        });
        
        // Save last run timestamp
        localStorage.setItem(STORAGE_KEYS.LAST_RUN, new Date().toISOString());
        
        // Reload page after short delay if items were processed
        if (state.processedCount > 0 && !cancelled) {
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    }
    
    /**
     * Update auto run UI state
     */
    function updateAutoRunUI() {
        if (elements.btnStart) {
            elements.btnStart.disabled = state.autoRunning;
        }
        if (elements.btnPause) {
            elements.btnPause.disabled = !state.autoRunning;
            if (!state.autoRunning) {
                elements.btnPause.innerHTML = '<i class="fas fa-pause"></i> Pause';
            }
        }
        if (elements.btnCancel) {
            elements.btnCancel.disabled = !state.autoRunning;
        }
    }
    
    /**
     * Update progress display
     */
    function updateProgress() {
        const percentage = state.totalCount > 0 ? 
            (state.processedCount / state.totalCount) * 100 : 0;
        
        if (elements.progressFill) {
            elements.progressFill.style.width = `${percentage}%`;
        }
        if (elements.progressText) {
            elements.progressText.textContent = `${state.processedCount} / ${state.totalCount}`;
        }
    }
    
    /**
     * Show confirmation dialog
     * @param {Object} options - Dialog options
     * @returns {Promise<boolean>}
     */
    function showConfirmDialog(options) {
        return new Promise((resolve) => {
            const {
                title = 'Konfirmasi',
                message = 'Apakah Anda yakin?',
                confirmText = 'Ya',
                cancelText = 'Batal',
                confirmClass = 'btn-primary',
                icon = 'question-circle'
            } = options;
            
            logAction('Showing confirm dialog', { title, message });
            
            // Use existing ConfirmDialog if available
            if (typeof ConfirmDialog !== 'undefined' && typeof $ !== 'undefined') {
                ConfirmDialog.show({
                    title,
                    message,
                    confirmText,
                    cancelText,
                    confirmClass
                })
                .then(() => {
                    logAction('Dialog confirmed', { title });
                    resolve(true);
                })
                .catch(() => {
                    logAction('Dialog cancelled', { title });
                    resolve(false);
                });
            } else {
                // Fallback to native confirm
                const result = confirm(message);
                logAction('Native confirm result', { result, message });
                resolve(result);
            }
        });
    }
    
    /**
     * Show toast notification
     * @param {string} message - Toast message
     * @param {string} type - Toast type (success, warning, info, error)
     */
    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        const iconMap = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle',
            error: 'times-circle'
        };
        
        toast.innerHTML = `
            <i class="fas fa-${iconMap[type] || 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
        
        logAction('Toast shown', { message, type });
    }
    
    /**
     * Log action for debugging
     * @param {string} action - Action description
     * @param {Object} data - Additional data
     */
    function logAction(action, data = {}) {
        const timestamp = new Date().toISOString();
        console.log(`[IrigasiController] ${timestamp} - ${action}`, data);
        
        // Store in session for debugging
        try {
            const logs = JSON.parse(sessionStorage.getItem('irigasi_action_logs') || '[]');
            logs.push({ timestamp, action, data });
            
            // Keep only last 100 logs
            while (logs.length > 100) {
                logs.shift();
            }
            sessionStorage.setItem('irigasi_action_logs', JSON.stringify(logs));
        } catch (e) {
            // Ignore storage errors
        }
    }
    
    // Public API
    return {
        init,
        isAutoApproveEnabled: () => state.autoApprove,
        isAutoRunning: () => state.autoRunning,
        getLogs: () => {
            try {
                return JSON.parse(sessionStorage.getItem('irigasi_action_logs') || '[]');
            } catch (e) {
                return [];
            }
        },
        showToast,
        logAction
    };
})();

// Initialize controller when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be ready if available
    if (typeof $ !== 'undefined') {
        IrigasiActionController.init();
    } else {
        // Wait for jQuery to load
        var checkInterval = setInterval(function() {
            if (typeof $ !== 'undefined') {
                clearInterval(checkInterval);
                IrigasiActionController.init();
            }
        }, 100);
        
        // Timeout after 3 seconds, initialize anyway
        setTimeout(function() {
            clearInterval(checkInterval);
            IrigasiActionController.init();
        }, 3000);
    }
});
</script>

<?php require_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>


