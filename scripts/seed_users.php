<?php
/**
 * User Seeder Script
 * Menambahkan pengguna dengan level berbeda ke database
 * 
 * @author Kiro AI Assistant
 * @version 1.0.0
 * @date 2025-12-01
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define ROOT_PATH
define('ROOT_PATH', dirname(__DIR__));

// Load configuration
require_once ROOT_PATH . '/config/database.php';

/**
 * User Seeder Class
 */
class UserSeeder {
    private $db;
    private $logFile;
    private $startTime;
    private $results = [];
    
    // User definitions
    private $users = [
        [
            'username' => 'admin_jagapadi',
            'password' => 'admin123',
            'nama_lengkap' => 'Administrator JAGAPADI',
            'email' => 'admin@jagapadi.com',
            'phone' => '081234567890',
            'role' => 'admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'status' => 'active'
        ],
        [
            'username' => 'operator1',
            'password' => 'op1test',
            'nama_lengkap' => 'Operator Satu',
            'email' => 'operator1@jagapadi.com',
            'phone' => '081234567891',
            'role' => 'operator',
            'permissions' => ['create', 'read', 'update'],
            'status' => 'active'
        ],
        [
            'username' => 'viewer1',
            'password' => 'vw1test',
            'nama_lengkap' => 'Viewer Satu',
            'email' => 'viewer1@jagapadi.com',
            'phone' => '081234567892',
            'role' => 'viewer',
            'permissions' => ['read'],
            'status' => 'active'
        ],
        [
            'username' => 'petugas',
            'password' => 'petugas3509',
            'nama_lengkap' => 'Petugas Lapangan',
            'email' => 'petugas@jagapadi.com',
            'phone' => '081234567893',
            'role' => 'petugas',
            'permissions' => ['create', 'read'],
            'status' => 'active'
        ]
    ];
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->logFile = ROOT_PATH . '/logs/user_seeder.log';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
    
    /**
     * Log message to file
     */
    private function log($level, $username, $status, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] [$username] [$status] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        
        // Also output to console
        echo $logEntry;
    }
    
    /**
     * Connect to database with retry mechanism
     */
    private function connectDatabase($maxRetries = 3) {
        $retries = 0;
        $lastError = null;
        
        while ($retries < $maxRetries) {
            try {
                $this->db = Database::getInstance()->getConnection();
                $this->log('INFO', 'SYSTEM', 'SUCCESS', 'Database connection established');
                return true;
            } catch (Exception $e) {
                $retries++;
                $lastError = $e->getMessage();
                $this->log('ERROR', 'SYSTEM', 'RETRY', "Connection attempt $retries failed: $lastError");
                
                if ($retries < $maxRetries) {
                    sleep(2); // Wait 2 seconds before retry
                }
            }
        }
        
        $this->log('ERROR', 'SYSTEM', 'FAILED', "Database connection failed after $maxRetries attempts: $lastError");
        return false;
    }
    
    /**
     * Validate user input
     */
    private function validateUser($user) {
        $errors = [];
        
        // Validate username
        if (empty($user['username'])) {
            $errors[] = 'Username tidak boleh kosong';
        } elseif (strlen($user['username']) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $user['username'])) {
            $errors[] = 'Username hanya boleh mengandung huruf, angka, dan underscore';
        }
        
        // Validate password
        if (empty($user['password'])) {
            $errors[] = 'Password tidak boleh kosong';
        } elseif (strlen($user['password']) < 8) {
            $errors[] = 'Password minimal 8 karakter';
        }
        
        // Validate email
        if (!empty($user['email']) && !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        // Validate role
        $validRoles = ['admin', 'operator', 'viewer', 'petugas'];
        if (!in_array($user['role'], $validRoles)) {
            $errors[] = 'Role tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Check if username already exists
     */
    private function usernameExists($username) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            $this->log('ERROR', $username, 'FAILED', 'Error checking username: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Hash password using bcrypt with salt round 10
     */
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    /**
     * Insert user to database
     */
    private function insertUser($user) {
        $sql = "INSERT INTO users (
                    username, 
                    password, 
                    nama_lengkap, 
                    email, 
                    phone, 
                    role, 
                    status,
                    created_at, 
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        $hashedPassword = $this->hashPassword($user['password']);
        
        return $stmt->execute([
            $user['username'],
            $hashedPassword,
            $user['nama_lengkap'],
            $user['email'],
            $user['phone'],
            $user['role'],
            $user['status']
        ]);
    }
    
    /**
     * Seed all users with transaction
     */
    public function seed() {
        $this->log('INFO', 'SYSTEM', 'START', 'Starting user seeding process');
        
        // Connect to database
        if (!$this->connectDatabase()) {
            return $this->generateReport('failed', 'Database connection failed');
        }
        
        try {
            // Begin transaction
            $this->db->beginTransaction();
            $this->log('INFO', 'SYSTEM', 'TRANSACTION', 'Transaction started');
            
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($this->users as $user) {
                $username = $user['username'];
                
                try {
                    // Validate user
                    $errors = $this->validateUser($user);
                    if (!empty($errors)) {
                        throw new Exception(implode(', ', $errors));
                    }
                    
                    // Check duplicate
                    if ($this->usernameExists($username)) {
                        $this->log('WARNING', $username, 'SKIPPED', 'Username already exists');
                        $this->results[] = [
                            'username' => $username,
                            'status' => 'skipped',
                            'message' => 'Username already exists'
                        ];
                        continue;
                    }
                    
                    // Insert user
                    if ($this->insertUser($user)) {
                        $successCount++;
                        $this->log('INFO', $username, 'SUCCESS', "User created with role: {$user['role']}");
                        $this->results[] = [
                            'username' => $username,
                            'role' => $user['role'],
                            'permissions' => implode(', ', $user['permissions']),
                            'status' => 'success',
                            'message' => 'User created successfully'
                        ];
                    } else {
                        throw new Exception('Insert failed');
                    }
                    
                } catch (Exception $e) {
                    $failedCount++;
                    $errorMsg = $e->getMessage();
                    $this->log('ERROR', $username, 'FAILED', $errorMsg);
                    $this->results[] = [
                        'username' => $username,
                        'status' => 'failed',
                        'message' => $errorMsg
                    ];
                    
                    // Rollback on error
                    throw $e;
                }
            }
            
            // Commit transaction
            $this->db->commit();
            $this->log('INFO', 'SYSTEM', 'TRANSACTION', 'Transaction committed');
            
            return $this->generateReport('success', "Successfully created $successCount users");
            
        } catch (Exception $e) {
            // Rollback transaction
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
                $this->log('ERROR', 'SYSTEM', 'ROLLBACK', 'Transaction rolled back: ' . $e->getMessage());
            }
            
            return $this->generateReport('failed', 'Transaction failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate execution report in JSON format
     */
    private function generateReport($overallStatus, $message) {
        $executionTime = microtime(true) - $this->startTime;
        
        $successCount = count(array_filter($this->results, function($r) {
            return $r['status'] === 'success';
        }));
        
        $failedCount = count(array_filter($this->results, function($r) {
            return $r['status'] === 'failed';
        }));
        
        $skippedCount = count(array_filter($this->results, function($r) {
            return $r['status'] === 'skipped';
        }));
        
        $report = [
            'overall_status' => $overallStatus,
            'message' => $message,
            'execution_time' => round($executionTime, 3) . ' seconds',
            'summary' => [
                'total_users' => count($this->users),
                'success' => $successCount,
                'failed' => $failedCount,
                'skipped' => $skippedCount
            ],
            'details' => $this->results,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->log('INFO', 'SYSTEM', 'COMPLETE', "Seeding completed: $successCount success, $failedCount failed, $skippedCount skipped");
        
        return $report;
    }
}

// ============================================
// Main Execution
// ============================================

try {
    echo "===========================================\n";
    echo "  JAGAPADI User Seeder\n";
    echo "===========================================\n\n";
    
    $seeder = new UserSeeder();
    $report = $seeder->seed();
    
    // Output JSON report
    echo "\n===========================================\n";
    echo "  Execution Report\n";
    echo "===========================================\n\n";
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    // Save report to file
    $reportFile = ROOT_PATH . '/logs/user_seeder_report_' . date('Y-m-d_His') . '.json';
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Report saved to: $reportFile\n\n";
    
    // Exit with appropriate code
    exit($report['overall_status'] === 'success' ? 0 : 1);
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
