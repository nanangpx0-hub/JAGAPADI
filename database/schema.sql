-- JAGAPADI Database Schema
-- Database: bpsjembe_jagapadi

CREATE DATABASE IF NOT EXISTS bpsjembe_jagapadi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bpsjembe_jagapadi;

-- Table: users
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(32) UNIQUE NOT NULL,
  password VARCHAR(128) NOT NULL,
  role ENUM('admin','operator','viewer') NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  aktif TINYINT(1) DEFAULT 1,
  email VARCHAR(100),
  phone VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: master_opt (Organisme Pengganggu Tumbuhan)
CREATE TABLE master_opt (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode_opt VARCHAR(20) UNIQUE,
  nama_opt VARCHAR(255) NOT NULL,
  jenis ENUM('Hama','Penyakit','Gulma') NOT NULL,
  deskripsi TEXT,
  foto_url VARCHAR(500),
  etl_acuan INT DEFAULT 0,
  rekomendasi TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: laporan_hama
CREATE TABLE laporan_hama (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  master_opt_id INT,
  tanggal DATE NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  tingkat_keparahan ENUM('Ringan','Sedang','Berat') NOT NULL,
  populasi INT DEFAULT 0,
  luas_serangan DECIMAL(10,2) DEFAULT 0,
  foto_url VARCHAR(500),
  status ENUM('Draf','Submitted','Diverifikasi','Ditolak') DEFAULT 'Draf',
  catatan TEXT,
  catatan_verifikasi TEXT,
  verified_by INT,
  verified_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (master_opt_id) REFERENCES master_opt(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: kegiatan_simitra (Sync from Simitra API)
CREATE TABLE kegiatan_simitra (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kegiatan_id VARCHAR(50) UNIQUE NOT NULL,
  nama_kegiatan VARCHAR(255) NOT NULL,
  tanggal_mulai DATE,
  tanggal_selesai DATE,
  status VARCHAR(50),
  pagu_honor DECIMAL(15,2) DEFAULT 0,
  synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: mitra_simitra (Sync from Simitra API)
CREATE TABLE mitra_simitra (
  id INT PRIMARY KEY AUTO_INCREMENT,
  mitra_id VARCHAR(50) UNIQUE NOT NULL,
  nama_mitra VARCHAR(100) NOT NULL,
  nik VARCHAR(20),
  alamat TEXT,
  phone VARCHAR(20),
  email VARCHAR(100),
  status VARCHAR(50),
  synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: honor_pelaporan (Link to Simitra)
CREATE TABLE honor_pelaporan (
  id INT PRIMARY KEY AUTO_INCREMENT,
  laporan_hama_id INT NOT NULL,
  mitra_id INT,
  kegiatan_id INT,
  jumlah_honor DECIMAL(15,2) NOT NULL,
  status ENUM('Pending','Disetujui','Dibayar') DEFAULT 'Pending',
  tanggal_pengajuan DATETIME DEFAULT CURRENT_TIMESTAMP,
  tanggal_disetujui DATETIME,
  catatan TEXT,
  FOREIGN KEY (laporan_hama_id) REFERENCES laporan_hama(id) ON DELETE CASCADE,
  FOREIGN KEY (mitra_id) REFERENCES mitra_simitra(id) ON DELETE SET NULL,
  FOREIGN KEY (kegiatan_id) REFERENCES kegiatan_simitra(id) ON DELETE SET NULL
);

-- Table: notifications
CREATE TABLE notifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info','warning','success','danger') DEFAULT 'info',
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: activity_log
CREATE TABLE activity_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(100) NOT NULL,
  table_name VARCHAR(50),
  record_id INT,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert dummy users
INSERT INTO users (username, password, role, nama_lengkap, email, phone) VALUES
('admin_jagapadi', MD5('admin123'), 'admin', 'Nanang Pamungkas', 'nanangpx@gmail.com', '+6281232303096'),
('operator1', MD5('op1test'), 'operator', 'Siti Aminah', 'aminah@bpsjember.go.id', '+628125678999'),
('viewer1', MD5('vw1test'), 'viewer', 'Agus Suryadi', 'agus@bpsjember.go.id', '+628139998877');

-- Insert dummy master_opt
INSERT INTO master_opt (kode_opt, nama_opt, jenis, deskripsi, foto_url, etl_acuan, rekomendasi) VALUES
('H001','Wereng Coklat','Hama','Hama utama pada padi, menyerang batang dan daun','/img/wereng.jpg', 500, 'Penyemprotan insektisida kontak'),
('H002','Penggerek Batang','Hama','Hama yang merusak batang padi dari dalam','', 300, 'Penggunaan perangkap feromon'),
('H003','Walang Sangit','Hama','Hama yang menyerang bulir padi','', 20, 'Penyemprotan insektisida sistemik'),
('P001','Blast','Penyakit','Penyakit daun dan leher malai, disebabkan jamur','/img/blast.jpg', 0, 'Penggunaan varietas tahan'),
('P002','Hawar Daun Bakteri','Penyakit','Penyakit yang disebabkan bakteri Xanthomonas','', 0, 'Sanitasi lahan dan varietas tahan'),
('G001','Brotowali','Gulma','Gulma rumput liar yang merusak padi','/img/brotowali.jpg', 0, 'Penyiangan manual rutin'),
('G002','Teki','Gulma','Gulma yang sulit dikendalikan','', 0, 'Herbisida selektif');

-- Insert dummy laporan_hama
INSERT INTO laporan_hama (user_id, master_opt_id, tanggal, lokasi, latitude, longitude, tingkat_keparahan, populasi, luas_serangan, foto_url, status) VALUES
(1, 1, '2025-11-01', 'Blok Kedawung, Kec. Rambipuji', -8.174381, 113.701399, 'Berat', 600, 2.5, '/uploads/wereng_kedawung_01.jpg', 'Submitted'),
(2, 4, '2025-11-02', 'Blok Pontang, Kec. Ambulu', -8.175010, 113.700899, 'Sedang', 0, 1.2, '/uploads/blast_pontang_01.jpg', 'Diverifikasi'),
(2, 2, '2025-11-03', 'Blok Sucopangepok, Kec. Jelbuk', -8.180234, 113.705432, 'Ringan', 150, 0.5, '', 'Submitted'),
(1, 3, '2025-11-04', 'Blok Curahnongko, Kec. Tempurejo', -8.165789, 113.698765, 'Sedang', 25, 1.8, '', 'Draf');

-- Insert dummy kegiatan_simitra
INSERT INTO kegiatan_simitra (kegiatan_id, nama_kegiatan, tanggal_mulai, tanggal_selesai, status, pagu_honor) VALUES
('KGT2025001', 'Survei Pertanian November 2025', '2025-11-01', '2025-11-30', 'Aktif', 50000000.00),
('KGT2025002', 'Monitoring Hama Padi Q4 2025', '2025-10-01', '2025-12-31', 'Aktif', 30000000.00);

-- Insert dummy mitra_simitra
INSERT INTO mitra_simitra (mitra_id, nama_mitra, nik, alamat, phone, email, status) VALUES
('MTR001', 'Budi Santoso', '3509012345678901', 'Jl. Mastrip No. 45, Jember', '+6281234567890', 'budi@email.com', 'Aktif'),
('MTR002', 'Siti Rahayu', '3509012345678902', 'Jl. Gajah Mada No. 12, Jember', '+6281234567891', 'siti@email.com', 'Aktif'),
('MTR003', 'Ahmad Dahlan', '3509012345678903', 'Jl. Sudirman No. 78, Jember', '+6281234567892', 'ahmad@email.com', 'Aktif');
