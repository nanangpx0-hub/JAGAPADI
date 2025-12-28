<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/MasterOpt.php';
require_once __DIR__ . '/../app/models/LaporanHama.php';

$db = Database::getInstance()->getConnection();
$optModel = new MasterOpt();
$laporanModel = new LaporanHama();

$file = __DIR__ . '/../data/laporan_hama_dummy_100.csv';
if (!file_exists($file)) { echo "File CSV tidak ditemukan\n"; exit(1); }

$fh = fopen($file, 'r');
if (!$fh) { echo "Gagal membuka file CSV\n"; exit(1); }

$header = fgetcsv($fh);
$inserted = 0; $skipped = 0; $createdOpt = 0;

function mapStatus($s) {
    $s = strtolower(trim($s));
    if ($s === 'selesai') return 'Diverifikasi';
    if ($s === 'dalam proses') return 'Submitted';
    if ($s === 'belum ditangani') return 'Submitted';
    return 'Draf';
}

function mapSeverity($s) {
    $s = strtolower(trim($s));
    if ($s === 'ringan') return 'Ringan';
    if ($s === 'sedang') return 'Sedang';
    if ($s === 'berat') return 'Berat';
    return 'Ringan';
}

function ensureOpt($optModel, $nama) {
    $stmt = $optModel->query("SELECT id FROM master_opt WHERE nama_opt = ?", [$nama]);
    if (!empty($stmt)) return $stmt[0]['id'];
    $id = $optModel->create([
        'kode_opt' => null,
        'nama_opt' => $nama,
        'jenis' => 'Hama',
        'deskripsi' => '',
        'etl_acuan' => 0,
        'rekomendasi' => ''
    ]);
    return (int)$id;
}

function mapJenisHamaToOptId($optModel, $jenis) {
    $j = strtolower(trim($jenis));
    if ($j === 'wereng') return ensureOpt($optModel, 'Wereng Coklat');
    if ($j === 'penggerek batang') return ensureOpt($optModel, 'Penggerek Batang');
    if ($j === 'blas padi' || $j === 'blast') return ensureOpt($optModel, 'Blast');
    if ($j === 'keong mas') return ensureOpt($optModel, 'Keong Mas');
    if ($j === 'ulat grayak') return ensureOpt($optModel, 'Ulat Grayak');
    if ($j === 'tikus') return ensureOpt($optModel, 'Tikus Sawah');
    return ensureOpt($optModel, ucfirst($j));
}

function derivePopulasiLuas($severity, $rowIndex) {
    if ($severity === 'Ringan') {
        $pop = 20 + ($rowIndex % 130);
        $luas = 0.2 + (($rowIndex % 80) / 100);
    } elseif ($severity === 'Sedang') {
        $pop = 150 + ($rowIndex % 250);
        $luas = 0.5 + (($rowIndex % 150) / 100);
    } else {
        $pop = 400 + ($rowIndex % 400);
        $luas = 1.0 + (($rowIndex % 400) / 100);
    }
    return [min($pop, 800), round(min($luas, 5.0), 2)];
}

$userId = 2;
$rowIndex = 0;
while (($row = fgetcsv($fh)) !== false) {
    $rowIndex++;
    if (count($row) < 10) { $skipped++; continue; }
    $namaPelapor = $row[0];
    $kecamatan = $row[1];
    $desa = $row[2];
    $lat = $row[3];
    $lng = $row[4];
    $jenisHama = $row[5];
    $tanggal = substr($row[6], 0, 10);
    $severity = mapSeverity($row[7]);
    $foto = $row[8];
    $status = mapStatus($row[9]);

    $optId = mapJenisHamaToOptId($optModel, $jenisHama);
    $lokasi = 'Desa ' . $desa . ', Kec. ' . $kecamatan;
    list($populasi, $luas) = derivePopulasiLuas($severity, $rowIndex);

    try {
        $laporanModel->create([
            'user_id' => $userId,
            'master_opt_id' => $optId,
            'tanggal' => $tanggal,
            'lokasi' => $lokasi,
            'latitude' => $lat,
            'longitude' => $lng,
            'tingkat_keparahan' => $severity,
            'populasi' => $populasi,
            'luas_serangan' => $luas,
            'foto_url' => $foto,
            'status' => $status
        ]);
        $inserted++;
    } catch (Exception $e) {
        $skipped++;
    }
}

fclose($fh);

$countStmt = $db->query("SELECT COUNT(*) as c FROM laporan_hama");
$total = $countStmt->fetch()['c'] ?? 0;
echo "Inserted: $inserted\n";
echo "Skipped: $skipped\n";
echo "Total laporan_hama: $total\n";