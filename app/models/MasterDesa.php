<?php
require_once ROOT_PATH . '/app/core/Cache.php';

class MasterDesa extends Model {
    protected $table = 'master_desa';
    
    public function getByKecamatan($kecamatanId, $q = null, $limit = 200) {
        // Sort by kode_desa ascending for consistent ordering
        $cacheKey = 'master_desa_kec_by_kode_' . $kecamatanId;
        
        if (!$q) {
            return Cache::remember($cacheKey, function() use ($kecamatanId) {
                $stmt = $this->db->prepare("SELECT * FROM master_desa WHERE kecamatan_id = ? AND deleted_at IS NULL ORDER BY kode_desa ASC");
                $stmt->execute([$kecamatanId]);
                return $stmt->fetchAll();
            }, 3600); // Cache for 1 hour
        }
        
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM master_desa WHERE kecamatan_id = ? AND nama_desa LIKE ? AND deleted_at IS NULL ORDER BY kode_desa ASC LIMIT $limit");
        $stmt->execute([$kecamatanId, '%'.$q.'%']);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM master_desa WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Admin CRUD Methods
    public function getAllWithHierarchy($kecamatanId = null, $search = '', $limit = 20, $offset = 0) {
        $sql = "SELECT d.*, kc.nama_kecamatan, kb.nama_kabupaten, kc.kabupaten_id
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.deleted_at IS NULL";
        $params = [];
        
        if ($kecamatanId) {
            $sql .= " AND d.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        if ($search) {
            $sql .= " AND (d.nama_desa LIKE ? OR d.kode_desa LIKE ? OR kc.nama_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " ORDER BY kb.nama_kabupaten, kc.nama_kecamatan, d.nama_desa LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($kecamatanId = null, $search = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.deleted_at IS NULL";
        $params = [];
        
        if ($kecamatanId) {
            $sql .= " AND d.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        if ($search) {
            $sql .= " AND (d.nama_desa LIKE ? OR d.kode_desa LIKE ? OR kc.nama_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO master_desa (kecamatan_id, nama_desa, kode_desa, kode_pos, created_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['kecamatan_id'], $data['nama_desa'], $data['kode_desa'], $data['kode_pos'], $data['created_by']]);
        Cache::delete('master_desa_kec_' . $data['kecamatan_id']);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        // Get old data to clear cache
        $old = $this->findById($id);
        
        $sql = "UPDATE master_desa SET kecamatan_id = ?, nama_desa = ?, kode_desa = ?, kode_pos = ?, updated_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['kecamatan_id'], $data['nama_desa'], $data['kode_desa'], $data['kode_pos'], $data['updated_by'], $id]);
        
        // Clear cache for both old and new kecamatan
        if ($old) {
            Cache::delete('master_desa_kec_' . $old['kecamatan_id']);
        }
        Cache::delete('master_desa_kec_' . $data['kecamatan_id']);
        
        return $stmt->rowCount();
    }
    
    public function softDelete($id, $userId) {
        // Get data to clear cache
        $data = $this->findById($id);
        
        $sql = "UPDATE master_desa SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $id]);
        
        if ($data) {
            Cache::delete('master_desa_kec_' . $data['kecamatan_id']);
        }
        
        return $stmt->rowCount();
    }
    
    public function checkKodeExists($kode, $excludeId = null) {
        $sql = "SELECT COUNT(*) as c FROM master_desa WHERE kode_desa = ? AND deleted_at IS NULL";
        $params = [$kode];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    public function countByKecamatan($kecamatanId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM master_desa WHERE kecamatan_id = ? AND deleted_at IS NULL");
        $stmt->execute([$kecamatanId]);
        return $stmt->fetch()['c'] ?? 0;
    }
    
    public function findByIdWithHierarchy($id) {
        $sql = "SELECT d.*, kc.nama_kecamatan, kb.nama_kabupaten, kc.kabupaten_id
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.id = ? AND d.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all desa with hierarchy, supporting both kabupaten and kecamatan filters
     * @param int|null $kabupatenId Filter by kabupaten
     * @param int|null $kecamatanId Filter by kecamatan (must be in kabupaten if kabupatenId is set)
     * @param string $search Search term for nama_desa, kode_desa, or kode_pos
     * @param int $limit Number of records to return
     * @param int $offset Starting offset for pagination
     * @param string $sortBy Column to sort by (kode_kecamatan, kode_desa, nama_desa, nama_kecamatan)
     * @param string $sortDir Sort direction (asc, desc)
     * @return array List of desa with hierarchy data
     */
    public function getAllWithHierarchyAndKabupaten($kabupatenId = null, $kecamatanId = null, $search = '', $limit = 20, $offset = 0, $sortBy = 'kode_kecamatan', $sortDir = 'asc') {
        $sql = "SELECT d.*, kc.nama_kecamatan, kc.kode_kecamatan, kb.nama_kabupaten, kc.kabupaten_id
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.deleted_at IS NULL";
        $params = [];
        
        // Filter by kabupaten (via kecamatan join)
        if ($kabupatenId) {
            $sql .= " AND kc.kabupaten_id = ?";
            $params[] = $kabupatenId;
        }
        
        // Filter by specific kecamatan
        if ($kecamatanId) {
            $sql .= " AND d.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        // Search filter
        if ($search) {
            $sql .= " AND (d.nama_desa LIKE ? OR d.kode_desa LIKE ? OR d.kode_pos LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Validate and build ORDER BY clause
        $allowedSortColumns = [
            'kode_kecamatan' => 'kc.kode_kecamatan',
            'kode_desa' => 'd.kode_desa',
            'nama_desa' => 'd.nama_desa',
            'nama_kecamatan' => 'kc.nama_kecamatan',
            'nama_kabupaten' => 'kb.nama_kabupaten'
        ];
        
        $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';
        $sortColumn = $allowedSortColumns[$sortBy] ?? 'kc.kode_kecamatan';
        
        // Default: sort by kode_kecamatan first, then kode_desa
        if ($sortBy === 'kode_kecamatan') {
            $sql .= " ORDER BY kc.kode_kecamatan $sortDir, d.kode_desa ASC";
        } elseif ($sortBy === 'kode_desa') {
            $sql .= " ORDER BY d.kode_desa $sortDir";
        } else {
            $sql .= " ORDER BY $sortColumn $sortDir, kc.kode_kecamatan ASC, d.kode_desa ASC";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count desa with kabupaten and kecamatan filters
     * @param int|null $kabupatenId Filter by kabupaten
     * @param int|null $kecamatanId Filter by kecamatan
     * @param string $search Search term
     * @return int Total count
     */
    public function countWithKabupaten($kabupatenId = null, $kecamatanId = null, $search = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.deleted_at IS NULL";
        $params = [];
        
        if ($kabupatenId) {
            $sql .= " AND kc.kabupaten_id = ?";
            $params[] = $kabupatenId;
        }
        
        if ($kecamatanId) {
            $sql .= " AND d.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        if ($search) {
            $sql .= " AND (d.nama_desa LIKE ? OR d.kode_desa LIKE ? OR d.kode_pos LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    /**
     * Search desa for autocomplete suggestions
     * @param int|null $kabupatenId Filter by kabupaten
     * @param int|null $kecamatanId Filter by kecamatan
     * @param string $search Search term
     * @param int $limit Max results to return
     * @return array List of matching desa for autocomplete
     */
    public function searchForAutocomplete($kabupatenId = null, $kecamatanId = null, $search = '', $limit = 10) {
        if (empty($search) || strlen($search) < 2) {
            return [];
        }
        
        $sql = "SELECT d.id, d.nama_desa, d.kode_desa, kc.nama_kecamatan, kb.nama_kabupaten
                FROM master_desa d
                LEFT JOIN master_kecamatan kc ON d.kecamatan_id = kc.id
                LEFT JOIN master_kabupaten kb ON kc.kabupaten_id = kb.id
                WHERE d.deleted_at IS NULL AND d.nama_desa LIKE ?";
        $params = ["%$search%"];
        
        if ($kabupatenId) {
            $sql .= " AND kc.kabupaten_id = ?";
            $params[] = $kabupatenId;
        }
        
        if ($kecamatanId) {
            $sql .= " AND d.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        $limit = (int)$limit;
        $sql .= " ORDER BY d.nama_desa ASC LIMIT $limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Validate that a kecamatan belongs to a specific kabupaten
     * @param int $kecamatanId Kecamatan ID to validate
     * @param int $kabupatenId Kabupaten ID to check against
     * @return bool True if kecamatan belongs to kabupaten
     */
    public function validateKecamatanInKabupaten($kecamatanId, $kabupatenId) {
        $sql = "SELECT COUNT(*) as c FROM master_kecamatan WHERE id = ? AND kabupaten_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kecamatanId, $kabupatenId]);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
}