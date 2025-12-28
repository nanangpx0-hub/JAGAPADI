<?php
/**
 * Migration: Create Tags System
 * 
 * Creates tables for tag management and many-to-many relationship with laporan_hama
 * 
 * Tables:
 * - tags: Master table for tags
 * - laporan_hama_tags: Junction table for many-to-many relationship
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating tags system tables...\n";
    
    // 1. Create tags table
    $sqlTags = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->exec($sqlTags);
    echo "✓ Tags table created successfully\n";
    
    // 2. Create laporan_hama_tags junction table
    $sqlJunction = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->exec($sqlJunction);
    echo "✓ Laporan_hama_tags junction table created successfully\n";
    
    // 3. Insert some default/common tags
    $defaultTags = [
        ['nama_tag' => 'serius', 'deskripsi' => 'Laporan dengan tingkat keparahan berat', 'warna' => '#dc3545'],
        ['nama_tag' => 'urgent', 'deskripsi' => 'Memerlukan penanganan segera', 'warna' => '#fd7e14'],
        ['nama_tag' => 'epidemi', 'deskripsi' => 'Wabah atau epidemi', 'warna' => '#e83e8c'],
        ['nama_tag' => 'musiman', 'deskripsi' => 'Terjadi secara musiman', 'warna' => '#20c997'],
        ['nama_tag' => 'baru', 'deskripsi' => 'Jenis hama baru atau pertama kali dilaporkan', 'warna' => '#6f42c1'],
        ['nama_tag' => 'berulang', 'deskripsi' => 'Laporan berulang di lokasi yang sama', 'warna' => '#ffc107'],
        ['nama_tag' => 'luas', 'deskripsi' => 'Luas serangan besar', 'warna' => '#17a2b8'],
        ['nama_tag' => 'terkendali', 'deskripsi' => 'Situasi sudah terkendali', 'warna' => '#28a745']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO tags (nama_tag, deskripsi, warna) VALUES (?, ?, ?)");
    foreach ($defaultTags as $tag) {
        $stmt->execute([$tag['nama_tag'], $tag['deskripsi'], $tag['warna']]);
    }
    echo "✓ Default tags inserted successfully\n";
    
    // 4. Create trigger to update usage_count
    $db->exec("DROP TRIGGER IF EXISTS update_tag_usage_on_insert");
    $db->exec("
        CREATE TRIGGER update_tag_usage_on_insert
        AFTER INSERT ON laporan_hama_tags
        FOR EACH ROW
        UPDATE tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id
    ");
    
    $db->exec("DROP TRIGGER IF EXISTS update_tag_usage_on_delete");
    $db->exec("
        CREATE TRIGGER update_tag_usage_on_delete
        AFTER DELETE ON laporan_hama_tags
        FOR EACH ROW
        UPDATE tags SET usage_count = GREATEST(usage_count - 1, 0) WHERE id = OLD.tag_id
    ");
    
    echo "✓ Triggers for usage_count updated successfully\n";
    
    echo "\n✓✓✓ Tags system migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

