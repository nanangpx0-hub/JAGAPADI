/**
 * Confirmation Dialog Library
 * Provides confirmation dialogs for destructive operations
 */
(function() {
    'use strict';
    
    const ConfirmDialog = {
        /**
         * Show confirmation dialog
         */
        show: function(options = {}) {
            const {
                title = 'Konfirmasi',
                message = 'Apakah Anda yakin?',
                confirmText = 'Ya',
                cancelText = 'Batal',
                confirmClass = 'btn-danger',
                onConfirm = null,
                onCancel = null
            } = options;
            
            return new Promise((resolve, reject) => {
                // Remove existing modal if any
                const existing = document.getElementById('confirm-dialog-modal');
                if (existing) {
                    existing.remove();
                }
                
                // Create modal
                const modal = document.createElement('div');
                modal.id = 'confirm-dialog-modal';
                modal.className = 'modal fade';
                modal.setAttribute('tabindex', '-1');
                modal.setAttribute('role', 'dialog');
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">${cancelText}</button>
                                <button type="button" class="btn ${confirmClass}" id="confirm-dialog-btn">${confirmText}</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Initialize Bootstrap modal
                const $modal = $(modal);
                $modal.modal('show');
                
                // Handle confirm
                const confirmBtn = modal.querySelector('#confirm-dialog-btn');
                confirmBtn.addEventListener('click', () => {
                    $modal.modal('hide');
                    
                    if (onConfirm) {
                        onConfirm();
                    }
                    
                    resolve(true);
                });
                
                // Handle cancel
                $modal.on('hidden.bs.modal', () => {
                    if (onCancel) {
                        onCancel();
                    }
                    
                    modal.remove();
                    reject(false);
                });
            });
        },
        
        /**
         * Confirm delete operation
         */
        delete: function(message = 'Data yang dihapus tidak dapat dikembalikan.') {
            return this.show({
                title: 'Konfirmasi Hapus',
                message: message,
                confirmText: 'Hapus',
                cancelText: 'Batal',
                confirmClass: 'btn-danger'
            });
        },
        
        /**
         * Confirm bulk delete operation
         */
        bulkDelete: function(count, message = null) {
            const defaultMessage = `Anda akan menghapus ${count} data. Data yang dihapus tidak dapat dikembalikan.`;
            return this.show({
                title: 'Konfirmasi Hapus Massal',
                message: message || defaultMessage,
                confirmText: 'Hapus Semua',
                cancelText: 'Batal',
                confirmClass: 'btn-danger'
            });
        },
        
        /**
         * Confirm logout
         */
        logout: function() {
            return this.show({
                title: 'Konfirmasi Logout',
                message: 'Apakah Anda yakin ingin keluar?',
                confirmText: 'Logout',
                cancelText: 'Batal',
                confirmClass: 'btn-primary'
            });
        }
    };
    
    // Auto-handle delete links
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete links
        document.addEventListener('click', function(e) {
            const deleteLink = e.target.closest('a[data-confirm-delete], button[data-confirm-delete]');
            if (deleteLink) {
                e.preventDefault();
                
                const message = deleteLink.getAttribute('data-message') || 
                               'Data yang dihapus tidak dapat dikembalikan.';
                
                ConfirmDialog.delete(message)
                    .then(() => {
                        // Proceed with delete
                        if (deleteLink.tagName === 'A') {
                            window.location.href = deleteLink.href;
                        } else {
                            deleteLink.closest('form').submit();
                        }
                    })
                    .catch(() => {
                        // User cancelled
                    });
            }
            
            // Handle bulk delete
            const bulkDeleteBtn = e.target.closest('button[data-confirm-bulk-delete]');
            if (bulkDeleteBtn) {
                e.preventDefault();
                
                const count = bulkDeleteBtn.getAttribute('data-count') || 0;
                const message = bulkDeleteBtn.getAttribute('data-message');
                
                ConfirmDialog.bulkDelete(count, message)
                    .then(() => {
                        // Trigger bulk delete
                        const event = new Event('bulk-delete-confirmed');
                        bulkDeleteBtn.dispatchEvent(event);
                    })
                    .catch(() => {
                        // User cancelled
                    });
            }
            
            // Handle logout
            const logoutLink = e.target.closest('a[data-confirm-logout]');
            if (logoutLink) {
                e.preventDefault();
                
                ConfirmDialog.logout()
                    .then(() => {
                        window.location.href = logoutLink.href;
                    })
                    .catch(() => {
                        // User cancelled
                    });
            }
        });
        
        // Handle form submissions with data-confirm attribute
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const confirmMessage = form.getAttribute('data-confirm');
            
            if (confirmMessage) {
                e.preventDefault();
                
                ConfirmDialog.show({
                    title: 'Konfirmasi',
                    message: confirmMessage,
                    confirmText: 'Ya',
                    cancelText: 'Batal'
                })
                .then(() => {
                    form.submit();
                })
                .catch(() => {
                    // User cancelled
                });
            }
        });
    });
    
    // Export to global scope
    window.ConfirmDialog = ConfirmDialog;
})();

