<?php
/**
 * User Import Service
 * Handles Excel parsing, validation, and import of user data
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

require_once ROOT_PATH . '/app/helpers/SimpleXLSX.php';
require_once ROOT_PATH . '/app/helpers/ImportLogger.php';
require_once ROOT_PATH . '/app/models/User.php';

class UserImportService {
    
    private $userModel;
    private $logger;
    private $errors = [];
    private $validRows = [];
    private $invalidRows = [];
    
    // Validation constants
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'csv'];
    const REQUIRED_COLUMNS = ['Nama Lengkap', 'Username', 'Email', 'Role', 'Status'];
    const VALID_ROLES = ['admin', 'operator', 'viewer', 'petugas'];
    const VALID_STATUSES = ['Aktif', 'Nonaktif', 'active', 'inactive', '1', '0'];
    
    // Rate limiting
    const MAX_UPLOADS_PER_MINUTE = 3;
    
    public function __construct() {
        $this->userModel = new User();
        $this->logger = new ImportLogger();
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateFile($file) {
        // Check if file exists
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Tidak ada file yang diupload'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi limit PHP)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi limit form)',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
            ];
            return ['valid' => false, 'error' => $errorMessages[$file['error']] ?? 'Error upload tidak diketahui'];
        }
        
        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'Ukuran file melebihi 5MB'];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'error' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'];
        }
        
        // Validate file content (MIME type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/zip', // xlsx is actually a zip
            'text/csv',
            'text/plain',
            'application/octet-stream'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Konten file tidak valid'];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Check rate limiting for uploads
     * 
     * @param int $userId
     * @return bool
     */
    public function checkRateLimit($userId) {
        return $this->logger->checkRateLimit($userId, self::MAX_UPLOADS_PER_MINUTE);
    }
    
    /**
     * Parse Excel file and return data
     * 
     * @param string $filePath
     * @return array ['success' => bool, 'data' => array|null, 'error' => string|null, 'debug' => array]
     */
    public function parseExcel($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Check if file exists
        if (!file_exists($filePath)) {
            return ['success' => false, 'data' => null, 'error' => 'File tidak ditemukan: ' . basename($filePath)];
        }
        
        // Check file is readable
        if (!is_readable($filePath)) {
            return ['success' => false, 'data' => null, 'error' => 'File tidak dapat dibaca. Periksa permission.'];
        }
        
        if ($extension === 'csv') {
            return $this->parseCsv($filePath);
        }
        
        // Try to parse XLSX
        $xlsx = SimpleXLSX::parse($filePath);
        
        // Check for parser errors
        if ($xlsx && $xlsx->error()) {
            $error = $xlsx->error();
            $debug = method_exists($xlsx, 'getDebug') ? $xlsx->getDebug() : [];
            return [
                'success' => false, 
                'data' => null, 
                'error' => 'Gagal membaca file Excel: ' . $error,
                'debug' => $debug
            ];
        }
        
        // Check if parser returned valid object and has data
        if (!$xlsx || !$xlsx->hasData()) {
            // Try to get error message
            $errorMsg = ($xlsx && $xlsx->error()) ? $xlsx->error() : 'File tidak dapat diparsing';
            $debug = ($xlsx && method_exists($xlsx, 'getDebug')) ? $xlsx->getDebug() : [];
            
            // If XLSX parsing failed, try CSV
            if ($this->looksLikeCsv($filePath)) {
                return $this->parseCsv($filePath);
            }
            
            return [
                'success' => false, 
                'data' => null, 
                'error' => 'Gagal membaca file Excel: ' . $errorMsg,
                'debug' => $debug
            ];
        }
        
        $rows = $xlsx->rows(0);
        
        if (empty($rows)) {
            return ['success' => false, 'data' => null, 'error' => 'File Excel kosong atau tidak memiliki data pada sheet pertama'];
        }
        
        // Validate column structure
        $headerRow = $rows[0] ?? [];
        $columnValidation = $this->validateColumns($headerRow);
        
        if (!$columnValidation['valid']) {
            return ['success' => false, 'data' => null, 'error' => $columnValidation['error']];
        }
        
        return ['success' => true, 'data' => $rows, 'error' => null];
    }
    
    /**
     * Check if file might be CSV despite extension
     * 
     * @param string $filePath
     * @return bool
     */
    private function looksLikeCsv($filePath) {
        $content = file_get_contents($filePath, false, null, 0, 500);
        // Check if content has comma separated values and no binary data
        return (strpos($content, ',') !== false || strpos($content, ';') !== false) 
               && !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $content);
    }
    
    /**
     * Parse CSV file
     * 
     * @param string $filePath
     * @return array
     */
    private function parseCsv($filePath) {
        $rows = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        
        if (empty($rows)) {
            return ['success' => false, 'data' => null, 'error' => 'File CSV kosong'];
        }
        
        // Validate column structure
        $headerRow = $rows[0] ?? [];
        $columnValidation = $this->validateColumns($headerRow);
        
        if (!$columnValidation['valid']) {
            return ['success' => false, 'data' => null, 'error' => $columnValidation['error']];
        }
        
        return ['success' => true, 'data' => $rows, 'error' => null];
    }
    
    /**
     * Validate column headers
     * 
     * @param array $headers
     * @return array
     */
    private function validateColumns($headers) {
        $normalizedHeaders = array_map(function($h) {
            return trim(strtolower($h));
        }, $headers);
        
        $requiredNormalized = array_map('strtolower', self::REQUIRED_COLUMNS);
        
        // Check if at least the first 5 columns match
        $missing = [];
        foreach ($requiredNormalized as $index => $required) {
            if (!isset($normalizedHeaders[$index]) || 
                strpos($normalizedHeaders[$index], str_replace(' ', '', $required)) === false &&
                strpos(str_replace(' ', '', $normalizedHeaders[$index]), str_replace(' ', '', $required)) === false) {
                // More flexible matching
                $found = false;
                foreach ($normalizedHeaders as $header) {
                    if (strpos($header, explode(' ', $required)[0]) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $missing[] = self::REQUIRED_COLUMNS[$index];
                }
            }
        }
        
        if (!empty($missing)) {
            return [
                'valid' => false, 
                'error' => 'Kolom tidak sesuai template. Kolom yang dibutuhkan: ' . implode(', ', self::REQUIRED_COLUMNS)
            ];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Validate all data rows
     * 
     * @param array $rows
     * @return array ['valid' => array, 'invalid' => array, 'stats' => array]
     */
    public function validateData($rows) {
        $this->validRows = [];
        $this->invalidRows = [];
        $existingUsernames = [];
        $existingEmails = [];
        
        // Skip header row
        $dataRows = array_slice($rows, 1);
        
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2; // Excel row number (1-indexed + header)
            $rowData = [
                'row' => $rowNumber,
                'nama_lengkap' => trim($row[0] ?? ''),
                'username' => trim($row[1] ?? ''),
                'email' => trim($row[2] ?? ''),
                'role' => strtolower(trim($row[3] ?? '')),
                'status' => trim($row[4] ?? '')
            ];
            
            $errors = $this->validateRow($rowData, $existingUsernames, $existingEmails);
            
            if (empty($errors)) {
                // Normalize status
                $rowData['aktif'] = $this->normalizeStatus($rowData['status']);
                $this->validRows[] = $rowData;
                $existingUsernames[] = strtolower($rowData['username']);
                $existingEmails[] = strtolower($rowData['email']);
            } else {
                $rowData['errors'] = $errors;
                $this->invalidRows[] = $rowData;
            }
        }
        
        return [
            'valid' => $this->validRows,
            'invalid' => $this->invalidRows,
            'stats' => [
                'total' => count($dataRows),
                'valid_count' => count($this->validRows),
                'invalid_count' => count($this->invalidRows)
            ]
        ];
    }
    
    /**
     * Validate a single row
     * 
     * @param array $row
     * @param array $existingUsernames (from current batch)
     * @param array $existingEmails (from current batch)
     * @return array List of errors
     */
    private function validateRow($row, $existingUsernames, $existingEmails) {
        $errors = [];
        
        // Nama Lengkap validation
        if (empty($row['nama_lengkap'])) {
            $errors[] = 'Nama Lengkap wajib diisi';
        } elseif (strlen($row['nama_lengkap']) > 100) {
            $errors[] = 'Nama Lengkap maksimal 100 karakter';
        }
        
        // Username validation
        if (empty($row['username'])) {
            $errors[] = 'Username wajib diisi';
        } elseif (strlen($row['username']) > 50) {
            $errors[] = 'Username maksimal 50 karakter';
        } elseif (in_array(strtolower($row['username']), $existingUsernames)) {
            $errors[] = 'Username duplikat dalam file';
        } elseif ($this->userModel->getUserByUsername($row['username'])) {
            $errors[] = 'Username sudah terdaftar di sistem';
        }
        
        // Email validation
        if (empty($row['email'])) {
            $errors[] = 'Email wajib diisi';
        } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif (in_array(strtolower($row['email']), $existingEmails)) {
            $errors[] = 'Email duplikat dalam file';
        } elseif ($this->userModel->getUserByEmail($row['email'])) {
            $errors[] = 'Email sudah terdaftar di sistem';
        }
        
        // Role validation
        if (empty($row['role'])) {
            $errors[] = 'Role wajib diisi';
        } elseif (!in_array($row['role'], self::VALID_ROLES)) {
            $errors[] = 'Role tidak valid. Pilihan: ' . implode(', ', self::VALID_ROLES);
        }
        
        // Status validation (optional, default to Aktif)
        if (!empty($row['status']) && !in_array(strtolower($row['status']), array_map('strtolower', self::VALID_STATUSES))) {
            $errors[] = 'Status tidak valid. Pilihan: Aktif, Nonaktif';
        }
        
        return $errors;
    }
    
    /**
     * Normalize status value
     * 
     * @param string $status
     * @return int
     */
    private function normalizeStatus($status) {
        $activeValues = ['aktif', 'active', '1', 'yes', 'ya'];
        return in_array(strtolower($status), $activeValues) || empty($status) ? 1 : 0;
    }
    
    /**
     * Import valid users to database
     * 
     * @param int $importedBy User ID who is importing
     * @return array ['success' => bool, 'imported' => int, 'failed' => int, 'errors' => array]
     */
    public function importUsers($importedBy) {
        $imported = 0;
        $failed = 0;
        $errors = [];
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            foreach ($this->validRows as $row) {
                try {
                    // Generate default password based on role
                    $passwordMap = [
                        'petugas' => 'Petugas3509',
                        'operator' => 'Operator3509',
                        'viewer' => 'Viewer3509',
                        'admin' => 'Admin3509'
                    ];
                    $plainPassword = $passwordMap[$row['role']] ?? $row['username'];
                    $defaultPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
                    
                    $userData = [
                        'nama_lengkap' => htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'),
                        'username' => htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'),
                        'email' => filter_var($row['email'], FILTER_SANITIZE_EMAIL),
                        'password' => $defaultPassword,
                        'role' => $row['role'],
                        'aktif' => $row['aktif'],
                        'must_change_password' => 1,
                        'created_by' => $importedBy
                    ];
                    
                    $result = $this->userModel->createUser($userData);
                    
                    if ($result) {
                        $imported++;
                    } else {
                        $failed++;
                        $errors[] = [
                            'row' => $row['row'],
                            'username' => $row['username'],
                            'error' => 'Gagal menyimpan ke database'
                        ];
                    }
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = [
                        'row' => $row['row'],
                        'username' => $row['username'],
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $db->commit();
            
            // Log the import
            $this->logger->logImport($importedBy, [
                'total_rows' => count($this->validRows) + count($this->invalidRows),
                'imported' => $imported,
                'failed' => $failed + count($this->invalidRows),
                'status' => 'completed'
            ]);
            
            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            
            $this->logger->logImport($importedBy, [
                'total_rows' => count($this->validRows),
                'imported' => 0,
                'failed' => count($this->validRows),
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'imported' => 0,
                'failed' => count($this->validRows),
                'errors' => [['row' => 0, 'username' => '', 'error' => $e->getMessage()]]
            ];
        }
    }
    
    /**
     * Get valid rows for preview
     * 
     * @param int $limit
     * @return array
     */
    public function getPreviewData($limit = 10) {
        return [
            'valid' => array_slice($this->validRows, 0, $limit),
            'invalid' => array_slice($this->invalidRows, 0, $limit),
            'stats' => [
                'total' => count($this->validRows) + count($this->invalidRows),
                'valid_count' => count($this->validRows),
                'invalid_count' => count($this->invalidRows)
            ]
        ];
    }
    
    /**
     * Get all validation results
     * 
     * @return array
     */
    public function getValidationResults() {
        return [
            'valid' => $this->validRows,
            'invalid' => $this->invalidRows
        ];
    }
}
