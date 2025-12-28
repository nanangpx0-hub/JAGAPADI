<?php
/**
 * Clean import script for master_kabupaten
 * - Import clean data with proper 2-digit IDs
 * - Set up proper structure
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Clean import master_kabupaten ===\n\n";

// Clean data with proper 2-digit IDs
$kabupaten_data = [
    ['kode_kabupaten' => '3501', 'nama_kabupaten' => 'Kabupaten Pacitan', 'id' => '01'],
    ['kode_kabupaten' => '3502', 'nama_kabupaten' => 'Kabupaten Ponorogo', 'id' => '02'],
    ['kode_kabupaten' => '3503', 'nama_kabupaten' => 'Kabupaten Trenggalek', 'id' => '03'],
    ['kode_kabupaten' => '3504', 'nama_kabupaten' => 'Kabupaten Tulungagung', 'id' => '04'],
    ['kode_kabupaten' => '3505', 'nama_kabupaten' => 'Kabupaten Blitar', 'id' => '05'],
    ['kode_kabupaten' => '3506', 'nama_kabupaten' => 'Kabupaten Kediri', 'id' => '06'],
    ['kode_kabupaten' => '3507', 'nama_kabupaten' => 'Kabupaten Malang', 'id' => '07'],
    ['kode_kabupaten' => '3508', 'nama_kabupaten' => 'Kabupaten Lumajang', 'id' => '08'],
    ['kode_kabupaten' => '3509', 'nama_kabupaten' => 'Kabupaten Jember', 'id' => '09'],
    ['kode_kabupaten' => '3510', 'nama_kabupaten' => 'Kabupaten Banyuwangi', 'id' => '10'],
    ['kode_kabupaten' => '3511', 'nama_kabupaten' => 'Kabupaten Bondowoso', 'id' => '11'],
    ['kode_kabupaten' => '3512', 'nama_kabupaten' => 'Kabupaten Situbondo', 'id' => '12'],
    ['kode_kabupaten' => '3513', 'nama_kabupaten' => 'Kabupaten Probolinggo', 'id' => '13'],
    ['kode_kabupaten' => '3514', 'nama_kabupaten' => 'Kabupaten Pasuruan', 'id' => '14'],
    ['kode_kabupaten' => '3515', 'nama_kabupaten' => 'Kabupaten Sidoarjo', 'id' => '15'],
    ['kode_kabupaten' => '3516', 'nama_kabupaten' => 'Kabupaten Mojokerto', 'id' => '16'],
    ['kode_kabupaten' => '3517', 'nama_kabupaten' => 'Kabupaten Jombang', 'id' => '17'],
    ['kode_kabupaten' => '3518', 'nama_kabupaten' => 'Kabupaten Nganjuk', 'id' => '18'],
    ['kode_kabupaten' => '3519', 'nama_kabupaten' => 'Kabupaten Madiun', 'id' => '19'],
    ['kode_kabupaten' => '3520', 'nama_kabupaten' => 'Kabupaten Magetan', 'id' => '20'],
    ['kode_kabupaten' => '3521', 'nama_kabupaten' => 'Kabupaten Ngawi', 'id' => '21'],
    ['kode_kabupaten' => '3522', 'nama_kabupaten' => 'Kabupaten Bojonegoro', 'id' => '22'],
    ['kode_kabupaten' => '3523', 'nama_kabupaten' => 'Kabupaten Tuban', 'id' => '23'],
    ['kode_kabupaten' => '3524', 'nama_kabupaten' => 'Kabupaten Lamongan', 'id' => '24'],
    ['kode_kabupaten' => '3525', 'nama_kabupaten' => 'Kabupaten Gresik', 'id' => '25'],
    ['kode_kabupaten' => '3526', 'nama_kabupaten' => 'Kabupaten Bangkalan', 'id' => '26'],
    ['kode_kabupaten' => '3527', 'nama_kabupaten' => 'Kabupaten Sampang', 'id' => '27'],
    ['kode_kabupaten' => '3528', 'nama_kabupaten' => 'Kabupaten Pamekasan', 'id' => '28'],
    ['kode_kabupaten' => '3529', 'nama_kabupaten' => 'Kabupaten Sumenep', 'id' => '29'],
    ['kode_kabupaten' => '3571', 'nama_kabupaten' => 'Kota Kediri', 'id' => '30'],
    ['kode_kabupaten' => '3572', 'nama_kabupaten' => 'Kota Blitar', 'id' => '31'],
    ['kode_kabupaten' => '3573', 'nama_kabupaten' => 'Kota Malang', 'id' => '32'],
    ['kode_kabupaten' => '3574', 'nama_kabupaten' => 'Kota Batu', 'id' => '33'],
    ['kode_kabupaten' => '3575', 'nama_kabupaten' => 'Kota Surabaya', 'id' => '34'],
    ['kode_kabupaten' => '3576', 'nama_kabupaten' => 'Kota Mojokerto', 'id' => '35'],
    ['kode_kabupaten' => '3577', 'nama_kabupaten' => 'Kota Madiun', 'id' => '36'],
    ['kode_kabupaten' => '3578', 'nama_kabupaten' => 'Kota Probolinggo', 'id' => '37'],
    ['kode_kabupaten' => '3579', 'nama_kabupaten' => 'Kota Pasuruan', 'id' => '38'],
];

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Modifying table structure...\n";
    
    // Drop primary key if exists
    try {
        $db->exec("ALTER TABLE master_kabupaten DROP PRIMARY KEY");
        echo "  ✓ Dropped primary key\n";
    } catch (Exception $e) {
        echo "  ⚠ Primary key may not exist: " . $e->getMessage() . "\n";
    }
    
    // Modify id column to VARCHAR(2)
    $db->exec("ALTER TABLE master_kabupaten MODIFY id VARCHAR(2) NOT NULL");
    echo "  ✓ Modified id column to VARCHAR(2)\n";
    
    echo "\nStep 2: Importing clean data...\n";
    
    $stmt = $db->prepare("
        INSERT INTO master_kabupaten (id, kode_kabupaten, nama_kabupaten) 
        VALUES (?, ?, ?)
    ");
    
    foreach ($kabupaten_data as $kab) {
        $stmt->execute([$kab['id'], $kab['kode_kabupaten'], $kab['nama_kabupaten']]);
        echo "  ✓ Imported: {$kab['kode_kabupaten']} - {$kab['nama_kabupaten']} (ID: {$kab['id']})\n";
    }
    
    echo "\nStep 3: Adding primary key...\n";
    $db->exec("ALTER TABLE master_kabupaten ADD PRIMARY KEY (id)");
    echo "  ✓ Added primary key with UNIQUE constraint\n";
    
    echo "\nStep 4: Verification...\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kabupaten");
    $total = $stmt->fetch()['total'];
    echo "Total records: $total\n";
    
    $stmt = $db->query("SELECT COUNT(*) as invalid FROM master_kabupaten WHERE LENGTH(id) != 2");
    $invalid = $stmt->fetch()['invalid'];
    echo "Records with invalid ID length: $invalid\n";
    
    $stmt = $db->query("
        SELECT id, COUNT(*) as count 
        FROM master_kabupaten 
        GROUP BY id 
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll();
    echo "Duplicate IDs: " . count($duplicates) . "\n";
    
    echo "\n=== Import completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
