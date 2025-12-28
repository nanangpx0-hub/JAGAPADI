<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/MasterOpt.php';

class OptController extends BaseApiController {
    
    private $optModel;
    
    public function __construct() {
        $this->optModel = new MasterOpt();
    }
    
    /**
     * Get all OPT with pagination and filters
     * GET /api/opt
     */
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            
            // Get filters
            $filters = [
                'jenis' => $_GET['jenis'] ?? null,
                'kategori' => $_GET['kategori'] ?? null,
                'aktif' => $_GET['aktif'] ?? null,
                'search' => $_GET['search'] ?? null
            ];
            
            // Remove null filters
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Get data
            $opt = $this->optModel->getAllWithFilters($filters, $pagination['limit'], $pagination['offset']);
            $total = $this->optModel->getCountWithFilters($filters);
            
            // Format response
            $response = $this->formatPaginatedResponse($opt, $total, $pagination['page'], $pagination['limit']);
            
            $this->sendResponse($response, 'OPT data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve OPT data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get specific OPT by ID
     * GET /api/opt/{id}
     */
    public function show($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid OPT ID', 400);
            }
            
            $opt = $this->optModel->getById($id);
            
            if (!$opt) {
                $this->sendError('OPT not found', 404);
            }
            
            $this->sendResponse($opt, 'OPT data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve OPT data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Create new OPT
     * POST /api/opt
     */
    public function store() {
        try {
            // Only admin can create OPT
            $this->checkPermission('admin');
            
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate required fields
            $requiredFields = ['nama_opt', 'nama_latin', 'jenis', 'kategori'];
            $errors = $this->validateRequired($data, $requiredFields);
            
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Validate jenis
            $validJenis = ['hama', 'penyakit', 'gulma'];
            if (!in_array($data['jenis'], $validJenis)) {
                $this->sendError('Invalid jenis. Must be one of: ' . implode(', ', $validJenis), 400);
            }
            
            // Validate kategori
            $validKategori = ['utama', 'sekunder', 'minor'];
            if (!in_array($data['kategori'], $validKategori)) {
                $this->sendError('Invalid kategori. Must be one of: ' . implode(', ', $validKategori), 400);
            }
            
            // Check if OPT name already exists
            if ($this->optModel->getByName($data['nama_opt'])) {
                $this->sendError('OPT name already exists', 409);
            }
            
            // Set default values
            $data['aktif'] = $data['aktif'] ?? 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $_SESSION['user_id'];
            
            // Handle image upload if present
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $data['gambar'] = $this->handleImageUpload($_FILES['gambar']);
            }
            
            $optId = $this->optModel->create($data);
            
            if ($optId) {
                $opt = $this->optModel->getById($optId);
                $this->sendResponse($opt, 'OPT created successfully', 201);
            } else {
                $this->sendError('Failed to create OPT', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to create OPT: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update OPT
     * PUT /api/opt/{id}
     */
    public function update($id) {
        try {
            // Only admin can update OPT
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid OPT ID', 400);
            }
            
            $existingOpt = $this->optModel->getById($id);
            if (!$existingOpt) {
                $this->sendError('OPT not found', 404);
            }
            
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate jenis if provided
            if (isset($data['jenis'])) {
                $validJenis = ['hama', 'penyakit', 'gulma'];
                if (!in_array($data['jenis'], $validJenis)) {
                    $this->sendError('Invalid jenis. Must be one of: ' . implode(', ', $validJenis), 400);
                }
            }
            
            // Validate kategori if provided
            if (isset($data['kategori'])) {
                $validKategori = ['utama', 'sekunder', 'minor'];
                if (!in_array($data['kategori'], $validKategori)) {
                    $this->sendError('Invalid kategori. Must be one of: ' . implode(', ', $validKategori), 400);
                }
            }
            
            // Check if OPT name already exists (excluding current OPT)
            if (isset($data['nama_opt'])) {
                $existingName = $this->optModel->getByName($data['nama_opt']);
                if ($existingName && $existingName['id'] != $id) {
                    $this->sendError('OPT name already exists', 409);
                }
            }
            
            // Set updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $_SESSION['user_id'];
            
            // Handle image upload if present
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $data['gambar'] = $this->handleImageUpload($_FILES['gambar']);
            }
            
            $success = $this->optModel->update($id, $data);
            
            if ($success) {
                $opt = $this->optModel->getById($id);
                $this->sendResponse($opt, 'OPT updated successfully');
            } else {
                $this->sendError('Failed to update OPT', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to update OPT: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete OPT
     * DELETE /api/opt/{id}
     */
    public function destroy($id) {
        try {
            // Only admin can delete OPT
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid OPT ID', 400);
            }
            
            $existingOpt = $this->optModel->getById($id);
            if (!$existingOpt) {
                $this->sendError('OPT not found', 404);
            }
            
            // Check if OPT is being used in reports
            if ($this->optModel->isUsedInReports($id)) {
                $this->sendError('Cannot delete OPT that is being used in reports', 400);
            }
            
            $success = $this->optModel->delete($id);
            
            if ($success) {
                $this->sendResponse(null, 'OPT deleted successfully');
            } else {
                $this->sendError('Failed to delete OPT', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete OPT: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Toggle OPT status
     * POST /api/opt/{id}/toggle-status
     */
    public function toggleStatus($id) {
        try {
            // Only admin can toggle OPT status
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid OPT ID', 400);
            }
            
            $existingOpt = $this->optModel->getById($id);
            if (!$existingOpt) {
                $this->sendError('OPT not found', 404);
            }
            
            $success = $this->optModel->toggleStatus($id);
            
            if ($success) {
                $opt = $this->optModel->getById($id);
                $statusText = $opt['aktif'] ? 'activated' : 'deactivated';
                $this->sendResponse($opt, "OPT {$statusText} successfully");
            } else {
                $this->sendError('Failed to toggle OPT status', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to toggle OPT status: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get OPT statistics
     * GET /api/opt/stats
     */
    public function getStats() {
        try {
            $stats = $this->optModel->getStatistics();
            $this->sendResponse($stats, 'OPT statistics retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve OPT statistics: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Search OPT
     * GET /api/opt/search?q={query}
     */
    public function search() {
        try {
            $query = $_GET['q'] ?? '';
            
            if (empty($query)) {
                $this->sendError('Search query is required', 400);
            }
            
            if (strlen($query) < 2) {
                $this->sendError('Search query must be at least 2 characters', 400);
            }
            
            $results = $this->optModel->search($query);
            $this->sendResponse($results, 'Search results retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to search OPT: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get OPT by category
     * GET /api/opt/by-category/{category}
     */
    public function getByCategory($category) {
        try {
            $validCategories = ['utama', 'sekunder', 'minor'];
            if (!in_array($category, $validCategories)) {
                $this->sendError('Invalid category. Must be one of: ' . implode(', ', $validCategories), 400);
            }
            
            $opt = $this->optModel->getByCategory($category);
            $this->sendResponse($opt, "OPT in category '{$category}' retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve OPT by category: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get OPT by type
     * GET /api/opt/by-type/{type}
     */
    public function getByType($type) {
        try {
            $validTypes = ['hama', 'penyakit', 'gulma'];
            if (!in_array($type, $validTypes)) {
                $this->sendError('Invalid type. Must be one of: ' . implode(', ', $validTypes), 400);
            }
            
            $opt = $this->optModel->getByType($type);
            $this->sendResponse($opt, "OPT of type '{$type}' retrieved successfully");
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve OPT by type: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file) {
        $uploadDir = ROOT_PATH . '/public/uploads/opt/';
        
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
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum 5MB allowed.');
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/opt/' . $filename;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }
}