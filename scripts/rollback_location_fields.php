<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

function columnExists($db, $table, $column) {
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([DB_NAME, $table, $column]);
    return ($stmt->fetch()['c'] ?? 0) > 0;
}

try {
    if (columnExists($db, 'laporan_hama', 'kecamatan')) {
        $db->exec("ALTER TABLE laporan_hama DROP COLUMN kecamatan");
    }
    if (columnExists($db, 'laporan_hama', 'desa')) {
        $db->exec("ALTER TABLE laporan_hama DROP COLUMN desa");
    }
    if (columnExists($db, 'laporan_hama', 'alamat_lengkap')) {
        $db->exec("ALTER TABLE laporan_hama DROP COLUMN alamat_lengkap");
    }
    echo "Rollback done\n";
} catch (Exception $e) {
    echo "Rollback failed: " . $e->getMessage() . "\n";
    exit(1);
}