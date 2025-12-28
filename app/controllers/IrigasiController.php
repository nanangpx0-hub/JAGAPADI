<?php
/**
 * IrigasiController
 * * Menangani fitur pelaporan dan monitoring sebaran irigasi.
 * Mengimplementasikan RBAC (Role-Based Access Control).
 * * @package app/controllers
 */
class IrigasiController extends Controller {
    
    private $model;
    private $wilayahModel;

    public function __construct() {
        $this->model = $this->model('LaporanIrigasi');
        // Load model wilayah untuk form dropdown
        $this->wilayahModel = $this->model('MasterKabupaten'); 
    }

    /**
     * READ: Menampilkan daftar laporan
     * Rule: Petugas hanya melihat data sendiri, Admin/Operator melihat semua.
     */
    public function index() {
        $this->checkAuth();
        $user = $this->getCurrentUser();
        
        $laporan = [];
        
        if ($user['role'] === 'petugas') {
            // Filter khusus petugas
            $laporan = $this->model->getAllWithDetails($user['id']);
        } else {
            // Admin & Operator lihat semua
            $laporan = $this->model->getAllWithDetails();
        }

        $this->view('irigasi/index', [
            'title' => 'Sebaran Irigasi',
            'laporan' => $laporan,
            'userRole' => $user['role']
        ]);
    }

    /**
     * CREATE: Form tambah laporan baru
     */
    public function create() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();

            try {
                // Log incoming POST data for debugging
                error_log("Irigasi Create - POST data: " . json_encode($_POST));
                
                // Validasi input
                $errors = $this->validateInput($_POST);
                if (!empty($errors)) {
                    error_log("Irigasi Create - Validation errors: " . implode(', ', $errors));
                    ErrorMessage::set(implode(', ', $errors));
                    $this->view('irigasi/create', [
                        'title' => 'Input Data Irigasi',
                        'kabupaten' => $this->wilayahModel->getAllOrdered(),
                        'data' => $_POST
                    ]);
                    return;
                }

                // Generate Unique ID
                $noLaporan = $this->generateUniqueId();

                // Sanitasi input dasar
                $kondisiFisik = !empty($_POST['kondisi_fisik']) ? $_POST['kondisi_fisik'] : 'Baik';
                
                // Logic: If kondisi Baik, status must be "Normal"
                // If kondisi Rusak, status must be one of the repair options
                $statusPerbaikan = $_POST['status_perbaikan'];
                
                // Auto-set status to "Normal" if kondisi is "Baik"
                if ($kondisiFisik === 'Baik') {
                    $statusPerbaikan = 'Normal';
                }
                
                $data = [
                    'no_laporan' => $noLaporan,
                    'user_id' => $_SESSION['user_id'],
                    'nama_pelapor' => trim(Security::sanitizeInput($_POST['nama_pelapor'])),
                    'kabupaten_id' => !empty($_POST['kabupaten_id']) ? (int)$_POST['kabupaten_id'] : null,
                    'kecamatan_id' => !empty($_POST['kecamatan_id']) ? (int)$_POST['kecamatan_id'] : null,
                    'desa_id' => !empty($_POST['desa_id']) ? (int)$_POST['desa_id'] : null,
                    'nama_saluran' => trim(Security::sanitizeInput($_POST['nama_saluran'])),
                    'jenis_saluran' => $_POST['jenis_saluran'],
                    'jenis_irigasi' => !empty($_POST['jenis_irigasi']) ? $_POST['jenis_irigasi'] : 'Teknis',
                    'kondisi_fisik' => $kondisiFisik,
                    'debit_air' => !empty($_POST['debit_air']) ? $_POST['debit_air'] : 'Cukup',
                    'status_perbaikan' => $statusPerbaikan,
                    'aksi_dilakukan' => Security::sanitizeInput($_POST['aksi_dilakukan'] ?? ''),
                    'luas_layanan' => (float)$_POST['luas_layanan'],
                    'tanggal' => $_POST['tanggal'],
                    'latitude' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                    'longitude' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                    'catatan' => Security::sanitizeInput($_POST['catatan'] ?? ''),
                    'status' => $_POST['status'] ?? 'Submitted'
                ];

                // Handle File Upload with Automatic Compression
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    require_once ROOT_PATH . '/app/helpers/ImageCompressor.php';
                    
                    $uploadDir = UPLOAD_PATH . 'irigasi/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $file = $_FILES['foto'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    // Validate file type using finfo
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($mimeType, $allowedTypes)) {
                        ErrorMessage::set('Tipe file tidak diizinkan. Hanya JPG, PNG, dan GIF yang diizinkan.');
                        $this->view('irigasi/create', [
                            'title' => 'Input Data Irigasi',
                            'kabupaten' => $this->wilayahModel->getAllOrdered(),
                            'data' => $_POST
                        ]);
                        return;
                    }
                    
                    // Validate file extension
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions)) {
                        ErrorMessage::set('Ekstensi file tidak diizinkan. Hanya JPG, PNG, dan GIF yang diizinkan.');
                        $this->view('irigasi/create', [
                            'title' => 'Input Data Irigasi',
                            'kabupaten' => $this->wilayahModel->getAllOrdered(),
                            'data' => $_POST
                        ]);
                        return;
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
                            $data['foto_url'] = 'public/uploads/irigasi/' . $fileName;
                            
                            // Set info message about compression
                            if ($result['compressed']) {
                                $originalSize = ImageCompressor::formatFileSize($result['original_size']);
                                $finalSize = ImageCompressor::formatFileSize($result['final_size']);
                                $_SESSION['info'] = "✅ Foto berhasil dikompresi dari {$originalSize} menjadi {$finalSize} (pengurangan {$result['reduction_percent']}%). Ukuran file sekarang sesuai batas maksimal.";
                            }
                        } else {
                            ErrorMessage::set('Gagal mengkompresi foto: ' . ($result['error'] ?? 'Unknown error'));
                            $this->view('irigasi/create', [
                                'title' => 'Input Data Irigasi',
                                'kabupaten' => $this->wilayahModel->getAllOrdered(),
                                'data' => $_POST
                            ]);
                            return;
                        }
                    } else {
                        // File size is acceptable, just move it
                        if (move_uploaded_file($tempPath, $targetPath)) {
                            $data['foto_url'] = 'public/uploads/irigasi/' . $fileName;
                        } else {
                            ErrorMessage::set('Gagal mengupload file.');
                            $this->view('irigasi/create', [
                                'title' => 'Input Data Irigasi',
                                'kabupaten' => $this->wilayahModel->getAllOrdered(),
                                'data' => $_POST
                            ]);
                            return;
                        }
                    }
                }

                // Attempt to create record
                $this->model->create($data);
                
                ErrorMessage::setSuccess('Data irigasi berhasil disimpan dengan ID: ' . $noLaporan);
                $this->redirect('irigasi/index');

            } catch (Exception $e) {
                error_log("Irigasi Create Exception: " . $e->getMessage());
                ErrorMessage::set('Gagal menyimpan data: ' . $e->getMessage());
                $this->view('irigasi/create', [
                    'title' => 'Input Data Irigasi',
                    'kabupaten' => $this->wilayahModel->getAllOrdered(),
                    'data' => $_POST
                ]);
                return;
            }
        }

        // Load data master untuk dropdown
        $kabupaten = $this->wilayahModel->getAllOrdered();

        $this->view('irigasi/create', [
            'title' => 'Input Data Irigasi',
            'kabupaten' => $kabupaten
        ]);
    }

    /**
     * Validate input data
     * @param array $data
     * @return array Array of error messages
     */
    private function validateInput(array $data): array {
        $errors = [];

        // Required fields - Nama Pelapor
        if (empty($data['nama_pelapor'])) {
            $errors[] = 'Nama pelapor wajib diisi';
        }

        // Required fields - Nama Irigasi
        if (empty($data['nama_saluran'])) {
            $errors[] = 'Nama irigasi wajib diisi';
        } elseif (strlen(trim($data['nama_saluran'])) < 3) {
            $errors[] = 'Nama irigasi minimal 3 karakter';
        } elseif (strlen($data['nama_saluran']) > 100) {
            $errors[] = 'Nama irigasi maksimal 100 karakter';
        }

        // Required fields - Jenis Saluran
        if (empty($data['jenis_saluran'])) {
            $errors[] = 'Jenis saluran wajib dipilih';
        }

        // Required fields - Kapasitas
        if (empty($data['luas_layanan'])) {
            $errors[] = 'Kapasitas layanan wajib diisi';
        } elseif (!is_numeric($data['luas_layanan']) || (float)$data['luas_layanan'] <= 0) {
            $errors[] = 'Kapasitas layanan harus berupa angka positif';
        }

        // Required fields - Tanggal
        if (empty($data['tanggal'])) {
            $errors[] = 'Tanggal laporan wajib diisi';
        } elseif (strtotime($data['tanggal']) > strtotime('today')) {
            $errors[] = 'Tanggal tidak boleh melebihi hari ini';
        }

        // Required fields - Foto Irigasi (v2.2.1)
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            if (!isset($_FILES['foto'])) {
                $errors[] = 'Foto irigasi wajib diupload';
            } elseif ($_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Foto irigasi wajib diupload';
            } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengupload foto';
            }
        }

        // Required fields - Kondisi (Radio)
        if (empty($data['kondisi_fisik'])) {
            $errors[] = 'Kondisi saluran wajib dipilih';
        }

        // Required fields - Status Perbaikan (logic based on kondisi)
        if (empty($data['status_perbaikan'])) {
            $errors[] = 'Status perbaikan wajib dipilih';
        } else {
            // Validate status based on kondisi
            $kondisi = $data['kondisi_fisik'] ?? '';
            $status = $data['status_perbaikan'];
            
            if ($kondisi === 'Baik' && $status !== 'Normal') {
                $errors[] = 'Status harus "Normal" ketika kondisi saluran baik';
            } elseif (in_array($kondisi, ['Rusak Ringan', 'Rusak Berat'])) {
                $validStatuses = ['Selesai Diperbaiki', 'Dalam Perbaikan', 'Belum Ditangani'];
                if (!in_array($status, $validStatuses)) {
                    $errors[] = 'Status perbaikan tidak valid. Pilih: Selesai Diperbaiki, Dalam Perbaikan, atau Belum Ditangani';
                }
            }
        }

        // Status workflow validation
        if (isset($data['status']) && !in_array($data['status'], ['Draf', 'Submitted', 'Diverifikasi', 'Ditolak'])) {
            $errors[] = 'Status workflow tidak valid';
        }

        return $errors;
    }

    /**
     * Generate Unique ID for Irrigation Report
     * Format: IRG-YYYYMMDD-XXXX
     */
    private function generateUniqueId() {
        $prefix = 'IRG-' . date('Ymd') . '-';
        $db = Database::getInstance()->getConnection();
        
        // Count reports today
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM laporan_irigasi WHERE tanggal = ?");
        $stmt->execute([date('Y-m-d')]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['total'] + 1;
        
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * VERIFY: Verifikasi laporan (Hanya Operator/Admin)
     */
    public function verify($id) {
        $this->checkAuth();
        $this->checkRole(['operator', 'admin'], 'Anda tidak memiliki akses verifikasi.');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            
            $status = $_POST['status'];
            $catatan = $_POST['catatan_verifikasi'] ?? null;
            
            try {
                $this->model->verify($id, $status, $_SESSION['user_id'], $catatan);
                ErrorMessage::setSuccess('Laporan berhasil ' . ($status == 'Diverifikasi' ? 'diverifikasi' : 'ditolak'));
            } catch (Exception $e) {
                error_log("Irigasi Verify Error: " . $e->getMessage());
                ErrorMessage::set('Gagal memproses verifikasi: ' . $e->getMessage());
            }
            
            $this->redirect('irigasi/index');
        }
    }

    /**
     * READ: Detail laporan irigasi
     */
    public function detail($id) {
        $this->checkAuth();
        
        $data = $this->model->getDetailById($id);
        
        if (!$data) {
            ErrorMessage::set('Data irigasi tidak ditemukan');
            $this->redirect('irigasi/index');
            return;
        }
        
        // Check access for petugas
        $user = $this->getCurrentUser();
        if ($user['role'] === 'petugas' && $data['user_id'] != $user['id']) {
            ErrorMessage::set('Anda tidak memiliki akses ke data ini');
            $this->redirect('irigasi/index');
            return;
        }
        
        $this->view('irigasi/detail', [
            'title' => 'Detail Laporan Irigasi',
            'data' => $data,
            'userRole' => $user['role']
        ]);
    }

    /**
     * UPDATE: Edit laporan irigasi
     */
    public function edit($id) {
        $this->checkAuth();
        
        $data = $this->model->find($id);
        
        if (!$data) {
            ErrorMessage::set('Data irigasi tidak ditemukan');
            $this->redirect('irigasi/index');
            return;
        }
        
        // Check access
        $user = $this->getCurrentUser();
        if ($user['role'] === 'petugas' && $data['user_id'] != $user['id']) {
            ErrorMessage::set('Anda tidak memiliki akses ke data ini');
            $this->redirect('irigasi/index');
            return;
        }
        
        // Only allow editing Draft or Rejected
        if (!in_array($data['status'], ['Draf', 'Ditolak'])) {
            ErrorMessage::set('Laporan yang sudah disubmit atau diverifikasi tidak dapat diedit');
            $this->redirect('irigasi/index');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            
            try {
                // Validate input
                $errors = $this->validateInput($_POST);
                
                // For edit, photo is optional if already exists
                if ((!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) && !empty($data['foto_url'])) {
                    // Remove photo validation error if photo already exists
                    $errors = array_filter($errors, function($err) {
                        return strpos($err, 'Foto') === false;
                    });
                }
                
                if (!empty($errors)) {
                    ErrorMessage::set(implode(', ', $errors));
                    $this->view('irigasi/edit', [
                        'title' => 'Edit Data Irigasi',
                        'data' => array_merge($data, $_POST),
                        'kabupaten' => $this->wilayahModel->getAllOrdered()
                    ]);
                    return;
                }
                
                // Build update data
                $kondisiFisik = !empty($_POST['kondisi_fisik']) ? $_POST['kondisi_fisik'] : 'Baik';
                $statusPerbaikan = $_POST['status_perbaikan'];
                
                if ($kondisiFisik === 'Baik') {
                    $statusPerbaikan = 'Normal';
                }
                
                $updateData = [
                    'nama_pelapor' => trim(Security::sanitizeInput($_POST['nama_pelapor'])),
                    'kabupaten_id' => !empty($_POST['kabupaten_id']) ? (int)$_POST['kabupaten_id'] : null,
                    'kecamatan_id' => !empty($_POST['kecamatan_id']) ? (int)$_POST['kecamatan_id'] : null,
                    'desa_id' => !empty($_POST['desa_id']) ? (int)$_POST['desa_id'] : null,
                    'nama_saluran' => trim(Security::sanitizeInput($_POST['nama_saluran'])),
                    'jenis_saluran' => $_POST['jenis_saluran'],
                    'jenis_irigasi' => !empty($_POST['jenis_irigasi']) ? $_POST['jenis_irigasi'] : 'Teknis',
                    'kondisi_fisik' => $kondisiFisik,
                    'debit_air' => !empty($_POST['debit_air']) ? $_POST['debit_air'] : 'Cukup',
                    'status_perbaikan' => $statusPerbaikan,
                    'aksi_dilakukan' => Security::sanitizeInput($_POST['aksi_dilakukan'] ?? ''),
                    'luas_layanan' => (float)$_POST['luas_layanan'],
                    'tanggal' => $_POST['tanggal'],
                    'latitude' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                    'longitude' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                    'catatan' => Security::sanitizeInput($_POST['catatan'] ?? ''),
                    'status' => $_POST['status'] ?? 'Submitted'
                ];
                
                // Handle new photo upload
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    require_once ROOT_PATH . '/app/helpers/ImageCompressor.php';
                    
                    $uploadDir = UPLOAD_PATH . 'irigasi/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $file = $_FILES['foto'];
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $fileName = hash('sha256', time() . $file['name'] . uniqid()) . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Delete old photo if exists
                        if (!empty($data['foto_url'])) {
                            $oldPath = ROOT_PATH . '/' . $data['foto_url'];
                            if (file_exists($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        $updateData['foto_url'] = 'public/uploads/irigasi/' . $fileName;
                    }
                }
                
                $this->model->update($id, $updateData);
                ErrorMessage::setSuccess('Data irigasi berhasil diperbarui');
                $this->redirect('irigasi/index');
                
            } catch (Exception $e) {
                error_log("Irigasi Edit Exception: " . $e->getMessage());
                ErrorMessage::set('Gagal mengupdate data: ' . $e->getMessage());
                $this->view('irigasi/edit', [
                    'title' => 'Edit Data Irigasi',
                    'data' => array_merge($data, $_POST),
                    'kabupaten' => $this->wilayahModel->getAllOrdered()
                ]);
                return;
            }
        }
        
        $this->view('irigasi/edit', [
            'title' => 'Edit Data Irigasi',
            'data' => $data,
            'kabupaten' => $this->wilayahModel->getAllOrdered()
        ]);
    }

    /**
     * DELETE: Hapus laporan irigasi (Admin only)
     */
    public function delete($id) {
        $this->checkAuth();
        $this->checkRole(['admin'], 'Hanya admin yang dapat menghapus data.');
        
        try {
            $data = $this->model->find($id);
            
            if (!$data) {
                ErrorMessage::set('Data irigasi tidak ditemukan');
                $this->redirect('irigasi/index');
                return;
            }
            
            // Delete photo if exists
            if (!empty($data['foto_url'])) {
                $photoPath = ROOT_PATH . '/' . $data['foto_url'];
                if (file_exists($photoPath)) {
                    @unlink($photoPath);
                }
            }
            
            $this->model->delete($id);
            ErrorMessage::setSuccess('Data irigasi berhasil dihapus');
            
        } catch (Exception $e) {
            error_log("Irigasi Delete Error: " . $e->getMessage());
            ErrorMessage::set('Gagal menghapus data: ' . $e->getMessage());
        }
        
        $this->redirect('irigasi/index');
    }
}

