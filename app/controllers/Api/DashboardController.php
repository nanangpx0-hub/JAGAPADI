<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/LaporanHama.php';
require_once ROOT_PATH . '/app/models/Irigasi.php';
require_once ROOT_PATH . '/app/models/User.php';

class DashboardController extends BaseApiController {
    
    private $laporanModel;
    private $irigasiModel;
    private $userModel;
    
    public function __construct() {
        $this->laporanModel = new LaporanHama();
        $this->irigasiModel = new Irigasi();
        $this->userModel = new User();
    }
    
    /**
     * Get dashboard statistics
     * GET /api/dashboard/stats
     */
    public function getStats() {
        try {
            // Apply user-based filtering for petugas
            $userFilter = null;
            if ($_SESSION['role'] === 'petugas') {
                $userFilter = $_SESSION['user_id'];
            }
            
            $stats = [
                'laporan_hama' => $this->laporanModel->getDashboardStats($userFilter),
                'irigasi' => $this->irigasiModel->getDashboardStats($userFilter),
                'users' => $this->userModel->getDashboardStats(),
                'summary' => $this->getSummaryStats($userFilter)
            ];
            
            $this->sendResponse($stats, 'Dashboard statistics retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve dashboard statistics: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get chart data for dashboard
     * GET /api/dashboard/charts
     */
    public function getChartData() {
        try {
            $chartType = $_GET['type'] ?? 'all';
            $period = $_GET['period'] ?? '30'; // days
            $userFilter = null;
            
            if ($_SESSION['role'] === 'petugas') {
                $userFilter = $_SESSION['user_id'];
            }
            
            $chartData = [];
            
            switch ($chartType) {
                case 'monthly_trends':
                    $chartData = $this->getMonthlyTrends($period, $userFilter);
                    break;
                    
                case 'top_opt':
                    $chartData = $this->getTopOPT($userFilter);
                    break;
                    
                case 'area_statistics':
                    $chartData = $this->getAreaStatistics($userFilter);
                    break;
                    
                case 'severity_distribution':
                    $chartData = $this->getSeverityDistribution($userFilter);
                    break;
                    
                case 'irrigation_status':
                    $chartData = $this->getIrrigationStatus($userFilter);
                    break;
                    
                case 'all':
                default:
                    $chartData = [
                        'monthly_trends' => $this->getMonthlyTrends($period, $userFilter),
                        'top_opt' => $this->getTopOPT($userFilter),
                        'area_statistics' => $this->getAreaStatistics($userFilter),
                        'severity_distribution' => $this->getSeverityDistribution($userFilter),
                        'irrigation_status' => $this->getIrrigationStatus($userFilter)
                    ];
                    break;
            }
            
            $this->sendResponse($chartData, 'Chart data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve chart data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get recent activities
     * GET /api/dashboard/activities
     */
    public function getActivities() {
        try {
            $limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
            $userFilter = null;
            
            if ($_SESSION['role'] === 'petugas') {
                $userFilter = $_SESSION['user_id'];
            }
            
            $activities = $this->getRecentActivities($limit, $userFilter);
            $this->sendResponse($activities, 'Recent activities retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve recent activities: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get alerts and notifications
     * GET /api/dashboard/alerts
     */
    public function getAlerts() {
        try {
            $userFilter = null;
            if ($_SESSION['role'] === 'petugas') {
                $userFilter = $_SESSION['user_id'];
            }
            
            $alerts = [
                'critical_reports' => $this->getCriticalReports($userFilter),
                'pending_verifications' => $this->getPendingVerifications(),
                'system_alerts' => $this->getSystemAlerts(),
                'irrigation_alerts' => $this->getIrrigationAlerts($userFilter)
            ];
            
            $this->sendResponse($alerts, 'Alerts retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve alerts: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get summary statistics
     */
    private function getSummaryStats($userFilter = null) {
        return [
            'total_reports' => $this->laporanModel->getTotalCount($userFilter),
            'pending_reports' => $this->laporanModel->getCountByStatus('Submitted', $userFilter),
            'verified_reports' => $this->laporanModel->getCountByStatus('Diverifikasi', $userFilter),
            'rejected_reports' => $this->laporanModel->getCountByStatus('Ditolak', $userFilter),
            'total_irrigation' => $this->irigasiModel->getTotalCount($userFilter),
            'active_irrigation' => $this->irigasiModel->getCountByStatus('Aktif', $userFilter)
        ];
    }
    
    /**
     * Get monthly trends data
     */
    private function getMonthlyTrends($period, $userFilter = null) {
        return $this->laporanModel->getMonthlyTrends($period, $userFilter);
    }
    
    /**
     * Get top OPT data
     */
    private function getTopOPT($userFilter = null) {
        return $this->laporanModel->getTopOPT(10, $userFilter);
    }
    
    /**
     * Get area statistics
     */
    private function getAreaStatistics($userFilter = null) {
        return $this->laporanModel->getAreaStatistics($userFilter);
    }
    
    /**
     * Get severity distribution
     */
    private function getSeverityDistribution($userFilter = null) {
        return $this->laporanModel->getSeverityDistribution($userFilter);
    }
    
    /**
     * Get irrigation status
     */
    private function getIrrigationStatus($userFilter = null) {
        return $this->irigasiModel->getStatusDistribution($userFilter);
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($limit, $userFilter = null) {
        $activities = [];
        
        // Get recent laporan hama activities
        $recentReports = $this->laporanModel->getRecentActivities($limit / 2, $userFilter);
        foreach ($recentReports as $report) {
            $activities[] = [
                'type' => 'laporan_hama',
                'action' => 'created',
                'title' => 'Laporan Hama Baru',
                'description' => $report['opt_name'] . ' di ' . $report['desa_name'],
                'user' => $report['user_name'],
                'timestamp' => $report['created_at'],
                'severity' => $report['tingkat_keparahan']
            ];
        }
        
        // Get recent irrigation activities
        $recentIrrigation = $this->irigasiModel->getRecentActivities($limit / 2, $userFilter);
        foreach ($recentIrrigation as $irrigation) {
            $activities[] = [
                'type' => 'irigasi',
                'action' => 'updated',
                'title' => 'Update Irigasi',
                'description' => $irrigation['nama_irigasi'] . ' - ' . $irrigation['status_kondisi'],
                'user' => $irrigation['user_name'],
                'timestamp' => $irrigation['updated_at'] ?? $irrigation['created_at'],
                'status' => $irrigation['status_kondisi']
            ];
        }
        
        // Sort by timestamp
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($activities, 0, $limit);
    }
    
    /**
     * Get critical reports
     */
    private function getCriticalReports($userFilter = null) {
        return $this->laporanModel->getCriticalReports($userFilter);
    }
    
    /**
     * Get pending verifications (admin/operator only)
     */
    private function getPendingVerifications() {
        if ($_SESSION['role'] === 'petugas') {
            return [];
        }
        
        return $this->laporanModel->getPendingVerifications();
    }
    
    /**
     * Get system alerts
     */
    private function getSystemAlerts() {
        $alerts = [];
        
        // Check for system issues
        if ($_SESSION['role'] !== 'petugas') {
            // Check for old pending reports
            $oldPending = $this->laporanModel->getOldPendingReports(7); // 7 days old
            if ($oldPending > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Laporan Tertunda',
                    'message' => "{$oldPending} laporan menunggu verifikasi lebih dari 7 hari",
                    'action' => 'review_pending'
                ];
            }
        }
        
        return $alerts;
    }
    
    /**
     * Get irrigation alerts
     */
    private function getIrrigationAlerts($userFilter = null) {
        return $this->irigasiModel->getAlerts($userFilter);
    }
}