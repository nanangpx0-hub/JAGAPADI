<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/Irigasi.php';

class IrigasiController extends BaseApiController {
    
    private $irigasiModel;
    
    public function __construct() {
        $this->irigasiModel = new Irigasi();
    }
    
    /**
     * Get all irigasi data with pagination and filters
     * GET /api/irigasi
     */
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            
            // Get filters
            $filters = [
                'status_kondisi' => $_GET['status_kondisi'] ?? null,
                'kabupaten_id' => $_GET['kabupaten_id'] ?? null,
                'kecamatan_id' => $_GET['kecamatan_id'] ?? null,
                'desa_id' => $_GET['desa_id'] ?? null,
                'jenis_irigasi' => $_GET['jenis_irigasi'] ?? null,
                'user_id' => $_GET['user_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null
            ];
            
            // Remove null filters
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Apply user-based filtering for petugas
            if ($_SESSION['role'] === 'petugas') {
                $filters['user_id'] = $_SESSION['user_id'];
            }
            
            // Get data
            $irigasi = $this->irigasiModel->getAllWithFilters($filters, $pagination['limit'], $pagination['offset']);
            $total = $this->irigasiModel->getCountWithFilters($filters);
            
            // Format response
            $response = $this->formatPaginatedResponse($irigasi, $total, $pagination['page'], $pagination['limit']);
            
            $this->sendResponse($response, 'Irigasi data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve irigasi data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get specific irigasi by ID
     * GET /api/irigasi/{id}
     */
    public function show($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid irigasi ID', 400);
            }
            
            $irigasi = $this->irigasiModel->getById($id);
            
            if (!$irigasi) {
                $this->sendError('Irigasi not found', 404);
            }
            
            // Check permission for petugas
            if ($_SESSION['role'] === 'petugas' && $irigasi['user_id'] != $_SESSION['user_id']) {
                $this->sendError('Forbidden', 403);
            }
            
            $this->sendResponse($irigasi, 'Irigasi data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve irigasi data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Create new irigasi data
     * POST /api/irigasi
     */
    public function store() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate required fields
            $requiredFields = [
                'nama_irigasi', 'jenis_irigasi', 'kabupaten_id', 'kecamatan_id', 
                'desa_id', 'alamat_lengkap', 'status_kondisi'
            ];
            
            $errors = $this->validateRequired($data, $requiredFields);
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Set user_id from session
            $data['user_id'] = $_SESSION['user_id'];
            
            // Set default values
            $data['luas_layanan'] = $data['luas_layanan'] ?? 0;
            $data['debit_air'] = $data['debit_air'] ?? 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // Handle file upload if present
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = $this->handleFileUpload($_FILES['foto']);
            }
            
            $irigasiId = $this->irigasiModel->create($data);
            
            if ($irigasiId) {
                $irigasi = $this->irigasiModel->getById($irigasiId);
                $this->sendResponse($irigasi, 'Irigasi data created successfully', 201);
            } else {
                $this->sendError('Failed to create irigasi data', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to create irigasi data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update irigasi data
     * PUT /api/irigasi/{id}
     */
    public function update($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid irigasi ID', 400);
            }
            
            $existingIrigasi = $this->irigasiModel->getById($id);
            if (!$existingIrigasi) {
                $this->sendError('Irigasi not found', 404);
            }
            
            // Check permission
            if ($_SESSION['role'] === 'petugas' && $existingIrigasi['user_id'] != $_SESSION['user_id']) {
                $this->sendError('Forbidden', 403);
            }
            
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Set updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Handle file upload if present
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = $this->handleFileUpload($_FILES['foto']);
            }
            
            $success = $this->irigasiModel->update($id, $data);
            
            if ($success) {
                $irigasi = $this->irigasiModel->getById($id);
                $this->sendResponse($irigasi, 'Irigasi data updated successfully');
            } else {
                $this->sendError('Failed to update irigasi data', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to update irigasi data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete irigasi data
     * DELETE /api/irigasi/{id}
     */
    public function destroy($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid irigasi ID', 400);
            }
            
            $existingIrigasi = $this->irigasiModel->getById($id);
            if (!$existingIrigasi) {
                $this->sendError('Irigasi not found', 404);
            }
            
            // Check permission
            if ($_SESSION['role'] !== 'admin' && 
                ($_SESSION['role'] === 'petugas' && $existingIrigasi['user_id'] != $_SESSION['user_id'])) {
                $this->sendError('Forbidden', 403);
            }
            
            $success = $this->irigasiModel->delete($id);
            
            if ($success) {
                $this->sendResponse(null, 'Irigasi data deleted successfully');
            } else {
                $this->sendError('Failed to delete irigasi data', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete irigasi data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get irigasi statistics
     * GET /api/irigasi/stats
     */
    public function getStats() {
        try {
            $stats = $this->irigasiModel->getStatistics();
            $this->sendResponse($stats, 'Irigasi statistics retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve irigasi statistics: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file) {
        $uploadDir = ROOT_PATH . '/public/uploads/irigasi/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($extension), $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
        }
        
        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum 10MB allowed.');
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/irigasi/' . $filename;
        } else {
            throw new Exception('Failed to upload file.');
        }
    }
}