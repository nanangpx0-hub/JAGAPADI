<?php
class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Index - List all users (Admin only)
     */
    public function index() {
        $this->checkAuth();
        $this->checkAdmin();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $limit = 20;
        
        $users = $this->userModel->getAllUsers($page, $limit, $search, $roleFilter, $statusFilter);
        $totalUsers = $this->userModel->getTotalUsers($search, $roleFilter, $statusFilter);
        $totalPages = ceil($totalUsers / $limit);
        
        $data = [
            'title' => 'Manajemen User',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter
        ];
        
        $this->view('admin/users/index', $data);
    }
    
    /**
     * Create - Show create form
     */
    public function create() {
        $this->checkAuth();
        $this->checkAdmin();
        
        $data = [
            'title' => 'Tambah User Baru'
        ];
        
        $this->view('admin/users/create', $data);
    }
    
    /**
     * Store - Save new user
     */
    public function store() {
        $this->checkAuth();
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'user/create');
            exit;
        }
        
        // Validate input
        $errors = [];
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username harus diisi';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        } elseif ($this->userModel->getUserByUsername($username)) {
            $errors[] = 'Username sudah digunakan';
        }
        
        if (empty($email)) {
            $errors[] = 'Email harus diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif ($this->userModel->getUserByEmail($email)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        if (empty($nama_lengkap)) {
            $errors[] = 'Nama lengkap harus diisi';
        }
        
        if (empty($password)) {
            $errors[] = 'Password harus diisi';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        if (empty($role) || !in_array($role, ['admin', 'operator', 'viewer', 'petugas'])) {
            $errors[] = 'Role tidak valid';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'user/create');
            exit;
        }
        
        // Create user
        $userData = [
            'username' => $username,
            'email' => $email,
            'nama_lengkap' => $nama_lengkap,
            'password' => $password,
            'role' => $role,
            'aktif' => isset($_POST['aktif']) ? (int)$_POST['aktif'] : 1
        ];
        
        $userId = $this->userModel->createUser($userData);
        
        if ($userId) {
            // Log activity
            $this->userModel->logActivity(
                $_SESSION['user_id'],
                'create_user',
                "Membuat user baru: $username (ID: $userId)"
            );
            
            $_SESSION['success'] = 'User berhasil ditambahkan';
            header('Location: ' . BASE_URL . 'user');
        } else {
            $_SESSION['error'] = 'Gagal menambahkan user';
            header('Location: ' . BASE_URL . 'user/create');
        }
        exit;
    }

    /**
     * Export users to CSV
     */
    public function exportCsv() {
        $this->checkAuth();
        $this->checkAdmin();

        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';

        $users = $this->userModel->getAllUsers(1, 100000, $search, $roleFilter, $statusFilter, true);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=users.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nama Lengkap', 'Username', 'Email', 'Role', 'Status', 'Tanggal Registrasi']);

        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['nama_lengkap'],
                $user['username'],
                $user['email'],
                $user['role'],
                $user['status'],
                $user['created_at']
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Export users to Excel (simple HTML table)
     */
    public function exportExcel() {
        $this->checkAuth();
        $this->checkAdmin();

        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';

        $users = $this->userModel->getAllUsers(1, 100000, $search, $roleFilter, $statusFilter, true);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=users.xls");

        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nama Lengkap</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Tanggal Registrasi</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nama_lengkap']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
    
    /**
     * Edit - Show edit form
     */
    public function edit($id) {
        $this->checkAuth();
        $this->checkAdmin();
        
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User tidak ditemukan';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        $data = [
            'title' => 'Edit User',
            'user' => $user
        ];
        
        $this->view('admin/users/edit', $data);
    }
    
    /**
     * Update - Save user changes
     */
    public function update($id) {
        $this->checkAuth();
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'user/edit/' . $id);
            exit;
        }
        
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User tidak ditemukan';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        // Prevent admin from editing themselves
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Anda tidak dapat mengubah data user sendiri';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        // Validate input
        $errors = [];
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username harus diisi';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        } elseif ($username !== $user['username']) {
            $existingUser = $this->userModel->getUserByUsername($username);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors[] = 'Username sudah digunakan';
            }
        }
        
        if (empty($email)) {
            $errors[] = 'Email harus diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif ($email !== $user['email']) {
            $existingUser = $this->userModel->getUserByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors[] = 'Email sudah digunakan';
            }
        }
        
        if (empty($nama_lengkap)) {
            $errors[] = 'Nama lengkap harus diisi';
        }
        
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter';
            } elseif ($password !== $password_confirm) {
                $errors[] = 'Konfirmasi password tidak cocok';
            }
        }
        
        if (empty($role) || !in_array($role, ['admin', 'operator', 'viewer', 'petugas'])) {
            $errors[] = 'Role tidak valid';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'user/edit/' . $id);
            exit;
        }
        
        // Update user
        $userData = [
            'username' => $username,
            'email' => $email,
            'nama_lengkap' => $nama_lengkap,
            'role' => $role,
            'aktif' => isset($_POST['aktif']) ? (int)$_POST['aktif'] : 1
        ];
        
        if (!empty($password)) {
            $userData['password'] = $password;
        }
        
        $result = $this->userModel->updateUser($id, $userData);
        
        if ($result) {
            // Log activity
            $this->userModel->logActivity(
                $_SESSION['user_id'],
                'update_user',
                "Mengubah data user: $username (ID: $id)"
            );
            
            $_SESSION['success'] = 'User berhasil diupdate';
            header('Location: ' . BASE_URL . 'user');
        } else {
            $_SESSION['error'] = 'Gagal mengupdate user';
            header('Location: ' . BASE_URL . 'user/edit/' . $id);
        }
        exit;
    }
    
    /**
     * Delete - Remove user
     */
    public function delete($id) {
        $this->checkAuth();
        $this->checkAdmin();
        
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User tidak ditemukan';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        // Prevent admin from deleting themselves
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Anda tidak dapat menghapus user sendiri';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        $result = $this->userModel->deleteUser($id);
        
        if ($result) {
            // Log activity
            $this->userModel->logActivity(
                $_SESSION['user_id'],
                'delete_user',
                "Menghapus user: {$user['username']} (ID: $id)"
            );
            
            $_SESSION['success'] = 'User berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus user';
        }
        
        header('Location: ' . BASE_URL . 'user');
        exit;
    }
    
    /**
     * Toggle Status - Activate/Deactivate user
     */
    public function toggleStatus($id) {
        $this->checkAuth();
        $this->checkAdmin();
        
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User tidak ditemukan';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        // Prevent admin from deactivating themselves
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Anda tidak dapat mengubah status user sendiri';
            header('Location: ' . BASE_URL . 'user');
            exit;
        }
        
        $result = $this->userModel->toggleStatus($id);
        
        if ($result) {
            $newStatus = $user['aktif'] == 1 ? 'nonaktif' : 'aktif';
            
            // Log activity
            $this->userModel->logActivity(
                $_SESSION['user_id'],
                'toggle_user_status',
                "Mengubah status user {$user['username']} menjadi $newStatus"
            );
            
            $_SESSION['success'] = 'Status user berhasil diubah';
        } else {
            $_SESSION['error'] = 'Gagal mengubah status user';
        }
        
        header('Location: ' . BASE_URL . 'user');
        exit;
    }
    
    /**
     * Import - Show import page
     */
    public function import() {
        $this->checkAuth();
        $this->checkAdmin();
        
        $data = [
            'title' => 'Import User dari Excel'
        ];
        
        $this->view('admin/users/import', $data);
    }
    
    /**
     * Download import template
     */
    public function downloadTemplate() {
        $this->checkAuth();
        $this->checkAdmin();
        
        require_once ROOT_PATH . '/app/helpers/SimpleXLSXWriter.php';
        
        $xlsx = new SimpleXLSXWriter();
        
        // Sheet 1: Data User
        $userData = [
            ['Nama Lengkap', 'Username', 'Email', 'Role', 'Status'],
            ['John Doe', 'johndoe', 'johndoe@email.com', 'operator', 'Aktif'],
            ['Jane Smith', 'janesmith', 'janesmith@email.com', 'petugas', 'Aktif'],
            ['Ahmad Rizki', 'ahmadrizki', 'ahmad@email.com', 'viewer', 'Aktif']
        ];
        
        $xlsx->addSheet('Data User', $userData, [
            'headerStyle' => true,
            'columnWidths' => [
                0 => 25,  // Nama Lengkap
                1 => 20,  // Username
                2 => 30,  // Email
                3 => 15,  // Role
                4 => 12   // Status
            ]
        ]);
        
        // Sheet 2: Instruksi
        $instruksi = [
            ['PETUNJUK PENGGUNAAN TEMPLATE IMPORT USER'],
            [''],
            ['1. Isi data user pada sheet "Data User"'],
            ['2. Kolom yang wajib diisi: Nama Lengkap, Username, Email, Role'],
            ['3. Hapus contoh data pada baris 2-4 sebelum mengisi data Anda'],
            [''],
            ['FORMAT KOLOM:'],
            ['- Nama Lengkap: Maksimal 100 karakter'],
            ['- Username: Huruf, angka, dan underscore saja (maks 50 karakter)'],
            ['- Email: Format email yang valid'],
            ['- Role: admin, operator, viewer, atau petugas'],
            ['- Status: Aktif atau Nonaktif (default: Aktif)'],
            [''],
            ['CATATAN PENTING:'],
            ['- Username harus unik (belum terdaftar di sistem)'],
            ['- Email harus unik (belum terdaftar di sistem)'],
            ['- Password default = Username (user diminta ganti saat login pertama)']
        ];
        
        $xlsx->addSheet('Instruksi', $instruksi, [
            'headerStyle' => false,
            'columnWidths' => [0 => 60]
        ]);
        
        // Download file
        $xlsx->download('template_import_user.xlsx');
    }
    
    /**
     * Preview upload - AJAX endpoint
     */
    public function uploadPreview() {
        $this->checkAuth();
        $this->checkAdmin();
        
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Token keamanan tidak valid']);
            exit;
        }
        
        // Check if file uploaded
        if (!isset($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
            echo json_encode(['success' => false, 'error' => 'Tidak ada file yang diupload']);
            exit;
        }
        
        require_once ROOT_PATH . '/app/services/UserImportService.php';
        $importService = new UserImportService();
        
        // Check rate limiting
        if (!$importService->checkRateLimit($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Terlalu banyak request. Tunggu 1 menit.']);
            exit;
        }
        
        // Validate file
        $validation = $importService->validateFile($_FILES['file']);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'error' => $validation['error']]);
            exit;
        }
        
        // Move to temp location - use logs directory which is definitely writable
        $tempDirs = [
            ROOT_PATH . '/logs',
            sys_get_temp_dir(),
            dirname($_FILES['file']['tmp_name']),
            'C:/Windows/Temp'
        ];
        
        $tempPath = null;
        foreach ($tempDirs as $dir) {
            if (is_dir($dir) && is_writable($dir)) {
                $tempPath = $dir . '/import_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
                break;
            }
        }
        
        if (!$tempPath) {
            echo json_encode(['success' => false, 'error' => 'Tidak dapat menemukan direktori temp yang writable']);
            exit;
        }
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $tempPath)) {
            echo json_encode(['success' => false, 'error' => 'Gagal menyimpan file sementara ke ' . dirname($tempPath)]);
            exit;
        }
        
        // Verify file exists after move
        if (!file_exists($tempPath)) {
            echo json_encode(['success' => false, 'error' => 'File temp tidak ditemukan setelah upload']);
            exit;
        }
        
        // Parse Excel/CSV
        $parseResult = $importService->parseExcel($tempPath);
        if (!$parseResult['success']) {
            @unlink($tempPath);
            $errorMsg = $parseResult['error'];
            if (!empty($parseResult['debug'])) {
                error_log('Parse debug: ' . json_encode($parseResult['debug']));
            }
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            exit;
        }
        
        // Validate data
        $validationResult = $importService->validateData($parseResult['data']);
        
        // Store temp path in session for later processing
        $_SESSION['import_temp_file'] = $tempPath;
        $_SESSION['import_validation'] = $validationResult;
        
        // Get preview data
        $preview = $importService->getPreviewData(10);
        
        echo json_encode([
            'success' => true,
            'preview' => $preview,
            'stats' => $validationResult['stats'],
            'canImport' => $validationResult['stats']['valid_count'] > 0
        ]);
        exit;
    }
    
    /**
     * Process import - Final import execution
     */
    public function processImport() {
        $this->checkAuth();
        $this->checkAdmin();
        
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Token keamanan tidak valid']);
            exit;
        }
        
        // Check if we have validated data in session
        if (!isset($_SESSION['import_temp_file']) || !isset($_SESSION['import_validation'])) {
            echo json_encode(['success' => false, 'error' => 'Sesi import tidak valid. Silakan upload ulang.']);
            exit;
        }
        
        $tempPath = $_SESSION['import_temp_file'];
        $validationResult = $_SESSION['import_validation'];
        
        // Check if temp file still exists
        if (!file_exists($tempPath)) {
            unset($_SESSION['import_temp_file'], $_SESSION['import_validation']);
            echo json_encode(['success' => false, 'error' => 'File sementara tidak ditemukan. Silakan upload ulang.']);
            exit;
        }
        
        require_once ROOT_PATH . '/app/services/UserImportService.php';
        $importService = new UserImportService();
        
        // Re-parse and validate to ensure consistency
        $parseResult = $importService->parseExcel($tempPath);
        if (!$parseResult['success']) {
            unlink($tempPath);
            unset($_SESSION['import_temp_file'], $_SESSION['import_validation']);
            echo json_encode(['success' => false, 'error' => $parseResult['error']]);
            exit;
        }
        
        $importService->validateData($parseResult['data']);
        
        // Execute import
        $result = $importService->importUsers($_SESSION['user_id']);
        
        // Clean up
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        unset($_SESSION['import_temp_file'], $_SESSION['import_validation']);
        
        // Log activity
        $this->userModel->logActivity(
            $_SESSION['user_id'],
            'import_users',
            "Mengimport {$result['imported']} user baru, {$result['failed']} gagal"
        );
        
        echo json_encode([
            'success' => $result['success'],
            'imported' => $result['imported'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
            'message' => $result['success'] 
                ? "Berhasil mengimport {$result['imported']} user." 
                : 'Terjadi kesalahan saat import.'
        ]);
        exit;
    }
    
    /**
     * Check if user is admin
     */
    private function checkAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }
}
