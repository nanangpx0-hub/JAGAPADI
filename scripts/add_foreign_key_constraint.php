<?php
/**
 * Add foreign key constraint between master_kecamatan and master_kabupaten
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Add foreign key constraint ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Checking if constraint already exists...\n";
    $stmt = $db->query("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'master_kecamatan'
        AND COLUMN_NAME = 'kabupaten_id'
        AND REFERENCED_TABLE_NAME = 'master_kabupaten'
        AND REFERENCED_COLUMN_NAME = 'id'
    ");
    $exists = $stmt->fetch()['count'] > 0;
    
    if ($exists) {
        echo "⚠ Foreign key constraint already exists\n";
        exit(0);
    }
    
    echo "Step 2: Adding foreign key constraint...\n";
    $db->exec("
        ALTER TABLE master_kecamatan 
        ADD CONSTRAINT fk_kecamatan_kabupaten 
        FOREIGN KEY (kabupaten_id) 
        REFERENCES master_kabupaten(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
    ");
    echo "✓ Foreign key constraint added successfully\n";
    
    echo "\nStep 3: Verification...\n";
    
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
    
    echo "\n=== Foreign key constraint added successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Failed to add foreign key constraint: " . $e->getMessage() . "\n";
    exit(1);
}
