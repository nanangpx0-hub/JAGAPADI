<?php
/**
 * API endpoint untuk autocomplete pencarian desa
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
require_once __DIR__ . '/../app/models/MasterKabupaten.php';

try {
    $rawKabupatenId = isset($_GET['kabupaten_id']) && $_GET['kabupaten_id'] !== '' ? $_GET['kabupaten_id'] : null;
    $kecamatanId = isset($_GET['kecamatan_id']) && $_GET['kecamatan_id'] !== '' ? (int)$_GET['kecamatan_id'] : null;
    $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
    $limit = min(15, max(1, (int)($_GET['limit'] ?? 10)));
    
    if (strlen($search) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    $desaModel = new MasterDesa();
    $kabModel = new MasterKabupaten();
    
    // Resolve kabupaten_id using flexible lookup
    $kabupatenId = null;
    if ($rawKabupatenId !== null) {
        $resolvedKabupaten = $kabModel->findByIdOrKode($rawKabupatenId);
        if ($resolvedKabupaten) {
            // Keep as string to match database ID format (e.g., '09' not 9)
            $kabupatenId = $resolvedKabupaten['id'];
        }
    }
    
    $results = $desaModel->searchForAutocomplete($kabupatenId, $kecamatanId, $search, $limit);
    
    // Format for autocomplete dropdown
    $suggestions = [];
    foreach ($results as $row) {
        $suggestions[] = [
            'id' => $row['id'],
            'value' => $row['nama_desa'],
            'label' => $row['nama_desa'] . ' - ' . ($row['nama_kecamatan'] ?? '') . ', ' . ($row['nama_kabupaten'] ?? ''),
            'kode_desa' => $row['kode_desa'],
            'nama_kecamatan' => $row['nama_kecamatan'],
            'nama_kabupaten' => $row['nama_kabupaten']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $suggestions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan'
    ]);
}
?>
