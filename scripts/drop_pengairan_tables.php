<?php
/**
 * Script untuk menghapus tabel-tabel fitur Pengairan Otomatis
 * 
 * Jalankan script ini untuk membersihkan database dari tabel pengairan
 * PERINGATAN: Ini akan menghapus semua data dalam tabel tersebut secara permanen!
 * 
 * Penggunaan: 
 * - Melalui browser: http://localhost/jagapadi/scripts/drop_pengairan_tables.php
 * - Melalui CLI: php scripts/drop_pengairan_tables.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Log file
$logFile = __DIR__ . '/../logs/pengairan_removal_' . date('Y-m-d_H-i-s') . '.log';

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $message . "\n";
}

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

logMessage("=== MULAI PENGHAPUSAN TABEL PENGAIRAN OTOMATIS ===", $logFile);

try {
    $db = Database::getInstance()->getConnection();
    
    // Disable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    logMessage("✓ Foreign key checks dinonaktifkan", $logFile);
    
    // Daftar tabel yang akan dihapus (urutan penting karena foreign key)
    $tables = [
        'pembacaan_sensor',
        'log_aktivitas_pengairan',
        'jadwal_pengairan',
        'aktuator_pengairan',
        'sensor_pengairan',
        'konfigurasi_pengairan'
    ];
    
    foreach ($tables as $table) {
        try {
            // Cek apakah tabel ada
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                // Hitung jumlah record sebelum dihapus
                $countResult = $db->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Drop tabel
                $db->exec("DROP TABLE `$table`");
                logMessage("✓ Tabel `$table` dihapus ($count records)", $logFile);
            } else {
                logMessage("⚠ Tabel `$table` tidak ditemukan (sudah dihapus)", $logFile);
            }
        } catch (Exception $e) {
            logMessage("✗ Gagal menghapus tabel `$table`: " . $e->getMessage(), $logFile);
        }
    }
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    logMessage("✓ Foreign key checks diaktifkan kembali", $logFile);
    
    logMessage("", $logFile);
    logMessage("=== PENGHAPUSAN TABEL PENGAIRAN OTOMATIS SELESAI ===", $logFile);
    logMessage("Log disimpan di: $logFile", $logFile);
    
} catch (Exception $e) {
    logMessage("✗ ERROR FATAL: " . $e->getMessage(), $logFile);
    error_log("Drop tables error: " . $e->getMessage());
    exit(1);
}
