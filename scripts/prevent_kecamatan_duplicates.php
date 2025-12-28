<?php
/**
 * Script untuk mencegah duplikasi data kecamatan di masa depan
 * Membuat trigger, constraint, dan validasi
 */

require_once __DIR__ . '/../app/config/Database.php';

class KecamatanDuplicatePrevention {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buat trigger untuk mencegah duplikasi
     */
    public function createDuplicatePreventionTrigger() {
        echo "=== MEMBUAT TRIGGER PENCEGAHAN DUPLIKASI ===\n\n";
        
        $triggers = [
            // Trigger sebelum insert
            "DROP TRIGGER IF EXISTS prevent_duplicate_kecamatan_insert;",
            "CREATE TRIGGER prevent_duplicate_kecamatan_insert
            BEFORE INSERT ON master_kecamatan
            FOR EACH ROW
            BEGIN
                DECLARE duplicate_count INT;
                
                -- Cek duplikasi nama kecamatan dalam kabupaten yang sama
                SELECT COUNT(*) INTO duplicate_count
                FROM master_kecamatan
                WHERE nama_kecamatan = NEW.nama_kecamatan 
                AND kabupaten_id = NEW.kabupaten_id;
                
                IF duplicate_count > 0 THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Duplikasi nama kecamatan dalam kabupaten yang sama tidak diperbolehkan';
                END IF;
                
                -- Cek duplikasi kode kecamatan
                SELECT COUNT(*) INTO duplicate_count
                FROM master_kecamatan
                WHERE kode_kecamatan = NEW.kode_kecamatan;
                
                IF duplicate_count > 0 THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Kode kecamatan sudah digunakan';
                END IF;
                
                -- Validasi format kode BPS
                IF NEW.kode_kecamatan NOT REGEXP '^35[0-9]{4}$' THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Format kode kecamatan tidak valid. Gunakan format 35XXXX';
                END IF;
            END",
            
            // Trigger sebelum update
            "DROP TRIGGER IF EXISTS prevent_duplicate_kecamatan_update;",
            "CREATE TRIGGER prevent_duplicate_kecamatan_update
            BEFORE UPDATE ON master_kecamatan
            FOR EACH ROW
            BEGIN
                DECLARE duplicate_count INT;
                
                -- Cek duplikasi nama kecamatan dalam kabupaten yang sama (exclude current record)
                IF NEW.nama_kecamatan != OLD.nama_kecamatan OR NEW.kabupaten_id != OLD.kabupaten_id THEN
                    SELECT COUNT(*) INTO duplicate_count
                    FROM master_kecamatan
                    WHERE nama_kecamatan = NEW.nama_kecamatan 
                    AND kabupaten_id = NEW.kabupaten_id
                    AND id != NEW.id;
                    
                    IF duplicate_count > 0 THEN
                        SIGNAL SQLSTATE '45000' 
                        SET MESSAGE_TEXT = 'Duplikasi nama kecamatan dalam kabupaten yang sama tidak diperbolehkan';
                    END IF;
                END IF;
                
                -- Cek duplikasi kode kecamatan (exclude current record)
                IF NEW.kode_kecamatan != OLD.kode_kecamatan THEN
                    SELECT COUNT(*) INTO duplicate_count
                    FROM master_kecamatan
                    WHERE kode_kecamatan = NEW.kode_kecamatan
                    AND id != NEW.id;
                    
                    IF duplicate_count > 0 THEN
                        SIGNAL SQLSTATE '45000' 
                        SET MESSAGE_TEXT = 'Kode kecamatan sudah digunakan';
                    END IF;
                END IF;
                
                -- Validasi format kode BPS
                IF NEW.kode_kecamatan NOT REGEXP '^35[0-9]{4}$' THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Format kode kecamatan tidak valid. Gunakan format 35XXXX';
                END IF;
            END"
        ];
        
        foreach ($triggers as $sql) {
            try {
                $this->db->exec($sql);
                echo "✅ Trigger berhasil dibuat/diperbarui\n";
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Buat unique constraint
     */
    public function createUniqueConstraints() {
        echo "\n=== MEMBUAT UNIQUE CONSTRAINTS ===\n\n";
        
        $constraints = [
            // Unique constraint untuk kode kecamatan
            "ALTER TABLE master_kecamatan ADD CONSTRAINT uk_kecamatan_kode UNIQUE (kode_kecamatan)",
            // Composite unique constraint untuk nama kecamatan dalam kabupaten
            "ALTER TABLE master_kecamatan ADD CONSTRAINT uk_kecamatan_nama_kab UNIQUE (nama_kecamatan, kabupaten_id)"
        ];
        
        foreach ($constraints as $sql) {
            try {
                $this->db->exec($sql);
                echo "✅ Constraint berhasil dibuat: " . $sql . "\n";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "⚠️  Constraint sudah ada: " . $sql . "\n";
                } else {
                    echo "❌ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    /**
     * Buat stored procedure untuk validasi data
     */
    public function createValidationProcedures() {
        echo "\n=== MEMBUAT STORED PROCEDURES ===\n\n";
        
        $procedures = [
            // Procedure untuk validasi data kecamatan
            "DROP PROCEDURE IF EXISTS validate_kecamatan_data;",
            "CREATE PROCEDURE validate_kecamatan_data(
                IN p_nama_kecamatan VARCHAR(255),
                IN p_kode_kecamatan VARCHAR(10),
                IN p_kabupaten_id INT,
                IN p_id INT
            )
            BEGIN
                DECLARE v_duplicate_nama INT DEFAULT 0;
                DECLARE v_duplicate_kode INT DEFAULT 0;
                DECLARE v_valid_kode INT DEFAULT 0;
                
                -- Validasi format kode
                IF p_kode_kecamatan REGEXP '^35[0-9]{4}$' THEN
                    SET v_valid_kode = 1;
                END IF;
                
                -- Cek duplikasi nama
                IF p_id IS NULL THEN
                    -- Insert case
                    SELECT COUNT(*) INTO v_duplicate_nama
                    FROM master_kecamatan
                    WHERE nama_kecamatan = p_nama_kecamatan 
                    AND kabupaten_id = p_kabupaten_id;
                ELSE
                    -- Update case
                    SELECT COUNT(*) INTO v_duplicate_nama
                    FROM master_kecamatan
                    WHERE nama_kecamatan = p_nama_kecamatan 
                    AND kabupaten_id = p_kabupaten_id
                    AND id != p_id;
                END IF;
                
                -- Cek duplikasi kode
                IF p_id IS NULL THEN
                    -- Insert case
                    SELECT COUNT(*) INTO v_duplicate_kode
                    FROM master_kecamatan
                    WHERE kode_kecamatan = p_kode_kecamatan;
                ELSE
                    -- Update case
                    SELECT COUNT(*) INTO v_duplicate_kode
                    FROM master_kecamatan
                    WHERE kode_kecamatan = p_kode_kecamatan
                    AND id != p_id;
                END IF;
                
                -- Return validation result
                SELECT 
                    v_valid_kode as valid_kode,
                    v_duplicate_nama as duplicate_nama,
                    v_duplicate_kode as duplicate_kode,
                    CASE 
                        WHEN v_valid_kode = 0 THEN 'Format kode tidak valid'
                        WHEN v_duplicate_nama > 0 THEN 'Nama kecamatan sudah ada dalam kabupaten ini'
                        WHEN v_duplicate_kode > 0 THEN 'Kode kecamatan sudah digunakan'
                        ELSE 'Data valid'
                    END as validation_message;
            END",
            
            // Procedure untuk mencari duplikasi
            "DROP PROCEDURE IF EXISTS find_kecamatan_duplicates;",
            "CREATE PROCEDURE find_kecamatan_duplicates()
            BEGIN
                SELECT 
                    k1.nama_kecamatan,
                    k1.kode_kecamatan as kode1,
                    kab1.nama_kabupaten as kab_nama1,
                    k1.id as id1,
                    k2.kode_kecamatan as kode2,
                    kab2.nama_kabupaten as kab_nama2,
                    k2.id as id2,
                    CASE 
                        WHEN k1.kabupaten_id = k2.kabupaten_id THEN 'Same Kabupaten'
                        ELSE 'Different Kabupaten'
                    END as duplicate_type
                FROM master_kecamatan k1
                INNER JOIN master_kecamatan k2 ON k1.nama_kecamatan = k2.nama_kecamatan 
                    AND k1.kode_kecamatan != k2.kode_kecamatan
                    AND k1.id < k2.id
                INNER JOIN master_kabupaten kab1 ON k1.kabupaten_id = kab1.id
                INNER JOIN master_kabupaten kab2 ON k2.kabupaten_id = kab2.id
                ORDER BY k1.nama_kecamatan;
            END"
        ];
        
        foreach ($procedures as $sql) {
            try {
                $this->db->exec($sql);
                echo "✅ Procedure berhasil dibuat\n";
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Buat index untuk performance
     */
    public function createPerformanceIndexes() {
        echo "\n=== MEMBUAT INDEXES ===\n\n";
        
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_kecamatan_nama ON master_kecamatan(nama_kecamatan)",
            "CREATE INDEX IF NOT EXISTS idx_kecamatan_kode ON master_kecamatan(kode_kecamatan)",
            "CREATE INDEX IF NOT EXISTS idx_kecamatan_kab_nama ON master_kecamatan(kabupaten_id, nama_kecamatan)",
            "CREATE INDEX IF NOT EXISTS idx_kecamatan_search ON master_kecamatan(nama_kecamatan, kode_kecamatan)"
        ];
        
        foreach ($indexes as $sql) {
            try {
                $this->db->exec($sql);
                echo "✅ Index berhasil dibuat: " . $sql . "\n";
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Update model untuk include validasi
     */
    public function updateModelValidation() {
        echo "\n=== UPDATE MODEL VALIDATION ===\n\n";
        
        $modelContent = '<?php
/**
 * Enhanced Kecamatan Model dengan validasi duplikasi
 */

class MasterKecamatan extends Model {
    protected $table = \'master_kecamatan\';
    
    /**
     * Validasi data sebelum insert/update
     */
    public function validateData($data, $excludeId = null) {
        $errors = [];
        
        // Validasi nama
        if (empty($data[\'nama_kecamatan\'])) {
            $errors[] = \'Nama kecamatan wajib diisi\';
        }
        
        // Validasi kode
        if (empty($data[\'kode_kecamatan\'])) {
            $errors[] = \'Kode kecamatan wajib diisi\';
        } elseif (!preg_match(\'/^35[0-9]{4}$/\', $data[\'kode_kecamatan\'])) {
            $errors[] = \'Format kode kecamatan tidak valid. Gunakan format 35XXXX\';
        }
        
        // Validasi kabupaten
        if (empty($data[\'kabupaten_id\'])) {
            $errors[] = \'Kabupaten wajib dipilih\';
        }
        
        // Cek duplikasi nama dalam kabupaten
        if (!empty($data[\'nama_kecamatan\']) && !empty($data[\'kabupaten_id\'])) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE nama_kecamatan = ? AND kabupaten_id = ?";
            $params = [$data[\'nama_kecamatan\'], $data[\'kabupaten_id\']];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetch()[\'count\'];
            
            if ($count > 0) {
                $errors[] = \'Nama kecamatan sudah ada dalam kabupaten ini\';
            }
        }
        
        // Cek duplikasi kode
        if (!empty($data[\'kode_kecamatan\'])) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE kode_kecamatan = ?";
            $params = [$data[\'kode_kecamatan\']];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetch()[\'count\'];
            
            if ($count > 0) {
                $errors[] = \'Kode kecamatan sudah digunakan\';
            }
        }
        
        return $errors;
    }
    
    /**
     * Cek duplikasi dengan procedure
     */
    public function checkDuplicatesWithProcedure($data, $excludeId = null) {
        try {
            $sql = "CALL validate_kecamatan_data(?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data[\'nama_kecamatan\'] ?? \'\',
                $data[\'kode_kecamatan\'] ?? \'\',
                $data[\'kabupaten_id\'] ?? 0,
                $excludeId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // Important for stored procedures
            
            return $result;
        } catch (Exception $e) {
            return [\'validation_message\' => $e->getMessage()];
        }
    }
    
    /**
     * Enhanced create dengan validasi
     */
    public function createWithValidation($data) {
        $errors = $this->validateData($data);
        
        if (!empty($errors)) {
            return [\'success\' => false, \'errors\' => $errors];
        }
        
        try {
            $sql = "INSERT INTO {$this->table} (nama_kecamatan, kode_kecamatan, kabupaten_id, created_at) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data[\'nama_kecamatan\'],
                $data[\'kode_kecamatan\'],
                $data[\'kabupaten_id\']
            ]);
            
            return [\'success\' => true, \'id\' => $this->db->lastInsertId()];
            
        } catch (Exception $e) {
            return [\'success\' => false, \'message\' => $e->getMessage()];
        }
    }
    
    /**
     * Enhanced update dengan validasi
     */
    public function updateWithValidation($id, $data) {
        $errors = $this->validateData($data, $id);
        
        if (!empty($errors)) {
            return [\'success\' => false, \'errors\' => $errors];
        }
        
        try {
            $sql = "UPDATE {$this->table} 
                    SET nama_kecamatan = ?, kode_kecamatan = ?, kabupaten_id = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data[\'nama_kecamatan\'],
                $data[\'kode_kecamatan\'],
                $data[\'kabupaten_id\'],
                $id
            ]);
            
            return [\'success\' => true, \'affected\' => $stmt->rowCount()];
            
        } catch (Exception $e) {
            return [\'success\' => false, \'message\' => $e->getMessage()];
        }
    }
}';
        
        $modelFile = __DIR__ . '/../app/models/MasterKecamatanEnhanced.php';
        
        if (file_put_contents($modelFile, $modelContent)) {
            echo "✅ Enhanced model berhasil dibuat: MasterKecamatanEnhanced.php\n";
        } else {
            echo "❌ Gagal membuat enhanced model\n";
        }
    }
    
    /**
     * Jalankan semua setup
     */
    public function runSetup() {
        echo "🚀 MEMULAI SETUP PENCEGAHAN DUPLIKASI KECAMATAN\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $this->createUniqueConstraints();
        $this->createDuplicatePreventionTrigger();
        $this->createValidationProcedures();
        $this->createPerformanceIndexes();
        $this->updateModelValidation();
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✅ SETUP PENCEGAHAN DUPLIKASI SELESAI\n";
        echo "📝 Catatan:\n";
        echo "   • Trigger database akan mencegah duplikasi pada level database\n";
        echo "   • Unique constraint untuk memastikan integritas data\n";
        echo "   • Enhanced model untuk validasi di level aplikasi\n";
        echo "   • Procedure untuk validasi dan pencarian duplikasi\n";
        echo "   • Index untuk meningkatkan performance query\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Jalankan setup
$prevention = new KecamatanDuplicatePrevention();
$prevention->runSetup();
?>
