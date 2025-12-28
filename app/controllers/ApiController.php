<?php
class ApiController extends Controller {
    private $laporanModel;
    private $optModel;
    
    public function __construct() {
        $this->laporanModel = $this->model('LaporanHama');
        $this->optModel = $this->model('MasterOpt');
    }
    
    /**
     * Check API authentication
     */
    private function checkApiAuth() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (empty($apiKey)) {
            return false;
        }
        // TODO: Implement proper API key validation against database
        return true;
    }
    
    // Public endpoint for pest distribution data
    public function getPestDistribution() {
        $mapData = $this->laporanModel->getMapData();
        $this->json([
            'status' => 'success',
            'data' => $mapData
        ]);
    }
    
    // Get dashboard statistics
    public function getStats() {
        $stats = $this->laporanModel->getDashboardStats();
        $this->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
    
    // Get top pests
    public function getTopPests() {
        $limit = $_GET['limit'] ?? 10;
        $topPests = $this->laporanModel->getTopPests($limit);
        $this->json([
            'status' => 'success',
            'data' => $topPests
        ]);
    }
    
    // Submit report via API (for external integration)
    public function submitReport() {
        // Check rate limiting
        if (Security::checkRateLimit('api_submit_report', 100, 60)) {
            $this->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
        
        // Simple API key validation
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (empty($apiKey)) {
            $this->json(['status' => 'error', 'message' => 'API key required'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['status' => 'error', 'message' => 'Invalid JSON'], 400);
        }
        
        // Validate required fields
        $required = ['master_opt_id', 'tanggal', 'lokasi', 'tingkat_keparahan'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                $this->json(['status' => 'error', 'message' => "Field $field is required"], 400);
            }
        }
        
        // Validate data types and formats
        if (!is_numeric($input['master_opt_id']) || $input['master_opt_id'] <= 0) {
            $this->json(['status' => 'error', 'message' => 'master_opt_id must be a positive integer'], 400);
        }
        
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $input['tanggal']);
        if (!$date || $date->format('Y-m-d') !== $input['tanggal']) {
            $this->json(['status' => 'error', 'message' => 'tanggal must be in Y-m-d format'], 400);
        }
        
        // Validate tingkat_keparahan
        $allowedSeverity = ['Ringan', 'Sedang', 'Berat'];
        if (!in_array($input['tingkat_keparahan'], $allowedSeverity)) {
            $this->json(['status' => 'error', 'message' => 'tingkat_keparahan must be one of: ' . implode(', ', $allowedSeverity)], 400);
        }
        
        // Validate numeric fields
        $populasi = isset($input['populasi']) ? (int)$input['populasi'] : 0;
        $luas_serangan = isset($input['luas_serangan']) ? (float)$input['luas_serangan'] : 0;
        
        if ($populasi < 0) {
            $this->json(['status' => 'error', 'message' => 'populasi must be non-negative'], 400);
        }
        if ($luas_serangan < 0) {
            $this->json(['status' => 'error', 'message' => 'luas_serangan must be non-negative'], 400);
        }
        
        // Validate GPS coordinates if provided
        $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
        $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;
        
        if ($latitude !== null && $longitude !== null) {
            if ($latitude < JEMBER_LAT_MIN || $latitude > JEMBER_LAT_MAX || 
                $longitude < JEMBER_LON_MIN || $longitude > JEMBER_LON_MAX) {
                $this->json(['status' => 'error', 'message' => 'Koordinat GPS harus berada dalam wilayah Jember'], 400);
            }
        }
        
        // Sanitize string inputs
        $lokasi = Security::sanitizeInput($input['lokasi']);
        $catatan = isset($input['catatan']) ? Security::sanitizeInput($input['catatan']) : '';
        
        // Create report
        $reportData = [
            'user_id' => 1, // Default to admin for API submissions
            'master_opt_id' => (int)$input['master_opt_id'],
            'tanggal' => $input['tanggal'],
            'lokasi' => $lokasi,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'tingkat_keparahan' => $input['tingkat_keparahan'],
            'populasi' => $populasi,
            'luas_serangan' => $luas_serangan,
            'catatan' => $catatan,
            'status' => 'Submitted'
        ];
        
        try {
        $id = $this->laporanModel->create($reportData);
            
            // Log API activity
            Security::logSecurityEvent('API_REPORT_SUBMIT', "Report submitted via API: ID {$id}", null);
        
        $this->json([
            'status' => 'success',
            'message' => 'Report submitted successfully',
            'data' => ['id' => $id]
        ], 201);
        } catch (Exception $e) {
            error_log("API submitReport error: " . $e->getMessage());
            $this->json(['status' => 'error', 'message' => 'Failed to create report'], 500);
        }
    }
    
    // Simitra Integration Endpoints
    public function getMitra() {
        // This would typically call Simitra API
        // For now, return local data
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM mitra_simitra WHERE status = 'Aktif'");
        $mitra = $stmt->fetchAll();
        
        $this->json([
            'status' => 'success',
            'data' => $mitra
        ]);
    }
    
    public function getKegiatan() {
        // This would typically call Simitra API
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM kegiatan_simitra WHERE status = 'Aktif'");
        $kegiatan = $stmt->fetchAll();
        
        $this->json([
            'status' => 'success',
            'data' => $kegiatan
        ]);
    }
    
    public function addHonorPoptPelaporan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO honor_pelaporan (laporan_hama_id, mitra_id, kegiatan_id, jumlah_honor, catatan)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['laporan_hama_id'],
            $input['mitra_id'],
            $input['kegiatan_id'],
            $input['jumlah_honor'],
            $input['catatan'] ?? ''
        ]);
        
        $this->json([
            'status' => 'success',
            'message' => 'Honor added successfully',
            'data' => ['id' => $db->lastInsertId()]
        ], 201);
    }
    
    public function validateSBML() {
        $kegiatanId = $_GET['kegiatan_id'] ?? null;
        
        if (!$kegiatanId) {
            $this->json(['status' => 'error', 'message' => 'kegiatan_id required'], 400);
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Get kegiatan pagu
        $stmt = $db->prepare("SELECT pagu_honor FROM kegiatan_simitra WHERE id = ?");
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch();
        
        if (!$kegiatan) {
            $this->json(['status' => 'error', 'message' => 'Kegiatan not found'], 404);
        }
        
        // Get total honor used
        $stmt = $db->prepare("SELECT SUM(jumlah_honor) as total_used FROM honor_pelaporan WHERE kegiatan_id = ?");
        $stmt->execute([$kegiatanId]);
        $result = $stmt->fetch();
        
        $totalUsed = $result['total_used'] ?? 0;
        $remaining = $kegiatan['pagu_honor'] - $totalUsed;
        
        $this->json([
            'status' => 'success',
            'data' => [
                'pagu_honor' => $kegiatan['pagu_honor'],
                'total_used' => $totalUsed,
                'remaining' => $remaining,
                'is_valid' => $remaining >= 0
            ]
        ]);
    }
}
