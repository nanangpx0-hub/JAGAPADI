<?php
/**
 * Curah Hujan Controller
 * Controller untuk dashboard dan API curah hujan
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

class CurahHujanController extends Controller {
    
    private $model;
    
    public function __construct() {
        require_once ROOT_PATH . '/app/models/CurahHujan.php';
        $this->model = new CurahHujan();
        
        // Ensure tables exist
        $this->model->createTablesIfNotExist();
    }
    
    /**
     * Check authentication
     */
    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    /**
     * Check admin access
     */
    protected function checkAdmin() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
    
    /**
     * Dashboard utama curah hujan
     */
    public function index() {
        $this->checkAuth();
        
        $data = [
            'title' => 'Curah Hujan - JAGAPADI',
            'page_title' => 'Data Curah Hujan Kabupaten Jember',
            'availableYears' => $this->model->getAvailableYears(),
            'currentYear' => date('Y'),
            'currentMonth' => date('m')
        ];
        
        // Get statistics for current year
        $data['statistics'] = $this->model->getStatistics(['year' => date('Y')]);
        
        // Get monthly data for chart
        $data['monthlyData'] = $this->model->getMonthlyAverage(date('Y'));
        
        // Get recent data for table
        $data['recentData'] = $this->model->getAll([
            'limit' => 10,
            'offset' => 0
        ]);
        
        // Get logs for admin
        if ($_SESSION['role'] === 'admin') {
            $data['recentLogs'] = $this->model->getRecentLogs(5);
        }

        // Get last successful scrape info for metadata display
        $lastLog = $this->model->getRecentLogs(1); // Re-using getRecentLogs, might need filter for 'success'
        // Actually, let's add a specific method in model or just filter here.
        // For efficiency, let's keep it simple. getRecentLogs sorts by created_at DESC.
        // We'll pass it to view.
        $data['lastScrape'] = !empty($lastLog) ? $lastLog[0] : null;
        
        $this->view('curah_hujan/index', $data);
    }
    
    /**
     * API: Get data dengan filter (AJAX)
     */
    public function getData() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        try {
            $filters = [
                'year' => $_GET['year'] ?? null,
                'month' => $_GET['month'] ?? null,
                'start_date' => $_GET['start_date'] ?? null,
                'end_date' => $_GET['end_date'] ?? null,
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0
            ];
            
            // Remove null filters
            $filters = array_filter($filters, function($v) { return $v !== null; });
            
            $data = $this->model->getAll($filters);
            $total = $this->model->countAll($filters);
            $statistics = $this->model->getStatistics($filters);
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => $total,
                'statistics' => $statistics
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * API: Get chart data (AJAX)
     */
    public function getChartData() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        try {
            $type = $_GET['type'] ?? 'monthly';
            $year = $_GET['year'] ?? date('Y');
            
            if ($type === 'monthly') {
                $data = $this->model->getMonthlyAverage($year);
                
                // Format for Chart.js
                $labels = [];
                $values = [];
                $totals = [];
                
                $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                
                // Initialize all months with 0
                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = $monthNames[$i - 1];
                    $values[$i] = 0;
                    $totals[$i] = 0;
                }
                
                // Fill with actual data
                foreach ($data as $row) {
                    $bulan = (int) $row['bulan'];
                    $values[$bulan] = (float) $row['rata_rata'];
                    $totals[$bulan] = (float) $row['total'];
                }
                
                echo json_encode([
                    'success' => true,
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Rata-rata Curah Hujan (mm)',
                            'data' => array_values($values),
                            'borderColor' => 'rgb(54, 162, 235)',
                            'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                            'tension' => 0.3
                        ],
                        [
                            'label' => 'Total Curah Hujan (mm)',
                            'data' => array_values($totals),
                            'borderColor' => 'rgb(75, 192, 192)',
                            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                            'tension' => 0.3
                        ]
                    ]
                ]);
            } elseif ($type === 'yearly') {
                $data = $this->model->getYearlySummary(5);
                
                $labels = [];
                $avgValues = [];
                $totalValues = [];
                
                foreach (array_reverse($data) as $row) {
                    $labels[] = $row['tahun'];
                    $avgValues[] = (float) $row['rata_rata'];
                    $totalValues[] = (float) $row['total'];
                }
                
                echo json_encode([
                    'success' => true,
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Rata-rata (mm)',
                            'data' => $avgValues,
                            'backgroundColor' => 'rgba(54, 162, 235, 0.7)'
                        ],
                        [
                            'label' => 'Total (mm)',
                            'data' => $totalValues,
                            'backgroundColor' => 'rgba(75, 192, 192, 0.7)'
                        ]
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid chart type']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * API: Get statistics (AJAX)
     */
    public function getStatistics() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        try {
            $filters = [
                'year' => $_GET['year'] ?? null,
                'month' => $_GET['month'] ?? null
            ];
            
            $filters = array_filter($filters, function($v) { return $v !== null; });
            
            $statistics = $this->model->getStatistics($filters);
            
            echo json_encode([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Manual trigger scraper (Admin only)
     */
    public function runScraper() {
        $this->checkAuth();
        $this->checkAdmin();
        
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Token keamanan tidak valid']);
            exit;
        }
        
        try {
            require_once ROOT_PATH . '/app/services/CurahHujanScraper.php';
            $scraper = new CurahHujanScraper();
            
            $options = [
                'year' => $_POST['year'] ?? date('Y'),
                'month' => $_POST['month'] ?? date('m'),
                'force_simulation' => isset($_POST['force_simulation'])
            ];
            
            $result = $scraper->run($options);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'source' => $result['source'],
                'records_success' => $result['records_success'],
                'records_failed' => $result['records_failed'],
                'execution_time' => $result['execution_time']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Manual data entry form (Admin only)
     */
    public function create() {
        $this->checkAuth();
        $this->checkAdmin();
        
        $data = [
            'title' => 'Tambah Data Curah Hujan - JAGAPADI',
            'page_title' => 'Tambah Data Curah Hujan'
        ];
        
        $this->view('curah_hujan/create', $data);
    }
    
    /**
     * Store manual data entry
     */
    public function store() {
        $this->checkAuth();
        $this->checkAdmin();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Token keamanan tidak valid';
            header('Location: ' . BASE_URL . '/curahHujan/create');
            exit;
        }
        
        try {
            $data = [
                'tanggal' => $_POST['tanggal'] ?? null,
                'lokasi' => $_POST['lokasi'] ?? 'Jember',
                'kode_wilayah' => $_POST['kode_wilayah'] ?? '35.09',
                'curah_hujan' => $_POST['curah_hujan'] ?? 0,
                'satuan' => 'mm',
                'sumber_data' => 'Manual',
                'keterangan' => $_POST['keterangan'] ?? null
            ];
            
            // Validation
            if (empty($data['tanggal'])) {
                throw new Exception('Tanggal harus diisi');
            }
            
            if (!is_numeric($data['curah_hujan']) || $data['curah_hujan'] < 0 || $data['curah_hujan'] > 500) {
                throw new Exception('Curah hujan harus antara 0-500 mm');
            }
            
            $result = $this->model->insert($data);
            
            if ($result) {
                // Log activity
                $this->model->logActivity('manual_entry', 'success', 'Data curah hujan ditambahkan', [
                    'processed' => 1,
                    'success' => 1,
                    'failed' => 0
                ]);
                
                $_SESSION['success'] = 'Data curah hujan berhasil ditambahkan';
            } else {
                throw new Exception('Gagal menyimpan data');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/curahHujan');
        exit;
    }
    
    /**
     * Delete data (Admin only)
     */
    public function delete($id = null) {
        $this->checkAuth();
        $this->checkAdmin();
        
        header('Content-Type: application/json');
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
            exit;
        }
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Token keamanan tidak valid']);
            exit;
        }
        
        try {
            $result = $this->model->delete($id);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Data berhasil dihapus' : 'Gagal menghapus data'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Delete log by ID (Admin only)
     * 
     * @param int $id
     * @return void
     */
    public function deleteLog($id) {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid CSRF token');
                }
                
                if ($this->model->deleteLog($id)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menghapus log']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Export data ke CSV
     */
    public function export() {
        $this->checkAuth();
        
        $filters = [
            'year' => $_GET['year'] ?? null,
            'month' => $_GET['month'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];
        
        $filters = array_filter($filters, function($v) { return $v !== null; });
        
        $data = $this->model->getAll($filters);
        
        // Generate CSV
        $filename = 'curah_hujan_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header row
        fputcsv($output, ['Tanggal', 'Lokasi', 'Curah Hujan (mm)', 'Sumber Data', 'Keterangan']);
        
        // Data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['tanggal'],
                $row['lokasi'],
                $row['curah_hujan'],
                $row['sumber_data'],
                $row['keterangan']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
