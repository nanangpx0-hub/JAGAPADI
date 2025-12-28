<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Detail Laporan #<?= $laporan['id'] ?></h3>
                <div class="card-tools">
                    <?php if(in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
                    <a href="<?= BASE_URL ?>laporan/edit/<?= $laporan['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>laporan" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['created_laporan_id']) && $_SESSION['created_laporan_id'] == $laporan['id']): ?>
                <!-- Success Confirmation for Newly Created Report -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Laporan Berhasil Dibuat!</h4>
                    <p><strong>Laporan #<?= $laporan['id'] ?></strong> telah berhasil dibuat dengan status: <strong><?= $laporan['status'] ?></strong></p>
                    <hr>
                    <p class="mb-0">
                        <?php if($laporan['status'] === 'Diverifikasi'): ?>
                            <i class="fas fa-check"></i> Laporan ini sudah diverifikasi dan dapat dilihat di dashboard.
                        <?php elseif($laporan['status'] === 'Submitted'): ?>
                            <i class="fas fa-clock"></i> Laporan ini sedang menunggu verifikasi dari operator/admin.
                        <?php else: ?>
                            <i class="fas fa-file"></i> Laporan ini disimpan sebagai draf. Anda dapat mengedit atau submit nanti.
                        <?php endif; ?>
                    </p>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>laporan" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Daftar Laporan
                        </a>
                        <?php if($laporan['status'] === 'Draf'): ?>
                        <a href="<?= BASE_URL ?>laporan/edit/<?= $laporan['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Laporan
                        </a>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['created_laporan_id']); ?>
                <?php endif; ?>
                
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Tanggal Pelaporan</th>
                        <td><?= date('d/m/Y', strtotime($laporan['tanggal'])) ?></td>
                    </tr>
                    <tr>
                        <th>Kode OPT</th>
                        <td><strong><?= htmlspecialchars($laporan['kode_opt']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Nama OPT</th>
                        <td><?= htmlspecialchars($laporan['nama_opt']) ?></td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            <span class="badge badge-<?= 
                                $laporan['jenis'] == 'Hama' ? 'danger' : 
                                ($laporan['jenis'] == 'Penyakit' ? 'warning' : 'info') 
                            ?>">
                                <?= $laporan['jenis'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td><?= htmlspecialchars($laporan['lokasi']) ?></td>
                    </tr>
                    <tr>
                        <th>Koordinat GPS</th>
                        <td>
                            <?php if($laporan['latitude'] && $laporan['longitude']): ?>
                            <?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>
                            <a href="https://www.google.com/maps?q=<?= $laporan['latitude'] ?>,<?= $laporan['longitude'] ?>" 
                               target="_blank" class="btn btn-xs btn-primary">
                                <i class="fas fa-map-marker-alt"></i> Lihat di Maps
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Tidak ada data koordinat</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Tingkat Keparahan</th>
                        <td>
                            <span class="badge badge-lg badge-<?= 
                                $laporan['tingkat_keparahan'] == 'Berat' ? 'danger' : 
                                ($laporan['tingkat_keparahan'] == 'Sedang' ? 'warning' : 'info') 
                            ?>">
                                <?= $laporan['tingkat_keparahan'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Populasi/Intensitas</th>
                        <td>
                            <?= $laporan['populasi'] ?>
                            <?php if($laporan['etl_acuan'] > 0): ?>
                            <br><small class="text-muted">ETL Acuan: <?= $laporan['etl_acuan'] ?></small>
                            <?php if($laporan['populasi'] > $laporan['etl_acuan']): ?>
                            <br><span class="badge badge-danger">
                                <i class="fas fa-exclamation-triangle"></i> Melampaui ETL
                            </span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Luas Serangan</th>
                        <td><?= $laporan['luas_serangan'] ?> Ha</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-lg badge-<?= 
                                $laporan['status'] == 'Diverifikasi' ? 'success' : 
                                ($laporan['status'] == 'Submitted' ? 'warning' : 
                                ($laporan['status'] == 'Ditolak' ? 'danger' : 'secondary'))
                            ?>">
                                <?= $laporan['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Pelapor</th>
                        <td>
                            <?= htmlspecialchars($laporan['pelapor_nama']) ?><br>
                            <small class="text-muted">
                                <?= $laporan['pelapor_email'] ?? '' ?><br>
                                <?= $laporan['pelapor_phone'] ?? '' ?>
                            </small>
                        </td>
                    </tr>
                    <?php if(!empty($laporan['catatan'])): ?>
                    <tr>
                        <th>Catatan Pelapor</th>
                        <td><?= nl2br(htmlspecialchars($laporan['catatan'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($laporan['status'] == 'Diverifikasi' || $laporan['status'] == 'Ditolak'): ?>
                    <tr>
                        <th>Diverifikasi Oleh</th>
                        <td><?= htmlspecialchars($laporan['verifikator_nama'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Verifikasi</th>
                        <td><?= $laporan['verified_at'] ? date('d/m/Y H:i', strtotime($laporan['verified_at'])) : '-' ?></td>
                    </tr>
                    <?php if(!empty($laporan['catatan_verifikasi'])): ?>
                    <tr>
                        <th>Catatan Verifikasi</th>
                        <td><?= nl2br(htmlspecialchars($laporan['catatan_verifikasi'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if(!empty($laporan['foto_url'])): ?>
                    <tr>
                        <th>Foto Dokumentasi</th>
                        <td>
                            <a href="<?= BASE_URL . $laporan['foto_url'] ?>" target="_blank">
                                <img src="<?= BASE_URL . $laporan['foto_url'] ?>" 
                                     style="max-width: 100%; cursor: pointer;" 
                                     class="img-thumbnail"
                                     alt="Foto Laporan"
                                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>public/images/no-image.png'; this.alt='Foto tidak ditemukan';">
                            </a>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Klik foto untuk melihat ukuran penuh
                                </small>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <?php if(!empty($laporan['rekomendasi'])): ?>
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info-circle"></i> Rekomendasi Pengendalian:</h5>
                    <?= nl2br(htmlspecialchars($laporan['rekomendasi'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <?php if($laporan['status'] == 'Submitted' && in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Verifikasi Laporan</h3>
            </div>
            <form action="<?= BASE_URL ?>laporan/verify/<?= $laporan['id'] ?>" method="POST" id="verifyForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Status Verifikasi <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-success verify-status-btn active" data-status="Diverifikasi" id="btnApprove">
                                <i class="fas fa-check-circle"></i> Setujui/Verifikasi
                            </button>
                            <button type="button" class="btn btn-outline-danger verify-status-btn" data-status="Ditolak" id="btnReject">
                                <i class="fas fa-times-circle"></i> Tolak
                            </button>
                        </div>
                        <input type="hidden" name="status" id="verifyStatus" value="Diverifikasi">
                    </div>
                    <div class="form-group">
                        <label>Catatan/Komentar Verifikasi <span id="catatanRequired" class="text-danger" style="display:none;">*</span></label>
                        <textarea name="catatan_verifikasi" id="catatanVerifikasi" class="form-control" rows="3" 
                            placeholder="Masukkan komentar (wajib jika menolak)"></textarea>
                        <small class="text-muted" id="catatanHint">Opsional untuk verifikasi, wajib untuk penolakan.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block" id="submitVerify">
                        <i class="fas fa-check"></i> <span id="submitText">Submit Verifikasi</span>
                    </button>
                </div>
            </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const verifyStatusBtns = document.querySelectorAll('.verify-status-btn');
            const verifyStatusInput = document.getElementById('verifyStatus');
            const catatanInput = document.getElementById('catatanVerifikasi');
            const catatanRequired = document.getElementById('catatanRequired');
            const catatanHint = document.getElementById('catatanHint');
            const submitBtn = document.getElementById('submitVerify');
            const submitText = document.getElementById('submitText');
            const form = document.getElementById('verifyForm');
            
            verifyStatusBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    verifyStatusBtns.forEach(b => {
                        b.classList.remove('active', 'btn-success', 'btn-danger');
                        b.classList.add(b.dataset.status === 'Diverifikasi' ? 'btn-outline-success' : 'btn-outline-danger');
                    });
                    this.classList.remove('btn-outline-success', 'btn-outline-danger');
                    this.classList.add('active', this.dataset.status === 'Diverifikasi' ? 'btn-success' : 'btn-danger');
                    verifyStatusInput.value = this.dataset.status;
                    
                    // Update required state
                    if (this.dataset.status === 'Ditolak') {
                        catatanRequired.style.display = 'inline';
                        catatanInput.required = true;
                        catatanHint.textContent = 'Alasan penolakan wajib diisi.';
                        submitBtn.className = 'btn btn-danger btn-block';
                        submitText.textContent = 'Tolak Laporan';
                    } else {
                        catatanRequired.style.display = 'none';
                        catatanInput.required = false;
                        catatanHint.textContent = 'Opsional untuk verifikasi, wajib untuk penolakan.';
                        submitBtn.className = 'btn btn-success btn-block';
                        submitText.textContent = 'Submit Verifikasi';
                    }
                });
            });
            
            form.addEventListener('submit', function(e) {
                const status = verifyStatusInput.value;
                const catatan = catatanInput.value.trim();
                
                if (status === 'Ditolak' && !catatan) {
                    e.preventDefault();
                    alert('Alasan penolakan wajib diisi!');
                    catatanInput.focus();
                    return false;
                }
                
                const confirmMsg = status === 'Diverifikasi' 
                    ? 'Apakah Anda yakin ingin menyetujui laporan ini?' 
                    : 'Apakah Anda yakin ingin MENOLAK laporan ini?';
                    
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.textContent = 'Memproses...';
            });
        });
        </script>
        <?php endif; ?>
        
        <!-- Status History / Audit Trail -->
        <?php if(!empty($statusHistory)): ?>
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Status</h3>
            </div>
            <div class="card-body p-0">
                <div class="timeline timeline-inverse p-3">
                    <?php foreach($statusHistory as $history): ?>
                    <div class="time-label">
                        <span class="bg-<?= 
                            $history['new_status'] == 'Diverifikasi' ? 'success' : 
                            ($history['new_status'] == 'Submitted' ? 'warning' : 
                            ($history['new_status'] == 'Ditolak' ? 'danger' : 'secondary'))
                        ?>">
                            <?= date('d/m/Y H:i', strtotime($history['created_at'])) ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-<?= 
                            $history['new_status'] == 'Diverifikasi' ? 'check-circle bg-success' : 
                            ($history['new_status'] == 'Submitted' ? 'paper-plane bg-warning' : 
                            ($history['new_status'] == 'Ditolak' ? 'times-circle bg-danger' : 'file bg-secondary'))
                        ?>"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-user"></i> <?= htmlspecialchars($history['changed_by_name'] ?? 'System') ?></span>
                            <h3 class="timeline-header">
                                <?php if($history['old_status']): ?>
                                    <span class="badge badge-secondary"><?= $history['old_status'] ?></span>
                                    <i class="fas fa-arrow-right mx-1"></i>
                                <?php endif; ?>
                                <span class="badge badge-<?= 
                                    $history['new_status'] == 'Diverifikasi' ? 'success' : 
                                    ($history['new_status'] == 'Submitted' ? 'warning' : 
                                    ($history['new_status'] == 'Ditolak' ? 'danger' : 'secondary'))
                                ?>"><?= $history['new_status'] ?></span>
                            </h3>
                            <?php if(!empty($history['komentar'])): ?>
                            <div class="timeline-body">
                                <i class="fas fa-comment text-muted"></i> 
                                <?= nl2br(htmlspecialchars($history['komentar'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($laporan['latitude'] && $laporan['longitude']): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> Lokasi di Peta</h3>
            </div>
            <div class="card-body p-0">
                <div id="detailMap" style="height: 300px;"></div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-map-marker-alt"></i> 
                    Koordinat: <?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>
                </small>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Wait for DOM and Leaflet to load
document.addEventListener('DOMContentLoaded', function() {
    <?php if($laporan['latitude'] && $laporan['longitude']): ?>
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet.js not loaded');
        document.getElementById('detailMap').innerHTML = '<div class="alert alert-warning m-3">Peta tidak dapat dimuat. Periksa koneksi internet Anda.</div>';
    } else {
        try {
            // Initialize map
            const detailMap = L.map('detailMap').setView([<?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>], 13);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(detailMap);
            
            // Define marker color based on severity
            let iconColor = '#28a745'; // green for Ringan
            <?php if($laporan['tingkat_keparahan'] == 'Berat'): ?>
            iconColor = '#dc3545'; // red
            <?php elseif($laporan['tingkat_keparahan'] == 'Sedang'): ?>
            iconColor = '#ffc107'; // orange
            <?php endif; ?>
            
            // Create custom icon
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${iconColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
            
            // Add marker
            const marker = L.marker([<?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>], { 
                icon: customIcon 
            }).addTo(detailMap);
            
            // Popup content
            const popupContent = `
                <div style="min-width: 200px;">
                    <h6 style="margin: 0 0 10px 0; color: #28a745;">
                        <strong><?= htmlspecialchars($laporan['nama_opt']) ?></strong>
                    </h6>
                    <table style="width: 100%; font-size: 12px;">
                        <tr><td><strong>Jenis:</strong></td><td><?= $laporan['jenis'] ?></td></tr>
                        <tr><td><strong>Lokasi:</strong></td><td><?= htmlspecialchars($laporan['lokasi']) ?></td></tr>
                        <tr><td><strong>Keparahan:</strong></td><td><span style="color: ${iconColor}; font-weight: bold;"><?= $laporan['tingkat_keparahan'] ?></span></td></tr>
                        <tr><td><strong>Tanggal:</strong></td><td><?= date('d/m/Y', strtotime($laporan['tanggal'])) ?></td></tr>
                    </table>
                </div>
            `;
            
            marker.bindPopup(popupContent).openPopup();
            
            // Invalidate size after a short delay to ensure proper rendering
            setTimeout(function() {
                detailMap.invalidateSize();
            }, 250);
            
        } catch (error) {
            console.error('Error initializing map:', error);
            document.getElementById('detailMap').innerHTML = '<div class="alert alert-danger m-3">Error: ' + error.message + '</div>';
        }
    }
    <?php endif; ?>
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
