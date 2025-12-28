<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function cleanCode($s) { return preg_replace('/\D+/', '', trim((string)$s)); }
function startsWithHeaderDesc($row) { return isset($row[0]) && stripos($row[0], '# Deskripsi Kolom') === 0; }
function detectDelimiter($path) {
    $candidates = [',',';','\t','|'];
    $scores = array_fill_keys($candidates, 0);
    $fh = fopen($path, 'r');
    $lines = 0;
    while (($line = fgets($fh)) !== false && $lines < 5) {
        $lines++;
        foreach ($candidates as $d) { $scores[$d] += substr_count($line, $d === "\t" ? "\t" : $d); }
    }
    fclose($fh);
    arsort($scores);
    $best = key($scores);
    return $best === "\t" ? "\t" : $best;
}
function detectSqlFormat($path) {
    $fh = fopen($path, 'r'); $found = false; $i = 0;
    while (($line = fgets($fh)) !== false && $i < 20) { $i++; if (stripos($line, 'INSERT INTO') !== false) { $found = true; break; } }
    fclose($fh); return $found;
}
function mapHeaders($h) {
    $hl = array_map(function($x){ return strtolower(trim($x)); }, $h);
    $idx = array_flip($hl);
    $get = function($keys) use ($idx) { foreach ($keys as $k) { if (isset($idx[$k])) return $idx[$k]; } return null; };
    return [
        'kab_code' => $get(['id_kabupaten','kode_kabupaten','kabupaten_code','kode_kab']),
        'kab_name' => $get(['nama_kabupaten','kabupaten','kab_name']),
        'kec_code' => $get(['id_kecamatan','kode_kecamatan','kecamatan_code','kode_kec']),
        'kec_name' => $get(['nama_kecamatan','kecamatan','kec_name']),
        'desa_code'=> $get(['id_desa','kode_desa','desa_code','kode_desa_bps']),
        'desa_name'=> $get(['nama_desa','desa','kelurahan','nama_kelurahan']),
    ];
}

$path = $argv[1] ?? (ROOT_PATH . '/MFD.csv');
if (!file_exists($path)) { echo "FILE NOT FOUND: $path"; exit(1); }

$db = Database::getInstance()->getConnection();
$db->beginTransaction();

$logDir = ROOT_PATH . '/logs'; if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
$ts = date('Ymd_His');
$logPath = $logDir . '/mfd_import_' . $ts . '.csv';
$errPath = $logDir . '/mfd_error_' . $ts . '.csv';
$logF = fopen($logPath, 'w');
$errF = fopen($errPath, 'w');
fputcsv($logF, ['timestamp_start', date('Y-m-d H:i:s')]);
fputcsv($logF, ['file', $path]);
fputcsv($logF, []);
fputcsv($logF, ['kode_error','id_record','pesan_error']);
fputcsv($errF, ['kode_error','id_record','pesan_error']);

$processed = 0; $success = 0; $failed = 0;
$kabCache = []; $kecCache = [];

$isSql = detectSqlFormat($path);
if ($isSql) {
    $h = fopen($path, 'r');
    while (($line = fgets($h)) !== false) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '--')) continue;
        if (stripos($line, 'INSERT INTO') !== false) continue;
        $processed++;
        if (preg_match("/^\('([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)'\),?$/", $line, $m)) {
            $desaCode = cleanCode($m[1]); $desaName = trim($m[2]);
            $kecCode = cleanCode($m[3]); $kecName = trim($m[4]);
            $kabName = trim($m[5]);
            if (!preg_match('/^\d{10}$/', $desaCode) || !preg_match('/^\d{7}$/', $kecCode)) { $failed++; fputcsv($logF, ['FORMAT', $m[1], 'code format invalid']); fputcsv($errF, ['FORMAT', $m[1], 'code format invalid']); continue; }
            $kabCode = substr($kecCode, 0, 4);
            $kabId = $kabCache[$kabCode] ?? null;
            if (!$kabId) {
                $st = $db->prepare('SELECT id FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL');
                $st->execute([$kabCode]); $r = $st->fetch();
                if ($r) { $kabId = (int)$r['id']; $st = $db->prepare('UPDATE master_kabupaten SET nama_kabupaten = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$kabName, $_SESSION['user_id'] ?? 1, $kabId]); }
                else { $st = $db->prepare('INSERT INTO master_kabupaten (nama_kabupaten, kode_kabupaten, created_by) VALUES (?, ?, ?)'); $st->execute([$kabName, $kabCode, $_SESSION['user_id'] ?? 1]); $kabId = (int)$db->lastInsertId(); }
                $kabCache[$kabCode] = $kabId;
            }
            $kecId = $kecCache[$kecCode] ?? null;
            if (!$kecId) {
                $st = $db->prepare('SELECT id FROM master_kecamatan WHERE kode_kecamatan = ? AND deleted_at IS NULL');
                $st->execute([$kecCode]); $r = $st->fetch();
                if ($r) { $kecId = (int)$r['id']; $st = $db->prepare('UPDATE master_kecamatan SET kabupaten_id = ?, nama_kecamatan = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$kabId, $kecName, $_SESSION['user_id'] ?? 1, $kecId]); }
                else { $st = $db->prepare('INSERT INTO master_kecamatan (kabupaten_id, nama_kecamatan, kode_kecamatan, created_by) VALUES (?, ?, ?, ?)'); $st->execute([$kabId, $kecName, $kecCode, $_SESSION['user_id'] ?? 1]); $kecId = (int)$db->lastInsertId(); }
                $kecCache[$kecCode] = $kecId;
            }
            $st = $db->prepare('SELECT id FROM master_desa WHERE kode_desa = ? AND deleted_at IS NULL');
            $st->execute([$desaCode]); $rd = $st->fetch();
            if ($rd) { $did = (int)$rd['id']; $st = $db->prepare('UPDATE master_desa SET kecamatan_id = ?, nama_desa = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$kecId, $desaName, $_SESSION['user_id'] ?? 1, $did]); }
            else { $st = $db->prepare('INSERT INTO master_desa (kecamatan_id, nama_desa, kode_desa, created_by) VALUES (?, ?, ?, ?)'); $st->execute([$kecId, $desaName, $desaCode, $_SESSION['user_id'] ?? 1]); }
            $success++;
        } else { $failed++; fputcsv($logF, ['PARSE', '', 'line not matched']); fputcsv($errF, ['PARSE', '', 'line not matched']); }
    }
    fclose($h);
} else {
    $delim = detectDelimiter($path);
    $h = fopen($path, 'r');
    $headers = null; $map = null; $skipDesc = false;
    while (($row = fgetcsv($h, 0, $delim)) !== false) {
        if (!$headers) {
            if (startsWithHeaderDesc($row)) { $skipDesc = true; continue; }
            $headers = $row; $map = mapHeaders($headers); continue;
        }
        $processed++;
        $vals = array_map(function($x){ return trim((string)$x); }, $row);
        $plain = array_map(function($x){ return cleanCode($x); }, $vals);
        $findIdx = function($patternLen) use ($plain) {
            foreach ($plain as $i => $p) { if (preg_match('/^\d{'. $patternLen .'}$/', $p)) return $i; }
            return null;
        };
        $isName = function($s){ return $s !== '' && preg_match('/[A-Za-z\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\s]/u', $s) && !preg_match('/^\d+(\.\d+)*$/', $s); };
        static $currentKabId = null; static $currentKecId = null;
        $idxKab = $findIdx(4); $idxKec = $findIdx(6); $idxDes = $findIdx(10);
        $didAction = false;
        if ($idxKab !== null) {
            $kabCodeRaw = $vals[$idxKab]; $kabCode = cleanCode($kabCodeRaw);
            $kabName = '';
            for ($j = $idxKab+1; $j < count($vals); $j++) { if ($isName($vals[$j])) { $kabName = $vals[$j]; break; } }
            if ($kabName !== '' && preg_match('/^\d{4}$/', $kabCode)) {
                $kabId = $kabCache[$kabCode] ?? null;
                if (!$kabId) {
                    $st = $db->prepare('SELECT id FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL');
                    $st->execute([$kabCode]); $r = $st->fetch();
                    if ($r) { $kabId = (int)$r['id']; $st = $db->prepare('UPDATE master_kabupaten SET nama_kabupaten = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$kabName, $_SESSION['user_id'] ?? 1, $kabId]); }
                    else { $st = $db->prepare('INSERT INTO master_kabupaten (nama_kabupaten, kode_kabupaten, created_by) VALUES (?, ?, ?)'); $st->execute([$kabName, $kabCode, $_SESSION['user_id'] ?? 1]); $kabId = (int)$db->lastInsertId(); }
                    $kabCache[$kabCode] = $kabId;
                }
                $currentKabId = $kabId; $didAction = true;
            }
        }
        if ($idxKec !== null) {
            $kecCodeRaw = $vals[$idxKec]; $kecCode = cleanCode($kecCodeRaw);
            $kecName = '';
            for ($j = $idxKec+1; $j < count($vals); $j++) { if ($isName($vals[$j])) { $kecName = $vals[$j]; break; } }
            if ($kecName !== '' && preg_match('/^\d{6}$/', $kecCode) && $currentKabId) {
                $kecId = $kecCache[$kecCode] ?? null;
                if (!$kecId) {
                    $st = $db->prepare('SELECT id FROM master_kecamatan WHERE kode_kecamatan = ? AND deleted_at IS NULL');
                    $st->execute([$kecCode]); $r = $st->fetch();
                    if ($r) { $kecId = (int)$r['id']; $st = $db->prepare('UPDATE master_kecamatan SET kabupaten_id = ?, nama_kecamatan = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$currentKabId, $kecName, $_SESSION['user_id'] ?? 1, $kecId]); }
                    else {
                        $st = $db->prepare('SELECT id FROM master_kecamatan WHERE kabupaten_id = ? AND nama_kecamatan = ? AND deleted_at IS NULL');
                        $st->execute([$currentKabId, $kecName]); $r2 = $st->fetch();
                        if ($r2) { $kecId = (int)$r2['id']; $st = $db->prepare('UPDATE master_kecamatan SET kode_kecamatan = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$kecCode, $_SESSION['user_id'] ?? 1, $kecId]); }
                        else { $st = $db->prepare('INSERT INTO master_kecamatan (kabupaten_id, nama_kecamatan, kode_kecamatan, created_by) VALUES (?, ?, ?, ?)'); $st->execute([$currentKabId, $kecName, $kecCode, $_SESSION['user_id'] ?? 1]); $kecId = (int)$db->lastInsertId(); }
                    }
                    $kecCache[$kecCode] = $kecId;
                }
                $currentKecId = $kecId; $didAction = true;
            }
        }
        if ($idxDes !== null) {
            $desaCodeRaw = $vals[$idxDes]; $desaCode = cleanCode($desaCodeRaw);
            $desaName = '';
            for ($j = $idxDes+1; $j < count($vals); $j++) { if ($isName($vals[$j])) { $desaName = $vals[$j]; break; } }
            if ($desaName !== '' && preg_match('/^\d{10}$/', $desaCode) && $currentKecId) {
                $st = $db->prepare('SELECT id FROM master_desa WHERE kode_desa = ? AND deleted_at IS NULL');
                $st->execute([$desaCode]); $rd = $st->fetch();
                if ($rd) { $did = (int)$rd['id']; $st = $db->prepare('UPDATE master_desa SET kecamatan_id = ?, nama_desa = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$currentKecId, $desaName, $_SESSION['user_id'] ?? 1, $did]); }
                else {
                    $st = $db->prepare('SELECT id FROM master_desa WHERE kecamatan_id = ? AND nama_desa = ? AND deleted_at IS NULL');
                    $st->execute([$currentKecId, $desaName]); $rd2 = $st->fetch();
                    if ($rd2) { $did = (int)$rd2['id']; $st = $db->prepare('UPDATE master_desa SET kode_desa = ?, updated_by = ?, updated_at = NOW() WHERE id = ?'); $st->execute([$desaCode, $_SESSION['user_id'] ?? 1, $did]); }
                    else { $st = $db->prepare('INSERT INTO master_desa (kecamatan_id, nama_desa, kode_desa, created_by) VALUES (?, ?, ?, ?)'); $st->execute([$currentKecId, $desaName, $desaCode, $_SESSION['user_id'] ?? 1]); }
                }
                $didAction = true;
            }
        }
        if ($didAction) { $success++; } else { $failed++; fputcsv($logF, ['SKIP', '', 'no actionable codes in row']); fputcsv($errF, ['SKIP', '', 'no actionable codes in row']); }
    }
    fclose($h);
}
$db->commit();
fputcsv($logF, ['SUMMARY', 'processed', $processed]);
fputcsv($logF, ['SUMMARY', 'success', $success]);
fputcsv($logF, ['SUMMARY', 'failed', $failed]);
fputcsv($logF, []);
fputcsv($logF, ['timestamp_end', date('Y-m-d H:i:s')]);
fclose($logF); fclose($errF);
echo "DONE: processed=$processed success=$success failed=$failed\n";
