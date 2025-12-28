<?php
session_start();

// Load configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        'app/controllers/',
        'app/models/',
        'app/core/',
        'app/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = ROOT_PATH . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Get request URI
$request = $_SERVER['REQUEST_URI'];
$request = str_replace('/jagapadi/', '', $request);
$request = strtok($request, '?'); // Remove query string

// Check if this is an API request
if (strpos($request, 'api/') === 0 || strpos($request, '/api/') === 0) {
    // Handle API requests with the Router class
    require_once ROOT_PATH . '/app/core/Router.php';
    $router = new Router();
    
    if ($router->handleRequest()) {
        // API request was handled
        exit;
    } else {
        // API route not found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'API endpoint not found',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}

// Handle regular web requests
// Default route
if ($request == '' || $request == '/') {
    if (isset($_SESSION['user_id'])) {
        $request = 'dashboard';
    } else {
        $request = 'auth/login';
    }
}

// Parse route
$parts = explode('/', trim($request, '/'));
$controllerPart = $parts[0] ?? 'dashboard';
// Handle camelCase and dash/underscore in route names
$controllerName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controllerPart))) . 'Controller';
$method = $parts[1] ?? 'index';
$params = array_slice($parts, 2);

// Check if controller exists
$controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "404 - Page Not Found";
    exit;
}

// Create controller and call method
require_once $controllerFile;
$controller = new $controllerName();

if (!method_exists($controller, $method)) {
    http_response_code(404);
    echo "404 - Method Not Found";
    exit;
}

call_user_func_array([$controller, $method], $params);
