<?php
/**
 * Curah Hujan Model
 * Model untuk operasi CRUD data curah hujan
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

class CurahHujan {
    
    private $db;
    private $table = 'curah_hujan';
    private $logTable = 'curah_hujan_logs';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all data curah hujan dengan filter
     * 
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        // Filter by date range
        if (!empty($filters['start_date'])) {
            $sql .= " AND tanggal >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND tanggal <= ?";
            $params[] = $filters['end_date'];
        }
        
        // Filter by location
        if (!empty($filters['lokasi'])) {
            $sql .= " AND lokasi = ?";
            $params[] = $filters['lokasi'];
        }
        
        // Filter by year
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(tanggal) = ?";
            $params[] = $filters['year'];
        }
        
        // Filter by month
        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(tanggal) = ?";
            $params[] = $filters['month'];
        }
        
        $sql .= " ORDER BY tanggal DESC";
        
        // Pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $limit = (int) $filters['limit'];
            $offset = (int) $filters['offset'];
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count total records dengan filter
     * 
     * @param array $filters
     * @return int
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND tanggal >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND tanggal <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['lokasi'])) {
            $sql .= " AND lokasi = ?";
            $params[] = $filters['lokasi'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(tanggal) = ?";
            $params[] = $filters['year'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(tanggal) = ?";
            $params[] = $filters['month'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }
    
    /**
     * Get data by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByDateRange($startDate, $endDate) {
        return $this->getAll([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    /**
     * Get statistics (avg, min, max, total)
     * 
     * @param array $filters
     * @return array
     */
    public function getStatistics($filters = []) {
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    ROUND(AVG(curah_hujan), 2) as rata_rata,
                    MAX(curah_hujan) as maksimum,
                    MIN(curah_hujan) as minimum,
                    SUM(curah_hujan) as total_curah_hujan,
                    COUNT(CASE WHEN curah_hujan > 0 THEN 1 END) as hari_hujan,
                    COUNT(CASE WHEN curah_hujan = 0 THEN 1 END) as hari_tidak_hujan
                FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND tanggal >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND tanggal <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(tanggal) = ?";
            $params[] = $filters['year'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(tanggal) = ?";
            $params[] = $filters['month'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get monthly average for a year
     * 
     * @param int $year
     * @return array
     */
    public function getMonthlyAverage($year = null) {
        $year = $year ?: date('Y');
        
        $sql = "SELECT 
                    MONTH(tanggal) as bulan,
                    MONTHNAME(tanggal) as nama_bulan,
                    ROUND(AVG(curah_hujan), 2) as rata_rata,
                    SUM(curah_hujan) as total,
                    COUNT(*) as jumlah_data,
                    MAX(curah_hujan) as maksimum,
                    MIN(curah_hujan) as minimum
                FROM {$this->table}
                WHERE YEAR(tanggal) = ?
                GROUP BY MONTH(tanggal), MONTHNAME(tanggal)
                ORDER BY bulan";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get yearly summary
     * 
     * @param int $limit
     * @return array
     */
    public function getYearlySummary($limit = 5) {
        $sql = "SELECT 
                    YEAR(tanggal) as tahun,
                    ROUND(AVG(curah_hujan), 2) as rata_rata,
                    SUM(curah_hujan) as total,
                    COUNT(*) as jumlah_data,
                    MAX(curah_hujan) as maksimum
                FROM {$this->table}
                GROUP BY YEAR(tanggal)
                ORDER BY tahun DESC
                LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available years
     * 
     * @return array
     */
    public function getAvailableYears() {
        $sql = "SELECT DISTINCT YEAR(tanggal) as tahun FROM {$this->table} ORDER BY tahun DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Insert single record
     * 
     * @param array $data
     * @return int|false
     */
    public function insert($data) {
        $sql = "INSERT INTO {$this->table} 
                (tanggal, lokasi, kode_wilayah, curah_hujan, satuan, sumber_data, keterangan)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                curah_hujan = VALUES(curah_hujan),
                sumber_data = VALUES(sumber_data),
                keterangan = VALUES(keterangan),
                updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['tanggal'],
            $data['lokasi'] ?? 'Jember',
            $data['kode_wilayah'] ?? '35.09',
            $data['curah_hujan'],
            $data['satuan'] ?? 'mm',
            $data['sumber_data'],
            $data['keterangan'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Bulk insert records
     * 
     * @param array $records
     * @return array ['success' => int, 'failed' => int]
     */
    public function bulkInsert($records) {
        $success = 0;
        $failed = 0;
        
        $this->db->beginTransaction();
        
        try {
            foreach ($records as $record) {
                if ($this->insert($record)) {
                    $success++;
                } else {
                    $failed++;
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $failed = count($records);
            $success = 0;
            error_log("Bulk insert curah hujan failed: " . $e->getMessage());
        }
        
        return ['success' => $success, 'failed' => $failed];
    }
    
    /**
     * Delete by ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return int Number of deleted rows
     */
    public function deleteByDateRange($startDate, $endDate) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE tanggal BETWEEN ? AND ?");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->rowCount();
    }

    /**
     * Delete log by ID
     * 
     * @param int $id
     * @return bool
     */
    public function deleteLog($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->logTable} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Log scraping activity
     * 
     * @param string $action
     * @param string $status
     * @param string $message
     * @param array $stats
     * @return bool
     */
    public function logActivity($action, $status, $message, $stats = []) {
        $sql = "INSERT INTO {$this->logTable} 
                (action, status, message, records_processed, records_success, records_failed, execution_time)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $action,
            $status,
            $message,
            $stats['processed'] ?? 0,
            $stats['success'] ?? 0,
            $stats['failed'] ?? 0,
            $stats['execution_time'] ?? null
        ]);
    }
    
    /**
     * Get recent logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentLogs($limit = 10) {
        $sql = "SELECT * FROM {$this->logTable} ORDER BY created_at DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if table exists
     * 
     * @return bool
     */
    public function tableExists() {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Create tables if not exist
     * 
     * @return bool
     */
    public function createTablesIfNotExist() {
        try {
            // Create main table
            $this->db->exec("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tanggal` DATE NOT NULL,
                `lokasi` VARCHAR(100) DEFAULT 'Jember',
                `kode_wilayah` VARCHAR(20) DEFAULT NULL,
                `curah_hujan` DECIMAL(10,2) NOT NULL,
                `satuan` VARCHAR(10) DEFAULT 'mm',
                `sumber_data` VARCHAR(255) NOT NULL,
                `keterangan` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_tanggal_lokasi` (`tanggal`, `lokasi`),
                INDEX `idx_tanggal` (`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            // Create log table
            $this->db->exec("CREATE TABLE IF NOT EXISTS `{$this->logTable}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `action` VARCHAR(50) NOT NULL,
                `status` ENUM('success', 'failed', 'partial') NOT NULL,
                `message` TEXT,
                `records_processed` INT DEFAULT 0,
                `records_success` INT DEFAULT 0,
                `records_failed` INT DEFAULT 0,
                `execution_time` DECIMAL(10,4) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to create curah_hujan tables: " . $e->getMessage());
            return false;
        }
    }
}
