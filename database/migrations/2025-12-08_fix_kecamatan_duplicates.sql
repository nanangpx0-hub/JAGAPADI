-- Backup first (example):
-- mysqldump -u<user> -p --single-transaction --quick --set-gtid-purged=OFF jagapadi > jagapadi_backup_2025-12-08.sql

START TRANSACTION;

-- 1) Identifikasi duplikasi nama/kode (audit saja)
SELECT nama_kecamatan, kabupaten_id, COUNT(*) AS cnt
FROM master_kecamatan
WHERE deleted_at IS NULL
GROUP BY nama_kecamatan, kabupaten_id
HAVING cnt > 1;

SELECT kode_kecamatan, COUNT(*) AS cnt
FROM master_kecamatan
WHERE deleted_at IS NULL AND kode_kecamatan IS NOT NULL
GROUP BY kode_kecamatan
HAVING cnt > 1;

-- 2) Hapus entri salah untuk Ajung (kode BPS tidak valid 350917)
-- Gunakan soft delete agar relasi/riwayat tetap aman
UPDATE master_kecamatan
SET deleted_at = NOW(), deleted_by = 0
WHERE kode_kecamatan = '350917' AND nama_kecamatan = 'Ajung' AND deleted_at IS NULL;

-- 3) Pastikan tidak ada entri tersisa dengan kode salah
DELETE FROM master_kecamatan
WHERE kode_kecamatan = '350917' AND deleted_at IS NOT NULL;

-- 4) Tambahkan constraint unik untuk mencegah duplikasi ke depan
-- Pastikan tabel sudah bersih sebelum menjalankan bagian ini
ALTER TABLE master_kecamatan
    ADD CONSTRAINT uq_kecamatan_kabupaten_nama UNIQUE (kabupaten_id, nama_kecamatan);

-- Opsional: jaga keunikan kode BPS jika belum ada
ALTER TABLE master_kecamatan
    ADD CONSTRAINT uq_kecamatan_kode UNIQUE (kode_kecamatan);

COMMIT;


