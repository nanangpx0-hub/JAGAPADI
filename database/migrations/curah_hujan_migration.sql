-- ============================================
-- Migrasi Database: Curah Hujan
-- Versi: 1.0.0
-- Tanggal: 27 Desember 2025
-- ============================================

-- Tabel utama untuk menyimpan data curah hujan
CREATE TABLE IF NOT EXISTS `curah_hujan` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanggal` DATE NOT NULL COMMENT 'Tanggal pengukuran',
    `lokasi` VARCHAR(100) DEFAULT 'Jember' COMMENT 'Lokasi pengukuran',
    `kode_wilayah` VARCHAR(20) DEFAULT NULL COMMENT 'Kode wilayah BMKG',
    `curah_hujan` DECIMAL(10,2) NOT NULL COMMENT 'Nilai curah hujan',
    `satuan` VARCHAR(10) DEFAULT 'mm' COMMENT 'Satuan pengukuran',
    `sumber_data` VARCHAR(255) NOT NULL COMMENT 'Sumber data (BMKG/Manual/Simulasi)',
    `keterangan` TEXT DEFAULT NULL COMMENT 'Catatan tambahan',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_tanggal_lokasi` (`tanggal`, `lokasi`),
    INDEX `idx_tanggal` (`tanggal`),
    INDEX `idx_lokasi` (`lokasi`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk logging aktivitas scraping
CREATE TABLE IF NOT EXISTS `curah_hujan_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `action` VARCHAR(50) NOT NULL COMMENT 'Jenis aksi (scrape/manual/import)',
    `status` ENUM('success', 'failed', 'partial') NOT NULL,
    `message` TEXT COMMENT 'Pesan detail',
    `records_processed` INT DEFAULT 0 COMMENT 'Jumlah record diproses',
    `records_success` INT DEFAULT 0 COMMENT 'Jumlah berhasil',
    `records_failed` INT DEFAULT 0 COMMENT 'Jumlah gagal',
    `execution_time` DECIMAL(10,4) DEFAULT NULL COMMENT 'Waktu eksekusi (detik)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data untuk demo
INSERT INTO `curah_hujan` (`tanggal`, `lokasi`, `kode_wilayah`, `curah_hujan`, `satuan`, `sumber_data`, `keterangan`) VALUES
-- Data Januari 2025
('2025-01-01', 'Jember', '35.09', 15.5, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-02', 'Jember', '35.09', 22.3, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-03', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-04', 'Jember', '35.09', 8.7, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-05', 'Jember', '35.09', 45.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-06', 'Jember', '35.09', 12.1, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-01-07', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data Februari 2025
('2025-02-01', 'Jember', '35.09', 28.4, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-02-05', 'Jember', '35.09', 35.6, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-02-10', 'Jember', '35.09', 18.9, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-02-15', 'Jember', '35.09', 42.1, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-02-20', 'Jember', '35.09', 5.3, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data Maret 2025
('2025-03-01', 'Jember', '35.09', 33.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-03-10', 'Jember', '35.09', 25.8, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-03-15', 'Jember', '35.09', 48.5, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-03-20', 'Jember', '35.09', 12.4, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-03-25', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data April 2025
('2025-04-01', 'Jember', '35.09', 18.6, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-04-10', 'Jember', '35.09', 22.3, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-04-15', 'Jember', '35.09', 8.9, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-04-20', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-04-25', 'Jember', '35.09', 5.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data Mei 2025
('2025-05-01', 'Jember', '35.09', 3.1, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-05-10', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-05-15', 'Jember', '35.09', 2.5, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-05-20', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data Juni - November 2025 (Musim Kemarau)
('2025-06-01', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-06-15', 'Jember', '35.09', 1.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-07-01', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-07-15', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-08-01', 'Jember', '35.09', 0.0, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-08-15', 'Jember', '35.09', 0.5, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-09-01', 'Jember', '35.09', 2.3, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-09-15', 'Jember', '35.09', 5.8, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-10-01', 'Jember', '35.09', 12.4, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-10-15', 'Jember', '35.09', 18.9, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-11-01', 'Jember', '35.09', 28.5, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-11-15', 'Jember', '35.09', 35.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
-- Data Desember 2025
('2025-12-01', 'Jember', '35.09', 42.8, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-12-10', 'Jember', '35.09', 55.3, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-12-15', 'Jember', '35.09', 38.6, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-12-20', 'Jember', '35.09', 48.2, 'mm', 'Simulasi', 'Data simulasi untuk demo'),
('2025-12-25', 'Jember', '35.09', 62.1, 'mm', 'Simulasi', 'Data simulasi untuk demo');
