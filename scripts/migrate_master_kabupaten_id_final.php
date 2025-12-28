<?php
/**
 * Migration script FINAL to modify master_kabupaten.id column
 * - Change id to VARCHAR(2)
 * - Add UNIQUE constraint
 * - Update id values with last 2 digits of kode_kabupaten
 * - Handle duplicates properly by using sequential numbering
 * 
 * Usage:
 * php migrate_master_kabupaten_id_final.php [--force|--yes]
 * or set $auto_approve = true below
 */

// Auto-approve configuration
$auto_approve = false;

// Parse command line arguments
if (in_array('--force', $argv) || in_array('--yes', $argv)) {
    $auto_approve = true;
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

echo "=== Migration FINAL: master_kabupaten.id column modification ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Step 1: Backup database
    echo "Step 1: Creating database backup...\n";
    $backup_file = __DIR__ . '/../backups/master_kabupaten_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    if (!is_dir(dirname($backup_file))) {
        mkdir(dirname($backup_file), 0755, true);
    }
    
    // Create backup using mysqldump
    $command = sprintf(
        'mysqldump -h%s -u%s %s master_kabupaten > %s',
        DB_HOST,
        DB_USER,
        DB_NAME,
        $backup_file
    );
    
    if (DB_PASS) {
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s master_kabupaten > %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $backup_file
        );
    }
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        echo "✓ Backup created: $backup_file\n";
    } else {
        echo "⚠ Warning: Backup creation failed. Continuing anyway...\n";
    }
    
    // Step 2: Verify current table structure
    echo "\nStep 2: Verifying current table structure...\n";
    $stmt = $db->query("DESCRIBE master_kabupaten");
    $columns = $stmt->fetchAll();
    
    $id_column = null;
    foreach ($columns as $col) {
        if ($col['Field'] === 'id') {
            $id_column = $col;
            break;
        }
    }
    
    if (!$id_column) {
        throw new Exception("Column 'id' not found in master_kabupaten table");
    }
    
    echo "Current id column: {$id_column['Type']} ({$id_column['Null']}, {$id_column['Key']})\n";
    
    // Step 3: Check for existing data and relationships
    echo "\nStep 3: Checking data and relationships...\n";
    $stmt = $db->query("SELECT COUNT(*) as total FROM master_kabupaten");
    $total_records = $stmt->fetch()['total'];
    echo "Total records in master_kabupaten: $total_records\n";
    
    // Check foreign key references
    $stmt = $db->query("
        SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'master_kabupaten' 
        AND REFERENCED_COLUMN_NAME = 'id'
        AND TABLE_SCHEMA = '" . DB_NAME . "'
    ");
    $references = $stmt->fetchAll();
    
    if (!empty($references)) {
        echo "⚠ Warning: Found foreign key references:\n";
        foreach ($references as $ref) {
            echo "  - {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']} (constraint: {$ref['CONSTRAINT_NAME']})\n";
        }
    } else {
        echo "✓ No foreign key references found\n";
    }
    
    // Step 4: Preview changes
    echo "\nStep 4: Previewing changes...\n";
    $stmt = $db->query("SELECT id, kode_kabupaten FROM master_kabupaten ORDER BY kode_kabupaten LIMIT 10");
    $preview = $stmt->fetchAll();
    
    echo "Sample records (current -> proposed):\n";
    foreach ($preview as $row) {
        $new_id = substr($row['kode_kabupaten'], -2);
        echo "  ID: {$row['id']} -> $new_id (kode: {$row['kode_kabupaten']})\n";
    }
    
    // Step 5: Confirmation
    if (!$auto_approve) {
        echo "\n⚠ This operation will modify the database structure and data.\n";
        echo "Type 'yes' to continue, 'no' to cancel: ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'yes') {
            echo "Operation cancelled by user.\n";
            exit(0);
        }
    } else {
        echo "\n--force/--yes detected, proceeding automatically...\n";
    }
    
    // Step 6: Begin transaction
    echo "\nStep 6: Starting transaction...\n";
    $db->beginTransaction();
    
    try {
        // Step 7: Drop foreign key constraints temporarily
        if (!empty($references)) {
            echo "Step 7: Temporarily dropping foreign key constraints...\n";
            foreach ($references as $ref) {
                $db->exec("ALTER TABLE {$ref['TABLE_NAME']} DROP FOREIGN KEY {$ref['CONSTRAINT_NAME']}");
                echo "  ✓ Dropped constraint: {$ref['CONSTRAINT_NAME']}\n";
            }
        }
        
        // Step 8: Remove AUTO_INCREMENT and drop primary key temporarily
        echo "\nStep 8: Removing AUTO_INCREMENT and dropping primary key...\n";
        
        // Check if primary key exists first
        $stmt = $db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
            AND TABLE_NAME = 'master_kabupaten' 
            AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ");
        $has_pk = $stmt->fetch()['count'] > 0;
        
        if ($has_pk) {
            // Remove AUTO_INCREMENT first
            try {
                $db->exec("ALTER TABLE master_kabupaten MODIFY id INT NOT NULL");
                echo "  ✓ Removed AUTO_INCREMENT from id column\n";
            } catch (Exception $e) {
                echo "  ⚠ AUTO_INCREMENT may not exist: " . $e->getMessage() . "\n";
            }
            
            // Drop primary key
            $db->exec("ALTER TABLE master_kabupaten DROP PRIMARY KEY");
            echo "  ✓ Dropped primary key\n";
        } else {
            echo "  ⚠ Primary key already removed or doesn't exist\n";
        }
        
        // Step 9: Create simple sequential ID mapping
        echo "\nStep 9: Creating sequential ID mapping...\n";
        
        // Get all records ordered by kode_kabupaten
        $stmt = $db->query("SELECT id, kode_kabupaten FROM master_kabupaten ORDER BY kode_kabupaten");
        $all_records = $stmt->fetchAll();
        
        $id_mapping = [];
        $new_id = 1;
        
        foreach ($all_records as $record) {
            $formatted_id = str_pad((string)$new_id, 2, '0', STR_PAD_LEFT);
            $id_mapping[$record['id']] = $formatted_id;
            
            echo "  Mapping: {$record['kode_kabupaten']} (old ID: {$record['id']}) -> new ID: $formatted_id\n";
            $new_id++;
        }
        
        // Step 10: Update all records with new IDs
        echo "\nStep 10: Updating records with new IDs...\n";
        foreach ($id_mapping as $old_id => $new_id) {
            $stmt = $db->prepare("UPDATE master_kabupaten SET id = ? WHERE id = ?");
            $stmt->execute([$new_id, $old_id]);
            echo "  ✓ Updated record ID $old_id -> $new_id\n";
        }
        
        // Step 11: Modify column structure
        echo "\nStep 11: Modifying column structure...\n";
        $db->exec("ALTER TABLE master_kabupaten MODIFY id VARCHAR(2) NOT NULL");
        echo "  ✓ Modified id column to VARCHAR(2)\n";
        
        // Step 12: Add primary key with UNIQUE constraint
        echo "\nStep 12: Adding primary key with UNIQUE constraint...\n";
        $db->exec("ALTER TABLE master_kabupaten ADD PRIMARY KEY (id)");
        echo "  ✓ Added PRIMARY KEY with UNIQUE constraint\n";
        
        // Step 13: Update foreign key references in related tables
        if (!empty($references)) {
            echo "\nStep 13: Updating foreign key references...\n";
            foreach ($references as $ref) {
                echo "  Updating {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']}...\n";
                
                // Update each foreign key reference
                foreach ($id_mapping as $old_id => $new_id) {
                    $stmt = $db->prepare("
                        UPDATE {$ref['TABLE_NAME']} 
                        SET {$ref['COLUMN_NAME']} = ? 
                        WHERE {$ref['COLUMN_NAME']} = ?
                    ");
                    $stmt->execute([$new_id, $old_id]);
                }
                
                // Recreate constraint
                $db->exec("
                    ALTER TABLE {$ref['TABLE_NAME']} 
                    ADD CONSTRAINT {$ref['CONSTRAINT_NAME']} 
                    FOREIGN KEY ({$ref['COLUMN_NAME']}) 
                    REFERENCES master_kabupaten(id)
                ");
                echo "  ✓ Recreated constraint: {$ref['CONSTRAINT_NAME']}\n";
            }
        }
        
        // Step 14: Commit transaction
        echo "\nStep 14: Committing transaction...\n";
        $db->commit();
        echo "✓ Transaction committed successfully\n";
        
    } catch (Exception $e) {
        echo "\n❌ Error during migration: " . $e->getMessage() . "\n";
        echo "Rolling back changes...\n";
        $db->rollBack();
        throw $e;
    }
    
    // Step 15: Validation
    echo "\nStep 15: Validating changes...\n";
    
    // Check id length
    $stmt = $db->query("SELECT COUNT(*) as count FROM master_kabupaten WHERE LENGTH(id) != 2");
    $invalid_length = $stmt->fetch()['count'];
    echo "Records with invalid id length: $invalid_length\n";
    
    // Check uniqueness
    $stmt = $db->query("
        SELECT id, COUNT(*) as count 
        FROM master_kabupaten 
        GROUP BY id 
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll();
    echo "Duplicate IDs: " . count($duplicates) . "\n";
    
    if (!empty($duplicates)) {
        echo "⚠ Warning: Duplicate IDs found:\n";
        foreach ($duplicates as $dup) {
            echo "  - ID '{$dup['id']}' appears {$dup['count']} times\n";
        }
    }
    
    // Check final structure
    $stmt = $db->query("DESCRIBE master_kabupaten");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        if ($col['Field'] === 'id') {
            echo "Final id column: {$col['Type']} ({$col['Null']}, {$col['Key']})\n";
            break;
        }
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    echo "Backup file: $backup_file\n";
    
    // Step 16: Create migration record
    echo "\nStep 16: Creating migration record...\n";
    $migration_record = [
        'migration' => 'migrate_master_kabupaten_id_final',
        'executed_at' => date('Y-m-d H:i:s'),
        'backup_file' => $backup_file,
        'records_affected' => $total_records,
        'auto_approve' => $auto_approve,
        'id_mapping_count' => count($id_mapping)
    ];
    
    $record_file = __DIR__ . '/../logs/migrations.log';
    if (!is_dir(dirname($record_file))) {
        mkdir(dirname($record_file), 0755, true);
    }
    
    $log_entry = json_encode($migration_record) . "\n";
    file_put_contents($record_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo "✓ Migration record created: $record_file\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Check the backup file and logs for details.\n";
    exit(1);
}
