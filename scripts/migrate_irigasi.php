<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS `laporan_irigasi` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) NOT NULL COMMENT 'ID Pelapor',
        `kabupaten_id` INT(11) NULL DEFAULT NULL,
        `kecamatan_id` INT(11) NULL DEFAULT NULL,
        `desa_id` INT(11) NULL DEFAULT NULL,
        
        -- Data Spesifik Irigasi
        `nama_saluran` VARCHAR(100) NOT NULL,
        `jenis_irigasi` ENUM('Teknis', 'Semi Teknis', 'Sederhana', 'Tadah Hujan') NOT NULL,
        `kondisi_fisik` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL,
        `debit_air` ENUM('Cukup', 'Kurang', 'Kering') NOT NULL,
        `luas_layanan` INT(11) NOT NULL COMMENT 'Dalam Hektar',
        
        -- Data Umum Laporan
        `tanggal` DATE NOT NULL,
        `foto_url` VARCHAR(255) NULL DEFAULT NULL,
        `latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
        `longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
        `catatan` TEXT NULL DEFAULT NULL,
        
        -- Workflow Status
        `status` ENUM('Draf', 'Submitted', 'Diverifikasi', 'Ditolak') DEFAULT 'Draf',
        `verified_by` INT(11) NULL DEFAULT NULL,
        `verified_at` DATETIME NULL DEFAULT NULL,
        `catatan_verifikasi` TEXT NULL DEFAULT NULL,
        
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (`id`),
        KEY `idx_laporan_irigasi_user` (`user_id`),
        KEY `idx_laporan_irigasi_status` (`status`),
        KEY `idx_laporan_irigasi_wilayah` (`kabupaten_id`, `kecamatan_id`, `desa_id`),
        KEY `idx_laporan_irigasi_verified` (`verified_by`),
        
        CONSTRAINT `fk_laporan_irigasi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_laporan_irigasi_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql);
    echo "Migration successful: laporan_irigasi table created.";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
