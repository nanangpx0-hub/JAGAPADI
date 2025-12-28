<?php
// Script to fix role enum - Browser Friendly
echo "<!DOCTYPE html><html><head><title>Fix Role Enum</title></head><body><pre>";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

try {
    // 1. Check current column type
    echo "Checking 'role' column structure...\n";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "Current Type: " . $column['Type'] . "\n";
        
        // 2. If it's an ENUM and doesn't contain 'petugas', update it
        if (stripos($column['Type'], 'enum') !== false && stripos($column['Type'], "'petugas'") === false) {
            echo "Role 'petugas' not found in ENUM. Updating table...\n";
            
            // Extract existing values
            preg_match("/^enum\((.*)\)$/", $column['Type'], $matches);
            $currentValues = $matches[1];
            
            // Add 'petugas'
            $newDefinition = "ENUM($currentValues, 'petugas')";
            
            $alterSql = "ALTER TABLE users MODIFY COLUMN role $newDefinition DEFAULT 'viewer'";
            echo "Executing: $alterSql\n";
            
            $db->exec($alterSql);
            echo "SUCCESS: Table updated. Role 'petugas' added to ENUM.\n";
        } else {
            echo "No update needed. Column is not ENUM or already contains 'petugas'.\n";
        }
    } else {
        echo "ERROR: Column 'role' not found.\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone. You can now retry creating the user.";
echo "</pre></body></html>";
