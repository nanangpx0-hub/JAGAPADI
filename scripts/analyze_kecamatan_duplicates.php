<?php
/**
 * Script untuk menganalisis duplikasi data kecamatan
 * Mengidentifikasi nama kecamatan yang sama dengan kode BPS berbeda
 */

require_once __DIR__ . '/../app/config/Database.php';

class KecamatanDuplicateAnalyzer {
    private $db;
    private $duplicates = [];
    private $report = [];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Analisis duplikasi nama kecamatan
     */
    public function analyzeDuplicates() {
        echo "=== ANALISIS DUPLIKASI DATA KECAMATAN ===\n\n";
        
        // Query untuk menemukan duplikasi nama kecamatan
        $sql = "
            SELECT 
                k1.nama_kecamatan,
                k1.kode_kecamatan as kode1,
                k1.kabupaten_id as kab_id1,
                kab1.nama_kabupaten as kab_nama1,
                k1.id as id1,
                k2.kode_kecamatan as kode2,
                k2.kabupaten_id as kab_id2,
                kab2.nama_kabupaten as kab_nama2,
                k2.id as id2,
                COUNT(*) as duplicate_count
            FROM master_kecamatan k1
            INNER JOIN master_kecamatan k2 ON k1.nama_kecamatan = k2.nama_kecamatan 
                AND k1.kode_kecamatan != k2.kode_kecamatan
                AND k1.id < k2.id
            INNER JOIN master_kabupaten kab1 ON k1.kabupaten_id = kab1.id
            INNER JOIN master_kabupaten kab2 ON k2.kabupaten_id = kab2.id
            GROUP BY k1.nama_kecamatan, k1.kode_kecamatan, k2.kode_kecamatan
            ORDER BY k1.nama_kecamatan, k1.kode_kecamatan
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                echo "✅ Tidak ada duplikasi nama kecamatan ditemukan.\n";
                return [];
            }
            
            echo "🔍 Ditemukan " . count($results) . " kasus duplikasi nama kecamatan:\n\n";
            
            foreach ($results as $row) {
                $this->duplicates[] = $row;
                $this->displayDuplicate($row);
            }
            
            return $results;
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Menampilkan informasi duplikasi
     */
    private function displayDuplicate($row) {
        echo "📋 Nama: " . $row['nama_kecamatan'] . "\n";
        echo "   ├── Entri 1: Kode " . $row['kode1'] . " | Kab: " . $row['kab_nama1'] . " (ID: " . $row['id1'] . ")\n";
        echo "   └── Entri 2: Kode " . $row['kode2'] . " | Kab: " . $row['kab_nama2'] . " (ID: " . $row['id2'] . ")\n";
        echo "\n";
    }
    
    /**
     * Analisis per kabupaten
     */
    public function analyzeByKabupaten() {
        echo "\n=== ANALISIS PER KABUPATEN ===\n\n";
        
        $sql = "
            SELECT 
                kab.nama_kabupaten,
                COUNT(*) as total_kecamatan,
                COUNT(DISTINCT kec.nama_kecamatan) as unique_nama,
                (COUNT(*) - COUNT(DISTINCT kec.nama_kecamatan)) as duplicate_count
            FROM master_kabupaten kab
            LEFT JOIN master_kecamatan kec ON kab.id = kec.kabupaten_id
            GROUP BY kab.id, kab.nama_kabupaten
            HAVING duplicate_count > 0
            ORDER BY duplicate_count DESC
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                echo "✅ Tidak ada kabupaten dengan duplikasi.\n";
                return;
            }
            
            foreach ($results as $row) {
                echo "📍 " . $row['nama_kabupaten'] . ":\n";
                echo "   Total kecamatan: " . $row['total_kecamatan'] . "\n";
                echo "   Nama unik: " . $row['unique_nama'] . "\n";
                echo "   Duplikasi: " . $row['duplicate_count'] . "\n\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Cari duplikasi dalam kabupaten yang sama
     */
    public function analyzeDuplicatesInSameKabupaten() {
        echo "=== DUPLIKASI DALAM KABUPATEN YANG SAMA ===\n\n";
        
        $sql = "
            SELECT 
                k1.nama_kecamatan,
                k1.kode_kecamatan as kode1,
                k1.id as id1,
                k2.kode_kecamatan as kode2,
                k2.id as id2,
                kab.nama_kabupaten
            FROM master_kecamatan k1
            INNER JOIN master_kecamatan k2 ON k1.nama_kecamatan = k2.nama_kecamatan 
                AND k1.kode_kecamatan != k2.kode_kecamatan
                AND k1.kabupaten_id = k2.kabupaten_id
                AND k1.id < k2.id
            INNER JOIN master_kabupaten kab ON k1.kabupaten_id = kab.id
            ORDER BY kab.nama_kabupaten, k1.nama_kecamatan
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                echo "✅ Tidak ada duplikasi dalam kabupaten yang sama.\n";
                return [];
            }
            
            echo "⚠️  Ditemukan " . count($results) . " duplikasi dalam kabupaten yang sama:\n\n";
            
            foreach ($results as $row) {
                echo "📍 " . $row['nama_kabupaten'] . ":\n";
                echo "   Nama: " . $row['nama_kecamatan'] . "\n";
                echo "   ├── Kode " . $row['kode1'] . " (ID: " . $row['id1'] . ")\n";
                echo "   └── Kode " . $row['kode2'] . " (ID: " . $row['id2'] . ")\n\n";
            }
            
            return $results;
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Generate laporan lengkap
     */
    public function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_duplicates' => count($this->duplicates),
            'duplicates' => $this->duplicates,
            'summary' => $this->generateSummary()
        ];
        
        // Save report to file
        $reportFile = __DIR__ . '/../reports/kecamatan_duplicates_' . date('Y-m-d_H-i-s') . '.json';
        $reportDir = dirname($reportFile);
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        echo "📄 Laporan disimpan: " . basename($reportFile) . "\n";
        
        return $report;
    }
    
    /**
     * Generate summary statistics
     */
    private function generateSummary() {
        $summary = [
            'total_duplicate_groups' => 0,
            'affected_kecamatan' => [],
            'affected_kabupaten' => [],
            'duplicate_patterns' => []
        ];
        
        $processed_names = [];
        foreach ($this->duplicates as $dup) {
            $nama = $dup['nama_kecamatan'];
            
            if (!in_array($nama, $processed_names)) {
                $summary['total_duplicate_groups']++;
                $processed_names[] = $nama;
            }
            
            if (!in_array($dup['kab_nama1'], $summary['affected_kabupaten'])) {
                $summary['affected_kabupaten'][] = $dup['kab_nama1'];
            }
            if (!in_array($dup['kab_nama2'], $summary['affected_kabupaten'])) {
                $summary['affected_kabupaten'][] = $dup['kab_nama2'];
            }
        }
        
        return $summary;
    }
    
    /**
     * Validasi kode BPS
     */
    public function validateBPSCode($kode) {
        // BPS code validation: should be 6 digits for kecamatan
        return preg_match('/^\d{6}$/', $kode) && substr($kode, 0, 2) === '35';
    }
    
    /**
     * Cek kecamatan dengan format kode tidak valid
     */
    public function analyzeInvalidCodes() {
        echo "\n=== ANALISIS KODE BPS TIDAK VALID ===\n\n";
        
        $sql = "
            SELECT 
                id,
                nama_kecamatan,
                kode_kecamatan,
                kab.nama_kabupaten
            FROM master_kecamatan kec
            INNER JOIN master_kabupaten kab ON kec.kabupaten_id = kab.id
            WHERE kode_kecamatan NOT REGEXP '^35[0-9]{4}$'
            OR LENGTH(kode_kecamatan) != 6
            ORDER BY kab.nama_kabupaten, kec.nama_kecamatan
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                echo "✅ Semua kode kecamatan valid.\n";
                return [];
            }
            
            echo "⚠️  Ditemukan " . count($results) . " kecamatan dengan kode tidak valid:\n\n";
            
            foreach ($results as $row) {
                echo "❌ " . $row['nama_kecamatan'] . " | Kode: " . $row['kode_kecamatan'] . 
                     " | Kab: " . $row['nama_kabupaten'] . " (ID: " . $row['id'] . ")\n";
            }
            
            return $results;
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return [];
        }
    }
}

// Run analysis
$analyzer = new KecamatanDuplicateAnalyzer();

echo "Mulai analisis duplikasi data kecamatan...\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Analisis duplikasi umum
$duplicates = $analyzer->analyzeDuplicates();

// 2. Analisis per kabupaten
$analyzer->analyzeByKabupaten();

// 3. Analisis duplikasi dalam kabupaten sama
$sameKabDuplicates = $analyzer->analyzeDuplicatesInSameKabupaten();

// 4. Analisis kode tidak valid
$invalidCodes = $analyzer->analyzeInvalidCodes();

// 5. Generate report
$report = $analyzer->generateReport();

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RINGKASAN ANALISIS:\n";
echo "• Total duplikasi nama: " . count($duplicates) . "\n";
echo "• Duplikasi dalam kabupaten sama: " . count($sameKabDuplicates) . "\n";
echo "• Kode tidak valid: " . count($invalidCodes) . "\n";
echo "• Laporan lengkap disimpan dalam format JSON\n";
echo str_repeat("=", 60) . "\n";

?>
