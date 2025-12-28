<?php
/**
 * Admin Wilayah Controller
 * Handles CRUD operations for Kabupaten, Kecamatan, Desa
 * with soft delete, audit logging, and validation
 */

class AdminWilayahController extends Controller {
    private $kabModel;
    private $kabRawModel;
    private $kecModel;
    private $desaModel;
    private $auditModel;
    
    public function __construct() {
        $this->kabModel = $this->model('MasterKabupaten');
        $this->kabRawModel = $this->model('Kabupaten');
        $this->kecModel = $this->model('MasterKecamatan');
        $this->desaModel = $this->model('MasterDesa');
        $this->auditModel = $this->model('AuditLogWilayah');
    }
    
    // ==================== KABUPATEN CRUD ====================
    
    public function kabupaten() {
        $this->checkRole(['admin']);
        
        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $data = [
            'title' => 'Manajemen Kabupaten',
            'kabupaten' => $this->kabRawModel->getAllWithPagination($search, $limit, $offset),
            'total' => $this->kabRawModel->count($search),
            'page' => $page,
            'limit' => $limit,
            'search' => $search
        ];
        
        $this->view('admin/wilayah/kabupaten/index', $data);
    }

    public function kabupaten_api() {
        $this->checkRole(['admin']);
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $orderColumn = $_GET['order_column'] ?? 1;
        $orderDir = $_GET['order_dir'] ?? 'asc';
        
        if ($limit > 200) $limit = 200;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
        
        // Map DataTables order column to database column
        $orderColumnMap = [
            1 => 'id',
            2 => 'kode_kabupaten',
            3 => 'nama_kabupaten', 
            4 => 'provinsi',
            5 => 'master_id'
        ];
        $orderBy = $orderColumnMap[$orderColumn] ?? 'kode_kabupaten';
        
        try {
            $rows = $this->kabRawModel->getAllWithPagination($search, $limit, $offset, $orderBy, $orderDir);
            $total = $this->kabRawModel->count($search);
            echo json_encode([
                'success' => true,
                'data' => $rows,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
        }
        exit;
    }
    
    public function kabupaten_create() {
        $this->checkRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'nama_kabupaten' => trim($_POST['nama_kabupaten'] ?? ''),
                'kode_kabupaten' => trim($_POST['kode_kabupaten'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['nama_kabupaten'])) {
                $errors[] = 'Nama kabupaten wajib diisi';
            }
            if (empty($data['kode_kabupaten'])) {
                $errors[] = 'Kode wilayah wajib diisi';
            } elseif (!preg_match('/^35[0-9]{2}$/', $data['kode_kabupaten'])) {
                $errors[] = 'Format kode wilayah tidak valid. Gunakan format 35XX (contoh: 3501)';
            } elseif ($this->kabRawModel->checkKodeExists($data['kode_kabupaten'])) {
                $errors[] = 'Kode wilayah sudah digunakan';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kabupaten/create');
            }
            
            $id = $this->kabRawModel->create($data);
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'kabupaten',
                'record_id' => $id,
                'action' => 'CREATE',
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Kabupaten berhasil ditambahkan';
            $this->redirect('admin/wilayah/kabupaten');
        }
        
        $data = [
            'title' => 'Tambah Kabupaten',
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/kabupaten/create', $data);
    }
    
    public function kabupaten_edit($id) {
        $this->checkRole(['admin']);
        
        $kabupaten = $this->kabRawModel->findById($id);
        if (!$kabupaten) {
            $_SESSION['error'] = 'Kabupaten tidak ditemukan';
            $this->redirect('admin/wilayah/kabupaten');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'nama_kabupaten' => trim($_POST['nama_kabupaten'] ?? ''),
                'kode_kabupaten' => trim($_POST['kode_kabupaten'] ?? ''),
                'updated_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['nama_kabupaten'])) {
                $errors[] = 'Nama kabupaten wajib diisi';
            }
            if (empty($data['kode_kabupaten'])) {
                $errors[] = 'Kode wilayah wajib diisi';
            } elseif (!preg_match('/^35[0-9]{2}$/', $data['kode_kabupaten'])) {
                $errors[] = 'Format kode wilayah tidak valid. Gunakan format 35XX (contoh: 3501)';
            } elseif ($data['kode_kabupaten'] != $kabupaten['kode_kabupaten'] && 
                      $this->kabRawModel->checkKodeExists($data['kode_kabupaten'])) {
                $errors[] = 'Kode wilayah sudah digunakan';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kabupaten/edit/' . $id);
            }
            
            $this->kabRawModel->update($id, $data);
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'kabupaten',
                'record_id' => $id,
                'action' => 'UPDATE',
                'old_values' => json_encode($kabupaten),
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Kabupaten berhasil diperbarui';
            $this->redirect('admin/wilayah/kabupaten');
        }
        
        $data = [
            'title' => 'Edit Kabupaten',
            'kabupaten' => $kabupaten,
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/kabupaten/edit', $data);
    }
    
    public function kabupaten_delete($id) {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        $kabupaten = $this->kabRawModel->findById($id);
        if (!$kabupaten) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kabupaten tidak ditemukan']);
            exit;
        }
        
        // Check if has kecamatan
        $hasKecamatan = $this->kecModel->countByKabupaten($id) > 0;
        if ($hasKecamatan) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus kabupaten yang memiliki kecamatan']);
            exit;
        }
        
        // Soft delete
        $this->kabRawModel->softDelete($id, $_SESSION['user_id']);
        
        // Audit log
        $this->auditModel->log([
            'user_id' => $_SESSION['user_id'],
            'table_name' => 'kabupaten',
            'record_id' => $id,
            'action' => 'DELETE',
            'old_values' => json_encode($kabupaten)
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Kabupaten berhasil dihapus']);
        exit;
    }
    
    public function kabupaten_update($id) {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        $kabupaten = $this->kabRawModel->findById($id);
        if (!$kabupaten) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kabupaten tidak ditemukan']);
            exit;
        }
        
        // Get JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        $data = [
            'nama_kabupaten' => trim($input['nama_kabupaten'] ?? ''),
            'kode_kabupaten' => trim($input['kode_kabupaten'] ?? ''),
            'updated_by' => $_SESSION['user_id']
        ];
        
        // Validation
        $errors = [];
        if (empty($data['nama_kabupaten'])) {
            $errors[] = 'Nama kabupaten wajib diisi';
        }
        if (empty($data['kode_kabupaten'])) {
            $errors[] = 'Kode wilayah wajib diisi';
        } elseif (!preg_match('/^35[0-9]{2}$/', $data['kode_kabupaten'])) {
            $errors[] = 'Format kode wilayah tidak valid. Gunakan format 35XX (contoh: 3501)';
        } elseif ($data['kode_kabupaten'] != $kabupaten['kode_kabupaten'] && 
                  $this->kabRawModel->checkKodeExists($data['kode_kabupaten'])) {
            $errors[] = 'Kode wilayah sudah digunakan';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        // Update data
        $this->kabRawModel->update($id, $data);
        
        // Audit log
        $this->auditModel->log([
            'user_id' => $_SESSION['user_id'],
            'table_name' => 'kabupaten',
            'record_id' => $id,
            'action' => 'UPDATE',
            'old_values' => json_encode($kabupaten),
            'new_values' => json_encode($data)
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Kabupaten berhasil diperbarui']);
        exit;
    }
    
    // ==================== KECAMATAN CRUD ====================
    
    public function kecamatan($kabupatenId = null) {
        $this->checkRole(['admin']);

        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $data = [
            'title' => 'Manajemen Kecamatan',
            'kecamatan' => $this->kecModel->getAllWithKabupaten($kabupatenId, $search, $limit, $offset),
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'selected_kabupaten' => $kabupatenId,
            'total' => $this->kecModel->count($kabupatenId, $search),
            'page' => $page,
            'limit' => $limit,
            'search' => $search
        ];

        $this->view('admin/wilayah/kecamatan/index', $data);
    }

    public function kecamatan_api() {
        $this->checkRole(['admin']);
        $this->setSecurityHeaders();
        header('Content-Type: application/json');

        $search = $_GET['search'] ?? '';
        $kabupatenId = $_GET['kabupaten_id'] ?? '';
        if ($kabupatenId !== '' && !preg_match('/^\d+$/', (string)$kabupatenId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parameter kabupaten_id tidak valid']);
            exit;
        }
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        if ($limit > 200) $limit = 200;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Map DataTables order column to database column
        $orderColumnMap = [
            2 => 'kode_kecamatan',
            3 => 'nama_kecamatan',
            4 => 'kode_kabupaten'
        ];
        $orderColumn = $orderColumnMap[$_GET['order_column'] ?? 2] ?? 'kode_kecamatan';
        $orderDir = strtoupper($_GET['order_dir'] ?? 'asc') === 'DESC' ? 'desc' : 'asc';

        try {
            // Validate data consistency
            $this->validateKecamatanDataConsistency();

            $rows = $this->kecModel->getAllWithPaginationAndFilters($search, $limit, $offset, $orderColumn, $orderDir, $kabupatenId);
            $total = $this->kecModel->countWithFilters($search, $kabupatenId);
            $grandTotal = $this->kecModel->countWithFilters('', '', '');
            echo json_encode([
                'success' => true,
                'draw' => (int)($_GET['draw'] ?? 0),
                'recordsTotal' => (int)$grandTotal,
                'recordsFiltered' => (int)$total,
                'data' => $rows,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
        }
        exit;
    }

    private function validateKecamatanDataConsistency() {
        try {
            // Check for kecamatan with invalid kabupaten_id
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) as invalid_count
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.deleted_at IS NULL AND (kb.id IS NULL OR kb.deleted_at IS NOT NULL)
            ");
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result['invalid_count'] > 0) {
                error_log("Data consistency warning: Found {$result['invalid_count']} kecamatan with invalid kabupaten references");
                // You could send notification or auto-fix here
            }
        } catch (Exception $e) {
            error_log("Data consistency check failed: " . $e->getMessage());
        }
    }

    public function kecamatan_import() {
        $this->checkRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data = [
                'title' => 'Import Kecamatan Jawa Timur'
            ];
            $this->view('admin/wilayah/kecamatan/import', $data);
            return;
        }
        $this->validateCSRF();
        if (empty($_FILES['file']['name'])) {
            $_SESSION['error'] = 'File tidak dipilih';
            $this->redirect('admin/wilayah/kecamatan/import');
        }
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = ROOT_PATH . '/public/uploads/imports';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        $destPath = $uploadDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/','_', $file['name']);
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['error'] = 'Gagal mengunggah file';
            $this->redirect('admin/wilayah/kecamatan/import');
        }
        $rows = [];
        if ($ext === 'csv') {
            $rows = $this->parseCsv($destPath);
        } elseif ($ext === 'pdf') {
            $text = $this->tryExtractTextFromPdf($destPath);
            if (!$text) {
                $_SESSION['error'] = 'Parser PDF tidak tersedia. Unggah file CSV hasil ekstraksi.';
                $this->redirect('admin/wilayah/kecamatan/import');
            }
            $rows = $this->parseTextToRows($text);
        } else {
            $_SESSION['error'] = 'Format tidak didukung. Gunakan PDF atau CSV';
            $this->redirect('admin/wilayah/kecamatan/import');
        }
        $inserted = 0; $skipped = 0; $errors = 0; $details = [];
        foreach ($rows as $r) {
            $kabName = trim($r['kabupaten'] ?? '');
            $kecName = trim($r['kecamatan'] ?? '');
            $kode = trim($r['kode'] ?? '');
            if ($kabName === '' || $kecName === '') { $errors++; $details[] = "INVALID ROW"; continue; }
            $kab = $this->kabModel->findByName($kabName);
            if (!$kab) { $errors++; $details[] = "KAB NOT FOUND: $kabName"; continue; }
            $kabId = $kab['id'];
            if ($this->kecModel->checkNameExists($kabId, $kecName)) { $skipped++; $details[] = "DUP NAME: $kecName"; continue; }
            if (!empty($kode) && $this->kecModel->checkKodeExists($kode)) { $skipped++; $details[] = "DUP KODE: $kode"; continue; }
            $data = [
                'kabupaten_id' => $kabId,
                'nama_kecamatan' => $kecName,
                'kode_kecamatan' => $kode ?: null,
                'created_by' => $_SESSION['user_id']
            ];
            $id = $this->kecModel->create($data);
            // Clear cache for affected kabupaten
            $this->kecModel->clearCacheByKabupaten($data['kabupaten_id']);
            $inserted++;
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_kecamatan',
                'record_id' => $id,
                'action' => 'IMPORT',
                'new_values' => json_encode($data)
            ]);
        }
        $_SESSION['success'] = "Import selesai. Inserted: $inserted, Skipped: $skipped, Errors: $errors";
        $this->redirect('admin/wilayah/kecamatan');
    }

    private function parseCsv($path) {
        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            $headers = null;
            while (($data = fgetcsv($h, 0, ',')) !== false) {
                if (!$headers) { $headers = array_map('strtolower', $data); continue; }
                $row = array_combine($headers, $data);
                $rows[] = [
                    'kabupaten' => $row['kabupaten'] ?? $row['kabupaten_kota'] ?? null,
                    'kecamatan' => $row['kecamatan'] ?? $row['nama_kecamatan'] ?? null,
                    'kode' => $row['kode'] ?? $row['kode_kecamatan'] ?? null
                ];
            }
            fclose($h);
        }
        return $rows;
    }

    private function tryExtractTextFromPdf($path) {
        $out = null;
        $bin = 'pdftotext';
        try {
            $cmd = "$bin \"$path\" -"; // output to stdout
            $out = @shell_exec($cmd);
        } catch (Exception $e) { $out = null; }
        return $out ?: null;
    }

    private function parseTextToRows($text) {
        $rows = [];
        $lines = preg_split('/\r?\n/', $text);
        $currentKab = null;
        foreach ($lines as $ln) {
            $s = trim($ln);
            if ($s === '') continue;
            if (preg_match('/^(KABUPATEN|KOTA)\s+(.+)/i', $s, $m)) { $currentKab = trim($m[2]); continue; }
            if (!$currentKab) continue;
            if (preg_match('/^(\d{6,})\s+(.+)$/', $s, $m)) { $rows[] = ['kabupaten' => $currentKab, 'kode' => $m[1], 'kecamatan' => trim($m[2])]; continue; }
            if (preg_match('/^(.+?)$/', $s)) { $rows[] = ['kabupaten' => $currentKab, 'kode' => null, 'kecamatan' => $s]; }
        }
        return $rows;
    }
    
    public function kecamatan_create() {
        $this->checkRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'kabupaten_id' => $_POST['kabupaten_id'] ?? '',
                'nama_kecamatan' => trim($_POST['nama_kecamatan'] ?? ''),
                'kode_kecamatan' => trim($_POST['kode_kecamatan'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['kabupaten_id'])) {
                $errors[] = 'Kabupaten wajib dipilih';
            } elseif (!$this->kabModel->findById($data['kabupaten_id'])) {
                $errors[] = 'Kabupaten tidak valid';
            }
            if (empty($data['nama_kecamatan'])) {
                $errors[] = 'Nama kecamatan wajib diisi';
            }
            if (empty($data['kode_kecamatan'])) {
                $errors[] = 'Kode wilayah wajib diisi';
            } elseif (!preg_match('/^[0-9]{6,7}$/', $data['kode_kecamatan'])) {
                $errors[] = 'Format kode kecamatan harus 6–7 digit angka';
            } elseif ($this->kecModel->checkKodeExists($data['kode_kecamatan'])) {
                $errors[] = 'Kode wilayah sudah digunakan';
            }
            
            if (!empty($errors)) {
                error_log('Create kecamatan validation error: ' . implode('; ', $errors));
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kecamatan/create');
            }
            
            try {
                $id = $this->kecModel->create($data);
            } catch (Exception $e) {
                error_log('Create kecamatan failed: ' . $e->getMessage());
                $_SESSION['error'] = 'Gagal membuat kecamatan: ' . $e->getMessage();
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kecamatan/create');
            }
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_kecamatan',
                'record_id' => $id,
                'action' => 'CREATE',
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Kecamatan berhasil ditambahkan';
            $this->redirect('admin/wilayah/kecamatan');
        }
        
        $data = [
            'title' => 'Tambah Kecamatan',
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/kecamatan/create', $data);
    }
    
    public function kecamatan_edit($id) {
        $this->checkRole(['admin']);
        
        $kecamatan = $this->kecModel->findByIdWithKabupaten($id);
        if (!$kecamatan) {
            $_SESSION['error'] = 'Kecamatan tidak ditemukan';
            $this->redirect('admin/wilayah/kecamatan');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'kabupaten_id' => $_POST['kabupaten_id'] ?? '',
                'nama_kecamatan' => trim($_POST['nama_kecamatan'] ?? ''),
                'kode_kecamatan' => trim($_POST['kode_kecamatan'] ?? ''),
                'updated_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['kabupaten_id'])) {
                $errors[] = 'Kabupaten wajib dipilih';
            } elseif (!$this->kabModel->findById($data['kabupaten_id'])) {
                $errors[] = 'Kabupaten tidak valid';
            }
            if (empty($data['nama_kecamatan'])) {
                $errors[] = 'Nama kecamatan wajib diisi';
            }
            if (empty($data['kode_kecamatan'])) {
                $errors[] = 'Kode wilayah wajib diisi';
            } elseif (!preg_match('/^[0-9]{6,7}$/', $data['kode_kecamatan'])) {
                $errors[] = 'Format kode kecamatan harus 6–7 digit angka';
            } elseif ($data['kode_kecamatan'] != $kecamatan['kode_kecamatan'] && 
                      $this->kecModel->checkKodeExists($data['kode_kecamatan'])) {
                $errors[] = 'Kode wilayah sudah digunakan';
            }
            
            if (!empty($errors)) {
                error_log('Edit kecamatan validation error: ' . implode('; ', $errors));
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kecamatan/edit/' . $id);
            }
            
            try {
                $this->kecModel->update($id, $data);
            } catch (Exception $e) {
                error_log('Edit kecamatan update failed: ' . $e->getMessage());
                $_SESSION['error'] = 'Gagal memperbarui kecamatan: ' . $e->getMessage();
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/kecamatan/edit/' . $id);
            }
            // Clear caches for both old and new kabupaten
            $this->kecModel->clearCacheByKabupaten($kecamatan['kabupaten_id']);
            $this->kecModel->clearCacheByKabupaten($data['kabupaten_id']);
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_kecamatan',
                'record_id' => $id,
                'action' => 'UPDATE',
                'old_values' => json_encode($kecamatan),
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Kecamatan berhasil diperbarui';
            $this->redirect('admin/wilayah/kecamatan');
        }
        
        $data = [
            'title' => 'Edit Kecamatan',
            'kecamatan' => $kecamatan,
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/kecamatan/edit', $data);
    }
    
    public function kecamatan_delete($id) {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        
        $kecamatan = $this->kecModel->findById($id);
        if (!$kecamatan) {
            $this->json(['success' => false, 'message' => 'Kecamatan tidak ditemukan'], 404);
        }
        
        // Check if has desa
        $hasDesa = $this->desaModel->countByKecamatan($id) > 0;
        if ($hasDesa) {
            $this->json(['success' => false, 'message' => 'Tidak dapat menghapus kecamatan yang memiliki desa'], 400);
        }
        
        // Soft delete
        $this->kecModel->softDelete($id, $_SESSION['user_id']);
        // Clear cache for affected kabupaten
        $this->kecModel->clearCacheByKabupaten($kecamatan['kabupaten_id']);
        
        // Audit log
        $this->auditModel->log([
            'user_id' => $_SESSION['user_id'],
            'table_name' => 'master_kecamatan',
            'record_id' => $id,
            'action' => 'DELETE',
            'old_values' => json_encode($kecamatan)
        ]);
        
        $this->json(['success' => true, 'message' => 'Kecamatan berhasil dihapus']);
    }

    public function kecamatan_bulk_delete() {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $ids = $input['ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            $this->json(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        // Sanitize IDs
        $ids = array_values(array_unique(array_filter(array_map(function($v) {
            return is_numeric($v) ? (int)$v : null;
        }, $ids), function($v) { return $v !== null; })));

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'ID tidak valid'], 400);
        }

        $deleted = 0;
        $skipped = [];
        $clearedKabupaten = [];

        foreach ($ids as $id) {
            $kecamatan = $this->kecModel->findById($id);
            if (!$kecamatan) {
                $skipped[] = "ID {$id} tidak ditemukan";
                continue;
            }

            if ($this->desaModel->countByKecamatan($id) > 0) {
                $skipped[] = "{$kecamatan['nama_kecamatan']} dilewati karena memiliki desa terkait";
                continue;
            }

            $this->kecModel->softDelete($id, $_SESSION['user_id']);
            $this->kecModel->clearCacheByKabupaten($kecamatan['kabupaten_id']);
            $clearedKabupaten[] = $kecamatan['kabupaten_id'];
            $deleted++;

            // Audit log per record
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_kecamatan',
                'record_id' => $id,
                'action' => 'DELETE',
                'old_values' => json_encode($kecamatan)
            ]);
        }

        $message = "Berhasil menghapus {$deleted} kecamatan.";
        if (!empty($skipped)) {
            $message .= ' Beberapa data dilewati karena tidak valid atau memiliki desa terkait.';
        }

        $this->json([
            'success' => true,
            'deleted' => $deleted,
            'skipped' => $skipped,
            'message' => $message
        ]);
    }
    
    public function kecamatan_update($id) {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        $kecamatan = $this->kecModel->findById($id);
        if (!$kecamatan) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kecamatan tidak ditemukan']);
            exit;
        }
        
        // Get JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        $data = [
            'nama_kecamatan' => trim($input['nama_kecamatan'] ?? ''),
            'kode_kecamatan' => $kecamatan['kode_kecamatan'],
            'kabupaten_id' => (int)$kecamatan['kabupaten_id'],
            'updated_by' => $_SESSION['user_id']
        ];
        
        // Validation
        $errors = [];
        if (empty($data['nama_kecamatan'])) {
            $errors[] = 'Nama kecamatan wajib diisi';
        }

        // Prevent changing kode_kecamatan or kabupaten_id via edit (must stay as existing)
        if (isset($input['kode_kecamatan']) && trim($input['kode_kecamatan']) !== $kecamatan['kode_kecamatan']) {
            $errors[] = 'Kode wilayah (BPS) tidak boleh diubah melalui edit ini';
        }
        if (isset($input['kabupaten_id']) && (string)$input['kabupaten_id'] !== (string)$kecamatan['kabupaten_id']) {
            $errors[] = 'Kabupaten tidak boleh diubah melalui edit ini';
        }

        // Enforce uniqueness of name within kabupaten
        if ($this->kecModel->checkNameExists($kecamatan['kabupaten_id'], $data['nama_kecamatan'], $id)) {
            $errors[] = 'Nama kecamatan sudah digunakan dalam kabupaten ini';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        // Update only name
        $this->kecModel->updateNameOnly($id, $data['nama_kecamatan'], $_SESSION['user_id']);
        
        // Audit log
        $this->auditModel->log([
            'user_id' => $_SESSION['user_id'],
            'table_name' => 'master_kecamatan',
            'record_id' => $id,
            'action' => 'UPDATE',
            'old_values' => json_encode($kecamatan),
            'new_values' => json_encode($data)
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Kecamatan berhasil diperbarui']);
        exit;
    }
    
    // ==================== DESA CRUD ====================
    
    public function desa() {
        $this->checkRole(['admin']);
        
        $search = $_GET['search'] ?? '';
        $rawKabupatenId = isset($_GET['kabupaten_id']) && $_GET['kabupaten_id'] !== '' ? $_GET['kabupaten_id'] : null;
        $kecamatanId = isset($_GET['kecamatan_id']) && $_GET['kecamatan_id'] !== '' ? (int)$_GET['kecamatan_id'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Sort parameters with defaults (kode_kecamatan ASC, then kode_desa ASC)
        $sortBy = $_GET['sort_by'] ?? 'kode_kecamatan';
        $sortDir = $_GET['sort_dir'] ?? 'asc';
        
        // Validate sort parameters
        $allowedSortBy = ['kode_kecamatan', 'kode_desa', 'nama_desa', 'nama_kecamatan'];
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'kode_kecamatan';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        
        // Resolve kabupaten_id using flexible lookup (supports ID, BPS kode, or short kode)
        $kabupatenId = null;
        $resolvedKabupaten = null;
        if ($rawKabupatenId !== null) {
            $resolvedKabupaten = $this->kabModel->findByIdOrKode($rawKabupatenId);
            if ($resolvedKabupaten) {
                // Keep as string to match database ID format (e.g., '09' not 9)
                $kabupatenId = $resolvedKabupaten['id'];
            }
        }
        
        // Validate kecamatan belongs to kabupaten if both are set
        if ($kabupatenId && $kecamatanId) {
            if (!$this->desaModel->validateKecamatanInKabupaten($kecamatanId, $kabupatenId)) {
                $kecamatanId = null; // Reset invalid kecamatan
            }
        }
        
        $data = [
            'title' => 'Manajemen Desa',
            'desa' => $this->desaModel->getAllWithHierarchyAndKabupaten($kabupatenId, $kecamatanId, $search, $limit, $offset, $sortBy, $sortDir),
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'kecamatan_list' => $kabupatenId ? $this->kecModel->getByKabupatenForDropdown($kabupatenId) : [],
            'selected_kabupaten' => $kabupatenId,
            'selected_kecamatan' => $kecamatanId,
            'total' => $this->desaModel->countWithKabupaten($kabupatenId, $kecamatanId, $search),
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'resolved_kabupaten' => $resolvedKabupaten // For debugging
        ];
        
        $this->view('admin/wilayah/desa/index', $data);
    }
    
    /**
     * AJAX API endpoint for desa data with filtering
     * Used by AJAX DataTable for dynamic filtering
     */
    public function desa_api() {
        $this->checkRole(['admin']);
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        try {
            $rawKabupatenId = isset($_GET['kabupaten_id']) && $_GET['kabupaten_id'] !== '' ? $_GET['kabupaten_id'] : null;
            $kecamatanId = isset($_GET['kecamatan_id']) && $_GET['kecamatan_id'] !== '' ? (int)$_GET['kecamatan_id'] : null;
            $search = trim($_GET['search'] ?? '');
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $requestId = $_GET['request_id'] ?? null;
            
            // Sort parameters with defaults
            $sortBy = $_GET['sort_by'] ?? 'kode_kecamatan';
            $sortDir = $_GET['sort_dir'] ?? 'asc';
            
            // Validate sort parameters
            $allowedSortBy = ['kode_kecamatan', 'kode_desa', 'nama_desa', 'nama_kecamatan'];
            if (!in_array($sortBy, $allowedSortBy)) {
                $sortBy = 'kode_kecamatan';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
            
            // Resolve kabupaten_id using flexible lookup (supports ID, BPS kode, or short kode)
            $kabupatenId = null;
            if ($rawKabupatenId !== null) {
                $resolvedKabupaten = $this->kabModel->findByIdOrKode($rawKabupatenId);
                if ($resolvedKabupaten) {
                    // Keep as string to match database ID format (e.g., '09' not 9)
                    $kabupatenId = $resolvedKabupaten['id'];
                }
            }
            
            // Validate kecamatan belongs to kabupaten
            if ($kabupatenId && $kecamatanId) {
                if (!$this->desaModel->validateKecamatanInKabupaten($kecamatanId, $kabupatenId)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Kecamatan tidak termasuk dalam kabupaten yang dipilih',
                        'request_id' => $requestId
                    ]);
                    exit;
                }
            }
            
            $desa = $this->desaModel->getAllWithHierarchyAndKabupaten($kabupatenId, $kecamatanId, $search, $limit, $offset, $sortBy, $sortDir);
            $total = $this->desaModel->countWithKabupaten($kabupatenId, $kecamatanId, $search);
            
            // Add search highlight if search term provided
            if ($search) {
                foreach ($desa as &$row) {
                    $row['nama_desa_highlighted'] = preg_replace(
                        '/(' . preg_quote($search, '/') . ')/i',
                        '<mark class="search-highlight">$1</mark>',
                        htmlspecialchars($row['nama_desa'] ?? '')
                    );
                    $row['kode_desa_highlighted'] = preg_replace(
                        '/(' . preg_quote($search, '/') . ')/i',
                        '<mark class="search-highlight">$1</mark>',
                        htmlspecialchars($row['kode_desa'] ?? '')
                    );
                }
                unset($row);
            }
            
            $totalPages = ceil($total / $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $desa,
                'pagination' => [
                    'total' => (int)$total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ],
                'filters' => [
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan_id' => $kecamatanId,
                    'search' => $search
                ],
                'request_id' => $requestId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
        exit;
    }
    
    /**
     * AJAX API endpoint for desa autocomplete search
     */
    public function desa_autocomplete() {
        $this->checkRole(['admin']);
        $this->setSecurityHeaders();
        header('Content-Type: application/json');
        
        try {
            $kabupatenId = isset($_GET['kabupaten_id']) && $_GET['kabupaten_id'] !== '' ? (int)$_GET['kabupaten_id'] : null;
            $kecamatanId = isset($_GET['kecamatan_id']) && $_GET['kecamatan_id'] !== '' ? (int)$_GET['kecamatan_id'] : null;
            $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
            $limit = min(15, max(1, (int)($_GET['limit'] ?? 10)));
            
            if (strlen($search) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
            
            $results = $this->desaModel->searchForAutocomplete($kabupatenId, $kecamatanId, $search, $limit);
            
            $suggestions = [];
            foreach ($results as $row) {
                $suggestions[] = [
                    'id' => $row['id'],
                    'value' => $row['nama_desa'],
                    'label' => $row['nama_desa'] . ' - ' . ($row['nama_kecamatan'] ?? '') . ', ' . ($row['nama_kabupaten'] ?? ''),
                    'kode_desa' => $row['kode_desa'],
                    'nama_kecamatan' => $row['nama_kecamatan'],
                    'nama_kabupaten' => $row['nama_kabupaten']
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $suggestions]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan']);
        }
        exit;
    }
    
    public function desa_create() {
        $this->checkRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'kecamatan_id' => $_POST['kecamatan_id'] ?? '',
                'nama_desa' => trim($_POST['nama_desa'] ?? ''),
                'kode_desa' => trim($_POST['kode_desa'] ?? ''),
                'kode_pos' => trim($_POST['kode_pos'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['kecamatan_id'])) {
                $errors[] = 'Kecamatan wajib dipilih';
            } elseif (!$this->kecModel->findById($data['kecamatan_id'])) {
                $errors[] = 'Kecamatan tidak valid';
            }
            if (empty($data['nama_desa'])) {
                $errors[] = 'Nama desa wajib diisi';
            }
            if (empty($data['kode_desa'])) {
                $errors[] = 'Kode desa wajib diisi';
            } elseif ($this->desaModel->checkKodeExists($data['kode_desa'])) {
                $errors[] = 'Kode desa sudah digunakan';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/desa/create');
            }
            
            $id = $this->desaModel->create($data);
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_desa',
                'record_id' => $id,
                'action' => 'CREATE',
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Desa berhasil ditambahkan';
            $this->redirect('admin/wilayah/desa');
        }
        
        $data = [
            'title' => 'Tambah Desa',
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/desa/create', $data);
    }
    
    public function desa_edit($id) {
        $this->checkRole(['admin']);
        
        $desa = $this->desaModel->findByIdWithHierarchy($id);
        if (!$desa) {
            $_SESSION['error'] = 'Desa tidak ditemukan';
            $this->redirect('admin/wilayah/desa');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $data = [
                'kecamatan_id' => $_POST['kecamatan_id'] ?? '',
                'nama_desa' => trim($_POST['nama_desa'] ?? ''),
                'kode_desa' => trim($_POST['kode_desa'] ?? ''),
                'kode_pos' => trim($_POST['kode_pos'] ?? ''),
                'updated_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            if (empty($data['kecamatan_id'])) {
                $errors[] = 'Kecamatan wajib dipilih';
            } elseif (!$this->kecModel->findById($data['kecamatan_id'])) {
                $errors[] = 'Kecamatan tidak valid';
            }
            if (empty($data['nama_desa'])) {
                $errors[] = 'Nama desa wajib diisi';
            }
            if (empty($data['kode_desa'])) {
                $errors[] = 'Kode desa wajib diisi';
            } elseif ($data['kode_desa'] != $desa['kode_desa'] && 
                      $this->desaModel->checkKodeExists($data['kode_desa'])) {
                $errors[] = 'Kode desa sudah digunakan';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $_SESSION['old'] = $data;
                $this->redirect('admin/wilayah/desa/edit/' . $id);
            }
            
            $this->desaModel->update($id, $data);
            
            // Audit log
            $this->auditModel->log([
                'user_id' => $_SESSION['user_id'],
                'table_name' => 'master_desa',
                'record_id' => $id,
                'action' => 'UPDATE',
                'old_values' => json_encode($desa),
                'new_values' => json_encode($data)
            ]);
            
            $_SESSION['success'] = 'Desa berhasil diperbarui';
            $this->redirect('admin/wilayah/desa');
        }
        
        $data = [
            'title' => 'Edit Desa',
            'desa' => $desa,
            'kabupaten_list' => $this->kabModel->getAllForDropdown(),
            'kecamatan_list' => $this->kecModel->getByKabupatenForDropdown($desa['kabupaten_id']),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);
        
        $this->view('admin/wilayah/desa/edit', $data);
    }
    
    public function desa_delete($id) {
        $this->checkRole(['admin']);
        $this->validateCSRF();
        $this->setSecurityHeaders();
        
        $desa = $this->desaModel->findById($id);
        if (!$desa) {
            $this->json(['success' => false, 'message' => 'Desa tidak ditemukan'], 404);
        }
        
        // Soft delete
        $this->desaModel->softDelete($id, $_SESSION['user_id']);
        
        // Audit log
        $this->auditModel->log([
            'user_id' => $_SESSION['user_id'],
            'table_name' => 'master_desa',
            'record_id' => $id,
            'action' => 'DELETE',
            'old_values' => json_encode($desa)
        ]);
        
        $this->json(['success' => true, 'message' => 'Desa berhasil dihapus']);
    }
    
    // ==================== AJAX HELPERS ====================
    
    public function get_kecamatan_by_kabupaten($kabupatenId) {
        $this->checkRole(['admin']);
        $kecamatan = $this->kecModel->getByKabupatenForDropdown($kabupatenId);
        $this->json(['success' => true, 'data' => $kecamatan]);
    }
    
    // ==================== HELPERS ====================
    
    private function validateCSRF() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('dashboard');
        }
    }

    private function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Content-Security-Policy: frame-ancestors 'self'");
    }
    private function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
