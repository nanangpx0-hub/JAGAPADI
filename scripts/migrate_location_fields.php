<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

function columnExists($db, $table, $column) {
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([DB_NAME, $table, $column]);
    return ($stmt->fetch()['c'] ?? 0) > 0;
}

try {
    if (!columnExists($db, 'laporan_hama', 'kecamatan')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN kecamatan VARCHAR(100) NULL");
    }
    if (!columnExists($db, 'laporan_hama', 'desa')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN desa VARCHAR(100) NULL");
    }
    if (!columnExists($db, 'laporan_hama', 'alamat_lengkap')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN alamat_lengkap VARCHAR(255) NULL");
    }
    if (!columnExists($db, 'laporan_hama', 'kabupaten')) {
        $db->exec("ALTER TABLE laporan_hama ADD COLUMN kabupaten VARCHAR(100) NULL");
    }
    try { $db->exec("CREATE INDEX idx_laporan_kecamatan ON laporan_hama(kecamatan)"); } catch (Exception $e) {}
    try { $db->exec("CREATE INDEX idx_laporan_desa ON laporan_hama(desa)"); } catch (Exception $e) {}
    try { $db->exec("CREATE INDEX idx_laporan_kabupaten ON laporan_hama(kabupaten)"); } catch (Exception $e) {}

    $db->beginTransaction();
    $stmt = $db->query("SELECT id, lokasi, latitude, longitude, kecamatan FROM laporan_hama");
    $update = $db->prepare("UPDATE laporan_hama SET kecamatan = ?, desa = ?, alamat_lengkap = ?, kabupaten = ? WHERE id = ?");
    while ($row = $stmt->fetch()) {
        $loc = $row['lokasi'] ?? '';
        $desa = null; $kec = null; $alamat = null;
        if ($loc) {
            if (preg_match('/Desa\s+([^,]+),\s*Kec\.\s*([^,]+)/i', $loc, $m)) {
                $desa = trim($m[1]);
                $kec = trim($m[2]);
                $alamat = $loc;
            } elseif (preg_match('/Kec\.\s*([^,]+).*Desa\s+([^,]+)/i', $loc, $m)) {
                $kec = trim($m[1]);
                $desa = trim($m[2]);
                $alamat = $loc;
            } elseif (preg_match('/Kec\.\s*([^,]+)/i', $loc, $m)) {
                $kec = trim($m[1]);
                $alamat = $loc;
            } else {
                $alamat = $loc;
            }
        }
        $kab = null;
        $lat = isset($row['latitude']) ? (float)$row['latitude'] : null;
        $lon = isset($row['longitude']) ? (float)$row['longitude'] : null;
        if (!is_null($lat) && !is_null($lon)) {
            if ($lat >= JEMBER_LAT_MIN && $lat <= JEMBER_LAT_MAX && $lon >= JEMBER_LON_MIN && $lon <= JEMBER_LON_MAX) {
                $kab = 'Jember';
            }
        }
        if (!$kab) {
            $kecName = strtolower(trim($row['kecamatan'] ?? $kec ?? ''));
            if (in_array($kecName, ['tempursari','pasirian'])) { $kab = 'Lumajang'; }
            elseif (in_array($kecName, ['binakal','taman krocok'])) { $kab = 'Bondowoso'; }
            elseif (in_array($kecName, ['rogojampi'])) { $kab = 'Banyuwangi'; }
            elseif (in_array($kecName, ['kraksaan'])) { $kab = 'Probolinggo'; }
            elseif (in_array($kecName, ['panarukan'])) { $kab = 'Situbondo'; }
        }
        if (!$kab) { $kab = 'Jember'; }
        $update->execute([$kec, $desa, $alamat, $kab, $row['id']]);
    }
    $db->commit();
    echo "Migration done\n";
} catch (Exception $e) {
    if ($db->inTransaction()) { $db->rollBack(); }
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}