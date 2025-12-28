<?php

class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function __construct() {
        $this->loadApiRoutes();
    }
    
    /**
     * Add a GET route
     */
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add a POST route
     */
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add a PUT route
     */
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Add a DELETE route
     */
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Add a route with any method
     */
    private function addRoute($method, $path, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Load API routes
     */
    private function loadApiRoutes() {
        // Laporan Hama API Routes
        $this->get('/api/laporan-hama', 'Api\LaporanHamaController@index', ['auth']);
        $this->get('/api/laporan-hama/{id}', 'Api\LaporanHamaController@show', ['auth']);
        $this->post('/api/laporan-hama', 'Api\LaporanHamaController@store', ['auth']);
        $this->put('/api/laporan-hama/{id}', 'Api\LaporanHamaController@update', ['auth']);
        $this->delete('/api/laporan-hama/{id}', 'Api\LaporanHamaController@destroy', ['auth', 'admin']);
        
        // Irigasi API Routes
        $this->get('/api/irigasi', 'Api\IrigasiController@index', ['auth']);
        $this->get('/api/irigasi/{id}', 'Api\IrigasiController@show', ['auth']);
        $this->post('/api/irigasi', 'Api\IrigasiController@store', ['auth']);
        $this->put('/api/irigasi/{id}', 'Api\IrigasiController@update', ['auth']);
        $this->delete('/api/irigasi/{id}', 'Api\IrigasiController@destroy', ['auth', 'admin']);
        
        // IoT/Pengairan API Routes
        $this->get('/api/pengairan/sensor', 'Api\IoTController@getSensors', ['auth']);
        $this->get('/api/pengairan/aktuator', 'Api\IoTController@getActuators', ['auth']);
        $this->get('/api/pengairan/log', 'Api\IoTController@getLogs', ['auth']);
        $this->post('/api/pengairan/sensor/{id}/update', 'Api\IoTController@updateSensor', ['auth']);
        $this->post('/api/pengairan/aktuator/{id}/control', 'Api\IoTController@controlActuator', ['auth']);
        $this->get('/api/pengairan/sensor/realtime', 'Api\IoTController@getRealtimeSensors', ['auth']);
        $this->get('/api/pengairan/schedule', 'Api\IoTController@getSchedule', ['auth']);
        $this->post('/api/pengairan/schedule/update', 'Api\IoTController@updateSchedule', ['auth']);
        
        // Wilayah API Routes
        $this->get('/api/wilayah/kabupaten', 'Api\WilayahController@getKabupaten');
        $this->get('/api/wilayah/kecamatan/{kabupaten_id}', 'Api\WilayahController@getKecamatan');
        $this->get('/api/wilayah/desa/{kecamatan_id}', 'Api\WilayahController@getDesa');
        $this->get('/api/wilayah/hierarchy', 'Api\WilayahController@getHierarchy');
        $this->get('/api/wilayah/search', 'Api\WilayahController@search');
        $this->get('/api/wilayah/stats', 'Api\WilayahController@getStats', ['auth']);
        $this->get('/api/wilayah/by-coordinates', 'Api\WilayahController@getByCoordinates');
        
        // Dashboard API Routes
        $this->get('/api/dashboard/stats', 'Api\DashboardController@getStats', ['auth']);
        $this->get('/api/dashboard/charts', 'Api\DashboardController@getChartData', ['auth']);
        $this->get('/api/dashboard/activities', 'Api\DashboardController@getActivities', ['auth']);
        $this->get('/api/dashboard/alerts', 'Api\DashboardController@getAlerts', ['auth']);
        
        // User API Routes
        $this->get('/api/users', 'Api\UserController@index', ['auth', 'admin']);
        $this->get('/api/users/{id}', 'Api\UserController@show', ['auth']);
        $this->post('/api/users', 'Api\UserController@store', ['auth', 'admin']);
        $this->put('/api/users/{id}', 'Api\UserController@update', ['auth']);
        $this->delete('/api/users/{id}', 'Api\UserController@destroy', ['auth', 'admin']);
        $this->post('/api/users/{id}/toggle-status', 'Api\UserController@toggleStatus', ['auth', 'admin']);
        $this->get('/api/users/profile', 'Api\UserController@getProfile', ['auth']);
        $this->put('/api/users/profile', 'Api\UserController@updateProfile', ['auth']);
        $this->post('/api/users/change-password', 'Api\UserController@changePassword', ['auth']);
        $this->post('/api/users/force-change-password', 'Api\UserController@forceChangePassword', ['auth']);
        $this->get('/api/users/check-password-change', 'Api\UserController@checkPasswordChange', ['auth']);
        $this->post('/api/users/{id}/force-password-change', 'Api\UserController@setForcePasswordChange', ['auth', 'admin']);
        $this->get('/api/users/needing-password-change', 'Api\UserController@getUsersNeedingPasswordChange', ['auth', 'admin']);
        
        // OPT API Routes
        $this->get('/api/opt', 'Api\OptController@index', ['auth']);
        $this->get('/api/opt/{id}', 'Api\OptController@show', ['auth']);
        $this->post('/api/opt', 'Api\OptController@store', ['auth', 'admin']);
        $this->put('/api/opt/{id}', 'Api\OptController@update', ['auth', 'admin']);
        $this->delete('/api/opt/{id}', 'Api\OptController@destroy', ['auth', 'admin']);
        $this->post('/api/opt/{id}/toggle-status', 'Api\OptController@toggleStatus', ['auth', 'admin']);
        $this->get('/api/opt/stats', 'Api\OptController@getStats', ['auth']);
        $this->get('/api/opt/search', 'Api\OptController@search', ['auth']);
        $this->get('/api/opt/by-category/{category}', 'Api\OptController@getByCategory', ['auth']);
        $this->get('/api/opt/by-type/{type}', 'Api\OptController@getByType', ['auth']);
    }
    
    /**
     * Handle the current request
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove base path and query string
        $uri = str_replace('/jagapadi', '', $uri);
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/');
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($this->matchRoute($method, $uri, $route)) {
                return $this->executeRoute($route, $uri);
            }
        }
        
        return false; // No API route matched
    }
    
    /**
     * Check if route matches
     */
    private function matchRoute($method, $uri, $route) {
        if ($route['method'] !== $method) {
            return false;
        }
        
        $pattern = $this->convertToRegex($route['path']);
        return preg_match($pattern, $uri);
    }
    
    /**
     * Convert route path to regex pattern
     */
    private function convertToRegex($path) {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Execute the matched route
     */
    private function executeRoute($route, $uri) {
        // Extract parameters
        $params = $this->extractParams($route['path'], $uri);
        
        // Apply middleware
        if (!$this->applyMiddleware($route['middleware'])) {
            return true; // Middleware handled the response
        }
        
        // Parse handler
        list($controller, $method) = explode('@', $route['handler']);
        
        // Handle namespaced controllers
        if (strpos($controller, '\\') !== false) {
            $controllerClass = str_replace('\\', '/', $controller) . 'Controller';
            $controllerFile = ROOT_PATH . '/app/controllers/' . $controllerClass . '.php';
        } else {
            $controllerFile = ROOT_PATH . '/app/controllers/' . $controller . '.php';
        }
        
        if (!file_exists($controllerFile)) {
            $this->sendJsonResponse(['error' => 'Controller not found'], 404);
            return true;
        }
        
        require_once $controllerFile;
        
        // Get the actual class name
        $className = basename($controller) . 'Controller';
        
        if (!class_exists($className)) {
            $this->sendJsonResponse(['error' => 'Controller class not found'], 404);
            return true;
        }
        
        $controllerInstance = new $className();
        
        if (!method_exists($controllerInstance, $method)) {
            $this->sendJsonResponse(['error' => 'Method not found'], 404);
            return true;
        }
        
        // Call the method with parameters
        call_user_func_array([$controllerInstance, $method], $params);
        return true;
    }
    
    /**
     * Extract parameters from URI
     */
    private function extractParams($routePath, $uri) {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));
        
        $params = [];
        foreach ($routeParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $params[$matches[1]] = $uriParts[$index] ?? null;
            }
        }
        
        return array_values($params);
    }
    
    /**
     * Apply middleware
     */
    private function applyMiddleware($middlewares) {
        foreach ($middlewares as $middleware) {
            switch ($middleware) {
                case 'auth':
                    if (!isset($_SESSION['user_id'])) {
                        $this->sendJsonResponse(['error' => 'Unauthorized'], 401);
                        return false;
                    }
                    break;
                    
                case 'admin':
                    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                        $this->sendJsonResponse(['error' => 'Forbidden'], 403);
                        return false;
                    }
                    break;
                    
                case 'operator':
                    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
                        $this->sendJsonResponse(['error' => 'Forbidden'], 403);
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Send JSON response
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}