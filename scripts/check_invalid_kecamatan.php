<?php
/**
 * Check for invalid kecamatan records
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Check invalid kecamatan records ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Finding invalid kabupaten_id values...\n";
    
    $stmt = $db->query("
        SELECT mk.kode_kecamatan, mk.nama_kecamatan, mk.kabupaten_id
        FROM master_kecamatan mk
        LEFT JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
        WHERE kab.id IS NULL
        ORDER BY mk.kode_kecamatan
        LIMIT 20
    ");
    $invalid_records = $stmt->fetchAll();
    
    if (!empty($invalid_records)) {
        echo "Invalid records found:\n";
        foreach ($invalid_records as $record) {
            echo "  {$record['kode_kecamatan']} - {$record['nama_kecamatan']} (Invalid kabupaten_id: {$record['kabupaten_id']})\n";
        }
        
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM master_kecamatan mk
            LEFT JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
            WHERE kab.id IS NULL
        ");
        $total_invalid = $stmt->fetch()['total'];
        echo "\nTotal invalid records: $total_invalid\n";
        
    } else {
        echo "✓ No invalid records found\n";
    }
    
    echo "\nStep 2: Checking kabupaten_id value distribution...\n";
    
    $stmt = $db->query("
        SELECT kabupaten_id, COUNT(*) as count
        FROM master_kecamatan
        GROUP BY kabupaten_id
        ORDER BY kabupaten_id
        LIMIT 20
    ");
    $distribution = $stmt->fetchAll();
    
    echo "kabupaten_id distribution:\n";
    foreach ($distribution as $dist) {
        echo "  ID '{$dist['kabupaten_id']}': {$dist['count']} records\n";
    }
    
    echo "\nStep 3: Valid kabupaten IDs...\n";
    
    $stmt = $db->query("SELECT id, kode_kabupaten, nama_kabupaten FROM master_kabupaten ORDER BY id");
    $valid_kabs = $stmt->fetchAll();
    
    echo "Valid kabupaten IDs:\n";
    foreach ($valid_kabs as $kab) {
        echo "  ID '{$kab['id']}' - {$kab['kode_kabupaten']} - {$kab['nama_kabupaten']}\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Check failed: " . $e->getMessage() . "\n";
    exit(1);
}
