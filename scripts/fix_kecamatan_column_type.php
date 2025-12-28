<?php
/**
 * Fix kecamatan kabupaten_id column type to match master_kabupaten.id
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Fix kecamatan kabupaten_id column type ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Checking current column types...\n";
    
    // Check master_kabupaten.id type
    $stmt = $db->query("DESCRIBE master_kabupaten");
    $kab_columns = $stmt->fetchAll();
    foreach ($kab_columns as $col) {
        if ($col['Field'] === 'id') {
            echo "master_kabupaten.id: {$col['Type']}\n";
            break;
        }
    }
    
    // Check master_kecamatan.kabupaten_id type
    $stmt = $db->query("DESCRIBE master_kecamatan");
    $kec_columns = $stmt->fetchAll();
    foreach ($kec_columns as $col) {
        if ($col['Field'] === 'kabupaten_id') {
            echo "master_kecamatan.kabupaten_id: {$col['Type']}\n";
            break;
        }
    }
    
    echo "\nStep 2: Modifying master_kecamatan.kabupaten_id type...\n";
    
    // Drop any existing constraints first
    try {
        $db->exec("
            ALTER TABLE master_kecamatan 
            DROP FOREIGN KEY fk_kecamatan_kabupaten
        ");
        echo "  ✓ Dropped existing foreign key constraint\n";
    } catch (Exception $e) {
        echo "  ⚠ No foreign key constraint to drop\n";
    }
    
    // Modify column type
    $db->exec("ALTER TABLE master_kecamatan MODIFY kabupaten_id VARCHAR(2) NOT NULL");
    echo "  ✓ Modified kabupaten_id to VARCHAR(2)\n";
    
    echo "\nStep 3: Adding foreign key constraint...\n";
    $db->exec("
        ALTER TABLE master_kecamatan 
        ADD CONSTRAINT fk_kecamatan_kabupaten 
        FOREIGN KEY (kabupaten_id) 
        REFERENCES master_kabupaten(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
    ");
    echo "  ✓ Added foreign key constraint\n";
    
    echo "\nStep 4: Verification...\n";
    
    // Test constraint
    $stmt = $db->query("
        SELECT COUNT(*) as valid_refs 
        FROM master_kecamatan mk
        INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
    ");
    $valid_refs = $stmt->fetch()['valid_refs'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kecamatan");
    $total_kec = $stmt->fetch()['total'];
    
    echo "Valid foreign key references: $valid_refs / $total_kec\n";
    
    // Show final column types
    echo "\nFinal column types:\n";
    $stmt = $db->query("DESCRIBE master_kabupaten");
    $kab_columns = $stmt->fetchAll();
    foreach ($kab_columns as $col) {
        if ($col['Field'] === 'id') {
            echo "master_kabupaten.id: {$col['Type']}\n";
            break;
        }
    }
    
    $stmt = $db->query("DESCRIBE master_kecamatan");
    $kec_columns = $stmt->fetchAll();
    foreach ($kec_columns as $col) {
        if ($col['Field'] === 'kabupaten_id') {
            echo "master_kecamatan.kabupaten_id: {$col['Type']}\n";
            break;
        }
    }
    
    echo "\n=== Column type fix completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Failed to fix column type: " . $e->getMessage() . "\n";
    exit(1);
}
