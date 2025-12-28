<?php
class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = $this->model('User');
    }
    

    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!Security::validateCsrfToken($token)) {
                Security::logSecurityEvent('CSRF_VIOLATION', 'Invalid CSRF token on login', null);
                $_SESSION['error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
                $this->redirect('auth/login');
            }
            
            // Check brute force protection
            if (Security::checkBruteForce('login', 5, 900)) {
                $_SESSION['error'] = 'Terlalu banyak percobaan login. Silakan coba lagi setelah 15 menit.';
                $this->redirect('auth/login');
            }
            
            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Username dan password harus diisi';
                $this->redirect('auth/login');
            }
            
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                // Check if user must change password
                if ($user['must_change_password'] == 1) {
                    $_SESSION['user_needs_password_change'] = $user['id'];
                    $_SESSION['temp_user_data'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'nama_lengkap' => $user['nama_lengkap']
                    ];
                    $_SESSION['success'] = 'Silakan ganti password Anda sebelum melanjutkan.';
                    $this->redirect('auth/change_password');
                }
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                
                // Update last_password_change_at if password was just changed
                if (isset($_SESSION['password_changed']) && $_SESSION['password_changed']) {
                    $this->userModel->updateLastPasswordChange($user['id']);
                    unset($_SESSION['password_changed']);
                }
                
                // Log activity
                $this->logActivity($user['id'], 'Login', 'users', $user['id'], 'User login berhasil');
                
                $_SESSION['success'] = 'Login berhasil. Selamat datang, ' . $user['nama_lengkap'];
                $this->redirect('dashboard');
            } else {
                $_SESSION['error'] = 'Username atau password salah';
                $this->redirect('auth/login');
            }
        }
        
        $this->view('auth/login');
    }
    
    public function change_password() {
        // Check if user needs to change password
        if (!isset($_SESSION['user_needs_password_change']) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Akses tidak valid';
            $this->redirect('auth/login');
        }
        
        // Get user data
        $userId = $_SESSION['user_needs_password_change'] ?? $_SESSION['user_id'];
        $userData = $_SESSION['temp_user_data'] ?? null;
        $isForceChange = isset($_SESSION['user_needs_password_change']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!Security::validateCsrfToken($token)) {
                $_SESSION['error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
                $this->redirect('auth/change_password');
            }
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (!$isForceChange && empty($currentPassword)) {
                $_SESSION['error'] = 'Password lama harus diisi';
                $this->redirect('auth/change_password');
            }
            
            if (empty($newPassword)) {
                $_SESSION['error'] = 'Password baru harus diisi';
                $this->redirect('auth/change_password');
            }
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Konfirmasi password tidak cocok';
                $this->redirect('auth/change_password');
            }
            
            // Change password
            $result = $this->userModel->changePassword(
                $userId, 
                $isForceChange ? null : $currentPassword, 
                $newPassword
            );
            
            if ($result['success']) {
                // Log activity
                $this->logActivity($userId, 'Password Change', 'users', $userId, 'Password berhasil diubah');
                
                if ($isForceChange) {
                    // Complete the login process
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['username'] = $userData['username'];
                    $_SESSION['role'] = $userData['role'];
                    $_SESSION['nama_lengkap'] = $userData['nama_lengkap'];
                    
                    // Clean up temporary session data
                    unset($_SESSION['user_needs_password_change']);
                    unset($_SESSION['temp_user_data']);
                    
                    $_SESSION['success'] = 'Password berhasil diubah. Selamat datang, ' . $userData['nama_lengkap'];
                    $this->redirect('dashboard');
                } else {
                    $_SESSION['success'] = $result['message'];
                    $this->redirect('dashboard');
                }
            } else {
                $_SESSION['error'] = $result['message'];
                $this->redirect('auth/change_password');
            }
        }
        
        // Pass data to view
        $data = [
            'is_force_change' => $isForceChange,
            'user_data' => $userData,
            'csrf_token' => Security::generateCsrfToken()
        ];
        
        $this->view('auth/change_password', $data);
    }
    
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->logActivity($userId, 'Logout', 'users', $userId, 'User logout');
        }
        
        session_destroy();
        $this->redirect('auth/login');
    }
    
    private function logActivity($userId, $action, $table, $recordId, $description) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $table,
            $recordId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
