<?php
class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require_once ROOT_PATH . '/app/views/' . $view . '.php';
    }
    
    protected function model($model) {
        require_once ROOT_PATH . '/app/models/' . $model . '.php';
        return new $model();
    }
    
    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        
        // Ensure CSRF token exists for authenticated sessions
        if (class_exists('Security')) {
            Security::generateCsrfToken();
        }
    }

    protected function validateCsrfToken() {
        // Only validate for state-changing requests
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

            if (!Security::validateCsrfToken($token)) {
                Security::logSecurityEvent('CSRF_VIOLATION', 'Invalid CSRF token detected', $_SESSION['user_id'] ?? null);
                http_response_code(403);
                $this->json(['error' => 'CSRF token validation failed'], 403);
            }
        }
    }
    
    protected function checkRole($roles = [], $customMessage = null) {
        $this->checkAuth();
        if (!in_array($_SESSION['role'], $roles)) {
            $message = $customMessage ?? 'Anda tidak memiliki akses ke halaman ini';
            $_SESSION['error'] = $message;
            $this->redirect('dashboard');
        }
    }
    
    protected function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'nama_lengkap' => $_SESSION['nama_lengkap']
            ];
        }
        return null;
    }
}
