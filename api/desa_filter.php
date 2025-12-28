<?php
/**
 * API endpoint untuk filter dan pencarian desa
 * Supports AJAX filtering dengan kabupaten > kecamatan > search hierarchy
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Request-ID');

// Security check
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/MasterDesa.php';
require_once __DIR__ . '/../app/models/MasterKecamatan.php';
require_once __DIR__ . '/../app/models/MasterKabupaten.php';

try {
    // Get request parameters
    $rawKabupatenId = isset($_GET['kabupaten_id']) && $_GET['kabupaten_id'] !== '' ? $_GET['kabupaten_id'] : null;
    $kecamatanId = isset($_GET['kecamatan_id']) && $_GET['kecamatan_id'] !== '' ? (int)$_GET['kecamatan_id'] : null;
    $search = trim($_GET['search'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $requestId = $_GET['request_id'] ?? null; // For race condition handling
    
    $desaModel = new MasterDesa();
    $kecModel = new MasterKecamatan();
    $kabModel = new MasterKabupaten();
    
    // Resolve kabupaten_id using flexible lookup (supports ID, BPS kode, or short kode)
    $kabupatenId = null;
    if ($rawKabupatenId !== null) {
        $resolvedKabupaten = $kabModel->findByIdOrKode($rawKabupatenId);
        if ($resolvedKabupaten) {
            // Keep as string to match database ID format (e.g., '09' not 9)
            $kabupatenId = $resolvedKabupaten['id'];
        }
    }
    
    // Validate kecamatan belongs to kabupaten if both are set
    if ($kabupatenId && $kecamatanId) {
        if (!$desaModel->validateKecamatanInKabupaten($kecamatanId, $kabupatenId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Kecamatan tidak termasuk dalam kabupaten yang dipilih',
                'request_id' => $requestId
            ]);
            exit;
        }
    }
    
    // Get desa data with filters
    $desa = $desaModel->getAllWithHierarchyAndKabupaten($kabupatenId, $kecamatanId, $search, $limit, $offset);
    $total = $desaModel->countWithKabupaten($kabupatenId, $kecamatanId, $search);
    
    // Add search highlight markers if search term is provided
    if ($search) {
        foreach ($desa as &$row) {
            $row['nama_desa_highlighted'] = preg_replace(
                '/(' . preg_quote($search, '/') . ')/i',
                '<mark class="search-highlight">$1</mark>',
                htmlspecialchars($row['nama_desa'] ?? '')
            );
            $row['kode_desa_highlighted'] = preg_replace(
                '/(' . preg_quote($search, '/') . ')/i',
                '<mark class="search-highlight">$1</mark>',
                htmlspecialchars($row['kode_desa'] ?? '')
            );
        }
        unset($row);
    }
    
    // Calculate pagination info
    $totalPages = ceil($total / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $desa,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ],
        'filters' => [
            'kabupaten_id' => $kabupatenId,
            'kecamatan_id' => $kecamatanId,
            'search' => $search
        ],
        'request_id' => $requestId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan saat memuat data',
        'debug' => $e->getMessage() // Remove in production
    ]);
}
?>
