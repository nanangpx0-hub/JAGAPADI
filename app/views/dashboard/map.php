<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Peta Sebaran Hama & Penyakit</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> Data Lokasi</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>OPT</th>
                            <th>Jenis</th>
                            <th>Lokasi</th>
                            <th>Koordinat</th>
                            <th>Keparahan</th>
                            <th>Populasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mapData as $data): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($data['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($data['nama_opt']) ?></td>
                            <td><span class="badge badge-info"><?= $data['jenis'] ?></span></td>
                            <td><?= htmlspecialchars($data['lokasi']) ?></td>
                            <td><?= $data['latitude'] ?>, <?= $data['longitude'] ?></td>
                            <td>
                                <span class="badge badge-<?= 
                                    $data['tingkat_keparahan'] == 'Berat' ? 'danger' : 
                                    ($data['tingkat_keparahan'] == 'Sedang' ? 'warning' : 'success') 
                                ?>">
                                    <?= $data['tingkat_keparahan'] ?>
                                </span>
                            </td>
                            <td><?= $data['populasi'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for Leaflet to load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') {
        console.error('Leaflet.js not loaded');
        document.getElementById('map').innerHTML = '<div class="alert alert-warning">Leaflet.js failed to load. Please check your internet connection.</div>';
        return;
    }
    
    // Initialize map centered on Jember
    const map = L.map('map').setView([-8.1706, 113.7003], 11);

    // Add Google Hybrid tiles (Satellite + Labels)
    L.tileLayer('http://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; <a href="https://www.google.com/maps">Google Maps</a>'
    }).addTo(map);

    // Add markers from data
    const mapData = <?= json_encode($mapData ?? []) ?>;
    
    if (mapData.length === 0) {
        L.popup()
            .setLatLng([-8.1706, 113.7003])
            .setContent('<strong>Belum ada data lokasi</strong><br>Data laporan dengan koordinat GPS akan ditampilkan di sini.')
            .openOn(map);
    } else {
        mapData.forEach(function(item) {
            if (item.latitude && item.longitude) {
                const lat = parseFloat(item.latitude);
                const lng = parseFloat(item.longitude);
                
                if (isNaN(lat) || isNaN(lng)) return;
                
                // Define marker icon based on severity
                let iconColor = '#28a745'; // green for Ringan
                if (item.tingkat_keparahan === 'Berat') iconColor = '#dc3545'; // red
                else if (item.tingkat_keparahan === 'Sedang') iconColor = '#ffc107'; // orange
                
                // Create custom icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${iconColor}; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [25, 25],
                    iconAnchor: [12, 12]
                });
                
                const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
                
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h6 style="margin: 0 0 10px 0; color: #28a745;"><strong>${item.nama_opt || 'Unknown'}</strong></h6>
                        <table style="width: 100%; font-size: 12px;">
                            <tr><td><strong>Jenis:</strong></td><td>${item.jenis || '-'}</td></tr>
                            <tr><td><strong>Lokasi:</strong></td><td>${item.lokasi || '-'}</td></tr>
                            <tr><td><strong>Tanggal:</strong></td><td>${item.tanggal || '-'}</td></tr>
                            <tr><td><strong>Keparahan:</strong></td><td><span style="color: ${iconColor}; font-weight: bold;">${item.tingkat_keparahan || '-'}</span></td></tr>
                            <tr><td><strong>Populasi:</strong></td><td>${item.populasi || 0}</td></tr>
                        </table>
                        <div style="margin-top: 10px; text-align: center;">
                            <a href="<?= BASE_URL ?>laporan/detail/${item.id}" class="btn btn-sm btn-primary" style="font-size: 11px; padding: 3px 10px;">Detail</a>
                        </div>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
            }
        });
        
        // Fit map to show all markers if there are any
        if (mapData.length > 0) {
            const validCoords = mapData.filter(item => item.latitude && item.longitude);
            if (validCoords.length > 0) {
                const bounds = L.latLngBounds(validCoords.map(item => [parseFloat(item.latitude), parseFloat(item.longitude)]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
