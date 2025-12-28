<?php
require_once ROOT_PATH . '/app/core/Cache.php';

class MasterKecamatan extends Model {
    protected $table = 'master_kecamatan';
    
    public function getByKabupaten($kabupatenId, $q = null, $limit = 100) {
        // Sort by kode_kecamatan ascending (Kencong 3509010 before Ajung 3509110)
        $cacheKey = 'master_kecamatan_kab_by_kode_' . $kabupatenId;
        
        if (!$q) {
            return Cache::remember($cacheKey, function() use ($kabupatenId) {
                $stmt = $this->db->prepare("SELECT * FROM master_kecamatan WHERE kabupaten_id = ? AND deleted_at IS NULL ORDER BY kode_kecamatan ASC");
                $stmt->execute([$kabupatenId]);
                return $stmt->fetchAll();
            }, 3600); // Cache for 1 hour
        }
        
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM master_kecamatan WHERE kabupaten_id = ? AND nama_kecamatan LIKE ? AND deleted_at IS NULL ORDER BY kode_kecamatan ASC LIMIT $limit");
        $stmt->execute([$kabupatenId, '%'.$q.'%']);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM master_kecamatan WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Admin CRUD Methods
    public function getAllWithKabupaten($kabupatenId = null, $search = '', $limit = 20, $offset = 0) {
        $sql = "SELECT k.*, kb.nama_kabupaten 
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.deleted_at IS NULL";
        $params = [];
        
        if ($kabupatenId) {
            $sql .= " AND k.kabupaten_id = ?";
            $params[] = $kabupatenId;
        }
        
        if ($search) {
            $sql .= " AND (k.nama_kecamatan LIKE ? OR k.kode_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " ORDER BY kb.nama_kabupaten, k.nama_kecamatan LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($kabupatenId = null, $search = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.deleted_at IS NULL";
        $params = [];
        
        if ($kabupatenId) {
            $sql .= " AND k.kabupaten_id = ?";
            $params[] = $kabupatenId;
        }
        
        if ($search) {
            $sql .= " AND (k.nama_kecamatan LIKE ? OR k.kode_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO master_kecamatan (kabupaten_id, nama_kecamatan, kode_kecamatan, created_by) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['kabupaten_id'], $data['nama_kecamatan'], $data['kode_kecamatan'], $data['created_by']]);
        Cache::delete('master_kecamatan_kab_' . $data['kabupaten_id']);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        // Get old data to clear cache
        $old = $this->findById($id);
        
        $sql = "UPDATE master_kecamatan SET kabupaten_id = ?, nama_kecamatan = ?, kode_kecamatan = ?, updated_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['kabupaten_id'], $data['nama_kecamatan'], $data['kode_kecamatan'], $data['updated_by'], $id]);
        
        // Clear cache for both old and new kabupaten
        if ($old) {
            Cache::delete('master_kecamatan_kab_' . $old['kabupaten_id']);
        }
        Cache::delete('master_kecamatan_kab_' . $data['kabupaten_id']);
        
        return $stmt->rowCount();
    }

    public function updateNameOnly($id, $nama, $userId) {
        $old = $this->findById($id);
        if (!$old) return 0;

        $sql = "UPDATE master_kecamatan SET nama_kecamatan = ?, updated_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nama, $userId, $id]);

        // Clear cache for the related kabupaten
        Cache::delete('master_kecamatan_kab_' . $old['kabupaten_id']);
        return $stmt->rowCount();
    }
    
    public function softDelete($id, $userId) {
        // Get data to clear cache
        $data = $this->findById($id);
        
        $sql = "UPDATE master_kecamatan SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $id]);
        
        if ($data) {
            Cache::delete('master_kecamatan_kab_' . $data['kabupaten_id']);
        }
        
        return $stmt->rowCount();
    }

    public function clearCacheByKabupaten($kabupatenId) {
        Cache::delete('master_kecamatan_kab_' . $kabupatenId);
    }

    public function clearAllCache() {
        // Clear all kecamatan caches (pattern matching)
        $files = glob(ROOT_PATH . '/storage/cache/*.cache');
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if (isset($data['value']) && strpos($file, 'master_kecamatan') !== false) {
                unlink($file);
            }
        }
    }
    
    public function checkKodeExists($kode, $excludeId = null) {
        $sql = "SELECT COUNT(*) as c FROM master_kecamatan WHERE kode_kecamatan = ? AND deleted_at IS NULL";
        $params = [$kode];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    public function countByKabupaten($kabupatenId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM master_kecamatan WHERE kabupaten_id = ? AND deleted_at IS NULL");
        $stmt->execute([$kabupatenId]);
        return $stmt->fetch()['c'] ?? 0;
    }
    
    public function findByIdWithKabupaten($id) {
        $sql = "SELECT k.*, kb.nama_kabupaten 
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.id = ? AND k.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByKabupatenForDropdown($kabupatenId) {
        // Order by kode_kecamatan ascending so Kencong (3509010) appears before Ajung (3509110)
        $stmt = $this->db->prepare("SELECT id, nama_kecamatan, kode_kecamatan FROM master_kecamatan WHERE kabupaten_id = ? AND deleted_at IS NULL ORDER BY kode_kecamatan ASC");
        $stmt->execute([$kabupatenId]);
        return $stmt->fetchAll();
    }

    public function checkNameExists($kabupatenId, $nama, $excludeId = null) {
        $sql = "SELECT COUNT(*) as c FROM master_kecamatan WHERE kabupaten_id = ? AND nama_kecamatan = ? AND deleted_at IS NULL";
        $params = [$kabupatenId, $nama];
        if ($excludeId) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    /**
     * Get all kecamatan with pagination and advanced filters for DataTables
     * @param string $search General search term
     * @param int $limit Number of records per page
     * @param int $offset Starting offset
     * @param string $orderBy Column to sort by (whitelisted)
     * @param string $orderDir Sort direction (asc/desc)
     * @param string $kabupatenId Filter by specific kabupaten
     * @param string $kodeKecamatan Filter by specific kode kecamatan
     * @return array
     */
    public function getAllWithPaginationAndFilters($search = '', $limit = 20, $offset = 0, $orderBy = 'kode_kecamatan', $orderDir = 'asc', $kabupatenId = '', $kodeKecamatan = '') {
        $sql = "SELECT k.id, k.kode_kecamatan, k.nama_kecamatan, k.kabupaten_id, kb.nama_kabupaten, kb.kode_kabupaten
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.deleted_at IS NULL";
        $params = [];
        
        // Filter by kabupaten_id if provided
        if ($kabupatenId !== '' && is_numeric($kabupatenId)) {
            $sql .= " AND k.kabupaten_id = ?";
            // Preserve leading zeros by not casting to int
            $params[] = (string)$kabupatenId;
        }
        
        // Filter by specific kode_kecamatan if provided (exact match)
        if ($kodeKecamatan !== '') {
            $sql .= " AND k.kode_kecamatan = ?";
            $params[] = $kodeKecamatan;
        }
        
        // General search filter (applies to nama, kode, and kabupaten name)
        if ($search !== '') {
            $sql .= " AND (k.nama_kecamatan LIKE ? OR k.kode_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Validate and sanitize order parameters (whitelist approach)
        $allowedColumns = ['kode_kecamatan', 'nama_kecamatan', 'nama_kabupaten', 'kode_kabupaten'];
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'kode_kecamatan';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        // Determine the correct table prefix for ordering
        if ($orderBy === 'nama_kabupaten') {
            $orderColumn = "kb.$orderBy";
        } elseif ($orderBy === 'kode_kabupaten') {
            $orderColumn = "kb.$orderBy";
        } else {
            $orderColumn = "k.$orderBy";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        // Default ordering: first by kabupaten BPS code, then by kecamatan BPS code
        if ($orderBy === 'kode_kecamatan') {
            // When sorting by kecamatan code, still maintain kabupaten grouping
            $sql .= " ORDER BY kb.kode_kabupaten ASC, k.kode_kecamatan $orderDir";
        } else {
            $sql .= " ORDER BY $orderColumn $orderDir";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count kecamatan with filters for DataTables
     * @param string $search General search term
     * @param string $kabupatenId Filter by specific kabupaten
     * @param string $kodeKecamatan Filter by specific kode kecamatan
     * @return int
     */
    public function countWithFilters($search = '', $kabupatenId = '', $kodeKecamatan = '') {
        $sql = "SELECT COUNT(*) as c 
                FROM master_kecamatan k
                LEFT JOIN master_kabupaten kb ON k.kabupaten_id = kb.id
                WHERE k.deleted_at IS NULL";
        $params = [];
        
        // Filter by kabupaten_id if provided
        if ($kabupatenId !== '' && is_numeric($kabupatenId)) {
            $sql .= " AND k.kabupaten_id = ?";
            // Preserve leading zeros by not casting to int
            $params[] = (string)$kabupatenId;
        }
        
        // Filter by specific kode_kecamatan if provided
        if ($kodeKecamatan !== '') {
            $sql .= " AND k.kode_kecamatan = ?";
            $params[] = $kodeKecamatan;
        }
        
        // General search filter
        if ($search !== '') {
            $sql .= " AND (k.nama_kecamatan LIKE ? OR k.kode_kecamatan LIKE ? OR kb.nama_kabupaten LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['c'] ?? 0;
    }
    
    /**
     * Validate that a kecamatan with given kode belongs to specified kabupaten
     * @param string $kodeKecamatan
     * @param int $kabupatenId
     * @return bool
     */
    public function validateKecamatanInKabupaten($kodeKecamatan, $kabupatenId) {
        $sql = "SELECT COUNT(*) as c FROM master_kecamatan 
                WHERE kode_kecamatan = ? AND kabupaten_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kodeKecamatan, (int)$kabupatenId]);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
}
