<?php
/**
 * Migration Script: Add foto field to master_opt table
 * 
 * This script checks if the foto field exists in master_opt table.
 * If not, it adds the field with appropriate structure.
 * 
 * Usage: php database/migrations/add_opt_foto_field.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if foto_url field exists
    $stmt = $db->query("DESCRIBE master_opt");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $hasFotoUrl = in_array('foto_url', $columns);
    $hasFoto = in_array('foto', $columns);
    
    echo "Checking master_opt table structure...\n";
    echo "Existing columns: " . implode(', ', $columns) . "\n\n";
    
    if ($hasFotoUrl) {
        echo "✓ Field 'foto_url' already exists. No migration needed.\n";
        
        // Check if we need to add 'foto' field as alias
        if (!$hasFoto) {
            echo "Adding 'foto' field as alternative field name...\n";
            $db->exec("ALTER TABLE master_opt ADD COLUMN foto VARCHAR(255) NULL AFTER foto_url");
            echo "✓ Field 'foto' added successfully.\n";
        } else {
            echo "✓ Field 'foto' also exists.\n";
        }
    } else {
        echo "Field 'foto_url' not found. Adding it...\n";
        $db->exec("ALTER TABLE master_opt ADD COLUMN foto_url VARCHAR(255) NULL DEFAULT NULL AFTER deskripsi");
        echo "✓ Field 'foto_url' added successfully.\n";
        
        // Also add 'foto' field for compatibility
        if (!$hasFoto) {
            echo "Adding 'foto' field as alternative...\n";
            $db->exec("ALTER TABLE master_opt ADD COLUMN foto VARCHAR(255) NULL DEFAULT NULL AFTER foto_url");
            echo "✓ Field 'foto' added successfully.\n";
        }
    }
    
    echo "\n✓ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
