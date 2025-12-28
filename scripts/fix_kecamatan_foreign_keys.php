<?php
/**
 * Fix kecamatan foreign keys using kode_kecamatan pattern matching
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Fix kecamatan foreign keys ===\n\n";

// Mapping based on first 4 digits of kode_kecamatan
$kabupaten_mapping = [
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
    
    echo "\nStep 2: Updating kabupaten_id based on kode_kecamatan...\n";
    
    foreach ($kabupaten_mapping as $kode_prefix => $new_id) {
        $stmt = $db->prepare("
            UPDATE master_kecamatan 
            SET kabupaten_id = ?
            WHERE kode_kecamatan LIKE ?
        ");
        $stmt->execute([$new_id, $kode_prefix . '%']);
        
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "  ✓ Updated $affected records for kode prefix $kode_prefix -> ID $new_id\n";
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
    
    // Show summary by kabupaten
    $stmt = $db->query("
        SELECT kab.kode_kabupaten, kab.nama_kabupaten, COUNT(mk.id) as kecamatan_count
        FROM master_kabupaten kab
        LEFT JOIN master_kecamatan mk ON kab.id = mk.kabupaten_id
        GROUP BY kab.id, kab.kode_kabupaten, kab.nama_kabupaten
        ORDER BY kab.kode_kabupaten
    ");
    $summary = $stmt->fetchAll();
    
    echo "\nSummary by kabupaten:\n";
    foreach ($summary as $row) {
        echo "  {$row['kode_kabupaten']} - {$row['nama_kabupaten']}: {$row['kecamatan_count']} kecamatan\n";
    }
    
    echo "\n=== Fix completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Fix failed: " . $e->getMessage() . "\n";
    exit(1);
}
