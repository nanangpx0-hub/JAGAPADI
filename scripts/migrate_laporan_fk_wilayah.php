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
    if (!columnExists($db, 'laporan_hama', 'kabupaten_id')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN kabupaten_id INT NULL");
    }
    if (!columnExists($db, 'laporan_hama', 'kecamatan_id')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN kecamatan_id INT NULL");
    }
    if (!columnExists($db, 'laporan_hama', 'desa_id')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN desa_id INT NULL");
    }
    echo "FK columns added\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}