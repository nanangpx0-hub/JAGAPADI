<?php
/**
 * Update kecamatan foreign keys to match new kabupaten IDs
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Update kecamatan foreign keys ===\n\n";

// Mapping old kabupaten IDs to new 2-digit IDs
$id_mapping = [
    '3501' => '01', '3502' => '02', '3503' => '03', '3504' => '04', '3505' => '05',
    '3506' => '06', '3507' => '07', '3508' => '08', '3509' => '09', '3510' => '10',
    '3511' => '11', '3512' => '12', '3513' => '13', '3514' => '14', '3515' => '15',
    '3516' => '16', '3517' => '17', '3518' => '18', '3519' => '19', '3520' => '20',
    '3521' => '21', '3522' => '22', '3523' => '23', '3524' => '24', '3525' => '25',
    '3526' => '26', '3527' => '27', '3528' => '28', '3529' => '29', '3571' => '30',
    '3572' => '31', '3573' => '32', '3574' => '33', '3575' => '34', '3576' => '35',
    '3577' => '36', '3578' => '37', '3579' => '38'
];

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Checking current kecamatan records...\n";
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kecamatan");
    $total_kecamatan = $stmt->fetch()['total'];
    echo "Total kecamatan records: $total_kecamatan\n";
    
    echo "\nStep 2: Updating kabupaten_id in master_kecamatan...\n";
    
    foreach ($id_mapping as $kode_kabupaten => $new_id) {
        $stmt = $db->prepare("
            UPDATE master_kecamatan mk
            INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
            SET mk.kabupaten_id = ?
            WHERE kab.kode_kabupaten = ?
        ");
        $stmt->execute([$new_id, $kode_kabupaten]);
        
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "  ✓ Updated $affected records for kabupaten $kode_kabupaten -> ID $new_id\n";
        }
    }
    
    echo "\nStep 3: Verification...\n";
    
    // Check for invalid kabupaten_id references
    $stmt = $db->query("
        SELECT COUNT(*) as invalid 
        FROM master_kecamatan mk
        LEFT JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
        WHERE kab.id IS NULL
    ");
    $invalid = $stmt->fetch()['invalid'];
    echo "Invalid kabupaten_id references: $invalid\n";
    
    // Show sample records
    $stmt = $db->query("
        SELECT mk.kode_kecamatan, mk.nama_kecamatan, mk.kabupaten_id, kab.kode_kabupaten, kab.nama_kabupaten
        FROM master_kecamatan mk
        INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
        ORDER BY kab.kode_kabupaten, mk.kode_kecamatan
        LIMIT 10
    ");
    $samples = $stmt->fetchAll();
    
    echo "\nSample records:\n";
    foreach ($samples as $sample) {
        echo "  {$sample['kode_kecamatan']} - {$sample['nama_kecamatan']} (Kab ID: {$sample['kabupaten_id']} - {$sample['nama_kabupaten']})\n";
    }
    
    echo "\n=== Update completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Update failed: " . $e->getMessage() . "\n";
    exit(1);
}
