<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* Photo Grid Layout */
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.photo-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.photo-thumbnail-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    background-color: #f8f9fa;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-thumbnail {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.photo-card:hover .photo-thumbnail {
    transform: scale(1.05);
}

.photo-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #6c757d;
}

.photo-loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.photo-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #dc3545;
    padding: 20px;
}

.photo-error i {
    font-size: 48px;
    margin-bottom: 10px;
}

.photo-info {
    padding: 15px;
}

.photo-filename {
    font-weight: bold;
    font-size: 14px;
    color: #212529;
    margin-bottom: 8px;
    word-break: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.photo-meta {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
}

.photo-meta i {
    margin-right: 5px;
    width: 16px;
}

.photo-actions {
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 5px;
    justify-content: center;
}

.photo-action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.photo-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-preview {
    background-color: #17a2b8;
    color: white;
}

.btn-preview:hover {
    background-color: #138496;
    color: white;
}

.btn-download {
    background-color: #28a745;
    color: white;
}

.btn-download:hover {
    background-color: #218838;
    color: white;
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn-delete:hover {
    background-color: #c82333;
    color: white;
}

/* Preview Modal */
.photo-preview-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.photo-preview-modal.active {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.photo-preview-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    text-align: center;
}

.photo-preview-image {
    max-width: 100%;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    animation: zoomIn 0.3s ease;
}

@keyframes zoomIn {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.photo-preview-close {
    position: absolute;
    top: -40px;
    right: 0;
    color: #fff;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.2);
}

.photo-preview-close:hover {
    background-color: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.photo-preview-info {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    color: #fff;
    text-align: center;
    font-size: 14px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        padding: 15px 0;
    }
    
    .photo-thumbnail-wrapper {
        height: 180px;
    }
    
    .photo-thumbnail {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 576px) {
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    
    .photo-thumbnail-wrapper {
        height: 150px;
    }
    
    .photo-thumbnail {
        width: 100px;
        height: 100px;
    }
    
    .photo-actions {
        flex-wrap: wrap;
    }
    
    .photo-action-btn {
        font-size: 11px;
        padding: 5px 10px;
    }
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-images"></i> Daftar Foto OPT
                    <span class="badge badge-primary ml-2"><?= $total ?? 0 ?></span>
                </h3>
                <div class="card-tools">
                    <a href="<?= BASE_URL ?>opt" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar OPT
                    </a>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <a href="<?= BASE_URL ?>opt/create" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Tambah Foto
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(empty($photos)): ?>
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <h3>Tidak ada foto</h3>
                    <p>Belum ada foto OPT yang diupload.</p>
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <a href="<?= BASE_URL ?>opt/create" class="btn btn-success mt-3">
                        <i class="fas fa-plus"></i> Upload Foto Pertama
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="photos-grid" id="photosGrid">
                    <?php foreach($photos as $photo): ?>
                    <div class="photo-card" data-path="<?= htmlspecialchars($photo['path']) ?>">
                        <div class="photo-thumbnail-wrapper">
                            <img src="<?= htmlspecialchars($photo['url']) ?>" 
                                 alt="<?= htmlspecialchars($photo['filename']) ?>"
                                 class="photo-thumbnail"
                                 loading="lazy"
                                 data-full-url="<?= htmlspecialchars($photo['url']) ?>"
                                 onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\'photo-error\'><i class=\'fas fa-exclamation-triangle\'></i><span>Gagal memuat gambar</span></div>';">
                        </div>
                        <div class="photo-info">
                            <div class="photo-filename" title="<?= htmlspecialchars($photo['filename']) ?>">
                                <?= htmlspecialchars($photo['filename']) ?>
                            </div>
                            <div class="photo-meta">
                                <i class="fas fa-hdd"></i> <?= htmlspecialchars($photo['size_formatted']) ?>
                            </div>
                            <?php if(isset($photo['width']) && isset($photo['height'])): ?>
                            <div class="photo-meta">
                                <i class="fas fa-expand"></i> <?= $photo['width'] ?> × <?= $photo['height'] ?> px
                            </div>
                            <?php endif; ?>
                            <div class="photo-meta">
                                <i class="fas fa-calendar"></i> <?= htmlspecialchars($photo['modified_formatted']) ?>
                            </div>
                            <div class="photo-meta">
                                <i class="fas fa-folder"></i> <?= $photo['year'] ?>/<?= $photo['month'] ?>
                            </div>
                        </div>
                        <div class="photo-actions">
                            <button type="button" 
                                    class="photo-action-btn btn-preview" 
                                    onclick="previewPhoto('<?= htmlspecialchars($photo['url']) ?>', '<?= htmlspecialchars($photo['filename']) ?>')"
                                    title="Pratinjau">
                                <i class="fas fa-eye"></i> Pratinjau
                            </button>
                            <a href="<?= htmlspecialchars($photo['url']) ?>" 
                               download="<?= htmlspecialchars($photo['filename']) ?>"
                               class="photo-action-btn btn-download"
                               title="Unduh">
                                <i class="fas fa-download"></i> Unduh
                            </a>
                            <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                            <button type="button" 
                                    class="photo-action-btn btn-delete" 
                                    onclick="deletePhoto('<?= htmlspecialchars($photo['path']) ?>', '<?= htmlspecialchars($photo['filename']) ?>')"
                                    title="Hapus">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="photo-preview-modal" id="previewModal" onclick="closePreview()">
    <div class="photo-preview-content" onclick="event.stopPropagation()">
        <span class="photo-preview-close" onclick="closePreview()">&times;</span>
        <img id="previewImage" class="photo-preview-image" src="" alt="Preview">
        <div class="photo-preview-info">
            <div id="previewFilename"></div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    // Preview photo function
    window.previewPhoto = function(url, filename) {
        const modal = document.getElementById('previewModal');
        const img = document.getElementById('previewImage');
        const filenameDiv = document.getElementById('previewFilename');
        
        img.src = url;
        filenameDiv.textContent = filename;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    
    // Close preview function
    window.closePreview = function() {
        const modal = document.getElementById('previewModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };
    
    // Delete photo function
    window.deletePhoto = function(path, filename) {
        if (!confirm('Yakin ingin menghapus foto "' + filename + '"?\n\nTindakan ini tidak dapat dibatalkan.')) {
            return;
        }
        
        // Find the photo card
        const card = document.querySelector(`.photo-card[data-path="${path.replace(/"/g, '&quot;')}"]`);
        
        // Show loading state
        if (card) {
            const actions = card.querySelector('.photo-actions');
            if (actions) {
                const originalHTML = actions.innerHTML;
                actions.innerHTML = '<div class="photo-loading"><i class="fas fa-spinner fa-spin"></i> Menghapus...</div>';
                
                // Disable all action buttons
                const buttons = card.querySelectorAll('.photo-action-btn');
                buttons.forEach(btn => btn.disabled = true);
            }
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('path', path);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        
        // Send AJAX request
        fetch('<?= BASE_URL ?>opt/deletePhoto', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove card with fade out animation
                if (card) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.remove();
                        
                        // Update total count
                        const badge = document.querySelector('.card-title .badge');
                        if (badge) {
                            const currentCount = parseInt(badge.textContent) || 0;
                            badge.textContent = Math.max(0, currentCount - 1);
                        }
                        
                        // Show message if no photos left
                        const grid = document.getElementById('photosGrid');
                        if (grid && grid.children.length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
                
                // Show success toast
                showToast('success', 'Foto berhasil dihapus');
            } else {
                // Show error
                showToast('error', data.error || 'Gagal menghapus foto');
                
                // Restore original state
                if (card) {
                    const actions = card.querySelector('.photo-actions');
                    if (actions && originalHTML) {
                        actions.innerHTML = originalHTML;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Terjadi kesalahan saat menghapus foto');
            
            // Restore original state
            if (card) {
                const actions = card.querySelector('.photo-actions');
                if (actions && originalHTML) {
                    actions.innerHTML = originalHTML;
                }
            }
        });
    };
    
    // Toast notification
    function showToast(type, message) {
        // Create toast container if doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000;';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
        toast.style.cssText = 'min-width: 300px; margin-bottom: 10px; animation: slideInRight 0.3s ease;';
        toast.innerHTML = `
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
    
    // Close preview on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreview();
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('.photo-thumbnail[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
})();
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
