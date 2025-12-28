<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Add new columns to laporan_irigasi
    $sql = "ALTER TABLE `laporan_irigasi` 
            ADD COLUMN `no_laporan` VARCHAR(20) UNIQUE AFTER `id`,
            ADD COLUMN `nama_pelapor` VARCHAR(100) AFTER `user_id`,
            ADD COLUMN `jenis_saluran` ENUM('Primer', 'Sekunder', 'Tersier') AFTER `nama_saluran`,
            ADD COLUMN `status_perbaikan` ENUM('Dalam Perbaikan', 'Selesai Diperbaiki', 'Belum Ditangani') AFTER `status`,
            ADD COLUMN `aksi_dilakukan` TEXT AFTER `status_perbaikan`;";

    $db->exec($sql);
    echo "Migration successful: New columns added to laporan_irigasi table.";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration skipped: Columns already exist.";
    } else {
        echo "Migration failed: " . $e->getMessage();
    }
}
