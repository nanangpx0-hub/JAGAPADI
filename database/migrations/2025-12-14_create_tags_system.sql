-- Migration: Create Tags System
-- Date: 2025-12-14
-- Description: Creates tables for tag management and many-to-many relationship with laporan_hama

-- 1. Create tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_tag VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT,
    warna VARCHAR(7) DEFAULT '#007bff',
    usage_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama_tag (nama_tag),
    INDEX idx_usage_count (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create laporan_hama_tags junction table
CREATE TABLE IF NOT EXISTS laporan_hama_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    laporan_hama_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_laporan_tag (laporan_hama_id, tag_id),
    FOREIGN KEY (laporan_hama_id) REFERENCES laporan_hama(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_laporan_hama_id (laporan_hama_id),
    INDEX idx_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insert default/common tags
INSERT IGNORE INTO tags (nama_tag, deskripsi, warna) VALUES
('serius', 'Laporan dengan tingkat keparahan berat', '#dc3545'),
('urgent', 'Memerlukan penanganan segera', '#fd7e14'),
('epidemi', 'Wabah atau epidemi', '#e83e8c'),
('musiman', 'Terjadi secara musiman', '#20c997'),
('baru', 'Jenis hama baru atau pertama kali dilaporkan', '#6f42c1'),
('berulang', 'Laporan berulang di lokasi yang sama', '#ffc107'),
('luas', 'Luas serangan besar', '#17a2b8'),
('terkendali', 'Situasi sudah terkendali', '#28a745');

-- 4. Create trigger to update usage_count on insert
DROP TRIGGER IF EXISTS update_tag_usage_on_insert;
DELIMITER $$
CREATE TRIGGER update_tag_usage_on_insert
AFTER INSERT ON laporan_hama_tags
FOR EACH ROW
BEGIN
    UPDATE tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END$$
DELIMITER ;

-- 5. Create trigger to update usage_count on delete
DROP TRIGGER IF EXISTS update_tag_usage_on_delete;
DELIMITER $$
CREATE TRIGGER update_tag_usage_on_delete
AFTER DELETE ON laporan_hama_tags
FOR EACH ROW
BEGIN
    UPDATE tags SET usage_count = GREATEST(usage_count - 1, 0) WHERE id = OLD.tag_id;
END$$
DELIMITER ;

