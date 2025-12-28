<?php
/**
 * API endpoint untuk kecamatan statistics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Security check
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get total statistics
    $stats = [];
    
    // Total kecamatan
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kecamatan");
    $stats['total_kecamatan'] = (int)$stmt->fetch()['total'];
    
    // Unique nama kecamatan
    $stmt = $db->query("SELECT COUNT(DISTINCT nama_kecamatan) as unique_nama FROM master_kecamatan");
    $stats['unique_nama_kecamatan'] = (int)$stmt->fetch()['unique_nama'];
    
    // Calculate duplicate count
    $stats['duplicate_count'] = $stats['total_kecamatan'] - $stats['unique_nama_kecamatan'];
    
    // Duplicate groups
    $stmt = $db->query("
        SELECT COUNT(DISTINCT nama_kecamatan) as duplicate_groups
        FROM (
            SELECT nama_kecamatan 
            FROM master_kecamatan 
            GROUP BY nama_kecamatan 
            HAVING COUNT(*) > 1
        ) as dupes
    ");
    $stats['duplicate_groups'] = (int)$stmt->fetch()['duplicate_groups'];
    
    // Invalid codes
    $stmt = $db->query("
        SELECT COUNT(*) as count FROM master_kecamatan 
        WHERE kode_kecamatan NOT REGEXP '^35[0-9]{4}$'
        OR LENGTH(kode_kecamatan) != 6
    ");
    $stats['invalid_codes'] = (int)$stmt->fetch()['count'];
    
    // Kabupaten with issues
    $stmt = $db->query("
        SELECT COUNT(*) as count FROM master_kabupaten kab
        INNER JOIN master_kecamatan kec ON kab.id = kec.kabupaten_id
        GROUP BY kab.id
        HAVING COUNT(kec.id) != COUNT(DISTINCT kec.nama_kecamatan)
    ");
    $stats['kabupaten_with_issues'] = (int)$stmt->fetch()['count'];
    
    // Calculate data quality score
    $qualityScore = 0;
    if ($stats['total_kecamatan'] > 0) {
        $validData = $stats['total_kecamatan'] - $stats['duplicate_count'] - $stats['invalid_codes'];
        $qualityScore = round(($validData / $stats['total_kecamatan']) * 100, 1);
    }
    $stats['data_quality_score'] = $qualityScore;
    
    // Timestamp
    $stats['timestamp'] = date('Y-m-d H:i:s');
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
