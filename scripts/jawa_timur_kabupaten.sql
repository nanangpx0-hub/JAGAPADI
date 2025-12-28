CREATE TABLE IF NOT EXISTS kabupaten (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode_kabupaten VARCHAR(10) NOT NULL UNIQUE,
  nama_kabupaten VARCHAR(100) NOT NULL,
  provinsi VARCHAR(50) NOT NULL DEFAULT 'Jawa Timur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;
INSERT INTO kabupaten (kode_kabupaten, nama_kabupaten) VALUES
('JT-01','Kabupaten Pacitan'),
('JT-02','Kabupaten Ponorogo'),
('JT-03','Kabupaten Trenggalek'),
('JT-04','Kabupaten Tulungagung'),
('JT-05','Kabupaten Blitar'),
('JT-06','Kabupaten Kediri'),
('JT-07','Kabupaten Malang'),
('JT-08','Kabupaten Lumajang'),
('JT-09','Kabupaten Jember'),
('JT-10','Kabupaten Banyuwangi'),
('JT-11','Kabupaten Bondowoso'),
('JT-12','Kabupaten Situbondo'),
('JT-13','Kabupaten Probolinggo'),
('JT-14','Kabupaten Pasuruan'),
('JT-15','Kabupaten Sidoarjo'),
('JT-16','Kabupaten Mojokerto'),
('JT-17','Kabupaten Jombang'),
('JT-18','Kabupaten Nganjuk'),
('JT-19','Kabupaten Madiun'),
('JT-20','Kabupaten Magetan'),
('JT-21','Kabupaten Ngawi'),
('JT-22','Kabupaten Bojonegoro'),
('JT-23','Kabupaten Tuban'),
('JT-24','Kabupaten Lamongan'),
('JT-25','Kabupaten Gresik'),
('JT-26','Kabupaten Bangkalan'),
('JT-27','Kabupaten Sampang'),
('JT-28','Kabupaten Pamekasan'),
('JT-29','Kabupaten Sumenep'),
('JT-30','Kota Kediri'),
('JT-31','Kota Blitar'),
('JT-32','Kota Malang'),
('JT-33','Kota Probolinggo'),
('JT-34','Kota Pasuruan'),
('JT-35','Kota Mojokerto'),
('JT-36','Kota Madiun'),
('JT-37','Kota Surabaya'),
('JT-38','Kota Batu');
COMMIT;

SELECT COUNT(*) AS total FROM kabupaten;
SELECT COUNT(DISTINCT kode_kabupaten) AS unique_codes FROM kabupaten;
SELECT COUNT(*) AS invalid_format FROM kabupaten WHERE kode_kabupaten NOT REGEXP '^JT-[0-9]{2}$';
SELECT COUNT(*) AS null_values FROM kabupaten WHERE kode_kabupaten IS NULL OR nama_kabupaten IS NULL OR provinsi IS NULL;
SELECT COUNT(*) AS jt_total FROM kabupaten WHERE provinsi='Jawa Timur';
SELECT * FROM kabupaten WHERE kode_kabupaten IN ('JT-01','JT-02','JT-03','JT-04','JT-05');
