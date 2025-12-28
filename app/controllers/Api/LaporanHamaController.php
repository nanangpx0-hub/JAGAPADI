<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/LaporanHama.php';

class LaporanHamaController extends BaseApiController {
    
    private $laporanModel;
    
    public function __construct() {
        $this->laporanModel = new LaporanHama();
    }
    
    /**
     * Get all laporan hama with pagination and filters
     * GET /api/laporan-hama
     */
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            
            // Get filters
            $filters = [
                'status' => $_GET['status'] ?? null,
                'kabupaten_id' => $_GET['kabupaten_id'] ?? null,
                'kecamatan_id' => $_GET['kecamatan_id'] ?? null,
                'master_opt_id' => $_GET['master_opt_id'] ?? null,
                'tingkat_keparahan' => $_GET['tingkat_keparahan'] ?? null,
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
            $laporan = $this->laporanModel->getAllWithFilters($filters, $pagination['limit'], $pagination['offset']);
            $total = $this->laporanModel->getCountWithFilters($filters);
            
            // Format response
            $response = $this->formatPaginatedResponse($laporan, $total, $pagination['page'], $pagination['limit']);
            
            $this->sendResponse($response, 'Laporan hama retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve laporan hama: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get specific laporan hama by ID
     * GET /api/laporan-hama/{id}
     */
    public function show($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid laporan ID', 400);
            }
            
            $laporan = $this->laporanModel->getById($id);
            
            if (!$laporan) {
                $this->sendError('Laporan not found', 404);
            }
            
            // Check permission for petugas
            if ($_SESSION['role'] === 'petugas' && $laporan['user_id'] != $_SESSION['user_id']) {
                $this->sendError('Forbidden', 403);
            }
            
            $this->sendResponse($laporan, 'Laporan hama retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve laporan hama: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Create new laporan hama
     * POST /api/laporan-hama
     */
    public function store() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate required fields
            $requiredFields = [
                'tanggal', 'master_opt_id', 'kabupaten_id', 'kecamatan_id', 
                'desa_id', 'alamat_lengkap', 'tingkat_keparahan'
            ];
            
            $errors = $this->validateRequired($data, $requiredFields);
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Set user_id from session
            $data['user_id'] = $_SESSION['user_id'];
            
            // Set default values
            $data['populasi'] = $data['populasi'] ?? 0;
            $data['luas_serangan'] = $data['luas_serangan'] ?? 0;
            $data['status'] = $data['status'] ?? 'Draf';
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // Handle file upload if present
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = $this->handleFileUpload($_FILES['foto']);
            }
            
            $laporanId = $this->laporanModel->create($data);
            
            if ($laporanId) {
                $laporan = $this->laporanModel->getById($laporanId);
                $this->sendResponse($laporan, 'Laporan hama created successfully', 201);
            } else {
                $this->sendError('Failed to create laporan hama', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to create laporan hama: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update laporan hama
     * PUT /api/laporan-hama/{id}
     */
    public function update($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid laporan ID', 400);
            }
            
            $existingLaporan = $this->laporanModel->getById($id);
            if (!$existingLaporan) {
                $this->sendError('Laporan not found', 404);
            }
            
            // Check permission
            if ($_SESSION['role'] === 'petugas' && $existingLaporan['user_id'] != $_SESSION['user_id']) {
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
            
            $success = $this->laporanModel->update($id, $data);
            
            if ($success) {
                $laporan = $this->laporanModel->getById($id);
                $this->sendResponse($laporan, 'Laporan hama updated successfully');
            } else {
                $this->sendError('Failed to update laporan hama', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to update laporan hama: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete laporan hama
     * DELETE /api/laporan-hama/{id}
     */
    public function destroy($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid laporan ID', 400);
            }
            
            $existingLaporan = $this->laporanModel->getById($id);
            if (!$existingLaporan) {
                $this->sendError('Laporan not found', 404);
            }
            
            // Check permission
            if ($_SESSION['role'] !== 'admin' && 
                ($_SESSION['role'] === 'petugas' && $existingLaporan['user_id'] != $_SESSION['user_id'])) {
                $this->sendError('Forbidden', 403);
            }
            
            $success = $this->laporanModel->delete($id);
            
            if ($success) {
                $this->sendResponse(null, 'Laporan hama deleted successfully');
            } else {
                $this->sendError('Failed to delete laporan hama', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete laporan hama: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file) {
        $uploadDir = ROOT_PATH . '/public/uploads/laporan/';
        
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
            return 'uploads/laporan/' . $filename;
        } else {
            throw new Exception('Failed to upload file.');
        }
    }
}