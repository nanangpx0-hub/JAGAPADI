<?php
/**
 * Cron Job Script untuk Scraping Curah Hujan
 * 
 * Jalankan script ini dengan cron job:
 * 0 23 28-31 * * php /path/to/jagapadi/scripts/cron_curah_hujan.php
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Define ROOT_PATH
define('ROOT_PATH', dirname(__DIR__));

// Load required files
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/services/CurahHujanScraper.php';

echo "===========================================\n";
echo "JAGAPADI - Curah Hujan Scraper Cron Job\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $scraper = new CurahHujanScraper();
    
    // Check if should run today (end of month)
    if (!$scraper->shouldRunToday()) {
        echo "Hari ini bukan akhir bulan (28-31). Skipping.\n";
        exit(0);
    }
    
    echo "Menjalankan scraper untuk bulan ini...\n\n";
    
    // Get previous month's data (since we run at end of month)
    $options = [
        'year' => date('Y'),
        'month' => date('m'),
        'force_simulation' => false // Set true for testing
    ];
    
    $result = $scraper->run($options);
    
    echo "Status: " . ($result['success'] ? 'SUKSES' : 'GAGAL') . "\n";
    echo "Sumber: " . ($result['source'] ?? 'N/A') . "\n";
    echo "Record diproses: " . $result['records_processed'] . "\n";
    echo "Record berhasil: " . $result['records_success'] . "\n";
    echo "Record gagal: " . $result['records_failed'] . "\n";
    echo "Waktu eksekusi: " . $result['execution_time'] . " detik\n";
    echo "Pesan: " . $result['message'] . "\n";
    
    if (!$result['success']) {
        // Send failure notification
        $scraper->sendFailureNotification($result['message']);
        echo "\nNotifikasi email dikirim.\n";
        exit(1);
    }
    
    echo "\n===========================================\n";
    echo "Scraper selesai dengan sukses!\n";
    echo "===========================================\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Try to send notification
    try {
        $scraper->sendFailureNotification($e->getMessage());
    } catch (Exception $e2) {
        echo "Failed to send notification: " . $e2->getMessage() . "\n";
    }
    
    exit(1);
}
