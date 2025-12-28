<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
try {
    $db->exec("CREATE TABLE IF NOT EXISTS master_kabupaten (id INT PRIMARY KEY AUTO_INCREMENT, kode_kabupaten VARCHAR(10) UNIQUE, nama_kabupaten VARCHAR(100) NOT NULL)");
    $db->exec("CREATE TABLE IF NOT EXISTS master_kecamatan (id INT PRIMARY KEY AUTO_INCREMENT, kabupaten_id INT NOT NULL, kode_kecamatan VARCHAR(10) UNIQUE, nama_kecamatan VARCHAR(100) NOT NULL, FOREIGN KEY (kabupaten_id) REFERENCES master_kabupaten(id) ON DELETE CASCADE)");
    $db->exec("CREATE TABLE IF NOT EXISTS master_desa (id INT PRIMARY KEY AUTO_INCREMENT, kecamatan_id INT NOT NULL, kode_desa VARCHAR(15) UNIQUE, nama_desa VARCHAR(100) NOT NULL, FOREIGN KEY (kecamatan_id) REFERENCES master_kecamatan(id) ON DELETE CASCADE)");
    $count = $db->query("SELECT COUNT(*) c FROM master_kabupaten")->fetch()['c'] ?? 0;
    if ($count == 0) {
        $db->exec("INSERT INTO master_kabupaten (kode_kabupaten, nama_kabupaten) VALUES ('3509','Jember'),('3508','Lumajang'),('3510','Bondowoso'),('3511','Banyuwangi'),('3507','Probolinggo'),('3512','Situbondo')");
        $kabJemberId = $db->query("SELECT id FROM master_kabupaten WHERE kode_kabupaten='3509'")->fetch()['id'];
        $db->exec("INSERT INTO master_kecamatan (kabupaten_id, kode_kecamatan, nama_kecamatan) VALUES 
            ($kabJemberId,'350917','Ajung'),($kabJemberId,'350912','Ambulu'),($kabJemberId,'350922','Arjasa'),($kabJemberId,'350910','Balung'),
            ($kabJemberId,'350909','Bangsalsari'),($kabJemberId,'350904','Gumukmas'),($kabJemberId,'350925','Jelbuk'),($kabJemberId,'350916','Jenggawah'),
            ($kabJemberId,'350901','Jombang'),($kabJemberId,'350927','Kalisat'),($kabJemberId,'350919','Kaliwates'),($kabJemberId,'350902','Kencong')");
        $kecId = $db->query("SELECT id FROM master_kecamatan WHERE nama_kecamatan='Ambulu' AND kabupaten_id=$kabJemberId")->fetch()['id'];
        $db->exec("INSERT INTO master_desa (kecamatan_id, kode_desa, nama_desa) VALUES 
            ($kecId,'3509122001','Ambulu'),($kecId,'3509122002','Andongsari'),($kecId,'3509122003','Karang Anyar'),($kecId,'3509122004','Pontang'),($kecId,'3509122005','Sabrang'),($kecId,'3509122006','Sumberejo'),($kecId,'3509122007','Tegalsari')");
    }
    echo "Wilayah master migrated\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}