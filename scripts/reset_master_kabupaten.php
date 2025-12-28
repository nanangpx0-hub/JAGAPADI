<?php
/**
 * Reset script to clean up master_kabupaten table
 * - Remove all data
 * - Reset auto-increment
 * - Prepare for fresh import
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Reset master_kabupaten table ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check foreign key constraints
    $stmt = $db->query("
        SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'master_kabupaten' 
        AND REFERENCED_COLUMN_NAME = 'id'
        AND TABLE_SCHEMA = '" . DB_NAME . "'
    ");
    $references = $stmt->fetchAll();
    
    if (!empty($references)) {
        echo "Found foreign key references:\n";
        foreach ($references as $ref) {
            echo "  - {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']} (constraint: {$ref['CONSTRAINT_NAME']})\n";
        }
        
        echo "\nDropping foreign key constraints...\n";
        foreach ($references as $ref) {
            $db->exec("ALTER TABLE {$ref['TABLE_NAME']} DROP FOREIGN KEY {$ref['CONSTRAINT_NAME']}");
            echo "  ✓ Dropped: {$ref['CONSTRAINT_NAME']}\n";
        }
    }
    
    // Truncate table
    echo "\nTruncating master_kabupaten table...\n";
    $db->exec("TRUNCATE TABLE master_kabupaten");
    echo "✓ Table truncated\n";
    
    // Reset auto-increment
    echo "Resetting auto-increment...\n";
    $db->exec("ALTER TABLE master_kabupaten AUTO_INCREMENT = 1");
    echo "✓ Auto-increment reset\n";
    
    echo "\n=== Reset completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Reset failed: " . $e->getMessage() . "\n";
    exit(1);
}
