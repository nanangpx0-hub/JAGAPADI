<?php
class DashboardController extends Controller {
    private $laporanModel;
    private $optModel;
    
    public function __construct() {
        $this->laporanModel = $this->model('LaporanHama');
        $this->optModel = $this->model('MasterOpt');
    }
    
    /**
     * Get user ID for filtering (null for admin/operator/viewer, user_id for petugas)
     * @return int|null
     */
    private function getFilterUserId(): ?int {
        $role = $_SESSION['role'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;
        
        // Only filter by user_id for petugas role
        if ($role === 'petugas' && $userId !== null) {
            return (int)$userId;
        }
        
        // Admin, operator, viewer see all data
        return null;
    }
    
    /**
     * Log data access for monitoring
     */
    private function logDataAccess(string $page, ?int $userId = null): void {
        $role = $_SESSION['role'] ?? 'unknown';
        $username = $_SESSION['username'] ?? 'unknown';
        $filter = $userId !== null ? "filtered by user_id={$userId}" : "all data";
        
        error_log(sprintf(
            "Dashboard Access: page=%s, user=%s (role=%s, user_id=%s), %s",
            $page,
            $username,
            $role,
            $userId ?? 'null',
            $filter
        ));
    }
    
    public function index() {
        $this->checkAuth();
        
        $filterUserId = $this->getFilterUserId();
        $this->logDataAccess('dashboard/index', $filterUserId);
        
        $stats = $this->laporanModel->getDashboardStats($filterUserId);
        $topPests = $this->laporanModel->getTopPests(5, $filterUserId);
        $monthlyStats = $this->laporanModel->getMonthlyStats(date('Y'), $filterUserId);
        $recentReports = array_slice($this->laporanModel->getAllWithDetails($filterUserId), 0, 5);
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'topPests' => $topPests,
            'monthlyStats' => $monthlyStats,
            'recentReports' => $recentReports
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    public function map() {
        $this->checkAuth();
        
        $filterUserId = $this->getFilterUserId();
        $this->logDataAccess('dashboard/map', $filterUserId);
        
        $mapData = $this->laporanModel->getMapData($filterUserId);
        
        $data = [
            'title' => 'Peta Sebaran Hama',
            'mapData' => $mapData
        ];
        
        $this->view('dashboard/map', $data);
    }
    
    public function charts() {
        $this->checkAuth();
        
        try {
            $filterUserId = $this->getFilterUserId();
            $this->logDataAccess('dashboard/charts', $filterUserId);
            
            $year = date('Y');
            
            // Get comprehensive statistics with user filter
            $monthlyStats = $this->laporanModel->getMonthlyStats($year, $filterUserId);
            $topPests = $this->laporanModel->getTopPests(10, $filterUserId);
            $severityStats = $this->laporanModel->getSeverityDistribution($filterUserId);
            $areaStats = $this->laporanModel->getAreaStatsByMonth($year, $filterUserId);
            $topKecamatan = $this->laporanModel->getTopKecamatan(5, $filterUserId);
            
            // Data integrity check
            $this->validateChartData($monthlyStats, $topPests, $severityStats, $areaStats);
            
            $data = [
                'title' => 'Grafik & Statistik',
                'monthlyStats' => $monthlyStats,
                'topPests' => $topPests,
                'severityStats' => $severityStats,
                'areaStats' => $areaStats,
                'topKecamatan' => $topKecamatan,
                'year' => $year
            ];
            
            $this->view('dashboard/charts', $data);
            
        } catch (Exception $e) {
            error_log("Dashboard Charts Error: " . $e->getMessage());
            
            // Fallback data
            $data = [
                'title' => 'Grafik & Statistik',
                'monthlyStats' => [],
                'topPests' => [],
                'severityStats' => [],
                'areaStats' => [],
                'topKecamatan' => [],
                'year' => date('Y'),
                'error' => 'Terjadi kesalahan saat memuat data grafik'
            ];
            
            $this->view('dashboard/charts', $data);
        }
    }
    
    /**
     * AJAX endpoint for chart data
     */
    public function getChartData() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        try {
            $filterUserId = $this->getFilterUserId();
            $type = $_GET['type'] ?? 'monthly';
            $year = $_GET['year'] ?? date('Y');
            
            $response = [
                'success' => true,
                'data' => [],
                'timestamp' => time()
            ];
            
            switch ($type) {
                case 'stats':
                    $response['data'] = $this->laporanModel->getDashboardStats($filterUserId);
                    break;

                case 'monthly':
                    $response['data'] = $this->laporanModel->getMonthlyStats($year, $filterUserId);
                    break;
                    
                case 'topPests':
                    $limit = $_GET['limit'] ?? 10;
                    $response['data'] = $this->laporanModel->getTopPests($limit, $filterUserId);
                    break;
                    
                case 'severity':
                    $response['data'] = $this->laporanModel->getSeverityDistribution($filterUserId);
                    break;
                    
                case 'area':
                    $response['data'] = $this->laporanModel->getAreaStatsByMonth($year, $filterUserId);
                    break;

                case 'kecamatan':
                    $response['data'] = $this->laporanModel->getTopKecamatan(5, $filterUserId);
                    break;
                    
                default:
                    throw new Exception('Invalid chart type');
            }
            
            // Validate data integrity
            if (empty($response['data']) && $type !== 'monthly') {
                $response['warning'] = 'Tidak ada data tersedia';
            }
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log("Chart Data Error: " . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Gagal memuat data grafik',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Validate chart data integrity
     */
    private function validateChartData($monthlyStats, $topPests, $severityStats, $areaStats) {
        $errors = [];
        
        // Check monthly stats structure
        if (!is_array($monthlyStats)) {
            $errors[] = 'Invalid monthly stats format';
        }
        
        // Check top pests data
        if (!is_array($topPests)) {
            $errors[] = 'Invalid top pests format';
        }
        
        // Check severity stats
        if (!is_array($severityStats)) {
            $errors[] = 'Invalid severity stats format';
        }
        
        // Check area stats
        if (!is_array($areaStats)) {
            $errors[] = 'Invalid area stats format';
        }
        
        if (!empty($errors)) {
            error_log("Chart Data Validation Errors: " . implode(', ', $errors));
            throw new Exception('Data validation failed');
        }
        
        return true;
    }
}
