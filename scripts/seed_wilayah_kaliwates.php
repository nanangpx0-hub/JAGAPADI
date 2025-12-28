<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$kab = $db->query("SELECT id FROM master_kabupaten WHERE kode_kabupaten='3509'")->fetch();
if(!$kab){ echo "Kabupaten Jember not found\n"; exit(1);} $kabId = $kab['id'];
$kec = $db->query("SELECT id FROM master_kecamatan WHERE kabupaten_id=$kabId AND nama_kecamatan='Kaliwates'")->fetch();
if(!$kec){ echo "Kecamatan Kaliwates not found\n"; exit(1);} $kecId = $kec['id'];
$rows = [
 ['3509191001','Jember Kidul'],
 ['3509191002','Kaliwates'],
 ['3509191003','Kebon Agung'],
 ['3509191004','Kepatihan'],
 ['3509191005','Mangli'],
 ['3509191006','Sempusari'],
 ['3509191007','Tegal Besar']
];
foreach($rows as $r){
    $cek = $db->prepare("SELECT id FROM master_desa WHERE kode_desa=?");
    $cek->execute([$r[0]]);
    if(!$cek->fetch()){
        $stmt = $db->prepare("INSERT INTO master_desa (kecamatan_id, kode_desa, nama_desa) VALUES (?, ?, ?)");
        $stmt->execute([$kecId, $r[0], $r[1]]);
    }
}
echo "Kaliwates villages seeded\n";