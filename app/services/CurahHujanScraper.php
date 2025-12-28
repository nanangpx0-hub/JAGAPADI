<?php
/**
 * Curah Hujan Scraper Service
 * Service untuk mengambil data curah hujan dari sumber eksternal
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

class CurahHujanScraper {
    
    private $model;
    private $logFile;
    private $config;
    
    // Konfigurasi sumber data
    private $sources = [
        'bmkg_api' => [
            'name' => 'BMKG API',
            'url' => 'https://api.bmkg.go.id/publik/prakiraan-cuaca',
            'enabled' => true,
            'priority' => 1
        ],
        'dataonline_bmkg' => [
            'name' => 'BMKG Data Online',
            'url' => 'https://dataonline.bmkg.go.id',
            'enabled' => false, // Requires authentication
            'priority' => 2
        ],
        'simulation' => [
            'name' => 'Data Simulasi',
            'url' => null,
            'enabled' => true,
            'priority' => 99
        ]
    ];
    
    // Kode wilayah Jember (tingkat kabupaten)
    private $kodeWilayahJember = '35.09';
    
    // Daftar kecamatan di Jember untuk sampling
    private $kecamatanJember = [
        '35.09.01' => 'Kencong',
        '35.09.02' => 'Gumukmas',
        '35.09.03' => 'Puger',
        '35.09.04' => 'Wuluhan',
        '35.09.05' => 'Ambulu',
        '35.09.06' => 'Tempurejo',
        '35.09.07' => 'Silo',
        '35.09.08' => 'Mayang',
        '35.09.09' => 'Mumbulsari',
        '35.09.10' => 'Jenggawah',
        '35.09.11' => 'Ajung',
        '35.09.12' => 'Rambipuji',
        '35.09.13' => 'Balung',
        '35.09.14' => 'Umbulsari',
        '35.09.15' => 'Semboro',
        '35.09.16' => 'Jombang',
        '35.09.17' => 'Sumberbaru',
        '35.09.18' => 'Tanggul',
        '35.09.19' => 'Bangsalsari',
        '35.09.20' => 'Panti',
        '35.09.21' => 'Sukorambi',
        '35.09.22' => 'Arjasa',
        '35.09.23' => 'Pakusari',
        '35.09.24' => 'Kalisat',
        '35.09.25' => 'Ledokombo',
        '35.09.26' => 'Sumberjambe',
        '35.09.27' => 'Sukowono',
        '35.09.28' => 'Jelbuk',
        '35.09.29' => 'Kaliwates',
        '35.09.30' => 'Sumbersari',
        '35.09.31' => 'Patrang'
    ];
    
    public function __construct() {
        require_once ROOT_PATH . '/app/models/CurahHujan.php';
        $this->model = new CurahHujan();
        $this->logFile = ROOT_PATH . '/logs/curah_hujan_scraper.log';
        
        // Ensure tables exist
        $this->model->createTablesIfNotExist();
    }
    
    /**
     * Run scraping process
     * 
     * @param array $options
     * @return array Result summary
     */
    public function run($options = []) {
        $startTime = microtime(true);
        $this->log("=== Starting Curah Hujan Scraper ===");
        
        $targetMonth = $options['month'] ?? date('m');
        $targetYear = $options['year'] ?? date('Y');
        $forceSimulation = $options['force_simulation'] ?? false;
        
        $result = [
            'success' => false,
            'source' => null,
            'records_processed' => 0,
            'records_success' => 0,
            'records_failed' => 0,
            'message' => '',
            'execution_time' => 0
        ];
        
        try {
            // Check if should use simulation
            if ($forceSimulation) {
                $this->log("Force simulation mode enabled");
                $data = $this->generateSimulationData($targetYear, $targetMonth);
                $result['source'] = 'Simulasi';
            } else {
                // Try real sources first
                $data = $this->fetchFromBMKG($targetYear, $targetMonth);
                
                if (empty($data)) {
                    $this->log("BMKG API failed, falling back to simulation");
                    $data = $this->generateSimulationData($targetYear, $targetMonth);
                    $result['source'] = 'Simulasi (Fallback)';
                } else {
                    $result['source'] = 'BMKG API';
                }
            }
            
            if (empty($data)) {
                throw new Exception("No data available from any source");
            }
            
            $result['records_processed'] = count($data);
            
            // Validate and insert data
            $validData = $this->validateData($data);
            $insertResult = $this->model->bulkInsert($validData);
            
            $result['records_success'] = $insertResult['success'];
            $result['records_failed'] = $insertResult['failed'];
            $result['success'] = $insertResult['success'] > 0;
            $result['message'] = sprintf(
                "Berhasil memproses %d dari %d record",
                $insertResult['success'],
                count($data)
            );
            
        } catch (Exception $e) {
            $result['message'] = "Error: " . $e->getMessage();
            $this->log("ERROR: " . $e->getMessage());
        }
        
        $result['execution_time'] = round(microtime(true) - $startTime, 4);
        
        // Log to database
        $this->model->logActivity(
            'scrape',
            $result['success'] ? 'success' : 'failed',
            $result['message'],
            [
                'processed' => $result['records_processed'],
                'success' => $result['records_success'],
                'failed' => $result['records_failed'],
                'execution_time' => $result['execution_time']
            ]
        );
        
        $this->log("=== Scraper Finished: {$result['message']} ===");
        
        return $result;
    }
    
    /**
     * Fetch data from BMKG API
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    private function fetchFromBMKG($year, $month) {
        $this->log("Fetching from BMKG API...");
        
        try {
            // Sample one kecamatan for weather data
            $sampleKode = '35.09.29.1001'; // Kaliwates, Jember
            $url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$sampleKode}";
            
            $response = $this->httpRequest($url);
            
            if ($response === false) {
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (empty($data) || !isset($data['data'])) {
                $this->log("Invalid response from BMKG API");
                return [];
            }
            
            // Parse BMKG data
            return $this->parseBMKGData($data, $year, $month);
            
        } catch (Exception $e) {
            $this->log("BMKG API Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Parse BMKG API response
     * 
     * @param array $data
     * @param int $year
     * @param int $month
     * @return array
     */
    private function parseBMKGData($data, $year, $month) {
        $result = [];
        
        // BMKG provides forecast, not historical data
        // We'll extract what we can and estimate rainfall from weather descriptions
        
        if (isset($data['data'][0]['cuaca'])) {
            foreach ($data['data'][0]['cuaca'] as $dayData) {
                foreach ($dayData as $hourData) {
                    $datetime = $hourData['local_datetime'] ?? null;
                    if (!$datetime) continue;
                    
                    $date = substr($datetime, 0, 10);
                    $weatherCode = $hourData['weather'] ?? 0;
                    
                    // Estimate rainfall based on weather code
                    $rainfall = $this->estimateRainfallFromWeather($weatherCode);
                    
                    // Only add if not already exists for this date
                    $dateKey = $date;
                    if (!isset($result[$dateKey])) {
                        $result[$dateKey] = [
                            'tanggal' => $date,
                            'lokasi' => 'Jember',
                            'kode_wilayah' => $this->kodeWilayahJember,
                            'curah_hujan' => $rainfall,
                            'satuan' => 'mm',
                            'sumber_data' => 'BMKG API',
                            'keterangan' => 'Estimasi dari prakiraan cuaca'
                        ];
                    } else {
                        // Take maximum rainfall for the day
                        $result[$dateKey]['curah_hujan'] = max(
                            $result[$dateKey]['curah_hujan'],
                            $rainfall
                        );
                    }
                }
            }
        }
        
        return array_values($result);
    }
    
    /**
     * Estimate rainfall from BMKG weather code
     * 
     * @param int $code
     * @return float
     */
    private function estimateRainfallFromWeather($code) {
        $rainfallEstimates = [
            0 => 0,      // Cerah
            1 => 0,      // Cerah Berawan
            2 => 0,      // Berawan
            3 => 0,      // Berawan Tebal
            4 => 0,      // Udara Kabur
            5 => 0,      // Asap
            10 => 0,     // Kabut
            45 => 0,     // Berkabut
            60 => 5.0,   // Hujan Ringan
            61 => 15.0,  // Hujan Sedang
            63 => 35.0,  // Hujan Lebat
            80 => 2.0,   // Hujan Lokal
            95 => 25.0,  // Hujan Petir
            97 => 40.0,  // Hujan Petir Lebat
        ];
        
        return $rainfallEstimates[$code] ?? 0;
    }
    
    /**
     * Generate simulation data for demo purposes
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    private function generateSimulationData($year, $month) {
        $this->log("Generating simulation data for {$year}-{$month}");
        
        $data = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Rainfall patterns for Jember (tropical monsoon climate)
        // Higher in Nov-Apr (wet season), lower in May-Oct (dry season)
        $wetMonths = [11, 12, 1, 2, 3, 4];
        $isWetSeason = in_array((int)$month, $wetMonths);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            
            // Generate realistic rainfall
            if ($isWetSeason) {
                // Wet season: 60% chance of rain, higher amounts
                $hasRain = (mt_rand(1, 100) <= 60);
                $rainfall = $hasRain ? round(mt_rand(5, 80) + (mt_rand(0, 99) / 100), 2) : 0;
            } else {
                // Dry season: 15% chance of rain, lower amounts
                $hasRain = (mt_rand(1, 100) <= 15);
                $rainfall = $hasRain ? round(mt_rand(1, 20) + (mt_rand(0, 99) / 100), 2) : 0;
            }
            
            $data[] = [
                'tanggal' => $date,
                'lokasi' => 'Jember',
                'kode_wilayah' => $this->kodeWilayahJember,
                'curah_hujan' => $rainfall,
                'satuan' => 'mm',
                'sumber_data' => 'Simulasi',
                'keterangan' => 'Data simulasi untuk demo'
            ];
        }
        
        return $data;
    }
    
    /**
     * Validate data before insert
     * 
     * @param array $data
     * @return array
     */
    private function validateData($data) {
        $valid = [];
        
        foreach ($data as $record) {
            // Check required fields
            if (empty($record['tanggal']) || !isset($record['curah_hujan'])) {
                continue;
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $record['tanggal'])) {
                continue;
            }
            
            // Validate rainfall range (0-500 mm is reasonable for daily rainfall)
            $rainfall = floatval($record['curah_hujan']);
            if ($rainfall < 0 || $rainfall > 500) {
                $this->log("Invalid rainfall value: {$rainfall} for date {$record['tanggal']}");
                continue;
            }
            
            $valid[] = $record;
        }
        
        return $valid;
    }
    
    /**
     * HTTP request using cURL
     * 
     * @param string $url
     * @param array $options
     * @return string|false
     */
    private function httpRequest($url, $options = []) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: JAGAPADI/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            $this->log("cURL Error: {$error}");
            return false;
        }
        
        if ($httpCode !== 200) {
            $this->log("HTTP Error: {$httpCode}");
            return false;
        }
        
        return $response;
    }
    
    /**
     * Log message to file
     * 
     * @param string $message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if should run (end of month check)
     * 
     * @return bool
     */
    public function shouldRunToday() {
        $day = (int) date('d');
        $daysInMonth = (int) date('t');
        
        // Run on days 28-31 (end of month)
        return $day >= 28 && $day <= $daysInMonth;
    }
    
    /**
     * Send email notification on failure
     * 
     * @param string $message
     * @return bool
     */
    public function sendFailureNotification($message) {
        // Check if mail function is available
        if (!function_exists('mail')) {
            $this->log("Mail function not available");
            return false;
        }
        
        $to = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@jagapadi.local';
        $subject = '[JAGAPADI] Curah Hujan Scraper Failed';
        $body = "Scraper curah hujan mengalami error:\n\n{$message}\n\n";
        $body .= "Waktu: " . date('Y-m-d H:i:s') . "\n";
        $body .= "Server: " . ($_SERVER['SERVER_NAME'] ?? 'localhost');
        
        $headers = "From: noreply@jagapadi.local\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return @mail($to, $subject, $body, $headers);
    }
}
