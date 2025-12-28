<?php
class LaporanController extends Controller {
    private $laporanModel;
    private $optModel;
    
    public function __construct() {
        $this->laporanModel = $this->model('LaporanHama');
        $this->optModel = $this->model('MasterOpt');
    }
    
    public function index() {
        $this->checkAuth();
        
        $status = $_GET['status'] ?? null;
        $user = $this->getCurrentUser();
        
        // Role-based filtering: petugas can only see their own reports
        if ($user['role'] === 'petugas') {
            if ($status) {
                $laporan = $this->laporanModel->getByStatusAndUser($status, $user['id']);
            } else {
                $laporan = $this->laporanModel->getAllWithDetailsByUser($user['id']);
            }
        } else {
            // Admin and operator can see all reports
            if ($status) {
                $laporan = $this->laporanModel->getByStatus($status);
            } else {
                $laporan = $this->laporanModel->getAllWithDetails();
            }
        }
        
        // Get rejected reports count for petugas
        $rejectedCount = 0;
        if ($user['role'] === 'petugas') {
            $rejectedCount = $this->laporanModel->getCountByStatusAndUser('Ditolak', $user['id']);
        }
        
        $data = [
            'title' => 'Daftar Laporan',
            'laporan' => $laporan,
            'status' => $status,
            'currentUser' => $user,
            'rejectedCount' => $rejectedCount
        ];
        
        $this->view('laporan/index', $data);
    }
    
    /**
     * API: Get tag suggestions for autocomplete
     * Route: GET /laporan/tagSuggestions?q=query
     */
    public function tagSuggestions() {
        $this->checkAuth();
        
        $query = $_GET['q'] ?? '';
        if (empty($query) || strlen($query) < 2) {
            $this->json(['success' => true, 'data' => []]);
        }
        
        try {
            $tagModel = $this->model('Tag');
            $tags = $tagModel->search($query, 10);
            $this->json(['success' => true, 'data' => $tags]);
        } catch (Exception $e) {
            error_log("Error in tagSuggestions: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }
    
    /**
     * API: Generate auto tags based on laporan content
     * Route: POST /laporan/generateAutoTags
     */
    public function generateAutoTags() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Get JSON input or POST data
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input) && !empty($_POST)) {
                $input = $_POST;
            }
            
            // Get OPT name if master_opt_id is provided
            $namaOpt = '';
            if (!empty($input['master_opt_id'])) {
                $opt = $this->optModel->find($input['master_opt_id']);
                if ($opt) {
                    $namaOpt = $opt['nama_opt'] ?? '';
                }
            }
            
            $laporanData = [
                'catatan' => $input['catatan'] ?? '',
                'tingkat_keparahan' => $input['tingkat_keparahan'] ?? '',
                'populasi' => isset($input['populasi']) ? (float)$input['populasi'] : 0,
                'luas_serangan' => isset($input['luas_serangan']) ? (float)$input['luas_serangan'] : 0,
                'nama_opt' => $input['nama_opt'] ?? $namaOpt
            ];
            
            $tagModel = $this->model('Tag');
            $suggestions = $tagModel->generateAutoTags($laporanData);
            
            $this->json(['success' => true, 'data' => $suggestions]);
        } catch (Exception $e) {
            error_log("Error in generateAutoTags: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghasilkan tag'], 500);
        }
    }
    
    public function create() {
        // Validasi level pengguna sebelum proses pembuatan laporan dimulai
        $this->checkRole(
            ['admin', 'operator', 'petugas'],
            'Anda tidak memiliki akses untuk membuat laporan hama. Hanya akun dengan level Admin, Operator, dan Petugas yang dapat membuat laporan.'
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $user = $this->getCurrentUser();
            $userRole = $user['role'];
            
            // Role-based user_id assignment
            // Admin dapat membuat laporan atas nama user lain
            // Operator dan Petugas hanya dapat membuat laporan atas nama sendiri
            $targetUserId = $user['id'];
            if ($userRole === 'admin' && !empty($_POST['target_user_id']) && is_numeric($_POST['target_user_id'])) {
                // Verify target user exists and is active
                $userModel = $this->model('User');
                $targetUser = $userModel->find($_POST['target_user_id']);
                if ($targetUser && $targetUser['aktif'] == 1) {
                    $targetUserId = (int)$_POST['target_user_id'];
                } else {
                    $_SESSION['error'] = 'User yang dipilih tidak ditemukan atau tidak aktif';
                    $this->redirect('laporan/create');
                }
            }
            
            // Prepare post data with proper defaults
            $alamatLengkap = trim($_POST['alamat_lengkap'] ?? $_POST['lokasi'] ?? '');
            
            // Ensure lokasi is never empty (required by database)
            // If alamat_lengkap is empty, use a default value or construct from wilayah
            $lokasi = $alamatLengkap;
            if (empty($lokasi)) {
                // Try to construct from wilayah if available
                $kabId = $_POST['kabupaten_id'] ?? null;
                $kecId = $_POST['kecamatan_id'] ?? null;
                $desId = $_POST['desa_id'] ?? null;
                
                if ($kabId && $kecId && $desId && $kabId !== 'unknown' && $kecId !== 'unknown' && $desId !== 'unknown') {
                    // Will be filled after wilayah validation
                    $lokasi = 'Lokasi akan diisi setelah validasi wilayah';
                } else {
                    $lokasi = 'Lokasi belum ditentukan';
                }
            }
            
            $postData = [
                'user_id' => $targetUserId,
                'master_opt_id' => $_POST['master_opt_id'] ?? null,
                'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
                'lokasi' => $lokasi, // Always set, required by database
                'kabupaten_id' => $_POST['kabupaten_id'] !== 'unknown' ? ($_POST['kabupaten_id'] ?? null) : null,
                'kecamatan_id' => $_POST['kecamatan_id'] !== 'unknown' ? ($_POST['kecamatan_id'] ?? null) : null,
                'desa_id' => $_POST['desa_id'] !== 'unknown' ? ($_POST['desa_id'] ?? null) : null,
                'alamat_lengkap' => $alamatLengkap ?: null,
                'latitude' => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
                'longitude' => !empty($_POST['longitude']) ? $_POST['longitude'] : null,
                'tingkat_keparahan' => $_POST['tingkat_keparahan'] ?? null,
                'populasi' => isset($_POST['populasi']) && $_POST['populasi'] !== '' ? (int)$_POST['populasi'] : 0,
                'luas_serangan' => isset($_POST['luas_serangan']) && $_POST['luas_serangan'] !== '' ? (float)$_POST['luas_serangan'] : 0,
                'catatan' => $_POST['catatan'] ?? '',
                'status' => $_POST['status'] ?? 'Draf'
            ];

            // Update lokasi after wilayah validation if needed
            if (!empty($postData['kabupaten_id']) && !empty($postData['kecamatan_id']) && !empty($postData['desa_id'])) {
                // Will be updated after wilayah names are fetched
            }
            
            // Role-based validation
            $validationErrors = $this->validateLaporanData($postData, $userRole);
            if (!empty($validationErrors)) {
                $_SESSION['error'] = implode('<br>', $validationErrors);
                $this->redirect('laporan/create');
            }
            // Validate wilayah relationship if wilayah data is provided
            if (!empty($postData['kabupaten_id']) && !empty($postData['kecamatan_id']) && !empty($postData['desa_id'])) {
            $kabModel = $this->model('MasterKabupaten');
            $kecModel = $this->model('MasterKecamatan');
            $desaModel = $this->model('MasterDesa');
            $kab = $kabModel->findById($postData['kabupaten_id']);
            $kec = $kecModel->findById($postData['kecamatan_id']);
            $des = $desaModel->findById($postData['desa_id']);
                
                if (!$kab || !$kec || !$des) {
                    $_SESSION['error'] = 'Data wilayah tidak ditemukan di database';
                    $this->redirect('laporan/create');
                }
                
                if ($kec['kabupaten_id'] != $kab['id'] || $des['kecamatan_id'] != $kec['id']) {
                    $_SESSION['error'] = 'Relasi wilayah tidak valid. Pastikan kecamatan berada di kabupaten yang dipilih dan desa berada di kecamatan yang dipilih.';
                $this->redirect('laporan/create');
            }
                
            $postData['kabupaten'] = $kab['nama_kabupaten'];
            $postData['kecamatan'] = $kec['nama_kecamatan'];
            $postData['desa'] = $des['nama_desa'];

                // Update lokasi with complete address if alamat_lengkap is empty
                if (empty($postData['alamat_lengkap']) || $postData['lokasi'] === 'Lokasi akan diisi setelah validasi wilayah') {
                    $postData['lokasi'] = $kab['nama_kabupaten'] . ', ' . $kec['nama_kecamatan'] . ', ' . $des['nama_desa'];
                } else {
                    $postData['lokasi'] = $postData['alamat_lengkap'];
                }
            } else {
                // If no wilayah but alamat_lengkap exists, use it
                if (!empty($postData['alamat_lengkap'])) {
                    $postData['lokasi'] = $postData['alamat_lengkap'];
                }
                // If still empty, ensure lokasi has a value (required by database)
                if (empty($postData['lokasi'])) {
                    $postData['lokasi'] = 'Lokasi belum ditentukan';
                }
            }
            
            // Handle file upload with automatic compression
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                require_once ROOT_PATH . '/app/helpers/ImageCompressor.php';
                
                $uploadDir = UPLOAD_PATH . 'laporan/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $_FILES['foto'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                // Validate file type using finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    $_SESSION['error'] = 'Tipe file tidak diizinkan. Hanya JPG, PNG, dan WEBP yang diizinkan.';
                    $this->redirect('laporan/create');
                }
                
                // Validate file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    $_SESSION['error'] = 'Ekstensi file tidak diizinkan. Hanya JPG, PNG, dan WEBP yang diizinkan.';
                    $this->redirect('laporan/create');
                }
                
                // Generate secure filename
                $fileName = hash('sha256', time() . $file['name'] . uniqid()) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                // Move uploaded file to temporary location
                $tempPath = $file['tmp_name'];
                
                // Check if compression is needed
                if ($file['size'] > $maxSize) {
                    // File is too large, compress it
                    $compressor = new ImageCompressor();
                    $result = $compressor->compress($tempPath, $targetPath, $maxSize);
                    
                    if ($result['success']) {
                        $postData['foto_url'] = 'public/uploads/laporan/' . $fileName;
                        
                        // Set info message about compression
                        if ($result['compressed']) {
                            $originalSize = ImageCompressor::formatFileSize($result['original_size']);
                            $finalSize = ImageCompressor::formatFileSize($result['final_size']);
                            $_SESSION['info'] = "Foto berhasil dikompresi dari {$originalSize} menjadi {$finalSize} (pengurangan {$result['reduction_percent']}%)";
                        }
                    } else {
                        $_SESSION['error'] = 'Gagal mengkompresi foto: ' . ($result['error'] ?? 'Unknown error');
                        $this->redirect('laporan/create');
                    }
                } else {
                    // File size is acceptable, just move it
                    if (move_uploaded_file($tempPath, $targetPath)) {
                        $postData['foto_url'] = 'public/uploads/laporan/' . $fileName;
                    } else {
                        $_SESSION['error'] = 'Gagal mengupload file.';
                        $this->redirect('laporan/create');
                    }
                }
            }
            
            // Debug: Log postData before create (remove in production)
            error_log("Creating laporan with data: " . json_encode($postData));
            
            // Ensure required fields are not null before database insert
            if (empty($postData['lokasi'])) {
                $_SESSION['error'] = 'Field lokasi tidak boleh kosong. Pastikan alamat lengkap atau data wilayah sudah diisi.';
                $this->redirect('laporan/create');
            }
            if (empty($postData['tanggal'])) {
                $_SESSION['error'] = 'Field tanggal tidak boleh kosong.';
                $this->redirect('laporan/create');
            }
            if (empty($postData['tingkat_keparahan'])) {
                $_SESSION['error'] = 'Field tingkat_keparahan tidak boleh kosong.';
                $this->redirect('laporan/create');
            }
            
            // Try to create the report with comprehensive error handling
            try {
                $id = $this->laporanModel->create($postData);
                
                if (!$id || $id <= 0) {
                    throw new Exception('Gagal menyimpan laporan ke database. ID tidak valid.');
                }
                
                // Save tags if provided
                if (!empty($_POST['tags']) && is_array($_POST['tags'])) {
                    $tagModel = $this->model('Tag');
                    $tagIds = [];
                    
                    foreach ($_POST['tags'] as $tagInput) {
                        if (is_numeric($tagInput)) {
                            // Existing tag ID
                            $tagIds[] = (int)$tagInput;
                        } else if (!empty(trim($tagInput))) {
                            // New tag - create if not exists
                            $tagId = $tagModel->findOrCreate(trim($tagInput));
                            $tagIds[] = $tagId;
                        }
                    }
                    
                    if (!empty($tagIds)) {
                        $tagModel->setForLaporan($id, $tagIds);
                    }
                }
                
                // Log successful creation
                error_log("Laporan created successfully: ID {$id} by user {$user['id']} ({$user['role']})");
                
                // Auto-approve logic (only runs if enabled in config)
                if (defined('AUTO_APPROVE_ENABLED') && AUTO_APPROVE_ENABLED === true) {
                    $complete = !empty($postData['kabupaten_id']) && !empty($postData['kecamatan_id']) && !empty($postData['desa_id']) && !empty($postData['alamat_lengkap']);
                    $validCoords = true;
                    if (!empty($postData['latitude']) && !empty($postData['longitude'])) {
                        $lat = (float)$postData['latitude'];
                        $lon = (float)$postData['longitude'];
                        $validCoords = ($lat >= JEMBER_LAT_MIN && $lat <= JEMBER_LAT_MAX && $lon >= JEMBER_LON_MIN && $lon <= JEMBER_LON_MAX);
                    }
                    $mandatory = !empty($postData['master_opt_id']) && !empty($postData['tanggal']) && !empty($postData['tingkat_keparahan']);
                    $notSuspicious = strlen($postData['alamat_lengkap'] ?? '') >= 5;
                    if ($postData['status'] === 'Submitted' && $complete && $validCoords && $mandatory && $notSuspicious) {
                        $this->laporanModel->verify($id, 1, 'Diverifikasi', 'Auto-approve: data lokasi lengkap dan valid');
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$user['id'], 'AutoApprove', 'laporan_hama', $id, 'Laporan auto-approve', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                        // Log status history for auto-approve
                        $this->logStatusHistory($id, 'Submitted', 'Diverifikasi', 1, 'Auto-approve: data lokasi lengkap dan valid');
                    }
                }
                
                // Log status history for new reports
                $this->logStatusHistory($id, null, $postData['status'], $user['id'], 'Laporan baru dibuat');
                
                // If submitted, notify admin/operators
                if ($postData['status'] === 'Submitted') {
                    $this->notifyAdminsOperatorsNewSubmission($id, $user);
                }
                
                // Check if exceeds ETL and create notification
                $opt = $this->optModel->find($postData['master_opt_id']);
                if ($opt && $opt['etl_acuan'] > 0 && $postData['populasi'] > $opt['etl_acuan']) {
                    $this->createNotification(
                        1, // Admin user
                        'Alert ETL Terlampaui',
                        "Laporan #{$id} melampaui ETL: {$opt['nama_opt']} dengan populasi {$postData['populasi']}",
                        'danger'
                    );
                }
                
                // Role-based success message and redirect
                $successMessage = '';
                $redirectUrl = 'laporan';
                
                if ($postData['status'] === 'Diverifikasi') {
                    // Admin/Operator langsung approve
                    $successMessage = "Laporan #{$id} berhasil dibuat dan langsung diverifikasi.";
                    $redirectUrl = 'laporan?status=Diverifikasi';
                } elseif ($postData['status'] === 'Submitted') {
                    if ($userRole === 'petugas') {
                        $successMessage = "Laporan #{$id} berhasil dibuat dan disubmit untuk verifikasi. Anda akan mendapat notifikasi setelah laporan diverifikasi.";
                    } else {
                        $successMessage = "Laporan #{$id} berhasil dibuat dan disubmit untuk verifikasi.";
                    }
                    $redirectUrl = 'laporan?status=Submitted';
                } else {
                    // Draf
                    if ($userRole === 'petugas') {
                        $successMessage = "Laporan #{$id} berhasil disimpan sebagai draf. Anda dapat mengedit atau submit nanti.";
                    } else {
                        $successMessage = "Laporan #{$id} berhasil disimpan sebagai draf.";
                    }
                    $redirectUrl = 'laporan?status=Draf';
                }
                
                // Add role-specific info
                if ($userRole === 'admin' && $targetUserId != $user['id']) {
                    $userModel = $this->model('User');
                    $targetUser = $userModel->find($targetUserId);
                    if ($targetUser) {
                        $successMessage .= " Laporan dibuat atas nama: " . htmlspecialchars($targetUser['nama_lengkap']);
                    }
                }
                
                $_SESSION['success'] = $successMessage;
                $_SESSION['created_laporan_id'] = $id; // Store ID for confirmation page
                
                // Redirect to detail page for confirmation
                $this->redirect('laporan/detail/' . $id);
                
            } catch (PDOException $e) {
                error_log("Database error creating laporan: " . $e->getMessage());
                error_log("SQL Error Code: " . $e->getCode());
                error_log("Post Data: " . json_encode($postData));
                
                $errorMessage = 'Terjadi kesalahan database saat menyimpan laporan.';
                
                // Provide more specific error messages
                if (strpos($e->getMessage(), 'NOT NULL') !== false) {
                    $errorMessage .= ' Pastikan semua field wajib sudah diisi.';
                } elseif (strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
                    $errorMessage .= ' Data referensi tidak valid (user atau OPT tidak ditemukan).';
                } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errorMessage .= ' Data duplikat terdeteksi.';
                }
                
                $_SESSION['error'] = $errorMessage;
                
                // Show detailed error in debug mode
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    $_SESSION['error'] .= '<br><small>Error Detail: ' . htmlspecialchars($e->getMessage()) . '</small>';
                }
                
                $this->redirect('laporan/create');
            } catch (Exception $e) {
                error_log("Error creating laporan: " . $e->getMessage());
                error_log("Post Data: " . json_encode($postData));
                $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan laporan: ' . htmlspecialchars($e->getMessage());
                $this->redirect('laporan/create');
            }
        }
        
        $data_opt = $this->optModel->all();
        
        $data = [
            'title' => 'Buat Laporan Baru',
            'data_opt' => $data_opt
        ];
        
        $this->view('laporan/create', $data);
    }
    
    public function edit($id) {
        $this->checkRole(
            ['admin', 'operator', 'petugas'],
            'Anda tidak memiliki akses untuk mengedit laporan hama. Hanya akun dengan level Admin, Operator, dan Petugas yang dapat mengedit laporan.'
        );
        
        $laporan = $this->laporanModel->find($id);
        if (!$laporan) {
            $_SESSION['error'] = 'Laporan tidak ditemukan';
            $this->redirect('laporan');
        }
        
        // Only creator or admin can edit
        $user = $this->getCurrentUser();
        if ($laporan['user_id'] != $user['id'] && $user['role'] != 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses untuk mengedit laporan ini';
            $this->redirect('laporan');
        }
        
        // Special handling for rejected reports
        if ($laporan['status'] === 'Ditolak' && $user['role'] === 'petugas' && $laporan['user_id'] == $user['id']) {
            // Add info message for rejected reports
            if (!isset($_SESSION['info'])) {
                $_SESSION['info'] = 'Laporan ini ditolak dan perlu diperbaiki. Alasan penolakan: ' . ($laporan['catatan_verifikasi'] ?? 'Tidak ada alasan yang diberikan');
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $postData = [
                'master_opt_id' => $_POST['master_opt_id'],
                'tanggal' => $_POST['tanggal'],
                'lokasi' => $_POST['alamat_lengkap'] ?? $_POST['lokasi'] ?? '',
                'kabupaten_id' => $_POST['kabupaten_id'] !== 'unknown' ? ($_POST['kabupaten_id'] ?? null) : null,
                'kecamatan_id' => $_POST['kecamatan_id'] !== 'unknown' ? ($_POST['kecamatan_id'] ?? null) : null,
                'desa_id' => $_POST['desa_id'] !== 'unknown' ? ($_POST['desa_id'] ?? null) : null,
                'alamat_lengkap' => $_POST['alamat_lengkap'] ?? null,
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null,
                'tingkat_keparahan' => $_POST['tingkat_keparahan'],
                'populasi' => $_POST['populasi'] ?? 0,
                'luas_serangan' => $_POST['luas_serangan'] ?? 0,
                'catatan' => $_POST['catatan'] ?? '',
                'status' => $_POST['status'] ?? 'Draf'
            ];

            // Role-based validation for location fields
            $userRole = $user['role'];
            if ($userRole === 'petugas') {
                // Validasi khusus untuk role Petugas - field lokasi wajib
                $errors = [];
                
                if (empty($postData['kabupaten_id'])) {
                    $errors[] = 'Kabupaten wajib dipilih';
                }
                
                if (empty($postData['kecamatan_id'])) {
                    $errors[] = 'Kecamatan wajib dipilih';
                }
                
                if (empty($postData['desa_id'])) {
                    $errors[] = 'Desa wajib dipilih';
                }
                
                if (!empty($errors)) {
                    $_SESSION['error'] = implode('<br>', $errors);
                    $this->redirect('laporan/edit/' . $id);
                }
            } else {
                // For admin/operator, location fields are optional but if provided must be complete
                if (!empty($postData['kabupaten_id']) || !empty($postData['kecamatan_id']) || !empty($postData['desa_id']) || !empty($postData['alamat_lengkap'])) {
                    if (empty($postData['kabupaten_id']) || empty($postData['kecamatan_id']) || empty($postData['desa_id']) || empty($postData['alamat_lengkap'])) {
                        $_SESSION['error'] = 'Jika mengisi data lokasi, semua field lokasi (kabupaten, kecamatan, desa, alamat lengkap) harus diisi lengkap';
                        $this->redirect('laporan/edit/' . $id);
                    }
                }
            }
            $kabModel = $this->model('MasterKabupaten');
            $kecModel = $this->model('MasterKecamatan');
            $desaModel = $this->model('MasterDesa');
            $kab = $kabModel->findById($postData['kabupaten_id']);
            $kec = $kecModel->findById($postData['kecamatan_id']);
            $des = $desaModel->findById($postData['desa_id']);
            if (!$kab || !$kec || !$des || $kec['kabupaten_id'] != $kab['id'] || $des['kecamatan_id'] != $kec['id']) {
                $_SESSION['error'] = 'Relasi wilayah tidak valid';
                $this->redirect('laporan/edit/' . $id);
            }
            $postData['kabupaten'] = $kab['nama_kabupaten'];
            $postData['kecamatan'] = $kec['nama_kecamatan'];
            $postData['desa'] = $des['nama_desa'];

            if (!empty($postData['latitude']) && !empty($postData['longitude'])) {
                $lat = (float)$postData['latitude'];
                $lon = (float)$postData['longitude'];
                if ($lat < JEMBER_LAT_MIN || $lat > JEMBER_LAT_MAX || $lon < JEMBER_LON_MIN || $lon > JEMBER_LON_MAX) {
                    $_SESSION['error'] = 'Koordinat GPS harus berada dalam wilayah Jember';
                    $this->redirect('laporan/edit/' . $id);
                }
            }
            
            // Handle file upload with automatic compression
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                require_once ROOT_PATH . '/app/helpers/ImageCompressor.php';
                
                $uploadDir = UPLOAD_PATH . 'laporan/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $_FILES['foto'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                // Validate file type using finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    $_SESSION['error'] = 'Tipe file tidak diizinkan. Hanya JPG, PNG, dan WEBP yang diizinkan.';
                    $this->redirect('laporan/edit/' . $id);
                }
                
                // Validate file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    $_SESSION['error'] = 'Ekstensi file tidak diizinkan. Hanya JPG, PNG, dan WEBP yang diizinkan.';
                    $this->redirect('laporan/edit/' . $id);
                }
                
                // Generate secure filename
                $fileName = hash('sha256', time() . $file['name'] . uniqid()) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                // Move uploaded file to temporary location
                $tempPath = $file['tmp_name'];
                
                // Check if compression is needed
                if ($file['size'] > $maxSize) {
                    // File is too large, compress it
                    $compressor = new ImageCompressor();
                    $result = $compressor->compress($tempPath, $targetPath, $maxSize);
                    
                    if ($result['success']) {
                        $postData['foto_url'] = 'public/uploads/laporan/' . $fileName;
                        
                        // Delete old photo if exists
                        if (!empty($laporan['foto_url']) && file_exists(ROOT_PATH . '/' . $laporan['foto_url'])) {
                            unlink(ROOT_PATH . '/' . $laporan['foto_url']);
                        }
                        
                        // Set info message about compression
                        if ($result['compressed']) {
                            $originalSize = ImageCompressor::formatFileSize($result['original_size']);
                            $finalSize = ImageCompressor::formatFileSize($result['final_size']);
                            $_SESSION['info'] = "Foto berhasil dikompresi dari {$originalSize} menjadi {$finalSize} (pengurangan {$result['reduction_percent']}%)";
                        }
                    } else {
                        $_SESSION['error'] = 'Gagal mengkompresi foto: ' . ($result['error'] ?? 'Unknown error');
                        $this->redirect('laporan/edit/' . $id);
                    }
                } else {
                    // File size is acceptable, just move it
                    if (move_uploaded_file($tempPath, $targetPath)) {
                        $postData['foto_url'] = 'public/uploads/laporan/' . $fileName;
                        
                        // Delete old photo if exists
                        if (!empty($laporan['foto_url']) && file_exists(ROOT_PATH . '/' . $laporan['foto_url'])) {
                            unlink(ROOT_PATH . '/' . $laporan['foto_url']);
                        }
                    } else {
                        $_SESSION['error'] = 'Gagal mengupload file.';
                        $this->redirect('laporan/edit/' . $id);
                    }
                }
            }
            
            $this->laporanModel->update($id, $postData);

            // Auto-approve logic (only runs if enabled in config)
            if (defined('AUTO_APPROVE_ENABLED') && AUTO_APPROVE_ENABLED === true) {
                $complete = !empty($postData['kabupaten_id']) && !empty($postData['kecamatan_id']) && !empty($postData['desa_id']) && !empty($postData['alamat_lengkap']);
                $validCoords = true;
                if (!empty($postData['latitude']) && !empty($postData['longitude'])) {
                    $lat = (float)$postData['latitude'];
                    $lon = (float)$postData['longitude'];
                    $validCoords = ($lat >= JEMBER_LAT_MIN && $lat <= JEMBER_LAT_MAX && $lon >= JEMBER_LON_MIN && $lon <= JEMBER_LON_MAX);
                }
                $mandatory = !empty($postData['master_opt_id']) && !empty($postData['tanggal']) && !empty($postData['tingkat_keparahan']);
                $notSuspicious = strlen($postData['alamat_lengkap']) >= 5;
                if ($postData['status'] === 'Submitted' && $complete && $validCoords && $mandatory && $notSuspicious) {
                    $user = $this->getCurrentUser();
                    $this->laporanModel->verify($id, $user['id'], 'Diverifikasi', 'Auto-approve setelah edit: data lokasi lengkap dan valid');
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user['id'], 'AutoApprove', 'laporan_hama', $id, 'Laporan auto-approve setelah edit', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                }
            }
            
            // Special handling for rejected reports that are being resubmitted
            if ($laporan['status'] === 'Ditolak' && $postData['status'] === 'Submitted') {
                // Log the resubmission
                $this->logStatusHistory($id, 'Ditolak', 'Submitted', $user['id'], 'Laporan diperbaiki dan disubmit ulang');
                
                // Notify admins/operators about resubmission
                $this->notifyAdminsOperatorsResubmission($id, $user, $laporan['catatan_verifikasi']);
                
                $_SESSION['success'] = 'Laporan berhasil diperbaiki dan disubmit ulang untuk verifikasi';
                $this->redirect('laporan?status=Submitted');
            } else {
                $_SESSION['success'] = 'Laporan berhasil diupdate';
                $this->redirect('laporan');
            }
        }
        
        $data_opt = $this->optModel->all();
        
        $data = [
            'title' => 'Edit Laporan',
            'laporan' => $laporan,
            'data_opt' => $data_opt
        ];
        
        $this->view('laporan/edit', $data);
    }
    
    public function detail($id) {
        $this->checkAuth();
        
        $sql = "SELECT 
                    lh.*,
                    mo.kode_opt,
                    mo.nama_opt,
                    mo.jenis,
                    mo.etl_acuan,
                    mo.rekomendasi,
                    u.nama_lengkap as pelapor_nama,
                    u.email as pelapor_email,
                    u.phone as pelapor_phone,
                    v.nama_lengkap as verifikator_nama
                FROM laporan_hama lh
                LEFT JOIN master_opt mo ON lh.master_opt_id = mo.id
                LEFT JOIN users u ON lh.user_id = u.id
                LEFT JOIN users v ON lh.verified_by = v.id
                WHERE lh.id = ?";
        
        $result = $this->laporanModel->query($sql, [$id]);
        $laporan = $result[0] ?? null;
        
        if (!$laporan) {
            $_SESSION['error'] = 'Laporan tidak ditemukan';
            $this->redirect('laporan');
        }
        
        // Fetch status history
        $statusHistory = $this->getStatusHistory($id);
        
        $data = [
            'title' => 'Detail Laporan',
            'laporan' => $laporan,
            'statusHistory' => $statusHistory
        ];
        
        $this->view('laporan/view', $data);
    }
    
    private function getStatusHistory($laporanId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT sh.*, u.nama_lengkap as changed_by_name 
                FROM laporan_status_history sh 
                LEFT JOIN users u ON sh.changed_by = u.id 
                WHERE sh.laporan_id = ? 
                ORDER BY sh.created_at DESC
            ");
            $stmt->execute([$laporanId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Table might not exist yet
            error_log("Failed to get status history: " . $e->getMessage());
            return [];
        }
    }
    
    public function verify($id) {
        $this->checkRole(['admin', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                // Validate CSRF token for AJAX
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['csrf_token']) || !$this->validateCsrfTokenAjax($input['csrf_token'])) {
                    $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
                }
                
                // Get the current report
                $laporan = $this->laporanModel->find($id);
                if (!$laporan) {
                    $this->json(['success' => false, 'message' => 'Laporan tidak ditemukan'], 404);
                }
                
                // Validate status transition: only Submitted can be verified/rejected
                if ($laporan['status'] !== 'Submitted') {
                    $this->json(['success' => false, 'message' => 'Hanya laporan dengan status "Submitted" yang dapat diverifikasi'], 400);
                }
                
                $user = $this->getCurrentUser();
                $status = $input['status'] ?? '';
                $catatan = $input['catatan_verifikasi'] ?? '';
                
                // Validate status value
                if (!in_array($status, ['Diverifikasi', 'Ditolak'])) {
                    $this->json(['success' => false, 'message' => 'Status verifikasi tidak valid'], 400);
                }
                
                // Require comment for rejection
                if ($status === 'Ditolak' && empty(trim($catatan))) {
                    $this->json(['success' => false, 'message' => 'Alasan penolakan wajib diisi'], 400);
                }
                
                try {
                    // Perform verification
                    $this->laporanModel->verify($id, $user['id'], $status, $catatan);
                    
                    // Log to status history table
                    $this->logStatusHistory($id, 'Submitted', $status, $user['id'], $catatan);
                    
                    // Log to activity_log
                    $db = Database::getInstance()->getConnection();
                    $action = $status === 'Diverifikasi' ? 'VerifyReport' : 'RejectReport';
                    $description = $status === 'Diverifikasi'
                        ? "Laporan #{$id} diverifikasi"
                        : "Laporan #{$id} ditolak: {$catatan}";
                    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user['id'], $action, 'laporan_hama', $id, $description, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                    
                    // Create notification for the report creator
                    if ($laporan['user_id']) {
                        $notifTitle = $status === 'Diverifikasi'
                            ? 'Laporan Anda Telah Diverifikasi'
                            : 'Laporan Anda Ditolak';
                        $notifMessage = $status === 'Diverifikasi'
                            ? "Laporan #{$id} telah diverifikasi oleh {$user['nama_lengkap']}"
                            : "Laporan #{$id} ditolak oleh {$user['nama_lengkap']}. Alasan: {$catatan}";
                        $notifType = $status === 'Diverifikasi' ? 'success' : 'danger';
                        
                        $this->createNotification($laporan['user_id'], $notifTitle, $notifMessage, $notifType);
                    }
                    
                    $successMsg = $status === 'Diverifikasi'
                        ? 'Laporan berhasil diverifikasi'
                        : 'Laporan berhasil ditolak';
                    
                    $this->json(['success' => true, 'message' => $successMsg]);
                    
                } catch (Exception $e) {
                    error_log("Verification error: " . $e->getMessage());
                    $this->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses laporan'], 500);
                }
            } else {
                // Handle traditional form submission
                $this->validateCsrfToken();
                
                // Get the current report
                $laporan = $this->laporanModel->find($id);
                if (!$laporan) {
                    $_SESSION['error'] = 'Laporan tidak ditemukan';
                    $this->redirect('laporan');
                }
                
                // Validate status transition: only Submitted can be verified/rejected
                if ($laporan['status'] !== 'Submitted') {
                    $_SESSION['error'] = 'Hanya laporan dengan status "Submitted" yang dapat diverifikasi';
                    $this->redirect('laporan/detail/' . $id);
                }
                
                $user = $this->getCurrentUser();
                $status = $_POST['status'];
                $catatan = $_POST['catatan_verifikasi'] ?? '';
                
                // Validate status value
                if (!in_array($status, ['Diverifikasi', 'Ditolak'])) {
                    $_SESSION['error'] = 'Status verifikasi tidak valid';
                    $this->redirect('laporan/detail/' . $id);
                }
                
                // Require comment for rejection
                if ($status === 'Ditolak' && empty(trim($catatan))) {
                    $_SESSION['error'] = 'Alasan penolakan wajib diisi';
                    $this->redirect('laporan/detail/' . $id);
                }
                
                // Perform verification
                $this->laporanModel->verify($id, $user['id'], $status, $catatan);
                
                // Log to status history table
                $this->logStatusHistory($id, 'Submitted', $status, $user['id'], $catatan);
                
                // Log to activity_log
                $db = Database::getInstance()->getConnection();
                $action = $status === 'Diverifikasi' ? 'VerifyReport' : 'RejectReport';
                $description = $status === 'Diverifikasi'
                    ? "Laporan #{$id} diverifikasi"
                    : "Laporan #{$id} ditolak: {$catatan}";
                $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $action, 'laporan_hama', $id, $description, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                
                // Create notification for the report creator
                if ($laporan['user_id']) {
                    $notifTitle = $status === 'Diverifikasi'
                        ? 'Laporan Anda Telah Diverifikasi'
                        : 'Laporan Anda Ditolak';
                    $notifMessage = $status === 'Diverifikasi'
                        ? "Laporan #{$id} telah diverifikasi oleh {$user['nama_lengkap']}"
                        : "Laporan #{$id} ditolak oleh {$user['nama_lengkap']}. Alasan: {$catatan}";
                    $notifType = $status === 'Diverifikasi' ? 'success' : 'danger';
                    
                    $this->createNotification($laporan['user_id'], $notifTitle, $notifMessage, $notifType);
                }
                
                $successMsg = $status === 'Diverifikasi'
                    ? 'Laporan berhasil diverifikasi'
                    : 'Laporan berhasil ditolak';
                $_SESSION['success'] = $successMsg;
                
                // Check if redirect_to parameter is set (for AJAX calls from index page)
                $redirectTo = $_POST['redirect_to'] ?? 'detail';
                if ($redirectTo === 'index') {
                    $this->redirect('laporan?status=Submitted');
                } else {
                    $this->redirect('laporan/detail/' . $id);
                }
            }
        }
    }
    
    private function logStatusHistory($laporanId, $oldStatus, $newStatus, $userId, $komentar = '') {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO laporan_status_history (laporan_id, old_status, new_status, changed_by, komentar) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$laporanId, $oldStatus, $newStatus, $userId, $komentar]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to log status history: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        $this->checkRole(
            ['admin', 'operator', 'petugas'],
            'Anda tidak memiliki akses untuk menghapus laporan hama. Hanya akun dengan level Admin, Operator, dan Petugas yang dapat menghapus laporan.'
        );
        
        $laporan = $this->laporanModel->find($id);
        if (!$laporan) {
            $_SESSION['error'] = 'Laporan tidak ditemukan';
            $this->redirect('laporan');
        }

        // Check ownership for petugas
        $user = $this->getCurrentUser();
        if ($user['role'] === 'petugas' && $laporan['user_id'] != $user['id']) {
            $_SESSION['error'] = 'Anda hanya dapat menghapus laporan yang Anda buat sendiri';
            $this->redirect('laporan');
        }
        
        $this->laporanModel->delete($id);
        $_SESSION['success'] = 'Laporan berhasil dihapus';
        $this->redirect('laporan');
    }
    
    public function bulkDelete() {
        $this->checkRole(['admin']);
        
        // Handle AJAX request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            // Validate CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfTokenAjax($token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid']);
                exit;
            }
            
            $ids = $_POST['ids'] ?? [];
            
            if (empty($ids) || !is_array($ids)) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
                exit;
            }
            
            // Validate all IDs are numeric
            foreach ($ids as $id) {
                if (!is_numeric($id)) {
                    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
                    exit;
                }
            }
            
            try {
                $deletedCount = 0;
                foreach ($ids as $id) {
                    if ($this->laporanModel->delete($id)) {
                        $deletedCount++;
                    }
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Berhasil menghapus {$deletedCount} laporan",
                    'count' => $deletedCount
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
            exit;
        }
    }
    
    private function validateCsrfTokenAjax($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    private function createNotification($userId, $title, $message, $type = 'info') {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $message, $type]);
    }
    
    private function notifyAdminsOperatorsNewSubmission($laporanId, $creator) {
        try {
            $db = Database::getInstance()->getConnection();
            // Get all admin and operator users
            $stmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'operator') AND aktif = 1");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($admins as $admin) {
                $this->createNotification(
                    $admin['id'],
                    'Laporan Baru Menunggu Verifikasi',
                    "Laporan #{$laporanId} telah disubmit oleh {$creator['nama_lengkap']} dan menunggu verifikasi Anda.",
                    'warning'
                );
            }
        } catch (Exception $e) {
            error_log("Failed to notify admins/operators: " . $e->getMessage());
        }
    }
    
    private function notifyAdminsOperatorsResubmission($laporanId, $creator, $previousRejectionReason) {
        try {
            $db = Database::getInstance()->getConnection();
            // Get all admin and operator users
            $stmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'operator') AND aktif = 1");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($admins as $admin) {
                $this->createNotification(
                    $admin['id'],
                    'Laporan Diperbaiki - Menunggu Verifikasi Ulang',
                    "Laporan #{$laporanId} telah diperbaiki oleh {$creator['nama_lengkap']} setelah sebelumnya ditolak. Alasan penolakan sebelumnya: " . substr($previousRejectionReason, 0, 100) . (strlen($previousRejectionReason) > 100 ? '...' : ''),
                    'info'
                );
            }
        } catch (Exception $e) {
            error_log("Failed to notify admins/operators about resubmission: " . $e->getMessage());
        }
    }
    
    /**
     * Validate laporan data based on user role
     * Different validation rules for admin, operator, and petugas
     */
    private function validateLaporanData($data, $userRole) {
        $errors = [];
        
        // Common validations for all roles
        if (empty($data['master_opt_id'])) {
            $errors[] = 'OPT wajib dipilih';
        }
        
        if (empty($data['tanggal'])) {
            $errors[] = 'Tanggal pelaporan wajib diisi';
        } else {
            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
            if (!$date || $date->format('Y-m-d') !== $data['tanggal']) {
                $errors[] = 'Format tanggal tidak valid';
            }
            // Check if date is not in the future
            if ($date > new DateTime()) {
                $errors[] = 'Tanggal pelaporan tidak boleh di masa depan';
            }
        }
        
        if (empty($data['tingkat_keparahan'])) {
            $errors[] = 'Tingkat keparahan wajib dipilih';
        } else {
            $allowedSeverity = ['Ringan', 'Sedang', 'Berat'];
            if (!in_array($data['tingkat_keparahan'], $allowedSeverity)) {
                $errors[] = 'Tingkat keparahan tidak valid';
            }
        }
        
        // Role-specific validations
        if ($userRole === 'petugas') {
            // Petugas: Validasi lebih ketat, semua field lokasi wajib
            if (empty($data['kabupaten_id']) || empty($data['kecamatan_id']) || empty($data['desa_id'])) {
                $errors[] = 'Data lokasi lengkap (kabupaten, kecamatan, desa) wajib diisi untuk petugas';
            }
            
            if (empty($data['alamat_lengkap']) || strlen(trim($data['alamat_lengkap'])) < 10) {
                $errors[] = 'Alamat lengkap wajib diisi minimal 10 karakter untuk petugas';
            }
            
            // Petugas hanya dapat membuat dengan status Draf atau Submitted
            if (!in_array($data['status'], ['Draf', 'Submitted'])) {
                $errors[] = 'Status tidak valid untuk petugas. Hanya Draf dan Submitted yang diizinkan.';
            }
            
            // Petugas wajib mengisi populasi jika tingkat keparahan Berat
            if ($data['tingkat_keparahan'] === 'Berat' && empty($data['populasi'])) {
                $errors[] = 'Populasi wajib diisi untuk tingkat keparahan Berat';
            }
            
        } elseif ($userRole === 'operator') {
            // Operator: Validasi standar
            if (empty($data['kabupaten_id']) || empty($data['kecamatan_id']) || empty($data['desa_id'])) {
                $errors[] = 'Data lokasi lengkap (kabupaten, kecamatan, desa) wajib diisi';
            }
            
            if (empty($data['alamat_lengkap']) || strlen(trim($data['alamat_lengkap'])) < 5) {
                $errors[] = 'Alamat lengkap wajib diisi minimal 5 karakter';
            }
            
            // Operator dapat membuat dengan status Draf, Submitted, atau Diverifikasi
            if (!in_array($data['status'], ['Draf', 'Submitted', 'Diverifikasi'])) {
                $errors[] = 'Status tidak valid untuk operator';
            }
            
        } elseif ($userRole === 'admin') {
            // Admin: Validasi lebih fleksibel
            // Admin dapat membuat laporan dengan lokasi tidak lengkap (untuk data entry)
            if (empty($data['alamat_lengkap'])) {
                $errors[] = 'Alamat lengkap wajib diisi';
            }
            
            // Admin dapat membuat dengan status apapun
            $allowedStatuses = ['Draf', 'Submitted', 'Diverifikasi', 'Ditolak'];
            if (!in_array($data['status'], $allowedStatuses)) {
                $errors[] = 'Status tidak valid';
            }
            
            // Admin dapat langsung approve (Diverifikasi)
            if ($data['status'] === 'Diverifikasi' && (empty($data['kabupaten_id']) || empty($data['kecamatan_id']) || empty($data['desa_id']))) {
                $errors[] = 'Data lokasi lengkap wajib diisi untuk laporan yang langsung diverifikasi';
            }
        }
        
        // Validate numeric fields
        if (isset($data['populasi']) && $data['populasi'] < 0) {
            $errors[] = 'Populasi tidak boleh negatif';
        }
        
        if (isset($data['luas_serangan']) && $data['luas_serangan'] < 0) {
            $errors[] = 'Luas serangan tidak boleh negatif';
        }
        
        // Validate luas serangan tidak boleh melebihi populasi (boleh sama dengan)
        if (isset($data['populasi']) && isset($data['luas_serangan'])) {
            $populasi = (float)$data['populasi'];
            $luasSerangan = (float)$data['luas_serangan'];
            
            // Only validate if both values are provided and greater than 0
            // Luas serangan boleh sama dengan populasi (<=), tidak boleh lebih besar (>)
            if ($populasi > 0 && $luasSerangan > 0 && $luasSerangan > $populasi) {
                $errors[] = 'Luas Serangan (' . number_format($luasSerangan, 2) . ' Ha) tidak boleh melebihi Populasi/Intensitas (' . number_format($populasi, 2) . ' Ha). Nilai maksimal yang diizinkan: ' . number_format($populasi, 2) . ' Ha';
            }
        }
        
        // Validate GPS coordinates if provided
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $lat = (float)$data['latitude'];
            $lon = (float)$data['longitude'];
            
            if ($lat < -90 || $lat > 90) {
                $errors[] = 'Latitude harus antara -90 dan 90';
            }
            
            if ($lon < -180 || $lon > 180) {
                $errors[] = 'Longitude harus antara -180 dan 180';
            }
            
            // Check Jember boundaries if both provided
            if ($lat < JEMBER_LAT_MIN || $lat > JEMBER_LAT_MAX || 
                $lon < JEMBER_LON_MIN || $lon > JEMBER_LON_MAX) {
                $errors[] = 'Koordinat GPS harus berada dalam wilayah Jember';
            }
        }
        
        return $errors;
    }
}
