<?php
/**
 * Error Logger Helper
 * Untuk logging error dan warning di aplikasi
 */

class ErrorLogger {
    private static $logFile = 'logs/error.log';
    
    /**
     * Log error ke file
     * 
     * @param string $message Pesan error
     * @param string $level Level error (ERROR, WARNING, INFO)
     * @param array $context Context tambahan
     */
    public static function log($message, $level = 'ERROR', $context = []) {
        $logDir = ROOT_PATH . '/logs';
        
        // Create logs directory if not exists
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/error.log';
        
        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message";
        
        if ($contextStr) {
            $logEntry .= " | Context: $contextStr";
        }
        
        $logEntry .= PHP_EOL;
        
        // Write to log file
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Also log to PHP error log if it's an ERROR
        if ($level === 'ERROR') {
            error_log($message);
        }
    }
    
    /**
     * Log missing array key
     * 
     * @param string $key Key yang tidak ditemukan
     * @param string $file File tempat error terjadi
     * @param int $line Line number
     */
    public static function logMissingKey($key, $file = '', $line = 0) {
        $context = [
            'key' => $key,
            'file' => $file,
            'line' => $line,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ];
        
        self::log("Missing array key: $key", 'WARNING', $context);
    }
    
    /**
     * Log database query error
     * 
     * @param string $query Query yang error
     * @param string $error Error message
     */
    public static function logQueryError($query, $error) {
        $context = [
            'query' => $query,
            'error' => $error,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ];
        
        self::log("Database query error", 'ERROR', $context);
    }
    
    /**
     * Log null parameter warning
     * 
     * @param string $function Function name
     * @param string $parameter Parameter name
     */
    public static function logNullParameter($function, $parameter) {
        $context = [
            'function' => $function,
            'parameter' => $parameter,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ];
        
        self::log("Null parameter passed to $function: $parameter", 'WARNING', $context);
    }
    
    /**
     * Get recent logs
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public static function getRecentLogs($lines = 50) {
        $logFile = ROOT_PATH . '/logs/error.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $file = new SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;
        
        $startLine = max(0, $totalLines - $lines);
        
        $logs = [];
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return array_reverse($logs);
    }
    
    /**
     * Clear log file
     */
    public static function clearLogs() {
        $logFile = ROOT_PATH . '/logs/error.log';
        
        if (file_exists($logFile)) {
            @file_put_contents($logFile, '');
        }
    }
}
