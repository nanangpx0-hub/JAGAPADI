<?php

class BaseApiController {
    
    /**
     * Send JSON response
     */
    protected function sendResponse($data, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send error response
     */
    protected function sendError($message, $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Get request data
     */
    protected function getRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            return json_decode($input, true) ?? [];
        }
        
        return $_REQUEST;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $requiredFields) {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeData($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeData'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Check user permissions
     */
    protected function checkPermission($requiredRole = null) {
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Unauthorized', 401);
        }
        
        if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
            if ($requiredRole === 'admin' || 
                ($requiredRole === 'operator' && !in_array($_SESSION['role'], ['admin', 'operator']))) {
                $this->sendError('Forbidden', 403);
            }
        }
    }
    
    /**
     * Get pagination parameters
     */
    protected function getPaginationParams() {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * Format pagination response
     */
    protected function formatPaginatedResponse($data, $total, $page, $limit) {
        $totalPages = ceil($total / $limit);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
}