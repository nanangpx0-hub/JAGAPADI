<?php
/**
 * Error Message Handler
 * Provides user-friendly error messages and centralized error handling
 */
class ErrorMessage {
    /**
     * Error message templates
     */
    private static $messages = [
        // Database errors
        'database_connection' => 'Koneksi ke database gagal. Silakan coba lagi dalam beberapa saat.',
        'database_query' => 'Terjadi kesalahan saat mengambil data. Silakan coba lagi.',
        'database_insert' => 'Gagal menyimpan data. Pastikan semua field diisi dengan benar.',
        'database_update' => 'Gagal memperbarui data. Pastikan data yang diubah valid.',
        'database_delete' => 'Gagal menghapus data. Data mungkin sedang digunakan.',
        
        // Validation errors
        'validation_required' => 'Field {field} wajib diisi.',
        'validation_email' => 'Format email tidak valid.',
        'validation_min_length' => 'Field {field} minimal {min} karakter.',
        'validation_max_length' => 'Field {field} maksimal {max} karakter.',
        'validation_numeric' => 'Field {field} harus berupa angka.',
        'validation_unique' => '{field} sudah digunakan. Silakan gunakan nilai lain.',
        'validation_file_size' => 'Ukuran file terlalu besar. Maksimal {max}MB.',
        'validation_file_type' => 'Tipe file tidak diizinkan. Hanya {types} yang diizinkan.',
        'validation_gps_range' => 'Koordinat GPS harus berada dalam wilayah Jember.',
        
        // Authentication errors
        'auth_invalid_credentials' => 'Username atau password salah.',
        'auth_inactive' => 'Akun Anda tidak aktif. Hubungi administrator.',
        'auth_required' => 'Anda harus login untuk mengakses halaman ini.',
        'auth_permission_denied' => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
        'auth_session_expired' => 'Session Anda telah berakhir. Silakan login lagi.',
        
        // File upload errors
        'upload_failed' => 'Gagal mengunggah file. Pastikan file valid dan tidak terlalu besar.',
        'upload_no_file' => 'Tidak ada file yang diunggah.',
        'upload_invalid_type' => 'Tipe file tidak diizinkan.',
        'upload_too_large' => 'Ukuran file terlalu besar.',
        
        // General errors
        'not_found' => 'Data yang Anda cari tidak ditemukan.',
        'server_error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
        'csrf_invalid' => 'Token keamanan tidak valid. Silakan refresh halaman dan coba lagi.',
        'rate_limit' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
        
        // Success messages
        'success_create' => 'Data berhasil ditambahkan.',
        'success_update' => 'Data berhasil diperbarui.',
        'success_delete' => 'Data berhasil dihapus.',
        'success_verify' => 'Laporan berhasil diverifikasi.',
    ];
    
    /**
     * Get error message
     * 
     * @param string $key Error key
     * @param array $params Parameters to replace in message
     * @return string Error message
     */
    public static function get($key, $params = []) {
        $message = self::$messages[$key] ?? 'Terjadi kesalahan yang tidak diketahui.';
        
        // Replace placeholders
        foreach ($params as $param => $value) {
            $message = str_replace('{' . $param . '}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Set error message in session
     * 
     * @param string $key Error key
     * @param array $params Parameters
     */
    public static function set($key, $params = []) {
        $_SESSION['error'] = self::get($key, $params);
    }
    
    /**
     * Set success message in session
     * 
     * @param string $key Success key
     * @param array $params Parameters
     */
    public static function setSuccess($key, $params = []) {
        $_SESSION['success'] = self::get($key, $params);
    }
    
    /**
     * Get and clear error message from session
     * 
     * @return string|null Error message or null
     */
    public static function flash() {
        if (isset($_SESSION['error'])) {
            $message = $_SESSION['error'];
            unset($_SESSION['error']);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Get and clear success message from session
     * 
     * @return string|null Success message or null
     */
    public static function flashSuccess() {
        if (isset($_SESSION['success'])) {
            $message = $_SESSION['success'];
            unset($_SESSION['success']);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Format error for JSON response
     * 
     * @param string $key Error key
     * @param array $params Parameters
     * @return array Formatted error array
     */
    public static function json($key, $params = []) {
        return [
            'success' => false,
            'error' => self::get($key, $params),
            'message' => self::get($key, $params)
        ];
    }
    
    /**
     * Format success for JSON response
     * 
     * @param string $key Success key
     * @param array $params Parameters
     * @param mixed $data Additional data
     * @return array Formatted success array
     */
    public static function jsonSuccess($key, $params = [], $data = null) {
        $response = [
            'success' => true,
            'message' => self::get($key, $params)
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Handle exception and return user-friendly message
     * 
     * @param Exception $e Exception
     * @return string User-friendly error message
     */
    public static function fromException($e) {
        // Log the actual error
        error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        // Return user-friendly message
        if ($e instanceof PDOException) {
            return self::get('database_query');
        }
        
        return self::get('server_error');
    }
}

