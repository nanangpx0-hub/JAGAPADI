<?php
class User extends Model {
    protected $table = 'users';
    
    /**
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $limit = 20, $search = '', $roleFilter = '', $statusFilter = '', $noLimit = false) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($roleFilter)) {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
        }
        
        if (!empty($statusFilter)) {
            // Convert status filter to aktif value (1 for active, 0 for inactive)
            $aktifValue = ($statusFilter === 'active' || $statusFilter === '1') ? 1 : 0;
            $sql .= " AND aktif = ?";
            $params[] = $aktifValue;
        }
        
        $sql .= " ORDER BY created_at DESC";
        if (!$noLimit) {
            // Cast to int to avoid SQL syntax issues with bound LIMIT/OFFSET in MySQL
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total users count
     */
    public function getTotalUsers($search = '', $roleFilter = '', $statusFilter = '') {
        $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($roleFilter)) {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
        }

        if (!empty($statusFilter)) {
            // Convert status filter to aktif value
            $aktifValue = ($statusFilter === 'active' || $statusFilter === '1') ? 1 : 0;
            $sql .= " AND aktif = ?";
            $params[] = $aktifValue;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user by username
     */
    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        // Hash password with recommended cost for PHP 8.0+
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "INSERT INTO users (username, password, email, nama_lengkap, role, aktif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['username'],
            $data['password'],
            $data['email'],
            $data['nama_lengkap'],
            $data['role'],
            $data['aktif'] ?? 1
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['nama_lengkap'])) {
            $fields[] = "nama_lengkap = ?";
            $params[] = $data['nama_lengkap'];
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['aktif'])) {
            $fields[] = "aktif = ?";
            $params[] = $data['aktif'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Toggle user status (aktif)
     */
    public function toggleStatus($id) {
        $sql = "UPDATE users SET aktif = CASE 
                WHEN aktif = 1 THEN 0 
                ELSE 1 
                END, 
                updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Get users count by role
     */
    public function getUsersCountByRole() {
        $stmt = $this->db->query("
            SELECT role, COUNT(*) as count 
            FROM users 
            GROUP BY role
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Authenticate user with username and password
     */
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        // Check if user is active
        if (isset($user['aktif']) && !$user['aktif']) {
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update last password change timestamp
     */
    public function updateLastPasswordChange($userId) {
        $sql = "UPDATE users SET last_password_change_at = NOW(), must_change_password = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Change user password with validation
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get current user data
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }
        
        // Verify current password (skip for force change)
        if ($currentPassword !== null && !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password lama tidak benar'];
        }
        
        // Validate new password
        $validation = $this->validatePassword($newPassword);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Check if new password is different from current
        if (password_verify($newPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password baru harus berbeda dari password lama'];
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "UPDATE users SET 
                password = ?,
                must_change_password = 0,
                last_password_change_at = NOW(),
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$hashedPassword, $userId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Password berhasil diubah'];
        } else {
            return ['success' => false, 'message' => 'Gagal mengubah password'];
        }
    }
    
    /**
     * Validate password strength
     */
    public function validatePassword($password) {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter';
        }
        
        // Maximum length
        if (strlen($password) > 128) {
            $errors[] = 'Password maksimal 128 karakter';
        }
        
        // Must contain at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 huruf besar';
        }
        
        // Must contain at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 huruf kecil';
        }
        
        // Must contain at least one number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 angka';
        }
        
        // Must contain at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 karakter khusus (!@#$%^&*()_+-=[]{}|;:,.<>?)';
        }
        
        // Check for common weak passwords
        $weakPasswords = [
            'password', 'password123', '12345678', 'qwerty123', 'admin123',
            'Password1', 'Password123', 'Qwerty123', 'Admin123'
        ];
        
        if (in_array($password, $weakPasswords)) {
            $errors[] = 'Password terlalu umum, gunakan kombinasi yang lebih unik';
        }
        
        if (empty($errors)) {
            return ['valid' => true, 'message' => 'Password valid'];
        } else {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
    }
    
    /**
     * Set user to require password change
     */
    public function setMustChangePassword($userId, $mustChange = true) {
        $sql = "UPDATE users SET 
                must_change_password = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$mustChange ? 1 : 0, $userId]);
    }
    
    /**
     * Get users that need password change
     */
    public function getUsersNeedingPasswordChange() {
        $stmt = $this->db->query("
            SELECT id, username, nama_lengkap, role, created_at, last_password_change_at
            FROM users 
            WHERE must_change_password = 1 
            ORDER BY created_at ASC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if password is expired (older than specified days)
     */
    public function checkPasswordExpiry($userId, $maxDays = 90) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                username,
                last_password_change_at,
                DATEDIFF(NOW(), last_password_change_at) as days_since_change
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$user['last_password_change_at']) {
            return ['expired' => true, 'days_since_change' => null];
        }
        
        $daysSinceChange = $user['days_since_change'];
        
        return [
            'expired' => $daysSinceChange > $maxDays,
            'days_since_change' => $daysSinceChange,
            'days_remaining' => max(0, $maxDays - $daysSinceChange)
        ];
    }
    
    /**
     * Log user activity
     */
    public function logActivity($userId, $action, $description) {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, description, ip_address, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }
}
