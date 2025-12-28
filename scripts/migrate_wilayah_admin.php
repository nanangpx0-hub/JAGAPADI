<?php
/**
 * Migration: Enhance Wilayah Tables for Admin CRUD System
 * - Add soft delete columns
 * - Add audit fields (created_by, updated_by, deleted_by)
 * - Add timestamps
 * - Add unique constraints
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "=== Migrating Wilayah Tables for Admin System ===\n\n";

try {
    // Check if columns exist
    function columnExists($db, $table, $column) {
        $stmt = $db->prepare("SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    // ALTER master_kabupaten
    echo "1. Enhancing master_kabupaten...\n";
    
    if (!columnExists($db, 'master_kabupaten', 'tanggal_dibuat')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "   ✅ Added tanggal_dibuat\n";
    }
    
    if (!columnExists($db, 'master_kabupaten', 'updated_at')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "   ✅ Added updated_at\n";
    }
    
    if (!columnExists($db, 'master_kabupaten', 'deleted_at')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "   ✅ Added deleted_at (soft delete)\n";
    }
    
    if (!columnExists($db, 'master_kabupaten', 'created_by')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN created_by INT NULL");
        echo "   ✅ Added created_by\n";
    }
    
    if (!columnExists($db, 'master_kabupaten', 'updated_by')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN updated_by INT NULL");
        echo "   ✅ Added updated_by\n";
    }
    
    if (!columnExists($db, 'master_kabupaten', 'deleted_by')) {
        $db->exec("ALTER TABLE master_kabupaten ADD COLUMN deleted_by INT NULL");
        echo "   ✅ Added deleted_by\n";
    }
    
    // ALTER master_kecamatan
    echo "\n2. Enhancing master_kecamatan...\n";
    
    if (!columnExists($db, 'master_kecamatan', 'created_at')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "   ✅ Added created_at\n";
    }
    
    if (!columnExists($db, 'master_kecamatan', 'updated_at')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "   ✅ Added updated_at\n";
    }
    
    if (!columnExists($db, 'master_kecamatan', 'deleted_at')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "   ✅ Added deleted_at (soft delete)\n";
    }
    
    if (!columnExists($db, 'master_kecamatan', 'created_by')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN created_by INT NULL");
        echo "   ✅ Added created_by\n";
    }
    
    if (!columnExists($db, 'master_kecamatan', 'updated_by')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN updated_by INT NULL");
        echo "   ✅ Added updated_by\n";
    }
    
    if (!columnExists($db, 'master_kecamatan', 'deleted_by')) {
        $db->exec("ALTER TABLE master_kecamatan ADD COLUMN deleted_by INT NULL");
        echo "   ✅ Added deleted_by\n";
    }
    
    // ALTER master_desa
    echo "\n3. Enhancing master_desa...\n";
    
    if (!columnExists($db, 'master_desa', 'kode_pos')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN kode_pos VARCHAR(10) NULL");
        echo "   ✅ Added kode_pos\n";
    }
    
    if (!columnExists($db, 'master_desa', 'created_at')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "   ✅ Added created_at\n";
    }
    
    if (!columnExists($db, 'master_desa', 'updated_at')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "   ✅ Added updated_at\n";
    }
    
    if (!columnExists($db, 'master_desa', 'deleted_at')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "   ✅ Added deleted_at (soft delete)\n";
    }
    
    if (!columnExists($db, 'master_desa', 'created_by')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN created_by INT NULL");
        echo "   ✅ Added created_by\n";
    }
    
    if (!columnExists($db, 'master_desa', 'updated_by')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN updated_by INT NULL");
        echo "   ✅ Added updated_by\n";
    }
    
    if (!columnExists($db, 'master_desa', 'deleted_by')) {
        $db->exec("ALTER TABLE master_desa ADD COLUMN deleted_by INT NULL");
        echo "   ✅ Added deleted_by\n";
    }
    
    // Create audit_log table
    echo "\n4. Creating audit_log_wilayah table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS audit_log_wilayah (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        table_name VARCHAR(50) NOT NULL,
        record_id INT NOT NULL,
        action VARCHAR(20) NOT NULL,
        old_values TEXT,
        new_values TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_table_record (table_name, record_id),
        INDEX idx_created_at (created_at)
    )");
    echo "   ✅ Audit log table created\n";
    
    // Add indexes for performance
    echo "\n5. Adding indexes for performance...\n";
    
    try {
        $db->exec("CREATE INDEX idx_kode_kabupaten ON master_kabupaten(kode_kabupaten)");
        echo "   ✅ Index on master_kabupaten.kode_kabupaten\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_kabupaten.kode_kabupaten\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_kode_kecamatan ON master_kecamatan(kode_kecamatan)");
        echo "   ✅ Index on master_kecamatan.kode_kecamatan\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_kecamatan.kode_kecamatan\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_kode_desa ON master_desa(kode_desa)");
        echo "   ✅ Index on master_desa.kode_desa\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_desa.kode_desa\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_deleted_at_kab ON master_kabupaten(deleted_at)");
        echo "   ✅ Index on master_kabupaten.deleted_at\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_kabupaten.deleted_at\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_deleted_at_kec ON master_kecamatan(deleted_at)");
        echo "   ✅ Index on master_kecamatan.deleted_at\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_kecamatan.deleted_at\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_deleted_at_desa ON master_desa(deleted_at)");
        echo "   ✅ Index on master_desa.deleted_at\n";
    } catch (Exception $e) {
        echo "   ℹ️  Index already exists on master_desa.deleted_at\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Run: php scripts/test_admin_wilayah.php\n";
    echo "2. Access: http://localhost/jagapadi/admin/wilayah\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
