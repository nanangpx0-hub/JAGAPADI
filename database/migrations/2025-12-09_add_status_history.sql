-- Status History Table for Audit Trail
-- Created: 2025-12-09
-- Purpose: Track status transitions for laporan_hama

CREATE TABLE IF NOT EXISTS laporan_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    laporan_id INT NOT NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20) NOT NULL,
    changed_by INT NOT NULL,
    komentar TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_laporan_id (laporan_id),
    INDEX idx_created (created_at)
);
