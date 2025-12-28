<?php
/**
 * Import Logger
 * Handles logging for import activities and rate limiting
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

class ImportLogger {
    
    private $logFile;
    private $rateLimitFile;
    
    public function __construct() {
        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/import_' . date('Y-m') . '.log';
        $this->rateLimitFile = $logDir . '/rate_limit.json';
    }
    
    /**
     * Log import activity
     * 
     * @param int $userId
     * @param array $data
     */
    public function logImport($userId, $data) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'action' => 'import_users',
            'total_rows' => $data['total_rows'] ?? 0,
            'imported' => $data['imported'] ?? 0,
            'failed' => $data['failed'] ?? 0,
            'status' => $data['status'] ?? 'unknown',
            'error' => $data['error'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Also log to database if table exists
        $this->logToDatabase($userId, $data);
    }
    
    /**
     * Log to database table
     * 
     * @param int $userId
     * @param array $data
     */
    private function logToDatabase($userId, $data) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if table exists
            $stmt = $db->query("SHOW TABLES LIKE 'user_import_logs'");
            if ($stmt->rowCount() == 0) {
                // Create table if not exists
                $this->createLogTable($db);
            }
            
            $stmt = $db->prepare("
                INSERT INTO user_import_logs 
                (user_id, filename, total_rows, success_count, failed_count, status, error_details, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $data['filename'] ?? 'unknown',
                $data['total_rows'] ?? 0,
                $data['imported'] ?? 0,
                $data['failed'] ?? 0,
                $data['status'] ?? 'unknown',
                json_encode($data['errors'] ?? [], JSON_UNESCAPED_UNICODE)
            ]);
            
        } catch (Exception $e) {
            // Silently fail - don't break import if logging fails
            error_log('Import logging to database failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Create log table if not exists
     * 
     * @param PDO $db
     */
    private function createLogTable($db) {
        $sql = "CREATE TABLE IF NOT EXISTS `user_import_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `filename` VARCHAR(255) DEFAULT NULL,
            `total_rows` INT(11) DEFAULT 0,
            `success_count` INT(11) DEFAULT 0,
            `failed_count` INT(11) DEFAULT 0,
            `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            `error_details` JSON DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $db->exec($sql);
    }
    
    /**
     * Check rate limiting for user
     * 
     * @param int $userId
     * @param int $maxPerMinute
     * @return bool True if allowed, false if rate limited
     */
    public function checkRateLimit($userId, $maxPerMinute = 3) {
        $limits = $this->getRateLimits();
        $now = time();
        $key = 'user_' . $userId;
        
        // Clean old entries
        if (isset($limits[$key])) {
            $limits[$key] = array_filter($limits[$key], function($timestamp) use ($now) {
                return ($now - $timestamp) < 60;
            });
        } else {
            $limits[$key] = [];
        }
        
        // Check if over limit
        if (count($limits[$key]) >= $maxPerMinute) {
            return false;
        }
        
        // Add current timestamp
        $limits[$key][] = $now;
        $this->saveRateLimits($limits);
        
        return true;
    }
    
    /**
     * Get rate limit data
     * 
     * @return array
     */
    private function getRateLimits() {
        if (!file_exists($this->rateLimitFile)) {
            return [];
        }
        
        $data = file_get_contents($this->rateLimitFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Save rate limit data
     * 
     * @param array $limits
     */
    private function saveRateLimits($limits) {
        file_put_contents($this->rateLimitFile, json_encode($limits), LOCK_EX);
    }
    
    /**
     * Log error details
     * 
     * @param string $action
     * @param string $error
     * @param array $context
     */
    public function logError($action, $error, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'action' => $action,
            'error' => $error,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get import history for a user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getImportHistory($userId, $limit = 10) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT * FROM user_import_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
