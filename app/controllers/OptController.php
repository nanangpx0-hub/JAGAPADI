<?php
class OptController extends Controller {
    private $optModel;
    
    public function __construct() {
        $this->optModel = $this->model('MasterOpt');
    }
    
    /**
     * Index page with pagination and filters
     */
    public function index() {
        $this->checkAuth();
        
        // Get filter parameters
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = intval($_GET['per_page'] ?? 10);
        $search = trim($_GET['search'] ?? '');
        
        $filters = [];
        if (!empty($_GET['jenis'])) {
            $filters['jenis'] = $_GET['jenis'];
        }
        if (!empty($_GET['status_karantina'])) {
            $filters['status_karantina'] = $_GET['status_karantina'];
        }
        if (!empty($_GET['tingkat_bahaya'])) {
            $filters['tingkat_bahaya'] = $_GET['tingkat_bahaya'];
        }
        if (!empty($_GET['kingdom'])) {
            $filters['kingdom'] = $_GET['kingdom'];
        }
        
        // Get paginated data
        $result = $this->optModel->paginate($page, $perPage, $filters, $search ?: null);
        
        // Get filter options
        $filterOptions = $this->optModel->getFilterOptions();
        
        // Get statistics
        $stats = $this->optModel->getStats();
        
        $data = [
            'title' => 'Master Data OPT',
            'data_opt' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'from' => $result['from'],
                'to' => $result['to']
            ],
            'filters' => $filters,
            'search' => $search,
            'filter_options' => $filterOptions,
            'stats' => $stats
        ];
        
        $this->view('opt/index', $data);
    }
    
    /**
     * Create new OPT
     */
    public function create() {
        $this->checkRole(['admin', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                error_log('OPT Create - CSRF Error: ' . $e->getMessage());
                $_SESSION['error'] = 'Sesi Anda telah berakhir. Silakan coba lagi.';
                $_SESSION['form_data'] = $_POST;
                $this->redirect('opt/create');
                return;
            }
            
            // Collect form data with new fields
            $postData = [
                'kode_opt' => trim($_POST['kode_opt'] ?? ''),
                'nama_opt' => trim($_POST['nama_opt'] ?? ''),
                'nama_ilmiah' => trim($_POST['nama_ilmiah'] ?? ''),
                'nama_lokal' => trim($_POST['nama_lokal'] ?? ''),
                'jenis' => $_POST['jenis'] ?? '',
                'kingdom' => trim($_POST['kingdom'] ?? ''),
                'filum' => trim($_POST['filum'] ?? ''),
                'kelas' => trim($_POST['kelas'] ?? ''),
                'ordo' => trim($_POST['ordo'] ?? ''),
                'famili' => trim($_POST['famili'] ?? ''),
                'genus' => trim($_POST['genus'] ?? ''),
                'status_karantina' => $_POST['status_karantina'] ?? 'Tidak',
                'tingkat_bahaya' => $_POST['tingkat_bahaya'] ?? 'Sedang',
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'etl_acuan' => isset($_POST['etl_acuan']) ? (int)$_POST['etl_acuan'] : 0,
                'rekomendasi' => trim($_POST['rekomendasi'] ?? ''),
                'referensi' => trim($_POST['referensi'] ?? ''),
                'foto_url' => $_POST['foto_url'] ?? null
            ];
            
            // Remove empty values for optional fields
            foreach (['nama_ilmiah', 'nama_lokal', 'filum', 'kelas', 'ordo', 'famili', 'genus', 'referensi'] as $field) {
                if (empty($postData[$field])) {
                    $postData[$field] = null;
                }
            }
            
            // Validate required fields
            $errors = [];
            if (empty($postData['kode_opt'])) {
                $errors[] = 'Kode OPT wajib diisi';
            }
            if (empty($postData['nama_opt'])) {
                $errors[] = 'Nama OPT wajib diisi';
            }
            if (empty($postData['jenis'])) {
                $errors[] = 'Jenis OPT wajib dipilih';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                $_SESSION['form_data'] = $postData;
                error_log('OPT Create - Validation Error: ' . implode(', ', $errors));
                $this->redirect('opt/create');
                return;
            }
            
            // Handle file upload
            if (empty($postData['foto_url']) && isset($_FILES['gambar']) && $_FILES['gambar']['error'] != UPLOAD_ERR_NO_FILE) {
                try {
                    require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
                    $uploader = new OptPhotoUploader();
                    $result = $uploader->upload($_FILES['gambar']);
                    
                    if ($result['success']) {
                        $postData['foto_url'] = $result['path'];
                        error_log('OPT Create - Photo uploaded successfully: ' . $result['path']);
                    } else {
                        $_SESSION['error'] = 'Upload Foto Gagal: ' . $result['error'];
                        $_SESSION['form_data'] = $postData;
                        error_log('OPT Create - Upload Error: ' . $result['error']);
                        $this->redirect('opt/create');
                        return;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Upload Foto Gagal: ' . $e->getMessage();
                    $_SESSION['form_data'] = $postData;
                    error_log('OPT Create - Upload Exception: ' . $e->getMessage());
                    $this->redirect('opt/create');
                    return;
                }
            }
            
            // Save to database
            try {
                $id = $this->optModel->create($postData);
                
                if ($id) {
                    error_log('OPT Create - Success: ID ' . $id . ', Data: ' . json_encode($postData));
                    $_SESSION['success'] = 'Data OPT berhasil ditambahkan';
                    unset($_SESSION['form_data']);
                    $this->redirect('opt');
                } else {
                    throw new Exception('Gagal menyimpan data. ID tidak valid.');
                }
            } catch (PDOException $e) {
                error_log('OPT Create - Database Error: ' . $e->getMessage());
                $_SESSION['error'] = 'Gagal menyimpan data ke database: ' . $e->getMessage();
                $_SESSION['form_data'] = $postData;
                $this->redirect('opt/create');
            } catch (Exception $e) {
                error_log('OPT Create - Error: ' . $e->getMessage());
                $_SESSION['error'] = 'Gagal menyimpan data: ' . $e->getMessage();
                $_SESSION['form_data'] = $postData;
                $this->redirect('opt/create');
            }
        }
        
        // Get saved form data if exists
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        
        // Get filter options for dropdowns
        $filterOptions = $this->optModel->getFilterOptions();
        
        $data = [
            'title' => 'Tambah Data OPT',
            'form_data' => $formData,
            'filter_options' => $filterOptions
        ];
        $this->view('opt/create', $data);
    }
    
    /**
     * Edit OPT
     */
    public function edit($id) {
        $this->checkRole(['admin', 'operator']);
        
        $opt = $this->optModel->find($id);
        if (!$opt) {
            $_SESSION['error'] = 'Data tidak ditemukan';
            $this->redirect('opt');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            
            $postData = [
                'kode_opt' => trim($_POST['kode_opt'] ?? ''),
                'nama_opt' => trim($_POST['nama_opt'] ?? ''),
                'nama_ilmiah' => trim($_POST['nama_ilmiah'] ?? ''),
                'nama_lokal' => trim($_POST['nama_lokal'] ?? ''),
                'jenis' => $_POST['jenis'] ?? '',
                'kingdom' => trim($_POST['kingdom'] ?? ''),
                'filum' => trim($_POST['filum'] ?? ''),
                'kelas' => trim($_POST['kelas'] ?? ''),
                'ordo' => trim($_POST['ordo'] ?? ''),
                'famili' => trim($_POST['famili'] ?? ''),
                'genus' => trim($_POST['genus'] ?? ''),
                'status_karantina' => $_POST['status_karantina'] ?? 'Tidak',
                'tingkat_bahaya' => $_POST['tingkat_bahaya'] ?? 'Sedang',
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'etl_acuan' => isset($_POST['etl_acuan']) ? (int)$_POST['etl_acuan'] : 0,
                'rekomendasi' => trim($_POST['rekomendasi'] ?? ''),
                'referensi' => trim($_POST['referensi'] ?? '')
            ];
            
            // Remove empty values for optional fields
            foreach (['nama_ilmiah', 'nama_lokal', 'filum', 'kelas', 'ordo', 'famili', 'genus', 'referensi'] as $field) {
                if (empty($postData[$field])) {
                    $postData[$field] = null;
                }
            }
            
            // Get old photo path
            $oldPhotoPath = $opt['foto_url'] ?? $opt['gambar'] ?? null;
            
            // Handle file upload
            $newPhotoPath = $_POST['foto_url'] ?? null;
            
            if (!empty($newPhotoPath)) {
                $postData['foto_url'] = $newPhotoPath;
                if ($oldPhotoPath) {
                    require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
                    $uploader = new OptPhotoUploader();
                    $uploader->deletePhoto($oldPhotoPath);
                }
            } elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] != UPLOAD_ERR_NO_FILE) {
                try {
                    require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
                    $uploader = new OptPhotoUploader();
                    $result = $uploader->upload($_FILES['gambar'], $id, $oldPhotoPath);
                    
                    if ($result['success']) {
                        $postData['foto_url'] = $result['path'];
                    } else {
                        $_SESSION['error'] = 'Upload Gagal: ' . $result['error'];
                        $this->redirect('opt/edit/' . $id);
                        return;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Upload Gagal: ' . $e->getMessage();
                    $this->redirect('opt/edit/' . $id);
                    return;
                }
            }
            
            try {
                $this->optModel->update($id, $postData);
                $_SESSION['success'] = 'Data OPT berhasil diupdate';
                $this->redirect('opt');
            } catch (Exception $e) {
                $_SESSION['error'] = 'Gagal mengupdate data: ' . $e->getMessage();
                $this->redirect('opt/edit/' . $id);
            }
        }
        
        // Get filter options for dropdowns
        $filterOptions = $this->optModel->getFilterOptions();
        
        $data = [
            'title' => 'Edit Data OPT',
            'opt' => $opt,
            'filter_options' => $filterOptions
        ];
        $this->view('opt/edit', $data);
    }
    
    /**
     * Delete OPT
     */
    public function delete($id) {
        $this->checkRole(['admin']);
        
        $opt = $this->optModel->find($id);
        if ($opt) {
            // Delete image if exists
            $photoPath = $opt['foto_url'] ?? $opt['gambar'] ?? null;
            if (!empty($photoPath)) {
                require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
                $uploader = new OptPhotoUploader();
                $uploader->deletePhoto($photoPath);
            }
            
            $this->optModel->delete($id);
            $_SESSION['success'] = 'Data OPT berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Data tidak ditemukan';
        }
        
        $this->redirect('opt');
    }
    
    /**
     * View OPT detail
     */
    public function detail($id) {
        $this->checkAuth();
        
        $opt = $this->optModel->find($id);
        if (!$opt) {
            $_SESSION['error'] = 'Data tidak ditemukan';
            $this->redirect('opt');
        }
        
        $data = [
            'title' => 'Detail Data OPT',
            'opt' => $opt
        ];
        $this->view('opt/view', $data);
    }
    
    /**
     * Export to Excel
     */
    public function exportExcel() {
        $this->checkRole(['admin', 'operator']);
        
        // Get filter parameters
        $search = trim($_GET['search'] ?? '');
        $filters = [];
        if (!empty($_GET['jenis'])) $filters['jenis'] = $_GET['jenis'];
        if (!empty($_GET['status_karantina'])) $filters['status_karantina'] = $_GET['status_karantina'];
        if (!empty($_GET['tingkat_bahaya'])) $filters['tingkat_bahaya'] = $_GET['tingkat_bahaya'];
        
        $data = $this->optModel->getForExport($filters, $search ?: null);
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=master_opt_' . date('Y-m-d_His') . '.xls');
        header('Cache-Control: max-age=0');
        
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Master OPT</x:Name>
                            <x:WorksheetOptions><x:Print><x:ValidPrinterInfo/></x:Print></x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <style>
                th { background-color: #28a745; color: white; font-weight: bold; }
                td, th { border: 1px solid #ddd; padding: 8px; }
            </style>
        </head>
        <body>';
        
        echo '<table border="1">
            <tr>
                <th colspan="15" style="text-align:center; font-size: 16px;">
                    <h2>JAGAPADI - Master Data OPT (Organisme Pengganggu Tumbuhan)</h2>
                    <p>Tanggal Export: ' . date('d/m/Y H:i:s') . '</p>
                </th>
            </tr>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama OPT</th>
                <th>Nama Ilmiah</th>
                <th>Nama Lokal</th>
                <th>Jenis</th>
                <th>Kingdom</th>
                <th>Filum</th>
                <th>Kelas</th>
                <th>Ordo</th>
                <th>Famili</th>
                <th>Genus</th>
                <th>Status Karantina</th>
                <th>Tingkat Bahaya</th>
                <th>ETL Acuan</th>
            </tr>';
        
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['kode_opt'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['nama_opt'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['nama_ilmiah'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['nama_lokal'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['jenis'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['kingdom'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['filum'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['kelas'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['ordo'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['famili'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['genus'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['status_karantina'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['tingkat_bahaya'] ?? '') . '</td>
                <td>' . ($row['etl_acuan'] ?? 0) . '</td>
            </tr>';
        }
        
        echo '</table>
        <p><strong>Total Data:</strong> ' . count($data) . ' record</p>
        </body></html>';
        exit;
    }
    
    /**
     * Export to PDF (HTML for print)
     */
    public function exportPdf() {
        $this->checkRole(['admin', 'operator']);
        
        // Get filter parameters
        $search = trim($_GET['search'] ?? '');
        $filters = [];
        if (!empty($_GET['jenis'])) $filters['jenis'] = $_GET['jenis'];
        if (!empty($_GET['status_karantina'])) $filters['status_karantina'] = $_GET['status_karantina'];
        if (!empty($_GET['tingkat_bahaya'])) $filters['tingkat_bahaya'] = $_GET['tingkat_bahaya'];
        
        $data = $this->optModel->getForExport($filters, $search ?: null);
        
        // Get filter text for display
        $filterText = [];
        if (!empty($filters['jenis'])) $filterText[] = 'Jenis: ' . $filters['jenis'];
        if (!empty($filters['status_karantina'])) $filterText[] = 'Karantina: ' . $filters['status_karantina'];
        if (!empty($filters['tingkat_bahaya'])) $filterText[] = 'Bahaya: ' . $filters['tingkat_bahaya'];
        if (!empty($search)) $filterText[] = 'Keyword: ' . $search;
        
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Master Data OPT - JAGAPADI</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            margin: 15px;
            background: white;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px; 
            text-align: left; 
        }
        th { 
            background-color: #28a745; 
            color: white; 
            font-weight: bold;
            font-size: 10px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            padding: 15px;
            border-bottom: 3px solid #28a745;
        }
        .logo { 
            font-size: 24px; 
            font-weight: bold; 
            color: #28a745; 
        }
        .subtitle { color: #666; font-size: 12px; }
        .filter-info { 
            background: #f8f9fa; 
            padding: 10px; 
            margin-bottom: 10px; 
            border-radius: 4px;
        }
        .badge { 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 9px;
            color: white;
        }
        .badge-danger { background: #dc3545; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; }
        .badge-success { background: #28a745; }
        .badge-secondary { background: #6c757d; }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .footer { margin-top: 20px; font-size: 10px; color: #666; }
        @media print {
            .print-button { display: none; }
            body { margin: 0; }
            @page { margin: 1.5cm; size: landscape; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>
    
    <div class="header">
        <div class="logo">JAGAPADI</div>
        <h2>Master Data OPT (Organisme Pengganggu Tumbuhan)</h2>
        <p class="subtitle">Jember Agrikultur Gapai Prestasi Digital - BPS Kabupaten Jember</p>
        <p>Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p>
    </div>';
        
        if (!empty($filterText)) {
            echo '<div class="filter-info"><strong>Filter Aktif:</strong> ' . implode(' | ', $filterText) . '</div>';
        }
        
        echo '<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama OPT</th>
                <th>Nama Ilmiah</th>
                <th>Jenis</th>
                <th>Klasifikasi</th>
                <th>Karantina</th>
                <th>Bahaya</th>
                <th>ETL</th>
            </tr>
        </thead>
        <tbody>';
        
        $no = 1;
        foreach ($data as $row) {
            // Build classification string
            $klasifikasi = array_filter([
                $row['kingdom'] ?? '',
                $row['filum'] ?? '',
                $row['kelas'] ?? '',
                $row['ordo'] ?? '',
                $row['famili'] ?? ''
            ]);
            $klasifikasiStr = !empty($klasifikasi) ? implode(' › ', $klasifikasi) : '-';
            
            // Badge colors
            $jenisBadge = $row['jenis'] == 'Hama' ? 'danger' : ($row['jenis'] == 'Penyakit' ? 'warning' : 'info');
            $karantinaBadge = $row['status_karantina'] == 'Tidak' ? 'secondary' : 'danger';
            $bahayaBadge = [
                'Rendah' => 'success',
                'Sedang' => 'warning',
                'Tinggi' => 'danger',
                'Sangat Tinggi' => 'danger'
            ][$row['tingkat_bahaya'] ?? 'Sedang'] ?? 'secondary';
            
            echo '<tr>
                <td>' . $no++ . '</td>
                <td><strong>' . htmlspecialchars($row['kode_opt'] ?? '') . '</strong></td>
                <td>' . htmlspecialchars($row['nama_opt'] ?? '') . '</td>
                <td><em>' . htmlspecialchars($row['nama_ilmiah'] ?? '-') . '</em></td>
                <td><span class="badge badge-' . $jenisBadge . '">' . htmlspecialchars($row['jenis'] ?? '') . '</span></td>
                <td style="font-size: 9px;">' . htmlspecialchars($klasifikasiStr) . '</td>
                <td><span class="badge badge-' . $karantinaBadge . '">' . htmlspecialchars($row['status_karantina'] ?? 'Tidak') . '</span></td>
                <td><span class="badge badge-' . $bahayaBadge . '">' . htmlspecialchars($row['tingkat_bahaya'] ?? 'Sedang') . '</span></td>
                <td>' . ($row['etl_acuan'] ?? 0) . '</td>
            </tr>';
        }
        
        echo '</tbody></table>
    
    <div class="footer">
        <p><strong>Total Data:</strong> ' . count($data) . ' record</p>
        <p>Dokumen ini digenerate otomatis oleh sistem JAGAPADI</p>
        <p>Untuk menyimpan sebagai PDF: Klik tombol Print, lalu pilih "Save as PDF"</p>
    </div>
</body>
</html>';
        exit;
    }
    
    /**
     * AJAX endpoint for photo upload
     */
    public function uploadPhoto() {
        $this->checkRole(['admin', 'operator']);
        
        header('Content-Type: application/json');
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfTokenAjax($token)) {
            echo json_encode([
                'success' => false,
                'error' => 'Token CSRF tidak valid',
                'code' => 'CSRF_ERROR'
            ]);
            exit;
        }
        
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
            echo json_encode([
                'success' => false,
                'error' => 'Tidak ada file yang diupload',
                'code' => 'NO_FILE'
            ]);
            exit;
        }
        
        try {
            require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
            
            $optId = isset($_POST['opt_id']) && is_numeric($_POST['opt_id']) ? (int)$_POST['opt_id'] : null;
            $oldPhotoPath = !empty($_POST['old_photo']) ? $_POST['old_photo'] : null;
            
            $uploader = new OptPhotoUploader();
            $result = $uploader->upload($_FILES['photo'], $optId, $oldPhotoPath);
            
            if ($result['success']) {
                $pathForUrl = strpos($result['path'], 'public/') === 0 ? $result['path'] : 'public/' . $result['path'];
                $result['url'] = BASE_URL . $pathForUrl;
                echo json_encode($result);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $result['error'],
                    'code' => $result['code'] ?? 'UPLOAD_ERROR'
                ]);
            }
            
        } catch (Exception $e) {
            error_log('OPT Photo Upload Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengupload: ' . $e->getMessage(),
                'code' => 'EXCEPTION'
            ]);
        }
        
        exit;
    }
    
    /**
     * Validate CSRF token for AJAX requests
     */
    private function validateCsrfTokenAjax($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * List all photos from upload directory
     */
    public function photos() {
        $this->checkAuth();
        
        $uploadDir = ROOT_PATH . '/public/uploads/opt/';
        $photos = [];
        
        try {
            if (is_dir($uploadDir)) {
                $years = scandir($uploadDir);
                foreach ($years as $year) {
                    if ($year === '.' || $year === '..' || !is_numeric($year)) continue;
                    
                    $yearPath = $uploadDir . $year . '/';
                    if (!is_dir($yearPath)) continue;
                    
                    $months = scandir($yearPath);
                    foreach ($months as $month) {
                        if ($month === '.' || $month === '..' || !is_numeric($month)) continue;
                        
                        $monthPath = $yearPath . $month . '/';
                        if (!is_dir($monthPath)) continue;
                        
                        $files = scandir($monthPath);
                        foreach ($files as $file) {
                            if ($file === '.' || $file === '..' || $file === '.htaccess') continue;
                            
                            $filePath = $monthPath . $file;
                            if (!is_file($filePath)) continue;
                            
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) continue;
                            
                            $relativePath = 'uploads/opt/' . $year . '/' . $month . '/' . $file;
                            $fullPath = BASE_URL . 'public/' . $relativePath;
                            
                            $fileInfo = [
                                'filename' => $file,
                                'path' => $relativePath,
                                'full_path' => $fullPath,
                                'url' => BASE_URL . $fullPath,
                                'size' => filesize($filePath),
                                'size_formatted' => $this->formatBytes(filesize($filePath)),
                                'modified' => filemtime($filePath),
                                'modified_formatted' => date('d/m/Y H:i', filemtime($filePath)),
                                'year' => $year,
                                'month' => $month,
                                'extension' => $ext
                            ];
                            
                            $imageInfo = @getimagesize($filePath);
                            if ($imageInfo) {
                                $fileInfo['width'] = $imageInfo[0];
                                $fileInfo['height'] = $imageInfo[1];
                                $fileInfo['mime'] = $imageInfo['mime'];
                            }
                            
                            $photos[] = $fileInfo;
                        }
                    }
                }
            }
            
            usort($photos, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
        } catch (Exception $e) {
            error_log('OPT Photos - Error scanning directory: ' . $e->getMessage());
            $_SESSION['error'] = 'Gagal membaca daftar foto: ' . $e->getMessage();
        }
        
        $data = [
            'title' => 'Daftar Foto OPT',
            'photos' => $photos,
            'total' => count($photos)
        ];
        
        $this->view('opt/photos', $data);
    }
    
    /**
     * Delete photo file
     */
    public function deletePhoto() {
        $this->checkRole(['admin', 'operator']);
        
        header('Content-Type: application/json');
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfTokenAjax($token)) {
            echo json_encode([
                'success' => false,
                'error' => 'Token CSRF tidak valid',
                'code' => 'CSRF_ERROR'
            ]);
            exit;
        }
        
        $photoPath = $_POST['path'] ?? '';
        if (empty($photoPath)) {
            echo json_encode([
                'success' => false,
                'error' => 'Path foto tidak valid',
                'code' => 'INVALID_PATH'
            ]);
            exit;
        }
        
        try {
            require_once ROOT_PATH . '/app/helpers/OptPhotoUploader.php';
            $uploader = new OptPhotoUploader();
            
            $result = $uploader->deletePhoto($photoPath);
            
            if ($result) {
                error_log('OPT Photo Deleted: ' . $photoPath);
                echo json_encode([
                    'success' => true,
                    'message' => 'Foto berhasil dihapus'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Gagal menghapus foto',
                    'code' => 'DELETE_ERROR'
                ]);
            }
            
        } catch (Exception $e) {
            error_log('OPT Photo Delete Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'code' => 'EXCEPTION'
            ]);
        }
        
        exit;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
