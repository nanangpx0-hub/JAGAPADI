<?php
/**
 * Final fix for kecamatan kabupaten_id values
 * Convert single-digit IDs to 2-digit format
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Final fix for kecamatan kabupaten_id ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Converting single-digit IDs to 2-digit format...\n";
    
    // Update single digit IDs to 2-digit format
    $updates = [
        '1' => '01', '2' => '02', '3' => '03', '4' => '04', '5' => '05',
        '6' => '06', '7' => '07', '8' => '08', '9' => '09'
    ];
    
    foreach ($updates as $old_id => $new_id) {
        $stmt = $db->prepare("
            UPDATE master_kecamatan 
            SET kabupaten_id = ?
            WHERE kabupaten_id = ?
        ");
        $stmt->execute([$new_id, $old_id]);
        
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "  ✓ Updated $affected records: $old_id -> $new_id\n";
        }
    }
    
    echo "\nStep 2: Verification...\n";
    
    // Check for invalid records
    $stmt = $db->query("
        SELECT COUNT(*) as invalid 
        FROM master_kecamatan mk
        LEFT JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
        WHERE kab.id IS NULL
    ");
    $invalid = $stmt->fetch()['invalid'];
    echo "Invalid kabupaten_id references: $invalid\n";
    
    // Show distribution
    $stmt = $db->query("
        SELECT kabupaten_id, COUNT(*) as count
        FROM master_kecamatan
        GROUP BY kabupaten_id
        ORDER BY kabupaten_id
    ");
    $distribution = $stmt->fetchAll();
    
    echo "\nkabupaten_id distribution:\n";
    foreach ($distribution as $dist) {
        echo "  ID '{$dist['kabupaten_id']}': {$dist['count']} records\n";
    }
    
    echo "\nStep 3: Adding foreign key constraint...\n";
    
    // Try to add constraint again
    try {
        $db->exec("
            ALTER TABLE master_kecamatan 
            ADD CONSTRAINT fk_kecamatan_kabupaten 
            FOREIGN KEY (kabupaten_id) 
            REFERENCES master_kabupaten(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
        ");
        echo "  ✓ Foreign key constraint added successfully\n";
    } catch (Exception $e) {
        echo "  ⚠ Failed to add foreign key constraint: " . $e->getMessage() . "\n";
    }
    
    // Final validation
    $stmt = $db->query("
        SELECT COUNT(*) as valid_refs 
        FROM master_kecamatan mk
        INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
    ");
    $valid_refs = $stmt->fetch()['valid_refs'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kecamatan");
    $total_kec = $stmt->fetch()['total'];
    
    echo "\nFinal validation:\n";
    echo "Valid foreign key references: $valid_refs / $total_kec\n";
    
    if ($valid_refs == $total_kec) {
        echo "✓ All kecamatan records have valid kabupaten references\n";
    } else {
        echo "⚠ Some records still have invalid references\n";
    }
    
    echo "\n=== Final fix completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Final fix failed: " . $e->getMessage() . "\n";
    exit(1);
}
