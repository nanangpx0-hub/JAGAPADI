-- Create master_kabupaten if not exists (aligned with app expectations)
CREATE TABLE IF NOT EXISTS master_kabupaten (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode_kabupaten VARCHAR(10) NOT NULL UNIQUE,
  nama_kabupaten VARCHAR(100) NOT NULL,
  provinsi VARCHAR(50) NOT NULL DEFAULT 'Jawa Timur',
  tanggal_dibuat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure column exists when table already present
ALTER TABLE master_kabupaten
  ADD COLUMN provinsi VARCHAR(50) NOT NULL DEFAULT 'Jawa Timur';
-- Helpful index for name lookups
ALTER TABLE master_kabupaten
  ADD INDEX idx_nama_kabupaten (nama_kabupaten);

START TRANSACTION;
-- Upsert from kabupaten → master_kabupaten (only Jawa Timur)
INSERT INTO master_kabupaten (kode_kabupaten, nama_kabupaten, provinsi)
SELECT k.kode_kabupaten, k.nama_kabupaten, k.provinsi
FROM kabupaten k
LEFT JOIN master_kabupaten m ON m.kode_kabupaten = k.kode_kabupaten
WHERE k.provinsi = 'Jawa Timur' AND m.id IS NULL;

-- Update names if code already exists (keeping kode as source of truth)
UPDATE master_kabupaten m
JOIN kabupaten k ON k.kode_kabupaten = m.kode_kabupaten
SET m.nama_kabupaten = k.nama_kabupaten,
    m.provinsi = k.provinsi
WHERE k.provinsi = 'Jawa Timur' AND (m.nama_kabupaten <> k.nama_kabupaten OR m.provinsi <> k.provinsi);

COMMIT;

-- Verification
SELECT COUNT(*) AS master_total FROM master_kabupaten;
SELECT COUNT(DISTINCT kode_kabupaten) AS master_unique_codes FROM master_kabupaten;
SELECT COUNT(*) AS master_jatim FROM master_kabupaten WHERE provinsi='Jawa Timur';
SELECT * FROM master_kabupaten ORDER BY nama_kabupaten LIMIT 5;

-- Convenience view for UI/reporting
CREATE OR REPLACE VIEW v_master_kabupaten_jatim AS
SELECT id, kode_kabupaten, nama_kabupaten, provinsi, tanggal_dibuat
FROM master_kabupaten
WHERE provinsi = 'Jawa Timur';
