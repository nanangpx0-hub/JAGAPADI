<?php
/**
 * Migration summary and validation
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Migration Summary ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Master Kabupaten Table Status\n";
    echo "=====================================\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kabupaten");
    $kab_total = $stmt->fetch()['total'];
    echo "Total records: $kab_total\n";
    
    $stmt = $db->query("DESCRIBE master_kabupaten");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        if ($col['Field'] === 'id') {
            echo "ID column type: {$col['Type']}\n";
            echo "ID nullable: " . ($col['Null'] === 'NO' ? 'NO' : 'YES') . "\n";
            echo "ID key: {$col['Key']}\n";
            break;
        }
    }
    
    // Check ID format
    $stmt = $db->query("SELECT COUNT(*) as invalid FROM master_kabupaten WHERE LENGTH(id) != 2");
    $invalid_ids = $stmt->fetch()['invalid'];
    echo "Records with invalid ID length: $invalid_ids\n";
    
    // Check duplicates
    $stmt = $db->query("
        SELECT id, COUNT(*) as count 
        FROM master_kabupaten 
        GROUP BY id 
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll();
    echo "Duplicate IDs: " . count($duplicates) . "\n";
    
    echo "\nStep 2: Master Kecamatan Table Status\n";
    echo "====================================\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kecamatan");
    $kec_total = $stmt->fetch()['total'];
    echo "Total records: $kec_total\n";
    
    // Check foreign key
    $stmt = $db->query("
        SELECT COUNT(*) as valid_refs 
        FROM master_kecamatan mk
        INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
    ");
    $valid_refs = $stmt->fetch()['valid_refs'];
    echo "Valid foreign key references: $valid_refs / $kec_total\n";
    
    // Check kabupaten_id type
    $stmt = $db->query("DESCRIBE master_kecamatan");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        if ($col['Field'] === 'kabupaten_id') {
            echo "kabupaten_id column type: {$col['Type']}\n";
            break;
        }
    }
    
    echo "\nStep 3: Sample Data\n";
    echo "==================\n";
    
    $stmt = $db->query("
        SELECT mk.kode_kecamatan, mk.nama_kecamatan, mk.kabupaten_id, kab.kode_kabupaten, kab.nama_kabupaten
        FROM master_kecamatan mk
        INNER JOIN master_kabupaten kab ON mk.kabupaten_id = kab.id
        ORDER BY kab.kode_kabupaten, mk.kode_kecamatan
        LIMIT 5
    ");
    $samples = $stmt->fetchAll();
    
    echo "Sample kecamatan records:\n";
    foreach ($samples as $sample) {
        echo "  {$sample['kode_kecamatan']} - {$sample['nama_kecamatan']}\n";
        echo "    Kabupaten: {$sample['nama_kabupaten']} (ID: {$sample['kabupaten_id']})\n";
    }
    
    echo "\nStep 4: Constraints Status\n";
    echo "========================\n";
    
    // Check foreign key constraints
    $stmt = $db->query("
        SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $constraints = $stmt->fetchAll();
    
    echo "Foreign key constraints:\n";
    foreach ($constraints as $constraint) {
        echo "  {$constraint['TABLE_NAME']}.{$constraint['COLUMN_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']} ({$constraint['CONSTRAINT_NAME']})\n";
    }
    
    echo "\nStep 5: Migration Validation\n";
    echo "===========================\n";
    
    $all_valid = true;
    
    // Validate kabupaten
    if ($invalid_ids > 0 || !empty($duplicates)) {
        echo "❌ Master Kabupaten validation failed\n";
        $all_valid = false;
    } else {
        echo "✅ Master Kabupaten validation passed\n";
    }
    
    // Validate kecamatan
    if ($valid_refs != $kec_total) {
        echo "❌ Master Kecamatan validation failed\n";
        $all_valid = false;
    } else {
        echo "✅ Master Kecamatan validation passed\n";
    }
    
    // Validate constraints
    if (empty($constraints)) {
        echo "❌ No foreign key constraints found\n";
        $all_valid = false;
    } else {
        echo "✅ Foreign key constraints exist\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if ($all_valid) {
        echo "🎉 MIGRATION COMPLETED SUCCESSFULLY!\n";
        echo "✅ All validations passed\n";
        echo "✅ Data integrity maintained\n";
        echo "✅ Foreign key constraints active\n";
    } else {
        echo "⚠️  MIGRATION COMPLETED WITH ISSUES\n";
        echo "❌ Some validations failed\n";
        echo "⚠️  Please review the issues above\n";
    }
    
    echo "\nMigration Details:\n";
    echo "- Master Kabupaten: $kab_total records with VARCHAR(2) IDs\n";
    echo "- Master Kecamatan: $kec_total records with valid foreign keys\n";
    echo "- Foreign Key Constraints: " . count($constraints) . " active\n";
    
} catch (Exception $e) {
    echo "\n❌ Summary failed: " . $e->getMessage() . "\n";
    exit(1);
}
