            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2025 <a href="#">JAGAPADI</a>.</strong>
        Dikembangkan oleh Nanang Pamungkas | BPS Kabupaten Jember
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Custom JavaScript -->
<script src="<?= BASE_URL ?>public/js/validation.js"></script>
<script src="<?= BASE_URL ?>public/js/loading.js"></script>
<script src="<?= BASE_URL ?>public/js/confirm-dialog.js"></script>
<!-- Mobile Enhancements -->
<script src="<?= BASE_URL ?>public/js/mobile-enhancements.js"></script>

<!-- Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= BASE_URL ?>public/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
                
                // Check for updates
                registration.addEventListener('updatefound', function() {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', function() {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New version available
                            if (confirm('Versi baru JAGAPADI tersedia. Muat ulang untuk menggunakan versi terbaru?')) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch(function(error) {
                console.log('ServiceWorker registration failed: ', error);
            });
    });
    
    // Handle service worker updates
    navigator.serviceWorker.addEventListener('controllerchange', function() {
        window.location.reload();
    });
}
</script>

</body>
</html>
